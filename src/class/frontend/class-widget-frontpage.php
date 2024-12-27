<?php declare( strict_types = 1 );
/**
 * Widget Frontend class
 * Display Movie data for Auto title widget and Normal widget (Metabox)
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

use Lumiere\Admin\Widget_Selection;
use Lumiere\Frontend\Movie;
use Lumiere\Frontend\Widget_Legacy;
use Lumiere\Frontend\Main;

/**
 * Widgets in Frontpages (displayed in single pages and posts only)
 *
 * Use Widget_Legacy class if legacy widget is active
 * Auto title widget: get the title of the post, can be disabled on a per-post basis in {@link \Lumiere\Admin\Metabox_Selection}
 * Do a search for the movie using one of them
 *
 * @see \Lumiere\Admin\Metabox_Selection Select the metadata to display, whether output auto title widget or not
 * @see \Lumiere\Frontend\Widget_Legacy Is used if the widget legacy is in use
 */
class Widget_Frontpage {

	/**
	 * Traits
	 */
	use Main;

	/**
	 * Shortcode to be used by add_shortcodes, ie [lumiereWidget][/lumiereWidget]
	 * This shortcode is temporary and created on the fly
	 * Doesn't need to be deleted when uninstalling Lumière plugin
	 * @see Block widget wich includes the shortcode
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
			'class' => [],
		],
	];

	/**
	 * Movie class
	 */
	private Movie $movie_class;

	/**
	 * Constructor. Sets up the widget name, description, etc.
	 *
	 */
	public function __construct() {

		// Construct Frontend trait.
		$this->start_main_trait();

		// @TODO : when updating to PHP8.2, pass this in the constructor params
		$this->movie_class = new Movie();
	}

	/**
	 * Statically start the class
	 * Use Legacy widget if no active Block widget and active Legacy widget are found
	 * Otherwise use shortcode to display data
	 */
	public static function lumiere_widget_frontend_start(): void {

		$that = new self();

		// If pre-5.8 widget is active and Block Widget unactive, use Widget_Legacy class.
		if (
			is_active_widget( false, false, Widget_Selection::WIDGET_NAME, false ) !== false
			&& Widget_Selection::lumiere_block_widget_isactive( Widget_Selection::BLOCK_WIDGET_NAME ) === false
		) {
			Widget_Legacy::lumiere_widget_legacy_start();
			return;
		}

		// Regular post-5.8 widgets.
		add_shortcode( self::WIDGET_SHORTCODE, [ $that, 'lumiere_widget_shortcode_parser' ] );
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
	public function lumiere_widget_shortcode_parser( array|string $attributes, ?string $inside_tags, string $tags ): string {

		if ( isset( $inside_tags ) ) {
			$this->logger->log()->debug( '[Lumiere][' . $this->classname . '] Shortcode [' . $tags . '] found.' );
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
	 * @see \Lumiere\Frontend\Widget_Legacy::widget() calls it for pre-5.8 WordPress widgets
	 *
	 * @param string $title_box Title of the widget to be displayed
	 * @return string The title and movie data of the Widget
	 */
	public function lum_get_widget( string $title_box ): string {

		// Exit if neither a post nor a page!
		if ( is_singular( [ 'post', 'page' ] ) === false ) {
			$this->logger->log()->debug( '[Lumiere][' . $this->classname . '] This is not a post or page, process stopped.' );
			return '';
		}

		// Initialize var for id/name of the movie to display.
		$movies_array = [];

		// Build title, use a default text if title has not been edited in the widget interface.
		$title_box = strlen( $title_box ) > 0 ? $title_box : '';

		// Get the post ID to query if metaboxes are available in the post.
		$post_id = get_the_ID();

		// Log what type of widget is utilised.
		if ( Widget_Selection::lumiere_block_widget_isactive( Widget_Selection::BLOCK_WIDGET_NAME ) === true ) {
			// Post 5.8 WordPress.
			$this->logger->log()->debug( '[Lumiere][' . $this->classname . '] Block-based widget found' );
		} elseif ( is_active_widget( false, false, Widget_Selection::WIDGET_NAME, false ) !== false ) {
			$this->logger->log()->debug( '[Lumiere][' . $this->classname . '] Pre-5.8 WordPress widget found' );
		}

		/**
		 * Display the movie according to the post's title (option in -> general -> advanced).
		 * Add the title to the array if auto title widget is enabled and is not disabled for this post
		 */
		if (
			$this->imdb_admin_values['imdbautopostwidget'] === '1'
			&& is_int( $post_id )
			&& get_post_meta( $post_id, 'lumiere_autotitlewidget_perpost', true ) !== 'disabled' // thus the var may not have been created.
		) {
			$movies_array[]['byname'] = esc_html( get_the_title() );
			$this->logger->log()->debug( '[Lumiere][' . $this->classname . '] Auto title widget activated, using the post title ' . sanitize_text_field( get_the_title() ) . ' for querying' );

			// the post-based selection for auto title widget is turned off
		} elseif (
			$this->imdb_admin_values['imdbautopostwidget'] === '1'
			&& is_int( $post_id )
			&& get_post_meta( $post_id, 'lumiere_autotitlewidget_perpost', true ) === 'disabled'
		) {
			$this->logger->log()->debug( '[Lumiere][' . $this->classname . '] Auto title widget is deactivated for this post' );
		}

		// Check if a metabox is available in the post and add it.
		$movies_array[] = is_int( $post_id ) ? $this->maybe_get_lum_post_metada( $post_id ) : null;

		// Clean the array, remove empty multidimensional arrays.
		/** @psalm-var list<array{0?: array{0?: array{0?: array{byname: string}, bymid?: string, byname: string, ...<int<0, max>, array{byname: string}>}, bymid?: string, byname: string, ...<int<0, max>, array{0?: array{byname: string}, bymid?: string, byname: string, ...<int<0, max>, array{byname: string}>}>}, bymid?: string, byname?: string, ...<int<0, max>, array{0?: array{0?: array{byname: string}, bymid?: string, byname: string, ...<int<0, max>, array{byname: string}>}, bymid?: string, byname: string, ...<int<0, max>, array{0?: array{byname: string}, bymid?: string, byname: string, ...<int<0, max>, array{byname: string}>}>}>}> $final_movies_array
		* @phpstan-var array<int, array<string, string>> $final_movies_array
		*/
		$final_movies_array = array_filter( $movies_array, fn( $movies_array ) => ( $movies_array !== null && count( $movies_array ) > 0 ) );

		// Exit if no metadata, no auto title option activated
		if ( $this->imdb_admin_values['imdbautopostwidget'] !== '1' && count( $final_movies_array ) === 0 ) {
			$this->logger->log()->debug( '[Lumiere][' . $this->classname . '] Auto title widget deactivated and no IMDb meta for this post, exiting' );
			return '';
		}

		// Get movie's data from {@link \Lumiere\Frontend\Movie}
		/**
		 * @psalm-var list<array{0?: array{0?: array{0?: array{byname: string}, bymid?: string, byname: string, ...<int<0, max>, array{byname: string}>}, bymid?: string, byname: string, ...<int<0, max>, array{0?: array{byname: string}, bymid?: string, byname: string, ...<int<0, max>, array{byname: string}>}>}, bymid?: string, byname?: string, ...<int<0, max>, array{0?: array{0?: array{byname: string}, bymid?: string, byname: string, ...<int<0, max>, array{byname: string}>}, bymid?: string, byname: string, ...<int<0, max>, array{0?: array{byname: string}, bymid?: string, byname: string, ...<int<0, max>, array{byname: string}>}>}>}>|null $final_movies_array
		 * @phpstan-var array<int<0, max>, array<string, string>> $final_movies_array */
		$movie = $this->movie_class->lumiere_show( $final_movies_array );

		/**
		 * Output the result using a layout wrapper.
		 */
		return $this->lum_wrap_widget_content( $title_box, $movie );
	}

	/**
	 * Query WordPress current post using the PostID to get post metadata
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
				$this->logger->log()->debug( "[Lumiere][$this->classname] Custom field lumiere_widget_movietitle found, using \"$value\" for querying" );
			}

		}

		// Custom field "lumiere_widget_movieid", using the movie ID provided.
		if ( $get_movie_id !== false && count( $get_movie_id ) > 0 ) {
			// Do a loop, even if today the plugin allows only one metabox.
			foreach ( $get_movie_id as $key => $value ) {
				$movies_array['bymid'] = esc_html( $value );
				$this->logger->log()->debug( "[Lumiere][$this->classname] Custom field lumiere_widget_movieid found, using \"$value\" for querying" );
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
	private function lum_wrap_widget_content( string $title_box, string $movie ): string {

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
