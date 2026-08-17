# Modern Catholic Plugin Suite

Part of **Modern Catholic** — modular WordPress tools for Catholic parish websites.

---

# Modern Catholic – Parish Bulletins

![License: GPL-3.0-only](https://img.shields.io/badge/License-GPL--3.0--only-blue.svg)
![WordPress: 6.7+](https://img.shields.io/badge/WordPress-6.7%2B-21759b.svg)
![PHP: 7.4+](https://img.shields.io/badge/PHP-7.4%2B-777bbb.svg)

PDF-first parish bulletin publishing with dated archives, generated thumbnails, an embedded viewer, and optional retention controls.

---

## Features

- Standardized `mc_bulletin` custom post type with automatic migration from `parish_bulletin`
- Bulletin date, PDF attachment, optional excerpt, featured image, and block-editor content
- Date-sorted public archive at `/bulletins/`
- View, download, and responsive in-page PDF actions
- First-page PDF thumbnail generation when no featured image is selected
- All Bulletins retained by default, with an optional administrator-defined rolling retention period
- Cleanup safeguards that preserve media referenced by other WordPress content
- Theme-overridable archive and single templates

---

## Installation

1. Upload or clone `modern-catholic-plugin-parish-bulletins` into `wp-content/plugins/`.
2. Activate **Modern Catholic – Parish Bulletins**.
3. Go to **Bulletins → Add New**.
4. Enter the Bulletin date, select a PDF, and publish.
5. Add `/bulletins/` to the site navigation when ready.

Themes may override the public templates with:

- `parish-bulletins/archive-mc_bulletin.php`
- `parish-bulletins/single-mc_bulletin.php`

---

## Retention

The default policy is **Keep all Bulletins**. Administrators may configure a rolling month limit under **Bulletins → Settings**. When enabled, daily WordPress cron cleanup permanently removes expired Bulletin records and unshared generated media.

---

## Changelog

### 1.5.2

- Standardize the GitHub README with Modern Catholic branding, compatibility badges, installation guidance, and GPL-3.0-only licensing.

### 1.5.1

- Complete the Modern Catholic branding and repository transition.
- Standardize the post type key as `mc_bulletin` and migrate existing Bulletin posts.
- Correct Bulletin Settings screen detection after the post type migration.

---

## License

Licensed under the GNU General Public License version 3.0 only (`GPL-3.0-only`). Mozilla PDF.js and other bundled third-party components retain their original licenses; see `THIRD-PARTY-NOTICES.md`.
