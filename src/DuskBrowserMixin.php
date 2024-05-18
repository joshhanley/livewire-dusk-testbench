<?php

namespace LivewireDuskTestbench;

use Illuminate\Testing\Constraints\SeeInOrder;
use PHPUnit\Framework\Assert as PHPUnit;

class DuskBrowserMixin
{
    public function assertSeeInOrder()
    {
        return function ($selector, $contents) {
            /** @var \Laravel\Dusk\Browser $this */
            $fullSelector = $this->resolver->format($selector);

            $element = $this->resolver->findOrFail($selector);

            $contentsString = implode(', ', $contents);

            PHPUnit::assertThat(
                array_map('e', $contents),
                new SeeInOrder($element->getText()),
                "Did not see expected contents [{$contentsString}] within element [{$fullSelector}]."
            );

            return $this;
        };
    }

    public function assertIsVisibleInContainer()
    {
        $script = $this->isVisibleScript();

        return function ($container, $selector) use ($script) {
            /** @var \Laravel\Dusk\Browser $this */
            $fullSelector = $this->resolver->format($selector);
            $fullContainer = $this->resolver->format($container);

            $this->resolver->findOrFail($selector);
            $this->resolver->findOrFail($container);

            PHPUnit::assertTrue(
                $this->driver->executeScript(sprintf($script, $fullSelector, $fullContainer)),
                "Element [{$fullSelector}] is not visible in [{$fullContainer}]"
            );

            return $this;
        };
    }

    public function assertIsNotVisibleInContainer()
    {
        $script = $this->isVisibleScript();

        return function ($container, $selector) use ($script) {
            /** @var \Laravel\Dusk\Browser $this */
            $fullSelector = $this->resolver->format($selector);
            $fullContainer = $this->resolver->format($container);

            $this->resolver->findOrFail($selector);
            $this->resolver->findOrFail($container);

            PHPUnit::assertFalse(
                $this->driver->executeScript(sprintf($script, $fullSelector, $fullContainer)),
                "Element [{$fullSelector}] is visible in [{$fullContainer}]"
            );

            return $this;
        };
    }

    protected function isVisibleScript()
    {
        return '
            let elRect = document.querySelector(`%1$s`).getBoundingClientRect()
            let containerRect = document.querySelector(`%2$s`).getBoundingClientRect()

            return containerRect.top < elRect.bottom && containerRect.bottom > elRect.top
        ';
    }
}
