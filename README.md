# PJU Surveyor

Aplikasi pendataan tiang PJU berbasis Laravel.

Frontend dan backend sekarang berjalan dalam satu aplikasi Laravel di folder `backend/`. UI memakai Blade, form Laravel, session login, dan route web biasa. Frontend Next.js lama sudah dibersihkan karena tidak diperlukan untuk deploy Hostinger.

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
http://127.0.0.1:8000/dashboard
```

Jika memakai LAMPP/XAMPP, buat database via MySQL LAMPP:

```bash
/opt/lampp/bin/mysql -h 127.0.0.1 -P 3306 -u root -e "CREATE DATABASE IF NOT EXISTS pju_surveyor CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

## Deploy via Git ke Hosting

Panduan lengkap ada di [docs/deploy-git-hosting.md](docs/deploy-git-hosting.md).

Ringkasnya:

```bash
git pull origin main
bash backend/scripts/deploy.sh
```

Pastikan document root domain mengarah ke:

```text
/path/ke/repository/backend/public
```

Contoh `.env` production tersedia di [backend/.env.production.example](backend/.env.production.example). Setelah deploy pertama, edit `backend/.env` dan isi nilai domain serta database:

```env
APP_URL=https://domainanda.com
FRONTEND_URL=https://domainanda.com
DB_DATABASE=nama_database
DB_USERNAME=username_database
DB_PASSWORD=password_database
```

Jangan jalankan `php artisan migrate --seed` di production kecuali benar-benar ingin mengisi ulang data sample. Seeder utama proyek ini menghapus data tiang sample sebelum mengisi ulang.

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
