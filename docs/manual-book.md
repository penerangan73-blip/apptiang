# Manual Book Aplikasi Inventarisasi APJ Non APP

## 1. Pendahuluan

### 1.1 Tentang Aplikasi

Aplikasi Inventarisasi APJ Non APP adalah sistem pendataan aset penerangan jalan umum berbasis web. Aplikasi ini digunakan untuk mencatat data tiang, memantau kondisi aset, melihat sebaran titik aset di peta, mencatat laporan aktivitas lapangan, dan mengelola pengguna.

### 1.2 Tujuan Penggunaan

Manual book ini membantu pengguna memahami cara:

- Masuk ke aplikasi.
- Membaca ringkasan data di dashboard.
- Menambah, mengubah, mencari, menghapus, dan mengekspor data tiang.
- Melihat data tiang pada peta.
- Membuat laporan kerusakan atau aktivitas lapangan.
- Mengelola profil dan password.
- Mengelola akun pengguna untuk superadmin.

### 1.3 Kebutuhan Akses

Aplikasi dapat dibuka melalui browser seperti Google Chrome, Microsoft Edge, Firefox, atau Safari. Untuk penggunaan lapangan, browser mobile dapat digunakan selama perangkat memiliki akses internet dan izin lokasi jika ingin memakai fitur Ambil Lokasi.

## 2. Hak Akses Pengguna

### 2.1 Superadmin

Superadmin memiliki akses penuh untuk:

- Melihat dashboard.
- Mengelola data tiang.
- Melihat dan menggunakan peta.
- Mengelola aktivitas/laporan.
- Menghapus foto tiang dan foto laporan.
- Mengelola akun pengguna.
- Mengubah profil sendiri.

### 2.2 Surveyor

Surveyor memiliki akses operasional untuk:

- Melihat dashboard.
- Menambah dan mengubah data tiang.
- Menghapus data tiang.
- Mengunggah dan menghapus foto tiang/laporan.
- Membuat laporan aktivitas atau kerusakan.
- Melihat peta dan data aset.
- Mengubah profil sendiri.

### 2.3 User

User adalah akun baca data. User dapat:

- Melihat dashboard.
- Melihat data tiang.
- Melihat peta.
- Melihat aktivitas.
- Mengubah profil sendiri.

User tidak dapat menambah, mengubah, atau menghapus data tiang, laporan, foto, maupun akun pengguna.

## 3. Login dan Logout

### 3.1 Login

1. Buka alamat aplikasi di browser.
2. Masukkan email dan password.
3. Tekan tombol Login.
4. Jika data benar, pengguna akan diarahkan ke Dashboard.

### 3.2 Logout

1. Tekan tombol profil di kanan atas.
2. Pilih Keluar.
3. Sistem akan mengakhiri sesi pengguna dan kembali ke halaman login.

## 4. Dashboard

Dashboard adalah halaman ringkasan kondisi aset.

### 4.1 Informasi yang Ditampilkan

Dashboard menampilkan:

- Total Tiang.
- Kondisi Tiang Baik.
- Kondisi Tiang Rusak.
- Kondisi Tiang Perbaikan.
- Total Daya Lampu dalam watt.
- Aktivitas hari ini.
- Peta ringkas sebaran aset.
- Daftar aset terbaru.

### 4.2 Tombol Cepat

Pada dashboard tersedia tombol:

- Tambah Tiang: membuka halaman Data Tiang untuk menambah aset. Tombol ini hanya tampil untuk superadmin dan surveyor.
- Buka Peta: membuka halaman peta aset.

## 5. Data Tiang PJU

Menu Data Tiang digunakan untuk mengelola inventaris tiang.

### 5.1 Melihat Data Tiang

1. Pilih menu Data Tiang.
2. Sistem menampilkan daftar tiang yang sudah tersimpan.
3. Gunakan tabel untuk melihat ID pelanggan, alamat, kondisi, dan informasi lainnya.

### 5.2 Mencari dan Memfilter Data

1. Masukkan kata kunci pada kolom pencarian.
2. Kata kunci dapat berupa isi kolom data tiang, misalnya ID pelanggan, alamat, koordinat, daya lampu, kondisi, jenis lampu, KWH meter, tanggal input, atau nama penginput.
3. Pilih kondisi jika ingin memfilter data berdasarkan Baik, Rusak, atau Perbaikan.
4. Tekan Cari.

### 5.3 Menambah Data Tiang

Fitur ini hanya tersedia untuk superadmin dan surveyor.

1. Pilih menu Data Tiang.
2. Tekan Tambah Tiang Baru.
3. Isi data berikut:
   - ID Pelanggan.
   - Alamat.
   - Latitude.
   - Longitude.
   - Daya Lampu.
   - Kondisi Tiang.
   - Jenis Tiang.
   - Kepemilikan.
   - Tinggi Tiang.
   - Tipe Ornamen.
   - Jenis Lampu.
   - Status Lampu.
   - Kondisi Rumah Lampu.
   - Jumlah Titik Lampu.
   - KWH Meter.
   - Foto.
   - Catatan.
4. Untuk menentukan koordinat, gunakan peta pinpoint:
   - Klik titik pada peta atau geser pin.
   - Tekan Set Lokasi.
   - Jika ingin memakai lokasi perangkat, tekan Ambil Lokasi dan izinkan akses lokasi pada browser.
5. Tekan Simpan.

Setelah data tiang disimpan, sistem otomatis membuat catatan aktivitas bahwa data pelanggan tersebut ditambahkan ke inventaris.

### 5.4 Mengubah Data Tiang

Fitur ini hanya tersedia untuk superadmin dan surveyor.

1. Pilih menu Data Tiang.
2. Cari data tiang yang akan diubah.
3. Tekan tombol Edit.
4. Perbarui data yang diperlukan.
5. Jika menambahkan foto baru, pilih atau ambil foto dari perangkat.
6. Tekan Simpan.

Setelah data tiang diperbarui, sistem otomatis membuat catatan aktivitas bahwa data pelanggan tersebut diperbarui.

### 5.5 Menghapus Data Tiang

Fitur ini hanya tersedia untuk superadmin dan surveyor.

1. Pilih menu Data Tiang.
2. Cari data tiang yang akan dihapus.
3. Tekan Hapus.
4. Konfirmasi penghapusan.

Gunakan fitur ini dengan hati-hati karena data tiang yang dihapus tidak tampil lagi pada daftar dan peta.

### 5.6 Mengunduh Data Tiang

1. Pilih menu Data Tiang.
2. Jika perlu, lakukan pencarian atau filter kondisi terlebih dahulu.
3. Tekan Download Data.
4. Sistem akan mengunduh file Excel berisi data sesuai filter aktif.

Kolom export mencakup ID pelanggan, alamat, koordinat, daya, kondisi, jenis tiang, tinggi, bentangan, pondasi, kepemilikan, trafo PLN, waktu input, penginput, waktu update, pengupdate, URL foto, dan keterangan.

## 6. Detail Tiang

Halaman detail tiang digunakan untuk melihat informasi lengkap satu aset.

### 6.1 Membuka Detail Tiang

1. Buka Data Tiang.
2. Tekan tombol Detail pada data yang ingin dilihat.
3. Sistem menampilkan detail aset, foto, histori aktivitas, dan peta kecil lokasi tiang.

### 6.2 Foto Tiang

Foto tiang dapat dilihat pada halaman detail. Jika pengguna memiliki hak kelola, foto dapat dihapus melalui tombol hapus pada foto.

### 6.3 Histori Aktivitas

Histori aktivitas menampilkan laporan atau perubahan yang pernah dicatat pada tiang tersebut, termasuk foto laporan jika tersedia.

## 7. Peta

Menu Peta digunakan untuk melihat sebaran aset pada peta interaktif.

### 7.1 Melihat Titik Tiang

1. Pilih menu Peta.
2. Sistem menampilkan marker/titik tiang berdasarkan data koordinat.
3. Klik marker untuk melihat ringkasan data tiang.
4. Panel Inspeksi Terpilih akan menampilkan detail singkat aset yang dipilih.

### 7.2 Warna Marker

Warna marker menunjukkan kondisi tiang:

- Baik: hijau.
- Rusak: merah.
- Perbaikan: kuning/oranye.

### 7.3 Filter Peta

1. Gunakan kolom pencarian pada halaman Peta.
2. Masukkan kata kunci dari kolom data tiang, misalnya ID pelanggan, alamat, koordinat, daya lampu, kondisi, jenis lampu, KWH meter, tanggal, tahun, atau nama penginput.
3. Pilih kondisi jika diperlukan.
4. Tekan Cari.

Peta dan tabel akan menampilkan data sesuai filter.

### 7.4 Menampilkan 2 Layer

Pada halaman Peta tersedia tombol Tampilkan 2 Layer. Tombol ini menampilkan layer tambahan:

- Administrasi Area.
- Batas Administrasi.

Tekan tombol Tampilkan 2 Layer untuk menyalakan layer. Tekan Tutup 2 Layer untuk menyembunyikannya kembali.

## 8. Aktivitas

Menu Aktivitas berisi rekap kegiatan pendataan dan laporan kerusakan.

### 8.1 Melihat Aktivitas

1. Pilih menu Aktivitas.
2. Sistem menampilkan daftar aktivitas berdasarkan tanggal, ID pelanggan, jalan, catatan, dan foto.

### 8.2 Filter Tanggal

1. Pilih tanggal pada kolom tanggal.
2. Tekan Filter.
3. Sistem menampilkan aktivitas pada tanggal tersebut.
4. Tekan Reset untuk menghapus filter tanggal.

### 8.3 Membuat Laporan Kerusakan

Fitur ini tersedia untuk superadmin dan surveyor.

1. Buka dialog laporan dari menu yang menyediakan tombol laporan.
2. Pilih tiang yang dilaporkan.
3. Isi catatan laporan.
4. Unggah atau ambil foto laporan jika tersedia.
5. Tekan Simpan.

Saat laporan kerusakan disimpan, kondisi tiang terkait otomatis diperbarui menjadi Rusak.

### 8.4 Menghapus Foto Laporan

Fitur ini tersedia untuk superadmin dan surveyor.

1. Buka menu Aktivitas atau Detail Tiang.
2. Cari foto laporan yang ingin dihapus.
3. Tekan tombol hapus foto.
4. Konfirmasi penghapusan.

## 9. Manajemen User

Menu Manajemen User hanya tersedia untuk superadmin.

### 9.1 Menambah User

1. Pilih menu Manajemen User.
2. Tekan Tambah User.
3. Isi nama, email, telepon, alamat, role, dan password.
4. Tekan Simpan User.

Role yang tersedia:

- Superadmin.
- Surveyor.
- User.

### 9.2 Mengubah User

1. Pilih menu Manajemen User.
2. Tekan Edit pada akun yang ingin diubah.
3. Perbarui data.
4. Kosongkan password jika tidak ingin mengganti password.
5. Tekan Simpan User.

Catatan: Superadmin terakhir tidak dapat diturunkan rolenya.

### 9.3 Menghapus User

1. Pilih menu Manajemen User.
2. Tekan Hapus pada akun yang ingin dihapus.
3. Konfirmasi penghapusan.

Catatan:

- Pengguna tidak dapat menghapus akunnya sendiri.
- Superadmin terakhir tidak dapat dihapus.

## 10. Profil Pengguna

### 10.1 Mengubah Profil

1. Tekan tombol profil di kanan atas.
2. Pilih Ubah Profil.
3. Ubah nama, telepon, alamat, atau foto profil.
4. Simpan perubahan.

### 10.2 Mengubah Password

1. Tekan tombol profil di kanan atas.
2. Pilih Ubah Password.
3. Masukkan password baru sesuai ketentuan.
4. Simpan perubahan.

## 11. Foto dan Penyimpanan File

### 11.1 Lokasi Penyimpanan Foto

Foto disimpan pada storage public Laravel.

Foto tiang:

```text
storage/app/public/poles
```

Foto laporan:

```text
storage/app/public/reports
```

Foto profil:

```text
storage/app/public/profiles
```

URL foto disimpan di database pada kolom terkait, misalnya:

- pole_photos.photo_url.
- daily_report_photos.photo_url.
- users.photo_url.

### 11.2 Syarat Foto

Aturan unggah foto:

- Maksimal 10 foto untuk satu input.
- Ukuran maksimal tiap foto 5 MB.
- File harus berupa gambar.

### 11.3 Agar Foto Tampil di Hosting

Pastikan storage public sudah terhubung ke folder public:

```bash
php artisan storage:link
```

Perintah ini membuat akses:

```text
public/storage -> storage/app/public
```

## 12. Catatan Admin dan Hosting

### 12.1 Sinkronisasi Database

Jika ada perubahan struktur database, jalankan migration di hosting:

```bash
php artisan migrate --force
```

Sebelum menjalankan migration di production, lakukan backup database terlebih dahulu.

### 12.2 Deploy Aplikasi

Alur deploy umum:

```bash
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Jika memakai script deploy dari repository, jalankan:

```bash
bash scripts/deploy.sh
```

### 12.3 Document Root

Pastikan document root domain mengarah ke folder:

```text
public
```

Contoh:

```text
/path/ke/repository/public
```

### 12.4 File Environment

Pastikan file .env production sudah berisi konfigurasi benar:

```env
APP_URL=https://domainanda.com
DB_DATABASE=nama_database
DB_USERNAME=username_database
DB_PASSWORD=password_database
```

## 13. Troubleshooting

### 13.1 Tidak Bisa Login

Periksa:

- Email sudah benar.
- Password sudah benar.
- Akun masih tersedia di Manajemen User.

### 13.2 Peta Tidak Muncul

Periksa:

- Koneksi internet aktif.
- Browser tidak memblokir script peta.
- Data latitude dan longitude valid.

### 13.3 Tombol Ambil Lokasi Tidak Berfungsi

Periksa:

- Browser sudah diberi izin lokasi.
- Perangkat memiliki GPS atau layanan lokasi aktif.
- Website dibuka melalui HTTPS jika di hosting production.

### 13.4 Foto Tidak Tampil

Periksa:

- File foto ada di storage.
- Perintah php artisan storage:link sudah dijalankan.
- APP_URL pada .env sesuai domain aplikasi.
- Folder storage memiliki permission yang cukup.

### 13.5 Data Tidak Muncul Setelah Deploy

Periksa:

- Koneksi database pada .env sudah benar.
- Migration sudah dijalankan.
- Cache konfigurasi sudah diperbarui dengan php artisan config:cache.

## 14. Praktik Penggunaan yang Disarankan

- Gunakan ID Pelanggan yang konsisten dan unik.
- Lengkapi koordinat sebelum menyimpan data tiang.
- Unggah foto lapangan yang jelas.
- Gunakan catatan untuk menjelaskan kondisi khusus.
- Filter data sebelum export jika hanya membutuhkan sebagian data.
- Backup database sebelum migration atau penghapusan data besar.
