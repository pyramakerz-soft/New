<?php
// app/Exports/SkillsExport.php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SkillsExport implements FromArray, WithHeadings
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Student Name',
            'Skill Name',
            'Score',
            'Count of Games',
            'Average Score',
            'Current Level',
            'Date'
        ];
    }
}
