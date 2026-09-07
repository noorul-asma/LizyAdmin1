<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The admin panel's pages (dashboard, services, categories, ...) are
 * server-rendered Blade views whose actual content — the inline
 * renderListingPage({ columns, fields, ... }) config — lives directly in
 * the HTML response, not in a separately-versioned static file. The
 * shared engine (public/js/lizy-admin.js) already busts its own cache via
 * ?v={{ filemtime(...) }} in layouts/admin.blade.php, but the page itself
 * had no such protection: a browser that had already cached the old HTML
 * (e.g. from an earlier visit in the same tab/session) could keep serving
 * it after a Blade template was edited on disk, making a real, deployed
 * fix look like it "isn't there" even though the server has the new
 * version. This forces every admin page response to be revalidated on
 * every load, so a template change always shows up immediately.
 */
class NoBrowserCache
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }
}
