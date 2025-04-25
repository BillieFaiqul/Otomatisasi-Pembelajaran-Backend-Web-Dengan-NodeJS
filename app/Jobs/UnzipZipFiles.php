<?php

namespace App\Jobs;

use App\Models\ExecutionStep;
use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
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
            $this->prepareTempDirectory();

            $process = new Process(
                $this->command,
                null,
                $this->getEnvironment(),
                null,
                300
            );
            $process->start();
            $process_pid = $process->getPid();
            $process->wait();
            if ($process->isSuccessful()) {
                Log::info("Unzipped {$this->zipFileDir} into {$this->tempDir}");
                $this->updateSubmissionStatus($submission, Submission::$COMPLETED, "Unzipped submitted folder");
            } else {
                $error = "Failed to unzip: " . $process->getErrorOutput() . "\n" . $process->getOutput();
                Log::error($error);
                $this->cleanup();
                $this->updateSubmissionStatus($submission, Submission::$FAILED, $error);
            }
        } catch (\Throwable $th) {
            $error = "Failed to unzip {$this->zipFileDir} " . $th->getMessage();
            Log::error($error);
            $this->cleanup();
            $this->updateSubmissionStatus($submission, Submission::$FAILED, $error);
            Process::fromShellCommandline('kill ' . $process_pid)->run();
            Process::fromShellCommandline("rm -rf {$this->tempDir}")->run();
        }
    }

    protected function prepareTempDirectory(): void
    {
        File::ensureDirectoryExists($this->tempDir, 0755);

        File::cleanDirectory($this->tempDir);
    }

    protected function getEnvironment(): array
    {
        return [
            'PATH' => '/usr/local/bin:/usr/bin:/bin:' . getenv('PATH'),
            'HOME' => getenv('HOME') ?: '/tmp',
        ];
    }

    protected function cleanup(): void
    {
        try {
            if (File::exists($this->tempDir)) {
                File::deleteDirectory($this->tempDir);
            }
        } catch (\Throwable $th) {
            Log::error("Cleanup failed: " . $th->getMessage());
        }
    }

    private function updateSubmissionStatus(Submission $submission, string $status, string $output): void
    {
        $stepName = ExecutionStep::$UNZIP_ZIP_FILES;
        if ($status !== Submission::$PROCESSING) {
            $submission->updateOneResult($stepName, $status, $output);
        }
        if ($status !== Submission::$COMPLETED) {
            $submission->updateStatus($status);
        }
    }
}
