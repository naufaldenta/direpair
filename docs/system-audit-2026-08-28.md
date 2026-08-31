# Audit Sistem Direpair — 28 Agustus 2026

> Catatan topologi: hasil audit ini dibuat ketika frontend masih memakai Cloudflare adapter. Target deployment telah dikonversi menjadi static Astro di cPanel; gunakan `docs/deployment-cpanel.md` sebagai panduan production terbaru.
>
> Resolusi 31 Agustus 2026: regresi webhook settlement → late pending dan boundary REST CMS untuk verified/consented content sudah diperbaiki serta diverifikasi dengan test/lint. Temuan lain dalam audit tetap berlaku sampai ditutup secara terpisah.

## Kesimpulan

Core vertical slice Direpair **berfungsi di local**: Astro dapat dibangun dan dilayani, Laravel menjalankan booking/status/quotation/operations, dan WordPress menyediakan content model serta REST API. Namun sistem **belum 100% sesuai blueprint dan belum production-ready**.

Alasan utama:

1. webhook Midtrans dapat menurunkan status pembayaran yang sudah `paid` bila notifikasi lama `pending` datang terlambat;
2. REST API WordPress production hanya menyaring `is_demo`, belum mewajibkan `is_verified` dan `publish_consent` pada repair case;
3. GA4/Consent Mode, Search Console, `LocalBusiness`, dan `BreadcrumbList` belum selesai walaupun termasuk backlog P0 blueprint;
4. seluruh konten bisnis local masih fixture demo dan kebijakan privasi/terms belum final;
5. Midtrans nyata, accessibility automation, Core Web Vitals lapangan, serta publish-to-deploy CMS belum diuji end-to-end.

Jadi status yang tepat adalah: **core functional, production gate belum lolos**.

## Verifikasi yang Lolos

| Area | Hasil |
|---|---|
| Astro type/content check | 37 file, 0 error, 0 warning, 0 hint |
| Astro production build | Sukses dengan Cloudflare adapter |
| Link crawl frontend | 24 URL internal, seluruhnya HTTP 200, tanpa broken link |
| Template dinamis | 5 service detail dan 4 problem detail yang berasal dari CMS seluruhnya HTTP 200 |
| Responsive smoke test | 390×844, 1440×1000, dan 1920×1080 tanpa horizontal overflow pada halaman representatif |
| Regresi hero | Label Diagnosis berada di dalam hub; CTA status tidak bertabrakan dengan tiket setelah animasi selesai |
| Accessibility dasar | Satu H1 per halaman yang diuji, 19/19 kontrol booking berlabel, tanpa duplicate ID dan tanpa image yang kehilangan atribut `alt` |
| Console browser | Tidak ada error/warning pada route yang diuji |
| Laravel test suite | 8 test lulus, 49 assertion |
| Laravel migrations | 14 migration sudah applied |
| Laravel routes | 13 route API/operations terdaftar |
| Dependency audit | Composer dan `pnpm audit --prod` tidak menemukan advisory |
| WordPress plugin lint | 11 file PHP lulus `php -l` |
| WordPress runtime | Plugin aktif, 10 custom post type terdaftar, 13 endpoint Direpair merespons HTTP 200 |
| Private status | Header `no-store`, `noindex`, CORS origin frontend, dan payload tidak memuat email/telepon pelanggan |

Build frontend yang diperiksa berisi sekitar 290,5 KB file HTML/CSS/font mentah atau 129,6 KB bila di-gzip. Tidak ditemukan bundle JavaScript besar; gambar CMS berada di luar angka ini.

## Matriks Blueprint

Blueprint sumber: `Blueprint Website Direpair_ Lightweight, SEO-First, Local GEO & AI-Ready untuk Repair Produk Non-Mai.pdf`, terutama final developer backlog halaman 51–52.

### P0

| Requirement | Status | Catatan |
|---|---|---|
| Design system | Lolos | Repair Interchange dan token desain sudah diterapkan; detector hanya memberi advisory drift token kecil |
| Homepage | Lolos | Responsive dan tidak ada regresi overlap pada breakpoint yang diuji |
| Service category template | Lolos | Index dan data dinamis dari WordPress |
| Service/problem detail | Lolos sebagian | Template bekerja; Service schema ada, tetapi Breadcrumb schema belum ada |
| Booking diagnostic funnel | Lolos local | Validasi, consent, private upload, dan status token bekerja; production sengaja fail-closed bila privacy masih draft |
| Location template | Lolos template | Belum boleh untuk local SEO karena alamat nyata belum diisi/diverifikasi |
| LocalBusiness schema | Belum | Yang tersedia baru `Organization`; tidak boleh dibuat dari lokasi fixture |
| Service/Breadcrumb schema | Sebagian | `Service` tersedia pada service detail terverifikasi; `BreadcrumbList` belum ada |
| Meta/canonical/OG | Lolos | Canonical, robots, OpenGraph dasar, dan title/description tersedia |
| XML sitemap | Lolos | Dihasilkan dinamis dan mengecualikan fixture fallback production |
| robots.txt | Lolos | Status privat dan mock payment diblok dari crawler |
| GA4/Search Console | Belum | Tidak ada gtag/Consent Mode/event schema; Search Console juga belum dikonfigurasi |
| Core Web Vitals | Sebagian | Arsitektur ringan dan build kecil, tetapi belum ada Lighthouse/field data production |

### P1, P2, dan HOLD

| Requirement | Status |
|---|---|
| Knowledge template | Belum; post type CMS ada tetapi frontend belum mengonsumsi/merendernya |
| Technician profile | Sebagian; halaman daftar ada, detail profile belum ada |
| Repair case | Sebagian; daftar dan consent filter frontend ada, detail page belum ada |
| Pricing | Lolos sebagai kisaran dinamis, bukan quotation final |
| Pickup/home service | Sebagian; pilihan dan alamat ada pada booking, scheduling/coverage/capacity belum ada |
| Spare-parts store | Belum |
| CRM/repair status | Lolos sebagai vertical slice guest-token + operations dashboard |
| Brand/model, English, customer account, B2B | Belum; memang P2 |
| Vape SEO/commerce | Tidak dibuat; sesuai status HOLD legal review |

## Temuan Prioritas

### P0 — Perbaiki sebelum Midtrans production

`HandlePaymentNotificationAction` selalu menulis status notifikasi terbaru ke payment, invoice, dan service request. Bila notifikasi `settlement` diterima lebih dahulu lalu `pending` yang terlambat datang, data `paid` dapat kembali menjadi `pending`. Midtrans secara eksplisit menyatakan notifikasi dapat tiba di luar urutan dan menyarankan GET Status API atau mengabaikan `pending` setelah sukses.

Perbaikan yang dibutuhkan:

- buat state transition pembayaran yang monotonic;
- abaikan `pending` setelah `paid`/`refunded` sesuai aturan bisnis;
- tambahkan GET Status API untuk reconciliation;
- tambahkan test `settlement -> duplicate settlement -> late pending`;
- tetapkan aturan partial refund/cancel sesudah settlement.

### P0 — Perketat publication boundary CMS

`RestApi::visibilityMetaQuery()` production hanya mengecualikan `is_demo=1`. Record published yang belum verified masih dapat dibaca langsung dari REST API, dan repair case tidak diwajibkan memiliki `publish_consent=1`. Astro melakukan filter tambahan, tetapi REST publik tetap menjadi data surface tersendiri.

Perbaikan yang dibutuhkan:

- production REST wajib `is_demo != 1` dan `is_verified = 1`;
- endpoint repair case juga wajib `publish_consent = 1`;
- tambahkan PHPUnit/WordPress integration test untuk collection dan single endpoint;
- pastikan catalog memakai filter yang sama.

### P1 — Lengkapi P0 SEO/measurement blueprint

- implementasikan `LocalBusiness` hanya setelah NAP, koordinat, jam, dan lokasi nyata diverifikasi;
- tambahkan `BreadcrumbList` pada service/problem detail;
- implementasikan analytics consent-aware tanpa PII dan event sesuai blueprint;
- verifikasi domain di Search Console dan submit sitemap;
- ukur Lighthouse serta CWV production, bukan hanya ukuran build local.

### P1 — Tutup production operations gap

- seluruh identity, kontak, lokasi, teknisi, foto, harga, garansi, FAQ, privacy, dan terms masih demo;
- signed content webhook WordPress sudah ada, tetapi receiver/automation untuk rebuild dan upload static Astro ke cPanel belum ada;
- local WordPress memiliki banyak event webhook yang sudah jatuh tempo, sehingga cron production wajib eksplisit;
- Midtrans sandbox/production, delayed notification, refund, dan reconciliation belum pernah diuji dengan akun merchant;
- Turnstile, duplicate booking detection, malware scanning attachment, retention/deletion, monitoring, dan restore drill belum tersedia.

## Coverage yang Belum Bisa Diklaim

Tidak ada sistem non-trivial yang dapat dibuktikan “100% tanpa bug”. Audit ini juga belum mencakup:

- transaksi Midtrans nyata;
- browser manual pada dashboard WordPress yang authenticated;
- JavaScript E2E suite frontend;
- WordPress PHPUnit/integration suite;
- automated axe/a11y scanner dan screen reader;
- perangkat fisik iOS/Android;
- beban tinggi, failover hosting, backup restore, dan observability production;
- data serta persetujuan legal dari klien.

Gunakan `docs/deployment-cpanel.md` setelah dua temuan P0 di atas diperbaiki dan semua production content gate dipenuhi.
