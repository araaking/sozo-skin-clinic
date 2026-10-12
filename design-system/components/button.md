# Button

- **Tipe:** Primitive
- **Tugas:** Aksi dengan penekanan primary, secondary, atau rendah.
- **Turunan:** [WhatsApp CTA](whatsapp-cta.md)

## Kapan dipakai

| Kebutuhan | Pakai |
| :-- | :-- |
| Aksi utama section (konsultasi, booking) | Button Primary |
| Aksi pendukung saat sudah ada Primary | Button Secondary atau Outline |
| Navigasi ke halaman lain dalam daftar / kartu | [Link](link.md), bukan Button |
| Teks aksi di dalam paragraf | [Link](link.md) |

## Variant

| Variant | Background | Teks | Border | Hover |
| :-- | :-- | :-- | :-- | :-- |
| `btn--primary` | `--color-brand-primary` | `--color-text-on-primary` | transparent | background `--color-primary-hover` |
| `btn--secondary` | `--color-brand-secondary` | `--color-brand-primary` | transparent | background `--color-secondary-hover` |
| `btn--outline` | transparent | `--color-brand-primary` | 1px `--color-brand-primary` | background `--color-primary-subtle` |

Hanya tiga variant ini. Variant Ghost, invert-to-white, dan warna lain tidak ada di sistem.

## Ukuran

| Ukuran | Kelas | Tinggi min. | Padding horizontal | Font | Ikon | Dipakai untuk |
| :-- | :-- | --: | --: | :-- | --: | :-- |
| Small | `btn--sm` | 40px | 18px | 13px / 600 | 16px | Aksi di kartu, UI padat (desktop) |
| Medium | `btn--md` | 48px | 24px | 14px / 600 | 18px | **Default** |
| Large | `btn--lg` | 56px | 28px | 15px / 600 | 20px | Hero, CTA utama halaman |

Di mobile (≤ 768px) aksi utama minimal `btn--md`. `btn--sm` hanya untuk aksi sekunder yang punya jarak tap cukup.

## Anatomi

```
[ ikon ]  Label  [ ikon trailing opsional ]
```

| Properti | Nilai |
| :-- | :-- |
| Display | `inline-flex`, `align-items: center`, `justify-content: center` |
| Gap ikon–label | 8px (`--space-2`) |
| Radius | `--radius-full` (pill) di semua variant dan ukuran |
| Border | 1px (transparent kecuali Outline), supaya ukuran ketiga variant sama |
| Font | Poppins 600, `line-height: 1` |
| Text transform | none (tidak uppercase) |
| White-space | `nowrap` |
| Cursor | `pointer` |

## State

| State | Perilaku |
| :-- | :-- |
| Default | Sesuai variant |
| Hover | Warna hover variant + `translateY(-1px)` |
| Active / pressed | `translateY(0)` |
| Focus-visible | `outline: var(--focus-ring)`, offset 3px. Di atas background biru pakai `--focus-ring-inverse` |
| Disabled | `opacity: 0.5`, `cursor: not-allowed`, tanpa hover dan transform. `<button disabled>`, atau `<a aria-disabled="true">` tanpa `href` |

Ukuran tombol (tinggi, padding, border) **tidak boleh berubah** antar state.

## CSS referensi

```css
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: var(--space-2);
  min-height: 48px;
  padding: 0 var(--space-5);
  border: var(--border-width) solid transparent;
  border-radius: var(--radius-full);
  font-family: var(--font-family-primary);
  font-size: 14px;
  font-weight: var(--font-weight-semibold);
  line-height: 1;
  white-space: nowrap;
  text-decoration: none;
  cursor: pointer;
  transition:
    background-color var(--duration-fast) var(--ease-standard),
    color var(--duration-fast) var(--ease-standard),
    border-color var(--duration-fast) var(--ease-standard),
    transform var(--duration-fast) var(--ease-standard);
}
.btn:hover  { transform: translateY(-1px); }
.btn:active { transform: translateY(0); }
.btn:focus-visible { outline: var(--focus-ring); outline-offset: var(--focus-offset); }

.btn--sm { min-height: 40px; padding-inline: 18px; font-size: 13px; }
.btn--md { min-height: 48px; padding-inline: var(--space-5); font-size: 14px; }
.btn--lg { min-height: 56px; padding-inline: 28px; font-size: 15px; }

.btn--primary   { background: var(--color-brand-primary);   color: var(--color-text-on-primary); }
.btn--primary:hover   { background: var(--color-primary-hover); }
.btn--secondary { background: var(--color-brand-secondary); color: var(--color-brand-primary); }
.btn--secondary:hover { background: var(--color-secondary-hover); }
.btn--outline   { background: transparent; color: var(--color-brand-primary); border-color: var(--color-brand-primary); }
.btn--outline:hover   { background: var(--color-primary-subtle); }

.btn__icon { width: 18px; height: 18px; flex: none; }
.btn--sm .btn__icon { width: 16px; height: 16px; }
.btn--lg .btn__icon { width: 20px; height: 20px; }

.btn:disabled,
.btn[aria-disabled="true"] { opacity: 0.5; cursor: not-allowed; transform: none; }

@media (max-width: 480px) {
  .btn--block-mobile { width: 100%; }
}
@media (prefers-reduced-motion: reduce) {
  .btn:hover { transform: none; }
}
```

## Markup

```html
<a href="/treatment/skin-treatment/" class="btn btn--outline btn--md">Lihat Semua Treatment</a>

<button type="button" class="btn btn--primary btn--md">
  <svg class="btn__icon" aria-hidden="true">…</svg>
  <span>Pilih Cabang</span>
</button>
```

## Aturan

1. Satu section maksimal **satu** Primary. Aksi lain memakai Secondary, Outline, atau Link.
2. Label menjelaskan aksi dan tujuan, 2–4 kata, satu baris. Dilarang label kabur seperti "Klik di sini".
3. Lebar penuh (`btn--block-mobile`) hanya di ≤ 480px untuk CTA dominan. Desktop tidak memakai tombol lebar penuh.
4. Pakai `<a>` untuk navigasi dan link keluar, `<button>` untuk aksi di halaman.
5. Jangan styling setiap link sebagai pill button.
6. Ukuran ikon seimbang dengan label (lihat tabel ukuran).

## QA

- [ ] Variant sesuai makna aksi.
- [ ] Ukuran sesuai konteks. Mobile: aksi utama ≥ `btn--md`.
- [ ] Poppins 600.
- [ ] Radius pill.
- [ ] Hover, active, focus-visible, disabled ada.
- [ ] Focus terlihat.
- [ ] Tidak ada lompatan layout saat hover/active.
- [ ] Label menjelaskan aksi.
