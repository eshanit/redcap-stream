<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectEventRepeat extends Model
{
    use HasFactory;

    protected $table = 'redcap_events_repeat';

    public function project_events_metadata(): BelongsTo
    {
        return $this->belongsTo(ProjectEventMetadata::class, 'event_id');
    }
}
