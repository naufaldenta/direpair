# Direpair Content

Plugin WordPress untuk konten terstruktur, form editorial, REST API publik, dan pemicu rebuild frontend Direpair. Source plugin disertakan di repository yang sama; tidak ada repository plugin terpisah yang diperlukan.

- Versi: **0.2.1**
- WordPress: **6.6+**
- PHP: **8.2+** (hosting project menggunakan PHP 8.3)
- Entry point: [direpair-content.php](direpair-content.php)

## Instalasi dan penggunaan

1. Install WordPress di `/home/direpair/cms.direpair.id` dengan URL `https://cms.direpair.id`.
2. Upload folder `direpair-content` ini ke `wp-content/plugins/direpair-content`, kemudian aktifkan melalui Plugins.
3. Atur `WP_ENVIRONMENT_TYPE` menjadi `production` di `wp-config.php` pada hosting production.
4. Isi informasi usaha melalui **Settings → Direpair Content**. Isi konten melalui menu Services, Problems, Locations, Technicians, Repair Cases, Pricing Guides, FAQs, Warranties, Knowledge Articles, dan Policies.
5. Gunakan kolom judul, ringkasan/deskripsi, gambar, kategori, dan field khusus yang disediakan. Konten dikelola melalui form, bukan menyusun layout frontend dengan Gutenberg.
6. Untuk ditampilkan pada production: publish konten, pastikan bukan demo, dan tandai sudah diverifikasi. Repair Case juga memerlukan persetujuan publikasi.

WordPress core, `wp-config.php`, database, dan `wp-content/uploads` tidak disertakan dalam source plugin. Backup secara terpisah jika memindahkan CMS. Update plugin dilakukan dengan mengganti source plugin, bukan menghapus database konten.

## REST API

Base URL production: `https://cms.direpair.id/wp-json/direpair/v1`.

Semua endpoint berikut menggunakan **GET** dan dapat dibaca tanpa kredensial. Data internal operasional/customer bukan bagian dari API ini. Implementasi ada di [src/RestApi.php](src/RestApi.php); consumer frontend ada di [cms.ts](../../web/src/lib/cms.ts).

| Endpoint | Keterangan | Digunakan dataset frontend |
| --- | --- | --- |
| `/site-settings` | Informasi usaha, kontak, tautan sosial, disclaimer | Ya |
| `/services?per_page=100` | Layanan | Ya |
| `/problems?per_page=100` | Masalah/gejala | Ya |
| `/faqs?per_page=100` | Pertanyaan dan jawaban | Ya |
| `/technicians?per_page=100` | Profil teknisi | Ya |
| `/locations?per_page=100` | Lokasi layanan | Ya |
| `/repair-cases?per_page=100` | Kasus perbaikan yang boleh dipublikasikan | Ya |
| `/warranties?per_page=100` | Garansi | Ya |
| `/policies?per_page=100` | Kebijakan | Ya |
| `/pricing-guides` | Koleksi panduan harga | Belum dimuat sebagai koleksi tersendiri |
| `/knowledge-articles` | Koleksi artikel pengetahuan | Belum dimuat sebagai koleksi tersendiri |
| `/catalog` | Ringkasan layanan dan taxonomy | Tersedia untuk integrasi tambahan |
| `/health` | Status, versi plugin, environment | Pemeriksaan layanan |

Setiap endpoint koleksi juga menyediakan `GET /{collection}/{slug}` untuk satu item, misalnya `/services/service-kamera`. Item yang tidak ditemukan/tidak dapat dipublikasikan mengembalikan HTTP 404.

Parameter koleksi: `page` mulai 1 (default 1), `per_page` antara 1–100 (default 20). Response memuat `meta.total` dan `meta.total_pages`. Consumer frontend saat ini mengambil halaman pertama dengan 100 item, belum mengiterasi pagination.

Contoh bentuk response koleksi, menggunakan data ilustrasi:

```json
{
  "data": [
    {
      "id": 123,
      "uuid": "example-content-id",
      "type": "direpair_faq",
      "slug": "berapa-lama-diagnosis",
      "title": "Berapa lama diagnosis?",
      "excerpt": "Durasi bergantung pada kondisi perangkat.",
      "content": "<p>Tim akan mengonfirmasi estimasi setelah pemeriksaan.</p>",
      "featured_image": null,
      "meta": { "is_demo": false, "is_verified": true },
      "terms": {},
      "published_at": "2026-09-01T00:00:00+00:00",
      "modified_at": "2026-09-01T00:00:00+00:00"
    }
  ],
  "meta": { "page": 1, "per_page": 100, "total": 1, "total_pages": 1 }
}
```

`meta` berisi field sesuai jenis konten; definisinya ada di [src/MetaFields.php](src/MetaFields.php). Beberapa nilai numerik/boolean dapat dikembalikan WordPress sebagai string. `featured_image` berupa `null` atau object `{id, url, width, height, alt}`. `terms` selalu object/map taxonomy ke array term `{id, slug, name}`; tanpa taxonomy nilainya `{}`. Frontend juga menoleransi `[]` kosong dari versi plugin lama.

Response `/site-settings` berbentuk `{data: {...pengaturan publik}, meta: {version, environment}}`. Konfigurasi Deploy Hook dan secret tidak termasuk pengaturan publik.

Di production, API hanya mengembalikan konten published, non-demo, dan verified. Repair Case harus memiliki `publish_consent`. Sanitasi dan aturan visibilitas diterapkan di plugin serta pemeriksaan kepercayaan konten di frontend.

## Pembaruan otomatis ke Vercel

Implementasi: [src/Publishing.php](src/Publishing.php).

1. Vercel project `direpair-web` → Settings → Git → Deploy Hooks.
2. Buat hook untuk branch **main**.
3. Salin URL hook ke **CMS → Settings → Direpair Content → Pembaruan otomatis ke Vercel**, lalu simpan. URL tersebut adalah rahasia; jangan masukkan ke Git atau pesan grup. Field HMAC opsional tidak diperlukan untuk hook Vercel langsung.
4. Pastikan WordPress cron berjalan. Pada hosting headless, gunakan cPanel Cron Jobs agar tidak bergantung pada kunjungan CMS.

Jadwal setiap menit, dengan executable PHP sesuai hosting:

```cron
* * * * * /usr/local/bin/php -q /home/direpair/cms.direpair.id/wp-cron.php >/dev/null 2>&1
```

Setelah cron cPanel terpasang, nonaktifkan pemicu cron berbasis kunjungan di `wp-config.php`:

```php
define('DISABLE_WP_CRON', true);
```

Pemicu otomatis mencakup publish/update konten terkelola yang published, unpublish/delete konten published, perubahan taxonomy terkelola, dan perubahan pengaturan publik. Penyimpanan draft biasa, penggantian source plugin, atau pengeditan file media saja tidak selalu memicu build.

Alur: perubahan tersimpan → event dijadwalkan setelah 30 detik → cron mengirim POST ke hook → Vercel build dari branch main dan membaca CMS terbaru → website berubah setelah deployment berhasil. Perubahan berdekatan digabung selama event masih menunggu, bukan satu build untuk setiap field.

**Status sukses di CMS hanya mengonfirmasi request build diterima.** Periksa status Ready di Vercel untuk memastikan konten sudah terbit. Plugin tidak melakukan polling hasil build dan tidak memiliki retry otomatis bila request hook gagal; setelah memperbaiki koneksi/hook, simpan ulang konten terkait untuk menjadwalkan ulang.

Jika perubahan belum terlihat:

1. Periksa published, verified, non-demo, serta persetujuan repair case.
2. Buka endpoint koleksi dan pastikan item sudah ada.
3. Periksa status hook di Settings → Direpair Content dan cron hosting.
4. Periksa log build Vercel serta `CMS_BASE_URL` dan `CMS_FIXTURE_FALLBACK=false`.
5. Jika CMS memakai cache, pastikan REST API tidak mengembalikan response lama.

Referensi platform: [Vercel Deploy Hooks](https://vercel.com/docs/deploy-hooks).
