<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnquiryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'message' => $this->message,
            'cta_type' => $this->cta_type,
            'status' => $this->status,
            'source' => $this->source,

            'enquiryable_type' => $this->enquiryable_type ? class_basename($this->enquiryable_type) : null,
            'enquiryable_id' => $this->enquiryable_id,
            // Only ever populated when the caller did ->with('enquiryable') -
            // manual leads (phone/walk-in) have no related listing at all,
            // so this stays null for them rather than erroring.
            'enquiryable' => $this->whenLoaded('enquiryable', function () {
                return $this->enquiryable ? [
                    'id' => $this->enquiryable->id,
                    'name' => $this->enquiryable->name ?? $this->enquiryable->title ?? null,
                ] : null;
            }),

            // viewed_at is the single source of truth for New vs Old on the
            // Enquiries list page (and for the dashboard badge count) -
            // separate from `status`, which only changes when an admin
            // deliberately edits a lead. is_new is exposed as a plain
            // boolean so the frontend doesn't have to re-derive a
            // null-check on a timestamp string in every place it groups
            // enquiries.
            'viewed_at' => optional($this->viewed_at)->toISOString(),
            'is_new' => is_null($this->viewed_at),

            'created_at' => optional($this->created_at)->toISOString(),
        ];
    }
}
