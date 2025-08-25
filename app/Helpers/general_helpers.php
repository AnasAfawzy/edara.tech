<?php

use Illuminate\Support\Facades\View;

if (!function_exists('breadcrumb')) {
    /**
     * @param array $items كل عنصر: ['title' => ..., 'url' => ...] أو ['title' => ...] فقط
     */
    function breadcrumb(array $items = [])
    {
        return view('components.breadcrumb', compact('items'))->render();
    }
}
