<?php

namespace Habib\Master\Providers;

use Habib\Master\Console\InstallMasterConsole;
use Habib\Master\Console\MakeAllConsole;
use Habib\Master\Console\MakeRepositoryCommand;
use Habib\Master\Migrations\MigrationCreator;
use Illuminate\Support\ServiceProvider;

class MasterServiceProvider extends ServiceProvider
{
    /**
     * @var string[]
     */
    protected $commands=[
        InstallMasterConsole::class,
        MakeAllConsole::class,
        MakeRepositoryCommand::class,
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(MigrationCreator::class, function ($app) {
            return new MigrationCreator($app['files'], $app->basePath('stubs/base_stubs'));
        });

        foreach (config('master.repositories',[]) as $interface => $repository) {
            $this->app->bind(
                $interface,
                $repository
            );
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);
        }

        if (count($publishes = $this->publish())) {
            foreach ($publishes as $key => $publish) {
                $this->publishes($publish, $key);
            }
        }

    }

    /**
     * @return array
     */
    public function publish(): array
    {
        $separator = DIRECTORY_SEPARATOR;
        $path = dirname(dirname(__DIR__)).$separator;
        return [
            "config" => [
                $path.'config/master.php'=>config_path('master.php')
            ],
            "views"=>[
                $path.'resources/views'=>resource_path('views/vendor/master')
            ],
            "assets"=>[
                $path.'assets'=>public_path('vendor/master/'),
            ],
            "base_stubs"=>[
                $path.'stubs/base_stubs'=>base_path('stubs/base_stubs')
            ]
        ];
    }
}
