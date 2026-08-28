<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Data6Patient extends Model
{
    protected $table = 'data6_patients';

    protected $guarded = [];

    public function sourceRecords(): BelongsToMany
    {
        return $this->belongsToMany(Data6SourceRecord::class, 'data6_patient_source_records', 'patient_id', 'source_record_id')
            ->withPivot(['match_method', 'match_confidence', 'review_status'])
            ->withTimestamps();
    }
}