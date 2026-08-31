import type { CmsContent, CmsDataset, SiteSettings } from '../lib/cms-types';

const baseMeta = {
  external_uuid: '', is_demo: true, is_verified: false, noindex: true,
  primary_cta_label: 'Cek kerusakan', primary_cta_url: '/booking/', currency: 'IDR',
};

const content = (
  id: number,
  type: string,
  slug: string,
  title: string,
  excerpt: string,
  meta: Record<string, unknown>,
  body = '',
): CmsContent => ({
  id,
  uuid: `fixture-${type}-${id}`,
  type,
  slug,
  title,
  excerpt,
  content: body || `<p>${excerpt}</p>`,
  featured_image: null,
  meta: { ...baseMeta, ...meta },
  terms: {},
  published_at: '2026-08-26T00:00:00+07:00',
  modified_at: '2026-08-26T00:00:00+07:00',
});

export const fixtureSettings: SiteSettings = {
  business_name: 'Direpair',
  tagline: 'Kami Memperbaiki yang Sulit Diperbaiki.',
  phone: '', whatsapp: '', email: '', street_address: '', city: '', region: '', postal_code: '', country_code: 'ID',
  opening_hours_summary: '', booking_path: '/booking/', status_path: '/cek-status/', instagram_url: '', youtube_url: '', facebook_url: '',
  default_pricing_disclaimer: 'Harga final diberikan setelah diagnosis dan sebelum pekerjaan dimulai.',
  demo_mode: true,
};

export const fixtureDataset: CmsDataset = {
  source: 'fixture',
  settings: fixtureSettings,
  services: [
    content(1, 'direpair_service', 'tws', 'TWS & Wireless Earbuds', 'Diagnosis untuk masalah charging, baterai, koneksi, audio, dan charging case.', {
      quick_answer: 'Kami memeriksa penyebab kerusakan dulu, lalu mengirim estimasi sebelum repair dimulai.', price_from: 150000, price_to: 650000, diagnosis_fee: 50000, turnaround_min_days: 2, turnaround_max_days: 7, service_methods: ['drop-off', 'pickup'], supported_models: ['Sony WF series', 'JBL Tune series', 'Soundcore Liberty series'],
    }),
    content(2, 'direpair_service', 'vacuum', 'Vacuum & Cleaning Tech', 'Service vacuum cordless dan robot vacuum untuk masalah daya, baterai, charger, motor, dan sensor.', {
      quick_answer: 'Pemeriksaan menyeluruh mencegah penggantian baterai atau motor yang belum tentu diperlukan.', price_from: 175000, price_to: 950000, diagnosis_fee: 75000, turnaround_min_days: 2, turnaround_max_days: 10, service_methods: ['drop-off', 'pickup'],
    }),
    content(3, 'direpair_service', 'household', 'Household Electronics', 'Perangkat rumah tangga non-mainstream: air purifier, humidifier, coffee machine, dan lainnya.', {
      quick_answer: 'Kelayakan repair mengikuti hasil diagnosis dan ketersediaan komponen.', diagnosis_fee: 75000, turnaround_min_days: 3, turnaround_max_days: 14, service_methods: ['drop-off', 'pickup'],
    }),
    content(4, 'direpair_service', 'sepeda-listrik', 'Sepeda Listrik', 'Diagnosis baterai, charger, controller, wiring, throttle, motor, dan sistem kelistrikan.', {
      quick_answer: 'Kami melacak masalah dari sumber daya sampai motor agar komponen tidak diganti berdasarkan tebakan.', price_from: 200000, price_to: 2500000, diagnosis_fee: 100000, turnaround_min_days: 1, turnaround_max_days: 14, service_methods: ['drop-off', 'pickup', 'home-service'],
    }),
    content(5, 'direpair_service', 'lainnya', 'Produk Lainnya', 'Tidak menemukan perangkat kamu? Kirim detail dan foto untuk pemeriksaan awal.', {
      quick_answer: 'Tim Direpair akan menilai apakah perangkat dapat masuk proses diagnosis.', service_methods: ['drop-off', 'pickup'],
    }),
  ],
  problems: [
    content(11, 'direpair_problem', 'tws-mati-sebelah', 'TWS Mati Sebelah', 'Kemungkinan masalah pada baterai, charging contact, driver, PCB, atau firmware.', { quick_answer: 'Reset dan bersihkan charging contact lebih dulu. Hentikan pemakaian bila unit panas atau baterai mengembung.', related_service_ids: [1] }),
    content(12, 'direpair_problem', 'tws-tidak-charging', 'TWS Tidak Bisa Di-charge', 'Langkah aman dan diagnosis untuk earbud atau charging case yang tidak mengisi.', { quick_answer: 'Jangan memaksa charging bila perangkat panas, berbau, atau baterai terlihat mengembung.', related_service_ids: [1] }),
    content(13, 'direpair_problem', 'vacuum-tidak-menyala', 'Vacuum Tidak Menyala', 'Baterai, charger, switch, motor, sensor, atau control board perlu diperiksa berurutan.', { quick_answer: 'Mulai dari charger dan indikator. Diagnosis diperlukan bila tidak ada respons atau unit langsung mati.', related_service_ids: [2] }),
    content(14, 'direpair_problem', 'sepeda-listrik-tidak-jalan', 'Sepeda Listrik Tidak Jalan', 'Baterai penuh belum tentu berarti controller, wiring, throttle, brake cut-off, dan motor berfungsi.', { quick_answer: 'Matikan unit bila ada panas, bau, atau kabel meleleh, lalu jangan charge sebelum diperiksa.', related_service_ids: [4] }),
  ],
  faqs: [
    content(21, 'direpair_faq', 'apakah-bisa-diperbaiki', 'Bagaimana mengetahui perangkat bisa diperbaiki?', 'Repairability ditentukan setelah pemeriksaan penyebab, kondisi unit, dan ketersediaan komponen.', {}),
    content(22, 'direpair_faq', 'apakah-ada-biaya-diagnosis', 'Apakah ada biaya diagnosis?', 'Biaya diagnosis mengikuti jenis perangkat dan diinformasikan sebelum unit diperiksa.', {}),
    content(23, 'direpair_faq', 'kapan-bayar', 'Kapan saya harus membayar?', 'Tidak ada pembayaran penuh saat booking. Kamu membayar setelah quotation disetujui; deposit hanya bila pekerjaan membutuhkannya.', {}),
    content(24, 'direpair_faq', 'bagaimana-cek-status', 'Bagaimana memantau status repair?', 'Setelah booking, kamu menerima nomor request dan tautan privat untuk melihat timeline, quotation, dan payment.', {}),
  ],
  technicians: [
    content(31, 'direpair_tech', 'demo-teknisi', '[Demo] Teknisi Direpair', 'Profil contoh untuk menguji layout. Ganti nama, foto, dan bio melalui WordPress sebelum launch.', {
      specialties: ['TWS', 'Vacuum', 'Sepeda listrik'], years_experience: 0,
    }, '<p>Konten demo. Jangan gunakan sebagai klaim pengalaman atau sertifikasi.</p>'),
  ],
  locations: [
    content(32, 'direpair_location', 'demo-workshop', '[Demo] Workshop Direpair', 'Lokasi contoh untuk staging. Ganti dengan alamat nyata sebelum local SEO diaktifkan.', {
      city: 'Jakarta', region: 'DKI Jakarta', service_methods: ['drop-off', 'pickup'],
    }, '<p>Alamat demo tidak ditampilkan karena bukan lokasi bisnis nyata.</p>'),
  ],
  repairCases: [
    content(33, 'direpair_case', 'demo-repair-tws-tidak-mengisi', '[Demo] Repair TWS Tidak Mengisi', 'Kasus fixture untuk menguji layout, bukan bukti repair nyata.', {
      device_brand: 'Demo Brand', device_model: 'Demo Model', reported_symptom: 'Tidak mengisi', diagnosis_summary: 'Fixture diagnosis untuk pengujian layout.', repair_action: 'Fixture repair action.', repair_outcome: 'Demo only', publish_consent: false,
    }, '<p>Ganti dengan kasus nyata yang telah mendapat izin publikasi.</p>'),
  ],
  warranties: [
    content(34, 'direpair_warranty', 'demo-garansi-service', '[Demo] Kebijakan Garansi Service', 'Draft contoh yang harus ditinjau bisnis/legal sebelum launch.', {
      warranty_duration_days: 30, warranty_summary: 'Contoh garansi 30 hari untuk pekerjaan yang tercantum pada invoice.', warranty_exclusions: ['Kerusakan fisik baru', 'Kerusakan cairan baru', 'Modifikasi pihak ketiga'],
    }, '<p>Garansi final mengikuti jenis pekerjaan, komponen, dan syarat yang tercatat pada quotation atau invoice.</p>'),
  ],
  policies: [
    content(35, 'direpair_policy', 'privacy', '[Demo] Kebijakan Privasi', 'Draft struktur privasi untuk ditinjau dan diganti klien sebelum pengumpulan data production.', {}, '<h2>Data yang dikumpulkan</h2><p>Booking memerlukan data kontak, detail perangkat, keluhan, consent, dan lampiran opsional untuk menjalankan layanan repair.</p><h2>Penggunaan dan penyimpanan</h2><p>Data operasional disimpan di sistem Laravel, bukan WordPress. Isi retention, hak subjek data, dasar pemrosesan, vendor, dan kanal permintaan sesuai kebijakan final.</p>'),
    content(36, 'direpair_policy', 'terms', '[Demo] Syarat Layanan', 'Draft struktur syarat layanan untuk ditinjau bisnis/legal sebelum launch.', {}, '<h2>Diagnosis dan quotation</h2><p>Booking bukan persetujuan repair. Pekerjaan mengikuti diagnosis, quotation aktif, dan keputusan pelanggan.</p><h2>Pembayaran dan garansi</h2><p>Isi aturan diagnosis fee, deposit, pelunasan, pembatalan, penyimpanan unit, serah terima, dan garansi sesuai kebijakan final Direpair.</p>'),
  ],
};
