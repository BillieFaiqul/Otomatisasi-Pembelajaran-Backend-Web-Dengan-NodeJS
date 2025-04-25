<div class="p-6 text-gray-900 dark:text-gray-100">
    @if (isset($projects) && count($projects) > 0)
    <div class="mb-3 py-2 border-b w-fit">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Projects') }}
        </h2>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 py-2">
        @foreach ($projects as $project)
        @include('projects.partials.card')
        @endforeach
    </div>
    @else
    <x-not-found message="No Projects Found" />
    @endif
</div>