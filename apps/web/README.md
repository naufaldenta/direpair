# Direpair Astro Frontend

Frontend publik Direpair dibangun dengan Astro dan dideploy dari Git ke Vercel. Halaman konten publik diprerender saat build; route status privat `/cek-status/<token>/` dijalankan melalui adapter Vercel dan mengambil data aman dari Laravel di browser.

## Local development

Dari root repository:

```bat
corepack pnpm install --frozen-lockfile
corepack pnpm dev:web
```

Nilai local berada di `.env.example`. Salin menjadi `.env` hanya bila perlu mengubah endpoint local.

## Production verification

```bat
corepack pnpm check:web
corepack pnpm build:vercel
```

Vercel project settings:

- Root Directory: `apps/web`
- Framework: Astro
- Build Command: berasal dari `vercel.json`
- Output Directory: kosong/otomatis

Environment production dan preview:

```dotenv
PUBLIC_API_BASE_URL=https://api.direpair.id/api/v1
CMS_BASE_URL=https://cms.direpair.id/wp-json/direpair/v1
CMS_FIXTURE_FALLBACK=false
```

Sebelum custom domain aktif, canonical otomatis mengikuti `VERCEL_PROJECT_PRODUCTION_URL`. Setelah `direpair.id` terhubung, set `PUBLIC_SITE_URL=https://direpair.id` dan redeploy.

Konten WordPress baru terlihat setelah build baru. Deploy Hook plugin Direpair Content memicu build tersebut otomatis setiap kali editor menerbitkan atau memperbarui konten, sehingga tidak ada upload Astro manual.

Panduan lengkap ada di [`../../docs/deployment-cpanel.md`](../../docs/deployment-cpanel.md).
