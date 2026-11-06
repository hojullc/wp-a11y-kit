<?php
/**
 * WPAAK_Scanner — Extended server-side accessibility scanner.
 */
class WPAAK_Scanner {

    private array $settings;

    public function __construct() {
        $this->settings = wp_parse_args( get_option( 'wpaak_settings', [] ), [ 'alt_text_audit' => true, 'heading_check' => true ] );
        if ( $this->settings['alt_text_audit'] ) {
            add_filter( 'manage_media_columns', [ $this, 'add_alt_column' ] );
            add_action( 'manage_media_custom_column', [ $this, 'render_alt_column' ], 10, 2 );
            add_action( 'admin_head', [ $this, 'alt_column_style' ] );
        }
    }

    public function add_alt_column( array $columns ): array {
        $columns['wpaak_alt'] = __( 'Alt Text', 'wp-a11y-kit' );
        return $columns;
    }

    public function render_alt_column( string $column, int $post_id ): void {
        if ( $column !== 'wpaak_alt' ) return;
        $alt  = get_post_meta( $post_id, '_wp_attachment_image_alt', true );
        $mime = get_post_mime_type( $post_id );
        if ( strpos( $mime, 'image/' ) === false ) { echo '<span>—</span>'; return; }
        if ( ! empty( $alt ) ) { echo '<span style="color:#3fb950">✔  . esc_html( wp_trim_words( $alt, 6 ) ) . '</span>'; }
        else { echo '<span style="color:#f78166">✘ Missing</span>';; }
    }

    public function alt_column_style(): void {
        $screen = get_current_screen();
        if ( ! $screen || $screen->base !== 'upload' ) return;
        echo '<style>.column-wpaak_alt { width: 180px; }</style>';
    }

    public function scan_post( $post ): array {
        $post    = get_post( $post );
        if ( ! $post ) return [ 'post_id' => 0, 'issues' => [], 'score' => 100 ];
        $content = $post->post_content;
        $issues  = [];
        preg_match_all( '/<img([^>]*)>/i' , $content, $m );
        foreach ( $m[1] as $attrs ) { if ( stripos( $attrs, 'alt=' ) === false ) { $issues[] = [ 'type' => 'img-missing-alt', 'severity' => 'error', 'description' => 'Image missing alt attribute', 'wcag' => '1.1.1' ]; } }
        $errors = count( $issues ); $score = max( 0, 100 - $errors * 10 );
        return [ 'post_id' => $post->ID, 'issues' => $issues, 'errors' => $errors, 'score' => $score ];
    }
}
