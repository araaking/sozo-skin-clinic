# ADR-0001: Matikan Output JSON-LD Yoast SEO

- **Status:** Accepted
- **Tanggal:** 2026-07-13 (dicatat retrospektif)
- **Sumber:** Dokumentasi.md §1, §5.1

## Context

Yoast SEO men-generate JSON-LD default (Organization, WebSite, Breadcrumb, Article) untuk setiap halaman. Ketika tim mulai menambahkan custom schema via JS injection, dua sumber schema berjalan bersamaan. Audit Screaming Frog menunjukkan:

- 47 dari 132 halaman punya `BreadcrumbList` dobel.
- 114 halaman punya nama breadcrumb berawalan "SEO –" / "SEO |" karena Yoast menarik nama dari SEO title, bukan hierarki URL.
- 218 rich result error, mayoritas karena halaman treatment di-markup sebagai `Product`/`Offer`.
- Konflik `inLanguage` (en-US dari Yoast vs id dari custom) dan `datePublished` (2021 vs 2022).

Override level field bukan solusi: Yoast meregenerasi field setiap save, dan override tidak selesaikan duplikasi struktur.

## Decision

Matikan seluruh output JSON-LD Yoast via WPCode snippet PHP:

```php
add_filter( 'wpseo_json_ld_output', '__return_false' );
```

Snippet dipasang sekali (Code Type: PHP, Location: Run Everywhere). Schema dikontrol manual per halaman via WPCode HTML snippet di head.

## Consequences

**Positif:**
- Tidak ada duplikasi BreadcrumbList atau konflik data antar sumber.
- Breadcrumb bisa disesuaikan dengan hierarki URL flat (mis. `/treatment/`, `/skin-treatment/`).
- Perubahan brand-wide (alamat, telepon, logo) cukup dilakukan di satu node Organization di homepage.
- Struktur schema bisa dioptimasi per tipe halaman tanpa intervensi Yoast.

**Negatif:**
- Schema harus dibuat dan dipelihara manual per halaman (~137 halaman; ~72 selesai, ~65+ belum per §5.42 Dokumentasi.md).
- Kehilangan fallback otomatis Yoast untuk Organization/WebSite/Article.
- WPCode snippet harus di-sync manual saat konten berubah (harga, FAQ, dll).

**Alternatif yang dipertimbangkan:**
- *Field-level override* — mempertahankan Yoast, override field tertentu. Ditolak: Yoast regenerate field tiap save, override tidak selesaikan duplikasi.
- *Disable per page type* — menyembunyikan schema Yoast hanya di halaman tertentu. Ditolak: tidak menyelesaikan kasus cross-page (Organization tetap muncul di mana-mana).
