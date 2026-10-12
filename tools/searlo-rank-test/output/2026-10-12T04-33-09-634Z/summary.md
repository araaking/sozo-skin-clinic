# Searlo rank tracking test

- Waktu: 2026-10-12T04:33:09.635Z
- Domain: sozoskinclinic.com
- gl=id, hl=id, depth=30

## Persiapan: /search/countries

Respons: success=true, engine="all". Respons tidak membawa keterangan arti daftar ini.

| Code | Nama | states | exit |
| :-- | :-- | :-- | :-- |
| US | United States | true | true |

Indonesia (ID) tidak ada di daftar 1 negara. Menurut docs Searlo, request search untuk gl di luar daftar ini dikirim dari exit AS dengan gl diteruskan ke Google. Request tes ini kemungkinan dikirim dari luar Indonesia.

## Request gagal

- hair treatment: rankings/check gagal: HTTP 503 UPSTREAM_UNAVAILABLE down

## Keyword non-lokal (dasar kesimpulan)

| Keyword | rank_check | gl rank_check | search/web | Selisih | URL sama | Found sepakat | Overlap |
| :-- | --: | :-- | --: | :-- | :-- | :-- | --: |
| beauty treatment | 3 | id | 3 | 0 | ya | ya | 10 |
| hair treatment | error | error | 3 | error | error | error | 10 |
| hair growth treatment | 3 | id | 3 | 0 | ya | ya | 10 |
| perawatan rambut rontok | 3 | id | 3 | 0 | ya | ya | 10 |
| prp hair treatment | 3 | id | 3 | 0 | ya | ya | 10 |

- Keyword dengan data lengkap: 4/5
- Posisi persis sama: 4
- Selisih ≤1: 4
- Selisih ≤3: 4
- Selisih >3 (termasuk satu ketemu, satu tidak): 0
- Status found berbeda: 0
- Rata-rata overlap top 10 /search/web vs /serp: 10.0 (dari 5 keyword)

## Catatan: keyword lokal (tidak dihitung)

| Keyword | rank_check | gl rank_check | search/web | Selisih | URL sama | Found sepakat | Overlap |
| :-- | --: | :-- | --: | :-- | :-- | :-- | --: |
| klinik kecantikan terdekat | 3 | id | 3 | 0 | ya | ya | 10 |

- Keyword dengan data lengkap: 1/1
- Posisi persis sama: 1
- Selisih ≤1: 1
- Selisih ≤3: 1
- Selisih >3 (termasuk satu ketemu, satu tidak): 0
- Status found berbeda: 0
- Rata-rata overlap top 10 /search/web vs /serp: 10.0 (dari 1 keyword)

Searlo hanya mendukung level negara. Hasil organik "klinik kecantikan terdekat" di Google asli bergantung lokasi pencari, jadi angka di atas tidak mewakili hasil yang dilihat user di kota tertentu.

## Field rank di /search/web

- klinik kecantikan terdekat: di-reset per halaman. p1: mulai 1 (10 item), p2: mulai 1 (10 item), p3: mulai 1 (10 item)
- beauty treatment: di-reset per halaman. p1: mulai 1 (10 item), p2: mulai 1 (10 item), p3: mulai 1 (10 item)
- hair treatment: di-reset per halaman. p1: mulai 1 (10 item), p2: mulai 1 (10 item), p3: mulai 1 (10 item)
- hair growth treatment: di-reset per halaman. p1: mulai 1 (10 item), p2: mulai 1 (10 item), p3: mulai 1 (10 item)
- perawatan rambut rontok: di-reset per halaman. p1: mulai 1 (10 item), p2: mulai 1 (10 item), p3: mulai 1 (10 item)
- prp hair treatment: di-reset per halaman. p1: mulai 1 (10 item), p2: mulai 1 (10 item), p3: mulai 1 (10 item)

## Sanity check situs Indonesia (top 10 organik /serp)

Dihitung Indonesia: domain berakhiran .id (termasuk .co.id) atau ada di daftar KNOWN_ID_SITES di run.mjs. Domain lain ditampilkan supaya bisa dicek manual.

- klinik kecantikan terdekat: 6/10 situs Indonesia. Bukan/tidak dikenali: healthline.com, webmd.com, mayoclinic.org, x.com
- beauty treatment: 6/10 situs Indonesia. Bukan/tidak dikenali: healthline.com, webmd.com, mayoclinic.org, x.com
- hair treatment: 6/10 situs Indonesia. Bukan/tidak dikenali: healthline.com, webmd.com, mayoclinic.org, x.com
- hair growth treatment: 6/10 situs Indonesia. Bukan/tidak dikenali: healthline.com, webmd.com, mayoclinic.org, x.com
- perawatan rambut rontok: 6/10 situs Indonesia. Bukan/tidak dikenali: healthline.com, webmd.com, mayoclinic.org, x.com
- prp hair treatment: 6/10 situs Indonesia. Bukan/tidak dikenali: healthline.com, webmd.com, mayoclinic.org, x.com

## Credit

- Total terpakai (dari header X-Credits-Deducted, fallback credits.used): 30
- Sisa credit (X-Credits-Remaining terakhir): 2969

## Kesimpulan (5 keyword non-lokal)

Data tidak lengkap (4/5 keyword). Kesimpulan tidak bisa ditarik; ulangi tes untuk keyword yang gagal.
