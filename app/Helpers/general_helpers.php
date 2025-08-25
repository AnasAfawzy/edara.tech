<?php

use App\Models\AccountingSetting;
use App\Models\Setting;

if (!function_exists('breadcrumb')) {
    /**
     * @param array $items كل عنصر: ['title' => ..., 'url' => ...] أو ['title' => ...] فقط
     */
    function breadcrumb(array $items = [])
    {
        return view('components.breadcrumb', compact('items'))->render();
    }
}

if (!function_exists('acc_setting')) {
    function acc_setting($key, $default = null)
    {
        return AccountingSetting::where('key', $key)->value('value') ?? $default;
    }
}



if (!function_exists('setting')) {
    function setting($key, $default = null)
    {
        return Setting::get($key, $default);
    }
}
