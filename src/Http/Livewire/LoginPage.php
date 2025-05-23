<?php

namespace Autonic\Restuser\Http\Livewire;

use Livewire\Component;
use Livewire\Attributes\Validate;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Route;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Log;


class LoginPage extends Component
{

    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public $redirectAfterLogin;
    public function mount()
    {

        // check if user should view mobile or desktop version
        $agent = new Agent();

        if ($agent->isDesktop()) {
            $this->redirectAfterLogin = config('restuser.redirect_after_login_desktop');
        } elseif ($agent->isTablet()) {
            $this->redirectAfterLogin = config('restuser.redirect_after_login_tablet');
        } elseif ($agent->isMobile()) {
            $this->redirectAfterLogin = config('restuser.redirect_after_login_mobile');
        } else {
            $this->redirectAfterLogin = config('restuser.redirect_after_login_desktop');
        }

        // check if user is already logged in
        // if user is already logged in, redirect to the correct URL
        if (auth()->check()) {
            return redirect(config('app.url') . $this->redirectAfterLogin);
        }

    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public $loader = false;
    public function attemptLogin()
    {

        // prevent too many login attempts
        $this->ensureIsNotRateLimited();

        // activate loading animation
        $this->loader = true;

        debug_logging('');
        debug_logging('requesting API-token for user ' . $this->email);

        // preform login to external API
        $authResponse = auth()->authenticate([
            'email' => $this->email,
            'password' => $this->password,
        ]);

        if ($authResponse->successful()) {

            debug_logging('recieved 200 response from identity server');

            // clear the rate limiter for the current user
            RateLimiter::clear($this->throttleKey());

            if (auth()->check()) {

                debug_logging('recieved valid API-token from identity server');

                // check if customer settings are set for the customer
                if (!\App\Models\CustomerSetting::checkDefaultSettings(auth()->customerId())) {
                    $msg = 'Grundinställningarna för ditt företag saknas, kontakta supporten';
                    error_logging($msg);
                    $this->loader = false;
                    auth()->logout();
                    throw ValidationException::withMessages(['email' => $msg]);
                }

                // set selected token (we default to first token - user can change it later)
                // (this also synchronizes the user data via api)
                auth()->setSelectedToken(0);

                debug_logging('selected default API-token, redirecting user to: ' . $this->redirectAfterLogin);

                // redirect user to the correct URL
                return redirect(config('app.url') . $this->redirectAfterLogin);

            } else {

                error_logging('could not validate token / auth()->check() failed - loging out user ' . $this->email);

                auth()->logout();

                return redirect(config('app.url') . '/login');

            }

        } else {

            $this->loader = false;

            $response = $authResponse->json();

            error_logging('could not authenticate user ' . $this->email . ' - reason unknown');

            throw ValidationException::withMessages(['email' => isset($response['message']) ? $response['message'] : 'Inloggning misslyckades, okänd anledning']);

        }

        debug_logging('end of authentication method');
        debug_logging('');

    }

    public function render()
    {
        return view('restuser::livewire.pages.auth.login')->layout('layouts.guest');
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'form.email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }


}
