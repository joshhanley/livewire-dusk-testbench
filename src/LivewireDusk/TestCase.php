<?php

namespace LivewireDusk;

use Closure;
use Exception;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Livewire\Component;
use Livewire\LivewireServiceProvider;
use Livewire\Macros\DuskBrowserMacros;
use LivewireDusk\HttpKernel;
use Orchestra\Testbench\Dusk\Options as DuskOptions;
use Orchestra\Testbench\Dusk\TestCase as DuskTestCase;
use Psy\Shell;
use Throwable;

class TestCase extends DuskTestCase
{
    protected $packageProviders = [];

    protected $withoutUI = false;
    protected $storeConsoleLogs = false;
    protected $captureFailures = false;

    public static $useSafari = false;

    protected function getPackageProviders($app)
    {
        return [
            LivewireServiceProvider::class,
            ...$this->packageProviders
        ];
    }

    public function viewsDirectory()
    {
        return __DIR__.'/../../resources/views';
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

        $viewsDirectory = $this->viewsDirectory();

        $this->tweakApplication(function () use ($viewsDirectory) {
            // Autoload all Livewire components in this test suite.
            collect(File::allFiles(__DIR__))
                ->map(function ($file) {
                    return 'LivewireComponents\\Tests\\Browser\\'.Str::of($file->getRelativePathname())->before('.php')->replace('/', '\\');
                })
                ->filter(function ($computedClassName) {
                    return class_exists($computedClassName);
                })
                ->filter(function ($class) {
                    return is_subclass_of($class, Component::class);
                })->each(function ($componentClass) {
                    app('livewire')->component($componentClass);
                });

            app('session')->put('_token', 'this-is-a-hack-because-something-about-validating-the-csrf-token-is-broken');


            app('config')->set('view.paths', [
                $viewsDirectory,
                resource_path('views'),
            ]);

            config()->set('app.debug', true);
        });
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

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('filesystems.disks.dusk-downloads', [
            'driver' => 'local',
            'root' => __DIR__.'/downloads',
        ]);
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
