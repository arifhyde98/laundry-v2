<template>
  <AppLayout title="Profil Outlet & Header Struk">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
          <Store class="w-6 h-6 text-sky-600" />
          <span>Profil Usaha & Format Struk Kasir</span>
        </h1>
        <p class="text-sm text-slate-500 mt-0.5">
          Atur identitas bisnis laundry, alamat, nomor telepon CS, dan format pencetakan nota kasir thermal.
        </p>
      </div>
    </div>

    <!-- Main Grid: Form Left, Live Receipt Preview Right -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
      <!-- Left Column: Outlet Settings Form -->
      <div class="lg:col-span-7 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-5">
        <form @submit.prevent="submit" class="space-y-4 text-xs">
          <!-- Outlet Name & Phone -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="font-bold text-slate-700 block mb-1">Nama Outlet / Laundry <span class="text-rose-500">*</span></label>
              <input 
                v-model="form.name" 
                required 
                type="text" 
                placeholder="Contoh: Fresh & Clean Laundry" 
                class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-900 text-xs focus:bg-white focus:ring-2 focus:ring-sky-500"
              />
            </div>
            <div>
              <label class="font-bold text-slate-700 block mb-1">Nomor Telepon / WhatsApp CS</label>
              <input 
                v-model="form.phone" 
                type="text" 
                placeholder="0812-3456-7890" 
                class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl font-medium text-slate-900 text-xs focus:bg-white focus:ring-2 focus:ring-sky-500"
              />
            </div>
          </div>

          <!-- Address -->
          <div>
            <label class="font-bold text-slate-700 block mb-1">Alamat Lengkap Outlet</label>
            <textarea 
              v-model="form.address" 
              rows="2" 
              placeholder="Jl. Kaliurang Km 5.5 No. 12, Sleman, Yogyakarta" 
              class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl font-medium text-slate-900 text-xs focus:bg-white focus:ring-2 focus:ring-sky-500"
            ></textarea>
          </div>

          <div class="pt-3 border-t border-slate-100">
            <h3 class="font-bold text-slate-900 text-sm mb-3 flex items-center gap-1.5 text-sky-700">
              <Printer class="w-4 h-4" />
              <span>Kustomisasi Struk Thermal Kasir</span>
            </h3>

            <!-- Receipt Header / Tagline -->
            <div class="space-y-3">
              <div>
                <label class="font-bold text-slate-700 block mb-1">Tagline / Slogan Atas Struk</label>
                <input 
                  v-model="form.receipt_header" 
                  type="text" 
                  placeholder="Cucian Bersih, Wangi, & Higienis" 
                  class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl font-medium text-slate-900 text-xs focus:bg-white focus:ring-2 focus:ring-sky-500"
                />
              </div>

              <!-- Paper Size Selection -->
              <div>
                <label class="font-bold text-slate-700 block mb-1">Ukuran Kertas Thermal Printer</label>
                <div class="grid grid-cols-2 gap-3">
                  <button 
                    type="button" 
                    @click="form.receipt_paper_size = '58mm'"
                    :class="['p-3 rounded-xl border text-center font-bold text-xs transition-all cursor-pointer flex items-center justify-center gap-2', form.receipt_paper_size === '58mm' ? 'border-sky-500 bg-sky-50 text-sky-800 ring-2 ring-sky-400/20' : 'border-slate-200 bg-white text-slate-600']"
                  >
                    <span>🖨️ Thermal 58 mm (Standar)</span>
                  </button>
                  <button 
                    type="button" 
                    @click="form.receipt_paper_size = '80mm'"
                    :class="['p-3 rounded-xl border text-center font-bold text-xs transition-all cursor-pointer flex items-center justify-center gap-2', form.receipt_paper_size === '80mm' ? 'border-sky-500 bg-sky-50 text-sky-800 ring-2 ring-sky-400/20' : 'border-slate-200 bg-white text-slate-600']"
                  >
                    <span>🖨️ Thermal 80 mm (Lebar)</span>
                  </button>
                </div>
              </div>

              <!-- Receipt Footer / Terms & Conditions -->
              <div>
                <label class="font-bold text-slate-700 block mb-1">Syarat & Ketentuan Nota (Footer Struk)</label>
                <textarea 
                  v-model="form.receipt_footer" 
                  rows="3" 
                  placeholder="1. Komplain maks 1x24 jam setelah cucian diambil.&#10;2. Cucian tidak diambil > 30 hari di luar tanggung jawab kami." 
                  class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl font-medium text-slate-900 text-xs focus:bg-white focus:ring-2 focus:ring-sky-500"
                ></textarea>
                <p class="text-[10px] text-slate-400 mt-1 italic">
                  *Teks ini akan otomatis dicetak di bagian paling bawah struk nota kasir.
                </p>
              </div>
            </div>
          </div>

          <div class="pt-4 border-t border-slate-100 flex justify-end">
            <button 
              type="submit" 
              :disabled="form.processing"
              class="px-6 py-2.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl font-bold text-xs shadow-md shadow-sky-600/20 transition-all cursor-pointer disabled:opacity-50 flex items-center gap-2"
            >
              <CheckCircle2 class="w-4 h-4" />
              <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}</span>
            </button>
          </div>
        </form>
      </div>

      <!-- Right Column: Live Receipt Simulator Preview -->
      <div class="lg:col-span-5 space-y-3">
        <div class="flex items-center justify-between px-1">
          <span class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
            <Sparkles class="w-4 h-4 text-amber-500" />
            <span>Pratinjau Struk Kasir (Live Preview)</span>
          </span>
          <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-slate-200 text-slate-700 font-bold uppercase">
            {{ form.receipt_paper_size }}
          </span>
        </div>

        <!-- Simulated Paper Receipt Container -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-lg relative overflow-hidden flex flex-col items-center">
          <!-- Thermal Paper Simulation -->
          <div 
            :class="[
              'bg-amber-50/40 p-4 rounded-xl border border-slate-200/80 font-mono text-[11px] text-slate-900 leading-tight shadow-inner w-full',
              form.receipt_paper_size === '58mm' ? 'max-w-[280px]' : 'max-w-[340px]'
            ]"
          >
            <!-- Header -->
            <div class="text-center pb-2 border-b border-dashed border-slate-400 space-y-0.5">
              <h4 class="font-extrabold text-sm uppercase tracking-tight text-slate-900">
                {{ form.name || 'NAMA OUTLET ANDA' }}
              </h4>
              <p v-if="form.receipt_header" class="text-[9px] font-semibold text-slate-600 italic">
                "{{ form.receipt_header }}"
              </p>
              <p class="text-[10px] text-slate-600 mt-1">
                {{ form.address || 'Alamat Outlet Belum Diisi' }}
              </p>
              <p class="text-[10px] text-slate-600">
                Telp/WA: {{ form.phone || '-' }}
              </p>
            </div>

            <!-- Transaction Dummy Info -->
            <div class="py-2 border-b border-dashed border-slate-400 text-[10px] space-y-0.5 text-slate-700">
              <div class="flex justify-between"><span>No. Nota:</span><span class="font-bold">INV-20260901-0001</span></div>
              <div class="flex justify-between"><span>Tgl:</span><span>01/09/2026 10:30</span></div>
              <div class="flex justify-between"><span>Pelanggan:</span><span class="font-bold">Budi Santoso</span></div>
              <div class="flex justify-between"><span>Status:</span><span class="font-bold text-emerald-700">LUNAS</span></div>
            </div>

            <!-- Items Dummy -->
            <div class="py-2 border-b border-dashed border-slate-400 text-[10px] space-y-1">
              <div class="flex justify-between">
                <span>3.0 Kg Cuci Kiloan Reguler</span>
                <span class="font-bold">Rp 21.000</span>
              </div>
              <div class="flex justify-between">
                <span>1 Pcs Cuci Bedcover Besar</span>
                <span class="font-bold">Rp 35.000</span>
              </div>
            </div>

            <!-- Summary Totals -->
            <div class="py-2 border-b border-dashed border-slate-400 text-[10px] space-y-0.5 font-bold">
              <div class="flex justify-between"><span>Total:</span><span>Rp 56.000</span></div>
              <div class="flex justify-between"><span>Bayar (Cash):</span><span>Rp 60.000</span></div>
              <div class="flex justify-between text-emerald-700"><span>Kembalian:</span><span>Rp 4.000</span></div>
            </div>

            <!-- Simulated QR Code -->
            <div class="text-center pt-2.5">
              <div class="w-16 h-16 bg-white border border-slate-300 rounded p-1 mx-auto shadow-xs flex items-center justify-center">
                <QrCode class="w-14 h-14 text-slate-900" />
              </div>
              <p class="text-[8px] font-bold text-slate-600 mt-1">Scan QR untuk Cek Resi Online</p>

              <!-- Dynamic Footer T&C -->
              <p v-if="form.receipt_footer" class="text-[8px] text-slate-600 mt-1.5 whitespace-pre-line leading-tight text-left bg-slate-100/70 p-1.5 rounded border border-slate-200/60">
                {{ form.receipt_footer }}
              </p>
              <p class="text-[8px] text-slate-400 mt-1 italic">Terima kasih atas kepercayaan Anda!</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Danger Zone / Dev Area -->
    <div class="mt-8 bg-rose-50/50 border border-rose-200 p-6 rounded-2xl shadow-sm">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h3 class="font-bold text-rose-700 text-base flex items-center gap-2">
            <AlertTriangle class="w-5 h-5" />
            <span>Zona Bahaya (Dev Mode) - Reset Data Transaksi</span>
          </h3>
          <p class="text-xs text-rose-600/80 mt-1 max-w-2xl">
            Aksi ini akan menghapus <strong>SELURUH</strong> riwayat pesanan, struk kasir, data pengeluaran harian, laporan Z-report, komisi, dan shift secara permanen menjadi kosong (Rp 0). Data Master (Pelanggan, Rak, Tarif Layanan, Karyawan) <strong>TIDAK</strong> akan ikut terhapus.
          </p>
        </div>
        <button 
          @click="confirmResetTransactions"
          class="shrink-0 px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-bold text-xs shadow-md shadow-rose-600/20 transition-all flex items-center gap-2"
        >
          <Trash2 class="w-4 h-4" />
          <span>Reset Semua Transaksi Sekarang</span>
        </button>
      </div>
    </div>

    <!-- Custom Confirmation Modal -->
    <ConfirmModal 
      :show="showResetModal"
      title="Peringatan Bahaya!"
      message="Apakah Anda YAKIN ingin MENGHAPUS BERSIH seluruh data Transaksi, Pengeluaran, Shift Kasir, dan Riwayat Keuangan?&#10;&#10;Aksi ini TIDAK BISA dibatalkan."
      confirmText="Ya, Hapus Semua"
      type="danger"
      @confirm="executeReset"
      @cancel="showResetModal = false"
    />
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { 
  Store, Printer, Sparkles, QrCode, CheckCircle2, AlertTriangle, Trash2 
} from 'lucide-vue-next';

const props = defineProps({
  outlet: {
    type: Object,
    default: () => ({})
  }
});

const showResetModal = ref(false);

const form = useForm({
  name: props.outlet?.name || 'Laundry Express',
  phone: props.outlet?.phone || '0812-3456-7890',
  address: props.outlet?.address || 'Jl. Utama Laundry No. 1, Kota',
  receipt_header: props.outlet?.receipt_header || 'Cucian Bersih, Wangi, & Higienis',
  receipt_footer: props.outlet?.receipt_footer || 'Perhatian: 1. Komplain maks 1x24 jam setelah barang diambil. 2. Cucian tidak diambil > 30 hari di luar tanggung jawab kami.',
  receipt_paper_size: props.outlet?.receipt_paper_size || '58mm',
});

function submit() {
  form.put('/outlet', {
    preserveScroll: true,
  });
}

function confirmResetTransactions() {
  showResetModal.value = true;
}

function executeReset() {
  showResetModal.value = false;
  router.post('/outlet/reset-transactions');
}
</script>

