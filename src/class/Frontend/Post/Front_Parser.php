<?php declare( strict_types = 1 );
/**
 * Class for displaying movies. This class automatically catches spans. It displays taxonomy links and add taxonomy according to the selected options
 *
 * @copyright (c) 2022, Lost Highway
 *
 * @version       3.0
 * @package       lumieremovies
 */

namespace Lumiere\Frontend\Post;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Config\Get_Options;
use Lumiere\Frontend\Post\Person_Factory;
use Lumiere\Frontend\Post\Movie_Factory;
use Lumiere\Frontend\Layout\Output;
use Lumiere\Plugins\Plugins_Start;
use Lumiere\Frontend\Main;

/**
 * Main class display items (Movie actor, movie source, etc) -- displayed on pages and posts only {@see self::movies_autorized_areas()}
 * It is compatible with Polylang WP plugin
 * It uses ImdbPHP Classes to display movies/people items
 * Plugins are loaded with imdbphp
 *
 * @phpstan-import-type TITLESEARCH_RETURNSEARCH from \Lumiere\Plugins\Manual\Imdbphp
 * @phpstan-import-type PLUGINS_ALL_CLASSES from \Lumiere\Plugins\Plugins_Detect
 * @phpstan-import-type PLUGINS_ALL_KEYS from \Lumiere\Plugins\Plugins_Detect
 * @phpstan-import-type PLUGINS_AUTO_KEYS from \Lumiere\Plugins\Plugins_Detect
 * @phpstan-import-type PLUGINS_AUTO_CLASSES from \Lumiere\Plugins\Plugins_Detect
 * @phpstan-import-type PLUGINS_MANUAL_KEYS from \Lumiere\Plugins\Plugins_Detect
 * @phpstan-import-type PLUGINS_MANUAL_CLASSES from \Lumiere\Plugins\Plugins_Detect
 */
class Front_Parser {

	/**
	 * Traits
	 */
	use Main;

	/**
	 * Lumière plugins started
	 *
	 * @var array<string, object>
	 * @phpstan-var array{'imdbphp': PLUGINS_MANUAL_CLASSES, PLUGINS_AUTO_KEYS?: PLUGINS_AUTO_CLASSES}
	 */
	protected array $plugins_classes_active;

	/**
	 * Constructor
	 */
	public function __construct(
		protected Plugins_Start $plugins = new Plugins_Start( [ 'imdbphp' ] ),
		protected Output $output_class = new Output(),
	) {
		// Construct Frontend Main trait with options and links.
		$this->start_main_trait();
		$this->start_linkmaker();

		/**
		 * @psalm-suppress InvalidPropertyAssignmentValue
		 * @phpstan-ignore assign.propertyType (Array does not have offset 'imdbphp' => find better notation)
		 */
		$this->plugins_classes_active = $this->plugins->plugins_classes_active;
	}

	/**
	 * Run the movies
	 *
	 * @return void Build the class
	 * @see \Lumiere\Frontend\Frontend::lumiere_static_start() Call this method
	 */
	public function start(): void {

		// Transform spans into movies.
		add_filter( 'the_content', [ $this, 'parse_spans' ] );

		// Transform spans into links to popups.
		add_filter( 'the_content', [ $this, 'link_popup_maker' ] );
		add_filter( 'the_excerpt', [ $this, 'link_popup_maker' ] );

		/**
		 * Detect the shortcodes [imdblt][/imdblt] and [imdbltid][/imdbltid] to display the movies, old way
		 * @deprecated 3.5 kept for compatibility purpose
		 */
		add_shortcode( 'imdblt', [ $this, 'parse_lumiere_tag_transform' ] );
		add_shortcode( 'imdbltid', [ $this, 'parse_lumiere_tag_transform_id' ] );
	}

	/**
	 * Search the movie and output the results
	 *
	 * @since 3.8 Extra logs are shown once only using singleton $this->movie_run_once
	 * @since 4.3.2 added is_amp_validating() method
	 *
	 * @phpstan-param array<array-key, array{bymid?: string, byname?: string}> $imdb_id_or_title
	 */
	public function display_movies( array $imdb_id_or_title ): string {

		/**
		 * If it is an AMP validation test, exit
		 * Create much cache and may lead to a PHP Fatal error
		 * @phpstan-ignore function.impossibleType, booleanAnd.alwaysFalse (Call to function array_key_exists() with 'amp' and array...will always evaluate to false)
		 */
		if ( array_key_exists( 'amp', $this->plugins_classes_active ) && $this->plugins_classes_active['amp']->is_amp_validating() === true ) {
			$this->logger->log?->debug( '[Front_Parser] This is an AMP validation test, exiting to save server resources' );
			return '';
		}

		$array_movies_with_imdbid = apply_filters( 'lum_find_movie_id', $imdb_id_or_title );
		return apply_filters( 'lum_display_movies_box', $array_movies_with_imdbid );
	}

	/**
	 * Search the persons and output the results
	 *
	 * @since 3.8 Extra logs are shown once only using singleton $this->movie_run_once
	 * @since 4.3.2 added is_amp_validating() method
	 *
	 * @phpstan-param array<array-key, array{bymid?: string, byname?: string}> $imdb_id_or_title
	 */
	public function display_persons( array $imdb_id_or_title ): string {

		/**
		 * If it is an AMP validation test, exit
		 * Create much cache and may lead to a PHP Fatal error
		 * @phpstan-ignore function.impossibleType, booleanAnd.alwaysFalse (Call to function array_key_exists() with 'amp' and array...will always evaluate to false)
		 */
		if ( array_key_exists( 'amp', $this->plugins_classes_active ) && $this->plugins_classes_active['amp']->is_amp_validating() === true ) {
			$this->logger->log?->debug( '[Front_Parser] This is an AMP validation test, exiting to save server resources' );
			return '';
		}

		$array_persons_with_imdbid = apply_filters( 'lum_find_person_id', $imdb_id_or_title );
		return apply_filters( 'lum_display_persons_box', $array_persons_with_imdbid );
	}

	/**
	 * Display the movies in the box
	 * Is a hook declared in {@see \Lumiere\Frontend\Frontend::__construct()}
	 *
	 * @since 4.4 method created
	 *
	 * @param array<array-key, string> $movies_searched
	 */
	public function lum_display_movies_box( array $movies_searched ): string {
		$output = '';
		foreach ( $movies_searched as $movie_found ) {
			$this->logger->log?->debug( "[Front_Parser] Displaying rows for *$movie_found*" );
			$output .= $this->output_class->front_main_wrapper( $this->imdb_admin_values, ( new Movie_Factory() )->factory_movie_items_methods( $movie_found ) );
		}
		return $output;
	}

	/**
	 * Display the persons in the box
	 * Is a hook declared in {@see \Lumiere\Frontend\Frontend::__construct()}
	 *
	 * @since 4.6 method created
	 *
	 * @param array<array-key, string> $persons_searched
	 */
	public function lum_display_persons_box( array $persons_searched ): string {
		$output = '';
		foreach ( $persons_searched as $person_found ) {
			$this->logger->log?->debug( "[Front_Parser] Displaying rows for *$person_found*" );
			$output .= $this->output_class->front_main_wrapper( $this->imdb_admin_values, ( new Person_Factory() )->factory_person_items_methods( $person_found ) );
		}
		return $output;
	}

	/**
	 * List of autorized areas where the class will run
	 *
	 * @since 4.2.3
	 * @return bool True if page is autorized
	 */
	private function movies_autorized_areas(): bool {
		return is_singular( [ 'post', 'page' ] );
	}

	/**
	 * Find in content the span to build the movies
	 * Looks for <span data-lum_movie_maker="[1]"></span> where [1] is movie_title or movie_id
	 *
	 * @since 3.10.2 The function always returns string, no null accepted -- PHP8.2 compatibility
	 * @since 4.2.3 The function will return with the content if not executed in autorized area
	 * @since 4.6.1 Use preg_replace_callback() instead of preg_replace_callback_array(), foreach loop, use {@see Get_Options::get_lum_all_type_search()}
	 *
	 * @param null|string $content HTML span tags + text inside
	 * @return string The spans have been replaced with movies/persons boxes
	 */
	public function parse_spans( ?string $content ): string {

		// if no content is available, abort.
		if ( ! isset( $content ) ) {
			return '';
		}

		// if not run on page or post, return the content untouched.
		if ( $this->movies_autorized_areas() === false ) {
			return $content;
		}

		foreach ( Get_Options::get_lum_all_type_search() as $key => $value ) {
			$value_array = explode( '_', $value['value'] );
			$col1 = $value_array[1] ?? ''; // Either movie or person.
			$col2 = isset( $value_array[2] ) && str_contains( $value_array[2], 'id' ) ? 'bymid' : 'byname';
			$callback_name = 'replace_' . $col1 . '_spans';
			$content = preg_replace_callback(
				'~<span data-lum_movie_maker="' . $value['value'] . '">(.+?)<\/span>~',
				function( $match ) use( $col2, $callback_name ): string {
					return $this->{$callback_name}( $match[1], $col2 );
				},
				$content
			) ?? $content;
		}

		return $content;
	}

	/**
	 * Callback for movies, helper method
	 * It applies method {@see Front_Parser::display_movies()} on the text found
	 *
	 * @see Front_Parser::parse_spans() use this method
	 *
	 * @param string $text_found Text found inside <span></span>
	 * @param 'byname'|'bymid' $search_type Searching type of the movie
	 */
	private function replace_movie_spans( string $text_found, string $search_type ): string {
		$imdb_id_or_title = [];
		$imdb_id_or_title[][ $search_type ] = esc_html( $text_found );
		return $this->display_movies( $imdb_id_or_title );
	}

	/**
	 * Callback for persons, helper method
	 * It applies method {@see Front_Parser::display_persons()} on the text found
	 *
	 * @see Front_Parser::parse_spans() use this method
	 *
	 * @param string $text_found Text found inside <span></span>
	 * @param 'byname'|'bymid' $search_type Searching type of the person
	 */
	private function replace_person_spans( string $text_found, string $search_type ): string {
		$imdb_id_or_title = [];
		$imdb_id_or_title[][ $search_type ] = esc_html( $text_found );
		return $this->display_persons( $imdb_id_or_title );
	}

	/**
	 * Replace [imdblt] shortcode by the movie
	 * @deprecated 3.5, kept for compatibility purposes
	 *
	 * @param string|array<string> $atts array of attributes
	 * @param null|string $content shortcode content or null if not set
	 */
	public function parse_lumiere_tag_transform( string|array $atts, ?string $content ): string {

		// if not run on page or post, return the content untouched.
		if ( $this->movies_autorized_areas() === false ) {
			return $content ?? '';
		}

		_deprecated_function( 'shortcode imdblt', '3.5', '"span" with data-lum_movie_maker="movie_title" to embed your movies' );
		return $this->lumiere_external_call( $content, '' );
	}

	/**
	 * Replace [imdbltid] shortcode by the movie
	 * @deprecated 3.5, kept for compatibility purposes
	 *
	 * @param string|array<string> $atts
	 * @param null|string $content shortcode content or null if not set
	 */
	public function parse_lumiere_tag_transform_id( $atts, ?string $content ): string {

		// if not run on page or post, return the content untouched.
		if ( $this->movies_autorized_areas() === false ) {
			return $content ?? '';
		}

		_deprecated_function( 'shortcode imdbltid', '3.5', '"span" with data-lum_movie_maker="movie_id" to embed your movies' );
		return $this->lumiere_external_call( '', $content );
	}

	/**
	 * Replace <span class="lumiere_link_maker"(anything)?></span> with links
	 *
	 * @param null|string $text parsed data
	 * @return null|string Null if text was already null, text otherwhise
	 * @since 4.1 Added the possibility to have some text after the data with [^>]*
	 * @since 4.2.3 The function will return if not executed in autorized area
	 */
	public function link_popup_maker( ?string $text ): ?string {

		if ( ! isset( $text ) ) {
			return null;
		}

		// if not run on page or post, return the content untouched.
		if ( $this->movies_autorized_areas() === false ) {
			return $text;
		}

		return preg_replace_callback_array(
			[
				// replace all occurences of <span class="lumiere_link_maker">(.+?)<\/span> into internal popup
				'~<span[^>]*data-lum_link_maker="popup"[^>]*>(.+)<\/span>~iU' => function ( array $match ): string {
					return $this->get_popup_link( $match );
				},
				// Kept for compatibility purposes:  <!--imdb--> still works -- it's really old, should be @deprecated
				 '~<!--imdb-->(.*?)<!--\/imdb-->~i' => function ( array $match ): string {
					return $this->get_popup_link( $match );
				 },
			],
			$text
		) ?? $text;
	}

	/**
	 * Replace <span data-lum_link_maker="popup"> by a link
	 *
	 * @param array<int, string> $correspondances parsed data
	 * @return string the link replaced
	 *
	 * @since 4.1 Replaced preg_match() by str_replace() and simplified the method
	 */
	private function get_popup_link( array $correspondances ): string {
		$result = isset( $correspondances[0] )
			? str_replace( $correspondances[0], $this->link_maker->get_popup_film_title( $correspondances[1], 'lum_link_with_movie' /* the class that adds the movie ico */ ), $correspondances[0] )
			: '';
		return $result;
	}

	/**
	 * Function external call (ie, inside a post)
	 * Utilized to build from shortcodes
	 * @obsolete since 3.1 not using shortcodes anymore, kept for compatibility purposes -- not marking @deprecated, which return phan error
	 *
	 * @param string|null $moviename
	 * @param string|null $filmid
	 */
	public function lumiere_external_call( ?string $moviename, ?string $filmid ): string {

		$imdb_id_or_title = [];

		//  Call with the parameter - imdb movie name (imdblt)
		if ( isset( $moviename ) && strlen( $moviename ) > 0 ) {
			$imdb_id_or_title[]['byname'] = esc_html( $moviename );
		}

		//  Call with the parameter - imdb movie id (imdbltid)
		if ( isset( $filmid ) && strlen( $filmid ) > 0 ) {
			$imdb_id_or_title[]['bymid'] = esc_html( $filmid );
		}
		/** @psalm-var array<array-key, array{bymid?: string, byname?: string}> $imdb_id_or_title */
		return $this->display_movies( $imdb_id_or_title );
	}
}
