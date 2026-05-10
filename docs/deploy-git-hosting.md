# Deploy via Git ke Hosting

Panduan ini untuk hosting shared/cPanel/Hostinger yang punya akses Git dan SSH.

## Struktur

Aplikasi aktif ada di folder `backend/`. Document root domain harus diarahkan ke:

```text
backend/public
```

Jangan arahkan domain ke root repository, karena file Laravel seperti `.env`, `vendor`, dan `storage` tidak boleh terbuka dari web.

## Langkah Pertama

1. Buat database MySQL di panel hosting.
2. Buat repository Git, lalu push project ini ke GitHub/GitLab/Bitbucket.
3. Di panel hosting, clone repository ke folder non-public, misalnya:

```text
/home/username/repositories/apptiang2
```

4. Set document root domain/subdomain ke:

```text
/home/username/repositories/apptiang2/backend/public
```

5. Masuk SSH ke folder repository:

```bash
cd /home/username/repositories/apptiang2
```

6. Jalankan deploy:

```bash
bash backend/scripts/deploy.sh
```

7. Edit `backend/.env` sesuai data hosting:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domainanda.com
FRONTEND_URL=https://domainanda.com

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=username_database
DB_PASSWORD=password_database

SANCTUM_STATEFUL_DOMAINS=domainanda.com,www.domainanda.com
```

8. Jalankan ulang deploy setelah `.env` benar:

```bash
bash backend/scripts/deploy.sh
```

## Deploy Berikutnya

Setelah update dari lokal:

```bash
git add .
git commit -m "Update aplikasi"
git push origin main
```

Di hosting:

```bash
cd /home/username/repositories/apptiang2
git pull origin main
bash backend/scripts/deploy.sh
```

Jika hosting mendukung auto deploy cPanel, file `.cpanel.yml` di root repository akan menjalankan script deploy setelah pull.

## Akun Awal

Script deploy tidak menjalankan seeder otomatis karena `DatabaseSeeder` menghapus data sample sebelum mengisi ulang. Untuk production, buat user lewat database/admin panel aplikasi.

Jika database masih kosong dan Anda memang ingin membuat akun sample, jalankan hanya sekali:

```bash
cd backend
php artisan db:seed --class=RbacUsersSeeder --force
```

Akun sample:

```text
superadmin@pju.local / password
surveyor@pju.local / password
user@pju.local / password
```

Segera ganti password setelah login.

## Jika Document Root Tidak Bisa Diubah

Opsi terbaik tetap meminta hosting mengarahkan domain ke `backend/public`. Jika benar-benar tidak bisa, taruh isi `backend/public` di `public_html`, lalu ubah dua path di `public_html/index.php` agar menunjuk ke folder backend:

```php
require __DIR__.'/../repositories/apptiang2/backend/vendor/autoload.php';
$app = require_once __DIR__.'/../repositories/apptiang2/backend/bootstrap/app.php';
```

Sesuaikan `../repositories/apptiang2` dengan lokasi clone di hosting.

## Troubleshooting

Jika halaman 500:

```bash
cd backend
php artisan optimize:clear
tail -n 80 storage/logs/laravel.log
```

Jika upload foto tidak tampil:

```bash
cd backend
php artisan storage:link --force
```

Jika command `composer` tidak ada tetapi hosting menyediakan `composer2`:

```bash
COMPOSER=composer2 bash backend/scripts/deploy.sh
```

Jika PHP CLI berbeda versi:

```bash
PHP=/usr/local/bin/php bash backend/scripts/deploy.sh
```
