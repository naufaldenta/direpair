# Direpair Remaining Roadmap

Dokumen ini memisahkan vertical slice yang sudah diimplementasikan dari pekerjaan yang masih memerlukan data, akun, atau ekspansi scope. Item di bawah tidak dianggap selesai.

## Required Before Production Launch

1. Jalankan plugin terhadap WordPress staging/production-like hosting nyata. Docker local, REST endpoint, seluruh custom post type, PHP lint, dan WP-CLI sudah diverifikasi; browser authenticated dan WordPress integration test masih perlu dibuat.
2. Klien mengganti semua fixture dan memverifikasi identity, kontak, lokasi, foto, profil teknisi, kisaran harga, FAQ, garansi, privacy, dan terms.
3. Set `CMS_FIXTURE_FALLBACK=false`, `PRIVACY_POLICY_VERSION` final, MySQL, private object storage, mail, queue, cache, dan secret production.
4. Uji Midtrans sandbox dengan akun merchant nyata, signature/webhook HTTPS, delayed notification, reconciliation, cancellation, dan refund; implementasi lokal saat ini memakai gateway mock yang mengikuti kontrak yang sama.
5. Tambahkan Turnstile/anti-abuse production, duplicate-request detection, dan malware scanning/quarantine untuk upload sebelum lampiran dapat dibuka staf.
6. Tetapkan retention/deletion policy lalu uji export/delete customer data, attachment, audit, dan backup restore.
7. Hubungkan monitoring untuk health, 5xx, queue failure, webhook failure, payment mismatch, storage, dan uptime.
8. Jalankan accessibility audit, CWV/Lighthouse, structured-data validation, crawl, mobile-device matrix, dan production smoke test.
9. Perketat REST production agar mewajibkan `is_verified=true` dan `publish_consent=true` untuk repair case, bukan hanya mengecualikan fixture demo.
10. Buat status transition payment yang monotonic dan GET Status reconciliation agar notifikasi Midtrans yang terlambat tidak menurunkan transaksi `paid` menjadi `pending`.
11. Implementasikan receiver/pipeline untuk signed WordPress publish webhook atau tetapkan redeploy Astro manual sebagai prosedur resmi.

## P1 After the Vertical Slice Is Stable

- Notification jobs dan provider WhatsApp Business resmi dengan delivery log/retry.
- Workflow garansi lengkap: snapshot coverage, certificate, claim intake, eligibility, inspection, resolution, dan audit.
- Operations roles/permissions terpisah untuk customer service, technician, finance, content approver, dan admin.
- Scheduling pickup/home service, capacity, service-area eligibility, dan biaya kunjungan.
- Knowledge articles, author/reviewer attribution, repair-case detail pages, dan internal-link flywheel.
- Payment reconciliation dashboard, manual payment evidence, refund/cancellation controls, dan finance reporting.
- Private signed upload/download flow, antivirus result, retention expiry, dan attachment access logging.
- Analytics consent mode dan event schema tanpa PII setelah kebijakan privacy disetujui.
- Redirect history untuk perubahan slug dan automated broken-link/404 monitoring.
- WooCommerce sparepart catalog, inventory, cart, checkout, order sync, dan product schema.

## P2 / HOLD

- English berdasarkan demand terukur.
- Customer accounts; guest token flow tetap dipertahankan.
- B2B retailer/distributor portal dan SLA reporting.
- Advanced inventory, technician capacity planning, pricing intelligence, dan repairability report.
- Halaman/commerce vape tetap HOLD sampai legal review eksplisit.
