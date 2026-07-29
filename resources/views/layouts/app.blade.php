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
    <body class="font-sans antialiased text-gray-900 bg-gray-50" x-data="{ mobileMenuOpen: false }">
        <div class="flex min-h-screen relative overflow-hidden">
            
            <!-- Desktop Sidebar Navigation (e-SPMI style) -->
            <aside class="hidden md:flex md:w-64 md:shrink-0 md:min-h-screen bg-[#181c32] border-r border-gray-200">
                @include('layouts.navigation')
            </aside>

            <!-- Mobile Sidebar Drawer Overlay (Hidden on Desktop) -->
            <div x-show="mobileMenuOpen" 
                 class="md:hidden fixed inset-0 z-50 flex" 
                 style="display: none;" 
                 role="dialog" 
                 aria-modal="true">
                
                <!-- Backdrop overlay click-to-close -->
                <div x-show="mobileMenuOpen"
                     x-transition:enter="transition-opacity ease-linear duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition-opacity ease-linear duration-300"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     @click="mobileMenuOpen = false"
                     class="fixed inset-0 bg-black/60 backdrop-blur-sm"></div>

                <!-- Slide-over Drawer Menu Panel -->
                <div x-show="mobileMenuOpen"
                     x-transition:enter="transition ease-in-out duration-300 transform"
                     x-transition:enter-start="-translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transition ease-in-out duration-300 transform"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="-translate-x-full"
                     class="relative flex w-full max-w-xs flex-1 flex-col bg-[#181c32]">
                    
                    <!-- Close button floating outside the drawer panel -->
                    <div class="absolute top-3 right-3">
                        <button type="button" 
                                @click="mobileMenuOpen = false" 
                                class="flex h-10 w-10 items-center justify-center rounded-full text-white bg-white/10 hover:bg-white/20 focus:outline-none">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Sidebar component content -->
                    @include('layouts.navigation')
                </div>
            </div>

            <!-- Right Content Area -->
            <div class="flex-1 flex flex-col min-w-0">
                
                <!-- Header / Top Bar (e-SPMI style) -->
                @isset($header)
                    <header class="bg-white border-b border-gray-200 py-3.5 px-4 sm:px-6 md:px-8 shrink-0">
                        <div class="flex items-center justify-between">
                            <!-- Left section: Hamburger (mobile) + Page Title -->
                            <div class="flex items-center gap-2.5">
                                <!-- Hamburger Button (Mobile only) -->
                                <button type="button" 
                                        @click="mobileMenuOpen = true" 
                                        class="md:hidden -ml-1 p-2 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-900 focus:outline-none transition-colors">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>
                                </button>
                                
                                <h2 class="text-base sm:text-lg font-bold text-gray-800 leading-tight">
                                    {{ $header }}
                                </h2>
                            </div>
                            
                            <!-- Date Badge with Calendar Icon -->
                            <div class="flex items-center gap-2 px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs font-semibold text-gray-600 shadow-sm shrink-0">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="hidden sm:inline">{{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
                                <span class="sm:hidden">{{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMM Y') }}</span>
                            </div>
                        </div>
                    </header>
                @endisset

                <!-- Main Content Viewport -->
                <main class="flex-1 overflow-y-auto bg-gray-50 p-4 sm:p-6 md:p-8">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
