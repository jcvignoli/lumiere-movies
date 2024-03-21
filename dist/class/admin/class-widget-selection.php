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
	wp_die( esc_html__( 'Lumière Movies: You can not call directly this page', 'lumiere-movies' ) );
}

use Lumiere\Settings;
use Lumiere\Tools\Utils;
use Lumiere\Tools\Settings_Global;
use WP_Widget;

/**
 * Add a Lumière Widget option in administration
 * It selects either legacy widget (pre-5.8 WordPress) or block-based widget (post-5.8 WordPress), so it is compatible with Classic widget plugin
 *
 * Once this widget is added, it may be used to display both autowidget and metabox info in a sidebar
 *
 * Constant Settings::BLOCK_WIDGET_NAME is the post-WP 5.8 widget block name.
 * Constant Settings::WIDGET_NAME is the pre-WP 5.8 widget name.
 *
 * @see \Lumiere\Admin that calls it
 * @see \Lumiere\Frontend\Widget_Legacy calls it when in frontend and extends the current class. The current class registers Widget_Legacy widget
 */
class Widget_Selection extends WP_Widget {

	/**
	 * Global Frontend trait
	 */
	use Settings_Global;

	/**
	 * Constructor. Sets up the widget name, description, etc.
	 */
	public function __construct() {

		// Get Global Settings class properties from trait Settings_Global.
		$this->get_settings_class();
		$this->get_db_options();

		parent::__construct(
			Settings::WIDGET_NAME,  // Base ID.
			'Lumière! Widget (legacy)',   // Name.
			[
				'description' => esc_html__( 'Add automatically movie details to your pages with Lumière! Legacy version: as of WordPress 5.8, prefer the new Widget version in block editor.', 'lumiere-movies' ),
				'show_instance_in_rest' => true, /** use WP REST API */
			]
		);

		/**
		 * Hide the widget in legacy widgets menu
		 * If legacy widget is hidden, when switching from classic to block, legacy widget can't be removed
		 */
		// add_action( 'widget_types_to_hide_from_legacy_widget_block', [ $this, 'lumiere_hide_widget' ] );

	}

	/**
	 * Statically start the class
	 *
	 * @since 4.0 using __CLASS__ instead of get_class() in register_widget()
	 * @since 4.0.3 replaced __CLASS__ with "Widget_Legacy" in register_widget(), changed the logic of registering the block widget and exit
	 */
	public static function lumiere_static_start(): void {

		$self_class = new self();

		// Register Block-based Widget if a block is already available, or if the plugin classic widget is available
		if (
			static::lumiere_block_widget_isactive( Settings::BLOCK_WIDGET_NAME ) === true
			|| is_plugin_active( 'classic-widgets/classic-widgets.php' ) === false
			// || is_active_widget( false, false, Settings::WIDGET_NAME, false ) === false
		) {
			add_action( 'widgets_init', [ $self_class, 'lumiere_register_widget_block' ] );
			return;
		}

		// Register legacy widget only if no Widget block has been added.
		add_action(
			'widgets_init',
			function() {
				register_widget( 'Lumiere\Frontend\Widget_Legacy' );
			}
		);
	}

	/**
	 * Hide Lumière legacy widget from WordPress legacy widgets list
	 * Not in use
	 *
	 * @param array<string> $widget_types
	 * @return array<string>
	 */
	public function lumiere_hide_widget( array $widget_types ): array {

		$widget_types[] = Settings::WIDGET_NAME;
		return $widget_types;
	}

	/**
	 * Register Block Widget (>= WordPress 5.8)
	 * @since 4.0.3 Using block.json
	 */
	public function lumiere_register_widget_block(): void {

		/**
		 * Fix; Avoid registering the block twice, register only if not already registered.
		 * Avoid WP notice 'WP_Block_Type_Registry::register was called incorrectly. Block type is already registered'.
		 */
		if (
			function_exists( 'register_block_type_from_metadata' )
			&& class_exists( '\WP_Block_Type_Registry' )
			&& ! \WP_Block_Type_Registry::get_instance()->is_registered( Settings::BLOCK_WIDGET_NAME )
		) {

			register_block_type_from_metadata( dirname( dirname( __DIR__ ) ) . '/assets/blocks/widget/' );
		}
	}

	/**
	 * Text for legacy widget. Supposed to display a preview in Block-based interface, but doesn't work.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array<array-key, mixed>|string $args Display arguments including 'before_title', 'after_title', 'before_widget', and 'after_widget'.
	 * @param array<array-key, mixed> $instance The settings for the particular instance of the widget.
	 * @return void
	 */
	public function widget( $args, $instance ) {

		// Display preview image only in widget block editor interface.
		$referer = strlen( $_SERVER['REQUEST_URI'] ?? '' ) > 0 ? wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) : '';
		$pages_authorised = [ '/wp-admin/widgets.php', '/wp-json/wp/v2/widget-types' ];
		if ( Utils::lumiere_array_contains_term( $pages_authorised, $referer ) ) {

			echo '<div align="center"><img src="' . esc_url( $this->config_class->lumiere_pics_dir . 'widget-preview.png' ) . '" /></div>';
			echo '<br />';
		}
	}

	/**
	 * Outputs the settings update form for Legacy widget.
	 *
	 * @see \WP_Widget::form()
	 *
	 * @param array<array-key, mixed> $instance Current settings.
	 * @return string Default return is 'noform'.
	 */
	public function form( $instance ): string {

		$title = $instance['title'] ?? '';
		$lumiere_query_widget = $instance['lumiere_queryid_widget'] ?? '';

		$lumiere_queryid_widget_input = $instance['lumiere_queryid_widget_input'] ?? '';

		$output = "\n" . '<!-- #lumiere-movies --><p class="lumiere_padding_ten">';

		$output .= "\n\t" . '<div class="lumiere_display_inline_flex">';
		$output .= "\n\t\t" . '<div class="lumiere_padding_ten">';
		$output .= "\n\t\t\t" . '<img class="lumiere_flex_auto" width="40" height="40" src="'
				. esc_url( $this->config_class->lumiere_pics_dir . 'lumiere-ico80x80.png' ) . '" />';
		$output .= "\n\t\t" . '</div>';

		$output .= "\n\t\t" . '<div class="lumiere_flex_auto">';
		$output .= "\n\t\t\t" . '<label for="' . esc_attr( $this->get_field_id( 'title' ) ) . '">'
					. esc_html__( 'Widget title:', 'lumiere-movies' ) . '</label>';
		$output .= "\n\t\t\t" . '<input class="widefat" id="' . esc_attr( $this->get_field_id( 'title' ) ) . '" name="' . esc_attr( $this->get_field_name( 'title' ) ) . '" type="text" value="' . esc_attr( $title ) . '" />';
		$output .= "\n\t\t\t" . '</div>';
		$output .= "\n\t\t" . '</div>';
		$output .= "\n\t" . '</p><!-- #lumiere-movies -->';

		echo $output;  // @phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

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
	 * @since 4.0.3 moved from Utils to this class
	 */
	public static function lumiere_block_widget_isactive( string $blockname ): bool {
		$widget_blocks = get_option( 'widget_block' );
		foreach ( $widget_blocks as $widget_block ) {
			if ( ( isset( $widget_block['content'] ) && strlen( $widget_block['content'] ) !== 0 )
			&& has_block( $blockname, $widget_block['content'] )
			) {
				return true;
			}
		}
		return false;
	}
}
