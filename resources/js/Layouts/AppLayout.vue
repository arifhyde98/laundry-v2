<template>
  <div class="min-h-screen bg-slate-50 flex">
    <!-- Mobile Sidebar Backdrop -->
    <div 
      v-if="isSidebarOpen" 
      class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm lg:hidden transition-opacity"
      @click="isSidebarOpen = false"
    ></div>

    <!-- Sidebar -->
    <aside 
      :class="[
        'fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-slate-300 flex flex-col transition-transform duration-300 ease-in-out lg:static lg:translate-x-0',
        isSidebarOpen ? 'translate-x-0' : '-translate-x-full'
      ]"
    >
      <!-- Brand Header -->
      <div class="flex items-center gap-3 px-6 py-5 border-b border-slate-800">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-sky-500 to-cyan-400 flex items-center justify-center text-white shadow-lg shadow-sky-500/30">
          <Droplets class="w-6 h-6" />
        </div>
        <div>
          <h1 class="font-bold text-white tracking-tight leading-tight">Laundry POS</h1>
          <p class="text-xs text-sky-400 font-medium">Modern Express v2.0</p>
        </div>
      </div>

      <!-- Navigation Menu -->
      <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
        <div class="px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-slate-500">Operasional</div>
        
        <Link 
          :href="route('dashboard')" 
          :class="['flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all', isActive('dashboard') ? 'bg-sky-600 text-white shadow-md shadow-sky-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800']"
        >
          <LayoutDashboard class="w-5 h-5" />
          <span>Dashboard</span>
        </Link>

        <Link 
          :href="route('pos.index')" 
          :class="['flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all', isActive('pos.index') ? 'bg-sky-600 text-white shadow-md shadow-sky-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800']"
        >
          <ReceiptText class="w-5 h-5" />
          <span>Kasir POS (Cepat)</span>
        </Link>

        <Link 
          :href="route('orders.index')" 
          :class="['flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all', isActive('orders.*') ? 'bg-sky-600 text-white shadow-md shadow-sky-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800']"
        >
          <ShoppingBag class="w-5 h-5" />
          <span>Data Transaksi</span>
        </Link>

        <Link 
          :href="route('workstation.index')" 
          :class="['flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all', isActive('workstation.*') ? 'bg-sky-600 text-white shadow-md shadow-sky-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800']"
        >
          <Kanban class="w-5 h-5" />
          <span>Antrian Workshop</span>
        </Link>

        <Link 
          :href="route('racks.index')" 
          :class="['flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all', isActive('racks.*') ? 'bg-sky-600 text-white shadow-md shadow-sky-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800']"
        >
          <Boxes class="w-5 h-5" />
          <span>Rak Penyimpanan</span>
        </Link>

        <div class="pt-4 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-slate-500">Pelanggan & Tarif</div>

        <Link 
          :href="route('customers.index')" 
          :class="['flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all', isActive('customers.*') ? 'bg-sky-600 text-white shadow-md shadow-sky-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800']"
        >
          <Users class="w-5 h-5" />
          <span>Data Pelanggan</span>
        </Link>

        <Link 
          :href="route('services.index')" 
          :class="['flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all', isActive('services.*') ? 'bg-sky-600 text-white shadow-md shadow-sky-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800']"
        >
          <Tags class="w-5 h-5" />
          <span>Tarif Layanan</span>
        </Link>

        <div class="pt-4 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-slate-500">Keuangan & Manajemen</div>

        <Link 
          :href="route('expenses.index')" 
          :class="['flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all', isActive('expenses.*') ? 'bg-sky-600 text-white shadow-md shadow-sky-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800']"
        >
          <WalletCards class="w-5 h-5" />
          <span>Pengeluaran & Kas</span>
        </Link>

        <Link 
          href="/rewash" 
          :class="['flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all', page.url.startsWith('/rewash') ? 'bg-sky-600 text-white shadow-md shadow-sky-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800']"
        >
          <RotateCcw class="w-5 h-5" />
          Garansi / Komplain
        </Link>

        <Link 
          :href="route('reports.index')" 
          :class="['flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all', isActive('reports.*') ? 'bg-sky-600 text-white shadow-md shadow-sky-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800']"
        >
          <BarChart3 class="w-5 h-5" />
          <span>Laporan & Analitik</span>
        </Link>

        <Link 
          :href="route('users.index')" 
          :class="['flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all', isActive('users.*') ? 'bg-sky-600 text-white shadow-md shadow-sky-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800']"
        >
          <UserCog class="w-5 h-5" />
          <span>Manajemen Staf</span>
        </Link>
      </nav>

      <!-- Bottom User Profile Card -->
      <div class="p-4 border-t border-slate-800">
        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-800/60">
          <div class="flex items-center gap-3 overflow-hidden">
            <div class="w-9 h-9 rounded-lg bg-sky-500/20 text-sky-400 flex items-center justify-center font-bold text-sm">
              {{ userInitial }}
            </div>
            <div class="truncate">
              <p class="text-sm font-semibold text-white truncate">{{ user?.name || 'Administrator' }}</p>
              <span class="text-[11px] font-medium px-1.5 py-0.5 rounded bg-slate-700 text-slate-300 capitalize">{{ user?.role || 'owner' }}</span>
            </div>
          </div>
          <Link :href="route('logout')" method="post" as="button" class="p-1.5 text-slate-400 hover:text-rose-400 hover:bg-slate-700/50 rounded-lg transition-colors" title="Keluar">
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
            class="p-2 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100 lg:hidden"
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

      <!-- Toast Alerts -->
      <div v-if="$page.props.flash?.success" class="m-4 mb-0 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between">
        <div class="flex items-center gap-2">
          <CheckCircle2 class="w-5 h-5 text-emerald-600" />
          <span>{{ $page.props.flash.success }}</span>
        </div>
      </div>

      <div v-if="$page.props.flash?.error" class="m-4 mb-0 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center justify-between">
        <div class="flex items-center gap-2">
          <AlertCircle class="w-5 h-5 text-rose-600" />
          <span>{{ $page.props.flash.error }}</span>
        </div>
      </div>

      <!-- Main Slot -->
      <main class="flex-1 p-4 sm:p-6 overflow-y-auto">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { 
  LayoutDashboard, 
  ShoppingCart, 
  ListOrdered, 
  ClipboardList, 
  Box, 
  Users, 
  Tag, 
  WalletCards, 
  BarChart3, 
  UsersRound,
  Search,
  Bell,
  ChevronDown,
  LogOut,
  User,
  RotateCcw,
  Droplets, ReceiptText, ShoppingBag, Kanban, 
  Boxes, Tags, UserCog, 
  Menu, Plus, Calendar, CheckCircle2, AlertCircle 
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

const userInitial = computed(() => {
  return (user.value?.name || 'A').charAt(0).toUpperCase();
});

const formattedDate = computed(() => {
  const now = new Date();
  return new Intl.DateTimeFormat('id-ID', { dateStyle: 'full' }).format(now);
});

function isActive(routeName) {
  // Simple check using current URL or component name
  const currentUrl = page.url;
  if (routeName === 'dashboard' && (currentUrl === '/' || currentUrl === '/dashboard')) return true;
  if (routeName === 'pos.index' && currentUrl.startsWith('/pos')) return true;
  if (routeName === 'orders.*' && currentUrl.startsWith('/orders')) return true;
  if (routeName === 'workstation.*' && currentUrl.startsWith('/workstation')) return true;
  if (routeName === 'racks.*' && currentUrl.startsWith('/racks')) return true;
  if (routeName === 'customers.*' && currentUrl.startsWith('/customers')) return true;
  if (routeName === 'services.*' && currentUrl.startsWith('/services')) return true;
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
    'expenses.index': '/expenses',
    'reports.index': '/reports',
    'users.index': '/users',
    'logout': '/logout',
  };
  return routes[name] || '/' + name.replace('.', '/');
}
</script>

