<?php

namespace Feraandrei1\FilamentDynamicSettings;

use Feraandrei1\FilamentDynamicSettings\Filament\Pages\GeneralSettings;
use Feraandrei1\FilamentDynamicSettings\Filament\Pages\HomePageSettings;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Assets\Js;
use Illuminate\Support\ServiceProvider;

class FilamentDynamicSettingsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'filament-dynamic-settings');

        $this->publishes([
            __DIR__ . '/../database/migrations/2022_12_14_083707_create_settings_table.php' => database_path('migrations/2022_12_14_083707_create_settings_table.php'),
        ], 'filament-dynamic-settings-migrations');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    public function register(): void
    {
        //
    }
}
