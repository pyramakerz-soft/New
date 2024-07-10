<?php 
namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;
enum TestType: string implements HasLabel
{
    case Test = "0";
    case Quiz = "1";
    case Homework = "2";
    public function getLabel(): string{
        return $this->name;

        
        
    }
}