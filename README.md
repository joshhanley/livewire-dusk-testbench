# Livewire Dusk Testbench

Livewire Dusk Testbench is a convenience wrapper around [Orchestral Testbench Dusk](https://github.com/orchestral/testbench-dusk) to make testing [Livewire](https://github.com/livewire/livewire) components in your package using [Laravel Dusk](https://laravel.com/docs/dusk) easier.

The code was developed by [Caleb Porzio](https://github.com/calebporzio) for testing Livewire itself, and packaged up by [Josh Hanley](https://github.com/joshhanley) for use by others.

## Getting Started

It's recommended you read the documentation of the packages before going through this document

- [Livewire](https://laravel-livewire.com/docs)
- [Orchestra Testbench Dusk](https://github.com/orchestral/testbench-dusk)
- [Laravel Dusk](https://laravel.com/docs/dusk)
- [Orchestra Testbench](https://github.com/orchestral/testbench)
- [Laravel Package Development](https://laravel.com/docs/packages)

## Installation

To install through composer, run the following command from terminal

```bash
composer require --dev josh/livewire-dusk
```

## Usage

To use this package you need to

- Setup your base browser testcase
- Configure an app key
- Register your package service providers (if required)
- Setup layout views (if required)

Then you are ready to start testing.

There are other configuration options you can override depending on your needs.

### Setup Browser TestCase

To use Livewire Dusk, all you need to do is extend `LivewireDusk\TestCase` instead of `Orchestra\Testbench\Dusk\TestCase` in your dusk tests.

Or configure this in your base browser testcase

```php
<?php

class BrowserTestCase extends LivewireDusk\TestCase
{
    //
}
```

### Configure App Key

Setup app key in phpunit.xml file as per [testbench instructions](https://github.com/orchestral/testbench#no-supported-encrypter-found-the-cipher-and--or-key-length-are-invalid)

>To solve this you can add a dummy APP_KEY or use a specific key to your application/package phpunit.xml.

```xml
<phpunit>

    // ...

    <php>
        <env name="APP_KEY" value="AckfSECXIvnK5r28GVIWUAxmbBSjTsmF"/>
    </php>

</phpunit>

```

### Register Package Service Providers

Register your package services providers in $packageProviders property to ensure they are loaded for testing.

### Setup Layout Views

To add other packages to your app layout such as AlpineJS, you will need to create a custom layout.

Create your own app layout by creating a `views/layouts/app.blade.php` file somewhere in your package.

Then set your base view folder by overridding `viewsDirectory` method to point to the `views` folder you created.

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

## Troubleshooting

This is just a convenience wrapper around Orchestral Testbench Dusk to make testing Livewire Components in your package easier.

Consult the documentation for the relevant packages for troubleshooting.

- [Livewire](https://laravel-livewire.com/docs)
- [Orchestra Testbench Dusk](https://github.com/orchestral/testbench-dusk)
- [Laravel Dusk](https://laravel.com/docs/dusk)
- [Orchestra Testbench](https://github.com/orchestral/testbench)
- [Laravel Package Development](https://laravel.com/docs/packages)
