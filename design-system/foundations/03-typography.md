# 03 · Tipografi

## Typeface

**Satu family: Poppins.** Dipakai untuk heading, body, tombol, label, dan semua teks UI. Font kedua baru boleh masuk kalau ada kebutuhan konten yang jelas, lewat keputusan di [decisions.md](../decisions.md).

```css
font-family: var(--font-family-primary);
/* "Poppins", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif */
```

### Loading

- Poppins di-load **sekali** di level dokumen / site-wide, bukan per komponen atau per section.
- Weight yang di-load: **400, 500, 600, 700, 800**. Jangan load weight lain.
- Pakai `display=swap`.

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
```

### Peran weight

| Weight | Token | Dipakai untuk |
| :-- | :-- | :-- |
| 400 Regular | `--font-weight-regular` | Body, teks baca normal |
| 500 Medium | `--font-weight-medium` | Teks pendukung, caption, link inline, penekanan ringan |
| 600 Semi Bold | `--font-weight-semibold` | Label UI, tombol, badge, H3–H5, judul kartu |
| 700 Bold | `--font-weight-bold` | Display, H1, H2 |
| 800 Extra Bold | `--font-weight-extrabold` | Penekanan hero tertentu saja (mis. satu kata atau angka promo). Bukan untuk heading penuh. |

## Skala

Ukuran display sampai H3 bersifat fluid: nilai mobile di viewport 375px, naik linear sampai nilai desktop di 1280px. Formula `clamp()` sudah ada di token.

| Style | Token ukuran | Mobile (375px) | Desktop (≥1280px) | Weight | Line-height | Dipakai untuk |
| :-- | :-- | --: | --: | :-- | :-- | :-- |
| Display XL | `--text-display-xl` | 48px | 64px | 700 | 1.05 | Hero heading besar |
| Display LG | `--text-display-lg` | 42px | 56px | 700 | 1.10 | Hero atau heading kampanye |
| H1 | `--text-h1` | 38px | 48px | 700 | 1.15 | Judul halaman |
| H2 | `--text-h2` | 32px | 40px | 700 | 1.20 | Judul section |
| H3 | `--text-h3` | 28px | 32px | 600 | 1.25 | Sub-section |
| H4 | `--text-h4` | 24px | 24px | 600 | 1.30 | Heading section kecil, judul kartu |
| H5 | `--text-h5` | 20px | 20px | 600 | 1.35 | Heading kompak, judul Location Card |
| Body Large | `--text-body-lg` | 18px | 18px | 400 | 1.65 | Lead / intro paragraf |
| Body | `--text-body` | 16px | 16px | 400 | 1.60 | Teks baca default |
| Body Small | `--text-body-sm` | 14px | 14px | 400 | 1.55 | Deskripsi kartu, teks pendukung |
| Caption | `--text-caption` | 12px | 12px | 500 | 1.40 | Metadata |
| Label | `--text-label` | 12px | 12px | 600 | 1.40 | Badge, eyebrow, kategori |

Ukuran di luar tabel ini tidak dipakai. Dua pengecualian tercatat di spec komponennya: tombol (13–15px) di [button.md](../components/button.md) dan breadcrumb (13px) di [breadcrumb.md](../components/breadcrumb.md).

### Peta tag HTML

Style visual dipilih berdasarkan peran, tag dipilih berdasarkan struktur dokumen. Keduanya terpisah.

- Satu `<h1>` per halaman. Hero memakai `<h1>` dengan style Display XL atau Display LG.
- Judul section memakai `<h2>`. Judul kartu di dalam section memakai `<h3>` walaupun style visualnya H4.
- Jangan loncat level (h2 langsung ke h4).

## Text style turunan

### Eyebrow

Label kecil di atas heading section. Default label section di halaman Sozo adalah **Badge Label** (pill Almond, lihat [badge.md](../components/badge.md#badge-label-label-section)). Eyebrow teks di bawah dipakai kalau section berlatar Almond atau sudah ramai.

| Properti | Nilai |
| :-- | :-- |
| Ukuran / weight / line-height | Label: 12px / 600 / 1.40 |
| Transform | `uppercase` |
| Letter-spacing | `--tracking-eyebrow` (0.12em) |
| Warna | `--color-brand-primary` |
| Ornamen | Garis 24px × 1px warna yang sama di kiri, gap 8px (opsional) |

### Kategori kartu

Sama dengan Eyebrow, tapi warna `--color-text-muted` dan tanpa garis.

## Ritme vertikal teks

```
Eyebrow  → 12px →  Heading  → 16px →  Description  → 24px →  CTA
```

| Jarak | Token |
| :-- | :-- |
| Eyebrow ke heading | `--space-3` (12px) |
| Heading ke deskripsi | `--space-4` (16px) |
| Deskripsi ke CTA | `--space-5` (24px) |
| Antar paragraf body | `--space-4` (16px) |

## Aturan

1. Poppins di seluruh permukaan produk.
2. Heading cukup tebal untuk hierarki, tapi jangan sampai setiap section terasa seperti iklan. Weight 800 hanya untuk penekanan hero.
3. Body copy memakai `--color-text-primary` atau `--color-text-secondary`. Jangan memakai warna dekoratif (Dun, Light Brown, kuning).
4. Letter-spacing hanya untuk label uppercase (eyebrow, kategori, badge). Body tidak diberi tracking.
5. Uppercase hanya untuk label, eyebrow, dan metadata pendek. Heading dan tombol tidak uppercase.
6. Paragraf panjang dibatasi `max-width: var(--measure)` (65ch). Jangan biarkan paragraf selebar container 1280px.
7. Heading boleh berwarna `--color-brand-primary` atau `--color-text-primary`. Pilih satu per halaman dan konsisten.
8. Mobile tidak sekadar mengecilkan semua ukuran secara proporsional. Body tetap 16px di semua viewport.
9. Token tipografi dinamai berdasarkan peran, bukan nama komponen.
