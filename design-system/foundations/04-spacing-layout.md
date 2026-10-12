# 04 · Spacing & Layout

## Skala spacing

Base 4px, ritme 8px untuk jarak yang paling sering dipakai.

| Token | Nilai | Dipakai untuk |
| :-- | --: | :-- |
| `--space-1` | 4px | Jarak mikro di dalam ikon atau elemen kecil |
| `--space-2` | 8px | Gap ikon–label, kategori ke judul kartu, judul ke deskripsi kartu |
| `--space-3` | 12px | Eyebrow ke heading, gap antar tombol, gap aksi kartu |
| `--space-4` | 16px | Gap elemen default, heading ke deskripsi |
| `--space-5` | 24px | Padding kartu, gap grid kartu desktop, deskripsi ke CTA |
| `--space-6` | 32px | Pengelompokan konten |
| `--space-7` | 40px | Pengelompokan besar |
| `--space-8` | 48px | Jarak heading section ke isi section |
| `--space-9` | 64px | Pemisah section kecil |
| `--space-10` | 80px | Pemisah section normal |
| `--space-11` | 96px | Pemisah section besar |
| `--space-12` | 120px | Hero atau transisi besar |

### Aturan spacing

1. Semua margin, padding, dan gap memakai token. Nilai di luar skala (10px, 14px, 18px, 20px, 28px) tidak dipakai kecuali tercatat di spec komponen.
2. Step kecil (`--space-1` s/d `--space-5`) di dalam komponen. Step besar (`--space-6` ke atas) di antara grup konten dan section.
3. Di dalam kartu padat, jarak maksimal `--space-5` (24px).
4. Whitespace adalah bagian fungsional dari kesan premium.
5. Kalau ruang sempit, kurangi jarak dekoratif dulu. Jangan kecilkan teks.

## Breakpoint

| Nama | Rentang | Media query |
| :-- | :-- | :-- |
| Desktop | ≥ 1025px | (default, tanpa media query) |
| Tablet | 769px – 1024px | `@media (max-width: 1024px)` |
| Mobile | ≤ 768px | `@media (max-width: 768px)` |
| Mobile kecil | ≤ 480px | `@media (max-width: 480px)` |

Pendekatan: **desktop-first** dengan `max-width`. Hanya empat media query ini yang dipakai untuk layout. Breakpoint lain (991px, 992px, 767px, 1100px, dll) tidak dipakai di halaman baru.

## Container

| Viewport | Lebar konten maksimal | Padding kiri-kanan |
| :-- | :-- | :-- |
| Desktop | 1280px | 32px |
| Tablet | fluid | 32px |
| Mobile | fluid | 20px |

```css
.container {
  width: min(100% - (var(--container-pad) * 2), var(--container-max));
  margin-inline: auto;
}
```

Container selalu di tengah dan tidak melebar melebihi 1280px walaupun viewport lebih besar.

## Grid

| Viewport | Kolom | Gap |
| :-- | --: | --: |
| Desktop | 12 | 24px |
| Tablet | 8 | 20px |
| Mobile | 4 | 16px |

Jumlah kolom adalah alat bantu alignment. Section tidak wajib menampilkan semua kolom.

### Grid kartu

| Konten | Desktop | Tablet | Mobile |
| :-- | :-- | :-- | :-- |
| Treatment Card | 3 kolom (4 kalau konten pendek) | 2 kolom | 1 kolom, atau slider horizontal |
| Promo Card | 2–3 kolom | 2 kolom | 1 kolom, atau slider horizontal |
| Location Card | Grid kartu (pola existing) | 2 kolom | List baris kompak |

```css
.card-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: var(--grid-gap);
}
@media (max-width: 1024px) { .card-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 768px)  { .card-grid { grid-template-columns: 1fr; } }
```

## Lebar baca

Paragraf panjang tidak boleh selebar container. Pakai `max-width: var(--measure)` (65ch, sekitar 60–70 karakter per baris).

## Jarak section

| Jenis section | Desktop | Tablet | Mobile | Token |
| :-- | --: | --: | --: | :-- |
| Normal | 80px | 64px | 56px | `--section-y` |
| Besar | 96px | 80px | 64px | `--section-y-major` |
| Hero | 120px | 96px | 72px | `--section-y-hero` |

```css
.section { padding-block: var(--section-y); }
```

Token berubah otomatis per breakpoint (lihat [tokens.css](../tokens.css)), jadi section tidak perlu media query sendiri.

## Aturan responsive

### Desktop
- Pakai ruang horizontal dengan sengaja.
- Kartu 3 atau 4 kolom sesuai kepadatan konten.

### Tablet
- Kurangi gutter dulu sebelum mengecilkan konten.

### Mobile
- Stack penuh atau grid 4 kolom.
- Tap target nyaman: elemen yang bisa di-tap minimal **44 × 44px**, tombol aksi utama minimal **48px** tinggi.
- Slider horizontal hanya kalau membantu discovery dan jumlah item memang banyak.
- Jangan asal stack setiap pola desktop. Informasi yang lebih cocok jadi list kompak (contoh: Location Card) dibuat jadi list kompak.
- Tidak boleh ada scroll horizontal di level halaman.

### Aturan kompak mobile
Interface Sozo menekan scroll vertikal yang tidak perlu. Pola kompak di mobile boleh dipakai selama hierarki dan kemudahan tap tetap terjaga.
