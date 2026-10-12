# Sozo Skin Clinic Design System

- **Versi:** 1.2 (9 Oktober 2026)
- **Status:** Aktif. Sumber kebenaran untuk semua UI baru di `sozoskinclinic.com`.
- **Asal:** Diperjelas dari `Sozo-Skin-Clinic-Design-System-v1.md`. Nilai yang di V1 masih berupa rentang atau placeholder sudah diberi satu angka. Daftar lengkapnya di [decisions.md](decisions.md).

## Ringkasan satu layar

| Bagian | Nilai |
| :-- | :-- |
| Brand primary | `#1A2080` Resolution Blue |
| Brand secondary | `#D9C7B1` Dun |
| Surface | `#E9DFD5` Almond · `#DFDBD4` Isabelline White · `#F7F5F1` Warm (kanvas peta) |
| Kanvas | `#FFFFFF` White |
| Font | Poppins 400 / 500 / 600 / 700 / 800 |
| Spacing | Base 4px: 4, 8, 12, 16, 24, 32, 40, 48, 64, 80, 96, 120 |
| Container | 1280px, padding 32px (20px mobile) |
| Grid | 12 / 8 / 4 kolom, gap 24 / 20 / 16px |
| Breakpoint | ≤ 1024px tablet · ≤ 768px mobile · ≤ 480px mobile kecil |
| Radius | 6 / 10 / 16 (kartu) / 24 / 999 (tombol, badge) |
| Shadow | none (default) / sm / md / lg / xl · pin (khusus pin peta) |
| Konversi utama | WhatsApp CTA, Resolution Blue |

## Isi folder

| File | Isi |
| :-- | :-- |
| [tokens.css](tokens.css) | **Satu-satunya** sumber nilai mentah. Copy ke halaman/WPCode, lalu pakai `var(--…)` |
| **Foundations** | |
| [foundations/01-principles.md](foundations/01-principles.md) | Lima kata arah visual, prinsip + tesnya, batas scope |
| [foundations/02-color.md](foundations/02-color.md) | Palet, peran warna, semantik, tabel kontras, gradient |
| [foundations/03-typography.md](foundations/03-typography.md) | Poppins, skala fluid, eyebrow, ritme vertikal |
| [foundations/04-spacing-layout.md](foundations/04-spacing-layout.md) | Spacing, breakpoint, container, grid, jarak section |
| [foundations/05-border-radius-shadow-motion.md](foundations/05-border-radius-shadow-motion.md) | Border, radius, shadow, focus, motion |
| [foundations/06-accessibility-html.md](foundations/06-accessibility-html.md) | WCAG AA, semantik HTML, alt text, BEM |
| **Components** | |
| [components/button.md](components/button.md) | Primary, Secondary, Outline. sm / md / lg |
| [components/whatsapp-cta.md](components/whatsapp-cta.md) | CTA konversi inline + floating |
| [components/link.md](components/link.md) | Link standalone dan inline |
| [components/badge.md](components/badge.md) | Brand, Neutral, Promo, Alert, Label (label section) |
| [components/image.md](components/image.md) | Rasio 1:1, 4:3, 3:2, 16:9 |
| [components/card-base.md](components/card-base.md) | Fondasi semua kartu |
| [components/treatment-card.md](components/treatment-card.md) | Kartu katalog treatment |
| [components/promo-card.md](components/promo-card.md) | Kartu promo |
| [components/breadcrumb.md](components/breadcrumb.md) | Breadcrumb visual, selaras schema `BreadcrumbList` |
| [components/location-card.md](components/location-card.md) | Kartu cabang existing + target normalisasi |
| **Lainnya** | |
| [checklists.md](checklists.md) | Design review, engineering review, responsive, visual QA |
| [decisions.md](decisions.md) | Keputusan V1.1, pertanyaan terbuka, exception log, changelog |

## Peta dependensi

```
tokens.css
   ↓
foundations (warna, tipe, spacing, radius, shadow, motion)
   ↓
Button · Link · Badge · Image · Base Card · Breadcrumb
   ↓
WhatsApp CTA · Treatment Card · Promo Card · Location Card
   ↓
Pola halaman (belum masuk V1.1)
```

Komponen di level atas tidak boleh membawa warna, radius, atau spacing baru kalau token yang ada sudah mencukupi.

## Cara pakai

1. Masukkan isi `tokens.css` sekali per halaman (atau sekali global lewat WPCode).
2. Pilih komponen dari tabel di atas. Ikuti markup dan kelas di spec-nya.
3. Tulis CSS memakai `var(--…)`. Hex, rgba, dan px di luar skala hanya boleh di `tokens.css` atau di nilai yang tercatat di spec komponen.
4. Cek pakai [checklists.md](checklists.md) sebelum dipasang.

## Tabel keputusan komponen

| Kebutuhan | Pakai | Jangan |
| :-- | :-- | :-- |
| Aksi utama | Button Primary / WhatsApp CTA | Link teks dengan affordance lemah |
| Navigasi | Link | Button, kecuali aksi di halaman |
| Konteks kategori | Badge / kategori kartu | Heading besar |
| Item katalog | Base Card + konfigurasi konten | Gaya kartu one-off |
| Katalog treatment | Treatment Card | Detail lengkap di dalam kartu |
| Promo | Promo Card | Banner tanpa struktur |
| Lokasi | Location Card existing | Bangun ulang dari nol |
| Hierarki halaman | Breadcrumb | Breadcrumb dekoratif |

## Hierarki CTA

- Satu section, satu aksi dominan.
- Aksi sekunder harus terlihat lebih lemah.
- Link teks untuk navigasi pendukung tanpa bersaing dengan konversi.
- Detail treatment: aksi dominan = WhatsApp CTA.
- Listing treatment: aksi dominan kartu = navigasi ke detail.

## Menambah komponen baru

Jawab tiga pertanyaan dulu:

1. Website saat ini benar-benar butuh komponen ini?
2. Konten atau interaksinya cukup berulang untuk jadi komponen?
3. Tidak bisa disusun dari primitive yang sudah ada?

Kalau salah satu jawabannya "tidak", simpan sebagai kode lokal sampai pengulangan nyata muncul. Kalau "ya" semua, tambahkan file di `components/`, catat di [decisions.md](decisions.md), dan update tabel di README ini.

## Figma (saat sistem dipindah ke Figma)

Halaman: `00 Cover & Principles`, `01 Foundations`, `02 Components`, `03 Sozo Components`, `04 Patterns — Future`, `05 Page Templates — Future`.

Nama variable: `brand/primary`, `brand/secondary`, `brand/almond`, `brand/isabelline`, `neutral/white`.
Nama komponen: `Button/Primary/MD`, `CTA/WhatsApp/Primary`, `Card/Base/Standard`, `Card/Treatment/Standard`, `Card/Promo/Standard`, `Card/Location/Existing`.
