# Direpair Operations API

Laravel untuk booking servis, pelacakan status privat, persetujuan quotation, pembayaran, dan dashboard operasional.

- Production: [api.direpair.id](https://api.direpair.id/api/v1/health)
- Login operasional: [api.direpair.id/operations/login](https://api.direpair.id/operations/login)
- Runtime: PHP **8.3**, Composer 2.
- Dependency terkunci di [composer.lock](composer.lock).

## Instalasi lokal

Dari folder `apps/api`, pada instalasi baru:

```sh
composer install --no-interaction --prefer-dist
```

Salin `.env.example` menjadi `.env`, lalu:

```sh
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate --seed
php artisan serve --host=127.0.0.1 --port=8000
```

Jangan menimpa `.env` atau database yang sudah digunakan. Seeder menyediakan data demo untuk development dan dibatasi agar tidak dijalankan pada production.

Frontend lokal memakai `http://localhost:4321`; samakan origin dengan `FRONTEND_URL` dan `CORS_ALLOWED_ORIGINS`. Login lokal menggunakan nilai demo dalam template environment, bukan kredensial production.

## Endpoint utama

Base URL: `https://api.direpair.id/api/v1`.

| Method | Path | Fungsi |
| --- | --- | --- |
| GET | `/health` | Health check |
| POST | `/service-requests` | Mengirim booking servis |
| GET | `/status/{token}` | Melihat status melalui token privat |
| POST | `/status/{token}/quotations/{quotationUuid}/decision` | Menyetujui/menolak quotation |
| POST | `/payments/midtrans/webhook` | Callback pembayaran, divalidasi oleh backend |

Definisi route ada di [routes/api.php](routes/api.php). Dashboard menggunakan session login di `/operations`; bukan `/admin` atau `/login`. Token status bersifat privat dan tidak boleh dicantumkan di dokumentasi publik.

## Pengujian

Dari `apps/api`:

```sh
php artisan test --compact
composer validate --strict
composer audit
```

Test memakai database SQLite in-memory dari `phpunit.xml`. Jangan menjalankan test dengan konfigurasi database production. Formatter: `vendor/bin/pint`.

## Deployment cPanel

Target:

- Domain: `api.direpair.id`
- Root: `/home/direpair/api.direpair.id`
- WordPress pada akun hosting yang sama: `/home/direpair/cms.direpair.id`
- Frontend: Vercel, `https://direpair.id`

File [`.htaccess`](.htaccess) pada root aplikasi mengarahkan request ke `public/` serta melindungi file internal. Sertakan juga `public/.htaccess` saat upload. Jangan memindahkan `.env` atau source Laravel ke folder public.

### Instalasi pertama

1. Aktifkan PHP 8.3, Composer, dan ekstensi yang diminta Composer.
2. Buat database `direpair_direpair_api`, user `direpair_apiuser`, dan berikan akses ke database tersebut melalui cPanel.
3. Upload isi `apps/api` ke root API. Jangan upload `.env` lokal, database SQLite, `vendor` lokal, log, atau cache hasil laptop.
4. Di Terminal cPanel:

```sh
cd /home/direpair/api.direpair.id
bash deploy/setup-cpanel.sh
```

Script meminta password database dan hostname frontend. Masukkan **direpair.id** sebagai hostname frontend. Script membuat `.env` dari [`.env.cpanel.example`](.env.cpanel.example), menghasilkan key/token secara acak, memasang dependency production, menjalankan migration, membuat admin awal, dan mengoptimalkan konfigurasi.

Simpan password admin yang ditampilkan secara privat, lalu ganti setelah login. Script menolak menimpa `.env` yang sudah ada. Tidak perlu membagikan password atau token melalui pesan.

### Update deployment yang sudah berjalan

Backup database dan source aktif terlebih dahulu. Upload source baru tanpa menimpa `.env`, `storage/`, dan data production, lalu:

```sh
cd /home/direpair/api.direpair.id
composer install --no-dev --classmap-authoritative --no-interaction --prefer-dist
php artisan optimize:clear
php artisan migrate --force
php artisan optimize
```

Jangan menjalankan `migrate:fresh`, `db:wipe`, atau seeder demo di production. Jangan menjalankan ulang `key:generate` pada update rutin karena data terenkripsi dan session menggunakan key yang sudah ada.

### Konfigurasi production yang perlu dijaga

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.direpair.id
FRONTEND_URL=https://direpair.id
PUBLIC_STATUS_URL=https://direpair.id/cek-status/
CORS_ALLOWED_ORIGINS=https://direpair.id
CMS_ALLOWED_ORIGIN=https://cms.direpair.id
```

Password database, `APP_KEY`, dan `STATUS_TOKEN_PEPPER` tetap berada di `.env` hosting. Jangan merotasi token pepper tanpa rencana karena link status yang sudah beredar bergantung padanya.

Template memakai `PAYMENT_DRIVER=mock` dan `MAIL_MAILER=log`. Artinya pembayaran nyata dan pengiriman email belum diaktifkan hanya dengan menyalin template; konfigurasi provider serta uji sandbox diperlukan sebelum mengaktifkannya.

Jika memakai scheduler, tambahkan cron cPanel setiap menit (sesuaikan executable PHP jika hosting memakai path lain):

```sh
cd /home/direpair/api.direpair.id && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
```

Jika menambah job asynchronous pada database queue, siapkan worker sesuai fasilitas hosting. Vercel tidak menjalankan migration, scheduler, atau worker Laravel.

## Pemeriksaan setelah upload

1. `https://api.direpair.id/api/v1/health` mengembalikan status `ok`.
2. `https://api.direpair.id/operations/login` membuka form login.
3. File seperti `/.env` dan `/composer.json` tidak dapat diunduh dari web.
4. Frontend dapat mengakses API dengan origin yang benar; lakukan booking uji hanya dengan persetujuan pengelola production.
