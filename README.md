# Livewire Dusk Testbench

---

## App Key

Setup app key in phpunit file as per testbench instructions

## Views

To add other packages to your app layout such as AlpineJS, you will need to create a custom layout.

Create your own app layout by creating a `views/layouts/app.blade.php` file somewhere in your package.
Then set your base view folder by overridding `viewsDirectory` method to point to the `views` folder you created.

## Package Providers

Register your package services providers in $packageProviders property to ensure they are loaded for testing.

## Possible Overrides

```php
public $packageProviders = [];

public $appDebug = true;
public $useDatabase = true;
public $useFilesystemDisks = true;

public $withoutUI = false;
public $storeConsoleLogs = false;
public $captureFailures = false;

public static $useSafari = false;

public function viewsDirectory()
{
    return __DIR__.'/views';
}

public function configureDatabase($app)
{
    $app['config']->set('database.default', 'testbench');
    $app['config']->set('database.connections.testbench', [
        'driver'   => 'sqlite',
        'database' => ':memory:',
        'prefix'   => '',
    ]);
}

public function configureFilesystemDisks($app)
{
    $app['config']->set('filesystems.disks.dusk-downloads', [
        'driver' => 'local',
        'root' => __DIR__.'/downloads',
    ]);
}

```
