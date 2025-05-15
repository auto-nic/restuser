<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validate token interval
    |--------------------------------------------------------------------------
    |
    | This value is the number of seconds before a token must be validated
    |
    */

    'validate_token_interval' => 5,

    /*
    |--------------------------------------------------------------------------
    | Redirect after login
    |--------------------------------------------------------------------------
    |
    | This is the URL to redirect to after a successful login.
    |
    */

    'redirect_after_login_desktop' => '/overview-desktop',
    'redirect_after_login_mobile' => '/overview-mobile',
    'redirect_after_login_tablet' => '/overview-desktop',

];
