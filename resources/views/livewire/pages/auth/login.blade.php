<?php

use Illuminate\Support\Facades\Route;

?>

<div>
    <form wire:submit.prevent="attemptLogin" class="space-y-6">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="'Mailadress'" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="'Lösenord'" />

            <x-text-input wire:model="password" id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div>
            <label for="remember" class="inline-flex items-center">
                <input wire:model="remember" id="remember" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Kom ihåg mig</span>
            </label>
        </div>

        <div class="flex items-center justify-end">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('password.request') }}" wire:navigate>
                    Har du glömt ditt lösenord?
                </a>
            @endif

            <x-primary-button class="ms-3" wire:click="attemptLogin()">
                Logga in
            </x-primary-button>
        </div>
    </form>
</div>
