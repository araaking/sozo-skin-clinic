# Treatment Card

- **Tipe:** Komponen Sozo (konfigurasi [Base Card](card-base.md))
- **Model mental:** Item katalog layanan. Perilakunya mirip kartu produk e-commerce, tapi isinya treatment.

## Tugas

1. Mengidentifikasi treatment.
2. Memberi konteks kategori dan nilai utama.
3. Mengirim user ke halaman detail treatment.

Konversi WhatsApp terjadi di halaman detail, bukan di kartu.

## Anatomi

| # | Zona | Spec |
| :-- | :-- | :-- |
| 1 | Media | [Image](image.md) rasio **4:3**, hover `scale(1.03)` |
| 2 | Kategori | 12px / 600, uppercase, `--tracking-eyebrow`, `--color-text-muted` |
| 3 | Nama treatment | `<h3>` dengan style H4 (24px / 600 / 1.30), `--color-brand-primary`. ≤ 768px: style H5 (20px) |
| 4 | Deskripsi singkat | 14px / 400 / 1.55, `--color-text-secondary`, **maksimal 3 baris** (line-clamp) |
| 5 | Aksi | [Link](link.md) standalone "Lihat Treatment →" |

## Jarak

```
Media → 24px (padding) → Kategori → 8px → Nama → 8px → Deskripsi → 20px → Aksi
```

Aksi selalu menempel di bawah kartu (`margin-top: auto`) supaya sejajar antar kartu di satu baris.

## Markup

```html
<article class="card card--interactive treatment-card">
  <div class="media media--4x3">
    <img src="pico-laser.webp" alt="Treatment Pico Laser di Sozo Skin Clinic"
         width="800" height="600" loading="lazy">
  </div>
  <div class="card__content">
    <p class="treatment-card__category">Skin</p>
    <h3 class="card__title treatment-card__title">
      <a href="/treatment/skin-treatment/pico-laser/">Pico Laser</a>
    </h3>
    <p class="treatment-card__description">Laser untuk meratakan warna kulit dan mencerahkan.</p>
    <span class="link treatment-card__action" aria-hidden="true">Lihat Treatment →</span>
  </div>
</article>
```

Link judul menjadi area klik seluruh kartu (stretched link). Teks "Lihat Treatment →" adalah penanda visual, jadi diberi `aria-hidden` supaya screen reader tidak membaca dua link ke tujuan yang sama.

## CSS referensi

```css
.treatment-card__category {
  margin: 0 0 var(--space-2);
  font-size: var(--text-label);
  font-weight: var(--font-weight-semibold);
  line-height: var(--leading-label);
  letter-spacing: var(--tracking-eyebrow);
  text-transform: uppercase;
  color: var(--color-text-muted);
}
.treatment-card__title {
  margin: 0;
  font-size: var(--text-h4);
  font-weight: var(--font-weight-semibold);
  line-height: var(--leading-h4);
  color: var(--color-brand-primary);
}
.treatment-card__title a { color: inherit; text-decoration: none; }
.treatment-card__description {
  margin: var(--space-2) 0 20px;
  font-size: var(--text-body-sm);
  line-height: var(--leading-body-sm);
  color: var(--color-text-secondary);
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.treatment-card__action { margin-top: auto; }

@media (max-width: 768px) {
  .treatment-card__title { font-size: var(--text-h5); line-height: var(--leading-h5); }
}
```

## Kategori

Skin · Hair · Hair Removal · Injectable · Slimming

Satu komponen untuk semua kategori. Jangan membuat kartu terpisah per cabang taksonomi.

## Grid

Desktop 3 kolom (4 kalau konten pendek), tablet 2, mobile 1 kolom atau slider. Gap `--grid-gap`.

## Aturan

1. Tanpa harga, durasi, downtime, rating, dan CTA WhatsApp secara default.
2. Satu badge maksimal, dan biasanya tidak perlu karena kategori sudah ada.
3. Deskripsi 2–3 baris. Copy lebih panjang dipotong di sumbernya, bukan cuma dengan clamp.

## QA

- [ ] Media 4:3.
- [ ] Kategori secara visual lebih lemah dari nama treatment.
- [ ] Nama treatment langsung terbaca.
- [ ] Deskripsi ≤ 3 baris, tinggi kartu seragam.
- [ ] Aksi berupa link ke detail.
- [ ] Hover halus.
- [ ] Border dan radius sesuai Base Card.
- [ ] Gambar `object-fit: cover`.
- [ ] Mobile tetap terbaca tanpa kepadatan desktop.
