<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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
    <body class="bg-white dark:bg-black font-sans antialiased text-slate-900 dark:text-slate-100 h-full overflow-hidden"
        x-data="devotionalApp"
        @close-calendar.window="showCalendar = false">
        
        {{ $slot }}
        
        @livewireScriptConfig
    </body>
</html>
