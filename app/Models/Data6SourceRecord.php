<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Data6SourceRecord extends Model
{
    protected $table = 'data6_source_records';

    protected $guarded = [];

    public function encounters(): HasMany
    {
        return $this->hasMany(Data6Encounter::class, 'source_record_id');
    }

    public function patients(): BelongsToMany
    {
        return $this->belongsToMany(Data6Patient::class, 'data6_patient_source_records', 'source_record_id', 'patient_id')
            ->withPivot(['match_method', 'match_confidence', 'review_status'])
            ->withTimestamps();
    }
}