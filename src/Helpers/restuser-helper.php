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
