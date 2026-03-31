<?php

namespace LivewireDuskTestbench\Tests\Browser;

use Livewire\Component;
use Livewire\Livewire;
use PHPUnit\Framework\AssertionFailedError;

class DuskBrowserMixinTest extends TestCase
{
    public function test_assert_see_in_order_macro_passes()
    {
        Livewire::visit(new class extends Component
        {
            public function render()
            {
                return <<< 'HTML'
                <div>
                    <ul dusk="list">
                        <li>bob</li>
                        <li>john</li>
                        <li>bill</li>
                    </ul>
                </div>
                HTML;
            }
        })
            ->assertSeeInOrder('@list', ['bob', 'john', 'bill']);
    }

    public function test_assert_see_in_order_macro_fails()
    {
        $this->expectException(AssertionFailedError::class);

        Livewire::visit(new class extends Component
        {
            public function render()
            {
                return <<< 'HTML'
                <div>
                    <ul dusk="list">
                        <li>bob</li>
                        <li>john</li>
                        <li>bill</li>
                    </ul>
                </div>
                HTML;
            }
        })
            ->assertSeeInOrder('@list', ['john', 'bob', 'bill']);
    }

    public function test_assert_is_visibile_in_container_passes()
    {
        Livewire::visit(new class extends Component
        {
            public function render()
            {
                return <<< 'HTML'
                <div>
                    <ul style="height:10px" dusk="list">
                        <li style="height:10px" dusk="bob">bob</li>
                        <li style="height:10px" dusk="john">john</li>
                        <li style="height:10px" dusk="bill">bill</li>
                    </ul>
                </div>
                HTML;
            }
        })
            ->assertIsVisibleInContainer('@list', '@bob');
    }

    public function test_assert_is_visibile_in_container_fails()
    {
        $this->expectException(AssertionFailedError::class);

        Livewire::visit(new class extends Component
        {
            public function render()
            {
                return <<< 'HTML'
                <div>
                    <ul style="height:10px" dusk="list">
                        <li style="height:10px" dusk="bob">bob</li>
                        <li style="height:10px" dusk="john">john</li>
                        <li style="height:10px" dusk="bill">bill</li>
                    </ul>
                </div>
                HTML;
            }
        })
            ->assertIsVisibleInContainer('@list', '@john');
    }

    public function test_assert_is_not_visibile_in_container_passes()
    {
        Livewire::visit(new class extends Component
        {
            public function render()
            {
                return <<< 'HTML'
                <div>
                    <ul style="height:10px" dusk="list">
                        <li style="height:10px" dusk="bob">bob</li>
                        <li style="height:10px" dusk="john">john</li>
                        <li style="height:10px" dusk="bill">bill</li>
                    </ul>
                </div>
                HTML;
            }
        })
            ->assertIsNotVisibleInContainer('@list', '@john')
            ->assertIsNotVisibleInContainer('@list', '@bill');
    }

    public function test_assert_is_not_visibile_in_container_fails()
    {
        $this->expectException(AssertionFailedError::class);

        Livewire::visit(new class extends Component
        {
            public function render()
            {
                return <<< 'HTML'
                <div>
                    <ul style="height:10px" dusk="list">
                        <li style="height:10px" dusk="bob">bob</li>
                        <li style="height:10px" dusk="john">john</li>
                        <li style="height:10px" dusk="bill">bill</li>
                    </ul>
                </div>
                HTML;
            }
        })
            ->assertIsNotVisibleInContainer('@list', '@bob');
    }

    public function test_assert_has_classes_passes()
    {
        Livewire::visit(new class extends Component
        {
            public function render()
            {
                return <<< 'HTML'
                <div>
                    <p class="test other sample" dusk="item"></p>
                </div>
                HTML;
            }
        })
            ->assertHasClasses('@item', ['test', 'other']);
    }

    public function test_assert_has_classes_fails()
    {
        $this->expectException(AssertionFailedError::class);

        Livewire::visit(new class extends Component
        {
            public function render()
            {
                return <<< 'HTML'
                <div>
                    <p class="test sample" dusk="item"></p>
                </div>
                HTML;
            }
        })
            ->assertHasClasses('@item', ['test', 'other']);
    }

    public function test_assert_has_only_classes_passes()
    {
        Livewire::visit(new class extends Component
        {
            public function render()
            {
                return <<< 'HTML'
                <div>
                    <p class="test other" dusk="item"></p>
                </div>
                HTML;
            }
        })
            ->assertHasOnlyClasses('@item', ['test', 'other']);
    }

    public function test_assert_has_only_classes_fails()
    {
        $this->expectException(AssertionFailedError::class);

        Livewire::visit(new class extends Component
        {
            public function render()
            {
                return <<< 'HTML'
                <div>
                    <p class="test other sample" dusk="item"></p>
                </div>
                HTML;
            }
        })
            ->assertHasOnlyClasses('@item', ['test', 'other']);
    }

    public function test_assert_missing_classes_passes()
    {
        Livewire::visit(new class extends Component
        {
            public function render()
            {
                return <<< 'HTML'
                <div>
                    <p class="sample" dusk="item"></p>
                </div>
                HTML;
            }
        })
            ->assertMissingClasses('@item', ['test', 'other']);
    }

    public function test_assert_missing_classes_fails()
    {
        $this->expectException(AssertionFailedError::class);

        Livewire::visit(new class extends Component
        {
            public function render()
            {
                return <<< 'HTML'
                <div>
                    <p class="sample" dusk="item"></p>
                </div>
                HTML;
            }
        })
            ->assertMissingClasses('@item', ['test', 'sample']);
    }

    public function test_assert_console_log_has_errors_passes()
    {
        Livewire::visit(new class extends Component
        {
            public function render()
            {
                return <<< 'HTML'
                <div x-init="console.error('test')">
                </div>
                HTML;
            }
        })
            ->assertConsoleLogHasErrors();
    }

    public function test_assert_console_log_has_errors_fails()
    {
        $this->expectException(AssertionFailedError::class);

        Livewire::visit(new class extends Component
        {
            public function render()
            {
                return <<< 'HTML'
                <div x-init="console.log('test')">
                </div>
                HTML;
            }
        })
            ->assertConsoleLogHasErrors();
    }

    public function test_assert_console_log_missing_errors_passes()
    {
        Livewire::visit(new class extends Component
        {
            public function render()
            {
                return <<< 'HTML'
                <div x-init="console.log('test')">
                </div>
                HTML;
            }
        })
            ->assertConsoleLogMissingErrors();
    }

    public function test_assert_console_log_missing_errors_fails()
    {
        $this->expectException(AssertionFailedError::class);

        Livewire::visit(new class extends Component
        {
            public function render()
            {
                return <<< 'HTML'
                <div x-init="console.error('test')">
                </div>
                HTML;
            }
        })
            ->assertConsoleLogMissingErrors();
    }

    public function test_assert_console_log_has_error_passes()
    {
        Livewire::visit(new class extends Component
        {
            public function render()
            {
                return <<< 'HTML'
                <div x-init="console.error('test')">
                </div>
                HTML;
            }
        })
            ->assertConsoleLogHasError('test');
    }

    public function test_assert_console_log_has_error_fails()
    {
        $this->expectException(AssertionFailedError::class);

        Livewire::visit(new class extends Component
        {
            public function render()
            {
                return <<< 'HTML'
                <div x-init="console.error('other')">
                </div>
                HTML;
            }
        })
            ->assertConsoleLogHasError('test');
    }

    public function test_assert_console_log_missing_error_passes()
    {
        Livewire::visit(new class extends Component
        {
            public function render()
            {
                return <<< 'HTML'
                <div x-init="console.error('other')">
                </div>
                HTML;
            }
        })
            ->assertConsoleLogMissingError('test');
    }

    public function test_assert_console_log_missing_error_fails()
    {
        $this->expectException(AssertionFailedError::class);

        Livewire::visit(new class extends Component
        {
            public function render()
            {
                return <<< 'HTML'
                <div x-init="console.error('test')">
                </div>
                HTML;
            }
        })
            ->assertConsoleLogMissingError('test');
    }
}
