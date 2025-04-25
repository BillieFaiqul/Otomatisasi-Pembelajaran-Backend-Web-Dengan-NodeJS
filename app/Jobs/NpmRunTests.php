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
                $env = [
                    'PATH' => config('app.process_path') . ':' . getenv('PATH'),
                ];

                $process = new Process($command, $this->tempDir, $env, null, 120);
                $process->setTty(false);
                $process->setPty(false);
                $output = '';
                $errorOutput = '';

                $process->run(function ($type, $buffer) use (&$output, &$errorOutput) {
                    if (Process::OUT === $type) {
                        $output .= $buffer;
                    } else {
                        $errorOutput .= $buffer;
                    }
                });

                $fullOutput = trim($output . "\n" . $errorOutput);

                $passedTests = null;
                $totalTests = null;
                $failedTests = null;

                // Handle format: "Tests: X failed, Y passed, Z total"
                if (preg_match('/Tests:\s+(\d+)\s+failed,\s+(\d+)\s+passed,\s+(\d+)\s+total/', $fullOutput, $matches)) {
                    $failedTests = (int)$matches[1];
                    $passedTests = (int)$matches[2];
                    $totalTests = (int)$matches[3];
                    Log::info("Extracted test metrics: $failedTests failed, $passedTests passed, $totalTests total");
                }
                // Handle format: "Tests: X passed, Y total"
                else if (preg_match('/Tests:\s+(\d+)\s+passed,\s+(\d+)\s+total/', $fullOutput, $matches)) {
                    $passedTests = (int)$matches[1];
                    $totalTests = (int)$matches[2];
                    $failedTests = $totalTests - $passedTests;
                    Log::info("Extracted test metrics: $passedTests passed, $totalTests total");
                }

                if ($process->isSuccessful()) {
                    $pass_all[$key] = true;
                    Log::info("{$command_string} completed in folder {$this->tempDir}");
                    $this->updateSubmissionTestsResultsStatus(
                        $command_string,
                        $submission,
                        Submission::$COMPLETED,
                        $fullOutput,
                        $passedTests,
                        $totalTests,
                        $failedTests
                    );
                } else {
                    $pass_all[$key] = false;
                    Log::error("Failed to NPM run test {$command_string}");
                    $this->updateSubmissionTestsResultsStatus(
                        $command_string,
                        $submission,
                        Submission::$FAILED,
                        $fullOutput,
                        $passedTests,
                        $totalTests,
                        $failedTests
                    );
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

    private function updateSubmissionTestsResultsStatus(
        $testName,
        Submission $submission,
        string $status,
        string $output,
        ?int $passedTests = null,
        ?int $totalTests = null,
        ?int $failedTests = null
    ): void {
        $stepName = ExecutionStep::$NPM_RUN_TESTS;
        $submission->updateOneTestResult($stepName, $testName, $status, $output, $passedTests, $totalTests, $failedTests);
        if ($status != Submission::$COMPLETED) $submission->updateStatus($status);
    }

    private function updateSubmissionStatus(Submission $submission, string $status, string $output): void
    {
        $stepName = ExecutionStep::$NPM_RUN_TESTS;
        if ($status != Submission::$PROCESSING) $submission->updateOneResult($stepName, $status, $output);
        if ($status != Submission::$COMPLETED) $submission->updateStatus($status);
    }
}
