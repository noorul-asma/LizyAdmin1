<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

/**
 * Backs the Settings page's General / Modules / Website / Notifications
 * tabs. Each group is a fixed set of Setting rows (see DEFAULTS below) -
 * index() returns all four groups in one call; each updateX() validates
 * and persists just its own group.
 */
class SettingsController extends Controller
{
    private const GROUPS = [
        'general' => ['app_name', 'company_name', 'admin_email', 'contact_phone', 'address', 'logo_url', 'favicon_url'],
        'modules' => ['product_enabled', 'property_enabled', 'tour_package_enabled', 'service_enabled'],
        'website' => ['name', 'url', 'logo_url', 'contact_email', 'contact_phone', 'address', 'facebook_url', 'instagram_url', 'whatsapp_number', 'youtube_url', 'footer_copyright'],
        'notifications' => ['new_enquiry', 'new_product_enquiry', 'new_property_enquiry', 'new_tour_enquiry', 'new_service_enquiry', 'email_notifications', 'dashboard_notifications'],
    ];

    private const DEFAULTS = [
        'general' => ['app_name' => 'Lizy Admin', 'company_name' => 'Lizy Group', 'admin_email' => null, 'contact_phone' => null, 'address' => null, 'logo_url' => null, 'favicon_url' => null],
        'modules' => ['product_enabled' => true, 'property_enabled' => true, 'tour_package_enabled' => true, 'service_enabled' => true],
        'website' => ['name' => null, 'url' => null, 'logo_url' => null, 'contact_email' => null, 'contact_phone' => null, 'address' => null, 'facebook_url' => null, 'instagram_url' => null, 'whatsapp_number' => null, 'youtube_url' => null, 'footer_copyright' => null],
        'notifications' => ['new_enquiry' => true, 'new_product_enquiry' => true, 'new_property_enquiry' => true, 'new_tour_enquiry' => true, 'new_service_enquiry' => true, 'email_notifications' => true, 'dashboard_notifications' => true],
    ];

    public function index()
    {
        return response()->json([
            'data' => collect(self::GROUPS)->mapWithKeys(
                fn (array $keys, string $group) => [$group => $this->group($group)]
            ),
        ]);
    }

    public function updateGeneral(Request $request)
    {
        return $this->updateGroup($request, 'general', [
            'app_name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'admin_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'logo_url' => ['nullable', 'string', 'max:2048'],
            'favicon_url' => ['nullable', 'string', 'max:2048'],
        ]);
    }

    public function updateModules(Request $request)
    {
        return $this->updateGroup($request, 'modules', [
            'product_enabled' => ['required', 'boolean'],
            'property_enabled' => ['required', 'boolean'],
            'tour_package_enabled' => ['required', 'boolean'],
            'service_enabled' => ['required', 'boolean'],
        ]);
    }

    public function updateWebsite(Request $request)
    {
        return $this->updateGroup($request, 'website', [
            'name' => ['nullable', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:2048'],
            'logo_url' => ['nullable', 'string', 'max:2048'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'facebook_url' => ['nullable', 'url', 'max:2048'],
            'instagram_url' => ['nullable', 'url', 'max:2048'],
            'whatsapp_number' => ['nullable', 'string', 'max:30'],
            'youtube_url' => ['nullable', 'url', 'max:2048'],
            'footer_copyright' => ['nullable', 'string', 'max:500'],
        ]);
    }

    public function updateNotifications(Request $request)
    {
        return $this->updateGroup($request, 'notifications', [
            'new_enquiry' => ['required', 'boolean'],
            'new_product_enquiry' => ['required', 'boolean'],
            'new_property_enquiry' => ['required', 'boolean'],
            'new_tour_enquiry' => ['required', 'boolean'],
            'new_service_enquiry' => ['required', 'boolean'],
            'email_notifications' => ['required', 'boolean'],
            'dashboard_notifications' => ['required', 'boolean'],
        ]);
    }

    private function updateGroup(Request $request, string $group, array $rules)
    {
        $data = $request->validate($rules);

        foreach ($data as $key => $value) {
            Setting::set("{$group}.{$key}", $value);
        }

        return response()->json(['data' => $this->group($group)]);
    }

    private function group(string $group): array
    {
        $keys = self::GROUPS[$group];
        $defaults = self::DEFAULTS[$group];

        $prefixedDefaults = [];
        foreach ($keys as $key) {
            $prefixedDefaults["{$group}.{$key}"] = $defaults[$key] ?? null;
        }

        $values = Setting::many(array_keys($prefixedDefaults), $prefixedDefaults);

        $out = [];
        foreach ($keys as $key) {
            $out[$key] = $values["{$group}.{$key}"];
        }

        return $out;
    }
}
