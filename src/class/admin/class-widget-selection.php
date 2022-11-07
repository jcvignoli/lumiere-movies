<?php declare( strict_types = 1 );
/**
 * Admin_Widget class: Add a Lumière Widget option in administration
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
use Lumiere\Utils;

/**
 * This widget is used to display both autowidget and metabox info in sidebar widgets
 */
class Widget_Selection extends \WP_Widget {

	// Use Frontend trait
	use \Lumiere\Settings_Global;

	/**
	 * Names of the Widgets
	 */
	const BLOCK_WIDGET_NAME = Settings::BLOCK_WIDGET_NAME; // post-WP 5.8 widget block name.
	const WIDGET_NAME = Settings::WIDGET_NAME; // pre-WP 5.8 widget name.

	/**
	 * Constructor. Sets up the widget name, description, etc.
	 *
	 */
	public function __construct() {

		parent::__construct(
			self::WIDGET_NAME,  // Base ID.
			'Lumière! Widget (legacy)',   // Name.
			[
				'description' => esc_html__( 'Add automatically movie details to your pages with Lumière! Legacy version: as of WordPress 5.8, prefer the new Widget version in block editor.', 'lumiere-movies' ),
				'show_instance_in_rest' => true, /** use WP REST API */
			]
		);

		// Settings trait.
		$this->settings_open();

		/**
		 * Hide the widget in legacy widgets menu
		 * If legacy widget is hidden, when switching from classic to block, legacy widget can't be removed
		 */
		// add_action( 'widget_types_to_hide_from_legacy_widget_block', [ $this, 'lumiere_hide_widget' ] );

	}

	/**
	 * Statically start the class
	 */
	public static function lumiere_widget_start(): void {

		$self_class = new self();

		// Register Block-based Widget in all cases. Does not impact legacy widget.
		add_action( 'widgets_init', [ $self_class, 'lumiere_register_widget_block' ] );

		// Register legacy widget only if no Widget block has been added.
		if (
			Utils::lumiere_block_widget_isactive( self::BLOCK_WIDGET_NAME ) === false
			|| is_active_widget( false, false, self::WIDGET_NAME, false ) !== false
		) {
			add_action(
				'widgets_init',
				function() {
					register_widget( get_class() );
				}
			);
		}

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
	 */
	public function lumiere_register_widget_block(): void {

		wp_register_script(
			'lumiere_block_widget',
			$this->config_class->lumiere_blocks_dir . 'widget/index.min.js',
			[ 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-data' ],
			$this->config_class->lumiere_version,
			false
		);

		wp_register_style(
			'lumiere_block_widget',
			$this->config_class->lumiere_blocks_dir . 'widget/index.min.css',
			[],
			$this->config_class->lumiere_version
		);

		// Fix; Avoid registering the block twice, register only if not already registered.
		// Avoid WP notice 'WP_Block_Type_Registry::register was called incorrectly. Block type is already registered'.
		if ( function_exists( 'register_block_type' ) && class_exists( '\WP_Block_Type_Registry' ) && ! \WP_Block_Type_Registry::get_instance()->is_registered( self::BLOCK_WIDGET_NAME ) ) {

			register_block_type(
				self::BLOCK_WIDGET_NAME,
				[
					'style' => 'lumiere_block_widget', // Loads both on editor and frontend.
					'editor_script' => 'lumiere_block_widget', // Loads only on editor.
				]
			);

		}
	}

	/**
	 * Text for legacy widget. Supposed to display a preview in Block-based interface, but doesn't work.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array<string>|string $args Display arguments including 'before_title', 'after_title', 'before_widget', and 'after_widget'.
	 * @param array<string> $instance The settings for the particular instance of the widget.
	 * @return void
	 * @phpstan-ignore-next-line inherited constraints from parent, can't comply with declaration requirements
	 */
	public function widget( $args, $instance ) {

		// Display preview image only in widget block editor interface.
		$referer = strlen( $_SERVER['REQUEST_URI'] ) > 0 ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$pages_authorised = [ '/wp-admin/widgets.php', '/wp-json/wp/v2/widget-types' ];
		if ( Utils::lumiere_array_contains_term( $pages_authorised, $referer ) ) {

			echo '<div align="center"><img src="' . esc_url( $this->config_class->lumiere_pics_dir . 'widget-preview.png' ) . '" /></div>';
			echo '<br />';

		}

	}

	/**
	 * Outputs the settings update form for Legacy widget.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Current settings.
	 * @return string Default return is 'noform'.
	 *
	 * @phpstan-ignore-next-line inherited constraints from parent, can't comply with declaration requirements
	 */
	public function form( $instance ) {

		$title = $instance['title'] ?? esc_html__( 'Lumière! Movies', 'lumiere-movies' );
		$lumiere_query_widget = $instance['lumiere_queryid_widget'] ?? '';

		$lumiere_queryid_widget_input = $instance['lumiere_queryid_widget_input'] ?? '';

		$output = "\n" . '<!-- #lumiere-movies --><p class="lumiere_padding_ten">';

		$output .= "\n\t" . '<div class="lumiere_display_inline_flex">';
		$output .= "\n\t\t" . '<div class="lumiere_padding_ten">';
		$output .= "\n\t\t\t" . '<img class="lumiere_flex_auto" width="40" height="40" src="'
				. esc_url( $this->config_class->lumiere_pics_dir . 'lumiere-ico80x80.png' ) . '" />';
		$output .= "\n\t\t" . '</div>';

		$output .= "\n\t\t" . '<div class="lumiere_flex_auto">';
		$output .= "\n\t\t\t" . '<label for="'
					. esc_attr( $this->get_field_id( 'title' ) ) . '">'
					. esc_html__( 'Widget title:', 'lumiere-movies' ) . '</label>';
		$output .= "\n\t\t\t" . '<input class="widefat" id="' . esc_attr( $this->get_field_id( 'title' ) ) . '" name="' . esc_attr( $this->get_field_name( 'title' ) ) . '" type="text" value="' . esc_attr( $title ) . '" />';
		$output .= "\n\t\t\t" . '</div>';
		$output .= "\n\t\t" . '</div>';
		$output .= "\n\t" . '</p><!-- #lumiere-movies -->';

		echo $output; // @phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

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
	 * @param array $new_instance New settings for this instance as input by the user via WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Settings to save or bool false to cancel saving.
	 * @phpstan-ignore-next-line inherited constraints from parent, can't comply with declaration requirements
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = [];

		$instance['title'] = ( isset( $new_instance['title'] ) ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		$instance['lumiere_queryid_widget'] = $new_instance['lumiere_queryid_widget'] ?? '';
		$instance['lumiere_queryid_widget_input'] = $new_instance['lumiere_queryid_widget_input'] ?? '';

		return $instance;
	}

}
