# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

- Pelanggan Indonesia yang memiliki perangkat elektronik non-mainstream dan belum mengetahui penyebab kerusakan atau kelayakan repair.
- Staf operasional dan teknisi Direpair yang memproses request, diagnosis, quotation, payment, timeline, dan warranty.
- Editor konten dari pihak klien yang mengelola layanan, kisaran harga, foto, teknisi, lokasi, FAQ, garansi, kasus repair, dan kebijakan publik.

## Product Purpose

Direpair membuat proses repair yang biasanya tidak pasti menjadi dapat dipahami dan dikendalikan. Pelanggan menceritakan gejala, memilih metode layanan, menerima diagnosis dan quotation, lalu mengambil keputusan sebelum pekerjaan dan pembayaran penuh dilakukan. Keberhasilan berarti pelanggan mengetahui langkah berikutnya tanpa merasa ditekan, sementara staf dan editor dapat bekerja tanpa mencampur data publik dengan data pelanggan sensitif.

## Positioning

Direpair bukan katalog harga atau form lead yang berhenti di WhatsApp. Mekanisme utamanya adalah alur repair terlacak dari gejala hingga keputusan: diagnosis lebih dulu, quotation terstruktur dan versioned, persetujuan eksplisit, pembayaran terverifikasi, serta halaman status privat.

## Operating Context

- Pelanggan mengakses website dari pencarian lokal, rekomendasi, dan perangkat mobile; sebagian datang dengan urgensi dan pengetahuan teknis terbatas.
- Booking publik mengirim data ke Laravel, bukan WordPress.
- WordPress menjadi control plane untuk konten publik yang dapat diganti klien.
- Laravel menjadi sistem operasional privat untuk pelanggan, request, diagnosis, quotation, invoice, payment, timeline, dan audit.
- Astro menjadi website publik SEO-first dan antarmuka booking/status pelanggan.
- Bahasa utama adalah Bahasa Indonesia; nilai harga menggunakan Rupiah dan harga final baru sah setelah diagnosis.

## Capabilities and Constraints

- Pertahankan seluruh workflow, state machine, API contract, privacy boundary, dan production guard yang telah diimplementasikan.
- Foto, profil teknisi, lokasi, harga publik, FAQ, garansi, kebijakan, dan repair case tetap dinamis dari WordPress.
- Dummy content harus selalu terlihat sebagai demo dan tidak boleh berubah menjadi klaim bisnis nyata.
- WordPress boleh mendapat reskin menyeluruh melalui plugin Direpair, tetapi affordance inti, kompatibilitas plugin, kemampuan update, dan aksesibilitas admin tidak boleh dirusak.
- Frontend harus tetap ringan, SEO-first, responsif mulai lebar 320px, serta cocok untuk Cloudflare Workers.
- Perubahan visual tidak boleh memperluas scope bisnis, menambah klaim, atau mengubah timing pembayaran tanpa keputusan produk baru.

## Brand Commitments

- Nama produk tetap **Direpair**.
- Kepribadian: kompeten, jujur, presisi, manusiawi, dan tidak mengintimidasi pengguna nonteknis.
- Voice berbahasa Indonesia yang langsung, tenang, dan transparan; hindari hype, jargon teknis tanpa penjelasan, serta urgency palsu.
- Identitas orange–teal–cream, tipografi, wordmark treatment, dan component language lama tidak mengikat; pengguna mengizinkan replacement visual identity secara total.

## Evidence on Hand

- Workflow dan sistem operasional yang berjalan menjadi bukti utama: booking, secure status, diagnosis, quotation, payment abstraction, timeline, dan admin operations.
- CMS lokal memiliki fixture berlabel demo untuk layanan, masalah, lokasi, teknisi, repair case, FAQ, warranty, dan kebijakan.
- Belum tersedia foto teknisi asli, dokumentasi workshop, logo final, testimonial terverifikasi, rating, alamat final, atau repair case pelanggan berizin. Redesign tidak boleh memalsukannya dan harus menyediakan replacement slots yang jelas.
- Blueprint requirement asli berada di `Blueprint Website Direpair_ Lightweight, SEO-First, Local GEO & AI-Ready untuk Repair Produk Non-Mai.pdf`.

## Product Principles

1. Diagnosis sebelum keputusan biaya.
2. Kejelasan status sebelum kecepatan visual.
3. Konten publik dapat diedit; data pelanggan tetap privat.
4. Tunjukkan bukti yang nyata, labeli demo, jangan mengarang kepercayaan.
5. Satu sistem merek, tetapi ekspresi mengikuti pekerjaan tiap surface.

## Accessibility & Inclusion

Antarmuka harus tetap dapat digunakan dengan keyboard, memiliki focus state yang terlihat, target sentuh yang layak, kontras yang aman, struktur heading dan label yang benar, serta menghormati `prefers-reduced-motion`. Bahasa dan instruksi harus dapat dipahami pengguna nonteknis.
