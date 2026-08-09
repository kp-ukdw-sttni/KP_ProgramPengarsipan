<x-app-layout>
    <x-slot name="header">{{ __('Manajemen Pengguna') }}</x-slot>

    @if(session('success'))
        <div class="mb-5 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-5 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">{{ session('error') }}</div>
    @endif

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <form method="GET" action="{{ route('users.index') }}" class="flex flex-wrap gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / email / NIK..."
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 outline-none w-56">
            <select name="role" class="border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-indigo-400 outline-none">
                <option value="">Semua Role</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                @endforeach
            </select>
            <select name="status_akun" class="border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-indigo-400 outline-none">
                <option value="">Semua Status</option>
                <option value="Aktif" {{ request('status_akun') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="Suspended" {{ request('status_akun') == 'Suspended' ? 'selected' : '' }}>Suspended</option>
                <option value="Deactivated" {{ request('status_akun') == 'Deactivated' ? 'selected' : '' }}>Deactivated</option>
            </select>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">Filter</button>
        </form>
        <div class="flex items-center gap-2">
            {{-- Import CSV --}}
            <form method="POST" action="{{ route('users.import_csv') }}" enctype="multipart/form-data" class="flex items-center gap-2">
                @csrf
                <label class="text-xs font-semibold text-gray-600 cursor-pointer bg-white border border-gray-200 px-3 py-2 rounded-lg hover:bg-gray-50 transition-colors flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Import CSV
                    <input type="file" name="csv_file" accept=".csv,.txt" class="hidden" onchange="this.form.submit()">
                </label>
            </form>
            <a href="{{ route('users.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Tambah Pengguna
            </a>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead>
                    <tr class="text-gray-500 font-semibold bg-gray-50">
                        <th class="px-4 py-3 text-left">Nama / NIK-NIM</th>
                        <th class="px-4 py-3 text-left">Email</th>
                        <th class="px-4 py-3 text-left">Role</th>
                        <th class="px-4 py-3 text-left">Divisi</th>
                        <th class="px-4 py-3 text-center">Status Akun</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-900">{{ $user->name }}</div>
                                @if($user->nik_nim)
                                    <div class="text-xs text-gray-400 font-mono mt-0.5">{{ $user->nik_nim }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $user->email }}</td>
                            <td class="px-4 py-3">
                                @foreach($user->roles as $role)
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                                        {{ $role->name === 'Superadmin' ? 'bg-purple-100 text-purple-700' : ($role->name === 'Operator' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700') }}">
                                        {{ $role->name }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $user->divisi?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                                    {{ $user->status_akun === 'Aktif' ? 'bg-green-50 text-green-700 border border-green-200' : ($user->status_akun === 'Suspended' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-gray-100 text-gray-500') }}">
                                    {{ $user->status_akun }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('users.edit', $user) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 mr-3">Edit</a>
                                @if($user->id !== Auth::id())
                                    <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline"
                                        onsubmit="return confirm('Nonaktifkan akun pengguna ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-red-500 hover:text-red-700">Nonaktifkan</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-12 text-center text-sm text-gray-400">Tidak ada pengguna ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100">{{ $users->links() }}</div>
    </div>
</x-app-layout>
