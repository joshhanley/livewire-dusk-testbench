<?php

namespace LivewireDuskTestbench\Tests\Browser;

use Livewire\Livewire;

class ComponentTest extends TestCase
{
    /** @test */
    public function component_loads_correctly()
    {
        Livewire::visit(Component::class)
            ->assertSeeIn('@title', 'Sample Component')
        ;
    }

    /** @test */
    public function component_title_can_be_changed()
    {
        Livewire::visit(Component::class)
            ->assertSeeIn('@title', 'Sample Component')
            ->waitForLivewire()->click('@change-title')
            ->assertSeeIn('@title', 'Changed Component')
        ;
    }
}
