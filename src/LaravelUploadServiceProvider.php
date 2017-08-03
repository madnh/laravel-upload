<?php


namespace MaDnh\LaravelUpload;


use Illuminate\Support\ServiceProvider;
use MaDnh\LaravelUpload\Command\UploadCommand;

class LaravelUploadServiceProvider extends ServiceProvider
{
    public function register()
    {

    }

    public function boot()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config.php', 'upload');
        $this->publishes([__DIR__ . '/../config.php' => config_path('upload.php')], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                UploadCommand::class
            ]);
        }
    }
}