# Livewire Dusk Testbench

Livewire Dusk Testbench is a convenience wrapper around [Orchestral Testbench Dusk](https://github.com/orchestral/testbench-dusk) to make testing [Livewire](https://github.com/livewire/livewire) components in your package using [Laravel Dusk](https://laravel.com/docs/dusk) easier.

The code was developed by [Caleb Porzio](https://github.com/calebporzio) for testing Livewire itself, and packaged up by [Josh Hanley](https://github.com/joshhanley) for use by others.

## Getting Started

It's recommended you read the documentation of these packages before going through this document:

- [Livewire](https://laravel-livewire.com/docs)
- [Orchestra Testbench Dusk](https://github.com/orchestral/testbench-dusk)
- [Laravel Dusk](https://laravel.com/docs/dusk)
- [Orchestra Testbench](https://github.com/orchestral/testbench)
- [Laravel Package Development](https://laravel.com/docs/packages)

## Installation

To install through composer, run the following command from terminal:

```bash
composer require --dev joshhanley/livewire-dusk
```

## Usage

To use this package you need to:

- Setup your base browser testcase
- Configure an app key
- Register your package service providers (if required)
- Setup layout views (if required)
- Configure test directory and namespace (if required)

Then you are ready to start testing.

There are other configuration options you can override depending on your needs.

### Setup Browser TestCase

To use Livewire Dusk, all you need to do is extend `LivewireDusk\TestCase` instead of `Orchestra\Testbench\Dusk\TestCase` in your dusk tests.

Or configure this in your base browser testcase:

```php
<?php

class BrowserTestCase extends LivewireDusk\TestCase
{
    //
}
```

### Configure App Key

Setup app key in phpunit.xml file as per [testbench instructions](https://github.com/orchestral/testbench#no-supported-encrypter-found-the-cipher-and--or-key-length-are-invalid):

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

Register your package services providers in $packageProviders property to ensure they are loaded for testing:

```php
public $packageProviders = [
    YourPackageServiceProvider::class,
];
```

### Setup Layout Views

To add other packages to your app layout for testing with, such as AlpineJS, you will need to create a custom layout.

Create your own app layout by creating a `views/layouts` folder somewhere in your package and add a `app.blade.php` file inside the layouts folder.

Populate your app layout as required (making sure in you include Livewire Scripts and Styles).

Then set your base view folder by overridding `viewsDirectory` method to point to the `views` folder you created.

*For Example*

A good location to store your views folder and app layout would be in your Dusk browser tests folder.

In the root of your package, create the following directories and file
`tests/Browser/views/layouts/app.blade.php`

Then in your `tests/Browser/TestCase.php` file add:

```php
public function configureViewsDirectory()
{
    // Resolves to 'tests/Browser/views'
    $this->viewsDirectory = __DIR__.'/views';
}
```

### Configure Tests Directory and Namespace

This package assumes you have a `tests` directory at the root of your project and that it's namespace contains the word `Tests`. It tries to automatically guess the namespace for your tests based on this.

If you have a different configuration, you can manually specify your namespace in your Browser Testcase file:

```php
public $testsNamespace = 'Company\\Package\\Tests';
```

You can also override the `configureTestsDirectory` method to calculate the absolute path of your tests directory:

```php

public function configureTestsDirectory()
{
    $this->testsDirectory = "/absolute/path/to/tests";
}
```

## Possible Overrides

Below is a list of some of the settings you can override inside your browser TestCase file to suit your testing needs. Listed below are the defaults:

```php
public $packageProviders = [];
public $packagePath = '';
public $testsDirectory = '';
public $testsNamespace = '';
public $viewsDirectory = '';

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
        $this->viewsDirectory = __DIR__.'/../../resources/views';
    }
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
        'root' => $this->getTestsDirectory().'/Browser/downloads',
    ]);
}

public function tweakApplicationHook()
{
    return function () {
    };
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
