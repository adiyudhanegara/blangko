<div class="space-y-5">

    {{-- Form header card with colour band --}}
    <div class="rounded-2xl bg-white shadow-sm overflow-hidden border border-slate-200/60">
        <div class="h-2.5 bg-linear-to-r from-indigo-500 via-violet-500 to-purple-600"></div>
        <div class="p-6 sm:p-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 leading-tight tracking-tight">
                {{ $release->form->title ?? $release->name }}
            </h1>

            @if ($release->form->description ?? null)
                <p class="mt-3 text-slate-500 text-sm leading-relaxed">
                    {{ $release->form->description }}
                </p>
            @endif

            @if ($release->start_at || $release->end_at)
                <div class="mt-4 flex flex-wrap gap-2">
                    @if ($release->start_at)
                        <span class="inline-flex items-center gap-1.5 text-xs text-slate-500 bg-slate-50 rounded-lg px-3 py-1.5 border border-slate-200">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5" />
                            </svg>
                            Opens {{ $release->start_at->format('d M Y, H:i') }}
                        </span>
                    @endif
                    @if ($release->end_at)
                        <span class="inline-flex items-center gap-1.5 text-xs text-slate-500 bg-slate-50 rounded-lg px-3 py-1.5 border border-slate-200">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Closes {{ $release->end_at->format('d M Y, H:i') }}
                        </span>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Step indicator --}}
    <div class="flex items-center gap-2 px-1">
        {{-- Step 1 --}}
        <div class="flex items-center gap-2 shrink-0">
            <span class="flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold
                {{ !$showRegistration ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-200 text-slate-500' }}">
                1
            </span>
            <span class="text-xs font-medium hidden sm:inline
                {{ !$showRegistration ? 'text-slate-700' : 'text-slate-400' }}">
                Identify
            </span>
        </div>
        <div class="flex-1 h-px bg-slate-200"></div>
        {{-- Step 2 --}}
        <div class="flex items-center gap-2 shrink-0">
            <span class="flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold
                {{ $showRegistration ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-400 ring-1 ring-slate-200' }}">
                2
            </span>
            <span class="text-xs font-medium hidden sm:inline
                {{ $showRegistration ? 'text-slate-700' : 'text-slate-400' }}">
                Register
            </span>
        </div>
        <div class="flex-1 h-px bg-slate-200"></div>
        {{-- Step 3 --}}
        <div class="flex items-center gap-2 shrink-0">
            <span class="flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold bg-slate-100 text-slate-400 ring-1 ring-slate-200">
                3
            </span>
            <span class="text-xs font-medium text-slate-400 hidden sm:inline">Fill Form</span>
        </div>
    </div>

    {{-- Main card --}}
    <div class="rounded-2xl bg-white shadow-sm border border-slate-200/60">
        <div class="p-6 sm:p-8 space-y-5">

            {{-- Card heading --}}
            @if (!$showRegistration)
                <div class="flex items-start gap-3">
                    <div class="shrink-0 w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center">
                        <svg class="w-4.5 h-4.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1.125rem;height:1.125rem">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-slate-800">Who are you?</h2>
                        <p class="mt-0.5 text-sm text-slate-500">Enter your contact info to get started.</p>
                    </div>
                </div>
            @else
                <div class="flex items-start gap-3">
                    <div class="shrink-0 w-9 h-9 rounded-xl bg-violet-50 flex items-center justify-center">
                        <svg style="width:1.125rem;height:1.125rem" class="text-violet-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0112 21.75c-2.331 0-4.512-.645-6.374-1.766z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-slate-800">Create your account</h2>
                        <p class="mt-0.5 text-sm text-slate-500">Just a few details and you're good to go.</p>
                    </div>
                </div>
            @endif

            {{-- Error message --}}
            @if ($errorMessage)
                <div class="flex items-start gap-3 rounded-xl bg-red-50 border border-red-200 px-4 py-3.5">
                    <svg class="w-4 h-4 mt-0.5 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                    <p class="text-sm text-red-700">{{ $errorMessage }}</p>
                </div>
            @endif

            {{-- ---- Identification step ---- --}}
            @if (!$showRegistration)

                {{-- Tab switcher --}}
                <div class="flex rounded-xl border border-slate-200 bg-slate-50 p-1 gap-1">
                    <button
                        wire:click="$set('identifierType', 'phone')"
                        type="button"
                        class="flex-1 flex items-center justify-center gap-2 rounded-lg py-2.5 text-sm font-medium transition-all duration-150
                            {{ $identifierType === 'phone'
                                ? 'bg-white text-indigo-700 shadow-sm border border-slate-200/80'
                                : 'text-slate-500 hover:text-slate-700' }}"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                        </svg>
                        Phone
                    </button>
                    <button
                        wire:click="$set('identifierType', 'email')"
                        type="button"
                        class="flex-1 flex items-center justify-center gap-2 rounded-lg py-2.5 text-sm font-medium transition-all duration-150
                            {{ $identifierType === 'email'
                                ? 'bg-white text-indigo-700 shadow-sm border border-slate-200/80'
                                : 'text-slate-500 hover:text-slate-700' }}"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                        Email
                    </button>
                    <button
                        wire:click="$set('identifierType', 'nip')"
                        type="button"
                        class="flex-1 flex items-center justify-center gap-2 rounded-lg py-2.5 text-sm font-medium transition-all duration-150
                            {{ $identifierType === 'nip'
                                ? 'bg-white text-indigo-700 shadow-sm border border-slate-200/80'
                                : 'text-slate-500 hover:text-slate-700' }}"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z" />
                        </svg>
                        NIP
                    </button>
                </div>

                {{-- Input field --}}
                <div class="space-y-1.5">
                    @if ($identifierType === 'phone')
                        <label for="identifier" class="block text-sm font-medium text-slate-700">Phone Number</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                                </svg>
                            </div>
                            <input
                                type="tel"
                                id="identifier"
                                wire:model="identifier"
                                placeholder="e.g. 08123456789"
                                autocomplete="tel"
                                class="block w-full rounded-xl border border-slate-300 bg-white pl-10 pr-4 py-3 text-base text-slate-900 placeholder-slate-400
                                       shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none
                                       @error('identifier') border-red-400 focus:border-red-500 focus:ring-red-500/20 @enderror"
                            >
                        </div>
                    @elseif ($identifierType === 'email')
                        <label for="identifier" class="block text-sm font-medium text-slate-700">Email Address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                </svg>
                            </div>
                            <input
                                type="email"
                                id="identifier"
                                wire:model="identifier"
                                placeholder="you@example.com"
                                autocomplete="email"
                                class="block w-full rounded-xl border border-slate-300 bg-white pl-10 pr-4 py-3 text-base text-slate-900 placeholder-slate-400
                                       shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none
                                       @error('identifier') border-red-400 focus:border-red-500 focus:ring-red-500/20 @enderror"
                            >
                        </div>
                    @else
                        <label for="identifier" class="block text-sm font-medium text-slate-700">NIP</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z" />
                                </svg>
                            </div>
                            <input
                                type="text"
                                id="identifier"
                                wire:model="identifier"
                                placeholder="e.g. 199001012020011001"
                                autocomplete="off"
                                class="block w-full rounded-xl border border-slate-300 bg-white pl-10 pr-4 py-3 text-base text-slate-900 placeholder-slate-400
                                       shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none
                                       @error('identifier') border-red-400 focus:border-red-500 focus:ring-red-500/20 @enderror"
                            >
                        </div>
                    @endif

                    @error('identifier')
                        <p class="text-xs text-red-600 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <button
                    wire:click="identify"
                    wire:loading.attr="disabled"
                    type="button"
                    class="w-full rounded-xl bg-indigo-600 px-4 py-3.5 text-sm font-semibold text-white shadow-sm
                           hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                           transition-colors duration-150 disabled:opacity-60 disabled:cursor-not-allowed"
                >
                    <span wire:loading.remove wire:target="identify" class="flex items-center justify-center gap-2">
                        Continue
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </span>
                    <span wire:loading wire:target="identify" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Checking...
                    </span>
                </button>

            @endif

            {{-- ---- Registration step ---- --}}
            @if ($showRegistration)

                <div class="flex items-start gap-3 rounded-xl bg-violet-50 border border-violet-200 px-4 py-3.5">
                    <svg class="w-4 h-4 shrink-0 mt-0.5 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
                    <p class="text-sm text-violet-800">
                        No account found for <strong class="font-semibold">{{ $identifier }}</strong>.
                        Fill in your details below to register.
                    </p>
                </div>

                {{-- Name field --}}
                <div class="space-y-1.5">
                    <label for="name" class="block text-sm font-medium text-slate-700">
                        Full Name <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                        </div>
                        <input
                            type="text"
                            id="name"
                            wire:model="name"
                            placeholder="Your full name"
                            autocomplete="name"
                            class="block w-full rounded-xl border border-slate-300 bg-white pl-10 pr-4 py-3 text-base text-slate-900 placeholder-slate-400
                                   shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none
                                   @error('name') border-red-400 focus:border-red-500 focus:ring-red-500/20 @enderror"
                        >
                    </div>
                    @error('name')
                        <p class="text-xs text-red-600 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Division field --}}
                @if ($divisions->isNotEmpty())
                    <div class="space-y-1.5">
                        <label for="divisionId" class="block text-sm font-medium text-slate-700">
                            Division
                            <span class="text-slate-400 font-normal text-xs ml-1">optional</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                                </svg>
                            </div>
                            <select
                                id="divisionId"
                                wire:model="divisionId"
                                class="block w-full rounded-xl border border-slate-300 bg-white pl-10 pr-10 py-3 text-base text-slate-900
                                       shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none appearance-none
                                       @error('divisionId') border-red-400 @enderror"
                            >
                                <option value="">-- Select a division --</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}">{{ $division->name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </div>
                        </div>
                        @error('divisionId')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                {{-- Actions --}}
                <div class="flex gap-3 pt-1">
                    <button
                        wire:click="$set('showRegistration', false)"
                        type="button"
                        class="flex items-center justify-center gap-2 shrink-0 rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-medium text-slate-700
                               hover:bg-slate-50 active:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-2
                               transition-colors duration-150"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        Back
                    </button>
                    <button
                        wire:click="register"
                        wire:loading.attr="disabled"
                        type="button"
                        class="flex-1 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm
                               hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                               transition-colors duration-150 disabled:opacity-60 disabled:cursor-not-allowed"
                    >
                        <span wire:loading.remove wire:target="register" class="flex items-center justify-center gap-2">
                            Register &amp; Continue
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </span>
                        <span wire:loading wire:target="register" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Registering...
                        </span>
                    </button>
                </div>

            @endif

        </div>
    </div>

</div>
