<?php declare( strict_types = 1 );
/**
 * Class for displaying movies. This class automatically catches spans. It displays taxonomy links and add taxonomy according to the selected options
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version       3.0
 * @package lumiere-movies
 */

namespace Lumiere\Frontend\Movie;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Frontend\Movie\Movie_Factory;

/**
 * Main class display items (Movie actor, movie source, etc) -- displayed on pages and posts only {@see self::movies_autorized_areas()}
 * It is compatible with Polylang WP plugin
 * It uses ImdbPHP Classes to display movies/people items
 * Plugins are loaded with imdbphp
 *
 * @since 4.4 Child class of Movie_Factory
 *
 * @phpstan-import-type TITLESEARCH_RETURNSEARCH from \Lumiere\Plugins\Manual\Imdbphp
 */
class Movie_Display extends Movie_Factory {

	/**
	 * Singleton: Make sure events are runned once in this class
	 *
	 * @var bool $movie_run_once
	 */
	private bool $movie_run_once = false;

	/**
	 * Keep track of the number of movies called
	 * Static public property meant to be called from another class
	 */
	public static int $nb_of_movies = 0;

	/**
	 * Run the movies
	 *
	 * @return void Build the class
	 * @see \Lumiere\Frontend\Frontend::lumiere_static_start() Call this method
	 */
	public function start(): void {

		// Transform spans into movies.
		add_filter( 'the_content', [ $this, 'lumiere_parse_spans' ] );

		// Transform spans into links to popups.
		add_filter( 'the_content', [ $this, 'lumiere_link_popup_maker' ] );
		add_filter( 'the_excerpt', [ $this, 'lumiere_link_popup_maker' ] );

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
	public function lumiere_show( array $imdb_id_or_title ): string {

		/**
		 * If it is an AMP validation test, exit
		 * Create much cache and may lead to a PHP Fatal error
		 * @psalm-suppress InvalidArrayOffset
		 * @phpstan-ignore function.impossibleType, booleanAnd.alwaysFalse (Call to function array_key_exists() with 'amp' and array...will always evaluate to false)
		 */
		if ( array_key_exists( 'amp', $this->plugins_classes_active ) && $this->plugins_classes_active['amp']->is_amp_validating() === true ) {
			$this->logger->log->debug( '[Movie_Display] This is an AMP validation test, exiting to save server resources' );
			return '';
		}

		$array_movies_with_imdbid = apply_filters( 'lum_find_movie_id', $imdb_id_or_title );
		return apply_filters( 'lum_display_movies_box', $array_movies_with_imdbid );
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
			$this->logger->log->debug( "[Movie_Display] Displaying rows for *$movie_found*" );
			$output .= $this->output_class->front_main_wrapper( $this->imdb_admin_values, parent::factory_items_methods( $movie_found ) );
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
	 * Search movies: if title is provied, search its imdbid; use imdbid otherwise
	 * Is a hook declared in {@see \Lumiere\Frontend\Frontend::__construct()}
	 *
	 * @since 4.3.2 method created
	 * @since 4.4 An array of movies's name without ['bymid'] can be passed
	 *
	 * @param non-empty-array<array-key, array<string, string>|string> $films_array Th
	 * @phpstan-param array<array-key|string, array{bymid?: string, byname?: string}|string> $films_array
	 * @return list<string> Array of results of imdbids
	 */
	public function find_imdb_id( array $films_array ): array {

		self::$nb_of_movies = count( $films_array );
		$movies_found = [];

		// Using singleton to display only once.
		if ( $this->movie_run_once === false ) {
			$this->logger->log->debug( '[Movie_Display] Using the link maker class: ' . str_replace( 'Lumiere\Link_Maker\\', '', get_class( $this->link_maker ) ) );
			$this->logger->log->debug( '[Movie_Display] The following plugins compatible with Lumière! are in use: [' . join( ', ', array_keys( $this->plugins_classes_active ) ) . ']' );
			$this->movie_run_once = true;
		}

		for ( $i = 0; $i < self::$nb_of_movies; $i++ ) {

			// A movie's ID was passed, which is a numeric-string.
			if ( isset( $films_array[ $i ]['bymid'] ) && ctype_digit( $films_array[ $i ]['bymid'] ) ) {
				$movies_found[] = esc_html( strval( $films_array[ $i ]['bymid'] ) );
				$this->logger->log->debug( '[Movie_Display] Storing IMDb ID: *' . $movies_found[0] . '*' );
				continue;
				// A movie's title was provided
			} elseif ( isset( $films_array[ $i ]['byname'] ) ) {
				$movie_name = strtolower( $films_array[ $i ]['byname'] );
				$this->logger->log->debug( '[Movie_Display] ' . ucfirst( 'The following "' . esc_html( $this->imdb_admin_values['imdbseriemovies'] ) ) . '" title provided: ' . esc_html( $movie_name ) );
				// If ['byname'] is not provided, assume a movie's name was in the array (string in the loop)
			} elseif ( is_string( $films_array[ $i ] ) ) {
				$movie_name = strtolower( $films_array[ $i ] );
				$this->logger->log->debug( '[Movie_Display] ' . ucfirst( 'The following "' . esc_html( $this->imdb_admin_values['imdbseriemovies'] ) ) . '" title provided: ' . esc_html( $movie_name ) );
			} else {
				$this->logger->log->debug( '[Movie_Display] Invalid IMDb ID or title provided, aborting' );
				continue;
			}

			// check a the movie title exists.
			$this->logger->log->debug( '[Movie_Display] Searching ' . esc_html( $movie_name ) );

			/** @phpstan-var TITLESEARCH_RETURNSEARCH|null $results */
			$results = strlen( $movie_name ) > 0 ? $this->plugins_classes_active['imdbphp']->search_movie_title(
				$movie_name,
				$this->logger->log,
			) : null;

			// No results were found in imdbphp query.
			if ( ! isset( $results[0] ) ) {
				$this->logger->log->info( '[Movie_Display] No ' . ucfirst( esc_html( $this->imdb_admin_values['imdbseriemovies'] ) ) . ' found for ' . esc_html( $movie_name ) . ', aborting.' );
				continue;
			}

			// Get the first result from the search
			$movies_found[] = esc_html( $results[0]['imdbid'] );
			$this->logger->log->debug( '[Movie_Display] IMDb ID found: *' . $results[0]['imdbid'] . '*' );

		}
		return $movies_found;
	}

	/**
	 * Find in content the span to build the movies
	 * Looks for <span data-lum_movie_maker="[1]"></span> where [1] is movie_title or movie_id
	 *
	 * @since 3.10.2 The function always returns string, no null accepted -- PHP8.2 compatibility
	 * @since 4.2.3 The function will return with the content if not executed in autorized area
	 *
	 * @param null|string $content HTML span tags + text inside
	 * @return string
	 */
	public function lumiere_parse_spans( ?string $content ): string {

		// if no content is available, abort.
		if ( ! isset( $content ) ) {
			return '';
		}

		// if not run on page or post, return the content untouched.
		if ( $this->movies_autorized_areas() === false ) {
			return $content;
		}

		$pattern_movid_id = '~<span data-lum_movie_maker="movie_id">(.+?)<\/span>~';
		if ( preg_match( $pattern_movid_id, $content, $match ) === 1 ) {
			$content = preg_replace_callback( $pattern_movid_id, [ $this, 'lumiere_parse_spans_callback_id' ], $content ) ?? $content;
		}

		$pattern_movid_title = '~<span data-lum_movie_maker="movie_title">(.+?)<\/span>~';
		if ( preg_match( $pattern_movid_title, $content, $match ) === 1 ) {
			$content = preg_replace_callback( $pattern_movid_title, [ $this, 'lumiere_parse_spans_callback_title' ], $content ) ?? $content;
		}

		return $content;
	}

	/**
	 * Callback for movies by IMDb ID
	 *
	 * @param array<int, string> $block_span
	 */
	private function lumiere_parse_spans_callback_id( array $block_span ): string {
		$imdb_id_or_title = [];
		$imdb_id_or_title[]['bymid'] = esc_html( $block_span[1] );
		return $this->lumiere_show( $imdb_id_or_title );
	}

	/**
	 * Callback for movies by imdb title
	 *
	 * @param array<string> $block_span
	 */
	private function lumiere_parse_spans_callback_title( array $block_span ): string {
		$imdb_id_or_title = [];
		$imdb_id_or_title[]['byname'] = esc_html( $block_span[1] );
		return $this->lumiere_show( $imdb_id_or_title );
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
	public function lumiere_link_popup_maker( ?string $text ): ?string {

		if ( ! isset( $text ) ) {
			return null;
		}

		// if not run on page or post, return the content untouched.
		if ( $this->movies_autorized_areas() === false ) {
			return $text;
		}

		// replace all occurences of <span class="lumiere_link_maker">(.+?)<\/span> into internal popup
		$pattern = '~<span[^>]*data-lum_link_maker="popup"[^>]*>(.+)<\/span>~iU';
		$text = preg_replace_callback( $pattern, [ $this, 'lumiere_build_popup_link' ], $text ) ?? $text;

		// Kept for compatibility purposes:  <!--imdb--> still works -- it's really old, should be @deprecated
		$pattern_two = '~<!--imdb-->(.*?)<!--\/imdb-->~i';
		$text = preg_replace_callback( $pattern_two, [ $this, 'lumiere_build_popup_link' ], $text ) ?? $text;

		return $text;
	}

	/**
	 * Replace <span data-lum_link_maker="popup"> by a link
	 *
	 * @param array<int, string> $correspondances parsed data
	 * @return string the link replaced
	 *
	 * @since 4.1 Replaced preg_match() by str_replace() and simplified the method
	 */
	private function lumiere_build_popup_link( array $correspondances ): string {
		$result = isset( $correspondances[0] )
			? str_replace( $correspondances[0], $this->link_maker->popup_film_link( $correspondances ), $correspondances[0] )
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
		return $this->lumiere_show( $imdb_id_or_title );
	}
}
