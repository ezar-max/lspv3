<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterProductSpecification extends Model
{
    use HasFactory;

    protected $table = 'master_product_specifications';

    protected $fillable = [
        'scheme_master_instrument_id',
        'spec_name',
        'standard_tolerance',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function schemeMasterInstrument()
    {
        return $this->belongsTo(SchemeMasterInstrument::class, 'scheme_master_instrument_id');
    }
}
