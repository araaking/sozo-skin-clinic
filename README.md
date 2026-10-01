# 📝 Sozo Skin Clinic - Schema Markup Migration Project

> **Versi:** 1.12 | **Tanggal Update:** 1 Oktober 2026
> **Fokus Utama:** Schema markup (custom JSON-LD via `@graph`) + dokumentasi UI & SEO lengkap untuk `sozoskinclinic.com`

Proyek ini bertujuan untuk mengontrol penuh struktur schema markup di `sozoskinclinic.com`, mengeliminasi error bawaan Yoast, dan membangun *Knowledge Graph* terpusat per tipe halaman untuk memaksimalkan SEO[cite: 1]. Seluruh schema Yoast telah dimatikan secara global via WPCode[cite: 1].

Sejak 13 Juli 2026, scope berkembang: dokumentasi melingkupi tidak hanya schema JSON-LD, tapi juga UI components (button, table, card, accordion, dll) dan SEO on-page per LP. Folder `docs/adr/` dan `docs/components/` adalah ekstensi modular dari tiga dokumen utama ini.

---

## 📚 Struktur Dokumentasi

Tiga dokumen utama + dua folder ekstensi:

| Dokumen | Fokus | Update terakhir |
| :-- | :-- | :-- |
| **README.md** (file ini) | Status per-LP, SOP schema, integrasi WPCode, struktur docs | 1 Oktober 2026 |
| **AGENTS.md** | Schema generation rules + cara AI bekerja di project | 1 Oktober 2026 |
| **Dokumentasi.md** | Log implementasi §5.x, troubleshooting, sisa pekerjaan, template per tipe halaman | 1 Oktober 2026 |
| `docs/adr/` | Architecture Decision Records (saat ini 8 ADR) | 22 September 2026 |
| `docs/components/` | UI component specs (saat ini 5: button, table, card, accordion, whatsapp-button) | 13 Juli 2026 |

**Audience:** Developer yang maintain LP + designer. Schema JSON-LD adalah salah satu aspek; UI components adalah aspek lain yang sekarang terdokumentasi. Untuk komponen UI, distandardisasi di level atom (button, input, badge) — bukan di level section (hero, info-row) yang memang bervariasi antar LP.

---

## 🤖 SOP Pembuatan Schema Baru (Panduan untuk AI / Tim Dev)

Jika Anda menugaskan AI atau *Developer* untuk membuat *schema* halaman baru, mereka **WAJIB** mengikuti panduan berikut:

### 📋 1. Kebutuhan Data (Input untuk AI)
Berikan data berikut ke AI sebelum meminta kode:
1. **URL Lengkap Halaman** (Ambil dari daftar di bawah).
2. **Nama breadcrumb** yang tampil di UI (level Home otomatis).
3. **Title & Meta Description dari Yoast** — untuk versi dinamis schema membaca langsung `_yoast_wpseo_title` dan `_yoast_wpseo_metadesc`, tidak perlu hardcode.

### 💬 2. Template Prompt untuk AI
> Buatkan custom schema JSON-LD dengan struktur `@graph` untuk halaman sozoskinclinic.com:
> 1. Gunakan node `WebPage` + `BreadcrumbList` saja. Jangan tambah `Service`, `FAQPage`, `MedicalWebPage`, atau `CreativeWork`.
> 2. `url` dan `@id` pakai `[URL]/#webpage`; breadcrumb pakai `[URL]/#breadcrumb`.
> 3. Pointer `isPartOf` → `https://sozoskinclinic.com/#website` dan `about` → `https://sozoskinclinic.com/#organization`.
> 4. Versi dinamis (PHP/WPCode): title & description ambil dari Yoast, jangan hardcode.
> 5. Item terakhir breadcrumb boleh tanpa `item`; level pertama pakai `Beranda`.
>
> Data Halaman:
> - URL: [Masukkan URL dari daftar]
> - Nama breadcrumb: [Masukkan nama halaman]

---

## 📊 Progress Tracking & Master URL Checklist

*Gunakan checklist di bawah ini untuk memantau progress pengerjaan di GitHub.*

### 🏠 1. Core & Static Pages
- [x] **Homepage** (`https://sozoskinclinic.com/`) - *Keyword: klinik kecantikan terdekat*[cite: 1]
- [x] **Promo Page** (`https://sozoskinclinic.com/promo/`) — *WebPage + BreadcrumbList; UI/CTA promo diperbarui di `promo/index.php`, schema memakai guard `is_page('promo')`, 1 Oktober 2026*
- [ ] **Testimoni** (`https://sozoskinclinic.com/testimoni/`)
- [x] **Tentang Kami** (`https://sozoskinclinic.com/tentang-kami/`) — *WebPage + BreadcrumbList; `about-us-schema-markup.php` (dinamis) + `about-us-schema-markup.html` (statis) + blok inline `about-us.html`, 30 September 2026*
- [x] **Editorial Board** (`https://sozoskinclinic.com/editorial-board/`) — *WebPage + BreadcrumbList; UI Design System V1; `other/editorial-board.html`, 30 September 2026*
- [x] **Kebijakan Privasi** (`https://sozoskinclinic.com/kebijakan-privasi/`) — *WebPage + BreadcrumbList; `privacy-policy.html` + `other/privacy.html`, UI Design System V1, 30 September 2026*
- [x] **Artikel Blog (single post)** (`/blog/[slug]/`) — *dynamic via `blog/singlepost.php`: MedicalWebPage + BlogPosting (`articleBody`) + reviewer; snippet WPCode lama deprecated, 21 September 2026*
- [ ] **Blog archive** (`https://sozoskinclinic.com/blog/`) — *halaman index, belum ada schema*

### 💇‍♀️ 2. Hair Removal Treatment (Category Hub & Subs)
- [x] **Main Hub: Hair Removal Treatment** (`https://sozoskinclinic.com/hair-removal-treatment/`)[cite: 1]
- [x] Laser Hair Removal (`https://sozoskinclinic.com/hair-removal-treatment/laser-hair-removal-treatment/`)
- [x] Brazilian Hair Removal (`https://sozoskinclinic.com/hair-removal-treatment/brazilian-hair-removal-treatment/`)
- [x] Body Hair Removal (`https://sozoskinclinic.com/hair-removal-treatment/hair-removal-body-treatment/`)
- [x] Underarm Hair Removal (`https://sozoskinclinic.com/hair-removal-treatment/underarm-hair-removal-treatment/`)
- [x] Underarm Brightening (`https://sozoskinclinic.com/hair-removal-treatment/underarm-brightening-treatment/`)
- [ ] DPL Treatment (`https://sozoskinclinic.com/hair-removal-treatment/dpl-treatment/`) — *sub-treatment baru, schema belum dibuat*

### 🦱 3. Hair Treatment (Category Hub & Subs)
- [x] **Main Hub: Hair Treatment** (`https://sozoskinclinic.com/hair-treatment/`)[cite: 1]
- [ ] Perawatan Rambut Rontok (`https://sozoskinclinic.com/hair-treatment/perawatan-rambut-rontok/`)
- [x] Hair Growth Booster (`https://sozoskinclinic.com/hair-treatment/hair-grow-booster-treatment/`)
- [x] PRP Hair Treatment (`https://sozoskinclinic.com/hair-treatment/prp-hair-treatment/`)
- [x] Exosome Hair Treatment (`https://sozoskinclinic.com/hair-treatment/exosome-hair-treatment/`)
- [x] Biolight Hair (`https://sozoskinclinic.com/hair-treatment/biolight-hair-treatment/`)
- [x] Beard Treatment (`https://sozoskinclinic.com/hair-treatment/beard-grow-treatment/`)
- [x] Treatment Alis - Brow Grow (`https://sozoskinclinic.com/hair-treatment/brow-grow/`)
- [x] Salmon DNA Hair Treatment (`https://sozoskinclinic.com/hair-treatment/salmon-dna-hair-treatment/`)
- [x] Express Hair Therapy (`https://sozoskinclinic.com/hair-treatment/express-hair-therapy/`)
- [ ] Treatment Alis - Brow Grow (varian URL) (`https://sozoskinclinic.com/hair-treatment/treatment-alis-brow-grow/`) — *alias URL, canonical `brow-grow`*

### 💃 4. Slimming, RF, & Meso Treatment (Shape & Contouring)
- [x] **Main Hub: Slimming Treatment** (`https://sozoskinclinic.com/slimming-treatment/`)[cite: 1]
- [ ] Caloburn Treatment (`https://sozoskinclinic.com/caloburn-treatment/`)
- [ ] UltraSculpt Treatment (`https://sozoskinclinic.com/ultrasculpt-treatment/`) — *URL live benar `ultrasculpt`, README lama pakai typo `ultrascrupt`*
- [x] **Meso Treatment Hub** (`https://sozoskinclinic.com/meso-treatment/`)
    - [x] Meso Slim Body (`https://sozoskinclinic.com/meso-treatment/meso-slim-body/`) — Rp 1.499.000
    - [x] Meso V Line (`https://sozoskinclinic.com/meso-treatment/meso-v-line/`) — Rp 299.000
    - [x] Meso Bloatway (`https://sozoskinclinic.com/meso-treatment/meso-bloataway/`) — Rp 949.000
    - [x] Meso Cellulift (`https://sozoskinclinic.com/meso-treatment/meso-cellulift/`) — Rp 1.299.000
    - [x] Meso Metabolic Boost (`https://sozoskinclinic.com/meso-treatment/meso-metabolic-boost/`) — Rp 1.499.000
- [x] **RF Treatment Hub** (`https://sozoskinclinic.com/radiofrequency-treatment/`)
    - [x] RF Face / Wajah (`https://sozoskinclinic.com/radiofrequency-treatment/rf-face/`) — Rp 299.000
    - [x] RF Body (`https://sozoskinclinic.com/radiofrequency-treatment/rf-body/`) — Rp 299.000

### 💉 5. Injectable & Anti-Aging Treatment
- [x] **Main Hub: Injectable Treatment** (`https://sozoskinclinic.com/injectable-treatment/`)
- [x] **Botox:** Zo-Tox Treatment (`https://sozoskinclinic.com/injectable-treatment/zo-tox-treatment/`) — *revamp UI + schema siap (2026-08-05), WPCode belum dipasang; CSS/JS dipisah ke `style-js.html` (3 widget: style-js + markup + schema)*
    - [ ] Zo-Tox 10U (`https://sozoskinclinic.com/injectable-treatment/zo-tox-treatment/zo-tox-10u/`) — *sub-treatment baru*
    - [ ] Zo-Tox Premium (`https://sozoskinclinic.com/injectable-treatment/zo-tox-treatment/zo-tox-premium/`) — *sub-treatment baru*
- [x] **Threadlift:** Tanam Benang Hub (`https://sozoskinclinic.com/injectable-treatment/threadlift-treatment/`)
    - [x] Tanam Benang Hidung (`https://sozoskinclinic.com/injectable-treatment/threadlift-treatment/tanam-benang-hidung/`) — Rp 2.499.000
    - [ ] Facelift (`https://sozoskinclinic.com/injectable-treatment/threadlift-treatment/facelift/`) — *sub-treatment baru, schema belum dibuat*
    - [ ] Perfect Facelift (`https://sozoskinclinic.com/injectable-treatment/threadlift-treatment/perfect-facelift/`) — *sub-treatment baru, schema belum dibuat*
    - [ ] Perfect Nose Job (`https://sozoskinclinic.com/injectable-treatment/threadlift-treatment/perfect-nose-job/`) — *sub-treatment baru, schema belum dibuat*
- [x] **Infus Whitening Hub** (`https://sozoskinclinic.com/injectable-treatment/infus-whitening-treatment/`)
    - [x] Infus Vitamin C Immune Glow (`https://sozoskinclinic.com/injectable-treatment/infus-whitening-treatment/infus-vitamin-c-immune-glow-injection/`) — Rp 1.099.000
    - [ ] Premium Glow Infusion (`https://sozoskinclinic.com/injectable-treatment/infus-whitening-treatment/premium-glow-infusion/`) — *sub-treatment baru, schema belum dibuat*
- [x] **Filler Treatment Hub** (`https://sozoskinclinic.com/injectable-treatment/filler-treatment/`)
    - [x] Filler Dagu (`https://sozoskinclinic.com/injectable-treatment/filler-treatment/filler-dagu/`) — Rp 5.499.000
    - [x] Korean Filler (`https://sozoskinclinic.com/injectable-treatment/filler-treatment/korean-filler/`) — Rp 2.999.000
    - [ ] Premium Filler (`https://sozoskinclinic.com/injectable-treatment/filler-treatment/premium-filler/`) — *sub-treatment baru, schema belum dibuat*
- [x] **Skin Booster Hub** (`https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/`)
    - [x] Aquashine Treatment (`https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/aquashine-treatment/`) — Rp 799.000
    - [x] Skin Booster DNA Salmon (`https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/dna-glow/`) — Rp 1.749.000
    - [x] Exosome Skin Booster (`https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/exosome-skin-booster/`) — Rp 4.899.000
    - [x] Profhilo (`https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/profhilo/`) — Rp 6.999.000
    - [x] Jalupro (`https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/jalupro-treatment/`) — Rp 8.598.000
    - [x] Juvelook (`https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/juvelook/`) — Rp 5.599.000
    - [x] Nucleofill (`https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/nucleofil-treatment/`) — Rp 6.499.000
    - [x] Rejuran Healer (`https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/rejuran-healer/`) — Rp 3.999.000
    - [x] Rejuran HB (`https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/rejuran-hb-treatment/`) — Rp 4.099.000
    - [x] Rejuran Eye (`https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/rejuran-eye/`) — Rp 3.099.000
    - [x] Rejuran Skin Booster (`https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/rejuran-skin-booster/`) — Rp 2.999.000 (schema siap, WPCode belum dipasang)
    - [x] Restylane Skinbooster (`https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/restylane-skinbooster/`) — Rp 3.999.000
    - [x] Xela Rederm (`https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/xela-rederm-treatment/`) — Rp 4.499.000
    - [x] Glass Skin Booster (`https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/glass-skin-booster/`) — Rp 2.199.000
    - [x] Treatment Mata Panda (`https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/eye-booster/`) — Rp 1.799.000
    - [x] Pink Bomb Booster (`https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/pink-bomb-booster/`) — Rp 2.999.000
    - [x] Pink Lips Booster (`https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/pink-lips-booster/`) — Rp 1.799.000

### 🧪 6. Advanced Skin & Facial Treatment
- [ ] **Main Hub: Beauty Treatment** (`https://sozoskinclinic.com/treatment/`)
- [x] **Skin Treatment Hub** (`https://sozoskinclinic.com/skin-treatment/`)
- [x] **HIFU Treatment Hub** (`https://sozoskinclinic.com/hifu-treatment/`)
    - [x] Liftera HIFU (`https://sozoskinclinic.com/hifu-treatment/liftera-hifu/`) — Rp 699.000
    - [ ] Signature HIFU (`https://sozoskinclinic.com/hifu-treatment/signature-hifu/`) — *sub-treatment baru, schema belum dibuat*
- [x] **Facial Treatment Hub** (`https://sozoskinclinic.com/skin-treatment/facial-treatment/`)
    - [x] Signature Facial (`https://sozoskinclinic.com/skin-treatment/facial-treatment/signature-facial/`)
    - [x] Mini Facial (`https://sozoskinclinic.com/skin-treatment/facial-treatment/mini-facial-treatment/`)
    - [x] Acne Facial (`https://sozoskinclinic.com/skin-treatment/facial-treatment/acne-clear-facial/`)
    - [x] Acne Laser Facial (`https://sozoskinclinic.com/skin-treatment/facial-treatment/acne-laser-facial/`)
    - [x] Brightening Facial (`https://sozoskinclinic.com/skin-treatment/facial-treatment/brightening-facial/`)
    - [x] Diamond Laser Facial (`https://sozoskinclinic.com/skin-treatment/facial-treatment/diamond-laser-facial/`)
    - [x] Collagen Mask (`https://sozoskinclinic.com/skin-treatment/facial-treatment/collagen-mask/`) — Rp 199.000 (schema siap, WPCode belum dipasang)
    - [x] Sylfirm X (`https://sozoskinclinic.com/skin-treatment/facial-treatment/sylfirm-x/`)
- [x] **Acne Treatment (Non-Facial)** (`https://sozoskinclinic.com/skin-treatment/acne-treatment/`)
    - [x] Pore Detox (`https://sozoskinclinic.com/skin-treatment/acne-treatment/pore-detox/`) — Rp 799.000 (schema siap, WPCode belum dipasang)
- [x] **IPL Treatment Hub** (`https://sozoskinclinic.com/skin-treatment/ipl-treatment/`)
    - [x] IPL Acne (`https://sozoskinclinic.com/skin-treatment/ipl-treatment/ipl-acne/`) — Rp 399.000
    - [x] IPL Glow (`https://sozoskinclinic.com/skin-treatment/ipl-treatment/ipl-glow/`) — Rp 399.000
- [x] **Derma Peel Hub** (`https://sozoskinclinic.com/skin-treatment/derma-peel-treatment/`)
    - [x] Acne Peel (`https://sozoskinclinic.com/skin-treatment/derma-peel-treatment/acne-peel/`) — Rp 299.000
    - [x] Glow Peel (`https://sozoskinclinic.com/skin-treatment/derma-peel-treatment/glow-peel/`) — Rp 299.000
    - [x] Dazzling Glow Peel (`https://sozoskinclinic.com/skin-treatment/derma-peel-treatment/dazling-glow-peel/`) — Rp 889.000
    - [x] Eternal Bloom Peel (`https://sozoskinclinic.com/skin-treatment/derma-peel-treatment/eternal-bloom-peel/`) — Rp 599.000
    - [x] Korean LHALA Peel (`https://sozoskinclinic.com/skin-treatment/derma-peel-treatment/korean-lhala-peel/`) — Rp 299.000
- [x] **Laser Treatment Hub (Laser Wajah)** (`https://sozoskinclinic.com/skin-treatment/laser-treatment/`)
    - [x] Laser CO2 (`https://sozoskinclinic.com/skin-treatment/laser-treatment/laser-co2-treatment/`)
    - [x] Laser Rejuve (`https://sozoskinclinic.com/skin-treatment/laser-treatment/laser-rejuve-treatment/`)
    - [x] Nano Laser (`https://sozoskinclinic.com/skin-treatment/laser-treatment/nano-laser-treatment/`)
    - [x] Pico Laser (`https://sozoskinclinic.com/skin-treatment/laser-treatment/pico-laser-treatment/`)
    - [x] Pink Lips Laser (`https://sozoskinclinic.com/skin-treatment/laser-treatment/pink-lips-laser-treatment/`)
    - [x] Laser Tattoo Removal (`https://sozoskinclinic.com/skin-treatment/laser-treatment/laser-tattoo-removal-treatment/`)
- [x] **Scar & Pores Hub** (`https://sozoskinclinic.com/skin-treatment/scar-treatment/`)
    - [ ] Laser CO2 Scar (`https://sozoskinclinic.com/skin-treatment/scar-treatment/laser-co2-treatment/`)
    - [x] Pores Treatment (`https://sozoskinclinic.com/skin-treatment/scar-treatment/pores-treatment/`)
    - [x] PRP Treatment Scar (`https://sozoskinclinic.com/skin-treatment/scar-treatment/prp-treatment/`)
    - [x] Rejuran Scar (`https://sozoskinclinic.com/skin-treatment/scar-treatment/rejuran-scar-treatment/`)
    - [x] Restylane Scar (`https://sozoskinclinic.com/skin-treatment/scar-treatment/restylane-scar/`)
    - [x] Subcision (`https://sozoskinclinic.com/skin-treatment/scar-treatment/subcision-treatment/`)

### 🛒 7. Product Pages (Skincare E-Commerce Schema)
*Catatan: Gunakan tipe schema `Product` khusus untuk rumpun URL ini.*
- [ ] **Product Catalog Hub** (`https://sozoskinclinic.com/product/`)
- [ ] Sunscreen (`https://sozoskinclinic.com/product/sunscreen/`)
- [ ] Acne Skincare (`https://sozoskinclinic.com/product/obat-cream-acne-jerawat/`)
- [ ] Serum Pencerah Wajah (`https://sozoskinclinic.com/product/serum-pencerah-wajah/`)
- [ ] Suplemen Diet (`https://sozoskinclinic.com/product/suplement-obat-diet-slimming/`)
- [ ] Pembersih Wajah (`https://sozoskinclinic.com/product/pembersih-wajah-toner/`)
- [ ] Day Cream (`https://sozoskinclinic.com/product/pelembap-wajah/`)
- [ ] Moisturizer (`https://sozoskinclinic.com/product/moisturizer-luminous-silk/`)
- [ ] Suplemen Pelangsing (`https://sozoskinclinic.com/suplemen-pelangsing/`) — *landing page suplemen, di luar rumpun `/product/*`*

### 📍 8. Local Business / Medical Clinic Pages (Cabang Lokasi)
*Catatan: Gunakan kombinasi `MedicalClinic` + `LocalBusiness` per cabang lokasi.*
- [ ] **Main Hub Lokasi** (`https://sozoskinclinic.com/lokasi/`)
- [ ] Klinik Kecantikan Bandung (`https://sozoskinclinic.com/lokasi/bandung/`)
- [ ] Klinik Kecantikan Bekasi (`https://sozoskinclinic.com/lokasi/bekasi/`)
- [ ] Klinik Kecantikan Bogor (`https://sozoskinclinic.com/lokasi/bogor/`)
- [ ] Klinik Kecantikan Depok (`https://sozoskinclinic.com/lokasi/depok/`)
- [ ] Klinik Kecantikan Jogja (`https://sozoskinclinic.com/lokasi/jogja/`)
- [ ] Klinik Kecantikan Makassar (`https://sozoskinclinic.com/lokasi/makassar/`)
- [ ] Klinik Kecantikan Malang (`https://sozoskinclinic.com/lokasi/malang/`)
- [ ] Klinik Kecantikan Medan (`https://sozoskinclinic.com/lokasi/medan/`)
- [ ] Klinik Kecantikan Semarang (`https://sozoskinclinic.com/lokasi/semarang/`)
- [ ] Klinik Kecantikan Surabaya (`https://sozoskinclinic.com/lokasi/surabaya/`)
- [ ] Klinik Kecantikan Tangerang (`https://sozoskinclinic.com/lokasi/tangerang/`)
    - [ ] Karawaci (`https://sozoskinclinic.com/lokasi/tangerang/karawaci/`)
    - [ ] Greenlake (`https://sozoskinclinic.com/lokasi/tangerang/greenlake/`)
    - [ ] Gading Serpong (`https://sozoskinclinic.com/lokasi/tangerang/gading-serpong/`)
- [ ] Klinik Kecantikan Cirebon (`https://sozoskinclinic.com/lokasi/cirebon/`)
- [ ] Klinik Kecantikan Solo (`https://sozoskinclinic.com/lokasi/solo/`)
- [ ] Klinik Kecantikan Balikpapan (`https://sozoskinclinic.com/lokasi/balikpapan/`)
- [ ] Klinik Kecantikan Cikarang (`https://sozoskinclinic.com/lokasi/cikarang/`)
- [ ] Klinik Kecantikan Palembang (`https://sozoskinclinic.com/lokasi/palembang/`)
- [ ] Klinik Kecantikan Pekanbaru (`https://sozoskinclinic.com/lokasi/pekanbaru/`)
- [ ] Klinik Kecantikan Manado (`https://sozoskinclinic.com/lokasi/manado/`)
- [ ] Klinik Kecantikan Batam (`https://sozoskinclinic.com/lokasi/batam/`)
- [ ] **Kluster Jabodetabek Tambahan:**
    - [ ] Klinik Kecantikan Jakarta (`https://sozoskinclinic.com/lokasi/jakarta/`)
    - [ ] Jakarta Selatan (`https://sozoskinclinic.com/lokasi/jakarta/jakarta-selatan/`)
    - [ ] Tebet (`https://sozoskinclinic.com/lokasi/jakarta/jakarta-selatan/tebet/`)
    - [ ] Pondok Indah (`https://sozoskinclinic.com/lokasi/jakarta/jakarta-selatan/pondok-indah/`)
    - [ ] Jakarta Barat (`https://sozoskinclinic.com/lokasi/jakarta/jakarta-barat/`)
    - [ ] Tanjung Duren (`https://sozoskinclinic.com/lokasi/jakarta/jakarta-barat/tanjung-duren/`)
    - [ ] Mangga Besar (`https://sozoskinclinic.com/lokasi/jakarta/jakarta-barat/mangga-besar/`)
    - [ ] Puri Indah (`https://sozoskinclinic.com/lokasi/jakarta/jakarta-barat/puri-indah/`)
    - [ ] Jakarta Timur (`https://sozoskinclinic.com/lokasi/jakarta/jakarta-timur/`)
    - [ ] Rawamangun (`https://sozoskinclinic.com/lokasi/jakarta/jakarta-timur/rawamangun/`)
    - [ ] JGC (`https://sozoskinclinic.com/lokasi/jakarta/jakarta-timur/jgc/`)
    - [ ] Jakarta Pusat (`https://sozoskinclinic.com/lokasi/jakarta/jakarta-pusat/`)
    - [ ] Jakarta Utara (`https://sozoskinclinic.com/lokasi/jakarta/jakarta-utara/`)
    - [ ] Kelapa Gading (`https://sozoskinclinic.com/lokasi/jakarta/jakarta-utara/kelapa-gading/`)
    - [ ] PIK (`https://sozoskinclinic.com/lokasi/jakarta/jakarta-utara/pik/`)
    - [ ] Tangerang Selatan (`https://sozoskinclinic.com/lokasi/tangerang-selatan/`)
    - [ ] Bintaro (`https://sozoskinclinic.com/lokasi/tangerang-selatan/bintaro/`)
    - [ ] BSD (`https://sozoskinclinic.com/lokasi/tangerang-selatan/bsd/`)
    - [ ] Bali (`https://sozoskinclinic.com/lokasi/bali/`) — *cabang di luar Jawa*

### 🩺 9. Halaman Tim Dokter (List & Detail Profil)
*Catatan: pola schema dokter berbeda dari treatment. List page pakai `CollectionPage` + `ItemList`, detail page pakai `ProfilePage` + `IndividualPhysician` (array `["Person", "IndividualPhysician"]`). Lihat AGENTS.md §6 dan §5.*

- [x] **List Tim Dokter** (`https://sozoskinclinic.com/tim-dokter-sozo-skin/`) — *CollectionPage + ItemList (5 Person), terpasang WPCode, valid 0 error*
- [ ] Detail dr. Elisabeth Ryan (`https://sozoskinclinic.com/tim-dokter-sozo-skin/elisabeth-ryan/`) — *schema siap (`dokter/dr-eli/schema-markup.html`), WPCode belum dipasang; STR penuh masuk `hasCredential`, organisasi masuk `memberOf`*
- [ ] Detail dr. Gesha Kautzar Putri (`https://sozoskinclinic.com/tim-dokter-sozo-skin/gesha-kautzar-putri/`) — *schema siap (`dokter/dr-gesha/schema-markup.html`), WPCode belum dipasang; STR penuh masuk `hasCredential`, organisasi masuk `memberOf`*
- [ ] Detail dr. Audi Sugiharto (`https://sozoskinclinic.com/tim-dokter-sozo-skin/audi-sugiharto/`) — *schema siap (`dokter/dr-audi/schema-markup.html`), WPCode belum dipasang; STR penuh masuk `hasCredential`, 3 pendidikan masuk `alumniOf`*
- [ ] Detail dr. RR. Putri Rizkya (`https://sozoskinclinic.com/tim-dokter-sozo-skin/dr-rr-putri-rizkya/`) — *schema siap (`dokter/dr-putri/schema-markup.html`), WPCode belum dipasang; STR tidak masuk schema (disensor di UI)*
- [ ] Detail dr. Syerli Rahmadeni (`https://sozoskinclinic.com/tim-dokter-sozo-skin/dr-syerli-rahmadeni/`) — *schema siap (`dokter/dr-sherly/schema-markup.html`), WPCode belum dipasang; STR tampil penuh di UI, masuk `hasCredential` + recognizedBy KKI*

---

## 🚨 Catatan Teknis & Aturan Baku (Red Flags)

Setiap schema yang di-generate harus mematuhi aturan standar Google (update 2026):

| Kasus | Panduan Wajib Penanganan |
| :--- | :--- |
| **Fokus schema saat ini** | Default cukup `WebPage` + `BreadcrumbList`. Jangan tambah `Service`, `FAQPage`, `MedicalWebPage`, atau `CreativeWork` sampai ada keputusan baru. Pengecualian: pola arsip yang sudah terpasang (treatment/dokter/blog) dan kategori yang menunggu keputusan (lokasi, product). |
| **Title & Description** | Versi dinamis mengambil dari Yoast (`_yoast_wpseo_title`, `_yoast_wpseo_metadesc`), fallback judul page → document title → default. Jangan hardcode. |
| **Field Item Breadcrumb** | Item terakhir (halaman saat ini) boleh tanpa `item`. Item lain wajib punya `item` (URL). Nama item pakai bahasa Indonesia sesuai breadcrumb visual (level pertama `Beranda`). |
| **Skema Product** | **HANYA** gunakan skema `Product` untuk rumpun URL kategori nomor 7 (`/product/*`). Halaman treatment memakai `Service` (pola arsip), bukan Product. |

---

## 🚀 Alur Integrasi ke WordPress (WPCode)

Setelah schema `.json` selesai di-generate dan divalidasi:
1. Masuk ke Dashboard WordPress > **WPCode** > **Add New**.
2. Pilih Code Type: **HTML Snippet**[cite: 1].
3. Paste kode `<script type="application/ld+json"> ... </script>` (Letakkan di `Insert Before </head>`)[cite: 1].
4. Buka tab **Smart Conditional Logic** > Enable[cite: 1].
5. Set kondisi: **Page URL contains** > `[slug-halaman]` (Contoh: `slimming-treatment`)[cite: 1].
6. **Active** > **Update**[cite: 1].
