<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col justify-between p-4 sm:p-6 lg:p-8 relative overflow-hidden font-sans">
    <!-- Ambient Background Lighting -->
    <div class="absolute -top-40 -right-40 w-96 h-96 bg-sky-500/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-cyan-500/20 rounded-full blur-3xl pointer-events-none"></div>

    <!-- Header Section -->
    <div class="max-w-xl w-full mx-auto text-center pt-6 sm:pt-12 relative z-10">
      <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-sky-500 to-cyan-400 text-white shadow-xl shadow-sky-500/30 mb-3">
        <Droplets class="w-8 h-8" />
      </div>
      <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Laundry Express</h1>
      <p class="text-sm text-slate-400 mt-1">Portal Pelacakan Status & Nota Digital Pelanggan</p>

      <!-- Invoice Search Form -->
      <form @submit.prevent="searchInvoice" class="mt-6 flex gap-2">
        <div class="relative flex-1">
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Masukkan No. Invoice (cth: INV-2026...)" 
            class="w-full pl-10 pr-4 py-3 bg-slate-900 border border-slate-800 rounded-2xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-sky-500 transition-colors shadow-inner"
          />
          <Search class="w-4 h-4 absolute left-3.5 top-3.5 text-slate-500" />
        </div>
        <button type="submit" class="px-6 py-3 bg-sky-500 hover:bg-sky-600 text-white text-sm font-bold rounded-2xl shadow-lg shadow-sky-500/25 transition-all">
          Lacak
        </button>
      </form>
    </div>

    <!-- Tracking Result Card -->
    <div class="max-w-xl w-full mx-auto my-8 relative z-10">
      <div v-if="order" class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6">
        <!-- Order Header -->
        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
          <div>
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Nomor Pesanan</span>
            <h2 class="text-xl font-bold text-white">{{ order.invoice_code }}</h2>
            <p class="text-xs text-slate-400 mt-0.5">{{ order.order_date }} • Pelanggan: <strong>{{ order.customer?.name }}</strong></p>
          </div>
          <div class="text-right">
            <span :class="['px-3 py-1 rounded-full text-xs font-bold capitalize inline-block', getPaymentBadge(order.payment_status)]">
              {{ order.payment_status === 'paid' ? 'LUNAS' : 'BELUM LUNAS' }}
            </span>
            <p class="text-xs font-bold text-sky-400 mt-1">Rp {{ formatNumber(order.grand_total) }}</p>
          </div>
        </div>

        <!-- Live Progress Stepper -->
        <div>
          <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Progres Pengerjaan</h3>
          <div class="space-y-4 relative pl-6 border-l-2 border-slate-800">
            <div v-for="(step, idx) in steps" :key="idx" class="relative">
              <div :class="['absolute -left-[31px] top-1 w-4 h-4 rounded-full border-2', isStepPassed(step.key) ? 'bg-sky-500 border-sky-400 shadow-sm shadow-sky-500/50' : 'bg-slate-900 border-slate-700']"></div>
              <p :class="['text-xs font-bold capitalize', isStepPassed(step.key) ? 'text-white' : 'text-slate-500']">{{ step.title }}</p>
              <p class="text-[11px] text-slate-400">{{ step.desc }}</p>
            </div>
          </div>
        </div>

        <!-- Location Rack Badge (If ready or completed) -->
        <div v-if="order.rack" class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-300 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <Boxes class="w-6 h-6 text-amber-400" />
            <div>
              <p class="text-xs text-amber-400 font-medium">Lokasi Penyimpanan di Kasir:</p>
              <h4 class="text-lg font-bold text-amber-200">{{ order.rack.rack_code }}</h4>
            </div>
          </div>
          <span class="text-xs bg-amber-400/20 px-2.5 py-1 rounded-xl font-bold">Siap Diambil</span>
        </div>

        <!-- Items Summary -->
        <div class="border-t border-slate-800 pt-4 space-y-2">
          <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">Rincian Cucian ({{ order.total_weight_qty }} Kg/Pcs)</h4>
          <div v-for="item in order.items" :key="item.id" class="flex justify-between text-xs py-1 text-slate-300">
            <span>{{ item.quantity }}x {{ item.item_name }}</span>
            <span class="font-semibold text-white">Rp {{ formatNumber(item.subtotal) }}</span>
          </div>
        </div>

        <!-- Online Payment Section (if unpaid/partial) -->
        <div v-if="order.payment_status !== 'paid' && activeGateway" class="border-t border-slate-800 pt-5 space-y-3">
          <div class="p-4 bg-gradient-to-r from-sky-950 to-slate-900 border border-sky-500/30 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xl">
            <div>
              <p class="text-xs font-bold text-sky-400 uppercase tracking-wider">Bayar Online Langsung</p>
              <p class="text-xs text-slate-300">Gunakan QRIS (BCA, Mandiri, GoPay, OVO, ShopeePay) atau Virtual Account.</p>
              <p class="text-sm font-extrabold text-white mt-1">Tagihan: Rp {{ formatNumber(order.grand_total - order.paid_amount) }}</p>
            </div>
            <button 
              @click="payOnline" 
              :disabled="loadingPay"
              class="w-full sm:w-auto px-6 py-3 bg-gradient-to-r from-sky-500 to-cyan-500 hover:from-sky-400 hover:to-cyan-400 text-white font-bold text-xs rounded-xl shadow-lg shadow-sky-500/25 transition-all flex items-center justify-center gap-2 shrink-0 disabled:opacity-50"
            >
              <CreditCard class="w-4 h-4" />
              <span>{{ loadingPay ? 'Memuat Gateway...' : 'Bayar Sekarang' }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Not Found State -->
      <div v-else-if="searchCode" class="bg-slate-900 border border-slate-800 rounded-3xl p-8 text-center text-slate-400 space-y-2">
        <AlertCircle class="w-10 h-10 text-rose-500 mx-auto" />
        <h3 class="text-base font-bold text-white">Pesanan Tidak Ditemukan</h3>
        <p class="text-xs">Nomor invoice <strong>"{{ searchCode }}"</strong> tidak terdaftar di sistem. Mohon periksa kembali nomor struk Anda.</p>
      </div>
    </div>

    <!-- Footer -->
    <div class="text-center text-xs text-slate-500 pb-4 relative z-10">
      &copy; 2026 Laundry Express POS. All rights reserved.
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { Droplets, Search, Boxes, AlertCircle, CreditCard } from 'lucide-vue-next';
import { payWithMidtrans } from '@/Utils/midtrans';

const props = defineProps({
  searchCode: String,
  order: Object,
});

const searchQuery = ref(props.searchCode || '');
const activeGateway = ref(null);
const loadingPay = ref(false);

const steps = [
  { key: 'received', title: '1. Diterima di Kasir', desc: 'Pakaian diterima dan ditimbang di kasir.' },
  { key: 'washing', title: '2. Sedang Dicuci', desc: 'Proses pencucian dengan deterjen higienis.' },
  { key: 'drying', title: '3. Pengeringan', desc: 'Pengeringan mesin hingga kering sempurna.' },
  { key: 'ironing', title: '4. Penyetrikaan', desc: 'Penyetrikaan uap rapi dan harum.' },
  { key: 'packing', title: '5. Dipacking & Masuk Rak', desc: 'Pengemasan plastik kedap debu.' },
  { key: 'ready', title: '6. Siap Diambil', desc: 'Pakaian siap diambil di rak kasir.' },
  { key: 'completed', title: '7. Selesai / Diambil', desc: 'Pakaian telah diterima oleh pelanggan.' },
];

const statusWeights = {
  'received': 1,
  'washing': 2,
  'drying': 3,
  'ironing': 4,
  'packing': 5,
  'ready': 6,
  'completed': 7,
};

onMounted(async () => {
  try {
    const res = await fetch('/api/payment/active-gateway');
    const data = await res.json();
    if (data.active_gateway && data.active_gateway.is_active) {
      activeGateway.value = data.active_gateway;
    }
  } catch (e) {
    console.error('Failed to load active gateway:', e);
  }
});

function formatNumber(num) {
  return Number(num || 0).toLocaleString('id-ID');
}

function getPaymentBadge(status) {
  if (status === 'paid') return 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30';
  return 'bg-amber-500/20 text-amber-300 border border-amber-500/30';
}

function isStepPassed(stepKey) {
  if (!props.order) return false;
  const currentWeight = statusWeights[props.order.order_status] || 1;
  const targetWeight = statusWeights[stepKey] || 1;
  return currentWeight >= targetWeight;
}

function searchInvoice() {
  if (!searchQuery.value) return;
  router.get(`/track/${searchQuery.value.trim()}`);
}

async function payOnline() {
  if (!props.order) return;
  loadingPay.value = true;
  try {
    const res = await fetch('/api/payment/snap-token', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        order_id: props.order.id,
      })
    });
    const data = await res.json();

    if (data.status === 'success' && data.data?.snap_token) {
      payWithMidtrans({
        snapToken: data.data.snap_token,
        clientKey: data.data.client_key,
        mode: data.data.mode,
        onSuccess: () => {
          router.reload({ preserveScroll: true });
        },
        onPending: () => {
          router.reload({ preserveScroll: true });
        },
        onError: () => {
          alert('Pembayaran Gagal atau Dibatalkan.');
        }
      });
    } else {
      alert(data.message || 'Gagal mendapatkan Snap Token Midtrans.');
    }
  } catch (e) {
    console.error(e);
    alert('Terjadi kesalahan jaringan.');
  } finally {
    loadingPay.value = false;
  }
}
</script>

