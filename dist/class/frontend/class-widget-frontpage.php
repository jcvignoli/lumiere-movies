<?php declare( strict_types = 1 );
/**
 * Widget Frontend class
 * Display Movie data for Autowidget and Normal widget (Metabox)
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
use Lumiere\Frontend\Movie;
use Lumiere\Frontend\Widget_Legacy;
use Lumiere\Tools\Utils;

/**
 * Widgets in Frontpages (displayed in single pages and posts only)
 *
 * Use Widget_Legacy class if legacy widget is active
 * Metabox Widget: get the post ID and query to get metabox metadata
 * Autowidget: get the title of the post
 * Do a search for the movie using one of them
 */
class Widget_Frontpage {

	// Use Frontend trait
	use  \Lumiere\Frontend\Main {
		Main::__construct as public __constructFrontend;
	}

	/**
	 * Shortcode to be used by add_shortcodes, ie [lumiereWidget][/lumiereWidget]
	 * This shortcode is temporary and created on the fly
	 * Doesn't need to be deleted when uninstalling Lumière plugin
	 */
	public const WIDGET_SHORTCODE = 'lumiereWidget';

	/**
	 * Names of the Widgets
	 */
	public const BLOCK_WIDGET_NAME = Settings::BLOCK_WIDGET_NAME; // post-WP 5.8 widget block name.
	public const WIDGET_NAME = Settings::WIDGET_NAME; // pre-WP 5.8 widget name.

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
			'alt' => [],
			'loading' => [],
			'src' => [],
			'id' => [],
			'class' => [],
		],
	];

	private Movie $movie_class;

	/**
	 * Constructor. Sets up the widget name, description, etc.
	 *
	 */
	public function __construct() {

		// Construct Frontend trait.
		$this->__constructFrontend( 'widgetFrontpage' );

		// @TODO : when updating to PHP8.2, pass this in the constructor params
		$this->movie_class = new Movie();

		// Execute logging.
		do_action( 'lumiere_logger' );

	}

	/**
	 * Statically start the class
	 * Use Legacy widget if no active Block widget and active Legacy widget are found
	 * Otherwise use shortcode to display data
	 */
	public static function lumiere_widget_frontend_start(): void {

		$self_class = new self();

		// If pre-5.8 widget is active and Block Widget unactive, use Widget_Legacy class.
		if (
			is_active_widget( false, false, self::WIDGET_NAME, false ) !== false
			&& Utils::lumiere_block_widget_isactive( self::BLOCK_WIDGET_NAME ) === false
		) {
			$legacy_class = new Widget_Legacy();
			$legacy_class->lumiere_widget_legacy_start();
			return;
		}

		$self_class->lumiere_widget_run_shortcodes();
	}

	/**
	 * Execute add_shortcode()
	 */
	private function lumiere_widget_run_shortcodes(): void {
		// Shortcodes are found only if blockbased widget was activated.
		add_shortcode( self::WIDGET_SHORTCODE, [ $this, 'lumiere_widget_shortcode_parser' ] );
	}

	/**
	 * Parse shortcodes, called in add_shortcode()
	 *
	 * @param array<string>|string $attributes
	 * @param null|string $inside_tags Text inside the shortcode
	 * @param string $tags Shortcode tag
	 * @return string The final Widget with Title+Content
	 */
	public function lumiere_widget_shortcode_parser( array|string $attributes, ?string $inside_tags, string $tags ): string {

		$output = '';

		if ( isset( $inside_tags ) ) {
			$this->logger->log()->debug( '[Lumiere][widget] Shortcode [' . $tags . '] found.' );
			return $this->lumiere_widget_display_movies( $inside_tags );
		}

		return $output;
	}

	/**
	 * Widget output in Frontend pages
	 * Used by current Shortcode Parser and Widget_Legacy class
	 *
	 * @since 3.10.2 added array_filter to clean $imdb_id_or_title
	 * @since 4.0 added exit if no metadata and no auto title widget activated
	 *
	 * @param string $title_box Title of the widget to be displayed
	 * @return string The title and movie data of the Widget
	 */
	public function lumiere_widget_display_movies( string $title_box ): string {

		// Exit if neither a post nor a page!
		if ( ! is_single() && ! is_page() ) {
			$this->logger->log()->debug( '[Lumiere][widget] This is not page, exit.' );
			return '';
		}

		// Initialize var for id/name of the movie to display.
		$imdb_id_or_title = [];

		// Build title, use a default text if title has not been edited in the widget interface.
		$title_box = strlen( $title_box ) > 0 ? $title_box : '';

		// Log what type of widget is utilised.
		if ( Utils::lumiere_block_widget_isactive( self::BLOCK_WIDGET_NAME ) === true ) {
			// Post 5.8 WordPress.
			$this->logger->log()->debug( '[Lumiere][widget] Block-based widget found' );
		} elseif ( is_active_widget( false, false, self::WIDGET_NAME, false ) !== false ) {
			$this->logger->log()->debug( '[Lumiere][widget] Pre-5.8 WordPress widget found' );
		}

		// Display the movie according to the post's title (option in -> general -> advanced).
		if ( $this->imdb_admin_values['imdbautopostwidget'] === '1' ) {
			$imdb_id_or_title[]['byname'] = sanitize_text_field( get_the_title() );
			$this->logger->log()->debug( '[Lumiere][widget] Auto widget activated, using the post title ' . sanitize_text_field( get_the_title() ) . ' for querying' );
		}

		// Get the post ID to query if metaboxes are available in the post.
		$post_id = get_the_ID();

		// Query if metaboxes are available in the post and add them to array to be queried in Movie class.
		$imdb_id_or_title[] = is_int( $post_id ) ? $this->lumiere_widget_get_metabox_metadata( $post_id ) : null;

		// Clean the array, remove empty multidimensional arrays.
		$final_imdb_id_or_title = array_filter( $imdb_id_or_title );

		// Exit if no metadata, no auto title option activated
		if ( $this->imdb_admin_values['imdbautopostwidget'] !== '1' && count( $final_imdb_id_or_title ) === 0 ) {
			$this->logger->log()->debug( '[Lumiere][widget] Auto title widget deactivated and no IMDb meta for this post, exiting' );
			return '';
		}

		// Query Movie class.
		$movie = $this->movie_class->lumiere_show( $final_imdb_id_or_title );

		/**
		 * Output the result using a layout wrapper.
		 * This result cannot be displayed anywhere else but in this widget() method.
		 * As far as I know, at least.
		 */
		return $this->lumiere_widget_layout( $title_box, $movie );

	}

	/**
	 * Query WordPress using the PostID to get metaboxes data
	 *
	 * @param int $post_id WordPress post ID to query about metaboxes
	 * @return array<string, string> Results found in metaboxes if any
	 */
	private function lumiere_widget_get_metabox_metadata( int $post_id ): array {

		$imdb_id_or_title = [];
		$get_movie_name = get_post_meta( $post_id, 'imdb-movie-widget', false );
		$get_movie_id = get_post_meta( $post_id, 'imdb-movie-widget-bymid', false );

		// Custom field "imdb-movie-widget", using the movie title provided.
		if ( count( $get_movie_name ) > 0 ) {
			// Do a loop, even if today the plugin allows only one metabox.
			foreach ( $get_movie_name as $key => $value ) {
				$imdb_id_or_title['byname'] = sanitize_text_field( $value );
				$this->logger->log()->debug( "[Lumiere][widget] Custom field imdb-movie-widget found, using $value for querying" );
			}

		}

		// Custom field imdb-movie-widget-bymid", using the movie ID provided.
		if ( count( $get_movie_id ) > 0 ) {
			// Do a loop, even if today the plugin allows only one metabox.
			foreach ( $get_movie_id as $key => $value ) {
				$imdb_id_or_title['bymid'] = sanitize_text_field( $value );
				$this->logger->log()->debug( "[Lumiere][widget] Custom field imdb-movie-widget-bymid found, using $value for querying" );
			}

		}

		return $imdb_id_or_title;
	}

	/**
	 * Final widget layout, called to merge data and widget layout
	 *
	 * @param string $title_box Title of the widget box
	 * @param string $movie Movie data details to be displayed
	 * @return string Entire widget (Title+Content)
	 */
	private function lumiere_widget_layout( string $title_box, string $movie ): string {

		$output = '';

		// Exit if no data provided.
		if ( strlen( $movie ) === 0 ) {
			return $output;
		}

		$embeded_title_box = strlen( $title_box ) > 0 ? self::ARGS['before_title'] . $title_box . self::ARGS['after_title'] : '';
		apply_filters( 'widget_title', $embeded_title_box ); // Change widget title according to the extra args.

		$output .= wp_kses( self::ARGS['before_widget'], self::ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS );
		$output .= wp_kses( $embeded_title_box, self::ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS ); // title of widget.
		$output .= wp_kses( $movie, self::ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS ); // Movie data.
		$output .= wp_kses( self::ARGS['after_widget'], self::ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS );

		return $output;
	}

}
