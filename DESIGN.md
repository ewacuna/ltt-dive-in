# Lake Tahoe Travel Design Guide

- Status: initial implementation reference
- Last verified against Figma: 2026-10-08
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
- **Roboto:** self-hosted Latin-subset variable WOFF2, available at weights `400` through `700` for approved component exceptions. Its SIL Open Font License is included with the font asset.
- **Inter:** specification labels inside the Figma style sheet. It is not the intended primary site typeface.

The theme includes self-hosted WOFF2 files for Gotham Book (`400`), Gotham HTF Medium (`500`), Gotham HTF Bold (`700`), and Big Caslon Medium (`500`). The Figma style sheet (`1:3`, checked 2026-10-09) sets every Gotham text style to family `Gotham HTF`, style `Medium`; `Gotham-Medium.woff2` was converted without subsetting from `GothamHTF-Medium.otf` (H&FJ, version `001.000`), and its glyph advances match the source exactly. `Gotham-Bold.woff2` has identical advances to `GothamHTF-Bold.otf`. `Gotham-Book.woff2` is the later H&FJ Gotham `2.200` Pro release rather than the HTF cut; no Figma text style uses Book. Do not silently substitute Inter, label one weight as another, or synthesize a missing weight. Confirm that the supplied Gotham files are licensed for web use; no license document was present alongside the source files.

### Gotham weight map

Verified on 2026-10-09 against the dev-access style sheet (`1:3`) and nine desktop review compositions (Home, Lodging Hub, Experience Landing, Places, Events & Live Music, Inspired, Stories, Meetings Hub and Contact). Medium `500` is the theme's base weight for body copy, headings, navigation, CTAs, tags, cards, forms, and controls. Only these Gotham roles differ:

| Role | Figma | Theme |
| --- | --- | --- |
| Accordion question (FAQ, footer FAQ CTA) | Gotham HTF Bold `19px` | `.global-accordion__question`, `700` |
| FAQ answer body | Gotham HTF Book `18px` / `180%` ("FAQ Body") | `.global-accordion__answer`, `400` |
| Utility navigation | Gotham HTF Book `11px` | `.utility-navigation a`, `400` |
| Stat numbers | Gotham HTF Book `96px` | `.content-module__stat-number`, `400` |
| Inline emphasis | Gotham HTF Bold | `strong`/`b` (`bolder` from Medium) |

Deliberate deviations: the selected Page Driver filter and accordion topic use `700` in addition to their fill so selection is not communicated by color alone, although Figma keeps the selected label Medium. Gravity Forms error messages, the PhotoSwipe lightbox, and Relume placeholder text (such as the Short Feature Driver title, `1:11346`) have no Gotham specification in Figma and keep their existing weights.

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

### Homepage display title — implementation reference

The user-supplied homepage SVG export (`4028:433`, supplied 2026-09-09) is a visual reference only; the title remains editable HTML text in Big Caslon Medium. Its fill is a vertical white / `#999999` / `#999999` / `#E0E0E0` gradient at 40% opacity, with stops at 0%, 32.6923%, 66.3462%, and 100%. The subtle outline is `#8AB5C5` at 20% opacity, nominally 1px.

The CSS gradient must follow the visible capital letters rather than the full font line box. With the supplied Big Caslon webfont and `line-height: 1`, the measured capital bounds are approximately `0.117em`–`0.845em` from the line top. Map the source stops into these bounds and repeat the gradient per line for longer editorial titles. Keep the full line box so `background-clip: text` does not clip glyphs. The SVG uses an inward masked stroke; CSS text stroke is centered, so small edge differences remain. Matching a line box to the SVG's 113px height does not establish matching glyph bounds.

The exported image's fill is composited within the SVG. Applying `mix-blend-mode` to the whole heading against the video is not equivalent; `background-blend-mode` on a single transparent gradient does not reproduce that SVG composition either. The implementation uses the exported translucent gradient directly, verified against the export over the same video frame. This visual comparison does not establish text contrast compliance across every video frame.

### General imagery guidance

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

The internal mobile hero in review node `95:24452` uses a `64px` Big Caslon Medium H1 on a `375px` canvas. On 2026-09-29 the client approved reducing mobile font sizes slightly at the theme's discretion, so at `767.98px` and narrower the theme uses `56px` as the mobile target and reduces it further only when the widest word would exceed the available width (for example, “Meetings”); the non-JavaScript fallback favors complete words and prevents clipping at narrow widths. Editorial line separators in page titles are rendered as normal spaces so wrapping can adapt to the viewport.

### Buttons and controls

Figma defines these control families:

- Standard primary, secondary, and tertiary CTAs.
- CTA treatments over white, light color, dark color, photography, and video.
- Glass CTA and forward/back arrow buttons.
- Default, hover, pressed, and disabled states.
- Category selectors with selected and hover states.
- Form submission buttons and text “Read More” actions.

#### Touch feedback

- Figma does not use the browser's native tap highlight (the translucent box iOS Safari and Android Chrome draw over a tapped link or button). It is disabled once, site-wide, on `html` in `assets/css/main.css`; the property is inherited, so it covers every link, control, and scripted container.
- Do not re-declare `-webkit-tap-highlight-color` in component or template stylesheets. Touch feedback comes from each component's designed hover, pressed/active, and focus-visible states, which must remain visible and must not rely on the tap highlight.

#### Footer FAQ CTA

The approved footer FAQ CTA uses the shared dark-tertiary button treatment over the primary shade 200 footer surface: tertiary tint 200 `#79CDC4` fill, primary `#073959` text, and the shared pill geometry. Its hover state uses a white fill with primary text, and keyboard focus uses the shared Peak Gold outline. The reference label is “Frequently Asked Questions”; the label and destination remain editable through the Footer Settings link field. When the editor provides only a destination, the theme uses the reference label as its fallback.

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
- The WordPress primary-menu hierarchy maps to the navigation states: level one opens the global panel, level two lists its destinations, and an optional level three opens the secondary flyout.
- Mobile navigation places the native site-search form before the primary menu. Primary labels use Big Caslon at `48px`; expanded submenu links use Gotham HTF at `18px`, and utility links use Gotham HTF at `14px`.
- Mobile submenu sections expand inline. Inactive primary sections use primary tint 300 `#85A2B2`, the open section uses near-white `#F9F9F9`, submenu links use primary tint 50 `#C7D1D8`, and the open indicator/back action uses Peak Gold `#EEB040`.
- Main and footer navigation move from light/cool tones toward gold for emphasized hover states on dark navy.
- Back actions and submenu arrows are part of the label's clickable target, not separate unlabeled controls.
- Mobile menu labels require the same selected, expanded, and focus states as desktop navigation.
- Any mega-menu or submenu must support keyboard traversal, `Escape`, accurate `aria-expanded`, and focus restoration.

### Optional page section navigation — implementation note

- Internal pages with the shared header can select a native WordPress menu in **LTT Dive In — Page Header → Section menu**. The PHP-owned ACF group has dynamic menu choices and must not also be registered in Local JSON.
- **Header background** offers Solid navy (the existing default) or Navy gradient, independently of the section-menu selection. The gradient applies only to the internal header's main row, reusing the existing navigation gradient (`180deg`, `#02162b` to `#073959`); the section bar below the hero uses the confirmed primary shade 200 (`#042742`). Verified directly from review instance `95:87899` (Internal Navigation Collapsed Desktop), child `I95:87899;95:21655`: vertical linear gradient, `#02162b` at `0%` to `#073959` at `100%`, with both stops and the fill fully opaque. This matches the existing CSS; no gradient change was needed. The Figma gradient frame is `85.967px` high and clips a `90px` header child; the theme retains its existing `90px` main row without clipping. No custom color controls are exposed. The homepage retains its independent transparent overlay; archives and posts retain their existing header.
- This renders top-level destination links in a separate navigation landmark directly below the Internal Page Hero, outside the site header, matching the review Places pages (`95:23633` desktop, `95:23917` mobile), where the Jump Link Banner follows the hero. It scrolls with the page and is not sticky. When the bar is present, the hero's “Scroll for more” link targets `#section-navigation` instead of `#page-content`, so the bar is not scrolled past. Client feedback on 2026-09-29 moved it from below the main header row. Assignment is explicit per page, with no inheritance. Front page, posts page, and Blank Canvas are excluded. Missing ACF, missing/deleted menus, and empty menus render no bar or component assets.
- Review node `95:87898` (Jump Link Banner / 14) was verified in Figma: a `1440px` canvas, `40px` navy `#042742` bar, `64px` outer horizontal padding, and six centered `206.667px` columns (`1240px` total), each with `6px` horizontal padding and an `18px` padded link wrapper. Link 1 is Gotham HTF Medium `500`, `16px`, `145%` line height, zero tracking, white. The source clips `48px` link wrappers inside the `40px` bar; production keeps the closed row `40px` high without clipping.
- At `1400px` and wider, the six top-level links appear in one `40px` row. At `1399.98px` and narrower, the same `40px` row slides horizontally, matching mobile review frame `95:24452`, which places the unchanged Jump Link Banner instance in a `375px` frame and clips it. The theme's registered Swiper (`ltt-dive-in-swiper`) drives the row in free mode with `20px` start and end offsets, horizontal mouse-wheel/trackpad support, and drag; Swiper's a11y module is disabled because it would replace the navigation's list semantics. Keyboard focus slides the focused link fully into view, and the current page's link is the initial position when present. `prefers-reduced-motion: reduce` removes momentum and slide animation. User-approved addition not present in Figma: while Swiper is active, a `40px` edge fade from the bar color `#042742` to transparent appears on each side only when more links lie in that direction; it ignores pointer input, is hidden in forced-colors mode, and stays clear of focused link text. Swiper is destroyed at `1400px` and wider. Without JavaScript, the row falls back to native horizontal scrolling with a hidden scrollbar and start snapping. Each column keeps its `206.667px` minimum width and grows for longer labels. This replaces the earlier editor-labelled disclosure at the user's request; the “Collapsed menu label” ACF field was removed, and any previously saved values remain unused in post meta. Hover and keyboard focus use the Jump Link Bar state: a white rectangle with navy `#073959` text and an inset navy focus outline. No nested-menu support is added.
- A ResizeObserver supplies the measured complete header height to the internal hero. Without JavaScript the header and bar remain in flow, so the hero can be taller than the viewport but content does not overlap. Pages without the bar retain their existing height calculations.

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
- The footer FAQ CTA uses the confirmed dark-tertiary treatment documented under Buttons and controls; accordion-module CTAs continue to use the approved variant for their individual surface.

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

- The Figma notes and the work orders describe a sync from the Tahoe Events Calendar into The Events Calendar. The project has since confirmed the [Seeker Events API](https://seeker.io/apidocs/events/) as the event source. Events are read live from Seeker on the server and are not stored in WordPress, so there are no event posts, detail pages, or FacetWP/Algolia records for events.
- Featured events use a rotating full-width hero with date, venue, organizer metadata, up to two CTAs, arrows, and pagination dots.
- The event grid uses cards, dropdown filtering, and load-more pagination.
- Filter taxonomies must match the data supplied by the external feed.
- Indexing event data in Algolia and including it in Content Trail are provisional architecture decisions.

#### Event Driver block — implementation note

- One ACF block, `ltt-dive-in/event-driver`, with a variant select: Hero Featured Events (`1:11652`/`1:11653`), Listed Events (`1:11591`/`1:11592`), and Events Carousel (`1:11658`/`1:11659`), all under Figma `1:11584`. The same frames appear in review node `95:87554`.
- The Seeker API key is entered in **Settings → Events** (`manage_options`) or defined as `LTT_DIVE_IN_SEEKER_API_KEY` in `wp-config.php`, which takes precedence. A saved key is never printed back into the form, is not autoloaded, and is never sent to the browser. Changing it invalidates every cached response.
- Requests are server-side only: `states=published`, cancelled events excluded, 15-minute cache per query, a one-day stale fallback, and a pause for the `Retry-After` period after a 429. Missing configuration, an API failure, or an empty result renders nothing publicly and an explanation in the editor preview.
- Hero: Seeker's `featured` flag (3–6 slides, soonest first) or 3–6 events chosen by searching Seeker in the editor. The two CTAs link to the first ticket URL and to the Seeker `eventurl`, with editor-defined labels; a CTA whose URL is missing is hidden. No auto-advance.
- Listed: Figma Story Card – Event tiles (`1:824` default, `1:883` hover) in three columns at `1400px`, two columns from `767.98px` to `1199.98px`, and a Swiper carousel of “Card with Date” cards below `768px`, as in mobile node `1:11592`. The hover reveal also opens on keyboard focus and is always open on `hover: none` devices. The Categories dropdown uses Seeker categories (the editor may limit the options). The date dropdown offers Upcoming, Today, This weekend, Next 7 days, This month, and Next month. These presets are a proposal: Figma shows only the “Upcoming” label. Filtering and Load More use `ltt-dive-in/v1/event-driver-events` and fall back to a GET form without JavaScript.
- “Remind Me” downloads an iCalendar file with a one-hour alert from `ltt-dive-in/v1/event-calendar`. Figma does not define this behavior; confirm it with design.
- Cards and the hero link to Seeker's external `eventurl`. Event images are Seeker's remote renditions (150/600/1024/1400px and original) and are decorative, because the title is always visible.
- Each rendered block prints schema.org `Event` JSON-LD from the Seeker data.
- Deviations: Roboto labels (hero meta, weekday) use Gotham. The Figma search placeholder color `rgba(197,197,203,.85)` on `#f2f2f2` fails contrast, so grey 600 `#525062` is used. On small screens the search field sits above the dropdowns while the DOM keeps the desktop order (dropdowns, then search). The unused Relume placeholder cards in the mobile frames are not implemented.

### Content modules

- Newsletter Sign Up: [desktop](https://www.figma.com/design/ojSvaH9s0cyWEnxWdk3ksH/DevOps-090726Copy-LAKE-TAHOE-TRAVEL---NEW-SITE-DESIGN---DEV-ACCESS--?node-id=1-11841&m=dev) and [mobile](https://www.figma.com/design/ojSvaH9s0cyWEnxWdk3ksH/DevOps-090726Copy-LAKE-TAHOE-TRAVEL---NEW-SITE-DESIGN---DEV-ACCESS--?node-id=1-11842&m=dev): the Gravity Form and the existing heading and description sit on the left; an optional editorial heading, description, and link CTA sit on the right. On mobile, the form column precedes the centered editorial panel. The editorial heading uses Gotham HTF Medium `32px` on desktop and Roboto Bold `36px` on mobile. Form inputs use Gotham HTF Medium `16px` on desktop and Roboto Regular `16px` on mobile; consent copy uses Roboto Regular `16px`. The mobile editorial description and CTA keep their desktop type styles. The glass CTA is used over photography and the primary outline CTA is used when no background image is selected.
- Meetings Request Contact: [desktop](https://www.figma.com/design/ojSvaH9s0cyWEnxWdk3ksH/DevOps-090726Copy-LAKE-TAHOE-TRAVEL---NEW-SITE-DESIGN---DEV-ACCESS--?node-id=1-11835&m=dev) and [mobile](https://www.figma.com/design/ojSvaH9s0cyWEnxWdk3ksH/DevOps-090726Copy-LAKE-TAHOE-TRAVEL---NEW-SITE-DESIGN---DEV-ACCESS--?node-id=1-11834&m=dev): copy sits on the left and the Gravity Form on the right at desktop; they stack in that order on mobile. The expected form has email and phone fields, a consent checkbox, and supporting copy. Email and phone share one desktop row, then stack on mobile. Newsletter forms use email and ZIP Code fields, a consent checkbox, and supporting copy; their submit button remains disabled until the consent checkbox is checked. On desktop, form inputs use Gotham HTF Medium `16px`, consent copy uses Roboto Regular `16px`, and submit controls use Gotham HTF Medium `14px`. On mobile, consent copy and the submit control use Roboto Regular `16px`; the introductory copy uses Roboto Regular `12px`. On desktop, place the submit control before the consent field in DOM and keyboard order, with consent to its right. On mobile, place consent before the submit control in both DOM and keyboard order. On image backgrounds, inputs and the checkbox share Figma's `rgba(2, 22, 43, 0.1)` fill, `rgba(199, 209, 216, 0.5)` border, 4px downward shadow and the theme's 4px glass blur; the submit button is white with a navy border. Gravity Forms owns the labels, consent language, supporting copy, validation, and submission; retain visible field labels even where Figma uses placeholder-style examples.
- Both form variants have an optional **Form supporting text** input for supplementary copy. Display it below the submit control at `0.75rem` (12px); use Gotham on desktop and Roboto Regular on mobile, and parse links written as `[link text](URL)` into safe anchors.
- Both variants use the selected Media Library image as a decorative full-bleed background. Their navy gradient overlay uses 65% opacity, matching the documented contrast treatment for Page Cluster imagery. An absent image retains a white background. On image backgrounds, the Gravity Forms validation summary stays visible with the same translucent glass fill and blur as the inputs, white text, and an input-matching border without an extra focus outline. Summary links use a thicker underline for keyboard focus instead of a colored border or outline. Newsletter inline field errors remain available to assistive technology but are visually hidden to avoid repeating the summary.
- Side-by-side modules pair text and CTAs with an image, allow zero to three CTAs, and have a minimum height of `17.625rem` (282px). When a background image is selected, the right-column body copy uses Roboto Regular `18px`/`1.5` on desktop and Gotham HTF Medium `12px`/`1.45` on mobile.
- Icon blocks contain two to four icon blurbs. Each blurb uses a required SVG attachment from the Media Library, displayed decoratively in a contained 93 × 93 px area. Desktop title and copy use Gotham HTF; on mobile, titles use Roboto Bold `24px`/`1.3` and copy uses Roboto Regular `16px`/`1.5`. Safe SVG is the approved upload dependency and must be active; version 2.5.1 is installed in Local. Configure its Media settings to allow Administrators and Editors. Safe SVG sanitizes normal WordPress uploads; the theme does not add an SVG MIME allowance. Legacy icon values that are not attachment IDs have no bundled-file fallback and must be replaced with a Media Library SVG. SVG upload requires WordPress 6.9 or newer; the theme itself continues to support WordPress 6.6+.
- Stats modules contain a header heading and copy plus two to four values and labels. The editor background selector uses the eight Stats swatches in Figma Color (`1:11913`): Navy `#003A5C`, Peak Gold `#EEB040`, Burgundy `#5E3236`, Red `#9E3F3D`, Forest Green `#296154`, Green `#4FA154`, Blue `#397398`, and Crystal Clear Cyan `#61C2B7`. Leaving the selector empty renders white. These are local Stats colors; the site's confirmed primary navy token remains `#073959`. The background image field is always available and any selected image takes priority over the color. The retired Photo selector value is normalized to empty, while existing image values continue to render. Four-value rows use a smaller number size at desktop widths so values like `6,225` stay on one line; they become two columns at `1199.98px` and one column on small screens.
- Comparison table row labels use Gotham HTF Medium; on mobile, cell values use Roboto SemiBold `16px`/`1.5`.
- Copy blocks, copy lists, and comparison tables are text-driven without imagery.
- Newsletter signup is a real Gravity Forms submission/lead-capture component. Gravity Forms owns its fields, validation, consent, confirmation, notifications, and entries; the theme owns its approved placement and visual treatment. The optional editorial CTA beside the form is a separate ACF link.
- Comparison tables have an optional `Feature column heading` above the feature labels, styled at `1.125rem` (18px). They have two to four editable comparison columns, each with a required SVG and label plus an optional short description; the short description uses the column-label styles at `0.875rem` (14px). Tables have one to five feature rows, and each row displays one cell for every active comparison column. Reordering a column in the editor keeps its cell values with that heading and is available through keyboard-accessible Up and Down controls. The front-end table is static and has an optional final CTA; four-column tables use a keyboard-focusable horizontal scroll region on narrow screens, while two- and three-column tables keep the existing stacked mobile treatment. Existing columns without a saved SVG remain blank until the editor chooses an icon. Safe SVG is the approved upload dependency.

### Page Cluster (C-13)

- Reference: [Page Cluster](https://www.figma.com/design/ojSvaH9s0cyWEnxWdk3ksH/?node-id=1-11876), checked 2026-09-29; Hub desktop `1:103`, mobile `1:1299`; Features desktop `1:3121`, mobile `1:3207`.
- Hub Driver uses four manually authored background-image cards, full-width in a desktop row and 2×2 on mobile, with optional individual CTAs and up to two header CTAs. Images were added to the field model because the visual design includes them. Desktop cards grow to accommodate text rather than clipping it into Figma's fixed heights; mobile card descriptions are visually limited to three lines while their full text remains in the document.
- Features List Grid uses exactly four image/title/copy cards, a 32px desktop gap, and Swiper on mobile with an 11px gap, next-card peek, pagination and arrows styled like the 5+ Up Gallery controls. Swiper assets load only for this variant, and the mobile row remains natively scrollable without JavaScript. The optional Primary CTA and Secondary CTA appear beside the header on desktop and below the cards on mobile, as specified after the initial implementation. The former Shared CTA field is no longer exposed or rendered; any existing saved value is left untouched.
- Both variants have a manual label/title; only Hub Driver exposes and renders the optional section body. A previously saved body remains stored when switching to Features List Grid. Reuse shared CTA and slider controls. Card titles use Gotham HTF Medium and the 40px desktop heading scale; mobile headings use the component's 36px reference. Inconsistent Roboto placeholder text in the mobile feature sample is normalized to Gotham.
- Both variants share the same four-card repeater. Individual card CTAs are displayed only for Hub Driver and retained when switching variants. Legacy variant-specific repeaters are read without changing the database: the saved active variant is preferred, then the other set if empty. The shared field is persisted on the next editor save; previous revisions retain the old content. If both old sets contain cards, only the preferred set is loaded, not merged.
- Hub overlays are darkened from the reference's 51% to 65% to keep white copy readable over bright editorial photographs. No photography is bundled: images belong to the Media Library.

### Galleries and image clusters

- The four-up feature pattern expands the selected or hovered card.
- Inspired-gallery copy appears after activation on both desktop and mobile.
- Figma presents two gallery-expansion ideas; the final expansion pattern remains open.
- On mobile, the PhotoSwipe counter and close control sit 24px above the active image frame, inside the image's horizontal gutters; their vertical position follows the displayed image rather than the viewport edge.
- Non-clickable clusters must not expose misleading link or button semantics.

### Weather module — data dependencies open

- Current Weather, Weather Forecast, and Historical Weather are independent accordions and load collapsed by default on desktop and mobile.
- Current conditions and the five-day forecast are intended to use the OpenWeather API.
- Monthly historical averages require a paid OpenWeather historical-data tier and cannot ship until the subscription is confirmed.
- Lake water temperature is not a standard OpenWeather field. It requires a separate lake-specific data source or an explicitly maintained manual source.
- Weather condition icons currently use the icon image returned by the OpenWeather API. Figma shows only a snowflake (header) and a sun (Weather blocks), not a complete condition set; the client has been asked for preferred icons.
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

- Confirmation of the Gotham webfont license and final fallback stacks.
- Confirmation of the annotated typography sizes versus raw Figma node sizes.
- Canonical spacing, radius, shadow, glass-blur, and opacity token scales.
- Container widths, layout grids, and gutters within the approved responsive breakpoint scale.
- Minimum logo sizes, clear space, and final exported assets.
- Approval and semantics of success, warning, and danger colors.
- Accessible light-background link and hover colors.
- Final gallery-expansion pattern.
- Search and filtering technology, including whether Algolia is used.
- Final ACF field-group and page-template mapping for each editable module, including which content is reusable or global.
- Final Gravity Forms inventory and environment-migration process, including fields, consent, confirmations, notifications, retention, spam protection, email delivery, and external feeds.
- Event detail pages, the Remind Me behavior, and the date-filter presets for the Seeker-based Event Driver.
- OpenWeather subscription level and lake-temperature data source.
- Weather condition icon set, pending client feedback; OpenWeather's API icons remain in use until then.
- Exact motion easing and whether the proposed bounce effect is retained.

## Updating this guide

When a design decision changes:

1. Link the exact Figma node that establishes the change.
2. Update the relevant confirmed value or behavior here.
3. Record unresolved conflicts in the open-decisions section rather than guessing.
4. Update `theme.json`, CSS tokens, templates, and component behavior together when implementation is authorized.
5. Recheck accessibility for every affected state and breakpoint.
6. Update the “Last verified against Figma” date.
