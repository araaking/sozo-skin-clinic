# 06 · Aksesibilitas & HTML

Target: **WCAG 2.2 level AA.**

## Kontras

| Jenis | Rasio minimum |
| :-- | :-- |
| Teks normal (< 24px, atau < 18.66px bold) | 4.5:1 |
| Teks besar | 3:1 |
| Komponen UI yang harus dikenali (border input, ikon tanpa label) | 3:1 |

Tabel pasangan warna yang sudah dihitung ada di [02-color.md](02-color.md#pasangan-teks-yang-boleh-dipakai). Teks di atas foto wajib diukur di titik paling terang foto.

## Semantik HTML

| Kebutuhan | Elemen |
| :-- | :-- |
| Pindah halaman / buka WhatsApp / buka Maps | `<a href>` |
| Aksi di halaman yang sama (buka modal, submit, toggle) | `<button type="button">` |
| Kartu konten | `<article>` |
| Breadcrumb | `<nav aria-label="Breadcrumb">` + `<ol>` |
| Heading | `<h1>`–`<h6>` berurutan, satu `<h1>` per halaman |

Dilarang `<div onclick>` atau `<span onclick>` untuk elemen yang bisa diklik. Pakai `<a>` atau `<button>`.

## Gambar

- Gambar informatif: `alt` mendeskripsikan isi dalam bahasa Indonesia. Contoh: `alt="Treatment Pico Laser di Sozo Skin Clinic"`.
- Gambar dekoratif: `alt=""`.
- Ikon di dalam tombol berlabel teks: `aria-hidden="true"`.
- Tombol ikon tanpa teks: `aria-label` wajib.

## Keyboard & focus

- Semua elemen interaktif bisa dicapai dengan Tab, urutannya sesuai urutan visual.
- Focus ring selalu terlihat (lihat [05-border-radius-shadow-motion.md](05-border-radius-shadow-motion.md#focus)).
- Informasi penting tidak boleh hanya muncul saat hover.

## Mobile

- Tap target minimal 44 × 44px. Tombol aksi utama minimal 48px tinggi.
- Jarak antar target tap minimal 8px.

## Link keluar

`target="_blank"` wajib disertai `rel="noopener"`. Link WhatsApp dan Google Maps termasuk.

## Status & warna

- Jangan memakai warna saja untuk menyampaikan status. Tambahkan teks atau ikon.
- Kuning dan merah komplementer tidak boleh jadi satu-satunya penanda status.

## Label ikon

Ikon yang tidak dikenali universal wajib punya label teks yang terlihat. Ikon WhatsApp, Maps, dan panah navigasi dianggap dikenali, tapi tetap butuh `aria-label` kalau berdiri tanpa teks.

## CSS naming

Kelas komponen memakai BEM:

```
.treatment-card              /* block */
.treatment-card__media       /* element */
.treatment-card__title
.treatment-card--featured    /* modifier */
.btn  .btn--primary  .btn--md
```

- Nama semantik dan bisa dipakai ulang.
- Dilarang membuat varian one-off seperti `.promo-blue-v2` atau `.card-special-3` untuk satu halaman. Kalau satu halaman butuh pengecualian, simpan lokal dan catat di [decisions.md](../decisions.md#exception-log).
