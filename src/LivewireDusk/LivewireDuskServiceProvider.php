<?php

namespace LivewireDusk;

use Illuminate\Support\ServiceProvider;
use Illuminate\Testing\Constraints\SeeInOrder;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Assert as PHPUnit;

class LivewireDuskServiceProvider extends ServiceProvider
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

        // Browser::macro('assertHasClass', function ($selector, $class) {
        //     $fullSelector = $this->resolver->format($selector);

        //     $actual = $this->resolver->findOrFail($selector)->getAttribute('class');

        //     $actualClasses = explode(' ', $actual);

        //     $isFound = false;

        //     foreach ($actualClasses as $actualClass) {
        //         if ($actualClass == $class) {
        //             $isFound = true;
        //         }
        //     }

        //     PHPUnit::assertNotNull(
        //         $actual,
        //         "Did not see expected attribute [class] within element [{$fullSelector}]."
        //     );

        //     PHPUnit::assertTrue(
        //         $isFound,
        //         "Expected class [{$class}] is not found in class list [$actual]."
        //     );

        //     return $this;
        // });

        // Browser::macro('assertMissingClass', function ($selector, $class) {
        //     $fullSelector = $this->resolver->format($selector);

        //     $actual = $this->resolver->findOrFail($selector)->getAttribute('class');

        //     $actualClasses = explode(' ', $actual);

        //     $isFound = false;

        //     foreach ($actualClasses as $actualClass) {
        //         if ($actualClass == $class) {
        //             $isFound = true;
        //         }
        //     }

        //     PHPUnit::assertNotNull(
        //         $actual,
        //         "Did not see expected attribute [class] within element [{$fullSelector}]."
        //     );

        //     PHPUnit::assertFalse(
        //         $isFound,
        //         "Found unexpected class [{$class}] in class list [$actual]."
        //     );

        //     return $this;
        // });
    }
}
