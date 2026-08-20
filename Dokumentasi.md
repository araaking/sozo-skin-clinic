**Dokumentasi Implementasi Schema Markup**

Sozo Skin Clinic — sozoskinclinic.com

*Migrasi dari Yoast SEO ke Custom Schema (JSON-LD)*

Versi 1.8  •  5 Agustus 2026

**Status: 75 dari 140 halaman memiliki custom schema + 2 schema siap WPCode (dr. Putri, editorial board review) + 1 README sync (URL baru Zo-Tox sub, Threadlift baru, Filler Premium, Infus Premium, Signature HIFU, Blog, Tim Dokter)**

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
| Halaman List Tim Dokter | CollectionPage \+ BreadcrumbList \+ ItemList (Person ringan) | Custom (WPCode) |
| Halaman Detail Dokter | ProfilePage \+ BreadcrumbList \+ IndividualPhysician (array \["Person", "IndividualPhysician"\]) | Custom (WPCode) |
| Editorial Board | AboutPage \+ reviewedBy \+ BreadcrumbList | Custom (WPCode) |
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

## **5.30 Pores Treatment — SELESAI**

URL: https://sozoskinclinic.com/skin-treatment/scar-treatment/pores-treatment/

Struktur @graph (4 node):

* **MedicalWebPage** — subtipe khusus halaman medis, dengan specialty: http://schema.org/Dermatology.

* **BreadcrumbList** — Home › Skin Treatment › Scar Treatment › Pores Treatment (4 level, URL sesuai hierarki).

* **Service + offers** — Single treatment dengan PriceSpecification: Rp 1.499.000 (Harga Mulai).

* **FAQPage** — 9 pertanyaan dari konten existing (definisi, kemampuan mengecilkan pori, harga, penyebab, kandidat, sesi, keamanan, beda dengan skincare, lokasi cabang).

Microdata breadcrumb (itemscope/itemtype) dan Product/Offer telah dihapus dari HTML body. Dipasang via WPCode tipe HTML, kondisi: Page URL contains pores-treatment.

## **5.31 PRP Treatment (Scar) — SELESAI**

URL: https://sozoskinclinic.com/skin-treatment/scar-treatment/prp-treatment/

Struktur @graph (4 node):

* **MedicalWebPage** — subtipe khusus halaman medis, dengan specialty: http://schema.org/Dermatology.

* **BreadcrumbList** — Home › Skin Treatment › Scar Treatment › PRP Treatment (4 level, URL sesuai hierarki).

* **Service + offers** — Single treatment dengan PriceSpecification: Rp 1.079.000 (Harga Promo). Service.alternateName: "PRP Treatment".

* **FAQPage** — 9 pertanyaan dari konten existing.

Microdata breadcrumb (itemscope/itemtype) dan Product/Offer telah dihapus dari HTML body. Dipasang via WPCode tipe HTML, kondisi: Page URL contains prp-treatment.

## **5.32 Rejuran Scar Treatment — SELESAI**

URL: https://sozoskinclinic.com/skin-treatment/scar-treatment/rejuran-scar-treatment/

Struktur @graph (4 node):

* **MedicalWebPage** — subtipe khusus halaman medis, dengan specialty: http://schema.org/Dermatology.

* **BreadcrumbList** — Home › Skin Treatment › Scar Treatment › Rejuran Scar (4 level, URL sesuai hierarki).

* **Service + offers** — Single treatment dengan PriceSpecification: Rp 3.099.000 (Harga Mulai). Service.alternateName: "Rejuran Scar".

* **FAQPage** — 9 pertanyaan dari konten existing.

Microdata breadcrumb (itemscope/itemtype) dan Product/Offer telah dihapus dari HTML body. Schema awal tidak memiliki node BreadcrumbList di @graph — diperbaiki dengan menambahkan 4-level breadcrumb lengkap. Dipasang via WPCode tipe HTML, kondisi: Page URL contains rejuran-scar-treatment.

## **5.33 Restylane Scar — SELESAI**

URL: https://sozoskinclinic.com/skin-treatment/scar-treatment/restylane-scar/

Struktur @graph (4 node):

* **MedicalWebPage** — subtipe khusus halaman medis, dengan specialty: http://schema.org/Dermatology.

* **BreadcrumbList** — Home › Skin Treatment › Scar Treatment › Restylane Scar (4 level, URL sesuai hierarki).

* **Service + offers** — Single treatment dengan PriceSpecification: Rp 3.999.000 (Harga Promo).

* **FAQPage** — 4 pertanyaan dari konten existing.

Microdata breadcrumb (itemscope/itemtype) dan Product/Offer telah dihapus dari HTML body. Schema awal tidak memiliki node BreadcrumbList di @graph — diperbaiki. Dipasang via WPCode tipe HTML, kondisi: Page URL contains restylane-scar.

## **5.34 Subcision Treatment — SELESAI**

URL: https://sozoskinclinic.com/skin-treatment/scar-treatment/subcision-treatment/

Struktur @graph (4 node):

* **MedicalWebPage** — subtipe khusus halaman medis, dengan specialty: http://schema.org/Dermatology.

* **BreadcrumbList** — Home › Skin Treatment › Scar Treatment › Subcision Treatment (4 level, URL sesuai hierarki).

* **Service + offers** — Single treatment dengan PriceSpecification: Rp 299.000 (Harga Mulai, dari harga coret Rp 459.000). Service.alternateName: "Subcision".

* **FAQPage** — 8 pertanyaan dari konten existing.

Microdata breadcrumb (itemscope/itemtype) dan Product/Offer telah dihapus dari HTML body. Schema awal tidak memiliki node BreadcrumbList di @graph, harga di schema masih angka lama (459.000), FAQ Q7 menyebut harga lama, deskripsi Service & MedicalWebPage berbeda, dan URL di schema menggunakan typo "subsicion" (dobel s). Semua diperbaiki: URL dikoreksi ke "subcision" (sinkron dengan folder), harga di schema dan FAQ disamakan ke Rp 299.000 (halaman aktif), deskripsi Service diselaraskan dengan MedicalWebPage, dan 4-level breadcrumb lengkap ditambahkan. Dipasang via WPCode tipe HTML, kondisi: Page URL contains subcision-treatment.

## **5.35 Salmon DNA Hair Treatment — SELESAI**

URL: https://sozoskinclinic.com/hair-treatment/salmon-dna-hair-treatment/

Struktur @graph (4 node):

* **MedicalWebPage** — subtipe khusus halaman medis, dengan specialty: http://schema.org/Dermatology.

* **BreadcrumbList** — Home › Hair Treatment › Salmon DNA Hair (3 level, URL sesuai hierarki).

* **Service + offers** — Single treatment dengan PriceSpecification: Rp 2.199.000 (Mulai dari). Service.alternateName: "PDRN Hair Treatment". Treatment injeksi mesoterapi PDRN (Polydeoxyribonucleotide) dari DNA salmon untuk regenerasi folikel.

* **FAQPage** — 6 pertanyaan dari konten existing (keamanan PDRN, rasa sakit, jumlah sesi optimal, kombinasi dengan PRP, harga, lokasi cabang).

Microdata breadcrumb (itemscope/itemtype) dan Product/Offer telah dihapus dari HTML body. Dipasang via WPCode tipe HTML, kondisi: Page URL contains salmon-dna-hair-treatment.

## **5.36 Express Hair Therapy — SELESAI**

URL: https://sozoskinclinic.com/hair-treatment/express-hair-therapy/

Struktur @graph (4 node):

* **MedicalWebPage** — subtipe khusus halaman medis, dengan specialty: http://schema.org/Dermatology.

* **BreadcrumbList** — Home › Hair Treatment › Express Hair Therapy (3 level, URL sesuai hierarki).

* **Service + offers** — Single treatment dengan PriceSpecification: Rp 699.000 (Mulai dari). Service.alternateName: "Express Hair Treatment". Treatment non-invasif (deep cleansing kulit kepala) — tidak menggunakan jarum atau bahan kimia keras, tidak ada downtime.

* **FAQPage** — 9 pertanyaan dari konten existing (definisi, keamanan, durasi 30–45 menit, efek samping, frekuensi maintenance, jenis rambut yang cocok, harga, persiapan, lokasi cabang).

Microdata breadcrumb (itemscope/itemtype) dan Product/Offer telah dihapus dari HTML body. Dipasang via WPCode tipe HTML, kondisi: Page URL contains express-hair-therapy.

## **5.37 Batch Pull 10 Juli 2026 — SELESAI (34 halaman)**

Commit pull `624c06a` menambahkan 34 halaman schema sekaligus dari satu sprint. Pola `MedicalWebPage` + `BreadcrumbList` + `Service` + `FAQPage` sudah jadi standar dan dipakai konsisten di seluruh batch. Tidak ada lagi schema Product/Offer yang lolos.

### **A. Slimming, RF & Meso (8 halaman, Rp 299.000–1.499.000)**

| # | Treatment | URL Slug | Harga | FAQ |
| :- | :-- | :-- | --: | :-: |
| 1 | Meso Slim Body | meso-slim-body | Rp 1.499.000 | 14 |
| 2 | Meso V Line | meso-v-line | Rp 299.000 | 8 |
| 3 | Meso Bloatway | meso-bloataway | Rp 949.000 | 8 |
| 4 | Meso Cellulift | meso-cellulift | Rp 1.299.000 | 8 |
| 5 | Meso Metabolic Boost | meso-metabolic-boost | Rp 1.499.000 | 8 |
| 6 | Liftera HIFU | liftera-hifu | Rp 699.000 | 6 |
| 7 | Radiofrequency Face | rf-face | Rp 299.000 | 8 |
| 8 | Radiofrequency Body | rf-body | Rp 299.000 | 8 |

Catatan: folder lokal `slimming-treatment/meso-treatment/meso-bloatway/` (tanpa huruf 'a' di "bloatway") tapi URL live pakai `meso-bloataway` (dengan 'a'). Penulisan README konsisten ke URL live.

### **B. Injectable — Filler, Threadlift, Infus (4 halaman, Rp 1.099.000–5.499.000)**

| # | Treatment | URL Slug | Harga | FAQ |
| :- | :-- | :-- | --: | :-: |
| 9 | Filler Dagu | filler-dagu | Rp 5.499.000 | 10 |
| 10 | Korean Filler | korean-filler | Rp 2.999.000 | 10 |
| 11 | Tanam Benang Hidung (Nose Threadlift) | threadlift-treatment/tanam-benang-hidung | Rp 2.499.000 | 9 |
| 12 | Infus Vitamin C (Immune Glow Injection) | infus-whitening-treatment/infus-vitamin-c-immune-glow-injection | Rp 1.099.000 | 9 |

Catatan: folder lokal `treadlift-treatment/` (typo, tanpa 'h'). URL live dan penulisan schema pakai `threadlift-treatment` (dengan 'h') sesuai konsensus dokumentasi. Typo tidak menyebabkan error teknis, hanya rapikan saat refactor berikutnya.

### **C. Injectable — Skin Booster (16 halaman, Rp 799.000–8.598.000)**

| # | Treatment | URL Slug | Harga | FAQ |
| :- | :-- | :-- | --: | :-: |
| 13 | Aquashine Treatment | aquashine-treatment | Rp 799.000 | 14 |
| 14 | DNA Glow (Skin Booster DNA Salmon) | dna-glow | Rp 1.749.000 | 10 |
| 15 | Exosome Face Treatment | exosome-skin-booster | Rp 4.899.000 | 5 |
| 16 | Panda Eye Booster | eye-booster | Rp 1.799.000 | 9 |
| 17 | Glass Skin Booster | glass-skin-booster | Rp 2.199.000 | 9 |
| 18 | Jalupro Treatment | jalupro-treatment | Rp 8.598.000 | 5 |
| 19 | Juvelook | juvelook | Rp 5.599.000 | 5 |
| 20 | Nucleofill | nucleofil-treatment | Rp 6.499.000 | 6 |
| 21 | Pink Bomb Booster | pink-bomb-booster | Rp 2.999.000 | 14 |
| 22 | Pink Lips Booster | pink-lips-booster | Rp 1.799.000 | 9 |
| 23 | Profhilo Treatment | profhilo | Rp 6.999.000 | 6 |
| 24 | Rejuran Eye | rejuran-eye | Rp 3.099.000 | 9 |
| 25 | Rejuran HB | rejuran-hb-treatment | Rp 4.099.000 | 5 |
| 26 | Rejuran Healer | rejuran-healer | Rp 3.999.000 | 11 |
| 27 | Restylane Skinbooster | restylane-skinbooster | Rp 3.999.000 | 7 |
| 28 | Xela Rederm | xela-rederm-treatment | Rp 4.499.000 | 5 |

Catatan: range harga skin booster paling lebar di antara semua batch (799rb–8,5 juta). Variation ini karena beda generasi produk (Aquashine entry-level vs Jalupro premium). Di FAQ biasanya sudah eksplisit menyebut jumlah sesi & kombinasi treatment untuk tiap use case.

### **D. Advanced Skin — IPL & Derma Peel (7 halaman, Rp 299.000–889.000)**

| # | Treatment | URL Slug | Harga | FAQ |
| :- | :-- | :-- | --: | :-: |
| 29 | IPL Acne Treatment | skin-treatment/ipl-treatment/ipl-acne | Rp 399.000 | 7 |
| 30 | IPL Glow | skin-treatment/ipl-treatment/ipl-glow | Rp 399.000 | 4 |
| 31 | Acne Peel | skin-treatment/derma-peel-treatment/acne-peel | Rp 299.000 | 9 |
| 32 | Glow Peel | skin-treatment/derma-peel-treatment/glow-peel | Rp 299.000 | 9 |
| 33 | Dazzling Glow Peel | skin-treatment/derma-peel-treatment/dazling-glow-peel | Rp 889.000 | 3 |
| 34 | Eternal Bloom Peel | skin-treatment/derma-peel-treatment/eternal-bloom-peel | Rp 599.000 | 7 |
| 35 | Korean LHALA Peel | skin-treatment/derma-peel-treatment/korean-lhala-peel | Rp 299.000 | 15 |

**Catatan penting — IPL Acne pakai pola Offer langsung, bukan PriceSpecification.** Schema Service untuk IPL Acne menulis `offers` sebagai `Offer` langsung dengan field `price`/`priceCurrency`/`priceValidUntil`/`itemCondition`, tanpa membungkus di `PriceSpecification`. Sepuluh file lain di batch ini konsisten pakai pola `PriceSpecification` di dalam `Offer` (mengikuti template Hair Removal).

Pola IPL Acne tetap valid (Offer dengan price adalah bentuk dasar yang legal), tapi **tidak konsisten** dengan standar tim. Rekomendasi: refactor IPL Acne ke pola `PriceSpecification` saat sentuhan berikutnya untuk konsistensi penuh. Jangan refactor dalam sprint ini — risiko regression tidak sebanding benefit.

Derma Peel entry price (Rp 299.000) dipasang sebagai "Mulai dari" untuk Acne Peel & Glow Peel. Eternal Bloom Peel diposisikan sebagai varian premium entry (Rp 599.000), Dazzling Glow Peel sebagai varian tertinggi (Rp 889.000).

## **5.38 Pore Detox Treatment — SCHEMA SIAP, WPCODE BELUM**

URL: https://sozoskinclinic.com/skin-treatment/acne-treatment/pore-detox/

Commit pull `fbc772f` (13 Juli 2026) menyertakan dua file:

* `treatment/skin-treatment/acne-treatment/pore-detox/index.html` — layout halaman (breadcrumb visual, hero, FAQ accordion, dll).
* `treatment/skin-treatment/acne-treatment/pore-detox/schema-markup.html` — blok `<script type="application/ld+json">` lengkap.

Struktur `@graph` (4 node):

* **MedicalWebPage** — specialty: Dermatology, pointer `isPartOf` → `#website`, `about` → `#organization`, `breadcrumb` → `#breadcrumb`.

* **BreadcrumbList** — Home › Skin Treatment › Acne Treatment › Pore Detox Treatment (4 level).

* **Service + offers** — Single paket (bukan `hasOfferCatalog`) dengan `Offer` + `PriceSpecification` Rp 799.000 (Mulai dari). Paket mengombinasikan Biolight Acne, IPL Acne, Acne Peel, dan rangkaian skincare.

* **FAQPage** — 14 pertanyaan dari konten.

**Catatan untuk QC sebelum WPCode:**

* `PriceSpecification` belum memuat `priceValidUntil` (lihat AGENTS.md §"Advanced Skin"). Tambahkan sebelum aktivasi.
* Validasi di validator.schema.org dan Rich Results Test belum dijalankan di sini.
* Snippet WPCode belum dibuat di lingkungan produksi; pasang dengan kondisi **Page URL contains `pore-detox`**.

### **Checklist WPCode untuk Batch Ini**

| Kategori | Slug kondisi WPCode |
| :-- | :-- |
| Meso | `meso-slim-body`, `meso-v-line`, `meso-bloataway`, `meso-cellulift`, `meso-metabolic-boost` |
| HIFU | `liftera-hifu` |
| RF | `rf-face`, `rf-body` |
| Filler | `filler-dagu`, `korean-filler` |
| Threadlift | `tanam-benang-hidung` |
| Infus | `infus-vitamin-c-immune-glow-injection` |
| Skin Booster (16) | slug masing-masing, lihat tabel di atas |
| IPL | `ipl-acne`, `ipl-glow` |
| Derma Peel (4) | slug masing-masing, lihat tabel di atas |

**Catatan tambahan:** Pore Detox menggunakan slug `pore-detox` (lihat §5.38) — snippet WPCode-nya belum dibuat saat dokumen ini diperbarui.

## **5.39 Collagen Mask Treatment — SCHEMA SIAP, WPCODE BELUM**

URL: https://sozoskinclinic.com/skin-treatment/facial-treatment/collagen-mask/

Commit pull `3e28a28` (13 Juli 2026) menambahkan dua file:

* `treatment/skin-treatment/facial-treatment/collagen-mask/index.html` — layout halaman (breadcrumb visual, hero, FAQ accordion).
* `treatment/skin-treatment/facial-treatment/collagen-mask/schema-markup.html` — blok `<script type="application/ld+json">` lengkap.

Struktur `@graph` (4 node):

* **MedicalWebPage** — specialty: Dermatology, pointer `isPartOf` → `#website`, `about` → `#organization`.

* **BreadcrumbList** — Home › Skin Treatment › Facial Treatment › Collagen Mask Treatment (4 level).

* **Service + offers** — Single treatment dengan `Offer` + `PriceSpecification` Rp 199.000 (Mulai dari).

* **FAQPage** — 6 pertanyaan dari konten (definisi, kandidat, hasil terlihat, jumlah sesi, harga, lokasi).

Catatan: di README.md dan AGENTS.md halaman ini sebelumnya salah dikategorikan di bawah "Skin Booster Hub". Posisi sebenarnya: **Facial Treatment Hub** (bukan injeksi, treatment topikal).

## **5.40 Rejuran Skin Booster — SCHEMA SIAP, WPCODE BELUM**

URL: https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/rejuran-skin-booster/

Commit pull `3e28a28` (13 Juli 2026) menambahkan dua file:

* `treatment/injectable-treatment/skin-booster-treatment/rejuran-skin-booster/index.html` — layout halaman.
* `treatment/injectable-treatment/skin-booster-treatment/rejuran-skin-booster/schema-markup.html` — JSON-LD schema markup.

Struktur `@graph` (4 node):

* **MedicalWebPage** — specialty: Dermatology, pointer `isPartOf` → `#website`, `about` → `#organization`.

* **BreadcrumbList** — Home › Injectable Treatment › Skin Booster Treatment › Rejuran Skin Booster (4 level).

* **Service + offers** — Single treatment dengan `Offer` + `PriceSpecification` Rp 2.999.000 (Mulai dari).

* **FAQPage** — 9 pertanyaan dari konten (definisi & cara kerja, manfaat utama, beda dengan Rejuran Healer, kandidat, jumlah sesi, durasi hasil, harga, keamanan, lokasi).

Catatan: FAQ Q3 secara eksplisit membedakan Rejuran Skin Booster (formulasi polynucleotide untuk hidrasi) dengan Rejuran Healer (regenerasi kulit rusak). Pastikan jawaban di schema match dengan positioning produk di halaman.

## **5.41 Rename File Batch 13 Juli 2026 — SELESAI**

Commit `3608483` men-swap seluruh folder tracked dari pola lama (`fix.html` body + `index.html` JSON-LD) ke pola baru (`index.html` body + `schema-markup.html` JSON-LD).

* **Total folder ter-rename:** 70+ folder tracked (lihat git log untuk detail).
* **Folder dari pull `3e28a28`** (Collagen Mask & Rejuran Skin Booster): langsung ditulis dengan pola baru, swap tidak diperlukan.
* **Konvensi final:** lihat AGENTS.md §4 untuk definisi peran masing-masing file.

Implikasi operasional: snippet WPCode untuk `schema-markup.html` perlu diperbarui path kondisinya jika ada snippet lama yang merujuk ke `fix.html` atau `index.html` sebagai sumber schema. Audit snippet WPCode sebelum push.

## **5.42 URL Baru Audit 13 Juli 2026 — README SYNC**

Audit silang antara daftar URL yang harus ada di README.md dengan isi dokumen. Hasilnya, 11 URL missing ditambah 2 anomali ejaan:

* **Missing dari checklist README.md** (semua status BELUM, schema belum dibuat):
  - `hair-removal-treatment/dpl-treatment/` (sub-treatment baru)
  - `hair-treatment/treatment-alis-brow-grow/` (alias URL untuk `brow-grow/`)
  - `suplemen-pelangsing/` (landing page suplemen, di luar rumpun `/product/*`)
  - `lokasi/tangerang/karawaci/`, `greenlake/`, `gading-serpong/` (sub-cabang Tangerang)
  - `lokasi/palembang/`, `pekanbaru/`, `manado/`, `batam/` (cabang baru di luar Jawa)
* **Anomali ejaan** (sudah dikoreksi di README.md):
  - `ultrascrupt-treatment/` → `ultrasculpt-treatment/` (typo lama)
  - `salmon-dna-hair/` (versi URL user tanpa `-treatment`) → dokumentasi & repo pakai `salmon-dna-hair-treatment/`
* **Anomali URL user** (tidak perlu dikoreksi di README, hanya dicatat):
  - `skin-treatment/scar-treatment/subsicion-treatment/` (typo dobel 's') — URL live benar `subcision-treatment/`, dokumentasi §5.34 sudah benar.

URL baru di atas tidak punya schema JSON-LD sendiri di repo (belum ada folder). Status tunggu Generate.

## **5.43 Alias URL Brow Grow — CATATAN**

URL `https://sozoskinclinic.com/hair-treatment/treatment-alis-brow-grow/` kemungkinan adalah alias/redirect dari `brow-grow/` (canonical). Sebelum generate schema, verifikasi dulu apakah URL ini return 200 atau 301/302 ke canonical. Jika 301, cukup satu schema di URL canonical — tidak perlu duplikasi. Schema.org duplicate content pada URL berbeda meski dengan entity sama tetap memicu warning.

## **5.44 List Tim Dokter — SELESAI (terpasang WPCode)**

URL: https://sozoskinclinic.com/tim-dokter-sozo-skin/

File: `dokter/schema-markup-dokter.html` (schema) + `dokter/index.html` (layout kartu 5 dokter).

Struktur `@graph` (4 node):

* **CollectionPage** — `#webpage`, pointer `isPartOf` → `#website`, `about` → `#organization`, `breadcrumb` → `#breadcrumb`, `mainEntity` → `#itemlist`.

* **BreadcrumbList** — Home › Tim Dokter Sozo Skin Clinic (2 level, URL flat).

* **ItemList** — 5 ListItem sesuai urutan kartu (Elisabeth, Gesha, Audi, Putri, Syerli). Tiap `item` = `Person` ringan: `name`, `jobTitle`, `url`, `image`, `worksFor` → `#organization`. STR sengaja TIDAK masuk `identifier` karena nomor dr. Putri disensor di UI.

* **WebSite** — pointer standar.

**Catatan penting — pilihan tipe: `CollectionPage` bukan `WebPage`.** Halaman index profil dokter = kumpulan item → subtype `CollectionPage` tepat. Validator schema.org: 0 error, 0 warning. Snippet WPCode kondisi: **Page URL contains `tim-dokter-sozo-skin`**.

## **5.45 Detail Dokter (dr. Putri) — SCHEMA SIAP, WPCODE BELUM**

URL: https://sozoskinclinic.com/tim-dokter-sozo-skin/dr-rr-putri-rizkya/

File: `dokter/dr-putri/schema-markup.html` (schema) + `dokter/dr-putri/dr-putri.html` (layout profil).

Struktur `@graph` (4 node):

* **ProfilePage** — `#webpage`, pointer lengkap, `mainEntity` → `#person`.

* **BreadcrumbList** — Home › Tim Dokter › dr. RR. Putri Rizkya (3 level).

* **IndividualPhysician** — `"@type": ["Person", "IndividualPhysician"]` (array WAJIB: `IndividualPhysician` hanya turunan `Organization`, properti `jobTitle`/`worksFor`/`alumniOf`/`honorificPrefix` baru valid dengan `Person` di array). Berisi `honorificPrefix`, `jobTitle`, `url`, `image`, `description`, `worksFor` + `practicesAt` → `{"@type": "MedicalOrganization", "@id": "...#organization"}` (referensi wajib diberi `@type` eksplisit agar validator tidak membaca target sebagai `Thing` polos), `alumniOf`, `knowsAbout`, `award`.

* **WebSite** — pointer standar.

**Catatan penting:**

* `@id` Person di halaman ini **identik** dengan referensi `#person` di halaman list (5.44) — entitas menyambung antar halaman.

* `medicalSpecialty` TIDAK dipakai — expected type-nya enumerasi `MedicalSpecialty`, string bebas (mis. "Anti-Aging & Aesthetic Medicine") memicu warning validator. `knowsAbout` menutupi fungsinya.

* STR tidak dimasukkan `hasCredential` — nomor dr. Putri disensor di UI; keputusan dihormati. Untuk dokter yang STR-nya tampil penuh (Syerli, Eli, Gesha, Audi), `hasCredential` + `EducationalOccupationalCredential` (credentialCategory: STR, + `recognizedBy` → KKI) bisa ditambahkan.

* Validasi: JSON valid (parse OK), target 0 error 0 warning di validator.schema.org. Snippet WPCode kondisi: **Page URL contains `dr-rr-putri-rizkya`**.

## **5.46 Editorial Board — SELESAI (schema lengkap)**

URL: https://sozoskinclinic.com/editorial-board/

File: `editorial-board.html`. Struktur `@graph` (3 node):

* **AboutPage** — `#webpage`, pointer `isPartOf` → `#website`, `about` → `#organization`, `breadcrumb` → `#breadcrumb`, `reviewedBy` (3 Person: Audi, Gesha, Elisabeth).

* **BreadcrumbList** — Home › Tim Editorial (2 level).

* **WebSite** — pointer standar.

Nama 3 dokter reviewer di section HTML juga di-link ke halaman profil masing-masing (`/tim-dokter-sozo-skin/[slug]/`) untuk distribusi authority internal. JSON valid.

## **5.48 Detail Dokter (dr. Syerli) — SCHEMA SIAP, WPCODE BELUM**

URL: https://sozoskinclinic.com/tim-dokter-sozo-skin/dr-syerli-rahmadeni/

File: `dokter/dr-sherly/schema-markup.html` (schema) + `dokter/dr-sherly/sherly.html` (layout profil).

Struktur `@graph` (4 node) — pola identik §5.45 (ProfilePage + BreadcrumbList 3 level + `["Person", "IndividualPhysician"]` + WebSite), dengan perbedaan penting:

* **`hasCredential` diisi** — STR `1321100222169296` tampil penuh di UI, sehingga masuk `EducationalOccupationalCredential` (credentialCategory: "STR (Surat Tanda Registrasi)", `identifier`, `recognizedBy` → KKI). Ini sinyal E-E-A-T terkuat di profil dokter.
* `knowsAbout` — Facial Aesthetics, Skin Aging Rejuvenation, Facial Contouring, Injectable, Energy-Based Device (dari profil).
* `award` — 6 pelatihan/seminar dari konten halaman.

Snippet WPCode kondisi: **Page URL contains `dr-syerli-rahmadeni`**.

## **5.49 Detail Dokter (dr. Elisabeth Ryan) — SCHEMA SIAP, WPCODE BELUM**

URL: https://sozoskinclinic.com/tim-dokter-sozo-skin/elisabeth-ryan/

File: `dokter/dr-eli/schema-markup.html` (schema) + `dokter/dr-eli/index.html` (layout profil).

Struktur `@graph` (4 node) — pola identik §5.48 (ProfilePage + BreadcrumbList 3 level + `["Person", "IndividualPhysician"]` + WebSite). Keunikan vs profil lain:

* **`memberOf` diisi** — 6 organisasi (PERDOSKI, KSDLI, KSDNI, IDS, ISD, IDI Jakarta Pusat) masuk `Person.memberOf`. Ini sinyal E-E-A-T keanggotaan profesi, belum dipakai di profil lain.
* `alumniOf` 4 entri — UKRIDA (sarjana + profesi), RITM Muntinlupa (residensi), Medical University of Warsaw (fellowship trikoskopi), UI (program adaptasi).
* `hasCredential` — STR `STRUI00001652595312` tampil penuh di UI.
* `award` — 6 dari 9 pencapaian & publikasi terpilih (publikasi jurnal + beasiswa World Congress of Dermatology). 50+ pelatihan/seminar tidak dimasukkan ke schema (noise).
* `knowsAbout` mencakup Trichoscopy + Hair and Scalp Health — pembeda positioning vs dokter lain.

Snippet WPCode kondisi: **Page URL contains `elisabeth-ryan`**.

## **5.50 Detail Dokter (dr. Audi Sugiharto) — SCHEMA SIAP, WPCODE BELUM**

URL: https://sozoskinclinic.com/tim-dokter-sozo-skin/audi-sugiharto/

File: `dokter/dr-audi/schema-markup.html` (schema) + `dokter/dr-audi/index.html` (layout profil).

Struktur `@graph` (4 node) — pola identik §5.48. Keunikan:

* **Satu-satunya dokter bersertifikat Sp.D.V.E** — `honorificSuffix: "Sp.D.V.E"` dipakai (profil lain pakai Sp.DVE / tanpa).
* `alumniOf` 3 entri — UKRIDA (sarjana & profesi 2005–2011), RITM Manila (residensi 2016–2019), Universitas Andalas / RSUP Dr. M. Djamil (program adaptasi 2022–2023).
* `hasCredential` — STR `VP00000073538568` tampil penuh di UI.
* `award` — 3: peringkat 8 Ujian Board Nasional Kolegium DV Indonesia (2023), Basic Surgical Skill Workshop (2022), COSMIC Hands-On Workshop (2022).
* `knowsAbout` mencakup Skin Surgery dan Cosmetic Surgery — pembeda positioning (bedah kulit).

Snippet WPCode kondisi: **Page URL contains `audi-sugiharto`**.

## **5.51 Detail Dokter (dr. Gesha Kautzar Putri) — SCHEMA SIAP, WPCODE BELUM**

URL: https://sozoskinclinic.com/tim-dokter-sozo-skin/gesha-kautzar-putri/

File: `dokter/dr-gesha/schema-markup.html` (schema) + `dokter/dr-gesha/index.html` (layout profil).

Struktur `@graph` (4 node) — pola identik §5.48. Keunikan:

* **Satu-satunya dokter non-spesialis kulit** — `jobTitle: "Medical Aesthetic Physician"` (identik dengan halaman list, bukan Dermatologist).
* `honorificSuffix: "M.Biomed (AAM)"` — gelar magister + sertifikasi American Academy of Aesthetic Medicine.
* `alumniOf` 2 entri — Universitas Tarumanagara (S1 Kedokteran 2013), Universitas Udayana (S2 Anti Aging Medicine 2018).
* `memberOf` 3 organisasi — PERDAWERI DKI Jakarta (Bendahara), IKLASI, IDI Jakarta Selatan.
* `hasCredential` — STR `KT00000123255767` tampil penuh di UI.
* `award` — 5 pelatihan & seminar (AMUSE 2026, IMCAS Bangkok 2025, IBSA DERMA Italia 2025, ISWAM 2025, Pharmaresearch Korea 2025).
* `knowsAbout` paling luas (8 item) — termasuk Slimming Consultation, pembeda positioning (prosedur non-kulit).

Snippet WPCode kondisi: **Page URL contains `gesha-kautzar-putri`**.

## **5.52 Korean LHALA Peel — SCHEMA SIAP**

URL: https://sozoskinclinic.com/skin-treatment/derma-peel-treatment/korean-lhala-peel/

File: `treatment/skin-treatment/derma-peel-treatment/korean-lhala-peel/schema-markup.html` (schema) + `index.html` (layout LP). Folder asli `treatment/peel-treatment/` dipindah ke jalur `skin-treatment/derma-peel-treatment/` agar konsisten dengan struktur LP peel lain.

Struktur `@graph` (4 node) — pola standar Derma Peel:

* `MedicalWebPage` (specialty Dermatology, isPartOf → #website, about → #organization, potentialAction ReadAction).
* `BreadcrumbList` 4-level: Home → Skin Treatment → Derma Peel → Korean LHALA Peel.
* `Service` + `offers` → `PriceSpecification` Rp 299.000 (entry-level, setara Acne/Glow Peel), `priceValidUntil: 2026-12-31`, `valueAddedTaxIncluded`.
* `FAQPage` 15 FAQ — paling banyak di rumpun Derma Peel.

Catatan QC: class wrapper LP memakai `sozo-sku-korean-lhala-wrapper` (bukan sisa nama Filler), alt text & link kartu terkait sudah menunjuk Glow Peel, Acne Peel, Dazzling Glow Peel yang benar.

Snippet WPCode kondisi: **Page URL contains `korean-lhala-peel`**.

## **5.47 URL Baru Audit 5 Agustus 2026 — README SYNC**

Daftar URL terbaru dari tim vs isi README.md. Hasilnya, 10 URL baru ditambahkan ke README + anomali ejaan dicatat:

* **Baru ditambahkan ke README.md** (semua status BELUM, schema belum dibuat):
  - `injectable-treatment/zo-tox-treatment/zo-tox-10u/`, `zo-tox-premium/` (sub Zo-Tox)
  - `injectable-treatment/threadlift-treatment/facelift/`, `perfect-facelift/`, `perfect-nose-job/`
  - `injectable-treatment/filler-treatment/premium-filler/`
  - `injectable-treatment/infus-whitening-treatment/premium-glow-infusion/`
  - `hifu-treatment/signature-hifu/`
  - `blog/` (masuk checklist README §1)
  - `tim-dokter-sozo-skin/` + 5 detail dokter (section baru README §9)
* **Anomali ejaan di daftar URL user** (dokumentasi memakai versi benar):
  - `ultrascrupt-treatment/` → benar `ultrasculpt-treatment/` (sudah dikoreksi README §4 sebelumnya)
  - `salmon-dna-hair/` → benar `salmon-dna-hair-treatment/` (konsisten repo)
  - `subsicion-treatment/` → benar `subcision-treatment/` (typo dobel 's')
  - `meso-bloataway/` (dengan 'a') — URL live benar; folder lokal `meso-bloatway` (tanpa 'a'), penulisan konsisten ke URL live
  - `hifu-treatment/liftera-hifu/` — URL live benar; folder lokal `lifetra-hifu` (typo), penulisan konsisten ke URL live
* **Catatan — daftar user menghilangkan canonical `brow-grow/`** (`hair-treatment/brow-grow/`). URL itu tetap canonical untuk Treatment Alis (lihat §5.43); alias `treatment-alis-brow-grow/` tetap tercatat di README.

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
| Pores Treatment | 1 | SELESAI |
| PRP Treatment (Scar) | 1 | SELESAI |
| Rejuran Scar Treatment | 1 | SELESAI |
| Restylane Scar | 1 | SELESAI |
| Subcision Treatment | 1 | SELESAI |
| Salmon DNA Hair Treatment | 1 | SELESAI |
| Express Hair Therapy | 1 | SELESAI |
| **Batch 10 Juli 2026 — Slimming/RF/Meso** | **8** | **SELESAI** (Meso Slim Body, V Line, Bloatway, Cellulift, Metabolic Boost, Liftera HIFU, RF Face, RF Body) |
| **Batch 10 Juli 2026 — Injectable** | **20** | **SELESAI** (Filler Dagu, Korean Filler, Tanam Benang Hidung, Infus Vitamin C, 16 Skin Booster) |
| **Batch 10 Juli 2026 — Advanced Skin** | **6** | **SELESAI** (IPL Acne, IPL Glow, Acne Peel, Glow Peel, Dazzling Glow Peel, Eternal Bloom Peel) |
| Pore Detox Treatment | 1 | SCHEMA SIAP, WPCODE BELUM (lihat §5.38) |
| Collagen Mask Treatment | 1 | SCHEMA SIAP, WPCODE BELUM (lihat §5.39) |
| Rejuran Skin Booster | 1 | SCHEMA SIAP, WPCODE BELUM (lihat §5.40) |
| **List Tim Dokter** | 1 | **SELESAI** — terpasang WPCode, valid 0 error (lihat §5.44) |
| **Detail Dokter (dr. Putri)** | 1 | SCHEMA SIAP, WPCODE BELUM (lihat §5.45) |
| **Detail Dokter (dr. Syerli)** | 1 | SCHEMA SIAP, WPCODE BELUM (lihat §5.48) — hasCredential STR penuh |
| **Detail Dokter (Eli, Audi)** | 2 | SCHEMA SIAP, WPCODE BELUM (lihat §5.49–§5.50) |
| **Detail Dokter (Gesha)** | 1 | SCHEMA SIAP, WPCODE BELUM (lihat §5.51) |
| **Korean LHALA Peel** | 1 | SCHEMA SIAP, WPCODE BELUM (lihat §5.52) |
| **Editorial Board** | 1 | **SELESAI** — AboutPage + reviewedBy + BreadcrumbList (lihat §5.46) |
| DPL Treatment | 1 | Belum ada schema — lihat §5.42 |
| Treatment Alis Brow Grow (alias URL) | 1 | Verifikasi canonical dulu — lihat §5.43 |
| Sublemen Pelangsing (landing page) | 1 | Belum ada schema |
| Halaman cabang baru: Tangerang (3 sub), Palembang, Pekanbaru, Manado, Batam | 7 | Belum ada schema |
| Zo-Tox 10U, Zo-Tox Premium, Facelift, Perfect Facelift, Perfect Nose Job, Premium Filler, Premium Glow Infusion, Signature HIFU | 8 | Belum ada schema — URL baru §5.47 |
| Halaman treatment lain (LP) | \~64 | Belum — pakai template Hair Removal |
| Benerin nama breadcrumb "SEO –" | 114 halaman | Belum (jika masih relevan setelah custom) |
| Schema blog / Article | — | Belum — pertimbangkan dynamic PHP |
| Schema cabang (LocalBusiness) | 60+ cabang | Belum |
| Refactor IPL Acne ke PriceSpecification | 1 | Inkonsisten dengan standar tim — bukan error, hanya stylistic |

## **Template untuk Halaman Treatment Berikutnya**

Gunakan schema Hair Removal sebagai template. Yang perlu diganti per halaman:

* URL halaman (di @id, url, breadcrumb, ReadAction).

* name & description (judul \+ meta halaman).

* Breadcrumb path (sesuai hierarki konten).

* Daftar sub-treatment \+ harga di hasOfferCatalog.

* Daftar FAQ (dari konten halaman tsb).

---

# **9. Ekstensi Dokumentasi (Update 13 Juli 2026)**

Sejak 13 Juli 2026, scope dokumentasi meluas dari schema markup saja menjadi dokumentasi UI & SEO lengkap. Folder `docs/` adalah ekstensi modular dari tiga dokumen utama (README.md, AGENTS.md, Dokumentasi.md).

## **9.1 docs/adr/ — Architecture Decision Records**

Berisi keputusan teknis beserta reasoning (kenapa, bukan cuma apa). Setiap keputusan yang mengubah aturan main layak jadi ADR. Format mengikuti Michael Nygard (Context, Decision, Consequences, Alternatif yang dipertimbangkan).

7 ADR saat ini:

| # | Judul | Topik |
| :-- | :-- | :-- |
| 0001 | Disable Yoast schema output | Schema disable global via PHP filter |
| 0002 | Service not Product | Schema type untuk halaman treatment |
| 0003 | Homepage as knowledge hub | Arsitektur `Organization` + `WebSite` |
| 0004 | PriceSpecification wrapper | Default pattern `Service.offers` |
| 0005 | Keep FAQ after rich result removal | FAQPage tetap dipasang |
| 0006 | Skip Yoast breadcrumb | Breadcrumb sepenuhnya custom |
| 0007 | Two-file convention | `index.html` + `schema-markup.html` per treatment |

Kandidat ADR berikutnya (perlu konfirmasi): unifikasi warna tombol WhatsApp (`#1A237E` production vs `#1A2080` `--sozo-blue`); accordion SKU-reference.

## **9.2 docs/components/ — UI Component Specs**

Standar atom/molekul UI yang dipakai di LP treatment. 5 specs saat ini:

- `button.md` — Primary (pill), Secondary, Ghost, Link, Icon-only. Brand tokens dari navbar: `--sozo-blue: #1A2080`, Inter font, pill 50px untuk primary.
- `table.md` — Pricing, Comparison, Info row. Cell padding 14×20, container 12px rounded.
- `card.md` — Doctor, Testimoni, Lokasi, Treatment card. Hover lift -2px + shadow.
- `accordion.md` — Single-open default untuk FAQ. SKU reference pending.
- `whatsapp-button.md` — Nav (pill, dari production navbar) + Floating (circle, rekomendasi).

**Prinsip utama:** Standarisasi di level atom/molekul, bukan di level section/organisme. Hero, info-row, dan section-level lain **dibiarkan bervariasi** antar LP — yang distandardisasi adalah komponen penyusunnya (button, image, badge, dll). Komposisi section bebas, visual consistency terjaga di level atom.

## **9.3 Audience dan Use Case**

- **Developer yang maintain LP existing** — lihat `docs/components/` untuk standar UI, AGENTS.md untuk schema rules, README untuk status per-LP.
- **Developer/AI yang generate LP/schema baru** — lihat README §2 SOP + AGENTS.md + ADR yang relevan dengan tipe halaman.
- **Designer** — lihat `docs/components/` untuk standar komponen, color/font tokens ada di button.md dan component specs lainnya.

## **9.4 Item Lintas yang Sedang Berjalan**

- Unifikasi warna tombol WhatsApp (`#1A237E` production → `var(--sozo-blue)`).
- Accordion spec refinement (perlu SKU page code sebagai referensi).
- Per-LP manifest format (perlu pilot 1-2 LP beneran untuk validasi).
- Brand tokens + font rules didokumentasikan di `docs/design-system/colors-typography.md` (v1.1, 6 Agustus 2026) — diselaraskan dengan Elementor global (Primary Font Inter, heading H1–H6 Poppins).

Update perubahan di tiga dokumen utama tracked via version bump: README 1.7 → 1.8, Dokumentasi 1.6 → 1.7, AGENTS.md (no version sebelumnya) → section 1.7 baru ditambahkan.

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