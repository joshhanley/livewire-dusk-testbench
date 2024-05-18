<?php

namespace LivewireDuskTestbench\Tests\Browser;

use Livewire\Component;
use Livewire\Livewire;

class DuskBrowserMixinsTest extends TestCase
{
    /** @test */
    public function assert_see_in_order_macro_works()
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
    public function assert_is_visibile_in_container_works()
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
    public function assert_is_not_visibile_in_container_works()
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
}
