<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::first();
        return view('settings.index', compact('settings'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'company_email' => 'nullable|email|max:255',
            'company_phone' => 'nullable|string|max:50',
            'company_address' => 'nullable|string|max:500',
            'company_logo' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'tax_number' => 'nullable|string|max:100',
            'default_tax_rate' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'timezone' => 'nullable|string|max:100',
            'language' => 'nullable|string|max:10',
            'payment_methods' => 'nullable|array',
        ]);

        // رفع الشعار
        if ($request->hasFile('company_logo')) {
            $data['company_logo'] = $request->file('company_logo')->store('logos', 'public');
        }

        $settings = Setting::first();
        if ($settings) {
            $settings->update($data);
        } else {
            Setting::create($data);
        }

        return redirect()->back()->with('success', 'Settings updated successfully!');
    }
}
