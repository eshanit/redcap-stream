<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectData3 extends Model
{
    //

    protected $table = 'redcap_data3';

    public function projects(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function project_event_metadata(): BelongsTo
    {
        return $this->belongsTo(ProjectEventMetadata::class, 'event_id', 'event_id');
    }

    public function project_metadata(): BelongsTo
    {
        return $this->belongsTo(ProjectMetadata::class, 'field_name', 'field_name');
    }
}
