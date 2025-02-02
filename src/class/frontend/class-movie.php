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

namespace Lumiere\Frontend;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Frontend\Main;
use Lumiere\Frontend\Movie_Data;

/**
 * The class uses Movie_Data class to display data (Movie actor, movie source, etc) -- displayed on pages and posts only {@see self::movies_autorized_areas()}
 * It is compatible with Polylang WP plugin
 * It uses ImdbPHP Classes to display movies/people data
 *
 * @phpstan-import-type TITLESEARCH_RETURNSEARCH from \Lumiere\Plugins\Manual\Imdbphp
 * @phpstan-import-type PLUGINS_ALL_CLASSES from \Lumiere\Plugins\Plugins_Detect
 * @phpstan-import-type PLUGINS_ALL_KEYS from \Lumiere\Plugins\Plugins_Detect
 * @phpstan-import-type PLUGINS_AUTO_KEYS from \Lumiere\Plugins\Plugins_Detect
 * @phpstan-import-type PLUGINS_AUTO_CLASSES from \Lumiere\Plugins\Plugins_Detect
 * @phpstan-import-type PLUGINS_MANUAL_KEYS from \Lumiere\Plugins\Plugins_Detect
 * @phpstan-import-type PLUGINS_MANUAL_CLASSES from \Lumiere\Plugins\Plugins_Detect
 */
class Movie {

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
	 * @phpstan-var array{'imdbphp': PLUGINS_MANUAL_CLASSES, PLUGINS_AUTO_KEYS: PLUGINS_AUTO_CLASSES}
	 */
	private array $plugins_classes_active;

	/**
	 * Class constructor
	 * @param array<string, object> $plugins_classes_active
	 * @phpstan-param array{PLUGINS_ALL_KEYS?: PLUGINS_ALL_CLASSES} $plugins_classes_active
	 */
	public function __construct( array $plugins_classes_active ) {

		/**
		 * @psalm-suppress InvalidPropertyAssignmentValue
		 * @phpstan-ignore assign.propertyType (Array does not have offset 'imdbphp' => find better notation)
		 */
		$this->plugins_classes_active = $plugins_classes_active;

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
	 * @param array<string, object> $plugins_classes_active
	 * @phpstan-param array{PLUGINS_ALL_KEYS?: PLUGINS_ALL_CLASSES} $plugins_classes_active
	 */
	public static function start( array $plugins_classes_active ): void {
		$start = new self( $plugins_classes_active );
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
		 * Create much cache and may lead PHP to a Fatal error
		 */
		if ( \Lumiere\Plugins\Auto\Amp::is_amp_validating() === true ) {
			$this->logger->log->debug( '[Lumiere][Widget_Frontpage] This is an AMP validation test, exiting to save server resources' );
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

			// Log the current link maker
			$this->logger->log->debug( '[Lumiere][Movie] Using the link maker class: ' . str_replace( 'Lumiere\Link_Makers\\', '', get_class( $this->link_maker ) ) );

			// Log Plugins_Start, $this->plugins_classes_active in trait
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
					// no result, so jump to the next query.
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
	 *
	 * @param string $mid_premier_resultat -> IMDb ID, not as int since it loses its heading 0s
	 */
	private function lumiere_methods_factory( string $mid_premier_resultat ): string {

		$outputfinal = '';

		// Find the Title based on $mid_premier_resultat.
		$movie_title_object = $this->plugins_classes_active['imdbphp']->get_title_class(
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

				/**
				 * Get the child class with the methods.
				 * @psalm-suppress InvalidArgument
				 */
				$movie_data_class = new Movie_Data( $this->plugins_classes_active );

				// Build the final class+method with the movie_object.
				if ( ! method_exists( $movie_data_class, $method ) ) {
					$this->logger->log->warning( '[Lumiere][Movie] The method ' . $method . ' does not exist in class ' . get_class( $movie_data_class ) . ', aborting' );

					exit( 1 );
				}
				$outputfinal .= $this->lumiere_movie_wrapper( $movie_data_class->$method( $movie_title_object ), $data_detail );
			}
		}
		return $outputfinal;
	}

	/**
	 * Function adding an HTML wrapper to text, here <div>
	 *
	 * @param string $html -> text to wrap
	 * @param string $item -> the item to transform, such as director, title, etc
	 * @return string
	 */
	private function lumiere_movie_wrapper( string $html, string $item ): string {

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
	 * Do taxonomy layouts and insert taxonomy and create the taxonomy relationship
	 *
	 * @since 4.0 rewritten taxonomy system, not using Polylang anymore, links between languages created, hierarchical taxonomy terms
	 *
	 * @param string $type_item mandatory: the general category of the item, ie 'director', 'color'
	 * @param string $first_title mandatory: the name of the first string to display, ie "Stanley Kubrick"
	 * @param string|null $second_title optional: the name of a second string to display, utilised in $layout 'two', ie "director"
	 * @param string $layout optional: the type of the layout, either 'one' or 'two', one by default
	 * @param string|null $movie_title Optional: movie's title, null by default
	 *
	 * @return string the text to be outputed
	 */
	protected function lumiere_make_display_taxonomy(
		string $type_item,
		string $first_title,
		?string $second_title = null,
		string $layout = 'one',
		?string $movie_title = null
	): string {

		/**
		 * Vars and sanitization
		 */
		$lang_term = strtok( get_bloginfo( 'language' ), '-' ); // Language to register the term with, English by default, first language characters if WP
		$output = '';
		$taxonomy_term = esc_attr( $first_title );
		$second_title ??= '';
		$taxonomy_category_full = esc_html( $this->imdb_admin_values['imdburlstringtaxo'] . $type_item );
		$page_id = get_the_ID();

		/**
		 * Insert the taxonomies, add a relationship if a previous taxo exists
		 * Insert the current language displayed and the hierarchical value (child_of) if a previous taxo exists (needs register taxonomy with hierarchical)
		 */
		if ( $page_id !== false && taxonomy_exists( $taxonomy_category_full ) ) {

			// delete if exists, for debugging purposes
			# $array_term_existing = get_term_by('name', $taxonomy_term, $taxonomy_category_full );
			# if ( $array_term_existing )
			#	 wp_delete_term( $array_term_existing->term_id, $taxonomy_category_full) ;

			$existent_term = term_exists( $taxonomy_term, $taxonomy_category_full );

			// The term doesn't exist in the post.
			if ( $existent_term === null ) {
				$term_inserted = wp_insert_term( $taxonomy_term, $taxonomy_category_full );
				$term_for_log = wp_json_encode( $term_inserted );
				if ( $term_for_log !== false ) {
					$this->logger->log->debug( '[Lumiere][Movie] Taxonomy term *' . $taxonomy_term . '* added to *' . $taxonomy_category_full . '* (association numbers ' . $term_for_log . ' )' );
				}
			}

			// If no term was inserted, take the current term.
			$term_for_set_object = $term_inserted ?? $taxonomy_term;

			/**
			 * Taxo terms could be inserted without error (it doesn't exist already), so add a relationship between the taxo and the page id number
			 * wp_set_object_terms() is almost always executed in order to add new relationships even if a new term wasn't inserted
			 */
			if ( ! $term_for_set_object instanceof \WP_Error ) {
				$term_taxonomy_id = wp_set_object_terms( $page_id, $term_for_set_object, $taxonomy_category_full, true );
				// $this->logger->log->debug( '[Lumiere][Movie] Check (and made if needed) association for term_taxonomy_id ' . json_encode( $term_taxonomy_id ) );
			}
		}

		/**
		 * Layout
		 */

		// Build the id for the link <a id="$link_id">
		$link_id = esc_html( $movie_title ?? '' ) . '_' . $lang_term . '_' . $taxonomy_category_full . '_' . $taxonomy_term;
		$link_id = preg_replace( "/^'|[^A-Za-z0-9\'-]|'|\-$/", '_', $link_id ) ?? '';
		$link_id = 'link_taxo_' . strtolower( str_replace( '-', '_', $link_id ) );

		// layout=two: display the layout for double entry details, ie actors
		if ( $layout === 'two' ) {
			$output .= "\n\t\t\t" . '<div align="center" class="lumiere_container">';
			$output .= "\n\t\t\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';
			$output .= "\n\t\t\t\t\t<a id=\"" . $link_id . '" class="lum_link_taxo_page" href="'
					. esc_url( $this->lumiere_get_taxo_link( $taxonomy_term, $taxonomy_category_full ) )
					. '" title="' . esc_html__( 'Find similar taxonomy results', 'lumiere-movies' )
					. '">';
			$output .= "\n\t\t\t\t\t" . $taxonomy_term;
			$output .= "\n\t\t\t\t\t" . '</a>';
			$output .= "\n\t\t\t\t" . '</div>';
			$output .= "\n\t\t\t\t" . '<div class="lumiere_align_right lumiere_flex_auto">';
			$output .= preg_replace( '/\n/', '', wp_kses( $second_title, [ 'i' => [] ] ) ); // remove breaking space, keep some html tags.
			$output .= "\n\t\t\t\t" . '</div>';
			$output .= "\n\t\t\t" . '</div>';

			// layout=one: display the layout for all details separated by comas, ie keywords
		} elseif ( $layout === 'one' ) {
			$output .= '<a id="' . $link_id . '" class="lum_link_taxo_page" '
					. 'href="' . esc_url( $this->lumiere_get_taxo_link( $taxonomy_term, $taxonomy_category_full ) )
					. '" '
					. 'title="' . esc_html__( 'Find similar taxonomy results', 'lumiere-movies' ) . '">';
			$output .= $taxonomy_term;
			$output .= '</a>';
		}

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
	private function lumiere_get_taxo_link( string $name_searched, string $taxo_category ): string {
		$find_term = get_term_by( 'name', $name_searched, $taxo_category );
		$taxo_link = $find_term instanceof \WP_Term ? get_term_link( $find_term->term_id, $taxo_category ) : '';
		return $taxo_link instanceof \WP_Error ? '' : $taxo_link;
	}
}
