<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SystemSetting;

class SystemSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get default tenant ID
        $defaultTenantId = config('seeding.default_tenant_id', 1);

        $settings = [
            // General Settings
            [
                'key' => 'app_name',
                'name' => 'Application Name',
                'value' => 'Orchestrator',
                'type' => 'text',
                'description' => 'The name of your application',
                'group' => 'general',
                'sort_order' => 1
            ],
            [
                'key' => 'app_description',
                'name' => 'Application Description',
                'value' => 'Web Application Management System',
                'type' => 'textarea',
                'description' => 'Brief description of your application',
                'group' => 'general',
                'sort_order' => 2
            ],
            [
                'key' => 'app_version',
                'name' => 'Application Version',
                'value' => '1.0.0',
                'type' => 'text',
                'description' => 'Current version of the application',
                'group' => 'general',
                'sort_order' => 3
            ],
            [
                'key' => 'app_timezone',
                'name' => 'Default Timezone',
                'value' => 'Asia/Jakarta',
                'type' => 'select',
                'description' => 'Default timezone for the application',
                'group' => 'general',
                'options' => [
                    'Asia/Jakarta' => 'Asia/Jakarta',
                    'UTC' => 'UTC',
                    'America/New_York' => 'America/New_York',
                    'Europe/London' => 'Europe/London',
                    'Asia/Tokyo' => 'Asia/Tokyo'
                ],
                'sort_order' => 4
            ],

            // Email Settings
            [
                'key' => 'mail_driver',
                'name' => 'Mail Driver',
                'value' => 'smtp',
                'type' => 'select',
                'description' => 'Email delivery method',
                'group' => 'email',
                'options' => [
                    'smtp' => 'SMTP',
                    'mailgun' => 'Mailgun',
                    'ses' => 'Amazon SES',
                    'sendmail' => 'Sendmail'
                ],
                'sort_order' => 1
            ],
            [
                'key' => 'mail_host',
                'name' => 'SMTP Host',
                'value' => 'smtp.gmail.com',
                'type' => 'text',
                'description' => 'SMTP server hostname',
                'group' => 'email',
                'sort_order' => 2
            ],
            [
                'key' => 'mail_port',
                'name' => 'SMTP Port',
                'value' => '587',
                'type' => 'number',
                'description' => 'SMTP server port',
                'group' => 'email',
                'sort_order' => 3
            ],
            [
                'key' => 'mail_from_address',
                'name' => 'From Email Address',
                'value' => 'admin@orchestrator.local',
                'type' => 'email',
                'description' => 'Default sender email address',
                'group' => 'email',
                'sort_order' => 4
            ],
            [
                'key' => 'mail_from_name',
                'name' => 'From Name',
                'value' => 'Orchestrator Admin',
                'type' => 'text',
                'description' => 'Default sender name',
                'group' => 'email',
                'sort_order' => 5
            ],

            // Appearance Settings
            [
                'key' => 'theme_color',
                'name' => 'Primary Theme Color',
                'value' => '#0d6efd',
                'type' => 'text',
                'description' => 'Primary color for the application theme',
                'group' => 'appearance',
                'sort_order' => 1
            ],
            [
                'key' => 'logo_url',
                'name' => 'Application Logo',
                'value' => '/images/logo.png',
                'type' => 'file',
                'description' => 'Upload application logo',
                'group' => 'appearance',
                'sort_order' => 2
            ],
            [
                'key' => 'favicon_url',
                'name' => 'Favicon',
                'value' => '/images/favicon.ico',
                'type' => 'file',
                'description' => 'Upload favicon',
                'group' => 'appearance',
                'sort_order' => 3
            ],

            // System Settings
            [
                'key' => 'maintenance_mode',
                'name' => 'Maintenance Mode',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Enable maintenance mode',
                'group' => 'system',
                'sort_order' => 1
            ],
            [
                'key' => 'user_registration',
                'name' => 'Allow User Registration',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Allow new users to register',
                'group' => 'system',
                'sort_order' => 2
            ],
            [
                'key' => 'max_upload_size',
                'name' => 'Max Upload Size (MB)',
                'value' => '10',
                'type' => 'number',
                'description' => 'Maximum file upload size in MB',
                'group' => 'system',
                'sort_order' => 3
            ],
            [
                'key' => 'session_lifetime',
                'name' => 'Session Lifetime (minutes)',
                'value' => '120',
                'type' => 'number',
                'description' => 'User session timeout in minutes',
                'group' => 'system',
                'sort_order' => 4
            ]
        ];

        foreach ($settings as $setting) {
            SystemSetting::firstOrCreate(
                ['key' => $setting['key']],
                array_merge($setting, ['tenant_id' => $defaultTenantId])
            );
        }

        $this->command->info('System settings seeded successfully.');
        $this->command->info('Total settings: ' . SystemSetting::count());
        $this->command->line('Groups: General, Email, Appearance, System');
    }
}
