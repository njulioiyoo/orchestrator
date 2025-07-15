<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RealTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Masjid Tenant
        $masjidTenant = Tenant::create([
            'name' => 'Masjid Al-Ikhlas',
            'slug' => 'masjid-al-ikhlas',
            'domain' => 'masjid-al-ikhlas.com',
            'subdomain' => 'masjid',
            'business_type' => 'mosque',
            'phone' => '021-12345678',
            'address' => 'Jl. Masjid Raya No. 123, Jakarta Pusat, DKI Jakarta',
            'logo_path' => '/storage/tenants/masjid/logo.png',
            'primary_color' => '#2D5A27',
            'secondary_color' => '#4A7C59',
            'timezone' => 'Asia/Jakarta',
            'locale' => 'id_ID',
            'currency' => 'IDR',
            'config' => [
                'allow_registration' => true,
                'email_verification' => true,
                'two_factor_auth' => false,
                'max_users' => 50,
                'max_storage_mb' => 1024,
                'features' => [
                    'user_management' => true,
                    'role_management' => true,
                    'permission_management' => true,
                    'donation_management' => true,
                    'event_management' => true,
                    'prayer_schedule' => true,
                    'inventory_management' => true,
                    'financial_reports' => true,
                    'announcement_system' => true,
                    'member_management' => true,
                    'committee_management' => true,
                    'rental_management' => false,
                    'booking_system' => false,
                    'customer_management' => false,
                    'payment_tracking' => false,
                    'student_management' => false,
                    'grade_management' => false,
                    'attendance_tracking' => false,
                    'library_management' => false,
                ],
                'branding' => [
                    'app_name' => 'Masjid Al-Ikhlas Management',
                    'tagline' => 'Sistem Manajemen Masjid Terpadu',
                    'favicon' => '/storage/tenants/masjid/favicon.ico',
                    'custom_css' => null,
                ],
                'notification_settings' => [
                    'prayer_reminders' => true,
                    'event_notifications' => true,
                    'donation_alerts' => true,
                    'committee_notifications' => true,
                ],
                'limits' => [
                    'max_events_per_month' => 20,
                    'max_announcements_per_day' => 5,
                    'max_committees' => 10,
                    'max_inventory_items' => 200,
                ]
            ],
            'is_active' => true,
            'expires_at' => now()->addYear(),
        ]);

        // Create Rental Tenant
        $rentalTenant = Tenant::create([
            'name' => 'Rental Sukses Mandiri',
            'slug' => 'rental-sukses-mandiri',
            'domain' => 'rentalsukses.com',
            'subdomain' => 'rental',
            'business_type' => 'rental',
            'phone' => '022-87654321',
            'address' => 'Jl. Rental Street No. 456, Bandung, Jawa Barat',
            'logo_path' => '/storage/tenants/rental/logo.png',
            'primary_color' => '#1E40AF',
            'secondary_color' => '#3B82F6',
            'timezone' => 'Asia/Jakarta',
            'locale' => 'id_ID',
            'currency' => 'IDR',
            'config' => [
                'allow_registration' => false,
                'email_verification' => true,
                'two_factor_auth' => true,
                'max_users' => 25,
                'max_storage_mb' => 2048,
                'features' => [
                    'user_management' => true,
                    'role_management' => true,
                    'permission_management' => true,
                    'rental_management' => true,
                    'inventory_management' => true,
                    'booking_system' => true,
                    'customer_management' => true,
                    'payment_tracking' => true,
                    'financial_reports' => true,
                    'invoice_generation' => true,
                    'maintenance_tracking' => true,
                    'damage_reports' => true,
                    'donation_management' => false,
                    'event_management' => false,
                    'prayer_schedule' => false,
                    'announcement_system' => false,
                    'member_management' => false,
                    'committee_management' => false,
                    'student_management' => false,
                    'grade_management' => false,
                    'attendance_tracking' => false,
                    'library_management' => false,
                ],
                'branding' => [
                    'app_name' => 'Rental Management System',
                    'tagline' => 'Solusi Manajemen Rental Terpadu',
                    'favicon' => '/storage/tenants/rental/favicon.ico',
                    'custom_css' => null,
                ],
                'notification_settings' => [
                    'booking_reminders' => true,
                    'payment_alerts' => true,
                    'inventory_alerts' => true,
                    'maintenance_reminders' => true,
                    'damage_notifications' => true,
                ],
                'limits' => [
                    'max_items' => 500,
                    'max_bookings_per_month' => 100,
                    'max_customers' => 200,
                    'max_invoices_per_month' => 150,
                ]
            ],
            'is_active' => true,
            'expires_at' => now()->addMonths(6),
        ]);

        // Create users for Masjid Tenant
        $imamUser = User::create([
            'tenant_id' => $masjidTenant->id,
            'name' => 'Ahmad Imam',
            'email' => 'imam@masjid-al-ikhlas.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'is_active' => true,
            'profile' => [
                'phone' => '081234567890',
                'position' => 'Ketua Pengurus Masjid',
                'address' => 'Jl. Imam Raya No. 789, Jakarta Pusat',
                'birth_date' => '1975-05-15',
                'join_date' => '2020-01-01',
                'emergency_contact' => '081234567891',
                'education' => 'S1 Pendidikan Agama Islam',
                'experience' => '10 tahun sebagai pengurus masjid',
            ],
            'permissions' => [
                'manage_users',
                'manage_roles',
                'manage_donations',
                'manage_events',
                'manage_prayer_schedule',
                'manage_inventory',
                'view_financial_reports',
                'manage_announcements',
                'manage_members',
                'manage_committees'
            ],
            'settings' => [
                'notification_email' => true,
                'notification_sms' => true,
                'dashboard_theme' => 'light',
                'language' => 'id',
                'timezone' => 'Asia/Jakarta',
            ]
        ]);

        // Create users for Rental Tenant
        $rentalOwner = User::create([
            'tenant_id' => $rentalTenant->id,
            'name' => 'Sari Rental',
            'email' => 'owner@rentalsukses.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'is_active' => true,
            'profile' => [
                'phone' => '081987654321',
                'position' => 'Pemilik Usaha',
                'address' => 'Jl. Rental Owner No. 101, Bandung',
                'birth_date' => '1985-03-20',
                'join_date' => '2022-01-05',
                'emergency_contact' => '081987654322',
                'education' => 'S1 Manajemen Bisnis',
                'experience' => '5 tahun di bidang rental',
            ],
            'permissions' => [
                'manage_users',
                'manage_roles',
                'manage_inventory',
                'manage_bookings',
                'manage_customers',
                'manage_payments',
                'view_financial_reports',
                'generate_invoices',
                'manage_maintenance',
                'view_damage_reports'
            ],
            'settings' => [
                'notification_email' => true,
                'notification_sms' => true,
                'dashboard_theme' => 'dark',
                'language' => 'id',
                'timezone' => 'Asia/Jakarta',
            ]
        ]);

        // Add rental staff
        User::create([
            'tenant_id' => $rentalTenant->id,
            'name' => 'Budi Staff',
            'email' => 'staff@rentalsukses.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'is_active' => true,
            'profile' => [
                'phone' => '081555666777',
                'position' => 'Staff Operasional',
                'address' => 'Jl. Staff No. 202, Bandung',
                'birth_date' => '1990-07-10',
                'join_date' => '2023-03-15',
                'emergency_contact' => '081555666778',
                'education' => 'SMK Teknik',
                'experience' => '2 tahun sebagai staff rental',
            ],
            'permissions' => [
                'view_inventory',
                'manage_bookings',
                'view_customers',
                'process_payments',
                'create_invoices',
                'report_maintenance',
                'report_damage'
            ],
            'settings' => [
                'notification_email' => true,
                'notification_sms' => false,
                'dashboard_theme' => 'light',
                'language' => 'id',
                'timezone' => 'Asia/Jakarta',
            ]
        ]);

        $this->command->info('Real tenant data seeded successfully!');
        $this->command->info('Masjid Tenant ID: ' . $masjidTenant->id);
        $this->command->info('Rental Tenant ID: ' . $rentalTenant->id);
    }
}