<?php

namespace LivewireDusk;

use Closure;
use Exception;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Laravel\Dusk\Browser;
use Livewire\LivewireServiceProvider;
use Livewire\Macros\DuskBrowserMacros;
use LivewireDusk\HttpKernel;
use Orchestra\Testbench\Dusk\Options as DuskOptions;
use Orchestra\Testbench\Dusk\TestCase as DuskTestCase;
use Psy\Shell;
use Throwable;

class TestCase extends DuskTestCase
{
    use SupportsSafari;

    protected $packageProviders = [];

    protected $withoutUI = false;
    protected $storeConsoleLogs = false;
    protected $captureFailures = false;
    protected $appDebug = true;
    protected $useDatabase = true;
    protected $useFilesystemDisks = true;

    public static $useSafari = false;

    protected function getPackageProviders($app)
    {
        return [
            LivewireServiceProvider::class,
            ...$this->packageProviders
        ];
    }

    public function setUp(): void
    {
        // Check if running in GitHub actions as CI will be set to true
        if (isset($_SERVER['CI']) || $this->withoutUI == true) {
            DuskOptions::withoutUI();
        }

        Browser::mixin(new DuskBrowserMacros);

        $this->afterApplicationCreated(function () {
            $this->makeACleanSlate();
        });

        $this->beforeApplicationDestroyed(function () {
            $this->makeACleanSlate();
        });

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->removeApplicationTweaks();

        parent::tearDown();
    }

    /**
     * Store the console output for the given browsers.
     *
     * @param  \Illuminate\Support\Collection  $browsers
     * @return void
     */
    protected function storeConsoleLogsFor($browsers)
    {
        if ($this->storeConsoleLogs) {
            parent::storeConsoleLogsFor($browsers);
        }
    }

    /**
     * Capture failure screenshots for each browser.
     *
     * @param  \Illuminate\Support\Collection  $browsers
     * @return void
     */
    protected function captureFailuresFor($browsers)
    {
        if ($this->captureFailures) {
            parent::captureFailuresFor($browsers);
        }
    }

    public function makeACleanSlate()
    {
        Artisan::call('view:clear');

        File::deleteDirectory($this->livewireViewsPath());
        File::cleanDirectory(__DIR__.'/downloads');
        File::deleteDirectory($this->livewireClassesPath());
        File::delete(app()->bootstrapPath('cache/livewire-components.php'));
    }

    public function configureViews($app)
    {
        $app['config']->set('view.paths', [
            $this->viewsDirectory(),
            resource_path('views'),
        ]);
    }

    public function viewsDirectory()
    {
        return __DIR__.'/../../resources/views';
    }

    protected function configureDatabase($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    protected function configureFilesystemDisks($app)
    {
        $app['config']->set('filesystems.disks.dusk-downloads', [
            'driver' => 'local',
            'root' => __DIR__.'/downloads',
        ]);
    }

    protected function getEnvironmentSetUp($app)
    {
        if (! $app['config']->get('app.key')) {
            throw new \Exception('Please set an app key in you phpunit file');
            exit;
        }

        $app['config']->set('app.debug', $this->appDebug);

        $this->configureViews($app);

        if ($this->useDatabase) {
            $this->configureDatabase($app);
        }

        if ($this->useFilesystemDisks) {
            $this->configureFilesystemDisks($app);
        }
    }

    protected function resolveApplicationHttpKernel($app)
    {
        $app->singleton('Illuminate\Contracts\Http\Kernel', HttpKernel::class);
    }

    protected function livewireClassesPath($path = '')
    {
        return app_path('Http/Livewire'.($path ? '/'.$path : ''));
    }

    protected function livewireViewsPath($path = '')
    {
        return resource_path('views').'/livewire'.($path ? '/'.$path : '');
    }

    protected function driver(): RemoteWebDriver
    {
        $options = DuskOptions::getChromeOptions();

        $options->setExperimentalOption('prefs', [
            'download.default_directory' => __DIR__.'/downloads',
        ]);

        return static::$useSafari
            ? RemoteWebDriver::create(
                'http://localhost:9515',
                DesiredCapabilities::safari()
            )
            : RemoteWebDriver::create(
                'http://localhost:9515',
                DesiredCapabilities::chrome()->setCapability(
                    ChromeOptions::CAPABILITY,
                    $options
                )
            );
    }

    public function browse(Closure $callback)
    {
        parent::browse(function (...$browsers) use ($callback) {
            try {
                $callback(...$browsers);
            } catch (Exception $e) {
                if (DuskOptions::hasUI()) {
                    $this->breakIntoATinkerShell($browsers, $e);
                }

                throw $e;
            } catch (Throwable $e) {
                if (DuskOptions::hasUI()) {
                    $this->breakIntoATinkerShell($browsers, $e);
                }

                throw $e;
            }
        });
    }

    public function breakIntoATinkerShell($browsers, $e)
    {
        $sh = new Shell();

        $sh->add(new DuskCommand($this, $e));

        $sh->setScopeVariables([
            'browsers' => $browsers,
        ]);

        $sh->addInput('dusk');

        $sh->setBoundObject($this);

        $sh->run();

        return $sh->getScopeVariables(false);
    }
}
