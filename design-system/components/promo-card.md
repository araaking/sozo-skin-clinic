# Promo Card

- **Tipe:** Komponen Sozo (konfigurasi [Base Card](card-base.md))
- **Model mental:** Pintu masuk promo. Menampilkan penawaran dan mengarahkan ke detail promo atau konsultasi WhatsApp.

## Tugas

1. Mengidentifikasi promo.
2. Memberi relevansi atau urgensi yang cukup untuk dibuka.
3. Memberi satu aksi lanjutan yang jelas.

## Anatomi

| # | Zona | Spec |
| :-- | :-- | :-- |
| 1 | Media | [Image](image.md) rasio **3:2** |
| 2 | Badge promo | [Badge](badge.md) `badge--promo`, menempel di media kiri atas, jarak 16px dari tepi |
| 3 | Judul promo | `<h3>` style H4 (24px / 600 / 1.30), `--color-brand-primary`. ≤ 768px: H5 |
| 4 | Copy pendukung | 14px / 400 / 1.55, `--color-text-secondary`, maksimal 2 baris. Periode promo ditulis di sini kalau ada |
| 5 | CTA | Satu aksi, lihat aturan CTA |

## Aturan CTA

| Kondisi | CTA |
| :-- | :-- |
| Promo punya halaman detail | [Link](link.md) "Lihat Promo →", kartu interaktif (stretched link) |
| Promo tanpa halaman detail | [WhatsApp CTA](whatsapp-cta.md) `btn btn--primary btn--sm` "Tanya Promo". Kartu tidak interaktif |

Satu kartu, satu CTA.

## Markup (tanpa halaman detail)

```html
<article class="card promo-card">
  <div class="media media--3x2">
    <img src="promo-glow.webp" alt="Promo Glow Treatment Sozo" width="900" height="600" loading="lazy">
    <span class="badge badge--promo promo-card__badge">PROMO</span>
  </div>
  <div class="card__content">
    <h3 class="card__title promo-card__title">Glow Treatment</h3>
    <p class="promo-card__description">Harga spesial untuk treatment pilihan. Berlaku sampai 31 Oktober 2026.</p>
    <a href="{WHATSAPP_URL}" class="btn btn--primary btn--sm btn--wa promo-card__cta"
       target="_blank" rel="noopener">
      <svg class="btn__icon" aria-hidden="true">…</svg>
      <span>Tanya Promo</span>
    </a>
  </div>
</article>
```

## CSS referensi

```css
.promo-card__badge { position: absolute; top: var(--space-4); left: var(--space-4); z-index: 1; }
.promo-card__title {
  margin: 0;
  font-size: var(--text-h4);
  font-weight: var(--font-weight-semibold);
  line-height: var(--leading-h4);
  color: var(--color-brand-primary);
}
.promo-card__description {
  margin: var(--space-2) 0 20px;
  font-size: var(--text-body-sm);
  line-height: var(--leading-body-sm);
  color: var(--color-text-secondary);
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.promo-card__cta { margin-top: auto; align-self: flex-start; }
@media (max-width: 768px) {
  .promo-card__title { font-size: var(--text-h5); line-height: var(--leading-h5); }
}
```

## Warna promo

Kuning, merah, dan aksen hangat bersifat opsional dan hanya kalau maknanya promo. Resolution Blue tetap warna CTA utama.

## Grid

Desktop 2–3 kolom sesuai panjang copy, tablet 2, mobile 1 kolom atau slider. Di mobile, judul dan CTA diprioritaskan.

## QA

- [ ] Badge promo bermakna, hanya satu.
- [ ] Judul promo jelas.
- [ ] Copy pendukung ringkas (≤ 2 baris).
- [ ] Aksen warna kontekstual.
- [ ] CTA jelas, hanya satu.
- [ ] Kartu tidak berubah jadi banner iklan penuh badge.
- [ ] Rasio gambar 3:2 di seluruh listing.
- [ ] Hover halus.
