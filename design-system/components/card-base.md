# Base Card

- **Tipe:** Primitive (fondasi semua kartu konten)
- **Tugas:** Surface bersama untuk kartu. Jenis konten (treatment, promo, lokasi) adalah konfigurasi isi di atas Base Card, bukan sistem visual terpisah.

## Anatomi

```
CARD (article)
├── Media            opsional
├── Eyebrow / Badge  opsional
├── Title            wajib
├── Supporting content  opsional
└── Action           opsional
```

Tidak semua kartu harus punya semua zona.

## Nilai default (Standard)

| Properti | Nilai |
| :-- | :-- |
| Background | `--color-white` |
| Border | 1px `--color-border` |
| Radius | `--radius-lg` (16px) |
| Shadow | `--shadow-none` |
| Overflow | `hidden` (media menempel ke tepi) |
| Padding konten | 24px (`--space-5`), 20px di ≤ 768px |

## Variant visual

| Variant | Kelas | Perbedaan dari Standard | Kapan |
| :-- | :-- | :-- | :-- |
| Standard | `.card` | — | Default di semua listing |
| Elevated | `.card--elevated` | Shadow `--shadow-md` | Kartu yang berdiri di atas background Almond/Isabelline, atau kartu tunggal yang perlu terangkat |
| Featured | — | Belum didefinisikan | Tunggu use case nyata. Lihat [decisions.md](../decisions.md#pertanyaan-terbuka) |

## Hover (kartu interaktif saja)

Berlaku kalau seluruh kartu bisa diklik.

| Properti | Nilai |
| :-- | :-- |
| Transform | `translateY(-2px)` |
| Shadow | `--shadow-md` |
| Border | `--color-border-strong` |
| Gambar | `scale(1.03)` |
| Durasi | `--duration-card` (220ms), gambar `--duration-media` (400ms) |

## Area klik

Satu kartu, satu tujuan. Seluruh kartu bisa diklik lewat pola *stretched link*: link di judul diperluas menutupi kartu. Dilarang `<div onclick>`.

```css
.card { position: relative; }
.card__title a::after { content: ""; position: absolute; inset: 0; }
```

Kalau kartu punya dua aksi berbeda (contoh: Location Card dengan WhatsApp + Maps), stretched link tidak dipakai. Setiap aksi jadi elemen sendiri.

Focus ring saat link judul di-focus muncul di seluruh kartu:

```css
.card:focus-within { outline: var(--focus-ring); outline-offset: var(--focus-offset); }
.card__title a:focus-visible { outline: none; } /* ring sudah di kartu */
```

## CSS referensi

```css
.card {
  position: relative;
  display: flex;
  flex-direction: column;
  background: var(--color-white);
  border: var(--border-width) solid var(--color-border);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-none);
  overflow: hidden;
  transition:
    transform var(--duration-card) var(--ease-standard),
    box-shadow var(--duration-card) var(--ease-standard),
    border-color var(--duration-card) var(--ease-standard);
}
.card--elevated { box-shadow: var(--shadow-md); }
.card__content { display: flex; flex-direction: column; flex: 1; padding: var(--space-5); }

.card--interactive:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); border-color: var(--color-border-strong); }
.card--interactive:hover .media img { transform: scale(1.03); }

@media (max-width: 768px) { .card__content { padding: 20px; } }
@media (prefers-reduced-motion: reduce) {
  .card--interactive:hover,
  .card--interactive:hover .media img { transform: none; }
}
```

## Kepadatan informasi

1. Kartu merangkum, tidak menjelaskan penuh.
2. Informasi paling penting di atas.
3. Harga, durasi, downtime, rating, banyak badge, dan banyak CTA **tidak** ditambahkan secara default. Kalau strategi konten butuh metadata tambahan, buat variant terdokumentasi di spec kartu terkait.
4. Kartu dalam satu listing harus seragam: rasio gambar, panjang deskripsi, posisi aksi.
