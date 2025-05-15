<?php

namespace Autonic\Restuser\Http\Livewire;

use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\Attributes\On;

abstract class BaseComponent extends Component
{

    /**
     * BaseComponent constructor.
     */
    public function __construct()
    {

        // redirect mobile users trying to access desktop routes
        $currentRoute = request()->route()->getName();
        if (str_ends_with($currentRoute, 'desktop') && isMobile()) {
            return redirect('/desktop-only-splash');
        }

    }

    public $modalStatus = [];

    #[On('close-modal')]
    public function closeModal($name)
    {
        $this->modalStatus[$name] = 'hidden';
    }

    #[On('open-modal')]
    public function showModal($name)
    {
        $this->modalStatus[$name] = '';
    }

    /**
     * Strictly authorize an action, ensuring the policy method exists.
     *
     * @param string $ability
     * @param mixed $arguments
     * @return void
     *
     * @throws \Exception
     */
    public function strictAuthorize(string $ability, $arguments = [])
    {

        // we only check the first argument for existing policy names
        // (any following models are only used inside policy as a data source)
        if (!is_array($arguments)) {
            $arguments = [$arguments];
        }

        // verify that at least one argument is passed
        if (empty($arguments)) {
            throw new \Exception("No model passed to strictAuthorize()");
        }

        $policy = Gate::getPolicyFor(
            $arguments[0] instanceof \Illuminate\Database\Eloquent\Model ? get_class($arguments[0]) : $arguments[0]
        );

        if (!$policy) {
            throw new \Exception("No policy found for: " . get_class($arguments[0]));
        }

        if (!method_exists($policy, $ability)) {
            throw new \Exception("Policy method '{$ability}' does not exist in " . ($policy ? get_class($policy) : 'any policy'));
        }

        // pass ability and models to gate
        Gate::authorize($ability, $arguments);

    }
    
}
