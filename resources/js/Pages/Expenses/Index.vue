<template>
  <AppLayout title="Pengeluaran & Kas Operasional">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Pengeluaran & Petty Cash</h1>
        <p class="text-sm text-slate-500 mt-0.5">Pencatatan beban operasional laundry (sabun, token listrik, plastik, transport, dll).</p>
      </div>
      <button @click="showModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-rose-500 hover:bg-rose-600 text-white font-semibold text-sm shadow-md shadow-rose-500/25 transition-all">
        <Plus class="w-4 h-4" />
        <span>Catat Pengeluaran</span>
      </button>
    </div>

    <!-- Monthly Summary Banner -->
    <div class="p-5 rounded-2xl bg-rose-50/70 border border-rose-100 mb-6 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-xl bg-rose-500 text-white flex items-center justify-center shadow-md shadow-rose-500/20">
          <WalletCards class="w-6 h-6" />
        </div>
        <div>
          <span class="text-xs font-semibold text-rose-800 uppercase tracking-wider">Total Beban Operasional Bulan Ini</span>
          <h2 class="text-2xl font-bold text-rose-950">Rp {{ formatNumber(totalThisMonth) }}</h2>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
          <thead class="bg-slate-50 text-slate-400 uppercase text-[11px] font-bold tracking-wider">
            <tr>
              <th class="px-5 py-3.5">Tanggal</th>
              <th class="px-5 py-3.5">Kategori</th>
              <th class="px-5 py-3.5">Judul Pengeluaran</th>
              <th class="px-5 py-3.5">Dicatat Oleh</th>
              <th class="px-5 py-3.5 text-right">Nominal Beban</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="exp in expenses.data" :key="exp.id" class="hover:bg-slate-50/80 transition-colors">
              <td class="px-5 py-4 font-semibold text-slate-900">{{ exp.expense_date }}</td>
              <td class="px-5 py-4">
                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-xs font-bold capitalize">
                  {{ exp.category }}
                </span>
              </td>
              <td class="px-5 py-4">
                <p class="font-bold text-slate-900">{{ exp.title }}</p>
                <p class="text-xs text-slate-400">{{ exp.description || '-' }}</p>
              </td>
              <td class="px-5 py-4 text-xs font-medium text-slate-600">{{ exp.user?.name || 'Staf' }}</td>
              <td class="px-5 py-4 text-right">
                <div class="flex justify-end items-center gap-2">
                  <span class="font-bold text-rose-600">
                    - Rp {{ formatNumber(exp.amount) }}
                  </span>
                  <button @click="deleteItem(exp)" class="inline-flex items-center gap-1 text-xs font-bold text-rose-600 bg-rose-50 px-2 py-1.5 rounded-lg hover:bg-rose-100 transition-colors ml-2">
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="expenses.data.length === 0">
              <td colspan="5" class="px-5 py-8 text-center text-slate-400">Belum ada catatan pengeluaran kas.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Form -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
      <div class="bg-white rounded-2xl max-w-sm w-full p-5 shadow-2xl space-y-3 text-xs">
        <h3 class="font-bold text-slate-900 text-sm">Catat Pengeluaran Baru</h3>
        <form @submit.prevent="submitExpense" class="space-y-3">
          <div>
            <label class="font-semibold block mb-1">Kategori Pengeluaran</label>
            <select v-model="form.category" class="w-full py-2 px-3 border rounded-xl">
              <option value="operational">Operasional (Deterjen, Parfum, Plastik)</option>
              <option value="utility">Utilitas (Listrik, Air, Gas, WiFi)</option>
              <option value="maintenance">Perawatan Mesin / Servis</option>
              <option value="salary">Gaji / Insentif Karyawan</option>
              <option value="other">Lain-lain</option>
            </select>
          </div>
          <div>
            <label class="font-semibold block mb-1">Judul / Keperluan</label>
            <input v-model="form.title" required placeholder="Contoh: Beli 2 Jerigen Deterjen" class="w-full py-2 px-3 border rounded-xl font-bold" />
          </div>
          <div>
            <label class="font-semibold block mb-1">Nominal Beban (Rp)</label>
            <input v-model.number="form.amount" type="number" required min="1" class="w-full py-2 px-3 border rounded-xl font-bold text-sm" />
          </div>
          <div>
            <label class="font-semibold block mb-1">Tanggal Pengeluaran</label>
            <input v-model="form.expense_date" type="date" required class="w-full py-2 px-3 border rounded-xl" />
          </div>
          <div>
            <label class="font-semibold block mb-1">Catatan Tambahan</label>
            <textarea v-model="form.description" rows="2" class="w-full py-2 px-3 border rounded-xl"></textarea>
          </div>
          <div class="flex gap-2 pt-2">
            <button type="button" @click="showModal = false" class="flex-1 py-2 bg-slate-100 rounded-xl font-semibold">Batal</button>
            <button type="submit" class="flex-1 py-2 bg-rose-600 text-white rounded-xl font-bold">Simpan Pengeluaran</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Custom Confirmation Modal -->
    <ConfirmModal 
      :show="showDeleteModal"
      title="Hapus Catatan Pengeluaran"
      :message="`Apakah Anda yakin ingin menghapus catatan pengeluaran '${itemToDelete?.title}' sebesar Rp ${formatNumber(itemToDelete?.amount)}?\n\nAksi ini tidak dapat dibatalkan.`"
      confirmText="Ya, Hapus"
      type="danger"
      @confirm="executeDelete"
      @cancel="showDeleteModal = false"
    />
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { Plus, WalletCards, Trash2 } from 'lucide-vue-next';

const props = defineProps({
  expenses: Object,
  totalThisMonth: Number,
});

const showModal = ref(false);
const showDeleteModal = ref(false);
const itemToDelete = ref(null);

const form = useForm({
  category: 'operational',
  title: '',
  amount: 0,
  expense_date: new Date().toISOString().substr(0, 10),
  description: '',
});

function formatNumber(num) {
  return Number(num || 0).toLocaleString('id-ID');
}

function submitExpense() {
  form.post('/expenses', {
    onSuccess: () => {
      showModal.value = false;
      form.reset();
    }
  });
}

function deleteItem(exp) {
  itemToDelete.value = exp;
  showDeleteModal.value = true;
}

function executeDelete() {
  if (itemToDelete.value) {
    router.delete(`/expenses/${itemToDelete.value.id}`);
    showDeleteModal.value = false;
    itemToDelete.value = null;
  }
}
</script>

