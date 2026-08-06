# WhatsApp Button Component

- **Status:** Spec aktif
- **Versi:** 1.0 (13 Juli 2026)
- **Tipe:** Komponen spesifik klinik (semua halaman pakai)
- **File terkait:** Navbar custom HTML, floating button (umum di semua LP)

## Konfigurasi Global

| Parameter | Nilai | Catatan |
| :-- | :-- | :-- |
| Nomor WhatsApp | `6285175225664` | Format internasional, tanpa `+` |
| Default message | `Halo SOZO, saya mau booking promo skin treatment [sumber: ORG-general]` | `sumber` di-template per LP |
| Brand | Sozo Skin Clinic | |
| Icon | WhatsApp logo SVG (lihat markup di bawah) | |

Konfigurasi message per LP (substitusi `[sumber: ...]`):

| LP / Konteks | `[sumber: ...]` value |
| :-- | :-- |
| Organic / homepage | `[sumber: ORG-general]` |
| Treatment page | `[sumber: ORG-{treatment-slug}]` (mis. `ORG-korean-filler`) |
| Lokasi page | `[sumber: ORG-{lokasi-slug}]` (mis. `ORG-jakarta-selatan`) |
| Product page | `[sumber: PROD-{product-slug}]` (mis. `PROD-sunscreen`) |
| Paid ads | `[sumber: ADS-{campaign}]` (mis. `ADS-instagram-jan2026`) |

## Varian

### Nav (Header)

Tombol WA di navbar header. Pill shape, prominent.

- Shape: pill (`border-radius: 50px`)
- Background: `#1A237E` (atau `var(--sozo-blue)` jika sudah di-unify — see Notes)
- Text: `#ffffff`
- Border: 2px solid `#1A237E` (siap untuk hover invert)
- Padding: 11px 22px (desktop), 10px 18px (≤1280px), 14px 18px (mobile full-width)
- Font: Inter, 15px (desktop), 14px (≤1280px), weight 600
- Line-height: 1
- Text label default: "Reservasi Sekarang"
- Icon: WA SVG 18×18px
- **Hover**: background `#ffffff`, text `#1A237E`, border tetap, transform `translateY(-1px)`, shadow `0 4px 14px rgba(26, 35, 126, 0.3)`
- **Active**: transform `translateY(0)` (reset hover lift)
- **Mobile (≤767px)**: jadi icon-only circle (lihat Responsive)

**Use case:** 1 instance per halaman, di navbar header. Click → buka WhatsApp dengan template message.

### Floating (Fixed Bottom)

Tombol WA floating, fixed di pojok bawah.

- Position: `fixed bottom-right` (atau `bottom-left`)
- Shape: circle, 56px diameter
- Background: WhatsApp green `#25D366` (atau `var(--sozo-blue)` jika brand-aligned)
- Icon: WA SVG 24×24px, color `#ffffff`
- Box-shadow: `0 4px 12px rgba(0, 0, 0, 0.15)`
- Z-index: tinggi (di atas content lain, tapi di bawah modal)
- Hover: scale 1.05, shadow lebih dalam `0 6px 20px rgba(0, 0, 0, 0.2)`
- Active: scale 1
- `aria-label`: "Chat WhatsApp untuk Reservasi"

**Use case:** 1 instance per halaman, muncul di semua LP. **Jangan duplikat** dengan Nav variant — pilih salah satu per halaman, atau sembunyikan Nav variant saat Floating muncul (tergantung design preference).

## Responsive Behavior (Nav Variant)

| Breakpoint | Behavior |
| :-- | :-- |
| > 1280px | Default: pill, text "Reservasi Sekarang" + icon |
| 991–1280px | Pill lebih kecil, font 14px, padding 10px 18px |
| 768–991px | Hidden (di-ganti mobile menu trigger) |
| ≤ 767px | Muncul di dalam mobile menu overlay, full-width, text "Reservasi Sekarang" + icon |
| ≤ 767px (di nav utama) | Icon-only circle, 40px diameter, no text |

## HTML Markup

### Nav Variant (Custom HTML, dari navbar production code)

```html
<a href="https://api.whatsapp.com/send?phone=6285175225664&text=Halo%20SOZO,%20saya%20mau%20booking%20promo%20skin%20treatment%20%5Bsumber:%20ORG-general%5D" 
   class="sozo-wa-btn-nav" 
   target="_blank" 
   rel="noopener">
  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
  </svg>
  <span>Reservasi Sekarang</span>
</a>
```

### Floating Variant

```html
<a href="https://api.whatsapp.com/send?phone=6285175225664&text=Halo%20SOZO,%20saya%20mau%20booking%20promo%20skin%20treatment%20%5Bsumber:%20ORG-general%5D" 
   class="sozo-wa-btn-floating" 
   target="_blank" 
   rel="noopener"
   aria-label="Chat WhatsApp untuk Reservasi">
  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
  </svg>
</a>
```

## CSS Pattern (dari production navbar)

```css
.sozo-wa-btn-nav {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  background: #1A237E;
  color: #ffffff !important;
  font-family: 'Inter', 'Poppins', sans-serif;
  font-size: 15px;
  font-weight: 600;
  line-height: 1;
  padding: 11px 22px;
  border-radius: 50px;
  text-decoration: none !important;
  transition: all 0.25s ease;
  border: 2px solid #1A237E;
  white-space: nowrap;
}

.sozo-wa-btn-nav:hover {
  background: #ffffff;
  color: #1A237E !important;
  transform: translateY(-1px);
  box-shadow: 0 4px 14px rgba(26, 35, 126, 0.3);
}

.sozo-wa-btn-nav:active {
  transform: translateY(0);
}

.sozo-wa-btn-nav svg {
  flex-shrink: 0;
}

@media (max-width: 1280px) {
  .sozo-wa-btn-nav {
    font-size: 14px;
    padding: 10px 18px;
  }
  .sozo-wa-btn-nav svg {
    width: 16px;
    height: 16px;
  }
}

@media (max-width: 767px) {
  .sozo-wa-btn-nav {
    padding: 10px;
    border-radius: 50%;
  }
  .sozo-wa-btn-nav span {
    display: none;
  }
  .sozo-wa-btn-nav svg {
    width: 18px;
    height: 18px;
  }
}
```

## Aksesibilitas

- Wajib `aria-label` yang mendeskripsikan action: "Chat WhatsApp untuk Reservasi" (atau "Chat WhatsApp [nama LP]" untuk konteks).
- Focus state visible: outline 2px `var(--sozo-blue)`, offset 2px.
- Hover state tidak boleh satu-satunya penanda clickability (ada `text` atau `aria-label`).
- `target="_blank"` wajib `rel="noopener"` (security).
- Icon-only mode (mobile nav) wajib punya `aria-label` di `<a>`.

## Anti-pattern

- ❌ Multiple WA button di satu halaman (Nav + Floating + footer + section CTA — pilih 1-2 lokasi).
- ❌ Nomor WA hardcoded di multiple tempat (pakai constant/template).
- ❌ Link tanpa `rel="noopener"` saat `target="_blank"`.
- ❌ Floating button dengan z-index terlalu tinggi (nutupin modal atau notification penting).
- ❌ Floating button nutupin konten penting (test bottom-right safe area).

## Notes

- **Inconsistency untuk di-resolve:** Production navbar pakai `#1A237E` inline, sedangkan brand token `var(--sozo-blue)` = `#1A2080`. Selisihnya subtle tapi inkonsisten. Rekomendasi: refactor ke `var(--sozo-blue)` untuk konsistensi dengan design system lain. Track di `docs/adr/0008-wa-button-color-unify.md` (akan dibuat).
- **Floating variant belum ada di production.** Nav variant ada di navbar. Floating variant adalah rekomendasi untuk konsistensi CTA di semua LP. Roll-out: tambah snippet global di WPCode atau Elementor template.
- **Substitusi `[sumber: ...]`:** bisa di-hardcode per LP, atau lebih scalable pakai variable JavaScript. Untuk sekarang, hardcode per LP cukup — refactor ke dynamic kalau sudah banyak LP.
- **Phone link format:** `https://api.whatsapp.com/send?phone=XXX&text=YYY` (alternatif: `https://wa.me/XXX?text=YYY`). Keduanya valid. Production pakai `api.whatsapp.com` — pertahankan untuk konsistensi.
