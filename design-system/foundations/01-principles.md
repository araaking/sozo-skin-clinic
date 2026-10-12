# 01 · Prinsip

Arah visual Sozo: **Modern, Clinical, Premium, Clean, Refined.** Lima kata ini jadi batasan desain. Setiap prinsip di bawah punya tes yang bisa dicek di layar.

| Prinsip | Aturan | Tes |
| :-- | :-- | :-- |
| Clarity first | Hierarki informasi jelas sebelum dekorasi ditambahkan. | Hapus semua warna dekoratif. Judul, isi, dan aksi utama masih kebaca urutannya? |
| Clinical confidence | Tipografi, kontras, spacing, dan foto terasa presisi. | Semua ukuran teks ada di skala tipografi. Semua jarak ada di skala spacing. |
| Premium through precision | Kesan premium datang dari konsistensi, bukan ornamen. | Tidak ada gradient, warna emas, atau pola dekoratif yang dipakai cuma biar "kelihatan mahal". |
| Clean canvas | White adalah kanvas utama. | Mayoritas luas halaman berwarna White. Section berwarna (Almond/Isabelline) tidak berurutan lebih dari satu. |
| Consistent primitives | Button, card, spacing, radius, border, shadow memakai token yang sama. | Tidak ada nilai mentah di CSS komponen. Lihat [tokens.css](../tokens.css). |
| Function before ornament | Setiap perlakuan visual punya fungsi: hierarki, pengelompokan, feedback, penekanan, atau navigasi. | Setiap border, shadow, dan warna bisa dijawab "ini buat apa". |
| Responsive by intent | Desktop memakai ruang horizontal. Mobile menjaga scanability dan kenyamanan tap. | Di 375px tidak ada scroll horizontal dan semua tombol utama ≥ 48px tinggi. |
| One component, one job | Satu komponen, satu tanggung jawab. | Treatment Card tidak memuat harga, rating, durasi, dan 2 CTA sekaligus. |
| Do not over-systematize | Buat komponen hanya kalau dipakai sekarang atau berulang di beberapa halaman. | Lolos 3 pertanyaan di [README §Menambah komponen](../README.md#menambah-komponen-baru). |
| Brand first, semantics second | Warna brand untuk identitas. Warna semantik untuk status. | Merah semantik (`--color-error`) tidak dipakai untuk promo. Kuning promo tidak dipakai untuk warning. |

## Model mental

- **White** adalah kanvas.
- **Resolution Blue** adalah identitas dan aksi utama.
- **Dun** adalah aksen kecantikan.
- **Almond** dan **Isabelline** memberi kelembutan dan pemisah.
- **Poppins** adalah suara tipografi.
- **Spacing dan konsistensi** yang membuat kesan premium.

Sozo harus terasa seperti **platform kecantikan klinis yang bersih**. Hindari tampilan dashboard SaaS generik dan hindari gaya majalah luxury yang penuh ornamen.

## Premium tanpa dekorasi

Premium dihasilkan oleh disiplin:

- Whitespace lega tapi terkontrol.
- Alignment yang tegas.
- Hierarki tipografi yang tajam.
- Foto berkualitas tinggi.
- Shadow yang ditahan.
- Radius konsisten.
- Palet terbatas.
- Motion yang punya tujuan.

Dilarang memakai warna emas, gradient berat, lengkungan berlebihan, atau pola ornamen sebagai jalan pintas kesan premium.

## Klinis tanpa terasa dingin

Resolution Blue membawa otoritas klinis. Dun dan Almond membawa kehangatan. White mencegah halaman terasa berat. Hasil akhirnya presisi tapi tetap manusiawi.

## Di luar scope design system

Design system duduk di atas struktur website yang sudah ada. Aturan di folder ini **tidak** mengubah:

- Information architecture website.
- Hierarki treatment (Hair Removal, Hair, Injectable, Skin, Slimming).
- Struktur navbar.
- Kategori treatment yang sudah ada.

Jangan membuat section baru cuma supaya cocok dengan konvensi design system.
