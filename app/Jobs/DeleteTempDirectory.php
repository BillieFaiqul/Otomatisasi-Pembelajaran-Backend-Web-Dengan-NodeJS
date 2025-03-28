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

class DeleteTempDirectory implements ShouldQueue
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
        Log::info("Deleting folder {$this->tempDir}");
        $this->updateSubmissionStatus($submission, Submission::$PROCESSING, "Deleting folder");
        try {
            // processing
            foreach ($this->command as $key => $value) {
                $process = new Process($value, null, null, null, 120);
                $process->start();
                $process_pid = $process->getPid();
                $process->wait();
                if ($process->isSuccessful()) {
                    Log::info('Command ' . implode(" ", $value) . ' is successful');
                } else {
                    Log::error("Failed to delete folder {$this->tempDir} "   . $process->getErrorOutput());
                    // $this->updateSubmissionStatus($submission, Submission::$FAILED, "Failed to delete folder");
                }
                Process::fromShellCommandline('kill ' . $process_pid)->run();
            }
            // completed
            Log::info("Deleted folder {$this->tempDir}");
            $this->updateSubmissionStatus($submission, Submission::$COMPLETED, "Deleted folder");
        } catch (\Throwable $th) {
            Log::error("Failed to delete folder {$this->tempDir} " . $th->getMessage());
            // $this->updateSubmissionStatus($submission, Submission::$FAILED, "Failed to delete folder");
        }
    }

    private function updateSubmissionStatus(Submission $submission, string $status, string $output): void
    {
        $stepName = ExecutionStep::$DELETE_TEMP_DIRECTORY;
        if ($status != Submission::$PROCESSING) $submission->updateOneResult($stepName, $status, $output);
        if ($status != Submission::$COMPLETED) $submission->updateStatus($status);
    }
}
