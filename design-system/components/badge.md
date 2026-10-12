# Badge / Tag

- **Tipe:** Primitive
- **Tugas:** Penanda konteks ringkas: kategori, promo, status, label konten.

## Nilai dasar

| Properti | Nilai |
| :-- | :-- |
| Tinggi min. | 30px |
| Padding horizontal | 10px |
| Radius | `--radius-full` |
| Font | 12px / 600 / 1.40 (style Label) |
| Letter-spacing | `--tracking-badge` (0.02em) |
| Transform | Boleh uppercase untuk label 1–2 kata (PROMO, NEW, SKIN) |

## Variant

| Variant | Kelas | Background | Teks | Kontras | Dipakai untuk |
| :-- | :-- | :-- | :-- | --: | :-- |
| Brand | `badge--brand` | `--color-brand-almond` | `--color-brand-primary` | 10.23 | Kategori treatment, region lokasi |
| Neutral | `badge--neutral` | `--color-brand-isabelline` | `--color-text-secondary` | 7.42 | Label informasi umum |
| Promo | `badge--promo` | `--color-promo-yellow` | `--color-text-primary` | 11.09 | Promo, penawaran terbatas |
| Alert | `badge--alert` | `--color-error-subtle` | `--color-error` | 4.65 | Status penting (mis. "Kuota terbatas") |
| Label | `badge--label` | `--color-brand-almond` | `--color-brand-primary` | 10.23 | Label section di atas heading (pengganti Eyebrow teks). Lihat di bawah |

Badge dengan background `--color-promo-red` solid dan teks putih tidak dipakai, karena kontrasnya 3.87:1 di bawah AA untuk teks 12px.

## CSS referensi

```css
.badge {
  display: inline-flex;
  align-items: center;
  min-height: 30px;
  padding: 0 10px;
  border-radius: var(--radius-full);
  font-size: var(--text-label);
  font-weight: var(--font-weight-semibold);
  line-height: var(--leading-label);
  letter-spacing: var(--tracking-badge);
}
.badge--brand   { background: var(--color-brand-almond);     color: var(--color-brand-primary); }
.badge--neutral { background: var(--color-brand-isabelline); color: var(--color-text-secondary); }
.badge--promo   { background: var(--color-promo-yellow);     color: var(--color-text-primary); }
.badge--alert   { background: var(--color-error-subtle);     color: var(--color-error); }
```

## Badge Label (label section)

Bentuk pill Almond untuk label kecil di atas heading section, contoh "KLINIK KECANTIKAN TERBAIK" di hero atau "SIAPA KAMI" di section cerita. Ini bentuk default label section di halaman Sozo (DEC-26). Eyebrow teks polos tetap boleh dipakai kalau section sudah ramai.

| Properti | Nilai |
| :-- | :-- |
| Tinggi min. | 30px |
| Padding horizontal | 18px |
| Font | 12px / 600 / 1.40, uppercase |
| Letter-spacing | `--tracking-label` (0.25em) |
| Background / teks | `--color-brand-almond` / `--color-brand-primary` |
| Jarak ke heading | 24px (`--space-5`) |
| Tag HTML | `<p>` atau `<span>`, bukan heading |

```css
.badge--label {
  min-height: 30px;
  padding: 0 18px;
  margin-bottom: var(--space-5);
  background: var(--color-brand-almond);
  color: var(--color-brand-primary);
  letter-spacing: var(--tracking-label);
  text-transform: uppercase;
}
```

Badge Label dipakai di atas surface White atau `--color-surface-warm`. Di section Almond, Almond-on-Almond tidak terlihat: pakai Eyebrow teks.

## Label umum

TREATMENT · SKIN · HAIR · HAIR REMOVAL · INJECTABLE · SLIMMING · PROMO · NEW · LOKASI

## Aturan

1. **Satu badge per kartu.** Tumpukan badge dekoratif tidak dipakai. Badge Label hanya satu per section.
2. Badge Promo (kuning) dan Alert (merah) hanya kalau maknanya memang promo atau status.
3. Badge bukan tombol. Kalau bisa diklik, pakai Link atau Button.
4. Badge tidak dipakai sebagai heading.
