# Laundry Management System (V2)

Aplikasi manajemen laundry modern berbasis web yang komprehensif, dirancang untuk memudahkan operasional bisnis laundry mulai dari pencatatan pesanan (POS), manajemen pelanggan, pelacakan status cucian, hingga pelaporan keuangan dan inventaris.

Aplikasi ini dibangun menggunakan tumpukan teknologi modern (**Laravel 11+ / 13**, **Vue 3**, **Inertia.js**, dan **Tailwind CSS v4**) untuk memberikan pengalaman pengguna yang cepat (Single Page Application) dan antarmuka yang responsif.

## 🚀 Fitur Utama

- **Point of Sale (POS) & Shift Kasir**
  Pencatatan transaksi yang mudah dan cepat, didukung dengan sistem buka/tutup shift kasir untuk keamanan arus kas harian.
- **Manajemen Pesanan & Pelacakan (Tracking)**
  Lacak proses pesanan dari tahap penerimaan, pencucian, hingga selesai. Menyediakan halaman pelacakan publik untuk pelanggan menggunakan nomor resi/invoice.
- **Workstation / Antrian Cuci**
  Sistem antrian dan pembaruan status cucian secara real-time untuk bagian produksi.
- **Manajemen Pelanggan & Deposit**
  Pencatatan data pelanggan beserta sistem saldo/deposit untuk kemudahan pembayaran di masa depan.
- **Manajemen Outlet & Struk Kustom**
  Kustomisasi profil outlet, pengaturan kustomisasi struk digital/cetak, dan integrasi WhatsApp API Token untuk notifikasi.
- **Manajemen Inventaris & Pengeluaran**
  Pemantauan stok bahan baku (chemical/deterjen) beserta fitur penyesuaian stok, serta pencatatan pengeluaran operasional (Expenses).
- **Sistem Penyimpanan (Rak)**
  Manajemen alokasi rak untuk menyimpan pakaian yang telah selesai diproses.
- **Komplain & Rewash (Cuci Ulang)**
  Penanganan komplain pelanggan melalui fitur rewash / garansi cuci ulang.
- **Laporan Keuangan & Komisi Karyawan**
  Laporan pendapatan, pengeluaran, serta perhitungan komisi karyawan yang dapat diekspor (Export PDF/Excel).
- **Manajemen Pengguna & Hak Akses (RBAC)**
  Pembatasan hak akses berbasis peran (Role-Based Access Control) menggunakan Spatie Permission. Memisahkan hak akses antara **Owner** dan **Cashier/Karyawan**.

## 🛠️ Teknologi yang Digunakan

**Backend:**
- [Laravel 13](https://laravel.com/)
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission/)
- [Barryvdh Laravel DomPDF](https://github.com/barryvdh/laravel-dompdf) (Untuk Ekspor PDF)

**Frontend:**
- [Vue.js 3](https://vuejs.org/) (Composition API)
- [Inertia.js](https://inertiajs.com/)
- [Tailwind CSS v4](https://tailwindcss.com/)
- [Pinia](https://pinia.vuejs.org/) (State Management)
- [Chart.js](https://www.chartjs.org/) & Vue-Chartjs (Untuk visualisasi data Dashboard)
- [Lucide Vue](https://lucide.dev/) (Ikon)

## 📦 Persyaratan Sistem

- PHP >= 8.3
- Composer 2.x
- Node.js >= 18.x & NPM/Yarn/Bun
- MySQL / PostgreSQL / SQLite

## ⚙️ Instalasi

Ikuti langkah-langkah di bawah ini untuk menjalankan aplikasi di lingkungan pengembangan lokal Anda:

1. **Clone repositori**
   ```bash
   git clone <url-repo-anda>
   cd laundry-v2
   ```

2. **Instal dependensi PHP (Composer)**
   ```bash
   composer install
   ```

3. **Instal dependensi Node (NPM)**
   ```bash
   npm install
   ```

4. **Konfigurasi Environment**
   Salin file `.env.example` menjadi `.env` lalu sesuaikan konfigurasi database Anda.
   ```bash
   cp .env.example .env
   ```

5. **Generate Application Key**
   ```bash
   php artisan key:generate
   ```

6. **Migrasi Database & Seeder**
   Jalankan migrasi untuk membuat tabel beserta data dummy (pengguna, role, layanan awal, dll).
   ```bash
   php artisan migrate --seed
   ```

7. **Kompilasi Aset Frontend (Vite)**
   ```bash
   npm run dev
   ```

8. **Jalankan Server Laravel**
   Buka terminal baru dan jalankan:
   ```bash
   php artisan serve
   ```

Aplikasi sekarang dapat diakses melalui `http://localhost:8000`.

## 📖 Panduan Penggunaan
Untuk mengetahui cara menggunakan aplikasi dari sisi Owner, Kasir, maupun Pelanggan, silakan baca **[Panduan Penggunaan (User Guide)](USER_GUIDE.md)** yang telah kami siapkan.

## 📖 Struktur Routing Utama (Roles)

- **Owner**: Memiliki akses penuh ke seluruh sistem, termasuk konfigurasi Outlet, Laporan, Manajemen Karyawan, Inventaris, dan Hak Akses Destruktif (Hapus pesanan, dll).
- **Cashier**: Memiliki akses ke POS, Buka/Tutup Shift, Manajemen Pesanan, dan Master Data Operasional (Pelanggan, Layanan).
- **Produksi/Karyawan Cuci**: Dapat mengakses Workstation untuk mengubah status pengerjaan pesanan.
- **Publik**: Dapat mengakses halaman pelacakan `/track/{invoice}` tanpa perlu login.

## 📄 Lisensi

Proyek ini bersifat tertutup (Private) / Sesuaikan dengan lisensi yang Anda gunakan.
