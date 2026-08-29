<template>
  <AppLayout title="Manajemen Staf & Pengguna">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Manajemen Akun Staf & Hak Akses</h1>
        <p class="text-sm text-slate-500 mt-0.5">Kelola akun kasir, operator workshop cuci, kurir, dan wewenang sistem.</p>
      </div>
      <button @click="openCreateModal" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-semibold text-sm shadow-md shadow-sky-500/25 transition-all">
        <Plus class="w-4 h-4" />
        <span>Tambah Staf Baru</span>
      </button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
          <thead class="bg-slate-50 text-slate-400 uppercase text-[11px] font-bold tracking-wider">
            <tr>
              <th class="px-5 py-3.5">Nama & Username</th>
              <th class="px-5 py-3.5">Role / Wewenang</th>
              <th class="px-5 py-3.5">Nomor HP</th>
              <th class="px-5 py-3.5">Status Akun</th>
              <th class="px-5 py-3.5">Total Transaksi Dibuat</th>
              <th class="px-5 py-3.5 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="u in users" :key="u.id" class="hover:bg-slate-50/80 transition-colors">
              <td class="px-5 py-4">
                <p class="font-bold text-slate-900">{{ u.name }}</p>
                <p class="text-xs text-slate-400">@{{ u.username }}</p>
              </td>
              <td class="px-5 py-4">
                <span :class="['px-2.5 py-1 rounded-lg text-xs font-bold capitalize', getRoleBadge(u.role)]">
                  {{ u.role }}
                </span>
              </td>
              <td class="px-5 py-4 text-xs font-medium text-slate-600">{{ u.phone || '-' }}</td>
              <td class="px-5 py-4">
                <span :class="['px-2 py-0.5 rounded text-xs font-bold', u.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800']">
                  {{ u.is_active ? 'Aktif' : 'Non-aktif' }}
                </span>
              </td>
              <td class="px-5 py-4 font-semibold text-slate-700">{{ u.orders_count || 0 }} Order</td>
              <td class="px-5 py-4 text-right">
                <div class="flex justify-end items-center gap-1.5">
                  <button @click="openEditModal(u)" class="inline-flex items-center gap-1 text-xs font-bold text-sky-600 bg-sky-50 px-2.5 py-1.5 rounded-lg hover:bg-sky-100 transition-colors">
                    <Edit class="w-3.5 h-3.5" />
                    <span>Edit</span>
                  </button>
                  <button v-if="u.role !== 'owner'" @click="deleteItem(u)" class="inline-flex items-center gap-1 text-xs font-bold text-rose-600 bg-rose-50 px-2.5 py-1.5 rounded-lg hover:bg-rose-100 transition-colors">
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Form -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
      <div class="bg-white rounded-2xl max-w-sm w-full p-5 shadow-2xl space-y-3 text-xs">
        <h3 class="font-bold text-slate-900 text-sm">{{ editingUser ? 'Edit Akun Staf' : 'Tambah Staf Baru' }}</h3>
        <form @submit.prevent="submitUser" class="space-y-3">
          <div>
            <label class="font-semibold block mb-1">Nama Lengkap</label>
            <input v-model="form.name" required class="w-full py-2 px-3 border rounded-xl font-bold" />
          </div>
          <div>
            <label class="font-semibold block mb-1">Username Login</label>
            <input v-model="form.username" required class="w-full py-2 px-3 border rounded-xl font-bold" />
          </div>
          <div>
            <label class="font-semibold block mb-1">Role / Peran</label>
            <select v-model="form.role" class="w-full py-2 px-3 border rounded-xl font-semibold capitalize">
              <option value="owner">Owner / Pemilik (Akses Penuh)</option>
              <option value="cashier">Kasir POS (Transaksi, Pelanggan, Shift)</option>
              <option value="washer">Operator Workshop (Cuci, Setrika, Packing)</option>
              <option value="courier">Kurir (Antar & Jemput)</option>
            </select>
          </div>
          <div>
            <label class="font-semibold block mb-1">Nomor WhatsApp / HP</label>
            <input v-model="form.phone" placeholder="08xxx" class="w-full py-2 px-3 border rounded-xl" />
          </div>
          <div>
            <label class="font-semibold block mb-1">Password {{ editingUser ? '(Kosongkan jika tidak diubah)' : '' }}</label>
            <input v-model="form.password" type="password" :required="!editingUser" minlength="6" placeholder="Minimal 6 karakter" class="w-full py-2 px-3 border rounded-xl" />
          </div>
          <div v-if="editingUser">
            <label class="flex items-center gap-2">
              <input type="checkbox" v-model="form.is_active" class="rounded text-sky-600" />
              <span class="font-semibold text-slate-700">Akun Aktif</span>
            </label>
          </div>
          <div class="flex gap-2 pt-2">
            <button type="button" @click="showModal = false" class="flex-1 py-2 bg-slate-100 rounded-xl font-semibold">Batal</button>
            <button type="submit" class="flex-1 py-2 bg-sky-600 text-white rounded-xl font-bold">Simpan</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Custom Confirmation Modal -->
    <ConfirmModal 
      :show="showDeleteModal"
      title="Hapus Akun Staf"
      :message="`Apakah Anda yakin ingin menghapus akun ${itemToDelete?.name} (@${itemToDelete?.username})?\n\nAksi ini tidak dapat dibatalkan.`"
      confirmText="Ya, Hapus"
      type="danger"
      @confirm="executeDelete"
      @cancel="showDeleteModal = false"
    />
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { Plus, Edit, Trash2 } from 'lucide-vue-next';

const props = defineProps({
  users: Array,
});

const showModal = ref(false);
const showDeleteModal = ref(false);
const editingUser = ref(null);
const itemToDelete = ref(null);

const form = useForm({
  name: '',
  username: '',
  role: 'cashier',
  phone: '',
  password: '',
  is_active: true,
});

function getRoleBadge(role) {
  const map = {
    'owner': 'bg-purple-100 text-purple-800',
    'cashier': 'bg-sky-100 text-sky-800',
    'washer': 'bg-cyan-100 text-cyan-800',
    'courier': 'bg-amber-100 text-amber-800',
  };
  return map[role] || 'bg-slate-100 text-slate-700';
}

function openCreateModal() {
  editingUser.value = null;
  form.reset();
  form.is_active = true;
  showModal.value = true;
}

function openEditModal(u) {
  editingUser.value = u;
  form.name = u.name;
  form.username = u.username;
  form.role = u.role;
  form.phone = u.phone;
  form.password = '';
  form.is_active = u.is_active;
  showModal.value = true;
}

function submitUser() {
  if (editingUser.value) {
    form.put(`/users/${editingUser.value.id}`, {
      onSuccess: () => { showModal.value = false; }
    });
  } else {
    form.post('/users', {
      onSuccess: () => { showModal.value = false; }
    });
  }
}

function deleteItem(u) {
  itemToDelete.value = u;
  showDeleteModal.value = true;
}

function executeDelete() {
  if (itemToDelete.value) {
    router.delete(`/users/${itemToDelete.value.id}`);
    showDeleteModal.value = false;
    itemToDelete.value = null;
  }
}
</script>

