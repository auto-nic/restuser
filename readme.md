After installing this package you need to add:

[bootstrap/app.php]:

        $middleware->alias([
            'auth' => \Autonic\Restuser\Middleware\AuthenticateWithRestUser::class,
        ]);



[Providers/AuthServiceProvider.php]:

        Auth::extend('external_user_auth', function ($app, $name, array $config) {
            return new \Autonic\Restuser\UserGuard(Auth::createUserProvider($config['provider']));
        });



[config/app.php]:

    'providers' => [

        Autonic\Restuser\RestuserServiceProvider::class,

    ]



[config/auth.php]

    'guards' => [
        'web' => [
            'driver' => 'external_user_auth',
            'provider' => 'users',
        ],
    ],