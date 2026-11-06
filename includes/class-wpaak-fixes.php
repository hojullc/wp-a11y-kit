<?php
/**
 * WPAAK_Fixes — Server-side accessibility patches applied to every page load.
 */
class WPAAK_Fixes {

    private array $settings;

    public function __construct() {
        $this->settings = wp_parse_args(
            get_option( 'wpaak_settings', [] ),
            [
                'skip_nav'       => true,
                'aria_labels'    => true,
                'lang_attribute' => true,
                'focus_styles'   => true,
            ]
        );

        // Skip navigation link
        if ( $this->settings['skip_nav'] ) {
            add_action( 'wp_body_open', [ $this, 'inject_skip_nav' ], 1 );
        }

        // <html lang> attribute
        if ( $this->settings['lang_attribute'] ) {
            add_filter( 'language_attributes', [ $this, 'ensure_lang_attribute' ] );
        }

        // Focus-visible styles
        if ( $this->settings['focus_styles'] ) {
            add_action( 'wp_head', [ $this, 'inject_focus_styles' ], 999 );
        }

        // ARIA patches via output buffering
        if ( $this->settings['aria_labels'] ) {
            add_action( 'template_redirect', [ $this, 'start_buffer' ] );
            add_action( 'shutdown', [ $this, 'end_buffer' ], 0 );
        }

        // Enqueue frontend scanner JS (admin bar badge)
        $opts = get_option( 'wpaak_settings', [] );
        if ( ! empty( $opts['admin_bar_badge'] ) && current_user_can( 'edit_posts' ) ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scanner' ] );
            add_action( 'admin_bar_menu',     [ $this, 'add_admin_bar_node' ], 999 );
        }
    }

    /* ── Skip Navigation ── */
    public function inject_skip_nav(): void {
        $main_id = apply_filters( 'wpaak_skip_nav_target', 'main' );
        echo '<a class="wpaak-skip-nav" href="#' . esc_attr( $main_id ) . '">'
           . esc_html( apply_filters( 'wpaak_skip_nav_text', __( 'Skip to main content', 'wp-a11y-kit' ) ) )
           . '</a>';
        echo '<style>
            .wpaak-skip-nav {
                position: absolute;
                top: -100%;
                left: 0;
                z-index: 99999;
                padding: 12px 20px;
                background: #0a2540;
                color: #fff;
                font-weight: 700;
                font-size: 14px;
                text-decoration: none;
                border-radius: 0 0 8px 0;
                transition: top 0.2s;
            }
            .wpaak-skip-nav:focus {
                top: 0;
                outline: 3px solid #635bff;
                outline-offset: 2px;
            }
        </style>';
    }

    /* ── Lang Attribute ── */
    public function ensure_lang_attribute( string $output ): string {
        if ( strpos( $output, 'lang=' ) === false ) {
            $lang = get_bloginfo( 'language' ) ?: 'en';
            $output .= ' lang="' . esc_attr( $lang ) . '"';
        }
        return $output;
    }

    /* ── Focus Visible Styles ── */
    public function inject_focus_styles(): void {
        echo '<style id="wpaak-focus-styles">
            /* WP A11y Kit — WCAG 2.4.11 Focus Not Obscured */
            :focus-visible {
                outline: 2px solid #635bff !important;
                outline-offset: 3px !important;
                border-radius: 3px;
            }
            /* Remove default only when :focus-visible is supported */
            :focus:not(:focus-visible) {
                outline: none;
            }
        </style>';
    }

    /* ── Output Buffer for ARIA patches ── */
    public function start_buffer(): void {
        if ( ! is_admin() ) {
            ob_start( [ $this, 'patch_html' ] );
        }
    }

    public function end_buffer(): void {
        if ( ob_get_level() > 0 ) {
            ob_end_flush();
        }
    }

    /**
     * Apply server-side HTML patches.
     * Runs on the final page output before it's sent to the browser.
     */
    public function patch_html( string $html ): string {
        if ( empty( $html ) ) return $html;

        // 1. Ensure menu naw elements have aria-label
        $html = preg_replace_callback(
            '/<nav([^>]*)>/i',
            function ( $matches ) {
                $attrs = $matches[1];
                if ( stripos( $attrs, 'aria-label' ) === false && stripos( $attrs, 'aria-labelledby' ) === false ) {
                    if ( preg_match( '/class=["\'][^"\']*primary[^"\']*["\']/', $attrs ) ) {
                        $label = __( 'Primary navigation', 'wp-a11y-kit' );
                    } elseif ( preg_match( '/class=["\'][^"\']*footer[^J"\']*["\']/', $attrs ) ) {
                        $label = __( 'Footer navigation', 'wp-a11y-kit' );
                    } else {
                        $label = __( 'Site navigation', 'wp-a11y-kit' );
                    }
                    $attrs .= ' aria-label="' . esc_attr( $label ) . '"';
                }
                return '<nav' . $attrs . '>';
            },
            $html
        );
        return $html;
    }

    public function enqueue_scanner(): void {
        wp_enqueue_script( 'wpaak-scanner', WPAAK_URL . 'assets/js/frontend-scanner.js', [], WPAAK_VERSION, true );
        wp_localize_script( 'wpaak-scanner', 'wpaakData', [ 'ajaxUrl' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'wpaak_report' ), 'version' => WPAAK_VERSION ] );
    }

    public function add_admin_bar_node( \WP_Admin_Bar $bar ): void {
        $bar->add_node( [ 'id' => 'wpaak-badge', 'title' => '<span id="wpaak-ab-label">♿ A11y</span>', 'href' => admin_url( 'options-general.php?page=wp-a11y-kit' ), 'meta' => [ 'title' => __( 'WP A11y Kit — Click to open settings', 'wp-a11y-kit' ) ] ] );
    }
}
