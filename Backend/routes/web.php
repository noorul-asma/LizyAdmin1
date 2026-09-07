<?php

use App\Http\Controllers\MediaStreamController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — backend-only build
|--------------------------------------------------------------------------
| The Blade admin UI (dashboard, products, properties, packages, services,
| categories, locations, media, enquiries, settings, login, and the old
| lizygo/lizymart/lizyreality placeholder pages) has been removed from
| this project — it now lives entirely in the separate React admin
| frontend, which talks to this backend only through routes/api.php.
|
| Only two things remain here:
|   1. The authenticated JSON health-check root below, so hitting the
|      backend's base URL confirms it's up without exposing any HTML
|      admin surface.
|   2. The media file route, which is NOT part of routes/api.php but is
|      still required — every media URL returned by the API (main_image,
|      gallery, banner_image, etc. on Products/Properties/Packages/
|      Services/Categories) points here. See MediaStreamController for
|      why this deliberately doesn't rely on the public/storage symlink.
*/

Route::get('/', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'Lizy Admin API',
        'message' => 'This is the Lizy Admin backend. The admin UI is a separate React app — see /api/admin and /api/public for the JSON API.',
    ]);
});

Route::get('/files/{path}', [MediaStreamController::class, 'show'])
    ->where('path', '.*')
    ->name('media.show');
