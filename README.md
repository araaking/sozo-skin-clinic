# 📝 Sozo Skin Clinic - Schema Markup Migration Project

> **Versi:** 1.2 | **Tanggal Update:** 2 Juli 2026
> **Fokus Utama:** Migrasi dari Yoast SEO ke Custom Schema (JSON-LD) via `@graph`[cite: 1]

Proyek ini bertujuan untuk mengontrol penuh struktur schema markup di `sozoskinclinic.com`, mengeliminasi error bawaan Yoast, dan membangun *Knowledge Graph* terpusat per tipe halaman untuk memaksimalkan SEO[cite: 1]. Seluruh schema Yoast telah dimatikan secara global via WPCode[cite: 1].

---

## 🤖 SOP Pembuatan Schema Baru (Panduan untuk AI / Tim Dev)

Jika Anda menugaskan AI atau *Developer* untuk membuat *schema* halaman baru, mereka **WAJIB** mengikuti panduan berikut:

### 📋 1. Kebutuhan Data (Input untuk AI)
Berikan data berikut ke AI sebelum meminta kode:
1. **URL Lengkap Halaman & Keyword Utama** (Ambil dari daftar di bawah).
2. **Title & Meta Description** halaman asli.
3. **Daftar Sub-Layanan & Harga** yang tertera pada halaman tersebut (Jika tidak ada harga, hilangkan properti `priceSpecification`).
4. **Konten FAQ** asli halaman tersebut.

### 💬 2. Template Prompt untuk AI
> Buatkan custom schema JSON-LD dengan struktur `@graph` untuk halaman klinik kecantikan berdasarkan ketentuan Sozo Skin Clinic:
> 1. Gunakan node `MedicalWebPage` (specialty: Dermatology), `BreadcrumbList`, dan `Service` (berisi `hasOfferCatalog`).
> 2. Pointer `@id` untuk entitas `isPartOf` dan `about` wajib mengarah ke `https://sozoskinclinic.com/#website` dan `https://sozoskinclinic.com/#organization`.
> 3. Jika halaman tidak memiliki harga, hilangkan properti `priceSpecification`. Jangan gunakan skema Product/Offer.
> 4. Urutan breadcrumb mengikuti struktur hierarki URL di bawah ini. Semua ListItem wajib memiliki properti 'item' (URL).
> 
> Data Halaman:
> - URL: [Masukkan URL dari daftar]
> - Keyword/Name: [Masukkan Nama/Keyword dari daftar]
> - FAQ & Layanan: [Paste konten halaman di sini]

---

## 📊 Progress Tracking & Master URL Checklist

*Gunakan checklist di bawah ini untuk memantau progress pengerjaan di GitHub.*

### 🏠 1. Core & Static Pages
- [x] **Homepage** (`https://sozoskinclinic.com/`) - *Keyword: klinik kecantikan terdekat*[cite: 1]
- [ ] **Promo Page** (`https://sozoskinclinic.com/promo/`) - *Keyword: promo sozo*
- [ ] **Testimoni** (`https://sozoskinclinic.com/testimoni/`)
- [ ] **Tentang Kami** (`https://sozoskinclinic.com/tentang-kami/`)
- [ ] **Editorial Board** (`https://sozoskinclinic.com/editorial-board/`)
- [ ] **Kebijakan Privasi** (`https://sozoskinclinic.com/kebijakan-privasi/`)

### 💇‍♀️ 2. Hair Removal Treatment (Category Hub & Subs)
- [x] **Main Hub: Hair Removal Treatment** (`https://sozoskinclinic.com/hair-removal-treatment/`)[cite: 1]
- [x] Laser Hair Removal (`https://sozoskinclinic.com/hair-removal-treatment/laser-hair-removal-treatment/`)
- [x] Brazilian Hair Removal (`https://sozoskinclinic.com/hair-removal-treatment/brazilian-hair-removal-treatment/`)
- [x] Body Hair Removal (`https://sozoskinclinic.com/hair-removal-treatment/hair-removal-body-treatment/`)
- [x] Underarm Hair Removal (`https://sozoskinclinic.com/hair-removal-treatment/underarm-hair-removal-treatment/`)
- [x] Underarm Brightening (`https://sozoskinclinic.com/hair-removal-treatment/underarm-brightening-treatment/`)

### 🦱 3. Hair Treatment (Category Hub & Subs)
- [x] **Main Hub: Hair Treatment** (`https://sozoskinclinic.com/hair-treatment/`)[cite: 1]
- [ ] Perawatan Rambut Rontok (`https://sozoskinclinic.com/hair-treatment/perawatan-rambut-rontok/`)
- [x] Hair Growth Booster (`https://sozoskinclinic.com/hair-treatment/hair-grow-booster-treatment/`)
- [x] PRP Hair Treatment (`https://sozoskinclinic.com/hair-treatment/prp-hair-treatment/`)
- [x] Exosome Hair Treatment (`https://sozoskinclinic.com/hair-treatment/exosome-hair-treatment/`)
- [x] Biolight Hair (`https://sozoskinclinic.com/hair-treatment/biolight-hair-treatment/`)
- [x] Beard Treatment (`https://sozoskinclinic.com/hair-treatment/beard-grow-treatment/`)
- [x] Treatment Alis - Brow Grow (`https://sozoskinclinic.com/hair-treatment/brow-grow/`)

### 💃 4. Slimming, RF, & Meso Treatment (Shape & Contouring)
- [x] **Main Hub: Slimming Treatment** (`https://sozoskinclinic.com/slimming-treatment/`)[cite: 1]
- [ ] Caloburn Treatment (`https://sozoskinclinic.com/caloburn-treatment/`)
- [ ] UltraSculpt Treatment (`https://sozoskinclinic.com/ultrascrupt-treatment/`)
- [ ] **Meso Treatment Hub** (`https://sozoskinclinic.com/meso-treatment/`)
    - [ ] Meso Slim Body (`https://sozoskinclinic.com/meso-treatment/meso-slim-body/`)
    - [ ] Meso V Line (`https://sozoskinclinic.com/meso-treatment/meso-v-line/`)
    - [ ] Meso Bloataway (`https://sozoskinclinic.com/meso-treatment/meso-bloataway/`)
    - [ ] Meso Cellulift (`https://sozoskinclinic.com/meso-treatment/meso-cellulift/`)
    - [ ] Meso Metabolic Boost (`https://sozoskinclinic.com/meso-treatment/meso-metabolic-boost/`)
- [ ] **RF Treatment Hub** (`https://sozoskinclinic.com/radiofrequency-treatment/`)
    - [ ] RF Face / Wajah (`https://sozoskinclinic.com/radiofrequency-treatment/rf-face/`)
    - [ ] RF Body (`https://sozoskinclinic.com/radiofrequency-treatment/rf-body/`)

### 💉 5. Injectable & Anti-Aging Treatment
- [x] **Main Hub: Injectable Treatment** (`https://sozoskinclinic.com/injectable-treatment/`)
- [ ] **Botox:** Zo-Tox Treatment (`https://sozoskinclinic.com/injectable-treatment/zo-tox-treatment/`)
- [ ] **Threadlift:** Tanam Benang Hub (`https://sozoskinclinic.com/injectable-treatment/threadlift-treatment/`)
    - [ ] Tanam Benang Hidung (`https://sozoskinclinic.com/injectable-treatment/threadlift-treatment/tanam-benang-hidung/`)
- [ ] **Infus Whitening Hub** (`https://sozoskinclinic.com/injectable-treatment/infus-whitening-treatment/`)
    - [ ] Infus Vitamin C Immune Glow (`https://sozoskinclinic.com/injectable-treatment/infus-whitening-treatment/infus-vitamin-c-immune-glow-injection/`)
- [ ] **Filler Treatment Hub** (`https://sozoskinclinic.com/injectable-treatment/filler-treatment/`)
    - [ ] Filler Dagu (`https://sozoskinclinic.com/injectable-treatment/filler-treatment/filler-dagu/`)
    - [ ] Korean Filler (`https://sozoskinclinic.com/injectable-treatment/filler-treatment/korean-filler/`)
- [ ] **Skin Booster Hub** (`https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/`)
    - [ ] Skin Booster DNA Salmon (`https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/dna-glow/`)
    - [ ] Exosome Skin Booster (`https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/exosome-skin-booster/`)
    - [ ] Profhilo (`https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/profhilo/`)
    - [ ] Jalupro (`https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/jalupro-treatment/`)
    - [ ] Juvelook (`https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/juvelook/`)
    - [ ] Nucleofill (`https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/nucleofil-treatment/`)
    - [ ] Rejuran Healer (`https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/rejuran-healer/`)
    - [ ] Rejuran HB (`https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/rejuran-hb-treatment/`)
    - [ ] Rejuran Eye (`https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/rejuran-eye/`)
    - [ ] Rejuran Skin Booster (`https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/rejuran-skin-booster/`)
    - [ ] Restylane Skinbooster (`https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/restylane-skinbooster/`)
    - [ ] Xela Rederm (`https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/xela-rederm-treatment/`)
    - [ ] Glass Skin Booster (`https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/glass-skin-booster/`)
    - [ ] Treatment Mata Panda (`https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/eye-booster/`)
    - [ ] Pink Bomb Booster / Lips (`https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/pink-lips-booster/`)

### 🧪 6. Advanced Skin & Facial Treatment
- [ ] **Main Hub: Beauty Treatment** (`https://sozoskinclinic.com/treatment/`)
- [x] **Skin Treatment Hub** (`https://sozoskinclinic.com/skin-treatment/`)
- [ ] **HIFU Treatment Hub** (`https://sozoskinclinic.com/hifu-treatment/`)
    - [ ] Liftera HIFU (`https://sozoskinclinic.com/hifu-treatment/liftera-hifu/`)
- [x] **Facial Treatment Hub** (`https://sozoskinclinic.com/skin-treatment/facial-treatment/`)
    - [x] Signature Facial (`https://sozoskinclinic.com/skin-treatment/facial-treatment/signature-facial/`)
    - [x] Mini Facial (`https://sozoskinclinic.com/skin-treatment/facial-treatment/mini-facial-treatment/`)
    - [x] Acne Facial (`https://sozoskinclinic.com/skin-treatment/facial-treatment/acne-clear-facial/`)
    - [x] Acne Laser Facial (`https://sozoskinclinic.com/skin-treatment/facial-treatment/acne-laser-facial/`)
    - [x] Brightening Facial (`https://sozoskinclinic.com/skin-treatment/facial-treatment/brightening-facial/`)
    - [x] Diamond Laser Facial (`https://sozoskinclinic.com/skin-treatment/facial-treatment/diamond-laser-facial/`)
    - [ ] Collagen Mask (`https://sozoskinclinic.com/skin-treatment/facial-treatment/collagen-mask/`)
    - [x] Sylfirm X (`https://sozoskinclinic.com/skin-treatment/facial-treatment/sylfirm-x/`)
- [ ] **Acne Treatment (Non-Facial)** (`https://sozoskinclinic.com/skin-treatment/acne-treatment/`)
- [ ] **IPL Treatment Hub** (`https://sozoskinclinic.com/skin-treatment/ipl-treatment/`)
    - [ ] IPL Acne (`https://sozoskinclinic.com/skin-treatment/ipl-treatment/ipl-acne/`)
    - [ ] IPL Glow (`https://sozoskinclinic.com/skin-treatment/ipl-treatment/ipl-glow/`)
- [ ] **Derma Peel Hub** (`https://sozoskinclinic.com/skin-treatment/derma-peel-treatment/`)
    - [ ] Acne Peel (`https://sozoskinclinic.com/skin-treatment/derma-peel-treatment/acne-peel/`)
    - [ ] Glow Peel (`https://sozoskinclinic.com/skin-treatment/derma-peel-treatment/glow-peel/`)
    - [ ] Dazzling Glow Peel (`https://sozoskinclinic.com/skin-treatment/derma-peel-treatment/dazling-glow-peel/`)
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
- [ ] Klinik Kecantikan Cirebon (`https://sozoskinclinic.com/lokasi/cirebon/`)
- [ ] Klinik Kecantikan Solo (`https://sozoskinclinic.com/lokasi/solo/`)
- [ ] Klinik Kecantikan Balikpapan (`https://sozoskinclinic.com/lokasi/balikpapan/`)
- [ ] Klinik Kecantikan Cikarang (`https://sozoskinclinic.com/lokasi/cikarang/`)
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

---

## 🚨 Catatan Teknis & Aturan Baku (Red Flags)

Setiap schema yang di-generate harus mematuhi aturan standar Google (update 2026):

| Kasus | Panduan Wajib Penanganan |
| :--- | :--- |
| **Aturan Tanpa Harga (No Price)** | Jika halaman tidak mencantumkan harga spesifik (hanya tombol konsultasi), blok `priceSpecification` dalam `hasOfferCatalog` **DILARANG** dimasukkan. |
| **Field Item Breadcrumb** | Setiap `ListItem` dalam breadcrumb **WAJIB** memiliki *field* `item` (URL)[cite: 1]. Jika item terakhir tidak memiliki URL, Google Search Console akan mengeluarkan error "Missing field item"[cite: 1]. |
| **Skema Product** | **HANYA** gunakan skema `Product` untuk rumpun URL di kategori nomor 7 (`/product/*`). Halaman kategori 2 sampai 6 wajib menggunakan skema `Service` demi menghindari error massal di Google[cite: 1]. |

---

## 🚀 Alur Integrasi ke WordPress (WPCode)

Setelah schema `.json` selesai di-generate dan divalidasi:
1. Masuk ke Dashboard WordPress > **WPCode** > **Add New**.
2. Pilih Code Type: **HTML Snippet**[cite: 1].
3. Paste kode `<script type="application/ld+json"> ... </script>` (Letakkan di `Insert Before </head>`)[cite: 1].
4. Buka tab **Smart Conditional Logic** > Enable[cite: 1].
5. Set kondisi: **Page URL contains** > `[slug-halaman]` (Contoh: `slimming-treatment`)[cite: 1].
6. **Active** > **Update**[cite: 1].