<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scheme_master_instruments', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('additional_metadata')->constrained('pengguna')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('scheme_master_instruments', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });
    }
};
