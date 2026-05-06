<div class="space-y-5">

    {{-- Header --}}
    <div class="rounded-2xl bg-white shadow-sm overflow-hidden border border-slate-200/60">
        <div class="h-2.5 bg-gradient-to-r from-violet-500 to-purple-600"></div>
        <div class="p-6 sm:p-8">
            <div class="flex items-center gap-3">
                <a href="{{ route('release.forms', $release->releaseSet->public_token) }}"
                    class="flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 transition shrink-0">
                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <p class="text-xs text-slate-400 font-medium">{{ $release->releaseSet->name }}</p>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 leading-tight">
                        {{ $release->form->title ?? 'Form History' }}
                    </h1>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-violet-700 bg-violet-50 rounded-lg px-3 py-1.5 border border-violet-100">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                    </svg>
                    {{ $this->submissions->count() }} submission{{ $this->submissions->count() != 1 ? 's' : '' }}
                </span>
                @if ($release->releaseSet->days_remaining <= 3)
                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-amber-700 bg-amber-50 rounded-lg px-3 py-1.5 border border-amber-200">
                        {{ $release->releaseSet->days_remaining }} day{{ $release->releaseSet->days_remaining != 1 ? 's' : '' }} remaining
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Add new button --}}
    <div class="flex justify-end">
        <button
            wire:click="addNew"
            class="inline-flex items-center gap-2 rounded-xl bg-violet-600 hover:bg-violet-700 text-white font-semibold px-4 py-2.5 text-sm transition-colors"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Add New Submission
        </button>
    </div>

    {{-- Submissions list --}}
    @forelse ($this->submissions as $submission)
        @php
            $isDraft = $submission->status === 'draft';
            $token   = $release->releaseSet->public_token;
            $editUrl = route('release.submission.edit', [$token, $release->id, $submission->id]);
        @endphp

        <div class="rounded-2xl bg-white shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="flex">
                <div @class([
                    'w-1 shrink-0 rounded-l-2xl',
                    'bg-gradient-to-b from-amber-400 to-orange-400'  => $isDraft,
                    'bg-gradient-to-b from-emerald-400 to-teal-500'  => !$isDraft,
                ])></div>

                <div class="flex-1 p-5 sm:p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            {{-- Status + date --}}
                            <div class="flex items-center gap-2 flex-wrap mb-3">
                                @if ($isDraft)
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-700 bg-amber-50 rounded-lg px-2.5 py-1 border border-amber-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                        Draft
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 rounded-lg px-2.5 py-1 border border-emerald-100">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                        </svg>
                                        Submitted
                                    </span>
                                @endif
                                <span class="text-xs text-slate-400">
                                    {{ $submission->submitted_at?->format('d M Y, H:i') ?? $submission->created_at->format('d M Y, H:i') }}
                                </span>
                            </div>

                            {{-- Preview answers --}}
                            @if ($this->previewQuestions->isNotEmpty())
                                <dl class="space-y-1.5">
                                    @foreach ($this->previewQuestions as $question)
                                        @php
                                            $answer = $submission->answers->firstWhere('release_question_id', $question->id);
                                        @endphp
                                        @if ($answer)
                                            <div class="flex gap-2 text-xs">
                                                <dt class="shrink-0 font-medium text-slate-500 max-w-[120px] truncate">{{ $question->label }}:</dt>
                                                <dd class="text-slate-700 truncate">{{ $answer->display_value ?? $answer->value ?? '—' }}</dd>
                                            </div>
                                        @endif
                                    @endforeach
                                </dl>
                            @endif
                        </div>

                        {{-- Actions --}}
                        <div class="shrink-0 flex flex-col gap-2 items-end">
                            <a href="{{ $editUrl }}"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-medium px-3 py-1.5 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                </svg>
                                {{ $isDraft ? 'Edit' : 'View' }}
                            </a>
                            <button
                                wire:click="duplicateFrom({{ $submission->id }})"
                                wire:loading.attr="disabled"
                                wire:target="duplicateFrom({{ $submission->id }})"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-indigo-200 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-medium px-3 py-1.5 transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                            >
                                <span wire:loading.remove wire:target="duplicateFrom({{ $submission->id }})">
                                    <svg class="w-3.5 h-3.5 inline" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75" />
                                    </svg>
                                    Duplicate
                                </span>
                                <span wire:loading wire:target="duplicateFrom({{ $submission->id }})" class="inline-flex items-center gap-1">
                                    <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    Copying...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="rounded-2xl bg-white border border-slate-200/60 shadow-sm p-10 text-center space-y-4">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-slate-100 mx-auto">
                <svg class="w-7 h-7 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-slate-700">No submissions yet</p>
                <p class="text-xs text-slate-400 mt-1">Create your first submission to get started.</p>
            </div>
            <button
                wire:click="addNew"
                class="inline-flex items-center gap-2 rounded-xl bg-violet-600 hover:bg-violet-700 text-white font-semibold px-4 py-2.5 text-sm transition-colors"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Create First Submission
            </button>
        </div>
    @endforelse

</div>
