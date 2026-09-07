<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\EnquiryResource;
use App\Models\Enquiry;
use Illuminate\Http\Request;

class EnquiryController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'message' => ['nullable', 'string'],
            'cta_type' => ['nullable', 'string', 'max:50'],
        ]);

        // Manually-logged leads (phone calls, walk-ins) aren't tied to a
        // specific listing the way website enquiries are, so enquiryable_*
        // is left null - EnquiryResource's enquiryable() already only
        // renders that block via whenLoaded/optional chaining, so a null
        // relation here is safe, not an error.
        $enquiry = Enquiry::create([
            ...$data,
            'status' => 'new',
            'source' => 'manual',
        ]);

        return new EnquiryResource($enquiry);
    }

    /**
     * Returns every enquiry (paginated), oldest filters unchanged. Nothing
     * is ever excluded here based on viewed_at - "Old" enquiries are kept
     * and returned forever (see destroy() below), just flagged via
     * viewed_at/is_new on each row so the Enquiries page can split them
     * into New/Old sections and the dashboard's Recent Activity feed
     * (which also hits this endpoint, per dashboard.blade.php) keeps
     * working unchanged.
     */
    public function index(Request $request)
    {
        $query = Enquiry::with('enquiryable')->status($request->get('status'));

        if ($request->filled('q')) {
            $term = $request->get('q');
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('message', 'like', "%{$term}%");
            });
        }

        if ($request->filled('enquiryable_type')) {
            $query->where('enquiryable_type', 'App\\Models\\'.$request->get('enquiryable_type'));
        }

        if ($request->filled('source')) {
            $query->where('source', $request->get('source'));
        }

        return EnquiryResource::collection($query->latest()->paginate($request->get('per_page', 15)));
    }

    /**
     * Viewing a single enquiry's detail is what actually clears it from
     * "New" - this is the per-item counterpart to markRead() below.
     * Only writes viewed_at the first time (whenNull), so re-opening an
     * already-read enquiry doesn't touch anything.
     */
    public function show(int $id)
    {
        $enquiry = Enquiry::with('enquiryable')->findOrFail($id);

        if (is_null($enquiry->viewed_at)) {
            $enquiry->forceFill(['viewed_at' => now()])->save();
        }

        return new EnquiryResource($enquiry);
    }

    /**
     * Bulk "mark all as read" - kept as an explicit, admin-triggered
     * action (e.g. a "Mark all as read" button on the Enquiries page)
     * rather than something that fires automatically when the page loads.
     * Auto-firing this on page load was the reason the New/Old split
     * never worked: it raced with the page's own data fetch and could
     * mark every enquiry read before the admin ever saw which ones were
     * new.
     */
    public function markRead()
    {
        $count = Enquiry::whereNull('viewed_at')->update(['viewed_at' => now()]);

        return response()->json(['data' => ['marked' => $count]]);
    }

    public function update(Request $request, int $id)
    {
        $enquiry = Enquiry::findOrFail($id);
        $enquiry->update($request->validate([
            'status' => ['required', 'string', 'in:new,contacted,converted,closed'],
        ]));

        return new EnquiryResource($enquiry);
    }

    // Intentionally no destroy() - enquiries must never be deleted from
    // the database (data preservation requirement). Old/handled leads
    // stay in the "Old Enquiries" section indefinitely instead of being
    // removed; there is deliberately no route wired to a delete action
    // for this resource (see routes/api.php).
}
