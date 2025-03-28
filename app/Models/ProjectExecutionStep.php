<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectExecutionStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'execution_step_id',
        'order',
        'variables',
    ];

    protected $casts = [
        'variables' => 'array',
    ];

    public function getVariablesAttribute($value)
    {
        return json_decode($value);
    }

    public function setVariablesAttribute($value)
    {
        $this->attributes['variables'] = json_encode($value);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function executionStep()
    {
        return $this->belongsTo(ExecutionStep::class);
    }
}
