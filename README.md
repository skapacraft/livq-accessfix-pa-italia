# LivQ AccessFix - PA Italia Add-on

WCAG 2.2 AA fixes for the Design Comuni Italia WordPress theme. Auto-detects the theme and applies five targeted EAA remediations. Zero config.

Get it on WordPress.org: https://wordpress.org/plugins/livq-accessfix-pa-italia/ (once published).

## What it fixes

This is a free add-on for [LivQ AccessFix - EAA & A11y AutoFix](https://wordpress.org/plugins/livq-accessfix/) that applies five WCAG 2.2 AA fixes specific to **Design Comuni Italia**, the official theme for Italian public administrations mandated by AgID and required for EAA (European Accessibility Act, Directive 2019/882) compliance.

The add-on auto-detects the theme using multi-signal fingerprinting (text domain, unique PHP functions, PA-specific custom post types) that survives theme folder renames and partial customisations. No configuration required.

1. **aria-current="page" on active nav items** (WCAG 4.1.2). The Design Comuni walkers only set `class="active"` on the current menu item; screen readers cannot identify the active page from a CSS class alone.
2. **Search modal aria-labelledby** (WCAG 4.1.2). The `#search-modal` dialog has `role="dialog"` but no accessible name.
3. **Generic placeholder alt text** (WCAG 1.1.1). `banner.php` and `evidenza.php` template-parts ship hardcoded, non-descriptive `alt` text.
4. **Leaflet map accessible name** (WCAG 1.1.1 / 1.3.1). The `single-luogo.php` map container has no role or text alternative.
5. **Megamenu aria-expanded initial state** (WCAG 4.1.2). Bootstrap Italia sets `aria-expanded` via JS at runtime; the server-rendered HTML has no initial ARIA state.

Full detail on each fix, requirements, installation and FAQ is in [readme.txt](readme.txt), the canonical WordPress.org readme.

## Requirements

- [LivQ AccessFix - EAA & A11y AutoFix](https://wordpress.org/plugins/livq-accessfix/) installed and active.
- Design Comuni Italia theme (or a derivative registering the same PA custom post types).

## Installation

```bash
wp plugin install livq-accessfix --activate
wp plugin install livq-accessfix-pa-italia --activate
```

Or via **Plugins > Add New** in wp-admin. No configuration needed; the theme is auto-detected and fixes apply immediately.

## Privacy

This plugin does not collect, store, or transmit any personal data. No third-party services, no CDN, no external API calls.

## Support

- Bug reports / feature requests: [GitHub Issues](https://github.com/skapacraft/livq-accessfix-pa-italia/issues)
- Support forum: wordpress.org/support/plugin/livq-accessfix-pa-italia

## Contributing

How to report, propose and build is in [CONTRIBUTING.md](CONTRIBUTING.md).

## Author

Developed and maintained by [SkapaCraft](https://skapacraft.com) and LivQTech.

## Licence

Copyright (C) 2026 SkapaCraft. GPL-2.0-or-later, see [LICENSE](LICENSE).
