<?php

namespace App\Http\Controllers;

use Exception;
use ZipArchive;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\Process\Process;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::all();
        return view('projects.index', compact('projects'));
    }

    public function show(Request $request, $project_id)
    {
        $project = Project::find($project_id);
        if (!$project) {
            return redirect()->route('projects');
        }
        return view('projects.show', compact('project'));
    }

    public function showPDF(Request $request)
    {
        if ($request->ajax()) {
            if ($request->id) {
                $media = Media::find($request->id);
                if ($media) {
                    $path = $media->getUrl();
                    return response()->json($path, 200);
                }
                return response()->json(["message" => "media not found"], 404);
            }
            return response()->json(["message" => "no media was requested"], 400);
        }
    }

    public function download(Request $request, $project_id)
    {
        if ($request->ajax()) {
            $project = Project::find($project_id);
            if (!$project) {
                return response()->json(["message" => "project not found"], 404);
            }
            if ($request->type) {
                switch ($request->type) {
                    case 'guides':
                        $zipMedia = $project->getMedia('project_zips')->where('file_name', 'guides.zip')->first();
                        if ($zipMedia) {
                            return response()->json($zipMedia->getUrl(), 200);
                        } else {
                            $guides = $project->getMedia('project_guides');
                            $tempDir = storage_path('app/public/assets/projects/' . $project->title . '/zips');
                            if (!is_dir($tempDir)) mkdir($tempDir);
                            foreach ($guides as $guide) {
                                $path = $guide->getPath();
                                $filename = $guide->file_name;
                                copy($path, $tempDir . '/' . $filename);
                            }
                            $zipPath = $tempDir . '/guides.zip';
                            $zip = new ZipArchive;
                            if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
                                $files = Storage::files('public/assets/projects/' . $project->title . '/zips');
                                foreach ($files as $file) {
                                    $zip->addFile(storage_path('app/' . $file), basename($file));
                                }
                                $zip->close();
                                foreach ($files as $file) {
                                    unlink(storage_path('app/' . $file));
                                }
                            } else {
                                throw new Exception('Failed to create zip archive');
                            }
                            $media = $project->addMedia($zipPath)->toMediaCollection('project_zips', 'public_projects_files');
                            return response()->json($media->getUrl(), 200);
                        }
                        break;
                    case 'supplements':
                        $zipMedia = $project->getMedia('project_zips')->where('file_name', 'supplements.zip')->first();
                        if ($zipMedia) {
                            return response()->json($zipMedia->getUrl(), 200);
                        } else {
                            $supplements = $project->getMedia('project_supplements');
                            $tempDir = storage_path('app/public/assets/projects/' . $project->title . '/zips');
                            if (!is_dir($tempDir)) mkdir($tempDir);
                            foreach ($supplements as $supplement) {
                                $path = $supplement->getPath();
                                $filename = $supplement->file_name;
                                copy($path, $tempDir . '/' . $filename);
                            }
                            $zipPath = $tempDir . '/supplements.zip';
                            $zip = new ZipArchive;
                            if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
                                $files = Storage::files('public/assets/projects/' . $project->title . '/zips');
                                foreach ($files as $file) {
                                    $zip->addFile(storage_path('app/' . $file), basename($file));
                                }
                                $zip->close();
                                foreach ($files as $file) {
                                    unlink(storage_path('app/' . $file));
                                }
                            } else {
                                throw new Exception('Failed to create zip archive');
                            }
                            $media = $project->addMedia($zipPath)->toMediaCollection('project_zips', 'public_projects_files');
                            return response()->json($media->getUrl(), 200);
                        }
                        break;
                    case 'tests':
                        $zipMedia = $project->getMedia('project_zips')->where('file_name', 'tests.zip')->first();
                        if ($zipMedia) {
                            return response()->json($zipMedia->getUrl(), 200);
                        } else {
                            $tests_api = $project->getMedia('project_tests_api');
                            $tests_web = $project->getMedia('project_tests_web');
                            $tests_images = $project->getMedia('project_tests_images');

                            $tempDir = storage_path('app/public/assets/projects/' . $project->title . '/zips');
                            if (!is_dir($tempDir)) mkdir($tempDir);
                            if (!is_dir($tempDir . '/tests')) mkdir($tempDir . '/tests');
                            if (!is_dir($tempDir . '/tests/api')) mkdir($tempDir . '/tests/api');
                            if (!is_dir($tempDir . '/tests/web')) mkdir($tempDir . '/tests/web');
                            if (!is_dir($tempDir . '/tests/web/images')) mkdir($tempDir . '/tests/web/images');

                            foreach ($tests_api as $test) {
                                $path = $test->getPath();
                                $filename = $test->file_name;
                                copy($path, $tempDir . '/tests/api/' . $filename);
                            }
                            foreach ($tests_web as $test) {
                                $path = $test->getPath();
                                $filename = $test->file_name;
                                copy($path, $tempDir . '/tests/web/' . $filename);
                            }
                            foreach ($tests_images as $test) {
                                $path = $test->getPath();
                                $filename = $test->file_name;
                                copy($path, $tempDir . '/tests/web/images/' . $filename);
                            }

                            $zipPath = $tempDir . '/tests.zip';
                            $zip = new ZipArchive;
                            if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
                                $zip->addEmptyDir('api');
                                $zip->addEmptyDir('web');
                                $zip->addEmptyDir('web/images');
                                $api_files = Storage::files('public/assets/projects/' . $project->title . '/zips/tests/api');
                                foreach ($api_files as $file) {
                                    $zip->addFile(storage_path('app/' . $file), 'api/' . basename($file));
                                }
                                $api_files = Storage::files('public/assets/projects/' . $project->title . '/zips/tests/web');
                                foreach ($api_files as $file) {
                                    $zip->addFile(storage_path('app/' . $file), 'web/' . basename($file));
                                }
                                $image_files = Storage::files('public/assets/projects/' . $project->title . '/zips/tests/web/images');
                                foreach ($image_files as $file) {
                                    $zip->addFile(storage_path('app/' . $file), 'web/images/' . basename($file));
                                }

                                $zip->close();
                                Process::fromShellCommandline("rm -rf {$tempDir}/tests")->run();
                            } else {
                                throw new Exception('Failed to create zip archive');
                            }
                            $media = $project->addMedia($zipPath)->toMediaCollection('project_zips', 'public_projects_files');
                            return response()->json($media->getUrl(), 200);
                        }
                        break;
                }
            } else {
                return response()->json(["message" => "no type was requested"], 400);
            }
        }
    }
}
