<?php

namespace App\Livewire;

use Livewire\Component;

class QuestionComponent extends Component
{

    public $choices = [];
    public $answer = ''; // Initialize answer variable

    public function addChoice()
    {
        $this->choices[] = ['choice' => '']; // Add an empty choice

        // Update the answer textarea after adding a choice
        // For example, concatenate the choices into the answer
        $this->answer = implode("\n", array_column($this->choices, 'choice'));
    }


    public function render()
    {
        return view('livewire.question-component');
    }

}
