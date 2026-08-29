<template>
  <AppLayout title="Data Pelanggan">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Data Pelanggan & Dompet Saldo</h1>
        <p class="text-sm text-slate-500 mt-0.5">Kelola data pelanggan, saldo deposit, dan riwayat transaksi.</p>
      </div>
      <button @click="openCreateModal" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-semibold text-sm shadow-md shadow-sky-500/25 transition-all">
        <Plus class="w-4 h-4" />
        <span>Tambah Pelanggan</span>
      </button>
    </div>

    <!-- Search Input -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm mb-6">
      <div class="relative">
        <input 
          v-model="search" 
          @input="applySearch"
          type="text" 
          placeholder="Cari nama pelanggan, nomor HP, atau alamat..." 
          class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-sky-500 focus:bg-white transition-colors"
        />
        <Search class="w-4 h-4 absolute left-3 top-3 text-slate-400" />
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
          <thead class="bg-slate-50 text-slate-400 uppercase text-[11px] font-bold tracking-wider">
            <tr>
              <th class="px-5 py-3.5">Nama Pelanggan</th>
              <th class="px-5 py-3.5">No. HP / WhatsApp</th>
              <th class="px-5 py-3.5">Alamat</th>
              <th class="px-5 py-3.5">Saldo Dompet Deposit</th>
              <th class="px-5 py-3.5">Total Order</th>
              <th class="px-5 py-3.5 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="c in customers.data" :key="c.id" class="hover:bg-slate-50/80 transition-colors">
              <td class="px-5 py-4 font-bold text-slate-900">{{ c.name }}</td>
              <td class="px-5 py-4">
                <a :href="`https://wa.me/${cleanPhone(c.phone)}`" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600 hover:underline">
                  <MessageCircle class="w-3.5 h-3.5" />
                  <span>{{ c.phone }}</span>
                </a>
              </td>
              <td class="px-5 py-4 text-slate-500">{{ c.address || '-' }}</td>
              <td class="px-5 py-4">
                <span class="font-extrabold text-emerald-600 text-sm">Rp {{ formatNumber(c.deposit_balance) }}</span>
              </td>
              <td class="px-5 py-4 font-semibold text-slate-700">{{ c.orders_count || 0 }} Kali</td>
              <td class="px-5 py-4 text-right space-x-2">
                <div class="flex justify-end items-center gap-1.5">
                  <button @click="openDepositModal(c)" class="inline-flex items-center gap-1 text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1.5 rounded-lg hover:bg-emerald-100 transition-colors">
                    <Wallet class="w-3.5 h-3.5" />
                    <span>Top-Up Saldo</span>
                  </button>
                  <button @click="openEditModal(c)" class="inline-flex items-center gap-1 text-xs font-bold text-sky-600 bg-sky-50 px-2.5 py-1.5 rounded-lg hover:bg-sky-100 transition-colors">
                    <Edit class="w-3.5 h-3.5" />
                    <span>Edit</span>
                  </button>
                  <button @click="deleteItem(c)" class="inline-flex items-center gap-1 text-xs font-bold text-rose-600 bg-rose-50 px-2.5 py-1.5 rounded-lg hover:bg-rose-100 transition-colors">
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Create / Edit Customer -->
    <div v-if="showCustomerModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
      <div class="bg-white rounded-2xl max-w-sm w-full p-5 shadow-2xl space-y-3 text-xs">
        <h3 class="font-bold text-slate-900 text-sm">{{ editingCustomer ? 'Edit Data Pelanggan' : 'Tambah Pelanggan Baru' }}</h3>
        <form @submit.prevent="submitCustomer" class="space-y-3">
          <div>
            <label class="font-semibold block mb-1">Nama Lengkap</label>
            <input v-model="customerForm.name" required class="w-full py-2 px-3 border rounded-xl" />
          </div>
          <div>
            <label class="font-semibold block mb-1">Nomor WhatsApp / HP</label>
            <input v-model="customerForm.phone" required placeholder="08xxx" class="w-full py-2 px-3 border rounded-xl" />
          </div>
          <div>
            <label class="font-semibold block mb-1">Alamat</label>
            <textarea v-model="customerForm.address" rows="2" class="w-full py-2 px-3 border rounded-xl"></textarea>
          </div>
          <div class="flex gap-2 pt-2">
            <button type="button" @click="showCustomerModal = false" class="flex-1 py-2 bg-slate-100 rounded-xl font-semibold">Batal</button>
            <button type="submit" class="flex-1 py-2 bg-sky-600 text-white rounded-xl font-bold">Simpan</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Top-Up Deposit Saldo -->
    <div v-if="showDepositModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
      <div class="bg-white rounded-2xl max-w-sm w-full p-5 shadow-2xl space-y-3 text-xs">
        <h3 class="font-bold text-slate-900 text-sm">Top-Up Saldo Deposit Pelanggan</h3>
        <p class="text-slate-500">Pelanggan: <strong>{{ activeCust?.name }}</strong> (Saldo saat ini: <strong>Rp {{ formatNumber(activeCust?.deposit_balance) }}</strong>)</p>
        <form @submit.prevent="submitDeposit" class="space-y-3">
          <div>
            <label class="font-semibold block mb-1">Nominal Top-Up (Rp)</label>
            <input v-model.number="depositForm.amount" type="number" required min="10000" step="5000" class="w-full py-2 px-3 border rounded-xl font-bold text-sm" />
          </div>
          <div class="grid grid-cols-3 gap-1">
            <button type="button" @click="depositForm.amount = 50000" class="py-1 bg-slate-100 rounded text-[11px] font-bold">50.000</button>
            <button type="button" @click="depositForm.amount = 100000" class="py-1 bg-slate-100 rounded text-[11px] font-bold">100.000</button>
            <button type="button" @click="depositForm.amount = 250000" class="py-1 bg-slate-100 rounded text-[11px] font-bold">250.000</button>
          </div>
          <div>
            <label class="font-semibold block mb-1">Metode Pembayaran</label>
            <select v-model="depositForm.payment_method" class="w-full py-2 px-3 border rounded-xl font-medium">
              <option value="cash">💵 Tunai</option>
              <option value="qris">📱 QRIS</option>
              <option value="transfer">🏦 Transfer</option>
            </select>
          </div>
          <div class="flex gap-2 pt-2">
            <button type="button" @click="showDepositModal = false" class="flex-1 py-2 bg-slate-100 rounded-xl font-semibold">Batal</button>
            <button type="submit" class="flex-1 py-2 bg-emerald-600 text-white rounded-xl font-bold">Top-Up Sekarang</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Custom Confirmation Modal -->
    <ConfirmModal 
      :show="showDeleteModal"
      title="Hapus Pelanggan"
      :message="`Apakah Anda yakin ingin menghapus pelanggan ${itemToDelete?.name}?\n\nAksi ini tidak dapat dibatalkan.`"
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
import { Plus, Search, MessageCircle, Wallet, Edit, Trash2 } from 'lucide-vue-next';

const props = defineProps({
  customers: Object,
  filters: Object,
});

const search = ref(props.filters.search || '');
const showCustomerModal = ref(false);
const showDepositModal = ref(false);
const showDeleteModal = ref(false);
const editingCustomer = ref(null);
const activeCust = ref(null);
const itemToDelete = ref(null);

const customerForm = useForm({
  name: '',
  phone: '',
  address: '',
});

const depositForm = useForm({
  amount: 50000,
  payment_method: 'cash',
});

function formatNumber(num) {
  return Number(num || 0).toLocaleString('id-ID');
}

function cleanPhone(phone) {
  if (!phone) return '';
  let clean = phone.replace(/[^0-9]/g, '');
  if (clean.startsWith('0')) clean = '62' + clean.substring(1);
  return clean;
}

function applySearch() {
  router.get('/customers', { search: search.value }, { preserveState: true, replace: true });
}

function openCreateModal() {
  editingCustomer.value = null;
  customerForm.reset();
  showCustomerModal.value = true;
}

function openEditModal(c) {
  editingCustomer.value = c;
  customerForm.name = c.name;
  customerForm.phone = c.phone;
  customerForm.address = c.address;
  showCustomerModal.value = true;
}

function submitCustomer() {
  if (editingCustomer.value) {
    customerForm.put(`/customers/${editingCustomer.value.id}`, {
      onSuccess: () => { showCustomerModal.value = false; }
    });
  } else {
    customerForm.post('/customers', {
      onSuccess: () => { showCustomerModal.value = false; }
    });
  }
}

function openDepositModal(c) {
  activeCust.value = c;
  depositForm.amount = 50000;
  showDepositModal.value = true;
}

function submitDeposit() {
  depositForm.post(`/customers/${activeCust.value.id}/deposit`, {
    onSuccess: () => { showDepositModal.value = false; }
  });
}

function deleteItem(c) {
  itemToDelete.value = c;
  showDeleteModal.value = true;
}

function executeDelete() {
  if (itemToDelete.value) {
    router.delete(`/customers/${itemToDelete.value.id}`);
    showDeleteModal.value = false;
    itemToDelete.value = null;
  }
}
</script>

