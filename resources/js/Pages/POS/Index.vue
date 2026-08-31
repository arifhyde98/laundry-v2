<template>
  <AppLayout title="Kasir POS Cepat">
    <!-- Active Shift Warning / Status Bar -->
    <div v-if="!activeShift" class="mb-4 p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-900 flex flex-col sm:flex-row items-center justify-between gap-3">
      <div class="flex items-center gap-2.5">
        <AlertTriangle class="w-5 h-5 text-amber-600 shrink-0" />
        <span class="text-xs sm:text-sm font-medium">Shift kasir Anda belum dibuka. Buka shift kasir terlebih dahulu untuk mencatat rekonsiliasi laci kas.</span>
      </div>
      <button @click="showOpenShiftModal = true" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold shrink-0 shadow-sm transition-all">
        Buka Shift Kasir Sekarang
      </button>
    </div>

    <div v-else class="mb-4 p-3 rounded-2xl bg-sky-50 border border-sky-100 flex items-center justify-between text-xs text-sky-800">
      <div class="flex items-center gap-2">
        <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></div>
        <span>Shift Aktif sejak <strong>{{ formatDateTime(activeShift.opened_at) }}</strong> (Modal Awal: <strong>Rp {{ formatNumber(activeShift.starting_cash) }}</strong>)</span>
      </div>
      <button @click="showCloseShiftModal = true" class="px-3 py-1 bg-white hover:bg-slate-100 border border-sky-200 text-sky-900 font-semibold rounded-lg shadow-xs transition-colors">
        Tutup Shift (Z-Report)
      </button>
    </div>

    <!-- Main POS Grid: Left Catalog, Right Cart -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
      <!-- Left Column: Customer & Catalog -->
      <div class="lg:col-span-7 space-y-4">
        <!-- Customer Selection Box -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
              <UserCheck class="w-4 h-4 text-sky-600" />
              <span>1. Pilih Pelanggan</span>
            </span>
            <button @click="showQuickCustomerModal = true" class="text-xs font-semibold text-sky-600 hover:underline flex items-center gap-1">
              <Plus class="w-3.5 h-3.5" />
              <span>Tambah Baru</span>
            </button>
          </div>

          <!-- Customer Autocomplete / Selector -->
          <div v-if="!cart.customer" class="relative">
            <input 
              v-model="customerSearch"
              type="text" 
              placeholder="Ketik nama atau nomor HP pelanggan..." 
              class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-sky-500 focus:bg-white transition-colors"
            />
            <Search class="w-4 h-4 absolute left-3 top-3 text-slate-400" />

            <!-- Search Dropdown List -->
            <div v-if="customerSearch && filteredCustomers.length > 0" class="absolute z-20 top-full left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-xl max-h-48 overflow-y-auto">
              <div 
                v-for="c in filteredCustomers" 
                :key="c.id" 
                @click="selectCustomer(c)"
                class="p-2.5 hover:bg-sky-50 cursor-pointer border-b border-slate-100 last:border-0 flex items-center justify-between text-xs"
              >
                <div>
                  <p class="font-bold text-slate-900">{{ c.name }}</p>
                  <p class="text-slate-500">{{ c.phone }} - {{ c.address || 'Alamat tidak ada' }}</p>
                </div>
                <div v-if="c.deposit_balance > 0" class="text-emerald-600 font-bold text-[11px] bg-emerald-50 px-2 py-0.5 rounded">
                  Saldo: Rp {{ formatNumber(c.deposit_balance) }}
                </div>
              </div>
            </div>
          </div>

          <!-- Selected Customer Pill -->
          <div v-else class="flex items-center justify-between p-3 rounded-xl bg-sky-50/70 border border-sky-200 text-sm">
            <div>
              <p class="font-bold text-slate-900">{{ cart.customer.name }} <span class="font-normal text-slate-500">({{ cart.customer.phone }})</span></p>
              <p class="text-xs text-slate-500">{{ cart.customer.address || 'Alamat tidak diisi' }}</p>
            </div>
            <div class="flex items-center gap-3">
              <span v-if="cart.customer.deposit_balance > 0" class="text-xs font-bold text-emerald-700 bg-emerald-100 px-2 py-1 rounded-lg">
                Saldo: Rp {{ formatNumber(cart.customer.deposit_balance) }}
              </span>
              <button @click="cart.customer = null" class="text-xs font-semibold text-rose-600 hover:underline">Ganti</button>
            </div>
          </div>
        </div>

        <!-- Service Category Tabs & Catalog -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
          <div class="flex items-center justify-between mb-4">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
              <Sparkles class="w-4 h-4 text-sky-600" />
              <span>2. Pilih Layanan / Jasa</span>
            </span>

            <!-- Filter Buttons -->
            <div class="flex gap-1.5 bg-slate-100 p-1 rounded-xl">
              <button 
                @click="activeCategory = 'all'" 
                :class="['px-2.5 py-1 text-xs font-semibold rounded-lg transition-colors', activeCategory === 'all' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-900']"
              >
                Semua
              </button>
              <button 
                @click="activeCategory = 'kg'" 
                :class="['px-2.5 py-1 text-xs font-semibold rounded-lg transition-colors', activeCategory === 'kg' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-900']"
              >
                Kiloan
              </button>
              <button 
                @click="activeCategory = 'pcs'" 
                :class="['px-2.5 py-1 text-xs font-semibold rounded-lg transition-colors', activeCategory === 'pcs' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-900']"
              >
                Satuan / Pcs
              </button>
            </div>
          </div>

          <!-- Services Grid Cards -->
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 max-h-[380px] overflow-y-auto pr-1">
            <div 
              v-for="service in filteredServices" 
              :key="service.id"
              @click="addServiceToCart(service)"
              class="p-3.5 rounded-xl border border-slate-200 hover:border-sky-500 hover:bg-sky-50/50 cursor-pointer transition-all flex flex-col justify-between group"
            >
              <div>
                <div class="flex items-center justify-between mb-1.5">
                  <span class="text-[10px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 group-hover:bg-sky-100 group-hover:text-sky-700 capitalize">
                    {{ service.unit }}
                  </span>
                  <span class="text-[10px] text-slate-400">{{ service.estimated_hours }} Jam</span>
                </div>
                <h4 class="text-xs font-bold text-slate-900 line-clamp-2 leading-tight">{{ service.name }}</h4>
              </div>

              <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs font-bold text-sky-600">Rp {{ formatNumber(service.price) }}</span>
                <div class="w-6 h-6 rounded-lg bg-slate-100 group-hover:bg-sky-500 group-hover:text-white flex items-center justify-center transition-colors">
                  <Plus class="w-3.5 h-3.5" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Interactive Shopping Cart & Checkout -->
      <div class="lg:col-span-5 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
              <ShoppingCart class="w-5 h-5 text-sky-600" />
              <h3 class="font-bold text-slate-900 text-sm">Keranjang Pesanan</h3>
            </div>
            <button 
              v-if="cart.items.length > 0" 
              @click="cart.clearCart()" 
              class="text-xs text-rose-500 font-semibold hover:underline"
            >
              Kosongkan
            </button>
          </div>

          <!-- Empty State -->
          <div v-if="cart.items.length === 0" class="py-12 text-center text-slate-400">
            <ShoppingBag class="w-10 h-10 mx-auto text-slate-300 mb-2 stroke-[1.5]" />
            <p class="text-xs">Keranjang masih kosong.<br>Pilih layanan dari daftar di sebelah kiri.</p>
          </div>

          <!-- Cart Items List -->
          <div v-else class="space-y-2.5 max-h-60 overflow-y-auto pr-1">
            <div 
              v-for="(item, idx) in cart.items" 
              :key="idx"
              class="p-2.5 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between gap-2"
            >
              <div class="flex-1 min-w-0">
                <input 
                  v-model="item.item_name" 
                  class="w-full text-xs font-bold text-slate-900 bg-transparent border-0 p-0 focus:ring-0 focus:border-b focus:border-sky-500" 
                />
                <p class="text-[11px] text-slate-500">@ Rp {{ formatNumber(item.unit_price) }} / {{ item.unit }}</p>
              </div>

              <!-- Quantity Controls -->
              <div class="flex items-center gap-1.5 shrink-0">
                <button @click="cart.updateQty(idx, item.quantity - 1)" class="w-6 h-6 rounded bg-white border border-slate-200 text-slate-600 flex items-center justify-center hover:bg-slate-100 text-xs font-bold">-</button>
                <input 
                  v-model.number="item.quantity" 
                  type="number" 
                  step="0.1" 
                  min="0.1" 
                  @input="cart.updateQty(idx, item.quantity)"
                  class="w-12 py-0.5 px-1 text-center font-bold text-xs bg-white border border-slate-200 rounded focus:ring-0 focus:border-sky-500" 
                />
                <button @click="cart.updateQty(idx, item.quantity + 1)" class="w-6 h-6 rounded bg-white border border-slate-200 text-slate-600 flex items-center justify-center hover:bg-slate-100 text-xs font-bold">+</button>
              </div>

              <!-- Subtotal & Remove -->
              <div class="text-right shrink-0">
                <p class="text-xs font-bold text-slate-900">Rp {{ formatNumber(item.subtotal) }}</p>
                <button @click="cart.removeItem(idx)" class="text-[10px] text-rose-500 hover:underline">Hapus</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Order Summary & Checkout Button -->
        <div class="mt-4 pt-4 border-t border-slate-100 space-y-2 text-xs">
          <!-- Discount & Ongkir inputs -->
          <div class="flex items-center justify-between text-slate-600">
            <span>Total Berat / Item:</span>
            <span class="font-bold text-slate-900">{{ cart.totalWeightQty }} Kg/Pcs</span>
          </div>
          <div class="flex items-center justify-between text-slate-600">
            <span>Subtotal:</span>
            <span class="font-semibold text-slate-900">Rp {{ formatNumber(cart.subtotal) }}</span>
          </div>
          <div class="flex items-center justify-between gap-2">
            <span class="text-slate-600">Diskon (Rp):</span>
            <input v-model.number="cart.discountValue" type="number" min="0" placeholder="0" class="w-24 text-right py-1 px-2 border border-slate-200 rounded-lg text-xs" />
          </div>
          <div class="flex items-center justify-between gap-2">
            <span class="text-slate-600">Biaya Antar (Rp):</span>
            <input v-model.number="cart.deliveryFee" type="number" min="0" placeholder="0" class="w-24 text-right py-1 px-2 border border-slate-200 rounded-lg text-xs" />
          </div>

          <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
            <span class="text-sm font-bold text-slate-900">Grand Total:</span>
            <span class="text-lg font-extrabold text-sky-600">Rp {{ formatNumber(cart.grandTotal) }}</span>
          </div>

          <div v-if="!activeShift && $page.props.auth?.user?.role === 'cashier'" class="mt-2 p-2 bg-rose-50 text-rose-600 text-[10px] font-bold rounded-lg border border-rose-200 text-center">
            ⚠️ Wajib "Buka Shift" sebelum bisa checkout pesanan
          </div>

          <!-- Checkout Trigger Button -->
          <button 
            @click="openPaymentModal" 
            :disabled="cart.items.length === 0 || !cart.customer || (!activeShift && $page.props.auth?.user?.role === 'cashier')"
            class="w-full mt-2 py-3 px-4 rounded-xl bg-gradient-to-r from-sky-500 to-cyan-500 hover:from-sky-600 hover:to-cyan-600 text-white font-bold text-sm shadow-md shadow-sky-500/25 disabled:opacity-50 transition-all flex items-center justify-center gap-2"
          >
            <CreditCard class="w-4 h-4" />
            <span>Lanjut Pembayaran (Checkout)</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Modal: Checkout & Pembayaran (Dynamic QRIS, Cash, Deposit) -->
    <div v-if="showPaymentModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
      <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div>
            <h3 class="font-bold text-slate-900 text-base">Konfirmasi Pembayaran</h3>
            <p class="text-xs text-slate-500">Pelanggan: <strong>{{ cart.customer?.name }}</strong></p>
          </div>
          <button @click="showPaymentModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <div class="p-3 bg-sky-50 rounded-xl border border-sky-100 flex items-center justify-between">
          <span class="text-xs text-sky-800 font-semibold">Total Tagihan:</span>
          <span class="text-base font-extrabold text-sky-700">Rp {{ formatNumber(cart.grandTotal) }}</span>
        </div>

        <!-- Payment Type -->
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Status Pembayaran</label>
          <div class="grid grid-cols-3 gap-2">
            <button 
              type="button" 
              @click="setPaymentType('paid')" 
              :class="['py-2 text-xs font-semibold rounded-xl border transition-all', cart.paymentType === 'paid' ? 'bg-emerald-500 text-white border-emerald-600' : 'bg-slate-50 text-slate-700 border-slate-200']"
            >
              Lunas di Awal
            </button>
            <button 
              type="button" 
              @click="setPaymentType('partial')" 
              :class="['py-2 text-xs font-semibold rounded-xl border transition-all', cart.paymentType === 'partial' ? 'bg-amber-500 text-white border-amber-600' : 'bg-slate-50 text-slate-700 border-slate-200']"
            >
              Uang Muka (DP)
            </button>
            <button 
              type="button" 
              @click="setPaymentType('unpaid')" 
              :class="['py-2 text-xs font-semibold rounded-xl border transition-all', cart.paymentType === 'unpaid' ? 'bg-rose-500 text-white border-rose-600' : 'bg-slate-50 text-slate-700 border-slate-200']"
            >
              Bayar Nanti
            </button>
          </div>
        </div>

        <!-- Partial DP Input Box -->
        <div v-if="cart.paymentType === 'partial'" class="p-3 bg-amber-50 rounded-xl border border-amber-200 space-y-1">
          <label class="block text-xs font-bold text-amber-900">Nominal Uang Muka (DP):</label>
          <input 
            v-model.number="partialAmount" 
            type="number" 
            :max="cart.grandTotal" 
            min="1000" 
            class="w-full py-2 px-3 border border-amber-300 rounded-xl text-sm font-bold text-slate-900 bg-white" 
          />
          <div class="flex justify-between text-[11px] text-amber-800 font-semibold pt-1">
            <span>Sisa Belum Lunas:</span>
            <span>Rp {{ formatNumber(Math.max(0, cart.grandTotal - partialAmount)) }}</span>
          </div>
        </div>
        <div v-if="cart.paymentType !== 'unpaid'">
          <label class="block text-xs font-bold text-slate-700 mb-1">Metode Pembayaran</label>
          <div class="grid grid-cols-4 gap-1.5">
            <button 
              type="button" 
              @click="cart.paymentMethod = 'cash'"
              :class="['py-2 text-xs font-bold rounded-xl border transition-all', cart.paymentMethod === 'cash' ? 'bg-sky-600 text-white border-sky-600' : 'bg-slate-50 text-slate-700 border-slate-200']"
            >
              💵 Tunai
            </button>
            <button 
              type="button" 
              @click="cart.paymentMethod = 'deposit'"
              :class="['py-2 text-xs font-bold rounded-xl border transition-all', cart.paymentMethod === 'deposit' ? 'bg-sky-600 text-white border-sky-600' : 'bg-slate-50 text-slate-700 border-slate-200']"
            >
              💳 Saldo
            </button>
            <button 
              type="button" 
              @click="cart.paymentMethod = 'qris'"
              :class="['py-2 text-xs font-bold rounded-xl border transition-all', cart.paymentMethod === 'qris' ? 'bg-sky-600 text-white border-sky-600' : 'bg-slate-50 text-slate-700 border-slate-200']"
            >
              📱 QRIS
            </button>
            <button 
              type="button" 
              @click="cart.paymentMethod = 'transfer'"
              :class="['py-2 text-xs font-bold rounded-xl border transition-all', cart.paymentMethod === 'transfer' ? 'bg-sky-600 text-white border-sky-600' : 'bg-slate-50 text-slate-700 border-slate-200']"
            >
              🏦 Transfer
            </button>
          </div>

          <!-- Dynamic QRIS / Gateway Display -->
          <div v-if="cart.paymentMethod === 'qris' || cart.paymentMethod === 'transfer'" class="mt-3 p-3 rounded-xl border text-center text-xs space-y-1" :class="activeGateway ? 'bg-indigo-50 border-indigo-200 text-indigo-900' : 'bg-slate-50 border-slate-200 text-slate-700'">
            <div v-if="activeGateway" class="font-bold flex items-center justify-center gap-1 text-indigo-700">
              <Sparkles class="w-3.5 h-3.5" />
              <span>Gateway {{ activeGateway.display_name }} Aktif</span>
            </div>
            <p v-else class="font-bold text-slate-700">Pembayaran {{ cart.paymentMethod.toUpperCase() }} Manual</p>
            <p class="text-[10px] text-slate-500">
              {{ activeGateway ? 'Transaksi akan dapat dibayar menggunakan QRIS / Virtual Account Midtrans.' : 'Kasir mengonfirmasi penerimaan dana secara manual.' }}
            </p>
          </div>

          <!-- Cash Input & Quick Money Buttons -->
          <div v-if="cart.paymentMethod === 'cash'" class="mt-3 space-y-2">
            <div class="flex items-center justify-between text-xs">
              <label class="font-bold text-slate-700">Nominal Uang Diterima:</label>
              <span v-if="cashChange >= 0" class="text-emerald-600 font-bold">Kembalian: Rp {{ formatNumber(cashChange) }}</span>
            </div>
            <input 
              v-model.number="receivedCash" 
              type="number" 
              class="w-full py-2 px-3 border border-slate-200 rounded-xl text-sm font-bold text-slate-900" 
            />
            <div class="grid grid-cols-4 gap-1">
              <button type="button" @click="receivedCash = cart.grandTotal" class="py-1 bg-slate-100 hover:bg-slate-200 rounded text-[11px] font-bold">Uang Pas</button>
              <button type="button" @click="receivedCash = 20000" class="py-1 bg-slate-100 hover:bg-slate-200 rounded text-[11px] font-bold">20.000</button>
              <button type="button" @click="receivedCash = 50000" class="py-1 bg-slate-100 hover:bg-slate-200 rounded text-[11px] font-bold">50.000</button>
              <button type="button" @click="receivedCash = 100000" class="py-1 bg-slate-100 hover:bg-slate-200 rounded text-[11px] font-bold">100.000</button>
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
          <input v-model="cart.notes" type="text" placeholder="Contoh: Jangan terlalu panas, pisahkan putih" class="w-full py-1.5 px-3 border border-slate-200 rounded-xl text-xs" />
        </div>

        <!-- Submit Checkout -->
        <div class="flex gap-2 pt-2">
          <button @click="showPaymentModal = false" type="button" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold">Batal</button>
          <button 
            @click="submitCheckout" 
            :disabled="isSubmitting"
            class="flex-2 py-2.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold shadow-md shadow-sky-600/25 disabled:opacity-50"
          >
            {{ isSubmitting ? 'Memproses...' : 'Cetak Struk & Simpan Order' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal: Quick Add Customer -->
    <div v-if="showQuickCustomerModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
      <div class="bg-white rounded-2xl max-w-sm w-full p-5 shadow-2xl space-y-3">
        <h3 class="font-bold text-slate-900 text-sm">Tambah Pelanggan Baru</h3>
        <form @submit.prevent="createCustomer" class="space-y-3 text-xs">
          <div>
            <label class="font-semibold block mb-1">Nama Lengkap</label>
            <input v-model="newCustForm.name" required class="w-full py-2 px-3 border rounded-xl" />
          </div>
          <div>
            <label class="font-semibold block mb-1">Nomor WhatsApp / HP</label>
            <input v-model="newCustForm.phone" required placeholder="08xxx" class="w-full py-2 px-3 border rounded-xl" />
          </div>
          <div>
            <label class="font-semibold block mb-1">Alamat</label>
            <textarea v-model="newCustForm.address" rows="2" class="w-full py-2 px-3 border rounded-xl"></textarea>
          </div>
          <div class="flex gap-2 pt-2">
            <button type="button" @click="showQuickCustomerModal = false" class="flex-1 py-2 bg-slate-100 rounded-xl">Batal</button>
            <button type="submit" class="flex-1 py-2 bg-sky-600 text-white rounded-xl font-bold">Simpan</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal: Open Shift -->
    <div v-if="showOpenShiftModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
      <div class="bg-white rounded-2xl max-w-sm w-full p-5 shadow-2xl space-y-3 text-xs">
        <h3 class="font-bold text-slate-900 text-sm">Buka Shift Kasir Baru</h3>
        <p class="text-slate-500">Masukkan jumlah modal kas awal di laci kasir.</p>
        <form @submit.prevent="openShiftSubmit" class="space-y-3">
          <div>
            <label class="font-semibold block mb-1">Modal Kas Awal (Rp)</label>
            <input v-model.number="shiftForm.starting_cash" type="number" required min="0" class="w-full py-2 px-3 border rounded-xl text-sm font-bold" />
          </div>
          <div>
            <label class="font-semibold block mb-1">Catatan</label>
            <input v-model="shiftForm.notes" placeholder="Shift Pagi / Reguler" class="w-full py-2 px-3 border rounded-xl" />
          </div>
          <div class="flex gap-2 pt-2">
            <button type="button" @click="showOpenShiftModal = false" class="flex-1 py-2 bg-slate-100 rounded-xl">Batal</button>
            <button type="submit" class="flex-1 py-2 bg-emerald-600 text-white rounded-xl font-bold">Buka Shift</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal: Close Shift (Z-Report) -->
    <div v-if="showCloseShiftModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
      <div class="bg-white rounded-2xl max-w-sm w-full p-5 shadow-2xl space-y-3 text-xs">
        <h3 class="font-bold text-slate-900 text-sm">Tutup Shift Kasir (Z-Report)</h3>
        <div class="p-3 bg-slate-50 rounded-xl space-y-1">
          <div class="flex justify-between"><span>Modal Awal:</span><strong>Rp {{ formatNumber(activeShift?.starting_cash) }}</strong></div>
          <div class="flex justify-between"><span>Penerimaan Tunai:</span><strong class="text-emerald-600">+ Rp {{ formatNumber(activeShift?.cash_income) }}</strong></div>
          <div class="flex justify-between"><span>Penerimaan Non-Tunai:</span><strong class="text-sky-600">+ Rp {{ formatNumber(activeShift?.non_cash_income) }}</strong></div>
          <div class="flex justify-between pt-1 border-t"><span>Uang Kas Seharusnya:</span><strong class="text-slate-900">Rp {{ formatNumber(activeShift?.expected_cash) }}</strong></div>
        </div>
        <form @submit.prevent="closeShiftSubmit" class="space-y-3">
          <div>
            <label class="font-semibold block mb-1">Total Uang Fisik di Laci (Rp)</label>
            <input v-model.number="closeShiftForm.closing_cash" type="number" required min="0" class="w-full py-2 px-3 border rounded-xl text-sm font-bold" />
          </div>
          <div class="flex gap-2 pt-2">
            <button type="button" @click="showCloseShiftModal = false" class="flex-1 py-2 bg-slate-100 rounded-xl">Batal</button>
            <button type="submit" class="flex-1 py-2 bg-rose-600 text-white rounded-xl font-bold">Tutup & Rekonsiliasi</button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useCartStore } from '@/Stores/cartStore';
import { 
  UserCheck, Plus, Search, Sparkles, ShoppingCart, ShoppingBag, 
  CreditCard, QrCode, AlertTriangle 
} from 'lucide-vue-next';

const props = defineProps({
  services: Array,
  customers: Array,
  racks: Array,
  activeShift: Object,
});

const cart = useCartStore();
const activeCategory = ref('all');
const customerSearch = ref('');
const showPaymentModal = ref(false);
const showQuickCustomerModal = ref(false);
const showOpenShiftModal = ref(false);
const showCloseShiftModal = ref(false);
const isSubmitting = ref(false);
const receivedCash = ref(0);
const partialAmount = ref(0);
const activeGateway = ref(null);

onMounted(async () => {
  try {
    const res = await fetch('/api/payment/active-gateway');
    const data = await res.json();
    if (data.active_gateway && data.active_gateway.is_active) {
      activeGateway.value = data.active_gateway;
    }
  } catch (e) {
    console.error('Failed to fetch active gateway:', e);
  }
});

const shiftForm = useForm({
  starting_cash: 50000,
  notes: 'Shift Reguler',
});

const closeShiftForm = useForm({
  closing_cash: 0,
  notes: '',
});

const newCustForm = useForm({
  name: '',
  phone: '',
  address: '',
});

function formatNumber(num) {
  return Number(num || 0).toLocaleString('id-ID');
}

function formatDateTime(dt) {
  if (!dt) return '-';
  return new Intl.DateTimeFormat('id-ID', { dateStyle: 'short', timeStyle: 'short' }).format(new Date(dt));
}

const filteredCustomers = computed(() => {
  if (!customerSearch.value) return [];
  const q = customerSearch.value.toLowerCase();
  return props.customers.filter(c => 
    c.name.toLowerCase().includes(q) || (c.phone && c.phone.includes(q))
  );
});

const filteredServices = computed(() => {
  if (activeCategory.value === 'all') return props.services;
  return props.services.filter(s => s.unit === activeCategory.value);
});

function selectCustomer(cust) {
  cart.customer = cust;
  customerSearch.value = '';
}

function addServiceToCart(service) {
  cart.addItem(service, 1);
}

function openPaymentModal() {
  receivedCash.value = cart.grandTotal;
  partialAmount.value = Math.max(1000, Math.floor(cart.grandTotal / 2));
  showPaymentModal.value = true;
}

function setPaymentType(type) {
  cart.paymentType = type;
  if (type === 'paid') {
    cart.paidAmount = cart.grandTotal;
  } else if (type === 'partial') {
    cart.paidAmount = partialAmount.value;
  } else if (type === 'unpaid') {
    cart.paidAmount = 0;
  }
}

const cashChange = computed(() => {
  const targetBill = cart.paymentType === 'partial' ? Number(partialAmount.value) : cart.grandTotal;
  return Number(receivedCash.value) - targetBill;
});

function submitCheckout() {
  isSubmitting.value = true;

  let computedPaid = 0;
  if (cart.paymentType === 'paid') {
    computedPaid = cart.grandTotal;
  } else if (cart.paymentType === 'partial') {
    computedPaid = Math.min(cart.grandTotal, Math.max(1, Number(partialAmount.value)));
  } else {
    computedPaid = 0;
  }

  const payload = {
    customer_id: cart.customer.id,
    items: cart.items,
    discount_amount: cart.discountAmount,
    delivery_fee: cart.deliveryFee,
    payment_status: cart.paymentType,
    payment_method: cart.paymentType === 'unpaid' ? 'cash' : cart.paymentMethod,
    paid_amount: computedPaid,
    notes: cart.notes,
  };

  router.post('/pos', payload, {
    onSuccess: () => {
      cart.clearCart();
      showPaymentModal.value = false;
    },
    onFinish: () => {
      isSubmitting.value = false;
    }
  });
}

function createCustomer() {
  newCustForm.post('/customers', {
    onSuccess: (page) => {
      showQuickCustomerModal.value = false;
      newCustForm.reset();
    }
  });
}

function openShiftSubmit() {
  shiftForm.post('/pos/shift/open', {
    onSuccess: () => {
      showOpenShiftModal.value = false;
    }
  });
}

function closeShiftSubmit() {
  closeShiftForm.post('/pos/shift/close', {
    onSuccess: () => {
      showCloseShiftModal.value = false;
    }
  });
}
</script>

