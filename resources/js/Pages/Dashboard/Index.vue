<template>
  <AppLayout title="Dashboard Utama">
    <!-- Welcome Header & Quick Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Selamat Datang Kembali!</h1>
        <p class="text-sm text-slate-500 mt-0.5">Berikut adalah ringkasan performa operasional & keuangan laundry Anda.</p>
      </div>
      <div class="flex items-center gap-2">
        <Link :href="'/pos'" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-semibold text-sm shadow-md shadow-sky-500/25 transition-all">
          <Plus class="w-4 h-4" />
          <span>Buka Kasir POS</span>
        </Link>
      </div>
    </div>

    <!-- Stat Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <!-- Omset Hari Ini -->
      <div class="p-5 bg-white rounded-2xl border border-slate-200/80 shadow-sm relative overflow-hidden">
        <div class="flex items-center justify-between">
          <div>
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Penjualan Hari Ini</span>
            <h3 class="text-2xl font-bold text-slate-900 mt-1">Rp {{ formatNumber(metrics.todaySales) }}</h3>
          </div>
          <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
            <Coins class="w-6 h-6" />
          </div>
        </div>
        <div class="mt-3 flex items-center gap-1.5 text-xs text-slate-500 font-medium">
          <span class="text-emerald-600 font-semibold">Omset Bulan Ini:</span>
          <span>Rp {{ formatNumber(metrics.thisMonthSales) }}</span>
        </div>
      </div>

      <!-- Cucian Diproses -->
      <div class="p-5 bg-white rounded-2xl border border-slate-200/80 shadow-sm relative overflow-hidden">
        <div class="flex items-center justify-between">
          <div>
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Antrian Cuci & Setrika</span>
            <h3 class="text-2xl font-bold text-amber-500 mt-1">{{ metrics.ordersProcessing }} <span class="text-sm font-normal text-slate-400">Order</span></h3>
          </div>
          <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center">
            <Clock class="w-6 h-6" />
          </div>
        </div>
        <div class="mt-3 flex items-center justify-between text-xs text-slate-500 font-medium">
          <span>Siap Diambil di Rak:</span>
          <span class="font-bold text-sky-600">{{ metrics.ordersReady }} Order</span>
        </div>
      </div>

      <!-- Laba Bersih Bulan Ini -->
      <div class="p-5 bg-white rounded-2xl border border-slate-200/80 shadow-sm relative overflow-hidden">
        <div class="flex items-center justify-between">
          <div>
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Laba Bersih Bulan Ini</span>
            <h3 class="text-2xl font-bold text-sky-600 mt-1">Rp {{ formatNumber(metrics.netProfitThisMonth) }}</h3>
          </div>
          <div class="w-12 h-12 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center">
            <TrendingUp class="w-6 h-6" />
          </div>
        </div>
        <div class="mt-3 flex items-center justify-between text-xs text-slate-500 font-medium">
          <span>Total Beban Kas:</span>
          <span class="text-rose-500 font-semibold">Rp {{ formatNumber(metrics.thisMonthExpenses) }}</span>
        </div>
      </div>

      <!-- Piutang Belum Lunas -->
      <div class="p-5 bg-white rounded-2xl border border-slate-200/80 shadow-sm relative overflow-hidden">
        <div class="flex items-center justify-between">
          <div>
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Piutang Belum Lunas</span>
            <h3 class="text-2xl font-bold text-rose-500 mt-1">Rp {{ formatNumber(metrics.totalUnpaidDebt) }}</h3>
          </div>
          <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center">
            <AlertCircle class="w-6 h-6" />
          </div>
        </div>
        <div class="mt-3 flex items-center justify-between text-xs text-slate-500 font-medium">
          <span>Kapasitas Rak Kosong:</span>
          <span class="text-emerald-600 font-bold">{{ metrics.availableRacks }} / {{ metrics.totalRacks }} Slot</span>
        </div>
      </div>
    </div>

    <!-- Chart & Summary Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
      <!-- Monthly Revenue & Expense Bar Chart -->
      <div class="lg:col-span-2 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h3 class="font-bold text-slate-900">Grafik Penjualan & Pengeluaran Bulanan</h3>
            <p class="text-xs text-slate-500">Perbandingan pemasukan omset dan beban operasional tahun {{ chart.year }}</p>
          </div>
        </div>
        <div class="h-64 sm:h-72 w-full">
          <Bar :data="chartData" :options="chartOptions" />
        </div>
      </div>

      <!-- Storage Rack & Quick Action Card -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold text-slate-900">Status Lemari & Rak</h3>
            <Link :href="'/racks'" class="text-xs font-semibold text-sky-600 hover:underline">Lihat Grid</Link>
          </div>
          <p class="text-xs text-slate-500 mb-4">Monitoring slot penyimpanan pakaian pelanggan yang telah selesai dipacking.</p>
          
          <div class="space-y-3">
            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
              <div class="flex items-center gap-2.5">
                <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                <span class="text-xs font-semibold text-slate-700">Slot Rak Tersedia</span>
              </div>
              <span class="text-sm font-bold text-slate-900">{{ metrics.availableRacks }} Rak</span>
            </div>

            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
              <div class="flex items-center gap-2.5">
                <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                <span class="text-xs font-semibold text-slate-700">Terisi Pakaian Siap Ambil</span>
              </div>
              <span class="text-sm font-bold text-slate-900">{{ metrics.ordersReady }} Order</span>
            </div>

            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
              <div class="flex items-center gap-2.5">
                <div class="w-3 h-3 rounded-full bg-sky-500"></div>
                <span class="text-xs font-semibold text-slate-700">Total Pelanggan Terdaftar</span>
              </div>
              <span class="text-sm font-bold text-slate-900">{{ metrics.totalCustomers }} Orang</span>
            </div>
          </div>
        </div>

        <div class="pt-4 border-t border-slate-100 mt-4">
          <Link :href="'/workstation'" class="w-full py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-medium text-xs flex items-center justify-center gap-2 transition-all">
            <Kanban class="w-4 h-4" />
            <span>Buka Kanban Antrian Cuci</span>
          </Link>
        </div>
      </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
      <div class="p-5 border-b border-slate-100 flex items-center justify-between">
        <div>
          <h3 class="font-bold text-slate-900">Transaksi Terbaru</h3>
          <p class="text-xs text-slate-500">Daftar transaksi masuk dan status pengerjaan saat ini</p>
        </div>
        <Link :href="'/orders'" class="text-xs font-semibold text-sky-600 hover:underline flex items-center gap-1">
          <span>Semua Transaksi</span>
          <ChevronRight class="w-4 h-4" />
        </Link>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
          <thead class="bg-slate-50 text-slate-400 uppercase text-[11px] font-bold tracking-wider">
            <tr>
              <th class="px-5 py-3.5">Invoice</th>
              <th class="px-5 py-3.5">Pelanggan</th>
              <th class="px-5 py-3.5">Berat / Qty</th>
              <th class="px-5 py-3.5">Total Biaya</th>
              <th class="px-5 py-3.5">Status Bayar</th>
              <th class="px-5 py-3.5">Status Pengerjaan</th>
              <th class="px-5 py-3.5 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="order in recentOrders" :key="order.id" class="hover:bg-slate-50/80 transition-colors">
              <td class="px-5 py-4 font-semibold text-slate-900">
                {{ order.invoice_code }}
              </td>
              <td class="px-5 py-4">
                <div class="font-medium text-slate-900">{{ order.customer?.name || '-' }}</div>
                <div class="text-xs text-slate-400">{{ order.customer?.phone || '-' }}</div>
              </td>
              <td class="px-5 py-4 font-medium">
                {{ order.total_weight_qty }} Kg/Pcs
              </td>
              <td class="px-5 py-4 font-bold text-slate-900">
                Rp {{ formatNumber(order.grand_total) }}
              </td>
              <td class="px-5 py-4">
                <span :class="['px-2.5 py-1 rounded-full text-xs font-semibold capitalize', getPaymentBadge(order.payment_status)]">
                  {{ order.payment_status }}
                </span>
              </td>
              <td class="px-5 py-4">
                <span :class="['px-2.5 py-1 rounded-full text-xs font-semibold capitalize', getOrderStatusBadge(order.order_status)]">
                  {{ formatOrderStatus(order.order_status) }}
                </span>
              </td>
              <td class="px-5 py-4 text-right">
                <Link :href="`/orders/${order.id}`" class="inline-flex items-center gap-1 text-xs font-semibold text-sky-600 hover:text-sky-800 bg-sky-50 px-3 py-1.5 rounded-lg transition-colors">
                  <span>Detail</span>
                </Link>
              </td>
            </tr>
            <tr v-if="recentOrders.length === 0">
              <td colspan="7" class="px-5 py-8 text-center text-slate-400 text-sm">
                Belum ada transaksi tercatat.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
  Coins, Clock, TrendingUp, AlertCircle, Plus, Kanban, 
  ChevronRight 
} from 'lucide-vue-next';
import { Bar } from 'vue-chartjs';
import { 
  Chart as ChartJS, Title, Tooltip, Legend, BarElement, 
  CategoryScale, LinearScale 
} from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const props = defineProps({
  metrics: Object,
  chart: Object,
  recentOrders: Array,
});

function formatNumber(num) {
  return Number(num || 0).toLocaleString('id-ID');
}

function getPaymentBadge(status) {
  if (status === 'paid') return 'bg-emerald-50 text-emerald-700 border border-emerald-200';
  if (status === 'partial') return 'bg-amber-50 text-amber-700 border border-amber-200';
  return 'bg-rose-50 text-rose-700 border border-rose-200';
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
    'ready': 'Siap Diambil',
    'completed': 'Selesai / Diambil',
    'cancelled': 'Dibatalkan',
  };
  return map[status] || status;
}

const chartData = computed(() => ({
  labels: props.chart.labels,
  datasets: [
    {
      label: 'Penjualan (Omset)',
      backgroundColor: '#0284c7',
      borderRadius: 6,
      data: props.chart.sales,
    },
    {
      label: 'Pengeluaran (Beban)',
      backgroundColor: '#f43f5e',
      borderRadius: 6,
      data: props.chart.expenses,
    }
  ]
}));

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top',
      labels: { font: { family: 'Plus Jakarta Sans', size: 12 } }
    },
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: { color: '#f1f5f9' },
      ticks: {
        callback: (val) => 'Rp ' + Number(val).toLocaleString('id-ID'),
        font: { family: 'Plus Jakarta Sans', size: 11 }
      }
    },
    x: {
      grid: { display: false },
      ticks: { font: { family: 'Plus Jakarta Sans', size: 11 } }
    }
  }
};
</script>

