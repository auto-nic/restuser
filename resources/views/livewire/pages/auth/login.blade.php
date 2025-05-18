<?php

use Illuminate\Support\Facades\Route;

?>

<div>

    @if($loader)
    <div wire:target="attemptLogin" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 z-20">
        <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
        </svg>
    </div>
    @endif

    <form wire:submit.prevent="attemptLogin" class="space-y-6 @if($loader) opacity-30 @endif">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="'Mailadress'" />
            <x-text-input wire:model.live.debounce.500ms="email" id="email" class="block mt-1 w-full" type="email" name="email" required autofocus autocomplete="email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="'Lösenord'" />

            <x-text-input wire:model.live.debounce.500ms="password" id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div>
            
        </div>

        <div class="flex items-center justify-end">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('password.request') }}" wire:navigate>
                    Har du glömt ditt lösenord?
                </a>
            @endif

            <x-primary-button class="ms-3">
                Logga in
            </x-primary-button>
        </div>
    </form>

    @push('scripts')
    <script>
        window.onload = () => {
            const emailInput = document.querySelector('input[name="email"]');
            alert(emailInput ? emailInput.value : 'Email input not found');
        };
    </script>
    @endpush

</div>
