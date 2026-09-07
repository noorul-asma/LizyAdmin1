<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Models\Service;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * One-off backfill: attaches a real image to every existing service that
 * doesn't already have a main image, so nothing is left blank on LizyNet.
 * Only touches services with zero images — never overwrites an image an
 * admin has already uploaded. Matches by slug against the same photos
 * LizyNet's own static SERVICES list used to reference (js/data.js), so
 * the look stays consistent with what was already on the site; any
 * service with no matching slug (e.g. a custom one created in Lizy Admin)
 * gets a neutral generic fallback photo instead of staying empty.
 *
 * Usage: php artisan services:seed-images
 */
class SeedServiceImages extends Command
{
    protected $signature = 'services:seed-images';

    protected $description = "Attach a suitable image to every existing service that doesn't have one yet";

    /** slug => Unsplash photo URL, same ones LizyNet's static service list already used. */
    private const IMAGES = [
        'static-website-development' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?fm=jpg&q=80&w=900&auto=format&fit=crop',
        'dynamic-website-development' => 'https://images.unsplash.com/photo-1758518731706-be5d5230e5a5?fm=jpg&q=80&w=900&auto=format&fit=crop',
        'ecommerce-website-development' => 'https://images.unsplash.com/photo-1563013544-824ae1b704d3?fm=jpg&q=80&w=900&auto=format&fit=crop',
        'seo' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?fm=jpg&q=80&w=900&auto=format&fit=crop',
        'social-media-marketing' => 'https://images.unsplash.com/photo-1517048676732-d65bc937f952?fm=jpg&q=80&w=900&auto=format&fit=crop',
        'ppc-advertising' => 'https://images.unsplash.com/photo-1533750349088-cd871a92f312?fm=jpg&q=80&w=900&auto=format&fit=crop',
        'mobile-app-development' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?fm=jpg&q=80&w=900&auto=format&fit=crop',
        'ai-agent-development' => 'https://images.unsplash.com/photo-1666875753105-c63a6f3bdc86?fm=jpg&q=80&w=900&auto=format&fit=crop',
        'software-development' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?fm=jpg&q=80&w=900&auto=format&fit=crop',
        'branding' => 'https://images.unsplash.com/photo-1561070791-2526d30994b5?fm=jpg&q=80&w=900&auto=format&fit=crop',
        'saas-app-development' => 'https://images.unsplash.com/photo-1608222351212-18fe0ec7b13b?fm=jpg&q=80&w=900&auto=format&fit=crop',
    ];

    /** Used for any service whose slug isn't in the list above (e.g. a custom one created directly in Lizy Admin). */
    private const FALLBACK_IMAGE = 'https://images.unsplash.com/photo-1552664730-d307ca884978?fm=jpg&q=80&w=900&auto=format&fit=crop';

    public function handle(): int
    {
        $services = Service::whereDoesntHave('media', fn ($q) => $q->where('collection', 'main'))->get();

        if ($services->isEmpty()) {
            $this->info('Every service already has an image — nothing to do.');

            return self::SUCCESS;
        }

        foreach ($services as $service) {
            $url = self::IMAGES[$service->slug] ?? self::FALLBACK_IMAGE;

            try {
                $response = Http::timeout(20)->get($url);

                if (! $response->successful()) {
                    $this->warn("Skipped \"{$service->name}\" — download failed (HTTP {$response->status()}).");

                    continue;
                }

                $filename = 'media/'.date('Y/m').'/'.$service->slug.'-'.uniqid().'.jpg';
                Storage::disk('public')->put($filename, $response->body());

                Media::create([
                    'mediable_type' => Service::class,
                    'mediable_id' => $service->id,
                    'type' => 'image',
                    'collection' => 'main',
                    'disk' => 'public',
                    'path' => $filename,
                    'original_name' => basename($filename),
                    'mime_type' => 'image/jpeg',
                    'size' => strlen($response->body()),
                    'sort_order' => 0,
                ]);

                $this->info("Added image to \"{$service->name}\".");
            } catch (\Throwable $e) {
                $this->warn("Skipped \"{$service->name}\" — {$e->getMessage()}");
            }
        }

        $this->info('Done.');

        return self::SUCCESS;
    }
}
