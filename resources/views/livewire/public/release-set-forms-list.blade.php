<div class="space-y-5">

    {{-- Release Set Header --}}
    <div class="rounded-2xl bg-white shadow-sm overflow-hidden border border-slate-200/60">
        <div class="h-2.5 bg-gradient-to-r from-indigo-500 via-violet-500 to-purple-600"></div>
        <div class="p-6 sm:p-8">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 leading-tight tracking-tight">
                        {{ $releaseSet->name }}
                    </h1>
                    @if ($releaseSet->description)
                        <p class="mt-2 text-slate-500 text-sm leading-relaxed">{{ $releaseSet->description }}</p>
                    @endif
                    <div class="mt-4 flex flex-wrap gap-2">
                        <span class="inline-flex items-center gap-1.5 text-xs text-slate-500 bg-slate-50 rounded-lg px-3 py-1.5 border border-slate-200">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ __('public.deadline') }}: {{ $releaseSet->end_at->format('d M Y, H:i') }}
                        </span>
                        @if ($releaseSet->days_remaining <= 3)
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-amber-700 bg-amber-50 rounded-lg px-3 py-1.5 border border-amber-200">
                                {{ trans_choice('public.days_remaining', $releaseSet->days_remaining, ['count' => $releaseSet->days_remaining]) }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Overall completion --}}
            <div class="mt-5 rounded-xl bg-slate-50 border border-slate-200 px-4 py-3">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-slate-500">{{ __('public.your_progress') }}</span>
                    <span class="text-xs font-bold text-indigo-600">
                        {{ $completionStats['complete'] }} / {{ $completionStats['total'] }} {{ __('public.complete') }}
                    </span>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                    <div
                        class="h-1.5 rounded-full bg-gradient-to-r from-indigo-500 to-violet-500 transition-all duration-500"
                        style="width: {{ $completionStats['percentage'] }}%"
                    ></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Participant greeting --}}
    <div class="flex items-center gap-3 px-1">
        <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center shrink-0">
            <span class="text-xs font-bold text-indigo-600">{{ strtoupper(substr($participant->name ?? 'U', 0, 1)) }}</span>
        </div>
        <div class="min-w-0">
            <p class="text-sm font-medium text-slate-700 truncate">{{ $participant->name }}</p>
            @if ($participant->division)
                <p class="text-xs text-slate-400 truncate">{{ $participant->division->name }}</p>
            @endif
        </div>
    </div>

    {{-- Forms list --}}
    @forelse ($releases->values() as $index => $release)
        @php
            $status = $releaseStatuses[$release->id] ?? ['type' => 'not_started'];
            $token  = $releaseSet->public_token;
        @endphp

        <div class="rounded-2xl bg-white shadow-sm border border-slate-200/60 overflow-hidden">
            {{-- left accent by status --}}
            <div class="flex">
                <div @class([
                    'w-1 shrink-0 rounded-l-2xl',
                    'bg-gradient-to-b from-indigo-400 to-violet-400' => $status['type'] === 'not_started',
                    'bg-gradient-to-b from-amber-400 to-orange-400'  => $status['type'] === 'draft',
                    'bg-gradient-to-b from-emerald-400 to-teal-500'  => $status['type'] === 'submitted',
                    'bg-gradient-to-b from-violet-400 to-purple-500' => $status['type'] === 'multi',
                ])></div>

                <div class="flex-1 p-5 sm:p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            {{-- Title row --}}
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="shrink-0 w-6 h-6 rounded-full bg-slate-100 text-slate-500 text-xs font-bold flex items-center justify-center">
                                    {{ $index + 1 }}
                                </span>
                                <h2 class="text-base font-semibold text-slate-800 leading-snug">
                                    {{ $release->form->title ?? ('Form ' . ($index + 1)) }}
                                </h2>
                                @if ($release->is_required ?? true)
                                    <span class="text-xs font-medium text-red-500 bg-red-50 border border-red-100 rounded-md px-2 py-0.5">{{ __('public.required') }}</span>
                                @else
                                    <span class="text-xs font-medium text-slate-400 bg-slate-50 border border-slate-100 rounded-md px-2 py-0.5">{{ __('public.optional') }}</span>
                                @endif
                            </div>

                            @if ($release->form->description ?? null)
                                <p class="mt-1.5 text-xs text-slate-500 leading-relaxed ml-8">{{ $release->form->description }}</p>
                            @endif

                            {{-- Status badge --}}
                            <div class="mt-3 ml-8">
                                @if ($status['type'] === 'not_started')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 bg-slate-100 rounded-lg px-2.5 py-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        {{ __('public.not_started') }}
                                    </span>
                                @elseif ($status['type'] === 'draft')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-700 bg-amber-50 rounded-lg px-2.5 py-1 border border-amber-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                        {{ __('public.draft_in_progress') }}
                                    </span>
                                @elseif ($status['type'] === 'submitted')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 rounded-lg px-2.5 py-1 border border-emerald-100">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                        </svg>
                                        {{ __('public.submitted') }}
                                    </span>
                                @elseif ($status['type'] === 'multi')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-violet-700 bg-violet-50 rounded-lg px-2.5 py-1 border border-violet-100">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                                        </svg>
                                        {{ __('public.submitted_count', ['count' => $status['submitted']]) }}
                                        @if ($status['draft'] > 0)
                                            &bull; {{ __('public.draft_count', ['count' => $status['draft']]) }}
                                        @endif
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Action buttons --}}
                        <div class="shrink-0 flex flex-col gap-2 items-end">
                            @if ($status['type'] === 'multi')
                                <a href="{{ route('release.history', [$token, $release->id]) }}"
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-semibold px-3.5 py-2 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
                                    </svg>
                                    {{ __('public.view_all') }}
                                </a>
                                <a href="{{ route('release.form', [$token, $release->id]) }}"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-violet-200 bg-violet-50 text-violet-700 text-xs font-medium px-3.5 py-2 hover:bg-violet-100 transition-colors">
                                    {{ __('public.add_new') }}
                                </a>
                            @elseif ($status['type'] === 'submitted')
                                <a href="{{ route('release.form', [$token, $release->id]) }}"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 text-xs font-medium px-3.5 py-2 hover:bg-emerald-100 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ __('public.view_edit') }}
                                </a>
                            @elseif ($status['type'] === 'draft')
                                <a href="{{ route('release.form', [$token, $release->id]) }}"
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold px-3.5 py-2 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                    </svg>
                                    {{ __('public.continue_draft') }}
                                </a>
                            @else
                                <a href="{{ route('release.form', [$token, $release->id]) }}"
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold px-3.5 py-2 transition-colors">
                                    {{ __('public.open_form') }}
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="rounded-2xl bg-white border border-slate-200/60 shadow-sm p-8 text-center">
            <p class="text-sm text-slate-500">{{ __('public.no_forms') }}</p>
        </div>
    @endforelse

</div>
