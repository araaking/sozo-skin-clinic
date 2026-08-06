# ADR-0005: Tetap Pasang FAQPage Schema Meski Google Hapus FAQ Rich Result

- **Status:** Accepted
- **Tanggal:** 2026-07-13 (dicatat retrospektif)
- **Sumber:** Dokumentasi.md §3 "Catatan Penting (Update Google 2026)"

## Context

Per 7 Mei 2026, Google menghapus FAQ rich results dari Search. Dukungan di Rich Results Test (RRT) dihapus Juni 2026, dan Search Console API menyusul Agustus 2026. Tampilan dropdown FAQ di SERP sudah tidak ada lagi.

Akar keputusan untuk tetap memasang FAQPage:
- `FAQPage` tetap tipe schema.org yang valid. Validasi struktural di validator.schema.org masih menerima dan menampilkan node-nya.
- AI/LLM (termasuk yang dipakai di generative search, ChatGPT browsing, Perplexity, dsb.) masih menggunakan schema untuk Q&A extraction.
- Bing, voice assistant (Google Assistant, Alexa), dan beberapa surface lain masih mendukung FAQ rich result.
- Penghapusan Google bukan berarti schema-nya mati — hanya SERP display yang hilang.
- 70+ halaman treatment Sozo sudah terlanjur terpasang FAQPage. Menghapus massal berarti kerja besar tanpa benefit jelas.

## Decision

Pertahankan node `FAQPage` di `@graph` untuk semua halaman yang punya konten FAQ. RRT tidak akan menampilkan FAQ sebagai rich result (itu normal, bukan error). Validator.schema.org tetap menampilkan node FAQPage lengkap.

Catat di AGENTS.md bahwa "FAQ tidak muncul di RRT = expected behavior, bukan bug". Pertahankan juga §3 Dokumentasi.md sebagai pusat catatan tentang update Google 2026.

## Consequences

**Positif:**
- Backward compatibility kalau Google re-introduce FAQ rich result di masa depan.
- Konten FAQ tetap bisa dipakai oleh AI/LLM untuk menjawab query user di surface lain.
- Bing, voice assistant, dan beberapa SERP fitur lain masih benefit.
- 70+ halaman existing tidak perlu di-decommission.

**Negatif:**
- Developer baru mungkin salah paham: "FAQ tidak muncul di RRT = schema salah". Perlu penjelasan eksplisit di AGENTS.md / onboarding.
- Search Console tidak lagi report FAQ sebagai rich result, jadi error terkait FAQ tidak akan tertangkap. Kalau ada regresi di FAQPage, tidak akan muncul di monitoring GSC.

**Mitigasi:**
- Validator.schema.org jadi ground truth untuk FAQPage validation, bukan RRT.
- Internal QC checklist (Dokumentasi.md §7) sudah include "FAQ schema match dengan konten halaman" — tetap relevan.

**Alternatif yang dipertimbangkan:**
- *Hapus semua FAQPage* — ditolak: kerja besar tanpa benefit (FAQ tidak salah), AI/LLM masih butuh, dan Bing/voice masih support.
- *Konversi FAQ ke Q&A di artikel body* — bukan keputusan schema, terpisah dari keputusan ini. Bisa dilakukan tanpa menghapus FAQPage.
