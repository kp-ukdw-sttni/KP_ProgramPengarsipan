<x-guest-layout>
    <!-- Welcome Header -->
    <div class="mb-6 text-center">
        <h1 class="text-xl font-bold text-gray-800">{{ __('Daftar Karyawan Baru') }}</h1>
        <p class="text-xs text-gray-500 mt-1">{{ __('Registrasikan akun karyawan untuk mengajukan peminjaman arsip digital') }}</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Nama Lengkap')" class="font-semibold text-gray-700" />
            <div class="relative mt-1">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                </span>
                <x-text-input id="name" class="block w-full pl-10 bg-gray-50/50 border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg text-sm text-gray-900" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Nama Lengkap Anda" />
            </div>
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Alamat Email')" class="font-semibold text-gray-700" />
            <div class="relative mt-1">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" /></svg>
                </span>
                <x-text-input id="email" class="block w-full pl-10 bg-gray-50/50 border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg text-sm text-gray-900" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="nama@sttni.ac.id" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- Divisi / Unit Kerja -->
        <div>
            <x-input-label for="divisi_id" :value="__('Divisi / Unit Kerja')" class="font-semibold text-gray-700" />
            <div class="relative mt-1">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                </span>
                <select id="divisi_id" name="divisi_id" class="block w-full pl-10 bg-gray-50/50 border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg text-sm text-gray-900 shadow-sm" required>
                    <option value="" disabled selected>-- Pilih Unit / Divisi Kerja --</option>
                    @foreach($divisi as $d)
                        <option value="{{ $d->id }}" {{ old('divisi_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <x-input-error :messages="$errors->get('divisi_id')" class="mt-1" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Kata Sandi Baru')" class="font-semibold text-gray-700" />
            <div class="relative mt-1">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                </span>
                <x-text-input id="password" class="block w-full pl-10 bg-gray-50/50 border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg text-sm text-gray-900"
                                type="password"
                                name="password"
                                required autocomplete="new-password" placeholder="Min. 8 karakter" />
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Kata Sandi')" class="font-semibold text-gray-700" />
            <div class="relative mt-1">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                </span>
                <x-text-input id="password_confirmation" class="block w-full pl-10 bg-gray-50/50 border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg text-sm text-gray-900"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi kata sandi" />
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <!-- Submit & Actions -->
        <div class="pt-4 space-y-3">
            <x-primary-button class="w-full justify-center py-2.5 rounded-lg font-semibold tracking-wide bg-indigo-600 hover:bg-indigo-700 focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all text-sm shadow-md hover:shadow-lg">
                {{ __('Daftar Akun Baru') }}
            </x-primary-button>
            
            <div class="text-center text-xs text-gray-500 pt-2">
                <span>{{ __('Sudah terdaftar?') }}</span>
                <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-800 font-semibold hover:underline ms-1">
                    {{ __('Masuk di sini') }}
                </a>
            </div>
        </div>
    </form>
</x-guest-layout>
