<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Project::insert([
            // [
            //     'title' => 'api-experiment',
            //     'description' => 'This is an API and web project using NodeJS, ExpressJS, and MongoDB. The goal of this project is to try testing API endpoints and Web pages using Jest, Supertest, and Puppeteer.',
            //     'tech_stack' => json_encode([
            //         'framework' => 'ExpressJS',
            //         'language' => 'NodeJS',
            //         'database' => 'MongoDB',
            //         'testing' => 'Jest, Supertest, Puppeteer',
            //     ]),
            //     'github_url' => 'https://github.com/Omar630603/api-experiment',
            //     'image' => 'image',
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'title' => 'auth-experiment',
            //     'description' => 'This is an API and web project using NodeJS, ExpressJS, and MongoDB. The goal of this project is to try testing API endpoints and Web pages using Jest, Supertest, and Puppeteer.',
            //     'tech_stack' => json_encode([
            //         'framework' => 'ExpressJS',
            //         'language' => 'NodeJS',
            //         'database' => 'MongoDB',
            //         'testing' => 'Jest, Supertest, Puppeteer',
            //     ]),
            //     'github_url' => 'https://github.com/Omar630603/auth-experiment',
            //     'image' => 'image',
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            [
                'title' => 'asynchronous-programming',
                'description' => 'Proyek ini adalah pengembangan backend untuk sistem reservasi restoran berbasis Node.js yang dikembangkan secara bertahap untuk mempelajari konsep asynchronous programming dan error handling. 
                              Mahasiswa akan memulai dengan setup project, kemudian mengimplementasikan berbagai pola asynchronous seperti callback, Promise, dan Async/Await untuk mengelola menu restoran, reservasi meja, dan pesanan. 
                              Selain itu, proyek ini juga mencakup error handling yang efektif menggunakan middleware untuk menangani kesalahan selama operasi database, validasi input, dan proses asynchronous lainnya',
                'tech_stack' => json_encode([
                    'framework' => 'ExpressJS',
                    'language' => 'NodeJS',
                    'database' => 'MongoDB',
                ]),
                'github_url' => 'https://github.com/Omar630603/auth-experiment',
                'image' => 'image',
                'created_at' => now(),
                'updated_at' => now(),
            ]

        ]);

        $project_asynchronous_programming = Project::where('title', 'asynchronous-programming')->first();

        //images
        $project_asynchronous_programming->addMedia(storage_path('projects/asycnchronous-programming/images/asycnchronous-programming.png'))->preservingOriginal()->toMediaCollection('project_images', 'public_projects_files')->preserve;

        //files
        $project_asynchronous_programming->addMedia(storage_path('projects/asycnchronous-programming/files/.env'))->preservingOriginal()->toMediaCollection('project_files', 'public_projects_files');
        $project_asynchronous_programming->addMedia(storage_path('projects/asycnchronous-programming/files/package.json'))->preservingOriginal()->toMediaCollection('project_files', 'public_projects_files');

        //guides
        $project_asynchronous_programming->addMedia(storage_path('projects/asycnchronous-programming/guides/Guide ASP01.pdf'))->preservingOriginal()->toMediaCollection('project_guides', 'public_projects_files');
        $project_asynchronous_programming->addMedia(storage_path('projects/asycnchronous-programming/guides/Guide ASP02.pdf'))->preservingOriginal()->toMediaCollection('project_guides', 'public_projects_files');
        $project_asynchronous_programming->addMedia(storage_path('projects/asycnchronous-programming/guides/Guide ASP03.pdf'))->preservingOriginal()->toMediaCollection('project_guides', 'public_projects_files');
        $project_asynchronous_programming->addMedia(storage_path('projects/asycnchronous-programming/guides/Guide ASP04.pdf'))->preservingOriginal()->toMediaCollection('project_guides', 'public_projects_files');
        $project_asynchronous_programming->addMedia(storage_path('projects/asycnchronous-programming/guides/Guide ASP05.pdf'))->preservingOriginal()->toMediaCollection('project_guides', 'public_projects_files');

        //supplements
        $project_asynchronous_programming->addMedia(storage_path('projects/asycnchronous-programming/supplements/.env.example'))->preservingOriginal()->toMediaCollection('project_supplements', 'public_projects_files');
        $project_asynchronous_programming->addMedia(storage_path('projects/asycnchronous-programming/supplements/.gitignore'))->preservingOriginal()->toMediaCollection('project_supplements', 'public_projects_files');

        //zips
        $project_asynchronous_programming->addMedia(storage_path('projects/asycnchronous-programming/zips/guides.zip'))->preservingOriginal()->toMediaCollection('project_zips', 'public_projects_files');
        $project_asynchronous_programming->addMedia(storage_path('projects/asycnchronous-programming/zips/supplements.zip'))->preservingOriginal()->toMediaCollection('project_zips', 'public_projects_files');

        //tests
        // $project_asynchronous_programming->addMedia(storage_path('projects/asycnchronous-programming/tests/praktikum1.test.js'))->preservingOriginal()->toMediaCollection('project_tests', 'public_projects_files');
        // $project_asynchronous_programming->addMedia(storage_path('projects/asycnchronous-programming/tests/praktikum2Integration.test.js'))->preservingOriginal()->toMediaCollection('project_tests', 'public_projects_files');
        // $project_asynchronous_programming->addMedia(storage_path('projects/asycnchronous-programming/tests/praktikum2Unit.test.js'))->preservingOriginal()->toMediaCollection('project_tests', 'public_projects_files');
        // $project_asynchronous_programming->addMedia(storage_path('projects/asycnchronous-programming/tests/praktikum3Integration.test.js'))->preservingOriginal()->toMediaCollection('project_tests', 'public_projects_files');
        // $project_asynchronous_programming->addMedia(storage_path('projects/asycnchronous-programming/tests/praktikum3Unit.test.js'))->preservingOriginal()->toMediaCollection('project_tests', 'public_projects_files');
        $project_asynchronous_programming->addMedia(storage_path('projects/asycnchronous-programming/tests/unit/modul1-unit.test.js'))->preservingOriginal()->toMediaCollection('project_tests', 'public_projects_files');
        $project_asynchronous_programming->addMedia(storage_path('projects/asycnchronous-programming/tests/unit/modul2-unit.test.js'))->preservingOriginal()->toMediaCollection('project_tests', 'public_projects_files');
        $project_asynchronous_programming->addMedia(storage_path('projects/asycnchronous-programming/tests/unit/modul3-unit.test.js'))->preservingOriginal()->toMediaCollection('project_tests', 'public_projects_files');
        $project_asynchronous_programming->addMedia(storage_path('projects/asycnchronous-programming/tests/unit/modul4-unit.test.js'))->preservingOriginal()->toMediaCollection('project_tests', 'public_projects_files');
        $project_asynchronous_programming->addMedia(storage_path('projects/asycnchronous-programming/tests/unit/modul5-unit.test.js'))->preservingOriginal()->toMediaCollection('project_tests', 'public_projects_files');
        $project_asynchronous_programming->addMedia(storage_path('projects/asycnchronous-programming/tests/integration/modul1-integration.test.js'))->preservingOriginal()->toMediaCollection('project_tests', 'public_projects_files');
        $project_asynchronous_programming->addMedia(storage_path('projects/asycnchronous-programming/tests/integration/modul2-integration.test.js'))->preservingOriginal()->toMediaCollection('project_tests', 'public_projects_files');
        $project_asynchronous_programming->addMedia(storage_path('projects/asycnchronous-programming/tests/integration/modul3-integration.test.js'))->preservingOriginal()->toMediaCollection('project_tests', 'public_projects_files');
        $project_asynchronous_programming->addMedia(storage_path('projects/asycnchronous-programming/tests/integration/modul4-integration.test.js'))->preservingOriginal()->toMediaCollection('project_tests', 'public_projects_files');
        $project_asynchronous_programming->addMedia(storage_path('projects/asycnchronous-programming/tests/integration/modul5-integration.test.js'))->preservingOriginal()->toMediaCollection('project_tests', 'public_projects_files');



        //         $project_api_experiment = Project::where('title', 'api-experiment')->first();
        //         $project_auth_experiment = Project::where('title', 'auth-experiment')->first();

        //         // images
        //         $project_api_experiment->addMedia(storage_path('app/public/assets/projects/api-experiment/images/api-experiment.png'))->toMediaCollection('project_images', 'public_projects_files');

        //         $project_auth_experiment->addMedia(storage_path('app/public/assets/projects/auth-experiment/images/auth-experiment.png'))->toMediaCollection('project_images', 'public_projects_files');

        //         // files
        //         $project_api_experiment->addMedia(storage_path('app/public/assets/projects/api-experiment/files/.env'))->toMediaCollection('project_files', 'public_projects_files');
        //         $project_api_experiment->addMedia(storage_path('app/public/assets/projects/api-experiment/files/package.json'))->toMediaCollection('project_files', 'public_projects_files');

        //         $project_auth_experiment->addMedia(storage_path('app/public/assets/projects/auth-experiment/files/.env'))->toMediaCollection('project_files', 'public_projects_files');
        //         $project_auth_experiment->addMedia(storage_path('app/public/assets/projects/auth-experiment/files/package.json'))->toMediaCollection('project_files', 'public_projects_files');

        //         // tests
        //         $project_api_experiment->addMedia(storage_path('app/public/assets/projects/api-experiment/tests/api/testA01.test.js'))->toMediaCollection('project_tests_api', 'public_projects_files');
        //         $project_api_experiment->addMedia(storage_path('app/public/assets/projects/api-experiment/tests/api/testA02.test.js'))->toMediaCollection('project_tests_api', 'public_projects_files');
        //         $project_api_experiment->addMedia(storage_path('app/public/assets/projects/api-experiment/tests/api/testA03.test.js'))->toMediaCollection('project_tests_api', 'public_projects_files');
        //         $project_api_experiment->addMedia(storage_path('app/public/assets/projects/api-experiment/tests/api/testA04.test.js'))->toMediaCollection('project_tests_api', 'public_projects_files');
        //         $project_api_experiment->addMedia(storage_path('app/public/assets/projects/api-experiment/tests/api/testA05.test.js'))->toMediaCollection('project_tests_api', 'public_projects_files');
        //         $project_api_experiment->addMedia(storage_path('app/public/assets/projects/api-experiment/tests/web/testA01.test.js'))->toMediaCollection('project_tests_web', 'public_projects_files');
        //         $project_api_experiment->addMedia(storage_path('app/public/assets/projects/api-experiment/tests/web/testA02.test.js'))->toMediaCollection('project_tests_web', 'public_projects_files');
        //         $project_api_experiment->addMedia(storage_path('app/public/assets/projects/api-experiment/tests/web/testA03.test.js'))->toMediaCollection('project_tests_web', 'public_projects_files');
        //         $project_api_experiment->addMedia(storage_path('app/public/assets/projects/api-experiment/tests/web/testA04.test.js'))->toMediaCollection('project_tests_web', 'public_projects_files');
        //         $project_api_experiment->addMedia(storage_path('app/public/assets/projects/api-experiment/tests/web/testA05.test.js'))->toMediaCollection('project_tests_web', 'public_projects_files');
        //         $project_api_experiment->addMedia(storage_path('app/public/assets/projects/api-experiment/tests/web/images/create-product-page.png'))->toMediaCollection('project_tests_images', 'public_projects_files');
        //         $project_api_experiment->addMedia(storage_path('app/public/assets/projects/api-experiment/tests/web/images/error-notFound-page.png'))->toMediaCollection('project_tests_images', 'public_projects_files');
        //         $project_api_experiment->addMedia(storage_path('app/public/assets/projects/api-experiment/tests/web/images/index-page.png'))->toMediaCollection('project_tests_images', 'public_projects_files');
        //         $project_api_experiment->addMedia(storage_path('app/public/assets/projects/api-experiment/tests/web/images/no-products-found-page.png'))->toMediaCollection('project_tests_images', 'public_projects_files');
        //         $project_api_experiment->addMedia(storage_path('app/public/assets/projects/api-experiment/tests/web/images/not-found-product-page.png'))->toMediaCollection('project_tests_images', 'public_projects_files');
        //         $project_api_experiment->addMedia(storage_path('app/public/assets/projects/api-experiment/tests/web/images/product-details-page.png'))->toMediaCollection('project_tests_images', 'public_projects_files');
        //         $project_api_experiment->addMedia(storage_path('app/public/assets/projects/api-experiment/tests/web/images/products-table-page.png'))->toMediaCollection('project_tests_images', 'public_projects_files');
        //         $project_api_experiment->addMedia(storage_path('app/public/assets/projects/api-experiment/tests/web/images/update-product-page.png'))->toMediaCollection('project_tests_images', 'public_projects_files');

        //         $project_auth_experiment->addMedia(storage_path('app/public/assets/projects/auth-experiment/tests/api/testB01.test.js'))->toMediaCollection('project_tests_api', 'public_projects_files');
        //         $project_auth_experiment->addMedia(storage_path('app/public/assets/projects/auth-experiment/tests/api/testB02.test.js'))->toMediaCollection('project_tests_api', 'public_projects_files');
        //         $project_auth_experiment->addMedia(storage_path('app/public/assets/projects/auth-experiment/tests/api/testB03.test.js'))->toMediaCollection('project_tests_api', 'public_projects_files');
        //         $project_auth_experiment->addMedia(storage_path('app/public/assets/projects/auth-experiment/tests/api/testB04.test.js'))->toMediaCollection('project_tests_api', 'public_projects_files');
        //         $project_auth_experiment->addMedia(storage_path('app/public/assets/projects/auth-experiment/tests/api/testB05.test.js'))->toMediaCollection('project_tests_api', 'public_projects_files');
        //         $project_auth_experiment->addMedia(storage_path('app/public/assets/projects/auth-experiment/tests/web/testB01.test.js'))->toMediaCollection('project_tests_web', 'public_projects_files');
        //         $project_auth_experiment->addMedia(storage_path('app/public/assets/projects/auth-experiment/tests/web/testB02.test.js'))->toMediaCollection('project_tests_web', 'public_projects_files');
        //         $project_auth_experiment->addMedia(storage_path('app/public/assets/projects/auth-experiment/tests/web/testB03.test.js'))->toMediaCollection('project_tests_web', 'public_projects_files');
        //         $project_auth_experiment->addMedia(storage_path('app/public/assets/projects/auth-experiment/tests/web/testB04.test.js'))->toMediaCollection('project_tests_web', 'public_projects_files');
        //         $project_auth_experiment->addMedia(storage_path('app/public/assets/projects/auth-experiment/tests/web/testB05.test.js'))->toMediaCollection('project_tests_web', 'public_projects_files');
        //         $project_auth_experiment->addMedia(storage_path('app/public/assets/projects/auth-experiment/tests/web/images/edit-page.png'))->toMediaCollection('project_tests_images', 'public_projects_files');
        //         $project_auth_experiment->addMedia(storage_path('app/public/assets/projects/auth-experiment/tests/web/images/edit-password-page.png'))->toMediaCollection('project_tests_images', 'public_projects_files');
        //         $project_auth_experiment->addMedia(storage_path('app/public/assets/projects/auth-experiment/tests/web/images/error-notFound-page.png'))->toMediaCollection('project_tests_images', 'public_projects_files');
        //         $project_auth_experiment->addMedia(storage_path('app/public/assets/projects/auth-experiment/tests/web/images/index-page.png'))->toMediaCollection('project_tests_images', 'public_projects_files');
        //         $project_auth_experiment->addMedia(storage_path('app/public/assets/projects/auth-experiment/tests/web/images/index-page-after-register.png'))->toMediaCollection('project_tests_images', 'public_projects_files');
        //         $project_auth_experiment->addMedia(storage_path('app/public/assets/projects/auth-experiment/tests/web/images/login-page.png'))->toMediaCollection('project_tests_images', 'public_projects_files');
        //         $project_auth_experiment->addMedia(storage_path('app/public/assets/projects/auth-experiment/tests/web/images/login-page-with-error.png'))->toMediaCollection('project_tests_images', 'public_projects_files');
        //         $project_auth_experiment->addMedia(storage_path('app/public/assets/projects/auth-experiment/tests/web/images/profile-page.png'))->toMediaCollection('project_tests_images', 'public_projects_files');
        //         $project_auth_experiment->addMedia(storage_path('app/public/assets/projects/auth-experiment/tests/web/images/register-page.png'))->toMediaCollection('project_tests_images', 'public_projects_files');

        //         // guides
        //         $project_api_experiment->addMedia(storage_path('app/public/assets/projects/api-experiment/guides/Guide A01.pdf'))->toMediaCollection('project_guides', 'public_projects_files');
        //         $project_api_experiment->addMedia(storage_path('app/public/assets/projects/api-experiment/guides/Guide A02.pdf'))->toMediaCollection('project_guides', 'public_projects_files');
        //         $project_api_experiment->addMedia(storage_path('app/public/assets/projects/api-experiment/guides/Guide A03.pdf'))->toMediaCollection('project_guides', 'public_projects_files');
        //         $project_api_experiment->addMedia(storage_path('app/public/assets/projects/api-experiment/guides/Guide A04.pdf'))->toMediaCollection('project_guides', 'public_projects_files');
        //         $project_api_experiment->addMedia(storage_path('app/public/assets/projects/api-experiment/guides/Guide A05.pdf'))->toMediaCollection('project_guides', 'public_projects_files');

        //         $project_auth_experiment->addMedia(storage_path('app/public/assets/projects/auth-experiment/guides/Guide B01.pdf'))->toMediaCollection('project_guides', 'public_projects_files');
        //         $project_auth_experiment->addMedia(storage_path('app/public/assets/projects/auth-experiment/guides/Guide B02.pdf'))->toMediaCollection('project_guides', 'public_projects_files');
        //         $project_auth_experiment->addMedia(storage_path('app/public/assets/projects/auth-experiment/guides/Guide B03.pdf'))->toMediaCollection('project_guides', 'public_projects_files');
        //         $project_auth_experiment->addMedia(storage_path('app/public/assets/projects/auth-experiment/guides/Guide B04.pdf'))->toMediaCollection('project_guides', 'public_projects_files');
        //         $project_auth_experiment->addMedia(storage_path('app/public/assets/projects/auth-experiment/guides/Guide B05.pdf'))->toMediaCollection('project_guides', 'public_projects_files');

        //         // supplements
        //         $project_api_experiment->addMedia(storage_path('app/public/assets/projects/api-experiment/supplements/.env.example'))->toMediaCollection('project_supplements', 'public_projects_files');
        //         $project_api_experiment->addMedia(storage_path('app/public/assets/projects/api-experiment/supplements/.gitignore'))->toMediaCollection('project_supplements', 'public_projects_files');
        //         $project_api_experiment->addMedia(storage_path('app/public/assets/projects/api-experiment/supplements/initial_data.json'))->toMediaCollection('project_supplements', 'public_projects_files');
        //         $project_api_experiment->addMedia(storage_path('app/public/assets/projects/api-experiment/supplements/main.css'))->toMediaCollection('project_supplements', 'public_projects_files');
        //         $project_api_experiment->addMedia(storage_path('app/public/assets/projects/api-experiment/supplements/main.ejs'))->toMediaCollection('project_supplements', 'public_projects_files');

        //         $project_auth_experiment->addMedia(storage_path('app/public/assets/projects/auth-experiment/supplements/.env.example'))->toMediaCollection('project_supplements', 'public_projects_files');
        //         $project_auth_experiment->addMedia(storage_path('app/public/assets/projects/auth-experiment/supplements/.gitignore'))->toMediaCollection('project_supplements', 'public_projects_files');
        //         $project_auth_experiment->addMedia(storage_path('app/public/assets/projects/auth-experiment/supplements/main.css'))->toMediaCollection('project_supplements', 'public_projects_files');
        //         $project_auth_experiment->addMedia(storage_path('app/public/assets/projects/auth-experiment/supplements/main.ejs'))->toMediaCollection('project_supplements', 'public_projects_files');

        //         // zips
        //         $project_api_experiment->addMedia(storage_path('app/public/assets/projects/api-experiment/zips/guides.zip'))->toMediaCollection('project_zips', 'public_projects_files');
        //         $project_api_experiment->addMedia(storage_path('app/public/assets/projects/api-experiment/zips/supplements.zip'))->toMediaCollection('project_zips', 'public_projects_files');
        //         $project_api_experiment->addMedia(storage_path('app/public/assets/projects/api-experiment/zips/tests.zip'))->toMediaCollection('project_zips', 'public_projects_files');

        //         $project_auth_experiment->addMedia(storage_path('app/public/assets/projects/auth-experiment/zips/guides.zip'))->toMediaCollection('project_zips', 'public_projects_files');
        //         $project_auth_experiment->addMedia(storage_path('app/public/assets/projects/auth-experiment/zips/supplements.zip'))->toMediaCollection('project_zips', 'public_projects_files');
        //         $project_auth_experiment->addMedia(storage_path('app/public/assets/projects/auth-experiment/zips/tests.zip'))->toMediaCollection('project_zips', 'public_projects_files');
    }
}
