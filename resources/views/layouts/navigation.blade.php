<div class="flex-1 flex flex-col h-full bg-[#181c32] text-gray-300">
    <!-- Brand / Header -->
    <div class="h-16 flex items-center px-4 border-b border-white/10 bg-[#13172e] shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center text-white">
            <x-application-logo />
        </a>
    </div>

    <!-- Navigation Links -->
    <nav class="flex-1 py-4 px-3 space-y-1.5 overflow-y-auto">
        
        <!-- New Archive Shortcut (Superadmin/Operator) -->
        @hasanyrole('Superadmin|Operator')
            <div class="px-2 mb-6">
                <a href="{{ route('arsip.create') }}" class="flex items-center justify-center gap-2.5 w-full py-3 bg-[#4f46e5] hover:bg-indigo-700 text-white rounded-full font-semibold shadow-md hover:shadow-lg transition-all text-sm">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>{{ __('Unggah Dokumen') }}</span>
                </a>
            </div>
        @endhasanyrole

        <!-- DASHBOARD -->
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('dashboard') ? 'bg-[#4f46e5] text-white font-semibold shadow-md' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>{{ __('Dashboard') }}</span>
        </a>

        <!-- DOKUMEN & ARSIP SECTION -->
        <div class="pt-4">
            <span class="text-[9px] text-gray-500 font-bold uppercase tracking-wider px-4 block mb-2">{{ __('PENGARSIPAN') }}</span>
            
            <a href="{{ route('arsip.index') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('arsip.index') ? 'bg-[#4f46e5] text-white font-semibold shadow-md' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8" />
                </svg>
                <span>{{ __('Daftar Dokumen') }}</span>
            </a>
        </div>

        <!-- TRANSAKSI AKSES SECTION -->
        <div class="pt-4">
            <span class="text-[9px] text-gray-500 font-bold uppercase tracking-wider px-4 block mb-2">{{ __('PELAKSANAAN & AKSES') }}</span>

            <a href="{{ route('peminjaman.index') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('peminjaman.index') ? 'bg-[#4f46e5] text-white font-semibold shadow-md' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <span>{{ __('Peminjaman Saya') }}</span>
            </a>

            @hasanyrole('Superadmin|Operator')
                <a href="{{ route('peminjaman.manage') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('peminjaman.manage') ? 'bg-[#4f46e5] text-white font-semibold shadow-md' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622" />
                    </svg>
                    <span>{{ __('Persetujuan Akses') }}</span>
                </a>
            @endhasanyrole
        </div>

        <!-- ADMINISTRASI SECTION -->
        @hasanyrole('Superadmin|Operator')
            <div class="pt-4">
                <span class="text-[9px] text-gray-500 font-bold uppercase tracking-wider px-4 block mb-2">{{ __('ADMINISTRASI') }}</span>
                
                <a href="{{ route('audit.index') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('audit.index') ? 'bg-[#4f46e5] text-white font-semibold shadow-md' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" />
                    </svg>
                    <span>{{ __('Audit Trail Log') }}</span>
                </a>
            </div>
        @endhasanyrole
    </nav>

    <!-- Bottom Section: Profile Card -->
    <div class="p-4 border-t border-white/10 bg-[#13172e] flex items-center justify-between shrink-0">
        <div class="flex items-center gap-3 min-w-0">
            <!-- Initials Avatar -->
            @php
                $words = explode(' ', Auth::user()->name);
                $initials = '';
                foreach ($words as $word) {
                    $initials .= strtoupper(substr($word, 0, 1));
                }
                $initials = substr($initials, 0, 2);
            @endphp
            <div class="w-9 h-9 rounded-full bg-[#4f46e5] text-white flex items-center justify-center font-bold text-xs shrink-0">
                {{ $initials }}
            </div>
            <!-- Name & Role -->
            <div class="flex flex-col min-w-0">
                <span class="text-xs font-semibold text-white truncate leading-none">{{ Auth::user()->name }}</span>
                <span class="text-[9px] text-gray-400 uppercase tracking-wider mt-1 truncate">{{ Auth::user()->roles->pluck('name')->first() }}</span>
            </div>
        </div>
        
        <!-- Logout Door Trigger -->
        <form method="POST" action="{{ route('logout') }}" class="m-0 shrink-0">
            @csrf
            <button type="submit" class="text-gray-400 hover:text-white p-1 rounded transition-colors" title="{{ __('Keluar') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
            </button>
        </form>
    </div>
</div>
