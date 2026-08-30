<template>
  <AppLayout title="Laporan & Analitik Keuangan">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 print:hidden">
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Laporan Keuangan & Rekap</h1>
        <p class="text-sm text-slate-500 mt-0.5">Analisis pendapatan omset, pengeluaran kas, komisi staf, dan laba bersih riil.</p>
      </div>
      <div class="flex flex-wrap items-center gap-2">
        <button @click="exportCSV" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-sm transition-all">
          <Download class="w-4 h-4" />
          <span>Ekspor CSV</span>
        </button>
        <button @click="printReport" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm shadow-sm transition-all">
          <Printer class="w-4 h-4" />
          <span>Cetak PDF / A4</span>
        </button>
      </div>
    </div>

    <!-- Date Filter Form -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm mb-6 flex flex-col sm:flex-row items-center gap-3 print:hidden">
      <div class="flex-1 flex items-center gap-2 w-full">
        <label class="text-xs font-bold text-slate-600 shrink-0">Dari:</label>
        <input v-model="startDate" type="date" class="w-full py-2 px-3 border border-slate-200 rounded-xl text-xs font-semibold" />
      </div>
      <div class="flex-1 flex items-center gap-2 w-full">
        <label class="text-xs font-bold text-slate-600 shrink-0">Sampai:</label>
        <input v-model="endDate" type="date" class="w-full py-2 px-3 border border-slate-200 rounded-xl text-xs font-semibold" />
      </div>
      <button @click="applyDateFilter" class="w-full sm:w-auto px-6 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold transition-colors">
        Terapkan Filter
      </button>
    </div>

    <!-- Print Header (Only visible when printing) -->
    <div class="hidden print:block mb-8 border-b-2 border-slate-800 pb-4">
      <h1 class="text-2xl font-bold uppercase text-center">Laporan Keuangan Laundry</h1>
      <p class="text-center mt-1 text-sm">Periode: {{ formatDateTime(startDate).split(' ')[0] }} s/d {{ formatDateTime(endDate).split(' ')[0] }}</p>
    </div>

    <!-- Summary Metrics 4 Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <!-- Omset -->
      <div class="p-5 rounded-2xl border border-slate-200/80 shadow-sm bg-gradient-to-br from-white to-slate-50 relative overflow-hidden">
        <div class="absolute -right-4 -bottom-4 opacity-5">
          <Activity class="w-24 h-24 text-slate-900" />
        </div>
        <div class="flex items-center gap-2 mb-2">
          <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-600">
            <Activity class="w-4 h-4" />
          </div>
          <span class="text-xs font-bold text-slate-500 uppercase">Total Omset Penjualan</span>
        </div>
        <h3 class="text-xl font-bold text-slate-900">Rp {{ formatNumber(summary.totalOmset) }}</h3>
        <p class="text-xs text-slate-500 mt-1 font-medium">{{ summary.totalOrders }} Transaksi ({{ summary.totalWeight }} Kg)</p>
      </div>

      <!-- Kas Masuk -->
      <div class="p-5 rounded-2xl border border-emerald-100 shadow-sm bg-gradient-to-br from-emerald-50 to-white relative overflow-hidden">
        <div class="absolute -right-4 -bottom-4 opacity-10">
          <TrendingUp class="w-24 h-24 text-emerald-600" />
        </div>
        <div class="flex items-center gap-2 mb-2">
          <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600">
            <TrendingUp class="w-4 h-4" />
          </div>
          <span class="text-xs font-bold text-emerald-700 uppercase">Total Kas Masuk Diterima</span>
        </div>
        <h3 class="text-xl font-bold text-emerald-600">Rp {{ formatNumber(summary.totalPaidCash) }}</h3>
        <p class="text-[11px] text-rose-500 font-bold mt-1">Piutang Belum Lunas: Rp {{ formatNumber(summary.totalUnpaid) }}</p>
      </div>

      <!-- Beban -->
      <div class="p-5 rounded-2xl border border-rose-100 shadow-sm bg-gradient-to-br from-rose-50 to-white relative overflow-hidden">
        <div class="absolute -right-4 -bottom-4 opacity-10">
          <TrendingDown class="w-24 h-24 text-rose-600" />
        </div>
        <div class="flex items-center gap-2 mb-2">
          <div class="w-8 h-8 rounded-lg bg-rose-100 flex items-center justify-center text-rose-600">
            <TrendingDown class="w-4 h-4" />
          </div>
          <span class="text-xs font-bold text-rose-700 uppercase">Total Beban Operasional</span>
        </div>
        <h3 class="text-xl font-bold text-rose-600">- Rp {{ formatNumber(summary.totalExpense) }}</h3>
        <p class="text-[11px] text-slate-500 font-medium mt-1">Gaji/Komisi Karyawan: Rp {{ formatNumber(summary.totalCommissions) }}</p>
      </div>

      <!-- Net Profit -->
      <div class="p-5 rounded-2xl border border-sky-200 shadow-sm bg-gradient-to-br from-sky-500 to-blue-600 relative overflow-hidden">
        <div class="absolute -right-4 -bottom-4 opacity-20">
          <Wallet class="w-24 h-24 text-white" />
        </div>
        <div class="flex items-center gap-2 mb-2">
          <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-white">
            <Wallet class="w-4 h-4" />
          </div>
          <span class="text-xs font-bold text-sky-100 uppercase">Laba Bersih Riil (Net Profit)</span>
        </div>
        <h3 class="text-2xl font-extrabold text-white">Rp {{ formatNumber(summary.netProfit) }}</h3>
        <p class="text-[11px] text-sky-100 font-medium mt-1">Kas Masuk - Beban - Komisi</p>
      </div>
    </div>

    <!-- Tabs for Detailed Tables -->
    <div class="mb-6 border-b border-slate-200 print:hidden overflow-x-auto">
      <nav class="-mb-px flex space-x-4 sm:space-x-8 min-w-max px-1">
        <button 
          @click="activeTab = 'shifts'"
          :class="[activeTab === 'shifts' ? 'border-sky-500 text-sky-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300', 'whitespace-nowrap pb-4 px-1 border-b-2 font-bold text-sm transition-colors']"
        >
          Rekap Kasir & Shift
        </button>
        <button 
          @click="activeTab = 'orders'"
          :class="[activeTab === 'orders' ? 'border-sky-500 text-sky-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300', 'whitespace-nowrap pb-4 px-1 border-b-2 font-bold text-sm transition-colors']"
        >
          Rincian Transaksi ({{ orders.length }})
        </button>
        <button 
          @click="activeTab = 'expenses'"
          :class="[activeTab === 'expenses' ? 'border-sky-500 text-sky-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300', 'whitespace-nowrap pb-4 px-1 border-b-2 font-bold text-sm transition-colors']"
        >
          Rincian Pengeluaran ({{ expenses.length }})
        </button>
      </nav>
    </div>

    <!-- Tab 1: Shift Reconciliation Section -->
    <div v-show="activeTab === 'shifts' || isPrinting" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-6">
      <div class="p-5 border-b border-slate-100 print:border-b-2 print:border-black">
        <h3 class="font-bold text-slate-900 text-sm print:text-lg">Rekapitulasi Shift Kasir & Laci Kas (Z-Report)</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600 print:text-xs">
          <thead class="bg-slate-50 text-slate-400 uppercase text-[11px] font-bold tracking-wider print:bg-transparent print:text-black print:border-b print:border-black">
            <tr>
              <th class="px-5 py-3">Kasir</th>
              <th class="px-5 py-3">Waktu Shift</th>
              <th class="px-5 py-3">Modal Awal</th>
              <th class="px-5 py-3">Penerimaan Kasir</th>
              <th class="px-5 py-3">Fisik Laci</th>
              <th class="px-5 py-3 text-right">Selisih Kas</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 print:divide-slate-300">
            <tr v-for="s in shifts" :key="s.id" class="hover:bg-slate-50/80">
              <td class="px-5 py-3.5 font-bold text-slate-900">{{ s.user?.name || 'Kasir' }}</td>
              <td class="px-5 py-3.5 text-xs text-slate-500">{{ formatDateTime(s.opened_at) }} s/d {{ s.closed_at ? formatDateTime(s.closed_at) : 'Aktif' }}</td>
              <td class="px-5 py-3.5 font-medium">Rp {{ formatNumber(s.starting_cash) }}</td>
              <td class="px-5 py-3.5 font-semibold text-slate-900">
                Tunai: Rp {{ formatNumber(s.cash_income) }}<br>
                <span class="text-xs text-slate-400">Non-Tunai: Rp {{ formatNumber(s.non_cash_income) }}</span>
              </td>
              <td class="px-5 py-3.5 font-bold text-slate-900">
                {{ s.closing_cash !== null ? 'Rp ' + formatNumber(s.closing_cash) : 'Buka' }}
              </td>
              <td class="px-5 py-3.5 text-right font-bold">
                <span v-if="s.cash_difference === 0" class="text-emerald-600 print:text-black">Cocok (Rp 0)</span>
                <span v-else-if="s.cash_difference > 0" class="text-sky-600 print:text-black">+ Rp {{ formatNumber(s.cash_difference) }}</span>
                <span v-else class="text-rose-600 print:text-black">Rp {{ formatNumber(s.cash_difference) }}</span>
              </td>
            </tr>
            <tr v-if="shifts.length === 0">
              <td colspan="6" class="px-5 py-8 text-center text-slate-400 text-sm">Tidak ada shift tercatat.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Tab 2: Orders Section -->
    <div v-show="activeTab === 'orders' || isPrinting" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-6">
      <div class="p-5 border-b border-slate-100 print:border-b-2 print:border-black flex justify-between items-center">
        <h3 class="font-bold text-slate-900 text-sm print:text-lg">Rincian Transaksi Penjualan</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600 print:text-xs">
          <thead class="bg-slate-50 text-slate-400 uppercase text-[11px] font-bold tracking-wider print:bg-transparent print:text-black print:border-b print:border-black">
            <tr>
              <th class="px-5 py-3">Tanggal</th>
              <th class="px-5 py-3">Invoice</th>
              <th class="px-5 py-3">Pelanggan</th>
              <th class="px-5 py-3">Qty/Berat</th>
              <th class="px-5 py-3">Total</th>
              <th class="px-5 py-3">Status Bayar</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 print:divide-slate-300">
            <tr v-for="o in orders" :key="o.id" class="hover:bg-slate-50/80">
              <td class="px-5 py-3 whitespace-nowrap text-xs">{{ formatDateTime(o.order_date).split(' ')[0] }}</td>
              <td class="px-5 py-3 font-bold text-slate-900 text-xs"><Link :href="`/orders/${o.id}`" class="hover:underline">{{ o.invoice_code }}</Link></td>
              <td class="px-5 py-3 font-medium text-xs">{{ o.customer?.name || '-' }}</td>
              <td class="px-5 py-3 text-xs">{{ o.total_weight_qty }}</td>
              <td class="px-5 py-3 font-bold text-slate-900 text-xs">Rp {{ formatNumber(o.grand_total) }}</td>
              <td class="px-5 py-3 text-xs uppercase font-bold tracking-wider">
                <span :class="o.payment_status === 'paid' ? 'text-emerald-600' : 'text-rose-600'">{{ o.payment_status }}</span>
              </td>
            </tr>
            <tr v-if="orders.length === 0">
              <td colspan="6" class="px-5 py-8 text-center text-slate-400 text-sm">Tidak ada transaksi.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Tab 3: Expenses Section -->
    <div v-show="activeTab === 'expenses' || isPrinting" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-6">
      <div class="p-5 border-b border-slate-100 print:border-b-2 print:border-black">
        <h3 class="font-bold text-slate-900 text-sm print:text-lg">Rincian Beban & Pengeluaran</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600 print:text-xs">
          <thead class="bg-slate-50 text-slate-400 uppercase text-[11px] font-bold tracking-wider print:bg-transparent print:text-black print:border-b print:border-black">
            <tr>
              <th class="px-5 py-3">Tanggal</th>
              <th class="px-5 py-3">Kategori</th>
              <th class="px-5 py-3">Deskripsi</th>
              <th class="px-5 py-3 text-right">Nominal</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 print:divide-slate-300">
            <tr v-for="e in expenses" :key="e.id" class="hover:bg-slate-50/80">
              <td class="px-5 py-3 whitespace-nowrap text-xs">{{ formatDateTime(e.expense_date).split(' ')[0] }}</td>
              <td class="px-5 py-3 text-xs uppercase tracking-wider font-bold text-slate-500">{{ e.category }}</td>
              <td class="px-5 py-3 font-medium text-xs">{{ e.description }}</td>
              <td class="px-5 py-3 text-right font-bold text-rose-600 text-xs print:text-black">Rp {{ formatNumber(e.amount) }}</td>
            </tr>
            <tr v-if="expenses.length === 0">
              <td colspan="4" class="px-5 py-8 text-center text-slate-400 text-sm">Tidak ada pengeluaran.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </AppLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Printer, Download, TrendingUp, TrendingDown, Wallet, Activity } from 'lucide-vue-next';

const props = defineProps({
  filters: Object,
  summary: Object,
  orders: Array,
  expenses: Array,
  shifts: Array,
});

const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);
const activeTab = ref('shifts'); // shifts, orders, expenses
const isPrinting = ref(false);

function formatNumber(num) {
  return Number(num || 0).toLocaleString('id-ID');
}

function formatDateTime(dt) {
  if (!dt) return '-';
  return new Intl.DateTimeFormat('id-ID', { dateStyle: 'short', timeStyle: 'short' }).format(new Date(dt));
}

function applyDateFilter() {
  router.get('/reports', {
    start_date: startDate.value,
    end_date: endDate.value,
  }, {
    preserveState: true,
  });
}

function exportCSV() {
  window.location.href = `/reports/export?start_date=${startDate.value}&end_date=${endDate.value}`;
}

function printReport() {
  window.print();
}

// Ensure tabs are expanded for printing
onMounted(() => {
  window.addEventListener('beforeprint', () => { isPrinting.value = true; });
  window.addEventListener('afterprint', () => { isPrinting.value = false; });
});
onUnmounted(() => {
  window.removeEventListener('beforeprint', () => { isPrinting.value = true; });
  window.removeEventListener('afterprint', () => { isPrinting.value = false; });
});
</script>

<style>
@media print {
  /* Sembunyikan elemen utama layout (sidebar/header) */
  body * {
    visibility: hidden;
  }
  /* Hanya tampilkan area laporan */
  .max-w-7xl, .max-w-7xl * {
    visibility: visible;
  }
  .max-w-7xl {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    margin: 0;
    padding: 0;
  }
  /* Paksa warna background dan gradient tercetak di Chrome/Safari */
  * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
}
</style>
