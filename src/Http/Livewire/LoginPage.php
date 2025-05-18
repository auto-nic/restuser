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

    #[Validate('boolean')]
    public bool $remember = false;

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

        // this calls a function that binds auto-filled values to the input fields
        // this is a workaround for cases when livewire is not able to bind the values
        // to the input fields, for example when using autofill in the browser
        // the script is added in login.blade.php
        $this->dispatch('check-autofill');

        // activate loading animation
        $this->loader = true;

        sleep(1); // Sleep for 0.2 seconds (200,000 microseconds)

        // verify that both email and password are set
        if (empty($this->email) || empty($this->password)) {
            $this->loader = false;
            throw ValidationException::withMessages(['email' => 'E-post och lösenord måste anges']);
        }
        
        // preform login to external API
        $authResponse = auth()->authenticate([
            'email' => $this->email,
            'password' => $this->password,
        ]);

        if ($authResponse->successful()) {

            // clear the rate limiter for the current user
            RateLimiter::clear($this->throttleKey());

            if (auth()->check()) {

                // check if customer settings are set for the customer
                if (!\App\Models\CustomerSetting::checkDefaultSettings(auth()->customerId())) {
                    throw ValidationException::withMessages(['email' => 'Grundinställningarna för ditt företag saknas, kontakta supporten']);
                }

                // check if user should view mobile or desktop version
                $agent = new Agent();

                if ($agent->isDesktop()) {
                    $redirectUrl = config('restuser.redirect_after_login_desktop');
                } elseif ($agent->isTablet()) {
                    $redirectUrl = config('restuser.redirect_after_login_tablet');
                } elseif ($agent->isMobile()) {
                    $redirectUrl = config('restuser.redirect_after_login_mobile');
                } else {
                    $redirectUrl = config('restuser.redirect_after_login_desktop');
                }

                // set selected token (we default to first token - user can change it later)
                session()->put('selected_token', 0);

                // fetch user data from API and store in local database
                \App\Models\User::synchronizeUsers();

                // redirect user to the correct URL
                return redirect(config('app.url') . $redirectUrl);

            } else {

                auth()->logout();
                session()->invalidate();
                session()->regenerateToken();
                return redirect(config('app.url') . '/login');

            }

        } else {

            $this->loader = false;

            $response = $authResponse->json();
            Log::error('Login failed for user "' . $this->email . '" with password: ' . $this->password . ' - ' . $authResponse->body());
            throw ValidationException::withMessages(['email' => isset($response['message']) ? $response['message'] : 'Inloggning misslyckades, okänd anledning']);

        }

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
