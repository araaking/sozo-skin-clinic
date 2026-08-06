# ADR-0006: Skip Yoast's Breadcrumb Sepenuhnya (Visual Maupun Schema)

- **Status:** Accepted
- **Tanggal:** 2026-07-13 (dicatat retrospektif)
- **Sumber:** Dokumentasi.md §5.1 catatan tentang Yoast Breadcrumbs Settings

## Context

Yoast punya dua mekanisme breadcrumb yang terpisah:

1. **Visual** — diaktifkan via Settings > Search Appearance > Breadcrumbs, lalu di-render di template dengan `<?php if ( function_exists('yoast_breadcrumb') ) { yoast_breadcrumb( ... ); } ?>`.
2. **Schema** — output JSON-LD `BreadcrumbList` otomatis oleh plugin (sudah dimatikan via ADR-0001).

Keduanya adalah sumber kebenaran terpisah. Kalau visual dari Yoast dipakai tapi schema dari custom, dua breadcrumb bisa saja menampilkan path berbeda. Lebih buruk, nama breadcrumb dari Yoast historically berawalan "SEO –" / "SEO |" karena dia pull dari SEO title (lihat §1 Dokumentasi.md).

Sozo tidak butuh mekanisme Yoast:
- Visual breadcrumb di template Elementor sudah ditulis manual sesuai hierarki URL flat.
- Schema breadcrumb ditulis manual di `schema-markup.html`.

## Decision

Jangan pakai fungsionalitas breadcrumb Yoast sama sekali — visual maupun schema. Biarkan Settings > Search Appearance > Breadcrumbs di Yoast pada kondisi default (nonaktif). Visual breadcrumb sepenuhnya di-handle oleh template Elementor. Schema breadcrumb sepenuhnya di-handle oleh `schema-markup.html` di WPCode snippet.

Kalau di kemudian hari ada developer yang menambahkan `yoast_breadcrumb()` call di template, anggap itu regression dan harus di-rollback.

## Consequences

**Positif:**
- Single source of truth untuk breadcrumb: template Elementor (visual) + `schema-markup.html` (schema). Tidak ada drift.
- Tidak ada risiko nama breadcrumb berawalan "SEO –" / "SEO |".
- Breadcrumb bisa disesuaikan dengan URL flat hierarchy (mis. `/treatment/`, `/skin-treatment/`) yang tidak bisa ditangani Yoast.

**Negatif:**
- Audit Yoast template perlu dilakukan manual untuk pastikan tidak ada `yoast_breadcrumb()` call yang lolos.
- Dokumentasi di §5.1 Dokumentasi.md adalah catatan kecil, mudah dilewatkan developer baru.

**Mitigasi:**
- Tambahkan checklist di Dokumentasi.md §7 QC: "Tidak ada `yoast_breadcrumb()` di template".
- Bisa ditambah GitHub Action regex search di template files sebagai safety net (belum diimplementasi).

**Alternatif yang dipertimbangkan:**
- *Pakai Yoast breadcrumb visual + custom schema* — ditolak: dua sumber visual berbeda, risiko inkonsistensi path.
- *Pakai Yoast breadcrumb visual + override nama di filter* — ditolak: workaround rapuh, Yoast bisa update behavior sewaktu-waktu.
