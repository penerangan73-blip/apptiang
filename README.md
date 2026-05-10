# PJU Surveyor

Aplikasi pendataan tiang PJU berbasis Laravel.

Frontend dan backend sekarang berjalan dalam satu aplikasi Laravel di folder `backend/`. UI memakai Blade, form Laravel, session login, dan route web biasa. Folder `frontend/` adalah frontend Next.js lama dan tidak diperlukan untuk deploy Hostinger.

## Struktur Aktif

```text
backend/
  app/Http/Controllers/Api/   API lama, tetap tersedia
  app/Http/Controllers/Web/   Controller halaman Blade
  resources/views/            Frontend Laravel Blade
  routes/api.php              API Sanctum
  routes/web.php              Halaman web Laravel
```

## Menjalankan Lokal

```bash
cd backend
/opt/lampp/bin/php /usr/bin/composer install
cp .env.example .env
/opt/lampp/bin/php artisan key:generate
/opt/lampp/bin/php artisan storage:link
/opt/lampp/bin/php artisan migrate --seed
/opt/lampp/bin/php artisan serve --host=127.0.0.1 --port=8000
```

Buka:

```text
http://127.0.0.1:8000
```

Jika memakai LAMPP/XAMPP, buat database via MySQL LAMPP:

```bash
/opt/lampp/bin/mysql -h 127.0.0.1 -P 3306 -u root -e "CREATE DATABASE IF NOT EXISTS pju_surveyor CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

## Deploy ke Hostinger

Upload hanya aplikasi Laravel di folder `backend`.

1. Buat database MySQL di hPanel.
2. Upload isi folder `backend` ke hosting.
3. Arahkan document root domain ke folder `public`.
4. Jika Hostinger tidak memberi opsi document root, pindahkan isi `backend/public` ke `public_html`, lalu sesuaikan path `vendor/autoload.php` dan `bootstrap/app.php` di `public_html/index.php`.
5. Lewat SSH, jalankan:

```bash
composer2 install --no-dev --optimize-autoloader
cp .env.production.example .env
php artisan key:generate
php artisan storage:link
php artisan migrate --seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Contoh `.env` production tersedia di:

```text
backend/.env.production.example
```

Pastikan nilai database dan domain sudah diganti:

```env
APP_URL=https://domainanda.com
FRONTEND_URL=https://domainanda.com
DB_DATABASE=nama_database
DB_USERNAME=username_database
DB_PASSWORD=password_database
```

## Script

```bash
cd backend && php artisan route:list
cd backend && php artisan test
```

## Akun Tes

```text
superadmin@pju.local / password
surveyor@pju.local / password
user@pju.local / password
```
