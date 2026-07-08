<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * RTP_Calculator
 *
 * Pure calculation logic, deliberately decoupled from WordPress display
 * concerns so it can be unit-tested or reused (e.g. by the shortcode,
 * the content filter, and the Gutenberg block all calling the same method).
 */
class RTP_Calculator {

	/**
	 * Calculate reading time in whole minutes (minimum 1) for a block of content.
	 *
	 * @param string   $content Raw or rendered post content.
	 * @param int|null $wpm     Words per minute override. Falls back to saved setting.
	 * @return int Minutes, rounded up, minimum 1.
	 */
	public static function get_minutes( $content, $wpm = null ) {
		if ( null === $wpm ) {
			$settings = get_option( 'rtp_settings', array() );
			$wpm      = ! empty( $settings['wpm'] ) ? (int) $settings['wpm'] : RTP_DEFAULT_WPM;
		}

		$wpm = max( 1, (int) $wpm );

		// Strip shortcodes/tags before counting so markup doesn't inflate the count.
		$plain_text = wp_strip_all_tags( strip_shortcodes( $content ) );
		$word_count = str_word_count( $plain_text );

		/**
		 * Filter: rtp_word_count
		 * Lets other plugins/themes adjust the counted word total
		 * (e.g. to add extra weight for embedded video/galleries).
		 */
		$word_count = apply_filters( 'rtp_word_count', $word_count, $content );

		$minutes = (int) ceil( $word_count / $wpm );
		$minutes = max( 1, $minutes );

		/**
		 * Filter: rtp_reading_minutes
		 * Final say on the computed minute value before it's displayed anywhere.
		 */
		return (int) apply_filters( 'rtp_reading_minutes', $minutes, $word_count, $wpm );
	}

	/**
	 * Build the fully formatted, translated display string, e.g. "4 min read".
	 *
	 * @param string $content
	 * @param int|null $wpm
	 * @return string
	 */
	public static function get_display_text( $content, $wpm = null ) {
		$minutes  = self::get_minutes( $content, $wpm );
		$settings = get_option( 'rtp_settings', array() );
		$format   = ! empty( $settings['text_singular'] ) ? $settings['text_singular'] : __( '%d min read', 'reading-time-plugin' );

		$text = sprintf( $format, $minutes );

		/**
		 * Filter: rtp_display_text
		 * Full control over the final string, e.g. to add a screen-reader prefix.
		 */
		return apply_filters( 'rtp_display_text', $text, $minutes );
	}

	/**
	 * Render the full HTML badge (icon + text), used everywhere the time is shown.
	 *
	 * @param string $content
	 * @param int|null $wpm
	 * @return string HTML
	 */
	public static function get_html( $content, $wpm = null ) {
		$settings  = get_option( 'rtp_settings', array() );
		$show_icon = empty( $settings['icon'] ) || 'yes' === $settings['icon'];
		$text      = self::get_display_text( $content, $wpm );

		$icon_svg = $show_icon
			? '<span class="rtp-icon" aria-hidden="true">'
				. '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>'
				. '</span>'
			: '';

		$html = sprintf(
			'<span class="rtp-reading-time">%s<span class="rtp-text">%s</span></span>',
			$icon_svg,
			esc_html( $text )
		);

		/**
		 * Filter: rtp_reading_time_html
		 * Complete override of the markup, e.g. to match a theme's own badge component.
		 */
		return apply_filters( 'rtp_reading_time_html', $html, $text );
	}
}
