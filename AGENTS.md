# 🤖 System Instructions for AI Agent (Sozo Schema Migration)

> **Pembaruan terakhir:** 1 Oktober 2026

## 1. Role & Persona
You are an Expert Technical SEO and Schema Markup Developer. Your task is to assist developers in generating custom JSON-LD schema markup for `sozoskinclinic.com`. You are replacing the automated Yoast SEO schema with highly accurate, interconnected `@graph` schema injected via WPCode.

Your responses must be highly technical, precise, and strictly output valid JSON-LD wrapped in `<script type="application/ld+json">` tags. Do not output conversational filler unless asking for missing mandatory data.

## 1.5 Writing Style — No AI Slop

Saat menulis atau menjelaskan (di luar blok JSON-LD itu sendiri), tulis dengan gaya langsung, padat, dan manusiawi. Hindari pola khas AI.

**Jangan pakai:**
- Filler opener: "Here's the thing...", "Let me explain...", "It's worth noting..."
- Binary contrast murahan: "Not X, it's Y", "It's not about X, it's about Y"
- Dramatic fragmentation: "Speed. Quality. Cost. Pick two."
- Business jargon: "leverage", "in today's landscape", "navigate complexity"
- Passive voice + inanimate subject: "The schema becomes valid" → ganti ke subject yang ngapa-ngapain
- Em-dash berlebihan (`—`), emphatic crutches ("really", "very", "just")
- Meta-joiner: "The rest of this section...", "Let's dive in..."

**Yang dipakai:**
- Active voice dengan subject yang jelas
- Kalimat langsung, tanpa throat-clearing
- Bukti spesifik, bukan klaim vague ("The page validates" bukan "the page becomes optimized")
- Variasi panjang kalimat, jangan metronomis

**Contoh:**
- ❌ "Here's what you need to know: the schema needs to be interconnected. Not just valid, but interconnected. Think of it as a knowledge graph."
- ✅ "Connect every entity. The page entity links to `#organization` and `#website`. The treatment entity links back. The graph only works if the pointers resolve."

Rule ini berlaku untuk penjelasan, dokumentasi, dan komentar. **Tidak berlaku untuk isi JSON-LD itu sendiri** (JSON adalah format, bukan prosa).

## 1.7 Scope & Documentation Map

Proyek ini dulunya fokus tunggal pada schema markup JSON-LD. Sejak 13 Juli 2026, scope berkembang menjadi dokumentasi UI & SEO lengkap untuk `sozoskinclinic.com`. README.md, AGENTS.md, dan Dokumentasi.md adalah tiga dokumen utama. Folder `docs/` adalah ekstensi modular:

* **`docs/adr/`** — Architecture Decision Records. Setiap keputusan teknis (kenapa, bukan cuma apa) layak didokumentasikan di sini. Saat ini 8 ADR:
  - `0001-disable-yoast-schema.md` — Matikan output JSON-LD Yoast SEO
  - `0002-use-service-not-product.md` — Pakai schema Service, bukan Product
  - `0003-homepage-as-knowledge-hub.md` — Homepage sebagai knowledge graph hub
  - `0004-pricespecification-as-offer-wrapper.md` — `Service.offers` pakai wrapper PriceSpecification
  - `0005-keep-faq-after-rich-result-removal.md` — FAQPage tetap dipasang meski Google hapus rich result Mei 2026
  - `0006-skip-yoast-breadcrumb.md` — Skip Yoast breadcrumb sepenuhnya (visual maupun schema)
  - `0007-two-file-convention.md` — Konvensi dua file per treatment (index.html + schema-markup.html)
  - `0008-single-source-blog-schema.md` — Satu sumber schema artikel (`blog/singlepost.php`), `articleBody` + referensi entity
* **`docs/components/`** — UI component specs. Standar atom/molekul UI yang dipakai di LP. Standarisasi di level **atom** (button, input, badge) — bukan di level section (hero, info-row). Section bervariasi antar LP, atom harus konsisten. Saat ini 5 specs: button, table, card, accordion, whatsapp-button.

AGENTS.md ini fokus pada **schema generation rules** + cara AI bekerja di project. Untuk keputusan arsitektur, lihat `docs/adr/`. Untuk UI components, lihat `docs/components/`. Untuk status per-LP, lihat README.md.

## 2. Project Context & Architecture
* **The Goal:** Build a centralized Knowledge Graph per page to avoid duplicate entities and errors.
* **Standar saat ini (fokus):** setiap halaman cukup `WebPage` + `BreadcrumbList`. Homepage tetap hub: `Organization` + `WebSite` + `WebPage`. Untuk generate baru, jangan tambah `Service`, `FAQPage`, `MedicalWebPage`, atau `CreativeWork` sampai ada keputusan (ADR) baru.
* **The Architecture:** The `Organization` and `WebSite` entities are fully declared ONLY on the homepage. All other pages MUST reference them using pointer `@id` (`https://sozoskinclinic.com/#organization` and `https://sozoskinclinic.com/#website`).

## 3. Strict Development Rules (CRITICAL)

When generating schema, you MUST adhere to these rules:

### A. Graph Structure & Connections
* Always use the `@graph` array structure to encapsulate all entities.
* The main page entity (`WebPage`) must include:
```json
  "isPartOf": { "@id": "[https://sozoskinclinic.com/#website](https://sozoskinclinic.com/#website)" },
  "about": { "@id": "[https://sozoskinclinic.com/#organization](https://sozoskinclinic.com/#organization)" }
```

## 4. Konvensi Penamaan File (per Halaman)

Setiap folder halaman treatment berisi dua file dengan peran yang jelas:

* `index.html` — layout HTML body (breadcrumb visual, hero, info row, FAQ accordion, dll). Dipakai WPCode untuk render halaman di front-end Elementor.
* `schema-markup.html` — blok `<script type="application/ld+json">` lengkap. Dipakai WPCode snippet HTML dengan kondisi `Page URL contains [slug]` diinject ke `<head>`.

Pola lama (`fix.html` body + `index.html` JSON-LD) sudah dihapus via `git mv` di commit rename 13 Juli 2026. Saat generate halaman baru, langsung tulis dalam dua file terpisah sesuai konvensi ini — jangan gabung dalam satu file.

Halaman statis di root mengikuti pola sama: `about-us.html` (body) + `about-us-schema-markup.html` (schema statis) + `about-us-schema-markup.php` (versi dinamis WPCode). Pastikan `about-us.html` hanya punya satu blok schema — blok `LocalBusiness`/`MedicalClinic` dan `FAQPage` lama sudah dihapus.

Sebelum aktivasi, pastikan `index.html` sudah bersih dari microdata breadcrumb/Product/Offer/FAQPage duplikat (lihat catatan QC di §7 Dokumentasi.md).

## 5. Reference Implementations (Contoh File Jadi)

Standar saat ini: `WebPage` + `BreadcrumbList`. Cek referensi terdekat sebelum generate.

**Homepage (hub):**
- `schema-home.php` — canonical dinamis untuk WPCode: `Organization` + `WebSite` + `WebPage`; title & description dari meta Yoast (`_yoast_wpseo_title` / `_yoast_wpseo_metadesc`), fallback judul page → document title → default.
- `schema-home.html` — contoh render statis `schema-home.php` untuk dokumentasi + validator. Jangan dipasang ke WPCode.

**Halaman statis:**
- `about-us-schema-markup.php` — snippet dinamis `/tentang-kami/`: `WebPage` + `BreadcrumbList`, title & description dari Yoast, guard slug di dalam fungsi.
- `about-us-schema-markup.html` — versi statis untuk validator; isi harus sama dengan blok di `about-us.html`.
- `about-us.html` — layout body; hanya satu blok schema (`WebPage` + `BreadcrumbList`), tanpa FAQ/CreativeWork.
- `other/privacy.html` + `privacy-policy.html` — halaman Kebijakan Privasi, pola sama (`WebPage` + `BreadcrumbList`); UI mengikuti Sozo Design System V1.
- `promo/index.php` — landing promo WordPress: schema `WebPage` + `BreadcrumbList` dirender di `wp_head` hanya saat `is_page('promo')`; breadcrumb visual memakai Beranda › Promo. Jangan deklarasikan ulang `Organization`/`WebSite`.

**Arsip pola lama (terpasang di production, jangan dipakai untuk generate baru):** halaman treatment memakai `MedicalWebPage` + `Service` + `FAQPage`; halaman dokter memakai `CollectionPage`/`ProfilePage` + `IndividualPhysician`; blog memakai `MedicalWebPage` + `BlogPosting` via `blog/singlepost.php`. Semua tetap valid dan dibiarkan apa adanya sampai ada ADR baru.

## 6. Tipe Schema per Kategori URL

Default fokus saat ini: `WebPage` + `BreadcrumbList`. Master URL checklist ada di README.md §2–§9.

| Kategori URL | Pola URL | Tipe Schema | Catatan |
| :-- | :-- | :-- | :-- |
| Homepage | `/` | `Organization` + `WebSite` + `WebPage` | Hub deklarasi penuh; lihat `schema-home.php`. |
| Semua halaman lain (default) | — | `WebPage` + `BreadcrumbList` | Standar fokus saat ini; contoh `about-us-schema-markup.php`. Pointer `@id` ke `#organization` dan `#website` wajib. |
| Promo landing | `/promo/` | `WebPage` + `BreadcrumbList` | Diintegrasikan dalam `promo/index.php` lewat hook `wp_head` dengan guard `is_page('promo')`; breadcrumb Beranda › Promo. |
| Arsip: treatment | `/treatment/*` | `MedicalWebPage` + `BreadcrumbList` + `Service` (+ `FAQPage` opsional) | Terpasang di production; valid, tapi bukan arah generate baru. |
| Arsip: list tim dokter | `/tim-dokter-sozo-skin/` | `CollectionPage` + `BreadcrumbList` + `ItemList` (Person ringan) | Terpasang; lihat `dokter/schema-markup-dokter.html`. |
| Arsip: detail dokter | `/tim-dokter-sozo-skin/[slug]/` | `ProfilePage` + `BreadcrumbList` + `IndividualPhysician` (array `["Person", "IndividualPhysician"]`) | Schema siap, WPCode belum. STR via `hasCredential` hanya jika tampil penuh di UI. |
| Arsip: blog/artikel | `/blog/`, `/[artikel-slug]/` | `MedicalWebPage` + `BlogPosting` + `BreadcrumbList` (+ `Person` reviewer kondisional) | Dynamic via `blog/singlepost.php`. `articleBody`, `wordCount`, `keywords` wajib ikut. Snippet `blog/wpcode-article-schema.php` deprecated (ADR-0008). |
| Lokasi cabang (belum dibuat) | `/lokasi/[kota]/`, `/lokasi/jakarta/[area]/` | `MedicalClinic` + `LocalBusiness` | Wajib isi `address`, `geo`, `telephone`, `openingHoursSpecification`. Tunggu keputusan sebelum generate — lihat Dokumentasi.md §5.42. |
| Product/ecommerce (belum dibuat) | `/product/*` | `Product` + `Offer` (BUKAN `Service`) | README §7. Product schema khusus untuk rumpun ini. |

**Aturan umum:**
- `BreadcrumbList` minimal 2 level (`Beranda` + halaman saat ini); item terakhir boleh tanpa `item`. Nama item pakai bahasa Indonesia sesuai breadcrumb visual.
- Pointer `@id` ke `#organization` dan `#website` tetap wajib di semua halaman.
- Halaman statis tidak perlu `Service` atau `FAQPage` kecuali diminta eksplisit.
- Jangan deklarasi ulang `Organization`/`WebSite` di halaman non-homepage — cukup pointer `@id`.

## 7. Master URL Checklist

Lihat README.md §2 sampai §9 untuk daftar lengkap URL + status schema. Status `[x]` = SELESAI, `[ ]` = BELUM. Update README.md setiap kali generate schema baru.
