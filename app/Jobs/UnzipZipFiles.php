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

class UnzipZipFiles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $submission;
    public $zipFileDir;
    public $tempDir;
    public $command;
    /**
     * Create a new job instance.
     */
    public function __construct($submission, $zipFileDir, $tempDir, $command)
    {
        $this->submission = $submission;
        $this->zipFileDir = $zipFileDir;
        $this->tempDir = $tempDir;
        $this->command = $command;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $submission = $this->submission;
        Log::info("Unzipping {$this->zipFileDir} into {$this->tempDir}");
        $this->updateSubmissionStatus($submission, Submission::$PROCESSING, "Unzipping submitted folder");
        try {
            // processing
            $process = new Process($this->command);
            $process->start();
            $process_pid = $process->getPid();
            $process->wait();
            if ($process->isSuccessful()) {
                Log::info("Unzipped {$this->zipFileDir} into {$this->tempDir}");
                $this->updateSubmissionStatus($submission, Submission::$COMPLETED, "Unzipped submitted folder");
            } else {
                Log::error("Failed to unzip {$this->zipFileDir} " . $process->getErrorOutput());
                $this->updateSubmissionStatus($submission, Submission::$FAILED, "Failed to unzip submitted folder");
                Process::fromShellCommandline('kill ' . $process_pid)->run();
                Process::fromShellCommandline("rm -rf {$this->tempDir}")->run();
            }
        } catch (\Throwable $th) {
            // failed
            Log::error("Failed to unzip {$this->zipFileDir} " . $th->getMessage());
            $this->updateSubmissionStatus($submission, Submission::$FAILED, "Failed tp unzip submitted folder");
            Process::fromShellCommandline("rm -rf {$this->tempDir}")->run();
        }
    }

    private function updateSubmissionStatus(Submission $submission, string $status, string $output): void
    {
        $stepName = ExecutionStep::$UNZIP_ZIP_FILES;
        if ($status != Submission::$PROCESSING) $submission->updateOneResult($stepName, $status, $output);
        if ($status != Submission::$COMPLETED) $submission->updateStatus($status);
    }
}
