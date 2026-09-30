<script setup>
import { computed, ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "../../Layouts/AuthenticatedLayout.vue";
import FlashMessages from "../../Components/ui/FlashMessages.vue";
import Badge from "../../Components/ui/Badge.vue";
import Modal from "../../Components/ui/Modal.vue";
import Pagination from "../../Components/ui/Pagination.vue";
import InputError from "../../Components/ui/InputError.vue";
import PrimaryButton from "../../Components/ui/PrimaryButton.vue";
import SecondaryButton from "../../Components/ui/SecondaryButton.vue";
import DangerButton from "../../Components/ui/DangerButton.vue";
import PageHero from "../../Components/ui/PageHero.vue";
import { useRoute } from "../../Composables/useRoute";

const props = defineProps({
    peminjaman: {
        type: Object,
        required: true,
    },
    pendingCount: { type: Number, default: 0 },
    approvedCount: { type: Number, default: 0 },
    rejectedCount: { type: Number, default: 0 },
});

const routeFn = useRoute();

const items = computed(() => props.peminjaman.data ?? []);
const paginationLinks = computed(() => props.peminjaman.links ?? []);

const approveTarget = ref(null);
const rejectTarget = ref(null);
const revokeTarget = ref(null);

// The reason is mandatory server-side, so the form carries it rather than
// posting an empty payload (which previously always failed validation).
const approveForm = useForm({ notes: "" });
const rejectForm = useForm({ notes: "" });
const revokeForm = useForm({ notes: "" });

const statusBadge = (p) => {
    const map = {
        Pending: { label: "Menunggu", color: "amber" },
        Approved: { label: "Disetujui", color: "green" },
        Rejected: { label: "Ditolak", color: "red" },
        Expired: { label: "Kedaluwarsa", color: "gray" },
    };
    return (
        map[p.status_approval] ?? { label: p.status_approval, color: "gray" }
    );
};

const formatDate = (value) => {
    if (!value) return "-";
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return "-";
    return new Intl.DateTimeFormat("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    }).format(date);
};

const openApprove = (p) => {
    approveForm.reset();
    approveForm.clearErrors();
    approveTarget.value = p;
};

const submitApprove = () => {
    approveForm.post(routeFn("peminjaman.approve", approveTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            approveTarget.value = null;
        },
    });
};

const confirmReject = (p) => {
    rejectForm.reset();
    rejectForm.clearErrors();
    rejectTarget.value = p;
};

const submitReject = () => {
    rejectForm.post(routeFn("peminjaman.reject", rejectTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            rejectTarget.value = null;
        },
    });
};

const confirmRevoke = (p) => {
    revokeForm.reset();
    revokeForm.clearErrors();
    revokeTarget.value = p;
};

const submitRevoke = () => {
    revokeForm.post(routeFn("peminjaman.revoke", revokeTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            revokeTarget.value = null;
        },
    });
};

const statCards = computed(() => [
    { label: "Menunggu", value: props.pendingCount, bg: "bg-amber-500" },
    { label: "Disetujui", value: props.approvedCount, bg: "bg-green-600" },
    { label: "Ditolak", value: props.rejectedCount, bg: "bg-red-500" },
]);
</script>

<template>
    <AuthenticatedLayout title="Persetujuan Akses">
        <FlashMessages />

        <PageHero
            eyebrow="Akses Dokumen"
            title="Persetujuan Akses"
            subtitle="Kelola dan setujui pengajuan akses dokumen dari pemohon."
        />

        <div class="mt-5 mb-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div
                v-for="card in statCards"
                :key="card.label"
                :class="card.bg"
                class="flex items-center justify-between rounded-2xl p-5 text-white shadow-sm"
            >
                <span class="text-xs font-medium text-white/85">{{
                    card.label
                }}</span>
                <span class="text-2xl font-extrabold">{{ card.value }}</span>
            </div>
        </div>

        <div
            class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm"
        >
            <div v-if="items.length === 0" class="py-16 text-center">
                <p class="text-sm font-medium text-gray-500">
                    Tidak ada pengajuan.
                </p>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-200 bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500"
                        >
                            <th
                                scope="col"
                                class="px-5 py-3.5 text-left font-bold"
                            >
                                Pemohon
                            </th>
                            <th
                                scope="col"
                                class="px-5 py-3.5 text-left font-bold"
                            >
                                Dokumen
                            </th>
                            <th
                                scope="col"
                                class="px-5 py-3.5 text-left font-bold"
                            >
                                Status
                            </th>
                            <th
                                scope="col"
                                class="px-5 py-3.5 text-left font-bold"
                            >
                                Waktu Pengajuan
                            </th>
                            <th
                                scope="col"
                                class="px-5 py-3.5 text-right font-bold"
                            >
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        <tr
                            v-for="p in items"
                            :key="p.id"
                            class="transition-colors duration-150 hover:bg-indigo-50/40"
                        >
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">
                                    {{ p.user?.name ?? 'Pengguna Tidak Ditemukan' }}
                                </div>
                                <div class="mt-0.5 text-[11px] text-gray-400">
                                    {{ p.user?.email ?? '-' }}
                                </div>
                            </td>
                            <td class="max-w-xs px-5 py-4">
                                <div
                                    class="leading-tight font-semibold text-gray-800"
                                >
                                    {{ p.arsip?.judul ?? 'Dokumen Tidak Ditemukan' }}
                                </div>
                                <div class="mt-1 text-[11px] text-gray-400">
                                    <code>{{ p.arsip?.nomor_arsip ?? '-' }}</code>
                                    &bull; {{ p.arsip?.kategori?.name ?? 'Tanpa Kategori' }} &bull;
                                    {{ p.arsip?.divisi?.name ?? (p.arsip?.study_program?.name ?? 'Tanpa Unit') }}
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <Badge :color="statusBadge(p).color">
                                    {{ statusBadge(p).label }}
                                </Badge>
                            </td>
                            <td
                                class="whitespace-nowrap px-5 py-4 font-mono text-xs text-gray-500"
                            >
                                {{ formatDate(p.created_at) }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <div
                                    v-if="p.status_approval === 'Pending'"
                                    class="flex items-center justify-end gap-1.5"
                                >
                                    <button
                                        type="button"
                                        class="rounded-md px-2 py-1 text-xs font-semibold text-green-700 hover:bg-green-50"
                                        @click="openApprove(p)"
                                    >
                                        Setujui
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-md px-2 py-1 text-xs font-semibold text-red-600 hover:bg-red-50"
                                        @click="confirmReject(p)"
                                    >
                                        Tolak
                                    </button>
                                </div>
                                <span
                                    v-else-if="
                                        p.status_approval === 'Approved' &&
                                        p.approver
                                    "
                                    class="inline-flex items-center gap-2 text-[11px] text-gray-400"
                                >
                                    oleh {{ p.approver?.name ?? 'Admin' }}
                                    <button
                                        type="button"
                                        class="rounded-md px-2 py-1 text-[11px] font-semibold text-red-600 hover:bg-red-50"
                                        @click="confirmRevoke(p)"
                                    >
                                        Cabut
                                    </button>
                                </span>
                                <span
                                    v-else-if="p.status_approval === 'Approved'"
                                    class="inline-flex items-center gap-2 text-[11px] text-gray-400"
                                >
                                    <button
                                        type="button"
                                        class="rounded-md px-2 py-1 text-[11px] font-semibold text-red-600 hover:bg-red-50"
                                        @click="confirmRevoke(p)"
                                    >
                                        Cabut
                                    </button>
                                </span>
                                <span
                                    v-else-if="p.status_approval === 'Rejected' && p.notes"
                                    class="block max-w-[14rem] text-[11px] text-gray-400"
                                    :title="p.notes"
                                >
                                    {{ p.notes }}
                                </span>
                                <span v-else class="text-xs text-gray-300"
                                    >-</span
                                >
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                v-if="paginationLinks.length > 3"
                class="border-t border-gray-100 px-4 py-3"
            >
                <Pagination :links="paginationLinks" />
            </div>
        </div>

        <Modal
            :show="!!approveTarget"
            max-width="sm"
            @close="approveTarget = null"
        >
            <form v-if="approveTarget" @submit.prevent="submitApprove">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-sm font-bold text-gray-900">
                        Setujui Akses Dokumen
                    </h3>
                    <p class="mt-0.5 text-xs text-gray-500">
                        {{ approveTarget.arsip?.judul }}
                    </p>
                </div>
                <div class="px-6 py-5">
                    <p
                        class="rounded-lg bg-green-50 px-3 py-2.5 text-xs text-green-800"
                    >
                        Setelah disetujui,
                        <span class="font-semibold">{{
                            approveTarget.user?.name
                        }}</span>
                        dapat membuka dan mengunduh dokumen ini tanpa batas
                        waktu. Anda tetap dapat mencabut izinnya kapan saja.
                    </p>
                    <label
                        class="mb-1 mt-4 block text-[11px] font-semibold text-gray-500"
                    >
                        Catatan (opsional)
                    </label>
                    <textarea
                        v-model="approveForm.notes"
                        rows="2"
                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Contoh:ctoasan rapat komite, 12 Sep 2026"
                    />
                    <InputError :message="approveForm.errors.notes" />
                </div>
                <div
                    class="flex items-center justify-end gap-2 border-t border-gray-100 px-6 py-4"
                >
                    <SecondaryButton type="button" @click="approveTarget = null"
                        >Batal</SecondaryButton
                    >
                    <PrimaryButton :disabled="approveForm.processing">
                        {{
                            approveForm.processing ? "Menyetujui..." : "Setujui"
                        }}
                    </PrimaryButton>
                </div>
            </form>
        </Modal>

        <Modal
            :show="!!rejectTarget"
            max-width="sm"
            @close="rejectTarget = null"
        >
            <form v-if="rejectTarget" @submit.prevent="submitReject">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-sm font-bold text-gray-900">
                        Tolak pengajuan?
                    </h3>
                    <p class="mt-0.5 text-xs text-gray-500">
                        Pengajuan
                        <span class="font-semibold">{{
                            rejectTarget.user?.name
                        }}</span>
                        untuk
                        <span class="font-semibold">{{
                            rejectTarget.arsip?.judul
                        }}</span>
                        akan ditolak.
                    </p>
                </div>
                <div class="px-6 py-5">
                    <label
                        class="mb-1 block text-[11px] font-semibold text-gray-500"
                    >
                        Alasan penolakan <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        v-model="rejectForm.notes"
                        rows="3"
                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500"
                        placeholder="Contoh: dokumen memuat data pribadi mahasiswa"
                    />
                    <InputError :message="rejectForm.errors.notes" />
                </div>
                <div
                    class="flex items-center justify-end gap-2 border-t border-gray-100 px-6 py-4"
                >
                    <SecondaryButton type="button" @click="rejectTarget = null"
                        >Batal</SecondaryButton
                    >
                    <DangerButton
                        type="submit"
                        :disabled="rejectForm.processing"
                    >
                        {{
                            rejectForm.processing ? "Menolak..." : "Ya, Tolak"
                        }}
                    </DangerButton>
                </div>
            </form>
        </Modal>

        <Modal
            :show="!!revokeTarget"
            max-width="sm"
            @close="revokeTarget = null"
        >
            <form v-if="revokeTarget" @submit.prevent="submitRevoke">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-sm font-bold text-gray-900">
                        Cabut izin akses?
                    </h3>
                    <p class="mt-0.5 text-xs text-gray-500">
                        <span class="font-semibold">{{
                            revokeTarget.user?.name
                        }}</span>
                        akan langsung kehilangan akses ke
                        <span class="font-semibold">{{
                            revokeTarget.arsip?.judul
                        }}</span>.
                    </p>
                </div>
                <div class="px-6 py-5">
                    <label
                        class="mb-1 block text-[11px] font-semibold text-gray-500"
                    >
                        Alasan pencabutan <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        v-model="revokeForm.notes"
                        rows="3"
                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500"
                        placeholder="Contoh: alasan peminjaman sudah selesai"
                    />
                    <InputError :message="revokeForm.errors.notes" />
                </div>
                <div
                    class="flex items-center justify-end gap-2 border-t border-gray-100 px-6 py-4"
                >
                    <SecondaryButton type="button" @click="revokeTarget = null"
                        >Batal</SecondaryButton
                    >
                    <DangerButton type="submit" :disabled="revokeForm.processing">
                        {{
                            revokeForm.processing ? "Mencabut..." : "Ya, Cabut"
                        }}
                    </DangerButton>
                </div>
            </form>
        </Modal>
    </AuthenticatedLayout>
</template>
