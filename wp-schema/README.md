#WordPress Schema

Requires Advanced Custom Fields Pro

## Features:

* Repeatable field for employee listing
* Repeatable field for locations listing
* Set up for Reviews markup/schema. Markup implemented through custom WordPress action hook.
* Social media "sameas" schema
* Link to review the Schema data in Google's [Structured Data Testing Tool](https://search.google.com/structured-data/testing-tool)
* Open Graph data functionality with metabox for posts and pages (overrides All-in-One SEO data)

## Instructions:

* Upload and activate plugin
* New admin page "RM Schema" will appear on sidebar
* Fill out as much data as possible

## Notes:

* Will override All-in-One SEO and Yoast plugins' schema data
* For Ratings markup to appear, simply add `<?php do_action('reviews_markup'); ?>` hook to any of the WP templates (usually the footer.php template).
* View CHANGELOG.md for full list of changes
