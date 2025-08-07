<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Respondent extends Model
{
    use HasFactory;

    protected $table = 'redcap_data3';

    protected $primaryKey = 'record';

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    public function data()
    {
        return $this->hasMany(ProjectData3::class, 'record', 'record');
    }
}
