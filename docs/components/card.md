# Card Component

- **Status:** Spec aktif
- **Versi:** 1.0 (13 Juli 2026)
- **Tipe:** Molekul (image + content + optional action)
- **File terkait:** `index.html` (custom HTML) atau Elementor Card/Box widget

## Anatomi

| Properti | Nilai | Catatan |
| :-- | :-- | :-- |
| Container bg | `#ffffff` | |
| Container border | 1px solid `var(--sozo-border)` | Optional, beberapa variant tanpa border |
| Container border-radius | 12px | |
| Container shadow (default) | none | Flat, handalkan border |
| Container shadow (hover) | 0 4px 16px rgba(26, 32, 128, 0.08) | Subtle elevation saat hover |
| Container padding | 0 (kalau ada image full-width) atau 20px | Tergantung variant |
| Image aspect ratio | variant-specific | Lihat di bawah |
| Image cover | `object-fit: cover` | Crop rapi |
| Title font family | Poppins (`var(--sozo-font-heading)`) | Judul kartu |
| Title font weight | 600 | Semi-bold |
| Title font size | 16–18px | |
| Title color | `var(--sozo-text)` | |
| Body font size | 14px | |
| Body font family | Inter (`var(--sozo-font-body)`) | |
| Body color | `var(--sozo-text)` 80% opacity | Subtle hierarchy |
| Font family | Inter, system-ui, sans-serif | Body/UI; lihat `docs/design-system/colors-typography.md` |

## Varian

### Doctor Card

Profil dokter/practitioner.

- Layout: image (atas, 1:1) + nama (h4) + spesialisasi (p) + optional bio pendek
- Image aspect ratio: 1:1 (square)
- Hover: shadow muncul, optional scale 1.02
- Click target: seluruh card clickable (kalau ada halaman detail)
- A11y: kalau clickable, card adalah `<a>` dengan text alternatif yang jelas

**Use case:** Halaman "Tim Dokter", section "Kenapa Pilih Kami".

### Testimoni Card

Review pasien.

- Layout: rating (stars, atas) + quote (body) + nama pasien + foto optional (kecil, circle)
- Rating: 5 stars, warna `var(--sozo-cyan)` atau gold
- Image: optional avatar 40px circle
- Tanpa hover effect (read-only)
- Bisa carousel/slider di container

**Use case:** Halaman testimoni, section social proof di LP.

### Lokasi Card

Pilihan cabang klinik.

- Layout: nama cabang (h4) + alamat (p) + jam operasional (p) + tombol "Lihat di Maps" (Ghost, SM) + tombol "Reservasi" (Primary, SM)
- Image: optional foto cabang 4:3
- Hover: shadow muncul
- Click target: tombol dalam card, bukan card secara keseluruhan (kalau multi-action)

**Use case:** Section "Lokasi Kami" di LP, halaman cabang individual.

### Treatment Card (Category Page)

Card treatment di halaman kategori (mis. daftar 16 skin booster).

- Layout: image treatment (atas, 4:3) + nama treatment (h4) + harga mulai (p, weight 600) + deskripsi 1-2 kalimat
- Image aspect ratio: 4:3
- Hover: shadow muncul + translateY(-2px)
- Click target: seluruh card clickable ke LP detail

**Use case:** Halaman `/skin-booster-treatment/`, `/meso-treatment/`, halaman kategori.

## Size

| Size | Padding | Image ratio | Use case |
| :-- | :-- | :-- | :-- |
| `sm` | 16px | 4:3 | Grid padat, list testimoni |
| `md` (default) | 20px | variant-specific | Most use cases |
| `lg` | 28px | 4:3 atau 1:1 | Featured card, single highlight |

## State

| State | Shadow | Transform | Catatan |
| :-- | :-- | :-- | :-- |
| Default | none | none | Flat |
| Hover | 0 4px 16px rgba(26, 32, 128, 0.08) | translateY(-2px) | Subtle lift |
| Active/Press | 0 2px 8px rgba(26, 32, 128, 0.06) | translateY(0) | Reset lift |
| Focus (kalau clickable) | outline 2px `var(--sozo-blue)`, offset 2px | none | A11y |
| Disabled | 50% opacity, no hover | none | Kartu tidak aktif |

## HTML Markup (Custom HTML)

```html
<!-- Doctor Card -->
<a href="/tim/dokter-ayu" class="card card-doctor">
  <div class="card-image-wrap">
    <img src="..." alt="Dr. Ayu, Sp.KK" class="card-image" />
  </div>
  <div class="card-body">
    <h4 class="card-title">Dr. Ayu</h4>
    <p class="card-meta">Spesialis Dermatologi</p>
    <p class="card-desc">10+ tahun pengalaman di bidang dermatologi estetik.</p>
  </div>
</a>

<!-- Testimoni Card -->
<div class="card card-testimoni">
  <div class="card-rating" aria-label="Rating 5 dari 5">
    ★★★★★
  </div>
  <p class="card-quote">"Hasilnya natural banget, gak keliatan kayak habis treatment."</p>
  <div class="card-author">
    <img src="..." alt="" class="card-avatar" />
    <span class="card-author-name">Sarah, 28</span>
  </div>
</div>

<!-- Lokasi Card -->
<div class="card card-location">
  <h4 class="card-title">Sozo Skin Clinic Tebet</h4>
  <p class="card-meta">Jl. Tebet Raya No.XX, Jakarta Selatan</p>
  <p class="card-hours">Senin–Sabtu: 09.00–20.00</p>
  <div class="card-actions">
    <a href="https://maps.google.com/..." class="btn btn-ghost btn-sm" target="_blank" rel="noopener">Lihat di Maps</a>
    <a href="#booking" class="btn btn-primary btn-sm">Reservasi</a>
  </div>
</div>

<!-- Treatment Card -->
<a href="/korean-filler/" class="card card-treatment">
  <div class="card-image-wrap">
    <img src="..." alt="Korean Filler Treatment" class="card-image" />
  </div>
  <div class="card-body">
    <h4 class="card-title">Korean Filler</h4>
    <p class="card-price">Mulai Rp 2.999.000</p>
    <p class="card-desc">Filler premium dengan teknologi Korea untuk hasil natural.</p>
  </div>
</a>
```

## Elementor Card / Box Widget (Recommended)

| Setting | Value |
| :-- | :-- |
| Type | Default |
| Background | `#ffffff` |
| Border type | Solid |
| Border width | 1 |
| Border color | `var(--sozo-border)` |
| **Border radius** | **12px** |
| Box shadow | none (default) / 0 4px 16px rgba(26, 32, 128, 0.08) (hover) |
| Padding | 20px (md) |
| Image (kalau ada) | Top, 4:3 atau 1:1 (variant-specific) |
| Image border-radius | 0 (kalau full-bleed dalam card) atau 12px (kalau floating) |
| Title typography | Inter, 18px, weight 600 |
| Body typography | Inter, 14px, weight 400, color 80% opacity |

## Aksesibilitas

- Kalau seluruh card clickable, gunakan `<a>` dengan text yang menjelaskan tujuan link (bukan "Klik di sini").
- Kalau multi-action dalam card, setiap action jadi `<a>`/`<button>` terpisah, card bukan `<a>` wrapper.
- Image wajib `alt` text yang deskriptif. Untuk avatar testimoni, `alt=""` (decorative) OK kalau nama author ada di samping.
- Focus state visible pada card clickable: outline 2px `var(--sozo-blue)`.
- Testimoni quote: pakai `<blockquote>` atau `<p>` dengan citation (`<cite>` untuk author name).

## Anti-pattern

- ❌ Card clickable sebagai `<div onclick>` (pakai `<a>`).
- ❌ Multi-action dalam card clickable wrapper (mis. card adalah `<a>` + ada `<a>` lain di dalamnya — nested links invalid HTML).
- ❌ Card dengan background image tanpa overlay (text jadi tidak terbaca).
- ❌ Image tanpa `alt` (atau `alt="image"` kosong generik).
- ❌ Card tanpa padding (content nempel ke border).
- ❌ Testimoni card dengan rating tanpa label a11y (`aria-label`).

## Notes

- Spec ini generic. Treatment card usage per LP lihat `docs/pages/[kategori]/[lp].md`.
- Untuk treatment card di category page, integrasikan dengan `service`-related schema di JSON-LD (lihat `docs/adr/0002-use-service-not-product.md`).
- Untuk testimoni card, kalau pakai slider/carousel, library yang direkomendasikan: Swiper, Splide, atau Elementor built-in. Hindari custom carousel tanpa library (a11y nightmare).
