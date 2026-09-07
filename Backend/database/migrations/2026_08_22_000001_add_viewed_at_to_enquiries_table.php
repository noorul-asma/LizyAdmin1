<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            // Separate from `status` (new/contacted/converted/closed), which
            // tracks where a lead is in its lifecycle and only changes when
            // an admin deliberately edits it. viewed_at tracks something
            // different: has anyone even looked at this enquiry yet - it's
            // what the "New Enquiries" dashboard badge should reflect, so
            // opening the Enquiries page clears it without having to touch
            // (or guess at) the lead's actual status.
            $table->timestamp('viewed_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            $table->dropColumn('viewed_at');
        });
    }
};
