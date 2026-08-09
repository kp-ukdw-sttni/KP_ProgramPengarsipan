<x-app-layout>
    <x-slot name="header">{{ __('Kategori & Klasifikasi Dokumen') }}</x-slot>

    @if(session('success'))
        <div class="mb-5 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-5 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Form Tambah Kategori --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm lg:col-span-1">
            <h3 class="text-sm font-bold text-gray-800 mb-4 pb-3 border-b border-gray-100">Tambah Kategori / Sub-Kategori</h3>
            <form method="POST" action="{{ route('kategori.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Kode Kategori <span class="text-red-500">*</span></label>
                    <input type="text" name="kode" value="{{ old('kode') }}" placeholder="Contoh: SK-01"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 outline-none @error('kode') border-red-400 @enderror">
                    @error('kode') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Kategori <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama kategori"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 outline-none @error('name') border-red-400 @enderror">
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Parent Kategori <span class="text-gray-400">(opsional)</span></label>
                    <select name="parent_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 outline-none bg-white">
                        <option value="">-- Kategori Utama (tidak ada parent) --</option>
                        @foreach($parentCategories as $parent)
                            <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                {{ $parent->kode ? "[{$parent->kode}] " : '' }}{{ $parent->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Deskripsi</label>
                    <textarea name="deskripsi" rows="3" placeholder="Deskripsi singkat kategori..."
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 outline-none resize-none">{{ old('deskripsi') }}</textarea>
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2.5 rounded-lg transition-colors">
                    Simpan Kategori
                </button>
            </form>
        </div>

        {{-- Tabel Daftar Kategori --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm lg:col-span-2 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-800">Master Kategori & Hirarki</h3>
                <span class="text-xs text-gray-400">{{ $categories->total() }} kategori utama</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead>
                        <tr class="text-gray-500 font-semibold bg-gray-50">
                            <th class="px-4 py-3 text-left">Kode</th>
                            <th class="px-4 py-3 text-left">Nama Kategori</th>
                            <th class="px-4 py-3 text-left">Deskripsi</th>
                            <th class="px-4 py-3 text-center">Jumlah Dokumen</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        @forelse($categories as $kategori)
                            {{-- Parent Row --}}
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="px-4 py-3">
                                    <code class="text-xs bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded font-mono">
                                        {{ $kategori->kode ?? '-' }}
                                    </code>
                                </td>
                                <td class="px-4 py-3 font-semibold text-gray-900">{{ $kategori->name }}</td>
                                <td class="px-4 py-3 text-xs text-gray-500 max-w-xs truncate">{{ $kategori->deskripsi ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-indigo-50 text-indigo-700">
                                        {{ $kategori->arsip_count }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('kategori.edit', $kategori) }}"
                                        class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 mr-3">Edit</a>
                                    <form method="POST" action="{{ route('kategori.destroy', $kategori) }}" class="inline" onsubmit="return confirm('Hapus kategori ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-red-500 hover:text-red-700">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            {{-- Sub-category Rows --}}
                            @foreach($kategori->children as $child)
                                <tr class="bg-gray-50/50 hover:bg-gray-100/50 transition-colors">
                                    <td class="pl-10 pr-4 py-2.5">
                                        <code class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded font-mono">
                                            {{ $child->kode ?? '-' }}
                                        </code>
                                    </td>
                                    <td class="px-4 py-2.5 text-gray-700 flex items-center gap-2">
                                        <svg class="w-3 h-3 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                        </svg>
                                        {{ $child->name }}
                                    </td>
                                    <td class="px-4 py-2.5 text-xs text-gray-400 max-w-xs truncate">{{ $child->deskripsi ?? '-' }}</td>
                                    <td class="px-4 py-2.5 text-center">
                                        <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-gray-100 text-gray-600">
                                            {{ $child->arsip()->count() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2.5 text-right whitespace-nowrap">
                                        <a href="{{ route('kategori.edit', $child) }}"
                                            class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 mr-3">Edit</a>
                                        <form method="POST" action="{{ route('kategori.destroy', $child) }}" class="inline" onsubmit="return confirm('Hapus sub-kategori ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-xs font-semibold text-red-500 hover:text-red-700">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr><td colspan="5" class="px-4 py-12 text-center text-sm text-gray-400">Belum ada kategori yang ditambahkan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $categories->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
