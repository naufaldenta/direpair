# Direpair Website Implementation Plan

## 1. Outcome

Membangun Direpair sebagai platform `Search -> Diagnosis -> Repair -> Payment -> Warranty -> Data`, bukan sekadar company profile. Implementasi harus:

- cepat, crawlable, dan Indonesia-first;
- memberi klien kontrol atas seluruh konten publik tanpa mengubah source code;
- memisahkan data pelanggan dan transaksi dari WordPress;
- dapat dimulai dengan dummy content yang aman lalu diganti bertahap;
- mempunyai jalur ekspansi menuju knowledge base, local SEO, sparepart, dan repair intelligence.

## 2. Architecture Decision

```text
                    Public visitor / search engine
                               |
                               v
                    Static Astro on cPanel
                     /                       \
          Public SEO/content             Interactive islands
                     |                       |
                     v                       v
        WordPress REST API              Laravel REST API
        (public content)                (repair operations)
                     |                       |
                     v                       v
        WordPress media library       MySQL + private object storage
                                             |
                                             v
                                      Midtrans + notifications
```

### Responsibilities

| Component | Responsibility |
|---|---|
| Astro | Public UI, SEO pages, structured data, booking UI, status UI, lightweight islands |
| WordPress | Services, symptoms, locations, technicians, public pricing, warranty copy, FAQs, cases, articles, media |
| Laravel | Booking, customer/device records, intake, diagnosis, quotation, approval, payment, repair timeline, warranty claims, audit logs |
| MySQL | Operational data and immutable business records |
| Private object storage | Customer device photos/videos and internal repair attachments |
| Midtrans Snap | Repair invoice payment after an amount is known |
| WooCommerce, later | Sparepart catalog, inventory, cart, checkout, product orders |
| cPanel/Apache or LiteSpeed | Menyajikan output statis Astro, rewrite status privat, dan security headers |

WordPress dan Laravel mempunyai database serta login terpisah. WordPress tidak menjadi sumber kebenaran untuk payment atau repair status.

## 3. Repository Layout

```text
direpair/
├── apps/
│   ├── web/                    # Astro public frontend
│   ├── api/                    # Laravel API + operations dashboard
│   └── wordpress-plugin/       # Direpair content model/plugin
├── packages/
│   ├── ui/                     # Astro UI primitives/design tokens
│   ├── cms-contracts/          # Runtime schemas for WordPress responses
│   └── api-contracts/          # Generated TypeScript client from Laravel OpenAPI
├── fixtures/
│   ├── cms-demo/               # Public demo content
│   └── operations-demo/        # Non-production repair jobs and quotes
├── infra/
│   ├── cpanel/
│   ├── ci/
│   └── local/
├── docs/
└── tests/
```

WordPress core tidak perlu di-commit. Repository hanya menyimpan custom plugin, configuration examples, fixture, dan deployment instructions.

## 4. Critical Path

Urutan yang benar adalah:

```text
Business rules
  -> data contracts
  -> CMS content model
  -> operations state machine
  -> design system
  -> public page templates
  -> booking intake
  -> diagnosis and quotation
  -> payment and status
  -> SEO/local/privacy/analytics validation
  -> launch
  -> knowledge/cases/spareparts expansion
```

Homepage tidak dibuat paling awal. Bila page dibuat sebelum content model dan workflow stabil, komponen akan dipenuhi hard-coded dummy data dan harus dikerjakan ulang.

## 5. Implementation Phases

### Phase 0 - Product Contract and Launch Guardrails

Tujuan: menetapkan aturan yang tidak boleh berubah diam-diam saat development.

Tasks:

1. Tetapkan domain production dan subdomain admin/API.
2. Tetapkan bahasa launch: Bahasa Indonesia saja.
3. Tetapkan service methods: drop-off, pickup, dan home service.
4. Tetapkan kategori launch: TWS, vacuum, household, e-bike, lainnya.
5. Tandai vape sebagai disabled/HOLD.
6. Definisikan roles: content editor, content approver, customer service, technician, finance, admin.
7. Buat production-content gate: fake review, fake location, fake certification, dan dummy repair case dilarang publish.

Definition of done:

- Semua keputusan mempunyai setting/default yang eksplisit.
- Data yang belum tersedia mempunyai fallback UI atau section visibility toggle.
- Tidak ada kebutuhan data nyata yang memblokir development.

### Phase 1 - Workspace, CI, and Environments

Tujuan: semua aplikasi dapat dijalankan dan diuji secara konsisten.

Tasks:

1. Inisialisasi Git, branch policy, linting, formatting, dan commit checks.
2. Scaffold Astro, Laravel, dan Direpair WordPress plugin.
3. Siapkan environment `local`, `staging`, dan `production`.
4. Tambahkan secret management untuk WordPress API, Midtrans, database, storage, analytics, dan webhook.
5. Siapkan CI untuk lint, typecheck, unit test, integration test, build, dan preview deployment.
6. Build Astro sebagai static output dan upload isi `dist` ke document root cPanel.
7. Sediakan health checks untuk WordPress dan Laravel API.

Definition of done:

- Satu perintah menjalankan workspace lokal.
- Pull request menghasilkan test report dan preview yang dapat diperiksa.
- Tidak ada secret production di repository.

### Phase 2 - Design System and Content Contracts

Tujuan: UI dan API mempunyai kontrak sebelum page dibangun.

Tasks:

1. Buat design tokens: color, typography, spacing, radius, shadow, breakpoints.
2. Buat primitives: Button, Link, Card, Badge, Breadcrumb, Section, FormField, Dialog, Alert, PriceRange.
3. Definisikan runtime schema untuk semua response WordPress.
4. Definisikan OpenAPI Laravel dan generate TypeScript client untuk Astro.
5. Definisikan shared identifiers: UUID immutable, slug editable, redirect history.
6. Tetapkan component states: loading, empty, partial data, error, demo, unpublished.

Definition of done:

- Komponen tidak membaca field WordPress mentah secara langsung.
- Invalid CMS/API data gagal secara terkontrol dan tercatat.
- Slug dapat berubah tanpa memutus operational reference.

### Phase 3 - WordPress Content Control Plane

Tujuan: klien dapat mengelola seluruh konten publik.

Tasks:

1. Register custom post types, taxonomies, fields, dan REST endpoints.
2. Buat global settings untuk identitas bisnis, CTA, kontak, jam, social links, service methods, dan default SEO.
3. Buat media rules: alt text, focal point, caption, width/height, verified/demo flag.
4. Implement relasi service-problem-brand-location-technician-case-pricing-FAQ.
5. Implement publishing validation dan production demo guard.
6. Tambahkan seed/import dummy content idempotent.
7. Tambahkan signed publish webhook untuk memicu build/deploy Astro.
8. Simpan redirect saat slug berubah.

Definition of done:

- Editor dapat mengganti foto, harga publik, copy, FAQ, lokasi, teknisi, dan CTA dari WordPress.
- Publish memicu deployment tanpa bantuan developer.
- Section tanpa data dapat disembunyikan.

### Phase 4 - Astro Public Foundation

Tujuan: membangun shell website dan mesin SEO reusable.

Tasks:

1. Implement header, footer, navigation, mobile menu, announcement, dan global CTA.
2. Implement CMS client, cache, retry, pagination, dan build-time validation.
3. Implement metadata, canonical, Open Graph, Twitter Card, sitemap index, robots, RSS, and redirects.
4. Implement structured-data builders untuk Organization, LocalBusiness, Service, Breadcrumb, Article, Person, Product/Offer, dan FAQ.
5. Implement image pipeline AVIF/WebP, responsive `srcset`, fixed dimensions, dan no-lazy-load untuk LCP image.
6. Implement 404/500, logging, security headers, CSP baseline, dan consent-aware scripts.

Definition of done:

- Semua template menghasilkan semantic HTML tanpa JavaScript wajib untuk konten utama.
- Structured data hanya memakai data visible dan verified.
- Performance budget otomatis diuji di CI.

### Phase 5 - MVP/P0 Public Pages

Tujuan: commercial architecture dan local conversion tersedia sebelum blog diperbanyak.

Build order:

1. Homepage.
2. Service hub.
3. Service category template.
4. Problem/service detail template.
5. Pricing overview.
6. Location template.
7. Booking entry page.
8. Pickup and home-service pages.
9. Warranty, FAQ, About, Contact.
10. Privacy, terms, shipping/pickup policy.

Initial page set:

- TWS: category, mati sebelah, tidak charging, ganti baterai.
- Vacuum: category, tidak menyala, hisap lemah, ganti baterai, robot vacuum.
- E-bike: category, battery, controller, motor, charger, tidak jalan, home service.
- Household and other-devices discovery pages.
- Real locations only.

Definition of done:

- Setiap commercial page menjawab scope, symptom, proses, pricing policy, warranty, area, evidence, FAQ, dan CTA.
- Tidak ada thin/doorway page.
- Dummy location, review, case, atau technician tidak ikut production schema.

### Phase 6 - Booking and Intake MVP

Tujuan: mengubah traffic menjadi structured repair request.

Form sequence:

```text
Device category
  -> brand
  -> model or "tidak tahu"
  -> symptom
  -> problem details and duration
  -> optional photo/video
  -> drop-off/pickup/home service
  -> area
  -> contact
  -> privacy/diagnostic consent
  -> submit
```

Tasks:

1. Implement Laravel customer, device, service request, attachment, consent, and status models.
2. Implement API validation, rate limiting, Turnstile, duplicate detection, and upload scanning policy.
3. Store attachments in private object storage using signed upload/download URLs.
4. Snapshot submitted catalog labels so historical requests remain readable after CMS edits.
5. Build staff intake queue and request detail view.
6. Generate repair/request number and secure customer status link.
7. Provide WhatsApp handoff containing request number; do not create a second untracked booking.

Definition of done:

- Booking dapat selesai tanpa account.
- Contact details hanya diminta pada tahap akhir.
- GA4 tidak menerima name, phone, email, address, atau free-text problem.
- Staf melihat request baru beserta consent dan attachment secara aman.

### Phase 7 - Diagnosis, Quotation, Payment, and Status

Tujuan: membuat workflow repair lengkap dan auditable.

Tasks:

1. Implement intake confirmation and unit-received event.
2. Implement diagnosis notes, repairability, parts, labor, risk, estimate, and expected turnaround.
3. Implement versioned quotation and line items.
4. Implement approve/decline through short-lived signed link.
5. Implement configurable deposit rule and invoice creation.
6. Integrate Midtrans Snap from Laravel backend.
7. Verify webhook signature/status server-side and handle notifications idempotently.
8. Implement payment reconciliation and manual payment recording with finance permission.
9. Implement public repair timeline, masked device summary, quotation/payment actions, and final handover.
10. Implement warranty generation after job completion.

Definition of done:

- Browser callback tidak dapat menandai invoice paid.
- Quote change membuat approval versi lama tidak berlaku.
- Semua status, quote, payment, dan manual override masuk audit log.
- Public status URL tidak mengekspos sequential database ID atau internal technician notes.

### Phase 8 - SEO, Local, Analytics, Privacy, and Reliability

Tujuan: memvalidasi mesin akuisisi dan compliance sebelum launch.

Tasks:

1. Configure GA4 events sesuai blueprint.
2. Configure Search Console, sitemap submission, indexing checks, and structured-data validation.
3. Configure Google Business Profile alignment for real locations.
4. Implement consent banner and consent mode based on approved privacy policy.
5. Implement data retention, deletion workflow, access logging, backup, and restore test.
6. Implement uptime, 5xx, 404, broken link, form failure, webhook failure, and payment reconciliation alerts.
7. Run CWV, accessibility, security, mobile, and crawl tests.

Definition of done:

- PII tidak terkirim ke analytics.
- Location data konsisten dengan bisnis nyata.
- Restore, payment reconciliation, and failed webhook runbooks telah diuji.

### Phase 9 - Staging Content Replacement and Launch

Tujuan: mengganti demo data dengan data klien tanpa perubahan code.

Tasks:

1. Client mengganti identity, phone, WhatsApp, address, hours, service areas, and legal entity.
2. Upload real workshop/service/technician images.
3. Review public pricing ranges and diagnostic policy.
4. Replace demo technicians, cases, FAQs, warranty, testimonials, and location data.
5. Run production-content validator.
6. Content/editor training and operational staff training.
7. Soft launch with indexing disabled, perform end-to-end repair/payment test, then enable indexing.

Definition of done:

- Tidak ada placeholder/demo badge atau demo structured data di production.
- Booking -> quote -> payment -> status -> warranty berhasil diuji menggunakan sandbox lalu production smoke test.

### Phase 10 - P1 Expansion

Urutan setelah MVP stabil:

1. Knowledge base and technician attribution.
2. Real repair-case module and internal linking flywheel.
3. Pickup/home-service scheduling refinement.
4. Pricing intelligence and reporting.
5. Brand/model pages only when unique evidence exists.
6. WooCommerce sparepart catalog, stock, cart, checkout, product schema, and Midtrans payment.
7. Repair-data dashboards and annual repairability report pipeline.

### Phase 11 - P2 Expansion

- English pages based on measured demand.
- Customer accounts; secure token flow remains available for guests.
- B2B retailer/distributor portal.
- B2B SLA/reporting dashboard.
- Advanced inventory and technician capacity planning.

## 6. MVP Boundary

MVP includes:

- public P0 pages and SEO engine;
- editable content from WordPress;
- booking with private uploads;
- operations dashboard;
- diagnosis, quote approval, Midtrans payment, status timeline, warranty;
- analytics, privacy, monitoring, and launch guardrails.

MVP does not include:

- full sparepart commerce;
- customer account;
- English;
- B2B dashboard;
- public vape pages;
- automated AI diagnosis;
- thousands of auto-generated brand/model/location pages.

## 7. Quality Gates

| Gate | Requirement |
|---|---|
| Content | No fake reviews, certifications, cases, technicians, or locations in production |
| SEO | Canonical, sitemap, robots, schema, breadcrumbs, and internal links verified |
| Performance | LCP < 2.0 s, INP < 150 ms, CLS < 0.05 target; JS and transfer budgets enforced |
| Security | Secrets outside repo, least privilege, signed URLs, rate limits, verified webhooks, audit logs |
| Privacy | Purpose disclosure, minimization, consent record, retention, deletion, no PII in analytics |
| Payment | Backend-created transaction, verified webhook, idempotency, reconciliation, audit trail |
| Operations | Staff can complete an entire repair without database/manual code changes |
| Recovery | Backup restore and failed notification/payment reconciliation tested |

## 8. First Implementation Milestone

Milestone pertama bukan homepage yang final. Target pertama adalah vertical slice berikut:

```text
WordPress service + problem + pricing dummy
  -> Astro service page
  -> booking form
  -> Laravel intake queue
  -> diagnosis + quotation
  -> customer approval
  -> Midtrans sandbox payment
  -> repair status page
```

Setelah vertical slice ini lulus end-to-end test, template dan kategori lain dapat diskalakan tanpa mengubah arsitektur.

## 9. Client Input Registry and Safe Fallbacks

Development tidak menunggu seluruh data ini. Dummy/empty-state dipakai di local/staging, tetapi kolom `Required before launch` menjadi production gate.

| Client input | During development | Production fallback | Required before launch |
|---|---|---|---|
| Domain and legal business identity | Demo config | Site can use staging identity only | Yes |
| Logo, colors, and brand assets | Neutral Direpair demo tokens | Text logo temporarily | Yes for public launch |
| Address, coordinates, hours, service area | Demo location, noindex | Hide location page and LocalBusiness fields not verified | Yes for local SEO |
| Phone, WhatsApp, email | Demo contacts in staging | Hide unavailable channel | Yes, at least one verified channel |
| Real workshop/device photos | Neutral placeholders | Keep non-personal neutral images | Strongly recommended |
| Technician names/photos/bios | Demo cards clearly labeled | Hide individual profiles and show workshop team copy | No, but required before technician pages index |
| Certifications | Empty | Omit claim | Only when claimed |
| Public pricing ranges | Demo range | `Estimasi setelah diagnosis` | No |
| Diagnosis/deposit/payment rules | System defaults | Default no booking fee and quote-first flow | Approve before payment go-live |
| Warranty terms | Draft marked legal review | Block warranty claim and production checkout until approved | Yes |
| Terms/privacy/retention | Draft marked legal review | Block production data collection | Yes |
| Testimonials/reviews | None | Hide section and schema | No |
| Repair cases | Demo, non-indexable | Hide section | No |
| Google Business Profile | Not connected | No GBP links/local claims | Required per indexed physical location |
| Midtrans merchant credentials | Sandbox | Payment disabled/manual only | Yes for online payment |
| Bank/manual payment details | Demo | Disable unavailable methods | Only for enabled methods |
| Sparepart catalog/inventory | Demo P1 fixtures | Commerce route disabled | No for MVP |
| Analytics/Search Console IDs | Test containers | Scripts disabled | Yes for measurement, not for functional launch |

Changes to these values happen through WordPress global settings/content entries or Laravel operations settings. No page component should require a developer to replace hard-coded client data.
