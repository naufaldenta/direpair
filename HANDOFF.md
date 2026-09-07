# Serah-terima source Direpair

Tanggal pemeriksaan awal: **7 September 2026**. Dokumen ini mencatat sumber deployment dan kelengkapan repository, bukan klaim bahwa semua kemungkinan bug sudah tereliminasi.

## Source dan production

| Item | Informasi |
| --- | --- |
| Repository | https://github.com/naufaldenta/direpair |
| Akses | Private; penerima perlu akses GitHub atau ZIP source |
| Branch frontend production | `main` |
| Project Vercel | `direpair-web` |
| Repository yang terhubung | `naufaldenta/direpair` |
| Frontend | https://direpair.id |
| CMS | https://cms.direpair.id |
| API | https://api.direpair.id |
| Node untuk reproduksi lokal | `24.14.1` melalui `.node-version` |
| Package manager | `pnpm@11.19.0` |

Sebelum pembaruan dokumentasi dan housekeeping ini, GitHub mencatat deployment Vercel environment **Production** sukses untuk commit **`e72cad8936ddf8db2f23a66fd483067fc5ada503`** pada 4 September 2026. Commit tersebut menjadi baseline source frontend. Domain `direpair.id` merespons melalui Vercel, dan health CMS melaporkan Direpair Content **0.2.1** pada pemeriksaan 7 September.

Perubahan serah-terima tidak mengubah halaman, komponen, styles, asset, integrasi data, dependency frontend terkunci, atau konfigurasi routing frontend dari baseline tersebut. Perubahan meliputi dokumentasi, pengecualian file lokal, konfigurasi CI, dan penghapusan dependency development backend yang tidak dibutuhkan aplikasi production.

Untuk memastikan versi yang diberikan sudah live, pilih deployment **Production / Ready** di Vercel dan cocokkan **Source → Commit** dengan commit branch `main` yang diserahkan. Jangan hanya mengandalkan tanggal upload atau status build lokal. Push tidak sama dengan deployment berhasil.

Konten WordPress adalah data di luar Git dan dibaca saat build. Dua deployment dari commit source yang sama dapat menampilkan konten berbeda setelah editor memperbarui CMS. Kesamaan source tidak berarti database/upload WordPress disertakan di repository.

## Kelengkapan yang disertakan

- `package.json`, `pnpm-workspace.yaml`, `pnpm-lock.yaml`, dan `.node-version` di root.
- `apps/web/package.json`, source halaman/komponen/styles/integrasi, serta public assets.
- [Petunjuk menjalankan dan deployment frontend](apps/web/README.md).
- Template [environment lokal](apps/web/.env.example) dan [production](apps/web/.env.production.example), tanpa kredensial asli.
- Seluruh source [plugin Direpair Content](apps/wordpress-plugin/direpair-content/) beserta [dokumentasi endpoint dan publish hook](apps/wordpress-plugin/direpair-content/README.md).
- Source Laravel, migration, test, Composer lockfile, [panduan deployment API](apps/api/README.md), serta template konfigurasi cPanel.

Data production, file upload CMS, dependency hasil install, build output, backup, dan konfigurasi rahasia tidak termasuk source Git. Dokumentasi operasional yang dibutuhkan penerima ada di README per aplikasi, tidak bergantung pada catatan kerja lokal.

## Konfirmasi pembaruan CMS

**Mendukung deployment otomatis, dengan Deploy Hook Vercel dan WordPress cron yang dikonfigurasi.** Riwayat deployment menunjukkan rebuild production pernah berhasil. URL hook dan kondisi cron saat serah-terima harus diperiksa oleh admin di CMS/cPanel; endpoint publik tidak membuktikan kedua pengaturan privat tersebut masih aktif.

Publish/update/unpublish/delete konten terkelola yang published, taxonomy terkelola, dan pengaturan publik menjadwalkan build otomatis. Tidak semua aktivitas admin WordPress memicu deploy, misalnya menyimpan draft atau mengubah source plugin. Perubahan tampil setelah deployment Vercel sukses, bukan langsung saat tombol Simpan ditekan.

Detail konfigurasi dan pengecekan: [Pembaruan otomatis ke Vercel](apps/wordpress-plugin/direpair-content/README.md#pembaruan-otomatis-ke-vercel).

## Verifikasi saat menerima source

Pemeriksaan lokal pada 7 September 2026: instalasi pnpm dengan frozen lockfile berhasil; pemeriksaan Astro pada 38 file menghasilkan 0 error, warning, dan hint; build Vercel dengan CMS production berhasil; test Laravel lulus 9 test / 58 assertions; Composer validate dan audit lulus. Dependency production PHP tidak berubah dari baseline. Pemeriksaan file yang akan diserahkan tidak menemukan file environment asli atau pola kredensial yang dipindai; ini bukan jaminan audit keamanan menyeluruh.

```sh
git switch main
git pull --ff-only
git rev-parse HEAD
node --version
corepack pnpm --version
corepack pnpm install --frozen-lockfile
corepack pnpm check:web
corepack pnpm build:vercel
```

Untuk build production lokal, salin `apps/web/.env.production.example` ke `apps/web/.env.production` terlebih dahulu. CMS harus dapat diakses saat build. Pemeriksaan backend tercantum di README API; test tidak perlu mengakses database production.

Untuk penyerahan ZIP tanpa akses repository, buat dari commit yang telah diverifikasi, bukan dengan menyalin seluruh folder kerja:

```sh
git archive --format=zip --output=../direpair-source.zip HEAD
```

ZIP ini menyertakan source dan dokumentasi yang dilacak Git, bukan histori commit atau konfigurasi lokal. Catat SHA commit bersama ZIP agar versinya dapat ditelusuri.

Jangan mengirim `.env`, token provider, password hosting/database/admin, atau URL Deploy Hook melalui WhatsApp. Pemberian akses GitHub/Vercel/cPanel dilakukan melalui mekanisme akses masing-masing layanan.
