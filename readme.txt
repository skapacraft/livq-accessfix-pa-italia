=== LivQ AccessFix - PA Italia Add-on ===
Contributors:            livqtech, danielegagliardi
Tags:                    accessibility, wcag, eaa, pa-italia, design-comuni
Requires at least:       6.0
Tested up to:            7.0
Requires PHP:            7.4
Stable tag:              1.0.0
Requires Plugins:        livq-accessfix
License:                 GPLv2 or later
License URI:             https://www.gnu.org/licenses/gpl-2.0.html

WCAG 2.2 AA fixes for the Design Comuni Italia WordPress theme. Auto-detects the theme and applies five targeted EAA remediations. Zero config.

== Description ==

**LivQ AccessFix - PA Italia** is a free add-on for [LivQ AccessFix – EAA & A11y AutoFix](https://wordpress.org/plugins/livq-accessfix/) that automatically applies five WCAG 2.2 AA fixes specific to the **Design Comuni Italia** WordPress theme - the official theme for Italian public administrations mandated by AgID and required for EAA (European Accessibility Act, Directive 2019/882) compliance.

The add-on **auto-detects** the Design Comuni theme using multi-signal fingerprinting (text domain, unique PHP functions, PA-specific custom post types) that survives theme folder renames and partial customisations. No configuration is required.

=== Fixes applied ===

**1. aria-current="page" on active nav items (WCAG 4.1.2)**
The Design Comuni walkers only set `class="active"` on the current menu item. Screen readers cannot identify the active page from a CSS class alone. This fix injects `aria-current="page"` on the active anchor and `aria-current="true"` on ancestor items.

**2. Search modal aria-labelledby (WCAG 4.1.2)**
The `#search-modal` dialog has `role="dialog"` but no `aria-labelledby` pointing to its heading. Screen readers announce it without context. This fix links the dialog to its `<h2>` via `aria-labelledby`.

**3. Generic placeholder alt text (WCAG 1.1.1)**
Two Design Comuni template-parts (`banner.php`, `evidenza.php`) contain hardcoded `alt="banner"` and `alt="descrizione immagine"`. These are non-descriptive for screen readers. This fix replaces them with the post title (via `post_thumbnail_html`) or marks them as decorative (`alt=""`).

**4. Leaflet map accessible name (WCAG 1.1.1 / 1.3.1)**
The `single-luogo.php` template renders an OpenStreetMap Leaflet map in a `<div id="map_all">` with no `role`, `aria-label`, or text alternative. This fix adds `role="application"` and `aria-label="Interactive map"` to the container.

**5. Megamenu aria-expanded initial state (WCAG 4.1.2)**
Bootstrap Italia's JS sets `aria-expanded` on dropdown triggers at runtime, but the server-rendered HTML has no initial ARIA state. Before JS loads - or with JS disabled - the structure is undiscoverable for assistive technologies. This fix adds `aria-haspopup="true"` and `aria-expanded="false"` as the static initial state.

=== Requirements ===

* [LivQ AccessFix – EAA & A11y AutoFix](https://wordpress.org/plugins/livq-accessfix/) must be installed and active.
* Design Comuni Italia WordPress theme (or a derivative that registers the same PA custom post types).

== Installation ==

1. Install and activate **LivQ AccessFix – EAA & A11y AutoFix** first.
2. Upload `livq-accessfix-pa-italia` to `/wp-content/plugins/`.
3. Activate via the **Plugins** screen.
4. No configuration needed - the theme is auto-detected and fixes are applied immediately.

== Frequently Asked Questions ==

= Does this work if the theme folder has been renamed? =

Yes. Detection uses three independent signals: the Text Domain in `style.css`, a unique PHP function registered by the theme, and the presence of PA-specific custom post types (`servizio`, `luogo`). At least two signals must match - renaming the folder does not affect any of these.

= Will it conflict with customisations made to the Design Comuni theme? =

No. All fixes are applied via standard WordPress filters (`walker_nav_menu_start_el`, `post_thumbnail_html`) and the parent plugin's output buffer filter (`livqacea_sanitized_html`). They never modify theme files.

= Does it work with child themes of Design Comuni? =

Yes, as long as the child theme does not override the PA custom post types or the `dci_get_breadcrumb_items` function. Detection will still pass with 2/3 signals present.

== Changelog ==

= 1.0.0 =
* Initial release.
* Multi-signal Design Comuni theme detection (text domain + function + CPT).
* aria-current="page" on active nav items via walker_nav_menu_start_el (WCAG 4.1.2).
* Search modal aria-labelledby via output buffer (WCAG 4.1.2).
* Generic alt text replacement via post_thumbnail_html and output buffer (WCAG 1.1.1).
* Leaflet map role="application" + aria-label via output buffer (WCAG 1.1.1 / 1.3.1).
* aria-haspopup + aria-expanded initial state on megamenu items (WCAG 4.1.2).
