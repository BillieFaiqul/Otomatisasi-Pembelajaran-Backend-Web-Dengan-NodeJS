<?php

namespace App\Jobs;

use App\Models\ExecutionStep;
use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class AddEnvFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $submission;
    public $envFile;
    public $tempDir;
    public $command;
    /**
     * Create a new job instance.
     */
    public function __construct($submission, $envFile, $tempDir, $command)
    {
        $this->submission = $submission;
        $this->envFile = $envFile;
        $this->tempDir = $tempDir;
        $this->command = $command;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $submission = $this->submission;
        Log::info("Adding env file {$this->envFile} into {$this->tempDir}");
        $this->updateSubmissionStatus($submission, Submission::$PROCESSING, "Adding env file");
        try {
            // processing
            $process = new Process($this->command);
            $process->start();
            $process_pid = $process->getPid();
            $process->wait();
            if ($process->isSuccessful()) {
                Log::info("Added env file {$this->envFile} into {$this->tempDir}");
                $this->updateSubmissionStatus($submission, Submission::$COMPLETED, "Added env file");
            } else {
                Log::error("Failed to add env file {$this->envFile} " . $process->getErrorOutput());
                $this->updateSubmissionStatus($submission, Submission::$FAILED, "Failed to add env file");
                Process::fromShellCommandline('kill ' . $process_pid)->run();
                Process::fromShellCommandline("rm -rf {$this->tempDir}")->run();
            }
        } catch (\Throwable $th) {
            // failed
            Log::error("Failed to add env file {$this->envFile} " . $th->getMessage());
            $this->updateSubmissionStatus($submission, Submission::$FAILED, "Failed to add env file");
        }
    }

    private function updateSubmissionStatus(Submission $submission, string $status, string $output): void
    {
        $stepName = ExecutionStep::$ADD_ENV_FILE;
        if ($status != Submission::$PROCESSING) $submission->updateOneResult($stepName, $status, $output);
        if ($status != Submission::$COMPLETED) $submission->updateStatus($status);
    }
}
