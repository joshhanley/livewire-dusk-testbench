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

        Browser::macro('assertHasAllClasses', function (string $selector, array $contents = []) {
            $fullSelector = $this->resolver->format($selector);

            $invalidClasses = array_diff($contents, explode(' ', $this->attribute($selector, 'class')));
            
            PHPUnit::assertEmpty(
                $invalidClasses,
                "Element [{$fullSelector}] is missing required classes [".implode(" ", $invalidClasses)."]."
            );

            return $this;
        });

        Browser::macro('assertHasOnlyClasses', function (string $selector, array $contents = []) {
            $fullSelector = $this->resolver->format($selector);

            $invalidClasses = array_diff(explode(' ', $this->attribute($selector, 'class')), $contents);

            PHPUnit::assertEmpty(
                $invalidClasses,
                "Element [{$fullSelector}] has classes that must not be present [".implode(" ", $invalidClasses)."]."
            );

            return $this;
        });

        Browser::macro('assertMissingAllClasses', function (string $selector, array $contents = []) {
            $fullSelector = $this->resolver->format($selector);

            $invalidClasses = array_intersect($contents, explode(' ', $this->attribute($selector, 'class')));

            PHPUnit::assertEmpty(
                $invalidClasses,
                "Element [{$fullSelector}] has classes that must be missing [".implode(" ", $invalidClasses)."]."
            );

            return $this;
        });

        Browser::macro('assertSeeInOrder', function ($selector, $contents) {
            $fullSelector = $this->resolver->format($selector);

            $element = $this->resolver->findOrFail($selector);

            $contentsString = implode(', ', $contents);

            PHPUnit::assertThat(
                array_map('e', $contents),
                new SeeInOrder($element->getText()),
                "Did not see expected contents [{$contentsString}] within element [{$fullSelector}]."
            );

            return $this;
        });
    }
}
