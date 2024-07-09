<?php 
namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\hasLabel;
use Filament\Support\Contracts\HasColor;
enum QuestionType: string implements HasLabel
{
    case Complete = '0';
    case Choices = '1';
    case TrueORFalse = '2';
    public function getLabel(): ?string{
        return $this->name;
    }
}