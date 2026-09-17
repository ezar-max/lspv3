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
        Schema::create('master_question_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheme_master_instrument_id')->constrained('scheme_master_instruments')->onDelete('cascade');
            $table->foreignId('kuk_id')->nullable()->constrained('kriteria_unjuk_kerja')->onDelete('set null');
            $table->string('question_type', 30)->default('multiple_choice'); // multiple_choice, essay, oral
            $table->longText('question_text');
            $table->string('image_path')->nullable();
            $table->json('options')->nullable(); // format {"A": "...", "B": "...", "C": "...", "D": "..."}
            $table->longText('correct_answer'); // Opsi kunci 'A'/'B'/etc atau rujukan esai/lisan
            $table->text('rubric_guide')->nullable(); // Pembahasan, rubrik penilaian asesor
            $table->integer('points')->default(1);
            $table->integer('order')->default(1);
            $table->timestamps();

            $table->index(['scheme_master_instrument_id', 'question_type'], 'idx_mqb_inst_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_question_banks');
    }
};
