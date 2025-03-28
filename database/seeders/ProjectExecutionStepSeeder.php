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
        $api_experiment_project_id = Project::where('title', 'api-experiment')->first()->id;
        $auth_experiment_project_id = Project::where('title', 'auth-experiment')->first()->id;
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
                        '{{repoUrl}}', '{{tempDir}}',
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
            ],
            [
                    'project_id' => $async_experiment_project_id,
                    'execution_step_id' => $unzip_zip_files_execution_step_id,
                    'order' => 2,
                    'variables' => json_encode([
                        '{{zipFileDir}}', '{{tempDir}}'
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
                        '{{envFile}}', '{{tempDir}}',
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
            ],
            [
                    'project_id' => $async_experiment_project_id,
                    'execution_step_id' => $replace_package_json_execution_step_id,
                    'order' => 5,
                    'variables' => json_encode([
                        '{{packageJson}}', '{{tempDir}}',
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
            ],
            [
                    'project_id' => $async_experiment_project_id,
                    'execution_step_id' => $copy_tests_folder_step_id,
                    'order' => 6,
                    'variables' => json_encode([
                        '{{testsDir}}', '{{tempDir}}',
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
                        '{{testFile}}=async-testA01',
                        '{{testFile}}=async-testA02',
                        '{{testFile}}=async-testA03',
                        '{{testFile}}=async-testA04',
                        '{{testFile}}=async-testA05',
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
            // [
            //     'project_id' => $api_experiment_project_id,
            //     'execution_step_id' => $clone_repo_execution_step_id,
            //     'order' => 1,
            //     'variables' => json_encode([
            //         '{{repoUrl}}', '{{tempDir}}',
            //     ]),
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'project_id' => $api_experiment_project_id,
            //     'execution_step_id' => $unzip_zip_files_execution_step_id,
            //     'order' => 2,
            //     'variables' => json_encode([
            //         '{{zipFileDir}}', '{{tempDir}}'
            //     ]),
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'project_id' => $api_experiment_project_id,
            //     'execution_step_id' => $checking_folder_structure_execution_step_id,
            //     'order' => 3,
            //     'variables' => json_encode([
            //         '{{tempDir}}',
            //     ]),
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'project_id' => $api_experiment_project_id,
            //     'execution_step_id' => $add_env_file_execution_step_id,
            //     'order' => 4,
            //     'variables' => json_encode([
            //         '{{envFile}}', '{{tempDir}}',
            //     ]),
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'project_id' => $api_experiment_project_id,
            //     'execution_step_id' => $replace_package_json_execution_step_id,
            //     'order' => 5,
            //     'variables' => json_encode([
            //         '{{packageJson}}', '{{tempDir}}',
            //     ]),
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'project_id' => $api_experiment_project_id,
            //     'execution_step_id' => $copy_tests_folder_step_id,
            //     'order' => 6,
            //     'variables' => json_encode([
            //         '{{testsDir}}', '{{tempDir}}',
            //     ]),
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'project_id' => $api_experiment_project_id,
            //     'execution_step_id' => $npm_install_execution_step_id,
            //     'order' => 7,
            //     'variables' => json_encode([
            //         '{{options}}',
            //     ]),
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'project_id' => $api_experiment_project_id,
            //     'execution_step_id' => $npm_run_start_execution_step_id,
            //     'order' => 8,
            //     'variables' => null,
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'project_id' => $api_experiment_project_id,
            //     'execution_step_id' => $npm_run_tests_execution_step_id,
            //     'order' => 9,
            //     'variables' => json_encode([
            //         '{{testFile}}=api-testA01',
            //         '{{testFile}}=web-testA01',
            //         '{{testFile}}=api-testA02',
            //         '{{testFile}}=web-testA02',
            //         '{{testFile}}=api-testA03',
            //         '{{testFile}}=web-testA03',
            //         '{{testFile}}=api-testA04',
            //         '{{testFile}}=web-testA04',
            //         '{{testFile}}=api-testA05',
            //         '{{testFile}}=web-testA05',
            //     ]),
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'project_id' => $api_experiment_project_id,
            //     'execution_step_id' => $delete_temp_directory_execution_step_id,
            //     'order' => 10,
            //     'variables' => json_encode([
            //         '{{tempDir}}',
            //     ]),
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'project_id' => $auth_experiment_project_id,
            //     'execution_step_id' => $clone_repo_execution_step_id,
            //     'order' => 1,
            //     'variables' => json_encode([
            //         '{{repoUrl}}', '{{tempDir}}',
            //     ]),
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'project_id' => $auth_experiment_project_id,
            //     'execution_step_id' => $unzip_zip_files_execution_step_id,
            //     'order' => 2,
            //     'variables' => json_encode([
            //         '{{zipFileDir}}', '{{tempDir}}'
            //     ]),
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'project_id' => $auth_experiment_project_id,
            //     'execution_step_id' => $checking_folder_structure_execution_step_id,
            //     'order' => 3,
            //     'variables' => json_encode([
            //         '{{tempDir}}',
            //     ]),
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'project_id' => $auth_experiment_project_id,
            //     'execution_step_id' => $add_env_file_execution_step_id,
            //     'order' => 4,
            //     'variables' => json_encode([
            //         '{{envFile}}', '{{tempDir}}',
            //     ]),
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'project_id' => $auth_experiment_project_id,
            //     'execution_step_id' => $replace_package_json_execution_step_id,
            //     'order' => 5,
            //     'variables' => json_encode([
            //         '{{packageJson}}', '{{tempDir}}',
            //     ]),
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'project_id' => $auth_experiment_project_id,
            //     'execution_step_id' => $copy_tests_folder_step_id,
            //     'order' => 6,
            //     'variables' => json_encode([
            //         '{{testsDir}}', '{{tempDir}}',
            //     ]),
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'project_id' => $auth_experiment_project_id,
            //     'execution_step_id' => $npm_install_execution_step_id,
            //     'order' => 7,
            //     'variables' => json_encode([
            //         '{{options}}',
            //     ]),
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'project_id' => $auth_experiment_project_id,
            //     'execution_step_id' => $npm_run_start_execution_step_id,
            //     'order' => 8,
            //     'variables' => null,
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'project_id' => $auth_experiment_project_id,
            //     'execution_step_id' => $npm_run_tests_execution_step_id,
            //     'order' => 9,
            //     'variables' => json_encode([
            //         '{{testFile}}=api-testB01',
            //         '{{testFile}}=web-testB01',
            //         '{{testFile}}=api-testB02',
            //         '{{testFile}}=web-testB02',
            //         '{{testFile}}=api-testB03',
            //         '{{testFile}}=web-testB03',
            //         '{{testFile}}=api-testB04',
            //         '{{testFile}}=web-testB04',
            //         '{{testFile}}=api-testB05',
            //         '{{testFile}}=web-testB05',
            //     ]),
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'project_id' => $auth_experiment_project_id,
            //     'execution_step_id' => $delete_temp_directory_execution_step_id,
            //     'order' => 10,
            //     'variables' => json_encode([
            //         '{{tempDir}}',
            //     ]),
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
        ]);
    }
}
