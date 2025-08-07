<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectEventArm extends Model
{
    use HasFactory;

    protected $table = 'redcap_events_arms';

    protected $primaryKey = 'arm_id';

    public function projects(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    //
    //
    public function project_events_metadata(): HasMany
    {
        return $this->hasMany(ProjectEventMetadata::class, 'arm_id');
    }
}
