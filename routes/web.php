<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // Projects
    Route::prefix('projects')->controller(ProjectController::class)->group(function () {
        Route::get('/', 'index')->name('projects');
        Route::get('/project/{project_id}', 'show')->name('projects.show');
        Route::get('/project/{project_id}/download', 'download')->name('projects.download');
        Route::get('pdf', 'showPDF')->name('projects.pdf');
    });
    // Submissions
    Route::prefix('submissions')->group(function () {
        // show all the submission based on the projects
        Route::get('/', [SubmissionController::class, 'index'])->name('submissions');
        // show all the attempts based on the submission and the project
        Route::get('/project/{project_id}', [SubmissionController::class, 'showAllSubmissionsBasedOnProject'])->name('submissions.showAll');
        // show the submission based on the submission id
        Route::get('/submission/{submission_id}', [SubmissionController::class, 'show'])->name('submissions.show');
        // show the submission history based on the history id
        Route::get('/submission/history/{history_id}', [SubmissionController::class, 'history'])->name('submissions.history');
        // download the submission history based on the history id
        Route::get('/submission/{id}/download', [SubmissionController::class, 'downloadHistory'])->name('submissions.downloadHistory');
        // process the submission steps
        Route::post('/process/submission', [SubmissionController::class, 'process'])->name('submissions.process');
        // refresh the submission steps based on the submission npm install step
        Route::post('/refresh/submission', [SubmissionController::class, 'refresh'])->name('submissions.refresh');
        // get the submission status based on the submission id
        Route::get('/status/submission/{submission_id}', [SubmissionController::class, 'status'])->name('submissions.status');
        // upload the submission's zip file based on the project id
        Route::post('/upload/{project_id}', [SubmissionController::class, 'upload'])->name('submissions.upload');
        // submit the submission based on the submission id
        Route::post('/submit', [SubmissionController::class, 'submit'])->name('submissions.submit');
        // delete the submission based on the submission id
        Route::delete('/delete/submission', [SubmissionController::class, 'destroy'])->name('submissions.destroy');
        // restart the submission based on the submission id
        Route::post('/restart/submission', [SubmissionController::class, 'restart'])->name('submissions.restart');
        // update source code page based on the submission id
        Route::get('/change/{submission_id}', [SubmissionController::class, 'changeSourceCode'])->name('submissions.changeSourceCode');
        // update source code based on the submission id
        Route::post('/update/submission', [SubmissionController::class, 'update'])->name('submissions.update');
    });
});

require __DIR__ . '/auth.php';
