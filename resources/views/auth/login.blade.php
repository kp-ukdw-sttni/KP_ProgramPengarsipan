<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-normal text-gray-700">{{ __('Email') }}</label>
            <input id="email" class="block mt-1 w-full bg-white border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-gray-900 shadow-sm" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-normal text-gray-700">{{ __('Password') }}</label>
            <input id="password" class="block mt-1 w-full bg-white border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-gray-900 shadow-sm"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 cursor-pointer" name="remember">
                <span class="ms-2 text-xs text-gray-600 font-medium">{{ __('Remember me') }}</span>
            </label>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between pt-2">
            @if (Route::has('password.request'))
                <a class="text-xs text-gray-500 hover:text-gray-900 underline" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <button type="submit" class="bg-[#131432] hover:bg-slate-800 text-white text-xs font-bold tracking-wider py-2.5 px-6 rounded-lg transition-colors uppercase">
                {{ __('LOG IN') }}
            </button>
        </div>

    </form>
</x-guest-layout>
