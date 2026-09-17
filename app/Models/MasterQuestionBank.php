<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterQuestionBank extends Model
{
    use HasFactory;

    protected $table = 'master_question_banks';

    protected $fillable = [
        'scheme_master_instrument_id',
        'kuk_id',
        'question_type',
        'question_text',
        'image_path',
        'options',
        'correct_answer',
        'rubric_guide',
        'points',
        'order',
    ];

    protected $casts = [
        'options' => 'array',
        'points' => 'integer',
        'order' => 'integer',
    ];

    public function schemeMasterInstrument()
    {
        return $this->belongsTo(SchemeMasterInstrument::class, 'scheme_master_instrument_id');
    }

    public function kriteriaUnjukKerja()
    {
        return $this->belongsTo(KriteriaUnjukKerja::class, 'kuk_id');
    }
}
