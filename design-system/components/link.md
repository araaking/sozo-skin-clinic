# Link

- **Tipe:** Primitive
- **Tugas:** Navigasi dan aksi informasi ringan.

## Variant

| Variant | Kelas | Ukuran | Weight | Underline | Dipakai untuk |
| :-- | :-- | :-- | :-- | :-- | :-- |
| Standalone | `.link` | 14px | 600 | Muncul saat hover | Aksi kartu, "Lihat Semua", link di luar paragraf |
| Inline | `.link--inline` | ikut teks sekitar | 500 | **Selalu** | Link di dalam paragraf |

Link inline selalu bergaris bawah supaya tidak hanya dibedakan lewat warna.

## Nilai

| Properti | Nilai |
| :-- | :-- |
| Warna | `--color-brand-primary` |
| Warna visited | Sama, `--color-brand-primary` |
| Hover | Underline, `text-underline-offset: 4px` |
| Focus | `--focus-ring`, offset 3px |
| Panah | `→` sebagai teks di akhir label, dibungkus `<span aria-hidden="true">` |
| Gap label–panah | 4px (`--space-1`) |

## CSS referensi

```css
.link {
  display: inline-flex;
  align-items: center;
  gap: var(--space-1);
  color: var(--color-brand-primary);
  font-size: var(--text-body-sm);
  font-weight: var(--font-weight-semibold);
  text-decoration: none;
  text-underline-offset: 4px;
}
.link:hover { text-decoration: underline; }
.link:visited { color: var(--color-brand-primary); }
.link:focus-visible { outline: var(--focus-ring); outline-offset: var(--focus-offset); }

.link--inline {
  display: inline;
  font-size: inherit;
  font-weight: var(--font-weight-medium);
  text-decoration: underline;
}
```

## Markup

```html
<a href="/treatment/skin-treatment/pico-laser/" class="link">
  Lihat Treatment <span aria-hidden="true">→</span>
</a>
```

## Label aksi kartu

| Kartu | Label |
| :-- | :-- |
| Treatment | Lihat Treatment → |
| Promo | Lihat Promo → |
| Lokasi | Lihat Lokasi → |
| Artikel (deferred) | Baca Artikel → |
| Produk (deferred) | Lihat Produk → |
| Dokter (deferred) | Lihat Profil → |

## Aturan

1. Panah hanya kalau menunjukkan arah navigasi.
2. Jangan styling setiap link jadi pill button.
3. Link tidak bersaing dengan CTA utama. Di section yang punya Primary button, link tetap Standalone, bukan Button.
4. Link keluar memakai `target="_blank" rel="noopener"`.
