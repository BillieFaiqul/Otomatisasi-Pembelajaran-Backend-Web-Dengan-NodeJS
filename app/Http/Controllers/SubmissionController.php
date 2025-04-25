<?php

namespace App\Http\Controllers;

use App\Jobs\AddEnvFile;
use App\Jobs\CloneRepository;
use App\Jobs\CopyTestsFolder;
use App\Jobs\DeleteTempDirectory;
use App\Jobs\ExamineFolderStructure;
use App\Jobs\NpmInstall;
use App\Jobs\NpmRunStart;
use App\Jobs\NpmRunTests;
use App\Jobs\ReplacePackageJson;
use App\Jobs\UnzipZipFiles;
use App\Models\ExecutionStep;
use App\Models\Project;
use App\Models\Submission;
use App\Models\SubmissionHistory;
use App\Models\TemporaryFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Yajra\DataTables\DataTables;

class SubmissionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $projects = Project::all();
        if ($request->ajax()) {
            $data = DB::table('projects')
                ->select(
                    'projects.id',
                    'projects.title',
                    DB::raw('(SELECT COUNT(*) FROM submission_histories INNER JOIN submissions ON submissions.id = submission_histories.submission_id WHERE submissions.project_id = projects.id AND submissions.user_id = ?) as attempts_count'),
                    DB::raw('(SELECT status FROM submissions WHERE submissions.project_id = projects.id AND submissions.user_id = ? ORDER BY id DESC LIMIT 1) as submission_status')
                )
                ->groupBy('projects.id', 'projects.title')
                ->setBindings([
                    $user->id,
                    $user->id
                ]);

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('title', function ($row) {
                    $title_button = '<a href="/submissions/project/' . $row->id . '" class="underline text-secondary">' . $row->title . '</a>';
                    return $title_button;
                })
                ->addColumn('submission_status', function ($row) {
                    $status = $row->submission_status ?? 'No Submission';
                    $status_color = ($status == 'completed') ? 'green' : (($status == 'pending') ? 'blue' : (($status == 'processing') ? 'secondary' : 'red'));
                    $status_button = $status != 'No Submission' ? '<span class="inline-flex items-center justify-center px-2 py-1 rounded-lg text-xs font-bold leading-none bg-' . $status_color . '-100 text-' . $status_color . '-800">' . ucfirst($status) . '</span>'
                        : '<span class="inline-flex items-center justify-center px-2 py-1 rounded-lg text-xs font-bold leading-none bg-gray-100 text-gray-800">No Submission</span>';
                    return $status_button;
                })
                ->addColumn('action', function ($row) use ($user) {
                    $submission = Submission::where('project_id', $row->id)->where('user_id', $user->id)->orderBy('id', 'DESC')->first();
                    $buttons = '
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
                        <div @click="open = ! open">
                            <button
                                class="flex items-center text-sm font-medium text-gray-900 hover:text-gray-500 dark:text-white dark:hover:text-gray-300 hover:underline">
                                <svg class="ml-1 h-5 w-5 text-gray-500 dark:text-gray-400"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                    aria-hidden="true">
                                    <g id="Menu / Menu_Alt_02">
                                        <path id="Vector" d="M11 17H19M5 12H19M11 7H19" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </g>
                                </svg>
                            </button>
                        </div>
                        <div x-show="open"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute z-50 mt-2 w-48 rounded-md shadow-lg origin-top"
                            style="display: none;"
                            @click="open = false">
                        <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white dark:bg-gray-700">
                    ';
                    if ($submission !== null) {

                        $deleteButton = ' <a data-submission-id="' . $submission->id . '"  data-request-type="delete" onclick="requestServer($(this))" class="block w-full px-4 py-2 text-left text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out">Delete submission</a> ';
                        $restartButton = ' <a data-submission-id="' . $submission->id . '"  data-request-type="restart" onclick="requestServer($(this))" class="block w-full px-4 py-2 text-left text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out">Restart submission</a> ';
                        $changeSourceCodeButton = ' <a data-submission-id="' . $submission->id . '"  data-request-type="change-source-code" onclick="requestServer($(this))" class="block w-full px-4 py-2 text-left text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out">Change source code</a> ';
                        if ($submission->status == 'failed' || $submission->status == 'pending') {
                            if (!$submission->isGithubUrl()) {
                                $buttons .= $restartButton . $changeSourceCodeButton . $deleteButton . '</div></div>';
                            } else {
                                $buttons .= $restartButton . $deleteButton . '</div></div>';
                            }
                        } else if ($submission->status == 'processing') {
                            $buttons .= $restartButton . $deleteButton . '</div></div>';
                        } else if ($submission->status == 'completed') {
                            $buttons .= $deleteButton . '</div></div>';
                        } else {
                            $buttons .= '</div></div>';
                        }
                    } else {
                        $buttons = '';
                    }
                    return $buttons;
                })
                ->editColumn('attempts_count', function ($row) {
                    $attempts_count = $row->attempts_count ?? 0;
                    return $attempts_count + 1;
                })
                ->rawColumns(['title', 'submission_status', 'action'])
                ->make(true);
        }
        return view('submissions.index', compact('projects'));
    }



    public function upload(Request $request, $project_id)
    {
        if ($request->hasFile('folder_path')) {
            $project_title = Project::find($project_id)->title;

            $file = $request->file('folder_path');
            $file_name = $file->getClientOriginalName();
            $folder_path = 'public/tmp/submissions/' . $request->user()->id . '/' . $project_title;
            $file->storeAs($folder_path, $file_name);

            TemporaryFile::create([
                'folder_path' => $folder_path,
                'file_name' => $file_name,
            ]);

            return $folder_path;
        }
        return '';
    }

    public function submit(Request $request)
    {

        try {
            $request->validate([
                'project_id' => 'required|exists:projects,id',
                'folder_path' => 'required_without:github_url',
                'github_url' => 'required_without:folder_path',
            ]);

            if (Submission::where('project_id', $request->project_id)->where('user_id', $request->user()->id)->exists()) {
                return response()->json([
                    'message' => 'Submission already exists',
                ], 400);
            }

            $submission = new Submission();
            $submission->user_id = $request->user()->id;
            $submission->project_id = $request->project_id;
            if ($request->has('folder_path')) {
                $submission->type = Submission::$FILE;
                $submission->path = $request->folder_path;

                $temporary_file = TemporaryFile::where('folder_path', $request->folder_path)->first();

                if ($temporary_file) {
                    $path = storage_path('app/' . $request->folder_path . '/' . $temporary_file->file_name);
                    $submission->addMedia($path)->toMediaCollection('submissions', 'public_submissions_files');
                    if ($this->is_dir_empty(storage_path('app/' . $request->folder_path))) {
                        rmdir(storage_path('app/' . $request->folder_path));
                    }
                    $temporary_file->delete();
                }
            } else {
                $submission->type = Submission::$URL;
                $submission->path = $request->github_url;
            }
            $submission->status = Submission::$PENDING;
            $submission->start = now();
            $submission->save();


            return response()->json([
                'message' => 'Submission created successfully',
                'submission' => $submission,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Submission failed',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function showAllSubmissionsBasedOnProject(Request $request, $project_id)
    {
        $project = Project::find($project_id);
        $submissions = Submission::where('project_id', $project_id)
            ->where('user_id', $request->user()->id)->get();
        $submission_history = SubmissionHistory::whereIn('submission_id', $submissions->pluck('id')->toArray())->get();

        if (!$project) {
            return redirect()->route('submissions');
        }
        return view('submissions.show', compact('project', 'submissions', 'submission_history'));
    }

    public function show(Request $request, $submission_id)
    {
        $user = Auth::user();
        $submission = Submission::where('id', $request->submission_id)->where('user_id', $user->id)->first();
        if ($submission) {
            $steps = $submission->getExecutionSteps();
            return view('submissions.show', compact('submission', 'steps'));
        }
        return redirect()->route('submissions');
    }

    public function history(Request $request, $history_id)
    {
        $user = Auth::user();
        $submission = SubmissionHistory::where('id', $history_id)->where('user_id', $user->id)->first();
        if ($submission) {
            $steps = $submission->getExecutionSteps();
            return view('submissions.show', compact('submission', 'steps'));
        }
        return redirect()->route('submissions');
    }

    public function status(Request $request, $submission_id)
    {
        $isNotHistory = filter_var($request->isNotHistory, FILTER_VALIDATE_BOOLEAN);
        $user = Auth::user();
        $submission = $isNotHistory ?  Submission::where('id', $submission_id)->where('user_id', $user->id)->first() : SubmissionHistory::where('id', $submission_id)->where('user_id', $user->id)->first();
        if (!$submission) {
            return response()->json([
                'message' => 'Submission not found',
            ], 404);
        }
        $completion_percentage = round($submission->getTotalCompletedSteps() / $submission->getTotalSteps() * 100);
        if ($submission->status === Submission::$PENDING) {
            return $this->returnSubmissionResponse(($isNotHistory ? "Submission is processing"  : "History"), $submission->status, $submission->results, $currentStep ?? null, $completion_percentage);
        } else if ($submission->status === Submission::$FAILED) {
            return $this->returnSubmissionResponse(($isNotHistory ?   "Submission has failed" : "History"), $submission->status, $submission->results, null, $completion_percentage);
        } else if ($submission->status === Submission::$COMPLETED) {
            return $this->returnSubmissionResponse(($isNotHistory ?  "Submission has completed" : "History"), $submission->status, $submission->results, null, $completion_percentage);
        } else if ($submission->status === Submission::$PROCESSING) {
            $step = $isNotHistory ? $submission->getCurrentExecutionStep() : null;
            if ($step) {
                return $this->returnSubmissionResponse(
                    $isNotHistory ?  'Step ' . $step->executionStep->name . ' is ' . $submission->results->{$step->executionStep->name}->status : "History",
                    $submission->status,
                    $submission->results,
                    $step,
                    $completion_percentage
                );
            }
            return $this->returnSubmissionResponse(
                ($isNotHistory ?  'Submission is processing meanwhile there is no step to execute' : "History"),
                $submission->status,
                $submission->results,
                $step,
                $completion_percentage
            );
        }
    }

    public function process(Request $request)
    {
        if ($request->submission_id == null || $request->isNotHistory == null) return response()->json([
            'message' => 'Submission ID is required',
        ], 404);

        $isNotHistory = filter_var($request->isNotHistory, FILTER_VALIDATE_BOOLEAN);
        $user = Auth::user();
        $submission = $isNotHistory ?  Submission::where('id', $request->submission_id)->where('user_id', $user->id)->first() : SubmissionHistory::where('id', $request->submission_id)->where('user_id', $user->id)->first();

        if ($submission) {
            $completion_percentage = round($submission->getTotalCompletedSteps() / $submission->getTotalSteps() * 100);
            if ($submission->status === Submission::$PENDING) {
                if ($isNotHistory) {
                    $submission->initializeResults();
                    $submission->updateStatus(Submission::$PROCESSING);
                    $currentStep = $submission->getCurrentExecutionStep();
                }
                return $this->returnSubmissionResponse(($isNotHistory ? "Submission is processing"  : "History"), $submission->status, $submission->results, $currentStep ?? null, $completion_percentage);
            } else if ($submission->status === Submission::$COMPLETED) {
                return $this->returnSubmissionResponse(($isNotHistory ?  "Submission has completed" : "History"), $submission->status, $submission->results, null, $completion_percentage);
            } else if ($submission->status === Submission::$FAILED) {
                return $this->returnSubmissionResponse(($isNotHistory ?   "Submission has failed" : "History"), $submission->status, $submission->results, null, $completion_percentage);
            } else if ($submission->status === Submission::$PROCESSING) {
                $step = $isNotHistory ? $submission->getCurrentExecutionStep() : null;
                if ($step) {
                    if ($submission->results->{$step->executionStep->name}->status == Submission::$PENDING) {
                        $submission->updateOneResult($step->executionStep->name, Submission::$PROCESSING, " ");
                        switch ($step->executionStep->name) {
                            case ExecutionStep::$CLONE_REPOSITORY:
                                $this->lunchCloneRepositoryJob($submission, $submission->path, $this->getTempDir($submission), $step);
                                break;
                            case ExecutionStep::$UNZIP_ZIP_FILES:
                                $zipFileDir = $submission->getMedia('submissions')->first()->getPath();
                                $this->lunchUnzipZipFilesJob($submission, $zipFileDir, $this->getTempDir($submission), $step);
                                break;
                            case ExecutionStep::$EXAMINE_FOLDER_STRUCTURE:
                                $this->lunchExamineFolderStructureJob($submission, $this->getTempDir($submission), $step);
                                break;
                            case ExecutionStep::$ADD_ENV_FILE:
                                $envFile = $submission->project->getMedia('project_files')->where('file_name', '.env')->first()->getPath();
                                $this->lunchAddEnvFileJob($submission, $envFile, $this->getTempDir($submission), $step);
                                break;
                            case ExecutionStep::$REPLACE_PACKAGE_JSON:
                                $packageJson = $submission->project->getMedia('project_files')->where('file_name', 'package.json')->first()->getPath();
                                $this->lunchReplacePackageJsonJob($submission, $packageJson, $this->getTempDir($submission), $step);
                                break;
                            case ExecutionStep::$COPY_TESTS_FOLDER:
                                $this->lunchCopyTestsFolderJob($submission, $this->getTempDir($submission), $step);
                                break;
                            case ExecutionStep::$NPM_INSTALL:
                                $this->lunchNpmInstallJob($submission, $this->getTempDir($submission), $step);
                                break;
                            case ExecutionStep::$NPM_RUN_START:
                                $this->lunchNpmRunStartJob($submission, $this->getTempDir($submission), $step);
                                break;
                            case ExecutionStep::$NPM_RUN_TESTS:
                                $this->lunchNpmRunTestsJob($submission, $this->getTempDir($submission), $step);
                                break;
                            case ExecutionStep::$DELETE_TEMP_DIRECTORY:
                                $this->lunchDeleteTempDirectoryJob($submission, $this->getTempDir($submission), $step);
                                break;
                            default:
                                break;
                        }
                    }

                    // Revalidate status before returning response to ensure consistency
                    if ($isNotHistory && $submission->status === Submission::$PROCESSING) {
                        // Force a fresh check of status based on current step results
                        $submission->getCurrentExecutionStep();
                        $completion_percentage = round($submission->getTotalCompletedSteps() / $submission->getTotalSteps() * 100);
                    }

                    return $this->returnSubmissionResponse(
                        $isNotHistory ?  'Step ' . $step->executionStep->name . ' is ' . $submission->results->{$step->executionStep->name}->status : "History",
                        $submission->status,
                        $submission->results,
                        $step,
                        $completion_percentage
                    );
                }

                // If we get here, check one more time if submission should be marked as completed
                if ($isNotHistory && $submission->status === Submission::$PROCESSING) {
                    $allStepsComplete = true;
                    $anyStepsFailed = false;

                    foreach ($submission->getExecutionSteps() as $execStep) {
                        $stepName = $execStep->executionStep->name;
                        if (
                            !isset($submission->results->$stepName) ||
                            $submission->results->$stepName->status === Submission::$PENDING ||
                            $submission->results->$stepName->status === Submission::$PROCESSING
                        ) {
                            $allStepsComplete = false;
                            break;
                        }

                        if ($submission->results->$stepName->status === Submission::$FAILED) {
                            $anyStepsFailed = true;
                        }
                    }

                    if ($allStepsComplete) {
                        $submission->updateStatus($anyStepsFailed ? Submission::$FAILED : Submission::$COMPLETED);
                    }
                }

                return $this->returnSubmissionResponse(
                    ($isNotHistory ?  'Submission is processing meanwhile there is no step to execute' : "History"),
                    $submission->status,
                    $submission->results,
                    $step,
                    $completion_percentage
                );
            }
        }
        return response()->json([
            'message' => 'Submission not found',
        ], 404);
    }

    public function returnSubmissionResponse($message, $status, $results, $next_step = null, $completion_percentage)
    {
        return response()->json([
            'message' => $message,
            'status' => $status,
            'results' => $results,
            'next_step' => $next_step,
            'completion_percentage' => $completion_percentage,
        ], 200);
    }

    public function refresh(Request $request)
    {
        if ($request->submission_id == null) return response()->json([
            'message' => 'Submission ID is required',
        ], 404);
        $user = Auth::user();
        $submission = Submission::where('id', $request->submission_id)->where('user_id', $user->id)->first();
        if ($submission and $submission->status === Submission::$FAILED) {
            // Create submission history
            $submission->createHistory("Submission has failed, so it has been refreshed");

            // if npm is installed
            if ($submission->results->{ExecutionStep::$NPM_INSTALL}->status == Submission::$COMPLETED and !$this->is_dir_empty($this->getTempDir($submission))) {
                $submission->restartAfterNpmInstall();
                if ($submission->port != null) Process::fromShellCommandline('npx kill-port ' . $submission->port, null, null, null, 120)->run();
            } else {
                $commands = [];
                if ($submission->port != null) {
                    $commands = [
                        ['npx', 'kill-port', $submission->port],
                        ['rm', '-rf', $this->getTempDir($submission)],
                    ];
                } else {
                    $commands = [
                        ['rm', '-rf', $this->getTempDir($submission)],
                    ];
                }
                // Delete temp directory
                foreach ($commands as $command) {
                    $env = [
                        'PATH' => config('app.process_path') . ':' . getenv('PATH'),
                    ];
                    $process = new Process($command, null, $env, null, 120);
                    $process->run();
                    if ($process->isSuccessful()) {
                        Log::info('Command ' . implode(" ", $command) . ' is successful');
                    } else {
                        Log::error('Command ' . implode(" ", $command) . ' has failed '   . $process->getErrorOutput());
                    }
                }

                $submission->initializeResults();
                $submission->updateStatus(Submission::$PENDING);
            }

            // Update submission status
            $submission->increaseAttempts();
            $submission->updatePort(null);
            $submission->restartTime();
            // Return response
            return response()->json([
                'message' => 'Submission has been refreshed',
                'status' => $submission->status,
                'results' => $submission->results,
                'attempts' => $submission->attempts,
                'completion_percentage' => 0,
            ], 200);
        }
    }

    private function getTempDir($submission)
    {
        return storage_path('app/public/tmp/submissions/' . $submission->user_id . '/' . $submission->project->title . '/' . $submission->id);
    }

    private function is_dir_empty($dir)
    {
        if (!is_readable($dir)) return true;
        $handle = opendir($dir);
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                closedir($handle);
                return false;
            }
        }
        closedir($handle);
        return true;
    }

    private function replaceCommandArraysWithValues($step_variables, $values, $step)
    {
        return array_reduce($step_variables, function ($commands, $variableValue) use ($values) {
            return array_map(function ($command) use ($variableValue, $values) {
                return $command === $variableValue ? $values[$variableValue] : $command;
            }, $commands);
        }, $step->executionStep->commands);
    }

    private function lunchCloneRepositoryJob($submission, $repoUrl, $tempDir, $step)
    {
        $commands = $step->executionStep->commands;
        $step_variables = $step->variables;
        $values = ["{{repoUrl}}" => $repoUrl, '{{tempDir}}' => $tempDir];
        $commands = $this->replaceCommandArraysWithValues($step_variables, $values, $step);
        dispatch(new CloneRepository($submission, $repoUrl, $tempDir, $commands))->onQueue(ExecutionStep::$CLONE_REPOSITORY);
    }

    private function lunchUnzipZipFilesJob($submission, $zipFileDir, $tempDir, $step)
    {
        $commands = $step->executionStep->commands;
        $step_variables = $step->variables;
        $values = ['{{zipFileDir}}' => $zipFileDir, '{{tempDir}}' => $tempDir];
        $commands = $this->replaceCommandArraysWithValues($step_variables, $values, $step);
        dispatch(new UnzipZipFiles($submission, $zipFileDir, $tempDir, $commands))->onQueue(ExecutionStep::$UNZIP_ZIP_FILES);
    }

    private function lunchExamineFolderStructureJob($submission, $tempDir, $step)
    {
        $commands = $step->executionStep->commands;
        $step_variables = $step->variables;
        $values = ['{{tempDir}}' => $tempDir];
        $commands = $this->replaceCommandArraysWithValues($step_variables, $values, $step);
        dispatch(new ExamineFolderStructure($submission, $tempDir, $commands))->onQueue(ExecutionStep::$EXAMINE_FOLDER_STRUCTURE);
    }

    private function lunchAddEnvFileJob($submission, $envFile, $tempDir, $step)
    {
        $commands = $step->executionStep->commands;
        $step_variables = $step->variables;
        $values = ['{{envFile}}' => $envFile, '{{tempDir}}' => $tempDir];
        $commands = $this->replaceCommandArraysWithValues($step_variables, $values, $step);
        dispatch(new AddEnvFile($submission, $envFile, $tempDir, $commands))->onQueue(ExecutionStep::$ADD_ENV_FILE);
    }

    private function lunchReplacePackageJsonJob($submission, $packageJson, $tempDir, $step)
    {
        $commands = $step->executionStep->commands;
        $step_variables = $step->variables;
        $values = ['{{packageJson}}' => $packageJson, '{{tempDir}}' => $tempDir];
        $commands = $this->replaceCommandArraysWithValues($step_variables, $values, $step);
        dispatch(new ReplacePackageJson($submission, $packageJson, $tempDir, $commands))->onQueue(ExecutionStep::$REPLACE_PACKAGE_JSON);
    }

    private function lunchCopyTestsFolderJob($submission, $tempDir, $step)
    {
        $testFiles = $step->variables;
        $commands = $step->executionStep->commands;
        $commandsArray = [];

        $testDirPath = $tempDir . '/tests';
        if (!is_dir($testDirPath)) {
            mkdir($testDirPath, 0777, true);
        }

        foreach ($testFiles as $testFile) {
            // Parse {{sourceFile}}=media:filename:subfolder
            $parts = explode("=", $testFile);

            if (isset($parts[1])) {
                // media:filename:subfolder -> [media, filename, subfolder]
                $fileConfig = explode(":", $parts[1]);

                $mediaCollection = isset($fileConfig[0]) ? $fileConfig[0] : 'tests';
                $fileName = isset($fileConfig[1]) ? $fileConfig[1] : '';
                $subFolder = isset($fileConfig[2]) ? $fileConfig[2] : '';

                $mediaItem = $submission->project->getMedia('project_tests' . ($mediaCollection != 'tests' ? "_$mediaCollection" : ""))
                    ->where('file_name', $fileName)
                    ->first();

                if ($mediaItem) {
                    $destDir = $testDirPath;
                    if (!empty($subFolder)) {
                        $destDir .= '/' . $subFolder;
                        if (!is_dir($destDir)) {
                            mkdir($destDir, 0777, true);
                        }
                    }

                    $copyCommand = $commands;
                    $copyCommand[2] = $mediaItem->getPath();
                    $copyCommand[3] = $destDir . '/' . basename($fileName);
                    $commandsArray[] = $copyCommand;
                } else {
                    Log::warning("Test file not found: {$fileName} in collection project_tests_{$mediaCollection}");
                }
            }
        }

        dispatch(new CopyTestsFolder($submission, null, $tempDir, $commandsArray))->onQueue(ExecutionStep::$COPY_TESTS_FOLDER);
    }

    private function lunchNpmInstallJob($submission, $tempDir, $step)
    {
        $commands = $step->executionStep->commands;
        $step_variables = $step->variables;
        $values = ['{{options}}' => " "];
        $commands = $this->replaceCommandArraysWithValues($step_variables, $values, $step);
        dispatch(new NpmInstall($submission, $tempDir, $commands))->onQueue(ExecutionStep::$NPM_INSTALL);
    }

    private function lunchNpmRunStartJob($submission, $tempDir, $step)
    {
        $commands = $step->executionStep->commands;
        dispatch(new NpmRunStart($submission, $tempDir, $commands));
    }

    private function lunchNpmRunTestsJob($submission, $tempDir, $step)
    {
        $commands = [];
        $tests = $submission->project->projectExecutionSteps->where('execution_step_id', $step->executionStep->id)->first()->variables;
        foreach ($tests as $testCommandValue) {
            $command = implode(" ", $step->executionStep->commands);
            $key = explode("=", $testCommandValue)[0];
            $value = explode("=", $testCommandValue)[1];
            $testName = str_replace($key, $value, $command);
            array_push($commands, explode(" ", $testName));
        }
        dispatch_sync(new NpmRunTests($submission, $tempDir, $commands));
    }

    private function lunchDeleteTempDirectoryJob($submission, $tempDir, $step, $commands = null)
    {
        if ($commands == null) {
            $commands = $step->executionStep->commands;
            $step_variables = $step->variables;
            $values = ['{{tempDir}}' => $tempDir];
            $commands = $this->replaceCommandArraysWithValues($step_variables, $values, $step);
            $commands = [$commands];
        }
        dispatch_sync(new DeleteTempDirectory($submission, $tempDir, $commands));
    }

    public function destroy(Request $request)
    {
        if ($request->ajax()) {
            if ($request->submission_id == null) return response()->json([
                'message' => 'Submission ID is required',
            ], 404);
            $user = Auth::user();
            $submission = Submission::where('id', $request->submission_id)->where('user_id', $user->id)->first();
            if ($submission) {
                $submission->delete();
                // delete temp directory and media
                if ($submission->type == Submission::$FILE) {
                    $submission->getMedia('submissions')->each(function ($media) {
                        $media->delete();
                    });
                }
                $tempDir = $this->getTempDir($submission);
                if (!$this->is_dir_empty($tempDir)) {
                    Process::fromShellCommandline('rm -rf ' . $tempDir)->run();
                }
                return response()->json([
                    'message' => 'Submission has been deleted successfully',
                ], 200);
            }
            return response()->json([
                'message' => 'Submission not found',
            ], 404);
        }
    }

    public function restart(Request $request)
    {
        if ($request->ajax()) {
            if ($request->submission_id == null) return response()->json([
                'message' => 'Submission ID is required',
            ], 404);
            $user = Auth::user();
            $submission = Submission::where('id', $request->submission_id)->where('user_id', $user->id)->first();
            if ($submission) {
                $submission->createHistory("Submission has been restarted");

                if ($submission->port != null) {
                    $commands = [
                        ['npx', 'kill-port', $submission->port],
                        ['rm', '-rf', $this->getTempDir($submission)],
                    ];
                } else {
                    $commands = [
                        ['rm', '-rf', $this->getTempDir($submission)],
                    ];
                }
                // Delete temp directory
                foreach ($commands as $command) {
                    if (!$this->is_dir_empty($this->getTempDir($submission))) {
                        $process = new Process($command, null, null, null, 120);
                        $process->run();
                        if ($process->isSuccessful()) {
                            Log::info('Command ' . implode(" ", $command) . ' is successful');
                        } else {
                            Log::error('Command ' . implode(" ", $command) . ' has failed '   . $process->getErrorOutput());
                        }
                    }
                }

                $submission->restart();

                return response()->json([
                    'message' => 'Submission has been restarted successfully',
                ], 200);
            }
            return response()->json([
                'message' => 'Submission not found',
            ], 404);
        }
    }

    public function changeSourceCode($submission_id)
    {
        $user = Auth::user();
        $submission = Submission::where('id', $submission_id)->where('user_id', $user->id)->first();
        if ($submission) {
            return view('submissions.change_source_code', compact('submission'));
        }
        return redirect()->route('submissions');
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'submission_id' => 'required|exists:submissions,id',
                'folder_path' => 'required_without:github_url',
                'github_url' => 'required_without:folder_path',
            ]);


            $user = Auth::user();
            $submission = Submission::where('id', $request->submission_id)->where('user_id', $user->id)->first();

            $submission->createHistory("Code has been changed");

            if (!$submission->isGithubUrl()) {
                $submission->getMedia('submissions')->each(function ($media) {
                    $media->delete();
                });
            }

            // delete temp directory if is not empty
            $tempDir = $this->getTempDir($submission);
            if (!$this->is_dir_empty($tempDir)) {
                Process::fromShellCommandline('rm -rf ' . $tempDir)->run();
            }

            if ($request->has('folder_path')) {
                $submission->type = Submission::$FILE;
                $submission->path = $request->folder_path;

                $temporary_file = TemporaryFile::where('folder_path', $request->folder_path)->first();

                if ($temporary_file) {
                    $path = storage_path('app/' . $request->folder_path . '/' . $temporary_file->file_name);
                    $submission->addMedia($path)->toMediaCollection('submissions', 'public_submissions_files');
                    if ($this->is_dir_empty(storage_path('app/' . $request->folder_path))) {
                        rmdir(storage_path('app/' . $request->folder_path));
                    }
                    $temporary_file->delete();
                }
            } else {
                $submission->type = Submission::$URL;
                $submission->path = $request->github_url;
            }
            $submission->save();
            $submission->restart();

            return response()->json([
                'message' => 'Submission created successfully',
                'submission' => $submission,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Submission failed',
                'error' => $th->getMessage(),
            ], 500);
        }
    }


    public function downloadHistory(Request $request, $id)
    {
        if (!$request->type) {
            return redirect()->route('submissions');
        }

        $user = Auth::user();
        $submission = $request->type == 'history' ? SubmissionHistory::where('id', $id)->where('user_id', $user->id)->first() :  Submission::where('id', $id)->where('user_id', $user->id)->first();

        if (!$submission) {
            return redirect()->route('submissions');
        }

        if ($request->type == 'current' && $submission->status != Submission::$COMPLETED && $submission->status != Submission::$FAILED) {
            return redirect()->route('submissions');
        }
        $results = json_encode($submission->results, JSON_PRETTY_PRINT);
        $results_array = json_decode($results, true);
        uasort($results_array, function ($a, $b) {
            return $a['order'] - $b['order'];
        });
        $jsonResults = json_encode($results_array, JSON_PRETTY_PRINT);
        $filename = 'submission_' . $submission->project->title . '_' . $user->id . '_' . $id . '_' . now()->format('Y-m-d_H-i-s') . '.json';
        $headers = [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename=' . $filename,
        ];

        return response()->streamDownload(function () use ($submission, $user, $jsonResults) {
            echo "Submission for project: " . $submission->project->title . " | User: " . $user->name . "\n";
            echo "====================================================================================================\n";
            echo $jsonResults;
            echo "\n====================================================================================================";
        }, $filename, $headers);
    }
}
