<template>
  <AppLayout title="Master Inventaris & Bahan Kimia">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
          <FlaskConical class="w-6 h-6 text-sky-600" />
          <span>Master Bahan Baku & Resep Kimia</span>
        </h1>
        <p class="text-sm text-slate-500 mt-0.5">
          Kelola stok deterjen, parfum, plastik packing, dan aturan takaran otomatis pemotongan per kg cucian.
        </p>
      </div>
      <button 
        @click="openCreateModal" 
        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-sm shadow-md shadow-sky-600/20 transition-all cursor-pointer"
      >
        <Plus class="w-4 h-4" />
        <span>Tambah Item Bahan</span>
      </button>
    </div>

    <!-- Stats KPI Overview Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-sky-50 flex items-center justify-center text-sky-600 shrink-0">
          <Boxes class="w-5 h-5" />
        </div>
        <div>
          <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Item</p>
          <p class="text-xl font-bold text-slate-900">{{ items.length }} Produk</p>
        </div>
      </div>

      <div class="bg-white p-4 rounded-2xl border border-rose-200/80 shadow-xs flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-rose-50 flex items-center justify-center text-rose-600 shrink-0">
          <AlertTriangle class="w-5 h-5" />
        </div>
        <div>
          <p class="text-xs font-semibold text-rose-600 uppercase tracking-wider">Stok Menipis</p>
          <p class="text-xl font-bold text-rose-700">{{ stats.lowStock }} Item</p>
        </div>
      </div>

      <div class="bg-white p-4 rounded-2xl border border-indigo-200/80 shadow-xs flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 shrink-0">
          <Sparkles class="w-5 h-5" />
        </div>
        <div>
          <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wider">Resep Otomatis</p>
          <p class="text-xl font-bold text-indigo-700">{{ stats.autoRecipes }} Resep</p>
        </div>
      </div>

      <div class="bg-white p-4 rounded-2xl border border-emerald-200/80 shadow-xs flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 shrink-0">
          <BadgePercent class="w-5 h-5" />
        </div>
        <div>
          <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Nilai Estimasi Stok</p>
          <p class="text-lg font-bold text-emerald-700">Rp {{ formatNumber(stats.totalAssetValue) }}</p>
        </div>
      </div>
    </div>

    <!-- Category Tabs & Search -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
      <div class="flex items-center gap-1.5 overflow-x-auto w-full md:w-auto p-1 bg-slate-100 rounded-xl text-xs font-bold">
        <button 
          v-for="cat in categories" 
          :key="cat.value"
          @click="activeCategory = cat.value"
          :class="[
            'px-3.5 py-1.5 rounded-lg transition-all cursor-pointer whitespace-nowrap',
            activeCategory === cat.value ? 'bg-white text-sky-700 shadow-xs font-extrabold' : 'text-slate-500 hover:text-slate-800'
          ]"
        >
          {{ cat.label }}
        </button>
      </div>

      <div class="relative w-full md:w-72">
        <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Cari nama bahan, kemasan..."
          class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-sky-500 transition-all"
        />
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
          <thead class="bg-slate-50 text-slate-400 uppercase text-[11px] font-bold tracking-wider border-b border-slate-100">
            <tr>
              <th class="px-5 py-3.5">Nama Bahan / Item</th>
              <th class="px-5 py-3.5">Kategori</th>
              <th class="px-5 py-3.5">Sisa Stok Fisik</th>
              <th class="px-5 py-3.5">Resep Takaran per Kg</th>
              <th class="px-5 py-3.5">Biaya / Unit</th>
              <th class="px-5 py-3.5">Status</th>
              <th class="px-5 py-3.5 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="item in filteredItems" :key="item.id" class="hover:bg-slate-50/80 transition-colors">
              <td class="px-5 py-4">
                <div class="font-bold text-slate-900 text-sm">{{ item.name }}</div>
                <div class="text-[11px] text-slate-400">Min. Alert: {{ item.minimum_stock }} {{ item.unit }}</div>
              </td>
              <td class="px-5 py-4">
                <span :class="['px-2.5 py-1 rounded-lg text-xs font-bold uppercase tracking-wider', getCategoryBadge(item.category)]">
                  {{ formatCategory(item.category) }}
                </span>
              </td>
              <td class="px-5 py-4">
                <div class="flex items-center gap-2">
                  <span class="text-sm font-extrabold text-slate-900 font-mono">{{ formatNumber(item.stock) }} {{ item.unit }}</span>
                  <button 
                    @click="openStockAdjustModal(item)" 
                    class="p-1 rounded-md bg-slate-100 hover:bg-sky-100 hover:text-sky-700 text-slate-500 transition-colors cursor-pointer"
                    title="Tambah / Kurangi Stok Cepat"
                  >
                    <Plus class="w-3 h-3" />
                  </button>
                </div>
              </td>
              <td class="px-5 py-4">
                <div v-if="item.recipe" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 font-bold text-xs">
                  <Sparkles class="w-3 h-3" />
                  <span>{{ item.recipe.dosage_per_kg }} {{ item.unit }} / kg cucian</span>
                </div>
                <span v-else class="text-xs text-slate-400 italic">Manual (Tanpa Resep)</span>
              </td>
              <td class="px-5 py-4 font-mono text-xs text-slate-700">
                Rp {{ formatNumber(item.cost_price) }} / {{ item.unit }}
              </td>
              <td class="px-5 py-4">
                <span 
                  v-if="Number(item.stock) <= Number(item.minimum_stock)" 
                  class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800"
                >
                  <AlertTriangle class="w-3 h-3" />
                  <span>Stok Menipis</span>
                </span>
                <span 
                  v-else 
                  class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800"
                >
                  <span>Stok Aman</span>
                </span>
              </td>
              <td class="px-5 py-4 text-right">
                <div class="flex items-center justify-end gap-1.5">
                  <button 
                    @click="openEditModal(item)" 
                    class="inline-flex items-center gap-1 text-xs font-bold text-sky-600 bg-sky-50 px-2.5 py-1.5 rounded-lg hover:bg-sky-100 transition-colors cursor-pointer"
                  >
                    <Pencil class="w-3.5 h-3.5" />
                    <span>Edit</span>
                  </button>
                  <button 
                    @click="deleteItem(item)" 
                    class="inline-flex items-center gap-1 text-xs font-bold text-rose-600 bg-rose-50 px-2.5 py-1.5 rounded-lg hover:bg-rose-100 transition-colors cursor-pointer"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="filteredItems.length === 0">
              <td colspan="7" class="text-center py-12 text-slate-400">
                <Boxes class="w-10 h-10 mx-auto text-slate-300 mb-2 stroke-[1.5]" />
                <p class="font-bold text-slate-600 text-sm">Tidak ada data item inventaris</p>
                <p class="text-xs text-slate-400 mt-0.5">Silakan tambahkan bahan baku baru melalui tombol di atas.</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Form (Tambah / Edit Item & Resep) -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
      <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <h3 class="font-bold text-slate-900 text-base">{{ editingItem ? 'Edit Item Inventaris' : 'Tambah Bahan Baru' }}</h3>
          <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">✕</button>
        </div>

        <form @submit.prevent="submitItem" class="space-y-3 text-xs">
          <div>
            <label class="font-bold text-slate-700 block mb-1">Nama Item / Bahan</label>
            <input v-model="form.name" required placeholder="Contoh: Deterjen Liquid Premium" class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-900 focus:bg-white" />
          </div>

          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="font-bold text-slate-700 block mb-1">Kategori</label>
              <select v-model="form.category" class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl font-medium">
                <option value="chemical">Bahan Kimia / Sabun</option>
                <option value="packaging">Kemasan / Plastik</option>
                <option value="equipment">Perlengkapan</option>
                <option value="other">Lainnya</option>
              </select>
            </div>
            <div>
              <label class="font-bold text-slate-700 block mb-1">Satuan</label>
              <input v-model="form.unit" required placeholder="ml, pcs, kg, roll" class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:bg-white" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="font-bold text-slate-700 block mb-1">Stok Awal Fisik</label>
              <input v-model.number="form.stock" type="number" step="any" min="0" required class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl font-bold focus:bg-white" />
            </div>
            <div>
              <label class="font-bold text-slate-700 block mb-1">Batas Min. Alert</label>
              <input v-model.number="form.minimum_stock" type="number" step="any" min="0" required class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:bg-white" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="font-bold text-slate-700 block mb-1">Harga Beli / Satuan (Rp)</label>
              <input v-model.number="form.cost_price" type="number" min="0" class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:bg-white" />
            </div>
            <div>
              <label class="font-bold text-indigo-700 block mb-1">Resep Potong per 1 Kg Cucian</label>
              <input v-model.number="form.dosage_per_kg" type="number" step="any" min="0" placeholder="0 (Opsional)" class="w-full py-2 px-3 bg-indigo-50/50 border border-indigo-200 rounded-xl font-bold text-indigo-900 focus:bg-white" />
            </div>
          </div>
          <p class="text-[10px] text-slate-400 italic">*Isi takaran per kg (misal 25 ml) jika ingin stok ini berkurang otomatis tiap kali cucian kiloan diproses di workshop.</p>

          <div class="flex gap-2.5 pt-3 border-t border-slate-100">
            <button type="button" @click="showModal = false" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold transition cursor-pointer">Batal</button>
            <button type="submit" class="flex-1 py-2.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl font-bold shadow-md shadow-sky-600/20 transition cursor-pointer">Simpan</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Cepat Tambah / Kurang Stok -->
    <div v-if="showAdjustModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
      <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <h3 class="font-bold text-slate-900 text-sm">Penyesuaian Stok: {{ activeAdjustItem?.name }}</h3>
          <button @click="showAdjustModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">✕</button>
        </div>

        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs">
          <p class="text-slate-500">Stok Saat Ini: <strong class="text-slate-900">{{ formatNumber(activeAdjustItem?.stock) }} {{ activeAdjustItem?.unit }}</strong></p>
        </div>

        <form @submit.prevent="submitAdjust" class="space-y-3 text-xs">
          <div>
            <label class="font-bold text-slate-700 block mb-1">Jumlah Masuk (+) / Keluar (-)</label>
            <input v-model.number="adjustForm.quantity" type="number" step="any" required placeholder="Contoh: 5000 (masuk) atau -500 (rusak)" class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-900" />
          </div>
          <div>
            <label class="font-bold text-slate-700 block mb-1">Catatan</label>
            <input v-model="adjustForm.notes" placeholder="Restok belanja mingguan" class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl" />
          </div>
          <div class="flex gap-2.5 pt-2">
            <button type="button" @click="showAdjustModal = false" class="flex-1 py-2 bg-slate-100 rounded-xl font-bold">Batal</button>
            <button type="submit" class="flex-1 py-2 bg-sky-600 text-white rounded-xl font-bold">Update Stok</button>
          </div>
        </form>
      </div>
    </div>
    <!-- Custom Confirmation Modal -->
    <ConfirmModal 
      :show="showDeleteModal"
      title="Hapus Master Inventaris"
      :message="`Apakah Anda yakin ingin menghapus item ${itemToDelete?.name}?\n\nAksi ini tidak dapat dibatalkan.`"
      confirmText="Ya, Hapus"
      type="danger"
      @confirm="executeDelete"
      @cancel="showDeleteModal = false"
    />
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { 
  FlaskConical, Plus, Search, Boxes, AlertTriangle, 
  Sparkles, BadgePercent, Pencil, Trash2 
} from 'lucide-vue-next';

const props = defineProps({
  items: {
    type: Array,
    default: () => []
  }
});

const showModal = ref(false);
const showAdjustModal = ref(false);
const showDeleteModal = ref(false);
const editingItem = ref(null);
const activeAdjustItem = ref(null);
const itemToDelete = ref(null);
const activeCategory = ref('all');
const searchQuery = ref('');

const categories = [
  { label: 'Semua Kategori', value: 'all' },
  { label: 'Bahan Kimia & Sabun', value: 'chemical' },
  { label: 'Kemasan & Plastik', value: 'packaging' },
  { label: 'Perlengkapan', value: 'equipment' },
];

const form = useForm({
  name: '',
  category: 'chemical',
  stock: 0,
  unit: 'ml',
  minimum_stock: 1000,
  cost_price: 0,
  dosage_per_kg: 0,
});

const adjustForm = useForm({
  quantity: 0,
  notes: '',
});

// Stats Calculation
const stats = computed(() => {
  let lowStock = 0;
  let autoRecipes = 0;
  let totalAssetValue = 0;

  props.items.forEach(i => {
    if (Number(i.stock) <= Number(i.minimum_stock)) lowStock++;
    if (i.recipe && Number(i.recipe.dosage_per_kg) > 0) autoRecipes++;
    totalAssetValue += Number(i.stock || 0) * Number(i.cost_price || 0);
  });

  return { lowStock, autoRecipes, totalAssetValue };
});

const filteredItems = computed(() => {
  return props.items.filter(i => {
    if (activeCategory.value !== 'all' && i.category !== activeCategory.value) return false;
    if (searchQuery.value.trim()) {
      const q = searchQuery.value.toLowerCase();
      return i.name.toLowerCase().includes(q) || i.unit.toLowerCase().includes(q);
    }
    return true;
  });
});

function formatNumber(num) {
  return Number(num || 0).toLocaleString('id-ID');
}

function formatCategory(cat) {
  const map = {
    'chemical': 'Bahan Kimia',
    'packaging': 'Kemasan',
    'equipment': 'Alat',
    'other': 'Lainnya'
  };
  return map[cat] || cat;
}

function getCategoryBadge(cat) {
  switch (cat) {
    case 'chemical': return 'bg-sky-50 text-sky-700';
    case 'packaging': return 'bg-purple-50 text-purple-700';
    case 'equipment': return 'bg-amber-50 text-amber-700';
    default: return 'bg-slate-100 text-slate-700';
  }
}

function openCreateModal() {
  editingItem.value = null;
  form.reset();
  showModal.value = true;
}

function openEditModal(item) {
  editingItem.value = item;
  form.name = item.name;
  form.category = item.category;
  form.stock = Number(item.stock);
  form.unit = item.unit;
  form.minimum_stock = Number(item.minimum_stock);
  form.cost_price = Number(item.cost_price);
  form.dosage_per_kg = item.recipe ? Number(item.recipe.dosage_per_kg) : 0;
  showModal.value = true;
}

function openStockAdjustModal(item) {
  activeAdjustItem.value = item;
  adjustForm.quantity = 0;
  adjustForm.notes = '';
  showAdjustModal.value = true;
}

function submitItem() {
  if (editingItem.value) {
    form.put(`/inventory/${editingItem.value.id}`, {
      onSuccess: () => { showModal.value = false; }
    });
  } else {
    form.post('/inventory', {
      onSuccess: () => { showModal.value = false; }
    });
  }
}

function submitAdjust() {
  adjustForm.post(`/inventory/${activeAdjustItem.value.id}/adjust`, {
    onSuccess: () => { showAdjustModal.value = false; }
  });
}

function deleteItem(item) {
  itemToDelete.value = item;
  showDeleteModal.value = true;
}

function executeDelete() {
  if (itemToDelete.value) {
    router.delete(`/inventory/${itemToDelete.value.id}`);
    showDeleteModal.value = false;
    itemToDelete.value = null;
  }
}
</script>

