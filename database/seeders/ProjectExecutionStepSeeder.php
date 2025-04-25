<?php

namespace Database\Seeders;

use App\Models\ExecutionStep;
use App\Models\Project;
use App\Models\ProjectExecutionStep;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectExecutionStepSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $api_experiment_project_id = Project::where('title', 'api-experiment')->first()->id;
        // $auth_experiment_project_id = Project::where('title', 'auth-experiment')->first()->id;
        $async_experiment_project_id = Project::where('title', 'asynchronous-programming')->first()->id;

        $clone_repo_execution_step_id = ExecutionStep::where('name', ExecutionStep::$CLONE_REPOSITORY)->first()->id;
        $unzip_zip_files_execution_step_id = ExecutionStep::where('name', ExecutionStep::$UNZIP_ZIP_FILES)->first()->id;
        $checking_folder_structure_execution_step_id = ExecutionStep::where('name', ExecutionStep::$EXAMINE_FOLDER_STRUCTURE)->first()->id;
        $add_env_file_execution_step_id = ExecutionStep::where('name', ExecutionStep::$ADD_ENV_FILE)->first()->id;
        $replace_package_json_execution_step_id = ExecutionStep::where('name', ExecutionStep::$REPLACE_PACKAGE_JSON)->first()->id;
        $copy_tests_folder_step_id = ExecutionStep::where('name', ExecutionStep::$COPY_TESTS_FOLDER)->first()->id;
        $npm_install_execution_step_id = ExecutionStep::where('name', ExecutionStep::$NPM_INSTALL)->first()->id;
        $npm_run_start_execution_step_id = ExecutionStep::where('name', ExecutionStep::$NPM_RUN_START)->first()->id;
        $npm_run_tests_execution_step_id = ExecutionStep::where('name', ExecutionStep::$NPM_RUN_TESTS)->first()->id;
        $delete_temp_directory_execution_step_id = ExecutionStep::where('name', ExecutionStep::$DELETE_TEMP_DIRECTORY)->first()->id;

        ProjectExecutionStep::insert([
            [
                'project_id' => $async_experiment_project_id,
                'execution_step_id' => $clone_repo_execution_step_id,
                'order' => 1,
                'variables' => json_encode([
                    '{{repoUrl}}',
                    '{{tempDir}}',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'project_id' => $async_experiment_project_id,
                'execution_step_id' => $unzip_zip_files_execution_step_id,
                'order' => 2,
                'variables' => json_encode([
                    '{{zipFileDir}}',
                    '{{tempDir}}'
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'project_id' => $async_experiment_project_id,
                'execution_step_id' => $checking_folder_structure_execution_step_id,
                'order' => 3,
                'variables' => json_encode([
                    '{{tempDir}}',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'project_id' => $async_experiment_project_id,
                'execution_step_id' => $add_env_file_execution_step_id,
                'order' => 4,
                'variables' => json_encode([
                    '{{envFile}}',
                    '{{tempDir}}',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'project_id' => $async_experiment_project_id,
                'execution_step_id' => $replace_package_json_execution_step_id,
                'order' => 5,
                'variables' => json_encode([
                    '{{packageJson}}',
                    '{{tempDir}}',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'project_id' => $async_experiment_project_id,
                'execution_step_id' => $copy_tests_folder_step_id,
                'order' => 6,
                'variables' => json_encode([
                    // Format: {{sourceFile}}=media:filename:subfolder
                    // Contoh:
                    // '{{sourceFile}}=api:testfile.js:api' - places file in tests/api folder
                    // '{{sourceFile}}=web:homepage.test.js:web/integration' - places file in tests/web/integration folder
                    '{{sourceFile}}=tests:modul1-unit.test.js:unit',
                    '{{sourceFile}}=tests:modul2-unit.test.js:unit',
                    '{{sourceFile}}=tests:modul3-unit.test.js:unit',
                    '{{sourceFile}}=tests:modul4-unit.test.js:unit',
                    '{{sourceFile}}=tests:modul5-unit.test.js:unit',
                    '{{sourceFile}}=tests:modul1-integration.test.js:integration',
                    '{{sourceFile}}=tests:modul2-integration.test.js:integration',
                    '{{sourceFile}}=tests:modul3-integration.test.js:integration',
                    '{{sourceFile}}=tests:modul4-integration.test.js:integration',
                    '{{sourceFile}}=tests:modul5-integration.test.js:integration',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'project_id' => $async_experiment_project_id,
                'execution_step_id' => $npm_install_execution_step_id,
                'order' => 7,
                'variables' => json_encode([
                    '{{options}}',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'project_id' => $async_experiment_project_id,
                'execution_step_id' => $npm_run_start_execution_step_id,
                'order' => 8,
                'variables' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'project_id' => $async_experiment_project_id,
                'execution_step_id' => $npm_run_tests_execution_step_id,
                'order' => 9,
                'variables' => json_encode([
                    // '{{testFile}}=praktikum1.test.js',
                    // // '{{testFile}}=praktikum2Integration.test.js',
                    // // '{{testFile}}=praktikum2Unit.test.js',
                    // // '{{testFile}}=praktikum3Integration.test.js',
                    // '{{testFile}}=praktikum3Unit.test.js',
                    '{{testFile}}=modul1-unit.test.js',
                    '{{testFile}}=modul2-unit.test.js',
                    '{{testFile}}=modul3-unit.test.js',
                    '{{testFile}}=modul4-unit.test.js',
                    '{{testFile}}=modul5-unit.test.js',
                    '{{testFile}}=modul1-integration.test.js',
                    '{{testFile}}=modul2-integration.test.js',
                    '{{testFile}}=modul3-integration.test.js',
                    '{{testFile}}=modul4-integration.test.js',
                    '{{testFile}}=modul5-integration.test.js',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'project_id' => $async_experiment_project_id,
                'execution_step_id' => $delete_temp_directory_execution_step_id,
                'order' => 10,
                'variables' => json_encode([
                    '{{tempDir}}',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
