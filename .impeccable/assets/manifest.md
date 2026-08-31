# Direpair replacement-world raster manifest

## Produced asset

| Asset | Dimensions | Format / alpha | Tiling intent | Approved reference |
|---|---:|---|---|---|
| `service-paper-texture.png` | 1024 × 1024 px | PNG, opaque RGB; no alpha channel required | Seamless repeat on both axes beneath semantic UI. Opposite edge pixels are matched; the field stays low-contrast and averages near the approved warm paper ground around `#F8F1E2`. | `.impeccable/mocks/service-line-atlas-interchange.png` |

The texture is the warm service-ticket paper material from the approved Service Line Atlas interchange comp. Its natural fibrous grain, slight toner wear, and microscopic speckle are intentionally quiet enough to sit behind accessible interface content. The exact built-in ImageGen prompt is embedded in the PNG.

## Shipping copy targets

Copy the same generated PNG, unchanged and under the same filename, to:

- `apps/web/public/media/service-paper-texture.png`
- `apps/api/public/media/service-paper-texture.png`
- `apps/wordpress-plugin/direpair-content/assets/media/service-paper-texture.png`

## Raster scope decision

No other raster is needed for this replacement-world build. Route paths, station rings, device and workflow icons, diagnosis geometry, ticket shape, labels, controls, and interaction states remain semantic SVG/HTML/CSS so they scale, animate, respond, and remain accessible.
