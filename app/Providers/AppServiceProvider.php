<?php

namespace App\Providers;

use BezhanSalleh\PanelSwitch\PanelSwitch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        PanelSwitch::configureUsing(function (PanelSwitch $panelSwitch) {
            $panelSwitch->modalHeading('Available Panels');
            $panelSwitch->modalWidth('sm');
            $panelSwitch->slideOver();
            $panelSwitch->simple();
            
            // $panelSwitch
            // ->labels([
            // 'admin' => 'Custom Admin Label',
            // 'general_manager' => __('General Manager')
            //     ]);

                // $panelSwitch
            // ->canSwitchPanels(fn (): bool => auth()->user()->role == 'Admin');
        });
        Model::unguard();
    }
}
