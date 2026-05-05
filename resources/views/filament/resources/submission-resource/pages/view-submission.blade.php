<x-filament-panels::page>
    @php
        $record    = $this->getRecord();
        $questions = $this->questions;
        $answerMap = $this->answerMap;

        $record->load(['participant.division', 'formRelease.form']);
    @endphp

    {{-- Participant & Release info header --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <div class="rounded-xl bg-white dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10 px-5 py-4 space-y-1">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Participant</p>
            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $record->participant->name }}</p>
        </div>

        <div class="rounded-xl bg-white dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10 px-5 py-4 space-y-1">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Division</p>
            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                {{ $record->participant->division?->name ?? '—' }}
            </p>
        </div>

        <div class="rounded-xl bg-white dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10 px-5 py-4 space-y-1">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Status</p>
            <span @class([
                'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold',
                'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400' => $record->status === 'submitted',
                'bg-warning-100 text-warning-700 dark:bg-warning-500/20 dark:text-warning-400' => $record->status === 'draft',
            ])>
                {{ ucfirst($record->status) }}
            </span>
        </div>

        <div class="rounded-xl bg-white dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10 px-5 py-4 space-y-1">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Submitted At</p>
            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                {{ $record->submitted_at?->format('d M Y, H:i') ?? '—' }}
            </p>
        </div>

    </div>

    {{-- Release info --}}
    <div class="rounded-xl bg-white dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10 px-5 py-4 flex items-start justify-between gap-4 flex-wrap">
        <div class="space-y-0.5">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Release</p>
            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $record->formRelease->name }}</p>
        </div>
        <div class="space-y-0.5">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Form</p>
            <p class="text-sm text-gray-600 dark:text-gray-300">{{ $record->formRelease->form->title }}</p>
        </div>
        @if ($record->formRelease->start_at || $record->formRelease->end_at)
            <div class="space-y-0.5">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Period</p>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    {{ $record->formRelease->start_at?->format('d M Y') ?? '?' }}
                    —
                    {{ $record->formRelease->end_at?->format('d M Y') ?? '?' }}
                </p>
            </div>
        @endif
    </div>

    {{-- Answers --}}
    <div class="space-y-3">
        <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wide">
            Answers ({{ $questions->count() }} questions)
        </h3>

        @forelse ($questions as $i => $question)
            @php
                $answer = $answerMap->get($question->id);
            @endphp

            <div class="rounded-xl bg-white dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10 overflow-hidden">
                <div class="flex">
                    {{-- Question number accent --}}
                    <div class="w-1 shrink-0 bg-indigo-400"></div>

                    <div class="flex-1 p-5">
                        {{-- Question --}}
                        <div class="flex items-start gap-3 mb-3">
                            <span class="shrink-0 w-6 h-6 rounded-full bg-indigo-50 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 text-xs font-bold flex items-center justify-center ring-1 ring-indigo-100 dark:ring-indigo-500/30">
                                {{ $i + 1 }}
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 leading-snug">
                                    {{ $question->label }}
                                    @if ($question->is_required)
                                        <span class="text-red-400 text-xs ml-1">required</span>
                                    @endif
                                </p>
                                @if ($question->help_text)
                                    <p class="mt-0.5 text-xs text-gray-400">{{ $question->help_text }}</p>
                                @endif
                                <span class="inline-block mt-1 text-xs text-gray-400 bg-gray-100 dark:bg-gray-800 rounded px-1.5 py-0.5">
                                    {{ $question->type }}
                                </span>
                            </div>
                        </div>

                        {{-- Answer --}}
                        <div class="ml-9">
                            @if (!$answer)
                                <p class="text-sm text-gray-400 italic">No answer provided</p>

                            @elseif ($question->type === 'file')
                                @if ($answer->file_path)
                                    <div class="inline-flex items-center gap-2 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/30 px-3 py-2 text-sm text-indigo-700 dark:text-indigo-300">
                                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" />
                                        </svg>
                                        {{ $answer->file_original_name ?? basename($answer->file_path) }}
                                    </div>
                                @else
                                    <p class="text-sm text-gray-400 italic">No file uploaded</p>
                                @endif

                            @elseif ($question->type === 'checkbox')
                                @php $values = $answer->value_json ?? []; @endphp
                                @if (empty($values))
                                    <p class="text-sm text-gray-400 italic">No options selected</p>
                                @else
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($values as $val)
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/30 px-3 py-1 text-xs font-medium text-indigo-700 dark:text-indigo-300">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                </svg>
                                                {{ $val }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                            @elseif ($question->type === 'radio' || $question->type === 'select')
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 px-3 py-1 text-xs font-medium text-emerald-700 dark:text-emerald-300">
                                    {{ $answer->value ?? '—' }}
                                </span>

                            @elseif ($question->type === 'textarea')
                                <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed bg-gray-50 dark:bg-gray-800 rounded-lg px-4 py-3">{{ $answer->value ?? '—' }}</p>

                            @else
                                <p class="text-sm text-gray-800 dark:text-gray-200 font-medium">{{ $answer->value ?? '—' }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        @empty
            <div class="rounded-xl bg-white dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10 p-8 text-center">
                <p class="text-sm text-gray-400">No questions found for this release.</p>
            </div>
        @endforelse
    </div>

</x-filament-panels::page>
