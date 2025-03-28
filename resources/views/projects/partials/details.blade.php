<div class="p-6 text-gray-900 dark:text-gray-100">
    <div class="mb-3 py-2 border-b w-fit">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Poject Details') }}
        </h2>
    </div>
    <div class="grid grid-col-1 md:grid-cols-2 gap-4 py-2">
        <div>
            <div class="px-6 py-4">
                <div class="font-bold text-xl mb-2 text-gray-800 dark:text-white">{{$project->title}}</div>
                <p class="text-gray-700 text-base dark:text-gray-400 leading-tight">
                    {{ $project->description }}
                </p>
            </div>
            <div class="mt-auto px-6 pt-4 pb-2">
                @forelse ($project->tech_stack as $key => $stack)
                <span
                    class=" inline-block bg-gray-200 dark:bg-gray-700 rounded-full px-3 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 mr-2 mb-2">
                    #{{$stack}}</span>
                @empty
                <span
                    class="inline-block bg-gray-200 dark:bg-gray-700 rounded-full px-3 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 mr-2 mb-2">#No
                    tech stack</span>
                @endforelse
            </div>
        </div>
        <div>
            <img class="w-40 mx-auto my-4" src="{{$project->getImageAttribute()}}" alt="Project {{$project->title}}"
                onerror="this.onerror=null;this.src='{{asset('placeholder.png')}}';">
        </div>
    </div>
</div>