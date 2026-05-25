<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    @php
        $statePath    = $getStatePath();
        $currentValue = $getState() ?? 'document';
        $icons        = $field->icons();
    @endphp

    <div x-data="{ selected: @js($currentValue) }">
        <div class="grid grid-cols-4 sm:grid-cols-6 gap-2 p-1">
            @foreach ($icons as $key => $icon)
                <button
                    type="button"
                    wire:key="icon-opt-{{ $key }}"
                    x-on:click="selected = '{{ $key }}'; $wire.set('{{ $statePath }}', '{{ $key }}')"
                    :class="selected === '{{ $key }}'
                        ? 'ring-2 ring-primary-500 bg-primary-50 dark:bg-primary-950/40'
                        : 'ring-1 ring-gray-200 dark:ring-white/10 hover:ring-primary-300 dark:hover:ring-primary-700 bg-white dark:bg-white/5'"
                    class="group flex flex-col items-center gap-1.5 rounded-xl p-3 transition-all duration-150 cursor-pointer"
                    title="{{ $icon['label'] }}"
                >
                    <svg
                        :class="selected === '{{ $key }}' ? 'text-primary-600 dark:text-primary-400' : 'text-gray-500 dark:text-gray-400 group-hover:text-primary-500'"
                        class="w-6 h-6 transition-colors duration-150"
                        fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon['path'] }}"/>
                    </svg>
                    <span
                        :class="selected === '{{ $key }}' ? 'text-primary-600 dark:text-primary-400 font-medium' : 'text-gray-400 dark:text-gray-500'"
                        class="text-xs leading-tight text-center transition-colors duration-150"
                    >{{ $icon['label'] }}</span>
                </button>
            @endforeach
        </div>
    </div>
</x-dynamic-component>
