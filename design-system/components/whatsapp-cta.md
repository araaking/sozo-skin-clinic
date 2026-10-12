# WhatsApp CTA

- **Tipe:** Komponen Sozo (turunan [Button](button.md))
- **Tugas:** Aksi konversi utama website. Membawa user ke WhatsApp Sozo.

WhatsApp CTA tetap memakai sistem brand Sozo. Ikon WhatsApp hanya penanda tujuan. Tombol hijau WhatsApp tidak dipakai.

## Hierarki

| Level | Tampilan | Kapan |
| :-- | :-- | :-- |
| Primary WhatsApp | `btn btn--primary` + ikon WA | CTA konversi utama section/halaman |
| Secondary WhatsApp | `btn btn--secondary` atau `btn btn--outline` + ikon WA | Halaman/section sudah punya aksi Primary lain |
| Floating WhatsApp | Lingkaran 56px fixed (spec di bawah) | Satu per halaman, opsional |

## Label

Label default: **Konsultasi via WhatsApp**

Label alternatif sesuai konteks:

| Konteks | Label |
| :-- | :-- |
| Hero, umum | Konsultasi Sekarang |
| Halaman treatment | Tanya Treatment |
| Ingin langsung booking | Booking via WhatsApp |
| Halaman / kartu cabang | Hubungi Cabang |
| Promo | Tanya Promo |

Label menjelaskan aksi dan tujuan. Label di luar daftar ini harus tetap memenuhi aturan itu.

## Penempatan

| Halaman | Dominan? |
| :-- | :-- |
| Hero | Ya |
| Detail treatment | Ya, aksi dominan halaman |
| Detail promo / surface promo high-intent | Ya |
| Detail lokasi / cabang | Ya |
| Treatment Card di listing | **Tidak.** Kartu treatment navigasi ke detail |

## Anatomi inline

```html
<a href="{WHATSAPP_URL}" class="btn btn--primary btn--md btn--wa"
   target="_blank" rel="noopener">
  <svg class="btn__icon" aria-hidden="true">…ikon WhatsApp…</svg>
  <span>Konsultasi via WhatsApp</span>
</a>
```

| Properti | Nilai |
| :-- | :-- |
| Elemen | `<a>` (pindah ke aplikasi lain) |
| Ikon | Logo WhatsApp, `currentColor`, ukuran ikon sesuai ukuran tombol |
| Ukuran | `btn--md` default, `btn--lg` di hero |
| Mobile | Lebar penuh (`btn--block-mobile`) kalau jadi CTA dominan |
| Link | `target="_blank" rel="noopener"` |

## Floating WhatsApp

Pola terpisah dari tombol inline.

| Properti | Nilai |
| :-- | :-- |
| Ukuran | Lingkaran 56px |
| Background | `--color-brand-primary`, hover `--color-primary-hover` |
| Ikon | WhatsApp 24px, `--color-white` |
| Shadow | `--shadow-lg` |
| Posisi | `position: fixed`, kanan bawah, jarak 24px dari tepi (16px di ≤ 768px) |
| Z-index | `--z-floating` (di bawah modal) |
| Label | `aria-label="Konsultasi via WhatsApp"` wajib |
| Hover | `translateY(-1px)` |
| Focus | `--focus-ring`, offset 3px |

Aturan floating:

- Maksimal satu floating button per sisi layar. Kalau halaman punya floating promo, keduanya tidak boleh menumpuk di pojok yang sama.
- Floating button tidak menutupi CTA utama atau konten penting di layar 375px.

## Tracking & tujuan

URL WhatsApp dibangun dari satu konfigurasi global (nomor + template pesan), bukan diketik ulang per tombol. Nomor dan format pesan belum ditetapkan di aturan V1.1, lihat [decisions.md](../decisions.md#pertanyaan-terbuka).

## Aturan

1. Satu CTA WhatsApp dominan per section.
2. Warna WhatsApp green (`#25D366`) tidak dipakai.
3. Jangan memasang CTA WhatsApp di setiap kartu katalog.
4. Ikon tanpa teks wajib `aria-label`.
