# Security policy

## What this add-on is, and why that shapes the threat model

This add-on extends [LivQ AccessFix](https://wordpress.org/plugins/livq-accessfix/) with fixes specific to the **Design Comuni Italia** WordPress theme. It applies its changes through two mechanisms:

- WordPress filters (`walker_nav_menu_start_el`, `post_thumbnail_html`) that run during normal template rendering.
- The parent plugin's output buffer filter (`livqacea_sanitized_html`), which sees the fully rendered HTML of the page.

The whole design rests on the same promise as the parent plugin:

> The add-on only adds accessibility attributes to markup the theme already
> produced. It never injects content of its own into a page, and it never
> sends anything anywhere.

Everything below follows from that.

## What counts as a vulnerability here

- **Injection through a filter or the output buffer.** Anything that lets
  attacker-controlled input (a post title, an option value, theme content)
  reach the rewritten HTML unescaped.
- **Theme detection bypass or spoofing.** The add-on fingerprints Design
  Comuni via text domain, a PHP function signature, and custom post types. A
  way to make it misidentify an unrelated theme and apply fixes that corrupt
  its markup belongs here.
- **Data leaving the site.** The add-on makes no outbound request. Any
  network call, DNS lookup or telemetry is a vulnerability, not a feature.
- **Blanking a page.** A fix that fails open should leave the original markup
  untouched. An input that defeats that and returns an empty or truncated
  page takes the site down, so it is treated as a security problem rather
  than a bug.

A malformed page producing a PHP notice is a bug worth an issue. It is not a
vulnerability on its own.

## Out of scope

- WordPress core, the Design Comuni theme itself, and the parent LivQ
  AccessFix plugin. What is in scope is this add-on's interaction with them,
  for example a fix that corrupts markup the theme produced.
- Anything requiring an attacker who is already an administrator. At that
  point the site is theirs without going through this add-on.
- Accessibility findings. A missing WCAG fix is a feature request, not a
  security report.

## How to report

**Please do not open a public issue for a security problem.** A public
report tells everyone how to exploit it before there is a fix, and this
add-on runs on sites the reporter does not own.

Report privately to **info@skapacraft.com**.

Useful in a report, roughly in order of usefulness:

- what an attacker gains, in one sentence
- the steps to reproduce, and the markup that triggers it
- the add-on and parent plugin versions, the WordPress version and the PHP
  version
- the exact Design Comuni theme version, or the derivative/child theme in use

## What happens then

This is maintained by one person, so no response time is promised that could
not be kept. What is promised instead:

- a report is acknowledged when it is read, even if the answer is that it
  needs time
- a confirmed finding is fixed before anything else, and released as a patch
  version
- the fix says what was wrong and since which version, in the changelog
- credit goes to whoever reported it, unless they prefer otherwise

## Supported versions

Only the latest release published on
[GitHub](https://github.com/skapacraft/livq-accessfix-pa-italia/releases) is
supported. There is no back-porting to older ones: update before reporting.
