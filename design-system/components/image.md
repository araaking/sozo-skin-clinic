# Image / Media

- **Tipe:** Primitive
- **Tugas:** Bingkai gambar dengan rasio tetap. Treatment, dokter, produk, lokasi, dan konten bergantung pada foto.

## Rasio

| Rasio | Kelas | Dipakai untuk |
| :-- | :-- | :-- |
| 1:1 | `media--1x1` | Dokter, produk, profil |
| 4:3 | `media--4x3` | **Treatment Card**, kartu umum |
| 3:2 | `media--3x2` | **Promo Card**, foto editorial |
| 16:9 | `media--16x9` | **Location Card**, blog, hero, media lebar |

Satu jenis kartu, satu rasio. Satu grid listing tidak boleh mencampur rasio.

## Nilai

| Properti | Nilai |
| :-- | :-- |
| Bingkai | `aspect-ratio` sesuai tabel, `overflow: hidden` |
| Fit | `object-fit: cover` |
| Posisi | `object-position: center`. Ganti per gambar hanya kalau subjek terpotong |
| Placeholder | Background `--color-brand-almond` selama gambar belum muncul |
| Radius | Di dalam kartu: tanpa radius (kartu yang memotong). Standalone: `--radius-lg` |
| Atribut `width` & `height` | Wajib, supaya layout tidak bergeser (CLS) |
| Loading | `loading="lazy"` untuk gambar di bawah layar pertama. Gambar hero: `loading="eager"` + `fetchpriority="high"` |
| Hover | `scale(1.03)` selama 400ms, hanya kalau gambar ada di dalam kartu interaktif |

## CSS referensi

```css
.media {
  position: relative;
  display: block;
  overflow: hidden;
  background: var(--color-brand-almond);
}
.media--1x1  { aspect-ratio: 1 / 1; }
.media--4x3  { aspect-ratio: 4 / 3; }
.media--3x2  { aspect-ratio: 3 / 2; }
.media--16x9 { aspect-ratio: 16 / 9; }
.media img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  transition: transform var(--duration-media) var(--ease-standard);
}
```

## Overlay

Default: tanpa overlay. Overlay hanya boleh kalau ada teks di atas gambar, dan teksnya harus mencapai 4.5:1 di titik paling terang gambar. Overlay dekoratif tidak dipakai.

## Alt text

| Jenis | `alt` |
| :-- | :-- |
| Foto treatment | Nama treatment + konteks. `alt="Treatment Pico Laser di Sozo Skin Clinic"` |
| Foto cabang | Nama cabang. `alt="Klinik Sozo Skin Tebet"` |
| Gambar dekoratif | `alt=""` |

## Kualitas foto

1. Foto tajam, terang, dibingkai profesional.
2. Framing subjek konsisten dalam satu jenis kartu.
3. Crop konsisten dalam satu grid.
4. Tidak ada zoom besar atau transisi dramatis.
