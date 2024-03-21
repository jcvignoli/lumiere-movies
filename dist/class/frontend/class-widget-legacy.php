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

use Lumiere\Frontend\Widget_Frontpage;
use Lumiere\Admin\Widget_Selection;

/**
 * Extends WP_Widget to retrieve legacy Widget
 * The whole point of this class is access to widget() method, provided when legacy widget is active
 * Method widget() calls function in Widget_Frontpage, it can't be filtered (afaik) directly in Widget_Frontpage
 *
 * @see Lumiere\Admin\Widget_Selection parent class which creates the legacy widget
 * @see Lumiere\Frontend\Widget_Frontpage which calls this widget if pre-5.8 widget is detected
 * @since 4.0.3 extends "Widget_Selection" instead of "WP_Widget" class
 */
class Widget_Legacy extends Widget_Selection {

	/**
	 * Use Frontend trait
	 */
	use  \Lumiere\Frontend\Main {
		Main::__construct as public __constructFrontend;
	}

	/**
	 * Constructor. Sets up the widget name, description, etc.
	 */
	public function __construct() {

		parent::__construct();

		// Construct Frontend trait.
		$this->__constructFrontend( __CLASS__ );

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
				register_widget( __CLASS__ );
			}
		);

	}

	/**
	 * Front end output, use Widget_Frontpage class to get the output
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array<array-key, mixed>|string $args Display arguments including 'before_title', 'after_title', 'before_widget', and 'after_widget'.
	 * @param array<array-key, mixed> $instance The settings for the particular instance of the widget.
	 * @return void
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

