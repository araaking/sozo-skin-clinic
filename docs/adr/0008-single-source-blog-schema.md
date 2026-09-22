# ADR-0008: Satu Sumber Schema Artikel Blog (`blog/singlepost.php`)

- **Status:** Accepted
- **Tanggal:** 2026-09-21
- **Sumber:** `blog/singlepost.php`; audit schema artikel 21 September 2026

## Context

Sampai 21 September 2026 ada dua snippet yang sama-sama meng-output schema artikel:

1. `blog/wpcode-article-schema.php` — hook `wp_head`, output `Article` + `BreadcrumbList` (breadcrumb 4 level: Home > Blog > Kategori > Judul).
2. `blog/singlepost.php` — shortcode `[sozoskin_single]`, output `MedicalWebPage` + `BlogPosting` + `Person` reviewer + `BreadcrumbList` (3 level: Beranda > Kategori > Judul).

Kalau keduanya aktif, satu artikel punya dua node `#article` dengan tipe berbeda, dua `BreadcrumbList` dengan hierarki berbeda, dan `about` yang saling bertentangan.

Tiga masalah lain ditemukan saat audit:

- `BlogPosting` tidak membawa isi artikel. `articleBody` dan `wordCount` absen, padahal `$word_count` sudah dihitung untuk `timeRequired`. Tag artikel juga tidak masuk `keywords`.
- `#organization` dan `#website` dideklarasikan penuh di setiap artikel dengan definisi yang berbeda dari homepage: `@type` berbeda (`Organization` vs `[MedicalClinic, Organization]`), logo berbeda, URL Facebook berbeda. Google merge by `@id`, definisi konflik memecah entity. Ini juga melanggar AGENTS.md §2.
- Saat `sozo_reviewer_key` kosong, UI menampilkan "Tim Medis Sozo Skin Clinic" tapi schema menunjuk `dr. Elisabeth Ryan`. Structured data tidak cocok dengan konten yang terlihat.

## Decision

- **Satu sumber:** `blog/singlepost.php` adalah satu-satunya sumber schema artikel. `blog/wpcode-article-schema.php` dijadikan tombstone tanpa kode; kalau snippet itu masih aktif di WPCode, matikan.
- **Isi artikel masuk schema:** `articleBody` dari `post_content` yang di-strip tag + shortcode dan dinormalisasi whitespace, plus `wordCount`, `keywords` dari tag, `url`, `isAccessibleForFree`.
- **Topik medis pindah ke `BlogPosting.about`** (`Thing` dengan `name` dari `sozo_medical_topic`). `MedicalWebPage.about` mengarah ke `#organization` sesuai AGENTS.md §3 A.
- **Halaman artikel hanya mereferensikan** `#organization` dan `#website` lewat `@id`. Deklarasi penuh tetap di homepage.
- **Reviewer adalah Person hanya kalau spesifik:** key registry valid, atau post lama punya `sozo_reviewer_schema_name` + `sozo_reviewer_schema_url`. Selain itu `reviewedBy` mengarah ke `#organization` (`MedicalOrganization`), bukan nama dokter yang tidak tampil di UI.

## Consequences

**Positif:**
- Tidak ada lagi node `#article` ganda. Breadcrumb schema konsisten 3 level di semua artikel.
- Mesin pencari dan LLM menerima isi artikel utuh, bukan cuma headline + 28 kata deskripsi.
- Entity `#organization` hanya punya satu definisi otoritatif, tidak ada konflik `@type`/logo/sameAs.
- Nama reviewer di schema selalu sama dengan yang tampil di UI.

**Negatif:**
- `articleBody` menggandakan isi artikel di HTML (konten tampil + JSON-LD). Halaman artikel jadi lebih berat; trade-off yang diambil untuk keterbacaan mesin.
- Halaman artikel bergantung pada resolusi `@id` lintas halaman ke homepage. Kalau schema homepage berubah/hilang, referensi jadi dangling (pola yang sama sudah dipakai halaman treatment).
- `reviewedBy` ke `#organization` lebih lemah untuk sinyal E-E-A-T dibanding nama dokter. Mitigasi: pilih reviewer dari dropdown di post editor supaya keluar sebagai Person.

**Mitigasi operasional:**
- Setelah deploy, buka 2-3 artikel di Rich Results Test dan pastikan hanya ada satu `BlogPosting` + satu `BreadcrumbList`.
- Verifikasi kekuatan entity via validator.schema.org: `#organization` di artikel harus resolve ke definisi homepage.

**Alternatif yang dipertimbangkan:**
- *Pertahankan kedua snippet* — ditolak: duplikat `#article` dan breadcrumb.
- *Tanpa `articleBody`* — ditolak: isi artikel tidak terbaca crawler LLM; justru ini tujuan audit.
- *Tetap deklarasi `#organization` di artikel* — ditolak: bentrok definisi dengan homepage dan melanggar arsitektur.
