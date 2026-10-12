# Breadcrumb

- **Tipe:** Primitive navigasi
- **Tugas:** Memberi konteks posisi halaman di arsitektur yang dalam. Penunjang navigasi, bukan headline.

## Contoh jalur

```
Beranda / Treatment / Skin Treatment / Pico Laser
Beranda / Lokasi / Jakarta / Jakarta Selatan / Tebet
Beranda / Promo
```

- Item pertama selalu **Beranda**.
- Label memakai bahasa Indonesia dan **harus sama** dengan nama item di schema `BreadcrumbList` halaman yang sama.

## Nilai

| Bagian | Font | Warna | Hover |
| :-- | :-- | :-- | :-- |
| Ancestor (link) | 13px / 500 | `--color-text-muted` | `--color-brand-primary` |
| Current (bukan link) | 13px / 600 | `--color-brand-primary` | — |
| Separator `/` | 13px | `--color-border-strong` | — |

| Properti | Nilai |
| :-- | :-- |
| Gap antar item | 8px (`--space-2`) |
| Jarak ke elemen berikutnya | 16px (`--space-4`) |
| Mobile | Boleh wrap ke baris kedua. Tidak ada scroll horizontal |

## Markup

```html
<nav class="breadcrumb" aria-label="Breadcrumb">
  <ol class="breadcrumb__list">
    <li><a href="/">Beranda</a></li>
    <li><a href="/treatment/">Treatment</a></li>
    <li><a href="/treatment/skin-treatment/">Skin Treatment</a></li>
    <li><span class="breadcrumb__current" aria-current="page">Pico Laser</span></li>
  </ol>
</nav>
```

Separator dibuat lewat CSS, bukan karakter di HTML, supaya screen reader tidak membacanya.

## CSS referensi

```css
.breadcrumb__list {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: var(--space-2);
  margin: 0 0 var(--space-4);
  padding: 0;
  list-style: none;
  font-size: 13px;
  line-height: var(--leading-caption);
}
.breadcrumb__list li { display: inline-flex; align-items: center; gap: var(--space-2); }
.breadcrumb__list li + li::before { content: "/"; color: var(--color-border-strong); }
.breadcrumb a { color: var(--color-text-muted); font-weight: var(--font-weight-medium); text-decoration: none; }
.breadcrumb a:hover { color: var(--color-brand-primary); }
.breadcrumb a:focus-visible { outline: var(--focus-ring); outline-offset: 2px; }
.breadcrumb__current { color: var(--color-brand-primary); font-weight: var(--font-weight-semibold); }
```

## Aturan

1. Breadcrumb tidak memakai microdata. Data terstruktur cukup dari JSON-LD `BreadcrumbList`.
2. Item terakhir bukan link dan memakai `aria-current="page"`.
3. Breadcrumb tidak diberi background, border, atau shadow dekoratif.
4. Halaman tanpa kedalaman (homepage) tidak memakai breadcrumb.
