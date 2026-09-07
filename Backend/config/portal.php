<?php

/**
 * Universal Portal Engine configuration.
 *
 * This is the "Configurable CTA System" described in the Universal Portal
 * Architecture doc (section 19): each module activates only the CTA types
 * that make sense for its business, instead of hard-coding
 * "if Flipkart show Buy Now" / "if OLX show Chat" branches in the app.
 *
 * To add a brand-new business module later (vehicles, jobs, events...):
 *   1. add its key + allowed CTAs here
 *   2. add 'vehicle' (etc.) to App\Models\Category::MODULES
 *   3. build the module the same way Product/Property/TourPackage/Service were built
 * No other core file needs to change.
 */
return [

    'ctas' => [
        'product' => ['ADD_TO_CART', 'BUY', 'ENQUIRE', 'CHAT'],
        'property' => ['CALL', 'ENQUIRE', 'SCHEDULE', 'WHATSAPP'],
        'tour_package' => ['BOOK', 'ENQUIRE', 'REQUEST_QUOTE', 'CALL'],
        'service' => ['BOOK', 'ENQUIRE', 'REQUEST_QUOTE', 'CALL', 'WHATSAPP'],
    ],

    // Default page size / max page size enforced by the public + admin listing engines.
    'per_page' => 15,
    'max_per_page' => 100,
];
