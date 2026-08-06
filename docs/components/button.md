# Button Component

- **Status:** Spec aktif
- **Versi:** 1.1 (13 Juli 2026) — updated dengan brand color & shape standar
- **Tipe:** Atom (komponen UI dasar)
- **File terkait:** `index.html` (custom HTML) atau Elementor Button widget

## Brand Tokens (Referensi)

| Token | Nilai | CSS Variable |
| :-- | :-- | :-- |
| Brand primary | `#1A2080` | `var(--sozo-blue)` |
| Brand primary hover | `#2a33a3` | `var(--sozo-blue-hover)` |
| Brand cyan accent | `#3AB4F2` | `var(--sozo-cyan)` |
| Text | `#333333` | `var(--sozo-text)` |
| Border | `#e5e7eb` | `var(--sozo-border)` |
| Light bg | `#F4F8FA` | `var(--sozo-light-bg)` |
| Font family | Inter, system-ui, -apple-system, sans-serif | Body/UI; lihat `docs/design-system/colors-typography.md` |

Token lengkap lihat `docs/design-system/colors-typography.md`.

## Anatomi

| Properti | Nilai | Catatan |
| :-- | :-- | :-- |
| Padding (default) | 11px vertikal, 22px horizontal (primary) | Scale by size |
| Min-height | 40px (md), 32px (sm), 48px (lg) | Touch target ≥ 32px mobile |
| Border-radius | **50px (primary, pill)** / 8px (secondary, ghost) | Primary = pill mengikuti navbar WA |
| Font weight | 600 (primary, secondary), 500 (ghost, link) | |
| Font family | Inter, system-ui, -apple-system, sans-serif | Body/UI; lihat `docs/design-system/colors-typography.md` |
| Text alignment | center | Konsisten di semua size |
| Line-height | 1 | Hindari multi-line button |
| Cursor | pointer (default), not-allowed (disabled) | |

## Varian

### Primary

Main CTA. Conversion driver. Pill shape (border-radius 50px) untuk prominence.

- Background: `var(--sozo-blue)` (#1A2080)
- Text: `#ffffff`
- Border: 2px solid `var(--sozo-blue)` (siap untuk hover invert)
- Padding: 11px 22px (md), 10px 18px (≤1280px), 14px 18px (mobile full-width)
- Hover: background `#ffffff`, text `var(--sozo-blue)`, border tetap, transform `translateY(-1px)`, shadow `0 4px 14px rgba(26, 35, 126, 0.3)`
- Active: transform `translateY(0)` (reset hover lift)
- Shape: pill (`border-radius: 50px`)

**Use case:** "Konsultasi Sekarang", "Booking Treatment", "Reservasi Sekarang", "Beli Paket".

**Aturan:** Maks 1 Primary per viewport section. Kalau ada 2 CTA kompetitif, salah satu jadi Secondary.

### Secondary

Action pelengkap. Rounded (8px) — tidak se-prominent Primary.

- Background: transparent
- Border: 1.5px solid `var(--sozo-blue)`
- Text: `var(--sozo-blue)`
- Hover: background `var(--sozo-blue)` 8% opacity

**Use case:** "Lihat Detail", "Pelajari Treatment Lain", "Lihat Lokasi".

### Ghost

Action tersier di area padat.

- Background: transparent
- Border: none
- Text: `var(--sozo-blue)` (atau `var(--sozo-text)`)
- Hover: background `var(--sozo-light-bg)` atau subtle gray

**Use case:** nav link, "Pelajari lebih lanjut" inline, close button.

### Link

Teks dengan underline, bukan button. Untuk inline action.

- Background: none
- Border: none
- Text: `var(--sozo-blue)` + underline
- Hover: text `var(--sozo-blue-hover)`

**Use case:** "Syarat & Ketentuan berlaku", "Baca selengkapnya".

### Icon-only

Hanya icon, tanpa teks. Untuk UI kompak.

- Padding: 8px (square) atau 12px 8px (rectangular)
- Min-width: 40px
- `aria-label` wajib

**Use case:** share button, table action, navigation icon, mobile nav toggle.

## Size

| Size | Padding | Min-height | Font | Use case |
| :-- | :-- | :-- | :-- | :-- |
| `sm` | 8px 16px | 32px | 14px | Compact UI, table action, badge |
| `md` (default) | 11px 22px | 40px | 15px | Most use cases |
| `lg` | 16px 32px | 48px | 18px | Hero CTA, mobile full-width CTA |

## State

| State | Background | Cursor | Pointer-events | Catatan |
| :-- | :-- | :-- | :-- | :-- |
| Default | base | pointer | auto | |
| Hover | base + invert (primary) / base 8% (secondary) | pointer | auto | Primary: translateY(-1px) + shadow |
| Active | base | pointer | auto | Primary: translateY(0) reset |
| Disabled | base + 50% opacity | not-allowed | none | `aria-disabled="true"` |
| Loading | base (atau muted) | wait | none | Spinner + text "Memproses..." |

## HTML Markup (Custom HTML)

```html
<!-- Primary, MD, pill -->
<a href="..." class="btn btn-primary btn-md" role="button">
  Konsultasi Sekarang
</a>

<!-- Primary dengan icon -->
<a href="..." class="btn btn-primary btn-md">
  <svg class="btn-icon" aria-hidden="true">...</svg>
  <span>Booking Treatment</span>
</a>

<!-- Secondary -->
<a href="..." class="btn btn-secondary btn-md" role="button">
  Lihat Detail
</a>

<!-- Ghost -->
<button class="btn btn-ghost btn-md" type="button">
  Pelajari lebih lanjut
</button>

<!-- Link -->
<a href="/syarat-ketentuan" class="btn btn-link">
  Syarat &amp; Ketentuan berlaku
</a>

<!-- Icon-only -->
<button class="btn btn-icon-only" type="button" aria-label="Buka menu">
  <svg aria-hidden="true">...</svg>
</button>

<!-- Disabled -->
<button class="btn btn-primary btn-md" type="button" disabled aria-disabled="true">
  Memproses...
</button>
```

## Elementor Button Widget (Recommended)

| Setting | Primary | Secondary |
| :-- | :-- | :-- |
| Type | Default | Default |
| Size | Medium | Medium |
| Text color | `#FFFFFF` | `#1A2080` |
| Background color | `#1A2080` | transparent |
| Border type | Solid | Solid |
| Border width | 2 | 1.5 |
| Border color | `#1A2080` | `#1A2080` |
| **Border radius** | **50px (pill)** | **8px** |
| Padding | 11px 22px | 12px 24px |
| Typography | Inter, 15px, weight 600 | Inter, 16px, weight 500 |
| Text shadow | none | none |

## Aksesibilitas

- Pakai `<a>` untuk navigasi (kalau ada href), `<button>` untuk action murni.
- Icon-only button wajib `aria-label` yang mendeskripsikan action.
- Disabled state wajib `aria-disabled="true"`.
- Focus state visible: outline 2px `var(--sozo-blue)`, offset 2px.
- Kontras text-to-background minimum 4.5:1 (WCAG AA).
- Primary white-on-#1A2080 = kontras ~12.6:1, lulus AAA.

## Anti-pattern

- ❌ Dua Primary button dalam satu viewport section.
- ❌ Primary button dengan text lebih dari 4 kata.
- ❌ Disabled button tanpa visual cue (opacity harus 50%).
- ❌ Button dengan `width: 100%` di desktop (kecuali mobile CTA).
- ❌ Inline button di tengah paragraf (pakai Link variant).
- ❌ Primary color button untuk action destruktif (Cancel, Hapus) — pakai Secondary atau Ghost.
- ❌ Mixing shape — primary harus pill, secondary/ghost harus rounded 8px, jangan dicampur.

## Notes

- WhatsApp button (nav + floating) punya spec sendiri: `docs/components/whatsapp-button.md`.
- Inconsistency: WA button production code pakai `#1A237E` (sedikit lebih gelap dari `--sozo-blue` #1A2080). Refactor target: samakan ke variable. Track di `docs/adr/0008-wa-button-color-unify.md` (akan dibuat).
- Lihat `docs/pages/[kategori]/[lp].md` untuk usage per LP.
