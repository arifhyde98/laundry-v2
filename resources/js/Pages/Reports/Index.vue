<template>
  <AppLayout title="Laporan & Analitik Keuangan">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Laporan Keuangan & Rekap Shift</h1>
        <p class="text-sm text-slate-500 mt-0.5">Analisis pendapatan omset, pengeluaran kas, komisi staf, dan laba bersih riil.</p>
      </div>
      <div class="flex items-center gap-2">
        <button @click="printReport" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm shadow-sm transition-all">
          <Printer class="w-4 h-4" />
          <span>Cetak Laporan</span>
        </button>
      </div>
    </div>

    <!-- Date Filter Form -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm mb-6 flex flex-col sm:flex-row items-center gap-3">
      <div class="flex-1 flex items-center gap-2 w-full">
        <label class="text-xs font-bold text-slate-600 shrink-0">Dari:</label>
        <input v-model="startDate" type="date" class="w-full py-2 px-3 border border-slate-200 rounded-xl text-xs font-semibold" />
      </div>
      <div class="flex-1 flex items-center gap-2 w-full">
        <label class="text-xs font-bold text-slate-600 shrink-0">Sampai:</label>
        <input v-model="endDate" type="date" class="w-full py-2 px-3 border border-slate-200 rounded-xl text-xs font-semibold" />
      </div>
      <button @click="applyDateFilter" class="w-full sm:w-auto px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold transition-colors">
        Tampilkan
      </button>
    </div>

    <!-- Summary Metrics 4 Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="p-5 bg-white rounded-2xl border border-slate-200/80 shadow-sm">
        <span class="text-xs font-semibold text-slate-400 uppercase">Total Omset Penjualan</span>
        <h3 class="text-xl font-bold text-slate-900 mt-1">Rp {{ formatNumber(summary.totalOmset) }}</h3>
        <p class="text-xs text-slate-500 mt-1">{{ summary.totalOrders }} Transaksi ({{ summary.totalWeight }} Kg)</p>
      </div>

      <div class="p-5 bg-white rounded-2xl border border-slate-200/80 shadow-sm">
        <span class="text-xs font-semibold text-slate-400 uppercase">Total Kas Masuk Diterima</span>
        <h3 class="text-xl font-bold text-emerald-600 mt-1">Rp {{ formatNumber(summary.totalPaidCash) }}</h3>
        <p class="text-xs text-rose-500 font-semibold mt-1">Piutang Belum Lunas: Rp {{ formatNumber(summary.totalUnpaid) }}</p>
      </div>

      <div class="p-5 bg-white rounded-2xl border border-slate-200/80 shadow-sm">
        <span class="text-xs font-semibold text-slate-400 uppercase">Total Beban Operasional</span>
        <h3 class="text-xl font-bold text-rose-600 mt-1">- Rp {{ formatNumber(summary.totalExpense) }}</h3>
        <p class="text-xs text-slate-500 mt-1">Komisi Operator: Rp {{ formatNumber(summary.totalCommissions) }}</p>
      </div>

      <div class="p-5 bg-white rounded-2xl border border-slate-200/80 shadow-sm">
        <span class="text-xs font-semibold text-slate-400 uppercase">Laba Bersih Riil (Net Profit)</span>
        <h3 class="text-2xl font-extrabold text-sky-600 mt-1">Rp {{ formatNumber(summary.netProfit) }}</h3>
        <p class="text-xs text-emerald-600 font-semibold mt-1">Kas Masuk - Beban - Komisi</p>
      </div>
    </div>

    <!-- Shift Reconciliation Section -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-6">
      <div class="p-5 border-b border-slate-100">
        <h3 class="font-bold text-slate-900 text-sm">Rekapitulasi Shift Kasir & Laci Kas (Z-Report)</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
          <thead class="bg-slate-50 text-slate-400 uppercase text-[11px] font-bold tracking-wider">
            <tr>
              <th class="px-5 py-3">Kasir</th>
              <th class="px-5 py-3">Waktu Shift</th>
              <th class="px-5 py-3">Modal Awal</th>
              <th class="px-5 py-3">Penerimaan Kasir</th>
              <th class="px-5 py-3">Fisik Laci</th>
              <th class="px-5 py-3 text-right">Selisih Kas</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="s in shifts" :key="s.id" class="hover:bg-slate-50/80">
              <td class="px-5 py-3.5 font-bold text-slate-900">{{ s.user?.name || 'Kasir' }}</td>
              <td class="px-5 py-3.5 text-xs text-slate-500">{{ formatDateTime(s.opened_at) }} s/d {{ s.closed_at ? formatDateTime(s.closed_at) : 'Aktif' }}</td>
              <td class="px-5 py-3.5 font-medium">Rp {{ formatNumber(s.starting_cash) }}</td>
              <td class="px-5 py-3.5 font-semibold text-slate-900">
                Tunai: Rp {{ formatNumber(s.cash_income) }}<br>
                <span class="text-xs text-slate-400">Non-Tunai: Rp {{ formatNumber(s.non_cash_income) }}</span>
              </td>
              <td class="px-5 py-3.5 font-bold text-slate-900">
                {{ s.closing_cash !== null ? 'Rp ' + formatNumber(s.closing_cash) : 'Shift Masih Buka' }}
              </td>
              <td class="px-5 py-3.5 text-right font-bold">
                <span v-if="s.cash_difference === 0" class="text-emerald-600">Cocok (Rp 0)</span>
                <span v-else-if="s.cash_difference > 0" class="text-sky-600">+ Rp {{ formatNumber(s.cash_difference) }}</span>
                <span v-else class="text-rose-600">Rp {{ formatNumber(s.cash_difference) }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Printer } from 'lucide-vue-next';

const props = defineProps({
  filters: Object,
  summary: Object,
  orders: Array,
  expenses: Array,
  shifts: Array,
});

const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);

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

function printReport() {
  window.print();
}
</script>

