=== Reading Time Plugin ===
Contributors: adimate
Tags: reading time, read time, blog, gutenberg block, shortcode
Requires at least: 5.8
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatically calculates and displays estimated reading time on posts and pages, with a settings page, shortcode, and native Gutenberg block.

== Description ==

Reading Time Plugin adds a simple "X min read" badge to your content, calculated from the actual word count and a configurable words-per-minute rate.

**Features**

* Automatic display before or after post content (or manual-only mode)
* Configurable words per minute, display text, and optional clock icon
* Per-post-type control (posts, pages, custom post types)
* `[reading_time]` shortcode for use anywhere shortcodes are supported
* Native Gutenberg block ("Reading Time") with a live editor preview
* Developer filters: `rtp_word_count`, `rtp_reading_minutes`, `rtp_display_text`, `rtp_reading_time_html`, `rtp_should_display`
* No build tools required — plain PHP/CSS/JS, easy to audit and extend

== Installation ==

1. Upload the `reading-time-plugin` folder to `/wp-content/plugins/`.
2. Activate the plugin through the "Plugins" screen in WordPress.
3. Go to **Settings > Reading Time** to configure words-per-minute, position, and post types.
4. Optionally use `[reading_time]` or the "Reading Time" block anywhere you want manual placement.

== Frequently Asked Questions ==

= Does this slow down my site? =
No. The calculation is a simple word count against post content that's already loaded in the page request — no external requests, no database writes on the front end.

= Can I change the wording? =
Yes, under Settings > Reading Time, "Text format" accepts a string with `%d` as the minutes placeholder, e.g. `Est. %d minute read`.

= Can developers hook into this? =
Yes — see the filters listed in the Description section above.

== Changelog ==

= 1.0.0 =
* Initial release: settings page, shortcode, content filter, Gutenberg block.
