<?php
use Jenssegers\Agent\Agent;

if (!function_exists('agent')) {
    function agent()
    {
        return new Agent();
    }
}

if (!function_exists('isDesktop')) {
    function isDesktop()
    {
        return agent()->isDesktop();
    }
}

if (!function_exists('isMobile')) {
    function isMobile()
    {
        if (agent()->isTablet() || agent()->isPhone()) {
            return true;
        }
        return false;
    }
}

if (!function_exists('get_customer_time_increment')) {
    function get_customer_time_increment($divide = true) {

        if (!auth()->customerId()) {
            throw new \Exception('Customer ID is not set for the authenticated user.');
        }

        $setting = \App\Models\CustomerSetting::where('customer_id', auth()->customerId())->first();

        if (!$setting) {
            throw new \Exception('Customer settings not found for the authenticated user.');
        }
        
        if ($divide == true) return $setting->time_increment / 100;
        if ($setting->time_increment == 0) return $setting->time_increment;

    }
}

if (!function_exists('get_customer_time_report_last_day')) {
    function get_customer_time_report_last_day() {

        if (!auth()->customerId()) {
            throw new \Exception('Customer ID is not set for the authenticated user.');
        }

        $setting = \App\Models\CustomerSetting::where('customer_id', auth()->customerId())->first();

        if (!$setting) {
            throw new \Exception('Customer settings not found for the authenticated user.');
        }
        
        return $setting->time_report_last_day;

    }
}

if (!function_exists('get_customer_time_report_auto_lock')) {
    function get_customer_time_report_auto_lock() {

        if (!auth()->customerId()) {
            throw new \Exception('Customer ID is not set for the authenticated user.');
        }

        $setting = \App\Models\CustomerSetting::where('customer_id', auth()->customerId())->first();

        if (!$setting) {
            throw new \Exception('Customer settings not found for the authenticated user.');
        }
        
        return $setting->time_report_auto_lock;

    }
}