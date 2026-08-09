<x-app-layout>
    <x-slot name="header">{{ __('Recycle Bin — Arsip Terhapus') }}</x-slot>

    @if(session('success'))
        <div class="mb-5 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-5 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-gray-800">Recycle Bin</h3>
                <p class="text-xs text-gray-400 mt-0.5">Arsip yang dihapus dapat dipulihkan atau dihapus permanen.</p>
            </div>
            <a href="{{ route('arsip.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold">
                ← Kembali ke Daftar Arsip
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead>
                    <tr class="text-gray-500 font-semibold bg-gray-50">
                        <th class="px-4 py-3 text-left">Kode Arsip</th>
                        <th class="px-4 py-3 text-left">Judul Dokumen</th>
                        <th class="px-4 py-3 text-left">Kategori</th>
                        <th class="px-4 py-3 text-left">Divisi</th>
                        <th class="px-4 py-3 text-left">Dihapus Pada</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    @forelse($arsip as $item)
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="px-4 py-3">
                                <code class="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded font-mono">{{ $item->nomor_arsip }}</code>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-900">{{ $item->judul }}</div>
                                @if($item->nomor_surat)
                                    <div class="text-xs text-gray-400 mt-0.5">No. Surat: {{ $item->nomor_surat }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $item->kategori?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $item->divisi?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500 font-mono">{{ $item->deleted_at->format('d M Y, H:i') }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap space-x-2">
                                {{-- Restore --}}
                                <form method="POST" action="{{ route('arsip.restore', $item->id) }}" class="inline">
                                    @csrf
                                    <button type="submit"
                                        class="text-xs font-semibold text-green-600 hover:text-green-800 bg-green-50 hover:bg-green-100 px-3 py-1.5 rounded-lg transition-colors">
                                        Pulihkan
                                    </button>
                                </form>

                                {{-- Hard Delete (Superadmin only) --}}
                                @role('Superadmin')
                                <form method="POST" action="{{ route('arsip.force_delete', $item->id) }}" class="inline"
                                    onsubmit="return confirm('PERHATIAN: Tindakan ini akan MENGHAPUS PERMANEN file dan data arsip ini dari server. Tidak dapat diurungkan. Lanjutkan?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="text-xs font-semibold text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition-colors">
                                        Hapus Permanen
                                    </button>
                                </form>
                                @endrole
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-2 text-gray-400">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                    <span class="text-sm font-medium">Recycle Bin kosong</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100">{{ $arsip->links() }}</div>
    </div>
</x-app-layout>
