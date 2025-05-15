<?php

namespace Autonic\Restuser;

class MicroserviceAccessToken
{

    public function __construct($tokenData) {

        foreach($tokenData as $key => $value) {
            $this->{$key} = $value;
        }

    }

    public function hasAbility(string $ability): bool
    {
        return in_array($ability, $this->abilities ?? []);
    }

}