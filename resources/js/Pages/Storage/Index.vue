<template>
  <AppLayout title="Manajemen Rak & Lemari">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Visualisasi Lemari & Rak Penyimpanan</h1>
        <p class="text-sm text-slate-500 mt-0.5">Monitoring kapasitas dan penempatan pakaian pelanggan yang siap diambil.</p>
      </div>
      <button @click="showAddModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-semibold text-sm shadow-md shadow-sky-500/25 transition-all">
        <Plus class="w-4 h-4" />
        <span>Tambah Slot Rak</span>
      </button>
    </div>

    <!-- Visual Grid of Racks -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
      <div 
        v-for="rack in racks" 
        :key="rack.id"
        :class="[
          'p-4 rounded-2xl border transition-all flex flex-col justify-between min-h-[140px]',
          rack.orders && rack.orders.length > 0 
            ? 'bg-amber-50/80 border-amber-300 shadow-sm' 
            : 'bg-white border-slate-200/80 hover:border-sky-400 shadow-xs'
        ]"
      >
        <div>
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-bold uppercase tracking-wider px-2 py-0.5 rounded-lg bg-slate-900 text-white">
              {{ rack.rack_code }}
            </span>
            <span :class="['w-2.5 h-2.5 rounded-full', rack.orders && rack.orders.length > 0 ? 'bg-amber-500' : 'bg-emerald-500']"></span>
          </div>
          <p class="text-[11px] text-slate-400 capitalize">Kategori: {{ rack.category }}</p>
        </div>

        <!-- Stored Order details if any -->
        <div v-if="rack.orders && rack.orders.length > 0" class="mt-3 pt-2 border-t border-amber-200 text-xs">
          <div v-for="ord in rack.orders" :key="ord.id" class="space-y-0.5">
            <p class="font-bold text-amber-900 truncate">{{ ord.invoice_code }}</p>
            <p class="text-[11px] text-amber-700 truncate">{{ ord.customer?.name }} ({{ ord.total_weight_qty }} Kg)</p>
          </div>
        </div>

        <div v-else class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between text-xs text-emerald-600 font-semibold">
          <span>Kosong (Siap Pakai)</span>
        </div>
      </div>
    </div>

    <!-- Modal Tambah Slot Rak -->
    <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
      <div class="bg-white rounded-2xl max-w-sm w-full p-5 shadow-2xl space-y-3 text-xs">
        <h3 class="font-bold text-slate-900 text-sm">Tambah Slot Rak / Lemari</h3>
        <form @submit.prevent="submitRack" class="space-y-3">
          <div>
            <label class="font-semibold block mb-1">Kode Slot Rak</label>
            <input v-model="form.rack_code" required placeholder="Contoh: RAK-C1, GANTUNG-03" class="w-full py-2 px-3 border rounded-xl font-bold uppercase" />
          </div>
          <div>
            <label class="font-semibold block mb-1">Kategori Rak</label>
            <select v-model="form.category" class="w-full py-2 px-3 border rounded-xl">
              <option value="regular">Regular (Lipat)</option>
              <option value="hanger">Hanger (Gantungan Jas/Gaun)</option>
              <option value="shoes">Sepatu / Tas</option>
              <option value="express">Express Kilat</option>
            </select>
          </div>
          <div>
            <label class="font-semibold block mb-1">Kapasitas Maksimal (Kantong)</label>
            <input v-model.number="form.capacity" type="number" required min="1" class="w-full py-2 px-3 border rounded-xl font-bold" />
          </div>
          <div class="flex gap-2 pt-2">
            <button type="button" @click="showAddModal = false" class="flex-1 py-2 bg-slate-100 rounded-xl">Batal</button>
            <button type="submit" class="flex-1 py-2 bg-sky-600 text-white rounded-xl font-bold">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Plus } from 'lucide-vue-next';

const props = defineProps({
  racks: Array,
});

const showAddModal = ref(false);

const form = useForm({
  rack_code: '',
  category: 'regular',
  capacity: 10,
});

function submitRack() {
  form.post('/racks', {
    onSuccess: () => {
      showAddModal.value = false;
      form.reset();
    }
  });
}
</script>

