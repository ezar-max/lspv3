<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('master_product_specifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scheme_master_instrument_id');
            $table->foreign('scheme_master_instrument_id', 'fk_mps_instrument_id')->references('id')->on('scheme_master_instruments')->onDelete('cascade');
            $table->string('spec_name');
            $table->text('standard_tolerance');
            $table->integer('order')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_product_specifications');
    }
};
