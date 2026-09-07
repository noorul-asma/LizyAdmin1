<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Media;
use App\Models\Property;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Creates 5 real Land properties (your exact poster images, used as each
 * one's Banner Image) plus one sample Apartment and one sample House
 * property, so LizyRealty's Land/Apartment/House pages have real banners
 * to display immediately.
 *
 * Idempotent (find-by-title-including-trashed, then update or create,
 * same pattern as LizyGo's sample seeder) - safe to re-run. Only sets a
 * property's Banner Image when it doesn't already have one - re-running
 * this never overwrites a banner you've since changed by hand in the
 * admin panel.
 *
 * Usage: php artisan db:seed --class=Database\\Seeders\\RealtyBannerPropertiesSeeder
 */
class RealtyBannerPropertiesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::where('module', 'property')->pluck('id', 'name');
        $assetsPath = __DIR__.'/realty-banner-assets';

        foreach ($this->properties() as $data) {
            $categoryId = $categories[$data['category_name']] ?? null;
            if (! $categoryId) {
                $this->command?->warn("Skipped \"{$data['title']}\" — no \"{$data['category_name']}\" category found.");

                continue;
            }

            $property = Property::withTrashed()->where('title', $data['title'])->first();

            if ($property && $property->trashed()) {
                $property->restore();
            }

            if (! $property) {
                $property = Property::create([
                    'category_id' => $categoryId,
                    'title' => $data['title'],
                    'listing_type' => 'sale',
                    'price' => $data['price'],
                    'price_unit' => $data['price_unit'] ?? null,
                    'status' => 'published',
                    'published_at' => now(),
                ]);
            } else {
                $property->fill([
                    'category_id' => $categoryId,
                    'listing_type' => 'sale',
                    'price' => $data['price'],
                    'price_unit' => $data['price_unit'] ?? null,
                    'status' => 'published',
                ])->save();
            }

            // Only attach a banner if this property doesn't already have
            // one - never overwrites a banner you've since changed by
            // hand in the admin panel.
            if (! $property->mediaByCollection('banner')) {
                $imagePath = $assetsPath.'/'.$data['image'];
                if (! is_file($imagePath)) {
                    $this->command?->warn("Skipped banner for \"{$data['title']}\" — asset file missing: {$data['image']}");

                    continue;
                }

                $filename = 'media/'.date('Y/m').'/'.\Illuminate\Support\Str::slug($data['title']).'-'.uniqid().'.jpg';
                Storage::disk('public')->put($filename, file_get_contents($imagePath));

                Media::create([
                    'mediable_type' => Property::class,
                    'mediable_id' => $property->id,
                    'type' => 'image',
                    'collection' => 'banner',
                    'disk' => 'public',
                    'path' => $filename,
                    'original_name' => basename($imagePath),
                    'mime_type' => 'image/jpeg',
                    'size' => filesize($imagePath),
                    'sort_order' => 0,
                ]);
            }
        }
    }

    private function properties(): array
    {
        return [
            ['title' => 'Dream Town Pollachi', 'category_name' => 'Land', 'price' => 825000, 'price_unit' => 'Per Cent', 'image' => 'dream-town.jpg'],
            ['title' => 'Kumaran City Pethappampatti', 'category_name' => 'Land', 'price' => 425000, 'price_unit' => 'Per Cent', 'image' => 'kumaran-city.jpg'],
            ['title' => 'Kumaran Residency Kinathukadavu', 'category_name' => 'Land', 'price' => 690000, 'price_unit' => 'Per Cent', 'image' => 'kumaran-residency.jpg'],
            ['title' => 'SR Garden Karamadai', 'category_name' => 'Land', 'price' => 299000, 'price_unit' => 'Per Cent', 'image' => 'sr-garden.jpg'],
            ['title' => 'Varahi Garden Periyanaikenpalayam', 'category_name' => 'Land', 'price' => 790000, 'price_unit' => 'Per Cent', 'image' => 'varahi-garden.jpg'],
            ['title' => 'Featured Apartment Complex', 'category_name' => 'Apartments', 'price' => 4500000, 'image' => 'apartment-banner.jpg'],
            ['title' => 'Featured Luxury House', 'category_name' => 'Houses', 'price' => 9500000, 'image' => 'house-banner.jpg'],
        ];
    }
}
