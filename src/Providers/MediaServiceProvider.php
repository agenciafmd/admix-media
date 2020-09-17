<?php

namespace Agenciafmd\Media\Providers;

use Collective\Html\FormFacade as Form;
use Illuminate\Support\ServiceProvider;

class MediaServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->providers();

        $this->setMenu();

        $this->loadViews();

        $this->loadMigrations();

        $this->loadTranslations();

        $this->loadComponents();

        $this->publish();
    }

    public function register()
    {
        $this->loadConfigs();
    }

    protected function providers()
    {
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

    protected function loadViews()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'agenciafmd/media');
    }

    protected function loadMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function loadTranslations()
    {
        $this->loadJsonTranslationsFrom(__DIR__ . '/../resources/lang');
    }

    protected function publish()
    {
        $this->publishes([
            __DIR__ . '/../resources/views' => base_path('resources/views/vendor/agenciafmd/media'),
        ], 'admix-media:views');

        $this->publishes([
            __DIR__ . '/../config' => base_path('config'),
        ], 'admix-media:config');
    }

    public function setLocalFactories()
    {
        $this->app->make('Illuminate\Database\Eloquent\Factory')
            ->load(__DIR__ . '/../database/factories');
    }

    /*
     * TODO: usar os componentes do blade
     * */
    protected function loadComponents()
    {
        Form::component('bsxImage', 'agenciafmd/media::components.image', [
            'label',
            'name',
            'value' => null,
            'attributes' => [],
            'helper' => null,
        ]);

        Form::component('bsxImages', 'agenciafmd/media::components.images', [
            'label',
            'name',
            'value' => null,
            'attributes' => [],
            'helper' => null,
        ]);

        Form::component('bsxFile', 'agenciafmd/media::components.file', [
            'label',
            'name',
            'value' => null,
            'attributes' => [],
            'helper' => null,
        ]);

        Form::component('bsxFiles', 'agenciafmd/media::components.files', [
            'label',
            'name',
            'value' => null,
            'attributes' => [],
            'helper' => null,
        ]);
    }

    protected function loadConfigs()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/admix-media.php', 'admix-media');

        config(['media-library.path_generator' => config('admix-media.path_generator')]);
    }
}
