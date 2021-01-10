<?php

namespace LivewireDusk;

use Closure;
use Exception;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
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
    use SupportsSafari;

    public $packageProviders = [];
    public $packagePath = '';
    public $testsDirectory = '';
    public $testsNamespace = '';
    public $viewsDirectory = '';
    public $databaseName = '';

    public $appDebug = true;
    public $useDatabase = true;
    public $useFilesystemDisks = true;

    public $withoutUI = false;
    public $storeConsoleLogs = false;
    public $captureFailures = false;

    public static $useSafari = false;

    public function configurePackagePath()
    {
        if ($this->packagePath == '') {
            $this->packagePath = getcwd();
        }
    }

    public function configureTestsDirectory()
    {
        if ($this->testsDirectory == '') {
            $this->testsDirectory = $this->getPackagePath()."/tests";
        }
    }

    public function configureViewsDirectory()
    {
        if ($this->viewsDirectory == '') {
            $this->viewsDirectory = __DIR__.'/../resources/views';
        }
    }

    public function configureDatabaseName()
    {
        $this->databaseName = $this->getPackagePath() . '/database/database.sqlite';
    }

    public function configureDatabase($app)
    {
        /**
         * Dusk Testbench doesn't support in memory sqlite database so we need to specifiy one.
         */
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => $this->getDatabaseName(),
            'prefix'   => '',
        ]);
    }

    public function configureFilesystemDisks($app)
    {
        $app['config']->set('filesystems.disks.dusk-downloads', [
            'driver' => 'local',
            'root' => $this->getTestsDirectory().'/Browser/downloads',
        ]);
    }

    public function tweakApplicationHook()
    {
        /**
         * As the database name is calculated, we need to pass it to the app
         * through this serialised closure to ensure it resolves correctly.
         */

        $databaseName = $this->getDatabaseName();

        return function () use ($databaseName) {
            $default = app('config')->get('database.default');

            app('config')->set("database.connections.{$default}.database", $databaseName);
        };
    }

    public function getPackagePath()
    {
        return $this->packagePath;
    }

    public function getTestsDirectory()
    {
        return $this->testsDirectory;
    }

    public function getTestsNamespace()
    {
        return $this->testsNamespace;
    }

    public function getViewsDirectory()
    {
        return $this->viewsDirectory;
    }

    public function getDatabaseName()
    {
        return $this->databaseName;
    }

    public function getTestComponentsClassList()
    {
        return $this->generateTestComponentsClassList();
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

    protected function setUp(): void
    {
        $this->configurePackagePath();

        $this->configureTestsDirectory();

        $this->checkTestsNamespace();

        $this->configureDatabaseName();

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

        $testComponents = $this->getTestComponentsClassList();

        $tweakApplicationHook = $this->tweakApplicationHook();

        $this->tweakApplication(function () use ($testComponents, $tweakApplicationHook) {
            // Autoload all Livewire components in this test suite.
            $testComponents->each(function ($componentClass) {
                app('livewire')->component($componentClass);
            });

            $tweakApplicationHook();
        });
    }

    protected function tearDown(): void
    {
        $this->removeApplicationTweaks();

        parent::tearDown();
    }

    protected function checkTestsNamespace()
    {
        if ($this->isTestsNamespacePopulated()) {
            return;
        }

        if ($this->tryAndGuessTestsNamespace()) {
            return;
        }

        throw new \Exception('Tests namespace missing. Set tests namespace');
        exit;
    }

    protected function isTestsNamespacePopulated()
    {
        return isset($this->testsNamespace) && $this->testsNamespace !=  '';
    }

    protected function tryAndGuessTestsNamespace()
    {
        $className = Str::of(get_class($this));

        if (! $className->contains('Tests')) {
            return false;
        }

        $testsNamespace = $className->before($className->after('Tests'));

        $this->testsNamespace = $testsNamespace;

        return $this->isTestsNamespacePopulated();
    }

    protected function generateTestComponentsClassList()
    {
        return collect(File::allFiles($this->getTestsDirectory()))
            ->map(function ($file) {
                return $this->generateClassNameFromFile($file);
            })
            ->filter(function ($computedClassName) {
                return class_exists($computedClassName);
            })
            ->filter(function ($class) {
                return is_subclass_of($class, Component::class);
            });
    }

    protected function generateClassNameFromFile($file)
    {
        return $this->getTestsNamespace().'\\'. Str::of($file->getRelativePathname())->before('.php')->replace('/', '\\');
    }

    protected function storeConsoleLogsFor($browsers)
    {
        if ($this->storeConsoleLogs) {
            parent::storeConsoleLogsFor($browsers);
        }
    }

    protected function captureFailuresFor($browsers)
    {
        if ($this->captureFailures) {
            parent::captureFailuresFor($browsers);
        }
    }

    protected function makeACleanSlate()
    {
        Artisan::call('view:clear');

        File::deleteDirectory($this->livewireViewsPath());
        File::cleanDirectory($this->getTestsDirectory().'/Browser/downloads');
        File::deleteDirectory($this->livewireClassesPath());
        File::delete(app()->bootstrapPath('cache/livewire-components.php'));
    }

    protected function getPackageProviders($app)
    {
        return [
            LivewireServiceProvider::class,
            LivewireDuskServiceProvider::class,
            ...$this->packageProviders
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        if (! $app['config']->get('app.key')) {
            throw new \Exception('Please set an app key in you phpunit file');
            exit;
        }

        $this->setAppDebug($app);

        $this->setViewsDirectory($app);

        if ($this->useDatabase) {
            $this->configureDatabase($app);
        }

        if ($this->useFilesystemDisks) {
            $this->configureFilesystemDisks($app);
        }
    }

    protected function setAppDebug($app)
    {
        $app['config']->set('app.debug', $this->appDebug);
    }

    protected function setViewsDirectory($app)
    {
        $this->configureViewsDirectory();

        $app['config']->set('view.paths', [
            $this->getViewsDirectory(),
            resource_path('views'),
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
            'download.default_directory' => $this->getTestsDirectory().'/Browser/downloads',
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

    protected function breakIntoATinkerShell($browsers, $e)
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
