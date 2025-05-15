<?php

namespace Autonic\Restuser\Middleware;

use Closure;
use Illuminate\Http\Request;
use Autonic\Restuser\UserGuard;

class AuthenticateWithRestUser
{
    protected $guard;

    public function __construct(UserGuard $guard)
    {
        $this->guard = $guard;
    }

    public function handle(Request $request, Closure $next)
    {

        // Check if the user is authenticated
        if (!$this->guard->check()) {
            return redirect()->route('logout');
        }

        // Attach the authenticated user to the request
        $request->setUserResolver(function () {
            return $this->guard->user();
        });

        return $next($request);

    }

}
