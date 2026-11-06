# Sozo Branch Template: Dokumentasi

Snippet: [`sozo-branch.php`](./sozo-branch.php). Contoh isi data: [`sozo-branch-rest-example.json`](./sozo-branch-rest-example.json).
Desain acuan: `reference-arteri-v3.html` (Sozo Design System V1.2). Markup, class, token, breakpoint, dan animasi mengikuti referensi.

## 1. Cara pasang

1. WPCode Lite atau Code Snippets: buat snippet baru, tipe **PHP**, tempel isi `sozo-branch.php` apa adanya (file ini sengaja tanpa tag `<?php`, sama seperti `promo/index.php`).
2. Lokasi: **Run everywhere**. Aktifkan.
3. Di tiap halaman cabang (Elementor), taruh satu **Shortcode widget** berisi `[sozo_branch]`.
4. Isi data tiap cabang: manual lewat kotak **Data Cabang Sozo** (bagian 3a) atau lewat REST (bagian 3b). Halaman jadi halaman cabang saat `sz_branch_name` terisi.
5. Purge LiteSpeed, lalu Elementor > Tools > Regenerate CSS & Data.
6. Poppins: kalau tema belum memuatnya, tambahkan `define('SZ_BRANCH_LOAD_FONT', true);` di `wp-config.php`.

## 2. Daftar meta key

Semua terdaftar di post type `page`, `single`, `show_in_rest`. Nilai disanitasi saat disimpan. Field JSON dikirim sebagai **string JSON**; JSON yang tidak valid disimpan kosong dan tidak dirender.

### Wajib

| Key | Tipe | Contoh | Catatan |
| :-- | :-- | :-- | :-- |
| `sz_branch_name` | string | `Arteri Pondok Indah` | Nama persis di Google Maps / GBP, tanpa awalan "Sozo Skin Clinic - ". Halaman dianggap halaman cabang hanya kalau field ini terisi. |

### Hero dan info cabang

| Key | Tipe | Contoh | Catatan |
| :-- | :-- | :-- | :-- |
| `sz_lead` | string | `Mencari klinik kecantikan di ...` | Paragraf intro, teks polos. Kosong: tidak tampil. |
| `sz_rating` | number | `4.7` | 0 sampai 5. Kosong: blok rating hilang. |
| `sz_review_count` | integer | `1856` | Tampil sebagai "1.856 ulasan Google". Hanya tampil bersama `sz_rating`. |
| `sz_gbp_url` | string (URL) | `https://maps.app.goo.gl/...` | Link ulasan, `hasMap`, `sameAs`. Kosong: link ulasan jatuh ke pencarian Maps dari `sz_maps_query`. |
| `sz_maps_query` | string | `Sozo Skin Clinic - Arteri Pondok Indah` | Tujuan tombol Petunjuk Arah. Default `Sozo Skin Clinic - {name}`. |
| `sz_hours` | string (JSON) | lihat di bawah | 7 hari lengkap, `day` 0 sampai 6 (0 = Minggu), unik. |
| `sz_address` | string | `Jl. Sultan Iskandar Muda No.68A, ...` | Alamat tampil + tombol Salin alamat. Kosong: grup alamat tanpa kepala, tombol aksi tetap ada. |
| `sz_phone` | string | `6285175225664` | Format E.164 tanpa plus. Dipakai tombol WhatsApp dan `telephone`. Kosong: tombol WhatsApp hilang. |
| `sz_wa_src` | string | `ORG-LOK` | Kode tracking di teks WhatsApp. Default `ORG-LOK`. Hanya huruf, angka, `_`, `-`. |
| `sz_gallery` | string (JSON) | lihat di bawah | Maksimal 12 foto. Kosong: galeri hilang, kolom kiri lebar penuh. Satu foto: tanpa panah, counter, thumbnail. |

Format `sz_hours`:

```json
[{"day":1,"open":"09:00","close":"20:00"},{"day":2,"open":"09:00","close":"20:00"},
 {"day":3,"open":"09:00","close":"20:00"},{"day":4,"open":"09:00","close":"20:00"},
 {"day":5,"open":"09:00","close":"20:00"},{"day":6,"open":"09:00","close":"20:00"},
 {"day":0,"closed":true}]
```

`close` harus lebih besar dari `open`. Jadwal lewat tengah malam belum didukung.

Format `sz_gallery` (campur ID dan URL boleh):

```json
[{"id":123,"label":"tampak depan klinik"},
 {"url":"https://asset.sozoskinclinic.com/.../foto.webp","alt":"Ruang klinik","label":"ruang klinik"}]
```

Foto dengan `id` memakai ukuran `full` dan `alt` dari media library. Foto dengan `url` memakai `width` 1600 dan `height` 900 sebagai atribut.

### Alamat untuk schema

| Key | Tipe | Contoh |
| :-- | :-- | :-- |
| `sz_street` | string | `Jl. Sultan Iskandar Muda No.68A, RT.3/RW.2` |
| `sz_locality` | string | `Kebayoran Lama` (juga jadi subteks kartu Cabang Terdekat) |
| `sz_city` | string | `Jakarta Selatan` |
| `sz_region` | string | `DKI Jakarta` |
| `sz_postal_code` | string | `12240` |
| `sz_lat` | number | contoh format: `-6.2447`. Ambil nilai asli dari GBP |
| `sz_lng` | number | contoh format: `106.7822`. Ambil nilai asli dari GBP |

`PostalAddress` ikut ke schema kalau minimal satu dari street, locality, region, postal terisi. `geo` ikut kalau `sz_lat` dan `sz_lng` dua-duanya terisi. `sz_city` disimpan tapi belum dipakai schema (Yoast/Google memakai `addressLocality` dari `sz_locality`).

### Seksi opsional (render hanya kalau terisi)

| Key | Tipe | Contoh | Seksi |
| :-- | :-- | :-- | :-- |
| `sz_access` | string (HTML terbatas) | `<p>Dekat Pondok Indah Mall, parkir basement.</p>` | Cara ke Lokasi |
| `sz_treatments` | string (JSON) | `[{"name":"HIFU","url":"https://sozoskinclinic.com/hifu-treatment/"}]` | Treatment di Cabang Ini |
| `sz_faq` | string (JSON) | `[{"q":"Apakah perlu booking?","a":"Disarankan booking lewat WhatsApp."}]` | FAQ (maksimal 10, teks polos) |
| `sz_nearby` | string (JSON) | `[1201,1344,1390]` | Cabang Terdekat (ID halaman, maksimal 6) |

`sz_nearby` kosong: snippet mendaftar sibling (parent sama, punya `sz_branch_name`, published, maksimal 6, urut judul). Link "Lihat semua cabang di {parent}" tampil selama halaman punya parent publish.

## 3a. Isi data manual (dashboard)

Buka **Pages > Edit** pada halaman cabang (layar edit WordPress, bukan editor Elementor). Di bawah editor ada kotak **Data Cabang Sozo**. Kotak tertutup otomatis di halaman yang belum punya Nama Cabang; klik judulnya untuk membuka.

- Isi **Nama Cabang** dulu, lalu bagian lain. Klik **Update**. Data masuk ke post meta yang sama dengan REST.
- Jam operasional: isi 7 hari, atau centang **Libur**. Tombol "Samakan semua hari dengan Senin" mengisi semuanya sekaligus. Kalau ada hari yang kosong atau jam tutup lebih kecil dari jam buka, jam tidak disimpan, jam lama dipertahankan, dan muncul peringatan di kotak.
- Galeri: **Pilih dari Media Library** (bisa banyak foto sekaligus) atau isi ID lampiran / URL manual. Urutan baris = urutan foto.
- Treatment dan FAQ: **Tambah**, isi, **Hapus** untuk membuang baris. Baris yang dikosongkan tidak disimpan.
- Mengosongkan sebuah field lalu Update akan menghapus meta-nya dari database.
- Data tidak punya tabel sendiri. Semuanya di `wp_postmeta` milik WordPress, jadi ikut backup dan migrasi biasa.
- Save dari Elementor atau REST tidak menimpa isi kotak ini. Form hanya disimpan saat tombol Update di layar edit WordPress ditekan.

## 3b. Isi data lewat REST

```bash
curl -X POST "https://sozoskinclinic.com/wp-json/wp/v2/pages/{ID}" \
  -u "{user}:{application-password}" \
  -H "Content-Type: application/json" \
  --data @sozo-branch-rest-example.json
```

Pakai Application Password dari user dengan hak edit halaman. Kalau mengisi lewat PHP (`update_post_meta`), bungkus nilai JSON dengan `wp_slash()`; REST sudah melakukannya sendiri.

Body contoh belum berisi `sz_gbp_url`, `sz_lat`, dan `sz_lng` karena referensi tidak memuatnya. Isi dari Google Business Profile sebelum schema dianggap lengkap.

## 4. Schema (Yoast `wpseo_schema_graph`)

- Snippet tidak membuat `WebPage` atau `BreadcrumbList`. Keduanya tetap dari Yoast.
- Satu node `MedicalClinic` (`@id` = permalink + `#clinic`) ditambahkan di halaman cabang: name, url, image, telephone, address, geo, openingHoursSpecification (hari berjam sama digabung), hasMap, sameAs, `parentOrganization` yang menunjuk `@id` Organization milik Yoast.
- Node `WebPage` Yoast mendapat `about: {"@id": "...#clinic"}`. Kalau `about` sudah ada, `#clinic` ditambahkan ke daftarnya.
- `sz_faq` terisi: node `FAQPage` (`@id` = permalink + `#faq`, `isPartOf` WebPage Yoast). Teks pertanyaan dan jawaban sama persis dengan yang tampil.
- Tidak ada `aggregateRating` atau `review`. Rating hanya teks yang terlihat.

Catatan kebijakan: `AGENTS.md` §6 menandai `MedicalClinic` + `LocalBusiness` untuk lokasi cabang sebagai "tunggu keputusan sebelum generate". Snippet ini mengikuti prompt yang diberikan. Putuskan dulu apakah perlu ADR baru sebelum dipasang di production.

## 5. Catatan perilaku

- **Cache:** status "Buka sekarang / Tutup" dan sorotan hari ini dihitung di browser (WIB) dari atribut `data-open` / `data-close`, jadi aman untuk HTML yang di-cache LiteSpeed.
- **Breadcrumb:** Beranda > Lokasi > semua ancestor page (terluar dulu, label = judul page) > nama cabang. Kalau halaman `/lokasi/` sendiri adalah ancestor, "Lokasi" tidak dobel. Label terakhir memakai `sz_branch_name`, sedangkan Yoast memakai judul page atau breadcrumb title-nya. Samakan keduanya di Yoast kalau ingin schema dan tampilan identik.
- **Assets:** CSS dan JS di-inline lewat `wp_add_inline_style` / `wp_add_inline_script` dan hanya di-enqueue saat `sz_branch_name` terisi di halaman itu. Skrip inline tidak bisa memakai `defer`; skrip berjalan di footer dan menunggu DOMContentLoaded.
- **Testing:** `[sozo_branch id="123"]` merender data page 123. Halaman non-publish hanya terlihat oleh user yang bisa mengeditnya.
- **Tanpa fatal error:** semua render dan schema dibungkus `try/catch (Throwable)`. Data kurang berarti blok itu tidak keluar, bukan error.

## 6. Checklist test

Belum ada hasil test. Snippet belum dijalankan di WordPress. PHP tidak tersedia di mesin kerja, jadi yang sudah dicek hanya syntax JS dan keseimbangan kurung PHP.

**Pasang**
- [ ] Snippet aktif tanpa notice/fatal di `debug.log`.
- [ ] REST `POST` dengan body contoh menyimpan 15 meta; `GET /wp/v2/pages/{ID}` mengembalikannya di `meta`.
- [ ] Kirim `sz_hours` rusak (6 hari): tersimpan kosong, jam tidak dirender, halaman tetap jalan.

**Tampilan**
- [ ] Desktop (1280+): sama dengan `reference-arteri-v3.html`, galeri kanan setinggi kolom kiri.
- [ ] Tablet (1024 dan 768): galeri 4:3 tidak ditarik, kolom kiri tidak patah.
- [ ] Mobile (480 dan 375): urutan header, info, galeri; tombol CTA lebar penuh di ≤ 480.
- [ ] Breadcrumb di mobile (6 item) turun baris dengan rapi.
- [ ] Console browser tanpa error.
- [ ] Tidak ada elemen `position: fixed`.

**Perilaku**
- [ ] Status buka/tutup dan sorotan hari ini benar dalam WIB (uji juga dengan jam laptop di zona waktu lain).
- [ ] Hari dengan `closed: true` menampilkan "Tutup", dan status melompat ke hari buka berikutnya.
- [ ] Salin alamat jalan di HTTPS dan fallback di HTTP.
- [ ] Galeri: klik foto, panah, thumbnail, swipe, panah keyboard.
- [ ] Satu foto: panah, counter, thumbnail hilang. Nol foto: galeri hilang, kolom kiri lebar penuh.
- [ ] Dua shortcode dalam satu halaman (`[sozo_branch id="..."]`): tiap galeri dan tombol salin jalan sendiri.
- [ ] Tombol WhatsApp: teks `Halo, saya mau booking treatment sozo di Sozo Skin Clinic {name} [src:ORG-LOK]` benar, class `sozo-master-cta` ada.
- [ ] Seksi opsional kosong tidak menghasilkan heading kosong.

**Schema**
- [ ] Rich Results Test dan Schema Markup Validator: `MedicalClinic` valid, `FAQPage` valid kalau `sz_faq` terisi.
- [ ] Tidak ada `WebPage` atau `BreadcrumbList` ganda.
- [ ] `WebPage.about` menunjuk `#clinic`; `parentOrganization` menunjuk `@id` Organization Yoast.
- [ ] Urutan dan label BreadcrumbList Yoast sama dengan breadcrumb visual.
- [ ] Teks FAQ di schema sama persis dengan teks di halaman.

**Assets dan cache**
- [ ] Halaman non-cabang (home, treatment, blog): tidak ada `sz-branch` di CSS maupun JS (cek View Source).
- [ ] Setelah purge LiteSpeed + regenerate CSS Elementor, halaman cabang tampil sama.
