# Contributing to LivQ AccessFix - PA Italia Add-on

This is maintained by one person, so the most useful contribution is usually a
precise report rather than a large patch. Everything below exists to make a
change reviewable, not to add ceremony.

## Before anything else

A security problem does not belong in a public issue. Report it privately to
**info@skapacraft.com**.

## Reporting a bug

Open an issue with the bug template. What makes a report actionable:

- the exact steps, from a clean state, and what you expected instead
- the Design Comuni theme version (or the specific derivative/child theme)
- the LivQ AccessFix and PA Italia add-on versions
- a screenshot or a minimal page URL when the trigger is one specific template

If the input holds personal data, describe how to build an equivalent one
rather than attaching it.

## Suggesting a fix for a new Design Comuni accessibility gap

Open an issue with the feature template and describe the WCAG/EAA issue before
the fix. Include the specific template-part or component affected, and which
WCAG success criterion it fails. A fix that only applies to a fork or a heavily
customised install does not ship: this add-on targets the stock theme and its
direct derivatives.

## Pull requests

Open an issue first for anything beyond a typo or a one-line fix, so the
approach is agreed before the work happens.

Once that is settled:

1. Branch from `main`.
2. Keep the change to one concern. Two unrelated fixes are two pull requests.
3. Add a `readme.txt` changelog entry, and bump the version.
4. Verify it against a real Design Comuni install (or a derivative), and say
   in the pull request how you did.

## Building and checking locally

```bash
composer install
composer phpcs      # WordPress Coding Standards, must come out clean
composer phpcbf     # fixes what it can automatically
```

## House rules

- **English only**, in code comments, commit messages and user-facing strings.
- **Commit messages** say what changed and why. The subject line is imperative
  and under 72 characters, the body wraps at 72 and explains the reasoning that
  is not obvious from the diff.
- **No generated or vendored files** in a commit unless the project already
  tracks them.
- **No credentials, keys or personal data**, including in test fixtures.

## Licence

By contributing you agree that your work is distributed under the licence in
[LICENSE](LICENSE).
