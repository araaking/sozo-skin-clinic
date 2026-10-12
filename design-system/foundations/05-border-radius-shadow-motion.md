# 05 · Border, Radius, Shadow, Motion

## Border

Border untuk pengelompokan, bukan dekorasi.

| Jenis | Lebar | Warna | Dipakai untuk |
| :-- | :-- | :-- | :-- |
| None | 0 | — | Surface tanpa batas |
| Subtle | 1px | `--color-border` (`#DFDBD4`) | Kartu default, divider |
| Strong | 1px | `--color-border-strong` (`#C8C3BA`) | Kartu saat hover, pemisah yang perlu lebih terlihat |
| Brand | 1px | `--color-brand-primary` | Outline button |
| Focus | 2px | `--color-brand-primary` | Focus ring keyboard (lihat Focus di bawah) |

Subtle dan Strong sama-sama 1px. Bedanya hanya warna.

## Radius

| Token | Nilai | Dipakai untuk |
| :-- | --: | :-- |
| `--radius-sm` | 6px | UI utilitas kecil (tooltip, chip kecil) |
| `--radius-md` | 10px | Input, kontrol kompak, thumbnail kecil |
| `--radius-lg` | 16px | **Kartu default** (Base, Treatment, Promo, Location desktop), gambar standalone |
| `--radius-xl` | 24px | Container featured besar, modal |
| `--radius-full` | 999px | **Semua button**, badge, tag, ikon bulat |

Aturan:

1. Hanya lima nilai ini. Radius 8px, 12px, 14px, 18px, 20px tidak dipakai.
2. Button selalu pill (`--radius-full`). Semua variant, semua ukuran.
3. Kartu selalu `--radius-lg`.
4. Jangan membuat semua elemen bulat penuh. Kartu, input, dan gambar tidak pill.
5. Elemen di dalam kartu yang menempel ke tepi kartu tidak diberi radius sendiri. Kartu memakai `overflow: hidden`.

## Shadow

Shadow ditahan. Komponen boleh tidak memakai shadow sama sekali, dan itu default.

| Token | Nilai | Dipakai untuk |
| :-- | :-- | :-- |
| `--shadow-none` | `none` | Default semua kartu |
| `--shadow-sm` | `0 2px 8px rgba(23,23,21,.04)` | Elevasi tipis (sticky bar, header saat scroll) |
| `--shadow-md` | `0 8px 24px rgba(23,23,21,.07)` | Kartu Elevated, kartu interaktif saat hover |
| `--shadow-lg` | `0 16px 40px rgba(23,23,21,.10)` | Dropdown, floating button, popover |
| `--shadow-xl` | `0 24px 60px rgba(23,23,21,.12)` | Modal. Jarang. |
| `--shadow-pin` | `0 3px 8px rgba(26,32,128,.45)` | **Hanya** pin peta cabang. Glow biru membuat pin kecil terbaca di atas peta abu-abu |

Aturan:

1. Semua shadow memakai warna netral `rgba(23, 23, 21, …)`. Satu-satunya shadow biru adalah `--shadow-pin` untuk pin peta (DEC-28). Kartu, tombol, dan elemen lain tidak memakai shadow biru.
2. Satu elemen, satu shadow token. Jangan menumpuk beberapa shadow.
3. Hindari shadow berat yang membuat tampilan seperti template SaaS generik.

## Focus

Focus wajib terlihat untuk pengguna keyboard.

```css
:focus-visible {
  outline: var(--focus-ring);          /* 2px solid Resolution Blue */
  outline-offset: var(--focus-offset); /* 3px */
}
/* Elemen di atas background biru */
.on-primary :focus-visible { outline: var(--focus-ring-inverse); }
```

- Pakai `:focus-visible`, bukan `:focus`, supaya ring tidak muncul saat klik mouse.
- Dilarang `outline: none` tanpa pengganti yang setara.

## Motion

Motion fungsional dan halus.

| Token | Nilai | Dipakai untuk |
| :-- | --: | :-- |
| `--duration-fast` | 180ms | Perubahan warna, border, background, transform kecil tombol |
| `--duration-card` | 220ms | Lift dan shadow kartu |
| `--duration-media` | 400ms | Zoom gambar di kartu |
| `--ease-standard` | `ease` | Semua transisi |

| Efek | Nilai yang diizinkan |
| :-- | :-- |
| Lift tombol saat hover | `translateY(-1px)`, kembali ke `0` saat active |
| Lift kartu saat hover | `translateY(-2px)` |
| Zoom gambar kartu | `scale(1.03)` |

Dilarang:

- Lompatan kartu besar, scaling agresif (> 1.05).
- Animasi panjang yang memperlambat navigasi (> 400ms).
- Gerakan terus-menerus di konten informasi (auto-bounce, pulse tanpa henti).
- Transisi `all`. Sebutkan properti yang berubah.

`prefers-reduced-motion: reduce` sudah menolkan semua durasi di [tokens.css](../tokens.css). Komponen yang memakai `transform` juga wajib menonaktifkan transform di media query itu.
