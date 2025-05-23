<?php

namespace Autonic\Restuser;

trait CustomerSettingTrait
{

    public static function createDefaultSettings($customerId)
    {

        // create default settings for the customer
        self::createIfNotExists($customerId);

    }

    public static function checkDefaultSettings(int $customerId)
    {

        if (!self::where('customer_id', $customerId)->first()) {
            return false; // settings do not exist
        }

        return true;

    }

    public static function createIfNotExists($customerId)
    {

        $customerSetting = self::where('customer_id', $customerId)->first();

        if (!$customerSetting) {
            $customerSetting = new self();
            $customerSetting->customer_id = $customerId;
            $customerSetting->save();
        }

        return $customerSetting;

    }

}