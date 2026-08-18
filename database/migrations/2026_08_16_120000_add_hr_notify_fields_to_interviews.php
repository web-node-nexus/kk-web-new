<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interviews', function (Blueprint $table) {
            if (! Schema::hasColumn('interviews', 'meeting_link')) {
                $table->string('meeting_link')->nullable()->after('mode');
            }
            if (! Schema::hasColumn('interviews', 'hr_email')) {
                $table->string('hr_email')->nullable()->after('meeting_link');
            }
            if (! Schema::hasColumn('interviews', 'hr_notified_at')) {
                $table->timestamp('hr_notified_at')->nullable()->after('hr_email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('interviews', function (Blueprint $table) {
            foreach (['meeting_link', 'hr_email', 'hr_notified_at'] as $col) {
                if (Schema::hasColumn('interviews', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
