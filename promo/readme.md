# SOZO Skin Promo Engine

Plugin kustom WordPress untuk mengelola dan menampilkan galeri promo interaktif dalam bentuk slider, lightbox modal, dan tata letak modular yang dapat dipanggil menggunakan shortcode.

---

## 📌 Fitur Utama

- **Bulk Upload Galeri** — Unggah banyak gambar promo sekaligus langsung dari menu Kategori Promo di WordPress Admin.
- **Master Landing Page** — Tampilkan seluruh halaman promo siap pakai, lengkap dengan *Hero Banner*, *Promo Pembayaran*, *Galeri Slider*, *Cara Klaim*, hingga *CTA WhatsApp*.
- **Shortcode Modular** — Setiap *section* dapat dipanggil secara terpisah sesuai kebutuhan tata letak desain kamu.
- **Shortcode Spesifik Kategori** — Tampilkan *slider promo* hanya untuk kategori tertentu di halaman khusus atau artikel blog.
- **Pop-up Lightbox & Link WA Otomatis** — Klik gambar untuk memperbesar penawaran dan langsung terhubung ke WhatsApp Booking.
- **Ringan & Tanpa Dependency** — Menggunakan CSS & Vanilla JavaScript tanpa pustaka eksternal berat (seperti Slick/Swiper).

---

## 🚀 Cara Instalasi

1. Masuk ke halaman **WordPress Admin**.
2. Buka menu **Plugins > Add New** (Tambah Baru).
3. Klik tombol **Upload Plugin** di bagian atas, lalu pilih berkas ZIP plugin ini (atau salin kodenya ke folder `wp-content/plugins/sozo-promo-engine/sozo-promo-engine.php`).
4. Klik **Activate** (Aktifkan).

---

## ⚙️ Cara Mengelola Gambar Promo (Admin)

1. Buka menu **Promo SOZO > Kategori Promo** di dashboard Admin.
2. Buat kategori baru atau edit kategori yang sudah ada (misal: *Skin Treatment*, *Hair Treatment*, dll).
3. Cari bidang **Gambar Promo (Upload Banyak Sekaligus)**.
4. Klik tombol **Pilih / Upload Gambar Promo** untuk membuka WordPress Media Library.
5. Pilih atau unggah gambar-gambar promo yang kamu inginkan, lalu klik **Gunakan Gambar Ini**.
6. Klik **Update** / **Add New Category** untuk menyimpan.

---

## 📖 Panduan Penggunaan Shortcode

Kamu bisa memasukkan shortcode di bawah ini ke dalam **Page Builder** (Elementor, Divi, WPBakery), **Gutenberg Block**, atau **Classic Editor**.

### 1. Master Shortcode (Tampilan Full Landing Page)

Menampilkan seluruh *flow* landing page promo secara berurutan, lengkap dengan tombol melayang (*Floating Button*).

```text
[sozo_promo_landing]
```

### 2. Shortcode Spesifik Kategori Treatment

Menampilkan slider gambar hanya untuk satu kategori spesifik. Sangat cocok ditaruh di halaman layanan khusus atau artikel blog.

Memanggil dengan judul default (otomatis dari nama kategori):

```text
[sozo_promo_kategori slug="skin-treatment"]
```

Memanggil dengan judul custom sesuai keinginan:

```text
[sozo_promo_kategori slug="acne-scar-free" title="Promo Spesial Acne & Scar"]
```

Memanggil dengan link WhatsApp custom (campaign/halaman khusus):

```text
[sozo_promo_kategori slug="skin-treatment" wa_link="https://api.whatsapp.com/send?phone=628123456789&text=Halo%20Promo%20Skin%20Halaman%20A"]
```

> Jika `wa_link` tidak diisi, otomatis pakai link default dari `sozo_get_wa_link()`. Jika diisi, tombol "Klaim Promo Sekarang" di lightbox modal untuk gambar dari kategori tersebut akan otomatis mengarah ke link custom tersebut (via `data-custom-wa` pada `.cat-block`).

Memanggil dengan header promo (judul + subtitle + tombol "Lihat Promo Lengkapnya") — **hanya untuk `[sozo_promo_kategori]`**:

```text
[sozo_promo_kategori slug="skin-treatment" title="Promo Klinik Kecantikan SOZO Bulan Ini" subtitle="Wujudkan Kulit Impian dengan Cicilan 0%! Klaim Promonya Sekarang, Syarat & Ketentuan Berlaku." promo_url="https://sozoskinclinic.com/promo/"]
```

Header desktop tampil di kanan (`Lihat Promo Lengkapnya` + panah), di mobile jadi tombol full-width di bawah slider. Atribut lengkap:

```text
[sozo_promo_kategori slug="skin-treatment" title="Judul Custom" subtitle="Subtitle custom" promo_url="https://sozoskinclinic.com/promo/" link_text="Lihat Promo Lengkapnya" show_header="true" wa_link="https://wa.me/..."]
```

- `subtitle` — teks di bawah judul (default: "Wujudkan Kulit Impian dengan Cicilan 0%! ...")
- `promo_url` — link tombol Lihat Promo Lengkapnya (default: `https://sozoskinclinic.com/promo/`)
- `link_text` — teks tombol (default: "Lihat Promo Lengkapnya")
- `show_header` — `true` tampilkan header, `false` hanya tampilkan `cat-block-head` biasa (default: `true`)

**Daftar Slug Default:**

- `single-treatment`
- `skin-treatment`
- `acne-scar-free`
- `hair-treatment`
- `body-slimming`

### 3. Shortcode Modular Per Section

Dipakai jika kamu ingin menyusun tata letak halaman secara kustom di Page Builder (misal: memisah-misahkan antar section menggunakan Container di Elementor).

| Shortcode | Deskripsi Tampilan |
|---|---|
| `[sozo_promo_hero]` | Banner utama (judul, deskripsi, & gambar hero) |
| `[sozo_promo_special]` | Banner promo pembayaran (cicilan 0% & logo paylater/bank) |
| `[sozo_promo_list]` | Semua slider promo kategori lengkap beserta navigasi tab-nya |
| `[sozo_promo_claim]` | Langkah-langkah cara klaim promo (Step 01–04 & Syarat Ketentuan) |
| `[sozo_promo_cta]` | Blok ajakan penutup untuk konsultasi via WhatsApp |

---

## 💬 Pengaturan Link WhatsApp

Secara default, tombol booking di modal dan CTA mengarah ke link berikut:

```text
https://api.whatsapp.com/send?phone=6285175225664&text=...
```

**Opsi 1 — Ubah global (semua halaman):** perbarui nilai kembalian (*return value*) pada fungsi `sozo_get_wa_link()` yang ada di dalam berkas PHP plugin.

**Opsi 2 — Custom per shortcode (campaign/halaman khusus):** pakai atribut `wa_link` pada `[sozo_promo_kategori]`:

```text
[sozo_promo_kategori slug="hair-treatment" wa_link="https://api.whatsapp.com/send?phone=628123456789&text=Halo%20Promo%20Hair%20LP%20A"]
```

Tanpa `wa_link` = pakai default. Dengan `wa_link` = lightbox modal di kategori tersebut otomatis pakai link tersebut.