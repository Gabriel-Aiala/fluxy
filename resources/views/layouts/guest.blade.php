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
    <div class="flex min-h-screen flex-col items-center bg-[var(--flux-bg)] pt-6 sm:justify-center sm:pt-0">
        <div class="flex items-center justify-center">
            <a href="{{ route('login') }}" class="inline-flex items-center gap-3 rounded-xl px-2 py-1 focus:outline-none focus:ring-2 focus:ring-[var(--flux-dark)] focus:ring-offset-2">
                <x-application-logo class="h-14 w-14 fill-current text-[var(--flux-dark)]" />
                <span class="text-3xl font-bold tracking-tight text-[var(--flux-dark)]">Lucre</span>
            </a>
        </div>

        <div class="mt-6 w-full overflow-hidden rounded-2xl border border-[var(--flux-border)] bg-white px-6 py-6 shadow-[0_12px_28px_rgba(6,59,69,0.08)] sm:max-w-md">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
