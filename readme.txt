=== Modern Catholic – Parish Bulletins ===
Contributors: twitchd8
Requires at least: 6.7
Requires PHP: 7.4
Stable tag: 1.3.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Publishes dated parish bulletin PDFs while preserving a natural path toward
full e-bulletin content.

== Description ==

Modern Catholic – Parish Bulletins adds Dashboard > Bulletins with a focused publishing workflow:

* Bulletin title
* Bulletin date
* PDF selection or upload through the Media Library
* Optional excerpt and featured thumbnail
* Optional block-editor content for a future e-bulletin

Published bulletins appear at /bulletins/, newest bulletin date first. Each
bulletin has View PDF and Download PDF actions plus an in-page PDF preview on
larger screens.

The plugin stores bulletins as normal WordPress posts with the
`parish_bulletin` post type. The date and PDF attachment ID are post metadata;
e-bulletin content can grow naturally in the existing post content.

== Installation ==

1. Activate Parish Bulletins under Plugins.
2. Go to Bulletins > Add New.
3. Enter a title, date, and PDF, then publish.
4. Add /bulletins/ to the site navigation when ready.

Themes can override the public templates by adding:

* parish-bulletins/archive-parish_bulletin.php
* parish-bulletins/single-parish_bulletin.php

== Warranty ==

Parish Bulletins is provided "as is," without warranty of any kind. To the
extent permitted by applicable law, the authors and copyright holders are not
liable for damages arising from its use or inability to use it. See Sections
15 and 16 of the GNU General Public License version 3 for the complete warranty
disclaimer and limitation of liability.

Third-party components retain their original licenses. See
`THIRD-PARTY-NOTICES.md` and the license files stored with those components.

== Changelog ==

= 1.3.0 =
* License Parish Bulletins under GPL version 3.
* Add the complete GPLv3 license, an explicit warranty disclaimer, and
  third-party notices for PDF.js and its bundled standard fonts.

= 1.2.2 =
* Prepare the current Bulletin archive, PDF viewer, and comment-disabled
  workflow for live-site previewing.

= 1.2.1 =
* Keep comments and pingbacks closed for every Bulletin, including existing
  records and requests made through WordPress APIs.

= 1.2.0 =
* Generate each post title from its Bulletin Date, such as
  `Bulletin - July 5th, 2026`.

= 1.1.0 =
* Redesign the Bulletin archive as a compact responsive card grid.
* Use smaller archive thumbnails and show up to 16 Bulletins per page.

= 1.0.0 =
* Promote the complete PDF-first Bulletin workflow to its first stable release.

= 0.2.0 =
* Use the PDF's generated first-page image as the archive thumbnail when no
  manual featured image is selected.
* Add an embedded PDF.js viewer with page navigation and zoom controls.
* Add a date-based visitor title when a Bulletin is published without a title.

The viewer bundles Mozilla PDF.js 6.1.200 under the Apache License 2.0. Its
license is included at `assets/vendor/pdfjs/LICENSE`.

= 0.1.1 =
* Clarify Draft and Published visibility in the Bulletin editor.
* Explicitly limit the public Bulletin archive to published records.

= 0.1.0 =
* Add PDF-first Bulletin records, Media Library selection, and admin columns.
* Add responsive archive, single view, PDF actions, and desktop PDF preview.
* Reserve the standard WordPress editor for future e-bulletin content.
