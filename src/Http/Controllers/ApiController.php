<?php

namespace Autonic\Restuser\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{

    public function createDefaultSettings(\Illuminate\Http\Request $request)
    {

        $this->ensureIsNotRateLimited();

        RateLimiter::hit($this->throttleKey());

        // check if the APP_UUID environment variable is set
        $uuid = env('APP_UUID');
        if (empty($uuid)) {
            return response()->json([
                'error' => 'The APP_UUID environment variable is not set.'
            ], 400);
        };

        // check if the table customer_settings exists
        if (!DB::getSchemaBuilder()->hasTable('customer_settings')) {
            return response()->json([
                'error' => 'The customer_settings table does not exist.'
            ], 400);
        }

        // check if CustomerSetting model exists
        if (!class_exists('App\Models\CustomerSetting')) {
            return response()->json([
                'error' => 'The CustomerSetting model does not exist.'
            ], 400);
        }

        // check if CustomerSetting method exists
        if (!method_exists('App\Models\CustomerSetting', 'createDefaultSettings')) {
            return response()->json([
                'error' => 'The createDefaultSettings method does not exist in the CustomerSetting model.'
            ], 400);
        }

        // check if CustomerSetting method exists
        if (!method_exists('App\Models\CustomerSetting', 'checkDefaultSettings')) {
            return response()->json([
                'error' => 'The checkDefaultSettings method does not exist in the CustomerSetting model.'
            ], 400);
        }

        $microserviceUuid = $request->header('microserviceUuid');
        $customerId = $request->header('customerId');

        // verify that the microservice_uuid and customer_id are not empty
        if (empty($microserviceUuid) || empty($customerId)) {
            return response()->json([
                'microservice "'.$microserviceUuid.'" or customer "'.$customerId.'" empty'
            ], 403);
        };

        // check if the microservice_uuid matches the APP_UUID
        if ($microserviceUuid !== $uuid) {
            return response()->json([
                'posted uuid ' . $microserviceUuid . ' not equal to uuid on server: ' . $uuid
            ], 403);
        };

        // all is ok, attempt to create the default settings
        \App\Models\CustomerSetting::createDefaultSettings($customerId);

        // clear the rate limiter for the current user
        RateLimiter::clear($this->throttleKey());

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
            return response()->json([
                'error' => 'The APP_UUID environment variable is not set.'
            ], 400);
        };

        // check if the table customer_settings exists
        if (!DB::getSchemaBuilder()->hasTable('customer_settings')) {
            return response()->json([
                'error' => 'The customer_settings table does not exist.'
            ], 400);
        }

        // check if CustomerSetting model exists
        if (!class_exists('App\Models\CustomerSetting')) {
            return response()->json([
                'error' => 'The CustomerSetting model does not exist.'
            ], 400);
        }

        // check if CustomerSetting method exists
        if (!method_exists('App\Models\CustomerSetting', 'createDefaultSettings')) {
            return response()->json([
                'error' => 'The createDefaultSettings method does not exist in the CustomerSetting model.'
            ], 400);
        }

        // check if CustomerSetting method exists
        if (!method_exists('App\Models\CustomerSetting', 'checkDefaultSettings')) {
            return response()->json([
                'error' => 'The checkDefaultSettings method does not exist in the CustomerSetting model.'
            ], 400);
        }

        $microserviceUuid = $request->header('microservice_uuid');
        $customerId = $request->header('customer_id');

        // verify that the microservice_uuid and customer_id are not empty
        if (empty($microserviceUuid) || empty($customerId)) {
            return response()->json([
                'microservice "'.$microserviceUuid.'" or customer "'.$customerId.'" empty'
            ], 403);
        };

        // check if the microservice_uuid matches the APP_UUID
        if ($microserviceUuid !== $uuid) {
            return response()->json([
                'posted uuid ' . $microserviceUuid . ' not equal to uuid on server: ' . $uuid
            ], 403);
        };

        // all is ok, check if default settings exist
        if (\App\Models\CustomerSetting::checkDefaultSettings($customerId)) {

            // clear the rate limiter for the current user
            RateLimiter::clear($this->throttleKey());

            // return success response
            return response()->json([
                '',
            ], 200);

        } else {

            // clear the rate limiter for the current user
            RateLimiter::clear($this->throttleKey());

            // return error response
            return response()->json([
                'error' => 'Default settings do not exist.'
            ], 400);

        }

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