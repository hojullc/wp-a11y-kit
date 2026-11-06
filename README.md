# WP A11y Kit

> WordPress plugin that automatically patches common WCAG 2.2 accessibility issues — skip navigation, ARIA labels, focus styles, heading checks, and a live admin bar score. Zero configuration required.

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-21759b.svg)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb4.svg)](https://php.net)
[![WCAG 2.2](https://img.shields.io/badge/WCAG-2.2%20AA-green.svg)](https://www.w3.org/WAI/WCAG22/quickref/)

Built by [Hoju LLC](https://hoju.us)

---

## What It Does

Install, activate — it starts working immediately. No settings required (though you can tune everything).

| Feature | WCAG Criterion |
|---------|---------------|
| ♿ Skip navigation link (injected as first element) | 2.4.1 |
| 🏷️ Auto-add `aria-label` to `<nav>`, `<header>`, `<footer>`, `<main>` | 1.3.1, 4.1.2 |
| 🌐 Ensure `<html lang>` attribute is set | 3.1.1 |
| 🎯 Focus-visible outline styles (2px, all interactive elements) | 2.4.11 |
| 🖼️ Alt-text audit in Media Library admin | 1.1.1 |
| 📐 Heading hierarchy warning in block editor | 2.4.6 |
| 📊 Live accessibility score in admin bar | — |
| 🔧 Dashboard widget with scan history | — |

---

## Installation

### From GitHub (manual)
1. Download this repo as a ZIP
2. In WordPress admin: **Plugins → Add New → Upload Plugin**
3. Upload the ZIP and activate

### From source
```bash
cd wp-content/plugins
git clone https://github.com/hojullc/wp-a11y-kit.git
```
Then activate in **Plugins → Installed Plugins**.

---

## Settings

**Settings → WP A11y Kit** — toggle each feature on/off:

- ✅ Skip Navigation Link
- ✅ Auto-patch ARIA Labels
- ✅ HTML lang Attribute
- ✅ Focus-Visible Styles
- ✅ Alt Text Audit (Admin)
- ✅ Heading Hierarchy Check
- ✅ Admin Bar A11y Badge

---

## Admin Bar Score

When logged in and browsing your site, the admin bar shows a live accessibility score (0–100). The score is calculated from:
- **−10pts** per error (missing alt, unlabelled form, broken ARIA, etc.)
- **−3pts** per warning
- **−1pt** per notice

Scores are saved to the database and shown in the **Dashboard widget**.

---

## Developer Hooks

### Customize skip-nav text
```php
add_filter( 'wpaak_skip_nav_text', function( $text ) {
    return 'Jump to content';
});
```

### Customize skip-nav target ID
```php
add_filter( 'wpaak_skip_nav_target', function( $id ) {
    return 'page-content'; // defaults to 'main'
});
```

### Act on scan results
```php
add_action( 'wpaak_scan_complete', function( array $result ) {
    // $result = [ 'score' => 87, 'errors' => 1, 'warnings' => 3, ... ]
    if ( $result['score'] < 60 ) {
        // e.g. send a Slack alert
    }
});
```

---

## File Structure

```
wp-a11y-kit/

├── wp-a11y-kit.php              # Plugin bootstrap
├── includes/
│   ├── class-wpaak-fixes.php   # Server-side HTML patches
│   ├── class-wpaak-admin.php    # Settings page + dashboard widget
│   └── class-wpaak-scanner.php  # (placeholder for extended scanner)
├── assets/
│   ├── js/
│   │   └── frontend-scanner.js  # Admin-bar live score + backend report
│   └── css/
│       └── admin.css            # Admin panel styles
└── README.md
```

---

## Requirements

- WordPress 5.8+
- PHP 7.4+

---

## Related

- **[a11y-checker](https://github.com/hojullc/a11y-checker)** — standalone browser-side WCAG scanner (zero deps)
- **[ScanCompliant](https://scancompliant.com)** — full SaaS accessibility monitoring by Hoju LLC
- **[Hoju LLC](https://hoju.us)** — web, mobile & accessibility consulting

---

## License

MIT © [Hoju LLC](https://hoju.us)
