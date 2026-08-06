# ADR-0003: Homepage sebagai Knowledge Graph Hub

- **Status:** Accepted
- **Tanggal:** 2026-07-13 (dicatat retrospektif)
- **Sumber:** Dokumentasi.md §4; AGENTS.md §2, §3

## Context

Schema.org best practice: setiap entity (`Organization`, `WebSite`) dideklarasikan sekali dan direferensikan via pointer `@id`. Pendekatan awal proyek ini mendeklarasikan `Organization` dan `WebSite` lengkap di setiap halaman, yang menimbulkan:

- Validator.schema.org menandai node sebagai duplikat.
- Risiko data drift: perubahan alamat klinik berarti edit di 137+ file.
- Payload bloat: dua node besar dikirim di setiap page load.

Homepage adalah anchor natural: satu-satunya halaman yang guaranteed di-crawl, memuat brand identity lengkap, dan merupakan canonical entry point. Semua halaman lain secara logis relate ke Organization dan WebSite yang sama.

## Decision

Homepage adalah satu-satunya tempat yang mendeklarasikan lengkap node `Organization` dan `WebSite`. Semua halaman lain hanya memakai pointer `@id`:

```json
{
  "@type": "MedicalWebPage",
  "isPartOf": { "@id": "https://sozoskinclinic.com/#website" },
  "about": { "@id": "https://sozoskinclinic.com/#organization" }
}
```

Node `Organization` dan `WebSite` TIDAK boleh dideklarasikan inline di halaman non-homepage.

## Consequences

**Positif:**
- Single source of truth untuk brand identity. Ubah nomor telepon, alamat, logo di satu node, propagasi ke seluruh graph.
- Validator tidak flag duplikat (tool me-resolve pointer, Organization tetap muncul utuh saat cek halaman treatment — itu normal, bukan duplikasi).
- Payload per halaman lebih kecil.
- Knowledge graph tetap interconnected.

**Negatif:**
- Penambahan field brand-wide butuh edit satu file, tapi deteksi breakage di halaman lain lebih sulit (perlu audit cross-page berkala).
- Developer/AI yang edit schema non-homepage harus paham semantik pointer `@id`. Jangan copy-paste entity utuh dari homepage ke halaman lain.

**Alternatif yang dipertimbangkan:**
- *Re-declare Organization di setiap halaman* — status awal. Ditolak karena data drift risk + payload bloat.
- *Centralize schema di file JSON eksternal yang di-include* — lebih kompleks, tidak ada benefit tambahan dibanding `@id` pointer.
