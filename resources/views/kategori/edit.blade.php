<x-app-layout>
    <x-slot name="header">{{ __('Edit Kategori') }}</x-slot>

    <div class="max-w-lg">
        <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
            <h3 class="text-sm font-bold text-gray-800 mb-5 pb-3 border-b border-gray-100">Edit Kategori: {{ $kategori->name }}</h3>

            <form method="POST" action="{{ route('kategori.update', $kategori) }}" class="space-y-4">
                @csrf @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Kode Kategori <span class="text-red-500">*</span></label>
                    <input type="text" name="kode" value="{{ old('kode', $kategori->kode) }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 outline-none @error('kode') border-red-400 @enderror">
                    @error('kode') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Kategori <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $kategori->name) }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 outline-none @error('name') border-red-400 @enderror">
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Parent Kategori <span class="text-gray-400">(opsional)</span></label>
                    <select name="parent_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 outline-none bg-white">
                        <option value="">-- Kategori Utama --</option>
                        @foreach($parentCategories as $parent)
                            <option value="{{ $parent->id }}" {{ old('parent_id', $kategori->parent_id) == $parent->id ? 'selected' : '' }}>
                                {{ $parent->kode ? "[{$parent->kode}] " : '' }}{{ $parent->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Deskripsi</label>
                    <textarea name="deskripsi" rows="3" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 outline-none resize-none">{{ old('deskripsi', $kategori->deskripsi) }}</textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2.5 rounded-lg transition-colors">
                        Simpan Perubahan
                    </button>
                    <a href="{{ route('kategori.index') }}" class="flex-1 text-center border border-gray-200 text-gray-600 hover:bg-gray-50 text-sm font-semibold py-2.5 rounded-lg transition-colors">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
