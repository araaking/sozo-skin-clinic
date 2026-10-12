# 02 · Warna

Semua warna dipanggil lewat token di [tokens.css](../tokens.css). Hex mentah hanya boleh ada di file token.

## Status nilai

| Status | Artinya |
| :-- | :-- |
| **LOCKED** | Dari brand reference Sozo. Tidak boleh diubah tanpa keputusan baru di [decisions.md](../decisions.md). |
| **SYSTEM** | Nilai implementasi turunan (hover, teks, border, semantik). Boleh disesuaikan di V1.x kalau ada alasan kontras atau konsistensi. |
| **WORKING** | Perkiraan dari gambar brand reference. Wajib diverifikasi ke file brand asli sebelum dianggap final. |

## Palet brand (LOCKED)

| Nama | Hex | Token | Peran |
| :-- | :-- | :-- | :-- |
| Resolution Blue | `#1A2080` | `--color-brand-primary` | Identitas brand, aksi utama, state aktif, heading penting |
| Dun | `#D9C7B1` | `--color-brand-secondary` | Aksen kecantikan, background Secondary button |
| Almond | `#E9DFD5` | `--color-brand-almond` | Surface lembut: background section, badge brand, placeholder gambar |
| Isabelline White | `#DFDBD4` | `--color-brand-isabelline` | Pemisah halus: border, divider, badge neutral |
| White | `#FFFFFF` | `--color-white` | Kanvas utama, background kartu |

## Warna komplementer (WORKING)

| Nama | Hex | Token | Boleh dipakai untuk |
| :-- | :-- | :-- | :-- |
| Bright Red | `#EC4242` | `--color-promo-red` | Aksen promo bernilai tinggi (titik notifikasi, garis aksen). **Bukan untuk teks.** |
| Dark Yellow | `#FFC200` | `--color-promo-yellow` | Background badge promo, aksen kampanye |
| Light Brown | `#B5956A` | `--color-light-brown` | Aksen hangat dekoratif di samping Dun. **Bukan untuk teks.** |

Warna komplementer bersifat opsional. Pakai hanya kalau isi kontennya memang butuh makna itu (promo, kampanye). Jangan pakai karena area terasa kosong.

## Warna peran (SYSTEM)

| Peran | Token | Hex |
| :-- | :-- | :-- |
| Teks utama (body, heading non-biru) | `--color-text-primary` | `#171715` |
| Teks pendukung (deskripsi, paragraf sekunder) | `--color-text-secondary` | `#41413E` |
| Teks muted (metadata, breadcrumb ancestor, kategori kartu) | `--color-text-muted` | `#777771` |
| Teks di atas Resolution Blue | `--color-text-on-primary` | `#FFFFFF` |
| Border default | `--color-border` | `#DFDBD4` |
| Border kuat | `--color-border-strong` | `#C8C3BA` |
| Hover Primary | `--color-primary-hover` | `#151A6D` |
| Hover Secondary | `--color-secondary-hover` | `#CBB79F` |
| Hover Outline (tint biru 5%) | `--color-primary-subtle` | `rgba(26, 32, 128, 0.05)` |
| Surface warm (kanvas peta, ilustrasi) | `--color-surface-warm` | `#F7F5F1` |

## Warna semantik (SYSTEM, feedback only)

| Peran | Token | Hex | Catatan |
| :-- | :-- | :-- | :-- |
| Success | `--color-success` | `#267A57` | 5.24:1 di White |
| Warning | `--color-warning` | `#A66A18` | 4.47:1 di White, **di bawah AA**. Pakai untuk ikon dan border saja. Teks warning ditulis dengan `--color-text-primary`. |
| Error | `--color-error` | `#B64242` | 5.46:1 di White |
| Error subtle | `--color-error-subtle` | `#FDE8E8` | Background badge alert |
| Info | `--color-info` | `#39718C` | 5.36:1 di White |

Warna semantik hanya untuk status sistem (berhasil, peringatan, error, info). Jangan dipakai sebagai aksen brand.

## Pasangan teks yang boleh dipakai

Rasio dihitung dengan rumus WCAG 2.x. Batas AA: 4.5:1 untuk teks normal, 3:1 untuk teks besar (≥ 24px, atau ≥ 18.66px bold).

| Teks | Background | Rasio | Status |
| :-- | :-- | --: | :-- |
| White | Resolution Blue | 13.44 | ✅ semua ukuran |
| White | Primary hover `#151A6D` | 14.99 | ✅ semua ukuran |
| Resolution Blue | White | 13.44 | ✅ semua ukuran |
| Resolution Blue | Dun | 8.16 | ✅ semua ukuran |
| Resolution Blue | Secondary hover `#CBB79F` | 6.92 | ✅ semua ukuran |
| Resolution Blue | Almond | 10.23 | ✅ semua ukuran |
| Resolution Blue | Isabelline | 9.74 | ✅ semua ukuran |
| Text primary | White | 17.95 | ✅ semua ukuran |
| Text secondary | White | 10.24 | ✅ semua ukuran |
| Text secondary | Almond | 7.79 | ✅ semua ukuran |
| Text secondary | Isabelline | 7.42 | ✅ semua ukuran |
| Text muted | White | 4.50 | ⚠️ pas di batas AA. Hanya di atas White, minimal 12px weight 500 |
| Text muted | Almond | 3.43 | ❌ jangan |
| Resolution Blue | Surface warm | 12.35 | ✅ semua ukuran |
| Text secondary | Surface warm | 9.41 | ✅ semua ukuran |
| Text muted | Surface warm | 4.14 | ❌ jangan untuk teks < 24px |
| Text primary | Dark Yellow | 11.09 | ✅ badge promo |
| Error | Error subtle | 4.65 | ✅ badge alert |
| White | Bright Red | 3.87 | ❌ untuk teks < 24px |
| Dun | White | 1.65 | ❌ jangan pernah jadi warna teks |
| Light Brown | White | 2.81 | ❌ jangan pernah jadi warna teks |
| Dark Yellow | White | 1.62 | ❌ jangan pernah jadi warna teks |

Pasangan yang tidak ada di tabel: hitung dulu sebelum dipakai, lalu tambahkan ke tabel ini.

## Aturan

1. White adalah background dominan dan kanvas default.
2. Resolution Blue adalah warna identitas dan aksi utama. Bukan kewajiban untuk setiap heading atau background.
3. Dun adalah aksen pendukung. Dun bukan primary kedua.
4. Almond dan Isabelline membuat pemisahan lembut. Jangan dipakai untuk perubahan visual yang keras.
5. Satu halaman maksimal memakai **satu** warna background section selain White (Almond **atau** Isabelline), dan section berwarna tidak boleh berurutan. `--color-surface-warm` bukan background section: hanya untuk kanvas di dalam komponen (peta, ilustrasi), jadi tidak dihitung.
6. Jangan membuat warna brand baru untuk satu section.
7. Jangan menumpuk beberapa warna aksen supaya terasa premium.
8. Warna semantik dipakai sesuai makna, bukan selera.
9. Jangan menyampaikan status hanya lewat warna. Tambahkan teks atau ikon.
10. WhatsApp green (`#25D366`) tidak dipakai di UI Sozo. Kontras putih di atasnya cuma 1.98:1 dan keluar dari palet brand.

## Gradient

Gradient adalah perluasan dekoratif, bukan perlakuan surface default.

- **Boleh:** aksen hero, visual kampanye, satu surface dekoratif yang disengaja.
- **Kombinasi yang diizinkan:** Blue → Dun, White → Blue, White → Dun.
- **Dilarang:** gradient sebagai background kartu default, gradient di setiap section, gradient di belakang teks body.

## Do / Don't

| Do | Don't |
| :-- | :-- |
| White sebagai kanvas utama | Setiap section beda warna |
| Blue untuk aksi utama dan momen brand | Dun untuk teks body panjang |
| Dun untuk kehangatan yang ditahan | Kuning atau merah karena area terasa kosong |
| Almond atau Isabelline untuk pemisah section | Gradient sebagai default kartu |
| Warna komplementer saat membawa makna | Warna baru tanpa alasan brand atau semantik |
