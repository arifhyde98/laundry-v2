<template>
  <AppLayout title="Garansi & Komplain Cuci Ulang">
    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
          <RotateCcw class="w-6 h-6 text-indigo-600" />
          <span>Garansi Cuci Ulang & Komplain</span>
        </h1>
        <p class="text-sm text-slate-500 mt-0.5">
          Pusat kendali komplain pelanggan, penanganan retur pencucian bebas biaya, dan jaminan kepuasan.
        </p>
      </div>
      <button 
        @click="openNewModal" 
        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-md shadow-indigo-600/20 transition-all cursor-pointer"
      >
        <Plus class="w-4 h-4" />
        <span>Buat Tiket Garansi</span>
      </button>
    </div>

    <!-- Stats Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600 shrink-0">
          <FileText class="w-5 h-5" />
        </div>
        <div>
          <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Tiket</p>
          <p class="text-xl font-bold text-slate-900">{{ tickets.length }}</p>
        </div>
      </div>

      <div class="bg-white p-4 rounded-2xl border border-amber-200/80 shadow-xs flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 shrink-0">
          <Clock class="w-5 h-5" />
        </div>
        <div>
          <p class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Perlu Tindakan</p>
          <p class="text-xl font-bold text-amber-700">{{ stats.pending }}</p>
        </div>
      </div>

      <div class="bg-white p-4 rounded-2xl border border-sky-200/80 shadow-xs flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-sky-50 flex items-center justify-center text-sky-600 shrink-0">
          <RotateCcw class="w-5 h-5" />
        </div>
        <div>
          <p class="text-xs font-semibold text-sky-600 uppercase tracking-wider">Sedang Dicuci</p>
          <p class="text-xl font-bold text-sky-700">{{ stats.processing }}</p>
        </div>
      </div>

      <div class="bg-white p-4 rounded-2xl border border-emerald-200/80 shadow-xs flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 shrink-0">
          <CheckCircle2 class="w-5 h-5" />
        </div>
        <div>
          <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Terselesaikan</p>
          <p class="text-xl font-bold text-emerald-700">{{ stats.resolved }}</p>
        </div>
      </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
      <!-- Status Tabs -->
      <div class="flex items-center gap-1.5 overflow-x-auto w-full md:w-auto p-1 bg-slate-100 rounded-xl text-xs font-bold">
        <button 
          v-for="tab in tabOptions" 
          :key="tab.value"
          @click="activeTab = tab.value"
          :class="[
            'px-3.5 py-1.5 rounded-lg transition-all cursor-pointer whitespace-nowrap',
            activeTab === tab.value ? 'bg-white text-indigo-700 shadow-xs font-extrabold' : 'text-slate-500 hover:text-slate-800'
          ]"
        >
          {{ tab.label }}
          <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px]" :class="activeTab === tab.value ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-200 text-slate-600'">
            {{ tab.count }}
          </span>
        </button>
      </div>

      <!-- Live Search -->
      <div class="relative w-full md:w-72">
        <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Cari kode tiket, invoice, pelanggan..."
          class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-500 transition-all"
        />
      </div>
    </div>

    <!-- Main Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
          <thead class="bg-slate-50 text-slate-400 uppercase text-[11px] font-bold tracking-wider border-b border-slate-100">
            <tr>
              <th class="px-5 py-3.5">Tiket & Tanggal</th>
              <th class="px-5 py-3.5">Order / Pelanggan</th>
              <th class="px-5 py-3.5">Alasan Keluhan</th>
              <th class="px-5 py-3.5">Status Tiket</th>
              <th class="px-5 py-3.5">Penyelesaian</th>
              <th class="px-5 py-3.5 text-right">Aksi Tindak Lanjut</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="t in filteredTickets" :key="t.id" class="hover:bg-slate-50/80 transition-colors">
              <td class="px-5 py-4">
                <span class="inline-block px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 font-mono font-bold text-xs">
                  {{ t.ticket_code }}
                </span>
                <p class="text-[11px] text-slate-400 mt-1">{{ formatDate(t.created_at) }}</p>
              </td>
              <td class="px-5 py-4">
                <div class="font-bold text-slate-900 text-sm flex items-center gap-1.5">
                  <span>{{ t.order?.customer?.name || 'Pelanggan Umum' }}</span>
                </div>
                <div class="flex items-center gap-2 mt-0.5">
                  <span class="text-xs text-slate-500 font-medium">Inv: {{ t.order?.invoice_code }}</span>
                  <a 
                    v-if="t.order?.customer?.phone" 
                    :href="`https://wa.me/${formatWa(t.order.customer.phone)}?text=Halo%20Kak%20${t.order.customer.name},%20terkait%20garansi%20cuci%20ulang%20tiket%20${t.ticket_code}`"
                    target="_blank"
                    class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 hover:text-emerald-700"
                    title="Hubungi WhatsApp"
                  >
                    <MessageCircle class="w-3 h-3" />
                    <span>WhatsApp</span>
                  </a>
                </div>
              </td>
              <td class="px-5 py-4 max-w-xs">
                <p class="text-xs text-slate-700 font-medium line-clamp-2">{{ t.reason }}</p>
              </td>
              <td class="px-5 py-4">
                <span :class="['inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold', getStatusBadge(t.status)]">
                  <span class="w-1.5 h-1.5 rounded-full" :class="getStatusDot(t.status)"></span>
                  <span>{{ getStatusLabel(t.status) }}</span>
                </span>
              </td>
              <td class="px-5 py-4">
                <div v-if="t.resolution_note" class="text-xs text-slate-600 bg-slate-50 p-2 rounded-lg border border-slate-100 max-w-xs">
                  <span class="font-bold text-slate-700 block text-[10px] uppercase">Catatan:</span>
                  {{ t.resolution_note }}
                </div>
                <div v-else-if="t.status === 'resolved'" class="text-xs text-emerald-600 font-medium">
                  Selesai tanpa catatan khusus.
                </div>
                <span v-else class="text-xs text-slate-400 italic">Belum diselesaikan</span>
              </td>
              <td class="px-5 py-4 text-right">
                <button 
                  @click="openActionModal(t)" 
                  class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 hover:bg-indigo-50 hover:text-indigo-700 transition-colors cursor-pointer"
                >
                  <Sparkles class="w-3.5 h-3.5" />
                  <span>Update Status</span>
                </button>
              </td>
            </tr>

            <!-- Empty State -->
            <tr v-if="filteredTickets.length === 0">
              <td colspan="6" class="text-center py-12 text-slate-400">
                <RotateCcw class="w-10 h-10 mx-auto text-slate-300 mb-2 stroke-[1.5]" />
                <p class="font-bold text-slate-600 text-sm">Tidak ada data tiket komplain</p>
                <p class="text-xs text-slate-400 mt-0.5">Semua cucian pelanggan aman atau filter tidak cocok.</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Buat Tiket Komplain Baru -->
    <div v-if="showNewModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
      <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
              <RotateCcw class="w-4 h-4" />
            </div>
            <h3 class="font-bold text-slate-900 text-base">Buat Tiket Garansi Cuci Ulang</h3>
          </div>
          <button @click="showNewModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
            <XCircle class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="createTicket" class="space-y-4 text-xs">
          <!-- Pilih Transaksi Order -->
          <div>
            <label class="font-bold text-slate-700 block mb-1.5">Pilih Nota Transaksi (Order)</label>
            <select v-model="form.order_id" required class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl font-medium text-slate-800 text-xs focus:bg-white focus:ring-2 focus:ring-indigo-500">
              <option value="" disabled>-- Pilih Invoice / Pelanggan --</option>
              <option v-for="o in recentOrders" :key="o.id" :value="o.id">
                {{ o.invoice_code }} — {{ o.customer?.name || 'Pelanggan' }} ({{ o.total_weight_qty }} kg/pcs - Rp {{ Number(o.grand_total).toLocaleString('id-ID') }})
              </option>
            </select>
          </div>

          <!-- Quick Issue Presets -->
          <div>
            <label class="font-bold text-slate-700 block mb-1.5">Pilih Keluhan Cepat (Klik untuk memilih):</label>
            <div class="flex flex-wrap gap-1.5">
              <button 
                type="button" 
                v-for="preset in reasonPresets" 
                :key="preset"
                @click="form.reason = preset"
                class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-indigo-50 hover:text-indigo-700 text-slate-600 text-[11px] font-semibold transition-colors cursor-pointer"
              >
                + {{ preset }}
              </button>
            </div>
          </div>

          <!-- Alasan Komplain Textarea -->
          <div>
            <label class="font-bold text-slate-700 block mb-1.5">Deskripsi Rinci Keluhan Pelanggan</label>
            <textarea 
              v-model="form.reason" 
              required 
              rows="3" 
              class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl font-medium text-slate-800 text-xs focus:bg-white focus:ring-2 focus:ring-indigo-500" 
              placeholder="Contoh: Baju kemeja putih masih ada noda kopi di kerah, minta cuci ulang khusus noda."
            ></textarea>
          </div>

          <div class="flex gap-2.5 pt-3 border-t border-slate-100">
            <button type="button" @click="showNewModal = false" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold transition cursor-pointer">
              Batal
            </button>
            <button type="submit" class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-md shadow-indigo-600/20 transition cursor-pointer">
              Simpan Tiket Garansi
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Tindak Lanjut / Update Status Tiket -->
    <div v-if="showActionModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
      <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div>
            <span class="text-[10px] uppercase tracking-wider font-bold text-slate-400">Tindak Lanjut Tiket</span>
            <h3 class="font-bold text-slate-900 text-base font-mono text-indigo-700">{{ activeTicket?.ticket_code }}</h3>
          </div>
          <button @click="showActionModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
            <XCircle class="w-5 h-5" />
          </button>
        </div>

        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 text-xs space-y-1">
          <p><strong class="text-slate-500">Pelanggan:</strong> <span class="font-bold text-slate-800">{{ activeTicket?.order?.customer?.name }}</span></p>
          <p><strong class="text-slate-500">Invoice:</strong> <span class="font-medium text-slate-700">{{ activeTicket?.order?.invoice_code }}</span></p>
          <p><strong class="text-slate-500">Keluhan:</strong> <span class="text-rose-600 font-medium">{{ activeTicket?.reason }}</span></p>
        </div>

        <form @submit.prevent="saveAction" class="space-y-4 text-xs">
          <div>
            <label class="font-bold text-slate-700 block mb-1.5">Ubah Status Tindak Lanjut</label>
            <div class="grid grid-cols-2 gap-2">
              <button 
                type="button" 
                @click="actionForm.status = 'pending'"
                :class="['p-2.5 rounded-xl border text-center font-bold transition-all cursor-pointer', actionForm.status === 'pending' ? 'border-amber-500 bg-amber-50 text-amber-800 ring-2 ring-amber-400/20' : 'border-slate-200 bg-white text-slate-600']"
              >
                ⏳ Pending Review
              </button>
              <button 
                type="button" 
                @click="actionForm.status = 'processing'"
                :class="['p-2.5 rounded-xl border text-center font-bold transition-all cursor-pointer', actionForm.status === 'processing' ? 'border-sky-500 bg-sky-50 text-sky-800 ring-2 ring-sky-400/20' : 'border-slate-200 bg-white text-slate-600']"
              >
                🔄 Sedang Dicuci Ulang
              </button>
              <button 
                type="button" 
                @click="actionForm.status = 'resolved'"
                :class="['p-2.5 rounded-xl border text-center font-bold transition-all cursor-pointer', actionForm.status === 'resolved' ? 'border-emerald-500 bg-emerald-50 text-emerald-800 ring-2 ring-emerald-400/20' : 'border-slate-200 bg-white text-slate-600']"
              >
                ✅ Selesai (Resolved)
              </button>
              <button 
                type="button" 
                @click="actionForm.status = 'rejected'"
                :class="['p-2.5 rounded-xl border text-center font-bold transition-all cursor-pointer', actionForm.status === 'rejected' ? 'border-rose-500 bg-rose-50 text-rose-800 ring-2 ring-rose-400/20' : 'border-slate-200 bg-white text-slate-600']"
              >
                ❌ Ditolak (Rejected)
              </button>
            </div>
          </div>

          <div>
            <label class="font-bold text-slate-700 block mb-1.5">Catatan Resolusi Penanganan (Opsional)</label>
            <textarea 
              v-model="actionForm.resolution_note" 
              rows="2" 
              class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-medium text-slate-800 text-xs focus:bg-white focus:ring-2 focus:ring-indigo-500" 
              placeholder="Contoh: Sudah dicuci ulang dan diberi ekstra parfum, siap diambil kembali."
            ></textarea>
          </div>

          <div class="flex gap-2.5 pt-3 border-t border-slate-100">
            <button type="button" @click="showActionModal = false" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold transition cursor-pointer">
              Batal
            </button>
            <button type="submit" class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-md shadow-indigo-600/20 transition cursor-pointer">
              Simpan Resolusi
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
  RotateCcw, Plus, Search, FileText, Clock, 
  CheckCircle2, XCircle, Sparkles, MessageCircle 
} from 'lucide-vue-next';

const props = defineProps({
  tickets: {
    type: Array,
    default: () => []
  },
  recentOrders: {
    type: Array,
    default: () => []
  }
});

const showNewModal = ref(false);
const showActionModal = ref(false);
const activeTicket = ref(null);
const activeTab = ref('all');
const searchQuery = ref('');

const reasonPresets = [
  'Noda Masih Tersisa',
  'Kurang Wangi / Bau Apek',
  'Kusut / Setrika Kurang Rapi',
  'Pakaian Tertukar / Jumlah Kurang',
  'Kelunturan / Kerusakan Pakaian'
];

const form = useForm({
  order_id: '',
  reason: ''
});

const actionForm = useForm({
  status: 'pending',
  resolution_note: ''
});

// Stats
const stats = computed(() => {
  const pending = props.tickets.filter(t => t.status === 'pending').length;
  const processing = props.tickets.filter(t => t.status === 'processing').length;
  const resolved = props.tickets.filter(t => t.status === 'resolved').length;
  const rejected = props.tickets.filter(t => t.status === 'rejected').length;
  return { pending, processing, resolved, rejected };
});

const tabOptions = computed(() => [
  { label: 'Semua Tiket', value: 'all', count: props.tickets.length },
  { label: 'Perlu Review', value: 'pending', count: stats.value.pending },
  { label: 'Sedang Dicuci', value: 'processing', count: stats.value.processing },
  { label: 'Terselesaikan', value: 'resolved', count: stats.value.resolved },
  { label: 'Ditolak', value: 'rejected', count: stats.value.rejected },
]);

const filteredTickets = computed(() => {
  return props.tickets.filter(t => {
    // Tab filter
    if (activeTab.value !== 'all' && t.status !== activeTab.value) {
      return false;
    }

    // Search query
    if (searchQuery.value.trim()) {
      const q = searchQuery.value.toLowerCase();
      const codeMatch = t.ticket_code?.toLowerCase().includes(q);
      const invMatch = t.order?.invoice_code?.toLowerCase().includes(q);
      const custMatch = t.order?.customer?.name?.toLowerCase().includes(q);
      const reasonMatch = t.reason?.toLowerCase().includes(q);
      return codeMatch || invMatch || custMatch || reasonMatch;
    }

    return true;
  });
});

function formatDate(dt) {
  if (!dt) return '-';
  const d = new Date(dt);
  return new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short' }).format(d);
}

function formatWa(phone) {
  if (!phone) return '';
  let cleaned = phone.replace(/[^0-9]/g, '');
  if (cleaned.startsWith('0')) {
    cleaned = '62' + cleaned.substring(1);
  }
  return cleaned;
}

function getStatusBadge(st) {
  switch (st) {
    case 'pending': return 'bg-amber-100 text-amber-800';
    case 'processing': return 'bg-sky-100 text-sky-800';
    case 'resolved': return 'bg-emerald-100 text-emerald-800';
    case 'rejected': return 'bg-rose-100 text-rose-800';
    default: return 'bg-slate-100 text-slate-700';
  }
}

function getStatusDot(st) {
  switch (st) {
    case 'pending': return 'bg-amber-500 animate-pulse';
    case 'processing': return 'bg-sky-500 animate-spin';
    case 'resolved': return 'bg-emerald-500';
    case 'rejected': return 'bg-rose-500';
    default: return 'bg-slate-400';
  }
}

function getStatusLabel(st) {
  switch (st) {
    case 'pending': return 'Pending Review';
    case 'processing': return 'Sedang Dicuci Ulang';
    case 'resolved': return 'Selesai (Resolved)';
    case 'rejected': return 'Ditolak';
    default: return st;
  }
}

function openNewModal() {
  form.reset();
  showNewModal.value = true;
}

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
