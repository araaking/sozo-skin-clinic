# Bulk Meta Editor

Plugin WordPress untuk edit massal **SEO meta title & meta description** dari satu tabel admin, lengkap dengan history perubahan, rollback satu klik, dan import/export CSV.

Dibuat untuk `sozoskinclinic.com`, tapi ditulis generik: prefix `bme_`, tanpa URL/slug yang di-hardcode, post type bisa dipilih di Settings, dan semua string translatable.

---

## Fitur

| Fitur | Detail |
| :-- | :-- |
| Bulk editor | Tabel inline untuk semua post type yang dipilih. Simpan semua perubahan via AJAX tanpa reload. |
| Conflict detection | Kalau nilai di database berubah sejak halaman dimuat (user lain edit, metabox Yoast, import lain), baris dilewati dan diberi peringatan. Tidak ada overwrite buta. |
| History | Log append-only berisi nilai lama, nilai baru, user, waktu, dan sumber perubahan (`manual`, `bulk`, `csv`, `rollback`, `baseline`). |
| Baseline | Saat aktivasi, plugin merekam nilai meta yang sudah ada sebagai titik awal history. Perubahan sebelum install tidak bisa direkonstruksi (WordPress/Yoast tidak menyimpannya), tapi nilai saat install tersimpan dan bisa jadi restore point. |
| Rollback | Tombol Restore mengembalikan nilai sebelum perubahan. Rollback menulis entri baru, tidak pernah menghapus log. |
| Import / Export CSV | Export mengikuti filter aktif. Import selalu lewat pratinjau dry-run sebelum diterapkan, diproses per batch 50 baris. |
| Settings | Pilih post type, jumlah baris per halaman, retensi log (auto-cleanup harian), dan toggle pencatatan perubahan dari luar plugin. |
| Counter karakter | Counter SEO title (60) dan meta description (160) dengan penanda warna. |

## Provider SEO

**Saat ini: Yoast SEO saja.** Plugin menulis langsung ke meta key Yoast:

- `_yoast_wpseo_title`
- `_yoast_wpseo_metadesc`

Rank Math dan AIOSEO menyusul. Adapter-nya sudah dipisah di `includes/class-bme-provider.php`: buat subclass `BME_Provider` baru, lalu kembalikan lewat filter `bme_provider`. Tidak ada logic Yoast yang bocor ke editor, history, atau CSV.

Kalau Yoast tidak aktif, plugin idle dan menampilkan admin notice. Meta dihapus (bukan disimpan sebagai string kosong) saat field dikosongkan, supaya Yoast kembali memakai template default.

## Instalasi

1. Zip folder ini (isi zip harus folder `plugin-meta-title-desc/` dengan `bulk-meta-editor.php` di dalamnya).
2. WordPress Admin > Plugins > Add New > Upload Plugin > pilih zip > Install > Activate.
3. Buka menu **Bulk Meta Editor** di sidebar admin.

Alternatif tanpa zip: salin folder ke `wp-content/plugins/` lalu aktifkan.

Saat aktivasi plugin membuat tabel `{$wpdb->prefix}bme_history` dan menjadwalkan cron `bme_daily_cleanup`.

## Alur Pakai

### Editor

1. Buka **Bulk Meta Editor > Editor**.
2. Filter dengan pencarian, status, post type, atau centang "Hanya yang meta-nya kosong".
3. Edit field yang perlu diubah. Baris yang berubah ditandai kuning.
4. Klik **Simpan Semua Perubahan**. Hasil per baris muncul di bawah field; ringkasan muncul di samping tombol.

Field yang dikosongkan akan menghapus meta (Yoast fallback ke template). Kalau ada konflik, nilai server ditampilkan di pesan error dan baris dilewati.

### Import / Export CSV

**Export** menghasilkan kolom:

```text
post_id, post_type, url, post_title, seo_title, meta_description, seo_title_before, meta_description_before, last_edited_at, last_edited_by
```

`seo_title` / `meta_description` = nilai saat ini. `seo_title_before` / `meta_description_before` = nilai sebelum perubahan terakhir (diambil dari `old_value` history non-baseline; kosong kalau post belum pernah diubah setelah plugin dipasang). Kolom before dan `last_edited_*` hanya untuk referensi — diabaikan saat import.

**Import** menerima header yang sama, dengan alias: `id`/`post` untuk `post_id`, `permalink`/`link` untuk `url`, `meta_title`/`title`/`yoast_title` untuk `seo_title`, `meta_desc`/`description`/`metadesc`/`yoast_metadesc` untuk `meta_description`. Pemisah kolom dideteksi otomatis (`,` atau `;`).

Aturan import:

- Baris dicocokkan lewat `post_id`; kalau kosong, fallback ke `url` via `url_to_postid()`.
- Upload tidak langsung mengubah data. Muncul pratinjau dry-run: berapa baris update, baru, tidak berubah, dan error.
- Sel kosong dilewati. Centang "Terapkan juga sel kosong sebagai penghapusan meta" kalau ingin sel kosong menghapus meta.
- Maksimal 5.000 baris per file, diproses 50 baris per request.
- File disimpan sementara di `wp-content/uploads/bme-imports/` dan dihapus setelah apply atau otomatis setelah 24 jam.
- Semua perubahan tercatat di history dengan sumber `Import CSV`.

### History

- Filter per post (autocomplete), field, user, sumber, dan rentang tanggal.
- Diff ditampilkan sebagai "SEBELUM" dan "SESUDAH".
- Tombol Restore mengembalikan nilai SEBELUM. Setelah restore, halaman redirect (PRG) supaya refresh tidak mengulang aksi.
- Toggle **Perubahan dari luar plugin** di Settings menentukan apakah edit dari metabox Yoast/editor klasik ikut dicatat. Default aktif.

### Baseline

Baseline adalah titik awal history. Saat plugin diaktifkan, plugin menyalin nilai `_yoast_wpseo_title` dan `_yoast_wpseo_metadesc` yang sudah ada ke tabel history dengan sumber `Baseline (sebelum plugin)`. Jadi:

- Nilai lama yang sudah terlanjur ada tetap terekam, walau riwayat perubahannya tidak bisa direkonstruksi.
- Baris baseline bisa dipakai tombol Restore untuk kembali ke nilai saat plugin dipasang.
- Baseline **tidak dihapus** oleh retensi log, dan tidak ikut dihitung sebagai "terakhir diubah" di editor.
- Kalau tabel baru kosong dan tabel legacy `{$wpdb->prefix}sozo_meta_history` ada, baris legacy tetap dimigrasikan lebih dulu.
- Tombol **Ambil Baseline Sekarang** di Settings menjalankan ulang baseline untuk post+field yang belum punya baris baseline. Data lama tidak ditimpa, jadi aman diulang.

## Settings

| Opsi | Default | Keterangan |
| :-- | :-- | :-- |
| Post type | `page` | Hanya post type publik. Minimal satu; kalau kosong fallback ke `page`. |
| Baris per halaman | 20 | Berlaku di editor dan history (10–100). |
| Retensi log | 0 | 0 = simpan selamanya. Pembersihan jalan sekali sehari. |
| Catat perubahan dari luar plugin | Aktif | Nonaktifkan kalau hanya ingin mencatat perubahan lewat plugin ini. |

## Keamanan

- Semua form dan aksi memakai nonce + `check_admin_referer`.
- Akses halaman mengikuti kapabilitas post type (`edit_pages` untuk Page, `edit_posts` untuk Post), dicek ulang per post dengan `edit_post`.
- Input disanitasi: `sanitize_text_field` untuk title, `sanitize_textarea_field` untuk description.
- Export CSV melindungi dari CSV injection (cell yang diawali `=`, `+`, `-`, `@` diberi apostrof; apostrof dilepas lagi saat import).
- Tidak ada kredensial atau URL eksternal yang di-hardcode.

## Developer

Filter untuk menambah provider:

```php
add_filter( 'bme_provider', function ( $provider ) {
	if ( class_exists( 'My_RankMath_Provider' ) ) {
		return new My_RankMath_Provider();
	}
	return $provider;
} );
```

Skema tabel:

```text
{$wpdb->prefix}bme_history
  id          bigint unsigned AUTO_INCREMENT
  post_id     bigint unsigned
  field_key   varchar(50)      -- _yoast_wpseo_title | _yoast_wpseo_metadesc
  old_value   text
  new_value   text
  source      varchar(20)      -- manual | bulk | csv | rollback
  user_id     bigint unsigned
  created_at  datetime
  INDEX (post_id, field_key), INDEX (created_at), INDEX (source)
```

Catatan: plugin menulis lewat `update_post_meta`/`delete_post_meta`. Yoast punya watcher di hook `updated_post_meta` yang otomatis menyinkronkan tabel indexable-nya, jadi tidak perlu memanggil `WPSEO_Meta::set_value()`. Jangan ganti tanpa alasan.

### Migrasi dari versi lama

Kalau tabel `{$wpdb->prefix}sozo_meta_history` (prefix lama) ada dan tabel baru masih kosong, barisnya disalin otomatis saat upgrade. Tabel lama tidak dihapus.

## Uninstall

`uninstall.php` menghapus tabel `bme_history`, option `bme_settings` dan `bme_db_version`, serta cron `bme_daily_cleanup`. Tabel legacy `sozo_meta_history` sengaja tidak dihapus.

## Changelog

### 1.2.0

- Export CSV tambah kolom `seo_title_before` dan `meta_description_before` (nilai sebelum perubahan terakhir per field). Kolom baru diabaikan saat import.

### 1.1.0

- Baseline otomatis saat aktivasi: nilai meta lama direkam sebagai titik awal, dengan tombol "Ambil Baseline Sekarang" di Settings.
- Baseline dikecualikan dari retensi log dan dari kolom "terakhir diubah".

### 1.0.0

- Rilis general: prefix `bme_`, provider adapter, settings, AJAX save, conflict detection, history + rollback, CSV import/export, cron cleanup.
