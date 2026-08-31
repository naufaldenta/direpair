# Direpair Production Runbook

Runbook operasional ini merangkum gate production. Tutorial instalasi dan perintah lengkap berada di [`deployment-cpanel.md`](deployment-cpanel.md).

## Topologi aktif

| Komponen | Production |
|---|---|
| Astro | Vercel hostname gratis, kemudian `https://direpair.id` |
| Laravel | `https://api.direpair.id` di `/home/direpair/api.direpair.id` |
| WordPress | `https://cms.direpair.id` di `/home/direpair/cms.direpair.id` |

## Gate sebelum deploy

```bat
cd /d C:\laragon\www\direpair
corepack pnpm install --frozen-lockfile
corepack pnpm check:web
corepack pnpm build:vercel
corepack pnpm test:api
infra\cpanel\package-cpanel.cmd
```

Pastikan lint seluruh plugin WordPress, Laravel Pint, dependency audit, dan smoke test lokal juga lulus sebelum release.

## Environment penting

Vercel:

```dotenv
PUBLIC_API_BASE_URL=https://api.direpair.id/api/v1
CMS_BASE_URL=https://cms.direpair.id/wp-json/direpair/v1
CMS_FIXTURE_FALLBACK=false
```

Laravel harus membatasi `CORS_ALLOWED_ORIGINS` ke hostname Vercel aktif. Setelah domain dipindahkan, origin utama menjadi `https://direpair.id` dan hostname gratis dapat dipertahankan sementara untuk rollback.

## Health checks

```text
GET https://cms.direpair.id/wp-json/direpair/v1/health
GET https://api.direpair.id/api/v1/health
GET https://direpair.id/
```

Uji juga booking, status token privat, login Operations, publish CMS → Deploy Hook → deployment Vercel, favicon, serta CORS.

## Operasional konten

- Editor memakai formulir terstruktur WordPress, bukan Gutenberg.
- Hanya konten published, verified, dan non-demo yang keluar ke production REST API.
- Plugin menggabungkan perubahan selama sekitar 30 detik lalu memicu Vercel Deploy Hook.
- Cron WordPress satu menit wajib aktif agar publish otomatis tidak bergantung pada trafik CMS.

## Backup dan rollback

- Vercel: promote deployment terakhir yang sehat.
- Laravel: backup `.env`, source, dan database sebelum update; pulihkan lalu jalankan `php artisan optimize`.
- WordPress: backup database dan `wp-content` sebelum update plugin/konten besar.
- DNS: simpan record apex/`www` lama sebelum cutover. Jangan mengubah record `api`, `cms`, atau email saat memindahkan frontend.

## Batas go-live

`PAYMENT_DRIVER=mock` dan `MAIL_MAILER=log` bukan konfigurasi transaksi production. Aktifkan Midtrans/SMTP hanya setelah credential, webhook, refund, rekonsiliasi, deliverability, dan monitoring telah diuji.
