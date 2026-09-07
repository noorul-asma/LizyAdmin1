<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use Illuminate\Http\Request;

class EnquiryController extends Controller
{
    /**
     * Universal lead-capture endpoint used by every CTA that isn't a direct
     * call/chat/whatsapp deep-link (ENQUIRE, REQUEST_QUOTE, BOOK, SCHEDULE...)
     * - including a general "Contact Us" form that isn't about any specific
     * listing, which is why enquiryable_type/enquiryable_id are optional:
     * pass both to attach the lead to a listing, or neither for a general
     * enquiry (the enquiries table's morph columns are nullable for
     * exactly this reason - see the migration).
     *
     * `source` identifies which frontend the enquiry came from (LizyGo,
     * LizyRealty, ...) so the admin Enquiries page can badge each row by
     * origin. It's caller-supplied (each site passes its own fixed value)
     * rather than derived from the request, and constrained to a known
     * list so a typo in a frontend's .env can't silently invent a new
     * source; anything omitted falls back to "website" for backward
     * compatibility with callers that predate this field.
     * Body: { enquiryable_type?: "product|property|tour_package|service", enquiryable_id?, name, email, phone, message, cta_type, source? }
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'enquiryable_type' => ['nullable', 'string', 'in:product,property,tour_package,service'],
            'enquiryable_id' => ['nullable', 'required_with:enquiryable_type', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'message' => ['nullable', 'string'],
            'cta_type' => ['nullable', 'string', 'max:50'],
            'source' => ['nullable', 'string', 'in:website,lizygo,lizyrealty,lizynet'],
        ]);

        $map = [
            'product' => \App\Models\Product::class,
            'property' => \App\Models\Property::class,
            'tour_package' => \App\Models\TourPackage::class,
            'service' => \App\Models\Service::class,
        ];

        $modelClass = null;
        if (! empty($data['enquiryable_type'])) {
            $modelClass = $map[$data['enquiryable_type']];
            abort_unless($modelClass::whereKey($data['enquiryable_id'])->exists(), 404, 'Listing not found.');
        }

        $enquiry = Enquiry::create([
            'enquiryable_type' => $modelClass,
            'enquiryable_id' => $modelClass ? $data['enquiryable_id'] : null,
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'],
            'message' => $data['message'] ?? null,
            'cta_type' => $data['cta_type'] ?? null,
            'status' => 'new',
            'source' => $data['source'] ?? 'website',
        ]);

        return response()->json(['message' => 'Thank you, we will get back to you shortly.', 'id' => $enquiry->id], 201);
    }
}
