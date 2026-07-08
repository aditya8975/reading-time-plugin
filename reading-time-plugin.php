<?php
/**
 * Plugin Name:       Reading Time Plugin
 * Plugin URI:        https://github.com/YOUR-USERNAME/reading-time-plugin
 * Description:       Automatically calculates and displays estimated reading time for posts/pages. Includes admin settings, a shortcode, and a native Gutenberg block.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Aditya / Adimate
 * Author URI:        https://adimate.in
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       reading-time-plugin
 * Domain Path:       /languages
 */

// Block direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---- Constants -------------------------------------------------------
define( 'RTP_VERSION', '1.0.0' );
define( 'RTP_PLUGIN_FILE', __FILE__ );
define( 'RTP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'RTP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'RTP_DEFAULT_WPM', 200 ); // average adult reading speed, words per minute.

// ---- Includes ----------------------------------------------------------
require_once RTP_PLUGIN_DIR . 'includes/class-rtp-calculator.php';
require_once RTP_PLUGIN_DIR . 'includes/class-rtp-admin.php';
require_once RTP_PLUGIN_DIR . 'includes/class-rtp-shortcode.php';
require_once RTP_PLUGIN_DIR . 'includes/class-rtp-content-filter.php';
require_once RTP_PLUGIN_DIR . 'includes/class-rtp-block.php';

/**
 * Core plugin bootstrap class. Wires up every module.
 * Kept intentionally thin — each responsibility lives in its own class.
 */
final class Reading_Time_Plugin {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'init', array( $this, 'init_modules' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_front_assets' ) );
	}

	public function load_textdomain() {
		load_plugin_textdomain( 'reading-time-plugin', false, dirname( plugin_basename( RTP_PLUGIN_FILE ) ) . '/languages' );
	}

	public function init_modules() {
		// Admin settings screen (only matters in wp-admin, class checks internally).
		RTP_Admin::instance();

		// [reading_time] shortcode.
		RTP_Shortcode::instance();

		// Auto-inject into the_content based on settings.
		RTP_Content_Filter::instance();

		// Gutenberg block registration.
		RTP_Block::instance();
	}

	public function enqueue_front_assets() {
		wp_register_style( 'rtp-style', RTP_PLUGIN_URL . 'assets/css/reading-time.css', array(), RTP_VERSION );
		wp_enqueue_style( 'rtp-style' );
	}
}

/**
 * Runs on activation. Seeds sane defaults so the plugin
 * works immediately without a trip to the settings page.
 */
function rtp_activate() {
	$defaults = array(
		'wpm'          => RTP_DEFAULT_WPM,
		'position'     => 'before', // before | after | manual
		'post_types'   => array( 'post' ),
		'text_singular' => __( '%d min read', 'reading-time-plugin' ),
		'icon'         => 'yes',
	);
	if ( false === get_option( 'rtp_settings' ) ) {
		add_option( 'rtp_settings', $defaults );
	}
}
register_activation_hook( RTP_PLUGIN_FILE, 'rtp_activate' );

/**
 * Runs on deactivation. Deliberately does NOT delete settings —
 * that only happens in uninstall.php if the user removes the plugin.
 */
function rtp_deactivate() {
	// Nothing destructive here on purpose.
}
register_deactivation_hook( RTP_PLUGIN_FILE, 'rtp_deactivate' );

// Boot the plugin.
Reading_Time_Plugin::instance();
