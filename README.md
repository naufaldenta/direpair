# Direpair Platform

Vertical slice untuk website Direpair berdasarkan requirement PDF: public website SEO-first di Astro, content control plane di WordPress, dan workflow repair privat di Laravel.

## Aplikasi

| Path | Fungsi |
|---|---|
| `apps/web` | Website publik Astro dan halaman status privat; target Git-connected Vercel |
| `apps/api` | Booking, operasi repair, quotation, invoice, payment, timeline, dan dashboard staf Laravel |
| `apps/wordpress-plugin/direpair-content` | Content model, settings, fixture, REST API, dan publish webhook WordPress |

Konten demo sengaja diberi label fixture dan tidak boleh terindeks di production. Nama teknisi, foto, lokasi, harga publik, FAQ, dan materi lain dikelola dari WordPress; data pelanggan dan transaksi tidak disimpan di WordPress.

## Menjalankan lokal

Prasyarat: Node.js 22.12+, pnpm 11, PHP 8.3+, Composer, dan Docker Desktop bila ingin menjalankan WordPress lokal.

```powershell
pnpm install

Copy-Item apps/api/.env.example apps/api/.env
Copy-Item apps/web/.env.example apps/web/.env
php apps/api/artisan key:generate
New-Item -ItemType File -Force apps/api/database/database.sqlite
php apps/api/artisan migrate --seed
```

Setup WordPress berikut hanya perlu dijalankan sekali setelah volume Docker dibuat:

```powershell
pnpm cms:up
docker compose -f infra/local/docker-compose.yml --profile tools run --rm wpcli core install --url=http://localhost:8080 --title="Direpair Local" --admin_user=admin --admin_password=direpair-local-only --admin_email=admin@direpair.test --skip-email
docker compose -f infra/local/docker-compose.yml --profile tools run --rm wpcli plugin activate direpair-content
docker compose -f infra/local/docker-compose.yml --profile tools run --rm wpcli rewrite structure '/%postname%/' --hard
pnpm cms:seed
```

Untuk pemakaian harian, buka dua terminal dari root proyek:

```powershell
# Terminal 1: CMS + database, lalu frontend Astro di background
pnpm cms:up
pnpm dev:web

# Terminal 2: backend operasional Laravel
pnpm dev:api
```

## URL dan fungsi lokal

| Komponen | URL | Fungsi |
|---|---|---|
| Frontend (FE) | `http://localhost:4321` | Website publik Astro yang dibaca pelanggan |
| Form booking | `http://localhost:4321/booking/` | Membuat repair request ke Laravel |
| Status fixture | `http://localhost:4321/cek-status/demo-status-token-direpair-local-123456789` | Contoh quotation dan timeline privat |
| Backend API | `http://localhost:8000/api/v1/health` | Health check API Laravel |
| Backend staf | `http://localhost:8000/operations/login` | Kelola request, diagnosis, quotation, dan payment |
| CMS WordPress | `http://localhost:8080/wp-admin/` | Ubah layanan, harga, foto, teknisi, lokasi, FAQ, dan kebijakan |
| CMS REST | `http://localhost:8080/wp-json/direpair/v1/health` | Health check konten untuk Astro |

Kredensial development lokal:

| Panel | User | Password |
|---|---|---|
| WordPress | `admin` | `direpair-local-only` |
| Backend staf | `admin@direpair.test` | `direpair-local-only` |

Nilai tersebut hanya fixture lokal. Jangan gunakan untuk staging atau production.

Command status dan stop:

```powershell
pnpm cms:status
pnpm status:web

pnpm stop:web
pnpm cms:stop
```

Backend Laravel dihentikan dengan `Ctrl+C` pada Terminal 2 yang menjalankan `pnpm dev:api`.

Pembagian tanggung jawabnya:

- WordPress hanya menyimpan konten publik yang dapat diedit klien.
- Laravel menyimpan data pelanggan, repair, quotation, invoice, dan payment.
- Astro membaca WordPress untuk konten, lalu mengirim booking/status ke Laravel.

## Pemeriksaan kualitas

```powershell
pnpm check:web
pnpm build:web
cd apps/api && vendor/bin/pint --test
corepack pnpm test:api
php tmp/tools/composer.phar audit --working-dir=apps/api
```

Tutorial final Vercel + cPanel yang siap diikuti ada di [docs/deployment-cpanel.md](docs/deployment-cpanel.md). Ringkasan production gate ada di [docs/deployment-runbook.md](docs/deployment-runbook.md). Keputusan workflow bisnis ada di [docs/repair-workflow.md](docs/repair-workflow.md). Pekerjaan yang memang masih P1/P2 atau memerlukan akun/data klien dicatat transparan di [docs/remaining-roadmap.md](docs/remaining-roadmap.md).
