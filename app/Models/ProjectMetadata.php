<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class ProjectMetadata extends Model
{
    use HasFactory;

    protected $table = 'redcap_metadata';

    //
    public function projects(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // Mutator
    public function formName(): Attribute
    {
        return Attribute::make(
            fn ($value) => Str::headline($value)
        );
    }

    /**
     * Mutator
     */
    public function elementEnum(): Attribute
    {
        return Attribute::make(
            fn ($value) => Str::replace('\n', '<br />', $value)
        );
    }

    // /defining many to many

    // public function project_event_metadata(): BelongsToMany
    // {
    //   return $this->belongsToMany(ProjectEventMetadata::class,'redcap_events_forms','form_name', 'event_id');
    // }

    /**
     * Get field names scope
     */
    public function scopeGetFieldNames($query, $projectId)
    {
        $query->where('project_id', $projectId)
            ->select(
                'field_name',
                'form_name',
                'element_type',
                'element_enum',
                'element_validation_type',
                'element_validation_min',
                'element_validation_max'
            );

        return $query;

    }
}
