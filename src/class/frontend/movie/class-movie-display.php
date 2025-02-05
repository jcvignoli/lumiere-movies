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
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Frontend\Main;
use Lumiere\Frontend\Movie\Movie_Data;
use Lumiere\Plugins\Plugins_Start;

/**
 * The class uses Data class to display items (Movie actor, movie source, etc) -- displayed on pages and posts only {@see self::movies_autorized_areas()}
 * It is compatible with Polylang WP plugin
 * It uses ImdbPHP Classes to display movies/people items
 *
 * @phpstan-import-type TITLESEARCH_RETURNSEARCH from \Lumiere\Plugins\Manual\Imdbphp
 * @phpstan-import-type PLUGINS_ALL_CLASSES from \Lumiere\Plugins\Plugins_Detect
 * @phpstan-import-type PLUGINS_ALL_KEYS from \Lumiere\Plugins\Plugins_Detect
 * @phpstan-import-type PLUGINS_AUTO_KEYS from \Lumiere\Plugins\Plugins_Detect
 * @phpstan-import-type PLUGINS_AUTO_CLASSES from \Lumiere\Plugins\Plugins_Detect
 * @phpstan-import-type PLUGINS_MANUAL_KEYS from \Lumiere\Plugins\Plugins_Detect
 * @phpstan-import-type PLUGINS_MANUAL_CLASSES from \Lumiere\Plugins\Plugins_Detect
 */
class Movie_Display {

	/**
	 * Traits
	 */
	use Main;

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
		private Plugins_Start $plugins = new Plugins_Start( [ 'imdbphp' ] )
	) {

		/**
		 * @psalm-suppress InvalidPropertyAssignmentValue
		 * @phpstan-ignore assign.propertyType (Array does not have offset 'imdbphp' => find better notation)
		 */
		$this->plugins_classes_active = $this->plugins->plugins_classes_active;

		// Construct Frontend Main trait.
		$this->start_main_trait();

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

			$output .= $this->lumiere_methods_factory( $movie_found );
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
			? str_replace( $correspondances[0], $this->link_maker->lumiere_popup_film_link( $correspondances ), $correspondances[0] )
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
	 * @param Movie_Data $movie_data_class Movie_Data child class, instanciated by default
	 */
	private function lumiere_methods_factory( string $mid_premier_resultat, Movie_Data $movie_data_class = new Movie_Data() ): string {

		$outputfinal = '';

		// Find the Title based on $mid_premier_resultat.
		$title_object = $this->plugins_classes_active['imdbphp']->get_title_class(
			esc_html( $mid_premier_resultat ), // The IMDb ID.
			$this->logger->log,
		);

		foreach ( $this->imdb_data_values['imdbwidgetorder'] as $data_detail => $order ) {

			if (
				// Use order to select the position of the data detail.
				$this->imdb_data_values['imdbwidgetorder'][ $data_detail ] === $order
				// Is the data detail activated?
				&& $this->imdb_data_values[ 'imdbwidget' . $data_detail ] === '1'
			) {
				// Build the method name according to the data detail name.
				$method = 'lum_movies_' . $data_detail;

				// Build the final class+method with the movie_object and child class.
				if ( ! method_exists( $movie_data_class, $method ) ) {
					$this->logger->log->warning( '[Lumiere][Movie] The method ' . $method . ' does not exist in class ' . get_class( $movie_data_class ) . ', aborting' );
					exit( 1 );
				}
				$outputfinal .= $this->movie_wrapper( $movie_data_class->$method( $title_object ), $data_detail );
			}
		}
		return $outputfinal;
	}

	/**
	 * Function wrapping with <div> the text
	 *
	 * @param string $html Text to wrap
	 * @param string $item The item to transform, such as director, title, etc
	 * @return string
	 */
	private function movie_wrapper( string $html, string $item ): string {

		if ( strlen( $html ) === 0 ) {
			return '';
		}

		$outputfinal = '';
		$item = sanitize_text_field( $item );
		$item_caps = strtoupper( $item );

		$outputfinal .= "\n\t\t\t\t\t\t\t" . '<!-- ' . $item . ' -->';

		// title doesn't take item 'lumiere-lines-common' as a class
		if ( $item !== 'title' ) {
			$outputfinal .= "\n\t\t" . '<div class="lumiere-lines-common';
		} else {
			$outputfinal .= "\n\t\t" . '<div class="imdbelement' . $item_caps;
		}

		$outputfinal .= ' lumiere-lines-common_' . $this->imdb_admin_values['imdbintotheposttheme'] . ' imdbelement' . $item_caps . '_' . $this->imdb_admin_values['imdbintotheposttheme'];
		$outputfinal .= '">';
		$outputfinal .= $html;
		$outputfinal .= "\n\t\t" . '</div>';

		return $outputfinal;
	}

	/**
	 * Insert taxonomy and return final options
	 *
	 * @since 4.0 Rewritten taxonomy system, not using Polylang anymore, links between languages created, hierarchical taxonomy terms
	 * @since 4.4 Splitted Taxonomy from layout, now the method is meant to create and get taxonomy details only
	 *
	 * @param string $type_item The general category of the item, ie 'director', 'color'
	 * @param string $taxonomy_term The name of the first string to display, ie "Stanley Kubrick"
	 * @return array<string, string>
	 * @phstan-return array{'custom_taxonomy_fullname': string, 'taxonomy_term': string}
	 */
	protected function create_taxonomy_options( string $type_item, string $taxonomy_term ): array {

		$taxonomy_term = esc_html( $taxonomy_term );
		$custom_taxonomy_fullname = esc_html( $this->imdb_admin_values['imdburlstringtaxo'] . $type_item ); // ie 'lumiere-director'
		$page_id = get_the_ID();

		/**
		 * Insert the taxonomies, add a relationship if a previous taxo exists
		 * Insert the current language displayed and the hierarchical value (child_of) if a previous taxo exists (needs register taxonomy with hierarchical)
		 */
		if ( $page_id !== false && taxonomy_exists( $custom_taxonomy_fullname ) ) {

			// delete if exists, for debugging purposes
			# $array_term_existing = get_term_by('name', $taxonomy_term, $custom_taxonomy_fullname );
			# if ( $array_term_existing )
			#	 wp_delete_term( $array_term_existing->term_id, $custom_taxonomy_fullname) ;

			$existent_term = term_exists( $taxonomy_term, $custom_taxonomy_fullname );

			// The term doesn't exist in the post.
			if ( $existent_term === null ) {
				$term_inserted = wp_insert_term( $taxonomy_term, $custom_taxonomy_fullname );
				$term_for_log = wp_json_encode( $term_inserted );
				if ( $term_for_log !== false ) {
					$this->logger->log->debug( '[Lumiere][Movie] Taxonomy term *' . $taxonomy_term . '* added to *' . $custom_taxonomy_fullname . '* (association numbers ' . $term_for_log . ' )' );
				}
			}

			// If no term was inserted, take the current term.
			$term_for_set_object = $term_inserted ?? $taxonomy_term;

			/**
			 * Taxo terms could be inserted without error (it doesn't exist already), so add a relationship between the taxo and the page id number
			 * wp_set_object_terms() is almost always executed in order to add new relationships even if a new term wasn't inserted
			 */
			if ( ! $term_for_set_object instanceof \WP_Error ) {
				$term_taxonomy_id = wp_set_object_terms( $page_id, $term_for_set_object, $custom_taxonomy_fullname, true );
				// $this->logger->log->debug( '[Lumiere][Movie] Check (and made if needed) association for term_taxonomy_id ' . json_encode( $term_taxonomy_id ) );
			}
		}

		return [
			'custom_taxonomy_fullname' => $custom_taxonomy_fullname,
			'taxonomy_term' => $taxonomy_term,
		];
	}

	/**
	 * Layout selection depends on $item_line_name value
	 * If data was passed, use the first layout, if null was passed, use the second layout
	 * First layout display two items per row
	 * Second layout display items comma-separated
	 *
	 * @param string $movie_title
	 * @param array<string, string> $taxo_options
	 * @phstan-param array{'custom_taxonomy_fullname': string, 'taxonomy_term': string} $taxo_options
	 * @param string|null $item_line_name Null if the second layout should be utilised
	 * @return string
	 */
	protected function get_layout_items( string $movie_title, array $taxo_options, ?string $item_line_name = null ): string {

		$lang = strtok( get_bloginfo( 'language' ), '-' );
		$lang_term = $lang !== false ? $lang : '';
		$output = '';

		// Build the id for the link <a id="$link_id">
		$link_id = esc_html( $movie_title ) . '_' . esc_html( $lang_term ) . '_' . esc_html( $taxo_options['custom_taxonomy_fullname'] ) . '_' . esc_html( $taxo_options['taxonomy_term'] );
		$link_id_cleaned = preg_replace( "/^'|[^A-Za-z0-9\'-]|'|\-$/", '_', $link_id ) ?? '';
		$link_id_final = 'link_taxo_' . strtolower( str_replace( '-', '_', $link_id_cleaned ) );

		// layout one: display the layout for two items per row, ie actors, writers, producers
		if ( is_string( $item_line_name ) === true ) {
			$output .= "\n\t\t\t" . '<div align="center" class="lumiere_container">';
			$output .= "\n\t\t\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';
			$output .= "\n\t\t\t\t\t<a id=\"" . $link_id_final . '" class="lum_link_taxo_page" href="'
					. esc_url( $this->create_taxonomy_link( $taxo_options['taxonomy_term'], $taxo_options['custom_taxonomy_fullname'] ) )
					. '" title="' . esc_html__( 'Find similar taxonomy results', 'lumiere-movies' )
					. '">';
			$output .= "\n\t\t\t\t\t" . $taxo_options['taxonomy_term'];
			$output .= "\n\t\t\t\t\t" . '</a>';
			$output .= "\n\t\t\t\t" . '</div>';
			$output .= "\n\t\t\t\t" . '<div class="lumiere_align_right lumiere_flex_auto">';
			$output .= preg_replace( '/\n/', '', $item_line_name ); // remove breaking space.
			$output .= "\n\t\t\t\t" . '</div>';
			$output .= "\n\t\t\t" . '</div>';
			return $output;
		}

		// layout two: display the layout for all details separated by commas, ie keywords
		$output .= '<a id="' . $link_id_final . '" class="lum_link_taxo_page" '
				. 'href="' . esc_url( $this->create_taxonomy_link( $taxo_options['taxonomy_term'], $taxo_options['custom_taxonomy_fullname'] ) )
				. '" '
				. 'title="' . esc_html__( 'Find similar taxonomy results', 'lumiere-movies' ) . '">';
		$output .= $taxo_options['taxonomy_term'];
		$output .= '</a>';
		return $output;
	}

	/**
	 * Create an html link for taxonomy using the name passed
	 *
	 * @since 4.0 New function taking out pieces from self::lumiere_make_display_taxonomy()
	 *
	 * @param string $name_searched The name searched, such as 'Stanley Kubrick'
	 * @param string $taxo_category The taxonomy category used, such as 'lumiere-director'
	 * @return string The WordPress full HTML link for the name with that category
	 */
	private function create_taxonomy_link( string $name_searched, string $taxo_category ): string {
		$find_term = get_term_by( 'name', $name_searched, $taxo_category );
		$taxo_link = $find_term instanceof \WP_Term ? get_term_link( $find_term->term_id, $taxo_category ) : '';
		return $taxo_link instanceof \WP_Error ? '' : $taxo_link;
	}
}
