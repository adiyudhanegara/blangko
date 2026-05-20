@php $locale = app()->getLocale(); @endphp
<div class="flex items-center gap-0.5 me-2 self-center">
    <a href="{{ route('lang.switch', 'id') }}"
       class="px-2 py-1 text-xs font-semibold rounded-md transition-colors
              {{ $locale === 'id'
                  ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300'
                  : 'text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300' }}">
        ID
    </a>
    <span class="text-gray-300 dark:text-gray-600 select-none text-xs">|</span>
    <a href="{{ route('lang.switch', 'en') }}"
       class="px-2 py-1 text-xs font-semibold rounded-md transition-colors
              {{ $locale === 'en'
                  ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300'
                  : 'text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300' }}">
        EN
    </a>
</div>
