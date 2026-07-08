<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * RTP_Admin
 *
 * Adds a settings screen under Settings > Reading Time using the
 * WordPress Settings API (register_setting / add_settings_section /
 * add_settings_field) — no hand-rolled form handling, no custom nonces
 * needed beyond what settings_fields() already provides.
 */
class RTP_Admin {

	private static $instance = null;
	const OPTION_GROUP = 'rtp_settings_group';
	const OPTION_NAME  = 'rtp_settings';

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( RTP_PLUGIN_FILE ), array( $this, 'add_settings_link' ) );
	}

	public function add_settings_page() {
		add_options_page(
			__( 'Reading Time Settings', 'reading-time-plugin' ),
			__( 'Reading Time', 'reading-time-plugin' ),
			'manage_options',
			'reading-time-plugin',
			array( $this, 'render_settings_page' )
		);
	}

	public function add_settings_link( $links ) {
		$settings_link = '<a href="' . admin_url( 'options-general.php?page=reading-time-plugin' ) . '">' . __( 'Settings', 'reading-time-plugin' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	public function enqueue_admin_assets( $hook ) {
		if ( 'settings_page_reading-time-plugin' !== $hook ) {
			return;
		}
		wp_enqueue_style( 'rtp-admin-style', RTP_PLUGIN_URL . 'assets/css/admin.css', array(), RTP_VERSION );
	}

	public function register_settings() {
		register_setting(
			self::OPTION_GROUP,
			self::OPTION_NAME,
			array(
				'sanitize_callback' => array( $this, 'sanitize' ),
				'default'           => array(
					'wpm'           => RTP_DEFAULT_WPM,
					'position'      => 'before',
					'post_types'    => array( 'post' ),
					'text_singular' => __( '%d min read', 'reading-time-plugin' ),
					'icon'          => 'yes',
				),
			)
		);

		add_settings_section(
			'rtp_main_section',
			__( 'General Settings', 'reading-time-plugin' ),
			function () {
				echo '<p>' . esc_html__( 'Control how reading time is calculated and displayed across your site.', 'reading-time-plugin' ) . '</p>';
			},
			'reading-time-plugin'
		);

		add_settings_field( 'rtp_wpm', __( 'Words per minute', 'reading-time-plugin' ), array( $this, 'field_wpm' ), 'reading-time-plugin', 'rtp_main_section' );
		add_settings_field( 'rtp_position', __( 'Display position', 'reading-time-plugin' ), array( $this, 'field_position' ), 'reading-time-plugin', 'rtp_main_section' );
		add_settings_field( 'rtp_post_types', __( 'Enable on', 'reading-time-plugin' ), array( $this, 'field_post_types' ), 'reading-time-plugin', 'rtp_main_section' );
		add_settings_field( 'rtp_text', __( 'Text format', 'reading-time-plugin' ), array( $this, 'field_text' ), 'reading-time-plugin', 'rtp_main_section' );
		add_settings_field( 'rtp_icon', __( 'Show clock icon', 'reading-time-plugin' ), array( $this, 'field_icon' ), 'reading-time-plugin', 'rtp_main_section' );
	}

	private function get_settings() {
		return wp_parse_args(
			get_option( self::OPTION_NAME, array() ),
			array(
				'wpm'           => RTP_DEFAULT_WPM,
				'position'      => 'before',
				'post_types'    => array( 'post' ),
				'text_singular' => __( '%d min read', 'reading-time-plugin' ),
				'icon'          => 'yes',
			)
		);
	}

	public function field_wpm() {
		$s = $this->get_settings();
		printf(
			'<input type="number" min="50" max="1000" step="10" name="%1$s[wpm]" value="%2$d" class="small-text" /> <p class="description">%3$s</p>',
			esc_attr( self::OPTION_NAME ),
			(int) $s['wpm'],
			esc_html__( 'Average adult reading speed is ~200-250 wpm.', 'reading-time-plugin' )
		);
	}

	public function field_position() {
		$s = $this->get_settings();
		$options = array(
			'before' => __( 'Before content (automatic)', 'reading-time-plugin' ),
			'after'  => __( 'After content (automatic)', 'reading-time-plugin' ),
			'manual' => __( 'Manual only (shortcode or block)', 'reading-time-plugin' ),
		);
		echo '<select name="' . esc_attr( self::OPTION_NAME ) . '[position]">';
		foreach ( $options as $value => $label ) {
			printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $value ), selected( $s['position'], $value, false ), esc_html( $label ) );
		}
		echo '</select>';
	}

	public function field_post_types() {
		$s = $this->get_settings();
		$selected_types = (array) $s['post_types'];
		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		foreach ( $post_types as $pt ) {
			printf(
				'<label style="display:inline-block;margin-right:14px;"><input type="checkbox" name="%1$s[post_types][]" value="%2$s" %3$s /> %4$s</label>',
				esc_attr( self::OPTION_NAME ),
				esc_attr( $pt->name ),
				checked( in_array( $pt->name, $selected_types, true ), true, false ),
				esc_html( $pt->label )
			);
		}
	}

	public function field_text() {
		$s = $this->get_settings();
		printf(
			'<input type="text" name="%1$s[text_singular]" value="%2$s" class="regular-text" /> <p class="description">%3$s</p>',
			esc_attr( self::OPTION_NAME ),
			esc_attr( $s['text_singular'] ),
			esc_html__( 'Use %d as a placeholder for the number of minutes, e.g. "%d min read".', 'reading-time-plugin' )
		);
	}

	public function field_icon() {
		$s = $this->get_settings();
		printf(
			'<label><input type="checkbox" name="%1$s[icon]" value="yes" %2$s /> %3$s</label>',
			esc_attr( self::OPTION_NAME ),
			checked( $s['icon'], 'yes', false ),
			esc_html__( 'Display a small clock icon next to the text', 'reading-time-plugin' )
		);
	}

	/**
	 * Sanitize every field on save. Never trust raw $_POST data.
	 */
	public function sanitize( $input ) {
		$output = array();
		$output['wpm']           = isset( $input['wpm'] ) ? max( 50, min( 1000, (int) $input['wpm'] ) ) : RTP_DEFAULT_WPM;
		$output['position']      = isset( $input['position'] ) && in_array( $input['position'], array( 'before', 'after', 'manual' ), true ) ? $input['position'] : 'before';
		$output['post_types']    = isset( $input['post_types'] ) ? array_map( 'sanitize_key', (array) $input['post_types'] ) : array();
		$output['text_singular'] = isset( $input['text_singular'] ) ? sanitize_text_field( $input['text_singular'] ) : '%d min read';
		$output['icon']          = isset( $input['icon'] ) ? 'yes' : 'no';
		return $output;
	}

	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap rtp-settings-wrap">
			<h1><?php esc_html_e( 'Reading Time Settings', 'reading-time-plugin' ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( self::OPTION_GROUP );
				do_settings_sections( 'reading-time-plugin' );
				submit_button();
				?>
			</form>

			<div class="rtp-usage-box">
				<h2><?php esc_html_e( 'Manual usage', 'reading-time-plugin' ); ?></h2>
				<p><?php esc_html_e( 'Shortcode:', 'reading-time-plugin' ); ?> <code>[reading_time]</code></p>
				<p><?php esc_html_e( 'Or use the "Reading Time" block in the block editor.', 'reading-time-plugin' ); ?></p>
			</div>
		</div>
		<?php
	}
}
