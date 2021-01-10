<?php

namespace LivewireDuskTestbench;

use Illuminate\Support\ServiceProvider;
use Illuminate\Testing\Constraints\SeeInOrder;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Assert as PHPUnit;

class LivewireDuskTestbenchServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Browser::macro('assertSeeInOrder', function ($selector, $contents) {
            $fullSelector = $this->resolver->format($selector);

            $element = $this->resolver->findOrFail($selector);

            $contentsString = implode(', ', $contents);

            PHPUnit::assertThat(
                array_map('e', ($contents)),
                new SeeInOrder($element->getText()),
                "Did not see expected contents [{$contentsString}] within element [{$fullSelector}]."
            );

            return $this;
        });
    }
}
