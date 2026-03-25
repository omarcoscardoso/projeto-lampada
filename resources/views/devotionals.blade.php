<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Vanilla Calendar CSS -->
        <link href="https://cdn.jsdelivr.net/npm/vanilla-calendar-pro/build/vanilla-calendar.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/vanilla-calendar-pro/build/themes/light.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/vanilla-calendar-pro/build/themes/dark.min.css" rel="stylesheet">

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-100 dark:bg-gray-900">
        <div class="container mx-auto mt-8 p-4">
            <div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-md p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Selecione uma data</h2>
                    <div id="calendar" class="vanilla-calendar light dark:dark"></div>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Devocional do Dia</h2>
                    <div id="devotional-display" class="relative bg-gray-50 dark:bg-gray-700 p-6 rounded-md min-h-[200px]">
                        <button id="whatsapp-share-btn" class="hidden absolute top-4 right-4 bg-green-500 hover:bg-green-600 text-white rounded-full p-2 focus:outline-none focus:ring-2 focus:ring-green-400 z-10">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12s-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.368a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path></svg>
                        </button>
                        <div id="devotional-content" class="prose dark:prose-invert max-w-none">
                            <p class="text-gray-600 dark:text-gray-400">Clique em um dia no calendário para ver o devocional.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vanilla Calendar JS -->
        <script src="https://cdn.jsdelivr.net/npm/vanilla-calendar-pro/build/vanilla-calendar.min.js"></script>
    </body>
</html>
