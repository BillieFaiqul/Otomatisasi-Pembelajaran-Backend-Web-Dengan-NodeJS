@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-gray-300 dark:border-gray-700
dark:bg-gray-900 dark:text-gray-300 focus:border-secondary-500 dark:focus:border-secondary-600 focus:ring-secondary-500
dark:focus:ring-secondary-600 rounded-md shadow-sm']) !!}>