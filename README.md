# PJU Surveyor

Website pendataan tiang PJU berdasarkan `prd.md`, dengan frontend Next.js dan backend Laravel API.

## Menjalankan

Frontend:

```bash
npm install
npm run dev
```

Buka `http://localhost:3000`.

Backend:

```bash
cd backend
/opt/lampp/bin/php /usr/bin/composer install
cp .env.example .env
/opt/lampp/bin/php artisan key:generate
/opt/lampp/bin/php artisan storage:link
/opt/lampp/bin/php artisan migrate --seed
/opt/lampp/bin/php artisan serve --host=127.0.0.1 --port=8000
```

Jika memakai LAMPP/XAMPP, buat database via MySQL LAMPP:

```bash
/opt/lampp/bin/mysql -h 127.0.0.1 -P 3306 -u root -e "CREATE DATABASE IF NOT EXISTS pju_surveyor CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Akun awal:

```text
surveyor@pju.local
password
```

## Script

```bash
npm run lint
npm run build
cd backend && php artisan test
```

## Catatan Runtime

- Environment lokal perlu PHP, Composer, dan MySQL/MariaDB.
- Frontend membaca API dari `NEXT_PUBLIC_PJU_API_URL`, default `http://127.0.0.1:8000/api`.
- Peta memakai Leaflet/OpenStreetMap tanpa API key.

## User TES
superadmin@pju.local / password
surveyor@pju.local / password
user@pju.local / password
