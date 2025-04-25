<div class="p-6 text-gray-900 dark:text-gray-100">
    <div class="mb-3 py-2 border-b w-fit">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Downloads') }}
        </h2>
    </div>
    <div class="w-full p-5 ">
        <p class="mt-1 text-sm font-normal text-gray-500 dark:text-gray-400">Project Downloads Center</p>
    </div>
    <div class="grid grid-col-1 md:grid-cols-3 gap-4 py-2 items-center px-5">
        @php
        $guidesCount = count($project->getMedia('project_guides'));
        $supplementsCount = count($project->getMedia('project_supplements'));
        $testsCount = count($project->getMedia('project_tests_api')) + count($project->getMedia('project_tests_web'));
        @endphp
        <div>
            @if ($guidesCount > 0)
            <button onclick="download('guides')"
                class="w-full relative inline-flex items-center justify-center p-0.5 mb-2 mr-2 overflow-hidden text-sm font-medium text-gray-900 rounded-lg group bg-gradient-to-br from-secondary-500 to-blue-500 group-hover:from-secondary-500 group-hover:to-blue-500 hover:text-white dark:text-white focus:ring-4 focus:outline-none focus:ring-secondary-200 dark:focus:ring-secondary-800">
                <span
                    class="w-full relative px-5 py-2.5 transition-all ease-in duration-75 bg-white dark:bg-gray-900 rounded-md group-hover:bg-opacity-0">
                    All Guides ({{$guidesCount}})
                </span>
            </button>
            @else
            <button disabled
                class="cursor-not-allowed w-full relative inline-flex items-center justify-center p-0.5 mb-2 mr-2 overflow-hidden text-sm font-medium text-gray-900 rounded-lg group bg-gradient-to-br from-secondary-500 to-blue-500 group-hover:from-secondary-500 group-hover:to-blue-500 hover:text-white dark:text-white focus:ring-4 focus:outline-none focus:ring-secondary-200 dark:focus:ring-secondary-800">
                <span
                    class="w-full relative px-5 py-2.5 transition-all ease-in duration-75 bg-white dark:bg-gray-900 rounded-md">
                    No Guides
                </span>
            </button>
            @endif
        </div>
        <div>
            @if ($supplementsCount > 0)
            <button onclick="download('supplements')"
                class="w-full relative inline-flex items-center justify-center p-0.5 mb-2 mr-2 overflow-hidden text-sm font-medium text-gray-900 rounded-lg group bg-gradient-to-br from-secondary-500 to-blue-500 group-hover:from-secondary-500 group-hover:to-blue-500 hover:text-white dark:text-white focus:ring-4 focus:outline-none focus:ring-secondary-200 dark:focus:ring-secondary-800">
                <span
                    class="w-full relative px-5 py-2.5 transition-all ease-in duration-75 bg-white dark:bg-gray-900 rounded-md group-hover:bg-opacity-0">
                    All Supplements ({{$supplementsCount}})
                </span>
            </button>
            @else
            <button disabled
                class="cursor-not-allowed w-full relative inline-flex items-center justify-center p-0.5 mb-2 mr-2 overflow-hidden text-sm font-medium text-gray-900 rounded-lg group bg-gradient-to-br from-secondary-500 to-blue-500 group-hover:from-secondary-500 group-hover:to-blue-500 hover:text-white dark:text-white focus:ring-4 focus:outline-none focus:ring-secondary-200 dark:focus:ring-secondary-800">
                <span
                    class="w-full relative px-5 py-2.5 transition-all ease-in duration-75 bg-white dark:bg-gray-900 rounded-md">
                    No Supplements
                </span>
            </button>
            @endif
        </div>
        <div>
            @if($testsCount > 0)
            <button onclick="download('tests')"
                class="w-full relative inline-flex items-center justify-center p-0.5 mb-2 mr-2 overflow-hidden text-sm font-medium text-gray-900 rounded-lg group bg-gradient-to-br from-secondary-500 to-blue-500 group-hover:from-secondary-500 group-hover:to-blue-500 hover:text-white dark:text-white focus:ring-4 focus:outline-none focus:ring-secondary-200 dark:focus:ring-secondary-800">
                <span
                    class="w-full relative px-5 py-2.5 transition-all ease-in duration-75 bg-white dark:bg-gray-900 rounded-md group-hover:bg-opacity-0">
                    All Tests ({{$testsCount}})
                </span>
            </button>
            @else
            <button disabled
                class="cursor-not-allowed w-full relative inline-flex items-center justify-center p-0.5 mb-2 mr-2 overflow-hidden text-sm font-medium text-gray-900 rounded-lg group bg-gradient-to-br from-secondary-500 to-blue-500 group-hover:from-secondary-500 group-hover:to-blue-500 hover:text-white dark:text-white focus:ring-4 focus:outline-none focus:ring-secondary-200 dark:focus:ring-secondary-800">
                <span
                    class="w-full relative px-5 py-2.5 transition-all ease-in duration-75 bg-white dark:bg-gray-900 rounded-md">
                    No Tests
                </span>
            </button>
            @endif
        </div>
    </div>
</div>
@section('scripts', 'projects.partials.downloads.scripts')
<script>
    function download(type){
        $.ajax({
            url: '/projects/project/{{$project->id}}/download',
            type: 'GET',
            data: {type: type},
            success: function (response) {
                window.location.href = response;
            },
            error: function (error) {
                alert('Something went wrong. Please try again later.');
                console.log(error);
            }
        });
    }
</script>