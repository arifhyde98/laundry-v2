<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    tickets: Array,
    recentOrders: Array
});

const showNewModal = ref(false);
const showActionModal = ref(false);
const activeTicket = ref(null);

const form = useForm({
    order_id: '',
    reason: ''
});

const actionForm = useForm({
    status: '',
    resolution_note: ''
});

function createTicket() {
    form.post('/rewash', {
        onSuccess: () => {
            showNewModal.value = false;
            form.reset();
        }
    });
}

function openActionModal(ticket) {
    activeTicket.value = ticket;
    actionForm.status = ticket.status;
    actionForm.resolution_note = ticket.resolution_note || '';
    showActionModal.value = true;
}

function saveAction() {
    actionForm.put(`/rewash/${activeTicket.value.id}`, {
        onSuccess: () => {
            showActionModal.value = false;
            actionForm.reset();
        }
    });
}
</script>

<template>
    <AppLayout>
        <Head title="Garansi Cuci Ulang" />
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Garansi Cuci Ulang (Rewash)</h1>
                    <p class="text-slate-500">Pencatatan komplain dan cuci ulang bebas biaya.</p>
                </div>
                <button @click="showNewModal = true" class="px-4 py-2 bg-emerald-600 text-white rounded-xl font-bold">
                    + Buat Tiket Komplain
                </button>
            </div>

            <div class="bg-white rounded-xl border overflow-hidden shadow-sm">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 border-b">
                        <tr>
                            <th class="px-5 py-3">Tiket</th>
                            <th class="px-5 py-3">Order Invoice</th>
                            <th class="px-5 py-3">Alasan Komplain</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="t in tickets" :key="t.id" class="border-b hover:bg-slate-50">
                            <td class="px-5 py-3 font-medium text-blue-600">{{ t.ticket_code }}</td>
                            <td class="px-5 py-3">
                                <div>{{ t.order?.invoice_code }}</div>
                                <div class="text-xs text-slate-500">{{ t.order?.customer?.name }}</div>
                            </td>
                            <td class="px-5 py-3">{{ t.reason }}</td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-1 rounded text-xs font-bold"
                                      :class="{
                                          'bg-yellow-100 text-yellow-800': t.status === 'pending',
                                          'bg-blue-100 text-blue-800': t.status === 'processing',
                                          'bg-green-100 text-green-800': t.status === 'resolved',
                                          'bg-red-100 text-red-800': t.status === 'rejected'
                                      }">
                                    {{ t.status.toUpperCase() }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <button v-if="t.status !== 'resolved' && t.status !== 'rejected'" @click="openActionModal(t)" class="text-indigo-600 font-bold hover:underline">Tindak Lanjuti</button>
                                <span v-else class="text-slate-400 text-xs">Selesai</span>
                            </td>
                        </tr>
                        <tr v-if="!tickets.length">
                            <td colspan="5" class="text-center py-6 text-slate-500">Belum ada data komplain cuci ulang.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="showNewModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60">
            <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md">
                <h3 class="font-bold mb-4">Buat Tiket Cuci Ulang Baru</h3>
                <form @submit.prevent="createTicket" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Pilih Order (Invoice)</label>
                        <select v-model="form.order_id" required class="w-full border rounded-lg p-2">
                            <option value="">Pilih Order</option>
                            <option v-for="o in recentOrders" :key="o.id" :value="o.id">{{ o.invoice_code }} - {{ o.customer?.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Alasan Komplain</label>
                        <textarea v-model="form.reason" required rows="3" class="w-full border rounded-lg p-2" placeholder="Cucian masih bau apek, dll..."></textarea>
                    </div>
                    <div class="flex gap-2 justify-end mt-4">
                        <button type="button" @click="showNewModal = false" class="px-4 py-2 bg-slate-100 rounded-lg">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg font-bold">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <div v-if="showActionModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60">
            <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md">
                <h3 class="font-bold mb-4">Tindak Lanjut Komplain {{ activeTicket?.ticket_code }}</h3>
                <form @submit.prevent="saveAction" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Ubah Status</label>
                        <select v-model="actionForm.status" required class="w-full border rounded-lg p-2">
                            <option value="pending">Pending</option>
                            <option value="processing">Sedang Diproses Ulang</option>
                            <option value="resolved">Selesai (Resolved)</option>
                            <option value="rejected">Ditolak (Rejected)</option>
                        </select>
                    </div>
                    <div v-if="['resolved', 'rejected'].includes(actionForm.status)">
                        <label class="block text-sm font-medium mb-1">Catatan Resolusi (Opsional)</label>
                        <textarea v-model="actionForm.resolution_note" rows="2" class="w-full border rounded-lg p-2" placeholder="Telah dicuci ulang gratis..."></textarea>
                    </div>
                    <div class="flex gap-2 justify-end mt-4">
                        <button type="button" @click="showActionModal = false" class="px-4 py-2 bg-slate-100 rounded-lg">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-bold">Simpan Status</button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

