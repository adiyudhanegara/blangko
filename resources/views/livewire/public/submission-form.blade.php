<div class="space-y-5">

    {{-- Form header card with colour band --}}
    <div class="rounded-2xl bg-white shadow-sm overflow-hidden border border-slate-200/60">
        <div class="h-2.5 bg-linear-to-r from-indigo-500 via-violet-500 to-purple-600"></div>
        <div class="p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-3">
                @if ($release->releaseSet)
                    <a href="{{ $this->backUrl() }}"
                        class="flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 transition shrink-0">
                        <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                        </svg>
                    </a>
                    <p class="text-xs text-slate-400 font-medium truncate">{{ $release->releaseSet->name }}</p>
                @endif
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 leading-tight tracking-tight">
                {{ $release->form->title ?? $release->name }}
            </h1>
            @if ($release->form->description ?? null)
                <p class="mt-3 text-sm text-slate-500 leading-relaxed">{{ $release->form->description }}</p>
            @endif
            @if ($release->releaseSet?->end_at)
                <span class="mt-4 inline-flex items-center gap-1.5 text-xs text-slate-500 bg-slate-50 rounded-lg px-3 py-1.5 border border-slate-200">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Closes {{ $release->releaseSet->end_at->format('d M Y, H:i') }}
                </span>
            @endif
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- SUCCESS STATE --}}
    {{-- ============================================================ --}}
    @if ($submitted)
        <div class="rounded-2xl bg-white border border-slate-200/60 shadow-sm overflow-hidden">
            <div class="h-1.5 bg-linear-to-r from-emerald-400 to-teal-500"></div>
            <div class="p-8 sm:p-12 text-center space-y-5">

                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-emerald-100 mx-auto ring-8 ring-emerald-50">
                    <svg class="w-10 h-10 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Response Submitted!</h2>
                    <p class="mt-2 text-slate-500 text-sm leading-relaxed">
                        Thank you for completing this form.<br>Your response has been recorded.
                    </p>
                </div>

                @if ($this->canEdit())
                    <button
                        wire:click="editResponse"
                        type="button"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-2.5
                               text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 active:bg-slate-100
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                               transition-colors duration-150"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                        </svg>
                        Edit my response
                    </button>
                @endif

            </div>
        </div>

    {{-- ============================================================ --}}
    {{-- FORM QUESTIONS --}}
    {{-- ============================================================ --}}
    @else

        {{-- Progress bar (always shown) --}}
        @php
            $totalCount    = $questions->count();
            $answeredCount = collect($questions)->filter(fn($q) => isset($answers[$q->id]) && $answers[$q->id] !== '' && $answers[$q->id] !== null && $answers[$q->id] !== [])->count();
            $progressPercent = $totalCount > 0 ? round($answeredCount / $totalCount * 100) : 0;
        @endphp
        <div class="rounded-xl bg-white border border-slate-200/60 shadow-sm px-5 py-4">
            <div class="flex justify-between items-center mb-2.5">
                <span class="text-xs font-medium text-slate-500">Progress</span>
                <span class="text-xs font-semibold text-indigo-600">{{ $answeredCount }} / {{ $totalCount }} answered</span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                <div
                    class="h-2 rounded-full bg-linear-to-r from-indigo-500 to-violet-500 transition-all duration-500"
                    style="width: {{ $progressPercent }}%"
                ></div>
            </div>
        </div>

        {{-- Question cards --}}
        @foreach ($questions as $index => $question)
            @if ($this->isVisible($question))
                <div
                    wire:key="question-{{ $question->id }}"
                    class="rounded-2xl bg-white border border-slate-200/60 shadow-sm overflow-hidden"
                >
                    {{-- Coloured left accent + question number --}}
                    <div class="flex">
                        <div class="w-1 shrink-0 bg-linear-to-b from-indigo-400 to-violet-400 rounded-l-2xl"></div>
                        <div class="flex-1 p-5 sm:p-6 space-y-3.5">

                            {{-- Label row --}}
                            <div class="flex items-start gap-3">
                                <span class="shrink-0 mt-0.5 w-6 h-6 rounded-full bg-indigo-50 text-indigo-600 text-xs font-bold flex items-center justify-center ring-1 ring-indigo-100">
                                    {{ $index + 1 }}
                                </span>
                                <div class="flex-1 min-w-0">
                                    <label
                                        for="field-{{ $question->id }}"
                                        class="block text-sm font-semibold text-slate-800 leading-snug"
                                    >
                                        {{ $question->label }}
                                        @if ($question->is_required)
                                            <span class="text-red-500 ml-0.5">*</span>
                                        @else
                                            <span class="text-slate-400 font-normal text-xs ml-1">optional</span>
                                        @endif
                                    </label>
                                    @if ($question->help_text)
                                        <p class="mt-1 text-xs text-slate-500 leading-relaxed">{{ $question->help_text }}</p>
                                    @endif
                                </div>
                            </div>

                            {{-- ---- text ---- --}}
                            @if ($question->type === 'text')
                                <input
                                    type="text"
                                    id="field-{{ $question->id }}"
                                    wire:model.live="answers.{{ $question->id }}"
                                    class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900
                                           placeholder-slate-400 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20
                                           focus:outline-none @error("answers.{$question->id}") border-red-400 @enderror"
                                >

                            {{-- ---- textarea ---- --}}
                            @elseif ($question->type === 'textarea')
                                <textarea
                                    id="field-{{ $question->id }}"
                                    wire:model.live="answers.{{ $question->id }}"
                                    rows="4"
                                    class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900
                                           placeholder-slate-400 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20
                                           focus:outline-none resize-y @error("answers.{$question->id}") border-red-400 @enderror"
                                ></textarea>

                            {{-- ---- number ---- --}}
                            @elseif ($question->type === 'number')
                                <input
                                    type="number"
                                    id="field-{{ $question->id }}"
                                    wire:model.live="answers.{{ $question->id }}"
                                    class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900
                                           placeholder-slate-400 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20
                                           focus:outline-none @error("answers.{$question->id}") border-red-400 @enderror"
                                >

                            {{-- ---- email ---- --}}
                            @elseif ($question->type === 'email')
                                <input
                                    type="email"
                                    id="field-{{ $question->id }}"
                                    wire:model.live="answers.{{ $question->id }}"
                                    placeholder="you@example.com"
                                    autocomplete="email"
                                    class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900
                                           placeholder-slate-400 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20
                                           focus:outline-none @error("answers.{$question->id}") border-red-400 @enderror"
                                >

                            {{-- ---- date ---- --}}
                            @elseif ($question->type === 'date')
                                <input
                                    type="date"
                                    id="field-{{ $question->id }}"
                                    wire:model.live="answers.{{ $question->id }}"
                                    class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900
                                           shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none
                                           @error("answers.{$question->id}") border-red-400 @enderror"
                                >

                            {{-- ---- radio ---- --}}
                            @elseif ($question->type === 'radio')
                                <div class="space-y-2">
                                    @foreach ($question->options as $option)
                                        <label
                                            class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3.5 cursor-pointer
                                                   hover:border-indigo-300 hover:bg-indigo-50/40 transition-colors duration-100
                                                   has-checked:border-indigo-500 has-checked:bg-indigo-50"
                                        >
                                            <input
                                                type="radio"
                                                name="radio-{{ $question->id }}"
                                                wire:model.live="answers.{{ $question->id }}"
                                                value="{{ $option->value }}"
                                                class="w-4 h-4 text-indigo-600 border-slate-300 focus:ring-indigo-500 focus:ring-2 shrink-0"
                                            >
                                            <span class="text-sm text-slate-800">{{ $option->label }}</span>
                                        </label>
                                    @endforeach
                                    @if ($this->isOtherSelected($question))
                                        <div class="pl-1 pt-1">
                                            <input
                                                type="text"
                                                wire:model.live="otherText.{{ $question->id }}"
                                                placeholder="Please specify…"
                                                class="block w-full rounded-xl border border-indigo-300 bg-indigo-50/30 px-4 py-2.5 text-sm text-slate-800
                                                       placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition
                                                       @error("otherText.{$question->id}") border-red-400 @enderror"
                                            >
                                            @error("otherText.{$question->id}")
                                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    @endif
                                </div>

                            {{-- ---- checkbox ---- --}}
                            @elseif ($question->type === 'checkbox')
                                <div class="space-y-2">
                                    @foreach ($question->options as $option)
                                        <label
                                            class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3.5 cursor-pointer
                                                   hover:border-indigo-300 hover:bg-indigo-50/40 transition-colors duration-100
                                                   has-checked:border-indigo-500 has-checked:bg-indigo-50"
                                        >
                                            <input
                                                type="checkbox"
                                                wire:model.live="answers.{{ $question->id }}"
                                                value="{{ $option->value }}"
                                                class="w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500 focus:ring-2 shrink-0"
                                            >
                                            <span class="text-sm text-slate-800">{{ $option->label }}</span>
                                        </label>
                                    @endforeach
                                    @if ($this->isOtherSelected($question))
                                        <div class="pl-1 pt-1">
                                            <input
                                                type="text"
                                                wire:model.live="otherText.{{ $question->id }}"
                                                placeholder="Please specify…"
                                                class="block w-full rounded-xl border border-indigo-300 bg-indigo-50/30 px-4 py-2.5 text-sm text-slate-800
                                                       placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition
                                                       @error("otherText.{$question->id}") border-red-400 @enderror"
                                            >
                                            @error("otherText.{$question->id}")
                                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    @endif
                                </div>

                            {{-- ---- select ---- --}}
                            @elseif ($question->type === 'select')
                                <div class="space-y-2">
                                    <div class="relative">
                                        <select
                                            id="field-{{ $question->id }}"
                                            wire:model.live="answers.{{ $question->id }}"
                                            class="block w-full rounded-xl border border-slate-300 bg-white pl-4 pr-10 py-3 text-sm text-slate-900
                                                   shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none appearance-none
                                                   @error("answers.{$question->id}") border-red-400 @enderror"
                                        >
                                            <option value="">-- Choose an option --</option>
                                            @foreach ($question->options as $option)
                                                <option value="{{ $option->value }}">{{ $option->label }}</option>
                                            @endforeach
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                            </svg>
                                        </div>
                                    </div>
                                    @if ($this->isOtherSelected($question))
                                        <input
                                            type="text"
                                            wire:model.live="otherText.{{ $question->id }}"
                                            placeholder="Please specify…"
                                            class="block w-full rounded-xl border border-indigo-300 bg-indigo-50/30 px-4 py-2.5 text-sm text-slate-800
                                                   placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition
                                                   @error("otherText.{$question->id}") border-red-400 @enderror"
                                        >
                                        @error("otherText.{$question->id}")
                                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>

                            {{-- ---- file ---- --}}
                            @elseif ($question->type === 'file')
                                @php
                                    $vr    = $question->validation_rules ?? [];
                                    $maxKb = $vr['max_size_kb'] ?? 5120;
                                    $types = $vr['file_types'] ?? ['pdf', 'jpg', 'jpeg', 'png', 'docx', 'xlsx'];
                                @endphp
                                <div class="space-y-2">
                                    <div
                                        class="relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed
                                               border-slate-300 bg-slate-50 px-4 py-8 text-center hover:border-indigo-400
                                               hover:bg-indigo-50/30 transition-colors duration-150 cursor-pointer"
                                        x-data
                                        @dragover.prevent
                                        @drop.prevent="$refs.fileInput{{ $question->id }}.files = $event.dataTransfer.files; $refs.fileInput{{ $question->id }}.dispatchEvent(new Event('change'))"
                                    >
                                        <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center mb-3">
                                            <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                            </svg>
                                        </div>
                                        <p class="text-sm font-medium text-slate-700">Click to upload or drag &amp; drop</p>
                                        <p class="text-xs text-slate-400 mt-1">
                                            {{ strtoupper(implode(', ', $types)) }} &bull; Max {{ round($maxKb / 1024, 1) }} MB
                                        </p>
                                        <input
                                            type="file"
                                            id="field-{{ $question->id }}"
                                            wire:model="fileUploads.{{ $question->id }}"
                                            x-ref="fileInput{{ $question->id }}"
                                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                            accept="{{ collect($types)->map(fn($t) => '.' . $t)->implode(',') }}"
                                        >
                                    </div>

                                    <div wire:loading wire:target="fileUploads.{{ $question->id }}" class="flex items-center gap-2 text-xs text-indigo-600">
                                        <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                        Uploading...
                                    </div>

                                    @if (isset($fileUploads[$question->id]) && $fileUploads[$question->id])
                                        <div class="flex items-center gap-2 rounded-xl bg-emerald-50 border border-emerald-200 px-3 py-2 text-xs text-emerald-700">
                                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            File selected
                                        </div>
                                    @endif
                                </div>
                            @endif

                            {{-- Validation error --}}
                            @error("answers.{$question->id}")
                                <p class="text-xs text-red-600 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>
                    </div>
                </div>
            @endif
        @endforeach

        <div class="h-2"></div>

    @endif

    {{-- ============================================================ --}}
    {{-- STICKY ACTION BAR --}}
    {{-- ============================================================ --}}
    @if (!$submitted)
        <div class="fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur-sm border-t border-slate-200 shadow-lg">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 py-3 flex items-center gap-3">

                <button
                    wire:click="saveDraft"
                    wire:loading.attr="disabled"
                    wire:target="saveDraft"
                    type="button"
                    class="shrink-0 flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium
                           text-slate-700 shadow-sm hover:bg-slate-50 active:bg-slate-100
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                           transition-colors duration-150 disabled:opacity-60 disabled:cursor-not-allowed"
                >
                    <span wire:loading.remove wire:target="saveDraft" class="flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M7.5 12l4.5 4.5m0 0l4.5-4.5M12 3v13.5" />
                        </svg>
                        Save Draft
                    </span>
                    <span wire:loading wire:target="saveDraft" class="flex items-center gap-1.5">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Saving...
                    </span>
                </button>

                <button
                    wire:click="submit"
                    wire:loading.attr="disabled"
                    wire:target="submit"
                    type="button"
                    class="flex-1 flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm
                           hover:bg-indigo-700 active:bg-indigo-800
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                           transition-colors duration-150 disabled:opacity-60 disabled:cursor-not-allowed"
                >
                    <span wire:loading.remove wire:target="submit" class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                        </svg>
                        Submit Form
                    </span>
                    <span wire:loading wire:target="submit" class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Submitting...
                    </span>
                </button>

            </div>
        </div>
    @endif

</div>
