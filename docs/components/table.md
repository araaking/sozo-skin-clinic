# Table Component

- **Status:** Spec aktif
- **Versi:** 1.0 (13 Juli 2026)
- **Tipe:** Molekul (komposisi cell + row + header)
- **File terkait:** `index.html` (custom HTML) atau Elementor Table widget

## Anatomi

| Properti | Nilai | Catatan |
| :-- | :-- | :-- |
| Container border-radius | 12px | Outer rounded corner |
| Container border | 1px solid `var(--sozo-border)` | |
| Container overflow | hidden | Supaya rounded kepotong rapi |
| Cell padding | 14px 20px (md) | 12px 16px (sm) |
| Cell border-bottom | 1px solid `var(--sozo-border)` | Pemisah row |
| Header bg | `var(--sozo-light-bg)` (#F4F8FA) | |
| Header font weight | 600 | Semi-bold |
| Header text color | `var(--sozo-text)` | |
| Body font weight | 400 | Regular |
| Body text color | `var(--sozo-text)` | |
| Font family | Inter, system-ui, sans-serif | Body/UI; lihat `docs/design-system/colors-typography.md` |
| Font size | 14–15px | Tergantung size |
| Last row border-bottom | none | Bersih di tepi bawah container |

## Varian

### Pricing

Untuk paket harga treatment (mis. "Korean Filler — 1 session Rp 2.999.000").

- Layout: 2 kolom (label + harga) atau 3 kolom (paket + harga + CTA)
- Harga: rata kanan, font weight 600, optional `var(--sozo-blue)` color
- CTA: di kolom ketiga atau di footer row, gunakan Button component (Primary, SM)
- Highlight row aktif: background `var(--sozo-light-bg)` + border-left 3px `var(--sozo-blue)`

**Use case:** Tabel paket Korean Filler, Profhilo, paket bundle.

### Comparison

Untuk perbandingan treatment A vs B (mis. "Botox vs Filler", "IPL vs Laser").

- Layout: 3+ kolom (kriteria + treatment A + treatment B)
- Header: nama treatment di header, sticky
- Check/cross icon di cell untuk boolean (✓/✗)
- Highlight "recommended" column: bg `var(--sozo-light-bg)`, border `var(--sozo-blue)`

**Use case:** Halaman "Botox vs Filler", "IPL vs Laser", halaman edukasi.

### Info Row

Untuk strip info treatment (harga, durasi, downtime, target area).

- Layout: 2 kolom (label + value) atau 4 kolom (icon + label + value)
- Compact: cell padding 12px 16px
- Bisa inline (bukan tabel sungguhan) — pakai flex/grid kalau lebih sesuai
- Icon optional di kolom label

**Use case:** Strip info di hero treatment, pricing breakdown, FAQ "Berapa lama?".

## Size

| Size | Cell padding | Font | Use case |
| :-- | :-- | :-- | :-- |
| `sm` | 10px 14px | 13px | Compact info row, mobile compact |
| `md` (default) | 14px 20px | 14–15px | Standard table |
| `lg` | 18px 24px | 16px | Highlight table, comparison utama |

## State

| State | Row bg | Catatan |
| :-- | :-- | :-- |
| Default | `#ffffff` | |
| Hover (body row) | `var(--sozo-light-bg)` | Subtle highlight |
| Active/Selected | `var(--sozo-light-bg)` + border-left 3px `var(--sozo-blue)` | Indikator pilihan user |
| Disabled | 50% opacity | Paket tidak tersedia |

## HTML Markup (Custom HTML)

```html
<!-- Pricing table -->
<div class="tbl-container">
  <table class="tbl tbl-pricing">
    <thead>
      <tr>
        <th>Paket</th>
        <th class="tbl-price-col">Harga</th>
        <th class="tbl-cta-col">Aksi</th>
      </tr>
    </thead>
    <tbody>
      <tr class="tbl-row-active">
        <td>Paket 1 Session</td>
        <td class="tbl-price">Rp 2.999.000</td>
        <td><a class="btn btn-primary btn-sm" href="#booking">Booking</a></td>
      </tr>
      <tr>
        <td>Paket 3 Session</td>
        <td class="tbl-price">Rp 7.999.000</td>
        <td><a class="btn btn-primary btn-sm" href="#booking">Booking</a></td>
      </tr>
    </tbody>
  </table>
</div>

<!-- Info row (compact) -->
<div class="tbl-info-row">
  <div class="tbl-info-item">
    <span class="tbl-info-label">Harga mulai</span>
    <span class="tbl-info-value">Rp 1.499.000</span>
  </div>
  <div class="tbl-info-item">
    <span class="tbl-info-label">Durasi</span>
    <span class="tbl-info-value">30–45 menit</span>
  </div>
  <div class="tbl-info-item">
    <span class="tbl-info-label">Downtime</span>
    <span class="tbl-info-value">Minimal</span>
  </div>
</div>
```

## Elementor Table Widget (Recommended)

| Setting | Value |
| :-- | :-- |
| Type | Default |
| Alignment | Left (default) |
| Width | 100% |
| Border type | Solid |
| Border width | 1 |
| Border color | `var(--sozo-border)` |
| **Border radius** | **12px** (container, dengan overflow hidden) |
| Cell padding | 14px 20px |
| Header typography | Inter, 14px, weight 600, color `var(--sozo-text)`, bg `var(--sozo-light-bg)` |
| Body typography | Inter, 14px, weight 400, color `var(--sozo-text)` |
| Stripes | None (atau `var(--sozo-light-bg)` untuk tabel panjang) |

## Aksesibilitas

- Pakai `<table>` semantic untuk data tabular (bukan untuk layout — pakai flex/grid untuk itu).
- `<th scope="col">` atau `<th scope="row">` untuk header.
- `<caption>` untuk judul tabel (opsional, bisa visually hidden).
- Striped/hover row hanya visual, tidak boleh satu-satunya penanda state (pakai text/icon juga).
- Responsive: di mobile, jangan horizontal scroll — pertimbangkan stack vertikal (lihat Pola Responsive di bawah).

## Pola Responsive

Tabel dengan ≤3 kolom: stack ke card per row di mobile.

```html
<!-- Mobile: setiap row jadi card -->
<div class="tbl-mobile-card">
  <div class="tbl-mobile-label">Paket</div>
  <div class="tbl-mobile-value">Paket 1 Session</div>
  <div class="tbl-mobile-label">Harga</div>
  <div class="tbl-mobile-value">Rp 2.999.000</div>
  <a class="btn btn-primary btn-sm" href="#booking">Booking</a>
</div>
```

Tabel dengan >3 kolom: tetap horizontal scroll dengan `overflow-x: auto` di container, tapi tambahkan hint visual.

## Anti-pattern

- ❌ Pakai tabel untuk layout (pakai flex/grid).
- ❌ Merge cell yang bikin struktur tabel tidak jelas (`colspan`/`rowspan` berlebihan).
- ❌ Striped row sebagai satu-satunya penanda kategori (butuh text/icon juga).
- ❌ Tabel dengan width tetap di mobile (pasti overflow).
- ❌ Hilangkan header di mobile tanpa alternatif a11y.

## Notes

- Spec ini generic, belum mereferensikan treatment LP spesifik. Usage per LP lihat `docs/pages/[kategori]/[lp].md`.
- Varian info row kadang lebih baik diimplementasi sebagai flex/grid container dengan class `tbl-info-row`, bukan `<table>` sungguhan — tergantung konteks. Default ke flex/grid kalau tidak ada data tabular yang strict.
