# Searlo rank tracking test

- Waktu: 2026-10-12T04:35:03.571Z
- Domain: sozoskinclinic.com
- gl=id, hl=id, depth=30

## Persiapan: /search/countries

Respons: success=true, engine="all". Respons tidak membawa keterangan arti daftar ini.

| Code | Nama | states | exit |
| :-- | :-- | :-- | :-- |
| US | United States | true | true |
| GB | United Kingdom | false | true |
| CA | Canada | false | true |
| AU | Australia | false | true |
| DE | Germany | false | true |
| FR | France | false | true |
| ES | Spain | false | true |
| IT | Italy | false | true |
| NL | Netherlands | false | true |
| IE | Ireland | false | true |
| IN | India | false | true |

Indonesia (ID) tidak ada di daftar 11 negara. Menurut docs Searlo, request search untuk gl di luar daftar ini dikirim dari exit AS dengan gl diteruskan ke Google. Request tes ini kemungkinan dikirim dari luar Indonesia.

## Keyword non-lokal (dasar kesimpulan)

| Keyword | rank_check | gl rank_check | search/web | Selisih | URL sama | Found sepakat | Overlap |
| :-- | --: | :-- | --: | :-- | :-- | :-- | --: |
| beauty treatment | - | ID | - | 0 | n/a | ya |  |
| hair treatment | 5 | ID | - | satu tidak ketemu | tidak | tidak |  |
| hair growth treatment | 24 | ID | - | satu tidak ketemu | tidak | tidak |  |
| perawatan rambut rontok | - | ID | - | 0 | n/a | ya |  |
| prp hair treatment | 23 | ID | - | satu tidak ketemu | tidak | tidak |  |

- Keyword dengan data lengkap: 5/5
- Posisi persis sama: 2
- Selisih ≤1: 2
- Selisih ≤3: 2
- Selisih >3 (termasuk satu ketemu, satu tidak): 3
- Status found berbeda: 3
- Rata-rata overlap top 10 /search/web vs /serp: n/a (dari 0 keyword)
- Catatan: beauty treatment, perawatan rambut rontok tidak ketemu di kedua endpoint (top 30); dihitung selisih 0.

## Catatan: keyword lokal (tidak dihitung)

| Keyword | rank_check | gl rank_check | search/web | Selisih | URL sama | Found sepakat | Overlap |
| :-- | --: | :-- | --: | :-- | :-- | :-- | --: |
| klinik kecantikan terdekat | 5 | ID | - | satu tidak ketemu | tidak | tidak |  |

- Keyword dengan data lengkap: 1/1
- Posisi persis sama: 0
- Selisih ≤1: 0
- Selisih ≤3: 0
- Selisih >3 (termasuk satu ketemu, satu tidak): 1
- Status found berbeda: 1
- Rata-rata overlap top 10 /search/web vs /serp: n/a (dari 0 keyword)

Searlo hanya mendukung level negara. Hasil organik "klinik kecantikan terdekat" di Google asli bergantung lokasi pencari, jadi angka di atas tidak mewakili hasil yang dilihat user di kota tertentu.

## Field rank di /search/web

- klinik kecantikan terdekat: tidak bisa dicek. p1: mulai - (0 item), p2: mulai - (0 item), p3: mulai - (0 item)
- beauty treatment: tidak bisa dicek. p1: mulai - (0 item), p2: mulai - (0 item), p3: mulai - (0 item)
- hair treatment: tidak bisa dicek. p1: mulai - (0 item), p2: mulai - (0 item), p3: mulai - (0 item)
- hair growth treatment: tidak bisa dicek. p1: mulai - (0 item), p2: mulai - (0 item), p3: mulai - (0 item)
- perawatan rambut rontok: tidak bisa dicek. p1: mulai - (0 item), p2: mulai - (0 item), p3: mulai - (0 item)
- prp hair treatment: tidak bisa dicek. p1: mulai - (0 item), p2: mulai - (0 item), p3: mulai - (0 item)

## Sanity check situs Indonesia (top 10 organik /serp)

Dihitung Indonesia: domain berakhiran .id (termasuk .co.id) atau ada di daftar KNOWN_ID_SITES di run.mjs. Domain lain ditampilkan supaya bisa dicek manual.

- klinik kecantikan terdekat: 5/9 situs Indonesia. Bukan/tidak dikenali: instagram.com, tiket.com, sehat.news, instagram.com
- beauty treatment: 4/8 situs Indonesia. Bukan/tidak dikenali: instagram.com, bmderma.com, vortabeautyclinic.com, youtube.com
- hair treatment: 8/10 situs Indonesia. Bukan/tidak dikenali: translate.google.com, instagram.com
- hair growth treatment: 3/8 situs Indonesia ⚠️. Bukan/tidak dikenali: diricare.com, translate.google.com, translate.google.com, translate.google.com, instagram.com
- perawatan rambut rontok: 7/8 situs Indonesia. Bukan/tidak dikenali: dove.com
- prp hair treatment: 4/9 situs Indonesia ⚠️. Bukan/tidak dikenali: ekahospital.com, translate.google.com, translate.google.com, diricare.com, youtube.com

**Peringatan:** mayoritas hasil /serp bukan situs Indonesia untuk hair growth treatment, prp hair treatment. gl=id kemungkinan tidak efektif dan posisi tidak mewakili Google Indonesia. Cek dulu daftar domain di atas, karena situs Indonesia ber-TLD .com yang tidak ada di daftar ikut terhitung "bukan".

## Credit

- Total terpakai (dari header X-Credits-Deducted, fallback credits.used): 90
- Sisa credit (X-Credits-Remaining terakhir): 2922

## Kesimpulan (5 keyword non-lokal)

- Selisih ≤1: 2/5 (syarat ≥4) → gagal
- Found sepakat 100%: gagal
- Rata-rata overlap ≥8: n/a → gagal

**Rekomendasi: pakai /rankings/check.** /search/web tidak memenuhi semua syarat di atas.

Keyword paling beda: **hair treatment** (rank_check 5, search/web tidak ketemu, overlap n/a).
