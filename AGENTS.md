# AGENTS.md

## Project overview

LTT Dive In is a classic PHP WordPress theme with modern block-editor support through `theme.json`. It targets WordPress 6.6+ and PHP 8.0+.

The theme intentionally has no Node, Composer, or asset-build pipeline. WordPress loads the source files in `assets/` directly.

## Local development environment

The project is developed and manually tested with [Local](https://localwp.com/).

- Local site URL: [http://lake-tahoe-travel.local/](http://lake-tahoe-travel.local/)
- WordPress Admin: [http://lake-tahoe-travel.local/wp-admin/](http://lake-tahoe-travel.local/wp-admin/)
- Theme directory: `wp-content/themes/ltt-dive-in/`

Assume the Local site must be running before browser-based or WordPress integration checks can succeed. Do not start, stop, reconfigure, import, or reset the Local site or its database unless the task explicitly requires it.

Use the Local URL for manual verification only. Never hard-code `lake-tahoe-travel.local`, another environment hostname, an absolute local filesystem path, or environment-specific credentials in theme PHP, CSS, JavaScript, content, or configuration. Generate site URLs through WordPress APIs.

The standalone shell PHP version may differ from the PHP runtime selected in Local. `php -l` is suitable for syntax checks, but use Local's Site Shell or inspect the Local runtime when exact WordPress/PHP parity matters. Do not assume WP-CLI is available in the regular theme shell.

### Sharing the database

```sh
# Windows (PowerShell), run from the theme directory.
.\scripts\db-backup.ps1
.\scripts\db-restore.ps1 .\db-backups\ltt-db-YYYY-MM-DD-HHmmss.sql

# macOS (Bash). Make executable once after cloning: chmod +x scripts/*.sh
./scripts/db-backup.sh
./scripts/db-restore.sh ./db-backups/ltt-db-YYYY-MM-DD-HHmmss.sql
```

Both scripts discover the MySQL connection from Local's `sites.json` (matched by domain) and the credentials from `wp-config.php` (located by walking up from the theme directory); the Local site must be running. The restore creates a timestamped safety backup before importing and asks for confirmation. Pass a different Local domain as the optional last argument if the site was created with another domain.

## Design source of truth

[`DESIGN.md`](./DESIGN.md) translates the project's Figma source into implementation guidance. Read it before creating or changing a public-facing page, component, pattern, design token, or interaction.

- Do not invent colors, typography, spacing, radii, shadows, or behavior. Use the approved responsive breakpoints documented below.
- Use confirmed values from `DESIGN.md`; treat its provisional and open items as unresolved decisions.
- When Figma, `DESIGN.md`, and the current code disagree, identify the conflict before changing production tokens.
- Keep editable editorial and marketing content in WordPress. Public-facing copy is in English unless the task explicitly adds localization.

## Repository map

- `functions.php`: defines theme constants and loads the files in `inc/`.
- `inc/setup.php`: theme supports, navigation menus, content width, and widget areas.
- `inc/enqueue.php`: front-end and editor asset loading.
- `inc/template-tags.php`: reusable presentation helpers.
- `inc/template-functions.php`: hooks and filters that affect templates.
- `inc/customizer.php`: Customizer settings and controls.
- `header.php` and `footer.php`: shared site chrome.
- `front-page.php`, `home.php`, `single.php`, `page.php`, `archive.php`, `search.php`, `404.php`, and `index.php`: WordPress template hierarchy entry points.
- `template-parts/`: reusable markup for content, pages, and sections.
- `templates/`: page templates selectable in the WordPress editor.
- `assets/css/main.css`: global foundations and styles genuinely shared across the site.
- `assets/css/templates/`: template-specific styles, added as templates are implemented.
- `assets/css/components/`: reusable component styles shared by more than one template, added as needed.
- `assets/fonts/gotham/`: self-hosted Gotham HTF WOFF2 files used by global typography.
- `assets/js/main.js`: dependency-free browser behavior.
- `style.css`: required WordPress theme metadata only; do not add application styles here.
- `theme.json`: block-editor settings, layout sizes, palette, typography, and design tokens.
- `scripts/`: cross-platform Local database backup/restore helpers; not loaded by WordPress. See “Sharing the database between developers”.

## Development principles

- Follow the WordPress template hierarchy and use core APIs instead of reproducing WordPress behavior.
- Keep business logic out of templates. Put reusable display helpers in `inc/template-tags.php` and hooked behavior in `inc/template-functions.php` or another focused file under `inc/`.
- If a new PHP file must load on every request, require it explicitly from `functions.php`.
- Prefer small, reusable template parts over duplicated markup.
- Do not add plugin-like functionality to the theme. Content or behavior that must survive a theme switch belongs in a plugin.
- Do not add third-party dependencies or a build system unless the task specifically requires one.
- Preserve compatibility with WordPress 6.6 and PHP 8.0; do not use newer PHP syntax or APIs without an explicit compatibility change.

## ACF Pro and editable content

ACF Pro is an approved project dependency and is installed in the Local WordPress environment. Use it to let editors manage structured, design-specific content while keeping presentation decisions in the theme. Do not bundle ACF with the theme, edit files under `wp-content/plugins/advanced-custom-fields-pro/`, or assume a locally installed plugin file is part of this repository.

### Content ownership

- Prefer native WordPress data when it already models the content: page/post title, main editor content, excerpt, featured image, menus, users, categories, tags, and other registered taxonomies.
- Use ACF for structured page sections, module settings, related-content selections, CTA groups, supplemental media, and metadata that WordPress core does not model clearly.
- Content types, taxonomies, or business rules that must survive a theme switch belong in a site plugin. Theme-specific field groups and rendering may remain with this theme.
- Use an ACF Options Page only for genuinely global content such as organization details or site-wide alerts. Do not turn it into a second page editor or duplicate WordPress settings and menus.
- Editors may choose content and an approved component variant. Do not expose raw CSS classes, arbitrary colors, pixel values, HTML fragments, or layout controls that can bypass `DESIGN.md` and accessibility constraints.

### Field definitions and version control

- A field group is not complete if it exists only in the local database. Version its definition with the code that consumes it.
- Prefer ACF Local JSON in an `acf-json/` directory for theme-owned field groups. If registration in PHP is required, place it in a focused file under `inc/` and load it from `functions.php`.
- Keep field and group keys stable after content exists. Prefix human-readable group names and programmatic identifiers consistently with `ltt_dive_in` or `ltt-dive-in` as appropriate.
- Scope location rules narrowly to the intended post type, page template, taxonomy, or options page. Avoid field groups that appear across unrelated editing screens.
- Give fields clear labels, concise instructions, useful defaults, and validation constraints that match the design. Use conditional logic to hide irrelevant controls.
- When changing a field name, type, return format, or structure, account for existing saved content and document any migration. Never delete or repurpose a populated field key casually.

### Template integration and safety

- Retrieve values with `get_field()` or focused wrapper functions; avoid `the_field()` because templates must escape values for their output context.
- Treat every field as optional at runtime unless the template has a deliberate, tested required-state design. Missing ACF, an unsynchronized field group, or an empty field must not cause a fatal error, invalid markup, or an empty interactive control.
- Escape text with `esc_html()`, attributes with `esc_attr()`, URLs with `esc_url()`, and intentionally permitted rich text with `wp_kses_post()`. Do not trust a field merely because it was entered by an administrator.
- Configure image fields to return attachment IDs when practical and render them through `wp_get_attachment_image()` or another WordPress image API so responsive sources, dimensions, lazy loading, and attachment alt text are preserved.
- For link fields, validate each returned value and render the title, URL, and target separately. A CTA without a meaningful label or valid destination must not be output.
- Keep queries out of repeater loops where possible. Limit editor-selectable item counts to what the design supports, avoid deeply nested repeaters or flexible-content structures, and measure any relationship or post-object query used on listing pages.
- If ACF Blocks are introduced later, register only approved components, provide editor previews, and keep their markup and accessibility behavior consistent with the matching front-end template part.

### Accessible authoring

- ACF controls must help editors produce accessible output rather than merely expose every visual option.
- Preserve the Media Library alt-text workflow for informative images. Provide explicit guidance when an image is decorative and should render with empty alt text.
- Use fixed semantic heading structure in templates where possible. Do not ask editors to choose heading levels for visual sizing; if a level must be configurable, restrict it to the valid levels for that component and validate the page outline.
- Provide fields for meaningful CTA labels, captions, transcripts, and other text alternatives whenever the associated media or interaction requires them.
- Required fields and validation messages in the editor must be understandable without relying on color alone. Front-end fallbacks must preserve semantic structure and accessible names.

## Gravity Forms

The paid version of Gravity Forms is an approved project dependency and is installed in the Local WordPress environment. Use Gravity Forms for newsletter, contact, lead-capture, registration, and other visitor submissions. ACF may select which form appears in an approved module, but it must not reproduce form fields, validation, notifications, entries, or submission processing.

### Integration and ownership

- Build and manage forms through Gravity Forms. Render them with the official block, widget, shortcode, or `gravity_form()` API as appropriate; do not recreate Gravity Forms markup or submission handling in the theme.
- Do not edit files under `wp-content/plugins/gravityforms/`, bundle the plugin with the theme, or commit a paid-plugin archive or license key.
- Avoid scattering numeric form IDs through templates. Select the form through the editor/widget or centralize and validate any required ID in a focused setting or helper.
- Guard direct API calls with `function_exists( 'gravity_form' )` or the relevant Gravity Forms availability check. A missing or inactive plugin must not cause a fatal error or leave an empty form landmark.
- Form structure, notification routing, confirmations, feeds, and integrations are configuration, not presentation. Document and migrate them deliberately between environments.
- Export form definitions for reproducibility when appropriate, but review exports before version control. Never commit entries, personal data, license keys, CAPTCHA credentials, webhook secrets, API tokens, SMTP credentials, or environment-specific recipient addresses.

### Styling and performance

- Preserve Gravity Forms semantic markup and behavior. Prefer supported classes, CSS custom properties, and documented hooks over copying or rewriting generated HTML.
- Shared visual treatment belongs in a focused `assets/css/components/forms.css` file when multiple templates use forms. A template stylesheet owns only that form's placement and page-specific composition.
- Do not load a theme form stylesheet globally unless forms truly appear site-wide. Let Gravity Forms conditionally enqueue its required assets and avoid registering duplicate copies of jQuery or plugin libraries.
- Use AJAX submission only when it improves the approved experience and its focus, error, success, analytics, and caching behavior have been tested. A normal POST remains an acceptable resilient baseline.
- Keep embeds and third-party marketing scripts limited to the forms that require them. Measure their effect on Core Web Vitals before enabling them site-wide.

### Accessibility and validation

- Every field needs a persistent visible label. Placeholders may provide examples but must not replace labels.
- Use the correct field type, autocomplete token, input purpose, instructions, required state, and validation constraints. Group related choices with an accessible fieldset and legend.
- Keep required indicators understandable without color alone. Instructions and error messages must be programmatically associated with the affected control.
- Preserve Gravity Forms' error summary, inline errors, focus management, status announcements, and keyboard behavior. Custom styling or hooks must not hide errors or remove accessible names and descriptions.
- Test keyboard-only completion, screen-reader reading order, browser autofill, `200%` zoom, `320 CSS px` reflow, error recovery, confirmation behavior, and AJAX states when enabled.
- Multi-page forms need an understandable progress indicator and must preserve entered values and focus when moving between steps.
- Use accessible anti-spam measures. Do not introduce image puzzles, time limits, or interactions that block keyboard or assistive-technology users.

### Security, privacy, and delivery

- Collect only data required for the stated purpose. Provide clear consent and privacy-policy context where legally or operationally required.
- Define entry retention, deletion, export, and access policies before production launch. Limit form-entry access to the minimum WordPress roles and staff who need it.
- Never expose secrets in hidden fields or trust hidden/default values for authorization, pricing, routing, or other sensitive decisions. Validate server-side through supported Gravity Forms hooks.
- Do not place passwords, payment-card data, health information, or other high-risk data in ordinary notifications or logs. Payments must use an approved Gravity Forms payment add-on and hosted/tokenized provider flow.
- Configure production email through an approved transactional mail or SMTP service. Use a domain-authorized From address, keep the visitor address as Reply-To where appropriate, and test SPF, DKIM, DMARC, delivery failures, confirmations, and administrative notifications.
- Configure spam protection, rate limiting, duplicate-submission behavior, and meaningful failure states. CAPTCHA, marketing, CRM, webhook, and analytics integrations require privacy and accessibility review before activation.

## PHP and WordPress conventions

- Follow WordPress PHP coding standards: tabs for indentation, spaces inside parentheses, Yoda conditions where appropriate, and docblocks for public functions and hooks.
- Prefix global functions, hooks, option names, script/style handles, and other global identifiers with `ltt_dive_in_` or `ltt-dive-in` as appropriate.
- Guard directly loaded PHP implementation files with `if ( ! defined( 'ABSPATH' ) ) { exit; }`.
- Use the `ltt-dive-in` text domain for all user-facing strings. Escape translated output with helpers such as `esc_html__()` or `esc_attr__()` when appropriate.
- Sanitize data when it enters the system and escape it at output time according to context:
  - text: `esc_html()`
  - attributes: `esc_attr()`
  - URLs: `esc_url()`
  - limited trusted HTML: `wp_kses_post()`
- Use nonces and capability checks for any state-changing request or saved setting.
- Prefer WordPress URL, query, media, menu, option, and enqueue APIs. Do not hard-code site URLs or installation paths.
- Use `get_template_part()` for reusable sections and pass explicit arguments through its third parameter.
- Do not modify the main query directly. Use `pre_get_posts` with the appropriate admin and main-query guards when query customization is required.

## Accessibility requirements

Treat accessibility as a release requirement, not a later enhancement. New and changed front-end code must target [WCAG 2.2 Level AA](https://www.w3.org/TR/WCAG22/) and follow the [WordPress Accessibility Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/accessibility/).

Automated checks are useful but cannot establish conformance by themselves. Every user-facing change requires relevant keyboard, zoom/reflow, and screen-reader-oriented manual checks.

### Document structure and semantics

- Set the document language through WordPress with `language_attributes()` and retain correct page metadata and viewport behavior.
- Use native HTML elements before ARIA. Do not recreate buttons, links, headings, lists, navigation, or form controls with generic `div` or `span` elements.
- Keep a logical heading hierarchy. Each page needs a clear primary heading, and heading levels must describe structure rather than visual size.
- Use landmarks such as `header`, `nav`, `main`, `aside`, and `footer` appropriately. Label repeated landmarks so their purposes are distinguishable.
- Keep exactly one primary `main` region and provide a visible-on-focus skip link to it.
- Preserve a sensible DOM and reading order at every breakpoint. CSS must not create a visual order that conflicts with keyboard or screen-reader order.
- Add ARIA only when native HTML cannot express the required behavior. ARIA roles, names, properties, and states must match the component's actual behavior.

### Keyboard and focus

- All interactive functionality must work with a keyboard alone, with no keyboard traps.
- Use naturally focusable controls. Do not use positive `tabindex` values to force a custom focus order.
- Provide a clearly visible focus indicator with sufficient contrast. Never remove outlines without an equally visible replacement.
- Focus must not be fully obscured by sticky headers, overlays, cookie banners, or other content.
- When opening a modal or similar blocking interface, move focus into it, contain focus while it is open, support `Escape` where appropriate, and restore focus to the invoking control when it closes.
- Menus, disclosure buttons, and toggles must expose accurate accessible names and states such as `aria-expanded` and `aria-controls`.
- Any drag-and-drop interaction must also provide a non-dragging input method.

### Links, controls, and targets

- Use links for navigation and buttons for actions. Do not attach button behavior to non-interactive elements.
- Link and button names must make sense from their accessible name and context. Avoid ambiguous text such as “click here” or repeated “read more” links without additional context.
- Icon-only controls require an accessible name; decorative icons must be hidden from assistive technology.
- Interactive targets must meet WCAG 2.2 target-size requirements and have adequate separation. Prefer targets at least `44px` by `44px` where the layout permits.
- Do not require hover, color, sound, or pointer precision to discover or operate functionality.

### Images, audio, video, and embeds

- Informative images need concise alternative text that communicates their purpose in context.
- Decorative images must use an empty `alt` attribute. Do not repeat nearby captions or visible text in alt text.
- Linked images must describe the link destination or action, not merely their appearance.
- Complex charts, maps, and diagrams require an equivalent text summary or data representation.
- Prerecorded video with meaningful audio requires synchronized captions; audio-only content requires a transcript. Provide audio description or a text alternative when important visual information is not conveyed in the soundtrack.
- Do not autoplay audible media. Embedded third-party content must have an accessible title and must not introduce a keyboard trap.

### Color, typography, zoom, and responsive layout

- Maintain a contrast ratio of at least `4.5:1` for normal text and `3:1` for large text. User-interface components, meaningful graphics, and focus indicators also require at least `3:1` contrast where WCAG applies.
- Never use color alone to communicate state, errors, selection, required fields, or meaning.
- Text must remain usable at `200%` browser zoom, and the layout must reflow without two-dimensional scrolling at a viewport equivalent to `320 CSS px`, except for content that inherently requires it.
- Do not lock text in fixed-height containers or prevent user zoom. Prefer relative units for typography and spacing.
- Keep line length, line height, paragraph spacing, and text alignment readable. Avoid justified body text and images of text.
- Verify high-contrast and forced-colors behavior for controls and focus indicators when relevant.

### Forms, validation, and status messages

- Every input needs a programmatically associated visible label. A placeholder is not a label.
- Identify required fields in text and programmatically; do not rely on color or an asterisk alone.
- Group related controls with `fieldset` and `legend` where appropriate.
- Provide useful autocomplete attributes and input purposes for common personal-data fields.
- Validation errors must identify the affected field, explain how to correct it, and be associated with the control. Preserve entered values after failed submission.
- On failed submission, move focus to an error summary or the first invalid field as appropriate.
- Success, error, loading, and updated-content messages must be announced without unexpectedly moving focus, using a suitable live region or status role when native behavior is insufficient.
- Do not disable submission controls without communicating why, and do not impose time limits unless users can turn them off, adjust them, or extend them.

### Motion and dynamic behavior

- Respect `prefers-reduced-motion`. Remove or reduce non-essential animation, parallax, and smooth scrolling for users who request it.
- Do not include content that flashes more than accessibility thresholds.
- Moving, blinking, scrolling, or auto-updating content needs a way to pause, stop, or hide it when WCAG requires one.
- Avoid unexpected changes of context on focus or input. State changes must be predictable and communicated.
- For content inserted with JavaScript, manage focus only when necessary and announce meaningful updates without excessive live-region noise.
- The existing mobile-navigation script must remain safe when its DOM elements are absent, preserve correct `aria-expanded` state, support `Escape`, and return focus appropriately.

### WordPress-specific accessibility

- Preserve accessibility provided by core functions such as `wp_nav_menu()`, `the_custom_logo()`, comment functions, and form APIs unless there is a tested reason to customize their output.
- Ensure custom navigation walkers, pagination, search forms, comment forms, and widget markup retain labels, current-item state, and keyboard behavior.
- Editor-facing choices should help authors create accessible content: meaningful labels, safe color combinations, alt-text support, and semantic block patterns.
- Do not add the `accessibility-ready` tag to `style.css` unless the complete theme has been audited against the current WordPress accessibility-ready requirements.

## CSS and JavaScript conventions

- Keep design tokens aligned between `theme.json` and `assets/css/main.css`. Prefer existing CSS custom properties before introducing new hard-coded colors or spacing values.
- Follow the CSS architecture below. Do not accumulate all page and component styling in `assets/css/main.css`.
- Write responsive styles desktop-first using the approved Bootstrap 5.3 breakpoint values and descending `max-width` queries documented below.
- Use vanilla JavaScript unless a task explicitly requires a dependency. The existing script is loaded in the footer and must remain safe when expected DOM elements are absent.
- Do not edit generated or minified assets. This repository currently contains only source assets.

### CSS architecture and ownership

Every WordPress template must own its page-level layout and unique presentation in a dedicated stylesheet. A style belongs in a global or shared file only when it is intentionally reused.

Use this structure as templates are implemented:

```text
assets/css/
├── main.css
├── components/
│   ├── buttons.css
│   ├── cards.css
│   ├── accordions.css
│   └── navigation.css
└── templates/
    ├── front-page.css
    ├── home.css
    ├── single.css
    ├── page.css
    ├── archive.css
    ├── search.css
    ├── 404.css
    ├── index.css
    ├── full-width.css
    └── blank-canvas.css
```

Only create files that are needed by the current work. The directory listing above defines naming and ownership; it does not require empty placeholder files.

### Responsive breakpoints and desktop-first workflow

Use the default [Bootstrap 5.3 breakpoint values](https://getbootstrap.com/docs/5.3/layout/breakpoints/#available-breakpoints) as the project's shared responsive scale. This adopts the breakpoint values and names only; it does not authorize installing or enqueueing Bootstrap CSS, JavaScript, Sass, utilities, or grid classes.

| Tier | Bootstrap lower bound | Desktop-first application |
| --- | ---: | --- |
| `xxl` | `1400px` and wider | Base/default desktop styles; no media query |
| `xl` | `1200px` and wider | `@media (max-width: 1399.98px)` |
| `lg` | `992px` and wider | `@media (max-width: 1199.98px)` |
| `md` | `768px` and wider | `@media (max-width: 991.98px)` |
| `sm` | `576px` and wider | `@media (max-width: 767.98px)` |
| `xs` | less than `576px` | `@media (max-width: 575.98px)` |

Author the complete large-desktop presentation first, outside media queries. Add overrides from widest to narrowest so the cascade progressively adapts the design:

```css
.component {
	/* Default design for 1400px and wider. */
}

@media (max-width: 1399.98px) {
	/* xl and narrower adjustments. */
}

@media (max-width: 1199.98px) {
	/* lg and narrower adjustments. */
}

@media (max-width: 991.98px) {
	/* md and narrower adjustments. */
}

@media (max-width: 767.98px) {
	/* sm and narrower adjustments. */
}

@media (max-width: 575.98px) {
	/* xs adjustments. */
}
```

- Keep the media-query order above in every stylesheet. Do not mix uncoordinated mobile-first `min-width` queries into a desktop-first component.
- Use `.02px` fractional upper bounds to prevent overlap with the corresponding Bootstrap `min-width` boundary on fractional-density viewports.
- Do not create device-specific breakpoints such as “iPhone,” “iPad,” or “laptop.” Add a non-standard breakpoint only when content demonstrably fails between the approved tiers, and document the exception and reason next to the rule.
- Prefer fluid sizing, wrapping, Grid/Flexbox, `clamp()`, and intrinsic layout over adding unnecessary media queries.
- Test each affected component immediately above and below its active boundaries, plus the Figma references at `1440px` and `375px`, WCAG reflow at `320 CSS px`, and browser zoom at `200%`.
- Desktop-first refers only to CSS authoring order. The DOM must remain semantic and usable at every size; do not duplicate meaningful content or reorder it incompatibly for mobile.

#### `main.css`

Keep `assets/css/main.css` limited to site-wide foundations:

- approved design tokens and CSS custom properties;
- box sizing, document defaults, typography foundations, media defaults, and accessibility utilities;
- global container and content-width primitives;
- shared WordPress/editor content behavior that applies throughout the site;
- site chrome only when it is present across the complete site.

Do not place homepage sections, post grids, archive layouts, template-specific hero treatments, or one-off overrides in `main.css`.

#### Template stylesheets

- Match the stylesheet name to the PHP template: `front-page.php` uses `assets/css/templates/front-page.css`, `single.php` uses `single.css`, and so on.
- A template stylesheet owns its composition, section spacing, template-only responsive behavior, and unique visual treatments.
- Selectable page templates follow the template basename: `templates/full-width.php` uses `assets/css/templates/full-width.css` and `templates/blank-canvas.php` uses `blank-canvas.css`.
- Keep template selectors scoped under an existing body class or a template root class when practical, especially when WordPress conditions can overlap.
- Do not copy a reusable component's complete styles into multiple template files. Move the shared component to `assets/css/components/` and keep only template-specific positioning or overrides in the template stylesheet.
- Do not place styles in PHP `<style>` elements or enqueue dynamically generated CSS unless a WordPress setting genuinely requires runtime values.

#### Shared component stylesheets

- Put a component in `assets/css/components/` when the same markup and behavior appear in two or more templates.
- Use one focused file per meaningful component or tightly related component family.
- Component styles own their internal layout, states, variants, and accessibility behavior. Template files own where the component sits in the page composition.
- Use BEM for theme-owned components. Do not rename or apply BEM to WordPress core structural classes.
- If a component is truly present on every public page, it may be loaded globally. Otherwise, enqueue it only on the templates that use it.

### Conditional stylesheet loading

Register and enqueue styles from `inc/enqueue.php` through WordPress APIs. Every non-global stylesheet must load only when its template or component is used.

- Use `is_front_page()` for `front-page.css`.
- Use `is_home()` for `home.css`.
- Use `is_singular( 'post' )` for `single.css`.
- Use `is_page()` for the common `page.css`, while handling `is_front_page()` first so the front page does not receive unintended page styles.
- Use `is_archive()` for `archive.css`.
- Use `is_search()` for `search.css`.
- Use `is_404()` for `404.css`.
- Use the fallback stylesheet only for requests that resolve to `index.php` and are not covered by a more specific condition.
- Use `is_page_template( 'templates/full-width.php' )` and `is_page_template( 'templates/blank-canvas.php' )` for selectable page-template styles.

Conditional checks can overlap. Order them from most specific to most general, and use explicit branching when a page must receive exactly one template stylesheet. A selectable page template may intentionally receive both the common `page.css` and its narrower template stylesheet.

Use consistent handles such as `ltt-dive-in-front-page` and declare `ltt-dive-in-style` as a dependency so global foundations load first. Version local styles with `filemtime()` and fall back to `LTT_DIVE_IN_VERSION`, matching the existing enqueue pattern.

Do not enqueue every template and component stylesheet globally for convenience. When a component is used on a conditionally assembled page, keep a clear mapping in `inc/enqueue.php` between the WordPress condition, template stylesheet, and component dependencies.

### Editor styles

The block editor currently loads `assets/css/main.css`. Keep editor-safe global typography, colors, and content primitives there. Template-only front-end layout must not leak into the editor automatically.

If an editor-visible pattern or component needs additional parity, add its focused stylesheet deliberately through the appropriate WordPress editor-style API and verify that it does not depend on front-end-only body classes or DOM structure.

### Webfont performance

- Self-host fonts from `assets/fonts/`; do not use CSS `@import`, Google Fonts, or another third-party font request.
- Prefer WOFF2 and declare faces once at the top of the global `assets/css/main.css` because typography is a site-wide foundation.
- The available Gotham files are Book `400` and Bold `700`. Do not label Book as Medium `500`, synthesize a missing weight, or point `500` at the Bold file.
- Preload only the regular Gotham Book face used for body content. Allow Bold to load on demand unless measurement proves it is consistently critical above the fold.
- Keep `font-display: swap` so text remains visible while the font loads and always provide a system-font fallback stack.
- Use the WordPress `wp_preload_resources` filter for preload markup. Do not hard-code preload tags in `header.php`.
- Do not base64-embed fonts in CSS; it prevents independent caching and increases stylesheet transfer size.
- Any new font file requires confirmation of its webfont license, real family/style metadata, actual usage, and transfer cost before it is added.

## Working safely

- Inspect `git status` before editing and preserve unrelated user changes.
- Keep changes scoped to the request. Avoid opportunistic rewrites or formatting unrelated files.
- Never edit WordPress core or plugins to solve a theme concern.
- Do not commit credentials, API keys, local URLs, database exports, uploads, logs, or editor-specific files.
- Do not change the theme version in `functions.php` or `style.css` unless the task includes preparing a release; keep both versions synchronized when it does.

## Validation

There is currently no automated test suite or configured linter. Run the checks that apply to the change:

```sh
# Syntax-check every PHP file in the theme.
find . -type f -name '*.php' -not -path './vendor/*' -print0 | xargs -0 -n1 php -l

# Validate theme.json.
php -r '$json = file_get_contents("theme.json"); json_decode($json, true, 512, JSON_THROW_ON_ERROR); echo "theme.json is valid JSON\n";'

# Review the final patch and repository state.
git diff --check
git diff
git status --short
```

For user-facing changes, also perform a manual WordPress check when the local site is available:

- Load the affected template with and without representative content.
- Check desktop and mobile layouts.
- Navigate the complete affected flow using only `Tab`, `Shift+Tab`, `Enter`, `Space`, arrow keys where expected, and `Escape`.
- Verify focus visibility, focus order, skip-link behavior, menus, forms, validation, modals, and empty states as relevant.
- Test at `200%` zoom and at a `320 CSS px`-equivalent viewport without loss of content or functionality.
- Check text, component, meaningful-graphic, and focus-indicator contrast.
- Inspect the accessibility tree or test with a screen reader for meaningful changes to structure, names, states, or dynamic announcements.
- Run a browser accessibility audit or axe when available, then manually evaluate its results and test issues automation cannot detect.
- Confirm the browser console has no new errors.
- If editor styles or `theme.json` changed, compare the block editor with the front end.

## Definition of done

A change is complete when it is narrowly scoped, follows WordPress security and escaping rules, remains accessible and responsive, passes applicable validation, and its manual verification needs or limitations are clearly reported.
