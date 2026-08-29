<template>
  <AppLayout title="Tarif Layanan">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Katalog & Tarif Layanan</h1>
        <p class="text-sm text-slate-500 mt-0.5">Atur harga layanan kiloan, satuan (bedcover, sepatu, jas), dan durasi pengerjaan.</p>
      </div>
      <button @click="openCreateModal" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-semibold text-sm shadow-md shadow-sky-500/25 transition-all">
        <Plus class="w-4 h-4" />
        <span>Tambah Layanan</span>
      </button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
          <thead class="bg-slate-50 text-slate-400 uppercase text-[11px] font-bold tracking-wider">
            <tr>
              <th class="px-5 py-3.5">Nama Layanan</th>
              <th class="px-5 py-3.5">Satuan</th>
              <th class="px-5 py-3.5">Tarif Harga</th>
              <th class="px-5 py-3.5">Estimasi Pengerjaan</th>
              <th class="px-5 py-3.5">Status</th>
              <th class="px-5 py-3.5 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="s in services" :key="s.id" class="hover:bg-slate-50/80 transition-colors">
              <td class="px-5 py-4">
                <p class="font-bold text-slate-900">{{ s.name }}</p>
                <p class="text-xs text-slate-400">{{ s.description || '-' }}</p>
              </td>
              <td class="px-5 py-4">
                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-bold text-xs uppercase">{{ s.unit }}</span>
              </td>
              <td class="px-5 py-4 font-bold text-sky-600">
                Rp {{ formatNumber(s.price) }} / {{ s.unit }}
              </td>
              <td class="px-5 py-4 font-medium text-slate-700">
                {{ s.estimated_hours }} Jam ({{ (s.estimated_hours / 24).toFixed(1) }} Hari)
              </td>
              <td class="px-5 py-4">
                <span :class="['px-2 py-0.5 rounded text-xs font-bold', s.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600']">
                  {{ s.is_active ? 'Aktif' : 'Non-aktif' }}
                </span>
              </td>
              <td class="px-5 py-4 text-right">
                <button @click="openPencilModal(s)" class="inline-flex items-center gap-1 text-xs font-bold text-sky-600 bg-sky-50 px-2.5 py-1.5 rounded-lg hover:bg-sky-100 transition-colors">
                  <Pencil class="w-3.5 h-3.5" />
                  <span>Pencil</span>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Form -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
      <div class="bg-white rounded-2xl max-w-sm w-full p-5 shadow-2xl space-y-3 text-xs">
        <h3 class="font-bold text-slate-900 text-sm">{{ editingService ? 'Pencil Layanan' : 'Tambah Layanan Baru' }}</h3>
        <form @submit.prevent="submitService" class="space-y-3">
          <div>
            <label class="font-semibold block mb-1">Nama Layanan</label>
            <input v-model="form.name" required class="w-full py-2 px-3 border rounded-xl font-bold" />
          </div>
          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="font-semibold block mb-1">Satuan Hitung</label>
              <select v-model="form.unit" class="w-full py-2 px-3 border rounded-xl">
                <option value="kg">Kiloan (kg)</option>
                <option value="pcs">Satuan (pcs)</option>
                <option value="meter">Meter (meter)</option>
                <option value="pasang">Pasang (pasang)</option>
              </select>
            </div>
            <div>
              <label class="font-semibold block mb-1">Harga (Rp)</label>
              <input v-model.number="form.price" type="number" required min="0" class="w-full py-2 px-3 border rounded-xl font-bold" />
            </div>
          </div>
          <div>
            <label class="font-semibold block mb-1">Estimasi Durasi (Jam)</label>
            <input v-model.number="form.estimated_hours" type="number" required min="1" class="w-full py-2 px-3 border rounded-xl font-bold" />
          </div>
          <div>
            <label class="font-semibold block mb-1">Deskripsi Layanan</label>
            <input v-model="form.description" class="w-full py-2 px-3 border rounded-xl" />
          </div>
          <div v-if="editingService">
            <label class="flex items-center gap-2">
              <input type="checkbox" v-model="form.is_active" class="rounded text-sky-600" />
              <span class="font-semibold text-slate-700">Layanan Aktif</span>
            </label>
          </div>
          <div class="flex gap-2 pt-2">
            <button type="button" @click="showModal = false" class="flex-1 py-2 bg-slate-100 rounded-xl font-semibold">Batal</button>
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
import { Plus, Pencil } from 'lucide-vue-next';

const props = defineProps({
  services: Array,
});

const showModal = ref(false);
const editingService = ref(null);

const form = useForm({
  name: '',
  unit: 'kg',
  price: 0,
  estimated_hours: 72,
  description: '',
  is_active: true,
});

function formatNumber(num) {
  return Number(num || 0).toLocaleString('id-ID');
}

function openCreateModal() {
  editingService.value = null;
  form.reset();
  form.is_active = true;
  showModal.value = true;
}

function openPencilModal(s) {
  editingService.value = s;
  form.name = s.name;
  form.unit = s.unit;
  form.price = Number(s.price);
  form.estimated_hours = s.estimated_hours;
  form.description = s.description;
  form.is_active = s.is_active;
  showModal.value = true;
}

function submitService() {
  if (editingService.value) {
    form.put(`/services/${editingService.value.id}`, {
      onSuccess: () => { showModal.value = false; }
    });
  } else {
    form.post('/services', {
      onSuccess: () => { showModal.value = false; }
    });
  }
}
</script>

