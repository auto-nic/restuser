<?php

namespace Autonic\Restuser\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{

    public function createDefaultSettings(\Illuminate\Http\Request $request)
    {

        $this->ensureIsNotRateLimited();

        debug_logging('');

        debug_logging('rate limit ok');

        RateLimiter::hit($this->throttleKey());

        // check if the APP_UUID environment variable is set
        $uuid = config('app.microservice_uuid');
        if (empty($uuid)) {
            $msg = 'The APP_UUID environment variable is not set';
            error_logging($msg);
            return response()->json([
                'error' => $msg
            ], 400);
        };

        // check if the table customer_settings exists
        if (!DB::getSchemaBuilder()->hasTable('customer_settings')) {
            $msg = 'The customer_settings table does not exist';
            error_logging($msg);
            return response()->json([
                'error' => $msg
            ], 400);
        }

        // check if CustomerSetting model exists
        if (!class_exists('App\Models\CustomerSetting')) {
            $msg = 'The CustomerSetting model does not exist';
            error_logging($msg);
            return response()->json([
                'error' => $msg
            ], 400);
        }

        // check if CustomerSetting method exists
        if (!method_exists('App\Models\CustomerSetting', 'createDefaultSettings')) {
            $msg = 'The createDefaultSettings method does not exist in the CustomerSetting model';
            error_logging($msg);
            return response()->json([
                'error' => $msg
            ], 400);
        }

        // check if CustomerSetting method exists
        if (!method_exists('App\Models\CustomerSetting', 'checkDefaultSettings')) {
            $msg = 'The checkDefaultSettings method does not exist in the CustomerSetting model';
            error_logging($msg);
            return response()->json([
                'error' => $msg
            ], 400);
        }

        $microserviceUuid = $request->header('microserviceUuid');
        $customerId = $request->header('customerId');

        // verify that the microservice_uuid and customer_id are not empty
        if (empty($microserviceUuid) || empty($customerId)) {
            $msg = 'provided microserviceUuid or customerId is missing';
            error_logging($msg);
            return response()->json([
                'error' => $msg
            ], 403);
        };

        debug_logging('microserviceUuid "' . $microserviceUuid . '" and customerId "' . $customerId . '" are set in headers');

        // check if the microservice_uuid matches the APP_UUID
        if ($microserviceUuid !== $uuid) {
            $msg = 'provided microserviceUuid does not match APP_UUID';
            error_logging($msg);
            return response()->json([
                'error' => $msg
            ], 403);
        };

        debug_logging('microserviceUuid "' . $microserviceUuid . '" matches APP_UUID in .env');

        // all is ok, attempt to create the default settings
        try {

            \App\Models\CustomerSetting::createDefaultSettings($customerId);

        } catch (\Exception $e) {
            $msg = 'Error creating default settings: ' . $e->getMessage();
            error_logging($msg);
            return response()->json([
                'error' => $msg
            ], 500);
        }

        debug_logging('created / verified existence of customer settings for customerId "' . $customerId . '"');

        // clear the rate limiter for the current user
        RateLimiter::clear($this->throttleKey());

        debug_logging('');

        // return success response
        return response()->json([
            '',
        ], 200);

    }

    public function checkDefaultSettings(\Illuminate\Http\Request $request)
    {

        $this->ensureIsNotRateLimited();

        RateLimiter::hit($this->throttleKey());

        // check if the APP_UUID environment variable is set
        $uuid = env('APP_UUID');
        if (empty($uuid)) {
            $msg = 'The APP_UUID environment variable is not set';
            error_logging($msg);
            return response()->json([
                'error' => $msg
            ], 400);
        };

        // check if the table customer_settings exists
        if (!DB::getSchemaBuilder()->hasTable('customer_settings')) {
            $msg = 'The customer_settings table does not exist';
            error_logging($msg);
            return response()->json([
                'error' => $msg
            ], 400);
        }

        // check if CustomerSetting model exists
        if (!class_exists('App\Models\CustomerSetting')) {
            $msg = 'The CustomerSetting model does not exist';
            error_logging($msg);
            return response()->json([
                'error' => $msg
            ], 400);
        }

        // check if CustomerSetting method exists
        if (!method_exists('App\Models\CustomerSetting', 'createDefaultSettings')) {
            $msg = 'The createDefaultSettings method does not exist in the CustomerSetting model';
            error_logging($msg);
            return response()->json([
                'error' => $msg
            ], 400);
        }

        // check if CustomerSetting method exists
        if (!method_exists('App\Models\CustomerSetting', 'checkDefaultSettings')) {
            $msg = 'The checkDefaultSettings method does not exist in the CustomerSetting model';
            error_logging($msg);
            return response()->json([
                'error' => $msg
            ], 400);
        }

        $microserviceUuid = $request->header('microservice_uuid');
        $customerId = $request->header('customerId');

        // verify that the microservice_uuid and customer_id are not empty
        if (empty($microserviceUuid) || empty($customerId)) {
            $msg = 'provided microserviceUuid or customerId is missing';
            error_logging($msg);
            return response()->json([
                'error' => $msg
            ], 403);
        };

        // check if the microservice_uuid matches the APP_UUID
        if ($microserviceUuid !== $uuid) {
            $msg = 'provided microserviceUuid does not match APP_UUID';
            error_logging($msg);
            return response()->json([
                'error' => $msg
            ], 403);
        };

        // all is ok, check if default settings exist
        if (\App\Models\CustomerSetting::checkDefaultSettings($customerId)) {

            // clear the rate limiter for the current user
            RateLimiter::clear($this->throttleKey());

            // return success response
            $msg = 'Default settings found';
            debug_logging($msg);
            return response()->json([
                $msg,
            ], 200);

        } else {

            // clear the rate limiter for the current user
            RateLimiter::clear($this->throttleKey());

            // return error response
            $msg = 'Default settings do not exist';
            error_logging($msg);
            return response()->json([
                'error' => $msg
            ], 400);

        }

    }

    /**
     * Trigger user synchronization.
     * 
     *   This quirky method exists so that the main server (identity server) can trigger
     *   a user synchronization request from a microservice server.
     *   This is needed for example when a user is added / removed on the main server.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function triggerUserSynchronization(\Illuminate\Http\Request $request)
    {

        debug_logging('');
        debug_logging('');

        $this->ensureIsNotRateLimited();

        debug_logging('rate limit ok');

        RateLimiter::hit($this->throttleKey());


        // check if the APP_UUID environment variable is set
        $uuid = config('app.microservice_uuid');
        if (empty($uuid)) {
            $msg = 'The APP_UUID environment variable is not set.';
            error_logging($msg);
            return response()->json([
                'error' => $msg
            ], 400);
        };

        debug_logging('APP_UUID is set in .env');

        // check if the IDENTITY_SERVER_TOKEN environment variable is set
        $serverToken = config('app.identity_server_token');
        if (empty($serverToken)) {
            $msg = 'The IDENTITY_SERVER_TOKEN environment variable is not set.';
            error_logging($msg);
            return response()->json([
                'error' => $msg
            ], 400);
        };

        debug_logging('IDENTITY_SERVER_TOKEN is set in .env');

        $serverAccessToken = $request->header('serverAccessToken');
        $microserviceUuid = $request->header('microserviceUuid');
        $customerId = $request->header('customerId');

        // verify that the microservice_uuid and customer_id are not empty
        if (empty($microserviceUuid) || empty($customerId)) {
            $msg = 'microserviceUuid or customerId is missing';
            error_logging($msg);
            return response()->json([
                $msg
            ], 403);
        };

        debug_logging('microserviceUuid and customerId are set in headers');

        // check if the microservice_uuid matches the APP_UUID
        if ($microserviceUuid !== $uuid) {
            $msg = 'microserviceUuid does not match';
            error_logging($msg);
            return response()->json([
                $msg
            ], 403);
        };

        debug_logging('microserviceUuid sent in headers matches APP_UUID in .env');

        // check if the serverAccessToken matches the IDENTITY_SERVER_TOKEN
        if ($serverAccessToken !== $serverToken) {
            $msg = 'serverAccessToken does not match';
            error_logging($msg);
            return response()->json([
                $msg
            ], 403);
        };

        debug_logging('serverAccessToken sent in headers matches IDENTITY_SERVER_TOKEN in .env');
        debug_logging('all ok - now dispatching job to synchronize users');

        // fetch user data from API and store in local database
        \Autonic\Restuser\Jobs\SynchronizeUserData::dispatch($customerId);

        // return success response
        return response()->json([
            '',
        ], 200);

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
            'throttled' => trans('auth.throttle', [
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
        return Str::transliterate(request()->ip());
    }

}