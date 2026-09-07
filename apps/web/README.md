# Direpair frontend

Website publik Astro dengan adapter Vercel. Seluruh source berada di `src/`: halaman di `src/pages/`, komponen di `src/components/`, stylesheet di `src/styles/`, dan integrasi data di `src/lib/`. Asset publik berada di `public/`.

## Runtime dan instalasi

Gunakan Node **24.14.1** untuk reproduksi lokal dan pnpm **11.19.0**. File versi, workspace, dan lockfile ada di root monorepo; jangan membuat lockfile terpisah di folder ini.

Dari root repository:

```sh
corepack pnpm install --frozen-lockfile
corepack pnpm dev:web
```

Aplikasi tersedia di [localhost:4321](http://localhost:4321). Perintah root menjalankan server background. Gunakan `corepack pnpm status:web` dan `corepack pnpm stop:web` untuk memeriksa/menghentikannya.

Salin `.env.example` menjadi `.env` di folder ini bila perlu menyesuaikan konfigurasi lokal. Fixture development disediakan; booking tetap memerlukan Laravel lokal.

## Environment

| Variabel | Production | Fungsi |
| --- | --- | --- |
| `PUBLIC_SITE_URL` | `https://direpair.id` | Canonical URL, sitemap, dan metadata |
| `PUBLIC_API_BASE_URL` | `https://api.direpair.id/api/v1` | Endpoint operasional Laravel yang dipakai browser |
| `CMS_BASE_URL` | `https://cms.direpair.id/wp-json/direpair/v1` | REST publik WordPress yang dibaca saat build |
| `CMS_FIXTURE_FALLBACK` | `false` | Gagalkan build jika CMS gagal dibaca, bukan menggantinya dengan data demo |

Lihat [template lokal](.env.example) dan [template production](.env.production.example). Frontend tidak membutuhkan password WordPress atau token CMS. Variabel berawalan `PUBLIC_` dapat masuk ke browser; jangan diisi secret.

Di production, `CMS_BASE_URL` wajib diisi bersamaan dengan fallback `false`. Tanpa URL CMS, kode menggunakan fixture. Simpan environment production melalui Vercel; jangan commit file `.env` hasil salinan template.

## Integrasi data

[`src/lib/cms.ts`](src/lib/cms.ts) mengambil pengaturan situs dan delapan koleksi konten dari WordPress, memvalidasi kontraknya dengan Zod, lalu membagikan dataset tersebut ke halaman Astro. Dokumentasi URL, response, dan aturan publikasi ada di [README plugin](../wordpress-plugin/direpair-content/README.md#rest-api).

Halaman publik diprerender. Perubahan konten WordPress terlihat sesudah build dan deployment berikutnya berhasil. Route privat `/cek-status/<token>/` tidak diprerender; halaman menggunakan adapter Vercel dan browser mengambil status terbaru dari Laravel, sehingga perubahan status repair tidak memerlukan rebuild konten.

Frontend saat ini memuat maksimal 100 item per koleksi CMS (halaman pertama). Endpoint pricing guides dan knowledge articles tersedia di plugin tetapi belum dimuat sebagai koleksi tersendiri oleh frontend. Hal ini perlu dipertimbangkan ketika menambah jenis konten atau menaikkan volume konten.

## Verifikasi lokal

Dari root repository:

```sh
corepack pnpm check:web
corepack pnpm build:vercel
```

Untuk build menggunakan CMS production, salin `apps/web/.env.production.example` menjadi `apps/web/.env.production` terlebih dahulu. Build memerlukan akses HTTPS ke CMS.

`check:web` memeriksa Astro dan TypeScript. `build:vercel` menjalankan pemeriksaan dan build adapter. Tidak ada klaim bahwa kedua perintah ini menggantikan pengujian seluruh alur bisnis di browser.

## Deployment Vercel

Project saat serah-terima:

| Pengaturan | Nilai |
| --- | --- |
| Project | `direpair-web` |
| Repository | `naufaldenta/direpair` |
| Production Branch | `main` |
| Root Directory | `apps/web` |
| Framework Preset | Astro |
| Node.js | 24.x |
| Install Command | `corepack pnpm install --frozen-lockfile` |
| Build Command | `corepack pnpm build:vercel` |
| Output Directory | Otomatis, jangan override menjadi folder static cPanel |
| Domain | `direpair.id`, `www.direpair.id` |
| Domain Vercel | `direpair-web.vercel.app` |

1. Import repository tersebut di Vercel dan gunakan pengaturan di atas. Pastikan workspace/lockfile root monorepo ikut tersedia.
2. Isi keempat variabel production pada tabel environment. Untuk Preview, gunakan backend staging bila ingin menguji penulisan data; endpoint production akan mengirim booking ke sistem nyata.
3. Deploy branch `main`, lalu periksa status **Ready** dan sumber commit di detail deployment.
4. Hubungkan domain di Project Settings → Domains. Record DNS frontend mengikuti nilai yang ditampilkan Vercel. Pertahankan `api.direpair.id` dan `cms.direpair.id` menuju cPanel.
5. Push berikutnya ke `main` memicu build frontend. Kegagalan build harus diperbaiki sebelum perubahan tampil di production.

Konfigurasi build dan security headers disimpan di [`vercel.json`](vercel.json).

## Pembaruan otomatis dari WordPress

Buat Deploy Hook untuk branch `main` di Vercel → Project Settings → Git → Deploy Hooks. Simpan URL tersebut hanya di CMS → Settings → Direpair Content → Pembaruan otomatis ke Vercel.

Plugin menjadwalkan pengiriman setelah perubahan konten yang relevan, lalu WordPress cron mengirim request ke hook. Status sukses di CMS berarti build berhasil **diminta**, bukan build selesai. Pantau status **Ready** di Vercel. Detail pemicu, cron cPanel, dan troubleshooting ada di [panduan plugin](../wordpress-plugin/direpair-content/README.md#pembaruan-otomatis-ke-vercel).

Referensi platform: [Vercel Deploy Hooks](https://vercel.com/docs/deploy-hooks).
