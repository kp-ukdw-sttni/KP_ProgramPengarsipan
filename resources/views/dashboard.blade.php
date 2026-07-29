<x-app-layout>
    <x-slot name="header">
        {{ __('Dashboard') }}
    </x-slot>

    <!-- Success Alerts -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <!-- Six-Card Metrics Grid (e-SPMI layout & colors) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-5 mb-8">
        
        <!-- Card 1: Total Arsip (Deep Blue-Indigo) -->
        <div class="bg-[#4f46e5] text-white rounded-2xl p-5 shadow-sm relative overflow-hidden flex justify-between items-center">
            <div>
                <span class="text-xs text-white/80 font-medium block mb-1.5">{{ __('Total Arsip') }}</span>
                <span class="text-3xl font-extrabold">{{ $totalArsip }}</span>
            </div>
            <div class="p-2.5 bg-white/15 rounded-xl shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
        </div>

        <!-- Card 2: Peminjaman Aktif (Medium Blue) -->
        <div class="bg-[#2563eb] text-white rounded-2xl p-5 shadow-sm relative overflow-hidden flex justify-between items-center">
            <div>
                <span class="text-xs text-white/80 font-medium block mb-1.5">{{ __('Pinjam Aktif') }}</span>
                <span class="text-3xl font-extrabold">{{ $totalActivePeminjaman }}</span>
            </div>
            <div class="p-2.5 bg-white/15 rounded-xl shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                </svg>
            </div>
        </div>

        <!-- Card 3: Arsip Expired (Purple) -->
        <div class="bg-[#8b5cf6] text-white rounded-2xl p-5 shadow-sm relative overflow-hidden flex justify-between items-center">
            <div>
                <span class="text-xs text-white/80 font-medium block mb-1.5">{{ __('Arsip Expired') }}</span>
                <span class="text-3xl font-extrabold">{{ $totalExpiredArsip }}</span>
            </div>
            <div class="p-2.5 bg-white/15 rounded-xl shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
        </div>

        <!-- Card 4: Pending Peminjaman (Red) -->
        <div class="bg-[#ef4444] text-white rounded-2xl p-5 shadow-sm relative overflow-hidden flex justify-between items-center">
            <div>
                <span class="text-xs text-white/80 font-medium block mb-1.5">{{ __('Akses Pending') }}</span>
                <span class="text-3xl font-extrabold">{{ $totalPendingPeminjaman }}</span>
            </div>
            <div class="p-2.5 bg-white/15 rounded-xl shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <!-- Card 5: Peminjaman Selesai (Amber/Orange) -->
        <div class="bg-[#f59e0b] text-white rounded-2xl p-5 shadow-sm relative overflow-hidden flex justify-between items-center">
            <div>
                <span class="text-xs text-white/80 font-medium block mb-1.5">{{ __('Akses Selesai') }}</span>
                <span class="text-3xl font-extrabold">{{ $totalCompletedPeminjaman }}</span>
            </div>
            <div class="p-2.5 bg-white/15 rounded-xl shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <!-- Card 6: Pengguna / Divisi (Green) -->
        <div class="bg-[#10b981] text-white rounded-2xl p-5 shadow-sm relative overflow-hidden flex justify-between items-center">
            <div>
                @if(Auth::user()->hasRole('Karyawan'))
                    <span class="text-[10px] text-white/80 font-medium block mb-1">{{ __('Divisi Saya') }}</span>
                    <span class="text-sm font-extrabold leading-tight block truncate max-w-[110px]">{{ Auth::user()->divisi->name ?? '-' }}</span>
                @else
                    <span class="text-xs text-white/80 font-medium block mb-1.5">{{ __('Staf Terdaftar') }}</span>
                    <span class="text-3xl font-extrabold">{{ $totalUsers }}</span>
                @endif
            </div>
            <div class="p-2.5 bg-white/15 rounded-xl shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Main Content Panel (e-SPMI style) -->
    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
        <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-100">
            <h3 class="text-base font-bold text-gray-800">
                {{ Auth::user()->hasRole('Karyawan') ? __('Riwayat Pengajuan Akses Saya') : __('Riwayat Pengajuan Peminjaman Terbaru') }}
            </h3>
            <a href="{{ Auth::user()->hasRole('Superadmin|Operator') ? route('peminjaman.manage') : route('peminjaman.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold hover:underline">
                {{ __('Lihat Selengkapnya') }} &rarr;
            </a>
        </div>

        @if($recentPeminjaman->isEmpty())
            <div class="py-12 text-center text-sm text-gray-400 font-medium">
                {{ __('Tidak ada riwayat aktivitas peminjaman terdokumentasi.') }}
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead>
                        <tr class="text-gray-500 font-semibold border-b">
                            <th scope="col" class="px-4 py-3 text-left w-12">No</th>
                            @if(!Auth::user()->hasRole('Karyawan'))
                                <th scope="col" class="px-4 py-3 text-left">Nama Staf / Karyawan</th>
                            @endif
                            <th scope="col" class="px-4 py-3 text-left">Dokumen Terkait</th>
                            <th scope="col" class="px-4 py-3 text-left">Status</th>
                            <th scope="col" class="px-4 py-3 text-left">Masa Aktif Akses</th>
                            <th scope="col" class="px-4 py-3 text-right">Waktu Pengajuan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        @foreach($recentPeminjaman as $index => $rp)
                            <tr class="hover:bg-gray-50/55 transition-colors">
                                <td class="px-4 py-3.5 font-medium text-gray-400">{{ $index + 1 }}</td>
                                @if(!Auth::user()->hasRole('Karyawan'))
                                    <td class="px-4 py-3.5">
                                        <div class="font-bold text-gray-800 leading-none">{{ $rp->user->name }}</div>
                                        <div class="text-[10px] text-gray-400 mt-1">{{ $rp->user->email }}</div>
                                    </td>
                                @endif
                                <td class="px-4 py-3.5">
                                    <div class="font-semibold text-gray-900 leading-tight">{{ $rp->arsip->judul }}</div>
                                    <div class="text-[10px] text-gray-400 mt-1"><code>{{ $rp->arsip->nomor_arsip }}</code> &bull; {{ $rp->arsip->kategori->name }}</div>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    @if($rp->status_approval === 'Pending')
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-50 text-amber-700 border border-amber-200">
                                            Pending
                                        </span>
                                    @elseif($rp->status_approval === 'Approved')
                                        @if($rp->expired_at && $rp->expired_at->isPast())
                                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-50 text-gray-600 border border-gray-200">
                                                Expired
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-green-50 text-green-700 border border-green-200">
                                                Active
                                            </span>
                                        @endif
                                    @elseif($rp->status_approval === 'Rejected')
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-red-50 text-red-700 border border-red-200">
                                            Rejected
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-50 text-gray-700 border border-gray-200">
                                            {{ $rp->status_approval }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-xs">
                                    @if($rp->status_approval === 'Approved')
                                        <span class="text-indigo-600 font-semibold">s.d. {{ $rp->expired_at->format('d M Y, H:i') }} WIB</span>
                                    @else
                                        <span class="text-gray-400 italic">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-right whitespace-nowrap text-xs text-gray-500 font-mono">
                                    {{ $rp->created_at->format('d M Y, H:i') }} WIB
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>
