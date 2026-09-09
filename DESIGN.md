# Lake Tahoe Travel Design Guide

- Status: initial implementation reference
- Last verified against Figma: 2026-09-08
- Design source: [Lake Tahoe Travel — New Site Design — Dev Access](https://www.figma.com/design/ojSvaH9s0cyWEnxWdk3ksH/DevOps-090726Copy-LAKE-TAHOE-TRAVEL---NEW-SITE-DESIGN---DEV-ACCESS--?node-id=0-1&m=dev)

## Purpose and authority

This document translates the Figma file into implementation guidance for the WordPress theme. Read it before changing a public-facing page, component, pattern, design token, or interaction.

- Figma remains the visual and behavioral source material.
- This file is the implementation-facing source of truth for values already verified in Figma.
- Do not invent colors, typography, spacing, radii, shadows, or interaction behavior. Use the approved Bootstrap-based breakpoint scale below.
- If Figma and this document disagree, stop and record the discrepancy before changing production tokens.
- Values marked **provisional** or **open** are not approved implementation decisions.
- Public-facing copy is in English. Editable editorial and marketing copy belongs in WordPress, not in PHP templates.

The Figma file currently has no local variables, paint styles, text styles, effect styles, or grid styles. Its values are embedded directly in layers and component examples. The values below are therefore a documented snapshot, not an automatically synchronized token library. Reinspect the relevant Figma node before implementing a major component.

## Figma map

| Page | Node | Purpose |
| --- | --- | --- |
| Homepage | `0:1` | Desktop and mobile homepage compositions |
| Website Components | `1:2` | Full component and module inventory |
| Style Sheet | `1:3` | Color, typography, logos, controls, cards, and interaction states |
| Components — With Notes for Dev | `2145:12745` | Implementation behavior, content limits, integrations, and open questions |

Important Style Sheet sections:

- [Color](https://www.figma.com/design/ojSvaH9s0cyWEnxWdk3ksH/?node-id=1-43426&m=dev)
- [Logos](https://www.figma.com/design/ojSvaH9s0cyWEnxWdk3ksH/?node-id=1-43803&m=dev)
- [Design concept](https://www.figma.com/design/ojSvaH9s0cyWEnxWdk3ksH/?node-id=1-43915&m=dev)
- [Typography on light backgrounds](https://www.figma.com/design/ojSvaH9s0cyWEnxWdk3ksH/?node-id=1-43952&m=dev)
- [Typography on dark backgrounds](https://www.figma.com/design/ojSvaH9s0cyWEnxWdk3ksH/?node-id=1-44068&m=dev)
- [Typography hover states](https://www.figma.com/design/ojSvaH9s0cyWEnxWdk3ksH/?node-id=1-44196&m=dev)
- [Buttons and controls](https://www.figma.com/design/ojSvaH9s0cyWEnxWdk3ksH/?node-id=1-44327&m=dev)
- [Accordions](https://www.figma.com/design/ojSvaH9s0cyWEnxWdk3ksH/?node-id=1-44872&m=dev)
- [Story cards](https://www.figma.com/design/ojSvaH9s0cyWEnxWdk3ksH/?node-id=1-44918&m=dev)
- [Inspiration panels](https://www.figma.com/design/ojSvaH9s0cyWEnxWdk3ksH/?node-id=1-45073&m=dev)
- [Inspiration panels — mobile](https://www.figma.com/design/ojSvaH9s0cyWEnxWdk3ksH/?node-id=1-45113&m=dev)

## Visual direction

The experience is image-led, editorial, and strongly associated with Lake Tahoe's landscape.

- Use immersive destination photography and generous full-bleed image areas.
- Deep navy is the visual anchor. Peak gold and clear cyan are restrained accents.
- Large Big Caslon display text supplies the editorial character; Gotham HTF handles interface and body copy.
- Translucent, blurred “looking glass” surfaces may reveal or magnify imagery while maintaining readable foreground content.
- Components should feel calm and spacious. Avoid decoration that competes with the photography.
- Motion should clarify state and hierarchy rather than operate as spectacle.

## Color system

### Brand palettes

| Token | Base | Tint 50 | Tint 100 | Tint 200 | Tint 300 | Shade 100 | Shade 200 | Shade 300 |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Primary — Deep, Deep Navy | `#073959` | `#C7D1D8` | `#8AB5C5` | `#87A8B8` | `#85A2B2` | `#06304D` | `#042742` | `#002A5D` |
| Secondary — Peak, Peak Gold | `#EEB040` | `#F8DFB3` | `#F5CD73` | `#F1BE59` | `#F0B74D` | `#EBA536` | `#E89A2D` | `#E1841A` |
| Tertiary — Crystal Clear Cyan | `#61C2B7` | `#C0E7E2` | `#91D9D1` | `#79CDC4` | `#6DC8BE` | `#55B9AD` | `#49AFA2` | `#319D8E` |

Use semantic token names in code. Palette-step names describe source values; they are not a license to select a color by appearance alone.

### System palettes — provisional

The Figma sheet explicitly questions whether system colors will be needed. Do not publish these as approved global tokens until error, warning, and success patterns are confirmed.

| Token | Base | Tint 50 | Tint 100 | Tint 200 | Tint 300 | Shade 100 | Shade 200 | Shade 300 |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Success | `#00BF6F` | `#DCF9ED` | `#A8F1D2` | `#6EDFAF` | `#33CC8C` | `#009959` | `#007343` | `#004C2C` |
| Warning | `#FF8800` | `#F8DFB3` | `#F5CD73` | `#F1BE59` | `#F0B74D` | `#EBA536` | `#E89A2D` | `#E1841A` |
| Danger | `#FF1028` | `#FFE7EA` | `#FFB7BF` | `#FF8894` | `#FF4C5E` | `#D50D21` | `#AA0B1B` | `#800814` |

### Greyscale

| Step | Value | Step | Value |
| --- | --- | --- | --- |
| 50 | `#F3F3F4` | 500 | `#6F6D7C` |
| 100 | `#E2E2E5` | 600 | `#525062` |
| 200 | `#C5C5CB` | 700 | `#3E3C4A` |
| 300 | `#A9A8B1` | 800 | `#292831` |
| 400 | `#8C8A96` | 900 | `#151419` |

### Accessible color use

The following contrast ratios were calculated from the confirmed hex values:

| Foreground / background | Ratio | WCAG AA guidance |
| --- | ---: | --- |
| Primary `#073959` / white | `12.08:1` | Passes normal text |
| Primary `#073959` / grey 50 | `10.89:1` | Passes normal text |
| Secondary `#EEB040` / primary | `6.28:1` | Passes normal text |
| Tertiary `#61C2B7` / primary | `5.70:1` | Passes normal text |
| Grey tint `#C7D1D8` / primary | `7.79:1` | Passes normal text |
| Secondary `#EEB040` / white | `1.92:1` | Fails text contrast |
| Tertiary `#61C2B7` / white | `2.12:1` | Fails text contrast |

- Never use gold or cyan as normal-sized text on white, even though those combinations appear in exploratory Figma examples.
- On light backgrounds, use primary navy or another verified dark text token. Preserve link recognition with an underline or another non-color cue.
- Gold and cyan are suitable text accents on primary navy when the exact pairing and state have been checked.
- Recheck contrast for hover, active, disabled, image-overlay, gradient, glass, and focus states. A compliant default state does not validate the other states.
- Do not use color alone to communicate selection, validation, or status.

## Typography

### Families and roles

- **Big Caslon:** H1 and H2 display headings and occasional large editorial statements.
- **Gotham HTF:** H3, H4, body copy, navigation, links, labels, metadata, and controls.
- **Inter:** specification labels inside the Figma style sheet. It is not the intended primary site typeface.

The theme now includes self-hosted WOFF2 files for Gotham HTF Book (`400`) and Bold (`700`). Gotham Medium (`500`) and Big Caslon are not present. Do not silently substitute Inter, label Book as Medium, or map a missing weight to the wrong file. Confirm that the supplied Gotham files are licensed for web use; no license document was present alongside the source files.

### Annotated implementation scale

| Role | Family | Size | Weight/style | Line height | Tracking |
| --- | --- | ---: | --- | ---: | ---: |
| H1 | Big Caslon | `48px` / `3rem` | annotated `800`; specimen uses Big Caslon Medium | open/normal | `0%` |
| H2 | Big Caslon | `40px` / `2.5rem` | annotated `700`; specimen uses Big Caslon Medium | open/normal | `2%` |
| H3 | Gotham HTF | `32px` / `2rem` | Medium; annotated `700` | open/normal | annotated `-0.5%` |
| H4 | Gotham HTF | `24px` / `1.5rem` | Medium; annotated `700` | open/normal | `-0.25%` |
| Body 1 | Gotham HTF | `16px` / `1rem` | Medium; bold emphasis available | `145%` | `0%` |
| Body 2 | Gotham HTF | `14px` / `0.875rem` | Medium; bold emphasis available | `145%` | `0%` |
| Body 3 | Gotham HTF | `12px` / `0.75rem` | Medium; bold emphasis available | `145%` | `0%` |
| Link 1 | Gotham HTF | `16px` / `1rem` | Medium | `145%` | `0%` |
| Link 2 | Gotham HTF | `14px` / `0.875rem` | Medium | `145%` | `0%` |
| Link 3 | Gotham HTF | `12px` / `0.75rem` | Medium | `145%` | `0%` |

The specimen layers do not fully agree with their written annotations: the raw H1 and H2 nodes report `64px` and `48px`, while their labels specify `48px` and `40px`; Body 1 also contains an `18px` segment while its label specifies `16px`. This may be inherited scaling in the Figma composition. Use the annotated implementation sizes above until design confirms otherwise; do not copy raw layer sizes blindly.

Typography on dark navy uses near-white `#F9F9F9` for headings and body. Light-background headings and body use primary `#073959`. Figma shows cyan hyperlinks and non-underlined text links, but light-background cyan does not meet AA contrast for normal text; implementation must use an accessible light-background link treatment.

## Logos and favicon

Figma supplies these visual families:

- Full-color horizontal logo with icon.
- Lake Tahoe Travel wordmark without the standalone icon.
- Standalone full-color icon.
- White/reverse horizontal logo and wordmark for primary navy or dark photography.
- Reverse standalone icon variants.
- Circular favicon using the mountain, lake, gold sun, navy, and cyan palette.

Implementation rules:

- Use exported SVG artwork for logos and icons. Never recreate the marks with CSS or text.
- Select full-color marks on light surfaces and reverse marks on dark surfaces.
- Preserve intrinsic proportions and adequate surrounding space.
- Do not recolor individual vector paths, stretch, crop, rotate, or add effects to the logo.
- Minimum sizes, exact clear-space measurements, and the final asset filenames are **open** because Figma does not document them explicitly.

## Layout and responsive references

Figma provides two reference viewports:

- Desktop: `1440px` wide.
- Mobile: `375px` wide.

These remain visual verification canvases rather than breakpoint values. Responsive implementation uses the default Bootstrap 5.3 scale as an approved engineering decision:

| Tier | Range used by the design system |
| --- | --- |
| `xxl` | `1400px` and wider |
| `xl` | `1200px`–`1399.98px` |
| `lg` | `992px`–`1199.98px` |
| `md` | `768px`–`991.98px` |
| `sm` | `576px`–`767.98px` |
| `xs` | Below `576px` |

CSS is authored desktop-first: the base presentation targets `xxl`, followed by descending `max-width` overrides at `1399.98px`, `1199.98px`, `991.98px`, `767.98px`, and `575.98px`. The values follow [Bootstrap 5.3 breakpoints](https://getbootstrap.com/docs/5.3/layout/breakpoints/#available-breakpoints), but the Bootstrap framework itself is not a project dependency.

Layouts should remain fluid inside each range. The breakpoint scale does not define the container widths, grid columns, or gutters, which remain separate design decisions. A non-standard breakpoint requires a demonstrated content failure and an explicitly documented exception.

### Homepage sequence

Desktop reference:

1. Four-image utility/header strip.
2. Main hero header.
3. Interactive map.
4. Four-image feature area.
5. One-up driver.
6. Stacked feature driver.
7. Five-up driver on a dark background.
8. Content trail.
9. Global accordion.
10. Final footer.

Mobile reference:

1. Mobile main hero header.
2. Interactive map in a `375px` layout.
3. Four-image mobile feature area.
4. One-up driver on a dark background.
5. Left-aligned header and CTA.
6. Stacked feature driver.
7. Five-up driver.
8. Mobile content trail.
9. Mobile global accordion.
10. Mobile footer.

The mobile map instance is named “Desktop” in Figma but is resized to the mobile canvas. Treat the visible mobile composition as intent and the layer name as stale metadata.

## Imagery and glass treatment

- Favor authentic Lake Tahoe landscape, activity, lodging, event, and community photography.
- Use `object-fit: cover`-style cropping only when the focal subject remains visible at every breakpoint.
- Image overlays use primary navy and, in selected components, cyan, gold, or danger-like tints. Exact opacity must be read from the target component rather than generalized.
- The “looking glass” concept uses translucent/blurred circular or rounded surfaces over photography.
- Glass UI must retain a border and sufficient text/control contrast across the complete range of possible photographs. Add a stable backing overlay when the image alone cannot guarantee contrast.
- Meaningful images need editorial alt text; decorative and duplicated card imagery should use empty alt text where appropriate.

## Components and states

The component catalog includes:

- Global navigation, desktop mega menus, mobile navigation, submenus, internal navigation, and jump-link banners.
- Main heroes and search-led hero treatments.
- Page drivers in one-, three-, four-, and five-up layouts.
- Feature images, static image clusters, inspiration panels, and expandable galleries.
- Story, event, listing, and adaptive-scale cards.
- Event drivers and listing grids.
- Content trail recommendations.
- Global, toggle, side-by-side, content, and weather accordions.
- Side-by-side content, icon blocks, stats, copy blocks, lists, comparison tables, and newsletter signup.
- Standalone, editorial, full-bleed, episodic, and carousel video modules.
- End-page clusters, page clusters, footer, tags, read-time metadata, and date badges.

### Buttons and controls

Figma defines these control families:

- Standard primary, secondary, and tertiary CTAs.
- CTA treatments over white, light color, dark color, photography, and video.
- Glass CTA and forward/back arrow buttons.
- Default, hover, pressed, and disabled states.
- Category selectors with selected and hover states.
- Form submission buttons and text “Read More” actions.

### Homepage header search

The homepage search-bar states were verified against Figma node `1:44668`:

- Default design reference: `99px × 25px`, “Search” placeholder, white search icon. The browser implementation reserves `104px` at rest so the supplied webfont remains unclipped under text rendering and zoom differences.
- Hover, keyboard focus, typing, or a populated query: `249px × 25px`, expanded placeholder, Peak Gold search icon.
- Surface: `rgba(2, 22, 43, 0.1)` with a `1px` `rgba(199, 209, 216, 0.5)` border, `90px` radius, and `0 4px 4px rgba(0, 0, 0, 0.25)` shadow.
- Typography: Gotham HTF, `12px`, `1.45` line height, white.

The Figma rows are state examples, not four simultaneous search fields. Production uses one native WordPress GET search form that changes presentation while preserving its label, keyboard behavior, server-rendered markup, and normal search-results URL.
- Carousel/slider navigation and pagination indicators.
- Accordion toggles.
- Search fields and search icons.
- Dropdowns, calendar fields, and expanded calendar.
- Load-more and “scroll for more” controls.
- Jump-link bars.

Implementation rules:

- Use BEM for new theme-owned components, while leaving WordPress structural classes intact.
- Use links for navigation and buttons for actions.
- Every interactive state must have a keyboard and focus-visible equivalent. Hover must never be the only way to reveal required content.
- Preserve a minimum `44px` preferred pointer target and satisfy WCAG 2.2 target-size requirements.
- Disabled styling must not be the only explanation for unavailable functionality.
- Carousel buttons require accessible names, and pagination indicators must expose the current item.
- Search and calendar fields require visible labels or an equivalent accessible naming pattern; placeholders are not labels.

### Navigation state direction

- Expanded desktop navigation uses large Big Caslon menu labels.
- Main and footer navigation move from light/cool tones toward gold for emphasized hover states on dark navy.
- Back actions and submenu arrows are part of the label's clickable target, not separate unlabeled controls.
- Mobile menu labels require the same selected, expanded, and focus states as desktop navigation.
- Any mega-menu or submenu must support keyboard traversal, `Escape`, accurate `aria-expanded`, and focus restoration.

### Cards, tags, and panels

- Story cards may be image-only with overlay text, expanded with a translucent information panel, or event-specific with a date badge and reminder action.
- The adaptive story-card example preserves the essential image, tag, heading, summary, and action at smaller widths.
- Tags and read-time metadata appear on both light and image backgrounds; use the matching contrast-safe variant.
- Inspiration panels use destination imagery and can reveal descriptive copy and location. Mobile examples include explicit close controls.
- The middle inspiration panel example represents a hover/revealed state with a top CTA.
- Revealed panel copy must work on click/tap and keyboard activation, not hover alone.

### Accordions

- Global accordions reveal one answer in place and may include an optional CTA.
- Toggle accordions include a title, optional subhead, optional CTA, and topic toggles that switch between separate question sets.
- Side-by-side accordions pair introductory content and one CTA with a static question list.
- The active question uses an expanded state; other questions remain collapsed.
- Implement with native buttons, unique relationships between triggers and panels, accurate `aria-expanded`, and predictable keyboard order.
- Figma leaves the final FAQ button treatment open. Do not invent a new visual pattern without approval.

## Developer notes captured from Figma

### Motion — partially open

- Search bubbles are intended to expand horizontally on hover/focus.
- A bounce effect and its exact hover treatment remain an open question.
- Proposed timing is `500ms`.
- Some navigation list items are intended to appear sequentially, with the complete list visible within one second.
- General hover-state transitions propose a `500ms` crossfade.
- Respect `prefers-reduced-motion`; reduced-motion mode should remove stagger, bounce, and non-essential crossfades while preserving immediate state feedback.

### Listing module — technology open

- Each card contains an image, title, short copy, and at most one or two buttons.
- Two dropdown filters and a dedicated keyword-search field may be combined.
- The unfiltered state shows the full initial listing set.
- Optional additional copy may appear in an expanded/rollover state, but it must also be available to keyboard and touch users.
- “See More” loads further listings after the initial grid.
- Algolia is only a current direction; the search technology has not been approved.

### Events

- Event content is expected to synchronize through The Events Calendar API from the Tahoe Events Calendar platform rather than being authored directly in this WordPress installation.
- Featured events use a rotating full-width hero with date, venue, organizer metadata, up to two CTAs, arrows, and pagination dots.
- The event grid uses cards, dropdown filtering, and load-more pagination.
- Filter taxonomies must match the data supplied by the external feed.
- Indexing event data in Algolia and including it in Content Trail are provisional architecture decisions.

### Content modules

- Side-by-side modules pair text and CTAs with an image and allow zero to three CTAs.
- Icon blocks contain two to four icon blurbs.
- Stats modules contain two to four large values and labels, on white or color backgrounds.
- Copy blocks, copy lists, and comparison tables are text-driven without imagery.
- Newsletter signup is a real Gravity Forms submission/lead-capture component, not a navigation CTA. Gravity Forms owns its fields, validation, consent, confirmation, notifications, and entries; the theme owns its approved placement and visual treatment.
- Comparison tables are static structured data with icon/label headers, rows, and a final CTA; the shown design is neither sortable nor interactive.

### Galleries and image clusters

- The four-up feature pattern expands the selected or hovered card.
- Inspired-gallery copy appears after activation on both desktop and mobile.
- Figma presents two gallery-expansion ideas; the final expansion pattern remains open.
- Non-clickable clusters must not expose misleading link or button semantics.

### Weather module — data dependencies open

- Current Weather, Weather Forecast, and Historical Weather are independent accordions and load collapsed by default on desktop and mobile.
- Current conditions and the five-day forecast are intended to use the OpenWeather API.
- Monthly historical averages require a paid OpenWeather historical-data tier and cannot ship until the subscription is confirmed.
- Lake water temperature is not a standard OpenWeather field. It requires a separate lake-specific data source or an explicitly maintained manual source.
- Weather data must include units, update/freshness information, understandable error states, and an accessible non-visual representation.

## Accessibility and inclusive behavior

All implementation must meet the accessibility requirements in `AGENTS.md`, including WCAG 2.2 Level AA. Design-specific requirements include:

- Treat Figma as visual intent, not evidence of accessibility conformance.
- Correct any Figma color combination that fails contrast; document the accessible substitution.
- Do not rely on hover for navigation, card expansion, panel copy, tooltips, or controls.
- Provide visible focus states that remain legible on white, navy, photography, video, and glass surfaces.
- Preserve content and functionality at `200%` zoom and at a `320 CSS px`-equivalent viewport.
- Keep DOM/reading order aligned with the visual order when desktop modules stack on mobile.
- Pause or provide controls for carousels and autoplaying media. Do not autoplay audible content.
- Provide captions/transcripts for media and accessible alternatives for maps, weather graphics, and other complex visuals.
- Avoid embedding important text in images.
- Ensure controls retain accessible names when their visible label changes between breakpoints.

## WordPress implementation mapping

This repository is currently a classic PHP theme with `theme.json` support.

- `theme.json` should eventually hold the approved editor-facing palette, font families, content widths, and other stable design tokens.
- `assets/css/main.css` currently contains source CSS and starter tokens; it is not generated.
- `style.css` remains theme metadata only.
- Reusable visual modules belong in `template-parts/` and should receive explicit data or WordPress content.
- Editable content belongs in native WordPress fields, posts, pages, menus, taxonomies, or the approved ACF Pro content model described below.
- UI labels in PHP must use the `ltt-dive-in` text domain.
- ACF Pro is approved and installed in the Local environment. Algolia, event synchronization, weather providers, and new custom post types are not approved merely because they appear in the design file; confirm their architecture before adding them.
- The paid version of Gravity Forms is approved and installed in the Local environment. Use it for visitor-facing forms; do not build a parallel submission system in ACF or theme PHP.

### ACF Pro content model

ACF Pro should make the substantive content of each approved design module editable without turning the WordPress editor into an unrestricted visual-design tool.

- Expose editorial values such as eyebrow text, headings, body copy, images, captions, CTA labels and destinations, related entries, and the order of repeatable items where the design allows reordering.
- Keep visual rules in `theme.json`, CSS, and templates. Colors, typography, spacing, breakpoints, animation values, arbitrary HTML, and CSS classes are not general-purpose editor fields.
- When a component has approved visual variations, expose a constrained select, radio, or button-group field with semantic names such as `light`, `dark`, or `image-overlay`; map those values to theme-owned classes in PHP.
- Match repeater minimums and maximums to the component specification: icon and stats modules support two to four items, CTAs support the documented limits, and fixed card compositions must not accept unlimited rows.
- Use relationship, post-object, or taxonomy fields when an editor is selecting existing WordPress content. Do not duplicate an entry's title, URL, image, or metadata into unrelated text fields without a documented editorial need.
- Use WordPress attachment IDs for images and rely on the Media Library alt text. If a module needs a separate visible caption or credit, model it as a distinct field rather than overloading the alt attribute.
- Treat optional fields as real visual states. Templates must omit absent descriptions, images, CTAs, or metadata cleanly instead of leaving empty wrappers or inaccessible controls.
- Store global values only on a deliberately scoped ACF Options Page. Page-specific module content remains attached to that page or to the selected reusable content entity.
- Version field-group definitions with the theme through ACF Local JSON or focused PHP registration; database-only field definitions are not a reproducible implementation.
- Exact field groups, field names, location rules, and page-template mappings must be defined as each template is implemented and kept synchronized with its template part and template-specific stylesheet.

### Gravity Forms mapping

- Use Gravity Forms for newsletter signup, contact, lead-capture, registration, and any future visitor-submission workflow approved for the site.
- ACF may store or select a validated Gravity Forms form ID for a design module, but Gravity Forms remains the source of truth for fields, conditional logic, validation, confirmations, notifications, entries, and add-on feeds.
- Form modules must include designed states for default, focus, completed input, help text, required fields, validation errors, submission/loading, success confirmation, and service failure.
- The theme may style a shared form component and its template-specific placement, but it must preserve visible labels, field grouping, error associations, focus visibility, and status announcements.
- Newsletter and other marketing forms require approved consent copy, privacy-policy context, retention rules, recipient routing, spam protection, and provider integrations before production launch.

The current `theme.json` and CSS palette are starter values and do not match the confirmed Figma brand palette. Reconcile them in a dedicated implementation task; do not partially replace tokens while leaving old aliases in active use.

## Open design and architecture decisions

The following must be resolved before the related production work is considered complete:

- Confirmation of the Gotham webfont license, acquisition of Gotham Medium and Big Caslon webfont files, and final fallback stacks.
- Confirmation of the annotated typography sizes versus raw Figma node sizes.
- Canonical spacing, radius, shadow, glass-blur, and opacity token scales.
- Container widths, layout grids, and gutters within the approved responsive breakpoint scale.
- Minimum logo sizes, clear space, and final exported assets.
- Approval and semantics of success, warning, and danger colors.
- Accessible light-background link and hover colors.
- Final FAQ button and gallery-expansion patterns.
- Search and filtering technology, including whether Algolia is used.
- Final ACF field-group and page-template mapping for each editable module, including which content is reusable or global.
- Final Gravity Forms inventory and environment-migration process, including fields, consent, confirmations, notifications, retention, spam protection, email delivery, and external feeds.
- Event-feed API contract, cache, failure behavior, and taxonomy mapping.
- OpenWeather subscription level and lake-temperature data source.
- Exact motion easing and whether the proposed bounce effect is retained.

## Updating this guide

When a design decision changes:

1. Link the exact Figma node that establishes the change.
2. Update the relevant confirmed value or behavior here.
3. Record unresolved conflicts in the open-decisions section rather than guessing.
4. Update `theme.json`, CSS tokens, templates, and component behavior together when implementation is authorized.
5. Recheck accessibility for every affected state and breakpoint.
6. Update the “Last verified against Figma” date.
