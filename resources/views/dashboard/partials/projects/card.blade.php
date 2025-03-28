<div class="flex flex-col max-w-sm rounded-lg overflow-hidden shadow-lg bg-white dark:bg-gray-900 h-full">
    <img class="w-40 mx-auto my-4" src="{{$project->getImageAttribute()}}" alt="Project {{$project->title}}"
        onerror="this.onerror=null;this.src='{{asset('placeholder.png')}}';">
    <div class="px-6 py-4">
        <div class="font-bold text-xl mb-2 text-gray-800 dark:text-white">{{$project->title}}</div>
        <p class="text-gray-700 text-base dark:text-gray-400 leading-tight line-clamp-3">
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
    <div class="px-6 py-2 bg-gray-100 dark:bg-secondary">
        <a href="/projects/project/{{$project->id}}"
            class="text-xs font-semibold text-secondary dark:text-white uppercase tracking-wide">
            Read more
            <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</div>