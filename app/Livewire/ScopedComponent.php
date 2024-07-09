<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Str;

class ScopedComponent extends Component
{
    public $cssScope;

    public function mounted()
    {
        // initialize a scope at mount
        $this->cssScope = Str::random();
    }

    // public function render()
    // {
    //     return view('filament.pages.programs.show');
    // }
    public function render()
    {

        return view('livewire.scoped-component');
    }
}
