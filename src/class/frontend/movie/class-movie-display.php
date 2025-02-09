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

use Lumiere\Frontend\Movie\Movie_Data;
use Lumiere\Plugins\Plugins_Start;
use Exception;

/**
 * Main class display items (Movie actor, movie source, etc) -- displayed on pages and posts only {@see self::movies_autorized_areas()}
 * It is compatible with Polylang WP plugin
 * It uses ImdbPHP Classes to display movies/people items
 * Plugins are loaded with imdbphp
 *
 * @since 4.4 Child class of Movie_Data
 *
 * @phpstan-import-type TITLESEARCH_RETURNSEARCH from \Lumiere\Plugins\Manual\Imdbphp
 * @phpstan-import-type PLUGINS_ALL_CLASSES from \Lumiere\Plugins\Plugins_Detect
 * @phpstan-import-type PLUGINS_ALL_KEYS from \Lumiere\Plugins\Plugins_Detect
 * @phpstan-import-type PLUGINS_AUTO_KEYS from \Lumiere\Plugins\Plugins_Detect
 * @phpstan-import-type PLUGINS_AUTO_CLASSES from \Lumiere\Plugins\Plugins_Detect
 * @phpstan-import-type PLUGINS_MANUAL_KEYS from \Lumiere\Plugins\Plugins_Detect
 * @phpstan-import-type PLUGINS_MANUAL_CLASSES from \Lumiere\Plugins\Plugins_Detect
 */
class Movie_Display extends Movie_Data {

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
	 * Lumière plugins started
	 *
	 * @var array<string, object>
	 * @phpstan-var array{'imdbphp': PLUGINS_MANUAL_CLASSES, PLUGINS_AUTO_KEYS?: PLUGINS_AUTO_CLASSES}
	 */
	private array $plugins_classes_active;

	/**
	 * Class constructor
	 */
	public function __construct(
		private Plugins_Start $plugins = new Plugins_Start( [ 'imdbphp' ] ),
	) {

		parent::__construct();

		/**
		 * @psalm-suppress InvalidPropertyAssignmentValue
		 * @phpstan-ignore assign.propertyType (Array does not have offset 'imdbphp' => find better notation)
		 */
		$this->plugins_classes_active = $this->plugins->plugins_classes_active;

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
	 * Static call of the current class Movie
	 *
	 * @return void Build the class
	 * @see \Lumiere\Frontend\Frontend::lumiere_static_start() Call this method
	 */
	public static function start(): void {
		$start = new self();
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
			$this->logger->log->debug( '[Lumiere][Movie] This is an AMP validation test, exiting to save server resources' );
			return '';
		}

		$output = '';
		$movies_searched = $this->search_categorized_movies( $imdb_id_or_title );

		foreach ( $movies_searched as $movie_found ) {

			$this->logger->log->debug( "[Lumiere][Movie] Displaying rows for *$movie_found*" );

			$output .= "\n\t\t\t\t\t\t\t\t\t" . '<!-- Lumière! movies plugin -->';
			$output .= "\n\t<div class='lum_results_frame";

			// add dedicated class for themes
			$output .= ' lum_results_frame_' . $this->imdb_admin_values['imdbintotheposttheme'] . "'>";

			$output .= $this->factory_items_methods( $movie_found );
			$output .= "\n\t</div>";
			$output .= "\n\t\t\t\t\t\t\t\t\t" . '<!-- /Lumière! movies plugin -->';
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
	 *
	 * @param non-empty-array<array-key, array<string, string>> $films_array
	 * @phpstan-param array<array-key, array{bymid?: string, byname?: string}> $films_array
	 * @return list<string> Array of results of imdbids
	 * @since 4.3.2
	 */
	private function search_categorized_movies( array $films_array ): array {

		self::$nb_of_movies = count( $films_array );
		$movies_found = [];

		// Using singleton to display only once.
		if ( $this->movie_run_once === false ) {
			$this->logger->log->debug( '[Lumiere][Movie] Using the link maker class: ' . str_replace( 'Lumiere\Link_Makers\\', '', get_class( $this->link_maker ) ) );
			$this->logger->log->debug( '[Lumiere][Movie] The following plugins compatible with Lumière! are in use: [' . join( ', ', array_keys( $this->plugins_classes_active ) ) . ']' );
			$this->movie_run_once = true;
		}

		for ( $i = 0; $i < self::$nb_of_movies; $i++ ) {

			// A movie's title has been specified, get its imdbid.
			if ( isset( $films_array[ $i ]['byname'] ) && strlen( $films_array[ $i ]['byname'] ) > 0 ) {

				$film = strtolower( $films_array[ $i ]['byname'] ); // @since 4.0 lowercase, less cache used.

				$this->logger->log->debug( '[Lumiere][Movie] ' . ucfirst( 'The following "' . esc_html( $this->imdb_admin_values['imdbseriemovies'] ) ) . '" title provided: ' . esc_html( $film ) );

				// check a the movie title exists.
				$this->logger->log->debug( '[Lumiere][Movie] searching for ' . $film );

				/** @phpstan-var TITLESEARCH_RETURNSEARCH $results */
				$results = $this->plugins_classes_active['imdbphp']->search_movie_title(
					esc_html( $film ),
					$this->logger->log,
				);

				// No results were found in imdbphp query.
				if ( ! isset( $results[0] ) ) {
					$this->logger->log->info( '[Lumiere][Movie] No ' . ucfirst( esc_html( $this->imdb_admin_values['imdbseriemovies'] ) ) . ' found for ' . $film . ', aborting.' );
					continue;
				}

				// Get the first result from the search
				$movies_found[] = esc_html( $results[0]['imdbid'] );
				$this->logger->log->debug( '[Lumiere][Movie] IMDb ID found: *' . $results[0]['imdbid'] . '*' );

				// A movie's ID was passed.
			} elseif ( isset( $films_array[ $i ]['bymid'] ) ) {
				$movies_found[] = esc_html( strval( $films_array[ $i ]['bymid'] ) );
				$this->logger->log->debug( '[Lumiere][Movie] IMDb ID provided: *' . $movies_found[0] . '*' );
			}

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

	/**
	 * Build the methods to be called in class Movie_Data
	 * Use imdbphp class to get the Title class
	 *
	 * @param string $mid_premier_resultat IMDb ID, not as int since it loses its heading 0s
	 * @throws Exception
	 */
	private function factory_items_methods( string $mid_premier_resultat ): string {

		$outputfinal = '';

		// Find the Title based on $mid_premier_resultat.
		$title_mid_object = $this->plugins_classes_active['imdbphp']->get_title_class(
			esc_html( $mid_premier_resultat ),
			$this->logger->log,
		);

		foreach ( $this->imdb_data_values['imdbwidgetorder'] as $data_detail => $order ) {

			if (
				// Use order to select the position of the data detail.
				isset( $this->imdb_data_values['imdbwidgetorder'][ $data_detail ] )
				&& $this->imdb_data_values['imdbwidgetorder'][ $data_detail ] === $order
				// Is the data detail activated?
				/** @psalm-suppress PossiblyUndefinedArrayOffset (even adding an extra check doesn't suppress the error) */
				&& $this->imdb_data_values[ 'imdbwidget' . $data_detail ] === '1'
			) {
				// Build the method name according to the data detail name.
				$method = 'get_item_' . $data_detail;

				// Something bad happened.
				if ( ! method_exists( __CLASS__, $method ) ) {
					throw new Exception( 'The method ' . esc_html( $method ) . ' does not exist in class ' . __CLASS__ );
				}

				// Build the final class+method with the movie_object and child class.
				$outputfinal .= $this->movie_layout->final_div_wrapper(
					parent::$method( $title_mid_object, $data_detail ),
					$data_detail,
					$this->imdb_admin_values
				);
			}
		}
		return $outputfinal;
	}
}
