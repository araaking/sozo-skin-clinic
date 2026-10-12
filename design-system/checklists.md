# Checklists

Checklist per komponen ada di file komponennya masing-masing. File ini berisi checklist lintas halaman.

## Sebelum mendesain atau membangun halaman

1. Tentukan peran konten.
2. Pilih komponen yang sudah ada di [components/](components/).
3. Pakai token dari [tokens.css](tokens.css).
4. Susun layout dengan spacing dan grid sistem.
5. Tambah variant hanya kalau use case berulang.
6. Cek desktop dan mobile.
7. Information architecture tidak diubah kecuali ada task IA terpisah.

## Design review

- [ ] Aksi utama langsung terlihat.
- [ ] Palet brand dihormati, White dominan.
- [ ] Tipografi mengikuti skala Poppins.
- [ ] Spacing, radius, shadow dari token.
- [ ] Border punya fungsi.
- [ ] Kartu dalam satu listing seragam.
- [ ] Mobile menjaga urutan prioritas yang sama.
- [ ] Tidak ada detail dekoratif tanpa fungsi.
- [ ] Halaman tetap terbaca kalau semua warna dekoratif dihapus.
- [ ] Halaman masih terasa Sozo, bukan template generik.

## Engineering review

- [ ] Token didefinisikan di satu tempat (`tokens.css`), komponen memakai `var(--…)`.
- [ ] Tidak ada hex, rgba, atau px mentah di luar token dan pengecualian tercatat.
- [ ] `<a>` untuk navigasi, `<button>` untuk aksi.
- [ ] Gambar punya `alt`, `width`, `height`.
- [ ] Focus-visible ada di semua elemen interaktif.
- [ ] Media query hanya 1024 / 768 / 480.
- [ ] Varian one-off tercatat di exception log.
- [ ] Poppins di-load sekali, weight 400–800 saja.

## Responsive

- [ ] Container 1280px di desktop, padding 20px di mobile.
- [ ] Tidak ada scroll horizontal di 375px.
- [ ] Tidak ada teks terpotong di tombol aksi.
- [ ] Tombol aksi utama ≥ 48px tinggi di mobile.
- [ ] Treatment Card, Promo Card, Location Card dicek di mobile.

## Visual QA

- [ ] Resolution Blue konsisten (`#1A2080`, tidak ada biru lain).
- [ ] Aksen Dun ditahan.
- [ ] Almond dan Isabelline tidak dipakai berlebihan sebagai background.
- [ ] Shadow tidak berlebihan.
- [ ] Radius kartu dan tombol konsisten.
- [ ] Rasio gambar konsisten.
- [ ] Motion halus, `prefers-reduced-motion` dihormati.

## Guardrails

- Tidak ada warna brand baru tanpa alasan tercatat.
- Tidak ada radius di luar skala tanpa alasan tercatat.
- Tidak ada variant tombol baru untuk satu halaman.
- Tidak ada paragraf selebar container.
- Tidak ada interaksi dekoratif yang bersaing dengan CTA.
- Tidak ada duplikasi komponen kalau komposisi bisa menyelesaikan.
- Tidak ada informasi penting yang hanya muncul saat hover.
- Tidak ada perlakuan mobile yang menghilangkan aksi utama atau membuatnya susah di-tap.
