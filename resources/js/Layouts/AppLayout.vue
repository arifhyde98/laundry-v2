<template>
  <div class="min-h-screen bg-slate-50 flex">
    <!-- Mobile Sidebar Backdrop Overlay -->
    <div 
      v-if="isSidebarOpen" 
      @click="isSidebarOpen = false" 
      class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-xs lg:hidden"
    ></div>

    <!-- Sidebar Navigation -->
    <aside 
      :class="[
        'fixed lg:static inset-y-0 left-0 z-50 w-64 bg-slate-900 text-slate-300 flex flex-col transition-transform duration-200 ease-in-out border-r border-slate-800',
        isSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
      ]"
    >
      <!-- Brand Logo & Header -->
      <div class="h-20 flex items-center justify-between px-5 border-b border-slate-800/80">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-sky-500 to-cyan-400 flex items-center justify-center text-white shadow-lg shadow-sky-500/30">
            <Droplets class="w-6 h-6" />
          </div>
          <div>
            <h1 class="font-bold text-white tracking-tight leading-tight text-base">{{ $page.props.outlet?.name || 'Laundry POS' }}</h1>
            <span class="text-xs text-sky-400 font-medium">Modern Express v2.0</span>
          </div>
        </div>
        <button @click="isSidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1">
          ✕
        </button>
      </div>

      <!-- Navigation Links -->
      <nav class="flex-1 px-4 py-5 space-y-1.5 overflow-y-auto">
        <!-- 1. OPERASIONAL -->
        <div class="px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-slate-500">Operasional</div>

        <Link 
          :href="route('dashboard')" 
          :class="['flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all', isActive('dashboard') ? 'bg-sky-600 text-white shadow-md shadow-sky-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60']"
        >
          <LayoutDashboard class="w-5 h-5" />
          <span>Dashboard</span>
        </Link>

        <div v-if="['owner', 'cashier'].includes(user?.role)">
          <Link 
            :href="route('pos.index')" 
            :class="['flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all', isActive('pos.index') ? 'bg-sky-600 text-white shadow-md shadow-sky-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60']"
          >
            <ReceiptText class="w-5 h-5" />
            <span>Kasir POS (Cepat)</span>
          </Link>

          <Link 
            :href="route('orders.index')" 
            :class="['flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all', isActive('orders.*') ? 'bg-sky-600 text-white shadow-md shadow-sky-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60']"
          >
            <ShoppingBag class="w-5 h-5" />
            <span>Data Transaksi</span>
          </Link>
        </div>

        <Link 
          :href="route('workstation.index')" 
          :class="['flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all', isActive('workstation.*') ? 'bg-sky-600 text-white shadow-md shadow-sky-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60']"
        >
          <Kanban class="w-5 h-5" />
          <span>Antrian Workshop</span>
        </Link>

        <Link 
          :href="route('racks.index')" 
          :class="['flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all', isActive('racks.*') ? 'bg-sky-600 text-white shadow-md shadow-sky-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60']"
        >
          <Boxes class="w-5 h-5" />
          <span>Rak Penyimpanan</span>
        </Link>

        <!-- 2. PELANGGAN & TARIF -->
        <div class="pt-4 px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-slate-500">Pelanggan & Tarif</div>

        <div v-if="['owner', 'cashier'].includes(user?.role)">
          <Link 
            :href="route('customers.index')" 
            :class="['flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all', isActive('customers.*') ? 'bg-sky-600 text-white shadow-md shadow-sky-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60']"
          >
            <Users class="w-5 h-5" />
            <span>Data Pelanggan</span>
          </Link>

          <Link 
            :href="route('services.index')" 
            :class="['flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all', isActive('services.*') ? 'bg-sky-600 text-white shadow-md shadow-sky-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60']"
          >
            <Tags class="w-5 h-5" />
            <span>Tarif Layanan</span>
          </Link>
        </div>

        <div v-if="user?.role === 'owner'">
          <Link 
            :href="route('inventory.index')" 
            :class="['flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all', isActive('inventory.*') ? 'bg-sky-600 text-white shadow-md shadow-sky-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60']"
          >
            <FlaskConical class="w-5 h-5" />
            <span>Stok & Resep Bahan</span>
          </Link>

          <Link 
            :href="route('outlet.index')" 
            :class="['flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all', isActive('outlet.*') ? 'bg-sky-600 text-white shadow-md shadow-sky-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60']"
          >
            <Store class="w-5 h-5" />
            <span>Profil Usaha & Struk</span>
          </Link>
        </div>

        <!-- 3. KEUANGAN & MANAJEMEN -->
        <div class="pt-4 px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-slate-500">Keuangan & Manajemen</div>

        <Link 
          href="/rewash" 
          :class="['flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all', page.url.startsWith('/rewash') ? 'bg-sky-600 text-white shadow-md shadow-sky-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60']"
        >
          <RotateCcw class="w-5 h-5" />
          <span>Garansi / Komplain</span>
        </Link>

        <div v-if="user?.role === 'owner'">
          <Link 
            :href="route('expenses.index')" 
            :class="['flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all', isActive('expenses.*') ? 'bg-sky-600 text-white shadow-md shadow-sky-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60']"
          >
            <WalletCards class="w-5 h-5" />
            <span>Pengeluaran & Kas</span>
          </Link>

          <Link 
            :href="route('reports.index')" 
            :class="['flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all', isActive('reports.*') ? 'bg-sky-600 text-white shadow-md shadow-sky-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60']"
          >
            <BarChart3 class="w-5 h-5" />
            <span>Laporan & Analitik</span>
          </Link>

          <Link 
            :href="route('users.index')" 
            :class="['flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all', isActive('users.*') ? 'bg-sky-600 text-white shadow-md shadow-sky-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60']"
          >
            <UserCog class="w-5 h-5" />
            <span>Manajemen Staf</span>
          </Link>
        </div>
      </nav>

      <!-- Bottom User Profile Card -->
      <div class="p-4 border-t border-slate-800">
        <div class="flex items-center justify-between p-2.5 rounded-2xl bg-slate-800/60">
          <div class="flex items-center gap-3 overflow-hidden">
            <div class="w-9 h-9 rounded-xl bg-sky-500/20 text-sky-400 flex items-center justify-center font-bold text-sm">
              {{ userInitial }}
            </div>
            <div class="truncate">
              <p class="text-sm font-semibold text-white truncate">{{ user?.name || 'Administrator' }}</p>
              <span class="text-[11px] font-medium px-1.5 py-0.5 rounded bg-slate-700 text-slate-300 capitalize">{{ user?.role || 'owner' }}</span>
            </div>
          </div>
          <Link :href="route('logout')" method="post" as="button" class="p-1.5 text-slate-400 hover:text-rose-400 hover:bg-slate-700/50 rounded-lg transition-colors cursor-pointer" title="Keluar">
            <LogOut class="w-4 h-4" />
          </Link>
        </div>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <!-- Topbar Header -->
      <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-30">
        <div class="flex items-center gap-3">
          <button 
            @click="isSidebarOpen = !isSidebarOpen"
            class="p-2 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100 lg:hidden cursor-pointer"
          >
            <Menu class="w-6 h-6" />
          </button>
          <div>
            <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">{{ title }}</h2>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <!-- Quick POS Button -->
          <Link 
            :href="route('pos.index')" 
            class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-sky-500 hover:bg-sky-600 text-white font-medium text-xs sm:text-sm shadow-sm transition-all"
          >
            <Plus class="w-4 h-4" />
            <span class="hidden sm:inline">Order Baru</span>
          </Link>

          <!-- Current Date/Time -->
          <div class="hidden md:flex items-center gap-1.5 text-xs text-slate-500 font-medium bg-slate-100 px-3 py-1.5 rounded-lg">
            <Calendar class="w-3.5 h-3.5 text-slate-400" />
            <span>{{ formattedDate }}</span>
          </div>
        </div>
      </header>

      <!-- Floating Toast Alerts -->
      <div class="fixed top-20 right-4 sm:right-6 z-50 flex flex-col gap-2 pointer-events-none max-w-sm w-full">
        <!-- Success Toast -->
        <transition enter-active-class="transition duration-300 ease-out" enter-from-class="transform translate-x-10 opacity-0" enter-to-class="transform translate-x-0 opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="transform translate-x-0 opacity-100" leave-to-class="transform translate-x-10 opacity-0">
          <div v-if="showSuccessToast && $page.props.flash?.success" class="pointer-events-auto p-4 rounded-2xl bg-emerald-50 border border-emerald-200 shadow-xl flex items-start gap-3">
            <CheckCircle2 class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" />
            <div class="flex-1">
              <h4 class="text-sm font-bold text-emerald-900">Berhasil!</h4>
              <p class="text-xs text-emerald-700 mt-0.5 leading-relaxed">{{ $page.props.flash.success }}</p>
            </div>
            <button @click="showSuccessToast = false" class="text-emerald-400 hover:text-emerald-600">✕</button>
          </div>
        </transition>

        <!-- Error Toast -->
        <transition enter-active-class="transition duration-300 ease-out" enter-from-class="transform translate-x-10 opacity-0" enter-to-class="transform translate-x-0 opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="transform translate-x-0 opacity-100" leave-to-class="transform translate-x-10 opacity-0">
          <div v-if="showErrorToast && $page.props.flash?.error" class="pointer-events-auto p-4 rounded-2xl bg-rose-50 border border-rose-200 shadow-xl flex items-start gap-3">
            <AlertCircle class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" />
            <div class="flex-1">
              <h4 class="text-sm font-bold text-rose-900">Oops, Gagal!</h4>
              <p class="text-xs text-rose-700 mt-0.5 leading-relaxed">{{ $page.props.flash.error }}</p>
            </div>
            <button @click="showErrorToast = false" class="text-rose-400 hover:text-rose-600">✕</button>
          </div>
        </transition>
      </div>

      <!-- Main Slot -->
      <main class="flex-1 p-4 sm:p-6 overflow-y-auto">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { 
  LayoutDashboard, 
  Droplets, 
  ReceiptText, 
  ShoppingBag, 
  Kanban, 
  RotateCcw, 
  Boxes, 
  Tags, 
  Users, 
  FlaskConical, 
  Store,
  WalletCards, 
  BarChart3, 
  UserCog, 
  Menu, 
  Plus, 
  Calendar, 
  CheckCircle2, 
  AlertCircle, 
  LogOut 
} from 'lucide-vue-next';

defineProps({
  title: {
    type: String,
    default: 'Dashboard'
  }
});

const isSidebarOpen = ref(false);
const page = usePage();
const user = computed(() => page.props.auth?.user);

const showSuccessToast = ref(false);
const showErrorToast = ref(false);
let toastTimeout = null;

watch(() => page.props.flash, (flash) => {
  if (flash?.success) {
    showSuccessToast.value = true;
    showErrorToast.value = false;
  }
  if (flash?.error) {
    showErrorToast.value = true;
    showSuccessToast.value = false;
  }
  
  if (flash?.success || flash?.error) {
    if (toastTimeout) clearTimeout(toastTimeout);
    toastTimeout = setTimeout(() => {
      showSuccessToast.value = false;
      showErrorToast.value = false;
    }, 5000);
  }
}, { deep: true, immediate: true });

const userInitial = computed(() => {
  return (user.value?.name || 'A').charAt(0).toUpperCase();
});

const formattedDate = computed(() => {
  const now = new Date();
  return new Intl.DateTimeFormat('id-ID', { dateStyle: 'full' }).format(now);
});

function isActive(routeName) {
  const currentUrl = page.url;
  if (routeName === 'dashboard' && (currentUrl === '/' || currentUrl === '/dashboard')) return true;
  if (routeName === 'pos.index' && currentUrl.startsWith('/pos')) return true;
  if (routeName === 'orders.*' && currentUrl.startsWith('/orders')) return true;
  if (routeName === 'workstation.*' && currentUrl.startsWith('/workstation')) return true;
  if (routeName === 'racks.*' && currentUrl.startsWith('/racks')) return true;
  if (routeName === 'customers.*' && currentUrl.startsWith('/customers')) return true;
  if (routeName === 'services.*' && currentUrl.startsWith('/services')) return true;
  if (routeName === 'inventory.*' && currentUrl.startsWith('/inventory')) return true;
  if (routeName === 'outlet.*' && currentUrl.startsWith('/outlet')) return true;
  if (routeName === 'expenses.*' && currentUrl.startsWith('/expenses')) return true;
  if (routeName === 'reports.*' && currentUrl.startsWith('/reports')) return true;
  if (routeName === 'users.*' && currentUrl.startsWith('/users')) return true;
  return false;
}

// Global route helper for Inertia
function route(name, params = {}) {
  const routes = {
    'dashboard': '/dashboard',
    'pos.index': '/pos',
    'orders.index': '/orders',
    'workstation.index': '/workstation',
    'racks.index': '/racks',
    'customers.index': '/customers',
    'services.index': '/services',
    'inventory.index': '/inventory',
    'outlet.index': '/outlet',
    'expenses.index': '/expenses',
    'reports.index': '/reports',
    'users.index': '/users',
    'logout': '/logout',
  };
  return routes[name] || '/' + name.replace('.', '/');
}
</script>
