<?php declare( strict_types = 1 );
/**
 * Widget Legacy class
 * Get data from widget() method
 *
 * @author Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version 2.0
 * @package lumiere-movies
 */

namespace Lumiere\Frontend;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	wp_die( esc_html__( 'Lumière Movies: You can not call directly this page', 'lumiere-movies' ) );
}

use Lumiere\Settings;
use Lumiere\Frontend\Widget_Frontpage;
use WP_Widget;

/**
 * Extends WP_Widget to retrieve legacy Widget
 * The whole point of this class is access to widget() method, provided when legacy widget is active
 * Method widget() calls function in Widget_Frontpage, it can't be filtered (afaik) directly in Widget_Frontpage
 */
class Widget_Legacy extends WP_Widget {

	// Use Frontend trait
	use  \Lumiere\Frontend\Main {
		Main::__construct as public __constructFrontend;
	}

	/**
	 * Shortcode to be used by add_shortcodes, ie [lumiereWidget][/lumiereWidget]
	 * This shortcode is temporary and created on the fly
	 * Doesn't need to be deleted when uninstalling Lumière plugin
	 */
	const WIDGET_SHORTCODE = Widget_Frontpage::WIDGET_SHORTCODE;

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
			'Lumière! auto-Widget (legacy)',   // Name.
			[
				'description' => esc_html__( 'Add automatically movie details to your pages with Lumière! Legacy version: as of WordPress 5.8, prefer the new widget.', 'lumiere-movies' ),
				'show_instance_in_rest' => true, /** use WP REST API */
			]
		);

		// Construct Frontend trait.
		$this->__constructFrontend( 'widgetLegacy' );

		// Execute logging.
		do_action( 'lumiere_logger' );

	}

	/**
	 * Call widget legacy (pre-WP 5.8)
	 */
	public function lumiere_widget_legacy_start(): void {

		add_action(
			'widgets_init',
			function() {
				register_widget( get_class() );
			}
		);

	}

	/**
	 * Front end output, use Widget_Frontpage class to get the output
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array<array-key, string>|string $args Display arguments including 'before_title', 'after_title', 'before_widget', and 'after_widget'.
	 * @param array<string> $instance The settings for the particular instance of the widget.
	 * @return void
	 *
	 * @phpstan-ignore-next-line inherited constraints from parent, can't comply with declaration requirements
	 */
	public function widget( $args, $instance ) {

		// Build title, use a default text if title has not been edited in the widget interface.
		$title_box = $instance['title'] ?? esc_html__( 'Lumière! Movies widget', 'lumiere-movies' );

		/**
		 * Output the result using a layout wrapper.
		 * This result cannot be displayed anywhere else but in this widget() method.
		 * widget() method could return data using ob_start(), but where to display it?
		 * As far as I know, at least.
		 */
		$widget_class = new Widget_Frontpage();
		echo $widget_class->lumiere_widget_display_movies( $title_box ); // @phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	}
}

