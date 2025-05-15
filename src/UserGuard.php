<?php

namespace Autonic\Restuser;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Log;

class UserGuard implements Guard
{
    protected $user;
    protected $provider;
    protected $token;

    public function __construct(UserProvider $provider)
    {

        $this->provider = $provider;

    }

    public function authenticate($credentials = [])
    {

        $email = $credentials['email'] ?? null;
        $password = $credentials['password'] ?? null;

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'email' => $email,
            'password' => $password,
            'microserviceUuid' => config('app.microservice_uuid'),
        ])->post(config('app.identity_server_url') . '/api/request-token');

        // if response is not successful, return the response
        if ($response->getStatusCode() !== 200) {
            return $response;
        }

        // otherwise, store the response data in the session
        $responseData = $response->json();
        session(['response' => $responseData]);

        // ...and then return the response
        return $response;

    }

    public function check()
    {

        // Check if the user is authenticated (has user array in session)
        $user = session('response.user');
        if (empty($user)) return false;

        // check if tokens has to be validated
        if (session('response.user.validate_tokens_at') < now()->format('Y-m-d H:i:s')) {
            if (!$this->validateTokens()) {
                return false;
            }
        }

        // check if a token can be fetched
        if (!$this->token()) {
            return false;
        }

        return true;

    }

    protected $tokens;
    public function collectTokensFromSession()
    {

        // reset the tokens array
        $this->tokens = collect();

        // get tokens from session data
        $tokens = session('response.api_tokens');

        // do nothing if no tokens are found
        if (empty($tokens) || !is_array($tokens)) {
            return false;
        }

        // loop through the tokens and create a new MicroserviceAccessToken object for each token
        foreach($tokens as $key => $token) {
            $this->tokens->add(new \Autonic\Restuser\MicroserviceAccessToken($token));
        }

        // auto-set selected_token if only one token is found
        if ($this->tokens->count() === 1) {
            session(['selected_token' => 0]);
        }

    }

    public function validateTokens()
    {

        $this->collectTokensFromSession();

        foreach($this->tokens as $key => $token) {

            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'token' => $token->token,
                'userId' => $token->tokenable_id,
            ])->post(config('app.identity_server_url') . '/api/validate-token');

            if ($response->successful()) {

                // if the token is valid, we do nothing

            } else {

                // TODO: have a look at this later to handle failure of individual tokens
                // if any token is not valid, logout the user
                $this->logout();

            }

        }

        // set the validate_tokens_at to X seconds for all future requests
        session(['response.user.validate_tokens_at' => now()->addSeconds(
            config('restuser.validate_token_interval')
        )->format('Y-m-d H:i:s')]);

        return true;

    }

    public function guest()
    {
        return !$this->check();
    }

    public function user()
    {

        // collect tokens
        $this->collectTokensFromSession();

        if ($this->user) {
            return $this->user;
        }

        if ($this->check()) {

            $userData = session('response.user');
            $this->user = new \App\Models\User();
            $userData = session('response.user');
            foreach ($userData as $key => $value) {
                $this->user->$key = $value;
            }

            $this->user->selected_customer_id = $this->customerId();

        }

        return $this->user;

    }

    public function setSelectedToken($key)
    {
        session(['selected_token' => $key]);
        $this->token = $this->tokens()[$key];
    }

    public function token()
    {

        $this->collectTokensFromSession();

        // if no token is selected, redirect to select-resource
        /*
        if (session('selected_token') === null) {
            return redirect('/select-resource');
        }

        // if no token exists, logout user
        if ($this->tokens()->count() === 0) {
            return redirect('/logout');
        }
        */

        // otherwise, return the selected token
        return isset($this->tokens[session('selected_token')]) ? $this->tokens[session('selected_token')] : null;

    }

    public function getAbilities()
    {

        $this->collectTokensFromSession();

        if ($token = $this->token()) {
            return $token->abilities;
        }

        return [];

    }

    public function tokens()
    {

        $this->collectTokensFromSession();

        return $this->tokens ? $this->tokens : collect();
    }

    public function customerName()
    {

        $this->collectTokensFromSession();

        // otherwise, return the selected token
        return session('selected_token') !== null ? $this->tokens[session('selected_token')]->customer_name : null;
    }

    public function entityName()
    {

        $this->collectTokensFromSession();

        // otherwise, return the selected token
        return session('selected_token') !== null ? $this->tokens[session('selected_token')]->entity_name : null;
    }

    public function customerId()
    {

        $this->collectTokensFromSession();

        // otherwise, return the selected token
        return session('selected_token') !== null ? $this->tokens[session('selected_token')]->customer_id : null;
    }

    public function microserviceName()
    {

        $this->collectTokensFromSession();

        // otherwise, return the selected token
        return session('selected_token') !== null ? $this->tokens[session('selected_token')]->microservice_label : null;
    }

    public function clientTitle()
    {
        if ($this->customerName() == $this->entityName()) {
            return $this->customerName();
        }
        return $this->customerName() . ' (' . $this->entityName() . ')';
    }

    public function microserviceVersion()
    {

        $this->collectTokensFromSession();

        // otherwise, return the selected token
        return session('selected_token') !== null ? $this->tokens[session('selected_token')]->microservice_version : null;
    }

    public function id()
    {
        return $this->user() && $this->user()->id ? $this->user()->id : null;
    }

    public function validate(array $credentials = [])
    {
        abort(500, 'validate() is not implemented');
        return false;
    }

    public function setUser($user)
    {
        abort(500, 'setUser() is not implemented');
        $this->user = $user;
        return $this;
    }

    public function hasUser()
    {
        return !is_null($this->user);
    }

    public function logout()
    {
        // Clear the session data related to the user
        session()->invalidate();
        session()->regenerateToken();
        $this->user = null;
    }

}
