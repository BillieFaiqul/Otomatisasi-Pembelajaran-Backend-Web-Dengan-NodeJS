<?php

namespace Database\Seeders;

use App\Models\ExecutionStep;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExecutionStepSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ExecutionStep::insert([
            [
                'name' => 'Clone Repository',
                'commands' => json_encode([
                    'git',
                    'clone',
                    '--depth=1',
                    '{{repoUrl}}',
                    '{{tempDir}}',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Unzip ZIP Files',
                'commands' => json_encode([
                    'unzip',
                    '-o',
                    '{{zipFileDir}}',
                    '-d',
                    '{{tempDir}}',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Remove ZIP Files',
                'commands' => json_encode([
                    'rm',
                    '-rf',
                    '{{zipFileDir}}',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Examine Folder Structure',
                'commands' => json_encode([
                    'ls',
                    '{{tempDir}}',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Add .env File',
                'commands' => json_encode([
                    'cp',
                    '-r',
                    '{{envFile}}',
                    '{{tempDir}}',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Replace package.json',
                'commands' => json_encode([
                    'cp',
                    '-r',
                    '{{packageJson}}',
                    '{{tempDir}}',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => "Copy 'tests' Folder",
                'commands' => json_encode([
                    'cp',
                    '-r',
                    '{{sourceFile}}',
                    '{{destinationFile}}',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'NPM Install',
                'commands' => json_encode([
                    'npm',
                    'install',
                    '{{options}}',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'NPM Run Start',
                'commands' => json_encode([
                    'npm',
                    'run',
                    'start',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'NPM Run Tests',
                'commands' => json_encode([
                    'npx',
                    'jest',
                    '{{testFile}}',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Delete Temp Directory',
                'commands' => json_encode([
                    'rm',
                    '-rf',
                    '{{tempDir}}',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
