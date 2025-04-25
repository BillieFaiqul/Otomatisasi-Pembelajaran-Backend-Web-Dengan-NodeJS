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
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class CloneRepository implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $submission;
    public $repoUrl;
    public $tempDir;
    public $command;
    /**
     * Create a new job instance.
     */
    public function __construct($submission, $repoUrl, $tempDir, $command)
    {
        $this->submission = $submission;
        $this->repoUrl = $repoUrl;
        $this->tempDir = $tempDir;
        $this->command = $command;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $submission = $this->submission;
        Log::info("Cloning repo {$this->repoUrl} into {$this->tempDir}");
        $this->updateSubmissionStatus($submission, Submission::$PROCESSING, "Cloning repo {$this->repoUrl}");
        try {
            $this->prepareTempDirectory();

            // processing
            $process = new Process($this->command, null, null, null, 500);
            $process->start();
            $process_pid = $process->getPid();
            $process->wait();
            if ($process->isSuccessful()) {
                // completed
                Log::info("Cloned repo {$this->repoUrl} into {$this->tempDir}");
                $this->updateSubmissionStatus($submission, Submission::$COMPLETED, "Cloned repo {$this->repoUrl}");
            } else {
                // failed
                Log::error("Failed to clone repo {$this->repoUrl} " . $process->getErrorOutput());
                $this->updateSubmissionStatus($submission, Submission::$FAILED, "Failed to clone repo {$this->repoUrl}");
                Process::fromShellCommandline('kill ' . $process_pid)->run();
                Process::fromShellCommandline("rm -rf {$this->tempDir}")->run();
            }
        } catch (\Throwable $th) {
            // failed
            Log::error("Failed to clone repo {$this->repoUrl} " . $th->getMessage());
            $this->updateSubmissionStatus($submission, Submission::$FAILED, "Failed to clone repo {$this->repoUrl}");
            Process::fromShellCommandline("rm -rf {$this->tempDir}")->run();
        }
    }

    private function updateSubmissionStatus(Submission $submission, string $status, string $output): void
    {
        $stepName = ExecutionStep::$CLONE_REPOSITORY;
        if ($status != Submission::$PROCESSING) $submission->updateOneResult($stepName, $status, $output);
        if ($status != Submission::$COMPLETED) $submission->updateStatus($status);
    }

    private function prepareTempDirectory(): void
    {
        if (File::exists($this->tempDir)) {
            File::deleteDirectory($this->tempDir);
        }

        File::ensureDirectoryExists(dirname($this->tempDir), 0755, true);

        Log::info("Created temp directory {$this->tempDir}");
    }
}
