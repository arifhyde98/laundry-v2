<template>
  <AppLayout title="Antrian Workshop Cuci">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Layar Antrian Workshop & Operator</h1>
        <p class="text-sm text-slate-500 mt-0.5">Monitoring dan pemindahan alur pengerjaan cucian dari cuci hingga masuk rak.</p>
      </div>
      <div class="flex items-center gap-2">
        <span class="text-xs font-semibold px-3 py-1.5 rounded-xl bg-sky-50 text-sky-700 border border-sky-100 flex items-center gap-1.5">
          <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
          <span>Live Kanban Flow</span>
        </span>
      </div>
    </div>

    <!-- Kanban Columns Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 items-start overflow-x-auto pb-6">
      <!-- 1. Diterima -->
      <div class="bg-slate-100/80 rounded-2xl p-3.5 border border-slate-200 min-w-[240px]">
        <div class="flex items-center justify-between mb-3 px-1">
          <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">1. Antrian Masuk</span>
          <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-white text-slate-700 shadow-xs">{{ getOrdersByStatus('received').length }}</span>
        </div>
        <div class="space-y-3">
          <div v-for="order in getOrdersByStatus('received')" :key="order.id" class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs space-y-2">
            <div class="flex items-center justify-between">
              <span class="font-bold text-xs text-slate-900">{{ order.invoice_code }}</span>
              <span class="text-[10px] font-semibold text-slate-400">{{ order.total_weight_qty }} Kg</span>
            </div>
            <p class="text-xs font-medium text-slate-700">{{ order.customer?.name }}</p>
            <button @click="advanceStatus(order, 'washing')" class="w-full py-1.5 bg-sky-500 hover:bg-sky-600 text-white rounded-lg text-xs font-semibold transition-all flex items-center justify-center gap-1">
              <span>Mulai Cuci</span>
              <ArrowRight class="w-3.5 h-3.5" />
            </button>
          </div>
        </div>
      </div>

      <!-- 2. Sedang Dicuci -->
      <div class="bg-sky-50/70 rounded-2xl p-3.5 border border-sky-100 min-w-[240px]">
        <div class="flex items-center justify-between mb-3 px-1">
          <span class="text-xs font-bold text-sky-800 uppercase tracking-wider">2. Sedang Dicuci</span>
          <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-white text-sky-800 shadow-xs">{{ getOrdersByStatus('washing').length }}</span>
        </div>
        <div class="space-y-3">
          <div v-for="order in getOrdersByStatus('washing')" :key="order.id" class="bg-white p-3.5 rounded-xl border border-sky-200 shadow-xs space-y-2">
            <div class="flex items-center justify-between">
              <span class="font-bold text-xs text-slate-900">{{ order.invoice_code }}</span>
              <span class="text-[10px] font-semibold text-sky-600">{{ order.total_weight_qty }} Kg</span>
            </div>
            <p class="text-xs font-medium text-slate-700">{{ order.customer?.name }}</p>
            <div class="flex gap-1.5 pt-1">
              <button @click="revertStatus(order, 'received')" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg transition-all" title="Batal & Kembali">
                <Undo2 class="w-3.5 h-3.5" />
              </button>
              <button @click="advanceStatus(order, 'drying')" class="flex-1 py-1.5 bg-cyan-500 hover:bg-cyan-600 text-white rounded-lg text-xs font-semibold transition-all flex items-center justify-center gap-1">
                <span>Pengeringan</span>
                <ArrowRight class="w-3.5 h-3.5" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- 3. Pengeringan -->
      <div class="bg-cyan-50/70 rounded-2xl p-3.5 border border-cyan-100 min-w-[240px]">
        <div class="flex items-center justify-between mb-3 px-1">
          <span class="text-xs font-bold text-cyan-800 uppercase tracking-wider">3. Pengeringan</span>
          <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-white text-cyan-800 shadow-xs">{{ getOrdersByStatus('drying').length }}</span>
        </div>
        <div class="space-y-3">
          <div v-for="order in getOrdersByStatus('drying')" :key="order.id" class="bg-white p-3.5 rounded-xl border border-cyan-200 shadow-xs space-y-2">
            <div class="flex items-center justify-between">
              <span class="font-bold text-xs text-slate-900">{{ order.invoice_code }}</span>
              <span class="text-[10px] font-semibold text-cyan-600">{{ order.total_weight_qty }} Kg</span>
            </div>
            <p class="text-xs font-medium text-slate-700">{{ order.customer?.name }}</p>
            <div class="flex gap-1.5 pt-1">
              <button @click="revertStatus(order, 'washing')" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg transition-all" title="Batal & Kembali">
                <Undo2 class="w-3.5 h-3.5" />
              </button>
              <button @click="advanceStatus(order, 'ironing')" class="flex-1 py-1.5 bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg text-xs font-semibold transition-all flex items-center justify-center gap-1">
                <span>Setrika</span>
                <ArrowRight class="w-3.5 h-3.5" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- 4. Penyetrikaan -->
      <div class="bg-indigo-50/70 rounded-2xl p-3.5 border border-indigo-100 min-w-[240px]">
        <div class="flex items-center justify-between mb-3 px-1">
          <span class="text-xs font-bold text-indigo-800 uppercase tracking-wider">4. Penyetrikaan</span>
          <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-white text-indigo-800 shadow-xs">{{ getOrdersByStatus('ironing').length }}</span>
        </div>
        <div class="space-y-3">
          <div v-for="order in getOrdersByStatus('ironing')" :key="order.id" class="bg-white p-3.5 rounded-xl border border-indigo-200 shadow-xs space-y-2">
            <div class="flex items-center justify-between">
              <span class="font-bold text-xs text-slate-900">{{ order.invoice_code }}</span>
              <span class="text-[10px] font-semibold text-indigo-600">{{ order.total_weight_qty }} Kg</span>
            </div>
            <p class="text-xs font-medium text-slate-700">{{ order.customer?.name }}</p>
            <div class="flex gap-1.5 pt-1">
              <button @click="revertStatus(order, 'drying')" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg transition-all" title="Batal & Kembali">
                <Undo2 class="w-3.5 h-3.5" />
              </button>
              <button @click="advanceStatus(order, 'packing')" class="flex-1 py-1.5 bg-purple-500 hover:bg-purple-600 text-white rounded-lg text-xs font-semibold transition-all flex items-center justify-center gap-1">
                <span>Packing</span>
                <ArrowRight class="w-3.5 h-3.5" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- 5. Dipacking -->
      <div class="bg-purple-50/70 rounded-2xl p-3.5 border border-purple-100 min-w-[240px]">
        <div class="flex items-center justify-between mb-3 px-1">
          <span class="text-xs font-bold text-purple-800 uppercase tracking-wider">5. Dipacking</span>
          <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-white text-purple-800 shadow-xs">{{ getOrdersByStatus('packing').length }}</span>
        </div>
        <div class="space-y-3">
          <div v-for="order in getOrdersByStatus('packing')" :key="order.id" class="bg-white p-3.5 rounded-xl border border-purple-200 shadow-xs space-y-2">
            <div class="flex items-center justify-between">
              <span class="font-bold text-xs text-slate-900">{{ order.invoice_code }}</span>
              <span class="text-[10px] font-semibold text-purple-600">{{ order.total_weight_qty }} Kg</span>
            </div>
            <p class="text-xs font-medium text-slate-700">{{ order.customer?.name }}</p>
            <div class="flex gap-1.5 pt-1">
              <button @click="revertStatus(order, 'ironing')" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg transition-all" title="Batal & Kembali">
                <Undo2 class="w-3.5 h-3.5" />
              </button>
              <button @click="openRackAssignment(order)" class="flex-1 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-semibold transition-all flex items-center justify-center gap-1">
                <span>Pilih Slot Rak</span>
                <Boxes class="w-3.5 h-3.5" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- 6. Siap Diambil di Rak -->
      <div class="bg-amber-50/70 rounded-2xl p-3.5 border border-amber-100 min-w-[240px]">
        <div class="flex items-center justify-between mb-3 px-1">
          <span class="text-xs font-bold text-amber-800 uppercase tracking-wider">6. Siap di Rak</span>
          <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-white text-amber-800 shadow-xs">{{ getOrdersByStatus('ready').length }}</span>
        </div>
        <div class="space-y-3">
          <div v-for="order in getOrdersByStatus('ready')" :key="order.id" class="bg-white p-3.5 rounded-xl border border-amber-200 shadow-xs space-y-2">
            <div class="flex items-center justify-between">
              <span class="font-bold text-xs text-slate-900">{{ order.invoice_code }}</span>
              <span class="text-[10px] font-bold text-amber-700 bg-amber-100 px-1.5 py-0.5 rounded">{{ order.rack?.rack_code || 'Rak ?' }}</span>
            </div>
            <p class="text-xs font-medium text-slate-700">{{ order.customer?.name }}</p>
            <div class="flex gap-1.5 pt-1">
              <button @click="revertStatus(order, 'packing')" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg transition-all" title="Batal & Keluarkan dari Rak">
                <Undo2 class="w-3.5 h-3.5" />
              </button>
              <button @click="advanceStatus(order, 'completed')" class="flex-1 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold transition-all flex items-center justify-center gap-1">
                <CheckCircle class="w-3.5 h-3.5" />
                <span>Diambil Pelanggan</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Assign Slot Rak -->
    <div v-if="showRackModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
      <div class="bg-white rounded-2xl max-w-sm w-full p-5 shadow-2xl space-y-3 text-xs">
        <h3 class="font-bold text-slate-900 text-sm">Penempatan di Lemari / Rak</h3>
        <p class="text-slate-500">Pilih nomor slot rak untuk menyimpan pakaian pesanan <strong>{{ selectedOrder?.invoice_code }}</strong>.</p>
        
        <div>
          <label class="font-semibold block mb-1">Pilih Slot Rak Penyimpanan</label>
          <select v-model="selectedRackId" class="w-full py-2 px-3 border rounded-xl font-bold text-slate-800">
            <option v-for="rack in racks" :key="rack.id" :value="rack.id">
              {{ rack.rack_code }} ({{ rack.category }})
            </option>
          </select>
        </div>

        <div class="flex gap-2 pt-2">
          <button type="button" @click="showRackModal = false" class="flex-1 py-2 bg-slate-100 rounded-xl font-semibold">Batal</button>
          <button type="button" @click="confirmRackAssignment" class="flex-1 py-2 bg-amber-500 text-white rounded-xl font-bold">Simpan & Masuk Rak</button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ArrowRight, Boxes, CheckCircle, Undo2 } from 'lucide-vue-next';

const props = defineProps({
  orders: Array,
  racks: Array,
});

const showRackModal = ref(false);
const selectedOrder = ref(null);
const selectedRackId = ref(props.racks[0]?.id || null);

function getOrdersByStatus(status) {
  return props.orders.filter(o => o.order_status === status);
}

function advanceStatus(order, nextStatus) {
  router.post(`/workstation/${order.id}/status`, {
    status: nextStatus,
  }, {
    preserveScroll: true,
  });
}

function revertStatus(order, prevStatus) {
  if (confirm(`Yakin ingin membatalkan status pesanan ${order.invoice_code} dan mengembalikannya ke tahap sebelumnya? Komisi tahap ini akan dihapus jika ada.`)) {
    advanceStatus(order, prevStatus);
  }
}

function openRackAssignment(order) {
  selectedOrder.value = order;
  showRackModal.value = true;
}

function confirmRackAssignment() {
  if (!selectedOrder.value) return;
  router.post(`/workstation/${selectedOrder.value.id}/status`, {
    status: 'ready',
    rack_id: selectedRackId.value,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      showRackModal.value = false;
    }
  });
}
</script>

