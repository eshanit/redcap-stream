<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Data6Encounter extends Model
{
    protected $table = 'data6_encounters';

    protected $guarded = [];

    protected $casts = [
        'service_date' => 'date:Y-m-d',
        'source_fields' => 'array',
    ];

    public function sourceRecord(): BelongsTo
    {
        return $this->belongsTo(Data6SourceRecord::class, 'source_record_id');
    }
}