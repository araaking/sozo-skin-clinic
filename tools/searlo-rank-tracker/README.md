# Searlo Rank Tracker

Cek posisi organik `sozoskinclinic.com` di Google Indonesia (top 30, desktop) lewat Searlo `/rankings/batch`. Hasilnya file TSV untuk di-paste ke spreadsheet, plus versi Markdown.

## Setup

1. Node.js 20 atau lebih baru. Tidak ada dependency.
2. Buat file `.env` di folder ini:
   ```
   SEARLO_API_KEY=sk_xxx
   ```
   `.env` dan `output/` sudah masuk `.gitignore` folder ini. Jangan tulis key di file lain.

## Menjalankan

Dari folder ini:

```bash
node --env-file=.env run.mjs
```

Script akan:
1. Cek saldo lewat `/credit/balance`. Run batal kalau saldo kurang dari kebutuhan + 10%.
2. Kirim keyword per 20 ke `/rankings/batch`.
3. Ulang sekali lewat `/rankings/check` untuk keyword yang gagal. Kalau masih gagal, ditandai `gagal`.
4. Tulis `output/rank-YYYY-MM-DD.tsv` dan `output/rank-YYYY-MM-DD.md` (tanggal WIB). Run kedua di hari yang sama menimpa file hari itu.

Isi kolom Rank: angka posisi, `-` kalau tidak ada di top 30, `gagal` kalau request gagal. Urutan baris mengikuti `keywords.txt`.

## Mengubah keyword

Edit `keywords.txt`: satu keyword per baris. Baris kosong dan baris diawali `#` diabaikan. Duplikat (beda huruf besar/kecil dihitung sama) dicek sekali dan muncul peringatan di terminal.

## Estimasi credit

`jumlah keyword × 9` per run (3 credit per 10 hasil, depth 30). Contoh: 6 keyword = 54 credit, 50 keyword = 450 credit. Keyword yang gagal di batch tetap ditagih, dan retry lewat `/rankings/check` menambah 9 credit per keyword.
