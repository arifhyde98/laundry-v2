<template>
  <AppLayout title="Data Transaksi">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Riwayat & Daftar Transaksi</h1>
        <p class="text-sm text-slate-500 mt-0.5">Kelola seluruh transaksi laundry, status pencucian, dan pelunasan.</p>
      </div>
      <Link :href="'/pos'" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-semibold text-sm shadow-md shadow-sky-500/25 transition-all">
        <Plus class="w-4 h-4" />
        <span>Transaksi Baru</span>
      </Link>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm mb-6 flex flex-col md:flex-row gap-3">
      <!-- Search Input -->
      <div class="flex-1 relative">
        <input 
          v-model="search" 
          @input="applyFilters"
          type="text" 
          placeholder="Cari no. invoice, nama pelanggan, atau HP..." 
          class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-sky-500 focus:bg-white transition-colors"
        />
        <Search class="w-4 h-4 absolute left-3 top-3 text-slate-400" />
      </div>

      <!-- Status Pengerjaan Filter -->
      <select 
        v-model="status" 
        @change="applyFilters"
        class="py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-sky-500 font-medium text-slate-700"
      >
        <option value="">Semua Status Pengerjaan</option>
        <option value="received">Diterima Kasir</option>
        <option value="washing">Sedang Dicuci</option>
        <option value="drying">Pengeringan</option>
        <option value="ironing">Penyetrikaan</option>
        <option value="packing">Dipacking</option>
        <option value="ready">Siap Diambil (di Rak)</option>
        <option value="completed">Selesai / Diambil</option>
        <option value="cancelled">Dibatalkan</option>
      </select>

      <!-- Payment Status Filter -->
      <select 
        v-model="paymentStatus" 
        @change="applyFilters"
        class="py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-sky-500 font-medium text-slate-700"
      >
        <option value="">Semua Status Bayar</option>
        <option value="paid">Lunas</option>
        <option value="partial">Sebagian (DP)</option>
        <option value="unpaid">Belum Lunas</option>
      </select>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
          <thead class="bg-slate-50 text-slate-400 uppercase text-[11px] font-bold tracking-wider">
            <tr>
              <th class="px-5 py-3.5">Invoice & Tgl</th>
              <th class="px-5 py-3.5">Pelanggan</th>
              <th class="px-5 py-3.5">Berat / Qty</th>
              <th class="px-5 py-3.5">Total & Pembayaran</th>
              <th class="px-5 py-3.5">Status Pengerjaan</th>
              <th class="px-5 py-3.5">Lokasi Rak</th>
              <th class="px-5 py-3.5 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="order in orders.data" :key="order.id" class="hover:bg-slate-50/80 transition-colors">
              <td class="px-5 py-4">
                <div class="font-bold text-slate-900">{{ order.invoice_code }}</div>
                <div class="text-xs text-slate-400">{{ order.order_date }}</div>
              </td>
              <td class="px-5 py-4">
                <div class="font-medium text-slate-900">{{ order.customer?.name || '-' }}</div>
                <div class="text-xs text-slate-400">{{ order.customer?.phone || '-' }}</div>
              </td>
              <td class="px-5 py-4 font-semibold text-slate-900">
                {{ order.total_weight_qty }} Kg/Pcs
              </td>
              <td class="px-5 py-4">
                <div class="font-bold text-slate-900">Rp {{ formatNumber(order.grand_total) }}</div>
                <span :class="['inline-block mt-0.5 px-2 py-0.5 rounded text-[11px] font-bold uppercase', getPaymentBadge(order.payment_status)]">
                  {{ order.payment_status }}
                </span>
              </td>
              <td class="px-5 py-4">
                <span :class="['px-2.5 py-1 rounded-full text-xs font-semibold', getOrderStatusBadge(order.order_status)]">
                  {{ formatOrderStatus(order.order_status) }}
                </span>
              </td>
              <td class="px-5 py-4 font-semibold text-sky-700">
                {{ order.rack ? order.rack.rack_code : '-' }}
              </td>
              <td class="px-5 py-4 text-right">
                <Link :href="`/orders/${order.id}`" class="inline-flex items-center gap-1 text-xs font-bold text-sky-600 hover:text-sky-800 bg-sky-50 px-3 py-1.5 rounded-lg transition-colors">
                  <span>Kelola</span>
                  <ArrowRight class="w-3.5 h-3.5" />
                </Link>
              </td>
            </tr>
            <tr v-if="orders.data.length === 0">
              <td colspan="7" class="px-5 py-12 text-center text-slate-400 text-sm">
                Tidak ada data transaksi yang sesuai filter.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="orders.links && orders.links.length > 3" class="p-4 border-t border-slate-100 flex items-center justify-between text-xs">
        <span class="text-slate-500">Menampilkan {{ orders.from || 0 }} - {{ orders.to || 0 }} dari total {{ orders.total }} transaksi</span>
        <div class="flex gap-1">
          <Link 
            v-for="(link, idx) in orders.links" 
            :key="idx" 
            :href="link.url || '#'" 
            :class="['px-3 py-1.5 rounded-lg border font-semibold transition-colors', link.active ? 'bg-sky-600 text-white border-sky-600' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50', !link.url ? 'opacity-40 pointer-events-none' : '']"
            v-html="link.label"
          />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Plus, Search, ArrowRight } from 'lucide-vue-next';

const props = defineProps({
  orders: Object,
  filters: Object,
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const paymentStatus = ref(props.filters.payment_status || '');

function formatNumber(num) {
  return Number(num || 0).toLocaleString('id-ID');
}

function applyFilters() {
  router.get('/orders', {
    search: search.value,
    status: status.value,
    payment_status: paymentStatus.value,
  }, {
    preserveState: true,
    replace: true,
  });
}

function getPaymentBadge(status) {
  if (status === 'paid') return 'bg-emerald-100 text-emerald-800';
  if (status === 'partial') return 'bg-amber-100 text-amber-800';
  return 'bg-rose-100 text-rose-800';
}

function getOrderStatusBadge(status) {
  const map = {
    'received': 'bg-slate-100 text-slate-700',
    'washing': 'bg-sky-50 text-sky-700',
    'drying': 'bg-cyan-50 text-cyan-700',
    'ironing': 'bg-indigo-50 text-indigo-700',
    'packing': 'bg-purple-50 text-purple-700',
    'ready': 'bg-amber-50 text-amber-700',
    'completed': 'bg-emerald-50 text-emerald-700',
    'cancelled': 'bg-rose-50 text-rose-700',
  };
  return map[status] || 'bg-slate-100 text-slate-700';
}

function formatOrderStatus(status) {
  const map = {
    'received': 'Diterima Kasir',
    'washing': 'Sedang Dicuci',
    'drying': 'Pengeringan',
    'ironing': 'Penyetrikaan',
    'packing': 'Dipacking',
    'ready': 'Siap Diambil (di Rak)',
    'completed': 'Selesai / Diambil',
    'cancelled': 'Dibatalkan',
  };
  return map[status] || status;
}
</script>

