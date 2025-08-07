<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ProjectEventForm extends Model
{
    use HasFactory;

    protected $table = 'redcap_events_forms';

    public function redcap_events_metadata(): HasMany
    {
        return $this->hasMany(ProjectEventMetadata::class);
    }

    // Mutator
    public function formName(): Attribute
    {
        return Attribute::make(
            fn ($value) => Str::headline($value)
        );
    }
}
