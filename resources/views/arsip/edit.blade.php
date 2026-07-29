<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Perbarui Informasi Arsip') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6 border-b pb-4">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('Formulir Pembaruan Data Arsip') }}</h3>
                        <a href="{{ route('arsip.index') }}">
                            <x-secondary-button type="button">
                                {{ __('Kembali') }}
                            </x-secondary-button>
                        </a>
                    </div>

                    <form action="{{ route('arsip.update', $arsip->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <!-- Grid Layout for Form -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- Nomor / Kode Arsip -->
                            <div>
                                <x-input-label for="nomor_arsip" :value="__('Nomor / Kode Arsip')" />
                                <x-text-input id="nomor_arsip" class="block mt-1 w-full" type="text" name="nomor_arsip" :value="old('nomor_arsip', $arsip->nomor_arsip)" required />
                                <x-input-error :messages="$errors->get('nomor_arsip')" class="mt-2" />
                            </div>

                            <!-- Judul Dokumen -->
                            <div>
                                <x-input-label for="judul" :value="__('Judul Dokumen')" />
                                <x-text-input id="judul" class="block mt-1 w-full" type="text" name="judul" :value="old('judul', $arsip->judul)" required />
                                <x-input-error :messages="$errors->get('judul')" class="mt-2" />
                            </div>

                            <!-- Kategori Arsip -->
                            <div class="mt-4">
                                <x-input-label for="kategori_id" :value="__('Kategori Arsip')" />
                                <select id="kategori_id" name="kategori_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full bg-white text-gray-900" required>
                                    @foreach($kategori as $k)
                                        <option value="{{ $k->id }}" {{ old('kategori_id', $arsip->kategori_id) == $k->id ? 'selected' : '' }}>{{ $k->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('kategori_id')" class="mt-2" />
                            </div>

                            <!-- Divisi Penanggung Jawab -->
                            <div class="mt-4">
                                <x-input-label for="divisi_id" :value="__('Divisi Penanggung Jawab')" />
                                <select id="divisi_id" name="divisi_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full bg-white text-gray-900" required>
                                    @if(Auth::user()->hasRole('Operator'))
                                        <option value="{{ Auth::user()->divisi->id }}" selected>{{ Auth::user()->divisi->name }}</option>
                                    @else
                                        @foreach($divisi as $d)
                                            <option value="{{ $d->id }}" {{ old('divisi_id', $arsip->divisi_id) == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <x-input-error :messages="$errors->get('divisi_id')" class="mt-2" />
                            </div>

                            <!-- File Upload (Optional) -->
                            <div class="mt-4">
                                <x-input-label for="file" :value="__('Ganti File Dokumen (Kosongkan jika tidak diubah)')" />
                                <x-text-input id="file" class="block mt-1 w-full border border-gray-300 p-2" type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" />
                                <small class="text-xs text-gray-500 mt-1 block">{{ __('File saat ini tersimpan dengan aman.') }}</small>
                                <x-input-error :messages="$errors->get('file')" class="mt-2" />
                            </div>

                            <!-- Masa Retensi -->
                            <div class="mt-4">
                                <x-input-label for="retention_date" :value="__('Masa Retensi (Jadwal Retensi Arsip / JRA)')" />
                                <x-text-input id="retention_date" class="block mt-1 w-full" type="date" name="retention_date" :value="old('retention_date', $arsip->retention_date->format('Y-m-d'))" required />
                                <x-input-error :messages="$errors->get('retention_date')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Status & Deskripsi Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <!-- Status -->
                            <div>
                                <x-input-label for="status" :value="__('Status Dokumen')" />
                                <select id="status" name="status" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full bg-white text-gray-900" required>
                                    <option value="Aktif" {{ old('status', $arsip->status) === 'Aktif' ? 'selected' : '' }}>{{ __('Aktif') }}</option>
                                    <option value="Expired" {{ old('status', $arsip->status) === 'Expired' ? 'selected' : '' }}>{{ __('Expired') }}</option>
                                    <option value="Dimusnahkan" {{ old('status', $arsip->status) === 'Dimusnahkan' ? 'selected' : '' }}>{{ __('Dimusnahkan') }}</option>
                                </select>
                                <x-input-error :messages="$errors->get('status')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Deskripsi (Full Width) -->
                        <div class="mt-6">
                            <div class="flex items-center justify-between mb-1">
                                <x-input-label for="deskripsi" :value="__('Deskripsi / Ringkasan Arsip')" />
                                <div class="flex items-center gap-2 text-xs font-semibold text-indigo-600">
                                    <button type="button" id="undo-btn" class="hover:underline opacity-50 cursor-not-allowed" disabled>{{ __('Undo') }}</button>
                                    <span class="text-gray-300">|</span>
                                    <button type="button" id="redo-btn" class="hover:underline opacity-50 cursor-not-allowed" disabled>{{ __('Redo') }}</button>
                                </div>
                            </div>
                            <textarea id="deskripsi" name="deskripsi" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full bg-white text-gray-900" rows="4" placeholder="{{ __('Tuliskan deskripsi singkat dokumen di sini...') }}">{{ old('deskripsi', $arsip->deskripsi) }}</textarea>
                            <x-input-error :messages="$errors->get('deskripsi')" class="mt-2" />
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end gap-4 mt-8 border-t pt-4">
                            <a href="{{ route('arsip.index') }}">
                                <x-secondary-button type="button">
                                    {{ __('Batalkan') }}
                                </x-secondary-button>
                            </a>
                            
                            <x-primary-button type="submit">
                                {{ __('Simpan Perubahan') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Undo Redo Text History Tracking -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const textarea = document.getElementById('deskripsi');
            const undoBtn = document.getElementById('undo-btn');
            const redoBtn = document.getElementById('redo-btn');

            if (!textarea || !undoBtn || !redoBtn) return;

            let history = [textarea.value];
            let position = 0;

            // Debounce state saving
            let timeout;
            textarea.addEventListener('input', function () {
                clearTimeout(timeout);
                timeout = setTimeout(saveState, 250);
            });

            function saveState() {
                if (position < history.length - 1) {
                    history = history.slice(0, position + 1);
                }
                
                if (textarea.value !== history[history.length - 1]) {
                    history.push(textarea.value);
                    position = history.length - 1;
                }
                updateButtons();
            }

            undoBtn.addEventListener('click', function (e) {
                e.preventDefault();
                if (position > 0) {
                    position--;
                    textarea.value = history[position];
                    textarea.focus();
                    updateButtons();
                }
            });

            redoBtn.addEventListener('click', function (e) {
                e.preventDefault();
                if (position < history.length - 1) {
                    position++;
                    textarea.value = history[position];
                    textarea.focus();
                    updateButtons();
                }
            });

            function updateButtons() {
                if (position > 0) {
                    undoBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    undoBtn.disabled = false;
                } else {
                    undoBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    undoBtn.disabled = true;
                }

                if (position < history.length - 1) {
                    redoBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    redoBtn.disabled = false;
                } else {
                    redoBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    redoBtn.disabled = true;
                }
            }

            // Inital check
            updateButtons();
        });
    </script>
</x-app-layout>
