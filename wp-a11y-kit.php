<?php
/**
 * Plugin Name: WP A11y Kit
 * Plugin URI:  https://github.com/hojullc/wp-a11y-kit
 * Description: Automatically patches common WCAG 2.2 accessibility issues on your WordPress site — skip navigation, ARIA labels, heading checks, contrast flags, and more. Zero configuration required.
 * Version:     1.0.0
 * Author:      Hoju LLC
 * Author URI:  https://hoju.us
 * License:     MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: wp-a11y-kit
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

defined( 'ABSPATH' ) || exit;

define( 'WPAAK_VERSION', '1.0.0' );
define( 'WPAAK_DIR',     plugin_dir_path( __FILE__ ) );
define( 'WPAAK_URL',     plugin_dir_url( __FILE__ ) );

// ─── Autoload includes ───────────────────────────────────────────
require_once WPAAK_DIR . 'includes/class-wpaak-scanner.php';
require_once WPAAK_DIR . 'includes/class-wpaak-admin.php';
require_once WPAAK_DIR . 'includes/class-wpaak-fixes.php';

// ─── Boot ────────────────────────────────────────────────────────
add_action( 'plugins_loaded', 'wpaak_boot' );
function wpaak_boot() {
    new WPAAK_Fixes();
    if ( is_admin() ) {
        new WPAAK_Admin();
    }
}

// ─── Activation ──────────────────────────────────────────────────
register_activation_hook( __FILE__, 'wpaak_activate' );
function wpaak_activate() {
    $defaults = [
        'skip_nav'           => true,
        'aria_labels'        => true,
        'heading_check'      => true,
        'alt_text_audit'     => true,
        'lang_attribute'     => true,
        'focus_styles'       => true,
        'admin_bar_badge'    => true,
        'admin_bar_color'    => '#3fb950',
    ];
    if ( ! get_option( 'wpaak_settings' ) ) {
        add_option( 'wpaak_settings', $defaults );
    }
    flush_rewrite_rules();
}

// ─── Deactivation ────────────────────────────────────────────────
register_deactivation_hook( __FILE__, 'wpaak_deactivate' );
function wpaak_deactivate() {
    flush_rewrite_rules();
}
