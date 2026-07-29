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
    <body class="font-sans text-gray-900 antialiased bg-gray-50">
        
        <div class="min-h-screen flex flex-col md:flex-row">
            
            <!-- Left Panel (Sidebar/Hero) - Visible on desktop, hidden on mobile -->
            <div class="hidden md:flex md:w-5/12 bg-[#131432] text-white flex-col justify-between p-12 relative overflow-hidden shrink-0">
                <!-- Blurred decorative backgrounds -->
                <div class="absolute w-96 h-96 rounded-full bg-indigo-500/10 blur-[100px] -top-20 -left-20 pointer-events-none"></div>
                <div class="absolute w-96 h-96 rounded-full bg-blue-500/10 blur-[100px] -bottom-20 -right-20 pointer-events-none"></div>

                <!-- Top Logo Branding -->
                <div class="flex items-center gap-3 z-10 text-white">
                    <x-application-logo />
                </div>

                <!-- Middle Header info -->
                <div class="my-auto z-10 pr-6">
                    <h1 class="text-3xl font-normal leading-tight text-white mb-3">
                        Sistem Informasi
                        <span class="text-indigo-400 font-extrabold block mt-1">Pengarsipan Kampus</span>
                    </h1>
                    
                    <p class="text-xs text-gray-300 leading-relaxed mb-8">
                        Platform digital untuk mengelola, mengarsipkan, dan mengamankan berkas digital Sekolah Tinggi Theologia Nazarene Indonesia secara terpusat dan aman.
                    </p>

                    <!-- Feature indicators -->
                    <div class="space-y-4">
                        <!-- Feature 1 -->
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded bg-indigo-600/30 border border-indigo-500/50 flex items-center justify-center text-indigo-300 font-bold text-xs shrink-0">
                                P
                            </div>
                            <div>
                                <h4 class="text-xs font-semibold text-white">Penetapan</h4>
                                <p class="text-[10px] text-gray-400">Penyimpanan dokumen digital terenkripsi.</p>
                            </div>
                        </div>
                        
                        <!-- Feature 2 -->
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded bg-indigo-600/30 border border-indigo-500/50 flex items-center justify-center text-indigo-300 font-bold text-xs shrink-0">
                                A
                            </div>
                            <div>
                                <h4 class="text-xs font-semibold text-white">Akses</h4>
                                <p class="text-[10px] text-gray-400">Persetujuan peminjaman digital terintegrasi.</p>
                            </div>
                        </div>

                        <!-- Feature 3 -->
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded bg-indigo-600/30 border border-indigo-500/50 flex items-center justify-center text-indigo-300 font-bold text-xs shrink-0">
                                R
                            </div>
                            <div>
                                <h4 class="text-xs font-semibold text-white">Retensi</h4>
                                <p class="text-[10px] text-gray-400">Pemusnahan berkas kedaluwarsa otomatis.</p>
                            </div>
                        </div>

                        <!-- Feature 4 -->
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded bg-indigo-600/30 border border-indigo-500/50 flex items-center justify-center text-indigo-300 font-bold text-xs shrink-0">
                                L
                            </div>
                            <div>
                                <h4 class="text-xs font-semibold text-white">Log Aktivitas</h4>
                                <p class="text-[10px] text-gray-400">Histori Audit Trail lengkap demi akuntabilitas.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer (Bottom Left) -->
                <div class="text-[10px] text-gray-500 z-10 mt-auto">
                    &copy; 2026 STTNI &mdash; Unit Kearsipan Kampus
                </div>
            </div>

            <!-- Right Panel (Login/Register Card Container) -->
            <div class="flex-1 flex flex-col justify-center items-center p-6 md:p-12 bg-gray-50">
                <!-- Mobile Logo Display -->
                <div class="flex md:hidden items-center gap-3 mb-8 text-gray-900">
                    <x-application-logo />
                </div>

                <!-- Form Card wrapper -->
                <div class="w-full sm:max-w-md bg-white border border-gray-200 shadow-sm rounded-2xl p-8 md:p-10">
                    {{ $slot }}
                </div>
            </div>
        </div>

    </body>
</html>
