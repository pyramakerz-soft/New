<?php

namespace App\Livewire;

use App\Models\GameImage;
use Livewire\Component;

class ImageSelect extends Component
{
    public $images;

    public function mount()
    {
        $this->images = GameImage::all();
    }

    public function render()
    {
        return view('livewire.image-select', [
            'images' => $this->images,
        ]);
    }
}
