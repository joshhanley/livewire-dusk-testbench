<?php

namespace LivewireDuskTestbench\Tests\Browser;

use Livewire\Component as LivewireComponent;

class Component extends LivewireComponent
{
    public $title = 'Sample Component';

    public function changeTitle()
    {
        $this->title = 'Changed Component';
    }

    public function render()
    {
        return
<<< 'HTML'
<div>
    <h1 dusk="title">{{ $title }}</h1>
    <button dusk="change-title" wire:click="changeTitle">Change</button>
</div>
HTML;
    }
}
