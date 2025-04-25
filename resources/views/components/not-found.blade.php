@props(['message' => 'Not Found', 'iconWidth' => 'w-full'])
<div class="{{$iconWidth}} h-40">
    <div class="flex flex-col items-center justify-center h-full">
        <x-svg.not-found />
        <p class="mt-4 text-xl font-semibold text-gray-500 dark:text-gray-400">{{ $message }}</p>
    </div>
</div>