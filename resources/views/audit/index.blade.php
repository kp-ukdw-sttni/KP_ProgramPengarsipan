<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Jejak Audit Sistem (Audit Trail)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Filters Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 border border-gray-200">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Filter Log Aktivitas') }}</h3>
                    
                    <form action="{{ route('audit.index') }}" method="GET">
                        <!-- Grid Layout for Filter Form -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- Aktor / Pengguna -->
                            <div>
                                <x-input-label for="user_id" :value="__('Aktor / Pengguna')" />
                                <select id="user_id" name="user_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full bg-white text-gray-900">
                                    <option value="">{{ __('Semua Pengguna') }}</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Tipe Tindakan -->
                            <div>
                                <x-input-label for="action" :value="__('Tipe Tindakan')" />
                                <select id="action" name="action" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full bg-white text-gray-900">
                                    <option value="">{{ __('Semua Tindakan') }}</option>
                                    <option value="Login" {{ request('action') === 'Login' ? 'selected' : '' }}>Login (Masuk)</option>
                                    <option value="Logout" {{ request('action') === 'Logout' ? 'selected' : '' }}>Logout (Keluar)</option>
                                    <option value="Create" {{ request('action') === 'Create' ? 'selected' : '' }}>Create (Tambah)</option>
                                    <option value="Update" {{ request('action') === 'Update' ? 'selected' : '' }}>Update (Edit)</option>
                                    <option value="Delete" {{ request('action') === 'Delete' ? 'selected' : '' }}>Delete (Hapus)</option>
                                    <option value="View" {{ request('action') === 'View' ? 'selected' : '' }}>View (Melihat)</option>
                                    <option value="Download" {{ request('action') === 'Download' ? 'selected' : '' }}>Download (Mengunduh)</option>
                                    <option value="Request Access" {{ request('action') === 'Request Access' ? 'selected' : '' }}>Request Access (Minta Akses)</option>
                                    <option value="Approve Access" {{ request('action') === 'Approve Access' ? 'selected' : '' }}>Approve Access (Setujui)</option>
                                    <option value="Reject Access" {{ request('action') === 'Reject Access' ? 'selected' : '' }}>Reject Access (Tolak)</option>
                                    <option value="System Expired" {{ request('action') === 'System Expired' ? 'selected' : '' }}>System Expired (JRA Otomatis)</option>
                                </select>
                            </div>

                            <!-- Tanggal Mulai -->
                            <div class="mt-4">
                                <x-input-label for="start_date" :value="__('Tanggal Mulai')" />
                                <x-text-input id="start_date" class="block mt-1 w-full" type="date" name="start_date" :value="request('start_date')" />
                            </div>

                            <!-- Tanggal Selesai -->
                            <div class="mt-4">
                                <x-input-label for="end_date" :value="__('Tanggal Selesai')" />
                                <x-text-input id="end_date" class="block mt-1 w-full" type="date" name="end_date" :value="request('end_date')" />
                            </div>
                        </div>

                        <div class="flex items-center gap-4 mt-6">
                            <x-primary-button type="submit">
                                {{ __('Filter Log') }}
                            </x-primary-button>
                            
                            <a href="{{ route('audit.index') }}">
                                <x-secondary-button type="button">
                                    {{ __('Reset') }}
                                </x-secondary-button>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Log Aktivitas Sistem') }}</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Waktu') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Aktor') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Tindakan') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Arsip Terkait') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('IP Address') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Keterangan') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($logs as $log)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                                            {{ $log->created_at->format('d/m/Y H:i:s') }}
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($log->user)
                                                <div class="text-sm font-semibold text-gray-900">{{ $log->user->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $log->user->email }}</div>
                                            @else
                                                <span class="text-xs text-gray-400 font-semibold italic">{{ __('SISTEM CRON') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if(in_array($log->action, ['Create', 'Approve Access']))
                                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    {{ $log->action }}
                                                </span>
                                            @elseif(in_array($log->action, ['Delete', 'Reject Access']))
                                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    {{ $log->action }}
                                                </span>
                                            @elseif(in_array($log->action, ['Update', 'Request Access']))
                                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-amber-100 text-amber-800">
                                                    {{ $log->action }}
                                                </span>
                                            @elseif($log->action === 'System Expired')
                                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    {{ $log->action }}
                                                </span>
                                            @else
                                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    {{ $log->action }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($log->arsip)
                                                <div class="text-sm font-medium text-gray-900">{{ $log->arsip->judul }}</div>
                                                <div class="text-xs text-gray-400"><code>{{ $log->arsip->nomor_arsip }}</code></div>
                                            @else
                                                <span class="text-xs text-gray-400 italic">{{ __('Dokumen Dihapus / Tidak Ada') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-500">
                                            {{ $log->ip_address ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-xs text-gray-500">
                                            {{ $log->details }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">
                                            {{ __('Tidak ditemukan catatan log aktivitas.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
