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
composer require --dev joshhanley/livewire-dusk-testbench
```

## Upgrading from V2 of this package

- App key in phpunit.xml is no longer needed.
- Signature of `public array $packageProviders = [];` now has array type definition.
- `configureViewDirectory()` method has been renamed to `viewsDirectory` and should now return a string of the path for the views directory. A `string` return type is also required.
- `$testsNamespace` property and `configureTestsDirectory()` method are no longer needed so can be deleted.
- `tweatApplicationHook()` method is now a `static` method.
- Almost all of the items listed in the `Possible Overrides` in the old README are no longer relevant and can be removed. Remaining ones are listed in that section below.
- Tests no longer need the `$this->browse()` method to be called, instead you can call `Livewire::visit(MyComponent::class)` and the first parameter of Livewire:visit is no longer `$browser` and instead is your component class. See below examples:

Old test structure:
```php
/** @test */
public function it_can_count()
{
    $this->browse(function (Browser $browser) {
        Livewire::visit($browser, CounterComponent::class)
            // Assertions here
            ;
    });
}
```

New test structure:
```php
/** @test */
public function it_can_count()
{
    Livewire::visit(CounterComponent::class)
        // Assertions here
        ;
}
```


## Usage

To use this package you need to:

- Setup your base browser testcase
- Register your package service providers (if required)
- Setup layout views (if required)
- Configure test directory and namespace (if required)

Then you are ready to start testing.

There are other configuration options you can override depending on your needs.

### Setup Browser TestCase

To use Livewire Dusk Testbench, all you need to do is extend `LivewireDuskTestbench\TestCase` instead of `Orchestra\Testbench\Dusk\TestCase` in your dusk tests.

Or configure this in your base browser testcase:

```php
<?php

class BrowserTestCase extends LivewireDuskTestbench\TestCase
{
    //
}
```

### Register Package Service Providers

Register your package services providers in $packageProviders property to ensure they are loaded for testing:

```php
public array $packageProviders = [
    YourPackageServiceProvider::class,
];
```

### Setup Layout Views

To add other packages to your app layout for testing with, such as AlpineJS, you will need to create a custom layout.

Create your own app layout by creating a `views/components/layouts` folder somewhere in your package and add a `app.blade.php` file inside the layouts folder.

Populate your app layout as required.

Then set your base view folder by overridding `viewsDirectory` method to return a path to the `views` folder you created.

*For Example*

A good location to store your views folder and app layout would be in your Dusk browser tests folder.

In the root of your package, create the following directories and file
`tests/Browser/views/components/layouts/app.blade.php`

Then in your `tests/Browser/TestCase.php` file add:

```php
public function viewsDirectory(): string
{
    // Resolves to 'tests/Browser/views'
    return __DIR__.'/views';
}
```

## Livewire Dusk Macros

Livewire comes with a bunch of Dusk macros which you can use.

Check them out in the Livewire source in ['livewire/src/Features/SupportTesting/DuskBrowserMacros.php'](https://github.com/livewire/livewire/blob/main/src/Features/SupportTesting/DuskBrowserMacros.php).

## Demo Package

A demo package has been setup which gives a sample of how this package can be used. Check it out here

[Livewire Package Demo](https://github.com/joshhanley/livewire-package-demo)

## Possible Overrides

Below is a list of some of the settings you can override inside your browser TestCase file to suit your testing needs. Listed below are the defaults:

```php
public $packageProviders = [];

public function viewsDirectory(): string
{
    return '';
}

public static function tweakApplicationHook()
{
    return function () {};
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
