<?php

namespace App\Http\Livewire;

class OverviewMobile extends \Autonic\Restuser\Http\Livewire\BaseComponent
{

    public function mount()
    {
    }

    public function render()
    {
        return view('livewire.overview-mobile')->layout('layouts.app');
    }

}