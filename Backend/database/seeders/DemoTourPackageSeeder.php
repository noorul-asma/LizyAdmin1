<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\TourPackage;
use Illuminate\Database\Seeder;

/**
 * Realistic placeholder Tour Packages so LizyGo's homepage, destination
 * pages, and package detail pages have real, coherent data to render
 * immediately instead of looking dummy/empty. Every field a LizyGo view
 * reads (itinerary, badge, tags, exclusions, includes) is populated —
 * not just name/price — so nothing shows as "0 Days / 0 Nights" or a
 * blank card.
 *
 * Deliberately does NOT include a Vagamon entry: that destination is
 * managed entirely from the admin dashboard's own "+ Add Package" form
 * (Camp Pricing fields included) rather than seeded here, so re-running
 * this file can never overwrite or duplicate it.
 *
 * Safe to re-run: uses firstOrCreate keyed by name, so it will never
 * overwrite anything you've since edited from either admin panel.
 *
 * Run standalone with: php artisan db:seed --class=DemoTourPackageSeeder
 * (already wired into the main DatabaseSeeder too).
 */
class DemoTourPackageSeeder extends Seeder
{
    public function run(): void
    {
        $domestic = Category::firstWhere(['module' => 'tour_package', 'slug' => 'domestic-tours']);
        $international = Category::firstWhere(['module' => 'tour_package', 'slug' => 'international-tours']);

        foreach ($this->packages($domestic?->id, $international?->id) as $data) {
            TourPackage::firstOrCreate(['name' => $data['name']], $data);
        }
    }

    private function packages(?int $domesticId, ?int $internationalId): array
    {
        return [
            [
                'category_id' => $domesticId,
                'name' => 'Goa Beach & Nightlife Explorer',
                'subtitle' => 'Sun, sand and shacks — the classic Goa getaway',
                'type' => 'Group Tour',
                'tags' => ['Group Tour', 'Most Booked', 'Recommended'],
                'badge' => ['label' => 'Most Booked', 'color' => 'emerald', 'icon' => 'fa-fire'],
                'destination_name' => 'Goa',
                'duration_days' => 4,
                'duration_nights' => 3,
                'price' => 12999,
                'original_price' => 15999,
                'discount' => '18% OFF',
                'description' => 'Four days across North and South Goa — beach hopping, a sunset cruise, and the flea market at Anjuna, with your stay handled at a beachside 3-star resort.',
                'includes' => 'Hotel stay, daily breakfast, airport transfers, sightseeing by AC vehicle, sunset cruise.',
                'itinerary' => [
                    ['day' => 1, 'title' => 'Arrival & Baga Beach', 'description' => 'Airport pickup, check-in, evening free at Baga Beach.', 'hotel_name' => 'Sea Pearl Resort, Calangute', 'meal' => 'Dinner', 'note' => 'Check-in from 1 PM.'],
                    ['day' => 2, 'title' => 'North Goa Sightseeing', 'description' => 'Fort Aguada, Chapora Fort, Anjuna Flea Market, Vagator Beach.', 'hotel_name' => 'Sea Pearl Resort, Calangute', 'meal' => 'Breakfast', 'note' => 'Flea market runs Wednesdays only.'],
                    ['day' => 3, 'title' => 'South Goa & Sunset Cruise', 'description' => 'Colva Beach, Basilica of Bom Jesus, evening Mandovi river cruise.', 'hotel_name' => 'Sea Pearl Resort, Calangute', 'meal' => 'Breakfast, Dinner', 'note' => 'Cruise subject to weather.'],
                    ['day' => 4, 'title' => 'Departure', 'description' => 'Check-out and airport drop.', 'hotel_name' => '', 'meal' => 'Breakfast', 'note' => 'Check-out by 11 AM.'],
                ],
                'exclusions' => ['Airfare / train fare to Goa', 'Water sports and adventure activities', 'Personal expenses and tips', 'Lunch on all days'],
                'terms_conditions' => 'Rates are per person on twin sharing. Hotel subject to availability at time of booking. 100% cancellation charge within 7 days of departure.',
                'status' => 'published',
                'is_featured' => true,
            ],
            [
                'category_id' => $domesticId,
                'name' => 'Ooty & Coonoor Hill Retreat',
                'subtitle' => 'Tea estates, toy train and misty viewpoints',
                'type' => 'Group Tour',
                'tags' => ['Group Tour', 'Recommended'],
                'badge' => ['label' => 'Recommended', 'color' => 'blue', 'icon' => 'fa-star'],
                'destination_name' => 'Ooty',
                'duration_days' => 3,
                'duration_nights' => 2,
                'price' => 8999,
                'original_price' => 10499,
                'discount' => '14% OFF',
                'description' => 'A relaxed hill-station break through Ooty and Coonoor — botanical gardens, Doddabetta viewpoint, and a ride on the Nilgiri toy train.',
                'includes' => 'Hotel stay, daily breakfast and dinner, all sightseeing by private vehicle, toy train tickets.',
                'itinerary' => [
                    ['day' => 1, 'title' => 'Arrival & Botanical Garden', 'description' => 'Check-in, Government Botanical Garden, Ooty Lake in the evening.', 'hotel_name' => 'Hill View Residency, Ooty', 'meal' => 'Dinner', 'note' => ''],
                    ['day' => 2, 'title' => 'Coonoor & Doddabetta', 'description' => 'Doddabetta Peak, Sim\'s Park, Coonoor tea estates and toy train ride.', 'hotel_name' => 'Hill View Residency, Ooty', 'meal' => 'Breakfast, Dinner', 'note' => 'Toy train seats are limited — book early.'],
                    ['day' => 3, 'title' => 'Departure', 'description' => 'Rose Garden visit, check-out and drop to Coimbatore.', 'hotel_name' => '', 'meal' => 'Breakfast', 'note' => ''],
                ],
                'exclusions' => ['Travel to/from Coimbatore', 'Lunch on all days', 'Entry tickets not mentioned in itinerary'],
                'terms_conditions' => 'Rates are per person on twin sharing. Toy train ride subject to seat availability.',
                'status' => 'published',
                'is_featured' => false,
            ],
            [
                'category_id' => $domesticId,
                'name' => 'Coorg Coffee Estates Retreat',
                'subtitle' => 'Misty coffee plantations and waterfalls',
                'type' => 'Group Tour',
                'tags' => ['Group Tour', 'Budget Friendly'],
                'badge' => null,
                'destination_name' => 'Coorg',
                'duration_days' => 4,
                'duration_nights' => 3,
                'price' => 10999,
                'original_price' => null,
                'discount' => null,
                'description' => 'A relaxed Coorg itinerary through coffee plantations, Abbey Falls, and an elephant camp on the Kaveri river.',
                'includes' => 'Hotel stay, daily breakfast, sightseeing by private vehicle, Dubare Elephant Camp entry.',
                'itinerary' => [
                    ['day' => 1, 'title' => 'Arrival in Coorg', 'description' => 'Check-in, evening walk at Raja\'s Seat for sunset.', 'hotel_name' => 'Coorg Coffee County Resort', 'meal' => 'Dinner', 'note' => ''],
                    ['day' => 2, 'title' => 'Abbey Falls & Plantation Walk', 'description' => 'Abbey Falls, a guided coffee plantation walk, spice garden visit.', 'hotel_name' => 'Coorg Coffee County Resort', 'meal' => 'Breakfast', 'note' => ''],
                    ['day' => 3, 'title' => 'Dubare Elephant Camp', 'description' => 'Dubare Elephant Camp, river rafting (own cost), Namdroling Monastery.', 'hotel_name' => 'Coorg Coffee County Resort', 'meal' => 'Breakfast', 'note' => 'Rafting is seasonal — confirm before booking.'],
                    ['day' => 4, 'title' => 'Departure', 'description' => 'Check-out and drop to Mysore.', 'hotel_name' => '', 'meal' => 'Breakfast', 'note' => ''],
                ],
                'exclusions' => ['Travel to/from Mysore', 'River rafting charges', 'Lunch and dinner on days 2-3'],
                'terms_conditions' => 'Rates are per person on twin sharing. Elephant camp timings are fixed by the forest department and subject to change.',
                'status' => 'published',
                'is_featured' => false,
            ],
            [
                'category_id' => $domesticId,
                'name' => 'Kashmir Paradise Tour',
                'subtitle' => 'Srinagar houseboats, Gulmarg gondola and Pahalgam valleys',
                'type' => 'Group Tour',
                'tags' => ['Group Tour', 'Most Booked', 'Recommended'],
                'badge' => ['label' => 'Best Seller', 'color' => 'rose', 'icon' => 'fa-crown'],
                'destination_name' => 'Kashmir',
                'duration_days' => 6,
                'duration_nights' => 5,
                'price' => 24999,
                'original_price' => 29999,
                'discount' => '17% OFF',
                'description' => 'The full Kashmir circuit — a night on a Dal Lake houseboat, the Gulmarg gondola, and Pahalgam\'s river valleys.',
                'includes' => 'Houseboat + hotel stay, daily breakfast and dinner, all transfers, shikara ride.',
                'itinerary' => [
                    ['day' => 1, 'title' => 'Arrival in Srinagar', 'description' => 'Airport pickup, evening shikara ride on Dal Lake.', 'hotel_name' => 'Deluxe Houseboat, Dal Lake', 'meal' => 'Dinner', 'note' => ''],
                    ['day' => 2, 'title' => 'Srinagar Local Sightseeing', 'description' => 'Mughal Gardens — Nishat, Shalimar, Chashme Shahi.', 'hotel_name' => 'Deluxe Houseboat, Dal Lake', 'meal' => 'Breakfast, Dinner', 'note' => ''],
                    ['day' => 3, 'title' => 'Gulmarg Excursion', 'description' => 'Day trip to Gulmarg, gondola ride (own cost), Apharwat Peak views.', 'hotel_name' => 'Hotel Alpine, Srinagar', 'meal' => 'Breakfast, Dinner', 'note' => 'Gondola tickets subject to weather closure.'],
                    ['day' => 4, 'title' => 'Pahalgam Valley', 'description' => 'Drive to Pahalgam via Awantipora ruins, Betaab Valley, Aru Valley.', 'hotel_name' => 'Hotel Pine Valley, Pahalgam', 'meal' => 'Breakfast, Dinner', 'note' => ''],
                    ['day' => 5, 'title' => 'Pahalgam to Srinagar', 'description' => 'Chandanwari, return to Srinagar, free evening for shopping.', 'hotel_name' => 'Hotel Alpine, Srinagar', 'meal' => 'Breakfast, Dinner', 'note' => ''],
                    ['day' => 6, 'title' => 'Departure', 'description' => 'Check-out and airport drop.', 'hotel_name' => '', 'meal' => 'Breakfast', 'note' => ''],
                ],
                'exclusions' => ['Airfare to/from Srinagar', 'Gondola/cable car tickets', 'Pony rides and adventure sports', 'Lunch on all days'],
                'terms_conditions' => 'Rates are per person on twin sharing. Gulmarg/Pahalgam access may be affected by weather or road conditions in winter.',
                'status' => 'published',
                'is_featured' => true,
            ],
            [
                'category_id' => $internationalId,
                'name' => 'Bali International Getaway',
                'subtitle' => 'Beaches, rice terraces and temple sunsets',
                'type' => 'Group Tour',
                'tags' => ['International', 'Recommended'],
                'badge' => ['label' => 'International', 'color' => 'violet', 'icon' => 'fa-plane'],
                'destination_name' => 'Bali',
                'duration_days' => 5,
                'duration_nights' => 4,
                'price' => 45999,
                'original_price' => 52999,
                'discount' => '13% OFF',
                'description' => 'A five-day Bali introduction — Ubud\'s rice terraces, Tanah Lot at sunset, and free time on Seminyak beach.',
                'includes' => 'Hotel stay, daily breakfast, airport transfers, Ubud and Tanah Lot tours, visa assistance.',
                'itinerary' => [
                    ['day' => 1, 'title' => 'Arrival in Bali', 'description' => 'Airport pickup, check-in, evening free at Seminyak Beach.', 'hotel_name' => 'Seminyak Beach Resort', 'meal' => 'Dinner', 'note' => 'Visa on arrival — carry printed itinerary.'],
                    ['day' => 2, 'title' => 'Ubud Day Tour', 'description' => 'Tegalalang Rice Terrace, Ubud Monkey Forest, local art market.', 'hotel_name' => 'Seminyak Beach Resort', 'meal' => 'Breakfast', 'note' => ''],
                    ['day' => 3, 'title' => 'Tanah Lot Sunset Tour', 'description' => 'Uluwatu Temple, Tanah Lot sunset, Kecak fire dance.', 'hotel_name' => 'Seminyak Beach Resort', 'meal' => 'Breakfast', 'note' => ''],
                    ['day' => 4, 'title' => 'Free Day / Optional Water Sports', 'description' => 'Day at leisure — optional water sports at own cost.', 'hotel_name' => 'Seminyak Beach Resort', 'meal' => 'Breakfast', 'note' => 'Water sports bookable through your tour manager.'],
                    ['day' => 5, 'title' => 'Departure', 'description' => 'Check-out and airport drop.', 'hotel_name' => '', 'meal' => 'Breakfast', 'note' => ''],
                ],
                'exclusions' => ['International airfare', 'Visa fees (assistance provided)', 'Water sports and optional activities', 'Lunch and dinner (except Day 1)'],
                'terms_conditions' => 'Rates are per person on twin sharing, subject to exchange rate at time of booking. Passport must be valid for 6+ months.',
                'status' => 'published',
                'is_featured' => false,
            ],
        ];
    }
}
