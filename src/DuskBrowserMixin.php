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

    public function assertHasClasses()
    {
        return function (string $selector, array $contents = []) {
            /** @var \Laravel\Dusk\Browser $this */
            $fullSelector = $this->resolver->format($selector);

            $invalidClasses = array_diff($contents, explode(' ', $this->attribute($selector, 'class')));

            PHPUnit::assertEmpty(
                $invalidClasses,
                "Element [{$fullSelector}] is missing required classes [".implode(' ', $invalidClasses).'].'
            );

            return $this;
        };
    }

    public function assertHasOnlyClasses()
    {
        return function (string $selector, array $contents = []) {
            /** @var \Laravel\Dusk\Browser $this */
            $fullSelector = $this->resolver->format($selector);

            $invalidClasses = array_diff(explode(' ', $this->attribute($selector, 'class')), $contents);

            PHPUnit::assertEmpty(
                $invalidClasses,
                "Element [{$fullSelector}] has classes that must not be present [".implode(' ', $invalidClasses).'].'
            );

            return $this;
        };
    }

    public function assertMissingClasses()
    {
        return function (string $selector, array $contents = []) {
            /** @var \Laravel\Dusk\Browser $this */
            $fullSelector = $this->resolver->format($selector);

            $invalidClasses = array_intersect($contents, explode(' ', $this->attribute($selector, 'class')));

            PHPUnit::assertEmpty(
                $invalidClasses,
                "Element [{$fullSelector}] has classes that must be missing [".implode(' ', $invalidClasses).'].'
            );

            return $this;
        };
    }

    public function assertConsoleLogHasErrors()
    {
        return function ($includeFavicon = false) {
            $logs = $this->driver->manage()->getLog('browser');

            $containsError = false;

            foreach ($logs as $log) {
                if (! isset($log['message']) || ! isset($log['level']) || ($log['level'] !== 'ERROR' && $log['level'] !== 'SEVERE')) {
                    continue;
                }

                // Ignore default favicon.ico error unless specified to include
                if (! str($log['message'])->contains('favicon.ico') || $includeFavicon) {
                    $containsError = true;
                    break;
                }
            }

            PHPUnit::assertTrue($containsError, 'Console log does not contain any error messages');

            return $this;
        };
    }

    public function assertConsoleLogMissingErrors()
    {
        return function ($includeFavicon = false) {
            $logs = $this->driver->manage()->getLog('browser');

            $containsError = false;

            foreach ($logs as $log) {
                if (! isset($log['message']) || ! isset($log['level']) || ($log['level'] !== 'ERROR' && $log['level'] !== 'SEVERE')) {
                    continue;
                }

                // Ignore default favicon.ico error unless specified to include
                if (! str($log['message'])->contains('favicon.ico') || $includeFavicon) {
                    $containsError = true;
                    break;
                }
            }

            PHPUnit::assertFalse($containsError, 'Console log contains an error message');

            return $this;
        };
    }

    public function assertConsoleLogHasError()
    {
        return function ($expectedMessage) {
            $logs = $this->driver->manage()->getLog('browser');

            $containsError = false;

            foreach ($logs as $log) {
                if (! isset($log['message']) || ! isset($log['level']) || ($log['level'] !== 'ERROR' && $log['level'] !== 'SEVERE')) {
                    continue;
                }

                if (str($log['message'])->contains($expectedMessage)) {
                    $containsError = true;
                }
            }

            PHPUnit::assertTrue($containsError, "Console log error message \"{$expectedMessage}\" was not found");

            return $this;
        };
    }

    public function assertConsoleLogMissingError()
    {
        return function ($expectedMessage) {
            $logs = $this->driver->manage()->getLog('browser');

            $containsError = false;

            foreach ($logs as $log) {
                if (! isset($log['message']) || ! isset($log['level']) || ($log['level'] !== 'ERROR' && $log['level'] !== 'SEVERE')) {
                    continue;
                }

                if (str($log['message'])->contains($expectedMessage)) {
                    $containsError = true;
                }
            }

            PHPUnit::assertFalse($containsError, "Console log error message \"{$expectedMessage}\" was found");

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
