<?php
/**
 * WPAAK_Admin — Settings page and dashboard widget.
 */
class WPAAK_Admin {

    public function __construct() {
        add_action( 'admin_menu',            [ $this, 'add_menu' ] );
        add_action( 'admin_init',            [ $this, 'register_settings' ] );
        add_action( 'wp_dashboard_setup',    [ $this, 'add_dashboard_widget' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
        add_action( 'wp_ajax_wpaak_report',  [ $this, 'handle_ajax_report' ] );
    }

    public function add_menu(): void {
        add_options_page( __( 'WP A11y Kit', 'wp-a11y-kit' ), __( 'WP A11y Kit', 'wp-a11y-kit' ), 'manage_options', 'wp-a11y-kit', [ $this, 'render_settings_page' ] );
    }

    public function register_settings(): void {
        register_setting( 'wpaak_settings_group', 'wpaak_settings', [ 'sanitize_callback' => [ $this, 'sanitize_settings' ] ] );
    }

    public function sanitize_settings( array $input ): array {
        return [ 'skip_nav' => ! empty( $input['skip_nav'] ), 'aria_labels' => ! empty( $input['aria_labels'] ), 'heading_check' => ! empty( $input['heading_check'] ), 'alt_text_audit' => ! empty( $input['alt_text_audit'] ), 'lang_attribute' => ! empty( $input['lang_attribute'] ), 'focus_styles' => ! empty( $input['aocus_styles'] ), 'admin_bar_badge' => ! empty( $input['admin_bar_badge'] ) ];
    }

    public function render_settings_page(): void {
        $opts = get_option( 'wpaak_settings', [] ); ?>
        <div class="wrap"><h1>♿ WP A11y Kit</h1>
        <form method="post" action="options.php"><?php settings_fields( 'wpaak_settings_group' ); ?>
        <table class="form-table"><?php
        $features = [ 'skip_nav' => [ __( 'Skip Navigation Link', 'wp-a11y-kit' ), __( 'Inject a Skip to main content link. WCAG 2.4.1.', 'wp-a11y-kit' ) ], 'aria_labels' => [ __( 'Auto-patch ARIA Labels', 'wp-a11y-kit' ), __( 'Add aria-label to nav, main, header, footer.', 'wp-a11y-kit' ) ], 'lang_attribute' => [ __( 'HTML lang Attribute', 'wp-a11y-kit' ), __( 'Ensure html has a lang attribute.', 'wp-a11y-kit' ) ], 'focus_styles' => [ __( 'Focus-Visible Styles', 'wp-a11y-kit' ), __( 'Inject a 2px focus outline. WCAG 2.4.11.', 'wp-a11y-kit' ) ], 'alt_text_audit' => [ __( 'Alt Text Audit', 'wp-a11y-kit' ), __( 'Flag images missing alt in Media Library.', 'wp-a11y-kit' ) ], 'heading_check' => [ __( 'Heading Hierarchy Check', 'wp-a11y-kit' ), __( 'Warn when headings skip levels.', 'wp-a11y-kit' ) ], 'admin_bar_badge' => [ __( 'Admin Bar A11y Badge', 'wp-a11y-kit' ), __( 'Show live accessibility score in admin bar.', 'wp-a11y-kit' ) ] ];
        foreach ( $features as $key => [$label, $desc] ) { $checked = ! empty( $opts[ $key ] ) ? 'checked' : ''; ?>
        <tr><th scope="row"><?php echo esc_html( $label ); ?></th><td><label><input type="checkbox" name="wpaak_settings[<?php echo esc_attr( $key ); ?>]" value="1" <?php echo $checked; ?>> <span class="description"><?php echo esc_html( $desc ); ?></span></label></td></tr>
        <?php } ?></table><?php submit_button(); ?></form></div><?php }

    public function add_dashboard_widget(): void {
        wp_add_dashboard_widget( 'wpaak_dashboard_widget', __( '♿ Accessibility Status', 'wp-a11y-kit' ), [ $this, 'render_dashboard_widget' ] );
    }

    public function render_dashboard_widget(): void {
        $last = get_transient( 'wpaak_last_scan' ); ?>
        <div><?php if ( $last ) { echo '<div style="font-size:2.5rem;font-weight:900;color:#3fb950">' . intval( $last['score'] ) . '/100</div>'; } else { _e( 'No scan data yet. Visit your site while logged in.', 'wp-a11y-kit' ); } ?>
        <a href="<?php echo admin_url( 'options-general.php?page=wp-a11y-kit' ); ?>" class="button"><?php _e( 'Settings', 'wp-a11y-kit' ); ?></a></div><?php }

    public function enqueue_admin_assets( string $hook ): void {
        wp_enqueue_style( 'wpaak-admin', WPAAK_URL . 'assets/css/admin.css', [], WPAAK_VERSION );
    }

    public function handle_ajax_report(): void {
        check_ajax_referer( 'wpaak_report', 'nonce' );
        if ( ! current_user_can( 'edit_posts' ) ) wp_die( 'Unauthorized', 403 );
        $data = json_decode( stripslashes( $_POST['report'] ?? '' ), true );
        if ( ! $data ) wp_send_json_error( 'Invalid report data' );
        $scan = [ 'score' => intval( $data['score'] ?? 0 ), 'errors' => intval( $data['errors'] ?? 0 ), 'warnings' => intval( $data['warnings'] ?? 0 ), 'notices' => intval( $data['notices'] ?? 0 ), 'url' => esc_url( $data['url'] ?? home_url() ), 'time' => time() ];
        set_transient( 'wpaak_last_scan', $scan, DAY_IN_SECONDS );
        wp_send_json_success( $scan );
    }
}
