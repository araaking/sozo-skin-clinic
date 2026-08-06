# Accordion Component

- **Status:** Spec aktif
- **Versi:** 1.0 (13 Juli 2026)
- **Tipe:** Molekul (trigger + collapsible content)
- **File terkait:** `index.html` (custom HTML) atau Elementor Accordion widget
- **Referensi:** SKU page (akan ditambahkan setelah spec SKU tersedia)

## Anatomi

| Properti | Nilai | Catatan |
| :-- | :-- | :-- |
| Container border-bottom | 1px solid `var(--sozo-border)` | Pemisah antar item |
| Trigger padding | 18px 0 | Vertikal lega untuk touch target |
| Trigger font family | Poppins (`var(--sozo-font-heading)`) | Pertanyaan = heading |
| Trigger font weight | 600 | Semi-bold untuk pertanyaan |
| Trigger font size | 16px (md), 14px (sm) | |
| Trigger text color | `var(--sozo-text)` | |
| Content padding | 0 0 18px 0 | Align dengan trigger text |
| Content font family | Inter (`var(--sozo-font-body)`) | |
| Content font weight | 400 | |
| Content font size | 14–15px | |
| Content text color | `var(--sozo-text)` 85% opacity | Subtle hierarchy |
| Icon (chevron) | 20×20px, stroke 2px, color `var(--sozo-blue)` | |
| Icon transition | transform 0.25s ease | Rotate 180° saat open |
| Font family | Inter, system-ui, sans-serif | Body/UI; lihat `docs/design-system/colors-typography.md` |

## Varian

### Single-open (default untuk FAQ)

Hanya 1 item yang boleh terbuka pada satu waktu. Buka item lain → item sebelumnya auto-close.

- Cocok untuk FAQ pattern tradisional.
- Click trigger kedua otomatis close trigger pertama.
- Initial state: semua tertutup (atau item pertama terbuka — lihat konvensi di bawah).

**Use case:** Halaman FAQ treatment, FAQ umum klinik.

### Multi-open

Semua item boleh terbuka bersamaan. Click trigger toggle tanpa affect yang lain.

- Cocok untuk FAQ panjang atau information architecture yang non-linear.
- Click trigger toggle individual open/close.

**Use case:** FAQ panjang (10+ item), knowledge base.

### Default Behavior

- **Initial state: semua tertutup.** User ekspansif sesuai kebutuhan.
- **Kecuali:** SKU-style accordion (referensi pending) — bisa jadi default buka item pertama untuk orientasi user.

## Size

| Size | Trigger padding | Trigger font | Use case |
| :-- | :-- | :-- | :-- |
| `sm` | 14px 0 | 14px | Sidebar FAQ, footer FAQ |
| `md` (default) | 18px 0 | 16px | Most FAQ sections |
| `lg` | 24px 0 | 18px | Featured FAQ, hero FAQ |

## State

| State | Trigger bg | Icon rotation | Content display | Catatan |
| :-- | :-- | :-- | :-- | :-- |
| Default (closed) | transparent | 0° | hidden (max-height 0) | |
| Hover | `var(--sozo-light-bg)` | 0° | hidden | Subtle highlight |
| Open | `var(--sozo-light-bg)` (optional) | 180° | visible (max-height 500–1000px) | Transition 0.25–0.35s |
| Focus | outline 2px `var(--sozo-blue)`, offset 2px | — | — | A11y keyboard navigation |
| Disabled | 50% opacity | 0° | hidden | Item FAQ tidak applicable |

## HTML Markup (Custom HTML)

```html
<!-- Single-open accordion (FAQ pattern) -->
<div class="accordion accordion-single" data-accordion="single">
  <div class="accordion-item">
    <h3 class="accordion-header">
      <button class="accordion-trigger" 
              aria-expanded="false" 
              aria-controls="faq-1-content"
              id="faq-1-trigger">
        <span class="accordion-title">Apa itu Korean Filler?</span>
        <svg class="accordion-icon" aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="6 9 12 15 18 9"></polyline>
        </svg>
      </button>
    </h3>
    <div class="accordion-content" 
         id="faq-1-content" 
         role="region" 
         aria-labelledby="faq-1-trigger"
         hidden>
      <p>Korean Filler adalah filler premium berbasis hyaluronic acid dengan teknologi Korea...</p>
    </div>
  </div>

  <div class="accordion-item">
    <h3 class="accordion-header">
      <button class="accordion-trigger" 
              aria-expanded="false" 
              aria-controls="faq-2-content"
              id="faq-2-trigger">
        <span class="accordion-title">Berapa lama hasilnya bertahan?</span>
        <svg class="accordion-icon" aria-hidden="true">...</svg>
      </button>
    </h3>
    <div class="accordion-content" 
         id="faq-2-content" 
         role="region" 
         aria-labelledby="faq-2-trigger"
         hidden>
      <p>Hasil Korean Filler biasanya bertahan 9–12 bulan tergantung metabolisme...</p>
    </div>
  </div>
</div>
```

## Elementor Accordion Widget (Recommended)

| Setting | Value |
| :-- | :-- |
| Type | Accordion |
| Default state | All closed (atau first item open — lihat konvensi) |
| Toggle | Yes (untuk multi-open) / No (untuk single-open) |
| Title HTML tag | H3 (atau H4 jika di dalam section yang sudah ada H3) |
| Border type | Solid |
| Border width | 0 0 1 0 (border-bottom only) |
| Border color | `var(--sozo-border)` |
| Title typography | Inter, 16px, weight 600 |
| Content typography | Inter, 15px, weight 400 |
| Icon | Chevron (default Elementor) atau custom SVG |
| Icon color | `var(--sozo-blue)` |
| Animation | 0.3s ease |

## JavaScript Pattern

Single-open (vanilla JS, no library):

```js
document.querySelectorAll('[data-accordion="single"] .accordion-trigger').forEach((trigger) => {
  trigger.addEventListener('click', () => {
    const item = trigger.closest('.accordion-item');
    const isOpen = trigger.getAttribute('aria-expanded') === 'true';
    
    // Close all siblings
    document.querySelectorAll('[data-accordion="single"] .accordion-item').forEach((other) => {
      if (other !== item) {
        other.querySelector('.accordion-trigger').setAttribute('aria-expanded', 'false');
        other.querySelector('.accordion-content').hidden = true;
        other.classList.remove('is-open');
      }
    });
    
    // Toggle current
    trigger.setAttribute('aria-expanded', String(!isOpen));
    item.querySelector('.accordion-content').hidden = isOpen;
    item.classList.toggle('is-open', !isOpen);
  });
});
```

## Aksesibilitas

- Trigger HARUS `<button>` element, bukan `<div>`.
- Wajib `aria-expanded` di trigger (true/false).
- Wajib `aria-controls` di trigger (id content).
- Content wajib `role="region"` dan `aria-labelledby` (id trigger).
- Content dengan `hidden` attribute saat tertutup (lebih reliable dari `display: none` untuk screen reader).
- Keyboard: trigger focusable via Tab, Enter/Space toggle, arrow keys opsional.
- Jangan trap focus — user harus bisa Tab keluar accordion.

## Anti-pattern

- ❌ Trigger sebagai `<div>` atau `<span>` (harus `<button>`).
- ❌ Hilangkan `aria-expanded` (screen reader gak tau state).
- ❌ Pakai `display: none` instead of `hidden` attribute (beberapa screen reader interpret beda).
- ❌ Icon-only trigger tanpa `aria-label` (text title wajib di dalam button).
- ❌ Auto-rotate accordion (auto-open item setelah beberapa detik) — annoying dan inaccessible.
- ❌ Accordion dengan 1 item saja (pakai section biasa, bukan accordion).

## Notes

- **SKU reference pending:** User mention "accordion kaya di SKU" sebagai referensi. SKU page code belum di-share. Spec ini generic berdasarkan pattern umum. Setelah SKU code tersedia, akan di-update dengan pattern spesifik Sozo (initial open state, icon style, animasi, dll).
- Spec ini juga relevan untuk mega menu dropdown (lihat navbar code), tapi mega menu adalah pattern sendiri dengan layout berbeda.
- Untuk FAQ schema, hubungkan dengan JSON-LD `FAQPage` di `schema-markup.html` (lihat `docs/adr/0005-keep-faq-after-rich-result-removal.md`).
