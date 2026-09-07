<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Models\Product;
use App\Models\Property;
use App\Models\Service;
use App\Models\TourPackage;

class DashboardController extends Controller
{
    /** Universal dashboard summary - the "Universal Admin Dashboard" from the spec. */
    public function index()
    {
        $modules = [
            'products' => Product::class,
            'properties' => Property::class,
            'tour_packages' => TourPackage::class,
            'services' => Service::class,
        ];

        $stats = [];
        foreach ($modules as $key => $class) {
            $stats[$key] = [
                'total' => $class::count(),
                'published' => $class::where('status', 'published')->count(),
                'draft' => $class::where('status', 'draft')->count(),
                'featured' => $class::where('is_featured', true)->count(),
            ];
        }

        $stats['enquiries'] = [
            'total' => Enquiry::count(),
            // Was Enquiry::where('status', 'new')->count() - that only
            // changed when an admin explicitly edited a lead's status, so
            // the badge stayed stuck even after the admin had opened and
            // read every enquiry. viewed_at tracks whether anyone has
            // actually looked, independent of where the lead is in its
            // status lifecycle - see EnquiryController::markRead().
            'new' => Enquiry::whereNull('viewed_at')->count(),
        ];

        return response()->json(['data' => $stats]);
    }
}
