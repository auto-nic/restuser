<?php

namespace Autonic\Restuser;

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

    public static function synchronizeUsers()
    {

        $token = auth()->token();

        if(!$token) {
            throw new \Exception('Token not found in session');
        }

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'token' => $token->token,
            'user_id' => $token->tokenable_id,
        ])->post(config('app.identity_server_url') . '/api/synchronize-users');

        $json = json_decode($response->body(), true);

        if (!isset($json['user_data']) || empty($json['user_data']) || !is_array($json['user_data']) || count($json['user_data']) < 1) {
            throw new \Exception('no user data found in response / badly formatted user data');
        }

        // ...process all users in the response
        foreach($json['user_data'] as $userData) {
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

        // de-activate all users not in the response
        $missingUsers = self::query()->whereNotIn('id', $ids)->get();
        foreach($missingUsers as $user) {
            $user->is_active = false;
            $user->save();
        }

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