<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class SystemSettingsController extends Controller
{
    public function index()
    {
        $settingsGrouped = SystemSetting::getAllGrouped();
        
        return Inertia::render('system/settings/Index', [
            'settingsGrouped' => $settingsGrouped
        ]);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
            'settings.*' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $updated = 0;
        $errors = [];

        foreach ($request->settings as $key => $value) {
            $setting = SystemSetting::where('key', $key)->first();
            
            if (!$setting) {
                $errors[] = "Setting '{$key}' not found";
                continue;
            }

            // Validate based on setting type
            $validation = $this->validateSettingValue($setting, $value);
            if (!$validation['valid']) {
                $errors[] = $validation['message'];
                continue;
            }

            if (SystemSetting::set($key, $value)) {
                $updated++;
            }
        }

        if (!empty($errors)) {
            return redirect()->back()
                ->withErrors(['settings' => $errors])
                ->withInput()
                ->with('error', 'Some settings could not be updated. Please check the errors below.');
        }

        return redirect()->back()
            ->with('success', "Successfully updated {$updated} settings")
            ->with('message', 'System settings have been updated successfully.');
    }

    private function validateSettingValue($setting, $value)
    {
        switch ($setting->type) {
            case 'boolean':
                return ['valid' => true, 'message' => ''];
                
            case 'number':
                if (!is_numeric($value)) {
                    return ['valid' => false, 'message' => "{$setting->name} must be a valid number"];
                }
                return ['valid' => true, 'message' => ''];
                
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return ['valid' => false, 'message' => "{$setting->name} must be a valid email address"];
                }
                return ['valid' => true, 'message' => ''];
                
            case 'url':
                if (!filter_var($value, FILTER_VALIDATE_URL)) {
                    return ['valid' => false, 'message' => "{$setting->name} must be a valid URL"];
                }
                return ['valid' => true, 'message' => ''];
                
            case 'select':
                if ($setting->options && !in_array($value, array_keys($setting->options))) {
                    return ['valid' => false, 'message' => "{$setting->name} has invalid option selected"];
                }
                return ['valid' => true, 'message' => ''];
                
            default:
                return ['valid' => true, 'message' => ''];
        }
    }
}
