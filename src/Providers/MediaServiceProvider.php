<?php

namespace Agenciafmd\Media\Providers;

use Illuminate\Support\ServiceProvider;

class MediaServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->providers();

        $this->setMenu();

        $this->loadMigrations();

        $this->publish();
    }

    public function register()
    {
        $this->loadConfigs();
    }

    protected function providers()
    {
        $this->app->register(BladeServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);
    }

    protected function setMenu()
    {
//        criar a listagem de todas as medias
//        $this->app->make('admix-menu')
//            ->push((object)[
//                'view' => 'agenciafmd/leads::partials.menus.item',
//                'ord' => config('admix-leads.sort', 1),
//            ]);
    }

    protected function loadMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function publish()
    {
        $this->publishes([
            __DIR__ . '/../config' => base_path('config'),
        ], 'admix-media:config');
    }

    protected function loadConfigs()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/admix-media.php', 'admix-media');

        config(['media-library.disk_name' => config('admix-media.disk_name')]);
        config(['media-library.path_generator' => config('admix-media.path_generator')]);
    }
}
