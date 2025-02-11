<?php declare( strict_types = 1 );
/**
 * Widget Legacy class
 *
 * @author Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version 2.0
 * @package lumiere-movies
 */

namespace Lumiere\Frontend;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Frontend\Widget_Frontpage;
use Lumiere\Admin\Widget_Selection;

/**
 * Extends Widget_Selection (which extends true WP_Widget) to display a legacy widget
 *
 * The whole point of this class is construct parent class and then access to widget() method, provided that the legacy widget is active
 * Widget_Legacy::widget() calls Widget_Frontpage::lumiere_display_widget(), which must be echoed here
 *
 * @see \Lumiere\Admin\Widget_Selection parent class which creates the legacy widget
 * @see \Lumiere\Frontend\Widget_Frontpage which calls this widget if pre-5.8 widget is detected
 * @since 4.1 extends "Widget_Selection" instead of "WP_Widget" class
 *
 * @psalm-suppress UndefinedClass -- it's defined above! how come it's undefined? Bug, if refreshing cache, the class is found
 */
class Widget_Legacy extends Widget_Selection {

	/**
	 * Register legacy widget (pre-WP 5.8), needed after the construction
	 */
	public static function widget_legacy_start(): void {

		$that = new self();
		add_action(
			'widgets_init',
			function() {
				register_widget( __CLASS__ );
			}
		);
	}

	/**
	 * @inheritdoc
	 * Front end output overwrite, use Widget_Frontpage class to get the output
	 *
	 * @see \WP_Widget::widget() extended in Widget_Selection
	 *
	 * @param array<array-key, mixed>|string $args Display arguments including 'before_title', 'after_title', 'before_widget', and 'after_widget'.
	 * @param array<array-key, mixed> $instance The settings for the particular instance of the widget.
	 * @return void
	 */
	#[\Override]
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
		$lum_widget_name = isset( $args['widget_name'] ) && is_string( $args['widget_name'] ) ? esc_html( $args['widget_name'] ) : '';
		$widget_class->logger->log->debug( '[Widget_Legacy] Using ' . $lum_widget_name . '.' );

		$kses_escape = [
			'div' => [
				'id' => [],
				'class' => [],
			],
			'h4' => [
				'id' => [],
				'class' => [],
			],
			'span' => [
				'id' => [],
				'class' => [],
			],
			'button' => [
				'type' => [],
				'class' => [],
				'data-*' => true,
				'aria-label' => [],
			],
			'a' => [
				'data-*' => true,
				'id' => [],
				'class' => [],
				'href' => [],
				'title' => [],
			],
			'img' => [
				'decoding' => [],
				'loading' => [],
				'class' => [],
				'alt' => [],
				'src' => [],
				'width' => [],
			],
		];
		echo wp_kses( $widget_class->lum_get_widget( $title_box ), $kses_escape );
	}
}

