# Tutorial Final Deployment Direpair

Panduan ini memakai topologi production berikut:

| Aplikasi | Domain | Lokasi / platform |
|---|---|---|
| Frontend Astro | hostname gratis Vercel, lalu `https://direpair.id` | Vercel, terhubung ke Git |
| Laravel API + Operations | `https://api.direpair.id` | `/home/direpair/api.direpair.id` di cPanel |
| WordPress CMS | `https://cms.direpair.id` | `/home/direpair/cms.direpair.id` di cPanel |

Astro tidak lagi diunggah ke `public_html`. Push ke branch production akan dideploy otomatis oleh Vercel. Perubahan konten WordPress juga memicu build Vercel melalui Deploy Hook, sehingga editor cukup mengisi formulir lalu menekan **Terbitkan/Perbarui**.

> Jangan menebak hostname Vercel. Nama seperti `direpair.vercel.app` hanya tersedia bila belum dipakai. Setelah deploy pertama, salin hostname production persis dari dashboard Vercel.

## 1. Persiapan lokal

Jalankan dari `C:\laragon\www\direpair`:

```bat
corepack pnpm install --frozen-lockfile
corepack pnpm check:web
corepack pnpm build:vercel
corepack pnpm test:api
infra\cpanel\package-cpanel.cmd
```

Hasil upload cPanel:

```text
release/cpanel/direpair-api.zip
release/cpanel/direpair-content.zip
```

Frontend tidak dibuat menjadi ZIP karena source-nya akan dibangun langsung oleh Vercel dari Git.

## 2. Siapkan Git untuk Vercel

Vercel perlu repository Git agar setiap push dapat menghasilkan deployment otomatis.

1. Buat repository private di GitHub, GitLab, atau Bitbucket.
2. Push seluruh repository Direpair, termasuk `pnpm-lock.yaml`, `pnpm-workspace.yaml`, `apps/web/vercel.json`, dan source `apps/web`.
3. Jangan commit file `.env`, folder `vendor`, `node_modules`, `dist`, atau `.vercel`.
4. Tentukan branch production, misalnya `main`.

## 3. Pasang WordPress CMS di cPanel

### 3.1 Instal WordPress

Di cPanel, pastikan document root `cms.direpair.id` adalah:

```text
/home/direpair/cms.direpair.id
```

Instal WordPress langsung ke folder tersebut memakai WordPress Manager/Softaculous atau instalasi manual. Setelah instalasi:

1. buka `https://cms.direpair.id/wp-admin/`;
2. masuk ke **Settings → Permalinks**;
3. pilih **Post name** lalu simpan;
4. pastikan `https://cms.direpair.id/wp-json/` menampilkan JSON, bukan 404.

Gunakan PHP 8.2 atau 8.3 dan aktifkan ekstensi umum WordPress: `curl`, `dom`, `fileinfo`, `gd`/`imagick`, `intl`, `mbstring`, `mysqli`, `openssl`, dan `zip`.

### 3.2 Pasang plugin Direpair

Cara termudah:

1. buka **Plugins → Add New Plugin → Upload Plugin**;
2. upload `release/cpanel/direpair-content.zip`;
3. instal dan aktifkan **Direpair Content**.

Alternatif melalui File Manager: extract ZIP sehingga file utama berada di:

```text
/home/direpair/cms.direpair.id/wp-content/plugins/direpair-content/direpair-content.php
```

Plugin ini menyediakan formulir kolom terstruktur untuk Layanan, Masalah, Lokasi, Teknisi, Harga, FAQ, Garansi, Artikel, dan Kebijakan. Gutenberg dinonaktifkan untuk seluruh tipe konten Direpair. Menu Posts, Pages, dan Comments yang tidak dipakai juga disembunyikan agar editor nonteknis tidak salah masuk.

### 3.3 Isi konten production

Untuk setiap konten:

1. isi judul, ringkasan, isi lengkap, harga, relasi, gambar, dan kolom lain yang relevan;
2. aktifkan **Konten sudah diverifikasi**;
3. jangan aktifkan label demo untuk konten asli;
4. khusus kasus perbaikan pelanggan, aktifkan persetujuan publikasi hanya jika benar-benar sudah diperoleh;
5. tekan **Terbitkan**.

Konten demo atau belum diverifikasi sengaja tidak dikirim oleh REST API production.

Verifikasi CMS:

```text
https://cms.direpair.id/wp-json/direpair/v1/health
https://cms.direpair.id/wp-json/direpair/v1/services
```

## 4. Deploy frontend Astro ke Vercel

### 4.1 Import project

1. Masuk ke Vercel dan pilih **Add New → Project**.
2. Import repository Direpair.
3. Isi **Root Directory** dengan `apps/web`.
4. Pilih framework **Astro** bila tidak terdeteksi otomatis.
5. Biarkan `vercel.json` memakai build command `corepack pnpm build:vercel`.
6. Jangan isi Output Directory secara manual; adapter Astro membuat `.vercel/output`.

### 4.2 Environment Variables

Tambahkan variabel berikut untuk environment **Production** dan **Preview**:

```dotenv
PUBLIC_API_BASE_URL=https://api.direpair.id/api/v1
CMS_BASE_URL=https://cms.direpair.id/wp-json/direpair/v1
CMS_FIXTURE_FALLBACK=false
```

Pada deploy awal, jangan tambahkan `PUBLIC_SITE_URL`. Aktifkan opsi Vercel **Automatically expose System Environment Variables** agar Astro dapat memakai `VERCEL_PROJECT_PRODUCTION_URL` sebagai canonical hostname sementara.

Tekan **Deploy**. Setelah berhasil:

1. buka **Project → Settings → Domains**;
2. salin hostname production `*.vercel.app` yang benar-benar diberikan;
3. buka homepage, halaman layanan, booking, dan `/cek-status/`;
4. simpan hostname itu untuk setup Laravel pada langkah berikutnya.

Setiap push berikutnya ke branch production akan membuat production deployment baru. Push ke branch lain menghasilkan Preview Deployment.

## 5. Deploy Laravel API di cPanel

### 5.1 Database

Di **MySQL Databases**, buat tepat seperti template bawaan:

```text
Database: direpair_direpair_api
User:     direpair_apiuser
```

Berikan user tersebut **ALL PRIVILEGES** ke database dan simpan passwordnya.

### 5.2 Upload dan setup

Upload `release/cpanel/direpair-api.zip` ke:

```text
/home/direpair/api.direpair.id
```

Extract langsung di folder tersebut. File berikut harus ada:

```text
/home/direpair/api.direpair.id/.htaccess
/home/direpair/api.direpair.id/artisan
/home/direpair/api.direpair.id/public/index.php
/home/direpair/api.direpair.id/deploy/setup-cpanel.sh
```

Root `.htaccess` sudah mengarahkan domain ke folder `public` dan memblokir `.env`, source, `vendor`, serta storage privat. Karena itu document root domain tetap `/home/direpair/api.direpair.id`; tidak perlu diarahkan ulang ke `public`.

Buka **Terminal** cPanel dan jalankan:

```bash
cd /home/direpair/api.direpair.id
chmod +x deploy/setup-cpanel.sh
bash deploy/setup-cpanel.sh
```

Skrip meminta:

1. password database `direpair_apiuser`;
2. hostname Vercel tanpa `https://`, misalnya `direpair-abc.vercel.app`.

Skrip kemudian membuat `.env`, memasang dependency production, membuat `APP_KEY`, menjalankan migration, membuat akun Operations dengan password acak, dan mengoptimalkan Laravel. Salin password Operations yang tampil satu kali di terminal.

Verifikasi:

```text
https://api.direpair.id/api/v1/health
https://api.direpair.id/operations/login
```

Jika root domain masih 403, pastikan file `.htaccess` tersembunyi ikut ter-upload dan fitur Apache `mod_rewrite`/override diaktifkan oleh hosting.

## 6. Hubungkan publish CMS ke Vercel

### 6.1 Buat Deploy Hook

Di Vercel:

1. buka **Project → Settings → Git → Deploy Hooks**;
2. buat hook bernama `Direpair CMS Production`;
3. pilih branch production, misalnya `main`;
4. salin URL hook yang diberikan.

URL Deploy Hook adalah kredensial. Jangan menaruhnya di repository, screenshot publik, atau frontend.

### 6.2 Simpan hook di WordPress

Di WordPress:

1. buka **Settings → Direpair Content**;
2. pada bagian **Pembaruan otomatis ke Vercel**, tempel URL Deploy Hook;
3. simpan.

Plugin menunggu sekitar 30 detik agar beberapa edit berurutan digabung, lalu memanggil Vercel. Status request terakhir terlihat di halaman setting tersebut.

### 6.3 Aktifkan cron cPanel

Tambahkan dua cron job dengan jadwal **Once Per Minute**. Sesuaikan path PHP bila cPanel menampilkan path berbeda:

```cron
* * * * * /usr/local/bin/php -q /home/direpair/cms.direpair.id/wp-cron.php >/dev/null 2>&1
* * * * * /usr/local/bin/php /home/direpair/api.direpair.id/artisan schedule:run >/dev/null 2>&1
```

Setelah cron WordPress aktif, tambahkan di `wp-config.php` sebelum komentar `That's all, stop editing`:

```php
define('DISABLE_WP_CRON', true);
```

Uji otomatisasi:

1. ubah satu konten terverifikasi lalu tekan **Perbarui**;
2. tunggu 1–2 menit;
3. pastikan deployment baru muncul di Vercel;
4. setelah build sukses, refresh halaman terkait di frontend.

Tidak perlu upload Astro secara manual setelah editor mengubah konten. Rebuild tetap wajib secara teknis karena halaman publik diprerender, tetapi Deploy Hook menjalankannya otomatis.

## 7. Uji production sebelum mengganti domain

Gunakan hostname gratis Vercel dan cek:

- homepage, layanan, masalah, lokasi, teknisi, artikel, FAQ, kebijakan, dan favicon;
- form booking berhasil membuat tiket;
- status tiket bisa dibuka dari URL token privat;
- login Operations bekerja;
- perubahan CMS menghasilkan Vercel deployment baru;
- DevTools tidak menunjukkan error CORS;
- demo/unverified content tidak muncul di production.

API memakai `PAYMENT_DRIVER=mock` dan `MAIL_MAILER=log` secara sengaja. Jangan menerima pembayaran nyata sebelum konfigurasi Midtrans production, webhook, refund, rekonsiliasi, dan SMTP sudah diuji terpisah.

## 8. Pindahkan `direpair.id` dari cPanel ke Vercel

Lakukan setelah hostname gratis Vercel sudah lulus seluruh pengujian.

### 8.1 Tambahkan domain di Vercel lebih dahulu

1. Buka **Project → Settings → Domains**.
2. Tambahkan `direpair.id`.
3. Tambahkan `www.direpair.id` bila ingin `www` ikut dipakai.
4. Catat record DNS persis yang diminta Vercel untuk project tersebut.

Jangan memakai angka IP dari tutorial lama. Dashboard Vercel adalah sumber record yang benar saat cutover.

### 8.2 Ubah DNS di cPanel

Karena DNS `direpair.id` masih dikelola cPanel, buka **Zone Editor → Manage**:

1. ubah hanya record apex `direpair.id` sesuai instruksi Vercel;
2. atur record `www` sesuai instruksi Vercel bila dipakai;
3. hapus record A/AAAA lama yang konflik hanya untuk apex/`www`;
4. pertahankan record `api`, `cms`, MX, SPF, DKIM, DMARC, dan TXT lain;
5. jangan mengganti nameserver seluruh domain bila tidak memang ingin memindahkan semua DNS.

Tunggu status domain di Vercel menjadi **Valid Configuration** dan sertifikat HTTPS aktif.

### 8.3 Perbarui canonical dan CORS

Di Vercel, tambahkan Production Environment Variable:

```dotenv
PUBLIC_SITE_URL=https://direpair.id
```

Redeploy production.

Lalu di Terminal cPanel jalankan. Ganti `HOSTNAME-VERCEL-ASLI` dengan hostname gratis yang sebelumnya dipakai agar tetap dapat digunakan sebagai jalur rollback:

```bash
cd /home/direpair/api.direpair.id
cp .env .env.before-direpair-domain
sed -i 's|^FRONTEND_URL=.*|FRONTEND_URL=https://direpair.id|' .env
sed -i 's|^PUBLIC_STATUS_URL=.*|PUBLIC_STATUS_URL=https://direpair.id/cek-status/|' .env
sed -i 's|^CORS_ALLOWED_ORIGINS=.*|CORS_ALLOWED_ORIGINS=https://direpair.id,https://HOSTNAME-VERCEL-ASLI|' .env
php artisan optimize:clear
php artisan optimize
```

Uji lagi booking, status, login Operations, REST CMS, serta perubahan konten otomatis. Setelah masa rollback selesai, hostname Vercel lama boleh dihapus dari `CORS_ALLOWED_ORIGINS`.

## 9. Update berikutnya

### Frontend

Commit dan push ke branch production. Vercel mengurus install, build, deployment, cache, dan rollback deployment.

### WordPress plugin

Jalankan kembali:

```bat
infra\cpanel\package-cpanel.cmd
```

Upload ZIP plugin terbaru melalui WordPress dan pilih replace/overwrite. Backup database serta folder plugin sebelum update production.

### Laravel API

Upload source/API ZIP terbaru, tetapi jangan menimpa `.env`. Setelah extract:

```bash
cd /home/direpair/api.direpair.id
composer install --no-dev --classmap-authoritative --no-interaction --prefer-dist
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
```

## 10. Troubleshooting cepat

### Konten CMS belum terlihat

Periksa urutannya:

1. status konten **Published**;
2. **Konten sudah diverifikasi** aktif;
3. label demo tidak aktif;
4. Deploy Hook tersimpan;
5. cron WordPress berjalan;
6. request terakhir di Settings Direpair sukses;
7. build Vercel selesai tanpa error.

### Build Vercel gagal membaca CMS

Buka endpoint health CMS, cek SSL, permalink, dan plugin aktif. Pastikan `CMS_BASE_URL` berakhir di `/wp-json/direpair/v1` tanpa slash ganda.

### Booking terkena CORS

Pastikan origin browser sama persis dengan salah satu nilai `CORS_ALLOWED_ORIGINS`, lalu jalankan:

```bash
cd /home/direpair/api.direpair.id
php artisan optimize:clear
php artisan optimize
```

### Rollback

- Vercel: buka deployment terakhir yang sehat lalu pilih **Promote to Production**.
- Laravel: pulihkan backup source/database dan jalankan `php artisan optimize`.
- WordPress: pulihkan backup database/plugin.
- DNS: kembalikan hanya record apex/`www` ke nilai cPanel sebelumnya; jangan ubah record API, CMS, atau email.

## 11. Checklist selesai

- [ ] CMS dan API memakai HTTPS valid.
- [ ] Plugin Direpair Content 0.2.0 atau lebih baru aktif.
- [ ] Konten asli terverifikasi dan data demo tidak dipublikasikan.
- [ ] Build Vercel pertama berhasil dan hostname gratisnya dicatat.
- [ ] Laravel `.env` memakai hostname Vercel yang benar.
- [ ] Deploy Hook WordPress dan cron satu menit bekerja.
- [ ] Booking, status token, Operations, REST CMS, favicon, dan CORS diuji.
- [ ] `direpair.id` baru dipindahkan setelah hostname gratis lulus QA.
- [ ] Payment tetap mock sampai Midtrans production benar-benar lolos go-live checklist.
