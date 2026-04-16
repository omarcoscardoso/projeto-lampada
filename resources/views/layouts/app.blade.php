<!DOCTYPE html>
<html class="scroll-smooth" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#1e3a5f">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Lâmpada">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">

    <!-- <title>Leitura Bíblica Diária - {{ config('app.name', 'Lâmpada') }}</title> -->
    <title>{{ $title ?? config('app.name', 'Lâmpada') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>

<body
    class="bg-white text-slate-800 font-sans selection:bg-primary/10 selection:text-primary overflow-x-hidden"

    x-data="lampadaApp"
    @close-calendar.window="calShowCalendar = false">

    {{ $slot }}

    @livewireScriptConfig
</body>

<!-- Footer -->
<footer class="bg-white py-16 border-t border-slate-100">
    <div class="mx-auto max-w-7xl px-6 lg:px-8 text-center">
        <img src="https://storage.googleapis.com/iprviamao-com-br/lampada/logo_lampada_app.webp" alt="Logo Lâmpada" class="h-10 w-auto mx-auto mb-8 opacity-20 grayscale" />
        <p class="text-sm font-bold text-slate-400 tracking-tight">&copy; 2026 Igreja Presbiteriana Renovada de Viamão. Todos os direitos reservados.</p>
    </div>
</footer>

</html>