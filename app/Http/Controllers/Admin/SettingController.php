<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin|superadmin');
    }

    /**
     * Display the settings management page
     */
    public function index(Request $request)
    {
        $group = $request->get('group', 'general');
        $groups = ['general', 'email', 'payment', 'security', 'notifications', 'seo'];

        $settings = Setting::where('group', $group)->get();

        return view('admin.settings.index', compact('settings', 'group', 'groups'));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $group = $request->get('group', 'general');

        // Validate based on group
        $rules = $this->getValidationRules($group);
        $validated = $request->validate($rules);

        foreach ($validated as $key => $value) {
            // Determine the type and store
            $type = $this->determineType($key, $value);

            Setting::set($key, $value, $type, $group);
        }

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['group' => $group, 'settings' => $validated])
            ->log('settings updated');

        return redirect()->back()->with('success', 'Settings updated successfully!');
    }

    /**
     * Get validation rules based on setting group
     */
    private function getValidationRules($group)
    {
        return match ($group) {
            'general' => [
                'app_name' => 'required|string|max:255',
                'app_description' => 'nullable|string|max:500',
                'app_url' => 'required|url',
                'app_timezone' => 'required|timezone',
                'currency' => 'required|string|max:3',
                'currency_symbol' => 'required|string|max:5',
                'locale' => 'required|string|max:10',
                'items_per_page' => 'required|integer|min:5|max:100',
            ],
            'email' => [
                'mail_from_address' => 'required|email',
                'mail_from_name' => 'required|string|max:255',
                'mail_driver' => 'required|in:smtp,sendmail,mailgun,ses',
                'smtp_host' => 'nullable|string',
                'smtp_port' => 'nullable|integer',
                'smtp_username' => 'nullable|string',
                'smtp_password' => 'nullable|string',
            ],
            'payment' => [
                'enable_stripe' => 'boolean',
                'stripe_key' => 'nullable|string',
                'stripe_secret' => 'nullable|string',
                'enable_paypal' => 'boolean',
                'paypal_client_id' => 'nullable|string',
                'paypal_client_secret' => 'nullable|string',
                'enable_bank_transfer' => 'boolean',
                'bank_account_number' => 'nullable|string',
                'bank_account_name' => 'nullable|string',
            ],
            'security' => [
                'enable_2fa' => 'boolean',
                'enable_ip_whitelist' => 'boolean',
                'session_timeout' => 'required|integer|min:5',
                'max_login_attempts' => 'required|integer|min:1',
                'password_min_length' => 'required|integer|min:6',
            ],
            'notifications' => [
                'enable_email_notifications' => 'boolean',
                'enable_sms_notifications' => 'boolean',
                'new_order_notification' => 'boolean',
                'new_user_notification' => 'boolean',
                'new_ticket_notification' => 'boolean',
                'low_stock_notification' => 'boolean',
                'low_stock_threshold' => 'nullable|integer|min:0',
            ],
            'seo' => [
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
                'meta_keywords' => 'nullable|string|max:500',
                'enable_sitemaps' => 'boolean',
                'enable_analytics' => 'boolean',
                'google_analytics_id' => 'nullable|string',
            ],
            default => [],
        };
    }

    /**
     * Determine the data type for a setting value
     */
    private function determineType($key, $value)
    {
        $booleanKeys = [
            'enable_2fa', 'enable_ip_whitelist', 'enable_email_notifications',
            'enable_sms_notifications', 'new_order_notification', 'new_user_notification',
            'new_ticket_notification', 'low_stock_notification', 'enable_stripe',
            'enable_paypal', 'enable_bank_transfer', 'enable_sitemaps', 'enable_analytics',
        ];

        $integerKeys = ['session_timeout', 'max_login_attempts', 'password_min_length', 'low_stock_threshold', 'items_per_page', 'smtp_port'];

        if (in_array($key, $booleanKeys)) {
            return 'boolean';
        }

        if (in_array($key, $integerKeys)) {
            return 'integer';
        }

        return 'string';
    }

    /**
     * Show general settings
     */
    public function generalSettings()
    {
        $settings = Setting::where('group', 'general')->get()->keyBy('key');
        $timezones = \DateTimeZone::listIdentifiers();

        return view('admin.settings.general', compact('settings', 'timezones'));
    }

    /**
     * Show email settings
     */
    public function emailSettings()
    {
        $settings = Setting::where('group', 'email')->get()->keyBy('key');
        $drivers = ['smtp' => 'SMTP', 'sendmail' => 'Sendmail', 'mailgun' => 'Mailgun', 'ses' => 'AWS SES'];

        return view('admin.settings.email', compact('settings', 'drivers'));
    }

    /**
     * Show payment settings
     */
    public function paymentSettings()
    {
        $settings = Setting::where('group', 'payment')->get()->keyBy('key');

        return view('admin.settings.payment', compact('settings'));
    }

    /**
     * Show security settings
     */
    public function securitySettings()
    {
        $settings = Setting::where('group', 'security')->get()->keyBy('key');

        return view('admin.settings.security', compact('settings'));
    }

    /**
     * Show notification settings
     */
    public function notificationSettings()
    {
        $settings = Setting::where('group', 'notifications')->get()->keyBy('key');

        return view('admin.settings.notifications', compact('settings'));
    }

    /**
     * Show SEO settings
     */
    public function seoSettings()
    {
        $settings = Setting::where('group', 'seo')->get()->keyBy('key');

        return view('admin.settings.seo', compact('settings'));
    }

    /**
     * Clear all caches
     */
    public function clearCache()
    {
        Cache::flush();

        activity()
            ->causedBy(auth()->user())
            ->log('cache cleared');

        return redirect()->back()->with('success', 'Cache cleared successfully!');
    }

    /**
     * Get all settings (for API or AJAX)
     */
    public function getSettings($group = null)
    {
        if ($group) {
            $settings = Setting::where('group', $group)->get();
        } else {
            $settings = Setting::all();
        }

        $result = [];
        foreach ($settings as $setting) {
            $result[$setting->key] = Setting::castValue($setting->value, $setting->type);
        }

        return response()->json($result);
    }

    /**
     * Update a single setting
     */
    public function updateSetting(Request $request, $key)
    {
        $validated = $request->validate([
            'value' => 'required',
            'type' => 'required|in:string,boolean,integer,float,array,json',
        ]);

        $setting = Setting::where('key', $key)->first();

        if (!$setting) {
            return response()->json(['error' => 'Setting not found'], 404);
        }

        $setting->update([
            'value' => is_array($validated['value']) ? json_encode($validated['value']) : $validated['value'],
            'type' => $validated['type'],
        ]);

        Cache::forget("setting_{$key}");

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['key' => $key, 'value' => $validated['value']])
            ->log('setting updated');

        return response()->json(['success' => true, 'message' => 'Setting updated successfully']);
    }
}
