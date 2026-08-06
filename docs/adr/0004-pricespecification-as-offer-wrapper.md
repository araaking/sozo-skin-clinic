# ADR-0004: Service.offers Pakai Wrapper PriceSpecification

- **Status:** Accepted
- **Tanggal:** 2026-07-13 (dicatat retrospektif)
- **Sumber:** AGENTS.md §5 "Aturan Konsistensi Harga"; Dokumentasi.md §5.7, §5.14, §5.16 (refactor records)

## Context

`Service.offers` punya dua shape yang schema.org terima:

1. `Offer` dengan field harga langsung di level Offer (`price`, `priceCurrency`, `priceValidUntil`).
2. `Offer` berisi `priceSpecification: PriceSpecification` (nested object dengan `price`, `priceCurrency`, `priceValidUntil`, `validFrom`, `validThrough`, `eligibleQuantity`, dll).

Implementasi awal Sozo tidak konsisten. Halaman seperti `treatment/hair-treatment/express-hair-therapy/index.html` (PriceSpecification) ditulis dengan pola nested, sementara `skin-treatment/ipl-treatment/ipl-acne/` (Offer langsung) ditulis sebelum standar ditetapkan. Hasilnya: validator.schema.org tetap menerima keduanya, tapi codebase punya dua gaya yang membingungkan.

`PriceSpecification` lebih future-proof: dukung rentang harga (`minPrice`/`maxPrice`), periode promo (`validFrom`/`validThrough`), kelayakan kuantitas, dan harga keanggotaan. Untuk klinik dengan banyak promo musiman, struktur ini extensible tanpa rewrite.

## Decision

Default pattern: `Service.offers` → `Offer` berisi `priceSpecification: PriceSpecification`.

Field minimum di PriceSpecification:
- `price` — angka.
- `priceCurrency` — `"IDR"`.
- `priceValidUntil` — ISO 8601 date.
- `description` — opsional, mis. `"Mulai dari"`, `"Harga Promo"`.

Jangan tulis `Offer` dengan `price` langsung di level Offer (tanpa wrapper). Kalau ragu, copy struktur dari `treatment/hair-treatment/express-hair-therapy/index.html`.

## Consequences

**Positif:**
- Konsisten di seluruh treatment sub-halaman.
- Extensible untuk periode promo, harga regional, harga member tanpa ubah struktur.
- Selaras dengan praktik terbaik schema.org untuk service pricing.

**Negatif:**
- JSON sedikit lebih panjang (+1 nesting level).
- `skin-treatment/ipl-treatment/ipl-acne/` masih inkonsisten. Refactor ditunda — risiko regression tidak sebanding benefit stylistic.

**Outlier yang diketahui:**
- `skin-treatment/ipl-treatment/ipl-acne/` pakai `Offer` langsung dengan `price`/`priceCurrency`/`priceValidUntil`/`itemCondition` (tanpa `PriceSpecification`). Valid secara schema.org. Refactor target: sprint berikutnya, sekaligus dengan normalisasi file `index.html`/schema.

**Alternatif yang dipertimbangkan:**
- *Standardisasi ke Offer langsung* (lebih ringkas) — ditolak: tidak extensible untuk promo period, dan inkonsisten dengan mayoritas halaman.
- *Pakai AggregateOffer* — ditolak: AggregateOffer untuk rentang variasi, bukan single price; tidak relevan di halaman sub-treatment yang punya satu harga pasti.
