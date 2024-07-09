<?php
namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\hasLabel;
use Filament\Support\Contracts\HasColor;

enum GameFlag: string implements HasLabel
{
    case OFF = '0';
    case ON = '1';
    public function getLabel(): ?string
    {
        return $this->name;



    }
}