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

if (!function_exists('get_customer_setting')) {
    function get_customer_setting(string $name) {

        if (!auth()->customerId()) throw new \Exception('Customer ID is not set for the authenticated user.');

        if (empty($name)) throw new \Exception('get_customer_setting() requires a name parameter.');

        $setting = \App\Models\CustomerSetting::where('customer_id', auth()->customerId())->first();

        if (!$setting) throw new \Exception('Customer settings not found for the authenticated user.');

        if (!property_exists($setting, $name)) throw new \Exception('There is no customer setting with the name: ' . $name);

        return $setting->$name;

    }
}

if (!function_exists('debug_jobs')) {

    function debug_jobs()
    {
        return config('app.debug_jobs');
    }

}

if (!function_exists('debug_logging')) {

    function debug_logging($message = '')
    {

        if (debug_jobs()) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
            $callingClass = isset($trace[1]['class']) ? $trace[1]['class'] : null;
            $callingMethod = isset($trace[1]['function']) ? $trace[1]['function'] : null;
            \Illuminate\Support\Facades\Log::debug($callingClass . '::' . $callingMethod . '() ' . $message);    
        }

    }

}

if (!function_exists('error_logging')) {

    function error_logging($message = '')
    {

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $callingClass = isset($trace[1]['class']) ? $trace[1]['class'] : null;
        $callingMethod = isset($trace[1]['function']) ? $trace[1]['function'] : null;
        \Illuminate\Support\Facades\Log::error($callingClass . '::' . $callingMethod . '() ' . $message);    

    }

}
