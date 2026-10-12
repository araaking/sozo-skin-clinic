# Location Card (existing)

- **Tipe:** Komponen Sozo, **sudah ada di production** (`lokasi/index.html`, kelas `.sozo-map-card`)
- **Status:** Dinormalisasi bertahap ke token. Tidak dibangun ulang dari nol.
- **Tugas:** Menampilkan satu cabang dan dua aksi: hubungi cabang via WhatsApp, buka Google Maps.

Spec ini menetapkan target normalisasi. Struktur, data, fungsi pencarian, pagination, modal pilih lokasi, dan tracking yang sudah berjalan tetap dipertahankan.

## Anatomi saat ini

| Zona | Elemen existing |
| :-- | :-- |
| Media | `.sozo-card-media`: embed Google Maps (desktop), thumbnail pin "SOZO" (mobile) |
| Badge region | `.sozo-region-badge` + ikon pin |
| Nama cabang | `<h3>`, link ke halaman cabang kalau ada (`.sozo-branch-link`) |
| Alamat | `.sozo-address` |
| Aksi | `.sozo-btn-wa` + `.sozo-btn-maps` |

## Target: desktop (kartu)

| Zona | Target |
| :-- | :-- |
| Kartu | [Base Card](card-base.md) Standard: border 1px `--color-border`, radius `--radius-lg` (16px), shadow `--shadow-none`, hover `--shadow-md` |
| Media | Rasio **16:9**, `overflow: hidden` |
| Badge region | [Badge](badge.md) `badge--brand` dengan ikon pin 12px |
| Nama cabang | Style H5 (20px / 600 / 1.35), `--color-brand-primary` |
| Alamat | 14px / 400 / 1.55, `--color-text-secondary` |
| Aksi | Grid 2 kolom, gap 12px (`--space-3`), jarak 20px dari alamat |
| Aksi WhatsApp | [WhatsApp CTA](whatsapp-cta.md) `btn btn--primary btn--sm`, label "Hubungi Cabang" |
| Aksi Maps | `btn btn--outline btn--sm` + ikon map, label "Lihat Maps" |
| Padding konten | 24px (`--space-5`) |

WhatsApp adalah aksi utama (Primary), Maps aksi pendukung (Outline). Hierarki dua tombol ini harus terlihat jelas.

## Target: mobile ≤ 768px (baris kompak)

Di mobile kartu berubah jadi baris list horizontal. Ini pola responsive yang sah dan bukan pelanggaran aturan Base Card.

| Properti | Target |
| :-- | :-- |
| Layout | Baris horizontal: thumbnail kiri, konten kanan |
| Surface | Tanpa radius luar, tanpa shadow |
| Pemisah | `border-bottom: 1px solid var(--color-border)` antar baris |
| Thumbnail | 1:1, radius `--radius-md` |
| Detail yang disembunyikan | Embed peta |
| Aksi | Inline, ringan, tetap ≥ 44px area tap |

## Daftar normalisasi

Dicek dari implementasi saat ini. Dikerjakan saat file disentuh berikutnya, bukan sebagai task terpisah.

| # | Saat ini | Target |
| :-- | :-- | :-- |
| 1 | Radius kartu desktop 20px | `--radius-lg` (16px) |
| 2 | Shadow bernuansa biru | `--shadow-none`, hover `--shadow-md` |
| 3 | Label tombol uppercase ("BUAT JADWAL", "LIHAT MAPS >") | Sentence case: "Hubungi Cabang", "Lihat Maps". Tombol tidak uppercase |
| 4 | Warna dan ukuran tombol custom | Kelas `btn` + variant dari [button.md](button.md) |
| 5 | Nilai warna mentah | Token dari [tokens.css](../tokens.css) |

## Pola pendukung di halaman lokasi

Pola ini sudah ada dan ikut dinormalisasi ke token, bukan dibuat ulang.

| Pola | Target token |
| :-- | :-- |
| Search card | Base Card, input radius `--radius-md` |
| Quick city links | [Link](link.md) atau Badge neutral |
| Empty state + tombol reset | Tipografi + Button Outline |
| Modal pilih lokasi | Radius `--radius-xl` (desktop) atau bottom sheet (mobile), shadow `--shadow-xl`, z-index `--z-modal` |
| Floating promo | Background `--color-brand-primary`, aksen titik `--color-promo-red`, z-index `--z-floating`. Belum jadi komponen generik |

## QA

- [ ] Aksi utama memakai token brand primary.
- [ ] Hierarki WhatsApp vs Maps jelas.
- [ ] Area media desktop stabil (16:9).
- [ ] Alamat terbaca.
- [ ] Mode kompak mobile mudah dipindai.
- [ ] Tidak ada shadow di baris kompak mobile.
- [ ] Pencarian dan pemilihan cabang tetap mudah dipahami.
