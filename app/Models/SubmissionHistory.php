<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmissionHistory extends Model
{
    use HasFactory;
    static $types = ['file', 'url'];
    static $statues =  ['pending', 'processing', 'completed', 'failed'];
    static $FILE = 'file';
    static $URL = 'url';
    static $PENDING = 'pending';
    static $PROCESSING = 'processing';
    static $COMPLETED = 'completed';
    static $FAILED = 'failed';

    protected $fillable = [
        'user_id',
        'project_id',
        'type',
        'path',
        'status',
        'results',
        'attempts',
        'port',
        'start',
        'end',
        'description'
    ];

    protected $casts = [
        'results' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function getResultsAttribute($value)
    {
        return json_decode($value);
    }

    public function setResultsAttribute($value)
    {
        $this->attributes['results'] = json_encode($value);
    }

    public function getFileAttribute()
    {
        return $this->getFirstMediaUrl('public_submissions_files');
    }

    public function isGithubUrl()
    {
        return $this->type == self::$URL;
    }

    public function submission()
    {
        return $this->belongsTo(SubmissionStatus::class);
    }

    public function getExecutionSteps()
    {
        if ($this->isGithubUrl()) {
            $steps = $this->project->projectExecutionSteps->filter(function ($step) {
                return $step->executionStep->name != 'Unzip ZIP Files' && $step->executionStep->name != 'Remove ZIP Files';
            });
        } else {
            $steps = $this->project->projectExecutionSteps->filter(function ($step) {
                return $step->executionStep->name != 'Clone Repository';
            });
        }
        // order the steps by their order
        $steps = $steps->sortBy('order');
        return $steps;
    }

    public function getTotalSteps()
    {
        return $this->getExecutionSteps()->count();
    }

    public function getTotalCompletedSteps()
    {
        $results = $this->results;
        $completed_steps = 0;
        if ($results != null) {
            foreach ($results as $result) {
                if ($result->status == self::$COMPLETED) {
                    $completed_steps++;
                }
            }
        }
        return $completed_steps;
    }
}
