<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    /**
     * Display settings page
     */
    public function index()
    {
        $eco4Settings = Setting::where('group', 'eco4')
            ->orderBy('label')
            ->get()
            ->map(function ($setting) {
                return [
                    'id' => $setting->id,
                    'key' => $setting->key,
                    'value' => $setting->value,
                    'type' => $setting->type,
                    'label' => $setting->label,
                    'description' => $setting->description,
                ];
            });

        return Inertia::render('Settings/Index', [
            'eco4Settings' => $eco4Settings,
        ]);
    }

    /**
     * Update a setting
     */
    public function update(Request $request, Setting $setting)
    {
        $request->validate([
            'value' => 'required',
        ]);

        $setting->update([
            'value' => $request->value,
        ]);

        // Clear the cache
        \Cache::forget("setting_{$setting->key}");

        return redirect()
            ->back()
            ->with('success', 'Setting updated successfully');
    }

    /**
     * API endpoint to get all settings
     */
    public function api()
    {
        return response()->json([
            'eco4' => Setting::getByGroup('eco4'),
            'general' => Setting::getByGroup('general'),
        ]);
    }

    /**
     * API endpoint to update a setting
     */
    public function apiUpdate(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'value' => 'required',
        ]);

        $setting = Setting::where('key', $request->key)->firstOrFail();
        
        $setting->update([
            'value' => $request->value,
        ]);

        \Cache::forget("setting_{$setting->key}");

        return response()->json([
            'success' => true,
            'setting' => [
                'key' => $setting->key,
                'value' => $setting->value,
                'type' => $setting->type,
            ],
        ]);
    }
}
