<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Project extends  Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'title',
        'description',
        'tech_stack',
        'github_url',
        'image',
    ];

    protected $casts = [
        'tech_stack' => 'array',
    ];

    public function defaultFileStructure(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProjectsDefaultFileStructure::class);
    }

    public function submissions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function projectExecutionSteps(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectExecutionStep::class);
    }

    public function getTechStackAttribute($value): array
    {
        return json_decode($value, true);
    }

    public function setTechStackAttribute($value): void
    {
        $this->attributes['tech_stack'] = json_encode($value);
    }

    public function getImageAttribute(): string
    {
        return $this->getFirstMediaUrl('project_images');
    }
}
