<div class="space-y-5">

    {{-- Header card --}}
    <div class="rounded-2xl bg-white shadow-sm overflow-hidden border border-slate-200/60">
        <div class="h-2.5 bg-gradient-to-r from-indigo-500 via-violet-500 to-purple-600"></div>
        <div class="p-6 sm:p-8">
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
                    Deadline: {{ $releaseSet->end_at->format('d M Y, H:i') }}
                </span>
                @if ($releaseSet->days_remaining <= 3)
                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-amber-700 bg-amber-50 rounded-lg px-3 py-1.5 border border-amber-200">
                        {{ $releaseSet->days_remaining }} day{{ $releaseSet->days_remaining != 1 ? 's' : '' }} remaining
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Step indicator --}}
    <div class="flex items-center gap-2 px-1">
        @foreach (['Identify', 'Register', 'Fill Forms'] as $step => $label)
            @php
                $active = ($step === 0 && !$showRegistration) || ($step === 1 && $showRegistration);
                $done   = ($step === 0 && $showRegistration);
            @endphp
            <div class="flex items-center gap-1.5 {{ $loop->first ? '' : 'flex-1' }}">
                @if (!$loop->first)
                    <div class="h-px flex-1 {{ $done ? 'bg-indigo-300' : 'bg-slate-200' }}"></div>
                @endif
                <div @class([
                    'w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold shrink-0',
                    'bg-indigo-600 text-white' => $active,
                    'bg-indigo-100 text-indigo-600' => $done,
                    'bg-slate-100 text-slate-400' => !$active && !$done,
                ])>
                    @if ($done)
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    @else
                        {{ $step + 1 }}
                    @endif
                </div>
                <span @class(['text-xs font-medium', 'text-indigo-700' => $active, 'text-slate-400' => !$active])>{{ $label }}</span>
            </div>
        @endforeach
    </div>

    @if ($errorMessage)
        <div class="rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700 flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/></svg>
            {{ $errorMessage }}
        </div>
    @endif

    {{-- Identify step --}}
    @if (!$showRegistration)
        <div class="rounded-2xl bg-white shadow-sm border border-slate-200/60 p-6 sm:p-8 space-y-5">
            <div>
                <h2 class="text-base font-semibold text-slate-800">Identify yourself</h2>
                <p class="text-sm text-slate-500 mt-0.5">Enter your phone number or email to continue.</p>
            </div>

            {{-- Tab switcher --}}
            <div class="flex rounded-xl border border-slate-200 bg-slate-50 p-1 gap-1">
                <button
                    wire:click="$set('identifierType','phone')"
                    @class(['flex-1 flex items-center justify-center gap-1.5 rounded-lg py-2 text-sm font-medium transition-all',
                        'bg-white shadow-sm text-indigo-700 border border-slate-200/70' => $identifierType === 'phone',
                        'text-slate-500 hover:text-slate-700' => $identifierType !== 'phone'])
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                    Phone
                </button>
                <button
                    wire:click="$set('identifierType','email')"
                    @class(['flex-1 flex items-center justify-center gap-1.5 rounded-lg py-2 text-sm font-medium transition-all',
                        'bg-white shadow-sm text-indigo-700 border border-slate-200/70' => $identifierType === 'email',
                        'text-slate-500 hover:text-slate-700' => $identifierType !== 'email'])
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                    Email
                </button>
            </div>

            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                    @if ($identifierType === 'phone')
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                    @else
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                    @endif
                </div>
                <input
                    wire:model="identifier"
                    wire:keydown.enter="identify"
                    type="{{ $identifierType === 'email' ? 'email' : 'tel' }}"
                    placeholder="{{ $identifierType === 'phone' ? 'e.g. 08123456789' : 'e.g. user@example.com' }}"
                    class="w-full rounded-xl border border-slate-200 bg-white pl-10 pr-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition"
                />
            </div>
            @error('identifier') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror

            <button
                wire:click="identify"
                class="w-full flex items-center justify-center gap-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 text-sm transition-colors"
            >
                Continue
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
            </button>
        </div>

    {{-- Registration step --}}
    @else
        <div class="rounded-2xl bg-white shadow-sm border border-violet-200/60 overflow-hidden">
            <div class="h-1 bg-gradient-to-r from-violet-400 to-purple-500"></div>
            <div class="p-6 sm:p-8 space-y-5">
                <div>
                    <h2 class="text-base font-semibold text-slate-800">Create your profile</h2>
                    <p class="text-sm text-slate-500 mt-0.5">You're new here — fill in a few details to get started.</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wide">Full Name <span class="text-red-400">*</span></label>
                        <input wire:model="name" type="text" placeholder="Your full name"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-400 focus:border-violet-400 transition" />
                        @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wide">Division</label>
                        <select wire:model="divisionId"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-violet-400 focus:border-violet-400 transition appearance-none">
                            <option value="">— Select division (optional) —</option>
                            @foreach ($divisions as $division)
                                <option value="{{ $division->id }}">{{ $division->name }}</option>
                            @endforeach
                        </select>
                        @error('divisionId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex gap-3">
                    <button wire:click="$set('showRegistration', false)"
                        class="flex-1 rounded-xl border border-slate-200 bg-white text-slate-600 font-medium py-3 text-sm hover:bg-slate-50 transition">
                        Back
                    </button>
                    <button wire:click="register"
                        class="flex-1 flex items-center justify-center gap-2 rounded-xl bg-violet-600 hover:bg-violet-700 text-white font-semibold py-3 text-sm transition-colors">
                        Register &amp; Continue
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
