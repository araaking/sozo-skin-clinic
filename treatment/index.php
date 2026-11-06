<?php
/**
 * SOZO SKIN - KATALOG TREATMENT (/treatment/)
 * File    : treatment/index.php
 * Pasang  : WPCode > PHP Snippet > Run Everywhere (Auto Insert).
 *           Snippet hanya mendaftarkan shortcode; tidak mencetak apa pun sampai shortcode dipakai.
 * Pakai   : Elementor > widget "Shortcode" (bukan Text Editor), isi: [sozo_treatment_catalog]
 * Isi     : JSON-LD (WebPage + BreadcrumbList), CSS, hero, katalog 90 kartu, pagination, CTA, JS.
 * Data    : sozo_tc_treatments() di bawah. Tambah/ubah treatment cukup edit satu baris array.
 *           'url' kosong = belum ada halaman SKU (judul tanpa link).
 *
 * SEO
 * - Semua kartu dicetak server (PHP), jadi link SKU terbaca crawler tanpa render JS.
 * - Pagination memakai ?halaman=n. Jangan ganti ke ?page=n: WordPress me-redirect ?page=2 (301) ke /treatment/.
 * - Setiap ?halaman=n berisi HTML yang sama (90 kartu), Yoast memberi canonical /treatment/.
 * - Per halaman: 12 kartu di desktop/tablet, 6 di mobile (<=768px). Nomor ?halaman mengikuti layar,
 *   jadi ?halaman=3 di HP dan di desktop berisi kartu berbeda. Tidak berpengaruh ke SEO (canonical sama).
 * - Server membaca ?halaman=n dan menyembunyikan kartu di luar halaman itu (atribut hidden),
 *   jadi halaman yang dibuka langsung tidak berkedip ke halaman 1 dulu.
 * - Tombol pagination berupa <button>, sehingga crawler tidak menemukan URL ?halaman duplikat.
 * - Filter (cari, keluhan, kategori) tidak ditulis ke URL, jadi tidak ada variasi URL yang terindeks.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Jangan daftar dua kali kalau snippet kepasang ganda.
if (!function_exists('sozo_tc_shortcode')) {

    // Kartu per halaman. Desktop/tablet 12 (grid 4 & 3 kolom), mobile <=768px 6 (grid 2 kolom).
    // Server tidak tahu ukuran layar, jadi HTML awal memakai angka desktop; JS memangkas ke 6 di mobile.
    function sozo_tc_page_size() {
        return 12;
    }

    function sozo_tc_page_size_mobile() {
        return 6;
    }

    function sozo_tc_wa_url() {
        return 'https://api.whatsapp.com/send?phone=6285175225664&text=Halo%2C%20saya%20mau%20booking%20promo%20treatment%20General%20di%20Sozo%20%5Bsrc%3AORG-General%5D';
    }

    // Chip keluhan: id dipakai di 'concerns' kartu (dipisah spasi).
    function sozo_tc_concerns() {
        return array(
            array('id' => 'jerawat', 'label' => 'Jerawat'),
            array('id' => 'bekas-jerawat', 'label' => 'Bekas Jerawat'),
            array('id' => 'kusam', 'label' => 'Kulit Kusam'),
            array('id' => 'flek', 'label' => 'Flek & Melasma'),
            array('id' => 'penuaan', 'label' => 'Penuaan'),
            array('id' => 'pori', 'label' => 'Pori Besar'),
            array('id' => 'rambut-rontok', 'label' => 'Rambut Rontok'),
            array('id' => 'bulu', 'label' => 'Bulu Berlebih'),
            array('id' => 'lemak', 'label' => 'Lemak & Selulit'),
        );
    }

    // Tab kategori: cat dipakai di 'cat' kartu. 'all' = Semua.
    function sozo_tc_tabs() {
        return array(
            array('cat' => 'all', 'label' => 'Semua', 'title' => '', 'link' => '', 'desc' => ''),
            array('cat' => 'skin-booster', 'label' => 'Skin Booster', 'title' => 'Skin Booster Treatment', 'link' => 'https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/', 'desc' => 'Perawatan injeksi premium untuk hidrasi mendalam, peremajaan kulit, dan stimulasi kolagen.'),
            array('cat' => 'injectable-treatment', 'label' => 'Injectable', 'title' => 'Injectable Treatment', 'link' => 'https://sozoskinclinic.com/injectable-treatment/', 'desc' => 'Solusi perawatan estetika medis dengan injeksi botox, filler, threadlift, dan infus whitening untuk hasil yang optimal.'),
            array('cat' => 'facial-treatment', 'label' => 'Facial', 'title' => 'Facial Treatment', 'link' => 'https://sozoskinclinic.com/skin-treatment/facial-treatment/', 'desc' => 'Dapatkan kulit bersih, cerah, dan sehat dengan pilihan facial treatment terbaik dari SOZO Skin Clinic.'),
            array('cat' => 'laser-treatment', 'label' => 'Laser', 'title' => 'Laser Treatment', 'link' => 'https://sozoskinclinic.com/skin-treatment/laser-treatment/', 'desc' => 'Solusi laser modern untuk pigmentasi, tekstur kulit, peremajaan, dan penghapusan tato secara efektif.'),
            array('cat' => 'hair-treatment', 'label' => 'Hair', 'title' => 'Hair Treatment', 'link' => 'https://sozoskinclinic.com/hair-treatment/', 'desc' => 'Perawatan komprehensif untuk kulit kepala dan rambut yang lebih sehat dan lebat.'),
            array('cat' => 'hair-removal', 'label' => 'Hair Removal', 'title' => 'Hair Removal Treatment', 'link' => 'https://sozoskinclinic.com/hair-removal-treatment/', 'desc' => 'Solusi efektif dan tahan lama untuk menghilangkan bulu yang tidak diinginkan.'),
            array('cat' => 'acne-treatment', 'label' => 'Acne', 'title' => 'Acne Treatment', 'link' => 'https://sozoskinclinic.com/skin-treatment/acne-treatment/', 'desc' => 'Solusi perawatan jerawat menyeluruh mulai dari facial medis, laser, mesotherapy, hingga botox untuk kulit bebas jerawat.'),
            array('cat' => 'meso-treatment', 'label' => 'Meso', 'title' => 'Meso Treatment', 'link' => 'https://sozoskinclinic.com/meso-treatment/', 'desc' => 'Perawatan mesotherapy untuk membentuk kontur wajah, membakar lemak, mengurangi selulit, dan mencapai body goals ideal.'),
            array('cat' => 'ipl-treatment', 'label' => 'IPL', 'title' => 'IPL Treatment', 'link' => 'https://sozoskinclinic.com/skin-treatment/ipl-treatment/', 'desc' => ''),
            array('cat' => 'derma-peel', 'label' => 'Derma Peel', 'title' => 'Derma Peel', 'link' => 'https://sozoskinclinic.com/skin-treatment/derma-peel-treatment/', 'desc' => ''),
            array('cat' => 'scar-treatment', 'label' => 'Scar', 'title' => 'Scar Treatment', 'link' => 'https://sozoskinclinic.com/skin-treatment/scar-treatment/', 'desc' => ''),
            array('cat' => 'rf-treatment', 'label' => 'RF', 'title' => 'RF Treatment', 'link' => 'https://sozoskinclinic.com/radiofrequency-treatment/', 'desc' => ''),
            array('cat' => 'hifu-treatment', 'label' => 'HIFU', 'title' => 'HIFU Treatment', 'link' => 'https://sozoskinclinic.com/hifu-treatment/', 'desc' => ''),
            array('cat' => 'slimming-treatment', 'label' => 'Slimming', 'title' => 'Slimming Treatment', 'link' => 'https://sozoskinclinic.com/slimming-treatment/', 'desc' => ''),
        );
    }

    // Urutan array = urutan tampil. 'search' = kata kunci tambahan untuk kolom cari (huruf kecil).
    function sozo_tc_treatments() {
        return array(
            array('cat' => 'skin-booster', 'label' => 'Skin Booster', 'title' => 'Whitening Booster', 'url' => '', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/K-Glow-DNA-boost.webp', 'duration' => '60 Menit', 'price' => 'Rp 899.000', 'badge' => 'Baru', 'concerns' => 'kusam', 'search' => 'whitening booster skin booster skin booster treatment skin booster whitening brightening'),
            array('cat' => 'skin-booster', 'label' => 'Skin Booster', 'title' => 'Vitaran H', 'url' => '', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Rejuran-Healer1.webp', 'duration' => '75 Menit', 'price' => 'Rp 7.598.000', 'badge' => 'Baru', 'concerns' => 'penuaan', 'search' => 'vitaran h skin booster skin booster treatment skin booster anti aging regenerasi'),
            array('cat' => 'facial-treatment', 'label' => 'Facial', 'title' => 'Korean LHALA Peel', 'url' => '', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2022/05/P1123791-Peeling-2-scaled.webp', 'duration' => '60 Menit', 'price' => 'Rp 599.000', 'badge' => 'Baru', 'concerns' => 'kusam', 'search' => 'korean lhala peel facial facial treatment facial treatment peel brightening'),
            array('cat' => 'skin-booster', 'label' => 'Skin Booster', 'title' => 'Rejuran Healer', 'url' => 'https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/rejuran-healer/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Rejuran-Healer1.webp', 'duration' => '90 Menit', 'price' => 'Rp 6.499.000', 'badge' => '', 'concerns' => 'penuaan', 'search' => 'rejuran healer skin booster skin booster treatment skin booster anti aging regenerasi'),
            array('cat' => 'skin-booster', 'label' => 'Skin Booster', 'title' => 'Rejuran Skin Booster', 'url' => 'https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/rejuran-skin-booster/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Rejuran-Scar1.webp', 'duration' => '90 Menit', 'price' => 'Rp 2.999.000', 'badge' => '', 'concerns' => '', 'search' => 'rejuran skin booster skin booster skin booster treatment skin booster hidrasi'),
            array('cat' => 'skin-booster', 'label' => 'Skin Booster', 'title' => 'Rejuran HB', 'url' => 'https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/rejuran-hb-treatment/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Rejuran-HB.webp', 'duration' => '90 Menit', 'price' => 'Rp 8.198.000', 'badge' => '', 'concerns' => '', 'search' => 'rejuran hb skin booster skin booster treatment skin booster hidrasi maksimal'),
            array('cat' => 'skin-booster', 'label' => 'Skin Booster', 'title' => 'Jalupro', 'url' => 'https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/jalupro-treatment/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/09/jalupro.webp', 'duration' => '60 Menit', 'price' => 'Rp 8.598.000', 'badge' => '', 'concerns' => 'penuaan', 'search' => 'jalupro skin booster skin booster treatment skin booster kolagen mata panda'),
            array('cat' => 'skin-booster', 'label' => 'Skin Booster', 'title' => 'Profhilo', 'url' => 'https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/profhilo/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/12/profhilo-treatment-1.webp', 'duration' => '75 Menit', 'price' => 'Rp 6.999.000', 'badge' => '', 'concerns' => 'penuaan', 'search' => 'profhilo skin booster skin booster treatment skin booster remodeling kulit lifting'),
            array('cat' => 'skin-booster', 'label' => 'Skin Booster', 'title' => 'DNA Glow (DNA Salmon)', 'url' => 'https://sozoskinclinic.com/injectable-treatment/skin-booster-treatment/dna-glow/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/K-Glow-DNA-boost.webp', 'duration' => '75 Menit', 'price' => 'Rp 1.599.000', 'badge' => '', 'concerns' => 'kusam', 'search' => 'dna glow (dna salmon) skin booster skin booster treatment skin booster dna salmon cerah'),
            array('cat' => 'skin-booster', 'label' => 'Skin Booster', 'title' => 'Glass Skin Booster', 'url' => 'https://sozoskinclinic.com/injectable-treatment/skin-booster/glass-skin-booster/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Glass-Skin-Skinbooster1.webp', 'duration' => '75 Menit', 'price' => 'Rp 2.099.000', 'badge' => '', 'concerns' => 'kusam pori', 'search' => 'glass skin booster skin booster skin booster treatment skin booster glowing pori-pori'),
            array('cat' => 'skin-booster', 'label' => 'Skin Booster', 'title' => 'Exosome Face', 'url' => 'https://sozoskinclinic.com/injectable-treatment/skin-booster/exosome-skin-booster/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Exosome-Face.webp', 'duration' => '90 Menit', 'price' => 'Rp 8.398.000', 'badge' => '', 'concerns' => '', 'search' => 'exosome face skin booster skin booster treatment skin booster exosome sensitif'),
            array('cat' => 'skin-booster', 'label' => 'Skin Booster', 'title' => 'Xela Rederm', 'url' => 'https://sozoskinclinic.com/injectable-treatment/skin-booster/xela-rederm-treatment/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Xela-Rederm1.webp', 'duration' => '75 Menit', 'price' => 'Rp 8.598.000', 'badge' => '', 'concerns' => 'penuaan', 'search' => 'xela rederm skin booster skin booster treatment skin booster anti aging'),
            array('cat' => 'skin-booster', 'label' => 'Skin Booster', 'title' => 'Juvelook', 'url' => 'https://sozoskinclinic.com/injectable-treatment/skin-booster/juvelook/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/09/juvelook.webp', 'duration' => '75 Menit', 'price' => 'Rp 11.198.000', 'badge' => '', 'concerns' => 'penuaan', 'search' => 'juvelook skin booster skin booster treatment skin booster volume kolagen'),
            array('cat' => 'skin-booster', 'label' => 'Skin Booster', 'title' => 'Nucleofill', 'url' => 'https://sozoskinclinic.com/injectable-treatment/skin-booster/nucleofil-treatment/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2024/07/nucleofill-skinbooster-treatment.webp', 'duration' => '80 Menit', 'price' => 'Rp 6.499.000', 'badge' => '', 'concerns' => '', 'search' => 'nucleofill skin booster skin booster treatment skin booster regenerasi'),
            array('cat' => 'skin-booster', 'label' => 'Skin Booster', 'title' => 'Restylane Skinbooster', 'url' => 'https://sozoskinclinic.com/injectable-treatment/skin-booster/restylane-skinbooster/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Restylance-Scar.webp', 'duration' => '60 Menit', 'price' => 'Rp 7.798.000', 'badge' => '', 'concerns' => 'penuaan', 'search' => 'restylane skinbooster skin booster skin booster treatment skin booster hidrasi kolagen'),
            array('cat' => 'skin-booster', 'label' => 'Skin Booster', 'title' => 'Aqua Shine Skinbooster', 'url' => '', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/09/aquashine.webp', 'duration' => '60 Menit', 'price' => 'Rp 2.599.000', 'badge' => '', 'concerns' => '', 'search' => 'aqua shine skinbooster skin booster skin booster treatment skin booster hidrasi'),
            array('cat' => 'skin-booster', 'label' => 'Skin Booster', 'title' => 'Sozo Pink Bomb', 'url' => '', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/09/sozo-pink-bomb.webp', 'duration' => '60 Menit', 'price' => 'Rp 3.099.000', 'badge' => '', 'concerns' => 'kusam', 'search' => 'sozo pink bomb skin booster skin booster treatment skin booster mencerahkan'),
            array('cat' => 'skin-booster', 'label' => 'Skin Booster', 'title' => 'Rejuran Eye (Rejuran I)', 'url' => 'https://sozoskinclinic.com/injectable-treatment/skin-booster/rejuran-eye/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/12/Rejuran-Eye.webp', 'duration' => '45 Menit', 'price' => 'Rp 6.198.000', 'badge' => '', 'concerns' => '', 'search' => 'rejuran eye (rejuran i) skin booster skin booster treatment eye treatment mata panda'),
            array('cat' => 'skin-booster', 'label' => 'Skin Booster', 'title' => 'Panda Eye Booster', 'url' => 'https://sozoskinclinic.com/injectable-treatment/skin-booster/eye-booster/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/under-eye-filler-mata-panda-1.webp', 'duration' => '45 Menit', 'price' => 'Rp 2.099.000', 'badge' => '', 'concerns' => '', 'search' => 'panda eye booster skin booster skin booster treatment eye treatment mata panda'),
            array('cat' => 'skin-booster', 'label' => 'Skin Booster', 'title' => 'Pink Lips Booster', 'url' => 'https://sozoskinclinic.com/injectable-treatment/skin-booster/pink-lips-booster/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Landing-Page-Refreshment-Laser-Treatment_Pink-Lips.webp', 'duration' => '30 Menit', 'price' => 'Rp 1.599.000', 'badge' => '', 'concerns' => '', 'search' => 'pink lips booster skin booster skin booster treatment lip treatment bibir hitam'),
            array('cat' => 'injectable-treatment', 'label' => 'Injectable', 'title' => 'Zo-Tox / Botox Standar 10u', 'url' => '', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2024/04/zo-tox-treatment-standar-10u.webp', 'duration' => '50 Menit', 'price' => 'Konsultasikan', 'badge' => '', 'concerns' => 'penuaan', 'search' => 'zo-tox / botox standar 10u injectable injectable treatment botox anti aging kerutan'),
            array('cat' => 'injectable-treatment', 'label' => 'Injectable', 'title' => 'Zo-Tox / Botox Premium 10u', 'url' => '', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2024/04/zo-tox-treatment-premium-10u.webp', 'duration' => '50 Menit', 'price' => 'Konsultasikan', 'badge' => '', 'concerns' => 'penuaan', 'search' => 'zo-tox / botox premium 10u injectable injectable treatment botox anti aging kerutan'),
            array('cat' => 'injectable-treatment', 'label' => 'Injectable', 'title' => 'Botox Underarm', 'url' => '', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Botox-Underarm-Premium-100u.webp', 'duration' => '90 Menit', 'price' => 'Konsultasikan', 'badge' => '', 'concerns' => '', 'search' => 'botox underarm injectable injectable treatment botox hyperhidrosis ketiak'),
            array('cat' => 'injectable-treatment', 'label' => 'Injectable', 'title' => 'Korean Filler', 'url' => 'https://sozoskinclinic.com/injectable-treatment/filler-treatment/korean-filler/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Korean-Filler.webp', 'duration' => '75 Menit', 'price' => 'Konsultasikan', 'badge' => '', 'concerns' => 'penuaan', 'search' => 'korean filler injectable injectable treatment filler volume wajah kolagen'),
            array('cat' => 'injectable-treatment', 'label' => 'Injectable', 'title' => 'Premium Filler', 'url' => '', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Premium-Filler.webp', 'duration' => '75 Menit', 'price' => 'Konsultasikan', 'badge' => '', 'concerns' => 'penuaan', 'search' => 'premium filler injectable injectable treatment filler anti aging pengencangan'),
            array('cat' => 'injectable-treatment', 'label' => 'Injectable', 'title' => 'Filler Dagu', 'url' => 'https://sozoskinclinic.com/injectable-treatment/filler-treatment/filler-dagu/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Premium-Filler.webp', 'duration' => '75 Menit', 'price' => 'Konsultasikan', 'badge' => '', 'concerns' => '', 'search' => 'filler dagu injectable injectable treatment filler v-shape kontur dagu'),
            array('cat' => 'injectable-treatment', 'label' => 'Injectable', 'title' => 'Facelift (Threadlift)', 'url' => '', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2024/03/facelift-sozo-skin-clinic.webp', 'duration' => '75 Menit', 'price' => 'Konsultasikan', 'badge' => '', 'concerns' => 'penuaan', 'search' => 'facelift (threadlift) injectable injectable treatment threadlift facelift anti aging'),
            array('cat' => 'injectable-treatment', 'label' => 'Injectable', 'title' => 'Perfect Facelift', 'url' => '', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2024/03/perfect-facelift-sozo-skin-clinic.webp', 'duration' => '90 Menit', 'price' => 'Konsultasikan', 'badge' => '', 'concerns' => 'penuaan', 'search' => 'perfect facelift injectable injectable treatment threadlift facelift pengencangan'),
            array('cat' => 'injectable-treatment', 'label' => 'Injectable', 'title' => 'Nose Job (Threadlift)', 'url' => 'https://sozoskinclinic.com/injectable-treatment/threadlift-treatment/tanam-benang-hidung/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2024/03/nose-job-sozo-skin-clinic.webp', 'duration' => '60 Menit', 'price' => 'Konsultasikan', 'badge' => '', 'concerns' => '', 'search' => 'nose job (threadlift) injectable injectable treatment threadlift hidung nose job'),
            array('cat' => 'injectable-treatment', 'label' => 'Injectable', 'title' => 'Perfect Nose Job', 'url' => '', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2024/03/perfect-nose-job-sozo-skin-clinic.webp', 'duration' => '60 Menit', 'price' => 'Konsultasikan', 'badge' => '', 'concerns' => '', 'search' => 'perfect nose job injectable injectable treatment threadlift hidung nose job'),
            array('cat' => 'injectable-treatment', 'label' => 'Injectable', 'title' => 'Immune Glow Injection', 'url' => 'https://sozoskinclinic.com/injectable-treatment/infus-whitening-treatment/infus-vitamin-c-immune-glow-injection/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2023/11/perawatan-assetwhiteinfusion.webp', 'duration' => '15 Menit', 'price' => 'Rp 199.000', 'badge' => '', 'concerns' => 'kusam', 'search' => 'immune glow injection injectable injectable treatment infus whitening vitamin c cerah'),
            array('cat' => 'injectable-treatment', 'label' => 'Injectable', 'title' => 'Immune Glow Infusion', 'url' => '', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2023/11/perawatan-assetwhiteinfusion.webp', 'duration' => '60 Menit', 'price' => 'Mulai Rp 399.000', 'badge' => '', 'concerns' => 'kusam', 'search' => 'immune glow infusion injectable injectable treatment infus whitening imunitas cerah'),
            array('cat' => 'injectable-treatment', 'label' => 'Injectable', 'title' => 'Premium Glow Infusion', 'url' => '', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2023/11/perawatan-assetwhiteinfusion.webp', 'duration' => '100 Menit', 'price' => 'Rp 1.299.000', 'badge' => '', 'concerns' => 'kusam', 'search' => 'premium glow infusion injectable injectable treatment infus whitening stamina regenerasi'),
            array('cat' => 'facial-treatment', 'label' => 'Facial', 'title' => 'Mini Facial', 'url' => 'https://sozoskinclinic.com/skin-treatment/facial-treatment/mini-facial-treatment/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Landing-Page-Refreshment-Facial-Treatment_Mini-Facial.webp', 'duration' => '30 Menit', 'price' => 'Rp 149.000', 'badge' => '', 'concerns' => 'jerawat kusam', 'search' => 'mini facial facial facial treatment facial treatment komedo mencerahkan'),
            array('cat' => 'facial-treatment', 'label' => 'Facial', 'title' => 'Acne Clear Facial', 'url' => 'https://sozoskinclinic.com/skin-treatment/facial-treatment/acne-clear-facial/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Landing-Page-Refreshment-Facial-Treatment_Acne-Clear-Facial.webp', 'duration' => '45 Menit', 'price' => 'Rp 299.000', 'badge' => '', 'concerns' => 'jerawat bekas-jerawat', 'search' => 'acne clear facial facial facial treatment facial treatment acne scar solution jerawat'),
            array('cat' => 'facial-treatment', 'label' => 'Facial', 'title' => 'Skin Bright Facial', 'url' => 'https://sozoskinclinic.com/skin-treatment/facial-treatment/brightening-facial/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Landing-Page-Refreshment-Facial-Treatment_Skin-Bright-Facial.webp', 'duration' => '45 Menit', 'price' => 'Rp 299.000', 'badge' => '', 'concerns' => 'kusam', 'search' => 'skin bright facial facial facial treatment facial treatment body & face brightening solution kusam'),
            array('cat' => 'facial-treatment', 'label' => 'Facial', 'title' => 'Sozo Signature Facial', 'url' => 'https://sozoskinclinic.com/skin-treatment/facial-treatment/signature-facial/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Landing-Page-Refreshment-Facial-Treatment_SOZO-Signature-Facial.webp', 'duration' => '60 Menit', 'price' => 'Rp 699.000', 'badge' => '', 'concerns' => 'penuaan', 'search' => 'sozo signature facial facial facial treatment facial treatment anti aging solution signature'),
            array('cat' => 'facial-treatment', 'label' => 'Facial', 'title' => 'Diamond Laser Facial', 'url' => 'https://sozoskinclinic.com/skin-treatment/facial-treatment/diamond-laser-facial/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Landing-Page-Refreshment-Facial-Treatment_Diamond-Laser-Facial.webp', 'duration' => '60 Menit', 'price' => 'Rp 1.499.000', 'badge' => '', 'concerns' => 'penuaan pori', 'search' => 'diamond laser facial facial facial treatment facial treatment anti aging solution pori-pori'),
            array('cat' => 'facial-treatment', 'label' => 'Facial', 'title' => 'Sylfirm X', 'url' => 'https://sozoskinclinic.com/skin-treatment/facial-treatment/sylfirm-x/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Landing-Page-Refreshment-Facial-Treatment_Diamond-Laser-Facial.webp', 'duration' => '60 Menit', 'price' => 'Rp 7.999.000', 'badge' => '', 'concerns' => 'bekas-jerawat flek', 'search' => 'sylfirm x facial facial treatment facial treatment melasma acne scar solution'),
            array('cat' => 'facial-treatment', 'label' => 'Facial', 'title' => 'Acne Laser Facial', 'url' => 'https://sozoskinclinic.com/skin-treatment/facial-treatment/acne-laser-facial/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Landing-Page-Refreshment-Facial-Treatment_Acne-Laser-Facial.webp', 'duration' => '60 Menit', 'price' => 'Rp 1.499.000', 'badge' => '', 'concerns' => 'jerawat bekas-jerawat', 'search' => 'acne laser facial facial facial treatment facial treatment jerawat acne scar solution'),
            array('cat' => 'facial-treatment', 'label' => 'Facial', 'title' => 'Collagen Mask', 'url' => 'https://sozoskinclinic.com/skin-treatment/facial-treatment/collagen-mask/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Landing-Page-Refreshment-Facial-Treatment_Mini-Facial.webp', 'duration' => '30 Menit', 'price' => 'Rp 199.000', 'badge' => '', 'concerns' => 'kusam', 'search' => 'collagen mask facial facial treatment facial treatment glowing mask'),
            array('cat' => 'laser-treatment', 'label' => 'Laser', 'title' => 'Nano Laser', 'url' => 'https://sozoskinclinic.com/skin-treatment/laser-treatment/nano-laser-treatment/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Landing-Page-Refreshment-Laser-Treatment_Nano-Laser.webp', 'duration' => '45 Menit', 'price' => 'Rp 1.199.000', 'badge' => '', 'concerns' => 'flek penuaan', 'search' => 'nano laser laser laser treatment laser treatment pigmentasi rejuvenation'),
            array('cat' => 'laser-treatment', 'label' => 'Laser', 'title' => 'Pico Laser', 'url' => 'https://sozoskinclinic.com/skin-treatment/laser-treatment/pico-laser-treatment/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Landing-Page-Refreshment-Laser-Treatment_Pico-Laser.webp', 'duration' => '45 Menit', 'price' => 'Rp 1.199.000', 'badge' => '', 'concerns' => 'flek', 'search' => 'pico laser laser laser treatment laser treatment pigmentasi melasma'),
            array('cat' => 'laser-treatment', 'label' => 'Laser', 'title' => 'Laser CO2', 'url' => 'https://sozoskinclinic.com/skin-treatment/laser-treatment/laser-co2-treatment/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Landing-Page-Refreshment-Laser-Treatment_Laser-CO2.webp', 'duration' => '60 Menit', 'price' => 'Rp 1.199.000', 'badge' => '', 'concerns' => 'bekas-jerawat pori', 'search' => 'laser co2 laser laser treatment laser treatment bopeng pori-pori besar'),
            array('cat' => 'laser-treatment', 'label' => 'Laser', 'title' => 'Pink Lips Laser', 'url' => 'https://sozoskinclinic.com/skin-treatment/laser-treatment/pink-lips-laser-treatment/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Landing-Page-Refreshment-Laser-Treatment_Pink-Lips.webp', 'duration' => '30 Menit', 'price' => 'Rp 499.000', 'badge' => '', 'concerns' => 'penuaan', 'search' => 'pink lips laser laser laser treatment laser treatment bibir rejuvenation'),
            array('cat' => 'laser-treatment', 'label' => 'Laser', 'title' => 'Laser Tattoo Removal', 'url' => 'https://sozoskinclinic.com/skin-treatment/laser-treatment/laser-tattoo-removal-treatment/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Landing-Page-Refreshment-Laser-Treatment_Tatto-Laser.webp', 'duration' => '60 Menit', 'price' => 'Rp 499.000', 'badge' => '', 'concerns' => '', 'search' => 'laser tattoo removal laser laser treatment laser treatment tattoo removal'),
            array('cat' => 'laser-treatment', 'label' => 'Laser', 'title' => 'Laser Rejuve', 'url' => 'https://sozoskinclinic.com/skin-treatment/laser-treatment/laser-rejuve-treatment/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Landing-Page-Refreshment-Laser-Treatment_Nano-Laser.webp', 'duration' => '45 Menit', 'price' => 'Rp 1.199.000', 'badge' => '', 'concerns' => 'penuaan', 'search' => 'laser rejuve laser laser treatment laser treatment rejuvenation anti aging'),
            array('cat' => 'hair-treatment', 'label' => 'Hair', 'title' => 'Hair Grow Booster', 'url' => 'https://sozoskinclinic.com/hair-treatment/hair-grow-booster-treatment/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Landing-Page-Hair_Hair-Grow-Booster-1.webp', 'duration' => '45 Menit', 'price' => 'Rp 1.499.000', 'badge' => '', 'concerns' => 'rambut-rontok', 'search' => 'hair grow booster hair hair treatment hair care solution kerontokan'),
            array('cat' => 'hair-treatment', 'label' => 'Hair', 'title' => 'PRP Hair', 'url' => 'https://sozoskinclinic.com/hair-treatment/prp-hair-treatment/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Landing-Page-Hair_PRP-Hair-1.webp', 'duration' => '60 Menit', 'price' => 'Mulai Rp 1.749.000', 'badge' => '', 'concerns' => 'rambut-rontok', 'search' => 'prp hair hair hair treatment hair care solution kerontokan'),
            array('cat' => 'hair-treatment', 'label' => 'Hair', 'title' => 'Biolight Hair', 'url' => 'https://sozoskinclinic.com/hair-treatment/biolight-hair-treatment/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Landing-Page-Hair_Biolight-Hair-1.webp', 'duration' => '70 Menit', 'price' => 'Mulai Rp 199.000', 'badge' => '', 'concerns' => 'rambut-rontok', 'search' => 'biolight hair hair hair treatment hair care solution kerontokan'),
            array('cat' => 'hair-treatment', 'label' => 'Hair', 'title' => 'Salmon DNA Hair', 'url' => '', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Landing-Page-Hair_Salmon-DNA-Hair-1.webp', 'duration' => '80 Menit', 'price' => 'Mulai Rp 1.399.000', 'badge' => '', 'concerns' => 'rambut-rontok', 'search' => 'salmon dna hair hair hair treatment hair care solution kulit kepala'),
            array('cat' => 'hair-treatment', 'label' => 'Hair', 'title' => 'Exosome Hair 5cc', 'url' => 'https://sozoskinclinic.com/hair-treatment/exosome-hair-treatment/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Landing-Page-Hair_Exosome-Hair-5cc-1.webp', 'duration' => '80 Menit', 'price' => 'Mulai Rp 3.849.000', 'badge' => '', 'concerns' => 'rambut-rontok', 'search' => 'exosome hair 5cc hair hair treatment hair care solution kerontokan'),
            array('cat' => 'hair-treatment', 'label' => 'Hair', 'title' => 'Express Hair Therapy', 'url' => '', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Landing-Page-Hair_Hair-Therapy-1.webp', 'duration' => '45 Menit', 'price' => 'Konsultasikan', 'badge' => '', 'concerns' => 'rambut-rontok', 'search' => 'express hair therapy hair hair treatment hair care solution kulit kepala'),
            array('cat' => 'hair-treatment', 'label' => 'Hair', 'title' => 'Brow Grow', 'url' => 'https://sozoskinclinic.com/hair-treatment/treatment-alis-brow-grow/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Landing-Page-Hair_Brow-Grow-1.webp', 'duration' => '80 Menit', 'price' => 'Mulai Rp 599.000', 'badge' => '', 'concerns' => 'rambut-rontok', 'search' => 'brow grow hair hair treatment hair care solution alis & jenggot'),
            array('cat' => 'hair-treatment', 'label' => 'Hair', 'title' => 'Beard Grow', 'url' => 'https://sozoskinclinic.com/hair-treatment/beard-grow-treatment/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Landing-Page-Hair_Beard-Grow-1.webp', 'duration' => '80 Menit', 'price' => 'Mulai Rp 749.000', 'badge' => '', 'concerns' => 'rambut-rontok', 'search' => 'beard grow hair hair treatment hair care solution alis & jenggot'),
            array('cat' => 'hair-removal', 'label' => 'Hair Removal', 'title' => 'Laser Hair Removal', 'url' => 'https://sozoskinclinic.com/hair-removal-treatment/laser-hair-removal-treatment/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Landing-Page-Refreshment-Hair-Removal_Laser-Hair-Removal.webp', 'duration' => '30 Menit', 'price' => 'Mulai Rp 799.000', 'badge' => '', 'concerns' => 'bulu', 'search' => 'laser hair removal hair removal hair removal treatment hair removal solution laser'),
            array('cat' => 'hair-removal', 'label' => 'Hair Removal', 'title' => 'Underarm Removal Treatment', 'url' => 'https://sozoskinclinic.com/hair-removal-treatment/underarm-hair-removal-treatment/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Landing-Page-Refreshment-Hair-Removal_IPL-Underarm.webp', 'duration' => '20 Menit', 'price' => 'Mulai Rp 249.000', 'badge' => '', 'concerns' => 'bulu', 'search' => 'underarm removal treatment hair removal hair removal treatment hair removal solution underarm'),
            array('cat' => 'hair-removal', 'label' => 'Hair Removal', 'title' => 'Underarm Brightening Treatment', 'url' => 'https://sozoskinclinic.com/hair-removal-treatment/underarm-brightening-treatment/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Landing-Page-Refreshment-Hair-Removal_Underarm-Brightening.webp', 'duration' => '30 Menit', 'price' => 'Mulai Rp 597.000', 'badge' => '', 'concerns' => 'kusam bulu', 'search' => 'underarm brightening treatment hair removal hair removal treatment hair removal solution underarm brightening'),
            array('cat' => 'hair-removal', 'label' => 'Hair Removal', 'title' => 'Brazilian Hair Removal', 'url' => 'https://sozoskinclinic.com/hair-removal-treatment/brazilian-hair-removal-treatment/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Landing-Page-Refreshment-Hair-Removal_Brazilian.webp', 'duration' => '45 Menit', 'price' => 'Mulai Rp 1.199.000', 'badge' => '', 'concerns' => 'bulu', 'search' => 'brazilian hair removal hair removal hair removal treatment hair removal solution body'),
            array('cat' => 'hair-removal', 'label' => 'Hair Removal', 'title' => 'Body Hair Removal (Med/Large)', 'url' => 'https://sozoskinclinic.com/hair-removal-treatment/hair-removal-body-treatment/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Landing-Page-Refreshment-Hair-Removal_Hair-Removal-Large.webp', 'duration' => '60 Menit', 'price' => 'Mulai Rp 1.598.000', 'badge' => '', 'concerns' => 'bulu', 'search' => 'body hair removal (med/large) hair removal hair removal treatment hair removal solution body'),
            array('cat' => 'acne-treatment', 'label' => 'Acne', 'title' => 'Acne Micro Botox Standard', 'url' => '', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/10/Acne-Injection.webp', 'duration' => '45 Menit', 'price' => 'Konsultasikan', 'badge' => '', 'concerns' => 'jerawat', 'search' => 'acne micro botox standard acne acne treatment acne treatment botox inflamasi'),
            array('cat' => 'acne-treatment', 'label' => 'Acne', 'title' => 'Acne Micro Botox Hydration', 'url' => '', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/10/Acne-Clear-Facial-2.webp', 'duration' => '45 Menit', 'price' => 'Konsultasikan', 'badge' => '', 'concerns' => 'jerawat', 'search' => 'acne micro botox hydration acne acne treatment acne treatment botox hidrasi'),
            array('cat' => 'acne-treatment', 'label' => 'Acne', 'title' => 'Acne Clear Facial - Medical', 'url' => '', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/10/IPL-Acne-2048x2048.webp', 'duration' => '60 Menit', 'price' => 'Konsultasikan', 'badge' => '', 'concerns' => 'jerawat', 'search' => 'acne clear facial - medical acne acne treatment acne treatment facial komedo'),
            array('cat' => 'acne-treatment', 'label' => 'Acne', 'title' => 'Intense Pulse Light Acne', 'url' => '', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/10/Acne-Laser-Facial1.webp', 'duration' => '45 Menit', 'price' => 'Konsultasikan', 'badge' => '', 'concerns' => 'jerawat', 'search' => 'intense pulse light acne acne acne treatment acne treatment ipl bakteri jerawat'),
            array('cat' => 'acne-treatment', 'label' => 'Acne', 'title' => 'Acne Laser Facial', 'url' => '', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/10/Meso-Acne.webp', 'duration' => '75 Menit', 'price' => 'Konsultasikan', 'badge' => '', 'concerns' => 'jerawat bekas-jerawat', 'search' => 'acne laser facial acne acne treatment acne treatment laser bekas jerawat'),
            array('cat' => 'acne-treatment', 'label' => 'Acne', 'title' => 'Meso Acne', 'url' => '', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/10/Meso-Acne.webp', 'duration' => '45 Menit', 'price' => 'Konsultasikan', 'badge' => '', 'concerns' => 'jerawat', 'search' => 'meso acne acne acne treatment acne treatment mesotherapy inflamasi'),
            array('cat' => 'meso-treatment', 'label' => 'Meso', 'title' => 'Meso V Line', 'url' => 'https://sozoskinclinic.com/meso-treatment/meso-v-line/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/08/Untitled-design-3-1.webp', 'duration' => '60 Menit', 'price' => 'Konsultasikan', 'badge' => '', 'concerns' => '', 'search' => 'meso v line meso meso treatment meso treatment v-shape kontur wajah'),
            array('cat' => 'meso-treatment', 'label' => 'Meso', 'title' => 'Meso Slim Body Premium', 'url' => 'https://sozoskinclinic.com/meso-treatment/meso-slim-body/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/08/Untitled-design-4-1.webp', 'duration' => '75 Menit', 'price' => 'Konsultasikan', 'badge' => '', 'concerns' => 'lemak', 'search' => 'meso slim body premium meso meso treatment meso treatment slimming body goals'),
            array('cat' => 'meso-treatment', 'label' => 'Meso', 'title' => 'Meso Metabolic Boost', 'url' => 'https://sozoskinclinic.com/meso-treatment/meso-metabolic-boost/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/08/Untitled-design-5-1.webp', 'duration' => '60 Menit', 'price' => 'Konsultasikan', 'badge' => '', 'concerns' => 'lemak', 'search' => 'meso metabolic boost meso meso treatment meso treatment metabolisme bakar lemak'),
            array('cat' => 'meso-treatment', 'label' => 'Meso', 'title' => 'Meso Cellulift', 'url' => 'https://sozoskinclinic.com/meso-treatment/meso-cellulift/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/08/Untitled-design-8.webp', 'duration' => '60 Menit', 'price' => 'Konsultasikan', 'badge' => '', 'concerns' => 'lemak', 'search' => 'meso cellulift meso meso treatment meso treatment selulit body contouring'),
            array('cat' => 'meso-treatment', 'label' => 'Meso', 'title' => 'Meso BloatAway', 'url' => 'https://sozoskinclinic.com/meso-treatment/meso-bloataway/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/08/Untitled-design-9.webp', 'duration' => '60 Menit', 'price' => 'Konsultasikan', 'badge' => '', 'concerns' => 'lemak', 'search' => 'meso bloataway meso meso treatment meso treatment bakar lemak slimming'),
            array('cat' => 'ipl-treatment', 'label' => 'IPL', 'title' => 'IPL Acne', 'url' => 'https://sozoskinclinic.com/skin-treatment/ipl-treatment/ipl-acne/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Landing-Page-Refreshment-Facial-Treatment_Acne-Laser-Facial.webp', 'duration' => '30 Menit', 'price' => 'Rp 299.000', 'badge' => '', 'concerns' => 'jerawat', 'search' => 'ipl acne ipl ipl treatment ipl treatment jerawat acne'),
            array('cat' => 'ipl-treatment', 'label' => 'IPL', 'title' => 'IPL Glow', 'url' => 'https://sozoskinclinic.com/skin-treatment/ipl-treatment/ipl-glow/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Landing-Page-Refreshment-Facial-Treatment_Skin-Bright-Facial.webp', 'duration' => '30 Menit', 'price' => 'Rp 299.000', 'badge' => '', 'concerns' => 'kusam', 'search' => 'ipl glow ipl ipl treatment ipl treatment glow brightening'),
            array('cat' => 'derma-peel', 'label' => 'Derma Peel', 'title' => 'Glow Peel', 'url' => 'https://sozoskinclinic.com/skin-treatment/derma-peel-treatment/glow-peel/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Landing-Page-Refreshment-Facial-Treatment_Skin-Bright-Facial.webp', 'duration' => '45 Menit', 'price' => 'Rp 399.000', 'badge' => '', 'concerns' => 'kusam', 'search' => 'glow peel derma peel derma peel derma peel glow brightening'),
            array('cat' => 'derma-peel', 'label' => 'Derma Peel', 'title' => 'Acne Peel', 'url' => 'https://sozoskinclinic.com/skin-treatment/derma-peel-treatment/acne-peel/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Landing-Page-Refreshment-Facial-Treatment_Acne-Clear-Facial.webp', 'duration' => '45 Menit', 'price' => 'Rp 299.000', 'badge' => '', 'concerns' => 'jerawat', 'search' => 'acne peel derma peel derma peel derma peel acne komedo'),
            array('cat' => 'derma-peel', 'label' => 'Derma Peel', 'title' => 'Dazzling Glow Peel', 'url' => 'https://sozoskinclinic.com/skin-treatment/derma-peel-treatment/dazzling-glow-peel/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Landing-Page-Refreshment-Facial-Treatment_Diamond-Laser-Facial.webp', 'duration' => '60 Menit', 'price' => 'Rp 499.000', 'badge' => '', 'concerns' => 'kusam penuaan', 'search' => 'dazzling glow peel derma peel derma peel derma peel glow anti aging'),
            array('cat' => 'derma-peel', 'label' => 'Derma Peel', 'title' => 'Eternal Bloom Peel', 'url' => 'https://sozoskinclinic.com/skin-treatment/derma-peel-treatment/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Landing-Page-Refreshment-Facial-Treatment_SOZO-Signature-Facial.webp', 'duration' => '60 Menit', 'price' => 'Rp 699.000', 'badge' => '', 'concerns' => 'flek penuaan', 'search' => 'eternal bloom peel derma peel derma peel derma peel anti aging hiperpigmentasi'),
            array('cat' => 'scar-treatment', 'label' => 'Scar', 'title' => 'Rejuran Scar', 'url' => 'https://sozoskinclinic.com/skin-treatment/scar-treatment/rejuran-scar-treatment/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/10/Rejuran-Scar-2048x2048.webp', 'duration' => '90 Menit', 'price' => 'Rp 5.499.000', 'badge' => '', 'concerns' => 'bekas-jerawat', 'search' => 'rejuran scar scar scar treatment scar treatment bopeng regenerasi'),
            array('cat' => 'scar-treatment', 'label' => 'Scar', 'title' => 'Restylane Scar', 'url' => 'https://sozoskinclinic.com/skin-treatment/scar-treatment/restylane-scar/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Restylance-Scar.webp', 'duration' => '60 Menit', 'price' => 'Rp 7.798.000', 'badge' => '', 'concerns' => 'bekas-jerawat', 'search' => 'restylane scar scar scar treatment scar treatment bopeng skin booster'),
            array('cat' => 'scar-treatment', 'label' => 'Scar', 'title' => 'PRP Treatment (Face)', 'url' => 'https://sozoskinclinic.com/skin-treatment/scar-treatment/prp-treatment/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/10/PRP-Face-2048x2048.webp', 'duration' => '75 Menit', 'price' => 'Rp 1.499.000', 'badge' => '', 'concerns' => 'bekas-jerawat', 'search' => 'prp treatment (face) scar scar treatment scar treatment bopeng prp'),
            array('cat' => 'scar-treatment', 'label' => 'Scar', 'title' => 'Laser CO2 (Rejuve)', 'url' => 'https://sozoskinclinic.com/skin-treatment/laser-treatment/laser-rejuve-treatment/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Landing-Page-Refreshment-Laser-Treatment_Laser-CO2.webp', 'duration' => '75 Menit', 'price' => 'Rp 1.199.000', 'badge' => '', 'concerns' => 'bekas-jerawat penuaan', 'search' => 'laser co2 (rejuve) scar scar treatment scar treatment rejuvenation laser'),
            array('cat' => 'scar-treatment', 'label' => 'Scar', 'title' => 'Laser CO2 (Scar/Pores)', 'url' => 'https://sozoskinclinic.com/skin-treatment/scar-treatment/laser-co2-treatment/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/10/Laser-CO2-Acne-scar-_-Pores-Full-Face.webp', 'duration' => '75 Menit', 'price' => 'Rp 1.499.000', 'badge' => '', 'concerns' => 'bekas-jerawat pori', 'search' => 'laser co2 (scar/pores) scar scar treatment scar treatment bopeng pori-pori'),
            array('cat' => 'scar-treatment', 'label' => 'Scar', 'title' => 'Pore Detox', 'url' => 'https://sozoskinclinic.com/skin-treatment/scar-treatment/pores-treatment/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2026/01/Landing-Page-Scar-Package-Treatment-Scar-Repair-PRP-I.webp', 'duration' => '90 Menit', 'price' => 'Rp 599.000', 'badge' => '', 'concerns' => 'jerawat bekas-jerawat pori', 'search' => 'pore detox scar scar treatment scar treatment pori-pori komedo'),
            array('cat' => 'scar-treatment', 'label' => 'Scar', 'title' => 'Subcision', 'url' => 'https://sozoskinclinic.com/skin-treatment/scar-treatment/subsicion-treatment/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/10/Website-Acne-Campaign_USP-Before-After-Scar-Scar-Fighter-1-768x576.webp', 'duration' => '75 Menit', 'price' => 'Rp 299.000', 'badge' => '', 'concerns' => 'bekas-jerawat', 'search' => 'subcision scar scar treatment scar treatment bopeng subcision'),
            array('cat' => 'rf-treatment', 'label' => 'RF', 'title' => 'RF Body Slimming', 'url' => 'https://sozoskinclinic.com/radiofrequency-treatment/rf-body/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/08/DSC05449-edit-RF-Body-sozo-signature-1.webp', 'duration' => '60 Menit', 'price' => 'Konsultasikan', 'badge' => '', 'concerns' => 'lemak', 'search' => 'rf body slimming rf rf treatment rf treatment slimming selulit'),
            array('cat' => 'rf-treatment', 'label' => 'RF', 'title' => 'RF Wajah', 'url' => 'https://sozoskinclinic.com/radiofrequency-treatment/rf-face/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/08/Untitled-design-1-1.webp', 'duration' => '45 Menit', 'price' => 'Konsultasikan', 'badge' => '', 'concerns' => 'penuaan', 'search' => 'rf wajah rf rf treatment rf treatment anti aging pengencangan'),
            array('cat' => 'hifu-treatment', 'label' => 'HIFU', 'title' => 'Liftera HIFU', 'url' => 'https://sozoskinclinic.com/hifu-treatment/liftera-hifu/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/08/DSC09073-1-scaled.webp', 'duration' => '75 Menit', 'price' => 'Konsultasikan', 'badge' => '', 'concerns' => 'penuaan', 'search' => 'liftera hifu hifu hifu treatment hifu pengencangan kolagen'),
            array('cat' => 'hifu-treatment', 'label' => 'HIFU', 'title' => 'Signature HIFU', 'url' => '', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/08/DSC06476-scaled-HIFU.webp', 'duration' => '75 Menit', 'price' => 'Konsultasikan', 'badge' => '', 'concerns' => 'penuaan', 'search' => 'signature hifu hifu hifu treatment hifu lifting anti aging'),
            array('cat' => 'slimming-treatment', 'label' => 'Slimming', 'title' => 'Caloburn', 'url' => 'https://sozoskinclinic.com/caloburn-treatment/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/07/Caloburn.webp', 'duration' => '60 Menit', 'price' => 'Rp 999.000', 'badge' => '', 'concerns' => 'lemak', 'search' => 'caloburn sauna blanket slimming slimming treatment body detox keringat lemak body goals'),
            array('cat' => 'slimming-treatment', 'label' => 'Slimming', 'title' => 'Ultrasculpt', 'url' => 'https://sozoskinclinic.com/ultrascrupt-treatment/', 'img' => 'https://asset.sozoskinclinic.com/wp-content/uploads/2025/12/Ultrasculpt.webp', 'duration' => '30 Menit', 'price' => 'Rp 899.000', 'badge' => '', 'concerns' => 'lemak', 'search' => 'ultrasculpt ultrascrupt slimming slimming treatment body contouring otot kontraksi body goals lemak'),
        );
    }

    // Halaman aktif dari ?halaman=n, dijepit ke rentang 1..total halaman.
    function sozo_tc_current_page($total) {
        $pages = max(1, (int) ceil($total / sozo_tc_page_size()));
        $page  = isset($_GET['halaman']) ? absint(wp_unslash($_GET['halaman'])) : 1;
        return min(max(1, $page), $pages);
    }

    function sozo_tc_render_card($c, $is_hidden) {
        $has_link = $c['url'] !== '';
        ?>
        <article class="tc-card<?php echo $has_link ? ' tc-card--link' : ''; ?>" data-cat="<?php echo esc_attr($c['cat']); ?>" data-concerns="<?php echo esc_attr($c['concerns']); ?>" data-search="<?php echo esc_attr($c['search']); ?>"<?php echo $is_hidden ? ' hidden' : ''; ?>>
          <div class="tc-card__media">
            <img src="<?php echo esc_url($c['img']); ?>" alt="<?php echo esc_attr($c['title'] . ' di SOZO Skin Clinic'); ?>" width="800" height="600" loading="lazy" decoding="async">
          </div>
<?php if ($c['badge'] !== '') : ?>
          <span class="tc-card__badge"><?php echo esc_html($c['badge']); ?></span>
<?php endif; ?>
          <div class="tc-card__body">
            <p class="tc-card__category"><?php echo esc_html($c['label']); ?></p>
            <h3 class="tc-card__title"><?php if ($has_link) : ?><a href="<?php echo esc_url($c['url']); ?>"><?php echo esc_html($c['title']); ?></a><?php else : echo esc_html($c['title']); endif; ?></h3>
            <p class="tc-card__meta"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg><span><?php echo esc_html($c['duration']); ?></span></p>
            <div class="tc-card__footer">
              <p class="tc-card__price"><?php echo esc_html($c['price']); ?></p>
              <a href="<?php echo esc_url(sozo_tc_wa_url()); ?>" target="_blank" rel="noopener noreferrer" class="btn btn--primary btn--sm btn--block tc-card__action" data-wa-src="organic"><svg class="btn__icon" aria-hidden="true" focusable="false"><use href="#tc-i-wa"></use></svg><span>Buat Jadwal</span></a>
            </div>
          </div>
        </article>
        <?php
    }

    add_shortcode('sozo_treatment_catalog', 'sozo_tc_shortcode');

    function sozo_tc_shortcode() {
        $cards = sozo_tc_treatments();
        $total = count($cards);
        $per   = sozo_tc_page_size();
        $page  = sozo_tc_current_page($total);
        $start = ($page - 1) * $per;
        $end   = min($start + $per, $total);

        ob_start();
        ?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "WebPage",
      "@id": "https://sozoskinclinic.com/treatment/#webpage",
      "url": "https://sozoskinclinic.com/treatment/",
      "name": "Treatment Kecantikan di SOZO Skin Clinic",
      "description": "Daftar treatment SOZO Skin Clinic untuk kulit, wajah, rambut, dan tubuh. Cari berdasarkan nama, keluhan, atau kategori, lengkap dengan durasi dan harga.",
      "inLanguage": "id",
      "isPartOf": { "@id": "https://sozoskinclinic.com/#website" },
      "about": { "@id": "https://sozoskinclinic.com/#organization" },
      "breadcrumb": { "@id": "https://sozoskinclinic.com/treatment/#breadcrumb" }
    },
    {
      "@type": "BreadcrumbList",
      "@id": "https://sozoskinclinic.com/treatment/#breadcrumb",
      "itemListElement": [
        { "@type": "ListItem", "position": 1, "name": "Beranda", "item": "https://sozoskinclinic.com/" },
        { "@type": "ListItem", "position": 2, "name": "Treatment" }
      ]
    }
  ]
}
</script>

<!-- Font resmi Sozo: Poppins 400-800 -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
  /* ============================================================
     Token Sozo Design System V1.1, di-scope ke #sozo-react-app.
     Density compact, sama dengan halaman treatment detail.
     ============================================================ */
  #sozo-react-app {
    --color-brand-primary: #1A2080;
    --color-brand-almond: #E9DFD5;
    --color-white: #FFFFFF;
    --color-text-primary: #171715;
    --color-text-secondary: #41413E;
    --color-text-muted: #777771;
    --color-text-on-primary: #FFFFFF;
    --color-border: #DFDBD4;
    --color-border-strong: #C8C3BA;
    --color-primary-hover: #151A6D;
    --color-primary-subtle: rgba(26, 32, 128, 0.05);
    --font-family-primary: "Poppins", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    --radius-lg: 16px;
    --radius-full: 999px;
    --shadow-md: 0 8px 24px rgba(23, 23, 21, 0.07);
    --shadow-xl: 0 24px 60px rgba(23, 23, 21, 0.12);
    --focus-ring: 2px solid var(--color-brand-primary);
    --focus-ring-inverse: 2px solid var(--color-white);
    --focus-offset: 3px;
    --duration-fast: 180ms;
    --duration-card: 220ms;
    --duration-media: 400ms;
    --ease-standard: ease;
    --container-max: 1280px;
    --container-pad: 32px;
    --grid-gap: 20px;
    --section-y: 48px;

    font-family: var(--font-family-primary) !important;
    color: var(--color-text-primary) !important;
    background-color: var(--color-white) !important;
    line-height: 1.5 !important;
    -webkit-font-smoothing: antialiased;
  }
  @media (max-width: 1024px) {
    #sozo-react-app { --grid-gap: 16px; }
  }
  @media (max-width: 768px) {
    #sozo-react-app { --container-pad: 16px; --grid-gap: 10px; --section-y: 28px; }
  }
  @media (prefers-reduced-motion: reduce) {
    #sozo-react-app { --duration-fast: 0ms; --duration-card: 0ms; --duration-media: 0ms; }
  }

  #sozo-react-app,
  #sozo-react-app * { box-sizing: border-box !important; }

  #sozo-react-app h1,
  #sozo-react-app h2,
  #sozo-react-app h3,
  #sozo-react-app p,
  #sozo-react-app ol {
    margin: 0 !important;
    padding: 0 !important;
    font-family: var(--font-family-primary) !important;
  }
  #sozo-react-app a { text-decoration: none !important; }
  #sozo-react-app [hidden][hidden] { display: none !important; }

  #sozo-react-app .tc-container {
    width: min(100% - (var(--container-pad) * 2), var(--container-max)) !important;
    margin-inline: auto !important;
  }

  /* ============ BUTTON (design-system/components/button.md) ============ */
  #sozo-react-app .btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    min-height: 48px !important;
    padding: 0 24px !important;
    border: 1px solid transparent !important;
    border-radius: var(--radius-full) !important;
    font-family: var(--font-family-primary) !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    line-height: 1.2 !important;
    text-align: center !important;
    cursor: pointer !important;
    transition:
      background-color var(--duration-fast) var(--ease-standard),
      border-color var(--duration-fast) var(--ease-standard),
      color var(--duration-fast) var(--ease-standard),
      transform var(--duration-fast) var(--ease-standard) !important;
  }
  #sozo-react-app .btn:hover { transform: translateY(-1px) !important; }
  #sozo-react-app .btn:active { transform: translateY(0) !important; }
  #sozo-react-app .btn:focus-visible {
    outline: var(--focus-ring) !important;
    outline-offset: var(--focus-offset) !important;
  }
  #sozo-react-app .btn--sm {
    min-height: 40px !important;
    padding: 0 18px !important;
    font-size: 13px !important;
  }
  #sozo-react-app .btn--block { display: flex !important; width: 100% !important; }
  #sozo-react-app .btn--primary,
  #sozo-react-app .btn--primary:visited {
    background-color: var(--color-brand-primary) !important;
    color: var(--color-text-on-primary) !important;
  }
  #sozo-react-app .btn--primary:hover {
    background-color: var(--color-primary-hover) !important;
    color: var(--color-text-on-primary) !important;
  }
  #sozo-react-app .btn--outline,
  #sozo-react-app .btn--outline:visited {
    background-color: transparent !important;
    color: var(--color-brand-primary) !important;
    border-color: var(--color-brand-primary) !important;
  }
  #sozo-react-app .btn--outline:hover {
    background-color: var(--color-primary-subtle) !important;
    color: var(--color-brand-primary) !important;
  }
  #sozo-react-app .btn__icon {
    width: 16px !important;
    font-size: 16px !important;
    line-height: 1 !important;
    flex: none !important;
  }
  #sozo-react-app svg.btn__icon { height: 16px !important; fill: currentColor !important; }

  /* ============ HERO (layout lama, bahan design system. EX-06) ============ */
  #sozo-react-app .tc-hero {
    background-color: var(--color-brand-primary) !important;
    padding: 24px 0 40px !important;
  }
  #sozo-react-app .tc-hero__inner {
    max-width: 1000px !important;
    margin-inline: auto !important;
  }

  /* Breadcrumb di atas biru: teks putih */
  #sozo-react-app .breadcrumb { margin-bottom: 16px !important; }
  #sozo-react-app .breadcrumb__list {
    list-style: none !important;
    display: flex !important;
    flex-wrap: wrap !important;
    align-items: center !important;
    gap: 8px !important;
    font-size: 13px !important;
    line-height: 1.4 !important;
  }
  #sozo-react-app .breadcrumb__list li {
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    margin: 0 !important;
    list-style: none !important;
    color: var(--color-text-on-primary) !important;
  }
  #sozo-react-app .breadcrumb__list li + li::before {
    content: "/" !important;
    opacity: 0.6 !important;
  }
  #sozo-react-app .breadcrumb a,
  #sozo-react-app .breadcrumb a:visited {
    color: var(--color-text-on-primary) !important;
    font-weight: 500 !important;
    opacity: 0.85 !important;
  }
  #sozo-react-app .breadcrumb a:hover { opacity: 1 !important; text-decoration: underline !important; }
  #sozo-react-app .breadcrumb a:focus-visible {
    outline: var(--focus-ring-inverse) !important;
    outline-offset: 2px !important;
  }
  #sozo-react-app .breadcrumb__current { font-weight: 600 !important; }

  #sozo-react-app .tc-hero__text { margin-bottom: 20px !important; }
  #sozo-react-app .tc-hero__title {
    font-size: clamp(24px, 4vw, 36px) !important;
    font-weight: 700 !important;
    line-height: 1.25 !important;
    color: var(--color-text-on-primary) !important;
    margin-bottom: 8px !important;
  }
  #sozo-react-app .tc-hero__subtitle {
    font-size: 16px !important;
    line-height: 1.6 !important;
    color: var(--color-text-on-primary) !important;
  }

  #sozo-react-app .tc-hero__card {
    background-color: var(--color-white) !important;
    border-radius: var(--radius-lg) !important;
    box-shadow: var(--shadow-xl) !important;
    overflow: hidden !important;
  }
  #sozo-react-app .tc-hero__banner {
    display: flex !important;
    flex-wrap: wrap !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 16px !important;
    padding: 16px 24px !important;
    border-bottom: 1px solid var(--color-border) !important;
  }
  #sozo-react-app .tc-hero__banner-text {
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
    font-size: 18px !important;
    font-weight: 600 !important;
    line-height: 1.35 !important;
    color: var(--color-text-primary) !important;
  }
  #sozo-react-app .tc-hero__banner-text svg {
    flex: none !important;
    width: 24px !important;
    height: 24px !important;
    color: var(--color-brand-primary) !important;
  }

  #sozo-react-app .tc-hero__cats {
    display: flex !important;
    flex-wrap: wrap !important;
    justify-content: center !important;
    gap: 32px !important;
    padding: 24px !important;
  }
  #sozo-react-app .tc-hero__cat {
    flex: 0 0 85px !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    gap: 12px !important;
    border-radius: var(--radius-lg) !important;
  }
  #sozo-react-app .tc-hero__cat:focus-visible {
    outline: var(--focus-ring) !important;
    outline-offset: var(--focus-offset) !important;
  }
  #sozo-react-app .tc-hero__cat-img {
    display: block !important;
    width: 76px !important;
    height: 76px !important;
    border-radius: var(--radius-full) !important;
    background-color: var(--color-brand-almond) !important;
    overflow: hidden !important;
    transition: transform var(--duration-card) var(--ease-standard), box-shadow var(--duration-card) var(--ease-standard) !important;
  }
  #sozo-react-app .tc-hero__cat-img img {
    display: block !important;
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
  }
  /* Seluruh item adalah link: hover kartu interaktif */
  #sozo-react-app .tc-hero__cat:hover .tc-hero__cat-img {
    transform: translateY(-2px) !important;
    box-shadow: var(--shadow-md) !important;
  }
  #sozo-react-app .tc-hero__cat-title {
    font-size: 13px !important;
    font-weight: 600 !important;
    line-height: 1.3 !important;
    text-align: center !important;
    color: var(--color-text-secondary) !important;
    transition: color var(--duration-fast) var(--ease-standard) !important;
  }
  #sozo-react-app .tc-hero__cat:hover .tc-hero__cat-title { color: var(--color-brand-primary) !important; }

  /* ============ KATALOG ============ */
  #sozo-react-app .tc-catalog {
    padding-block: var(--section-y) !important;
    scroll-margin-top: 20px !important;
  }
  #sozo-react-app .tc-catalog__head { margin-bottom: 16px !important; }
  #sozo-react-app .tc-section-title {
    font-size: clamp(24px, 3vw, 32px) !important;
    font-weight: 700 !important;
    line-height: 1.25 !important;
    color: var(--color-brand-primary) !important;
    margin-bottom: 8px !important;
  }
  #sozo-react-app .tc-section-desc {
    font-size: 16px !important;
    line-height: 1.6 !important;
    color: var(--color-text-secondary) !important;
  }

  /* Search */
  #sozo-react-app .tc-search {
    position: relative !important;
    max-width: 640px !important;
    margin-bottom: 12px !important;
  }
  #sozo-react-app .tc-search__icon {
    position: absolute !important;
    top: 50% !important;
    left: 18px !important;
    width: 20px !important;
    height: 20px !important;
    transform: translateY(-50%) !important;
    color: var(--color-text-muted) !important;
    pointer-events: none !important;
  }
  #sozo-react-app .tc-search:focus-within .tc-search__icon { color: var(--color-brand-primary) !important; }
  #sozo-react-app .tc-search__input {
    display: block !important;
    width: 100% !important;
    min-height: 48px !important;
    margin: 0 !important;
    padding: 0 20px 0 50px !important;
    border: 1px solid var(--color-border-strong) !important;
    border-radius: var(--radius-full) !important;
    background-color: var(--color-white) !important;
    box-shadow: none !important;
    font-family: var(--font-family-primary) !important;
    font-size: 16px !important;
    line-height: 1.4 !important;
    color: var(--color-text-primary) !important;
    -webkit-appearance: none !important;
    appearance: none !important;
    transition: border-color var(--duration-fast) var(--ease-standard) !important;
  }
  #sozo-react-app .tc-search__input::placeholder { color: var(--color-text-muted) !important; opacity: 1 !important; }
  #sozo-react-app .tc-search__input:hover { border-color: var(--color-text-muted) !important; }
  #sozo-react-app .tc-search__input:focus { border-color: var(--color-brand-primary) !important; outline: none !important; }
  #sozo-react-app .tc-search__input:focus-visible {
    outline: var(--focus-ring) !important;
    outline-offset: var(--focus-offset) !important;
  }

  /* Chip keluhan */
  #sozo-react-app .tc-concerns {
    display: flex !important;
    flex-wrap: wrap !important;
    align-items: center !important;
    gap: 8px !important;
    margin-bottom: 12px !important;
  }
  #sozo-react-app .tc-concerns__label {
    margin-right: 4px !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    color: var(--color-text-muted) !important;
  }
  #sozo-react-app .tc-chip {
    display: inline-flex !important;
    align-items: center !important;
    min-height: 40px !important;
    margin: 0 !important;
    padding: 0 16px !important;
    border: 1px solid var(--color-border) !important;
    border-radius: var(--radius-full) !important;
    background-color: var(--color-white) !important;
    color: var(--color-text-secondary) !important;
    font-family: var(--font-family-primary) !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    line-height: 1.2 !important;
    white-space: nowrap !important;
    cursor: pointer !important;
    transition:
      background-color var(--duration-fast) var(--ease-standard),
      border-color var(--duration-fast) var(--ease-standard),
      color var(--duration-fast) var(--ease-standard) !important;
  }
  #sozo-react-app .tc-chip:hover {
    border-color: var(--color-brand-primary) !important;
    color: var(--color-brand-primary) !important;
    background-color: var(--color-white) !important;
  }
  #sozo-react-app .tc-chip[aria-pressed="true"] {
    background-color: var(--color-brand-primary) !important;
    border-color: var(--color-brand-primary) !important;
    color: var(--color-text-on-primary) !important;
  }
  #sozo-react-app .tc-chip:focus-visible {
    outline: var(--focus-ring) !important;
    outline-offset: var(--focus-offset) !important;
  }

  /* Tab kategori: satu baris, geser ke samping */
  #sozo-react-app .tc-tabs {
    display: flex !important;
    flex-wrap: nowrap !important;
    gap: 4px !important;
    overflow-x: auto !important;
    margin-bottom: 12px !important;
    border-bottom: 1px solid var(--color-border) !important;
    scrollbar-width: none !important;
  }
  #sozo-react-app .tc-tabs::-webkit-scrollbar { display: none !important; }
  #sozo-react-app .tc-tab {
    flex: none !important;
    min-height: 44px !important;
    margin: 0 !important;
    padding: 0 14px !important;
    border: none !important;
    border-bottom: 2px solid transparent !important;
    border-radius: 0 !important;
    background: none !important;
    color: var(--color-text-secondary) !important;
    font-family: var(--font-family-primary) !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    line-height: 1.2 !important;
    white-space: nowrap !important;
    cursor: pointer !important;
    transition: color var(--duration-fast) var(--ease-standard), border-color var(--duration-fast) var(--ease-standard) !important;
  }
  #sozo-react-app .tc-tab:hover { color: var(--color-brand-primary) !important; background: none !important; }
  #sozo-react-app .tc-tab[aria-pressed="true"] {
    color: var(--color-brand-primary) !important;
    font-weight: 600 !important;
    border-bottom-color: var(--color-brand-primary) !important;
  }
  #sozo-react-app .tc-tab:focus-visible {
    outline: var(--focus-ring) !important;
    outline-offset: -3px !important;
  }

  /* Deskripsi kategori + jumlah hasil */
  #sozo-react-app .tc-filter-desc {
    margin-bottom: 8px !important;
    font-size: 14px !important;
    line-height: 1.6 !important;
    color: var(--color-text-secondary) !important;
  }
  #sozo-react-app .tc-filter-desc a,
  #sozo-react-app .tc-filter-desc a:visited {
    color: var(--color-brand-primary) !important;
    font-weight: 600 !important;
    text-decoration: underline !important;
    text-underline-offset: 4px !important;
  }
  #sozo-react-app .tc-filter-desc a:focus-visible {
    outline: var(--focus-ring) !important;
    outline-offset: 2px !important;
  }
  #sozo-react-app .tc-count {
    margin-bottom: 12px !important;
    font-size: 14px !important;
    color: var(--color-text-muted) !important;
  }

  /* ============ KARTU KATALOG (Base Card, variant katalog: EX-05) ============ */
  #sozo-react-app .tc-grid {
    display: grid !important;
    grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
    gap: var(--grid-gap) !important;
  }
  /* Kartu di luar halaman aktif diberi atribut hidden oleh server (PHP); HTML tetap memuat semua link */

  #sozo-react-app .tc-card {
    position: relative !important;
    display: flex !important;
    flex-direction: column !important;
    min-width: 0 !important;
    background-color: var(--color-white) !important;
    border: 1px solid var(--color-border) !important;
    border-radius: var(--radius-lg) !important;
    overflow: hidden !important;
    transition:
      transform var(--duration-card) var(--ease-standard),
      box-shadow var(--duration-card) var(--ease-standard),
      border-color var(--duration-card) var(--ease-standard) !important;
  }
  #sozo-react-app .tc-card__media {
    aspect-ratio: 3 / 2 !important;
    overflow: hidden !important;
    background-color: var(--color-brand-almond) !important;
  }
  #sozo-react-app .tc-card__media img {
    display: block !important;
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    transition: transform var(--duration-media) var(--ease-standard) !important;
  }
  #sozo-react-app .tc-card__badge {
    position: absolute !important;
    top: 10px !important;
    left: 10px !important;
    display: inline-flex !important;
    align-items: center !important;
    min-height: 28px !important;
    padding: 0 10px !important;
    border-radius: var(--radius-full) !important;
    background-color: var(--color-brand-almond) !important;
    color: var(--color-brand-primary) !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    line-height: 1.4 !important;
    letter-spacing: 0.02em !important;
    text-transform: uppercase !important;
  }
  #sozo-react-app .tc-card__body {
    display: flex !important;
    flex-direction: column !important;
    flex: 1 !important;
    padding: 14px !important;
  }
  #sozo-react-app .tc-card__category {
    margin-bottom: 2px !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    line-height: 1.4 !important;
    letter-spacing: 0.12em !important;
    text-transform: uppercase !important;
    color: var(--color-text-muted) !important;
  }
  #sozo-react-app .tc-card__title {
    margin-bottom: 4px !important;
    font-size: 15px !important;
    font-weight: 600 !important;
    line-height: 1.35 !important;
    color: var(--color-brand-primary) !important; /* treatment-card.md: judul Resolution Blue */
  }
  #sozo-react-app .tc-card__title a,
  #sozo-react-app .tc-card__title a:visited { color: inherit !important; }
  /* Stretched link: seluruh kartu ke halaman detail (card-base.md) */
  #sozo-react-app .tc-card__title a::after {
    content: "" !important;
    position: absolute !important;
    inset: 0 !important;
    z-index: 1 !important;
  }
  #sozo-react-app .tc-card__title a:focus-visible { outline: none !important; }
  #sozo-react-app .tc-card--link:focus-within {
    outline: var(--focus-ring) !important;
    outline-offset: var(--focus-offset) !important;
  }
  #sozo-react-app .tc-card__meta {
    display: flex !important;
    align-items: center !important;
    gap: 6px !important;
    font-size: 13px !important;
    font-weight: 500 !important;
    line-height: 1.4 !important;
    color: var(--color-text-muted) !important;
  }
  #sozo-react-app .tc-card__meta svg { flex: none !important; }
  #sozo-react-app .tc-card__footer {
    margin-top: auto !important;
    padding-top: 10px !important;
  }
  #sozo-react-app .tc-card__price {
    margin-bottom: 10px !important;
    padding-top: 10px !important;
    border-top: 1px solid var(--color-border) !important;
    font-size: 17px !important;
    font-weight: 700 !important;
    line-height: 1.3 !important;
    color: var(--color-brand-primary) !important;
  }
  /* Tombol WA di atas stretched link supaya tetap bisa diklik */
  #sozo-react-app a.tc-card__action { position: relative !important; z-index: 2 !important; }

  /* Tombol "Buat Jadwal" kartu: gaya sama dengan BUAT JADWAL di halaman lokasi */
  #sozo-react-app a.tc-card__action.btn--primary,
  #sozo-react-app a.tc-card__action.btn--primary:visited {
    min-height: 40px !important;
    padding: 0 16px !important;
    border: 1.5px solid transparent !important;
    border-radius: 12px !important;
    background-color: var(--color-brand-primary) !important;
    color: var(--color-white) !important;
    box-shadow: 0 4px 12px rgba(26, 32, 128, 0.18) !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    letter-spacing: 0.02em !important;
    text-transform: uppercase !important;
  }
  #sozo-react-app a.tc-card__action.btn--primary:hover {
    background-color: var(--color-brand-primary) !important;
    color: var(--color-white) !important;
    box-shadow: 0 6px 16px rgba(26, 32, 128, 0.24) !important;
  }
  #sozo-react-app a.tc-card__action svg.btn__icon {
    width: 15px !important;
    height: 15px !important;
    fill: currentColor !important;
  }

  /* Hover hanya untuk kartu yang seluruhnya bisa diklik */
  #sozo-react-app .tc-card--link:hover {
    transform: translateY(-2px) !important;
    box-shadow: var(--shadow-md) !important;
    border-color: var(--color-border-strong) !important;
  }
  #sozo-react-app .tc-card--link:hover .tc-card__media img { transform: scale(1.03) !important; }

  /* Empty state */
  #sozo-react-app .tc-empty {
    padding: 40px 0 !important;
    text-align: center !important;
  }
  #sozo-react-app .tc-empty__icon {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 72px !important;
    height: 72px !important;
    margin: 0 auto 20px !important;
    border-radius: var(--radius-full) !important;
    background-color: var(--color-brand-almond) !important;
    color: var(--color-brand-primary) !important;
  }
  #sozo-react-app .tc-empty__title {
    margin-bottom: 8px !important;
    font-size: 22px !important;
    font-weight: 700 !important;
    line-height: 1.3 !important;
    color: var(--color-brand-primary) !important;
  }
  #sozo-react-app .tc-empty__desc {
    margin-bottom: 20px !important;
    font-size: 16px !important;
    color: var(--color-text-secondary) !important;
  }

  /* Pagination (URL ?halaman=n, diatur data.js) */
  #sozo-react-app .tc-pagination {
    display: flex !important;
    flex-wrap: wrap !important;
    justify-content: center !important;
    align-items: center !important;
    gap: 6px !important;
    margin-top: 24px !important;
  }
  #sozo-react-app .tc-page {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    min-width: 40px !important;
    height: 40px !important;
    margin: 0 !important;
    padding: 0 8px !important;
    border: none !important;
    border-radius: var(--radius-full) !important;
    background: transparent !important;
    color: var(--color-text-secondary) !important;
    font-family: inherit !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    line-height: 1 !important;
    cursor: pointer !important;
    transition: background-color 0.2s ease, color 0.2s ease !important;
  }
  #sozo-react-app .tc-page:hover:not(:disabled):not([aria-current="page"]) {
    background-color: var(--color-brand-almond) !important;
    color: var(--color-brand-primary) !important;
  }
  #sozo-react-app .tc-page[aria-current="page"] {
    background-color: var(--color-brand-primary) !important;
    color: var(--color-white) !important;
    cursor: default !important;
  }
  #sozo-react-app .tc-page:focus-visible {
    outline: var(--focus-ring) !important;
    outline-offset: 2px !important;
  }
  #sozo-react-app .tc-page--arrow {
    color: var(--color-brand-primary) !important;
    font-size: 20px !important;
  }
  #sozo-react-app .tc-page--arrow:disabled {
    color: var(--color-border) !important;
    cursor: default !important;
  }
  #sozo-react-app .tc-page--dots {
    min-width: 24px !important;
    padding: 0 !important;
    color: var(--color-text-muted) !important;
    cursor: default !important;
  }

  /* ============ CTA KONSULTASI ============ */
  #sozo-react-app .tc-cta-section { padding-bottom: var(--section-y) !important; }
  #sozo-react-app .tc-cta {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 24px !important;
    padding: 32px !important;
    border-radius: var(--radius-lg) !important;
    background-color: var(--color-brand-almond) !important;
  }
  #sozo-react-app .tc-cta__title {
    margin-bottom: 6px !important;
    font-size: 24px !important;
    font-weight: 700 !important;
    line-height: 1.3 !important;
    color: var(--color-brand-primary) !important;
  }
  /* Teks di atas Almond pakai text-secondary (text-muted gagal AA di Almond) */
  #sozo-react-app .tc-cta__desc {
    font-size: 16px !important;
    line-height: 1.6 !important;
    color: var(--color-text-secondary) !important;
  }
  #sozo-react-app .tc-cta .btn { flex: none !important; }

  /* ============ RESPONSIVE (1024 / 768) ============ */
  @media (max-width: 1024px) {
    #sozo-react-app .tc-grid { grid-template-columns: repeat(3, minmax(0, 1fr)) !important; }
  }

  @media (max-width: 768px) {
    #sozo-react-app .tc-hero { padding: 14px 0 24px !important; }
    #sozo-react-app .breadcrumb { margin-bottom: 12px !important; }
    #sozo-react-app .tc-hero__text { margin-bottom: 16px !important; }
    #sozo-react-app .tc-hero__title { margin-bottom: 4px !important; font-size: 22px !important; }
    #sozo-react-app .tc-hero__subtitle { font-size: 14px !important; line-height: 1.5 !important; }
    #sozo-react-app .tc-hero__banner {
      flex-direction: column !important;
      align-items: stretch !important;
      gap: 10px !important;
      padding: 12px !important;
    }
    #sozo-react-app .tc-hero__banner-text { gap: 10px !important; font-size: 14px !important; }
    #sozo-react-app .tc-hero__banner-text svg { width: 20px !important; height: 20px !important; }
    #sozo-react-app .tc-hero__banner .btn { width: 100% !important; min-height: 44px !important; }
    #sozo-react-app .tc-hero__cats {
      flex-wrap: nowrap !important;
      justify-content: flex-start !important;
      overflow-x: auto !important;
      gap: 12px !important;
      padding: 14px 12px !important;
      scrollbar-width: none !important;
    }
    #sozo-react-app .tc-hero__cats::-webkit-scrollbar { display: none !important; }
    #sozo-react-app .tc-hero__cat { flex: 0 0 64px !important; gap: 6px !important; }
    #sozo-react-app .tc-hero__cat-img { width: 52px !important; height: 52px !important; }
    #sozo-react-app .tc-hero__cat-title { font-size: 11px !important; }

    #sozo-react-app .tc-section-title { font-size: 22px !important; }
    #sozo-react-app .tc-section-desc { font-size: 14px !important; }

    /* Chip dan tab: satu baris, geser sampai tepi layar */
    #sozo-react-app .tc-concerns,
    #sozo-react-app .tc-tabs {
      flex-wrap: nowrap !important;
      overflow-x: auto !important;
      margin-inline: calc(var(--container-pad) * -1) !important;
      padding-inline: var(--container-pad) !important;
      scrollbar-width: none !important;
    }
    #sozo-react-app .tc-concerns::-webkit-scrollbar { display: none !important; }
    #sozo-react-app .tc-concerns__label { flex: none !important; font-size: 13px !important; }
    #sozo-react-app .tc-chip { min-height: 36px !important; padding: 0 14px !important; font-size: 13px !important; }
    #sozo-react-app .tc-tab { padding: 0 12px !important; font-size: 13px !important; }

    #sozo-react-app .tc-grid { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
    #sozo-react-app .tc-card__body { padding: 10px !important; }
    #sozo-react-app .tc-catalog__head { margin-bottom: 12px !important; }
    #sozo-react-app .tc-search { margin-bottom: 10px !important; }
    #sozo-react-app .tc-search__input { min-height: 44px !important; padding: 0 16px 0 44px !important; }
    #sozo-react-app .tc-search__icon { left: 16px !important; width: 18px !important; height: 18px !important; }
    #sozo-react-app .tc-concerns { margin-bottom: 8px !important; }
    #sozo-react-app .tc-tabs { margin-bottom: 10px !important; }
    #sozo-react-app .tc-tab { min-height: 40px !important; }
    #sozo-react-app .tc-count { margin-bottom: 10px !important; font-size: 13px !important; }
    #sozo-react-app .tc-card__badge { min-height: 24px !important; padding: 0 8px !important; font-size: 11px !important; }
    #sozo-react-app .tc-card__category { font-size: 11px !important; letter-spacing: 0.08em !important; }
    #sozo-react-app .tc-card__title { margin-bottom: 2px !important; font-size: 14px !important; line-height: 1.3 !important; }
    #sozo-react-app .tc-card__meta { font-size: 12px !important; }
    #sozo-react-app .tc-card__footer { padding-top: 8px !important; }
    #sozo-react-app .tc-card__price { margin-bottom: 8px !important; padding-top: 8px !important; font-size: 15px !important; }
    #sozo-react-app .tc-card .btn--sm { padding: 0 8px !important; font-size: 12px !important; }
    #sozo-react-app a.tc-card__action.btn--primary {
      min-height: 36px !important;
      padding: 0 8px !important;
      gap: 6px !important;
      font-size: 11.5px !important;
      font-weight: 700 !important;
    }
    #sozo-react-app a.tc-card__action svg.btn__icon { width: 13px !important; height: 13px !important; }

    #sozo-react-app .tc-pagination { gap: 4px !important; margin-top: 20px !important; }
    #sozo-react-app .tc-page { min-width: 36px !important; height: 36px !important; padding: 0 6px !important; }
    #sozo-react-app .tc-page--dots { min-width: 20px !important; padding: 0 !important; }
    #sozo-react-app .tc-cta {
      flex-direction: column !important;
      align-items: stretch !important;
      padding: 24px 20px !important;
    }
    #sozo-react-app .tc-cta__title { font-size: 20px !important; }
    #sozo-react-app .tc-cta__desc { font-size: 14px !important; }
  }

  @media (prefers-reduced-motion: reduce) {
    #sozo-react-app .btn:hover,
    #sozo-react-app .tc-card--link:hover,
    #sozo-react-app .tc-card--link:hover .tc-card__media img,
    #sozo-react-app .tc-hero__cat:hover .tc-hero__cat-img { transform: none !important; }
  }
</style>

<div id="sozo-react-app">

  <!-- ====== HERO ====== -->
  <section class="tc-hero">
    <div class="tc-container">
      <div class="tc-hero__inner">

        <nav class="breadcrumb" aria-label="Breadcrumb">
          <ol class="breadcrumb__list">
            <li><a href="https://sozoskinclinic.com/">Beranda</a></li>
            <li><span class="breadcrumb__current" aria-current="page">Treatment</span></li>
          </ol>
        </nav>

        <div class="tc-hero__text">
          <h1 class="tc-hero__title">Treatment Kecantikan di SOZO Skin Clinic</h1>
          <p class="tc-hero__subtitle">Klinik estetika terpercaya untuk perawatan kulit &amp; rambut Anda</p>
        </div>

        <div class="tc-hero__card">
          <div class="tc-hero__banner">
            <p class="tc-hero__banner-text">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
              <span>Konsul keluhan sesuai kebutuhan, GRATIS!</span>
            </p>
            <a href="<?php echo esc_url(sozo_tc_wa_url()); ?>" target="_blank" rel="noopener noreferrer" class="btn btn--primary" data-wa-src="organic"><svg class="btn__icon" aria-hidden="true" focusable="false"><use href="#tc-i-wa"></use></svg><span>Mulai Konsul GRATIS</span></a>
          </div>

          <div class="tc-hero__cats">
            <a href="https://sozoskinclinic.com/skin-treatment/" class="tc-hero__cat">
              <span class="tc-hero__cat-img"><img src="https://asset.sozoskinclinic.com/wp-content/uploads/2026/01/Skin-Bright-Facial-Medical-1.webp" alt="" width="76" height="76" loading="lazy"></span>
              <span class="tc-hero__cat-title">Skin<br>Treatment</span>
            </a>
            <a href="https://sozoskinclinic.com/injectable-treatment/" class="tc-hero__cat">
              <span class="tc-hero__cat-img"><img src="https://asset.sozoskinclinic.com/wp-content/uploads/2025/10/Acne-Injection.webp" alt="" width="76" height="76" loading="lazy"></span>
              <span class="tc-hero__cat-title">Injectable<br>Treatment</span>
            </a>
            <a href="https://sozoskinclinic.com/meso-treatment/" class="tc-hero__cat">
              <span class="tc-hero__cat-img"><img src="https://asset.sozoskinclinic.com/wp-content/uploads/2025/07/slimming-home.webp" alt="" width="76" height="76" loading="lazy"></span>
              <span class="tc-hero__cat-title">Slimming<br>Treatment</span>
            </a>
            <a href="https://sozoskinclinic.com/hair-treatment/" class="tc-hero__cat">
              <span class="tc-hero__cat-img"><img src="https://asset.sozoskinclinic.com/wp-content/uploads/2026/03/Gemini_Generated_Image_qncw0qncw0qncw0q.webp" alt="" width="76" height="76" loading="lazy"></span>
              <span class="tc-hero__cat-title">Hair<br>Treatment</span>
            </a>
            <a href="https://sozoskinclinic.com/hair-removal-treatment/" class="tc-hero__cat">
              <span class="tc-hero__cat-img"><img src="https://asset.sozoskinclinic.com/wp-content/uploads/2025/11/Hair-Removal-Underarm-Glow.webp" alt="" width="76" height="76" loading="lazy"></span>
              <span class="tc-hero__cat-title">Hair<br>Removal</span>
            </a>
            <a href="https://sozoskinclinic.com/skin-treatment/acne-treatment/" class="tc-hero__cat">
              <span class="tc-hero__cat-img"><img src="https://asset.sozoskinclinic.com/wp-content/uploads/2026/01/Landing-Page-Acne-Single-Treatment-Acne-laser-facial.webp" alt="" width="76" height="76" loading="lazy"></span>
              <span class="tc-hero__cat-title">Acne<br>Treatment</span>
            </a>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- ====== KATALOG ====== -->
  <section id="semua-treatment" class="tc-catalog" aria-labelledby="tc-catalog-title">
    <div class="tc-container">
      <div class="tc-catalog__head">
        <h2 class="tc-section-title" id="tc-catalog-title">Pilih Treatment Sesuai Kebutuhan Anda</h2>
        <p class="tc-section-desc">Cari dari nama treatment, keluhan kulit atau rambut, atau kategori perawatan.</p>
      </div>

      <div class="tc-search">
        <svg class="tc-search__icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        <input type="search" id="tc-search-input" class="tc-search__input" placeholder="Cari treatment atau keluhan, misalnya jerawat" aria-label="Cari treatment atau keluhan" autocomplete="off">
      </div>

      <div class="tc-concerns" role="group" aria-label="Cari berdasarkan keluhan">
        <span class="tc-concerns__label" aria-hidden="true">Keluhan:</span>
<?php foreach (sozo_tc_concerns() as $chip) : ?>
        <button type="button" class="tc-chip" data-concern="<?php echo esc_attr($chip['id']); ?>" aria-pressed="false"><?php echo esc_html($chip['label']); ?></button>
<?php endforeach; ?>
      </div>

      <div class="tc-tabs" role="group" aria-label="Kategori treatment">
<?php foreach (sozo_tc_tabs() as $tab) : ?>
<?php if ($tab['cat'] === 'all') : ?>
        <button type="button" class="tc-tab" data-cat="all" aria-pressed="true"><?php echo esc_html($tab['label']); ?></button>
<?php else : ?>
        <button type="button" class="tc-tab" data-cat="<?php echo esc_attr($tab['cat']); ?>" data-title="<?php echo esc_attr($tab['title']); ?>" data-link="<?php echo esc_url($tab['link']); ?>" data-desc="<?php echo esc_attr($tab['desc']); ?>" aria-pressed="false"><?php echo esc_html($tab['label']); ?></button>
<?php endif; ?>
<?php endforeach; ?>
      </div>

      <p class="tc-filter-desc" id="tc-filter-desc" hidden></p>
      <p class="tc-count" id="tc-count" aria-live="polite">
        Menampilkan <?php echo (int) ($start + 1); ?>–<?php echo (int) $end; ?> dari <?php echo (int) $total; ?> treatment
      </p>

      <!-- Ikon WhatsApp dipakai ulang oleh tombol "Buat Jadwal" di kartu (sama dengan halaman lokasi) -->
      <svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
        <symbol id="tc-i-wa" viewBox="0 0 448 512"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157.1zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></symbol>
      </svg>
      <div class="tc-grid" id="tc-grid">
<?php
        foreach ($cards as $i => $c) {
            sozo_tc_render_card($c, $i < $start || $i >= $end);
        }
        ?>
      </div>

      <div class="tc-empty" id="tc-empty" hidden>
        <div class="tc-empty__icon" aria-hidden="true">
          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        </div>
        <h3 class="tc-empty__title">Treatment tidak ditemukan</h3>
        <p class="tc-empty__desc" id="tc-empty-desc">Coba kata kunci lain atau pilih kategori yang berbeda.</p>
        <button type="button" class="btn btn--primary" id="tc-reset">Lihat Semua Treatment</button>
      </div>

      <nav class="tc-pagination" id="tc-pagination" aria-label="Halaman daftar treatment" hidden></nav>
    </div>
  </section>

  <!-- ====== CTA KONSULTASI ====== -->
  <section class="tc-cta-section">
    <div class="tc-container">
      <div class="tc-cta">
        <div>
          <h2 class="tc-cta__title">Bingung pilih treatment?</h2>
          <p class="tc-cta__desc">Ceritakan keluhan Anda, tim SOZO bantu pilihkan treatment yang sesuai. Konsultasi gratis.</p>
        </div>
        <a href="<?php echo esc_url(sozo_tc_wa_url()); ?>" target="_blank" rel="noopener noreferrer" class="btn btn--primary" data-wa-src="organic"><svg class="btn__icon" aria-hidden="true" focusable="false"><use href="#tc-i-wa"></use></svg><span>Konsultasi via WhatsApp</span></a>
      </div>
    </div>
  </section>

</div>

<script data-no-optimize="1" data-no-defer="1">
/* ============================================================
   KATALOG TREATMENT - perilaku di browser
   Kartu sudah dicetak server (PHP di atas), script ini hanya
   menyaring kartu: cari, chip keluhan, tab kategori, dan pagination.
   Pagination memakai URL ?halaman=n; server juga membaca ?halaman
   untuk kondisi awal, jadi keduanya harus memakai nama yang sama.
   ?halaman hanya ditulis saat daftar tidak difilter.
   ============================================================ */
(function () {
  'use strict';

  // Jumlah kartu per halaman: desktop/tablet 12, mobile 6 (grid 2 kolom = 3 baris, kira-kira satu layar).
  // Breakpoint harus sama dengan @media (max-width: 768px) yang membuat grid 2 kolom di CSS.
  var PAGE_SIZE_DESKTOP = <?php echo (int) sozo_tc_page_size(); ?>; // dari sozo_tc_page_size()
  var PAGE_SIZE_MOBILE = <?php echo (int) sozo_tc_page_size_mobile(); ?>; // dari sozo_tc_page_size_mobile()
  var PAGE_PARAM = 'halaman';
  var mobileQuery = window.matchMedia ? window.matchMedia('(max-width: 768px)') : null;

  function pageSize() {
    return mobileQuery && mobileQuery.matches ? PAGE_SIZE_MOBILE : PAGE_SIZE_DESKTOP;
  }

  function init() {
    var app = document.getElementById('sozo-react-app');
    var grid = document.getElementById('tc-grid');
    if (!app || !grid || grid.classList.contains('is-ready')) return Boolean(grid);

    var cards = Array.prototype.slice.call(grid.querySelectorAll('.tc-card'));
    var input = document.getElementById('tc-search-input');
    var chips = Array.prototype.slice.call(app.querySelectorAll('.tc-chip'));
    var tabs = Array.prototype.slice.call(app.querySelectorAll('.tc-tab'));
    var count = document.getElementById('tc-count');
    var filterDesc = document.getElementById('tc-filter-desc');
    var empty = document.getElementById('tc-empty');
    var emptyDesc = document.getElementById('tc-empty-desc');
    var pager = document.getElementById('tc-pagination');
    var reset = document.getElementById('tc-reset');
    var catalog = document.getElementById('semua-treatment') || grid;

    var state = { q: '', concern: '', cat: 'all', page: 1 };
    var totalPages = 1;

    function matches(card) {
      if (state.cat !== 'all' && card.getAttribute('data-cat') !== state.cat) return false;
      if (state.concern && (' ' + card.getAttribute('data-concerns') + ' ').indexOf(' ' + state.concern + ' ') === -1) return false;
      if (state.q && card.getAttribute('data-search').indexOf(state.q) === -1) return false;
      return true;
    }

    function isUnfiltered() {
      return !state.q && !state.concern && state.cat === 'all';
    }

    // ---------- URL ?halaman=n ----------
    function readPageFromUrl() {
      var n = parseInt(new URLSearchParams(window.location.search).get(PAGE_PARAM), 10);
      return isFinite(n) && n > 1 ? n : 1;
    }

    function syncUrl(push) {
      try {
        var url = new URL(window.location.href);
        if (state.page > 1 && isUnfiltered()) url.searchParams.set(PAGE_PARAM, String(state.page));
        else url.searchParams.delete(PAGE_PARAM);
        if (url.href === window.location.href) return;
        window.history[push ? 'pushState' : 'replaceState']({ tcPage: state.page }, '', url.href);
      } catch (e) {
        // History API tidak tersedia: pagination tetap jalan tanpa ubah URL
      }
    }

    // ---------- Render ----------
    function render() {
      var matched = cards.filter(matches);
      var total = matched.length;
      var size = pageSize();
      totalPages = Math.max(1, Math.ceil(total / size));
      if (state.page > totalPages) state.page = totalPages;

      var start = (state.page - 1) * size;
      var end = start + size;
      cards.forEach(function (card) { card.hidden = true; });
      matched.slice(start, end).forEach(function (card) { card.hidden = false; });

      count.textContent = total
        ? 'Menampilkan ' + (start + 1) + '–' + Math.min(end, total) + ' dari ' + total + ' treatment'
        : '';
      empty.hidden = total !== 0;
      if (!total) {
        emptyDesc.textContent = state.q
          ? 'Tidak ada treatment untuk "' + input.value.trim() + '". Coba kata kunci lain atau pilih kategori yang berbeda.'
          : 'Coba kata kunci lain atau pilih kategori yang berbeda.';
      }
      renderPager();
    }

    function pageRange(current, total) {
      // 1 … (current-1) current (current+1) … total
      var out = [];
      var last = 0;
      for (var i = 1; i <= total; i++) {
        if (i === 1 || i === total || (i >= current - 1 && i <= current + 1)) {
          if (last && i - last === 2) out.push(last + 1);
          else if (last && i - last > 2) out.push('…');
          out.push(i);
          last = i;
        }
      }
      return out;
    }

    function pageButton(label, page, opts) {
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'tc-page' + (opts.arrow ? ' tc-page--arrow' : '');
      b.textContent = label;
      b.setAttribute('aria-label', opts.aria);
      if (opts.current) b.setAttribute('aria-current', 'page');
      if (opts.disabled) b.disabled = true;
      b.addEventListener('click', function (e) {
        // e.detail === 0: diklik lewat keyboard (Enter/Spasi), bukan mouse/tap
        if (!opts.disabled && !opts.current) goToPage(page, e.detail === 0);
      });
      return b;
    }

    function renderPager() {
      pager.textContent = '';
      if (totalPages <= 1) {
        pager.hidden = true;
        return;
      }
      pager.hidden = false;
      pager.appendChild(pageButton('‹', state.page - 1, { arrow: true, aria: 'Halaman sebelumnya', disabled: state.page === 1 }));
      pageRange(state.page, totalPages).forEach(function (p) {
        if (p === '…') {
          var dots = document.createElement('span');
          dots.className = 'tc-page tc-page--dots';
          dots.setAttribute('aria-hidden', 'true');
          dots.textContent = '…';
          pager.appendChild(dots);
        } else {
          pager.appendChild(pageButton(String(p), p, { aria: 'Halaman ' + p, current: p === state.page }));
        }
      });
      pager.appendChild(pageButton('›', state.page + 1, { arrow: true, aria: 'Halaman berikutnya', disabled: state.page === totalPages }));
    }

    function scrollToCatalog() {
      var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      catalog.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
    }

    function goToPage(page, fromKeyboard) {
      state.page = Math.min(Math.max(1, page), totalPages);
      render();
      syncUrl(true);
      scrollToCatalog();
      // Pengguna keyboard: fokus pindah ke kartu pertama halaman baru, karena tombol
      // yang tadi diklik sudah dibuat ulang. Untuk mouse/tap tidak, supaya kartu
      // tidak tampil ber-outline (:focus-within).
      if (fromKeyboard) {
        var first = cards.filter(function (c) { return !c.hidden; })[0];
        var target = first && (first.querySelector('.tc-card__title a') || first.querySelector('a'));
        if (target) target.focus({ preventScroll: true });
      }
    }

    // Filter apa pun selalu kembali ke halaman 1 dan menghapus ?halaman
    function applyFilter() {
      state.page = 1;
      render();
      syncUrl(false);
    }

    // ---------- Filter UI ----------
    function renderFilterDesc(tab) {
      var desc = tab.getAttribute('data-desc');
      var link = tab.getAttribute('data-link');
      var title = tab.getAttribute('data-title');
      filterDesc.textContent = '';
      if (tab.getAttribute('data-cat') === 'all' || (!desc && !link)) {
        filterDesc.hidden = true;
        return;
      }
      if (desc) filterDesc.appendChild(document.createTextNode(desc + ' '));
      if (link) {
        var a = document.createElement('a');
        a.href = link;
        a.textContent = 'Lihat halaman ' + title + ' →';
        filterDesc.appendChild(a);
      }
      filterDesc.hidden = false;
    }

    function setPressed(list, active) {
      list.forEach(function (el) { el.setAttribute('aria-pressed', String(el === active)); });
    }

    var timer = null;
    input.addEventListener('input', function () {
      clearTimeout(timer);
      timer = setTimeout(function () {
        state.q = input.value.trim().toLowerCase();
        applyFilter();
      }, 120);
    });

    chips.forEach(function (chip) {
      chip.addEventListener('click', function () {
        var id = chip.getAttribute('data-concern');
        state.concern = state.concern === id ? '' : id;
        setPressed(chips, state.concern ? chip : null);
        applyFilter();
      });
    });

    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        state.cat = tab.getAttribute('data-cat');
        setPressed(tabs, tab);
        renderFilterDesc(tab);
        applyFilter();
      });
    });

    reset.addEventListener('click', function () {
      input.value = '';
      state = { q: '', concern: '', cat: 'all', page: 1 };
      setPressed(chips, null);
      setPressed(tabs, tabs[0]);
      renderFilterDesc(tabs[0]);
      applyFilter();
      input.focus();
    });

    // Tombol back/forward browser
    window.addEventListener('popstate', function () {
      state.page = readPageFromUrl();
      render();
      scrollToCatalog();
    });

    // Layar diputar / di-resize melewati 768px: jumlah per halaman berubah.
    // Pertahankan kartu pertama yang sedang terlihat, lalu hitung ulang nomor halamannya.
    var lastSize = pageSize();
    function onSizeChange() {
      var size = pageSize();
      if (size === lastSize) return;
      var firstIndex = (state.page - 1) * lastSize;
      lastSize = size;
      state.page = Math.floor(firstIndex / size) + 1;
      render();
      syncUrl(false);
    }
    if (mobileQuery) {
      if (mobileQuery.addEventListener) mobileQuery.addEventListener('change', onSizeChange);
      else if (mobileQuery.addListener) mobileQuery.addListener(onSizeChange);
    }

    grid.classList.add('is-ready');
    // Buka langsung di halaman dari URL; nilai di luar rentang dinormalkan
    state.page = readPageFromUrl();
    render();
    syncUrl(false);
    return true;
  }

  var attempts = 0;
  function run() {
    if (init()) return;
    if (attempts++ < 30) setTimeout(run, 200);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', run);
  else run();

  window.addEventListener('elementor/frontend/init', function () {
    if (window.elementorFrontend && window.elementorFrontend.hooks) {
      window.elementorFrontend.hooks.addAction('frontend/element_ready/html.default', run);
    }
  });
})();
</script>
        <?php
        return ob_get_clean();
    }
}
