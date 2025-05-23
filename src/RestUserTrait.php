<?php

namespace Autonic\Restuser;

use Illuminate\Support\Facades\Log;

trait RestUserTrait
{

    public function hasAbility(string $ability): bool
    {
        return in_array($ability, auth()->getAbilities() ?? []);
    }

    public function listAbilities(): array
    {
        return auth()->getAbilities() ?? [];
    }

    public function can($abilities, $arguments = [])
    {

        // Retrieve granted abilities from the session
        $grantedAbilities = auth()->getAbilities();

        if (is_string($abilities)) {
            $abilities = [$abilities];
        }

        // Check if the requested ability exists in the granted abilities
        foreach ($abilities as $ability) {
            if (!in_array($ability, $grantedAbilities) && !in_array('do-everything', $grantedAbilities)) {
                return false;
            }
        }


        return true;

    }

    public static function synchronizeUsers(int $customerId)
    {

        if(!$customerId) throw new \Exception('Customer ID not passed');

        $url = config('app.identity_server_url') . '/api/list-customer-users';

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'serverAccessToken' => config('app.identity_server_token'),
            'microserviceUuid' => config('app.microservice_uuid'),
            'customerId' => $customerId,
        ])->post($url);

        // if response is not successful, return the response
        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Error while synchronizing users: ' . $response->body());
        }

        debug_logging('got successful response from identity server: ' . $url);

        // otherwise, try to decode the response
        $json = json_decode($response->body(), true);
        if (!isset($json['user_data']) || empty($json['user_data']) || !is_array($json['user_data']) || count($json['user_data']) < 1) {
            throw new \Exception('no user data found in response / badly formatted user data');
        }

        debug_logging('processing user data...');

        // ...if all ok, process all users in the response
        $i = 0;
        foreach($json['user_data'] as $userData) {
            $i++;
            $ids[] = $userData['id'];
            \App\Models\User::createOrUpdate(
                id: $userData['id'],
                name: $userData['name'],
                email: $userData['email'],
                customer_id: $userData['customer_id'],
                roles: $userData['roles'],
                synchronized_at: now()
            );
        }

        debug_logging('processed ' . $i . ' users');

        // de-activate all users not in the response
        $n = 0;
        $missingUsers = self::query()->whereNotIn('id', $ids)->get();
        foreach($missingUsers as $user) {
            $n++;
            debug_logging('de-activating user: ' . $user->id);
            $user->is_active = false;
            $user->save();
        }

        debug_logging('de-activated ' . $n . ' users');

    }

    public static function createOrUpdate($id, $name, $email, $customer_id, $roles, $synchronized_at = null)
    {

        $user = self::where('id', $id)->first();

        if (!$user) {

            $user = new self();
            $user->created_at = now();
            $user->id = $id;
            $user->is_active = true;

        }

        $user->updated_at = now();
        $user->synchronized_at = $synchronized_at;
        $user->name = $name;
        $user->email = $email;
        $user->customer_id = $customer_id;
        $user->roles = $roles;

        return $user->save();

    }

}