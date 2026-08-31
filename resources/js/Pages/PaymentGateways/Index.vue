<template>
  <AppLayout title="Manajemen Payment Gateway">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
          <CreditCard class="w-6 h-6 text-indigo-600" />
          <span>Integrasi Payment Gateway</span>
        </h1>
        <p class="text-sm text-slate-500 mt-0.5">
          Siapkan jalur pembayaran otomatis (QRIS Dinamis, Virtual Account) menggunakan Midtrans atau Xendit.
        </p>
      </div>
    </div>

    <!-- Webhook Notification Info Card for Clients -->
    <div class="mb-6 p-5 bg-gradient-to-r from-indigo-900 to-slate-900 rounded-2xl text-white shadow-lg relative overflow-hidden">
      <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-indigo-500/20 rounded-full blur-2xl pointer-events-none"></div>
      <div class="relative z-10 space-y-3">
        <div class="flex items-center gap-2 text-indigo-300 font-bold text-xs uppercase tracking-wider">
          <LinkIcon class="w-4 h-4" />
          <span>URL Webhook Callback (Notification URL)</span>
        </div>
        <p class="text-xs text-slate-300 leading-relaxed max-w-3xl">
          Salin URL di bawah ini lalu tempelkan (*paste*) di Dashboard Midtrans Anda pada menu <strong>Settings &gt; Configuration &gt; Payment Notification URL</strong> agar status pembayaran otomatis ter-update saat pelanggan membayar.
        </p>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 pt-1">
          <div class="bg-slate-950/80 px-3.5 py-2.5 rounded-xl font-mono text-xs text-indigo-300 border border-indigo-500/30 flex-1 flex items-center justify-between overflow-x-auto">
            <span>{{ webhookUrl }}</span>
          </div>
          <button 
            @click="copyWebhookUrl" 
            class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 shrink-0 shadow-md"
          >
            <CheckCircle v-if="copied" class="w-4 h-4 text-emerald-300" />
            <Copy v-else class="w-4 h-4" />
            <span>{{ copied ? 'Tersalin!' : 'Salin URL Webhook' }}</span>
          </button>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div 
        v-for="gateway in gateways" 
        :key="gateway.id"
        :class="['bg-white p-6 rounded-2xl border shadow-sm relative overflow-hidden', gateway.is_active ? 'border-indigo-500 shadow-indigo-100' : 'border-slate-200']"
      >
        <!-- Active Badge -->
        <div v-if="gateway.is_active" class="absolute top-0 right-0 bg-indigo-500 text-white text-[10px] font-bold px-3 py-1 rounded-bl-lg tracking-wider uppercase">
          Aktif
        </div>

        <div class="flex items-center gap-4 mb-6">
          <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center font-bold text-xl text-slate-400 uppercase">
            {{ gateway.name.substring(0, 2) }}
          </div>
          <div>
            <h2 class="text-lg font-bold text-slate-900">{{ gateway.display_name }}</h2>
            <span :class="['text-xs font-semibold px-2 py-0.5 rounded', gateway.mode === 'sandbox' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700']">
              Mode: {{ gateway.mode.toUpperCase() }}
            </span>
          </div>
        </div>

        <form @submit.prevent="submit(gateway.id)" class="space-y-4 text-xs">
          <div>
            <label class="font-bold text-slate-700 block mb-1">Server Key / Secret Key</label>
            <input 
              v-model="forms[gateway.id].server_key" 
              type="password" 
              placeholder="Masukkan Server Key..." 
              class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl font-mono focus:ring-2 focus:ring-indigo-500"
            />
          </div>

          <div>
            <label class="font-bold text-slate-700 block mb-1">Client Key / Public Key</label>
            <input 
              v-model="forms[gateway.id].client_key" 
              type="text" 
              placeholder="Masukkan Client Key..." 
              class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl font-mono focus:ring-2 focus:ring-indigo-500"
            />
          </div>

          <div>
            <label class="font-bold text-slate-700 block mb-1">Merchant ID (Opsional)</label>
            <input 
              v-model="forms[gateway.id].merchant_id" 
              type="text" 
              placeholder="G... / M..." 
              class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl font-mono focus:ring-2 focus:ring-indigo-500"
            />
          </div>

          <div class="grid grid-cols-2 gap-3 pt-2">
            <div>
              <label class="font-bold text-slate-700 block mb-1">Environment Mode</label>
              <select v-model="forms[gateway.id].mode" class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500">
                <option value="sandbox">Sandbox (Testing)</option>
                <option value="production">Production (Live)</option>
              </select>
            </div>
            <div>
              <label class="font-bold text-slate-700 block mb-1">Status Aktivasi</label>
              <label class="flex items-center gap-2 mt-2 cursor-pointer">
                <input type="checkbox" v-model="forms[gateway.id].is_active" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500" />
                <span class="font-bold text-slate-700">Jadikan Aktif</span>
              </label>
            </div>
          </div>

          <div class="pt-4 border-t border-slate-100 flex justify-end">
            <button 
              type="submit" 
              :disabled="forms[gateway.id].processing"
              class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold transition-all disabled:opacity-50 flex items-center gap-2"
            >
              <CheckCircle v-if="!forms[gateway.id].processing" class="w-4 h-4 text-emerald-400" />
              <span>{{ forms[gateway.id].processing ? 'Menyimpan...' : 'Simpan Kredensial' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { CreditCard, Link as LinkIcon, Copy, CheckCircle } from 'lucide-vue-next';

const props = defineProps({
  gateways: Array,
});

const forms = reactive({});
const copied = ref(false);

const webhookUrl = computed(() => {
  if (typeof window !== 'undefined') {
    return `${window.location.origin}/api/payment/midtrans/notification`;
  }
  return '/api/payment/midtrans/notification';
});

function copyWebhookUrl() {
  navigator.clipboard.writeText(webhookUrl.value);
  copied.value = true;
  setTimeout(() => {
    copied.value = false;
  }, 2500);
}

props.gateways.forEach(gw => {
  forms[gw.id] = useForm({
    is_active: gw.is_active,
    mode: gw.mode,
    server_key: gw.server_key || '',
    client_key: gw.client_key || '',
    merchant_id: gw.merchant_id || '',
  });
});

function submit(id) {
  forms[id].put(`/payment-gateways/${id}`, {
    preserveScroll: true,
  });
}
</script>

