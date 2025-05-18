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
        try {
            console.log('Autofill script loaded');

            // Run immediately, with retries
            const trySyncInputs = (attempt = 1, maxAttempts = 3) => {
                setTimeout(() => {
                    console.log(`Attempt ${attempt} to sync autofilled inputs`);
                    const inputs = document.querySelectorAll('input[name="email"], input[name="password"]');
                    let synced = false;

                    inputs.forEach(input => {
                        if (input.value) {
                            ['input', 'change', 'input.wire.model'].forEach(eventType => {
                                const event = eventType === 'input.wire.model'
                                    ? new CustomEvent('input.wire.model', { bubbles: true, detail: { value: input.value } })
                                    : new Event(eventType, { bubbles: true });
                                input.dispatchEvent(event);
                                console.log(`Dispatched ${eventType} event for ${input.name}: ${input.value}`);
                            });
                            synced = true;
                        }
                    });

                    if (typeof Livewire !== 'undefined') {
                        const component = Livewire.first(component => component.name === 'auth.login');
                        if (component) {
                            const emailInput = document.querySelector('input[name="email"]');
                            const passwordInput = document.querySelector('input[name="password"]');
                            const email = component.get('email');
                            const password = component.get('password');

                            if (emailInput.value && !email) {
                                component.set('email', emailInput.value, true);
                                console.log('Force set email to:', emailInput.value);
                            }
                            if (passwordInput.value && !password) {
                                component.set('password', passwordInput.value, true);
                                console.log('Force set password to:', passwordInput.value);
                            }

                            console.log('Livewire email:', email || 'null');
                            console.log('Livewire password:', password ? 'provided' : 'missing');

                            if (!email && synced && attempt < maxAttempts) {
                                console.log('State not synced, retrying...');
                                trySyncInputs(attempt + 1, maxAttempts);
                            }
                        } else {
                            console.error('Livewire component auth.login not found');
                            if (attempt < maxAttempts) {
                                console.log('Retrying due to missing component...');
                                trySyncInputs(attempt + 1, maxAttempts);
                            }
                        }
                    } else {
                        console.error('Livewire not defined');
                        if (attempt < maxAttempts) {
                            console.log('Retrying due to missing Livewire...');
                            trySyncInputs(attempt + 1, maxAttempts);
                        }
                    }
                }, attempt * 500);
            };

            // Run on DOMContentLoaded or immediately
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => {
                    console.log('DOMContentLoaded fired');
                    trySyncInputs();
                });
            } else {
                console.log('DOM already loaded, running sync');
                trySyncInputs();
            }
        } catch (error) {
            console.error('Script error:', error);
        }
    </script>
    @endpush

</div>
