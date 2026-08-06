# ADR-0007: Konvensi Dua File per Halaman Treatment (index.html + schema-markup.html)

- **Status:** Accepted
- **Tanggal:** 2026-07-13
- **Sumber:** AGENTS.md §4; Dokumentasi.md §5.41

## Context

Sebelum 13 Juli 2026, konvensi penamaan file di folder treatment halaman Sozo ambigu:

- `fix.html` — body HTML.
- `index.html` — JSON-LD schema.

Masalahnya: `index.html` adalah nama yang biasa diasosiasikan dengan entry point sebuah direktori. Ketika developer atau WPCode snippet cari "file utama", mereka akan menemukan `index.html` yang ternyata JSON-LD, bukan body. Bingung saat on-boarding, bingung saat debug.

Commit `3608483` (13 Juli 2026) melakukan rename di 70+ folder tracked, men-swap peran kedua file:
- `index.html` — body HTML (sesuai konvensi umum).
- `schema-markup.html` — JSON-LD schema markup saja.

## Decision

Setiap folder halaman treatment berisi tepat dua file:

- `index.html` — layout HTML body (breadcrumb visual, hero, info row, FAQ accordion, dll). Dipakai WPCode untuk render halaman di front-end Elementor.
- `schema-markup.html` — blok `<script type="application/ld+json">` lengkap. Dipakai WPCode snippet HTML dengan kondisi `Page URL contains [slug]` diinject ke `<head>`.

Tidak ada file lain di folder (gambar, aset, dll disimpan di luar folder schema). Penamaan strict: lowercase, hyphen-separated, ekstensi `.html`.

WPCode snippet untuk schema merujuk ke path `schema-markup.html`, bukan `index.html` atau `fix.html`. Audit snippet production WPCode pasca-rename untuk pastikan tidak ada referensi ke konvensi lama.

## Consequences

**Positif:**
- Pemisahan concern jelas: body vs schema.
- Filename mencerminkan isi file, intuitif untuk developer baru.
- WPCode snippet bisa target file schema langsung tanpa歧义.
- Standar naming umum (`index.html` = body) kembali berlaku.

**Negatif:**
- Snippet WPCode lama yang merujuk ke `fix.html` atau `index.html` sebagai sumber schema menjadi broken pasca-rename. Audit manual wajib dilakukan.
- Developer lama yang sudah习惯了 konvensi lama harus menyesuaikan mental model.
- Folder di repo yang belum ter-rename (folder baru di masa transisi) bisa inkonsisten.

**Mitigasi:**
- Commit rename (3608483) menyertakan semua folder tracked — sisanya hanya folder baru pasca-tanggal tersebut, yang otomatis pakai konvensi baru sejak dibuat.
- QC checklist WPCode pasca-rename: verifikasi semua snippet schema merujuk ke `schema-markup.html`.

**Alternatif yang dipertimbangkan:**
- *Satu file `index.html` berisi body + JSON-LD* — ditolak: WPCode snippet untuk JSON-LD perlu di-inject ke `<head>`, dan satu file akan coupling body dengan schema sehingga sulit di-maintain terpisah.
- *Pakai `body.html` + `schema.json`* — ditolak: inkonsisten dengan konvensi web (WPCode snippet HTML menerima `.html`, bukan `.json`).
