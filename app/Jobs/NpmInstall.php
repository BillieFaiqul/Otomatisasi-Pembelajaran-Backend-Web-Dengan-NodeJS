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

class NpmInstall implements ShouldQueue
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
        Log::info("NPM installing in folder {$this->tempDir}");
        $this->updateSubmissionStatus($submission, Submission::$PROCESSING, "NPM installing");
        try {
            // processing
            // check if the module is already exist within the assets folder
            $env = [
                'PATH' => config('app.process_path') . ':' . getenv('PATH'),
            ];
            $process = new Process($this->command, $this->tempDir, $env, null, 120);
            $process->start();
            $process_pid = $process->getPid();
            $process->wait();
            if ($process->isSuccessful()) {
                Log::info("NPM installed in folder {$this->tempDir}");
                $this->updateSubmissionStatus($submission, Submission::$COMPLETED, "NPM installed");
            } else {
                Log::error("Failed to NPM install in folder {$this->tempDir} "   . $process->getErrorOutput());
                $this->updateSubmissionStatus($submission, Submission::$FAILED, "Failed to NPM install");
                Process::fromShellCommandline('kill ' . $process_pid)->run();
                Process::fromShellCommandline("rm -rf {$this->tempDir}")->run();
                throw new \Exception($process->getErrorOutput());
            }
        } catch (\Throwable $th) {
            Log::error("Failed to NPM install in folder {$this->tempDir}" . $th->getMessage());
            $this->updateSubmissionStatus($submission, Submission::$FAILED, "Failed to NPM install");
            Process::fromShellCommandline("rm -rf {$this->tempDir}")->run();
        }
    }

    private function updateSubmissionStatus(Submission $submission, string $status, string $output): void
    {
        $stepName = ExecutionStep::$NPM_INSTALL;
        if ($status != Submission::$PROCESSING) $submission->updateOneResult($stepName, $status, $output);
        if ($status != Submission::$COMPLETED) $submission->updateStatus($status);
    }
}
