# SOZO SKIN CLINIC — DESIGN SYSTEM V1

**Status:** Foundation + Core Components V1
**Primary typeface:** Poppins
**Brand primary:** `#1A2080` — Resolution Blue
**Brand secondary:** `#D9C7B1` — Dun
**Base canvas:** `#FFFFFF` — White

> This document is the working source of truth for the Sozo Skin Clinic web design system covered in the current V1 scope. It documents the decisions already made in the design-system work and separates locked brand references from implementation-level recommendations that may be refined later.

## 1. Document Purpose

This document defines a reusable visual and component system for Sozo Skin Clinic.

The purpose is consistency across the current website without changing the existing information architecture or navigation structure.

The system is intentionally lean because the current website uses WhatsApp as the primary conversion destination rather than an on-site booking form.

- Use this document as the baseline for design, Figma components, HTML/CSS implementation, and frontend handoff.
- Use the same tokens and components across Treatment, Promo, Location, and supporting content.
- Prefer reuse over one-off styling.
- Keep the system compact and practical rather than creating components only because they are common in generic design systems.

## 2. Scope

### Included in V1

- Color system
- Color roles and usage
- Typography system using Poppins
- Spacing system
- Layout and grid system
- Border system
- Radius system
- Shadow system
- Core Button
- WhatsApp CTA pattern
- Link
- Badge / Tag
- Image / Media
- Base Card
- Treatment Card
- Promo Card
- Breadcrumb
- Location Card as an existing implementation to be normalized against the system.

### Deferred from V1

- Doctor Card
- Product Card
- Blog Card
- Testimonial Card
- Complex form components
- On-site booking workflow
- Large modal/form patterns beyond the current location picker
- Full page templates
- Complete section pattern library.

### Explicitly out of scope

- Changing the current website information architecture.
- Restructuring the existing treatment hierarchy.
- Removing or merging existing treatment categories.
- Redesigning or restructuring the current navbar.
- Inventing new site sections solely to fit a design-system convention.

## 3. Design-System Philosophy

The visual direction is defined by five words: **Modern, Clinical, Premium, Clean, Refined.**

These words are interpreted as design constraints rather than decorative themes.

### Clarity first
Information hierarchy must remain obvious before decorative treatment is considered.

### Clinical confidence
Typography, contrast, spacing, and imagery should feel precise and professional.

### Premium through precision
Premium perception should come from consistency, restraint, imagery quality, typography, and alignment rather than excessive ornament.

### Clean canvas
White is the primary canvas. Supporting colors separate sections without turning the page into a multi-color composition.

### Consistent primitives
Buttons, cards, spacing, radius, borders, and shadows should reuse the same system tokens.

### Function before ornament
A visual treatment must have a purpose: hierarchy, grouping, feedback, emphasis, or navigation.

### Responsive by intent
Desktop should use available horizontal space; mobile should preserve scanability and tap comfort.

### One component, one job
A component should have a clear responsibility and should not absorb unrelated content or interaction.

### Do not over-systematize
Only create components that are needed by the current product surface or are clearly reused across pages.

### Brand first, semantics second
Brand colors define identity; semantic colors communicate system states and must not be confused with brand accents.

## 4. Brand Color System

The core brand palette is based on the supplied Sozo brand reference and the decisions made during this project.

### Resolution Blue — `#1A2080`
**Role:** Primary brand color.
**Primary usage:** Brand identity, major actions, key emphasis, selected/active states, strong headings where appropriate..

### Dun — `#D9C7B1`
**Role:** Secondary brand color.
**Primary usage:** Beauty accent, secondary emphasis, warm surface, secondary action..

### Almond — `#E9DFD5`
**Role:** Soft surface.
**Primary usage:** Quiet section background, card/supporting surface, soft highlight..

### Isabelline White — `#DFDBD4`
**Role:** Warm neutral.
**Primary usage:** Subtle separation, border, divider, light supporting surface..

### White — `#FFFFFF`
**Role:** Base canvas.
**Primary usage:** Main page background, cards, content surfaces, negative space..

## 5. Complementary Colors

The supplied brand reference also shows complementary colors: Bright Red, Dark Yellow, and Light Brown.

The exact hexadecimal values for these three colors were not provided as text in the reference image. The values below are the working approximations previously used for the web system and should be verified against the original brand source before treating them as locked brand tokens.

### Bright Red — `#EC4242`
**Working value:** `#EC4242`.
**Role:** High-attention accent.
**Usage:** Use only when the content itself needs a red accent, such as a status or high-emphasis promotional detail..

### Dark Yellow — `#FFC200`
**Working value:** `#FFC200`.
**Role:** Promotional accent.
**Usage:** Use for promotional or energetic accents when the meaning supports it..

### Light Brown — `#B5956A`
**Working value:** `#B5956A`.
**Role:** Warm complementary accent.
**Usage:** Use sparingly where a deeper warm tone is needed beside Dun..

## 6. Color Roles & Usage

### Canvas / Base
- **Color family:** White
- **Token value:** `#FFFFFF`
- **Usage:** Primary page background; default card and content surface.

### Brand / Primary
- **Color family:** Resolution Blue
- **Token value:** `#1A2080`
- **Usage:** Main brand expression, primary action, active emphasis.

### Brand / Secondary
- **Color family:** Dun
- **Token value:** `#D9C7B1`
- **Usage:** Secondary action and beauty-oriented accent.

### Soft Surface
- **Color family:** Almond
- **Token value:** `#E9DFD5`
- **Usage:** Low-contrast surface and section variation.

### Warm Neutral
- **Color family:** Isabelline White
- **Token value:** `#DFDBD4`
- **Usage:** Border, divider, subtle surface separation.

### Text Primary
- **Color family:** Neutral dark
- **Token value:** Implementation token
- **Usage:** Primary reading text and headings where blue is not used.

### Text Secondary
- **Color family:** Neutral mid
- **Token value:** Implementation token
- **Usage:** Supporting paragraph and metadata text.

### Border
- **Color family:** Isabelline / neutral
- **Token value:** Implementation token
- **Usage:** Component boundaries and separators.

### Semantic Success
- **Color family:** Green semantic
- **Token value:** Implementation token
- **Usage:** Success feedback only.

### Semantic Warning
- **Color family:** Yellow/orange semantic
- **Token value:** Implementation token
- **Usage:** Warning feedback only.

### Semantic Error
- **Color family:** Red semantic
- **Token value:** Implementation token
- **Usage:** Error feedback only.

### Semantic Info
- **Color family:** Blue semantic
- **Token value:** Implementation token
- **Usage:** Informational feedback only.

## 7. Color Rules

- White is the dominant background and the default canvas.
- Resolution Blue is the main identity and action color.
- Dun is a supporting beauty accent rather than a second primary.
- Almond and Isabelline should be used to create soft separation rather than loud visual changes.
- Complementary colors are optional and contextual.
- Do not introduce a new brand color for a one-off section.
- Do not use multiple accent colors simply to make a section feel more premium.
- Use semantic colors according to meaning, not branding preference.
- Preserve readable contrast for text and interactive controls.
- When a color is reused, reference a token or role rather than hard-coding the hex inside each component.

## 8. Suggested Color Token Architecture

The token layer should separate raw brand values from semantic UI roles.

```css

:root {

  --color-brand-primary: #1A2080;

  --color-brand-secondary: #D9C7B1;

  --color-brand-almond: #E9DFD5;

  --color-brand-isabelline: #DFDBD4;

  --color-white: #FFFFFF;

  --color-text-primary: #171715;

  --color-text-secondary: #41413E;

  --color-text-muted: #777771;

  --color-border: #DFDBD4;

  --color-border-strong: #C8C3BA;

  --color-primary-hover: #151A6D;

  --color-secondary-hover: #CBB79F;

  --color-success: #267A57;

  --color-warning: #A66A18;

  --color-error: #B64242;

  --color-info: #39718C;

}

```

The derived hover and semantic values above are implementation recommendations, not replacements for the locked brand palette.

## 9. Color Do / Do Not

### Do

- Use white as the primary canvas.
- Use blue for primary actions and strong brand moments.
- Use Dun for restrained warmth.
- Use Almond or Isabelline for section separation.
- Use complementary colors when they carry meaning.

### Do not

- Do not make every section a different color.
- Do not use Dun for long body text when contrast is weak.
- Do not use yellow/red merely because a space feels empty.
- Do not add gradients as the default treatment for cards.
- Do not introduce unrelated colors without a semantic or brand reason.

## 10. Gradient Guidance

Gradients may be used as a decorative extension of the brand palette, but they are not the default surface treatment.

Examples previously discussed include Blue-to-Dun, White-to-Blue, and White-to-Dun combinations.

Use gradients primarily for hero accents, campaign visuals, or intentional decorative surfaces.

Avoid turning every section background into a gradient.

## 11. Typography System

### Typeface

**Primary family:** Poppins

The current implementation already loads Poppins weights 400, 500, 600, 700, and 800. fileciteturn12file0L1-L4

Use one primary family unless a later content requirement establishes a clear need for a secondary typeface.

### Weight 400 — Regular
Use for: Body copy and normal reading text.

### Weight 500 — Medium
Use for: Supporting text and restrained emphasis.

### Weight 600 — Semi Bold
Use for: UI labels, buttons, subtitles, card titles.

### Weight 700 — Bold
Use for: Headings and important titles.

### Weight 800 — Extra Bold
Use for: Selective hero emphasis or major display text.

## 12. Typography Scale

### Display XL
- Size: **64px**
- Weight: **700**
- Line-height: **1.05**
- Use: Large hero heading

### Display LG
- Size: **56px**
- Weight: **700**
- Line-height: **1.10**
- Use: Large hero or campaign heading

### H1
- Size: **48px**
- Weight: **700**
- Line-height: **1.15**
- Use: Page heading

### H2
- Size: **40px**
- Weight: **700**
- Line-height: **1.20**
- Use: Section heading

### H3
- Size: **32px**
- Weight: **600**
- Line-height: **1.25**
- Use: Card or subsection heading

### H4
- Size: **24px**
- Weight: **600**
- Line-height: **1.30**
- Use: Smaller section heading

### H5
- Size: **20px**
- Weight: **600**
- Line-height: **1.35**
- Use: Compact heading

### Body Large
- Size: **18px**
- Weight: **400**
- Line-height: **1.65**
- Use: Lead or introduction

### Body
- Size: **16px**
- Weight: **400**
- Line-height: **1.60**
- Use: Default reading text

### Body Small
- Size: **14px**
- Weight: **400**
- Line-height: **1.55**
- Use: Supporting copy

### Caption
- Size: **12px**
- Weight: **500**
- Line-height: **1.40**
- Use: Metadata

### Label
- Size: **12px**
- Weight: **600**
- Line-height: **1.40**
- Use: Badge and compact UI

## 13. Typography Rules

- Poppins is used consistently across the product surface.
- Heading weight should be strong enough to establish hierarchy but not so heavy that every section feels promotional.
- Body copy should remain readable and should not be pushed to decorative colors.
- Use letter-spacing intentionally; do not add tracking to ordinary body text.
- Uppercase is reserved for labels, eyebrows, and short metadata where useful.
- Hero typography may be large, but it should remain controlled and compatible with compact page layouts.
- Long-form text should use constrained measure rather than a full-width paragraph.
- Typography tokens should be named by role rather than by a single component.

## 14. Responsive Typography

Use fluid sizing for major headings where practical.

Example: `font-size: clamp(40px, 8vw, 64px);` for large hero titles.

Mobile should not simply scale every desktop value proportionally; readability and hierarchy come first.

## 15. Spacing System

The system uses a 4px base with an 8px rhythm for most common spacing.

### `space-1` — 4px
Tiny icon or internal micro spacing.

### `space-2` — 8px
Label/icon gap, compact content.

### `space-3` — 12px
Label to text, small groups.

### `space-4` — 16px
Default element gap.

### `space-5` — 24px
Card content and component gap.

### `space-6` — 32px
Content grouping.

### `space-7` — 40px
Larger grouping.

### `space-8` — 48px
Section internal spacing.

### `space-9` — 64px
Small section separation.

### `space-10` — 80px
Normal section separation.

### `space-11` — 96px
Major section separation.

### `space-12` — 120px
Hero or major transition.

## 16. Spacing Rules

- Prefer existing spacing tokens over arbitrary pixel values.
- Use smaller steps inside components and larger steps between content groups.
- Do not use the largest spacing values inside dense cards.
- Whitespace is a functional part of the premium aesthetic.
- When in doubt, reduce decorative separation before reducing text readability.

## 17. Common Vertical Rhythm

A common text stack is:

`Eyebrow → 12px → Heading → 16px → Description → 24px → CTA`

A common card stack is:

`Image → 24px → Category → 8px → Title → 8px → Description → 20–24px → Action`

## 18. Layout System

### Container

**Desktop target:** up to 1280px content width.

**Tablet:** fluid width with approximately 32px horizontal padding.

**Mobile:** fluid width with approximately 20px horizontal padding.

The container should be centered and should not become wider simply because the viewport grows.

Example:

```css

.container {

  width: min(100% - 40px, 1280px);

  margin-inline: auto;

}

```

## 19. Grid System

### Desktop

12 columns.

24px default gutter.

Use the grid to create repeatable content alignment.

### Tablet

8 columns.

20px default gap.

### Mobile

4 columns.

16px default gap.

Grid count is a layout tool, not a requirement that every section visibly show all columns.

## 20. Content Measure

Do not let long text occupy the entire 1280px container.

A reading measure around 60–70 characters is a practical starting point for body content.

Use `max-width: 65ch` where a paragraph needs a constrained reading width.

## 21. Section Spacing

### Desktop

- Normal section: 80px block spacing.

- Major section: 96px block spacing.

- Hero: up to 120px where content and viewport justify it.

### Mobile

- Normal section: around 56px.

- Major section: around 64px.

- Hero: around 72px.

These are baseline values and should be adjusted when a real page pattern demonstrates a better rhythm.

## 22. Border System

Borders should communicate grouping, not decoration.

### None — 0
No visible boundary.

### Subtle — 1px
Default card, input, divider.

### Strong — 1px
Stronger visual boundary.

### Focus — 2px
Keyboard or interaction focus where appropriate.

Default border color: `#DFDBD4` or another neutral token when contrast requirements demand it.

Default strong border color: `#C8C3BA`.

## 23. Radius System

### `radius-sm` — 6px
Small utility UI.

### `radius-md` — 10px
Inputs and compact controls.

### `radius-lg` — 16px
Default card.

### `radius-xl` — 24px
Large featured container.

### `radius-full` — 999px
Pill button, badge, compact tag.

The default card radius is 16px.

The default pill radius is 999px.

Do not make every component fully rounded.

## 24. Shadow System

Shadows are restrained. A component may use no shadow at all.

### `shadow-none`
- Value: `none`
- Use: Flat surface

### `shadow-sm`
- Value: `0 2px 8px rgba(23, 23, 21, 0.04)`
- Use: Subtle elevation

### `shadow-md`
- Value: `0 8px 24px rgba(23, 23, 21, 0.07)`
- Use: Default elevated card

### `shadow-lg`
- Value: `0 16px 40px rgba(23, 23, 21, 0.10)`
- Use: Dropdown, modal, floating surface

### `shadow-xl`
- Value: `0 24px 60px rgba(23, 23, 21, 0.12)`
- Use: Rare high-elevation surface

Avoid heavy shadows that make the interface feel like a generic SaaS template.

## 25. Component Inventory V1

1. **Button**

2. **WhatsApp CTA**

3. **Link**

4. **Badge / Tag**

5. **Image / Media**

6. **Base Card**

7. **Treatment Card**

8. **Promo Card**

9. **Breadcrumb**

10. **Existing Location Card**

The following content cards are intentionally deferred: Doctor Card, Product Card, Blog Card, Testimonial Card.

## 26. Button System

### Purpose

A generic action component for primary, secondary, and lower-emphasis UI actions.

### Variants

#### Primary
Main action. Resolution Blue background with White text.

#### Secondary
Supporting action. Dun background with Resolution Blue text.

#### Outline
Lower-emphasis action. Transparent background with a blue border.

### Sizes

#### Small
Height: 40px. Use: Compact card or dense UI.

#### Medium
Height: 48px. Use: Default button.

#### Large
Height: 56px. Use: Hero or major CTA.

### Base anatomy

`[ icon ]  Label  [ optional trailing icon ]`

Default icon-to-label gap: 8px.

Default horizontal padding: 20–24px.

Default radius: 999px.

Default font: Poppins, 14px, 600.

### States

- Default
- Hover
- Active / Pressed
- Focus
- Disabled

Do not change button dimensions between hover and active states.

## 27. Button CSS Reference

```css

.btn {

  display: inline-flex;

  align-items: center;

  justify-content: center;

  gap: 8px;

  min-height: 48px;

  padding-inline: 24px;

  border: 0;

  border-radius: 999px;

  font-family: "Poppins", sans-serif;

  font-size: 14px;

  font-weight: 600;

  line-height: 1;

  text-decoration: none;

}

```

## 28. WhatsApp CTA System

### Role

The WhatsApp CTA is a Sozo-specific conversion pattern built from the Button component.

It is not a generic “green social button”. The primary visual treatment remains inside the Sozo brand system while the WhatsApp icon signals destination/context.

### Primary pattern

`[ WhatsApp icon ] Konsultasi via WhatsApp`

### Alternative copy

- Konsultasi Sekarang

- Tanya Treatment

- Booking via WhatsApp

- Hubungi Cabang

- Tanya Promo

### Placement

- Hero CTA
- Treatment detail
- Promo detail or high-intent promo surface
- Location detail / branch surface
- Other high-intent conversion zones

### Mobile

Maintain a comfortable tap target and use full width when the context calls for a dominant CTA.

## 29. WhatsApp CTA Hierarchy

### Primary WhatsApp

Resolution Blue background, White text, WhatsApp icon.

### Secondary WhatsApp

Dun or outline treatment when a page already has a primary action.

### Floating WhatsApp

Separate floating pattern. It should not be treated as a normal inline button.

### Copy rule

Make the label describe the user action and destination. Avoid vague labels such as “Click Here”.

## 30. Link System

Links handle navigation and lighter informational actions.

Default link color: Resolution Blue.

Default weight: 500–600 depending on context.

Hover may introduce underline or a subtle color shift.

Use arrow notation such as `View Treatment →` when the arrow communicates navigation direction.

Do not style every link as a pill button.

## 31. Badge / Tag System

### Purpose

Compact context markers such as category, promotion, status, or content label.

### Default shape

Full radius.

Approximate height: 28–32px.

Horizontal padding: approximately 10px.

Font: 12px / 600.

### Brand badge

Almond background with Resolution Blue text.

### Neutral badge

Isabelline background with Resolution Blue or neutral text.

### Promo badge

Use Yellow or Red only when the promotional meaning supports it.

### Rule

One primary badge per card is generally enough. Avoid stacks of decorative badges.

## 32. Image / Media System

Images are a major part of the Sozo visual language because treatments, doctors, products, locations, and content all rely on media.

### 1:1
Doctor, product, profile.

### 4:3
Treatment and general cards.

### 3:2
Photography/editorial surfaces.

### 16:9
Blog, hero, wide media.

Default image behavior: `object-fit: cover` inside a fixed aspect-ratio frame.

Image hover may use a subtle transform such as `scale(1.03)` for interactive cards.

Avoid large zooms or dramatic image transitions.

Use overlays only when text readability requires them.

## 33. Base Card

The Base Card is the shared visual foundation for content cards.

### Default

- Background: White

- Border: 1px solid Isabelline

- Radius: 16px

- Shadow: none

- Overflow: hidden when media touches the edge

### Visual variants

- Standard: white + border + no shadow.

- Elevated: white + border + soft shadow.

- Featured: stronger visual emphasis while preserving the same underlying rules.

Content types are not separate visual systems. They are content configurations built on top of the base card.

## 34. Base Card Anatomy

```text

CARD

├── Media

├── Eyebrow / Badge (optional)

├── Title

├── Supporting content (optional)

└── Action (optional)

```

Not every card must include every zone.

## 35. Treatment Card

### Mental model

Treat Treatment Card like a service catalog item—similar in behavior to an e-commerce product/SKU card, but the item is a treatment/service rather than a physical SKU.

### Core job

- Identify the treatment.
- Give enough context to understand the category and value proposition.
- Send the user to the treatment detail page.

### Anatomy

1. Media

2. Category

3. Treatment name

4. Short description

5. View Treatment link

### Recommended media ratio

4:3

### Description constraint

Target 2–3 lines. Use line clamping if required for consistent card height.

### CTA rule

Treatment card navigates to Treatment Detail. The main WhatsApp conversion CTA belongs primarily on the treatment detail experience rather than every catalog card.

## 36. Treatment Card Example

```html

<article class="treatment-card">

  <a href="/pico-laser/" class="treatment-card__media">

    <img src="pico-laser.jpg" alt="Pico Laser treatment" loading="lazy">

  </a>

  <div class="treatment-card__content">

    <span class="treatment-card__category">SKIN</span>

    <h3 class="treatment-card__title">Pico Laser</h3>

    <p class="treatment-card__description">Advanced laser treatment for skin rejuvenation and brightening.</p>

    <a href="/pico-laser/" class="treatment-card__action">View Treatment →</a>

  </div>

</article>

```

## 37. Treatment Card CSS Reference

```css

.treatment-card {

  background: #FFFFFF;

  border: 1px solid #DFDBD4;

  border-radius: 16px;

  overflow: hidden;

}

.treatment-card__media {

  display: block;

  aspect-ratio: 4 / 3;

  overflow: hidden;

}

.treatment-card__media img {

  width: 100%;

  height: 100%;

  object-fit: cover;

  transition: transform 400ms ease;

}

.treatment-card:hover .treatment-card__media img {

  transform: scale(1.03);

}

```

## 38. Promo Card

### Mental model

A promotional entry point that surfaces an offer and leads users toward the relevant detail or WhatsApp consultation.

### Core job

- Identify the promotion.
- Create enough urgency or relevance to investigate.
- Provide a clear next action.

### Anatomy

1. Media

2. Promo badge

3. Promo title

4. Short supporting copy

5. CTA

### Promo color rule

Yellow, red, or warm accents are optional and should reflect promotional meaning. Brand Blue remains available as the main CTA color.

## 39. Promo Card Example

```html

<article class="promo-card">

  <a href="/promo/example/" class="promo-card__media">

    <img src="promo.jpg" alt="Sozo treatment promotion" loading="lazy">

    <span class="promo-card__badge">PROMO</span>

  </a>

  <div class="promo-card__content">

    <h3 class="promo-card__title">Glow Treatment</h3>

    <p class="promo-card__description">Nikmati special offer untuk treatment pilihan Sozo.</p>

    <a href="#" class="btn btn-primary btn-md">Konsultasi via WhatsApp</a>

  </div>

</article>

```

## 40. Breadcrumb

Breadcrumb supports pages where the information architecture is deep enough that users benefit from context.

Treatment examples can include `Treatment / Skin / Laser / Pico Laser`.

Location examples can include `Lokasi / Jakarta / Jakarta Selatan / Pondok Indah`.

The current page can use primary or stronger text; ancestors can use muted text.

Breadcrumb is a navigation support component, not a visual headline.

## 41. Existing Location Card — Design-System Status

The Location Card already exists in the current implementation and should be treated as an existing component rather than rebuilt from scratch.

The current desktop card uses a media area, region badge, clinic name, address, and stacked WhatsApp/Maps actions. fileciteturn12file0L417-L536

The current mobile implementation converts the desktop card into a compact horizontal list item, hides some desktop-only details, and keeps lightweight actions inline. fileciteturn12file0L877-L1001

The current location experience also includes location search, quick city links, a paginated clinic grid, an empty state, a location-selection modal, and a floating promo control. fileciteturn12file0L16-L54 fileciteturn12file0L57-L122

These are implementation facts from the supplied file. They are not being treated as a direction to redesign the current information architecture.

## 42. Existing Location Card Normalization Notes

The design system should eventually normalize the existing Location Card to shared tokens for typography, border, radius, shadow, and button states.

Current code uses a 20px desktop card radius, soft blue-tinted shadow, 20px content padding, and two actions. fileciteturn12file0L417-L536

For mobile the card is intentionally flat with no shadow and no outer radius, which is a valid responsive pattern and should not be assumed to violate the base card rule. fileciteturn12file0L878-L1001

## 43. Deferred Components

### Doctor Card
Deferred. Define only when the actual content/data and page context are ready.

### Product Card
Deferred. Define only when the actual content/data and page context are ready.

### Blog Card
Deferred. Define only when the actual content/data and page context are ready.

### Testimonial Card
Deferred. Define only when the actual content/data and page context are ready.

Do not create a speculative component API for these deferred cards just to fill a library.

## 44. Responsive System

### Desktop

- Use horizontal space intentionally.

- Default max content width: 1280px.

- Use 12-column grid where beneficial.

- Cards may use 3-column or 4-column arrangements depending on content density.

### Tablet

- Use 8-column grid where helpful.

- Reduce gutters before drastically shrinking content.

### Mobile

- Use 4-column underlying grid or straightforward full-width stacking.

- Preserve comfortable tap targets.

- Use horizontal scroll/slider only where it improves discovery and the content set benefits from it.

- Do not blindly stack every desktop pattern if the information is better represented as a compact horizontal list.

## 45. Mobile Compactness Rule

The Sozo interface should minimize unnecessary vertical scrolling.

Compact mobile patterns are allowed where they preserve hierarchy and tapability.

Example: the current Location Card becomes a compact row rather than a full card on mobile. fileciteturn12file0L877-L1001

## 46. Interaction Principles

- Interactive elements should have a clear visual affordance.
- Hover states should be subtle rather than theatrical.
- Focus states must remain visible for keyboard users.
- Buttons should not jump in size between states.
- Image hover should be restrained.
- Use active state for selected navigation or controls where applicable.
- Floating controls should have enough offset from viewport edges.
- Long actions should not be hidden behind ambiguous icons without labels when a text label improves clarity.

## 47. Accessibility Baseline

The design system should be implemented with accessible contrast and semantics even though V1 is primarily a visual system.

- Use semantic HTML for headings, links, buttons, and images.
- Provide meaningful alt text for informative imagery.
- Mark decorative imagery appropriately.
- Keep keyboard focus visible.
- Keep touch targets comfortably tappable on mobile.
- Do not use color alone to communicate status.
- Ensure text remains readable over colored backgrounds.
- Use visible labels when an icon is not universally recognizable.

## 48. CSS Naming Convention

Use a consistent naming model. BEM-style naming is recommended for component classes when working in plain HTML/CSS.

Example:

`treatment-card`

`treatment-card__media`

`treatment-card__content`

`treatment-card__title`

`treatment-card--featured`

Keep component names semantic and reusable.

## 49. Token Usage Rule

Component code should prefer semantic variables over raw hex values.

Bad: `background: #1A2080;` repeated throughout many unrelated files.

Better: `background: var(--color-brand-primary);`

Raw values may remain inside the token definition layer itself.

## 50. Suggested Root Token Block

```css

:root {

  /* Typography */

  --font-family-primary: "Poppins", sans-serif;

  --font-weight-regular: 400;

  --font-weight-medium: 500;

  --font-weight-semibold: 600;

  --font-weight-bold: 700;

  --font-weight-extrabold: 800;

  /* Colors */

  --color-brand-primary: #1A2080;

  --color-brand-secondary: #D9C7B1;

  --color-brand-almond: #E9DFD5;

  --color-brand-isabelline: #DFDBD4;

  --color-white: #FFFFFF;

  --color-text-primary: #171715;

  --color-text-secondary: #41413E;

  --color-text-muted: #777771;

  --color-border: #DFDBD4;

  --color-border-strong: #C8C3BA;

  /* Spacing */

  --space-1: 4px;

  --space-2: 8px;

  --space-3: 12px;

  --space-4: 16px;

  --space-5: 24px;

  --space-6: 32px;

  --space-7: 40px;

  --space-8: 48px;

  --space-9: 64px;

  --space-10: 80px;

  --space-11: 96px;

  --space-12: 120px;

  /* Radius */

  --radius-sm: 6px;

  --radius-md: 10px;

  --radius-lg: 16px;

  --radius-xl: 24px;

  --radius-full: 999px;

  /* Shadows */

  --shadow-none: none;

  --shadow-sm: 0 2px 8px rgba(23, 23, 21, 0.04);

  --shadow-md: 0 8px 24px rgba(23, 23, 21, 0.07);

  --shadow-lg: 0 16px 40px rgba(23, 23, 21, 0.10);

  --shadow-xl: 0 24px 60px rgba(23, 23, 21, 0.12);

}

```

## 51. Component Dependency Map

```text

Tokens

  ↓

Foundation primitives

  ↓

Button / Link / Badge / Image / Card

  ↓

Treatment Card / Promo Card / Location Card

  ↓

Page-level patterns

```

The dependency rule means a higher-level pattern should not introduce random colors, radius values, or spacing values when an existing token already covers the need.

## 52. Treatment Card QA Checklist

- [ ] Uses correct 4:3 media frame by default.

- [ ] Category is visually secondary to treatment name.

- [ ] Treatment name is immediately scannable.

- [ ] Description is short enough to preserve card height consistency.

- [ ] Action uses link behavior toward detail.

- [ ] Hover treatment is subtle.

- [ ] Card follows shared border/radius rules.

- [ ] Image uses object-fit cover.

- [ ] Mobile remains readable without forcing desktop density.

## 53. Promo Card QA Checklist

- [ ] Promo label is meaningful.

- [ ] Promo title is clear.

- [ ] Supporting copy is concise.

- [ ] Color accents are contextual.

- [ ] CTA clearly communicates action.

- [ ] Card does not become an advertising banner with excessive badges.

- [ ] Image ratio remains consistent with the listing pattern.

- [ ] Hover behavior is restrained.

## 54. Button QA Checklist

- [ ] Variant is semantically correct.

- [ ] Size matches context.

- [ ] Text uses Poppins 600.

- [ ] Radius follows the system.

- [ ] Hover/active/focus/disabled states exist.

- [ ] Focus is visible.

- [ ] Icon size is balanced with label.

- [ ] No layout jump occurs on hover/active.

- [ ] CTA copy describes the action.

## 55. Location Card QA Checklist

- [ ] Uses shared brand color token for primary action.

- [ ] WhatsApp and Maps actions have clear hierarchy.

- [ ] Desktop media area remains stable.

- [ ] Address remains readable.

- [ ] Mobile compact mode remains scannable.

- [ ] No unnecessary shadow appears on compact mobile list items.

- [ ] Search and branch selection remain understandable.

## 56. Figma Organization Recommendation

When this system is moved into Figma, organize it in layers rather than by page.

Recommended pages:

1. `00 Cover & Principles`

2. `01 Foundations`

3. `02 Components`

4. `03 Sozo Components`

5. `04 Patterns — Future`

6. `05 Page Templates — Future`

Use variables for colors, spacing, radius, and typography wherever the Figma setup allows it.

## 57. Figma Naming Examples

Color variables:

- `brand/primary`

- `brand/secondary`

- `brand/almond`

- `brand/isabelline`

- `neutral/white`

Components:

- `Button/Primary/MD`

- `Button/Secondary/MD`

- `Button/Outline/MD`

- `CTA/WhatsApp/Primary`

- `Card/Base/Standard`

- `Card/Treatment/Standard`

- `Card/Promo/Standard`

- `Card/Location/Existing`

## 58. HTML Implementation Guidance

Load Poppins once at the document level or through the site-wide asset pipeline rather than per-component when possible.

Use semantic tags: `article` for content cards, `a` for navigation, `button` for true in-page actions, and headings in logical order.

Do not use a `div` with a click handler where an anchor or button provides a better semantic element.

## 59. Example Mini System

```html

<section class="section">

  <div class="container">

    <div class="section-heading">

      <span class="eyebrow">TREATMENT</span>

      <h2>Featured Treatment</h2>

      <p>Explore treatment options from Sozo Skin Clinic.</p>

    </div>

    <div class="treatment-grid">

      <!-- Treatment Cards -->

    </div>

    <div class="section-action">

      <a class="btn btn-outline" href="/treatment/">

        View All Treatments

      </a>

    </div>

  </div>

</section>

```

## 60. Do Not Create One-Off Styling

- Do not create `.promo-blue-v2`, `.card-special-3`, or similar one-off variants just to fix a single page.
- Prefer adding a documented variant only if the pattern appears repeatedly or has a clear semantic purpose.
- When one page needs a special case, keep the exception local and document why it exists.

## 61. Existing Website Architecture Alignment

The design system is designed to sit on top of the existing site structure. The existing structure contains Treatment as the central parent with categories including Hair Removal, Hair, Injectable, Skin, and Slimming; it also includes Blog, Location, Product, Promo, About, Testimoni, and Tim Dokter pages. fileciteturn1file0L1-L112 fileciteturn1file0L113-L166

This document does not propose changing that architecture.

Treatment Card patterns should therefore be flexible enough to work across the existing treatment categories without creating a separate component for every taxonomy branch.

## 62. Treatment Catalog Behavior

Treatment Card acts as a catalog preview.

Treatment Detail acts as the information and evaluation experience.

WhatsApp CTA acts as the main conversion action.

This separation prevents the card from becoming overloaded with every treatment detail.

## 63. Information Density Rules

- Cards should summarize, not fully explain.
- Use hierarchy to distinguish category, title, supporting copy, and action.
- Keep the highest-value information near the top of the card.
- Do not add price, duration, downtime, ratings, badges, and multiple CTAs by default unless the actual content strategy requires them.
- When extra metadata is needed, add it through a documented card variant rather than arbitrary inline elements.

## 64. CTA Hierarchy Rules

A page or section should usually have one dominant action.

Secondary actions should visibly recede.

Text links can carry supporting navigation without competing with primary conversion.

On Treatment Detail, the dominant action can be WhatsApp consultation.

On a Treatment Listing, the dominant card action is navigation to the treatment detail, not necessarily direct WhatsApp conversion.

## 65. Premium Without Decoration

For Sozo, premium perception is produced by discipline:

- Generous but controlled whitespace.

- Strong alignment.

- Crisp typography hierarchy.

- High-quality imagery.

- Restrained shadows.

- Consistent radius.

- Limited palette.

- Intentional motion.

Avoid using gold-like colors, heavy gradients, excessive curves, or ornamental patterns as shortcuts for premium perception.

## 66. Clinical Without Feeling Cold

Resolution Blue provides clinical and professional authority.

Dun and Almond provide warmth and beauty context.

White prevents the interface from becoming visually heavy.

The balance should feel precise but human.

## 67. Clean Background Strategy

White is the primary canvas.

Dun and Almond should be used as secondary surfaces.

Isabelline functions as a quiet separator or warm neutral.

A section does not need a colored background to be a distinct section; spacing and typography can provide separation.

## 68. Image Quality Rules

- Prefer sharp, well-lit, professionally framed photography.
- Keep subject framing consistent within a card type.
- Avoid inconsistent crops across a single listing grid.
- Use the same aspect-ratio rule for repeated card types.
- Do not use decorative overlays unless they improve information legibility.

## 69. Motion Principles

Motion is functional and subtle.

### Recommended

- 150–220ms for UI color/state transitions.
- Around 300–400ms for image hover transforms.
- Small card lift such as `translateY(-2px)` when helpful.

### Avoid

- Large card jumps.
- Aggressive scaling.
- Long animations that slow navigation.
- Constant movement on informational content.

## 70. Accessibility for Brand Colors

Because Resolution Blue is used heavily, test text-on-blue and blue-on-white combinations in the implemented product.

Because Dun and Almond are light warm colors, avoid using them as the only background behind small low-weight text without a contrast check.

Do not use the complementary yellow or red as the only state indicator.

## 71. Error / Empty States

Error and empty states are supporting patterns and should reuse the Button, typography, border, and spacing system.

The existing Location implementation already includes an empty state with a reset action. fileciteturn12file0L117-L122

The future pattern should normalize its visual tokens rather than create a new visual language.

## 72. Modal Surface Baseline

A modal is not currently a core design-system component for the generic site, but the existing Location implementation includes a location-selection modal. fileciteturn12file0L128-L148

When modal styling is normalized, use `radius-xl` or a mobile-specific bottom-sheet treatment, `shadow-lg` or `shadow-xl`, and the same Poppins/color tokens.

## 73. Existing Floating Promo

The current location implementation contains a floating promo button using Resolution Blue, a white icon circle, and a small red notification accent. fileciteturn12file0L151-L160

Treat this as an existing pattern to be standardized later, not as a new generic component requirement for all pages.

## 74. Design Review Checklist

- [ ] Is the primary action obvious?

- [ ] Is the brand palette respected?

- [ ] Is white allowed to dominate where appropriate?

- [ ] Does typography follow the Poppins hierarchy?

- [ ] Are spacing values from the system?

- [ ] Are radius values from the system?

- [ ] Are borders purposeful?

- [ ] Are shadows restrained?

- [ ] Are cards consistent across a listing?

- [ ] Does mobile preserve the same information hierarchy?

- [ ] Does any decorative detail lack a clear purpose?

## 75. Engineering Review Checklist

- [ ] Are tokens defined in one place?

- [ ] Are components using semantic variables?

- [ ] Are repeated styles abstracted?

- [ ] Are anchors used for navigation?

- [ ] Are buttons used for true actions?

- [ ] Are images given useful alt text?

- [ ] Are focus states present?

- [ ] Are responsive breakpoints consistent?

- [ ] Are hard-coded colors minimized?

- [ ] Are one-off variants documented?

## 76. Design System Acceptance Criteria V1

- A designer can build a standard treatment listing without inventing new colors, type sizes, radii, or shadows.
- A developer can implement core components from tokens without copying arbitrary visual values from page to page.
- The main CTA pattern is consistent and recognizable as Sozo.
- Cards share a visual foundation while allowing different content types.
- Existing Location UI can be normalized incrementally without rebuilding the underlying location functionality.
- The system remains compatible with the current website information architecture.

## 77. Current Baseline Summary

| Brand primary | #1A2080 | Resolution Blue |

| Brand secondary | #D9C7B1 | Dun |

| Supporting light | #E9DFD5 | Almond |

| Supporting neutral | #DFDBD4 | Isabelline White |

| Canvas | #FFFFFF | White |

| Typeface | Poppins | Single primary family |

| Spacing | 4px base | 8px rhythm for common patterns |

| Container | 1280px max | Desktop baseline |

| Grid | 12 / 8 / 4 | Desktop / tablet / mobile |

| Default card radius | 16px | Base card |

| Button radius | 999px | Pill |

| Default card shadow | None | Use elevation selectively |

## 78. Future Expansion Roadmap

The following items are intentionally outside the current V1 foundation and can be added later when needed.

1. Doctor Card

2. Product Card

3. Blog Card

4. Testimonial Card

5. Treatment Detail Pattern

6. Location Detail Pattern

7. Promo Section Pattern

8. Hero Pattern

9. Section heading pattern

10. Responsive page templates

11. Full Figma component library

12. Documentation site / Storybook-style component reference

## 79. Future Component Rule

Before adding a new component, answer three questions:

1. Does the current website actually need it?

2. Is the content or interaction repeated enough to justify a reusable component?

3. Can it be composed from existing primitives instead of creating a parallel visual system?

If the answer is no, keep it local until a real repetition appears.

## 80. Versioning

### V1.0

Initial foundation and core component baseline documented in this project.

### V1.x

Use for refinements that do not change the core design language.

Examples: token cleanup, component API refinements, accessibility improvements, responsive edge-case corrections.

### V2

Use when the system gains substantial new patterns or the website scope changes materially.

## 81. Change Log Template

| Date | Version | Change | Status |

|---|---|---|---|

| YYYY-MM-DD | 1.0 | Initial design-system foundation | Approved as working baseline |

## 82. Notes on Source Fidelity

Locked decisions in this document come from the brand colors and choices explicitly established during the design-system work.

Where implementation-level values were proposed—such as hover colors, semantic colors, shadow values, or exact spacing—they should be treated as system recommendations rather than original brand-guide claims.

Complementary color hex codes are working approximations because the supplied reference image did not provide machine-readable hex values.

Existing Location implementation details cited in this document come directly from the supplied HTML file. fileciteturn12file0L409-L536

## 83. Final Design Direction

The Sozo web system should feel like a **clean clinical beauty platform**, not a generic SaaS dashboard and not an overly ornate luxury-magazine website.

Use the following mental model when reviewing any new UI:

**White is the canvas.**

**Resolution Blue is the identity and primary action.**

**Dun is the beauty accent.**

**Almond and Isabelline create softness and separation.**

**Poppins provides the structured typographic voice.**

**Spacing and consistency create the premium feel.**

**Components should remain compact, reusable, and purposeful.**

## 84. Quick Reference — One Page

```text

SOZO DESIGN SYSTEM V1

BRAND

Primary   #1A2080  Resolution Blue

Secondary #D9C7B1  Dun

Almond    #E9DFD5

Isabelline#DFDBD4

White     #FFFFFF

TYPOGRAPHY

Poppins 400 / 500 / 600 / 700 / 800

LAYOUT

1280px max container

12 / 8 / 4 column grid

SPACING

4px base → 8, 12, 16, 24, 32, 40, 48, 64, 80, 96, 120

RADIUS

6 / 10 / 16 / 24 / Full

SHADOW

None / subtle / medium / floating

CORE

Button

WhatsApp CTA

Link

Badge

Image

Base Card

Breadcrumb

SOZO

Treatment Card

Promo Card

Location Card (existing)

```

## 85. Practical Token Reference Appendix

### Button label examples

- View Treatment

- View All Treatments

- Konsultasi via WhatsApp

- Konsultasi Sekarang

- Tanya Treatment

- Hubungi Cabang

- Tanya Promo

- View Location

### Treatment categories

- Skin

- Hair

- Hair Removal

- Injectable

- Slimming

### Card actions

- View Treatment →

- Read Article →

- View Product →

- View Profile →

- View Location →

### Common content labels

- TREATMENT

- SKIN

- HAIR

- INJECTABLE

- SLIMMING

- PROMO

- NEW

- LOCATION

### Image ratios

- 1:1

- 4:3

- 3:2

- 16:9

### Common gaps

- 8px

- 12px

- 16px

- 24px

- 32px

### Common section spacing

- 56px

- 64px

- 80px

- 96px

- 120px

## 86. Component Decision Table

| Need | Use | Do not use |

|---|---|---|

| Main action | Button / WhatsApp CTA | Plain text link with weak affordance |

| Navigation | Link | Button unless an in-page action is required |

| Category context | Badge / Label | Large headline treatment |

| Catalog item | Base Card + content type | One-off card styles |

| Treatment catalog | Treatment Card | Full detail inside card |

| Promotion | Promo Card | Unstructured banner |

| Location | Existing Location Card | Rebuild from scratch |

| Page hierarchy | Breadcrumb | Decorative breadcrumb |

## 87. Component Composition Examples

### Treatment section
Section heading + Treatment Card grid + View All link

### Promo section
Section heading + Promo Card list + WhatsApp CTA

### Location section
Section heading + Existing Location Card grid/list + Location search pattern

### Hero conversion
Eyebrow + Display heading + Lead paragraph + WhatsApp CTA + optional secondary link

## 88. Responsive Composition Examples

### Treatment desktop
3 columns of cards with 24px gap when the content density supports it.

### Treatment mobile
1-column cards or a horizontal slider when discovery benefits from side-scrolling.

### Promo desktop
2–3 cards depending on copy and image ratio.

### Promo mobile
Stack or horizontal slider; prioritize title and CTA.

### Location desktop
Existing grid/card structure with search and quick links.

### Location mobile
Existing compact list structure.

## 89. Typography Usage Matrix

| Context | Token | Typical use |

|---|---|---|

| Display XL | 64px / 700 / 1.05 | Large hero heading |

| Display LG | 56px / 700 / 1.10 | Large hero or campaign heading |

| H1 | 48px / 700 / 1.15 | Page heading |

| H2 | 40px / 700 / 1.20 | Section heading |

| H3 | 32px / 600 / 1.25 | Card or subsection heading |

| H4 | 24px / 600 / 1.30 | Smaller section heading |

| H5 | 20px / 600 / 1.35 | Compact heading |

| Body Large | 18px / 400 / 1.65 | Lead or introduction |

| Body | 16px / 400 / 1.60 | Default reading text |

| Body Small | 14px / 400 / 1.55 | Supporting copy |

| Caption | 12px / 500 / 1.40 | Metadata |

| Label | 12px / 600 / 1.40 | Badge and compact UI |

## 90. Spacing Usage Matrix

| Token | Value | Example |

|---|---:|---|

| `space-1` | 4px | Tiny icon or internal micro spacing |

| `space-2` | 8px | Label/icon gap, compact content |

| `space-3` | 12px | Label to text, small groups |

| `space-4` | 16px | Default element gap |

| `space-5` | 24px | Card content and component gap |

| `space-6` | 32px | Content grouping |

| `space-7` | 40px | Larger grouping |

| `space-8` | 48px | Section internal spacing |

| `space-9` | 64px | Small section separation |

| `space-10` | 80px | Normal section separation |

| `space-11` | 96px | Major section separation |

| `space-12` | 120px | Hero or major transition |

## 91. Radius Usage Matrix

| Token | Value | Example |

|---|---:|---|

| `radius-sm` | 6px | Small utility UI |

| `radius-md` | 10px | Inputs and compact controls |

| `radius-lg` | 16px | Default card |

| `radius-xl` | 24px | Large featured container |

| `radius-full` | 999px | Pill button, badge, compact tag |

## 92. Shadow Usage Matrix

| Token | Value | Example |

|---|---|---|

| `shadow-none` | `none` | Flat surface |

| `shadow-sm` | `0 2px 8px rgba(23, 23, 21, 0.04)` | Subtle elevation |

| `shadow-md` | `0 8px 24px rgba(23, 23, 21, 0.07)` | Default elevated card |

| `shadow-lg` | `0 16px 40px rgba(23, 23, 21, 0.10)` | Dropdown, modal, floating surface |

| `shadow-xl` | `0 24px 60px rgba(23, 23, 21, 0.12)` | Rare high-elevation surface |

## 93. Color Usage Matrix

| Role | Value | Example |

|---|---|---|

| Primary brand color | `#1A2080` | Brand identity, major actions, key emphasis, selected/active states, strong headings where appropriate. |

| Secondary brand color | `#D9C7B1` | Beauty accent, secondary emphasis, warm surface, secondary action. |

| Soft surface | `#E9DFD5` | Quiet section background, card/supporting surface, soft highlight. |

| Warm neutral | `#DFDBD4` | Subtle separation, border, divider, light supporting surface. |

| Base canvas | `#FFFFFF` | Main page background, cards, content surfaces, negative space. |

## 94. Implementation Guardrails

- No arbitrary brand colors without a documented reason.
- No arbitrary component radius without a documented reason.
- No new button variant for a single page unless it becomes a reusable semantic pattern.
- No full-width body copy when a constrained measure improves readability.
- No decorative interaction that competes with the CTA.
- No component duplication when composition can solve the need.
- No hidden critical information behind hover-only interactions.
- No mobile treatment that removes the main action or makes it difficult to tap.

## 95. Handoff Checklist

- [ ] Provide token file or CSS variables.

- [ ] Provide Figma variables for brand colors.

- [ ] Provide typography styles for Poppins.

- [ ] Provide button variants and states.

- [ ] Provide base card and treatment card components.

- [ ] Provide promo card component.

- [ ] Document Location Card as existing implementation and identify normalization work.

- [ ] Attach sample pages showing component usage.

- [ ] Validate responsive behavior at key widths.

- [ ] Record any deviations as documented exceptions.

## 96. Recommended Next Phase

The foundation is now ready for the next layer: **Page Patterns**.

Patterns should assemble the finished primitives into reusable structures such as hero, treatment listing, promo strip, location finder, and CTA sections without changing the existing information architecture.

Page patterns are intentionally not part of this V1 foundation document unless explicitly added as a next phase.

## 97. Closing Rule

When designing any new Sozo page or component, start from the system before starting from the canvas.

1. Pick the semantic content role.

2. Pick the existing component.

3. Apply the existing tokens.

4. Compose the layout using the spacing and grid system.

5. Add a documented variant only when the repeated use case justifies it.

6. Validate desktop and mobile behavior.

7. Keep the information architecture unchanged unless a separate IA exercise is explicitly requested.

## 98. Detailed Component Property Matrix

### Button

- `display`: define consistently through component tokens and documented usage.

- `alignment`: define consistently through component tokens and documented usage.

- `gap`: define consistently through component tokens and documented usage.

- `height`: define consistently through component tokens and documented usage.

- `horizontal padding`: define consistently through component tokens and documented usage.

- `radius`: define consistently through component tokens and documented usage.

- `font family`: define consistently through component tokens and documented usage.

- `font size`: define consistently through component tokens and documented usage.

- `font weight`: define consistently through component tokens and documented usage.

- `text transform`: define consistently through component tokens and documented usage.

- `icon size`: define consistently through component tokens and documented usage.

- `border`: define consistently through component tokens and documented usage.

- `background`: define consistently through component tokens and documented usage.

- `text color`: define consistently through component tokens and documented usage.

- `hover`: define consistently through component tokens and documented usage.

- `active`: define consistently through component tokens and documented usage.

- `focus`: define consistently through component tokens and documented usage.

- `disabled`: define consistently through component tokens and documented usage.

- `cursor`: define consistently through component tokens and documented usage.

- `transition`: define consistently through component tokens and documented usage.

- `link semantics`: define consistently through component tokens and documented usage.

### WhatsApp CTA

- `destination`: define consistently through component tokens and documented usage.

- `icon`: define consistently through component tokens and documented usage.

- `label`: define consistently through component tokens and documented usage.

- `height`: define consistently through component tokens and documented usage.

- `radius`: define consistently through component tokens and documented usage.

- `primary color`: define consistently through component tokens and documented usage.

- `secondary variant`: define consistently through component tokens and documented usage.

- `mobile width`: define consistently through component tokens and documented usage.

- `focus`: define consistently through component tokens and documented usage.

- `hover`: define consistently through component tokens and documented usage.

- `tracking`: define consistently through component tokens and documented usage.

- `conversion context`: define consistently through component tokens and documented usage.

### Link

- `color`: define consistently through component tokens and documented usage.

- `weight`: define consistently through component tokens and documented usage.

- `underline behavior`: define consistently through component tokens and documented usage.

- `arrow behavior`: define consistently through component tokens and documented usage.

- `hover`: define consistently through component tokens and documented usage.

- `focus`: define consistently through component tokens and documented usage.

- `visited handling`: define consistently through component tokens and documented usage.

- `semantic anchor`: define consistently through component tokens and documented usage.

### Badge

- `height`: define consistently through component tokens and documented usage.

- `padding`: define consistently through component tokens and documented usage.

- `radius`: define consistently through component tokens and documented usage.

- `font size`: define consistently through component tokens and documented usage.

- `font weight`: define consistently through component tokens and documented usage.

- `background role`: define consistently through component tokens and documented usage.

- `text role`: define consistently through component tokens and documented usage.

- `single-badge rule`: define consistently through component tokens and documented usage.

- `promo variant`: define consistently through component tokens and documented usage.

- `status variant`: define consistently through component tokens and documented usage.

### Image

- `aspect ratio`: define consistently through component tokens and documented usage.

- `object fit`: define consistently through component tokens and documented usage.

- `object position`: define consistently through component tokens and documented usage.

- `loading`: define consistently through component tokens and documented usage.

- `alt text`: define consistently through component tokens and documented usage.

- `hover scale`: define consistently through component tokens and documented usage.

- `overlay rule`: define consistently through component tokens and documented usage.

- `radius`: define consistently through component tokens and documented usage.

- `overflow`: define consistently through component tokens and documented usage.

### Base Card

- `background`: define consistently through component tokens and documented usage.

- `border`: define consistently through component tokens and documented usage.

- `radius`: define consistently through component tokens and documented usage.

- `shadow`: define consistently through component tokens and documented usage.

- `media slot`: define consistently through component tokens and documented usage.

- `content padding`: define consistently through component tokens and documented usage.

- `hover`: define consistently through component tokens and documented usage.

- `focus`: define consistently through component tokens and documented usage.

- `click target`: define consistently through component tokens and documented usage.

- `responsive mode`: define consistently through component tokens and documented usage.

### Treatment Card

- `category`: define consistently through component tokens and documented usage.

- `name`: define consistently through component tokens and documented usage.

- `description`: define consistently through component tokens and documented usage.

- `action`: define consistently through component tokens and documented usage.

- `4:3 media`: define consistently through component tokens and documented usage.

- `line clamp`: define consistently through component tokens and documented usage.

- `catalog semantics`: define consistently through component tokens and documented usage.

- `detail navigation`: define consistently through component tokens and documented usage.

### Promo Card

- `promo badge`: define consistently through component tokens and documented usage.

- `promo title`: define consistently through component tokens and documented usage.

- `supporting copy`: define consistently through component tokens and documented usage.

- `CTA`: define consistently through component tokens and documented usage.

- `accent use`: define consistently through component tokens and documented usage.

- `image ratio`: define consistently through component tokens and documented usage.

- `featured variant`: define consistently through component tokens and documented usage.

### Breadcrumb

- `ancestor labels`: define consistently through component tokens and documented usage.

- `current label`: define consistently through component tokens and documented usage.

- `separator`: define consistently through component tokens and documented usage.

- `link color`: define consistently through component tokens and documented usage.

- `current color`: define consistently through component tokens and documented usage.

- `mobile handling`: define consistently through component tokens and documented usage.

- `overflow behavior`: define consistently through component tokens and documented usage.

### Location Card

- `media`: define consistently through component tokens and documented usage.

- `region badge`: define consistently through component tokens and documented usage.

- `clinic name`: define consistently through component tokens and documented usage.

- `address`: define consistently through component tokens and documented usage.

- `WhatsApp action`: define consistently through component tokens and documented usage.

- `Maps action`: define consistently through component tokens and documented usage.

- `desktop card`: define consistently through component tokens and documented usage.

- `mobile compact row`: define consistently through component tokens and documented usage.

- `responsive visibility`: define consistently through component tokens and documented usage.

## 99. Detailed Design Review Prompts

- Does the component still read correctly if all decorative color is removed?

- Can the user identify the main action in under a glance?

- Does the primary brand color retain its identity role?

- Is Dun being used as a supporting accent rather than competing with Blue?

- Could any spacing be expressed with a token already in the system?

- Could the component reuse Base Card rather than introduce a new surface pattern?

- Would the component remain understandable with a slower network and images loading late?

- Does the mobile version preserve the same priority order?

- Can a keyboard user reach and understand the interactive elements?

- Is the component still recognizable as part of Sozo rather than a generic template?

## 100. Reference Source Notes

Primary supplied site-structure source: `SEO [Skin] Task List - Web-structure-plan.csv` in the conversation files.

Primary supplied implementation source for Location UI: `index(1).html` in the conversation files.

Brand color reference: images supplied in the conversation; basic brand colors were explicitly established as Resolution Blue, Dun, Almond, Isabelline White, and White.

Complementary color hex values should be verified against the original brand asset before production tokens are marked fully locked.

## 101. Maintainer FAQ

### Why is White the main background?
Because the chosen visual direction emphasizes clean negative space and lets the primary Blue and secondary Dun act as controlled accents.

### Why is there only one main typeface?
Poppins already provides sufficient weight and size variety for the V1 scope, reducing unnecessary complexity.

### Why is the WhatsApp CTA separate from Button?
Button is a generic action primitive; WhatsApp CTA encodes a repeated Sozo conversion destination and its copy/icon conventions.

### Why is Location Card not rebuilt?
An existing implementation already exists and should be normalized against tokens incrementally.

### Why are several cards deferred?
The current website scope does not require speculative components. They can be added when their real content and repetition justify them.

### Why are shadows subtle?
The intended personality is refined and clinical, so elevation should support hierarchy rather than dominate the visual surface.

### Why are there multiple neutrals?
White is the canvas, while Almond and Isabelline create quiet separation without introducing unrelated colors.

### Should every section use Blue?
No. Blue is the identity and primary action color, not a requirement for every background or heading.

### Should every card have a button?
No. Catalog cards often use links; conversion should be reserved for the right level of the user journey.

### Should gradients be used everywhere?
No. Gradient is a decorative tool, not a base surface system.

## 102. Production Checklist — Foundations

- [ ] Color variables exist globally.

- [ ] Brand colors are not duplicated in component files.

- [ ] Poppins is loaded once globally.

- [ ] Font weights 400, 500, 600, 700, 800 are available.

- [ ] Spacing tokens are available.

- [ ] Container width is standardized.

- [ ] Grid behavior is standardized.

- [ ] Radius tokens are available.

- [ ] Shadow tokens are available.

- [ ] Focus styles are documented.

- [ ] Semantic colors are separated from brand accents.

- [ ] Complementary colors are labeled as working values until verified.

## 103. Production Checklist — Components

- [ ] Button primary implemented

- [ ] Button secondary implemented

- [ ] Button outline implemented

- [ ] WhatsApp CTA implemented

- [ ] Link style implemented

- [ ] Badge style implemented

- [ ] Image wrapper implemented

- [ ] Base Card implemented

- [ ] Treatment Card implemented

- [ ] Promo Card implemented

- [ ] Breadcrumb implemented

- [ ] Location Card mapped to existing code

- [ ] Deferred card backlog recorded

## 104. Production Checklist — Responsive

- [ ] Desktop container validated

- [ ] Tablet spacing validated

- [ ] Mobile horizontal padding validated

- [ ] Treatment card mobile validated

- [ ] Promo card mobile validated

- [ ] Location card compact behavior validated

- [ ] CTA tap target validated

- [ ] Typography scale validated

- [ ] No horizontal overflow

- [ ] No text clipping in critical actions

## 105. Production Checklist — Visual QA

- [ ] White remains dominant where intended

- [ ] Resolution Blue remains visually consistent

- [ ] Dun accents are restrained

- [ ] Almond surfaces remain subtle

- [ ] Isabelline is not overused as a background

- [ ] Shadows are not excessive

- [ ] Card radius is consistent

- [ ] Button radius is consistent

- [ ] Image ratios are consistent

- [ ] Typography hierarchy is obvious

- [ ] Motion is subtle

## 106. Token Rationale — Color

### Resolution Blue
The main visual anchor. Use when the UI needs to say “this is Sozo” or “this is the primary action.”

### Dun
The warm beauty counterweight to Blue. Use where softness or premium warmth is helpful.

### Almond
A very soft surface that supports warmth without becoming a competing brand color.

### Isabelline White
A quiet neutral that helps separate surfaces and can act as a border color.

### White
The default canvas and the main source of visual breathing room.

## 107. Token Rationale — Typography

### Display XL
`64px` / `700` / `1.05` — Large hero heading.

### Display LG
`56px` / `700` / `1.10` — Large hero or campaign heading.

### H1
`48px` / `700` / `1.15` — Page heading.

### H2
`40px` / `700` / `1.20` — Section heading.

### H3
`32px` / `600` / `1.25` — Card or subsection heading.

### H4
`24px` / `600` / `1.30` — Smaller section heading.

### H5
`20px` / `600` / `1.35` — Compact heading.

### Body Large
`18px` / `400` / `1.65` — Lead or introduction.

### Body
`16px` / `400` / `1.60` — Default reading text.

### Body Small
`14px` / `400` / `1.55` — Supporting copy.

### Caption
`12px` / `500` / `1.40` — Metadata.

### Label
`12px` / `600` / `1.40` — Badge and compact UI.

## 108. Token Rationale — Spacing

### space-1
`4px` — Tiny icon or internal micro spacing.

### space-2
`8px` — Label/icon gap, compact content.

### space-3
`12px` — Label to text, small groups.

### space-4
`16px` — Default element gap.

### space-5
`24px` — Card content and component gap.

### space-6
`32px` — Content grouping.

### space-7
`40px` — Larger grouping.

### space-8
`48px` — Section internal spacing.

### space-9
`64px` — Small section separation.

### space-10
`80px` — Normal section separation.

### space-11
`96px` — Major section separation.

### space-12
`120px` — Hero or major transition.

## 109. Token Rationale — Radius

### radius-sm
`6px` — Small utility UI.

### radius-md
`10px` — Inputs and compact controls.

### radius-lg
`16px` — Default card.

### radius-xl
`24px` — Large featured container.

### radius-full
`999px` — Pill button, badge, compact tag.

## 110. Token Rationale — Shadow

### shadow-none
`none` — Flat surface.

### shadow-sm
`0 2px 8px rgba(23, 23, 21, 0.04)` — Subtle elevation.

### shadow-md
`0 8px 24px rgba(23, 23, 21, 0.07)` — Default elevated card.

### shadow-lg
`0 16px 40px rgba(23, 23, 21, 0.10)` — Dropdown, modal, floating surface.

### shadow-xl
`0 24px 60px rgba(23, 23, 21, 0.12)` — Rare high-elevation surface.

## 111. Final Reference

**SOZO** = Modern + Clinical + Premium + Clean + Refined

**Canvas** = White

**Identity** = Resolution Blue

**Beauty accent** = Dun

**Soft surfaces** = Almond + Isabelline

**Typography** = Poppins

**Primary conversion** = WhatsApp CTA

**Core catalog unit** = Treatment Card

**Promo entry unit** = Promo Card

**Existing location pattern** = Location Card

**Principle** = reuse, restraint, consistency

## 112. Exception Log Template

| ID | Component | Exception | Reason | Scope | Review date |

|---|---|---|---|---|---|

| EX-01 | — | — | — | — | — |

| EX-02 | — | — | — | — | — |

| EX-03 | — | — | — | — | — |

| EX-04 | — | — | — | — | — |

| EX-05 | — | — | — | — | — |

| EX-06 | — | — | — | — | — |

| EX-07 | — | — | — | — | — |

| EX-08 | — | — | — | — | — |

| EX-09 | — | — | — | — | — |

| EX-10 | — | — | — | — | — |

| EX-11 | — | — | — | — | — |

| EX-12 | — | — | — | — | — |

| EX-13 | — | — | — | — | — |

| EX-14 | — | — | — | — | — |

| EX-15 | — | — | — | — | — |

## 113. Decision Log Template

| Date | Decision | Rationale | Owner |

|---|---|---|---|

| — | DEC-01 | — | — |

| — | DEC-02 | — | — |

| — | DEC-03 | — | — |

| — | DEC-04 | — | — |

| — | DEC-05 | — | — |

| — | DEC-06 | — | — |

| — | DEC-07 | — | — |

| — | DEC-08 | — | — |

| — | DEC-09 | — | — |

| — | DEC-10 | — | — |

| — | DEC-11 | — | — |

| — | DEC-12 | — | — |

| — | DEC-13 | — | — |

| — | DEC-14 | — | — |

| — | DEC-15 | — | — |

## 114. Future Pattern Placeholder

### Hero
- Status: Future pattern.
- Do not implement as part of V1 foundation unless explicitly approved.

### Treatment Listing
- Status: Future pattern.
- Do not implement as part of V1 foundation unless explicitly approved.

### Treatment Detail
- Status: Future pattern.
- Do not implement as part of V1 foundation unless explicitly approved.

### Promo Listing
- Status: Future pattern.
- Do not implement as part of V1 foundation unless explicitly approved.

### Promo Detail
- Status: Future pattern.
- Do not implement as part of V1 foundation unless explicitly approved.

### Location Finder
- Status: Future pattern.
- Do not implement as part of V1 foundation unless explicitly approved.

### Location Detail
- Status: Future pattern.
- Do not implement as part of V1 foundation unless explicitly approved.

### Doctor Listing
- Status: Future pattern.
- Do not implement as part of V1 foundation unless explicitly approved.

### Doctor Detail
- Status: Future pattern.
- Do not implement as part of V1 foundation unless explicitly approved.

### Product Listing
- Status: Future pattern.
- Do not implement as part of V1 foundation unless explicitly approved.

### Product Detail
- Status: Future pattern.
- Do not implement as part of V1 foundation unless explicitly approved.

### Blog Listing
- Status: Future pattern.
- Do not implement as part of V1 foundation unless explicitly approved.

### Blog Detail
- Status: Future pattern.
- Do not implement as part of V1 foundation unless explicitly approved.

### Testimonial Section
- Status: Future pattern.
- Do not implement as part of V1 foundation unless explicitly approved.

### Global CTA Section
- Status: Future pattern.
- Do not implement as part of V1 foundation unless explicitly approved.

## 115. Final Status

**V1 Foundation: complete for the agreed scope.**

The document is intentionally detailed so it can act as a practical handoff artifact for design and frontend work.

Do not interpret deferred sections as missing requirements; they are deliberately postponed.
