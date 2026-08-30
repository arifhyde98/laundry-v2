# Panduan Penggunaan Aplikasi Laundry V2

Panduan ini ditujukan untuk membantu Anda (Owner maupun Karyawan/Kasir) dalam menggunakan aplikasi manajemen laundry.

---

## 1. Login & Memulai Aplikasi
1. Buka halaman aplikasi melalui browser (contoh: `http://localhost:8000`).
2. Masukkan email dan password Anda. (Hubungi Owner jika Anda belum memiliki akun).
3. Setelah login, Anda akan diarahkan ke halaman **Dashboard** yang menampilkan ringkasan informasi (jumlah pesanan, pendapatan hari ini, dll).

---

## 2. Manajemen Shift (Khusus Kasir / Owner)
Sebelum dapat membuat transaksi di halaman POS, kasir **wajib** membuka shift terlebih dahulu.

### Membuka Shift
1. Buka menu **POS** (Point of Sale).
2. Jika shift belum dibuka, sistem akan meminta Anda memasukkan **Saldo Awal / Modal Kasir**.
3. Masukkan nominal uang yang ada di laci kasir saat ini, lalu klik **Buka Shift**.

### Menutup Shift
1. Buka menu **POS** atau klik icon profil/shift di bagian atas.
2. Pilih opsi **Tutup Shift**.
3. Sistem akan menghitung total transaksi selama shift Anda.
4. Masukkan **Uang Fisik Aktual** yang ada di laci kasir untuk melihat apakah ada selisih (minus/plus).
5. Klik **Konfirmasi Tutup Shift**.

---

## 3. Transaksi Baru (POS)
1. Buka menu **POS**.
2. **Pilih Pelanggan**: Cari nama pelanggan atau tambah pelanggan baru jika belum terdaftar.
3. **Pilih Layanan**: Klik pada layanan yang ingin dipesan (misal: Cuci Kering, Setrika, Cuci Komplit).
4. Masukkan jumlah / berat (kg) untuk layanan tersebut.
5. (Opsional) Tambahkan diskon atau pilih pembayaran menggunakan **Deposit/Saldo** pelanggan.
6. Simpan pesanan. Sistem akan menghasilkan nomor invoice dan struk (PDF/Print) yang bisa diberikan kepada pelanggan.

---

## 4. Alur Proses Pengerjaan (Workstation)
Setelah pesanan masuk, bagian produksi dapat memperbarui status pesanan.
1. Buka menu **Workstation / Antrian Cuci**.
2. Anda akan melihat daftar pesanan yang sedang dalam antrean.
3. Ubah status pesanan sesuai dengan pengerjaan aktual (contoh: *Menunggu* ➔ *Sedang Dicuci* ➔ *Sedang Disetrika* ➔ *Selesai*).
4. Pembaruan status ini akan otomatis mengubah status pelacakan untuk pelanggan.

---

## 5. Penyimpanan Rak (Racks)
Setelah pakaian selesai diproses (status selesai), Anda bisa menempatkan pakaian ke dalam rak agar mudah dicari.
1. Buka menu **Racks**.
2. Pilih pesanan yang sudah selesai dan tempatkan ke dalam rak yang kosong.
3. Saat pelanggan datang mengambil, Anda dapat melihat informasi di rak mana pakaian mereka disimpan.

---

## 6. Pembayaran & Pengambilan (Checkout)
1. Buka menu **Orders / Pesanan** atau cari nomor Invoice di kolom pencarian.
2. Jika pesanan belum dibayar, klik tombol **Bayar**. Masukkan jumlah uang yang diterima.
3. Jika pelanggan sudah mengambil pakaiannya, ubah status pesanan menjadi **Diambil** (Completed).

---
### C. Cetak Struk Cepat (Direct Kiosk Printing)
Agar saat tombol "Cetak Struk" diklik kasir, mesin printer langsung mencetak tanpa memunculkan jendela *preview browser* (layaknya sistem POS desktop asli), Anda perlu mengubah *shortcut* browser Chrome di komputer Kasir:
1. Klik kanan ikon Google Chrome di Desktop Anda, pilih **Properties**.
2. Pada kolom **Target**, tambahkan spasi dan kode `--kiosk-printing` di bagian paling belakang (Contoh: `"C:\Program Files\Google\Chrome\Application\chrome.exe" --kiosk-printing`).
3. Buka Chrome melalui ikon tersebut, lakukan cetak struk sekali, dan Chrome tidak akan bertanya lagi.
## 7. Fitur Owner & Manajer
Pemilik (Owner) memiliki akses ke fitur-fitur khusus yang tidak dapat diakses kasir:

### A. Manajemen Outlet & WhatsApp Gateway
* Buka menu **Outlet**. Di sini Anda bisa mengatur nama laundry, alamat, nomor telepon, logo, dan teks *footer/header* pada struk cetak.
* **Integrasi WhatsApp:** Masukkan **Token API Fonnte** Anda dan centang tombol "Aktifkan Kirim WA Otomatis". Sistem akan otomatis nge-WA pelanggan setiap kali pesanan baru dibuat atau saat pakaian sudah siap diambil.
* Di halaman ini juga terdapat tabel **Log Riwayat Pengiriman WA** untuk memastikan apakah pesan sukses terkirim ke pelanggan atau gagal.

### B. Laporan & Keuangan (Reports & Expenses)
* **Expenses / Pengeluaran**: Catat pengeluaran operasional (seperti beli bensin, listrik, komisi tambahan) agar laporan laba/rugi sangat presisi.
* **Reports / Laporan**: Lihat 4 Kartu Metrik (Omset, Kas Masuk, Beban Operasional, dan **Laba Bersih Riil**).
* Dilengkapi dengan tabel **Rekap Z-Report Shift Kasir** untuk melacak selisih uang di laci.
* **Ekspor Data:** Anda dapat mencetak laporan keuangan ke kertas A4 (dengan desain rapi) atau mengunduhnya dalam format Excel (CSV).

### C. Manajemen Inventaris
* Buka menu **Inventory**. Anda bisa mencatat stok bahan kimia (deterjen, pewangi).
* Gunakan fitur **Adjust Stock** jika ada stok yang terpakai atau masuk.

### D. Manajemen Karyawan (Users)
* Tambah, edit, atau nonaktifkan akun karyawan.
* Atur peran (Role) apakah mereka sebagai **Kasir** atau **Owner**.

---

## 8. Pelacakan Publik (Tracking)
Pelanggan Anda dapat melacak status cucian mereka secara mandiri tanpa harus bertanya via WhatsApp.
1. Berikan URL: `namadomain.com/track/`
2. Pelanggan cukup memasukkan **Nomor Invoice / Resi** yang tertera di struk mereka.
3. Sistem akan menampilkan rincian pesanan dan status terkini (misal: Sedang Disetrika).
