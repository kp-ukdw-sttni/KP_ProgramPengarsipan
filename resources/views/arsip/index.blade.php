<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar Arsip STTNI') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Success/Error Alerts -->
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Google Drive-Inspired File Explorer Header & Filter Panel -->
            <div class="bg-white border border-gray-200 rounded-2xl mb-6 p-6 shadow-sm">
                
                <!-- Top Row: Breadcrumb and Action Buttons -->
                <div class="flex items-center justify-between flex-wrap gap-4 mb-6">
                    
                    <!-- Breadcrumb (Left) -->
                    <div class="flex items-center flex-wrap gap-2">
                        <span class="bg-gray-100 px-4 py-1.5 rounded-full text-gray-700 text-sm font-medium">
                            {{ __('Arsip STTNI') }}
                        </span>
                        
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                        
                        <div class="flex items-center gap-1 text-lg font-normal text-gray-900 cursor-pointer">
                            <span>{{ __('Semua Dokumen') }}</span>
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    <!-- Action Icons (Right) -->
                    <div class="flex items-center gap-3">


                        <!-- Shared People Button -->
                        <button class="text-gray-600 hover:bg-gray-100 p-2 rounded-full transition-colors" title="Orang yang berbagi">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </button>

                        <!-- View Toggle (List/Grid Segmented Control) -->
                        <div class="flex border border-gray-300 rounded-full overflow-hidden">
                            <!-- List View (Active) -->
                            <button type="button" class="bg-blue-100 px-3 py-1.5 text-blue-800 transition-colors" title="Tampilan Daftar">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>
                            <!-- Grid View (Inactive) -->
                            <button type="button" class="bg-white px-3 py-1.5 text-gray-600 hover:bg-gray-50 transition-colors" title="Tampilan Kisi">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                            </button>
                        </div>

                        <!-- Info Button -->
                        <button class="p-2 rounded-full hover:bg-gray-100 border border-transparent text-gray-600 transition-colors" title="Detail Info">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Bottom Row: Filter Chips (Material Design 3 style) -->
                <form action="{{ route('arsip.index') }}" method="GET" x-data="{ activeDropdown: null }">
                    <div class="flex flex-wrap items-center gap-2">
                        
                        <!-- Search input (like google drive's filter keyword search) -->
                        <div class="relative flex-grow max-w-xs">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            </div>
                            <input type="text" name="judul" placeholder="Cari judul..." value="{{ request('judul') }}" class="block w-full pl-9 pr-3 py-1.5 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                        </div>

                        <div class="relative flex-grow max-w-xs">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" /></svg>
                            </div>
                            <input type="text" name="nomor_arsip" placeholder="Cari nomor..." value="{{ request('nomor_arsip') }}" class="block w-full pl-9 pr-3 py-1.5 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                        </div>

                        <!-- Chip: Type (Kategori) -->
                        <div class="relative">
                            <button type="button" @click="activeDropdown = (activeDropdown === 'type' ? null : 'type')" class="flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition-colors cursor-pointer">
                                <span>{{ __('Tipe Kategori') }}</span>
                                <span class="text-xs text-indigo-600 font-semibold" id="selected-kategori">
                                    @if(request('kategori_id'))
                                        ({{ $kategori->firstWhere('id', request('kategori_id'))?->name }})
                                    @endif
                                </span>
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="activeDropdown === 'type'" @click.outside="activeDropdown = null" class="absolute left-0 mt-1 w-56 bg-white border border-gray-200 rounded-md shadow-lg z-50 py-1" style="display: none;">
                                <label class="flex items-center px-4 py-2 hover:bg-gray-100 cursor-pointer">
                                    <input type="radio" name="kategori_id" value="" class="mr-2 text-indigo-600 focus:ring-indigo-500" {{ !request('kategori_id') ? 'checked' : '' }} @change="$el.form.submit()">
                                    <span class="text-sm text-gray-700">{{ __('Semua Kategori') }}</span>
                                </label>
                                @foreach($kategori as $k)
                                    <label class="flex items-center px-4 py-2 hover:bg-gray-100 cursor-pointer">
                                        <input type="radio" name="kategori_id" value="{{ $k->id }}" class="mr-2 text-indigo-600 focus:ring-indigo-500" {{ request('kategori_id') == $k->id ? 'checked' : '' }} @change="$el.form.submit()">
                                        <span class="text-sm text-gray-700">{{ $k->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Chip: Source / Division -->
                        <div class="relative">
                            <button type="button" @click="activeDropdown = (activeDropdown === 'source' ? null : 'source')" class="flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition-colors cursor-pointer">
                                <span>{{ __('Sumber / Divisi') }}</span>
                                <span class="text-xs text-indigo-600 font-semibold" id="selected-divisi">
                                    @if(request('divisi_id'))
                                        ({{ $divisi->firstWhere('id', request('divisi_id'))?->name }})
                                    @endif
                                </span>
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="activeDropdown === 'source'" @click.outside="activeDropdown = null" class="absolute left-0 mt-1 w-56 bg-white border border-gray-200 rounded-md shadow-lg z-50 py-1" style="display: none;">
                                <label class="flex items-center px-4 py-2 hover:bg-gray-100 cursor-pointer">
                                    <input type="radio" name="divisi_id" value="" class="mr-2 text-indigo-600 focus:ring-indigo-500" {{ !request('divisi_id') ? 'checked' : '' }} @change="$el.form.submit()">
                                    <span class="text-sm text-gray-700">{{ __('Semua Divisi') }}</span>
                                </label>
                                @foreach($divisi as $d)
                                    <label class="flex items-center px-4 py-2 hover:bg-gray-100 cursor-pointer">
                                        <input type="radio" name="divisi_id" value="{{ $d->id }}" class="mr-2 text-indigo-600 focus:ring-indigo-500" {{ request('divisi_id') == $d->id ? 'checked' : '' }} @change="$el.form.submit()">
                                        <span class="text-sm text-gray-700">{{ $d->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Action buttons for submit and reset -->
                        <div class="flex items-center gap-2 ml-auto">
                            <x-primary-button type="submit" class="text-xs py-1.5 px-3">
                                {{ __('Cari') }}
                            </x-primary-button>
                            <a href="{{ route('arsip.index') }}">
                                <x-secondary-button type="button" class="text-xs py-1.5 px-3">
                                    {{ __('Reset') }}
                                </x-secondary-button>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Table Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Hasil Pencarian Arsip') }}</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Nomor Arsip') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Judul Dokumen') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Kategori') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Divisi') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Batas Retensi') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Aksi') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($arsip as $a)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <code>{{ $a->nomor_arsip }}</code>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-semibold text-gray-900">{{ $a->judul }}</div>
                                            @if($a->deskripsi)
                                                <div class="text-xs text-gray-500 mt-1">{{ Str::limit($a->deskripsi, 60) }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ $a->kategori->name }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $a->divisi->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span class="{{ $a->retention_date->isPast() ? 'text-red-600 font-bold' : '' }}">
                                                {{ $a->retention_date->format('d M Y') }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($a->status === 'Aktif')
                                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    {{ __('Aktif') }}
                                                </span>
                                            @elseif($a->status === 'Expired')
                                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    {{ __('Expired') }}
                                                </span>
                                            @else
                                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    {{ __('Dimusnahkan') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end gap-2">
                                                
                                                @if(Auth::user()->hasRole('Superadmin') || (Auth::user()->hasRole('Operator') && $a->divisi_id == Auth::user()->divisi_id))
                                                    <!-- Admins and owner Operators can read and download immediately -->
                                                    <a href="{{ route('arsip.view', $a->id) }}" target="_blank">
                                                        <x-primary-button class="text-xs px-2.5 py-1.5">
                                                            {{ __('Lihat') }}
                                                        </x-primary-button>
                                                    </a>
                                                    <a href="{{ route('arsip.download', $a->id) }}">
                                                        <x-secondary-button class="text-xs px-2.5 py-1.5">
                                                            {{ __('Unduh') }}
                                                        </x-secondary-button>
                                                    </a>
                                                    
                                                    <!-- Edit Action -->
                                                    <a href="{{ route('arsip.edit', $a->id) }}">
                                                        <x-secondary-button class="text-xs px-2.5 py-1.5">
                                                            {{ __('Edit') }}
                                                        </x-secondary-button>
                                                    </a>

                                                    <!-- Delete Action -->
                                                    <form action="{{ route('arsip.destroy', $a->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen ini secara permanen dari server?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <x-danger-button class="text-xs px-2.5 py-1.5">
                                                            {{ __('Hapus') }}
                                                        </x-danger-button>
                                                    </form>
                                                @else
                                                    <!-- Karyawan Request Workflow -->
                                                    @if($a->status === 'Expired')
                                                        <span class="text-xs text-gray-400 italic">{{ __('Retensi Habis') }}</span>
                                                    @else
                                                        @php
                                                            $req = $activePeminjaman->get($a->id);
                                                        @endphp

                                                        @if(!$req)
                                                            <form action="{{ route('peminjaman.request', $a->id) }}" method="POST">
                                                                @csrf
                                                                <x-primary-button class="text-xs px-2.5 py-1.5">
                                                                    {{ __('Minta Akses') }}
                                                                </x-primary-button>
                                                            </form>
                                                        @elseif($req->status_approval === 'Pending')
                                                            <span class="px-2.5 py-1 text-xs font-semibold rounded bg-amber-100 text-amber-800">{{ __('Menunggu Persetujuan') }}</span>
                                                        @elseif($req->isActive())
                                                            <a href="{{ route('arsip.view', $a->id) }}" target="_blank">
                                                                <x-primary-button class="text-xs px-2.5 py-1.5 bg-green-600 hover:bg-green-700">
                                                                    {{ __('Buka Viewer') }}
                                                                </x-primary-button>
                                                            </a>
                                                            <a href="{{ route('arsip.download', $a->id) }}">
                                                                <x-secondary-button class="text-xs px-2.5 py-1.5">
                                                                    {{ __('Unduh') }}
                                                                </x-secondary-button>
                                                            </a>
                                                        @else
                                                            <form action="{{ route('peminjaman.request', $a->id) }}" method="POST">
                                                                @csrf
                                                                <x-primary-button class="text-xs px-2.5 py-1.5">
                                                                    {{ __('Minta Akses Lagi') }}
                                                                </x-primary-button>
                                                            </form>
                                                        @endif
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-10 text-center text-sm text-gray-500">
                                            {{ __('Tidak ditemukan dokumen arsip yang sesuai filter pencarian.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $arsip->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
