<?php declare( strict_types = 1 );
/**
 * Admin_Widget class
 *
 * @author Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version 2.0
 * @package lumiere-movies
 */
namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Tools\Settings_Global;
use WP_Widget;

/**
 * Add a Lumière Widget option in administration
 * It selects either legacy widget (pre-5.8 WordPress) or block-based widget (post-5.8 WordPress), so it is compatible with Classic widget plugin
 *
 * Once this widget is added, it may be used to display both autow title widget and metabox info in a sidebar
 *
 * Constant self::BLOCK_WIDGET_NAME is the post-WP 5.8 widget block name.
 * Constant self::WIDGET_NAME is the pre-WP 5.8 widget name.
 *
 * If calling with 'init' hook, priority must be 0, as the legacy widget doesn't work otherwise
 * widgets_init is started at init 1: https://developer.wordpress.org/reference/hooks/widgets_init/#user-contributed-notes
 * Easier to call it in hook 'widgets_init'!
 *
 * @see \Lumiere\Core that calls it
 * @see \Lumiere\Frontend\Widget_Legacy Call it in frontend which will extend the current class. The current class registers Widget_Legacy widget
 *
 * @extends \WP_Widget<array<string, mixed>>
 */
class Widget_Selection extends WP_Widget {

	/**
	 * Global Frontend trait
	 */
	use Settings_Global;

	/**
	 *  Names of the Widgets
	 */
	const BLOCK_WIDGET_NAME = 'lumiere/widget'; // post-WP 5.8 widget block name.
	const WIDGET_NAME = 'lumiere_movies_widget'; // pre-WP 5.8 widget name.

	/**
	 * Constructor. Sets up the widget name, description, etc.
	 */
	public function __construct() {

		// Get Global Settings class properties from trait Settings_Global.
		$this->get_settings_class();
		$this->get_db_options();

		parent::__construct(
			self::WIDGET_NAME,  // Base ID.
			'Lumière! Widget (legacy)',   // Name.
			[
				'description' => esc_html__( 'Add automatically movie details to your pages with Lumière! Legacy version. As of WordPress 5.8, you rather should use block-based widgets.', 'lumiere-movies' ),
				'show_instance_in_rest' => true, /** use WP REST API */
			]
		);

		add_action( 'widgets_init', [ $this, 'lum_select_widget' ], 11 ); // called in class Core with default priority 10

		/**
		 * Hide the widget in legacy widgets menu
		 * If legacy widget is hidden, when switching from classic to block, legacy widget can't be removed
		 */
		// add_action( 'widget_types_to_hide_from_legacy_widget_block', [ $this, 'lumiere_hide_widget' ] );

	}

	/**
	 * Statically start the class
	 */
	public static function lumiere_static_start(): void {
		$self_class = new self();
	}

	/**
	 * Select which widget between legacy and block-based gutenberg to instanciate
	 *
	 * @info: check if a widget is active in frontend: "is_active_widget( false, false, self::WIDGET_NAME, false ) === true"
		(set last "false" to true to check inactive widgets too)
	 * @info: check if block-based widget is active: "self::lumiere_block_widget_isactive( self::BLOCK_WIDGET_NAME ) === true"
	 * @info: check if block-based widget is registered: \WP_Block_Type_Registry::get_instance()->is_registered( self::BLOCK_WIDGET_NAME )
	 *
	 * @since 4.0 using __CLASS__ instead of get_class() in register_widget()
	 * @since 4.1 replaced __CLASS__ with "Widget_Legacy" in register_widget(), changed the logic of registering the block widget
	 */
	public function lum_select_widget(): void {

		// Can't use is_widget_active in widgets_init hook, so home-made check
		$is_classic_active = in_array( 'classic-widgets/classic-widgets.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ); // @phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		// Register legacy widget if the Classic widget plugin is active, prevents it from appearing in block-based interface, where it's invisible.
		if ( $is_classic_active === true ) {
			add_action(
				'widgets_init',
				function() {
					register_widget( 'Lumiere\Frontend\Widget_Legacy' );
				},
				12
			);
		}

		// Register Block-based Widget by default if no classic widget plugin is available
		add_action( 'widgets_init', [ $this, 'lumiere_register_widget_block' ], 12 );
	}

	/**
	 * Hide Lumière legacy widget from WordPress legacy widgets list
	 * Not in use
	 *
	 * @param array<string> $widget_types
	 * @return array<string>
	 */
	public function lumiere_hide_widget( array $widget_types ): array {

		$widget_types[] = self::WIDGET_NAME;
		return $widget_types;
	}

	/**
	 * Register Block Widget (>= WordPress 5.8)
	 * @since 4.1 Using block.json, removed conditions, which are useless as it doesn't register twice anymore, added translation
	 */
	public function lumiere_register_widget_block(): void {

		register_block_type( dirname( dirname( __DIR__ ) ) . '/assets/blocks/widget/' );
		wp_set_script_translations( 'lumiere-widget-editor-script', 'lumiere-movies', dirname( dirname( __DIR__ ) ) . '/languages/' );
	}

	/**
	 * @inheritdoc
	 * Outputs the settings update form for Legacy widget.
	 *
	 * @see \WP_Widget::form()
	 *
	 * @param array<array-key, mixed> $instance Current settings.
	 * @return string Default return is 'noform'.
	 */
	#[\Override]
	public function form( $instance ): string {

		$title = $instance['title'] ?? '';
		$lumiere_query_widget = $instance['lumiere_queryid_widget'] ?? '';

		$lumiere_queryid_widget_input = $instance['lumiere_queryid_widget_input'] ?? '';

		$output = "\n\t" . '<!-- Lumière movies widget -->';
		$output .= "\n\t" . '<p class="lumiere_padding_ten">';

		$output .= "\n\t" . '<div class="lumiere_display_inline_flex lum_legacy_wrapper">';
		$output .= "\n\t\t" . '<div class="lumiere_padding_ten">';
		$output .= "\n\t\t\t" . '<img class="lumiere_flex_auto" width="40" height="40" src="'
				. esc_url( $this->config_class->lumiere_pics_dir . 'lumiere-ico80x80.png' ) . '" />';
		$output .= "\n\t\t" . '</div>';

		$output .= "\n\t\t" . '<div class="lumiere_flex_auto lumiere_flex_nowrap_container">';
		$output .= "\n\t\t\t" . '<label class="lum_legacy_widget_label" for="' . esc_attr( $this->get_field_id( 'title' ) ) . '">'
					. esc_html__( 'Widget title:', 'lumiere-movies' ) . '</label>';
		$output .= "\n\t\t\t" . '<input class="widefat" id="' . esc_attr( $this->get_field_id( 'title' ) ) . '" name="' . esc_attr( $this->get_field_name( 'title' ) ) . '" type="text" value="' . esc_attr( $title ) . '" />';
		$output .= "\n\t\t\t" . '</div>';
		$output .= "\n\t\t" . '</div>';
		$output .= "\n\t" . '</p><!-- /Lumiere movies widget -->' . "\n\t";

		echo wp_kses(
			$output,
			[
				'div' => [ 'class' => [] ],
				'p' => [ 'class' => [] ],
				'img' => [
					'class' => [],
					'width' => [],
					'height' => [],
					'src' => [],
				],
				'label' => [
					'class' => [],
					'for' => [],
				],
				'input' => [
					'class' => [],
					'id' => [],
					'name' => [],
					'type' => [],
					'value' => [],
				],
			]
		);

		return $output;
	}

	/**
	 * Updates a particular instance of a legacy widget.
	 *
	 * This function checks that `$new_instance` is set correctly. The newly-calculated
	 * value of `$instance` should be returned. If false is returned, the instance won't be
	 * saved/updated.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array<array-key, mixed> $new_instance New settings for this instance as input by the user via WP_Widget::form().
	 * @param array<array-key, mixed> $old_instance Old settings for this instance.
	 * @return array<array-key, mixed> Settings to save or bool false to cancel saving.
	 */
	#[\Override]
	public function update( $new_instance, $old_instance ) {

		$instance = [];

		$instance['title'] = isset( $new_instance['title'] ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		$instance['lumiere_queryid_widget'] = $new_instance['lumiere_queryid_widget'] ?? '';
		$instance['lumiere_queryid_widget_input'] = $new_instance['lumiere_queryid_widget_input'] ?? '';

		return $instance;
	}

	/**
	 * Check if a block widget is active
	 *
	 * @param string $blockname Name of the block to look for
	 * @return bool True if found
	 * @since 4.1 moved from Utils to here
	 */
	public static function lumiere_block_widget_isactive( string $blockname ): bool {
		$widget_blocks = get_option( 'widget_block' );
		foreach ( $widget_blocks as $widget_block ) {
			if (
				( isset( $widget_block['content'] ) && strlen( $widget_block['content'] ) !== 0 )
				&& has_block( $blockname, $widget_block['content'] )
			) {
				return true;
			}
		}
		return false;
	}
}
