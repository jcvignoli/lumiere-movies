<?php declare( strict_types = 1 );
/**
 * Widget Frontend class
 *
 * @copyright (c) 2021, Lost Highway
 *
 * @version       2.0
 * @package       lumieremovies
 */

namespace Lumiere\Frontend\Widget;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Config\Open_Options;
use Lumiere\Frontend\Widget\Widget_Legacy;
use Lumiere\Plugins\Logger;
use Lumiere\Admin\Widget_Selection;

/**
 * Widgets in Frontpages (displayed in single pages and posts only)
 *
 * Use Widget_Legacy class if legacy widget is active
 * Auto title widget: get the title of the post, can be disabled on a per-post basis in {@link \Lumiere\Admin\Metabox_Selection}
 * Do a search for the movie using one of them
 *
 * @see \Lumiere\Admin\Metabox_Selection Select the metadata to display, whether output auto title widget or not
 * @see \Lumiere\Frontend\Widget_Legacy Is used if the legacy widget is in use
 *
 * @phpstan-import-type PLUGINS_ALL_KEYS from \Lumiere\Plugins\Plugins_Detect
 * @phpstan-import-type PLUGINS_ALL_CLASSES from \Lumiere\Plugins\Plugins_Detect
 */
class Widget_Frontpage {

	/**
	 * Traits
	 */
	use Open_Options;

	/**
	 * Shortcode to be used by add_shortcodes, ie [lumiereWidget][/lumiereWidget]
	 * This shortcode is temporary and created on the fly
	 * Doesn't need to be deleted when uninstalling Lumière plugin
	 * @see Block widget which includes the shortcode
	 */
	public const WIDGET_SHORTCODE = 'lumiereWidget';

	/**
	 * HTML wrapping to the widget name
	 */
	public const ARGS = [
		'after_widget' => '</div>',
		'before_title' => '<h4 id="lumiere_movies_widget_title" class="widget-title">',
		'after_title' => '</h4>',
		'before_widget' => '<div id="lumiere_movies_widget" class="sidebar-box widget_lumiere_movies_widget clr">',
	];

	/**
	 * HTML allowed for use of wp_kses()
	 */
	private const ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS = [
		'div' => [
			'id' => [],
			'class' => [],
		],
		'h4' => [
			'id' => [],
			'class' => [],
		],
		'button' => [ // Utilised by bootstrap modal window
			'type' => [],
			'class' => [],
			'aria-label' => [],
			'data-*' => true, // perhaps due to the wildcard, [] doesn't work here
		],
		'i' => [],
		'br' => [],
		'strong' => [],
		'hr' => [],
		'a' => [
			'id' => [],
			'class' => [],
			'href' => [],
			'title' => [],
			'data-*' => true, // perhaps due to the wildcard, [] doesn't work here
		],
		'span' => [
			'id' => [],
			'class' => [],
		],
		'img' => [
			'width' => [],
			'height' => [],
			'alt' => [],
			'loading' => [],
			'src' => [],
			'id' => [],
			'title' => [],
			'class' => [],
		],
	];

	/**
	 * Constructor.
	 */
	public function __construct(
		public Logger $logger = new Logger( 'Widget_Frontpage' ),
	) {
		// Get global settings class properties.
		$this->get_db_options(); // In Open_Options trait.
	}

	/**
	 * Run the widget
	 * Sets up the widget name, description, etc.
	 * Use Legacy widget if no active Block widget and active Legacy widget are found
	 * Otherwise use shortcode to display data
	 *
	 * @return void Either Legacy or Block-based widget displayed
	 */
	public function start(): void {

		// If pre-5.8 widget is active and Block Widget unactive, use Widget_Legacy class.
		if (
			is_active_widget( false, false, Widget_Selection::WIDGET_NAME, false ) !== false
			&& Widget_Selection::lumiere_block_widget_isactive( Widget_Selection::BLOCK_WIDGET_NAME ) === false
		) {
			Widget_Legacy::widget_legacy_start();
			return;
		}

		/**
		 * Regular post-5.8 widgets.
		 * @INFO Check deactivated: the condition prevents some customized WP to see the widget https://wordpress.org/support/topic/4-3-2-1/page/2/
		 * @INFO Perhaps changing Widget_Selection::lumiere_block_widget_isactive() could fix it
		if ( Widget_Selection::lumiere_block_widget_isactive( Widget_Selection::BLOCK_WIDGET_NAME ) === true ) {
		 */
		add_shortcode( self::WIDGET_SHORTCODE, [ $this, 'shortcode_parser' ] );
	}

	/**
	 * Is this a forbidden area for the Widget
	 * @return bool True if forbidden
	 */
	private function is_forbidden_areas(): bool {
		return is_home() || is_front_page() || is_404() || is_attachment() || is_archive() || is_author();
	}

	/**
	 * Parse shortcodes, called in add_shortcode()
	 * Only called if regular post-5.8 block widget was found
	 *
	 * @param array<string>|string $attributes
	 * @param null|string $inside_tags Text inside the shortcode
	 * @param string $tags Shortcode tag
	 * @return string The final Widget with Title+Content, or nothing if nothing was found
	 */
	public function shortcode_parser( array|string $attributes, ?string $inside_tags, string $tags ): string {

		// Exit it's home, frontpage, 404, attachment, etc (must allow people with custom posts to display the widget)
		if ( $this->is_forbidden_areas() === true ) {
			$this->logger->log?->debug( '[Widget_Frontpage] This is a forbidden area for displaying the widget, process stopped.' );
			return '';
		}

		if ( isset( $inside_tags ) ) {
			$this->logger->log?->debug( '[Widget_Frontpage] Shortcode [' . $tags . '] added.' );
			return $this->lum_get_widget( $inside_tags );
		}
		return '';
	}

	/**
	 * Widget output in Frontend pages
	 * Used by current Shortcode Parser and Widget_Legacy class
	 *
	 * @since 3.10.2 added array_filter to clean $imdb_id_or_title
	 * @since 4.0 added exit if no metadata and no auto title widget activated
	 * @since 4.1 do not use auto title widget if auto title widget exclusion is selected in the current post
	 * @see \Lumiere\Frontend\Widget\Widget_Legacy::widget() calls it for pre-5.8 WordPress widgets
	 *
	 * @param string $title_box Title of the widget to be displayed
	 * @return string The title and movie data of the Widget
	 */
	public function lum_get_widget( string $title_box ): string {

		// Exit it's home, frontpage, 404, attachment, etc (must allow people with custom posts to display the widget)
		if ( $this->is_forbidden_areas() === true ) {
			$this->logger->log?->debug( '[Widget_Frontpage] This is a forbidden area for displaying the widget, process stopped.' );
			return '';
		}

		// Build title, use a default text if title has not been edited in the widget interface.
		$title_box = strlen( $title_box ) > 0 ? $title_box : '';
		$movies_array = [];
		$post_id = get_the_ID();

		if ( is_int( $post_id ) === false || $post_id === 0 ) {
			$this->logger->log?->debug( '[Widget_Frontpage] Wrong post ID' );
			return '';
		}

		// Log what widget type is in use.
		if ( Widget_Selection::lumiere_block_widget_isactive( Widget_Selection::BLOCK_WIDGET_NAME ) === true ) {
			// Post 5.8 WordPress.
			$this->logger->log?->debug( '[Widget_Frontpage] Block-based widget found' );
		} elseif ( is_active_widget( false, false, Widget_Selection::WIDGET_NAME, false ) !== false ) {
			$this->logger->log?->debug( '[Widget_Frontpage] Pre-5.8 WordPress widget found' );
		}

		/**
		 * Display the movie according to the post's title (option in -> main -> advanced).
		 * Add the title to the array if auto title widget is enabled and is not disabled for this post
		 */
		if (
			$this->imdb_admin_values['imdbautopostwidget'] === '1'
			&& get_post_meta( $post_id, 'lumiere_autotitlewidget_perpost', true ) !== 'disabled' // thus the var may not have been created.
		) {
			$movies_array[]['byname'] = esc_html( get_the_title( $post_id ) );
			$this->logger->log?->debug( '[Widget_Frontpage] Auto title widget activated, using the post title ' . esc_html( get_the_title( $post_id ) ) . ' for querying' );

			// the post-based selection for auto title widget is turned off
		} elseif (
			$this->imdb_admin_values['imdbautopostwidget'] === '1'
			&& get_post_meta( $post_id, 'lumiere_autotitlewidget_perpost', true ) === 'disabled'
		) {
			$this->logger->log?->debug( '[Widget_Frontpage] Auto title widget is specifically deactivated for this post' );
		}

		// Check if a post ID is available add it.
		$movies_array[] = $this->maybe_get_lum_post_metada( $post_id );

		// Remove empty array_values from $movies_arrays
		$movies_array_cleaned = array_filter(
			$movies_array,
			function( $movies_array ) {
				return count( array_values( $movies_array ) ) > 0;
			}
		);
		// Exit if array is empty (meaning that no metadata was found and auto title option is disabled)
		if ( count( $movies_array_cleaned ) === 0 ) {
			$this->logger->log?->debug( '[Widget_Frontpage] Neither movie title nor id were passed to be queried for this widget, exit' );
			return '';
		}

		/**
		 * Get movie's data from {@link \Lumiere\Frontend\Movie\Front_Parser}
		 * @since 4.4 using filters declared in {@see \Lumiere\Frontend\Frontend::__construct()}
		 *
		 * @psalm-var array<array-key, array{bymid?: string, byname?: string}> $get_array_imdbid
		 */
		$get_array_imdbid = apply_filters( 'lum_find_movie_id', $movies_array_cleaned );
		$lum_movie_box = apply_filters( 'lum_display_movies_box', $get_array_imdbid );

		// Output the result in a layout wrapper.
		return $this->wrap_widget_content( $title_box, $lum_movie_box );
	}

	/**
	 * Query WordPress current post using the PostID to get post metadata
	 * Custom field lumiere_widget_movietitle, which may include the name of the movie if user added it
	 * Custom field lumiere_widget_movieid, which may include the ID of the movie if user added it
	 *
	 * @param int $post_id WordPress post ID to query about metaboxes
	 * @return array<string, string> Results found in metaboxes if any
	 *
	 * @see \Lumiere\Admin\Metabox_Selection Post metada is added in a metabox
	 */
	private function maybe_get_lum_post_metada( int $post_id ): array {

		$movies_array = [];
		$get_movie_name = get_post_meta( $post_id, 'lumiere_widget_movietitle', false /* false to get an array of values, can have many */ );
		$get_movie_id = get_post_meta( $post_id, 'lumiere_widget_movieid', false /* false to get an array of values, can have many */ );

		// Custom field "lumiere_widget_movietitle", using the movie title provided.
		if ( $get_movie_name !== false && count( $get_movie_name ) > 0 ) {
			// Do a loop, even if today the plugin allows only one metabox.
			foreach ( $get_movie_name as $key => $value ) {
				$movies_array['byname'] = esc_html( $value );
				$this->logger->log?->debug( "[Widget_Frontpage] Custom field lumiere_widget_movietitle found, using \"$value\" for querying" );
			}

		}

		// Custom field "lumiere_widget_movieid", using the movie ID provided.
		if ( $get_movie_id !== false && count( $get_movie_id ) > 0 ) {
			// Do a loop, even if today the plugin allows only one metabox.
			foreach ( $get_movie_id as $key => $value ) {
				$movies_array['bymid'] = esc_html( $value );
				$this->logger->log?->debug( "[Widget_Frontpage] Custom field lumiere_widget_movieid found, using \"$value\" for querying" );
			}

		}

		return $movies_array;
	}

	/**
	 * Final widget layout, merging the wrapper title and its content
	 *
	 * @param string $title_box Title of the widget box
	 * @param string $movie Movie data details to be displayed
	 * @return string Entire widget (Title+Content)
	 */
	private function wrap_widget_content( string $title_box, string $movie ): string {

		// Exit if no data provided.
		if ( strlen( $movie ) === 0 ) {
			return '';
		}

		$embeded_title_box = strlen( $title_box ) > 0 ? self::ARGS['before_title'] . $title_box . self::ARGS['after_title'] : '';
		apply_filters( 'widget_title', $embeded_title_box ); // Change widget title according to the extra args.

		$output = wp_kses( self::ARGS['before_widget'], self::ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS );
		$output .= wp_kses( $embeded_title_box, self::ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS ); // title of widget.
		$output .= wp_kses( $movie, self::ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS ); // Movie data.
		$output .= wp_kses( self::ARGS['after_widget'], self::ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS );

		return $output;
	}

}
