# ADR-0002: Pakai Schema Service, Bukan Product, untuk Halaman Treatment

- **Status:** Accepted
- **Tanggal:** 2026-07-13 (dicatat retrospektif)
- **Sumber:** Dokumentasi.md §1, §3; AGENTS.md §5, §6; §5.7, §5.14, §5.15, §5.16 (refactor records)

## Context

Awalnya, halaman treatment Sozo di-markup sebagai `Product` + `Offer` dengan field `availability: InStock`, `itemCondition`, `price`, dan `priceCurrency`. Pendekatan ini menumpuk 218 rich result error di Google Search Console.

Akar masalah:
- Skema `Product` mewajibkan field untuk barang fisik (`sku`, `mpn`, `gtin`, `itemCondition`, `availability`) yang tidak relevan untuk jasa medis.
- Semantik "harga" berbeda: `Product` = barang yang dibeli dan dimiliki; `Service` = prosedur yang dibayar dan diterima.
- Taksonomi schema.org: `MedicalProcedure` adalah subtype dari `Service`, bukan `Product`.
- Google 2026 spam filter makin ketat. Over-markup `Product` untuk non-physical goods berisiko manual action.

## Decision

Untuk semua URL di bawah `/treatment/*` (kategori 2–6 di README), gunakan schema `Service` dengan `Offer` + `PriceSpecification` di dalam `offers`. Schema `Product` dicadangkan HANYA untuk rumpun `/product/*` (kategori 7).

Service node:
- `Service.name` = nama treatment.
- `Service.description` = ringkasan singkat.
- `Service.offers` = `Offer` dengan `priceSpecification: PriceSpecification` (field: `price`, `priceCurrency`, `priceValidUntil`, `description` opsional).

`Product` tidak boleh muncul di halaman treatment dengan alasan apa pun.

## Consequences

**Positif:**
- 218 rich result error hilang.
- Tidak perlu isi field `availability: InStock` atau `itemCondition` yang irrelevant.
- Sesuai taksonomi schema.org (`Service` > `MedicalProcedure`).
- Lolos Google 2026 spam filter.

**Negatif:**
- Tidak bisa pakai Product-specific rich result (price drops, stock status). Tidak relevan untuk jasa.
- Halaman tanpa harga spesifik harus menghilangkan `priceSpecification` sepenuhnya — tidak boleh diisi angka fiktif.

**Outlier yang diketahui:**
- `skin-treatment/ipl-treatment/ipl-acne/` masih pakai `Offer` langsung tanpa wrapper `PriceSpecification`. Valid secara schema.org tapi inkonsisten dengan standar tim. Refactor ditunda — risiko regression lebih besar dari benefit stylistic. Target refactor: sprint berikutnya.

**Alternatif yang dipertimbangkan:**
- *Pakai `Product` dengan sembunyikan field wajib* — ditolak: validator.schema.org tetap flag missing required fields.
- *Pakai `MedicalProcedure` sebagai top-level* — dipertimbangkan tapi `MedicalProcedure` tidak se-fleksibel `Service` untuk variasi treatment (Botox, Filler, Skin Booster, dll).
