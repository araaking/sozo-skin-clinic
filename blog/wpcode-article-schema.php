<?php
/**
 * ==========================================================
 * DEPRECATED — JANGAN DIAKTIFKAN
 *
 * Snippet ini dulu meng-output Article + BreadcrumbList ke wp_head untuk
 * semua single post. Sejak blog/singlepost.php (shortcode [sozoskin_single])
 * menangani schema artikel — MedicalWebPage + BlogPosting + Person +
 * BreadcrumbList dalam satu @graph — snippet ini jadi sumber duplikat:
 * dua node "#article" dengan tipe dan breadcrumb berbeda di halaman yang sama.
 *
 * Aksi: kalau snippet ini masih aktif di WPCode, nonaktifkan atau hapus.
 * Sumber schema artikel yang benar: blog/singlepost.php.
 * ==========================================================
 */
