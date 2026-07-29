<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'E-Archive STTNI') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-gray-900 bg-gray-50">
        <div class="flex min-h-screen">
            
            <!-- Left Sidebar Navigation (e-SPMI style) -->
            @include('layouts.navigation')

            <!-- Right Content Area -->
            <div class="flex-1 flex flex-col min-w-0">
                
                <!-- Header / Top Bar (e-SPMI style) -->
                @isset($header)
                    <header class="bg-white border-b border-gray-200 py-3.5 px-6 sm:px-8">
                        <div class="flex items-center justify-between">
                            <!-- Page Title (e.g. Dashboard) -->
                            <div class="text-lg font-bold text-gray-800">
                                {{ $header }}
                            </div>
                            
                            <!-- Date Badge with Calendar Icon -->
                            <div class="flex items-center gap-2 px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs font-semibold text-gray-600 shadow-sm">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>{{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
                            </div>
                        </div>
                    </header>
                @endisset

                <!-- Main Content Viewport -->
                <main class="flex-1 overflow-y-auto bg-gray-50 p-6 sm:p-8">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
