# Direpair

Platform layanan perbaikan elektronik: website publik dengan Astro, pengelolaan konten dengan WordPress, dan operasional servis dengan Laravel.

## Repository dan production

- Repository: [naufaldenta/direpair](https://github.com/naufaldenta/direpair) (private).
- Branch production frontend: `main`.
- Project Vercel: `direpair-web`.
- Domain frontend: [direpair.id](https://direpair.id).
- API dan dashboard operasional: [api.direpair.id](https://api.direpair.id/operations/login).
- CMS: [cms.direpair.id/wp-admin/](https://cms.direpair.id/wp-admin/).

Lihat [catatan serah-terima](HANDOFF.md) untuk verifikasi versi production, cakupan source, dan status pembaruan konten otomatis. Akses repository private perlu diberikan kepada akun GitHub penerima; URL saja tidak memberikan akses.

## Isi project

| Lokasi | Isi |
| --- | --- |
| `apps/web/` | Astro, source TypeScript, halaman, komponen, styles, dan public assets |
| `apps/api/` | Laravel, dashboard operasional, REST API, migration, dan test |
| `apps/wordpress-plugin/direpair-content/` | Seluruh source plugin Direpair Content, admin forms, REST API, dan publish hook |
| `infra/local/` | Docker Compose WordPress/MariaDB untuk development |
| `infra/cpanel/` | Script pembuatan paket upload API dan plugin |
| `.github/workflows/ci.yml` | Pemeriksaan frontend, backend, dependency, dan sintaks plugin |

WordPress core, database production, file upload CMS, `vendor/`, `node_modules/`, dan konfigurasi rahasia tidak disimpan di Git. Konten CMS dan database perlu backup terpisah untuk pemindahan server; source repository bukan backup data production.

## Prasyarat

- Node.js **24.14.1**, dicatat di [`.node-version`](.node-version); Vercel menggunakan lini Node **24.x**.
- pnpm **11.19.0**, dicatat di `package.json#packageManager`.
- PHP **8.3** dan Composer 2 untuk API.
- Docker Desktop hanya diperlukan jika menjalankan CMS lokal dengan Compose.

Dependency frontend dikunci oleh [`pnpm-lock.yaml`](pnpm-lock.yaml), dependency backend oleh [`composer.lock`](apps/api/composer.lock). Jalankan instalasi dari root repository agar workspace dan lockfile yang sama digunakan.

## Menjalankan frontend

```sh
git clone https://github.com/naufaldenta/direpair.git
cd direpair
corepack pnpm install --frozen-lockfile
corepack pnpm dev:web
```

Buka [localhost:4321](http://localhost:4321). Untuk konfigurasi lokal, salin `apps/web/.env.example` menjadi `apps/web/.env`. Data fixture bertanda demo tersedia jika CMS lokal belum berjalan. Pengiriman booking memerlukan API lokal yang aktif.

```sh
corepack pnpm status:web
corepack pnpm stop:web
```

Perintah menggunakan `corepack pnpm` langsung; `corepack enable` tidak diperlukan jika Windows membatasi penulisan shim ke folder instalasi Node.

## Pemeriksaan dan build

```sh
corepack pnpm check:web
corepack pnpm build:vercel
```

Build production harus menggunakan variabel dalam [`apps/web/.env.production.example`](apps/web/.env.production.example). Di Vercel, isi variabel melalui Project Settings. Untuk build production di laptop, salin template tersebut menjadi `apps/web/.env.production`.

Halaman konten dibuat saat build; URL status bertoken menggunakan adapter Vercel. Hasil build Vercel bukan paket static hosting cPanel biasa.

Untuk API yang dependency dan environment lokalnya sudah disiapkan:

```sh
corepack pnpm test:api
corepack pnpm audit:api
```

`test:web` saat ini menjalankan pemeriksaan Astro/TypeScript, bukan browser end-to-end test.

## Panduan per aplikasi

- [Frontend: environment, build, integrasi, dan deploy Vercel](apps/web/README.md)
- [Laravel: setup lokal, endpoint, dan deploy cPanel](apps/api/README.md)
- [Direpair Content: instalasi, endpoint WordPress, dan deploy hook](apps/wordpress-plugin/direpair-content/README.md)

## Konfigurasi dan keamanan

File `.env.example` hanya berisi nama variabel, nilai publik, placeholder, dan nilai demo lokal. Jangan mengunggah `.env`, password database, application key, token pembayaran, database dump, atau URL Deploy Hook ke repository maupun WhatsApp. Konfigurasi production tetap berada di Vercel/cPanel/WordPress.

Push ke `main` memicu deployment frontend melalui integrasi Git Vercel. Perubahan Laravel dan plugin WordPress perlu diunggah terpisah ke cPanel; tidak ikut dideploy oleh Vercel.
