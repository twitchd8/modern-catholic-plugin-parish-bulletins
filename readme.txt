=== Parish Bulletins ===
Contributors: twitchd8
Requires at least: 6.7
Requires PHP: 7.4
Stable tag: 0.2.0
License: GPLv2 or later

Publishes dated parish bulletin PDFs while preserving a natural path toward
full e-bulletin content.

== Description ==

Parish Bulletins adds Dashboard > Bulletins with a focused publishing workflow:

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

== Changelog ==

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
