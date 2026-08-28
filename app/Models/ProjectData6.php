<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProjectData6 extends Model
{
    protected $table = 'redcap_data6';

    public $timestamps = false;

    public function scopeForProjects(Builder $query, array $projectIds): Builder
    {
        return $query->whereIn('project_id', $projectIds);
    }

    public function scopeForRecord(Builder $query, string $record): Builder
    {
        return $query->where('record', $record);
    }

    public function scopeForField(Builder $query, string $fieldName): Builder
    {
        return $query->where('field_name', $fieldName);
    }
}