# Direpair UI Redesign — Service Line Atlas

## Direction contract

THESIS: Direpair turns varied devices into one traceable repair interchange and refuses the standard split hero plus card grid.

OWN-WORLD: Warm service paper, flat route colors, enamel linework, station rings and codes, punched repair tickets, compact corners, and clear grotesk typography remain recognizable without content.

STORY: Visitors see different devices converge on diagnosis, understand that quotation precedes repair, then start diagnosis or re-enter a private status route.

FIRST VIEWPORT: A boxed route navigation sits above an oversized two-line headline. Camera, console, and audio branches enter a central diagnosis ring; quotation, repair, and finish exit right; a yellow ticket CTA overlaps the hub.

FORM: Service Line Atlas, grounded model pick; Repair Interchange composition; seed `22704d32`.

FINISH: unreviewed and undocumented is unfinished; this build ends with the finish review, the verdict, DESIGN.md, and every shipping raster carrying its provenance

## Surface modes

| Surface | Mode | Translation |
|---|---|---|
| Astro homepage and conversion pages | Persuade | Route map dominates the first viewport; the ticket is the primary action. |
| Astro guides, service pages, policies | Read | Route labels become reading landmarks; text measure stays within 65–75 characters. |
| Booking and private status | Operate | Steps become stations, decisions become route switches, and error/success states name recovery. |
| Laravel Operations | Operate | Dense request tables and forms use a maintenance-board grammar with stable status routes. |
| WordPress | Operate/editorial | Core affordances remain native; Direpair adds route navigation, stronger grouping, and a quieter editorial shell. |

## Responsive contract

- Desktop keeps the three incoming device branches, central hub, outgoing stages, and overlapping ticket in one viewport.
- Tablet reduces branch annotation density but preserves the convergence story and primary ticket.
- Mobile becomes a vertical service route: devices, diagnosis hub, quotation, repair, completion, then the ticket action. Nothing is hidden only to rescue the layout.
- The system supports 320 px and wider, keyboard navigation, 200% zoom, and reduced motion.

## Motion and interaction

- One authored moment: route segments activate toward the diagnosis hub, then continue to completion as the hero enters view.
- Content is visible before animation. `prefers-reduced-motion` receives the complete static route.
- Hover and focus states move station markers or underline route segments; they do not scatter unrelated entrance effects.
- Loading, empty, success, error, disabled, and privacy-sensitive states use explicit language and the same route/status grammar.

## Implementation inventory

| Ingredient | Commitment | Medium |
|---|---|---|
| Boxed route navigation | Thin navy enclosure, spaced links, status re-entry at the terminal | Semantic HTML and CSS |
| Display headline | Two-line dominant grotesk silhouette, no eyebrow label | Self-hosted variable font |
| Device branches | Three colored paths with compact device pictograms and route codes | Authored SVG plus semantic labels |
| Diagnosis hub | Oversized transfer ring and diagnostic pulse | SVG and CSS |
| Outgoing stages | Quotation, repair, and completion share one navy route | HTML, CSS, and SVG icons |
| Repair ticket CTA | Yellow punched ticket overlaps the interchange and remains the only primary action | Semantic link and CSS mask/clip geometry |
| Paper material | Warm low-contrast service-ticket grain across public and admin surfaces | Generated raster texture with embedded prompt |
| Browser surfaces | Selection, focus rings, scrollbar, caret, tabular numerals | CSS tokens |

## Anti-patterns and content rules

- No gradients, glass panels, decorative blur, neon repair-lab styling, emoji icons, fake photos, testimonials, review counts, or fabricated trust claims.
- No same-size icon-card grid as page scaffolding and no nested cards.
- Pricing, technician images, locations, policies, cases, and contact details remain dynamic in WordPress.
- Operational PII remains in Laravel and never becomes decorative demo content on public pages.

## Acceptance criteria

- Astro, Laravel, and WordPress share the same route vocabulary while matching their task modes.
- The Astro first viewport reproduces the approved comp at 1586×992 before later sections are polished.
- All existing booking, status, quotation, payment, CMS, and operations behavior continues working.
- Desktop and mobile screenshots pass two bounded QA rounds, the design detector, the finish review, and production builds.
