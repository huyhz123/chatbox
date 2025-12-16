<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General Settings
            [
                'key' => 'app_name',
                'value' => 'DigitalMarket',
                'type' => 'string',
                'group' => 'general',
            ],
            [
                'key' => 'app_description',
                'value' => 'Professional e-commerce and digital products platform',
                'type' => 'string',
                'group' => 'general',
            ],
            [
                'key' => 'app_url',
                'value' => 'https://example.com',
                'type' => 'string',
                'group' => 'general',
            ],
            [
                'key' => 'app_logo',
                'value' => '/images/logo.png',
                'type' => 'string',
                'group' => 'general',
            ],
            [
                'key' => 'app_favicon',
                'value' => '/images/favicon.ico',
                'type' => 'string',
                'group' => 'general',
            ],
            [
                'key' => 'company_name',
                'value' => 'Digital Market Inc.',
                'type' => 'string',
                'group' => 'general',
            ],
            [
                'key' => 'company_email',
                'value' => 'info@example.com',
                'type' => 'string',
                'group' => 'general',
            ],
            [
                'key' => 'company_phone',
                'value' => '+1-234-567-8900',
                'type' => 'string',
                'group' => 'general',
            ],
            [
                'key' => 'company_address',
                'value' => '123 Business Street, City, Country',
                'type' => 'string',
                'group' => 'general',
            ],

            // Payment Settings
            [
                'key' => 'payment_stripe_key',
                'value' => 'your_stripe_publishable_key_here',
                'type' => 'string',
                'group' => 'payment',
            ],
            [
                'key' => 'payment_stripe_secret',
                'value' => 'your_stripe_secret_key_here',
                'type' => 'string',
                'group' => 'payment',
            ],
            [
                'key' => 'payment_paypal_client_id',
                'value' => 'xxxxxxxxxxxxxxxxxxxxxxxx',
                'type' => 'string',
                'group' => 'payment',
            ],
            [
                'key' => 'payment_paypal_secret',
                'value' => 'xxxxxxxxxxxxxxxxxxxxxxxx',
                'type' => 'string',
                'group' => 'payment',
            ],
            [
                'key' => 'default_currency',
                'value' => 'VND',
                'type' => 'string',
                'group' => 'payment',
            ],
            [
                'key' => 'default_tax_rate',
                'value' => '10',
                'type' => 'float',
                'group' => 'payment',
            ],

            // Email Settings
            [
                'key' => 'mail_host',
                'value' => 'smtp.mailtrap.io',
                'type' => 'string',
                'group' => 'email',
            ],
            [
                'key' => 'mail_port',
                'value' => '465',
                'type' => 'integer',
                'group' => 'email',
            ],
            [
                'key' => 'mail_username',
                'value' => 'your-mailtrap-username',
                'type' => 'string',
                'group' => 'email',
            ],
            [
                'key' => 'mail_password',
                'value' => 'your-mailtrap-password',
                'type' => 'string',
                'group' => 'email',
            ],
            [
                'key' => 'mail_from_address',
                'value' => 'noreply@example.com',
                'type' => 'string',
                'group' => 'email',
            ],
            [
                'key' => 'mail_from_name',
                'value' => 'Digital Market',
                'type' => 'string',
                'group' => 'email',
            ],

            // SMM Panel Settings
            [
                'key' => 'smm_panel_api_url',
                'value' => 'https://api.smmpanel.com',
                'type' => 'string',
                'group' => 'api',
            ],
            [
                'key' => 'smm_panel_api_key',
                'value' => 'your-api-key-here',
                'type' => 'string',
                'group' => 'api',
            ],
            [
                'key' => 'smm_panel_api_secret',
                'value' => 'your-api-secret-here',
                'type' => 'string',
                'group' => 'api',
            ],

            // Site Features
            [
                'key' => 'enable_registration',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'features',
            ],
            [
                'key' => 'enable_comments',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'features',
            ],
            [
                'key' => 'enable_ratings',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'features',
            ],
            [
                'key' => 'enable_wishlist',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'features',
            ],
            [
                'key' => 'items_per_page',
                'value' => '20',
                'type' => 'integer',
                'group' => 'features',
            ],
            [
                'key' => 'cache_duration',
                'value' => '3600',
                'type' => 'integer',
                'group' => 'features',
            ],

            // Security Settings
            [
                'key' => 'session_lifetime',
                'value' => '120',
                'type' => 'integer',
                'group' => 'security',
            ],
            [
                'key' => 'max_login_attempts',
                'value' => '5',
                'type' => 'integer',
                'group' => 'security',
            ],
            [
                'key' => 'enable_two_factor',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'security',
            ],
            [
                'key' => 'enable_ssl',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'security',
            ],

            // Notification Settings
            [
                'key' => 'enable_email_notifications',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'notifications',
            ],
            [
                'key' => 'enable_sms_notifications',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'notifications',
            ],
            [
                'key' => 'notify_admin_on_order',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'notifications',
            ],
            [
                'key' => 'notify_customer_on_shipment',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'notifications',
            ],

            // Social Media Settings
            [
                'key' => 'facebook_url',
                'value' => 'https://facebook.com/digitalmarket',
                'type' => 'string',
                'group' => 'social',
            ],
            [
                'key' => 'twitter_url',
                'value' => 'https://twitter.com/digitalmarket',
                'type' => 'string',
                'group' => 'social',
            ],
            [
                'key' => 'instagram_url',
                'value' => 'https://instagram.com/digitalmarket',
                'type' => 'string',
                'group' => 'social',
            ],
            [
                'key' => 'linkedin_url',
                'value' => 'https://linkedin.com/company/digitalmarket',
                'type' => 'string',
                'group' => 'social',
            ],

            // Storage Settings
            [
                'key' => 'file_upload_max_size',
                'value' => '102400',
                'type' => 'integer',
                'group' => 'storage',
            ],
            [
                'key' => 'allowed_file_types',
                'value' => json_encode(['pdf', 'zip', 'rar', 'exe', 'docx', 'xlsx', 'pptx']),
                'type' => 'array',
                'group' => 'storage',
            ],
            [
                'key' => 'storage_disk',
                'value' => 'public',
                'type' => 'string',
                'group' => 'storage',
            ],

            // Maintenance Settings
            [
                'key' => 'maintenance_mode',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'maintenance',
            ],
            [
                'key' => 'maintenance_message',
                'value' => 'We are currently under maintenance. Please check back soon.',
                'type' => 'string',
                'group' => 'maintenance',
            ],
            [
                'key' => 'backup_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'maintenance',
            ],
            [
                'key' => 'backup_frequency',
                'value' => 'daily',
                'type' => 'string',
                'group' => 'maintenance',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        echo "Settings created successfully!\n";
    }
}
