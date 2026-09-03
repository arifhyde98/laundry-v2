<template>
  <AppLayout :title="`Detail Transaksi - ${order.invoice_code}`">
    <!-- Header Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div class="flex items-center gap-3">
        <Link :href="'/orders'" class="p-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 transition-colors">
          <ArrowLeft class="w-4 h-4" />
        </Link>
        <div>
          <div class="flex items-center gap-2">
            <h1 class="text-xl font-bold text-slate-900">{{ order.invoice_code }}</h1>
            <span :class="['px-2.5 py-0.5 rounded-full text-xs font-bold capitalize', getPaymentBadge(order.payment_status)]">
              {{ order.payment_status }}
            </span>
          </div>
          <p class="text-xs text-slate-500 mt-0.5">Dibuat pada {{ formatDateTime(order.created_at) }} oleh {{ order.user?.name || 'Kasir' }}</p>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center gap-2">
        <button 
          @click="printThermalReceipt" 
          class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-sm transition-all"
        >
          <Printer class="w-4 h-4" />
          <span>Cetak Struk Thermal</span>
        </button>

        <a 
          :href="`https://wa.me/${cleanPhone(order.customer?.phone)}?text=${encodeURIComponent(getWhatsAppText())}`" 
          target="_blank"
          class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition-all"
        >
          <MessageCircle class="w-4 h-4" />
          <span>Kirim WhatsApp</span>
        </a>

        <button 
          v-if="order.payment_status !== 'paid'"
          @click="showPayModal = true"
          class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold shadow-sm transition-all"
        >
          <CreditCard class="w-4 h-4" />
          <span>Pelunasan Sisa</span>
        </button>
      </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
      <!-- Left 8 cols: Customer, Items, Payments -->
      <div class="lg:col-span-8 space-y-6">
        <!-- Customer Info Card -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
          <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Informasi Pelanggan</h3>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
            <div>
              <p class="text-xs text-slate-500">Nama Pelanggan</p>
              <p class="font-bold text-slate-900">{{ order.customer?.name }}</p>
            </div>
            <div>
              <p class="text-xs text-slate-500">Nomor Telepon / WA</p>
              <p class="font-bold text-slate-900">{{ order.customer?.phone || '-' }}</p>
            </div>
            <div>
              <p class="text-xs text-slate-500">Alamat</p>
              <p class="font-bold text-slate-900">{{ order.customer?.address || '-' }}</p>
            </div>
          </div>
        </div>

        <!-- Items Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
          <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Rincian Layanan & Pakaian</h3>
            <span class="text-xs font-semibold text-slate-500">Total Berat: <strong>{{ order.total_weight_qty }} Kg/Pcs</strong></span>
          </div>

          <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-slate-400 uppercase text-[11px] font-bold tracking-wider">
              <tr>
                <th class="px-5 py-3">Nama Item / Layanan</th>
                <th class="px-5 py-3">Jumlah</th>
                <th class="px-5 py-3">Harga Satuan</th>
                <th class="px-5 py-3 text-right">Subtotal</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="item in order.items" :key="item.id">
                <td class="px-5 py-3.5 font-bold text-slate-900">
                  {{ item.item_name }}
                  <span v-if="item.service" class="block text-xs font-normal text-slate-400">{{ item.service.name }}</span>
                </td>
                <td class="px-5 py-3.5 font-medium">{{ item.quantity }}</td>
                <td class="px-5 py-3.5 font-medium">Rp {{ formatNumber(item.unit_price) }}</td>
                <td class="px-5 py-3.5 text-right font-bold text-slate-900">Rp {{ formatNumber(item.subtotal) }}</td>
              </tr>
            </tbody>
          </table>

          <!-- Financial Breakdown -->
          <div class="p-5 bg-slate-50/70 border-t border-slate-100 space-y-1.5 text-xs">
            <div class="flex justify-between text-slate-600">
              <span>Subtotal:</span>
              <span class="font-semibold text-slate-900">Rp {{ formatNumber(order.subtotal_amount) }}</span>
            </div>
            <div v-if="order.discount_amount > 0" class="flex justify-between text-slate-600">
              <span>Diskon:</span>
              <span class="font-semibold text-rose-600">- Rp {{ formatNumber(order.discount_amount) }}</span>
            </div>
            <div v-if="order.delivery_fee > 0" class="flex justify-between text-slate-600">
              <span>Biaya Pengantaran:</span>
              <span class="font-semibold text-slate-900">+ Rp {{ formatNumber(order.delivery_fee) }}</span>
            </div>
            <div class="flex justify-between pt-2 border-t border-slate-200 text-sm font-bold text-slate-900">
              <span>Grand Total:</span>
              <span class="text-sky-600 text-base">Rp {{ formatNumber(order.grand_total) }}</span>
            </div>
            <div class="flex justify-between text-emerald-700 font-bold">
              <span>Sudah Dibayar:</span>
              <span>Rp {{ formatNumber(order.paid_amount) }}</span>
            </div>
            <div v-if="order.grand_total - order.paid_amount > 0" class="flex justify-between text-rose-600 font-bold">
              <span>Sisa Tagihan:</span>
              <span>Rp {{ formatNumber(order.grand_total - order.paid_amount) }}</span>
            </div>
          </div>
        </div>

        <!-- Payment History Records -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
          <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Riwayat Pembayaran Masuk</h3>
          <div v-if="order.payments && order.payments.length > 0" class="space-y-2">
            <div v-for="p in order.payments" :key="p.id" class="p-3 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between text-xs">
              <div>
                <p class="font-bold text-slate-900">Rp {{ formatNumber(p.amount_paid) }} <span class="font-normal text-slate-500">via {{ p.payment_method }}</span></p>
                <p class="text-slate-400">Diterima oleh {{ p.receiver?.name || 'Kasir' }} • {{ formatDateTime(p.paid_at) }}</p>
              </div>
              <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px] uppercase">Berhasil</span>
            </div>
          </div>
          <p v-else class="text-xs text-slate-400">Belum ada pembayaran tercatat.</p>
        </div>
      </div>

      <!-- Right 4 cols: Status, Storage Rack & Timeline Logs -->
      <div class="lg:col-span-4 space-y-6">
        <!-- Status & Rack Card -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
          <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Status Operasional</h3>
          
          <div>
            <label class="block text-xs text-slate-500 mb-1">Status Pengerjaan Saat Ini:</label>
            <div class="flex items-center gap-2">
              <span :class="['px-3 py-1 rounded-xl text-xs font-bold capitalize', getOrderStatusBadge(order.order_status)]">
                {{ formatOrderStatus(order.order_status) }}
              </span>
            </div>
          </div>

          <div>
            <label class="block text-xs text-slate-500 mb-1">Lokasi Rak / Lemari:</label>
            <div class="p-3 rounded-xl bg-sky-50 border border-sky-100 flex items-center justify-between">
              <span class="font-bold text-sky-900">{{ order.rack ? order.rack.rack_code : 'Belum Ditempatkan di Rak' }}</span>
              <Boxes class="w-4 h-4 text-sky-600" />
            </div>
          </div>

          <div>
            <label class="block text-xs text-slate-500 mb-1">Estimasi Selesai:</label>
            <p class="font-semibold text-slate-900 text-xs">{{ formatDateTime(order.estimated_completion) }}</p>
          </div>
        </div>

        <!-- Tracking Timeline History -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
          <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Log Progres Cucian</h3>
          <div class="relative pl-4 space-y-4 border-l-2 border-slate-100">
            <div v-for="log in order.tracking_logs" :key="log.id" class="relative">
              <div class="absolute -left-[21px] top-0.5 w-3 h-3 rounded-full bg-sky-500 border-2 border-white"></div>
              <p class="text-xs font-bold text-slate-900 capitalize">{{ formatOrderStatus(log.status_to) }}</p>
              <p class="text-[11px] text-slate-500">{{ log.notes || '-' }}</p>
              <p class="text-[10px] text-slate-400 mt-0.5">{{ formatDateTime(log.created_at) }} • {{ log.changer?.name || 'Sistem' }}</p>
            </div>
          </div>
        </div>

        <!-- Rewash & Complaints History Card -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm space-y-3">
          <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Garansi & Komplain</h3>
            <Link href="/rewash" class="text-[11px] text-indigo-600 font-bold hover:underline">+ Tiket Baru</Link>
          </div>
          <div v-if="order.rewash_tickets && order.rewash_tickets.length > 0" class="space-y-2">
            <div v-for="t in order.rewash_tickets" :key="t.id" class="p-3 bg-amber-50/70 border border-amber-200/60 rounded-xl text-xs space-y-1">
              <div class="flex items-center justify-between">
                <span class="font-mono font-bold text-indigo-700">{{ t.ticket_code }}</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-100 text-amber-800">{{ t.status }}</span>
              </div>
              <p class="text-slate-700 font-medium">{{ t.reason }}</p>
              <p v-if="t.resolution_note" class="text-slate-500 text-[11px] italic">Res: {{ t.resolution_note }}</p>
            </div>
          </div>
          <p v-else class="text-xs text-slate-400 italic">Tidak ada catatan komplain pada order ini.</p>
        </div>
      </div>
    </div>

    <!-- Hidden Thermal Receipt for Printing -->
    <div id="thermal-receipt" class="hidden print:block p-2 font-mono text-xs text-black" :style="{ width: order.outlet?.receipt_paper_size || $page.props.outlet?.receipt_paper_size || '58mm' }">
      <div class="text-center pb-2 border-b border-dashed border-black">
        <h2 class="font-bold text-sm uppercase">{{ order.outlet?.name || $page.props.outlet?.name || 'LAUNDRY EXPRESS' }}</h2>
        <p v-if="order.outlet?.receipt_header || $page.props.outlet?.receipt_header" class="text-[9px] italic font-semibold">
          {{ order.outlet?.receipt_header || $page.props.outlet?.receipt_header }}
        </p>
        <p class="text-[10px]">{{ order.outlet?.address || $page.props.outlet?.address || 'Jl. Utama Laundry No. 1, Kota' }}</p>
        <p class="text-[10px]">Telp/WA: {{ order.outlet?.phone || $page.props.outlet?.phone || '0812-3456-7890' }}</p>
      </div>

      <div class="py-2 border-b border-dashed border-black text-[10px] space-y-0.5">
        <div class="flex justify-between"><span>No. Nota:</span><span>{{ order.invoice_code }}</span></div>
        <div class="flex justify-between"><span>Tgl Masuk:</span><span>{{ order.order_date }}</span></div>
        <div class="flex justify-between"><span>Pelanggan:</span><span>{{ order.customer?.name }}</span></div>
        <div class="flex justify-between"><span>Telp:</span><span>{{ order.customer?.phone }}</span></div>
        <div v-if="order.rack" class="flex justify-between font-bold"><span>Lokasi Rak:</span><span>{{ order.rack.rack_code }}</span></div>
      </div>

      <!-- Items -->
      <div class="py-2 border-b border-dashed border-black text-[10px] space-y-1">
        <div v-for="item in order.items" :key="item.id" class="flex justify-between">
          <span>{{ item.quantity }}x {{ item.item_name }}</span>
          <span>{{ formatNumber(item.subtotal) }}</span>
        </div>
      </div>

      <!-- Totals -->
      <div class="py-2 border-b border-dashed border-black text-[10px] space-y-0.5 font-bold">
        <div class="flex justify-between"><span>Total:</span><span>Rp {{ formatNumber(order.grand_total) }}</span></div>
        <div class="flex justify-between"><span>Bayar:</span><span>Rp {{ formatNumber(order.paid_amount) }}</span></div>
        <div class="flex justify-between"><span>Sisa:</span><span>Rp {{ formatNumber(order.grand_total - order.paid_amount) }}</span></div>
      </div>

      <!-- QR Code Tracking -->
      <div class="text-center pt-2">
        <img :src="receipt.qrCode" alt="QR Code Resi" class="w-24 h-24 mx-auto" />
        <p class="text-[9px] mt-1 font-bold">Scan QR Code untuk Cek Progres Cucian</p>
        <p v-if="order.outlet?.receipt_footer || $page.props.outlet?.receipt_footer" class="text-[8px] mt-1 text-slate-700 whitespace-pre-line leading-tight">
          {{ order.outlet?.receipt_footer || $page.props.outlet?.receipt_footer }}
        </p>
        <p class="text-[8px] mt-1 italic">Terima kasih atas kunjungan Anda!</p>
      </div>
    </div>

    <!-- Modal Pelunasan Sisa -->
    <div v-if="showPayModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
      <div class="bg-white rounded-2xl max-w-sm w-full p-5 shadow-2xl space-y-3 text-xs">
        <h3 class="font-bold text-slate-900 text-sm">Pelunasan Tagihan Pesanan</h3>
        <div class="p-3 bg-rose-50 rounded-xl flex justify-between font-bold text-rose-700">
          <span>Sisa Tagihan:</span>
          <span>Rp {{ formatNumber(order.grand_total - order.paid_amount) }}</span>
        </div>
        <form @submit.prevent="submitPayment" class="space-y-3">
          <div>
            <label class="font-semibold block mb-1">Nominal Bayar (Rp)</label>
            <input v-model.number="payForm.amount" type="number" required min="1" class="w-full py-2 px-3 border rounded-xl font-bold" />
          </div>
          <div>
            <label class="font-semibold block mb-1">Metode Pembayaran</label>
            <select v-model="payForm.payment_method" class="w-full py-2 px-3 border rounded-xl font-medium">
              <option value="cash">💵 Tunai (Kasir)</option>
              <option value="qris">📱 QRIS / Transfer Manual</option>
              <option v-if="activeGateway" value="midtrans">⚡ Midtrans Online (QRIS / VA Otomatis)</option>
            </select>
          </div>

          <div v-if="payForm.payment_method === 'midtrans'" class="p-3 bg-indigo-50 border border-indigo-100 rounded-xl text-indigo-900 text-[11px] leading-relaxed">
            Pop-up QRIS Dinamis / Virtual Account Midtrans akan muncul secara otomatis.
          </div>

          <div class="flex gap-2 pt-2">
            <button type="button" @click="showPayModal = false" class="flex-1 py-2 bg-slate-100 rounded-xl">Batal</button>
            <button 
              type="submit" 
              :disabled="loadingPay"
              class="flex-1 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl font-bold transition-all disabled:opacity-50 flex items-center justify-center gap-1.5"
            >
              <span>{{ loadingPay ? 'Memproses...' : (payForm.payment_method === 'midtrans' ? 'Bayar Online' : 'Simpan Pembayaran') }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
  ArrowLeft, Printer, MessageCircle, CreditCard, Boxes 
} from 'lucide-vue-next';
import { payWithMidtrans } from '@/Utils/midtrans';

const props = defineProps({
  order: Object,
  receipt: Object,
  availableRacks: Array,
  autoPrint: Boolean,
});

const showPayModal = ref(false);
const activeGateway = ref(null);
const loadingPay = ref(false);

const payForm = useForm({
  amount: Math.max(0, Number(props.order.grand_total) - Number(props.order.paid_amount)),
  payment_method: 'cash',
});

onMounted(async () => {
  // Auto-print receipt trigger after POS checkout
  const urlParams = new URLSearchParams(window.location.search);
  const shouldAutoPrint = props.autoPrint || urlParams.get('autoprint') === '1' || urlParams.has('autoprint');

  console.log('[AutoPrint] Checking status:', { prop: props.autoPrint, urlParam: urlParams.get('autoprint'), shouldAutoPrint });

  if (shouldAutoPrint) {
    nextTick(() => {
      setTimeout(() => {
        console.log('[AutoPrint] Triggering printThermalReceipt()...');
        printThermalReceipt();
      }, 250);
    });
  }

  try {
    const res = await fetch('/api/payment/active-gateway');
    const data = await res.json();
    if (data.active_gateway && data.active_gateway.is_active) {
      activeGateway.value = data.active_gateway;
    }
  } catch (e) {
    console.error('Failed to load active payment gateway:', e);
  }
});

function formatNumber(num) {
  return Number(num || 0).toLocaleString('id-ID');
}

function formatDateTime(dt) {
  if (!dt) return '-';
  return new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(dt));
}

function cleanPhone(phone) {
  if (!phone) return '';
  let clean = phone.replace(/[^0-9]/g, '');
  if (clean.startsWith('0')) clean = '62' + clean.substring(1);
  return clean;
}

function getPaymentBadge(status) {
  if (status === 'paid') return 'bg-emerald-100 text-emerald-800';
  if (status === 'partial') return 'bg-amber-100 text-amber-800';
  return 'bg-rose-100 text-rose-800';
}

function getOrderStatusBadge(status) {
  const map = {
    'received': 'bg-slate-100 text-slate-700',
    'washing': 'bg-sky-100 text-sky-800',
    'drying': 'bg-cyan-100 text-cyan-800',
    'ironing': 'bg-indigo-100 text-indigo-800',
    'packing': 'bg-purple-100 text-purple-800',
    'ready': 'bg-amber-100 text-amber-800',
    'completed': 'bg-emerald-100 text-emerald-800',
    'cancelled': 'bg-rose-100 text-rose-800',
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

function getWhatsAppText() {
  const status = formatOrderStatus(props.order.order_status);
  const trackUrl = window.location.origin + '/track/' + props.order.invoice_code;
  return `Halo Kak ${props.order.customer?.name}, update pesanan laundry no ${props.order.invoice_code}: Status saat ini [${status}]. Cek progres lengkap: ${trackUrl}`;
}

function printThermalReceipt() {
  window.print();
}

async function submitPayment() {
  if (payForm.payment_method === 'midtrans') {
    loadingPay.value = true;
    try {
      const res = await fetch('/api/payment/snap-token', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify({
          order_id: props.order.id,
          amount: payForm.amount,
        })
      });
      const data = await res.json();

      if (data.status === 'success' && data.data?.snap_token) {
        showPayModal.value = false;
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
          onError: (err) => {
            alert('Pembayaran Gagal atau Dibatalkan.');
          }
        });
      } else {
        alert(data.message || 'Gagal mendapatkan Snap Token Midtrans');
      }
    } catch (e) {
      console.error(e);
      alert('Terjadi kesalahan jaringan.');
    } finally {
      loadingPay.value = false;
    }
  } else {
    payForm.post(`/orders/${props.order.id}/pay`, {
      onSuccess: () => {
        showPayModal.value = false;
      }
    });
  }
}
</script>

<style>
@media print {
  body * {
    visibility: hidden;
  }
  #thermal-receipt, #thermal-receipt * {
    visibility: visible;
  }
  #thermal-receipt {
    position: absolute;
    left: 0;
    top: 0;
    width: 58mm;
  }
}
</style>

