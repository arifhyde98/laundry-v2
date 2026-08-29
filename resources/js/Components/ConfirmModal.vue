<template>
  <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl space-y-4">
      <div class="flex items-center gap-3">
        <div :class="['p-3 rounded-full flex shrink-0', type === 'danger' ? 'bg-rose-100 text-rose-600' : 'bg-sky-100 text-sky-600']">
          <AlertTriangle v-if="type === 'danger'" class="w-6 h-6" />
          <HelpCircle v-else class="w-6 h-6" />
        </div>
        <div>
          <h3 class="font-bold text-slate-900 text-lg">{{ title }}</h3>
        </div>
      </div>
      <p class="text-sm text-slate-600 leading-relaxed whitespace-pre-line">{{ message }}</p>
      <div class="flex gap-3 pt-3">
        <button 
          type="button" 
          @click="$emit('cancel')" 
          class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-semibold transition-colors"
        >
          {{ cancelText }}
        </button>
        <button 
          type="button" 
          @click="$emit('confirm')" 
          :class="['flex-1 py-2.5 text-white rounded-xl font-bold shadow-md transition-all', type === 'danger' ? 'bg-rose-600 hover:bg-rose-700 shadow-rose-600/20' : 'bg-sky-600 hover:bg-sky-700 shadow-sky-600/20']"
        >
          {{ confirmText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { AlertTriangle, HelpCircle } from 'lucide-vue-next';

defineProps({
  show: Boolean,
  title: String,
  message: String,
  confirmText: {
    type: String,
    default: 'Ya, Lanjutkan'
  },
  cancelText: {
    type: String,
    default: 'Batal'
  },
  type: {
    type: String,
    default: 'danger' // 'danger' or 'info'
  }
});

defineEmits(['confirm', 'cancel']);
</script>
