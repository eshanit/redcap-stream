<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

// use Illuminate\Database\Eloquent\Casts\Attribute;
// use Illuminate\Support\Str;

class ProjectEventMetadata extends Model
{
    use HasFactory;

    protected $table = 'redcap_events_metadata';

    protected $primaryKey = 'event_id';

    //

    public function project_data(): HasMany
    {
        return $this->HasMany(ProjectData3::class);
    }
    //

    public function project_events_arms(): BelongsTo
    {
        return $this->belongsTo(ProjectEventArm::class);
    }

    //
    public function project_events_forms(): BelongsTo
    {
        return $this->belongsTo(ProjectEventForm::class);
    }

    //

    public function project_events_repeats(): HasMany
    {
        return $this->hasMany(ProjectEventRepeat::class, 'event_id');
    }

    // /defining many to many

    // public function project_metadata(): BelongsToMany
    // {
    //   return $this->belongsToMany(ProjectMetadata::class,'redcap_events_forms','event_id', 'form_name');
    // }

}
