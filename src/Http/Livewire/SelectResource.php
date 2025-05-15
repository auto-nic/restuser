<?php

namespace Autonic\Restuser\Http\Livewire;

use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Jenssegers\Agent\Agent;

class SelectResource extends Component
{

    public function selectToken($key)
    {

        session()->put('selected_token', $key);

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

        redirect()->to($redirectUrl);

    }

    public function render()
    {
        return view('restuser::livewire.pages.auth.select-resource')->layout('layouts.app');
    }

}
