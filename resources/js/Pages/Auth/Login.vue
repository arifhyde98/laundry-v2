<template>
  <div class="min-h-screen bg-slate-950 flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative overflow-hidden">
    <!-- Ambient background glow -->
    <div class="absolute -top-40 -right-40 w-96 h-96 bg-sky-500/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-cyan-500/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10">
      <div class="flex justify-center">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-sky-500 to-cyan-400 flex items-center justify-center text-white shadow-xl shadow-sky-500/30">
          <Droplets class="w-8 h-8" />
        </div>
      </div>
      <h2 class="mt-4 text-center text-2xl font-bold tracking-tight text-white">Laundry Express POS</h2>
      <p class="mt-1 text-center text-sm text-slate-400">Masuk ke sistem operasional kasir & manajemen</p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md relative z-10 px-4 sm:px-0">
      <div class="bg-slate-900 border border-slate-800 py-8 px-6 sm:px-10 shadow-2xl rounded-2xl">
        <!-- Error Alerts -->
        <div v-if="form.errors.username || form.errors.password" class="mb-5 p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs flex items-center gap-2">
          <AlertCircle class="w-4 h-4 shrink-0" />
          <span>{{ form.errors.username || form.errors.password }}</span>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
          <!-- Username / Email -->
          <div>
            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Username / Email</label>
            <div class="relative rounded-xl shadow-sm">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                <User class="w-4 h-4" />
              </div>
              <input 
                v-model="form.username" 
                type="text" 
                required 
                autofocus
                placeholder="Masukkan username Anda"
                class="block w-full pl-10 pr-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition-colors"
              />
            </div>
          </div>

          <!-- Password -->
          <div>
            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Password</label>
            <div class="relative rounded-xl shadow-sm">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                <Lock class="w-4 h-4" />
              </div>
              <input 
                v-model="form.password" 
                :type="showPassword ? 'text' : 'password'" 
                required 
                placeholder="Masukkan kata sandi"
                class="block w-full pl-10 pr-10 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition-colors"
              />
              <button 
                type="button" 
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300"
              >
                <Eye v-if="!showPassword" class="w-4 h-4" />
                <EyeOff v-else class="w-4 h-4" />
              </button>
            </div>
          </div>

          <!-- Remember Me -->
          <div class="flex items-center justify-between pt-1">
            <label class="flex items-center gap-2 cursor-pointer">
              <input 
                v-model="form.remember" 
                type="checkbox" 
                class="w-4 h-4 rounded bg-slate-950 border-slate-800 text-sky-500 focus:ring-sky-500 focus:ring-offset-slate-900"
              />
              <span class="text-xs text-slate-400">Ingat sesi saya</span>
            </label>
          </div>

          <!-- Submit Button -->
          <button 
            type="submit" 
            :disabled="form.processing"
            class="w-full mt-2 py-2.5 px-4 rounded-xl bg-gradient-to-r from-sky-500 to-cyan-500 hover:from-sky-600 hover:to-cyan-600 text-white font-semibold text-sm shadow-lg shadow-sky-500/25 transition-all flex items-center justify-center gap-2 disabled:opacity-50"
          >
            <LogIn class="w-4 h-4" />
            <span>{{ form.processing ? 'Memproses...' : 'Masuk ke Aplikasi' }}</span>
          </button>
        </form>

        <div class="mt-6 pt-4 border-t border-slate-800 text-center">
          <p class="text-xs text-slate-500">Default Login: <strong>admin</strong> / <strong>123456</strong></p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Droplets, User, Lock, Eye, EyeOff, LogIn, AlertCircle } from 'lucide-vue-next';

const showPassword = ref(false);

const form = useForm({
  username: 'admin',
  password: '',
  remember: true,
});

function submit() {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  });
}
</script>

