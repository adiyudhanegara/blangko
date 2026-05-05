<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Blangko') }}</title>

    <!-- Inter font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
    </style>

    @livewireStyles
</head>
<body class="min-h-screen bg-linear-to-br from-slate-100 via-indigo-50/20 to-slate-100 antialiased">

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

    <!-- Flash message -->
    @if (session('message'))
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 4000)"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="fixed top-4 left-1/2 -translate-x-1/2 z-50 w-full max-w-sm px-4"
        >
            <div class="flex items-center gap-3 rounded-2xl bg-emerald-600 px-4 py-3 text-white shadow-xl ring-1 ring-emerald-500/30">
                <div class="shrink-0 w-6 h-6 rounded-full bg-white/20 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                </div>
                <span class="text-sm font-medium flex-1">{{ session('message') }}</span>
                <button @click="show = false" class="ml-auto shrink-0 text-white/70 hover:text-white transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <!-- Main content -->
    <main class="mx-auto max-w-3xl px-4 sm:px-6 py-8 pb-32">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="py-8 text-center">
        <p class="text-xs text-slate-400">
            Powered by <span class="font-medium text-slate-500">Blangko</span> &bull; &copy; {{ date('Y') }}
        </p>
    </footer>

    @livewireScripts
</body>
</html>
