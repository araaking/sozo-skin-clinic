**Dokumentasi Implementasi Schema Markup**

Sozo Skin Clinic — sozoskinclinic.com

*Migrasi dari Yoast SEO ke Custom Schema (JSON-LD)*

Versi 1.0  •  23 Juni 2026

**Status: 28 dari 133 halaman selesai**

# **1\. Ringkasan Proyek**

Dokumen ini mencatat seluruh proses migrasi schema markup di sozoskinclinic.com, dari yang sebelumnya mengandalkan plugin Yoast SEO (dengan masalah duplikasi) menjadi custom schema JSON-LD yang dikontrol penuh secara manual.

## **Latar Belakang Masalah**

* **Breadcrumb dobel** — terdapat dua sumber breadcrumb schema yang berjalan bersamaan: satu dari custom (JS-injected) dan satu dari Yoast. Terdeteksi di 47 dari 132 halaman.

* **Nama breadcrumb tercemar** — 114 halaman memiliki nama breadcrumb Yoast yang berawalan "SEO –" / "SEO |" (contoh: "SEO – Skin Treatment"), karena Yoast menarik nama dari SEO title.

* **Product schema error massal** — 218 rich result error di seluruh situs, mayoritas karena halaman treatment di-markup sebagai Product/Offer tanpa field wajib.

* **Data konflik** — saat custom & Yoast jalan bersamaan, properti seperti inLanguage (en-US vs id) dan datePublished (2021 vs 2022\) saling bertabrakan.

## **Keputusan Strategis**

Setelah evaluasi, diputuskan untuk **mematikan seluruh schema Yoast** dan membangun custom schema JSON-LD per tipe halaman. Alasan utama: kontrol penuh atas struktur, menghindari duplikasi permanen, dan kemampuan menyesuaikan breadcrumb untuk URL flat.

# **2\. Tools yang Digunakan**

| Tool | Fungsi | Catatan Penting |
| :---- | :---- | :---- |
| Screaming Frog | Audit schema massal seluruh situs | Aktifkan Configuration \> Spider \> Extraction \> Structured Data. Untuk hitung duplikat pakai Custom Extraction (mode Function Value untuk count()). |
| validator.schema.org | Cek validitas struktur markup | Baca HTML mentah, TIDAK render JavaScript. Menampilkan SEMUA tipe schema valid. |
| Rich Results Test (RRT) | Cek eligibility rich result Google | Render JavaScript (seperti Googlebot). Hanya tampilkan tipe yang eligible rich result. |
| WPCode | Inject custom schema ke halaman | Plugin snippet. Pakai tipe HTML untuk schema, PHP untuk filter Yoast. |
| Search Console | Konfirmasi schema di level situs | Ground truth — data dari crawl asli Google. |

## **Perbedaan Penting: Validator vs RRT**

Ini sering membingungkan. Kedua tool menjawab pertanyaan yang berbeda:

* **validator.schema.org** \= "Apakah kode saya benar?" → menampilkan semua item, tidak render JS.

* **Rich Results Test** \= "Apakah bisa tampil cantik di SERP?" → hanya tipe yang eligible (Breadcrumb, Organization). MedicalWebPage, Service, FAQ TIDAK muncul di RRT tapi tetap valid & dibaca.

Patokan: **tidak ada error di validator \= schema benar**. Muncul di RRT \= bonus tampilan SERP. Tidak muncul di RRT ≠ salah.

# **3\. Catatan Penting (Update Google 2026\)**

## **FAQ Rich Results Dihapus**

* Per **7 Mei 2026**, Google menghapus FAQ rich results dari Search.

* Dukungan FAQ di Rich Results Test dihapus **Juni 2026**; Search Console API **Agustus 2026**.

* **FAQPage tetap tipe schema.org yang valid** dan boleh dipertahankan — masih berguna untuk pemahaman AI/mesin, hanya tidak lagi memberi tampilan dropdown di SERP.

* Konsekuensi: FAQ schema tidak muncul di RRT (normal), tapi tetap muncul di validator.

## **Prinsip Schema 2026**

* **Jangan over-markup** — jangan pakai Product schema untuk listing jasa demi menampilkan harga. Filter spam Google makin tajam.

* **Konsistensi @id** — deklarasi Organization sekali di homepage (\#organization), halaman lain cukup pointer @id.

* **Hanya markup konten yang terlihat user** — markup konten tersembunyi \= risiko manual action.

# **4\. Arsitektur Schema**

Konsep inti: Organization dideklarasi lengkap **hanya di homepage**. Semua halaman lain menghubungkan diri ke entity tersebut via pointer @id, sehingga membentuk satu Knowledge Graph yang terhubung.

Homepage (Organization hub — deklarasi lengkap)  
    |  
    |-- @id pointer: https://sozoskinclinic.com/\#organization  
    |  
    \+-- Hair Removal Treatment  \--\> about / provider \--\> \#organization  
    \+-- Slimming Treatment      \--\> about / provider \--\> \#organization  
    \+-- Facial Treatment        \--\> about / provider \--\> \#organization  
    \+-- ... 130+ halaman lain

Catatan: saat cek halaman treatment di validator/RRT, Organization akan tetap muncul lengkap. Itu **normal** — tool me-resolve pointer @id dan menarik data Organization dari homepage. Bukan duplikasi.

## **Pemetaan Schema per Tipe Halaman**

| Tipe Halaman | Schema yang Dipakai | Sumber |
| :---- | :---- | :---- |
| Homepage | Organization \+ WebSite \+ WebPage \+ BreadcrumbList \+ FAQPage | Custom (WPCode) |
| Halaman Treatment | MedicalWebPage \+ BreadcrumbList \+ Service \+ FAQPage | Custom (WPCode) |
| Blog / Artikel | Article / BlogPosting (rekomendasi dynamic) | Belum dikerjakan |
| Halaman Cabang | LocalBusiness / MedicalClinic per lokasi | Belum dikerjakan |

# **5\. Langkah yang Sudah Dilakukan**

## **5.1 Mematikan Schema Yoast**

Seluruh output JSON-LD Yoast dimatikan via snippet PHP di WPCode (tipe PHP, location Run Everywhere):

add\_filter( 'wpseo\_json\_ld\_output', '\_\_return\_false' );

**Penting:** snippet lama (remove\_breadcrumbs\_from\_schema, disable homepage) menjadi redundan setelah ini dan sudah dihapus.

Catatan tentang Yoast: setting Breadcrumbs di Yoast (Settings \> Breadcrumbs) hanya mengatur tampilan VISUAL, bukan schema. Mematikan schema wajib lewat kode.

## **5.2 Homepage — SELESAI**

URL: https://sozoskinclinic.com/

Perbaikan yang dilakukan dari versi Yoast:

* Organization duplikat dihapus → 1 node, sisanya pointer @id.

* Logo dikonsolidasi ke satu URL (.avif).

* inLanguage diubah dari en-US ke id.

* BreadcrumbList homepage: 1 item (Home) dengan field item URL lengkap.

Dipasang via WPCode tipe HTML, kondisi: **Type of page Is Homepage**. Status validator: 0 error, 0 warning.

## **5.3 Hair Removal Treatment — SELESAI**

URL: https://sozoskinclinic.com/hair-removal-treatment/

Struktur @graph (4 node):

* **MedicalWebPage** — subtipe khusus halaman medis, dengan specialty: Dermatology.

* **BreadcrumbList** — Home › Treatment › Hair Removal Treatment (URL flat, hierarki sesuai konten).

* **Service \+ hasOfferCatalog** — 5 sub-treatment dengan PriceSpecification (BUKAN Product/Offer yang sebelumnya error).

* **FAQPage** — 15 pertanyaan dari konten existing.

Dipasang via WPCode tipe HTML, kondisi: Page URL contains hair-removal-treatment. Status validator: 0 error, 0 warning.

## **5.4 Skin Treatment — SELESAI**

URL: https://sozoskinclinic.com/skin-treatment/

Struktur @graph (3 node):

* **MedicalWebPage** — subtipe khusus halaman medis, dengan specialty: Dermatology.

* **BreadcrumbList** — Home › Treatment › Skin Treatment (URL flat, hierarki sesuai konten).

* **Service \+ hasOfferCatalog** — 6 sub-treatment (Facial, Laser, IPL, Derma Peel, Scar, Acne Treatment) tanpa PriceSpecification karena halaman tidak mencantumkan harga spesifik.

Catatan: Halaman ini tidak memiliki konten FAQ, sehingga FAQPage tidak disertakan.

Dipasang via WPCode tipe HTML, kondisi: Page URL contains skin-treatment.

## **5.5 Injectable Treatment — SELESAI**

URL: https://sozoskinclinic.com/injectable-treatment/

Struktur @graph (4 node):

* **MedicalWebPage** — subtipe khusus halaman medis, dengan specialty: Dermatology.

* **BreadcrumbList** — Home › Treatment › Injectable Treatment (URL flat, hierarki sesuai konten).

* **Service \+ hasOfferCatalog** — 5 sub-treatment (Skin Booster, Botox, Infusion, Threadlift, Filler) tanpa PriceSpecification karena halaman tidak mencantumkan harga spesifik.

* **FAQPage** — 6 pertanyaan dari konten existing.

Dipasang via WPCode tipe HTML, kondisi: Page URL contains injectable-treatment.

## **5.6 Hair Grow Booster Treatment — SELESAI**

URL: https://sozoskinclinic.com/hair-treatment/hair-grow-booster-treatment/

Struktur @graph (4 node):

* **MedicalWebPage** — subtipe khusus halaman medis, dengan specialty: http://schema.org/Dermatology.

* **BreadcrumbList** — Home › Hair Treatment › Hair Grow Booster (3 level, URL sesuai hierarki).

* **Service \+ offers** — Single treatment (bukan hub) dengan PriceSpecification: Rp 1.499.000 (Mulai dari). Tidak menggunakan hasOfferCatalog karena ini halaman layanan spesifik, bukan katalog. Properti `offers` bersih tanpa redundant `name`, `price`/`priceCurrency` dobel, dan `availability` (tidak relevan untuk jasa medis).

* **FAQPage** — 5 pertanyaan dari konten existing (microneedling, hasil, sesi, efek samping, keramas).

Dipasang via WPCode tipe HTML, kondisi: Page URL contains hair-grow-booster-treatment.

## **5.7 PRP Hair Treatment — SELESAI**

URL: https://sozoskinclinic.com/hair-treatment/prp-hair-treatment/

Struktur @graph (4 node):

* **MedicalWebPage** — subtipe khusus halaman medis, dengan specialty: http://schema.org/Dermatology.

* **BreadcrumbList** — Home › Hair Treatment › PRP Hair Treatment (3 level, URL sesuai hierarki).

* **Service \+ offers** — Single treatment (bukan hub) dengan PriceSpecification: Rp 1.099.000 (Mulai dari). Schema sebelumnya menggunakan `Product` + `Offer` + `availability: InStock` yang merupakan red flag (skema Product tidak boleh untuk jasa). Dioptimasi menggunakan `Service` + `Offer` + `PriceSpecification` yang clean.

* **FAQPage** — 7 pertanyaan dari konten existing (kegunaan, hasil, harga, permanen, kebotakan pria, rasa sakit, lokasi cabang).

Dipasang via WPCode tipe HTML, kondisi: Page URL contains prp-hair-treatment.

## **5.8 Biolight Hair Treatment — SELESAI**

URL: https://sozoskinclinic.com/hair-treatment/biolight-hair-treatment/

Struktur @graph (4 node):

* **MedicalWebPage** — subtipe khusus halaman medis, dengan specialty: http://schema.org/Dermatology.

* **BreadcrumbList** — Home › Hair Treatment › Biolight Hair (3 level, URL sesuai hierarki).

* **Service \+ offers** — Single treatment (bukan hub) dengan PriceSpecification: Rp 1.455.000 (Mulai dari). Schema sebelumnya menggunakan `Product` + `Offer` + `availability: InStock` yang merupakan red flag. Dioptimasi menggunakan `Service` + `Offer` + `PriceSpecification` yang clean.

* **FAQPage** — 2 pertanyaan dari konten existing (keamanan terapi, waktu hasil terlihat).

Dipasang via WPCode tipe HTML, kondisi: Page URL contains biolight-hair-treatment.

## **5.9 Exosome Hair Treatment — SELESAI**

URL: https://sozoskinclinic.com/hair-treatment/exosome-hair-treatment/

Struktur @graph (4 node):

* **MedicalWebPage** — subtipe khusus halaman medis, dengan specialty: http://schema.org/Dermatology.

* **BreadcrumbList** — Home › Hair Treatment › Exosome Hair Treatment (3 level, URL sesuai hierarki).

* **Service \+ offers** — Single treatment (bukan hub) dengan PriceSpecification: Rp 3.849.000 (Mulai dari). Schema sebelumnya menggunakan `Product` + `Offer` + `availability: InStock` yang merupakan red flag. Dioptimasi menggunakan `Service` + `Offer` + `PriceSpecification` yang clean.

* **FAQPage** — 7 pertanyaan dari konten existing (cara kerja, beda dengan PRP, kandidat, hasil, sesi, harga, lokasi cabang).

Dipasang via WPCode tipe HTML, kondisi: Page URL contains exosome-hair-treatment.

## **5.10 Brow Grow Treatment Alis — SELESAI**

URL: https://sozoskinclinic.com/hair-treatment/brow-grow/

Struktur @graph (4 node):

* **MedicalWebPage** — subtipe khusus halaman medis, dengan specialty: http://schema.org/Dermatology.

* **BreadcrumbList** — Home › Hair Treatment › Brow Grow (3 level, URL sesuai hierarki).

* **Service \+ offers** — Single treatment (bukan hub) dengan PriceSpecification: Rp 599.000 (Mulai dari). Schema sebelumnya menggunakan `Product` + `Offer` + `availability: InStock` yang merupakan red flag. Dioptimasi menggunakan `Service` + `Offer` + `PriceSpecification` yang clean.

* **FAQPage** — 7 pertanyaan dari konten existing (cara kerja, rasa sakit, permanen, sesi, larangan basah, harga, lokasi cabang).

Dipasang via WPCode tipe HTML, kondisi: Page URL contains brow-grow.

## **5.11 Beard Grow Treatment — SELESAI**

URL: https://sozoskinclinic.com/hair-treatment/beard-grow-treatment/

Struktur @graph (4 node):

* **MedicalWebPage** — subtipe khusus halaman medis, dengan specialty: http://schema.org/Dermatology.

* **BreadcrumbList** — Home › Hair Treatment › Beard Grow Treatment (3 level, URL sesuai hierarki).

* **Service \+ offers** — Single treatment (bukan hub) dengan PriceSpecification: Rp 999.000 (Mulai dari). Schema sebelumnya menggunakan `MedicalWebPage` dengan `about` berisi objek `MedicalProcedure` + `Service` bersarang yang non-standar, serta `BreadcrumbList` yang hilang dari `@graph`. Dioptimasi menggunakan struktur standar: `MedicalWebPage` + `BreadcrumbList` + `Service` + `FAQPage`.

* **FAQPage** — 9 pertanyaan dari konten existing (definisi, cara kerja, masalah brewok, hasil, keamanan, rasa sakit, harga, permanen, lokasi cabang).

Dipasang via WPCode tipe HTML, kondisi: Page URL contains beard-grow-treatment.

## **5.12 Laser Hair Removal Treatment — SELESAI**

URL: https://sozoskinclinic.com/hair-removal-treatment/laser-hair-removal-treatment/

Struktur @graph (4 node):

* **MedicalWebPage** — subtipe khusus halaman medis, dengan specialty: http://schema.org/Dermatology.

* **BreadcrumbList** — Home › Hair Removal Treatment › Laser Hair Removal Treatment (3 level).

* **Service + offers** — Single treatment dengan PriceSpecification: Rp 249.000 (Mulai dari).

* **FAQPage** — 9 pertanyaan dari konten existing (definisi, harga, sesi, rasa sakit, permanen, perbedaan IPL/waxing, keamanan kulit, area tubuh, lokasi cabang).

Microdata breadcrumb (itemscope/itemtype) telah dihapus dari HTML body untuk mencegah duplikasi BreadcrumbList.

Dipasang via WPCode tipe HTML, kondisi: Page URL contains laser-hair-removal-treatment.

## **5.13 Underarm Hair Removal Treatment — SELESAI**

URL: https://sozoskinclinic.com/hair-removal-treatment/underarm-hair-removal-treatment/

Struktur @graph (4 node):

* **MedicalWebPage** — subtipe khusus halaman medis, dengan specialty: http://schema.org/Dermatology.

* **BreadcrumbList** — Home › Hair Removal Treatment › Underarm Hair Removal (3 level).

* **Service + offers** — Single treatment dengan PriceSpecification: Rp 249.000 (Mulai dari).

* **FAQPage** — 8 pertanyaan dari konten existing (definisi IPL, perbedaan shaving/waxing, rasa sakit, permanen, jumlah sesi, aftercare, harga, lokasi cabang).

Microdata breadcrumb (itemscope/itemtype) telah dihapus dari HTML body untuk mencegah duplikasi BreadcrumbList.

Dipasang via WPCode tipe HTML, kondisi: Page URL contains underarm-hair-removal-treatment.

## **5.14 Underarm Brightening Treatment — SELESAI**

URL: https://sozoskinclinic.com/hair-removal-treatment/underarm-brightening-treatment/

Struktur @graph (4 node):

* **MedicalWebPage** — subtipe khusus halaman medis, dengan specialty: http://schema.org/Dermatology.

* **BreadcrumbList** — Home › Hair Removal Treatment › Underarm Brightening Treatment (3 level).

* **Service + offers** — Single treatment dengan PriceSpecification: Rp 379.000 (Mulai dari). Schema sebelumnya menggunakan `about` berisi objek `MedicalProcedure` + `Service` bersarang yang non-standar, serta `Offer` + `availability: InStock` yang merupakan red flag. Dioptimasi menggunakan `Service` + `Offer` + `PriceSpecification` yang clean.

* **FAQPage** — 8 pertanyaan dari konten existing (definisi, keamanan kulit sensitif, hasil, harga, kombinasi laser, beda krim, pantangan, lokasi cabang).

Microdata breadcrumb (itemscope/itemtype) dan microdata Product/Offer telah dihapus dari HTML body untuk mencegah duplikasi.

Dipasang via WPCode tipe HTML, kondisi: Page URL contains underarm-brightening-treatment.

## **5.15 Brazilian Hair Removal — SELESAI**

URL: https://sozoskinclinic.com/hair-removal-treatment/brazilian-hair-removal-treatment/

Struktur @graph (4 node):

* **MedicalWebPage** — subtipe khusus halaman medis, dengan specialty: http://schema.org/Dermatology.

* **BreadcrumbList** — Home › Hair Removal Treatment › Brazilian Hair Removal (3 level).

* **Service + offers** — Single treatment dengan PriceSpecification: Rp 599.000 (Mulai dari). Schema sebelumnya menggunakan `Product` + `Offer` + `availability: InStock` yang merupakan red flag. Dioptimasi menggunakan `Service` + `Offer` + `PriceSpecification` yang clean.

* **FAQPage** — 9 pertanyaan dari konten existing (definisi, area treatment, keamanan, rasa sakit, beda waxing, harga, sesi, permanen, lokasi cabang).

Microdata breadcrumb (itemscope/itemtype) dan microdata Product/Offer telah dihapus dari HTML body untuk mencegah duplikasi.

Dipasang via WPCode tipe HTML, kondisi: Page URL contains brazilian-hair-removal-treatment.

## **5.16 Body Hair Removal — SELESAI**

URL: https://sozoskinclinic.com/hair-removal-treatment/hair-removal-body-treatment/

Struktur @graph (4 node):

* **MedicalWebPage** — subtipe khusus halaman medis, dengan specialty: http://schema.org/Dermatology.

* **BreadcrumbList** — Home › Hair Removal Treatment › Body Hair Removal (3 level).

* **Service + offers** — Single treatment dengan PriceSpecification: Rp 1.598.000 (Mulai dari). Schema sebelumnya menggunakan `Product` + `Offer` + `availability: InStock` yang merupakan red flag. Dioptimasi menggunakan `Service` + `Offer` + `PriceSpecification` yang clean.

* **FAQPage** — 9 pertanyaan dari konten existing (definisi, area treatment, keamanan, durasi, harga, sesi, permanen, beda IPL vs waxing, lokasi cabang).

Microdata breadcrumb (itemscope/itemtype) dan microdata Product/Offer telah dihapus dari HTML body untuk mencegah duplikasi.

Dipasang via WPCode tipe HTML, kondisi: Page URL contains hair-removal-body-treatment.

## **5.17 Mini Facial Treatment — SELESAI**

URL: https://sozoskinclinic.com/skin-treatment/facial-treatment/mini-facial-treatment/

Struktur @graph (4 node):

* **MedicalWebPage** — subtipe khusus halaman medis, dengan specialty: http://schema.org/Dermatology.

* **BreadcrumbList** — Home › Skin Treatment › Facial Treatment › Mini Facial Treatment (4 level).

* **Service + offers** — Single treatment dengan PriceSpecification: Rp 149.000 (Mulai dari). Schema sebelumnya menggunakan `Product` + `Offer` + `availability: InStock` yang merupakan red flag. Dioptimasi menggunakan `Service` + `Offer` + `PriceSpecification` yang clean.

* **FAQPage** — 9 pertanyaan dari konten existing (definisi, durasi, manfaat, harga, kulit berjerawat, beda facial biasa, rutin, downtime, lokasi cabang).

Microdata breadcrumb (itemscope/itemtype) dan microdata Product/Offer telah dihapus dari HTML body untuk mencegah duplikasi.

Dipasang via WPCode tipe HTML, kondisi: Page URL contains mini-facial-treatment.

## **5.18 Acne Clear Facial — SELESAI**

URL: https://sozoskinclinic.com/facial-treatment/acne-clear-facial/

Struktur @graph (4 node):

* **MedicalWebPage** — subtipe khusus halaman medis, dengan specialty: Dermatology.

* **BreadcrumbList** — Home › Skin Treatment › Facial Treatment › Acne Clear Facial (URL flat, hierarki sesuai konten).

* **Service + offers** — Single treatment dengan PriceSpecification: Rp 449.000 (Mulai dari).

* **FAQPage** — 6 pertanyaan dari konten existing.

Microdata breadcrumb (itemscope/itemtype) dan microdata Product/Offer telah dihapus dari HTML body untuk mencegah duplikasi.

Dipasang via WPCode tipe HTML, kondisi: Page URL contains acne-clear-facial.

## **5.19 Brightening Facial — SELESAI**

URL: https://sozoskinclinic.com/facial-treatment/brightening-facial/

Struktur @graph (4 node):

* **MedicalWebPage** — subtipe khusus halaman medis, dengan specialty: Dermatology.

* **BreadcrumbList** — Home › Skin Treatment › Facial Treatment › Skin Brightening Facial (URL flat, hierarki sesuai konten).

* **Service + offers** — Single treatment dengan PriceSpecification: Rp 499.000 (Mulai dari). Schema sebelumnya menggunakan `Product` + `Offer` + `availability: InStock` yang merupakan red flag. Dioptimasi menggunakan `Service` + `Offer` + `PriceSpecification` yang clean.

* **FAQPage** — 8 pertanyaan dari konten existing.

Microdata breadcrumb (itemscope/itemtype) dan microdata Product/Offer telah dihapus dari HTML body untuk mencegah duplikasi.

Dipasang via WPCode tipe HTML, kondisi: Page URL contains brightening-facial.

## **5.20 Signature Facial — SELESAI**

URL: https://sozoskinclinic.com/facial-treatment/signature-facial/

Struktur @graph (4 node):

* **MedicalWebPage** — subtipe khusus halaman medis, dengan specialty: Dermatology.

* **BreadcrumbList** — Home › Skin Treatment › Facial Treatment › Signature Facial (URL flat, hierarki sesuai konten).

* **Service + offers** — Single treatment dengan PriceSpecification: Rp 699.000 (Harga Promo).

* **FAQPage** — 3 pertanyaan dari konten existing.

Microdata breadcrumb (itemscope/itemtype) dan microdata Product/Offer telah dihapus dari HTML body untuk mencegah duplikasi.

Dipasang via WPCode tipe HTML, kondisi: Page URL contains signature-facial.

## **5.21 Diamond Laser Facial — SELESAI**

URL: https://sozoskinclinic.com/facial-treatment/diamond-laser-facial/

Struktur @graph (4 node):

* **MedicalWebPage** — subtipe khusus halaman medis, dengan specialty: Dermatology.

* **BreadcrumbList** — Home › Skin Treatment › Facial Treatment › Diamond Laser Facial (URL flat, hierarki sesuai konten).

* **Service + offers** — Single treatment dengan PriceSpecification: Rp 999.000 (Harga Promo).

* **FAQPage** — 3 pertanyaan dari konten existing.

Microdata breadcrumb (itemscope/itemtype) dan microdata Product/Offer telah dihapus dari HTML body untuk mencegah duplikasi.

Dipasang via WPCode tipe HTML, kondisi: Page URL contains diamond-laser-facial.

## **5.22 Sylfirm X — SELESAI**

URL: https://sozoskinclinic.com/skin-treatment/facial-treatment/sylfirm-x/

Struktur @graph (4 node):

* **MedicalWebPage** — subtipe khusus halaman medis, dengan specialty: Dermatology.

* **BreadcrumbList** — Home › Skin Treatment › Facial Treatment › Sylfirm X (4 level, URL sesuai hierarki).

* **Service + offers** — Single treatment dengan PriceSpecification: Rp 3.999.000 (Mulai dari). Schema sebelumnya menggunakan `Product` + `AggregateOffer` + `availability: InStock` yang merupakan red flag. Dioptimasi menggunakan `Service` + `Offer` + `PriceSpecification` yang clean.

* **FAQPage** — 9 pertanyaan dari konten existing.

Microdata breadcrumb (itemscope/itemtype) dan microdata Product/Offer telah dihapus dari HTML body.

Dipasang via WPCode tipe HTML, kondisi: Page URL contains sylfirm-x.

## **5.23 Acne Laser Facial — SELESAI**

URL: https://sozoskinclinic.com/skin-treatment/facial-treatment/acne-laser-facial/

Struktur @graph (4 node):

* **MedicalWebPage** — subtipe khusus halaman medis, dengan specialty: Dermatology.

* **BreadcrumbList** — Home › Skin Treatment › Facial Treatment › Acne Laser Facial (4 level, URL sesuai hierarki).

* **Service + offers** — Single treatment dengan PriceSpecification: Rp 1.499.000 (Harga Promo).

* **FAQPage** — 9 pertanyaan dari konten existing.

Microdata breadcrumb (itemscope/itemtype) dan microdata Product/Offer telah dihapus dari HTML body.

Dipasang via WPCode tipe HTML, kondisi: Page URL contains acne-laser-facial.

## **5.24 Nano Laser — SELESAI**

URL: https://sozoskinclinic.com/skin-treatment/laser-treatment/nano-laser-treatment/

Struktur @graph (4 node):

* **MedicalWebPage** — subtipe khusus halaman medis, dengan specialty: Dermatology.

* **BreadcrumbList** — Home › Skin Treatment › Laser Treatment › Nano Laser Treatment (4 level, URL sesuai hierarki).

* **Service + offers** — Single treatment dengan PriceSpecification: Rp 1.199.000 (Harga Promo).

* **FAQPage** — 9 pertanyaan dari konten existing.

Microdata breadcrumb (itemscope/itemtype) dan microdata Product/Offer telah dihapus dari HTML body. Breadcrumb sebelumnya memiliki item "Treatment" ekstra yang sudah diperbaiki.

Dipasang via WPCode tipe HTML, kondisi: Page URL contains nano-laser.

## **5.25 Pico Laser — SELESAI**

URL: https://sozoskinclinic.com/skin-treatment/laser-treatment/pico-laser-treatment/

Struktur @graph (4 node):

* **MedicalWebPage** — subtipe khusus halaman medis, dengan specialty: Dermatology.

* **BreadcrumbList** — Home › Skin Treatment › Laser Treatment › Pico Laser Treatment (4 level, URL sesuai hierarki).

* **Service + offers** — Single treatment dengan PriceSpecification: Rp 1.199.000 (Harga Promo).

* **FAQPage** — 9 pertanyaan dari konten existing.

Microdata breadcrumb (itemscope/itemtype) dan microdata Product/Offer telah dihapus dari HTML body. Breadcrumb sebelumnya memiliki item "Treatment" ekstra yang sudah diperbaiki.

Dipasang via WPCode tipe HTML, kondisi: Page URL contains pico-laser.

## **5.26 Laser CO2 — SELESAI**

URL: https://sozoskinclinic.com/skin-treatment/laser-treatment/laser-co2-treatment/

Struktur @graph (4 node):

* **MedicalWebPage** — subtipe khusus halaman medis, dengan specialty: Dermatology.

* **BreadcrumbList** — Home › Skin Treatment › Laser Treatment › Laser CO2 Treatment (4 level, URL sesuai hierarki).

* **Service + offers** — Single treatment dengan PriceSpecification: Rp 799.000 (Harga Promo).

* **FAQPage** — 10 pertanyaan dari konten existing.

Microdata breadcrumb (itemscope/itemtype) dan microdata Product/Offer/FAQPage telah dihapus dari HTML body. URL breadcrumb sebelumnya salah (kurang `/skin-treatment/`) sudah diperbaiki.

Dipasang via WPCode tipe HTML, kondisi: Page URL contains laser-co2.

## **5.27 Pink Lips Laser — SELESAI**

URL: https://sozoskinclinic.com/skin-treatment/laser-treatment/pink-lips-laser-treatment/

Struktur @graph (4 node):

* **MedicalWebPage** — subtipe khusus halaman medis, dengan specialty: Dermatology.

* **BreadcrumbList** — Home › Skin Treatment › Laser Treatment › Pink Lips Laser (4 level, URL sesuai hierarki).

* **Service + offers** — Single treatment dengan PriceSpecification: Rp 499.000 (Harga Promo).

* **FAQPage** — 7 pertanyaan dari konten existing.

Microdata breadcrumb (itemscope/itemtype) dan microdata Product/Offer/FAQPage telah dihapus dari HTML body. URL breadcrumb sebelumnya salah (kurang `/skin-treatment/`) sudah diperbaiki.

Dipasang via WPCode tipe HTML, kondisi: Page URL contains pink-lips-laser.

## **5.28 Tattoo Removal — SELESAI**

URL: https://sozoskinclinic.com/skin-treatment/laser-treatment/tattoo-removal/

Struktur @graph (4 node):

* **MedicalWebPage** — subtipe khusus halaman medis, dengan specialty: Dermatology.

* **BreadcrumbList** — Home › Skin Treatment › Laser Treatment › Tattoo Removal (4 level, URL sesuai hierarki).

* **Service + offers** — Single treatment dengan PriceSpecification: Rp 499.000 (Harga Promo).

* **FAQPage** — 2 pertanyaan dari konten existing.

Microdata breadcrumb (itemscope/itemtype) dan microdata Product/Offer/FAQPage telah dihapus dari HTML body. URL breadcrumb dan position sebelumnya salah, sudah diperbaiki.

Dipasang via WPCode tipe HTML, kondisi: Page URL contains tattoo-removal.

## **5.29 Laser Rejuve — SELESAI**

URL: https://sozoskinclinic.com/skin-treatment/laser-treatment/laser-rejuve-treatment/

Struktur @graph (4 node):

* **MedicalWebPage** — subtipe khusus halaman medis, dengan specialty: Dermatology.

* **BreadcrumbList** — Home › Skin Treatment › Laser Treatment › Laser Rejuve (4 level, URL sesuai hierarki).

* **Service + offers** — Single treatment dengan PriceSpecification: Rp 1.199.000 (Harga Promo).

* **FAQPage** — 9 pertanyaan dari konten existing.

Microdata breadcrumb (itemscope/itemtype) dan microdata Product/Offer telah dihapus dari HTML body. Breadcrumb sebelumnya memiliki item "Treatment" ekstra yang sudah diperbaiki.

Dipasang via WPCode tipe HTML, kondisi: Page URL contains laser-rejuve.

# **6\. Cara Memasang Schema (WPCode)**

## **Untuk Schema Baru (HTML)**

1. Buat snippet baru di WPCode.

2. Code Type: **HTML Snippet**.

3. Paste isi \<script type="application/ld+json"\>...\</script\> (komentar di atasnya tidak perlu ikut).

4. Insert Method: Auto Insert, Location: **Insert Before \</head\>**.

5. Smart Conditional Logic: Enable → Show this code snippet if → set kondisi halaman (Homepage, atau Page URL contains \[slug\]).

6. Aktifkan (toggle Active), Update, lalu test di validator \+ RRT.

## **Kenapa WPCode, Bukan Widget HTML Elementor**

* Schema masuk ke \<head\> (best practice), bukan body.

* Tidak hilang/tergeser saat halaman di-rebuild di Elementor.

* Tersentralisasi & mudah di-audit.

* Mudah bulk update (mis. ganti nomor telepon di semua schema).

# **7\. Checklist QC per Halaman**

Sebelum menandai sebuah halaman "selesai", pastikan:

* Validator schema.org: **0 error, 0 warning**.

* Tidak ada BreadcrumbList dobel (cuma 1 blok).

* Setiap ListItem breadcrumb punya field **item** (URL) — termasuk item terakhir.

* Breadcrumb mengikuti hierarki konten yang benar (bukan nama tercemar "SEO –").

* Tidak ada nilai konflik (inLanguage, datePublished).

* Harga pakai PriceSpecification dalam Service, BUKAN Product/Offer.

* Kondisi WPCode di-set ke halaman yang tepat (tidak Run Everywhere kecuali memang perlu).

* @id pointer ke \#organization & \#website konsisten.

# **8\. Sisa Pekerjaan**

| Item | Jumlah | Status |
| :---- | :---- | :---- |
| Homepage | 1 | SELESAI |
| Hair Removal Treatment | 1 | SELESAI |
| Skin Treatment | 1 | SELESAI |
| Injectable Treatment | 1 | SELESAI |
| Hair Grow Booster Treatment | 1 | SELESAI |
| PRP Hair Treatment | 1 | SELESAI |
| Biolight Hair Treatment | 1 | SELESAI |
| Exosome Hair Treatment | 1 | SELESAI |
| Brow Grow Treatment Alis | 1 | SELESAI |
| Beard Grow Treatment | 1 | SELESAI |
| Laser Hair Removal Treatment | 1 | SELESAI |
| Underarm Hair Removal Treatment | 1 | SELESAI |
| Underarm Brightening Treatment | 1 | SELESAI |
| Brazilian Hair Removal | 1 | SELESAI |
| Body Hair Removal | 1 | SELESAI |
| Mini Facial Treatment | 1 | SELESAI |
| Acne Clear Facial | 1 | SELESAI |
| Brightening Facial | 1 | SELESAI |
| Signature Facial | 1 | SELESAI |
| Diamond Laser Facial | 1 | SELESAI |
| Sylfirm X | 1 | SELESAI |
| Acne Laser Facial | 1 | SELESAI |
| Nano Laser | 1 | SELESAI |
| Pico Laser | 1 | SELESAI |
| Laser CO2 | 1 | SELESAI |
| Pink Lips Laser | 1 | SELESAI |
| Tattoo Removal | 1 | SELESAI |
| Laser Rejuve | 1 | SELESAI |
| Halaman treatment lain (LP) | \~105 | Belum — pakai template Hair Removal |
| Benerin nama breadcrumb "SEO –" | 114 halaman | Belum (jika masih relevan setelah custom) |
| Schema blog / Article | — | Belum — pertimbangkan dynamic PHP |
| Schema cabang (LocalBusiness) | 60+ cabang | Belum |

## **Template untuk Halaman Treatment Berikutnya**

Gunakan schema Hair Removal sebagai template. Yang perlu diganti per halaman:

* URL halaman (di @id, url, breadcrumb, ReadAction).

* name & description (judul \+ meta halaman).

* Breadcrumb path (sesuai hierarki konten).

* Daftar sub-treatment \+ harga di hasOfferCatalog.

* Daftar FAQ (dari konten halaman tsb).

## **Pertimbangan: Custom Dynamic vs Manual**

Custom schema BISA dibuat dynamic via PHP (get\_the\_title(), get\_the\_date(), dll) sehingga auto-maintain seperti Yoast. Keputusannya bukan teknis tapi soal resource: siapa yang menanggung maintenance kode jangka panjang. Untuk blog yang isinya terus bertambah, dynamic PHP sangat dianjurkan agar tidak perlu generate manual tiap artikel.

# **9\. Catatan Teknis Penting**

| Topik | Catatan |
| :---- | :---- |
| Field item wajib | Google strict: SEMUA ListItem breadcrumb harus punya field item (URL), termasuk item terakhir. Tanpa ini muncul error "Missing field item". |
| URL flat & breadcrumb | URL boleh flat (sozoskinclinic.com/caloburn-treatment/) tapi breadcrumb tetap bisa menampilkan hierarki (Home › Treatment › Slimming › Caloburn). Ini valid menurut Google. |
| Custom breadcrumb JS | Breadcrumb custom lama di-inject via JavaScript, makanya muncul di RRT (render JS) tapi tidak di validator (HTML mentah). |
| CreativeWork di validator | Saat cek halaman treatment, \#website kadang muncul sebagai CreativeWork generik. Normal — validator tidak bisa resolve WebSite penuh dari halaman non-homepage. |
| Organization muncul di halaman treatment | Normal. Tool me-resolve pointer @id ke homepage. Bukan duplikasi, bukan dari Yoast (Yoast sudah mati). |

*— Akhir Dokumen —*