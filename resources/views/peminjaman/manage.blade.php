<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Persetujuan Peminjaman') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-6">{{ __('Daftar Pengajuan Akses Dokumen Karyawan') }}</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Peminta (Karyawan)') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Dokumen Arsip') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Tgl Pengajuan') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Detail Akses / Catatan') }}</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Tindakan') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($peminjaman as $p)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-semibold text-gray-900">{{ $p->user->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $p->user->email }}</div>
                                            <div class="text-xs text-gray-400 mt-0.5">{{ __('Divisi:') }} {{ $p->user->divisi->name ?? '-' }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $p->arsip->judul }}</div>
                                            <div class="text-xs text-gray-400"><code>{{ $p->arsip->nomor_arsip }}</code></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $p->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($p->status_approval === 'Pending')
                                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-amber-100 text-amber-800">
                                                    {{ __('Pending') }}
                                                </span>
                                            @elseif($p->status_approval === 'Approved')
                                                @if($p->expired_at && $p->expired_at->isPast())
                                                    <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        {{ __('Expired') }}
                                                    </span>
                                                @else
                                                    <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        {{ __('Approved') }}
                                                    </span>
                                                @endif
                                            @elseif($p->status_approval === 'Rejected')
                                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    {{ __('Rejected') }}
                                                </span>
                                            @else
                                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    {{ $p->status_approval }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-xs text-gray-500">
                                            @if($p->status_approval === 'Approved')
                                                <div class="text-gray-500">{{ __('Mulai:') }} {{ $p->borrowed_at->format('d/m H:i') }}</div>
                                                <div class="text-amber-600 font-semibold">{{ __('Habis:') }} {{ $p->expired_at->format('d/m H:i') }}</div>
                                            @elseif($p->status_approval === 'Rejected')
                                                <span class="text-red-600 font-medium">{{ __('Alasan:') }} {{ $p->notes }}</span>
                                            @else
                                                <span class="text-gray-400 italic">{{ __('Menunggu keputusan') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            @if($p->status_approval === 'Pending')
                                                <div class="flex flex-col items-end gap-2">
                                                    <div class="flex gap-2">
                                                        <x-primary-button type="button" onclick="toggleActionForm('approve-{{ $p->id }}')" class="text-xs px-2.5 py-1.5 bg-indigo-600 hover:bg-indigo-700">
                                                            {{ __('Setujui') }}
                                                        </x-primary-button>
                                                        
                                                        <x-danger-button type="button" onclick="toggleActionForm('reject-{{ $p->id }}')" class="text-xs px-2.5 py-1.5">
                                                            {{ __('Tolak') }}
                                                        </x-danger-button>
                                                    </div>

                                                    <!-- Inline Approve Form -->
                                                    <div id="approve-{{ $p->id }}" class="action-form hidden mt-2 p-4 bg-gray-50 border rounded-lg shadow-inner text-left w-64">
                                                        <form action="{{ route('peminjaman.approve', $p->id) }}" method="POST">
                                                            @csrf
                                                            <div class="mb-3">
                                                                <x-input-label for="duration-{{ $p->id }}" :value="__('Durasi Akses (Jam)')" class="text-xs" />
                                                                <x-text-input id="duration-{{ $p->id }}" class="block mt-1 w-full text-xs p-1.5" type="number" name="duration" value="24" min="1" max="168" required />
                                                            </div>
                                                            <div class="mb-3">
                                                                <x-input-label for="notes-{{ $p->id }}" :value="__('Catatan (Opsional)')" class="text-xs" />
                                                                <x-text-input id="notes-{{ $p->id }}" class="block mt-1 w-full text-xs p-1.5" type="text" name="notes" placeholder="Catatan persetujuan..." />
                                                            </div>
                                                            <div class="flex justify-end gap-2">
                                                                <x-secondary-button type="button" onclick="toggleActionForm('approve-{{ $p->id }}')" class="text-xs px-2 py-1">
                                                                    {{ __('Batal') }}
                                                                </x-secondary-button>
                                                                <x-primary-button type="submit" class="text-xs px-2.5 py-1">
                                                                    {{ __('Kirim') }}
                                                                </x-primary-button>
                                                            </div>
                                                        </form>
                                                    </div>

                                                    <!-- Inline Reject Form -->
                                                    <div id="reject-{{ $p->id }}" class="action-form hidden mt-2 p-4 bg-gray-50 border rounded-lg shadow-inner text-left w-64">
                                                        <form action="{{ route('peminjaman.reject', $p->id) }}" method="POST">
                                                            @csrf
                                                            <div class="mb-3">
                                                                <x-input-label for="reject-notes-{{ $p->id }}" :value="__('Alasan Penolakan')" class="text-xs" />
                                                                <x-text-input id="reject-notes-{{ $p->id }}" class="block mt-1 w-full text-xs p-1.5" type="text" name="notes" placeholder="Tuliskan alasannya..." required />
                                                            </div>
                                                            <div class="flex justify-end gap-2">
                                                                <x-secondary-button type="button" onclick="toggleActionForm('reject-{{ $p->id }}')" class="text-xs px-2 py-1">
                                                                    {{ __('Batal') }}
                                                                </x-secondary-button>
                                                                <x-danger-button type="submit" class="text-xs px-2.5 py-1">
                                                                    {{ __('Tolak Akses') }}
                                                                </x-danger-button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-xs text-gray-400 italic">{{ __('Selesai diproses') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">
                                            {{ __('Tidak ada permohonan peminjaman dokumen arsip saat ini.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $peminjaman->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleActionForm(id) {
            // Close other open action forms
            const forms = document.querySelectorAll('.action-form');
            forms.forEach(form => {
                if (form.id !== id) {
                    form.classList.add('hidden');
                }
            });

            // Toggle target form class
            const target = document.getElementById(id);
            if (target.classList.contains('hidden')) {
                target.classList.remove('hidden');
            } else {
                target.classList.add('hidden');
            }
        }
    </script>
</x-app-layout>
