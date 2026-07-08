<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * RTP_Block
 *
 * Registers the native "Reading Time" Gutenberg block using block.json
 * (the modern, recommended registration method since WP 5.8+). This is
 * a *dynamic* block — it has no saved markup in post_content; instead
 * render_callback runs on every page load, so edits to admin settings
 * (WPM, text format, icon) are reflected immediately without re-saving
 * every post that uses the block.
 */
class RTP_Block {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'register_editor_script' ), 5 );
		add_action( 'init', array( $this, 'register_block' ) );
	}

	/**
	 * Manually register the editor script with explicit dependencies.
	 * No build tooling (webpack/wp-scripts) is used in this plugin, so we
	 * can't rely on an auto-generated index.asset.php for dependency
	 * discovery — declaring them by hand keeps the plugin a plain, portable
	 * set of PHP/JS/CSS files.
	 */
	public function register_editor_script() {
		wp_register_script(
			'rtp-block-editor',
			RTP_PLUGIN_URL . 'blocks/reading-time/index.js',
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-i18n', 'wp-server-side-render', 'wp-components' ),
			RTP_VERSION,
			true
		);
	}

	public function register_block() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return; // WP too old for block editor support.
		}

		register_block_type(
			RTP_PLUGIN_DIR . 'blocks/reading-time',
			array(
				'editor_script'    => 'rtp-block-editor',
				'render_callback'  => array( $this, 'render' ),
			)
		);
	}

	/**
	 * Server-side render callback. $attributes come from the block editor;
	 * $content is empty for this block (no InnerBlocks).
	 */
	public function render( $attributes, $content, $block ) {
		$post_id = ! empty( $block->context['postId'] ) ? (int) $block->context['postId'] : get_the_ID();
		$post    = $post_id ? get_post( $post_id ) : null;

		if ( ! $post ) {
			return '';
		}

		$wpm = ! empty( $attributes['customWpm'] ) ? (int) $attributes['customWpm'] : null;

		$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => 'rtp-block' ) );

		return sprintf(
			'<div %1$s>%2$s</div>',
			$wrapper_attributes,
			RTP_Calculator::get_html( $post->post_content, $wpm )
		);
	}
}
