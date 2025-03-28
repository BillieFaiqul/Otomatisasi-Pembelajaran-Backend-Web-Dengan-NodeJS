<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectsDefaultFileStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'structure',
        'excluded',
        'replacements',
    ];

    protected $casts = [
        'structure' => 'array',
        'excluded' => 'array',
        'replacements' => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function getStructureAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setStructureAttribute($value)
    {
        $this->attributes['structure'] = json_encode($value);
    }

    public function getExcludedAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setExcludedAttribute($value)
    {
        $this->attributes['excluded'] = json_encode($value);
    }

    public function getReplacementsAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setReplacementsAttribute($value)
    {
        $this->attributes['replacements'] = json_encode($value);
    }
}
