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

class CopyTestsFolder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $submission;
    public $testsDir;
    public $tempDir;
    public $command;
    /**
     * Create a new job instance.
     */
    public function __construct($submission, $testsDir, $tempDir, $command)
    {
        $this->submission = $submission;
        $this->testsDir = $testsDir;
        $this->tempDir = $tempDir;
        $this->command = $command;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $submission = $this->submission;
        Log::info("Copying tests folder to {$this->tempDir}");
        $this->updateSubmissionStatus($submission, Submission::$PROCESSING, "Copying tests folder");
        try {
            // Ensure tests directory exists
            if (!is_dir($this->tempDir . '/tests')) {
                mkdir($this->tempDir . '/tests', 0777, true);
            }

            // Execute all commands to copy test files
            foreach ($this->command as $command) {
                $process = new Process($command);
                $process->start();
                $process_pid = $process->getPid();
                $process->wait();

                if ($process->isSuccessful()) {
                    Log::info("Copied test file {$command[2]} to {$command[3]}");
                } else {
                    Log::error("Failed to copy test file {$command[2]} to {$command[3]}");
                    Log::error("Error: " . $process->getErrorOutput());
                    $this->updateSubmissionStatus($submission, Submission::$FAILED, "Failed to copy test files: " . $process->getErrorOutput());
                    Process::fromShellCommandline("rm -rf {$this->tempDir}")->run();
                    Process::fromShellCommandline('kill ' . $process_pid)->run();
                    throw new \Exception($process->getErrorOutput());
                }
            }

            // completed
            Log::info("Copied all test files to {$this->tempDir}");
            $this->updateSubmissionStatus($submission, Submission::$COMPLETED, "Copied test files successfully");
        } catch (\Throwable $th) {
            Log::error("Failed to copy test files to {$this->tempDir}: " . $th->getMessage());
            $this->updateSubmissionStatus($submission, Submission::$FAILED, "Failed to copy test files: " . $th->getMessage());
            Process::fromShellCommandline("rm -rf {$this->tempDir}")->run();
        }
    }

    private function updateSubmissionStatus(Submission $submission, string $status, string $output): void
    {
        $stepName = ExecutionStep::$COPY_TESTS_FOLDER;
        if ($status != Submission::$PROCESSING) $submission->updateOneResult($stepName, $status, $output);
        if ($status != Submission::$COMPLETED) $submission->updateStatus($status);
    }
}
