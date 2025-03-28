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
            // processing
            if (is_dir($this->tempDir . '/tests')) {
                Log::info("Removing old tests folder from {$this->tempDir}");
                Process::fromShellCommandline("rm -rf {$this->tempDir}/tests")->run();
            }
            mkdir($this->tempDir . '/tests', 0777, true);
            mkdir($this->tempDir . '/tests/api', 0777, true);
            mkdir($this->tempDir . '/tests/web', 0777, true);
            mkdir($this->tempDir . '/tests/web/images', 0777, true);
            foreach ($this->command as $key => $value) {
                $process = new Process($value);
                $process->start();
                $process_pid = $process->getPid();
                $process->wait();
                if ($process->isSuccessful()) {
                    Log::info("Copied tests {$value[2]} folder to {$value[3]}");
                } else {
                    Log::error("Failed to copying tests {$value[2]} folder to {$value[3]}");
                    $this->updateSubmissionStatus($submission, Submission::$FAILED, "Failed to copying tests folder");
                    Process::fromShellCommandline("rm -rf {$this->tempDir}")->run();
                    Process::fromShellCommandline('kill ' . $process_pid)->run();
                    throw new \Exception($process->getErrorOutput());
                }
            }
            // completed
            Log::info("Copied tests folder to {$this->tempDir}");
            $this->updateSubmissionStatus($submission, Submission::$COMPLETED, "Copied tests folder");
        } catch (\Throwable $th) {
            Log::error("Failed to copying tests folder to {$this->tempDir} " . $th->getMessage());
            $this->updateSubmissionStatus($submission, Submission::$FAILED, "Failed to copying tests folder");
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
