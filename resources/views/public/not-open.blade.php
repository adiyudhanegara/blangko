<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Blangko') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-linear-to-br from-slate-100 via-indigo-50/20 to-slate-100 antialiased flex flex-col">

    <!-- Header -->
    <header class="bg-white/80 backdrop-blur-sm border-b border-slate-200/70 sticky top-0 z-30">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 py-3.5 flex items-center gap-3">
            <div class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-linear-to-br from-indigo-500 to-violet-600 shadow-sm shrink-0">
                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                </svg>
            </div>
            <span class="text-base font-semibold text-slate-800 tracking-tight">Blangko</span>
        </div>
    </header>

    <!-- Content -->
    <main class="flex-1 flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-md space-y-5">

            {{-- Status card --}}
            <div class="rounded-2xl bg-white shadow-sm overflow-hidden border border-slate-200/60">

                @if ($reason === 'not_yet')
                    <div class="h-2.5 bg-linear-to-r from-amber-400 to-orange-400"></div>
                    <div class="p-8 text-center space-y-5">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-amber-100 mx-auto ring-8 ring-amber-50">
                            <svg class="w-8 h-8 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-slate-900">Not Open Yet</h1>
                            <p class="mt-2 text-sm text-slate-500 leading-relaxed">
                                This form is not accepting responses yet.<br>Please check back at the opening time.
                            </p>
                        </div>
                        @if ($releaseSet->start_at)
                            <div class="inline-flex items-center gap-2 rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-sm font-medium text-amber-800">
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5" />
                                </svg>
                                Opens {{ $releaseSet->start_at->format('d M Y \a\t H:i') }}
                            </div>
                        @endif
                    </div>

                @else
                    <div class="h-2.5 bg-linear-to-r from-red-400 to-rose-500"></div>
                    <div class="p-8 text-center space-y-5">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-100 mx-auto ring-8 ring-red-50">
                            <svg class="w-8 h-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                        </div>
                        <div>
                            @if ($releaseSet->status === 'cancelled')
                                <h1 class="text-xl font-bold text-slate-900">Closed</h1>
                                <p class="mt-2 text-sm text-slate-500 leading-relaxed">
                                    This submission period has been cancelled and is no longer accepting responses.
                                </p>
                            @else
                                <h1 class="text-xl font-bold text-slate-900">Submission Closed</h1>
                                <p class="mt-2 text-sm text-slate-500 leading-relaxed">
                                    This submission period has closed and is no longer accepting new responses.
                                </p>
                            @endif
                        </div>
                        @if ($releaseSet->end_at)
                            <div class="inline-flex items-center gap-2 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm font-medium text-red-800">
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5" />
                                </svg>
                                Closed {{ $releaseSet->end_at->format('d M Y \a\t H:i') }}
                            </div>
                        @endif
                    </div>
                @endif

            </div>

            {{-- Release Set context pill --}}
            <div class="rounded-xl bg-white border border-slate-200/60 shadow-sm px-5 py-4">
                <p class="text-xs font-medium text-slate-400 uppercase tracking-wide mb-1.5">Submission Period</p>
                <p class="text-sm font-semibold text-slate-800">{{ $releaseSet->name }}</p>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="py-8 text-center">
        <p class="text-xs text-slate-400">
            Powered by <span class="font-medium text-slate-500">Blangko</span> &bull; &copy; {{ date('Y') }}
        </p>
    </footer>

</body>
</html>
