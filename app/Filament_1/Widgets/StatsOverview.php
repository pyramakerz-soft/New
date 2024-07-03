<?php

namespace App\Filament\Widgets;

use App\Models\Game;
use App\Models\Program;
use App\Models\Unit;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
     protected static ?int $sort = 3;
    protected function getStats(): array
    {
        
        return [
            Stat::make('Phonics : Units', Unit::query()->join('programs','units.program_id','programs.id')->join('courses','programs.course_id','courses.id')->where('courses.name','Phonics')->count())->chart([7, 2, 10, 3, 15, 4, 17])
            ->color('success')
            ->description('Phonics Unit Count')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->extraAttributes([
                'class' => 'cursor-pointer',
                'wire:click' => "\$dispatch('setStatusFilter', { filter: 'processed' })",
            ]),
            Stat::make('Phonics : Games', Game::query()->join('lessons','games.lesson_id','lessons.id')->join('units','lessons.unit_id','units.id')->join('programs','units.program_id','programs.id')->join('courses','programs.course_id','courses.id')->where('courses.name','Phonics')->count())->chart([7, 2, 10, 3, 15, 4, 17])
            ->color('success')
            ->description('Phonics Unit Count')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->extraAttributes([
                'class' => 'cursor-pointer',
                'wire:click' => "\$dispatch('setStatusFilter', { filter: 'processed' })",
            ]),
            
            
            Stat::make('Culture : Units', Unit::query()->join('programs','units.program_id','programs.id')->join('courses','programs.course_id','courses.id')->where('courses.name','Culture')->count())->chart([17, 4, 15, 3, 10, 2, 7])
            ->color('danger')
            ->description('Culture Unit Count')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->extraAttributes([
                'class' => 'cursor-pointer',
                'wire:click' => "\$dispatch('setStatusFilter', { filter: 'processed' })",
            ]),
            
            
            Stat::make('Culture : Games', Game::query()->join('lessons','games.lesson_id','lessons.id')->join('units','lessons.unit_id','units.id')->join('programs','units.program_id','programs.id')->join('courses','programs.course_id','courses.id')->where('courses.name','Culture')->count())->chart([17, 4, 15, 3, 10, 2, 7])
            ->color('danger')
            ->description('Culture Unit Count')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->extraAttributes([
                'class' => 'cursor-pointer',
                'wire:click' => "\$dispatch('setStatusFilter', { filter: 'processed' })",
            ]),
            
            
            
            
            Stat::make('Math : Units', Unit::query()->join('programs','units.program_id','programs.id')->join('courses','programs.course_id','courses.id')->where('courses.name','Math')->count())->chart([7, 2, 10, 3, 15, 4, 17])
            ->color('primary')
            ->description('Math Unit Count')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->extraAttributes([
                'class' => 'cursor-pointer',
                'wire:click' => "\$dispatch('setStatusFilter', { filter: 'processed' })",
            ]),
            Stat::make('Math : Games', Game::query()->join('lessons','games.lesson_id','lessons.id')->join('units','lessons.unit_id','units.id')->join('programs','units.program_id','programs.id')->join('courses','programs.course_id','courses.id')->where('courses.name','Math')->count())->chart([7, 2, 10, 3, 15, 4, 17])
            ->color('primary')
            ->description('Math Unit Count')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->extraAttributes([
                'class' => 'cursor-pointer',
                'wire:click' => "\$dispatch('setStatusFilter', { filter: 'processed' })",
            ]),
            
            
            Stat::make('Practical Life : Units', Unit::query()->join('programs','units.program_id','programs.id')->join('courses','programs.course_id','courses.id')->where('courses.name','Practical Life')->count())->chart([7, 2, 10, 3, 15, 4, 17])
            ->color('primary')
            ->description('Practical Life Unit Count')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->extraAttributes([
                'class' => 'cursor-pointer',
                'wire:click' => "\$dispatch('setStatusFilter', { filter: 'processed' })",
            ]),
            
            Stat::make('Practical Life : Games', Game::query()->join('lessons','games.lesson_id','lessons.id')->join('units','lessons.unit_id','units.id')->join('programs','units.program_id','programs.id')->join('courses','programs.course_id','courses.id')->where('courses.name','Practical Life')->count())->chart([7, 2, 10, 3, 15, 4, 17])
            ->color('primary')
            ->description('Practical Life Unit Count')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->extraAttributes([
                'class' => 'cursor-pointer',
                'wire:click' => "\$dispatch('setStatusFilter', { filter: 'processed' })",
            ]),
            
            
            
            Stat::make('Arabic : Units', Unit::query()->join('programs','units.program_id','programs.id')->join('courses','programs.course_id','courses.id')->where('courses.name','Arabic')->count())->chart([17, 2, 10, 3, 15, 4, 17])
            ->color('primary')
            ->description('Arabic Unit Count')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->extraAttributes([
                'class' => 'cursor-pointer',
                'wire:click' => "\$dispatch('setStatusFilter', { filter: 'processed' })",
            ]),

            
            Stat::make('Arabic : Games', Game::query()->join('lessons','games.lesson_id','lessons.id')->join('units','lessons.unit_id','units.id')->join('programs','units.program_id','programs.id')->join('courses','programs.course_id','courses.id')->where('courses.name','Arabic')->count())->chart([17, 2, 10, 3, 15, 4, 17])
            ->color('primary')
            ->description('Arabic Unit Games')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->extraAttributes([
                'class' => 'cursor-pointer',
                'wire:click' => "\$dispatch('setStatusFilter', { filter: 'processed' })",
            ]),
        ];
    }
}
