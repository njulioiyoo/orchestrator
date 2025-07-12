<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use OwenIt\Auditing\Models\Audit;

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
        \Log::info('SystemSettings update started', [
            'request_data' => $request->all(),
            'user_id' => auth()->id()
        ]);

        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
            'settings.*' => 'required'
        ]);

        if ($validator->fails()) {
            \Log::error('SystemSettings validation failed', ['errors' => $validator->errors()]);
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $updated = 0;
        $errors = [];
        $auditData = [];

        foreach ($request->settings as $key => $value) {
            \Log::info("Processing setting: {$key}", ['value' => $value]);
            
            $setting = SystemSetting::where('key', $key)->first();
            
            if (!$setting) {
                $error = "Setting '{$key}' not found";
                $errors[] = $error;
                \Log::error($error);
                continue;
            }

            // Validate based on setting type
            $validation = $this->validateSettingValue($setting, $value);
            if (!$validation['valid']) {
                $errors[] = $validation['message'];
                \Log::error("Validation failed for {$key}", ['message' => $validation['message']]);
                continue;
            }

            // Store old value for audit
            $oldValue = $setting->value;
            
            $result = SystemSetting::set($key, $value);
            \Log::info("Setting update result for {$key}", ['result' => $result]);
            
            if ($result) {
                $updated++;
                
                // Store data for batch audit log
                $auditData[] = [
                    'setting_key' => $key,
                    'setting_name' => $setting->name,
                    'old_value' => $oldValue,
                    'new_value' => $value,
                    'setting_type' => $setting->type
                ];
            } else {
                $errors[] = "Failed to update setting '{$key}'";
            }
        }

        // Create manual audit log for batch update
        if (!empty($auditData)) {
            $this->createBatchUpdateAudit($auditData, $updated);
        }

        \Log::info('SystemSettings update completed', [
            'updated_count' => $updated,
            'errors_count' => count($errors),
            'errors' => $errors
        ]);

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

    /**
     * Create manual audit log for batch system settings update
     *
     * @param array $auditData
     * @param int $updatedCount
     */
    private function createBatchUpdateAudit(array $auditData, int $updatedCount): void
    {
        try {
            // Create a summary audit entry for the batch operation
            Audit::create([
                'user_type' => 'App\Models\User',
                'user_id' => auth()->id(),
                'event' => 'batch_updated',
                'auditable_type' => 'App\Models\SystemSetting',
                'auditable_id' => null, // null for batch operations
                'old_values' => [],
                'new_values' => [
                    'batch_operation' => 'system_settings_update',
                    'updated_count' => $updatedCount,
                    'settings_modified' => collect($auditData)->pluck('setting_key')->toArray(),
                    'timestamp' => now()->toISOString()
                ],
                'url' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'tags' => json_encode(['system_settings', 'batch_update']),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Create individual audit entries for each setting change
            foreach ($auditData as $audit) {
                $setting = SystemSetting::where('key', $audit['setting_key'])->first();
                
                if ($setting) {
                    Audit::create([
                        'user_type' => 'App\Models\User',
                        'user_id' => auth()->id(),
                        'event' => 'updated',
                        'auditable_type' => 'App\Models\SystemSetting',
                        'auditable_id' => $setting->id,
                        'old_values' => [
                            'value' => $audit['old_value']
                        ],
                        'new_values' => [
                            'value' => $audit['new_value']
                        ],
                        'url' => request()->fullUrl(),
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                        'tags' => json_encode(['system_settings', $audit['setting_key']]),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            \Log::info('Batch audit log created successfully', [
                'updated_count' => $updatedCount,
                'audit_entries' => count($auditData) + 1 // +1 for summary entry
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to create batch audit log', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
