<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProjectsDefaultFileStructure;
use Illuminate\Database\Seeder;

class ProjectsDefaultFileStructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $api_experiment_project_id = Project::where('title', 'api-experiment')->first()->id;
        // $auth_experiment_project_id = Project::where('title', 'auth-experiment')->first()->id;
        $async_experiment_project_id = Project::where('title', 'asynchronous-programming')->first()->id;

        ProjectsDefaultFileStructure::insert([
            [
                'project_id' => $async_experiment_project_id,
                'structure' => json_encode([
                    'src' => [
                        'config' => [
                            'database.js' => '',
                        ],
                        'controllers' => [
                            'mejaController.js' => '',
                            'menuController.js' => '',
                            'orderController.js' => '',
                        ],
                        'models' => [
                            'mejaModel.js' => '',
                            'menuModel.js' => '',
                            'orderModel.js' => '',
                        ],
                        'middleware' => [
                            'errorHandler.js' => '',
                        ],
                        'routes' => [
                            'mejaRoutes.js' => '',
                            'menuRoutes.js' => '',
                            'orderRoutes.js' => '',
                        ],
                    ],
                    'node_modules' => '',
                    'tests' => [],
                    '.env' => '',
                    '.env.example' => '',
                    '.gitignore' => '',
                    'app.js' => '',
                    'package-lock.json' => '',
                    'package.json' => '',
                    'README' => '',
                    'server.js' => '',
                ]),
                'excluded' => json_encode([
                    'node_modules',
                    'tests',
                    '.env',
                    '.env.example',
                    '.gitignore',
                    'package-lock.json',
                    'README',
                ]),
                'replacements' => json_encode([
                    '.env',
                    'tests',
                    'package.json'
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // [
            //     'project_id' => $api_experiment_project_id,
            //     'structure' => json_encode([
            //         'controllers' => [
            //             'api' => [
            //                 'product.controller.js' => '',
            //             ],
            //             'web' => [
            //                 'product.controller.js' => '',
            //             ],
            //         ],
            //         'models' => [
            //             'product.model.js' => '',
            //         ],
            //         'node_modules' => '',
            //         'routes' => [
            //             'api' => [
            //                 'product.routes.js' => '',
            //             ],
            //             'web' => [
            //                 'product.routes.js' => '',
            //             ],
            //         ],
            //         'tests' => [
            //             'api' => [
            //                 'testA01.test.js' => '',
            //                 'testA02.test.js' => '',
            //                 'testA03.test.js' => '',
            //                 'testA04.test.js' => '',
            //                 'testA05.test.js' => '',
            //             ],
            //             'web' => [
            //                 'images' => [
            //                     'create-product-page.png' => '',
            //                     'error-notFound-page.png' => '',
            //                     'index-page.png' => '',
            //                     'no-products-found-page.png' => '',
            //                     'not-found-product-page.png' => '',
            //                     'product-details-page.png' => '',
            //                     'products-table-page.png' => '',
            //                     'update-product-page.png' => '',
            //                 ],
            //                 'testA01.test.js' => '',
            //                 'testA02.test.js' => '',
            //                 'testA03.test.js' => '',
            //                 'testA04.test.js' => '',
            //                 'testA05.test.js' => '',
            //             ],
            //         ],
            //         'web' => [
            //             'layouts' => [
            //                 'main.ejs' => '',
            //             ],
            //             'styles' => [
            //                 'main.css' => '',
            //             ],
            //             'views' => [
            //                 'products' => [
            //                     'create.ejs' => '',
            //                     'details.ejs' => '',
            //                     'index.ejs' => '',
            //                     'update.ejs' => '',
            //                 ],
            //                 'error.ejs' => '',
            //                 'index.ejs' => '',
            //             ],
            //         ],
            //         '.env' => '',
            //         '.env.example' => '',
            //         '.gitignore' => '',
            //         'app.js' => '',
            //         'initial_data.json' => '',
            //         'package-lock.json' => '',
            //         'package.json' => '',
            //         'README' => '',
            //         'server.js' => '',
            //     ]),
            //     'excluded' => json_encode([
            //         'node_modules',
            //         'tests',
            //         '.env',
            //         '.env.example',
            //         '.gitignore',
            //         'package-lock.json',
            //         'initial_data.json',
            //         'README',
            //     ]),
            //     'replacements' => json_encode([
            //         '.env',
            //         'tests',
            //         'package.json'
            //     ]),
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'project_id' => $auth_experiment_project_id,
            //     'structure' => json_encode([
            //         'controllers' => [
            //             'api' => [
            //                 'auth.controller.js' => '',
            //             ],
            //             'web' => [
            //                 'auth.controller.js' => '',
            //             ],
            //         ],
            //         'helpers' => [
            //             'errorhandler.helper.js' => '',
            //             'jsonwebtoken.helper.js' => '',
            //         ],
            //         'models' => [
            //             'user.model.js' => '',
            //         ],
            //         'node_modules' => '',
            //         'routes' => [
            //             'api' => [
            //                 'auth.routes.js' => '',
            //             ],
            //             'web' => [
            //                 'auth.routes.js' => '',
            //             ],
            //         ],
            //         'services' => [
            //             'auth.service.js' => '',
            //         ],
            //         'tests' => [
            //             'api' => [
            //                 'testB01.test.js' => '',
            //                 'testB02.test.js' => '',
            //                 'testB03.test.js' => '',
            //                 'testB04.test.js' => '',
            //                 'testB05.test.js' => '',
            //             ],
            //             'web' => [
            //                 'images' => [
            //                     'edit-page.png' => '',
            //                     'edit-password-page.png' => '',
            //                     'error-notFound-page.png' => '',
            //                     'index-page.png' => '',
            //                     'index-page-after-register.png' => '',
            //                     'login-page.png' => '',
            //                     'login-page-with-error.png' => '',
            //                     'profile-page.png' => '',
            //                     'register-page.png' => '',
            //                 ],
            //                 'testB01.test.js' => '',
            //                 'testB02.test.js' => '',
            //                 'testB03.test.js' => '',
            //                 'testB04.test.js' => '',
            //                 'testB05.test.js' => '',
            //             ],
            //         ],
            //         'web' => [
            //             'layouts' => [
            //                 'main.ejs' => '',
            //             ],
            //             'styles' => [
            //                 'main.css' => '',
            //             ],
            //             'views' => [
            //                 'auth' => [
            //                     'edit.ejs' => '',
            //                     'login.ejs' => '',
            //                     'profile.ejs' => '',
            //                     'register.ejs' => '',
            //                 ],
            //                 'error.ejs' => '',
            //                 'index.ejs' => '',
            //             ],
            //         ],
            //         '.env' => '',
            //         '.env.example' => '',
            //         '.gitignore' => '',
            //         'app.js' => '',
            //         'package-lock.json' => '',
            //         'package.json' => '',
            //         'README' => '',
            //         'server.js' => '',
            //     ]),
            //     'excluded' => json_encode([
            //         'node_modules',
            //         'tests',
            //         '.env',
            //         '.env.example',
            //         '.gitignore',
            //         'package-lock.json',
            //         'README',
            //     ]),
            //     'replacements' => json_encode([
            //         '.env',
            //         'tests',
            //         'package.json'
            //     ]),
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ]

        ]);
    }
}
