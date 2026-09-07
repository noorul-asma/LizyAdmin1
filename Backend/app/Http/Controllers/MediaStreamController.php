<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaStreamController extends Controller
{
    public function show(string $path): StreamedResponse|Response
    {
        $disk = Storage::disk('public');

        // Guard against path traversal (../../.env etc.) — the route's
        // {path} wildcard is otherwise happy to accept anything.
        $normalized = str_replace('\\', '/', $path);
        if (str_contains($normalized, '..') || ! $disk->exists($normalized)) {
            abort(404);
        }

        return $disk->response($normalized, null, [
            // Media is content-addressed by upload time (media/Y/m/random),
            // never overwritten in place, so it's safe to cache hard.
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}