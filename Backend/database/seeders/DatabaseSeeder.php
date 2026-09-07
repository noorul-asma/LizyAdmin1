<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Location;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Roles are inserted by the create_roles_table migration itself, so this
     * seeder focuses on: permissions, one Administrator login, and a starter
     * set of categories/locations so the 4 modules are usable immediately.
     */
    public function run(): void
    {
        $this->seedPermissions();
        $this->seedAdminUser();
        $this->seedLocations();
        $this->seedCategories();
        $this->call(DemoTourPackageSeeder::class);
        $this->call(LizyNetServiceSeeder::class);
    }

    private function seedPermissions(): void
    {
        $modules = ['products', 'properties', 'packages', 'services', 'categories', 'locations', 'media', 'enquiries', 'users'];
        $actions = ['view', 'create', 'edit', 'delete', 'publish'];

        $administrator = Role::where('slug', 'administrator')->first();
        $manager = Role::where('slug', 'manager')->first();

        $allPermissionIds = [];
        $publishPermissionIds = [];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $permission = Permission::firstOrCreate(
                    ['slug' => "{$module}.{$action}"],
                    ['name' => ucfirst($action)." {$module}", 'module' => $module]
                );
                $allPermissionIds[] = $permission->id;
                if ($action !== 'delete') {
                    $publishPermissionIds[] = $permission->id;
                }
            }
        }

        $administrator?->permissions()->sync($allPermissionIds);
        $manager?->permissions()->sync($publishPermissionIds);
    }

    private function seedAdminUser(): void
    {
        $administrator = Role::where('slug', 'administrator')->first();

        User::firstOrCreate(
            ['email' => 'admin@lizyadmin.test'],
            [
                'name' => 'Lizy Admin',
                'password' => Hash::make('password'),
                'role_id' => $administrator?->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }

    private function seedLocations(): void
    {
        $india = Location::firstOrCreate(['slug' => 'india', 'parent_id' => null], ['type' => 'country', 'name' => 'India']);
        $tn = Location::firstOrCreate(['slug' => 'tamil-nadu', 'parent_id' => $india->id], ['type' => 'state', 'name' => 'Tamil Nadu']);
        Location::firstOrCreate(['slug' => 'coimbatore', 'parent_id' => $tn->id], ['type' => 'city', 'name' => 'Coimbatore']);
        Location::firstOrCreate(['slug' => 'chennai', 'parent_id' => $tn->id], ['type' => 'city', 'name' => 'Chennai']);
    }

    /**
     * Starter categories for all four modules (LizyGo/LizyMart/LizyReality/
     * LizyNet), matching the exact lists the business asked the Categories
     * admin page to manage.
     *
     * A few of these ids are already pointed at by real rows seeded
     * elsewhere (DemoTourPackageSeeder's packages use the Domestic/
     * International tour_package categories; the 3 demo Properties use
     * Villa/Apartment/Plot) - renaming those categories in place, rather
     * than deleting and recreating them under the new names, means this
     * stays safe to re-run without ever orphaning those foreign keys. The
     * other old placeholder categories (Electronics, Motor Pumps, Home
     * Services, ...) aren't referenced by anything and are simply dropped.
     */
    private function seedCategories(): void
    {
        $renames = [
            ['module' => 'tour_package', 'from_slug' => 'domestic', 'name' => 'Domestic Tours'],
            ['module' => 'tour_package', 'from_slug' => 'international', 'name' => 'International Tours'],
            ['module' => 'property', 'from_slug' => 'villa', 'name' => 'Villas'],
            ['module' => 'property', 'from_slug' => 'apartment', 'name' => 'Apartments'],
            ['module' => 'property', 'from_slug' => 'plot', 'name' => 'Land'],
            // LizyNet's real service categories, replacing the earlier
            // generic placeholder set ('Website Development', 'Banner
            // Design', ...) that didn't match what the business actually
            // offers. Renamed in place (not deleted+recreated) so any
            // service already saved under the old category keeps working.
            ['module' => 'service', 'from_slug' => 'website-development', 'name' => 'Web Development'],
            ['module' => 'service', 'from_slug' => 'software-development', 'name' => 'Engineering'],
        ];

        foreach ($renames as $r) {
            Category::where('module', $r['module'])->where('slug', $r['from_slug'])->first()
                ?->update(['name' => $r['name'], 'slug' => Str::slug($r['name'])]);
        }

        Category::whereIn('module', ['product', 'service'])
            ->whereIn('slug', ['electronics', 'mobiles', 'laptops', 'motor-pumps', 'self-priming', 'submersible', 'home-services', 'cleaning', 'plumbing', 'professional-services', 'banner-design', 'video-editing'])
            ->delete();

        $flat = [
            'tour_package' => [
                'Domestic Tours', 'International Tours', 'Honeymoon Packages', 'Family Packages',
                'Adventure Tours', 'Weekend Trips', 'Group Tours', 'Pilgrimage Tours',
            ],
            'product' => [
                'Water Pumps', 'Submersible Pumps', 'Agricultural Pumps', 'Industrial Pumps',
                'Solar Pumps', 'Booster Pumps', 'Sewage Pumps', 'Pump Accessories',
            ],
            'property' => [
                'Residential', 'Commercial', 'Land', 'Apartments', 'Villas', 'Houses', 'Rental', 'PG & Hostels',
            ],
            // Matches the real LizyNet service catalogue (lizynet.com's own
            // nav / js/data.js SERVICES on the LizyNet site) - every one of
            // the 11 real services falls under exactly one of these four.
            'service' => [
                'Web Development', 'Digital Marketing', 'Engineering', 'Design',
            ],
        ];

        foreach ($flat as $module => $names) {
            foreach ($names as $sortOrder => $name) {
                Category::firstOrCreate(
                    ['module' => $module, 'slug' => Str::slug($name)],
                    ['name' => $name, 'is_active' => true, 'sort_order' => $sortOrder]
                );
            }
        }
    }
}
