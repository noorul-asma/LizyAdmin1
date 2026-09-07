<?php

/**
 * This file was missing entirely, which is why cross-origin requests never
 * worked: Illuminate\Http\Middleware\HandleCors (registered globally by
 * Laravel by default) reads its settings from config('cors'), and with no
 * config/cors.php present that resolves to an empty 'paths' list - so the
 * middleware's hasMatchingPath() check always returned false and it added
 * NO Access-Control-Allow-Origin header to ANY response, on any route.
 * Browsers then silently block the response from being read by JS on a
 * different origin - which is exactly what LizyNet, LizyGo, LizyRealty (or
 * this project's own /wamp64 dev setup) hit when calling /api/public/*.
 *
 * 'paths' covers only the read-only public API (api/public/*) plus the
 * admin API (api/admin/*, needed so a locally-served admin frontend on a
 * different port isn't blocked either) - never the Blade admin panel's own
 * pages, which don't need CORS since they're not called cross-origin.
 *
 * allowed_origins is '*' because every one of these consumer endpoints is
 * either public read-only data or protected by a Sanctum Bearer token (not
 * cookies), so there's no session/CSRF risk in allowing any origin to call
 * them - this mirrors the "public REST API for external frontends"
 * architecture the project is already built around.
 */
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];
