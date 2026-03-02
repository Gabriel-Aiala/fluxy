@props(['hideGlobalNavigation' => false])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=public-sans:400,500,600,700,800|space-grotesk:500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-[var(--flux-ink)]">
    <div class="relative min-h-screen bg-[var(--flux-bg)]">
        @if (! $hideGlobalNavigation)
            @include('layouts.navigation')
        @endif

        @isset($header)
            <header class="border-b border-[var(--flux-border)] bg-white/70 backdrop-blur">
                <div class="flux-shell py-5">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <main class="relative">
            {{ $slot }}
        </main>

        <footer class="mt-8 border-t border-[var(--flux-border)] bg-white/30 py-6">
            <div class="flux-shell text-center text-sm text-[var(--flux-muted)]">
                <span class="font-bold text-[var(--flux-dark)]">fluxy</span> &copy; {{ now()->year }} - Sua vida financeira em fluxo
            </div>
        </footer>
    </div>
</body>
</html>
