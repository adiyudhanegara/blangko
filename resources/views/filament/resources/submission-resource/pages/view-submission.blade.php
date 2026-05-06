<x-filament-panels::page>
@php
    $record = $this->getRecord();
    $record->load(['participant.division', 'formRelease.form']);
    $questions  = $this->questions;
    $answerMap  = $this->answerMap;

    $answeredCount = $questions->filter(fn($q) =>
        $answerMap->has($q->id) &&
        ($answerMap->get($q->id)->value !== null && $answerMap->get($q->id)->value !== ''
         || $answerMap->get($q->id)->value_json !== null
         || $answerMap->get($q->id)->file_path !== null)
    )->count();
    $totalCount  = $questions->count();
    $completePct = $totalCount > 0 ? round($answeredCount / $totalCount * 100) : 0;
    $isSubmitted = $record->status === 'submitted';
@endphp

<style>
/* ── Scoped submission-view styles ─────────────────────── */
.sv { --accent: #6366f1; --accent-light: #e0e7ff; --accent-text: #4338ca; }
.dark .sv { --accent-light: rgba(99,102,241,.15); --accent-text: #a5b4fc; }

/* card */
.sv-card { background:#fff; border-radius:.75rem; box-shadow:0 0 0 1px rgba(9,9,11,.05); overflow:hidden; }
.dark .sv-card { background:#111827; box-shadow:0 0 0 1px rgba(255,255,255,.1); }

/* sections */
.sv-stack { display:flex; flex-direction:column; gap:.875rem; }

/* ── hero ─────────────────────────────── */
.sv-band { height:.5rem; }
.sv-band--submitted { background:linear-gradient(to right,#34d399,#14b8a6); }
.sv-band--draft     { background:linear-gradient(to right,#fbbf24,#fb923c); }

.sv-hero-body { padding:1.25rem 1.5rem; display:flex; flex-wrap:wrap; align-items:flex-start; gap:1.25rem; }

.sv-avatar { width:3.5rem; height:3.5rem; border-radius:9999px; display:flex; align-items:center; justify-content:center; font-size:1.25rem; font-weight:700; flex-shrink:0; }
.sv-avatar--submitted { background:#d1fae5; color:#065f46; }
.dark .sv-avatar--submitted { background:rgba(52,211,153,.2); color:#6ee7b7; }
.sv-avatar--draft { background:#fef3c7; color:#92400e; }
.dark .sv-avatar--draft { background:rgba(251,191,36,.2); color:#fcd34d; }

.sv-hero-main { flex:1; min-width:0; display:flex; flex-direction:column; gap:.375rem; }
.sv-name-row { display:flex; flex-wrap:wrap; align-items:center; gap:.5rem; }
.sv-name { font-size:1.125rem; font-weight:700; color:#111827; line-height:1.25; margin:0; }
.dark .sv-name { color:#f9fafb; }

.sv-badge { display:inline-flex; align-items:center; gap:.3rem; border-radius:9999px; padding:.25rem .625rem; font-size:.7rem; font-weight:700; letter-spacing:.03em; }
.sv-badge svg { width:.75rem; height:.75rem; flex-shrink:0; }
.sv-badge--submitted { background:#d1fae5; color:#065f46; }
.dark .sv-badge--submitted { background:rgba(52,211,153,.2); color:#6ee7b7; }
.sv-badge--draft { background:#fef3c7; color:#92400e; }
.dark .sv-badge--draft { background:rgba(251,191,36,.2); color:#fcd34d; }

.sv-meta { display:flex; flex-wrap:wrap; align-items:center; gap:.25rem 1rem; }
.sv-meta-item { display:inline-flex; align-items:center; gap:.35rem; font-size:.8125rem; color:#6b7280; }
.dark .sv-meta-item { color:#9ca3af; }
.sv-meta-item svg { width:.875rem; height:.875rem; flex-shrink:0; }
.sv-meta-item a { color:#6366f1; text-decoration:none; }
.sv-meta-item a:hover { text-decoration:underline; }
.dark .sv-meta-item a { color:#a5b4fc; }

.sv-timestamps { text-align:right; font-size:.75rem; color:#9ca3af; flex-shrink:0; display:flex; flex-direction:column; gap:.375rem; }
.dark .sv-timestamps { color:#6b7280; }
.sv-ts-label { font-weight:600; color:#6b7280; display:block; }
.dark .sv-ts-label { color:#9ca3af; }

/* ── release / progress bar ──────────────── */
.sv-release { padding:1rem 1.25rem; display:flex; flex-direction:column; gap:.75rem; }
.sv-release-row { display:flex; flex-wrap:wrap; align-items:flex-start; gap:.5rem 2rem; }
.sv-release-item { display:flex; flex-direction:column; gap:.2rem; }
.sv-release-item:last-child { margin-left:auto; text-align:right; }
.sv-rlabel { font-size:.625rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#9ca3af; }
.sv-rvalue { font-size:.875rem; font-weight:600; color:#111827; }
.dark .sv-rvalue { color:#f9fafb; }
.sv-rvalue--muted { font-weight:400; color:#4b5563; }
.dark .sv-rvalue--muted { color:#9ca3af; }
.sv-completion { font-size:.875rem; font-weight:700; }
.sv-completion--full { color:#059669; }
.dark .sv-completion--full { color:#34d399; }
.sv-completion--partial { color:#d97706; }
.dark .sv-completion--partial { color:#fbbf24; }

.sv-progress { height:.375rem; border-radius:9999px; background:#f3f4f6; overflow:hidden; }
.dark .sv-progress { background:#1f2937; }
.sv-progress-fill { height:100%; border-radius:9999px; transition:width .3s; }
.sv-progress-fill--full    { background:#10b981; }
.sv-progress-fill--partial { background:#f59e0b; }

/* ── question cards ──────────────────────── */
.sv-q { border-radius:.75rem; overflow:hidden; display:flex; box-shadow:0 0 0 1px rgba(9,9,11,.05); }
.dark .sv-q { box-shadow:0 0 0 1px rgba(255,255,255,.08); }
.sv-q--answered { background:#fff; }
.dark .sv-q--answered { background:#111827; }
.sv-q--empty { background:#fafafa; }
.dark .sv-q--empty { background:rgba(17,24,39,.5); }

.sv-stripe { width:.25rem; flex-shrink:0; }
.sv-stripe--answered { background:#818cf8; }
.sv-stripe--empty    { background:#e5e7eb; }
.dark .sv-stripe--empty { background:#374151; }

.sv-q-body { flex:1; padding:1rem 1.25rem; display:flex; align-items:flex-start; gap:.75rem; min-width:0; }

.sv-num { width:1.75rem; height:1.75rem; border-radius:9999px; display:flex; align-items:center; justify-content:center; font-size:.7rem; font-weight:700; flex-shrink:0; margin-top:.1rem; }
.sv-num--answered { background:rgba(99,102,241,.1); color:#4338ca; box-shadow:0 0 0 1px rgba(99,102,241,.2); }
.dark .sv-num--answered { background:rgba(99,102,241,.2); color:#a5b4fc; box-shadow:0 0 0 1px rgba(99,102,241,.3); }
.sv-num--empty { background:#f3f4f6; color:#9ca3af; box-shadow:0 0 0 1px #e5e7eb; }
.dark .sv-num--empty { background:#1f2937; color:#4b5563; box-shadow:0 0 0 1px #374151; }

.sv-q-content { flex:1; min-width:0; display:flex; flex-direction:column; gap:.5rem; }

.sv-q-header { display:flex; flex-wrap:wrap; align-items:center; gap:.375rem; }
.sv-q-label { font-size:.875rem; font-weight:600; color:#111827; line-height:1.4; }
.dark .sv-q-label { color:#f3f4f6; }
.sv-q-label--empty { color:#9ca3af; }
.dark .sv-q-label--empty { color:#4b5563; }

.sv-type-pill { display:inline-flex; align-items:center; gap:.25rem; background:#f3f4f6; border-radius:.375rem; padding:.125rem .5rem; font-size:.65rem; font-weight:500; color:#6b7280; }
.dark .sv-type-pill { background:#1f2937; color:#6b7280; }
.sv-type-pill svg { width:.625rem; height:.625rem; flex-shrink:0; }
.sv-required { font-size:.65rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#f87171; }

.sv-helptext { font-size:.75rem; color:#9ca3af; line-height:1.5; }

/* ── answer values ───────────────────────── */
.sv-no-answer { display:inline-flex; align-items:center; gap:.375rem; font-size:.8125rem; color:#9ca3af; font-style:italic; }
.sv-no-answer svg { width:.875rem; height:.875rem; flex-shrink:0; }

.sv-pill { display:inline-flex; align-items:center; gap:.375rem; border-radius:9999px; padding:.25rem .75rem; font-size:.8125rem; font-weight:500; }
.sv-pill svg { width:.75rem; height:.75rem; flex-shrink:0; }
.sv-pill--indigo { background:rgba(99,102,241,.08); border:1px solid rgba(99,102,241,.25); color:#4338ca; }
.dark .sv-pill--indigo { background:rgba(99,102,241,.15); border-color:rgba(99,102,241,.3); color:#a5b4fc; }
.sv-pill--emerald { background:rgba(16,185,129,.08); border:1px solid rgba(16,185,129,.2); color:#047857; }
.dark .sv-pill--emerald { background:rgba(16,185,129,.15); border-color:rgba(16,185,129,.3); color:#6ee7b7; }

.sv-pills-wrap { display:flex; flex-wrap:wrap; gap:.375rem; }

.sv-textarea-block { background:#f9fafb; border:1px solid #e5e7eb; border-radius:.5rem; padding:.75rem 1rem; }
.dark .sv-textarea-block { background:#1f2937; border-color:#374151; }
.sv-textarea-block p { font-size:.875rem; color:#374151; white-space:pre-wrap; line-height:1.625; margin:0; }
.dark .sv-textarea-block p { color:#d1d5db; }

.sv-file { display:inline-flex; align-items:center; gap:.5rem; background:rgba(99,102,241,.06); border:1px solid rgba(99,102,241,.2); border-radius:.5rem; padding:.5rem .875rem; font-size:.875rem; color:#4338ca; max-width:100%; }
.dark .sv-file { background:rgba(99,102,241,.12); border-color:rgba(99,102,241,.25); color:#a5b4fc; }
.sv-file svg { width:1rem; height:1rem; flex-shrink:0; }
.sv-file span { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

.sv-link { display:inline-flex; align-items:center; gap:.375rem; font-size:.875rem; font-weight:500; color:#6366f1; text-decoration:none; }
.dark .sv-link { color:#a5b4fc; }
.sv-link:hover { text-decoration:underline; }
.sv-link svg { width:.875rem; height:.875rem; flex-shrink:0; }

.sv-plain { font-size:.875rem; font-weight:500; color:#111827; }
.dark .sv-plain { color:#f3f4f6; }

/* ── empty state ─────────────────────────── */
.sv-empty-state { padding:3rem; text-align:center; }
.sv-empty-state svg { width:2.5rem; height:2.5rem; color:#d1d5db; margin:0 auto .75rem; display:block; }
.dark .sv-empty-state svg { color:#374151; }
.sv-empty-state p { font-size:.875rem; font-weight:500; color:#9ca3af; margin:0; }
</style>

<div class="sv sv-stack">

    {{-- ── Hero ────────────────────────────────────────────── --}}
    <div class="sv-card">
        <div class="sv-band {{ $isSubmitted ? 'sv-band--submitted' : 'sv-band--draft' }}"></div>

        <div class="sv-hero-body">
            {{-- Avatar --}}
            <div class="sv-avatar {{ $isSubmitted ? 'sv-avatar--submitted' : 'sv-avatar--draft' }}">
                {{ strtoupper(mb_substr($record->participant->name, 0, 1)) }}
            </div>

            {{-- Name / meta --}}
            <div class="sv-hero-main">
                <div class="sv-name-row">
                    <p class="sv-name">{{ $record->participant->name }}</p>
                    <span class="sv-badge {{ $isSubmitted ? 'sv-badge--submitted' : 'sv-badge--draft' }}">
                        @if ($isSubmitted)
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        @else
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                        @endif
                        {{ ucfirst($record->status) }}
                    </span>
                </div>

                <div class="sv-meta">
                    @if ($record->participant->division)
                        <span class="sv-meta-item">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                            {{ $record->participant->division->name }}
                        </span>
                    @endif
                    @if ($record->participant->phone)
                        <span class="sv-meta-item">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                            <a href="tel:{{ $record->participant->phone }}">{{ $record->participant->phone }}</a>
                        </span>
                    @endif
                    @if ($record->participant->email)
                        <span class="sv-meta-item">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                            <a href="mailto:{{ $record->participant->email }}">{{ $record->participant->email }}</a>
                        </span>
                    @endif
                </div>
            </div>

            {{-- Timestamps --}}
            <div class="sv-timestamps">
                @if ($record->submitted_at)
                    <div>
                        <span class="sv-ts-label">Submitted</span>
                        {{ $record->submitted_at->format('d M Y, H:i') }}
                    </div>
                @endif
                @if ($record->last_edited_at)
                    <div>
                        <span class="sv-ts-label">Last edited</span>
                        {{ $record->last_edited_at->format('d M Y, H:i') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Release info + progress ─────────────────────────── --}}
    <div class="sv-card">
        <div class="sv-release">
            <div class="sv-release-row">
                <div class="sv-release-item">
                    <span class="sv-rlabel">Form</span>
                    <span class="sv-rvalue">{{ $record->formRelease->form->title }}</span>
                </div>
                <div class="sv-release-item">
                    <span class="sv-rlabel">Release</span>
                    <span class="sv-rvalue sv-rvalue--muted">{{ $record->formRelease->name }}</span>
                </div>
                @if ($record->formRelease->start_at || $record->formRelease->end_at)
                    <div class="sv-release-item">
                        <span class="sv-rlabel">Period</span>
                        <span class="sv-rvalue sv-rvalue--muted">
                            {{ $record->formRelease->start_at?->format('d M Y') ?? '?' }}
                            –
                            {{ $record->formRelease->end_at?->format('d M Y') ?? '?' }}
                        </span>
                    </div>
                @endif
                <div class="sv-release-item">
                    <span class="sv-rlabel">Completion</span>
                    <span class="sv-completion {{ $completePct === 100 ? 'sv-completion--full' : 'sv-completion--partial' }}">
                        {{ $answeredCount }} / {{ $totalCount }} answered
                    </span>
                </div>
            </div>
            <div class="sv-progress">
                <div
                    class="sv-progress-fill {{ $completePct === 100 ? 'sv-progress-fill--full' : 'sv-progress-fill--partial' }}"
                    style="width:{{ $completePct }}%"
                ></div>
            </div>
        </div>
    </div>

    {{-- ── Answers ─────────────────────────────────────────── --}}
    <div class="sv-stack">
        @forelse ($questions as $i => $question)
            @php
                $answer     = $answerMap->get($question->id);
                $hasAnswer  = $answer && (
                    ($answer->value !== null && $answer->value !== '')
                    || $answer->value_json !== null
                    || $answer->file_path !== null
                );
            @endphp

            <div class="sv-q {{ $hasAnswer ? 'sv-q--answered' : 'sv-q--empty' }}">
                <div class="sv-stripe {{ $hasAnswer ? 'sv-stripe--answered' : 'sv-stripe--empty' }}"></div>

                <div class="sv-q-body">
                    <div class="sv-num {{ $hasAnswer ? 'sv-num--answered' : 'sv-num--empty' }}">
                        {{ $i + 1 }}
                    </div>

                    <div class="sv-q-content">
                        {{-- Header --}}
                        <div class="sv-q-header">
                            <span class="sv-q-label {{ $hasAnswer ? '' : 'sv-q-label--empty' }}">{{ $question->label }}</span>
                            <span class="sv-type-pill">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                    @switch($question->type)
                                        @case('email') <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/> @break
                                        @case('textarea') <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h7.5"/> @break
                                        @case('number') <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 8.25h15m-16.5 7.5h15m-1.8-13.5-3.9 19.5m-2.1-19.5-3.9 19.5"/> @break
                                        @case('radio') @case('checkbox') <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/> @break
                                        @case('select') <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"/> @break
                                        @case('date') <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/> @break
                                        @case('file') <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13"/> @break
                                        @default <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12"/>
                                    @endswitch
                                </svg>
                                {{ $question->type }}
                            </span>
                            @if ($question->is_required)
                                <span class="sv-required">required</span>
                            @endif
                        </div>

                        @if ($question->help_text)
                            <p class="sv-helptext">{{ $question->help_text }}</p>
                        @endif

                        {{-- Answer --}}
                        @if (!$hasAnswer)
                            <span class="sv-no-answer">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                No answer provided
                            </span>

                        @elseif ($question->type === 'file')
                            @if ($answer->file_path)
                                <div class="sv-file">
                                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13"/></svg>
                                    <span>{{ $answer->file_original_name ?? basename($answer->file_path) }}</span>
                                </div>
                            @else
                                <span class="sv-no-answer">No file uploaded</span>
                            @endif

                        @elseif ($question->type === 'checkbox')
                            @php $vals = $answer->value_json ?? []; @endphp
                            @if (empty($vals))
                                <span class="sv-no-answer">No options selected</span>
                            @else
                                <div class="sv-pills-wrap">
                                    @foreach ($vals as $v)
                                        <span class="sv-pill sv-pill--indigo">
                                            <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                            {{ $v }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                        @elseif ($question->type === 'radio' || $question->type === 'select')
                            <span class="sv-pill sv-pill--emerald">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                {{ $answer->value }}
                            </span>

                        @elseif ($question->type === 'textarea')
                            <div class="sv-textarea-block">
                                <p>{{ $answer->value }}</p>
                            </div>

                        @elseif ($question->type === 'email')
                            <a href="mailto:{{ $answer->value }}" class="sv-link">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                                {{ $answer->value }}
                            </a>

                        @elseif ($question->type === 'phone')
                            <a href="tel:{{ $answer->value }}" class="sv-link">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                                {{ $answer->value }}
                            </a>

                        @else
                            <p class="sv-plain">{{ $answer->value }}</p>
                        @endif

                    </div>
                </div>
            </div>

        @empty
            <div class="sv-card">
                <div class="sv-empty-state">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/></svg>
                    <p>No questions found for this release.</p>
                </div>
            </div>
        @endforelse
    </div>

</div>
</x-filament-panels::page>
