<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    //
    protected $table = 'redcap_projects';

    protected $primaryKey = 'project_id';

    public function project_data(): HasMany
    {
        return $this->hasMany(ProjectData3::class, 'project_id');
    }

    // removed has Many
    public function project_metadata(): HasMany
    {
        return $this->hasMany(ProjectMetadata::class, 'project_id');
    }

    public function scopeOrderByName($query)
    {
        $query->orderBy('app_title');
    }
}
