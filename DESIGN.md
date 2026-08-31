---
name: "Direpair"
description: "Service Line Atlas: a traceable repair interchange built from warm service paper, route geometry, and honest operational tickets."
colors:
  route-navy: "#011D38"
  route-red: "#CC3110"
  route-green: "#0B6F5D"
  route-blue: "#064395"
  route-yellow: "#F4C444"
  paper: "#F8F1E2"
  paper-deep: "#EFE4CE"
  surface: "#FFFAF0"
  ink-soft: "#314B60"
  muted: "#556B7B"
  line: "#9DADB5"
  red-deep: "#9F250B"
  green-deep: "#075144"
typography:
  display:
    fontFamily: "Archivo, Arial, ui-sans-serif, sans-serif"
    fontSize: "clamp(3rem, 6vw, 6rem)"
    fontWeight: 790
    lineHeight: 1.02
    letterSpacing: "-0.035em"
  headline:
    fontFamily: "Archivo, Arial, ui-sans-serif, sans-serif"
    fontSize: "clamp(2.25rem, 4.3vw, 4.6rem)"
    fontWeight: 790
    lineHeight: 1.02
    letterSpacing: "-0.035em"
  title:
    fontFamily: "Archivo, Arial, ui-sans-serif, sans-serif"
    fontSize: "1.28rem"
    fontWeight: 790
    lineHeight: 1.05
    letterSpacing: "-0.01em"
  body:
    fontFamily: "Archivo, Arial, ui-sans-serif, sans-serif"
    fontSize: "1rem"
    fontWeight: 400
    lineHeight: 1.62
    letterSpacing: "normal"
  label:
    fontFamily: "Azeret Mono, ui-monospace, monospace"
    fontSize: "0.68rem"
    fontWeight: 740
    lineHeight: 1.2
    letterSpacing: "0.02em"
rounded:
  square: "0px"
  route-code: "4px"
  compact: "5px"
  control: "7px"
  action: "8px"
  surface: "12px"
  station: "50%"
spacing:
  xxs: "4px"
  xs: "8px"
  sm: "12px"
  md: "16px"
  lg: "22px"
  xl: "28px"
  xxl: "42px"
  section-sm: "62px"
  section: "108px"
components:
  button-primary:
    backgroundColor: "{colors.route-yellow}"
    textColor: "{colors.route-navy}"
    rounded: "{rounded.action}"
    padding: "0 20px"
    height: "50px"
  button-primary-hover:
    backgroundColor: "#FFD45A"
    textColor: "{colors.route-navy}"
    rounded: "{rounded.action}"
    padding: "0 20px"
    height: "50px"
  button-secondary:
    backgroundColor: "transparent"
    textColor: "{colors.route-navy}"
    rounded: "{rounded.action}"
    padding: "0 20px"
    height: "50px"
  button-secondary-hover:
    backgroundColor: "{colors.route-navy}"
    textColor: "{colors.paper}"
    rounded: "{rounded.action}"
    padding: "0 20px"
    height: "50px"
  button-operate:
    backgroundColor: "{colors.route-yellow}"
    textColor: "{colors.route-navy}"
    rounded: "{rounded.square}"
    padding: "9px 15px"
    height: "40px"
  field-public:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.route-navy}"
    rounded: "{rounded.control}"
    padding: "12px 13px"
  field-operate:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.route-navy}"
    rounded: "{rounded.square}"
    padding: "10px 11px"
  card-public:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.route-navy}"
    rounded: "{rounded.surface}"
    padding: "22px"
  panel-operate:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.route-navy}"
    rounded: "{rounded.square}"
    padding: "22px"
  service-ticket:
    backgroundColor: "{colors.route-yellow}"
    textColor: "{colors.route-navy}"
    rounded: "{rounded.square}"
    padding: "25px 30px 24px"
  route-navigation:
    backgroundColor: "{colors.paper}"
    textColor: "{colors.route-navy}"
    rounded: "{rounded.surface}"
    height: "72px"
  route-code:
    backgroundColor: "transparent"
    textColor: "{colors.route-red}"
    typography: "{typography.label}"
    rounded: "{rounded.compact}"
    padding: "5px 9px"
---

# Design System: Direpair

## Overview

**Creative North Star: "Service Line Atlas / Repair Interchange"**

Direpair turns the repair workflow itself into the visual material. Warm service paper carries flat enamel route colors, station rings, maintenance codes, and punched tickets; three device lines converge on diagnosis before one transparent route proceeds to quotation, repair, and completion. The result is competent and precise without becoming clinical or intimidating.

The same world changes density by job. Astro Persuade surfaces use the interchange as a memorable first-view composition; Astro Read surfaces reduce it to landmarks and disciplined text measure; Laravel Operations and the WordPress editorial control plane become flatter, squarer maintenance boards optimized for scanning and action. Brand recognition comes from route grammar, type, color, borders, and ticket language—not from repeating a marketing layout inside tools.

The approved Repair Interchange comp and the final 1586 × 992 reproduction are the canonical homepage expression. The shipped correction keeps outbound labels clear of the main line and limits route drawing to paths, leaving station circles solid. The finish review passed the direction contract with `DISPOSITION: ship`.

**Key Characteristics:**

- Warm, lightly textured service paper instead of sterile white or dark laboratory chrome.
- Flat red, green, and blue feeder routes that resolve into a single navy service path.
- Oversized Archivo headlines paired with compact Azeret Mono codes, states, dates, and money.
- Punched yellow tickets reserved for consequential entry, quotation, and decision moments.
- Thin structural borders, station rings, and table rules instead of decorative card stacks.
- One visual world expressed as Persuade/Read on Astro and Operate/editorial in Laravel and WordPress.

## Colors

The palette reads like printed transit and maintenance signage on warm stock: navy supplies structure, branch colors identify paths, and yellow marks the ticket or decision surface.

### Primary

- **Route Navy:** The wordmark, primary ink, boxed navigation, hub rings, outbound service path, footer, table headers, and operational structure. It is the dominant dark field, never a decorative navy gradient.

### Secondary

- **Signal Red:** Camera route, entry and current-state emphasis, route codes, focus outlines, scroll thumb, and high-attention hover states.
- **Service Green:** Console route, successful or verified state, timeline progress, and calm operational continuity.
- **Instrument Blue:** Audio route, links, and field-focus borders; it communicates navigation or focus rather than generic brand decoration.
- **Deep Red and Deep Green:** Reserved darker companions for hover or contrast corrections inside their own semantic families.

### Tertiary

- **Ticket Yellow:** The single high-salience action material: service tickets, active step cells, selected service methods, quotation surfaces, and small verified highlights. It is not a general section background.

### Neutral

- **Service Paper:** The textured page field and the interior of route stations.
- **Deep Paper:** Section contrast, table/filter bands, and the edge color around route markers.
- **Clean Paper Surface:** Inputs and bordered records that need a quieter reading plane above the textured page.
- **Soft Ink and Muted Ink:** Supporting copy and metadata; never substitutes for navy on essential labels or actions.
- **Service Line:** Low-priority dividers and input boundaries when a full navy rule would be too dominant.

**The Route Colors Are Semantic Rule.** Red, green, and blue identify feeder routes or explicit states; do not scatter them as interchangeable decoration.

**The One Yellow Ticket Rule.** Yellow earns attention by marking the primary ticket or a real decision state; keep it scarce enough to remain the obvious next action.

## Typography

**Display Font:** Archivo with Arial and sans-serif fallbacks  
**Body Font:** Archivo with Arial and sans-serif fallbacks  
**Label/Mono Font:** Azeret Mono with ui-monospace and monospace fallbacks

**Character:** Archivo provides the dense, legible silhouette of service signage without retro pastiche. Azeret Mono supplies route codes and operational precision; its smaller size and wider tracking create a distinct information layer rather than a second headline voice. Both variable fonts are self-hosted with `font-display: swap`.

### Hierarchy

- **Display:** Heavy variable weight for page titles; global headings use the display token, while the homepage and booking hero tighten to `0.96` or `0.88` line-height and up to `-0.04em` tracking for the approved silhouette.
- **Headline:** Heavy section hierarchy, balanced wrapping, and a maximum width near `22ch`; it should establish a reading landmark rather than label every panel.
- **Title:** Compact record, card, and timeline headings. Operational screens may reduce this to `1rem`–`1.45rem` to increase scan density.
- **Body:** Calm sentence case with `1.62` public line-height. Reading copy stays near `65–75ch`; lead copy is typically capped at `68ch` and policy prose at `72ch`.
- **Label:** Azeret Mono at approximately `0.64rem`–`0.72rem`, weight `700`–`800`, and `0.02em`–`0.13em` tracking. Use uppercase for route codes, states, table headers, dates, and money; use tabular numerals for financial columns.

**The Two-Face Typography Rule.** Archivo carries human explanation and hierarchy; Azeret Mono carries system identity and machine-like state. Never set long prose or large display copy in the mono face.

## Layout

The public shell uses a centered `1180px` container with `40px` total horizontal inset and a `760px` narrow reading measure. Default sections use `108px` vertical spacing and compact sections use `62px`; at `820px` and below the main section rhythm compresses to `76px`. Full-width sections alternate paper, deep-paper, navy, and yellow ticket planes while preserving one continuous route vocabulary.

The homepage hero is the signature exception: its shell reaches `1500px` with `48px` total inset and a minimum height of `calc(100svh - 112px)`. The intro grid holds an approximately `860px` headline column and a `410px` explanation column. The authored SVG uses a `1400 × 470` coordinate system: feeder stations begin at `x=152` on `y=80`, `215`, and `350`; they meet hub ports at `x=690`; the diagnosis ring is centered at `790,212` with `102` and `58` radii; the navy outbound line runs from `x=895` to `1372` through stations at `1015`, `1170`, and `1324`. The yellow service ticket overlaps the hub/outbound field at the lower right; it is a consequence of the route, not an unrelated floating card.

**The Converge, Then Proceed Rule.** Every complete repair-route diagram must show varied inputs converging on diagnosis before quotation, repair, and completion continue as one ordered path.

### Responsive transformations

- At `1120px`, boxed navigation tightens its cell padding; at `900px`, it becomes a menu button plus a bordered vertical route menu while preserving the status re-entry link.
- At `1080px`, the hero intro becomes one column, the route canvas deepens, and the ticket narrows; the convergence story remains intact.
- At `820px`, common two- and three-column content, forms, service detail layouts, and status/quotation layouts stack. Sticky explanatory asides become static when the workflow needs full width.
- At `760px`, the decorative desktop SVG is replaced by a semantic vertical service spine: device stations, diagnosis, quotation, repair, completion, the ticket, then status re-entry. Content is reordered by normal document flow, not hidden to rescue the composition.
- At `700px` and below, process and support routes become single-column records; at `520px`–`540px`, action rows stack and public buttons become full width.
- Laravel uses a `1240px` work surface with `36px` total inset. At `800px`, work grids and quotation rows stack, dense tables scroll horizontally, and the decorative header route is removed.
- WordPress retains core admin breakpoints. Direpair metadata grids and the custom settings header stack at the native `782px` admin threshold; the settings surface is capped at `1120px`.
- Every surface has a `320px` minimum contract and must remain usable at `200%` zoom without horizontal page overflow.

## Elevation & Depth

The system is flat by default. Ordinary public cards, operational panels, WordPress tables, postboxes, and navigation structure rely on paper tone, one- or two-pixel rules, and overlap rather than ambient elevation. Soft navy shadows appear only where an element is materially above the route: public primary buttons (`0 10px 24px rgba(1,29,56,.12)`), forms and status summaries (`0 18px 42px rgba(1,29,56,.14)`), and the foreground service ticket (`0 18px 38px rgba(1,29,56,.20)`). Ticket hover may lift to `0 24px 48px rgba(1,29,56,.24)`.

Station-ring `box-shadow` outlines are geometry, not elevation. They build concentric route markers and must remain optically flat.

**The Flat-by-Default Rule.** Use borders, tonal paper changes, and route overlap first; reserve soft shadows for true foreground tickets, forms, and decision surfaces.

## Shapes

Public actions and page-level surfaces use compact curves: `7px` controls, `8px` buttons, and `12px` cards/navigation. Route labels use small `4px`–`5px` corners, while station dots and transfer rings remain true circles. The Operate language in Laravel and WordPress deliberately squares panels, inputs, buttons, badges, and tables to make maintenance work feel exact and dense.

Tickets use clipped notches rather than oversized rounding. The desktop service ticket cuts opposing edge bites around the middle and lower edge; quotation bands reuse the same punched-stock silhouette at larger scale. Dashed rules represent perforation or ticket separation, never generic decoration.

**The Punched Ticket Rule.** A yellow action or quotation surface may use clipped edge bites and dashed perforation; ordinary cards must not imitate the ticket silhouette.

## Components

### Component inventory

| Pattern | Astro Persuade/Read | Laravel Operate | WordPress Operate/editorial |
|---|---|---|---|
| Navigation | Sticky boxed route bar; status is the terminal cell; bordered mobile menu | Sticky work header with brand, live route spine, and session action | Native admin bar/menu recolored; current section is red, submenus stay navy |
| Primary action | Yellow, navy border, compact radius, soft lift | Yellow, square, no shadow; red hover | Native primary/button actions reskinned yellow and square |
| Secondary action | Transparent paper, navy border; navy fill on hover | Paper, square; red hover shared with primary | Native secondary action remains semantically native and square |
| Field | Clean-paper fill, `7px` corner, blue focus halo | Clean-paper fill, square navy rule, blue focus halo | Native controls preserved, squared, and given the same focus halo |
| Card/panel | Paper surface, `12px` corner where appropriate; most registry rows are ruled, not carded | Flat square work-ticket panel | Flat native postbox/list table with stronger grouping |
| Code/tag/badge | Mono route code or outlined semantic tag | Square mono status badge and tabular money | Mono table/navigation metadata while preserving native labels |
| Signature | Repair Interchange, service ticket, route registry, public timeline | Work queue, route control, quotation item matrix, audit timeline | Content registry, two-column metadata fields, public/private settings route |

**The Surface-Mode Rule.** Share palette, type, route codes, borders, and state language across applications, but keep public actions compactly rounded and operating surfaces square and dense.

### Navigation

The desktop public navigation is a `72px` boxed route with a navy `2px` enclosure and `12px` corners. The wordmark occupies its own cell; links are separated by fine vertical rules, invert to navy on hover, and end with the heartbeat/status terminal. The mobile menu uses a real button with `aria-expanded` and `aria-controls`; its dropdown retains the enclosure and at least `52px` row height. Laravel and WordPress retain sticky/native navigation semantics rather than copying the public hero bar.

### Buttons and links

Public primary buttons are yellow with a navy `2px` border, minimum `50px` height, `8px` corner, bold Archivo text, and a restrained three-pixel hover lift. Secondary buttons invert from transparent paper to navy. Dark buttons are reserved for high-contrast actions inside yellow or success surfaces. Operate buttons are at least `40px` high, square, shadowless, and move only one pixel on hover. Text links stay underlined or gain an explicit color change; navigation links may remove underlines because their cell boundary supplies the affordance.

### Route stations and tickets

Route stations combine an outlined pictogram, a human label, a mono code, a colored line, and a paper-filled circular marker. The diagnosis hub is the only oversized transfer ring. Outbound states use one navy line and keep icon/label groups visibly above it; station circles must never inherit the route path dash animation. The service ticket is the singular public CTA and contains task language—device, symptom, initial inspection—not fabricated customer data.

### Cards, records, and tables

Prefer ruled registries and sequential rows over same-size icon-card grids. Public service records use image or code-native device art, route code, title, truthful excerpt, price orientation, and one route-arrow action. Laravel and WordPress tables use navy headers with mono uppercase labels, one-pixel row rules, yellow-tinted row hover, and horizontal overflow on narrow screens. Empty records state what is unavailable and why; they do not invent content to fill space.

### Inputs and forms

Every control has a visible label. Public fields use a clean-paper fill, neutral rule, compact curve, and blue `3px` focus halo; Laravel and WordPress use the same focus behavior on square fields. Checkbox/radio accents are red on public pages and green in operating tools. File controls name accepted types and limits. Disabled controls reduce opacity and show a not-allowed cursor.

Booking is a three-station form: Device, Service, Contact. Validate before advancing; selected method rows receive a yellow tint; pickup/home-service reveals address fields with a dashed blue enclosure. Keep privacy consent explicit, place errors/success in a live status region, disable submission while pending, and explain recovery in plain Indonesian. Status lookup treats the token as a private key, and quotation approval requires explicit terms acknowledgement.

### Status, timeline, and feedback

Timelines are vertical green routes with a red current station. Status badges are outlined mono labels, never ambiguous color-only dots. Success uses a calm green wash, error uses a red wash, and warnings or demo notices use yellow with direct language. Private status pages expose request-safe state, device summary, timeline, quotation, and payment status while omitting contact data, address, full symptom, internal notes, and attachments.

### Motion and state change

The homepage has one authored arrival: feeder paths draw toward diagnosis over `1.35s` with the expressive route easing, the green and blue branches stagger, the main path follows after `0.72s`, and the hub resolves after the feeder routes. Content and completed geometry exist before animation. Hover/focus motion is limited to a small lift, line inversion, or station emphasis; there are no unrelated entrance cascades.

**The Static-First Motion Rule.** When `prefers-reduced-motion: reduce` is active, show the complete route immediately and reduce all animation and transition durations to effectively zero.

### Accessibility

Use semantic landmarks, ordered lists for stages, details/summary for FAQs, tables for tabular operations, and associated labels for every control. Global keyboard focus is a red `3px` outline with offset; component focus halos supplement rather than erase it. Maintain visible skip navigation, descriptive link names, decorative SVGs marked `aria-hidden`, meaningful image alt text, adequate touch targets, high-contrast status copy, and no information conveyed by color alone.

**The Keyboard Route Rule.** Every route action, form step, disclosure, table link, and admin control must remain reachable and visibly focused without a pointer.

### CMS and operations boundaries

Astro consumes public services, price ranges, technician media, locations, FAQ, warranties, policies, and consented repair cases from WordPress. It may show clearly labeled fixtures in local/staging, but production trust content must pass verification and consent gates. Booking posts to Laravel; private repair data, diagnosis, versioned quotations, invoices, payments, timeline, and audit history stay in Laravel.

WordPress is reskinned through the Direpair plugin while retaining native admin affordances, capabilities, editor semantics, update compatibility, notices, tables, and form structure. The custom settings page visually separates public website settings from the private publish connection. Laravel prioritizes dense work queues, allowed state transitions, visible audit history, and tabular Rupiah values; decorative marketing composition must not interrupt those tasks.

**The Honest Content Rule.** Never hard-code or visually imply a technician identity, workshop photo, rating, address, price, warranty, repair case, or trust claim that the client has not verified and published.

**The Native Admin Rule.** Brand WordPress through scoped color, type, rules, and grouping while preserving native controls, permissions, notices, editor behavior, and plugin compatibility.

## Do's and Don'ts

### Do:

- **Do** use the diagnosis-to-quotation workflow as the visual proof of the service.
- **Do** preserve the ordered route from symptom or device intake through diagnosis, quotation, repair, and completion.
- **Do** keep editable public content in WordPress and private customer/repair data in Laravel.
- **Do** label fixture content as demo and leave honest replacement states for missing client assets.
- **Do** keep public reading measures near `65–75ch`, operating tables scannable, and money tabular.
- **Do** verify the full static experience at reduced motion, keyboard focus, `320px`, and `200%` zoom.

### Don't:

- **Don't** use gradients, glass panels, decorative blur, neon repair-lab styling, or dark sci-fi chrome.
- **Don't** use emoji, icon fonts, or generic stock repair photography as a substitute for authored SVG linework or verified client media.
- **Don't** scaffold pages from same-size icon cards, nested cards, excessive pills, or floating rounded containers.
- **Don't** animate station circles with route-path dashes or let lines cross stage labels and controls.
- **Don't** make yellow a generic background or add multiple competing primary tickets to one decision scene.
- **Don't** move booking, quotation, payment, customer PII, or operational state into WordPress or public decorative content.
- **Don't** fabricate urgency, reviews, technician credentials, locations, prices, cases, or warranty claims.
