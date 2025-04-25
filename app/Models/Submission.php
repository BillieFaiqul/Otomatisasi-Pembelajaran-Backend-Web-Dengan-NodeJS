<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Submission extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;
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

    public function history()
    {
        return $this->hasMany(SubmissionHistory::class);
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

    public function initializeResults()
    {
        $results = [];
        $steps = $this->getExecutionSteps();
        foreach ($steps as $step) {
            if ($step->executionStep->name == ExecutionStep::$NPM_RUN_TESTS) {
                $tests = $this->project->projectExecutionSteps->where('execution_step_id', $step->executionStep->id)->first()->variables;
                $testResults = [];
                $order = 0;
                foreach ($tests as $testCommandValue) {
                    $order = $order + 1;
                    $command = implode(" ", $step->executionStep->commands);
                    $key = explode("=", $testCommandValue)[0];
                    $value = explode("=", $testCommandValue)[1];
                    $testName = str_replace($key, $value, $command);
                    $testResults[$testName] = [
                        'status' => self::$PENDING,
                        'output' => '',
                        'order' => $order,
                    ];
                }
                $results[$step->executionStep->name] = [
                    'stepID' => $step->id,
                    'status' => self::$PENDING,
                    'order'  => $step->order,
                    'output' => '',
                    'testResults' => $testResults,
                ];
            } else {
                $results[$step->executionStep->name] = [
                    'stepID' => $step->id,
                    'status' => self::$PENDING,
                    'order'  => $step->order,
                    'output' => '',
                ];
            }
        }
        $this->updateResults($results);
    }

    public function increaseAttempts()
    {
        $this->attempts = $this->attempts + 1;
        $this->save();
    }

    public function updateStatus($status)
    {
        $this->status = $status;
        if ($status == self::$FAILED || self::$COMPLETED) $this->end = now();
        $this->save();
    }

    public function restartTime()
    {
        $this->start    = now();
        $this->end      = null;
        $this->save();
    }

    public function updateOneResult($step_name, $status, $output)
    {
        $results = $this->results;
        $results->$step_name->status = $status;
        $results->$step_name->output = $output;
        $this->updateResults($results);
    }

    public function updateOneTestResult(
        $step_name,
        $test_name,
        $status,
        $output,
        ?int $passedTests = null,
        ?int $totalTests = null,
        ?int $failedTests = null
    ) {
        $results = $this->results;
        $results->$step_name->testResults->$test_name->status = $status;
        $results->$step_name->testResults->$test_name->output = $output;

        if ($passedTests !== null && $totalTests !== null) {
            $results->$step_name->testResults->$test_name->passedTests = $passedTests;
            $results->$step_name->testResults->$test_name->totalTests = $totalTests;
            $results->$step_name->testResults->$test_name->failedTests = $failedTests;
        }

        $this->updateResults($results);
    }

    public function updateResults($results)
    {
        $this->results = $results;
        $this->save();
    }

    public function updatePort($port)
    {
        $this->port = $port;
        $this->save();
    }

    public function getCurrentExecutionStep($step_id = null)
    {
        $steps = $this->getExecutionSteps();
        if ($step_id) {
            $current_step = $steps->first(function ($step) use ($step_id) {
                return $step->id == $step_id;
            });
            return $current_step;
        } else {
            $results = $this->results;
            $current_step = null;
            if (!$results) {
                return $current_step;
            }

            foreach ($steps as $step) {
                if (
                    $results->{$step->executionStep->name}?->status == self::$PROCESSING ||
                    $results->{$step->executionStep->name}?->status == self::$PENDING
                ) {
                    $current_step = $step;
                    break;
                }
            }

            if (!$current_step) {
                $have_failed_steps = false;
                $all_completed = true;

                foreach ($steps as $step) {
                    if (
                        !isset($results->{$step->executionStep->name}) ||
                        $results->{$step->executionStep->name}?->status == self::$PENDING ||
                        $results->{$step->executionStep->name}?->status == self::$PROCESSING
                    ) {
                        $all_completed = false;
                        break;
                    }

                    if ($results->{$step->executionStep->name}?->status == self::$FAILED) {
                        $have_failed_steps = true;
                    }
                }

                if ($all_completed) {
                    if ($have_failed_steps) {
                        $this->updateStatus(self::$FAILED);
                    } else {
                        $this->updateStatus(self::$COMPLETED);
                    }
                }
            }

            return $current_step;
        }
    }

    public function getNextExecutionStep($step_id = null)
    {
        if ($step_id == null) return null;
        $next_step = null;
        $steps = $this->getExecutionSteps();
        $current_step = $this->getCurrentExecutionStep($step_id);
        if ($current_step) {
            $current_step_index = $steps->search(function ($step) use ($current_step) {
                return $step->id == $current_step->id;
            });
            if ($current_step_index < $steps->count()) {
                $next_step = $steps[$current_step_index + 1];
            }
        }
        return $next_step;
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
                if ($result->status == self::$COMPLETED || $result->status == self::$FAILED) {
                    $completed_steps++;
                }
            }
        }
        return $completed_steps;
    }

    public function restartAfterNpmInstall()
    {
        $step = ProjectExecutionStep::where('project_id', $this->project_id)->where('execution_step_id', ExecutionStep::where('name', ExecutionStep::$NPM_INSTALL)->first()->id)->first();
        $next_step = $this->getNextExecutionStep($step->id ?? null);
        // update results to pending
        while ($next_step) {
            if ($next_step->executionStep->name == ExecutionStep::$NPM_RUN_TESTS) {
                $tests = $this->project->projectExecutionSteps->where('execution_step_id', $next_step->executionStep->id)->first()->variables;
                foreach ($tests as $testCommandValue) {
                    $command = implode(" ", $next_step->executionStep->commands);
                    $key = explode("=", $testCommandValue)[0];
                    $value = explode("=", $testCommandValue)[1];
                    $testName = str_replace($key, $value, $command);
                    $this->updateOneTestResult($next_step->executionStep->name, $testName, Submission::$PENDING, " ");
                }
            }
            $this->updateOneResult($next_step->executionStep->name, Submission::$PENDING, " ");
            $next_step = $this->getNextExecutionStep($next_step->id ?? null);
        }
        $this->updateStatus(Submission::$PROCESSING);
    }

    public function getTotalAttemptsCount()
    {
        return $this->history->count() + 1;
    }

    public function restart()
    {
        $this->updateStatus(Submission::$PENDING);
        $this->increaseAttempts();
        $this->updatePort(null);
        $this->restartTime();
        $this->initializeResults();
    }

    public function createHistory($description)
    {
        // use the method attach to attach the history to the submission
        $history = $this->history()->create([
            'user_id' => $this->user_id,
            'project_id' => $this->project_id,
            'type' => $this->type,
            'path' => $this->path,
            'status' => $this->status,
            'results' => $this->results,
            'attempts' => $this->attempts,
            'port' => $this->port,
            'start' => $this->start,
            'end' => $this->end,
            'description' => $description,
        ]);

        return $history;
    }
}
