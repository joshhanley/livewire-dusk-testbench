<?php

namespace LivewireDuskTestbench\Tests\Browser;

use Livewire\Component;
use Livewire\Livewire;
use PHPUnit\Framework\AssertionFailedError;

class DuskBrowserMixinsTest extends TestCase
{
    /** @test */
    public function assert_see_in_order_macro_passes()
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

    /** @test */
    public function assert_see_in_order_macro_fails()
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

    /** @test */
    public function assert_is_visibile_in_container_passes()
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

    /** @test */
    public function assert_is_visibile_in_container_fails()
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

    /** @test */
    public function assert_is_not_visibile_in_container_passes()
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

    /** @test */
    public function assert_is_not_visibile_in_container_fails()
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

    /** @test */
    public function assert_has_classes_passes()
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

    /** @test */
    public function assert_has_classes_fails()
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

    /** @test */
    public function assert_has_only_classes_passes()
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

    /** @test */
    public function assert_has_only_classes_fails()
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

    /** @test */
    public function assert_missing_classes_passes()
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

    /** @test */
    public function assert_missing_classes_fails()
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
}
