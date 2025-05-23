After installing this package you need to add:

[bootstrap/app.php]:

        $middleware->alias([
            'auth' => \Autonic\Restuser\Middleware\AuthenticateWithRestUser::class,
        ]);



[config/app.php]:

    'providers' => [

        Autonic\Restuser\RestuserServiceProvider::class,

    ]



[app/models/User.php]:

    use \Autonic\Restuser\RestUserTrait;




[app/models/CustomerSetting.php]:

    use \Autonic\Restuser\CustomerSettingTrait;




[config/auth.php]

    'defaults' => [
        'guard' => 'restuser', // Set restuser as default
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session', // Use the guard from the package
            'provider' => 'users',
        ],
        'restuser' => [
            'driver' => 'restuser',
            'provider' => 'restuser',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', App\Models\User::class),
        ],

        'restuser' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

    ],