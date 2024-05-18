<?php

namespace LivewireDuskTestbench;

use Illuminate\Support\ServiceProvider;
use Laravel\Dusk\Browser;

class LivewireDuskTestbenchServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Browser::mixin(new DuskBrowserMixin());
    }
}
