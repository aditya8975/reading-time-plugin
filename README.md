# Reading Time Plugin

A lightweight WordPress plugin that calculates and displays estimated reading
time on posts and pages — settings page, `[reading_time]` shortcode, and a
native Gutenberg block. No build tools, no dependencies, plain PHP/CSS/JS.

## Features

- Auto-inject before/after content, or manual-only mode
- Configurable WPM, text format, clock icon, per-post-type control
- `[reading_time]` shortcode (`[reading_time id="123" wpm="180"]`)
- Native Gutenberg block with live server-side-rendered preview
- Filters for developers: `rtp_word_count`, `rtp_reading_minutes`,
  `rtp_display_text`, `rtp_reading_time_html`, `rtp_should_display`

## Local install (no build step)

```bash
git clone https://github.com/YOUR-USERNAME/reading-time-plugin.git
cp -r reading-time-plugin /path/to/wordpress/wp-content/plugins/
```

Then in wp-admin: **Plugins > Activate**, then **Settings > Reading Time**.

## Plugin structure

```
reading-time-plugin/
├── reading-time-plugin.php        # Bootstrap: header, constants, activation hooks
├── uninstall.php                  # Cleanup on delete (not on deactivate)
├── includes/
│   ├── class-rtp-calculator.php   # Pure word-count / minutes logic + filters
│   ├── class-rtp-admin.php        # Settings API page (Settings > Reading Time)
│   ├── class-rtp-shortcode.php    # [reading_time] shortcode
│   ├── class-rtp-content-filter.php  # Auto the_content injection
│   └── class-rtp-block.php        # Gutenberg block registration (PHP side)
├── blocks/reading-time/
│   ├── block.json                 # Block metadata (API v3)
│   └── index.js                   # Editor UI (plain JS, no JSX/webpack)
├── assets/css/
│   ├── reading-time.css           # Front-end badge styling
│   └── admin.css                  # Settings page styling
└── readme.txt                     # WordPress.org-format readme
```

## Development notes

- **No build step by design.** The block's `index.js` is written against
  the `wp.*` globals with `wp.element.createElement` instead of JSX, so it
  runs as-is. If you'd rather write JSX, add `@wordpress/scripts` and run
  `wp-scripts build`, then point `editor_script` in
  `includes/class-rtp-block.php` at the compiled output + its generated
  `index.asset.php` dependency list.
- All user input is sanitized in `RTP_Admin::sanitize()`; all output is
  escaped (`esc_html`, `esc_attr`) before printing.
- The calculator strips shortcodes/HTML before counting words so markup
  doesn't inflate the estimate.

## Deployment options

**A. WordPress.org (public plugin directory)**
1. Create an account at wordpress.org, then submit the plugin for review
   at https://wordpress.org/plugins/developers/add/ (upload as a zip of
   this folder). Approval grants you SVN repo access.
2. Once approved, `svn checkout` the assigned repo, copy files into
   `/trunk`, add screenshots to `/assets`, `svn commit`, then tag the
   release (`svn cp trunk tags/1.0.0`).
3. Users install via wp-admin's built-in plugin search — zero manual
   upload needed after that.

**B. GitHub + manual/zip install (what most freelance/client plugins use)**
1. Push this repo to GitHub as-is.
2. For releases, zip the folder (excluding `.git`, `.github`) and attach
   it to a GitHub Release, e.g.:
   ```bash
   cd reading-time-plugin && zip -r ../reading-time-plugin-1.0.0.zip . -x ".git/*"
   ```
3. Client/user installs via **Plugins > Add New > Upload Plugin** in
   wp-admin, selecting the zip.

**C. GitHub + auto-updates (no WordPress.org listing)**
Add a lightweight updater so the plugin checks your GitHub Releases for
new versions and offers "Update now" inside wp-admin, same as a
WordPress.org plugin would. Two common options:
- [`YahnisElsts/plugin-update-checker`](https://github.com/YahnisElsts/plugin-update-checker) —
  drop the library in, point it at your repo URL, done.
- A custom `pre_set_site_transient_update_plugins` filter that calls the
  GitHub Releases API (`GET /repos/:owner/:repo/releases/latest`) and
  compares tag versions to `RTP_VERSION`.

**D. Direct server deploy (staging/production without wp-admin upload)**
```bash
scp -r reading-time-plugin user@yourserver:/var/www/html/wp-content/plugins/
ssh user@yourserver "wp plugin activate reading-time-plugin"   # via WP-CLI
```

## License

GPLv2 or later — required for anything distributed on WordPress.org, and
the conventional choice for WordPress plugins generally.
