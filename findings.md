# Findings & Decisions

## 2026-08-31 Vercel & Structured CMS Revision

- The frontend remains an Astro static site: Vercel will run the build and publish it automatically, but WordPress content changes still require a new deployment.
- The preferred content flow is WordPress publish/update -> private Vercel Deploy Hook -> debounced WordPress cron dispatch -> Vercel production build. Git pushes independently trigger preview/production deployments.
- The generated Vercel domain cannot be assumed to be exactly `direpair.vercel.app`; Vercel assigns it from the available project name. Documentation must use a placeholder until the first deployment returns the real hostname.
- WordPress managed CPTs should keep native capabilities, revisions, status, featured images, taxonomies, and notices while removing the block editor/content canvas only for those CPTs. All frontend-consumed values must remain explicit fields or taxonomies.
- A single code-native favicon mark should identify all three surfaces; each application can use a color treatment suited to its surface without inventing separate brands.
- Production origins remain `api.direpair.id` and `cms.direpair.id` on cPanel. The frontend origin is initially the actual Vercel hostname, then changes to `https://direpair.id`; CORS and WordPress public URLs must be updated at cutover.
- Current Astro production keeps public content prerendered and uses the official Vercel adapter only for `/cek-status/[token]`, the single on-demand private route. Public CMS pages retain static output.
- The root and web package scripts still expose cPanel-named build commands and README language. These must be replaced with host-neutral/Vercel commands while keeping ordinary local `build` intact.
- All ten Direpair CPTs currently advertise `editor` support, which is why the Gutenberg canvas appears. The custom meta box already renders every registered meta definition, but it appears below the editor and uses generic English labels without task grouping.
- `Publishing` already sends an asynchronous signed POST after a managed published post is saved. It can target a Vercel Deploy Hook, but its per-post five-second scheduling can create redundant builds; the revised implementation should coalesce events into one site-level deploy and surface delivery failures/status to administrators.
- The WordPress admin theme is plugin-owned and safe to extend. The CMS form redesign can remain within native post edit screens, preserving title, featured image, taxonomies, revisions, publish status, permissions, and notices.
- Astro already links `/favicon.svg`; Laravel has no favicon declaration, and WordPress currently has no plugin-owned admin/login/site icon override.
- Current official Astro guidance confirms Vercel auto-detects Astro. This project additionally needs the official adapter because its private token route is on-demand; non-production branches receive Preview Deployments and the production branch receives Production Deployments.
- Current Vercel guidance requires a Git-connected project for Deploy Hooks. A unique hook URL accepts GET/POST and triggers a deployment without a new commit; the whole static site is rebuilt.
- Vercel assigns every deployment/project a `vercel.app` URL, but availability/project naming determines the exact hostname. The final guide must tell the user to copy the actual hostname from the first Production Deployment.
- For the later apex-domain cutover, add `direpair.id` to the Vercel project first, inspect the exact record Vercel requests, then update the external cPanel DNS zone. Vercel generally requests an A record for an apex and CNAME for a subdomain, but its dashboard-provided value is authoritative.
- Vercel automatically provisions TLS after DNS verification. Existing email/MX and API/CMS subdomain records must remain untouched; changing the website apex record is not the same as changing all nameservers.
- The default Astro mark was replaced with one code-native Repair Interchange SVG and the same source identity is installed explicitly in Astro, Laravel Operations, and WordPress admin/login/site heads. Existing legacy `.ico` files remain only as fallback; explicit SVG declarations take precedence.
- The existing CMS form exposes all technical fields in a flat two-column grid. Its biggest UX problems are not missing storage fields but lack of Indonesian labels, lack of sections, raw numeric relationship IDs, generic checkbox wording, and the Gutenberg canvas appearing before the form.
- Ten managed CPTs share a broad field set. A field schema can add `section`, `description`, `placeholder`, and friendlier control metadata without changing REST keys or stored values.
- Relationship fields currently ask editors to type post IDs. These should become native multi-select controls populated from the relevant managed CPT, retaining the same integer-array storage contract.
- Featured images remain in the familiar native panel, while all Direpair taxonomies now appear as labeled checkbox groups in the main structured form. This removes the long, duplicated sidebar stack seen in the original service editor.
- The previous final deployment guide is fully cPanel-oriented, including manual Astro ZIP/upload and post-publish rebuild steps. It must be rewritten rather than appended, while preserving the proven WordPress/Laravel cPanel paths and setup commands.
- Vercel exposes `VERCEL_PROJECT_PRODUCTION_URL` at build time when system environment variables are enabled. Astro config can derive the canonical production origin from it, allowing the first free-domain build before the exact hostname is known.
- Only Astro config currently consumes `PUBLIC_SITE_URL`; client pages do not require that variable directly. `PUBLIC_API_BASE_URL` and `CMS_BASE_URL` still need explicit Vercel Production/Preview values.
- Laravel currently hard-codes `https://direpair.id` in the cPanel environment template for `FRONTEND_URL`, `PUBLIC_STATUS_URL`, and CORS. Those three values necessarily need one post-first-deploy synchronization to the actual free Vercel hostname, then another controlled switch at the final `direpair.id` cutover.
- Vercel monorepo setup should select `apps/web` as Root Directory. The repository root lockfile and `packageManager: pnpm@11.19.0` let Vercel install the correct workspace package manager; Astro static output remains `dist`.
- A project-local `vercel.json` inside `apps/web` is appropriate for trailing-slash canonicalization and security/privacy headers. It should not introduce rewrites for Astro routes because Vercel warns that Astro path rewrites are unsupported/inconsistent.
- The cPanel frontend `.htaccess` currently provides the dynamic status-token fallback to one static shell. Vercel needs an equivalent route mechanism; because Astro/Vercel warns against generic rewrites, the safer static approach is to make the booking/status URL use `/cek-status/status/?token=...` on Vercel rather than relying on Apache token-path rewriting.
- Existing Laravel public status URLs are centrally configured. The cPanel template should document a temporary Vercel URL in `PUBLIC_STATUS_URL`; no PHP code change is needed to switch it later to `https://direpair.id/cek-status/` after custom-domain cutover.
- The cPanel packager currently builds and packages Astro. It should become a two-artifact backend/CMS packager so users do not accidentally upload a stale frontend after Vercel adoption.
- Demo content stores HTML in `post_content`; the structured form should use a small classic WYSIWYG field rather than a plain textarea so nontechnical editors do not see HTML tags. This remains a single content field, not a page builder.
- Installed `@astrojs/vercel` 11.0.8, whose peer contract explicitly supports Astro 7. The current package exposes its adapter from the main `@astrojs/vercel` export.
- The existing static status shell derives the private token from the URL path and already fetches Laravel client-side. Converting that shell to an on-demand `[token].astro` route requires no private server-side data fetch; Vercel only serves the shell and the browser retains the existing no-PII API contract.
- The resulting deployment is hybrid by behavior: CMS-backed public pages remain prerendered during each Vercel build, while only the opaque-token status shell is on-demand. This removes the Apache rewrite dependency and preserves direct `/cek-status/<token>/` links.

## 2026-08-28 Full-System Audit

- Audit scope: complete PDF requirements, Astro frontend, Laravel API/Operations, WordPress CMS/plugin, local integrations, end-to-end fixture workflow, production-readiness configuration, and deployment documentation for `api.demoaja.com`, `cms-direpair.demoaja.com`, plus a free Astro frontend host.
- "100% work / bug-free" will be treated as a claim requiring evidence. The conclusion will distinguish tested behavior from untested external infrastructure, browser/device coverage, payment sandbox/production credentials, email/queue/cron, cPanel limitations, and client content still intentionally pending.
- The source PDF is confirmed as a tagged, unencrypted, 57-page letter-size blueprint. All 57 pages were rendered and visually inspected across five contact sheets; the document is a strategic architecture/SEO/product backlog rather than a promise that every listed P1/P2/HOLD idea belongs in the first vertical slice.
- Major PDF requirement groups visible across the complete document: SEO-first public information architecture; service/category/detail, symptom/problem, pricing, booking, FAQ, about, contact, location, technician, warranty and legal pages; structured data/canonical/sitemap/robots/Core Web Vitals; headless CMS and editable public content; private repair workflow and demand database; privacy/consent; analytics/reporting; staged repair/spare-parts/e-bike/vape roadmap; and a final P0/P1/P2/HOLD developer backlog.
- The PDF itself leaves factual business inputs unresolved (verified NAP, actual technicians/photos, prices, warranty policy, reviews/cases, hours, service area) and explicitly places vape behind legal review. Those cannot truthfully be scored as implemented production content merely because fixtures exist.
- PDF pages 10-23 define a deliberately broad future sitemap and page-template inventory. P0 surfaces include home, service/category/detail, symptom/problem, pricing, booking/status, location, FAQ, about/contact, warranty/legal; later or expansion surfaces include spare-parts commerce, knowledge/article taxonomy, per-technician detail, B2B, pickup/home-service detail, brand/model programmatic pages, and city pages that must contain genuinely localized evidence rather than doorway copy.
- PDF pages 24-33 require truthful structured data and technical SEO: LocalBusiness only with verified NAP/hours/location, Service/FAQ/Breadcrumb where content supports it, Product/Offer for real commerce, canonical URLs, XML sitemap, restrictive robots rules for private/admin routes, mobile parity, and internal performance budgets (LCP <2.0s, INP <150ms, CLS <0.05, initial compressed HTML <50KB, initial JS <100KB, transfer <500KB, explicit image dimensions/alt and modern formats).
- PDF pages 34-36 recommend Astro plus a headless CMS and a separate operational database/API. They rate generic shared hosting as a budget WordPress option to avoid for a serious speed target, while Cloudflare edge/static hosting is preferred for Astro. The requested cPanel design is technically possible but must be presented with host-capability and performance caveats, not as equivalent to managed WordPress/VPS.
- PDF pages 37-41 require a problem-first diagnostic funnel (device/brand/model/symptom/time/media/service method/area before contact), a proprietary repair dataset, GA4 events without PII, Search Console monitoring, privacy notice/purpose disclosure/data minimization/retention/access control/deletion workflow/secure status/audit/vendor inventory, and consent-aware analytics.
- PDF pages 41-42 put vape behind an explicit legal gate. No public vape SEO, commerce, promotional imagery, paid campaign, or usage-oriented content should ship before review.
- The final backlog on pages 51-52 is the authoritative phase boundary: P0 design/home/service templates/booking/location/schema/meta/sitemap/robots/analytics/Core Web Vitals; P1 knowledge, technician profile, repair cases, pricing, pickup/home service, spare-parts store, and CRM/status; P2 brand/model pages, English, customer accounts, B2B dashboard; HOLD vape. Therefore a compliant vertical slice can be complete for its implemented P0/P1 subset while still being materially short of the blueprint's long-term platform.
- The visual direction asks for a non-generic repair identity, technical typography, real photography, diagnostic diagrams, strong whitespace, simple icons, restrained motion, no giant carousel/autoplay video, and minimal JavaScript. The selected Repair Interchange system satisfies the identity/diagram/restraint direction, but real photography remains a client-supplied CMS input rather than an implemented production asset.
- Repository inventory confirms a real three-application implementation: Astro 7 + Cloudflare adapter, Laravel 13/PHP 8.3 operations and versioned API, and a custom WordPress plugin with CPTs/settings/REST/fixtures/admin theme. Public and private data boundaries are represented in separate codebases and databases.
- The Astro project uses `output: 'server'` with the Cloudflare adapter and a Wrangler deploy command; this is not a plain static-folder deployment. It can target Cloudflare Workers directly, while Vercel would require changing/installing the Vercel adapter or deliberately converting to a static build.
- Production env examples default to local/demo-safe values (`APP_ENV=local`, `APP_DEBUG=true`, SQLite, mock payment, fixture fallback true, draft privacy, demo credentials). Deployment is not production-ready until those values are explicitly replaced and caches rebuilt.
- Source/config search found no implemented GA4/gtag/Consent Mode client integration despite GA4/Search Console being P0 in the PDF. Search Console itself is an external verification/configuration task, but the analytics event layer and consent banner are an implementation gap.
- The root and app scripts provide Astro checks/build/deploy, Laravel tests/audit, Docker WordPress setup, and CI. No single root `check:all` exists; the audit must run component gates explicitly.
- Objective gates are green: Astro check reports 37 files with 0 errors/warnings/hints; Cloudflare server build completes; Laravel runs 8/8 feature tests with 49 assertions; all 11 WordPress plugin PHP files pass PHP 8.3 lint; Composer and pnpm production dependency audits report no known advisories.
- Laravel exposes 13 application routes covering health, booking creation, private status, quotation decision, Midtrans webhook, staff login/logout, work queue, request detail, quotation creation, and status transition. Current local runtime is explicitly not production (`APP_ENV=local`, debug enabled, SQLite, log mail, database queue, uncached config/routes).
- These gates do not include JavaScript unit/E2E tests for frontend behavior, automated accessibility tests, WordPress PHPUnit tests, or real external-service verification. WordPress currently has syntax and runtime REST/UI smoke coverage, not plugin unit coverage.
- Live runtime check: Docker MariaDB is healthy, WordPress REST returns 200 with `X-Robots-Tag: noindex`, Astro and Laravel returned 200 after local services were started, and Laravel CORS allows the configured frontend origin. All 14 Laravel migrations are applied and the deterministic local fixture consists of one request/diagnosis/quotation with two items and two timeline events.
- Recent WordPress/Apache logs show successful admin and all Direpair REST collection requests without PHP fatal errors. MariaDB logs contain historical crash-recovery and `io_uring` fallback messages from Docker restarts, but the current server completed recovery and reports ready for connections; this is not an active application failure.
- The WordPress plugin exposes public read-only custom REST endpoints while protecting publication quality through demo/verification/consent metadata and noindex behavior. Exact production filtering still requires source-level confirmation in the next pass.
- Source review found a real production-boundary defect in the WordPress REST layer: `visibilityMetaQuery()` excludes demo records in production but does not require `is_verified=true`; repair-case endpoints also do not require `publish_consent=true`. Astro applies additional trust/consent filtering before rendering relevant pages, but the public CMS REST URLs could still expose non-demo unverified or unconsented records directly. Classify as P1 privacy/publishing risk before launch.
- Current local WordPress data is entirely demo-mode: 5 services, 4 problems, 1 FAQ, 1 technician, 1 location, 1 repair case, 1 warranty, and 2 policies; every inspected record is marked demo. This is correct for development but proves the production content gate is not yet satisfied.
- Astro validates CMS responses with Zod, times out fetches after four seconds, can fail closed when fixture fallback is disabled, filters trusted public content in production, emits canonical/OG/Organization JSON-LD, and conditionally builds sitemap URLs. Status/mock-payment routes are excluded from robots and status responses receive no-store/noindex headers.
- The CSP currently permits `'unsafe-inline'` for scripts and styles to support Astro inline output. This is weaker than a nonce/hash CSP and should be recorded as a P2 hardening item, not called a release blocker by itself.

## Requirements
- Buat urutan implementasi website Direpair dari awal sampai launch dan pengembangan lanjutan.
- Terjemahkan blueprint PDF menjadi keputusan teknis dan alur bisnis konkret.
- Foto teknisi, harga, lokasi, garansi, layanan, FAQ, dan data bisnis lain harus dinamis serta dapat diubah klien.
- Sediakan dummy data untuk development/demo, tetapi jangan hard-code data bisnis ke komponen UI.
- Ambil keputusan rasional untuk alur diagnosis, quotation, payment, repair status, dan warranty berdasarkan blueprint.
- Gunakan Astro dan WordPress sesuai rekomendasi PDF bila tetap menjadi pilihan terbaik.

## Research Findings
- Repository saat ini hanya berisi PDF blueprint, metadata skill, dan folder sementara; belum ada codebase website.
- PDF menetapkan Astro + headless CMS sebagai best fit, WordPress + Gutenberg sebagai alternatif operational simplicity, dan heavy page builder sebagai opsi yang tidak dipilih.
- PDF memisahkan static SEO pages dari interactive functions seperti booking, diagnosis, cart, dan repair status.
- PDF menempatkan operational repair database terpisah dari CMS/content layer.
- Funnel inti: Search/Maps/AI/referral -> service/problem/location page -> cek kerusakan -> pilih device/symptom -> metode service -> kontak -> diagnosis -> quotation -> approval -> repair -> payment/return -> review/warranty/repeat.
- Harga final tidak boleh dipalsukan; halaman publik menampilkan diagnosis fee, kisaran jasa, sparepart terpisah, dan keputusan final setelah diagnosis.
- WhatsApp adalah tertiary CTA/fallback, bukan satu-satunya jalur booking.
- Website Indonesia-first; English, customer account, dan B2B dashboard adalah P2.
- Dokumentasi Astro mengonfirmasi WordPress dapat dipakai headless melalui REST API bawaan; GraphQL bersifat opsional.
- WordPress Custom Post Types dan taxonomies dapat diekspos secara resmi melalui REST API dengan `show_in_rest => true`.
- Dokumentasi Cloudflare terbaru menyarankan proyek baru dimulai di Workers; Astro mendukung static assets maupun SSR/on-demand rendering di Workers.
- Cloudflare Pages masih mendukung WordPress-triggered deploy hooks, tetapi Pages bukan lagi pilihan default utama untuk proyek baru menurut dokumentasi Cloudflare Agustus 2026.
- Midtrans Snap membuat token transaksi dari merchant backend dan mengirim perubahan status melalui HTTP webhook ke backend.
- Midtrans mewajibkan keputusan finansial menggunakan status server-side yang telah diverifikasi; callback browser tidak boleh dijadikan sumber kebenaran.
- Midtrans merekomendasikan webhook idempotent, verifikasi signature/GET Status, HTTPS, serta rekonsiliasi bila notifikasi terlambat.
- WooCommerce menyediakan REST API untuk produk/order, Store API untuk cart/checkout publik, dan signed webhooks untuk sinkronisasi perubahan produk/order.
- Laravel menyediakan validation, database transactions, queued notifications, dan filesystem abstraction yang sesuai untuk booking, upload, quotation, status, dan webhook payment.
- Workspace berjalan di bawah Laragon/PHP, sehingga Laravel menambah kemampuan backend tanpa memperkenalkan runtime backend yang asing bagi lingkungan lokal saat ini.
- Runtime audit implementation: PHP 8.3.30, Node 24.14.1, pnpm 11.19.0, Composer 2.9.5, dan MySQL client 8.4.3 tersedia.
- MySQL server lokal tidak sedang berjalan pada `localhost:3306`; vertical-slice tests akan memakai SQLite dan production tetap dikonfigurasi untuk MySQL.
- Repository belum diinisialisasi sebagai Git repository.
- Composer 2.9.5 melaporkan advisories dan merekomendasikan 2.10.2; jangan mengubah Composer global dari proyek ini, tetapi audit project dependencies dan dokumentasikan prerequisite runtime yang aman.
- Package registry audit: Astro 7.2.6 dan Laravel application skeleton 13.10.1 tersedia; Laravel 13 membutuhkan PHP 8.3 yang dipenuhi runtime lokal.
- Composer 2.10.2 PHAR berhasil diunduh dari sumber resmi dan diverifikasi dengan SHA-256 sebelum digunakan.
- Git repository telah diinisialisasi pada branch `main`.
- Astro scaffold completed with Astro 7.2.6 and pnpm.
- Laravel scaffold completed from skeleton 13.10.1 with framework 13.27.0; default SQLite database and base migrations were created successfully.
- Laravel dependency install reported no package security advisories.
- Scaffold baseline verification passed: Astro static build (1 route), Laravel PHPUnit suite (2 tests), and Composer audit all succeeded.
- `apps/web/AGENTS.md` requires Astro development servers to use Astro background mode.
- `apps/api/AGENTS.md` requires installing Laravel Boost and re-reading generated Laravel guidelines before application changes.
- Laravel Boost 2.6.0 installed successfully with no dependency security advisories.
- Laravel Boost generated application-specific rules: use Artisan generators, versioned Eloquent API Resources, explicit PHP types, PHPUnit feature tests, and Pint formatting.
- Before Laravel file edits, `.ai/rules/index.md` and matching rule files must be read; relevant generated skills must also be activated.
- `.ai/rules` does not exist yet, so there are no path-scoped project rules to load; Boost says continuing is valid in this state.
- Activated/read generated `laravel-best-practices` and `testing-best-practices` skills; the fresh skeleton has insufficient evidence for `infer-conventions`, so no convention rules are recorded yet.
- Laravel implementation rules selected for this vertical slice: focused reversible migrations with indexes, typed Eloquent relationships/casts, Form Requests, thin versioned API controllers, action classes, gateway contracts, config-only env access, verified outbound HTTP, JSON exception rendering, and queued/after-commit notifications.
- Test strategy selected: PHPUnit feature tests first, `LazilyRefreshDatabase`, factories per test, exact endpoint fakes, explicit validation/security coverage, known-value assertions, and no real network calls.
- Laravel Boost MCP tools (`search-docs`, `database-schema`, `record-rule`) are not exposed in the current tool session after installation; use official versioned docs and installed source/Artisan inspection as fallback.

## Technical Decisions
| Decision | Rationale |
|----------|-----------|
| WordPress menjadi content control plane | Klien membutuhkan UI familiar untuk mengganti seluruh data publik tanpa mengubah source code |
| Astro menjadi presentation/SEO frontend | Cocok untuk halaman statis cepat dan islands untuk komponen interaktif |
| Data booking/repair/payment tidak disimpan sebagai post WordPress | Mengurangi pencampuran PII dan data operasional sensitif dengan CMS publik |
| Dummy data dimasukkan melalui seed/import yang idempotent | Dapat di-reset dan diganti tanpa menyentuh komponen atau layout |
| Payment tidak dilakukan penuh saat booking | Jenis repair niche membutuhkan diagnosis dan quotation sebelum harga final diketahui |
| Gunakan WordPress REST API sebagai kontrak awal, bukan GraphQL | REST API tersedia native; mengurangi plugin wajib dan operational coupling |
| Target hosting direvisi dari Pages ke Cloudflare Workers | Workers kini menjadi platform utama Cloudflare dan mendukung Astro static maupun hybrid rendering |
| Mayoritas halaman di-prerender; SSR hanya untuk kebutuhan yang benar-benar dinamis | Menjaga SEO/performance sambil menghindari rebuild berlebihan |
| Midtrans Snap menjadi default gateway repair | Cocok untuk membuat transaksi setelah quotation disetujui dan memiliki alur webhook server-side yang terdokumentasi |
| Booking tidak memungut full payment | Harga final baru valid setelah diagnosis; pembayaran dibuat dari invoice/quotation yang telah disetujui |
| WooCommerce ditunda ke fase sparepart commerce | MVP repair tidak perlu menanggung kompleksitas cart, checkout, stock, dan order commerce |
| Laravel API + MySQL menjadi repair operations backend | Workflow repair bersifat transaksional, memerlukan audit/state, queue, upload, notification, dan webhook; stack ini juga cocok dengan Laragon lokal |
| Admin publik dan admin operasional dipisah | `/wp-admin` untuk konten publik; operations dashboard untuk booking, diagnosis, quotation, payment, dan warranty |
| Public pricing range berada di WordPress; actual quotation di Laravel | Konten pemasaran tetap editable, sedangkan harga yang disetujui tetap versioned dan auditable |
| Full payment default setelah quality control, sebelum handover | Menghindari tagihan penuh sebelum repairability diketahui; deposit hanya untuk kebutuhan tertentu |
| WhatsApp meneruskan request number yang sudah dibuat | Menjaga convenience tanpa menghasilkan lead yang hilang dari database |
| Demo data diblok dari production schema/indexing | Dummy membantu development tetapi tidak boleh menjadi klaim bisnis palsu |
| First milestone adalah vertical slice end-to-end | Menguji arsitektur dan workflow sebelum memperbanyak page/template |
| SQLite untuk local/test, MySQL untuk staging/production | Implementasi dapat diuji tanpa bergantung pada service MySQL Laragon yang sedang mati |
| Laravel API follows generated Boost conventions | Project-local guidelines are load-bearing and version-aware |
| Production WordPress collections require `is_verified=true` | Prevents non-demo but still unreviewed profiles, prices, locations, policies, and claims from entering indexed pages |
| Repair cases require explicit publish consent | A verified CMS entry is insufficient when customer/device evidence has no publication permission |
| Consent version is snapshotted in Laravel | A timestamp alone cannot prove which privacy text the customer accepted |
| Fixture builds disable indexing, schema, sitemap URLs, and booking | Demo content remains useful locally while a misconfigured production build fails closed |
| Status pages and API responses use no-store/no-referrer controls | Private opaque tokens must not enter caches, search results, or outbound referrers |
| Custom content-aware sitemap replaces the generic generator | Conditional technician, location, case, warranty, and policy pages are listed only when publishable content exists |
| Direpair visual identity will be replaced, not refined | User explicitly allowed a total change; current orange/teal/cream, Inter/Arial Narrow, generic cards and gradient haze become anti-reference rather than authority |
| One brand system, three surface modes | Astro is Persuade/Read, Laravel is Operate, and WordPress is Operate/editorial; expression changes by task while brand DNA remains recognizable |
| WordPress redesign uses a plugin-scoped admin theme layer | Broadly reskin navigation, lists, editors, fields, and Direpair settings while preserving WordPress semantics, accessibility, plugin compatibility, and update safety |
| Replacement-world lead direction is a numbered repair bay / workshop shadow-board | The repair workflow becomes the visual material itself: bays, tags, calibration marks, and status rails work across public, operations, and editorial surfaces without relying on generic repair imagery |
| Transit wayfinding is the strongest grounded alternative | It has Indonesian urban resonance and communicates diagnosis-to-repair progression clearly, while remaining distinct from ordinary repair-site conventions |
| Challengers are limited to reverse cabinet, event reconstruction, and jackfield routing | These are the only seeded challengers that materially improve inventory topology, diagnostic causality, or operational state grammar; rain-neon, iridescent cloud, and raygun directions were declined |
| Service Line Atlas is the committed replacement world | The user selected it explicitly; route lines, station codes, enamel signage, repair tickets, and converging service paths now govern Astro, Laravel Operations, and WordPress editorial UI |
| The comp round tests topology, not identity | Original Atlas, Service Platform, and Repair Interchange share the same palette/material language while varying headline dominance, route sequence, intake placement, and diagnostic focal geometry |
| Repair Interchange is the approved spatial contract | Three device branches converge on an oversized diagnosis hub; quotation, repair, and completion leave as one traceable line; the ticket CTA overlaps the hub without becoming a floating generic card |
| Comp texture and geometry use different media | Paper grain must be a produced raster; route lines, station rings/codes, device pictograms, focus states, and motion remain semantic HTML/CSS/SVG so they scale, react, and preserve accessibility |

## Issues Encountered
| Issue | Resolution |
|-------|------------|
| Sequential-thinking MCP tidak tersedia | Gunakan tahapan eksplisit, review keputusan, dan file-based planning |
| MySQL local connection refused | Gunakan SQLite untuk local/test; pertahankan MySQL environment template untuk deployment |
| Composer global tertinggal dan memiliki advisories | Jangan mutate tool global; pin dan audit project dependencies, lalu dokumentasikan Composer minimum yang aman |
| Planning patch pertama gagal karena anchor tidak tepat | Baca lokasi aktual dengan `rg`, lalu terapkan patch yang lebih kecil dan spesifik |
| Verifikasi Composer PHAR gagal karena PowerShell memperlakukan response checksum sebagai byte sequence dan menghasilkan token `53` | Simpan checksum response ke file lalu baca sebagai teks sebelum membandingkan; jangan jalankan PHAR sebelum hash cocok |
| `.ai/rules/index.md` tidak ditemukan setelah Boost install | Per guideline, lanjut tanpa project-specific rules; gunakan generated skills dan base Boost guidelines |
| Boost MCP tools tidak tersedia di current session | Gunakan official Laravel 13 docs, `artisan` inspection, and installed framework source; record the limitation |
| Root `pnpm install` stopped with `ERR_PNPM_IGNORED_BUILDS` for esbuild 0.28.2 | Inspect pnpm 11 project approval syntax, explicitly allow the exact build dependency, then reinstall without repeating the same configuration |
| `apply_patch` menolak dua operation untuk file yang sama | Gabungkan perubahan file tersebut dalam satu `Update File` operation |
| PHP 8.3 menolak kombinasi ternary/Elvis tanpa tanda kurung pada sanitasi array meta | Buat precedence eksplisit dan jadikan full-plugin PHP lint sebagai gate Phase 7 |
- pnpm 11 writes pending build approvals into `allowBuilds`; setting `esbuild: true` resolved the install while keeping other dependency scripts denied by default.
- WordPress implementation exposes a stable normalized `direpair/v1` contract instead of coupling Astro to raw post-meta response shapes.
- Demo CMS records are idempotent and explicitly marked `is_demo`, `noindex`, and `is_verified=false`; production guards prevent them from being published or returned by the custom catalog endpoints.
- CMS publish notifications are signed with HMAC and dispatched asynchronously through WP-Cron so an editor save is not blocked by the frontend rebuild target.
- All WordPress plugin PHP files pass PHP 8.3 syntax lint; runtime integration will be covered later against a real WordPress environment because WordPress core is not installed in this workspace.
- Laravel vertical slice passes 8 feature tests / 43 assertions after Pint formatting; migrations rebuild cleanly on SQLite and project dependencies have no known Composer advisories.
- Public repair tokens are never stored in plaintext: only an HMAC hash is persisted, while the raw 48-character token is returned once at booking creation.
- Payment state advances only from a verified gateway notification whose order and amount match a stored pending payment; browser redirects never mark an invoice paid.
- Final-payment invoices are created from the approved quotation balance after subtracting prior paid invoices, so a deposit is not charged twice.
- Astro consumes only the normalized custom WordPress API and validates it with Zod; a CMS outage falls back to explicitly marked fixtures unless `CMS_FIXTURE_FALLBACK=false`.
- The public frontend has no hard-coded trust claims such as review counts, years of experience, technician credentials, or real locations; those surfaces remain empty until client-controlled CMS values exist.
- Visual QA found one mobile UX issue (booking initialization scrolled directly to the form) and a one-pixel overflow edge; both were corrected in source.
- The public UI now consumes client-controlled featured images for service cards/details, problem pages, technicians, locations, and repair cases, with neutral code-native placeholders when no image exists.
- Full local HTTP integration passed booking, secure status lookup, quotation approval, deposit creation, duplicate payment notification, and status advancement; local data was reset to the deterministic initial fixture afterward.
- Final verification passes Astro check/build with 36 source files, Laravel 8/8 feature tests with 49 assertions, full migration/seed, 13 routes, WordPress PHP lint, and both pnpm/Composer dependency audits.
- Docker Desktop is absent on the current machine, so actual WordPress core/Compose runtime activation remains a launch-prerequisite test; plugin code and its normalized contract are implemented and statically validated.
- Docker Desktop and WSL 2 are now operational on the user's Windows 11 host. The Compose stack starts successfully with MariaDB healthy and WordPress available on port 8080.
- Live WordPress initialization passed: environment `local`, Direpair Content plugin active, and the idempotent seed created 5 services, 4 problems, and 12 supporting entries. The custom health/catalog/settings/policies routes return HTTP 200.
- The Apache `ServerName` startup notice is benign for this local container and no WordPress/PHP fatal errors appeared during REST verification.
- Remaining launch, P1, P2, and HOLD scope is explicitly separated in `docs/remaining-roadmap.md`.

## Resources
- `Blueprint Website Direpair_ Lightweight, SEO-First, Local GEO & AI-Ready untuk Repair Produk Non-Mai.pdf`
- `task_plan.md`
- Astro WordPress guide: https://docs.astro.build/en/guides/cms/wordpress/
- WordPress REST custom content types: https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-rest-api-support-for-custom-content-types/
- Cloudflare Astro on Workers: https://developers.cloudflare.com/workers/framework-guides/web-apps/astro/
- Cloudflare Pages deploy hooks: https://developers.cloudflare.com/pages/configuration/deploy-hooks/
- Midtrans Snap integration: https://docs.midtrans.com/docs/snap-snap-integration-guide
- Midtrans webhooks: https://docs.midtrans.com/docs/https-notification-webhooks
- WooCommerce API overview: https://developer.woocommerce.com/docs/apis/
- WooCommerce webhooks: https://woocommerce.com/document/webhooks/
- Laravel validation: https://laravel.com/docs/13.x/validation
- Laravel filesystem: https://laravel.com/docs/13.x/filesystem
- Laravel notifications: https://laravel.com/docs/13.x/notifications

## Visual/Browser Findings
- PDF terdiri dari 57 halaman dengan diagram sitemap, funnel conversion, architecture Astro/CMS, repair-data flywheel, dan developer backlog.
- Dokumen bersifat strategic blueprint; data nyata seperti alamat, telepon, harga, teknisi, SLA, dan garansi masih placeholder.
- Live browser smoke test at `http://localhost:4321` rendered successfully from the local WordPress stack, including 5 services, 4 problem guides, and supporting demo content.
- Live CMS data exposed two presentation edge cases: unset numeric price meta became `Rp 0`, and a WordPress-encoded ampersand was rendered literally as `&#038;`. These need normalization before browser handoff.
- Both live-data edge cases were fixed at the normalized WordPress REST boundary: absent meta stays `null` while explicit zero remains representable, and public plain-text fields decode WordPress HTML entities.
- Browser authentication verified both admin surfaces with local fixture accounts: WordPress exposes all Direpair CPT/settings menus and Laravel operations exposes the seeded repair request workflow.
- Approved-comp baseline at 1586×992 showed the Astro first viewport preserving the Repair Interchange structure, palette, branch convergence, diagnosis hub, outbound stations, and ticket CTA; the remaining fidelity defect was vertical compression, with the ticket ending roughly 28px below the fold.
- Mobile baseline at 390×844 retained semantic order but the ticket measured roughly 467px wide. The root cause was intrinsic sizing inside the single-column route canvas, not document-level horizontal overflow.
- Repair Interchange should use soft optical elevation only on true foreground tickets/forms; operational panels, WordPress tables, and ordinary content cards read more faithfully as bordered paper surfaces without offset shadows.
- Route ring `box-shadow: 0 0 0 ...` declarations are deliberate station geometry rather than decorative elevation and remain part of the selected wayfinding language.

## 2026-08-28 Full-System Audit Findings

- The implemented vertical slice is functional locally but cannot honestly be rated 100% production-ready.
- Astro check/build, Laravel feature tests, WordPress PHP lint/runtime, Composer audit, and pnpm production audit are green.
- All 24 discovered internal frontend URLs returned 200. Five real service slugs and four real problem slugs from WordPress render correctly; guessed/nonexistent slugs correctly return the custom 404.
- Browser regression checks at 390×844, 1440×1000, and 1920×1080 found no horizontal overflow on representative public pages. Booking exposes 19 labeled controls with no duplicate IDs, and browser logs contained no warning/error.
- The Repair Interchange hero entrance deliberately blurs the diagnosis hub for about 1.3 seconds. After 1.8 seconds, the filter is `blur(0px)`, Diagnosis is fully inside the hub, and the status link does not intersect the ticket.
- The private status API returns no customer email/phone and sends `Cache-Control: no-store, private`, `Referrer-Policy: no-referrer`, and `X-Robots-Tag: noindex, nofollow`.
- `HandlePaymentNotificationAction` accepts a verified but older `pending` notification after `paid` and rewrites payment, invoice, and request payment status. Midtrans explicitly documents that settlement may arrive before pending and says to query GET Status or ignore late pending. This is a production payment blocker.
- WordPress `RestApi::visibilityMetaQuery()` production only excludes demo content. It does not require `is_verified`, nor `publish_consent` for repair cases. Astro adds its own filters, but the direct REST surface remains too permissive.
- The P0 blueprint backlog is incomplete: `LocalBusiness`, `BreadcrumbList`, GA4/Consent Mode, Search Console, and measured production CWV remain outstanding. `Service` schema, canonical/OG, sitemap, robots, design system, templates, and booking/status vertical slice exist.
- P1 remains partial: knowledge frontend, technician/case detail, scheduling/coverage, and spare-parts commerce are absent; CRM/status and public pricing are present.
- Every current WordPress content record is demo data. Production identity, NAP, technicians, images, pricing, warranty, privacy, terms, and publication consent must be supplied and verified by the client.
- WordPress had many due `direpair_dispatch_content_webhook` events in local runtime. Production must use system cron/WP-CLI or an HTTPS cron trigger.
- Laravel currently defines no scheduled tasks and no queued jobs, so a cPanel worker is not required until reconciliation/notifications are implemented.
- The signed WordPress publish webhook has no receiver/deploy pipeline. Because public Astro pages are prerendered, CMS changes require a manual Worker redeploy until that pipeline exists.
- Cloudflare Workers is the lowest-change frontend target because the repository already uses `@astrojs/cloudflare`, `output: server`, and Wrangler. Vercel is possible only after changing adapters.
- The generated deployment guides are `docs/system-audit-2026-08-28.md` and `docs/deployment-cpanel-cloudflare.md`.

## 2026-08-28 Deployment Topology Revision Findings

- Final public origins are `api.demoaja.com` (Laravel), `cms-direpair.demoaja.com` (WordPress), and `direpair.demoproyek.software` (Astro Worker).
- Laravel and WordPress share one cPanel hosting account/server, but must retain separate document roots, databases, database users, admin credentials, and application responsibilities.
- The existing runbook incorrectly assumed `direpair.demoaja.com` and instructed moving `demoaja.com` DNS to Cloudflare. Those instructions must be removed because API/CMS hostnames are already provided by the cPanel host.
- Only the existing Cloudflare zone `demoproyek.software` is involved in the frontend setup. The safest first-deploy flow is Worker deployment followed by adding `direpair.demoproyek.software` as that Worker's Custom Domain.
- The repository has no `.openai/hosting.json`; the Sites workflow is not applicable to this Cloudflare Workers deployment tutorial.
- Current Astro content routes are partly prerendered at build time, so local or CI build environment values are required. Runtime-only Worker variables are not a substitute for `CMS_BASE_URL` during the build.
- Cloudflare's current Custom Domain flow requires an active Cloudflare zone and an already-created Worker. Dashboard path: Workers & Pages → select Worker → Settings → Domains & Routes → Add → Custom Domain.
- Cloudflare automatically creates the DNS record and issues the hostname certificate for a Worker Custom Domain. A pre-existing CNAME on the same hostname prevents creation, so `direpair.demoproyek.software` should not be manually created first; remove a conflicting record if one already exists.
- Current Workers Free limits include 100,000 requests/day, 10 ms CPU/request, 128 MB memory, and a 3 MB compressed Worker bundle; the runbook must frame these as current limits to monitor, not permanent guarantees.
- Workers Builds supports monorepo root-directory, explicit build/deploy commands, and build-only variables. A manual CLI deployment remains the primary first-launch path; Git automation can be documented as an optional follow-up.
- cPanel's document-root setting only changes where the web server looks; it does not move application files. Existing API/CMS hostnames therefore still require explicit document-root verification even though no DNS/domain creation is needed.
- Repository-specific Cloudflare deployment is already wired through `apps/web/package.json`: `pnpm build && wrangler deploy --config dist/server/wrangler.json`. The tutorial should use `pnpm --dir apps/web deploy` instead of generic autoconfiguration commands.
- The monorepo pins pnpm 11.19.0 and requires Node 22.12.0 or newer. Astro uses `@astrojs/cloudflare`, `output: 'server'`, and a generated deployment config under `apps/web/dist/server/wrangler.json`.
- The source `apps/web/wrangler.jsonc` names the Worker `direpair-web`, but the package deployment deliberately uses the adapter-generated config; custom-domain setup is therefore safest in the dashboard after the first deploy unless repository configuration is later consolidated.
- The currently generated `apps/web/dist/server/wrangler.json` preserves the Worker name `direpair-web`, entry `entry.mjs`, static-assets binding, compatibility date, and observability setting, so the documented package deploy command targets the intended Worker without additional route configuration.
- For optional Workers Builds, pin `NODE_VERSION=22.12.0` and `PNPM_VERSION=11.19.0` as build variables: Cloudflare's current default pnpm is older than this repository's pinned pnpm version.

## 2026-08-31 Full Shared-cPanel Conversion Findings

- Final deployment topology supersedes all earlier demo/Cloudflare origins: `direpair.id`, `api.direpair.id`, and `cms.direpair.id`, all under cPanel user `/home/direpair`.
- Astro must become a static build whose `dist` contents are uploaded to `/home/direpair/public_html`; Cloudflare adapter, Worker deploy commands, and Worker custom-domain instructions are no longer appropriate.
- Laravel remains a stateful PHP application and WordPress remains a PHP CMS. Both retain their own database and responsibility boundary even though all three surfaces share one hosting account.
- Current Astro source includes dynamic-looking parameter routes for service/problem/status, Cloudflare adapter configuration, Wrangler config, and Worker-oriented deploy scripts. Static conversion must verify that every parameter route has build-time paths or can be replaced with client-side status lookup.
- The cPanel frontend document root must receive the contents of `apps/web/dist`, not the `dist` directory itself, otherwise `direpair.id` would serve from `/dist/` rather than the domain root.
- All public Astro pages are already prerendered except `cek-status/[token].astro`. Service and problem detail routes already implement `getStaticPaths()` from WordPress, so they are compatible with static output as long as CMS production is reachable during build.
- The private status page currently fetches data during Worker SSR and sets response headers through `Astro.response`. Static cPanel requires moving that fetch/render path to browser JavaScript and using an Apache/LiteSpeed `.htaccess` rewrite plus header rules for arbitrary `/cek-status/<token>/` URLs.
- Booking already performs browser-side API requests and receives a Laravel-generated `status_url`; after domain updates it remains compatible with static hosting.
- Root/package scripts and lockfile still contain Cloudflare adapter/Wrangler dependencies. Static conversion should remove them rather than merely leaving unused Worker code behind.
- `apps/web/public/_headers` contains the privacy/cache rules previously interpreted by Cloudflare. Apache/LiteSpeed needs equivalent directives in `apps/web/public/.htaccess`; otherwise the file is inert on cPanel.
- Laravel already reads all cross-origin/public-status values from environment configuration, so domain conversion requires no controller change: production env values are sufficient.
- WordPress public settings use `booking_path` and `status_path`, but the plugin exposes no `frontend_origin` setting. Production setup must configure only the real option keys and must not repeat the inaccurate frontend-origin instruction from the older runbook.
- The static production build must use `CMS_BASE_URL=https://cms.direpair.id/wp-json/direpair/v1` with fixture fallback disabled, so WordPress/plugin/content must be live before the final Astro build.
- On 2026-08-31, the final API and CMS health endpoints still returned hosting-level 404 responses and their TLS chain was not trusted by the local Windows client; `direpair.id` returned an existing PHP page. These are expected pre-upload/pre-SSL deployment states, not evidence that the new artifacts are live.
- A QA static build using an intentionally unreachable CMS plus explicit fixture fallback completed successfully with 27 pages. The final production build must be repeated without that override after `cms.direpair.id` is healthy.
- The Laravel payment handler now treats paid/refunded states as monotonic: a verified late pending/failed/expired/cancelled notification cannot regress a paid invoice/request, while a legitimate refund can still advance paid to refunded. A settlement-then-late-pending feature test covers the original blocker.
- The WordPress production REST boundary now requires `is_verified=1`, excludes demo content, and additionally requires `publish_consent=1` for repair cases. Local/staging fixture behavior is unchanged.
