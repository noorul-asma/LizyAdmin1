<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\TourPackage;
use Illuminate\Database\Seeder;

/**
 * Adds the 3 correctly-split Vagamon packages (Per Head / Couple / Family —
 * previously entered as one combined package, which is what this replaces)
 * plus 4 additional sample packages, all published and ready to show on
 * LizyGo immediately.
 *
 * Idempotent (find-by-slug-including-trashed, then update or create) —
 * safe to re-run, including multiple times in a row.
 *
 * IMPORTANT: this does NOT delete any existing "combined" Vagamon package
 * that may already exist in your live database — a seeder can't safely
 * guess which existing record that is without risking deleting the wrong
 * thing. After running this, please check the Tour Packages list in Lizy
 * Admin yourself and delete the old combined one if it's still there.
 *
 * The Vagamon banner image was provided and its figures are reflected here
 * exactly: Per Head Package ₹1,550/person (3 meals, comfortable stay, DJ &
 * campfire, off-road jeep safari), Family Package (family room from ₹2,250,
 * food 3 times ₹500, jeep safari ₹2,450). The banner didn't cover a Couple
 * Package, so that one still uses the figures given in text (₹2,000 room,
 * ₹2,000 jeep safari).
 */
class LizyGoSamplePackagesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::where('module', 'tour_package')->pluck('id', 'slug');

        foreach ($this->packages() as $data) {
            $categoryId = $categories[$data['category_slug']] ?? $categories->first();
            unset($data['category_slug']);

            $attributes = array_merge($data, [
                'category_id' => $categoryId,
                'status' => 'published',
                'published_at' => now(),
            ]);

            // TourPackage uses SoftDeletes, so a previously soft-deleted
            // package still occupies its slug at the database level even
            // though a normal query (including updateOrCreate's own
            // lookup) can't see it — that mismatch is exactly what caused
            // "UNIQUE constraint failed: tour_packages.slug" here:
            // updateOrCreate() didn't find a live match, tried to INSERT
            // a new row, and collided with the trashed one still holding
            // that slug. withTrashed() finds it either way; restore()
            // brings it back if it had been deleted, and no image field
            // is touched here at all, so nothing already uploaded is
            // overwritten by re-running this.
            $package = TourPackage::withTrashed()->where('slug', $data['slug'])->first();

            if ($package) {
                if ($package->trashed()) {
                    $package->restore();
                }
                $package->fill($attributes)->save();
            } else {
                TourPackage::create($attributes);
            }
        }
    }

    private function packages(): array
    {
        $vagamonFoodMenu = "Morning: Idli, Dosa, Appam, Idiyappam with Kadala Curry & Egg Curry. "
            ."Lunch: Biryani & Meals. Dinner: Chapathy, Porotta with Chicken Curry & Veg Kuruma.";

        $vagamonJeepSpots = ['Kottamala View Point', 'Idukki Dam Back Side View', 'Waterfalls', 'Cave', 'Suicide Point'];

        return [
            // ---------------------------------------------------------
            // Vagamon — split into 3 separate packages (was one combined
            // package before)
            // ---------------------------------------------------------
            [
                'slug' => 'vagamon-per-head-package',
                'name' => 'Vagamon Per Head Package',
                'subtitle' => 'Room, food, DJ, campfire and off-road jeep safari — priced per head',
                'category_slug' => 'adventure-tours',
                'type' => 'Adventure',
                'tags' => ['Most Booked'],
                'destination_name' => 'Vagamon',
                'duration_days' => 2,
                'duration_nights' => 1,
                'price' => 1550,
                'description' => "A complete per-head Vagamon getaway at ₹1,550 per person: 3 meals (breakfast, lunch & dinner), "
                    ."comfortable stay, DJ & campfire in the evening, and an off-road jeep safari covering ".implode(', ', $vagamonJeepSpots).". ".$vagamonFoodMenu,
                'includes' => "3 Meals (Breakfast, Lunch & Dinner), Comfortable Stay, DJ & Campfire, Off-Road Jeep Safari",
                'inclusions' => ['3 Meals (Breakfast, Lunch & Dinner)', 'Comfortable Stay', 'DJ & Campfire', 'Off-Road Jeep Safari'],
                'activities' => $vagamonJeepSpots,
                'itinerary' => [
                    ['day' => 1, 'title' => 'Arrival & Campfire Evening', 'description' => 'Check into the stay, lunch, free time, evening DJ & campfire, dinner.', 'meal' => $vagamonFoodMenu],
                    ['day' => 2, 'title' => 'Off-Road Jeep Safari', 'description' => 'Breakfast, then off-road jeep safari covering '.implode(', ', $vagamonJeepSpots).'. Lunch and departure.'],
                ],
            ],
            [
                'slug' => 'vagamon-couple-package',
                'name' => 'Vagamon Couple Package',
                'subtitle' => 'Room and off-road jeep safari, priced for couples',
                'category_slug' => 'honeymoon-packages',
                'type' => 'Honeymoon',
                'tags' => ['Recommended'],
                'destination_name' => 'Vagamon',
                'duration_days' => 2,
                'duration_nights' => 1,
                'price' => 2000,
                'description' => 'A quiet Vagamon stay for couples — room starting from ₹2,000, with an off-road jeep safari available for ₹2,000.',
                'includes' => 'Room (starting ₹2,000), Off-road Jeep Safari (₹2,000)',
                'inclusions' => ['Room stay (starting from ₹2,000)', 'Off-road Jeep Safari (₹2,000)'],
                'activities' => ['Off-road Jeep Safari'],
                'itinerary' => [
                    ['day' => 1, 'title' => 'Check-in & Leisure', 'description' => 'Check into the room, evening at leisure.'],
                    ['day' => 2, 'title' => 'Jeep Safari & Departure', 'description' => 'Optional off-road jeep safari, then check-out.'],
                ],
            ],
            [
                'slug' => 'vagamon-family-package',
                'name' => 'Vagamon Family Package',
                'subtitle' => 'Family room, food 3 times and off-road jeep safari for the whole family',
                'category_slug' => 'family-packages',
                'type' => 'Family',
                'tags' => [],
                'destination_name' => 'Vagamon',
                'duration_days' => 2,
                'duration_nights' => 1,
                'price' => 2250,
                'description' => 'A family-friendly Vagamon package: family room starting from ₹2,250, food 3 times a day at ₹500, and an off-road jeep safari at ₹2,450.',
                'includes' => 'Family Room Starts (₹2,250), Food 3 Times (₹500), Jeep Safari (₹2,450)',
                'inclusions' => ['Family Room Starts (₹2,250)', 'Food 3 Times (₹500)', 'Jeep Safari (₹2,450)'],
                'activities' => ['Off-road Jeep Safari'],
                'itinerary' => [
                    ['day' => 1, 'title' => 'Arrival', 'description' => 'Check into the family room, lunch, evening at leisure, dinner.'],
                    ['day' => 2, 'title' => 'Jeep Safari & Departure', 'description' => 'Breakfast, off-road jeep safari, lunch, check-out.'],
                ],
            ],

            // ---------------------------------------------------------
            // 4 additional sample packages
            // ---------------------------------------------------------
            [
                'slug' => 'munnar-tea-trail-escape',
                'name' => 'Munnar Tea Trail Escape',
                'subtitle' => 'Rolling tea estates, misty viewpoints and a cool hill-station break',
                'category_slug' => 'family-packages',
                'type' => 'Family',
                'tags' => ['Recommended'],
                'destination_name' => 'Munnar',
                'duration_days' => 3,
                'duration_nights' => 2,
                'price' => 9500,
                'description' => 'A relaxed family trip through Munnar\'s tea estates, viewpoints and cool climate.',
                'includes' => 'Stay, breakfast, sightseeing cab',
                'inclusions' => ['Hotel stay', 'Daily breakfast', 'Sightseeing by private cab'],
                'itinerary' => [
                    ['day' => 1, 'title' => 'Arrival & Local Sightseeing', 'description' => 'Check-in, evening at a local viewpoint.'],
                    ['day' => 2, 'title' => 'Tea Estates & Dam', 'description' => 'Full-day tea estate and Mattupetty dam visit.'],
                    ['day' => 3, 'title' => 'Departure', 'description' => 'Breakfast and check-out.'],
                ],
            ],
            [
                'slug' => 'alleppey-backwater-houseboat-retreat',
                'name' => 'Alleppey Backwater Houseboat Retreat',
                'subtitle' => 'A night aboard a traditional houseboat on the Kerala backwaters',
                'category_slug' => 'honeymoon-packages',
                'type' => 'Honeymoon',
                'tags' => ['Most Booked'],
                'destination_name' => 'Alleppey',
                'duration_days' => 2,
                'duration_nights' => 1,
                'price' => 11000,
                'description' => 'An overnight stay on a traditional Kerala houseboat, cruising the Alleppey backwaters.',
                'includes' => 'Houseboat stay, all meals onboard',
                'inclusions' => ['Houseboat overnight stay', 'Breakfast, lunch and dinner onboard'],
                'itinerary' => [
                    ['day' => 1, 'title' => 'Boarding & Backwater Cruise', 'description' => 'Board the houseboat by noon, cruise through the backwaters, sunset views, dinner onboard.'],
                    ['day' => 2, 'title' => 'Disembark', 'description' => 'Breakfast onboard, disembark by 9 AM.'],
                ],
            ],
            [
                'slug' => 'wayanad-wildlife-waterfalls-adventure',
                'name' => 'Wayanad Wildlife & Waterfalls Adventure',
                'subtitle' => 'Wildlife sanctuaries, caves and waterfalls in the Western Ghats',
                'category_slug' => 'adventure-tours',
                'type' => 'Adventure',
                'tags' => [],
                'destination_name' => 'Wayanad',
                'duration_days' => 3,
                'duration_nights' => 2,
                'price' => 8500,
                'description' => 'An adventure-focused Wayanad trip covering wildlife sanctuaries, caves and waterfalls.',
                'includes' => 'Stay, breakfast, entry tickets',
                'inclusions' => ['Hotel stay', 'Daily breakfast', 'Sanctuary and cave entry tickets'],
                'itinerary' => [
                    ['day' => 1, 'title' => 'Arrival & Edakkal Caves', 'description' => 'Check-in, visit Edakkal Caves.'],
                    ['day' => 2, 'title' => 'Wildlife Sanctuary & Waterfalls', 'description' => 'Wayanad Wildlife Sanctuary safari, Soochipara Waterfalls.'],
                    ['day' => 3, 'title' => 'Departure', 'description' => 'Breakfast and check-out.'],
                ],
            ],
            [
                'slug' => 'pondicherry-french-quarter-weekend',
                'name' => 'Pondicherry French Quarter Weekend',
                'subtitle' => 'A short coastal break through Pondicherry\'s French Quarter and beaches',
                'category_slug' => 'weekend-trips',
                'type' => 'Weekend',
                'tags' => [],
                'destination_name' => 'Pondicherry',
                'duration_days' => 2,
                'duration_nights' => 1,
                'price' => 6500,
                'description' => 'A quick weekend escape through Pondicherry\'s French Quarter, Promenade Beach and Auroville.',
                'includes' => 'Stay, breakfast',
                'inclusions' => ['Hotel stay', 'Daily breakfast'],
                'itinerary' => [
                    ['day' => 1, 'title' => 'French Quarter Walk', 'description' => 'Check-in, evening walk through the French Quarter and Promenade Beach.'],
                    ['day' => 2, 'title' => 'Auroville & Departure', 'description' => 'Morning visit to Auroville, then check-out.'],
                ],
            ],
        ];
    }
}
