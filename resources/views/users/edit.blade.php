<x-app-layout>
    <x-slot name="header">{{ __('Edit Pengguna') }}</x-slot>

    <div class="max-w-2xl">
        <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
            <h3 class="text-sm font-bold text-gray-800 mb-5 pb-3 border-b border-gray-100">Edit: {{ $user->name }}</h3>
            <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-4">
                @csrf @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 outline-none @error('name') border-red-400 @enderror">
                        @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">NIK / NIM</label>
                        <input type="text" name="nik_nim" value="{{ old('nik_nim', $user->nik_nim) }}"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 outline-none @error('email') border-red-400 @enderror">
                    @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Password Baru <span class="text-gray-400">(kosongkan jika tidak diubah)</span></label>
                        <input type="password" name="password"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Role <span class="text-red-500">*</span></label>
                        <select name="role" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-indigo-400 outline-none">
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" {{ old('role', $user->roles->first()?->name) == $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Divisi</label>
                        <select name="divisi_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-indigo-400 outline-none">
                            <option value="">-- Tidak ada Divisi --</option>
                            @foreach($divisi as $d)
                                <option value="{{ $d->id }}" {{ old('divisi_id', $user->divisi_id) == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Status Akun <span class="text-red-500">*</span></label>
                        <select name="status_akun" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-indigo-400 outline-none">
                            <option value="Aktif" {{ old('status_akun', $user->status_akun) == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="Suspended" {{ old('status_akun', $user->status_akun) == 'Suspended' ? 'selected' : '' }}>Suspended</option>
                            <option value="Deactivated" {{ old('status_akun', $user->status_akun) == 'Deactivated' ? 'selected' : '' }}>Deactivated</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2.5 rounded-lg transition-colors">
                        Simpan Perubahan
                    </button>
                    <a href="{{ route('users.index') }}" class="flex-1 text-center border border-gray-200 text-gray-600 hover:bg-gray-50 text-sm font-semibold py-2.5 rounded-lg transition-colors">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
