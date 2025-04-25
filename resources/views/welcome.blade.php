<x-guest-layout>
    @if (Route::has('login'))
    <div class="sm:fixed sm:top-0 sm:right-0 p-6 text-right">
        @auth
        <a href="{{ url('/dashboard') }}"
            class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Dashboard</a>
        @else
        <a href="{{ route('login') }}"
            class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Log
            in</a>

        @if (Route::has('register'))
        <a href="{{ route('register') }}"
            class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Register</a>
        @endif
        @endauth
    </div>
    @endif
    <p class="dark:text-gray-300 text-center">
        @auth
        Welcome, {{ Auth::user()->name }}.
        @else
        Welcome, login to view your dashboard.
        @endauth
    </p>
</x-guest-layout>