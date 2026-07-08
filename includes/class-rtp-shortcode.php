<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * RTP_Shortcode
 *
 * Registers [reading_time] so users can drop the badge anywhere —
 * post content, widgets, or template PHP via do_shortcode().
 *
 * Usage:
 *   [reading_time]
 *   [reading_time id="123"]         -> force a specific post's content
 *   [reading_time wpm="180"]        -> override words-per-minute just here
 */
class RTP_Shortcode {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_shortcode( 'reading_time', array( $this, 'render' ) );
	}

	public function render( $atts ) {
		$atts = shortcode_atts(
			array(
				'id'  => get_the_ID(),
				'wpm' => null,
			),
			$atts,
			'reading_time'
		);

		$post_id = (int) $atts['id'];
		if ( ! $post_id ) {
			return '';
		}

		$post = get_post( $post_id );
		if ( ! $post ) {
			return '';
		}

		$wpm = ( null !== $atts['wpm'] && is_numeric( $atts['wpm'] ) ) ? (int) $atts['wpm'] : null;

		return RTP_Calculator::get_html( $post->post_content, $wpm );
	}
}
