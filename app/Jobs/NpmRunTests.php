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

class NpmRunTests implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $submission;
    public $tempDir;
    public $command;
    /**
     * Create a new job instance.
     */
    public function __construct($submission, $tempDir, $command)
    {
        $this->submission = $submission;
        $this->tempDir = $tempDir;
        $this->command = $command;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $submission = $this->submission;
        Log::info("NPM running tests in folder {$this->tempDir}");
        $this->updateSubmissionStatus($submission, Submission::$PROCESSING, "NPM running tests");
        try {
            // processing
            $pass_all = [];
            $commands = $this->command;
            foreach ($commands as $key =>  $command) {
                $command_string = implode(" ", $command);
                Log::info("Running {$command_string} in folder {$this->tempDir}");
                $this->updateSubmissionTestsResultsStatus($command_string, $submission, Submission::$PROCESSING, "Running");
                usleep(100000);
                $process = new Process($command, $this->tempDir, null, null, 120);
                $process->start();
                $process_pid = $process->getPid();
                $process->wait();
                if ($process->isSuccessful()) {
                    $pass_all[$key] = true;
                    Log::info("{$command_string} in folder {$this->tempDir}");
                    $this->updateSubmissionTestsResultsStatus($command_string, $submission, Submission::$COMPLETED, "Completed");
                } else {
                    $pass_all[$key] = false;
                    Log::error("Failed to NPM run test {$command_string} "   . $process->getErrorOutput());
                    $this->updateSubmissionTestsResultsStatus($command_string, $submission, Submission::$FAILED, $process->getErrorOutput());
                    Process::fromShellCommandline('kill ' . $process_pid)->run();
                }
            }
            if (in_array(false, $pass_all) == false) {
                Log::info("NPM ran tests in folder {$this->tempDir}");
                $this->updateSubmissionStatus($submission, Submission::$COMPLETED, "NPM tested");
            } else {
                Log::info("NPM failed to run tests in folder {$this->tempDir}");
                $this->updateSubmissionStatus($submission, Submission::$FAILED, "Failed to run NPM tests");
                if ($submission->port) Process::fromShellCommandline("npx kill-port $submission->port")->run();
            }
        } catch (\Throwable $th) {
            Log::error("Failed to NPM run tests in folder {$this->tempDir} " . $th->getMessage());
            $this->updateSubmissionStatus($submission, Submission::$FAILED, "Failed to NPM running tests");
            Process::fromShellCommandline("rm -rf {$this->tempDir}")->run();
        }
    }

    private function updateSubmissionTestsResultsStatus($testName, Submission $submission, string $status, string $output): void
    {
        $stepName = ExecutionStep::$NPM_RUN_TESTS;
        $submission->updateOneTestResult($stepName, $testName, $status, $output);
        if ($status != Submission::$COMPLETED) $submission->updateStatus($status);
    }

    private function updateSubmissionStatus(Submission $submission, string $status, string $output): void
    {
        $stepName = ExecutionStep::$NPM_RUN_TESTS;
        if ($status != Submission::$PROCESSING) $submission->updateOneResult($stepName, $status, $output);
        if ($status != Submission::$COMPLETED) $submission->updateStatus($status);
    }
}
