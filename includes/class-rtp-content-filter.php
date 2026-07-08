<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * RTP_Content_Filter
 *
 * Hooks into `the_content` to automatically prepend/append the reading
 * time badge, driven entirely by what's configured on the settings page.
 * If the admin picked "manual only", this filter no-ops and the user is
 * expected to use the shortcode or the Gutenberg block instead.
 */
class RTP_Content_Filter {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// Priority 20 so it runs after most other content filters (e.g. shortcodes) have expanded.
		add_filter( 'the_content', array( $this, 'maybe_inject' ), 20 );
	}

	public function maybe_inject( $content ) {
		// Never touch content outside the main query loop or in feeds — avoids duplicate badges.
		if ( is_feed() || ! is_singular() || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		$settings = get_option( 'rtp_settings', array() );
		$position = ! empty( $settings['position'] ) ? $settings['position'] : 'before';

		if ( 'manual' === $position ) {
			return $content;
		}

		$enabled_types = ! empty( $settings['post_types'] ) ? (array) $settings['post_types'] : array( 'post' );
		if ( ! in_array( get_post_type(), $enabled_types, true ) ) {
			return $content;
		}

		/**
		 * Filter: rtp_should_display
		 * Final escape hatch — return false to suppress on a specific post
		 * (e.g. based on a custom field), even if settings say to show it.
		 */
		if ( ! apply_filters( 'rtp_should_display', true, get_the_ID() ) ) {
			return $content;
		}

		$badge = RTP_Calculator::get_html( $content );

		if ( 'after' === $position ) {
			return $content . $badge;
		}

		// Default: before.
		return $badge . $content;
	}
}
