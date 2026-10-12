# Decisions, Pertanyaan Terbuka, Exception Log

## Keputusan V1.1 (8 Oktober 2026)

V1.1 memperjelas `Sozo-Skin-Clinic-Design-System-v1.md`. Bagian yang di V1 masih berupa rentang ("20–24px"), placeholder ("Implementation token"), atau "define consistently" kini punya satu nilai.

Sumber: **V1** = dokumen V1. **Playground** = `sozo-design-system-playground.html`. **Kode** = pola yang dipakai di file repo. **Baru** = ditetapkan di V1.1 karena belum ada sumber.

| ID | Keputusan | Sumber |
| :-- | :-- | :-- |
| DEC-01 | Satu font: Poppins 400–800 untuk semua teks | V1 |
| DEC-02 | Warna teks: primary `#171715`, secondary `#41413E`, muted `#777771` | V1 §8 |
| DEC-03 | Text muted hanya di atas White (4.50:1). Tidak boleh di Almond (3.43:1) | Baru, hasil hitung kontras |
| DEC-04 | Warning `#A66A18` (4.47:1) hanya untuk ikon dan border, bukan teks | Baru, hasil hitung kontras |
| DEC-05 | Badge merah solid + teks putih dilarang (3.87:1). Diganti `badge--alert` (`#FDE8E8` + `#B64242`, 4.65:1) | Playground + hitung kontras |
| DEC-06 | Teks badge promo `--color-text-primary` (11.09:1), bukan hex baru `#3B2E00` | Baru, supaya tidak menambah token |
| DEC-07 | Button hanya 3 variant: Primary, Secondary (Dun), Outline. Semua pill | V1 §26 |
| DEC-08 | Ukuran button: sm 40/18/13px, md 48/24/14px, lg 56/28/15px | Playground |
| DEC-09 | Hover button: warna hover + `translateY(-1px)`. Outline hover `rgba(26,32,128,.05)` | Playground |
| DEC-10 | Disabled button: `opacity: .5`, tanpa hover | Baru |
| DEC-11 | Focus ring 2px Resolution Blue, offset 3px. Di background biru: putih | Playground |
| DEC-12 | Skala tipografi fluid 375px → 1280px. Mobile: Display XL 48, Display LG 42, H1 38, H2 32, H3 28. H4/H5 tetap | Playground (nilai mobile) |
| DEC-13 | Letter-spacing label uppercase 0.12em, badge 0.02em | Playground (dibulatkan dari 0.12/0.14em) |
| DEC-14 | Breakpoint: tablet ≤ 1024px, mobile ≤ 768px, mobile kecil ≤ 480px | Kode (768px paling banyak dipakai) |
| DEC-15 | Container padding: 32px desktop/tablet, 20px mobile | V1 §18 |
| DEC-16 | Jarak section tablet: 64 / 80 / 96px (normal / besar / hero) | Baru, di antara nilai desktop dan mobile V1 |
| DEC-17 | Badge tinggi 30px | Playground (V1 menyebut 28–32px) |
| DEC-18 | Treatment Card: judul style H4 (24px), H5 di mobile; deskripsi maks 3 baris | Playground |
| DEC-19 | Promo Card: rasio gambar 3:2, copy maks 2 baris, satu CTA | Playground + Baru |
| DEC-20 | Location Card: rasio media 16:9, gap aksi 12px (playground 10px dibulatkan ke token) | Playground |
| DEC-21 | Label aksi kartu dalam bahasa Indonesia ("Lihat Treatment →") | Baru, mengikuti bahasa website dan breadcrumb |
| DEC-22 | Separator breadcrumb `/`, ukuran 13px | V1 §40 + Playground |
| DEC-23 | Shadow hanya warna netral `rgba(23,23,21,…)`. Shadow biru dilarang, kecuali `--shadow-pin` (DEC-28) | V1 §24 |
| DEC-24 | Floating WhatsApp: lingkaran 56px biru, `--shadow-lg`, jarak 24px (16px mobile) | Baru |
| DEC-25 | Maksimal satu warna background section selain White per halaman | Baru, dari V1 §7 dan §67 |
| DEC-26 | Label section default berbentuk pill: `badge--label` (Almond + Resolution Blue, 12px / 600, uppercase, tracking 0.25em, padding 18px). Eyebrow teks jadi alternatif | Permintaan user, 9 Oktober 2026, dari tampilan lama Tentang Kami |
| DEC-27 | Token `--color-surface-warm` `#F7F5F1` untuk kanvas peta/ilustrasi. Tidak dihitung sebagai background section | Permintaan user, 9 Oktober 2026 |
| DEC-28 | Token `--shadow-pin` `0 3px 8px rgba(26,32,128,.45)` untuk pin peta saja | Permintaan user, 9 Oktober 2026 |

## Pertanyaan terbuka

Setiap baris sudah punya default yang berlaku sekarang. Kalau jawabannya beda, ubah file terkait dan pindahkan barisnya ke tabel keputusan.

| # | Pertanyaan | Default yang berlaku | File terdampak |
| :-- | :-- | :-- | :-- |
| Q-01 | Breakpoint 1024/768/480 (dari kode) atau 1100/760/480 (dari playground)? | 1024 / 768 / 480 | `tokens.css`, `foundations/04-spacing-layout.md` |
| Q-02 | Label aksi kartu bahasa Indonesia atau Inggris ("View Treatment →" seperti di V1)? | Bahasa Indonesia | `components/link.md`, kartu |
| Q-03 | Nilai hex Bright Red, Dark Yellow, Light Brown sudah sesuai brand guide asli? | Pakai nilai WORKING | `tokens.css`, `foundations/02-color.md` |
| Q-04 | Nomor WhatsApp dan template pesan (termasuk kode sumber tracking) untuk URL global? | Belum ditetapkan, `{WHATSAPP_URL}` | `components/whatsapp-cta.md` |
| Q-05 | Variant Featured untuk Base Card: perlu sekarang? | Tidak dibuat sampai ada use case | `components/card-base.md` |
| Q-06 | Warning `#A66A18` digelapkan supaya lulus AA untuk teks? | Dipertahankan, dibatasi untuk ikon/border | `tokens.css` |

## Komponen ditunda

Doctor Card, Product Card, Blog Card, Testimonial Card. Ditetapkan hanya ketika konten, data, dan konteks halamannya siap. Jangan membuat API komponen spekulatif.

Pola halaman (Hero, Treatment Listing, Treatment Detail, Promo, Location Finder, Location Detail, Doctor, Product, Blog, Testimonial, Global CTA) juga di luar V1.1.

## Exception log

Pengecualian lokal yang sengaja keluar dari aturan. Tulis di sini sebelum di-merge.

| ID | Komponen / halaman | Pengecualian | Alasan | Scope | Review |
| :-- | :-- | :-- | :-- | :-- | :-- |
| EX-01 | Breadcrumb di hero Almond | Ancestor pakai `--color-text-secondary`, bukan `--color-text-muted` | Text muted di Almond 3.43:1, gagal AA. Text secondary 7.79:1 | Semua breadcrumb yang duduk di surface Almond | Kalau berulang di LP lain, pindahkan ke `components/breadcrumb.md` |
| EX-02 | Promo Card berisi banner gambar | Media 16:9, bukan 3:2 | Banner promo didesain 16:9 dengan teks di dalam gambar. Crop 3:2 memotong teksnya | Kartu promo yang isinya banner jadi, tanpa judul/copy HTML | Saat banner promo diganti format kartu penuh |
| EX-03 | Lightbox promo | `z-index: 9999999`, bukan `--z-modal` | Harus di atas header Elementor dan admin bar WordPress | Lightbox/modal yang di-inject lewat WPCode | Saat token z-index diselaraskan dengan tema Elementor |
| EX-04 | Badge masalah di hero Immune Glow, mobile | Font 11px dan tinggi 26px, bukan 12px / 30px. Satu baris, geser ke samping | Permintaan user: badge sejajar satu baris di mobile supaya above the fold lebih ringkas | `infus-vitamin-c-immune-glow/index.html` ≤ 768px | Kalau pola badge masalah dipakai di LP lain, masukkan ke `components/badge.md` |
| EX-05 | Kartu katalog di halaman `/treatment/` | Kartu memuat kategori, durasi, dan harga. Satu aksi: kartu dengan halaman detail jadi stretched link (tombol "Lihat Detail"), kartu tanpa halaman detail pakai tombol WA "Buat Jadwal". Badge "Baru" 12px / 28px, 11px / 24px di mobile | Halaman ini katalog: pengunjung membandingkan harga. 23 dari 88 treatment belum punya halaman detail, jadi kartu link-only belum bisa dipakai | `treatment/index.html`, `treatment/build-treatment-list.mjs` | Kalau pola katalog dipakai di halaman lain, jadikan variant resmi di `components/treatment-card.md` |
| EX-06 | Halaman `/treatment/` | Dua warna surface selain White: hero Resolution Blue dan blok CTA Almond | Hero biru dipertahankan atas permintaan user. Gradient lama (biru ke `#3b82f6`) diganti solid biru karena `#3b82f6` di luar palet | `treatment/index.html` | Saat hero halaman treatment didesain ulang |
| EX-07 | Halaman `/tentang-kami/` | (1) Judul kartu slider treatment pakai style H5 (20px) di desktop, bukan H4. (2) Peta cabang: pin 18px dengan area tap 44px lewat `::before`, popup radius `--radius-md`. Di ≤ 768px popup diganti panel detail di bawah peta. Warna kanvas dan glow pin sudah resmi lewat DEC-27 dan DEC-28 | (1) Kartu 4 kolom di container 1100px menyisakan ±210px untuk teks; di 24px "Hair Removal Treatment" pecah 2 baris dan tinggi kartu tidak seragam. (2) Peta di mobile cuma ±240px, popup terpotong | `about-us.html` | Kalau peta cabang dipakai di halaman lain, jadikan komponen di `components/` |

## Versioning

| Versi | Kapan dipakai |
| :-- | :-- |
| V1.x | Perbaikan tanpa mengubah bahasa desain: rapikan token, API komponen, aksesibilitas, edge case responsive |
| V2 | Ada banyak pola baru, atau scope website berubah besar |

## Changelog

| Tanggal | Versi | Perubahan |
| :-- | :-- | :-- |
| 2026-10-09 | 1.2 | Tambah `badge--label` (DEC-26), `--color-surface-warm` (DEC-27), `--shadow-pin` (DEC-28), token `--tracking-label`. DEC-23 diberi pengecualian pin peta |
| 2026-10-08 | 1.1 | Aturan V1 dipecah ke `design-system/`, semua rentang dan placeholder diberi satu nilai, `tokens.css` dibuat, tabel kontras ditambahkan |
