# Colors & Typography — Design System

- **Status:** Spec aktif (v1.1, 6 Agustus 2026) — diselaraskan dengan setup Elementor global (Primary Font Inter, heading H1–H6 Poppins, snippet Google Fonts global).
- **Tujuan:** Satu sumber kebenaran untuk brand color token dan aturan penggunaan font di seluruh LP `sozoskinclinic.com`.
- **Hubungan dengan dokumen lain:** `docs/components/*` memakai token di bawah ini. Update token di sini, jangan di spec komponen.

---

## 1. Color Tokens

Semua warna diimplementasikan lewat CSS custom property. Jangan hardcode hex di komponen.

| Token | Nilai | CSS Variable | Penggunaan |
| :-- | :-- | :-- | :-- |
| Brand primary | `#1A2080` | `var(--sozo-blue)` | CTA utama, heading, link, accent |
| Brand primary hover | `#2a33a3` | `var(--sozo-blue-hover)` | Hover state elemen brand |
| Brand cyan accent | `#3AB4F2` | `var(--sozo-cyan)` | Rating, highlight, icon aksen |
| Text | `#333333` | `var(--sozo-text)` | Body text |
| Border | `#e5e7eb` | `var(--sozo-border)` | Border container, divider |
| Light bg | `#F4F8FA` | `var(--sozo-light-bg)` | Section background, table header, hover |

### Inkonsistensi yang wajib di-resolve

- **`#1A237E` vs `#1A2080`:** Production navbar & WA button masih pakai `#1A237E` (inline), brand token adalah `#1A2080`. Selisihnya subtle tapi merusak konsistensi. Refactor semua ke `var(--sozo-blue)`. Track: `docs/adr/0008-wa-button-color-unify.md` (akan dibuat).
- **Kontras:** White text on `#1A2080` = rasio ~12.6:1, lulus AAA. Jangan gunakan `--sozo-text` (#333333) untuk text di atas `--sozo-blue`.

---

## 2. Font Rules

**Dua font, dua peran. Satu halaman maksimal dua font.**

| Peran | Font | Catatan |
| :-- | :-- | :-- |
| Heading (H1–H6, judul section, judul kartu) | **Poppins** | Weight 600–800 |
| Body & UI (paragraf, caption, tombol, tabel, accordion, input) | **Inter** | Weight 400–600 |

### Implementasi di Elementor (global)

Elementor Site Settings:

- **Primary Font** → **Inter** (isi teks/body, berlaku untuk seluruh site)
- **H1, H2, H3, H4, H5, H6** → **Poppins** (judul, seluruh heading)

Snippet Google Fonts global (dipasang via Elementor Custom Code / WPCode, berlaku di semua halaman):

```html
<!-- Preconnect Server Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<!-- Load Hanya Weight Poppins & Inter yang Dipakai -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
```

Snippet ini adalah **sumber loading font resmi** — LP custom tidak perlu `@import` Google Fonts sendiri, cukup pakai family name. Kalau LP tetap punya `@import`, hapus agar tidak dobel load.

### Stack (fallback ke system default)

```css
--sozo-font-heading: 'Poppins', 'Inter', system-ui, -apple-system, sans-serif;
--sozo-font-body:    'Inter', system-ui, -apple-system, sans-serif;
```

Kalau Google Fonts gagal load, fallback otomatis: Poppins gagal → Inter → font sistem (Segoe UI di Windows, San Francisco di macOS). Tidak pernah kosong.

### Berat font (weight)

Weight yang di-load global: **Poppins 600, 700, 800** dan **Inter 400, 500, 600**. Pakai hanya dalam rentang ini.

| Elemen | Font | Weight |
| :-- | :-- | :-- |
| H1 | Poppins | 700 |
| H2 / judul section | Poppins | 600–700 |
| H3–H6 / judul kartu | Poppins | 600 |
| Body | Inter | 400 |
| Button, link, label, header tabel | Inter | 600 |
| Ghost/link button | Inter | 500 |

Hindari weight di bawah 400 untuk teks body — readability turun. Jangan pakai weight 800 kecuali untuk angka harga/CTA akurat (lihat section type scale).

### Type scale (desktop default)

| Level | Ukuran | Font / Weight | Contoh penggunaan |
| :-- | :-- | :-- | :-- |
| H1 (hero) | `clamp(2rem, 3.5vw, 2.75rem)` | Poppins 700 | Hero title, line-height 1.25 |
| H2 | `clamp(1.5rem, 2.5vw, 2rem)` | Poppins 600–700 | Judul section |
| H3–H6 | `1.125–1.25rem` | Poppins 600 | Judul kartu SKU |
| Body | `0.95–1rem` | Inter 400 | Paragraf deskripsi |
| Body small / caption | `0.85–0.9rem` | Inter 400 | Meta, sub-caption |
| Button | `0.85–1rem` | Inter 600 | Semua tombol |
| Harga | `1.05–1.25rem` | Poppins 800 | Angka harga, `var(--sozo-blue)` |

Skala di atas referensi implementasi LP (contoh: `treatment/injectable-treatment/botox-treatment/style-js.html`). Tambah level baru hanya kalau ada komponen yang butuh — jangan menambah demi gaya.

### Aturan pakai

- Heading selalu Poppins, body selalu Inter. Satu pengecualian: kalau halaman memang khusus treatment lama yang belum migrasi, gunakan `var(--sozo-font-heading)` untuk heading dan `var(--sozo-font-body)` untuk sisanya — jangan hardcode `font-family` di setiap rule.
- Jangan `@import` Google Fonts di LP custom — font sudah di-load global via snippet. Cukup tulis `font-family: 'Poppins', ...` / `'Inter', ...`.
- Jangan load weight di luar 3 weight per font — bobot halaman turun.
- Fallback `sans-serif` generic selalu ditulis paling akhir di stack.

---

## 3. Migrasi dari State Saat Ini

Kondisi implementasi (6 Agustus 2026):

- **Elementor global** sudah di-set: Primary Font = Inter, heading H1–H6 = Poppins, Google Fonts di-load lewat snippet global.
- **LP treatment** (filler, botox, meso, RF, HIFU, skin booster, hair): `style-js.html` / `<style>` masih hardcode `font-family: 'Poppins'` untuk **seluruh teks** — body harus pindah ke Inter.
- **Halaman non-treatment** (dokter, product/ecommerce, privacy, editorial-board): seluruh teks memakai Inter — heading harus pindah ke Poppins.
- **`section-html/doctor-section.html`:** memakai Manrope + Plus Jakarta Sans — outlier. Migrasi ke Inter (halaman dokter = non-treatment).

Target akhir: semua heading Poppins, semua body Inter, tidak ada font ketiga, tidak ada `@import` di LP custom.

### Urutan migrasi yang disarankan

1. ✅ **Selesai: Botox** (`treatment/injectable-treatment/botox-treatment/style-js.html`) — heading → Poppins, body → Inter, `@import` Google Fonts dihapus. Jadi pilot + referensi.
2. ✅ **Selesai: Privacy Policy** (`privacy.html`) — h1/h3 → Poppins, body tetap Inter.
3. Per LP treatment lain: ganti `font-family` body dari Poppins ke Inter, pertahankan Poppins di heading. Hapus `@import` Google Fonts dari `style-js.html`.
4. Per halaman non-treatment lain (dokter, product, editorial-board): tambahkan Poppins di rule heading.
5. Hapus Manrope/Plus Jakarta Sans dari `doctor-section.html`.
6. ✅ **Selesai: Spec komponen** — `docs/components/*` sudah pisahkan heading/body font family (lihat `docs/design-system/colors-typography.md`).

Progress migrasi di-track di README.md status per-LP.

---

## 4. Anti-pattern

- ❌ Pakai font ketiga (Manrope, Plus Jakarta Sans, Montserrat, dll).
- ❌ Heading pakai Inter, body pakai Poppins (terbalik dari aturan).
- ❌ `@import` Google Fonts di LP custom — sudah di-load global.
- ❌ Hardcode hex di komponen — selalu `var(--sozo-*)`.
- ❌ Load weight di luar Poppins 600–800 / Inter 400–600.
- ❌ `font-family` dideklarasi di tiap rule tanpa token.
