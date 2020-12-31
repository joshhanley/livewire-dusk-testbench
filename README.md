# Livewire Dusk Testbench

---

## Overrides

```php
protected $packageProviders = [];
protected $withoutUI = false;
protected $storeConsoleLogs = false;
protected $captureFailures = false;
protected $appDebug = true;

public static $useSafari = false;

protected function getPackageProviders($app)
{
    return [
        ...parent::getPackageProviders($app),
        LivewireComponentsServiceProvider::class,
    ];
}

public function viewsDirectory()
{
    return __DIR__.'/views';
}
```

## App Key

Setup app key in phpunit file as per testbench instructions
