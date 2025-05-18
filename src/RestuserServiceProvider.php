<?php

namespace Autonic\Restuser;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Route;
use Autonic\Restuser\Console\Commands\InstallRestuser;
use Livewire\Livewire;

class RestuserServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Bind the UserProvider to a concrete implementation
        $this->app->bind(UserProvider::class, function ($app) {
            return new EloquentUserProvider($app['hash'], \App\Models\User::class);
        });

        // Bind the UserGuard
        $this->app->singleton(UserGuard::class, function ($app) {
            return new UserGuard($app->make(UserProvider::class));
        });
    }

    public function boot()
    {

        // Register restuser driver
        $this->app['auth']->extend('restuser', function ($app, $name, array $config) {
            return $app->make(UserGuard::class);
        });
        
        // Register the view path
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'restuser');

        // Load package routes with the "web" middleware group
        Route::middleware('web')->group(__DIR__ . '/../routes/web.php');
        Route::middleware('web')->group(__DIR__ . '/../routes/api.php');
        Route::middleware('web')->group(__DIR__ . '/../routes/auth.php');

        // Load a specific route file
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/auth.php');

        // Register the install-restuser command
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallRestuser::class,
            ]);
        }

        // Load the helper file
        $helperPath = __DIR__ . '/Helpers/restuser-helper.php';
        if (file_exists($helperPath)) {
            require_once $helperPath;
        }

        Livewire::component('autonic.restuser.login-page', \Autonic\Restuser\Http\Livewire\LoginPage::class);
        Livewire::component('autonic.restuser.select-resource', \Autonic\Restuser\Http\Livewire\SelectResource::class);
        Livewire::component('autonic.restuser.base-component', \Autonic\Restuser\Http\Livewire\BaseComponent::class);

        // Dynamically register all Livewire components in app/Http/Livewire
        $livewirePath = app_path('Http/Livewire');
        if (is_dir($livewirePath)) {
            foreach (scandir($livewirePath) as $file) {
                if (is_file($livewirePath . '/' . $file) && str_ends_with($file, '.php')) {
                    $className = 'App\\Http\\Livewire\\' . pathinfo($file, PATHINFO_FILENAME);
                    $componentName = \Illuminate\Support\Str::kebab(pathinfo($file, PATHINFO_FILENAME));
                    Livewire::component($componentName, $className);
                }
            }
        }
    }

}