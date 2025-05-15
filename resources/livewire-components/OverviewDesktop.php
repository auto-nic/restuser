<?php

namespace App\Http\Livewire;

class OverviewDesktop extends \Autonic\Restuser\Http\Livewire\BaseComponent
{

    public function mount()
    {

        // make sure that mobile users are redirected to the mobile version
        if (isMobile()) return redirect('/overview-mobile');

    }

    public function render()
    {
        return view('livewire.overview-desktop')->layout('layouts.app');
    }

}