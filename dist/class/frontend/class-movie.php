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
	wp_die( esc_html__( 'Lumière Movies: You can not call directly this page', 'lumiere-movies' ) );
}

use Imdb\Title;
use Imdb\TitleSearch;
use Lumiere\Frontend\Movie_Data;

/**
 * The class uses Movie_Data to display data (Movie actor, movie source, etc)
 * It is compatible with Polylang WP plugin
 * It uses ImdbPHP Classes to display movies/people data
 */
class Movie {

	// Use trait frontend
	use \Lumiere\Frontend\Main {
		Main::__construct as public __constructFrontend;
	}

	/**
	 * Singleton: Make sure events are runned once in this class
	 *
	 * @var bool $movie_run_once
	 */
	private bool $movie_run_once = false;

	/**
	 *  HTML allowed for use of wp_kses_post()
	 */
	const ALLOWED_HTML = [
		'a' => [
			'id' => true,
			'href' => true,
			'title' => true,
		],
	];

	/**
	 * Name of the class
	 * Constant utilised in logs
	 */
	private const CLASS_NAME = 'movieClass';

	/**
	 * Class constructor
	 */
	public function __construct() {

		// Construct Frontend trait.
		$this->__constructFrontend( self::CLASS_NAME );

		// Parse the content to add the movies.
		add_filter( 'the_content', [ $this, 'lumiere_parse_spans' ] );

		// Transform span into links to popups.
		add_filter( 'the_content', [ $this, 'lumiere_link_popup_maker' ] );
		add_filter( 'the_excerpt', [ $this, 'lumiere_link_popup_maker' ] );

		// Add the shortcodes to parse the text, old way
		// @obsolete, kept for compatibility purpose
		add_shortcode( 'imdblt', [ $this, 'parse_lumiere_tag_transform' ] );
		add_shortcode( 'imdbltid', [ $this, 'parse_lumiere_tag_transform_id' ] );

	}

	/**
	 * Search the movie and output the results
	 *
	 * @since 3.8   Extra logs are shown once only using singleton $this->movie_run_once and PluginsDetect class added
	 * @since 3.12  Ban bots added, just before doing IMDb query
	 *
	 * @param array<int<0, max>, array<string, string>>|null $imdb_id_or_title_outside Name or IMDbID of the movie to find in array
	 * @psalm-param list<array{0?: array{0?: array{0?: array{byname: string}, bymid?: string, byname: string, ...<int<0, max>, array{byname: string}>}, bymid?: string, byname: string, ...<int<0, max>, array{0?: array{byname: string}, bymid?: string, byname: string, ...<int<0, max>, array{byname: string}>}>}, bymid?: string, byname?: string, ...<int<0, max>, array{0?: array{0?: array{byname: string}, bymid?: string, byname: string, ...<int<0, max>, array{byname: string}>}, bymid?: string, byname: string, ...<int<0, max>, array{0?: array{byname: string}, bymid?: string, byname: string, ...<int<0, max>, array{byname: string}>}>}>}> $imdb_id_or_title_outside
	 */
	public function lumiere_show( ?array $imdb_id_or_title_outside = null ): string {

		/**
		 * Start PluginsDetect class
		 * Is instanciated only if not instanciated already
		 * Use lumiere_set_plugins_array() in trait to set $plugins_in_use var in trait
		 */
		if ( count( $this->plugins_in_use ) === 0 ) {
			$this->lumiere_set_plugins_array();
		}

		do_action( 'lumiere_logger' );

		// Show log for link maker and plugin detect
		if ( $this->movie_run_once === false ) {

			// Log the current link maker
			$this->logger->log()->debug( '[Lumiere][' . self::CLASS_NAME . '] Using the link maker class: ' . str_replace( 'Lumiere\Link_Makers\\', '', get_class( $this->link_maker ) ) );

			// Log PluginsDetect, $this->plugins_in_use in trait
			$this->logger->log()->debug( '[Lumiere][' . self::CLASS_NAME . '] The following plugins compatible with Lumière! are in use: [' . join( ', ', $this->plugins_in_use ) . ']' );
			$this->logger->log()->debug( '[Lumiere][' . self::CLASS_NAME . '] Calling IMDbPHP class.' );

			// Set the trigger to true so this is not called again.
			$this->movie_run_once = true;
		}

		$imdb_id_or_title = $imdb_id_or_title_outside ?? null;
		$output = '';

		// Ban bots before doing an IMDb search.
		do_action( 'lumiere_ban_bots' );

		$search = new TitleSearch( $this->imdbphp_class, $this->logger->log() );

		// $imdb_id_or_title var comes from custom post's field in widget or in post
		$counter_imdb_id_or_title = $imdb_id_or_title !== null ? count( $imdb_id_or_title ) : 0;

		for ( $i = 0; $i < $counter_imdb_id_or_title; $i++ ) {

			// sanitize
			$film = $imdb_id_or_title !== null ? $imdb_id_or_title[ $i ] : null;

			// A movie's title has been specified, get its imdbid.
			if ( isset( $film['byname'] ) ) {

				$film = strtolower( $film['byname'] ); // @since 3.12 lowercase, less cache used.

				$this->logger->log()->debug( '[Lumiere][' . self::CLASS_NAME . '] ' . ucfirst( 'The following "' . esc_html( $this->imdb_admin_values['imdbseriemovies'] ) ) . '" title provided: ' . $film );

				// check a the movie title exists.
				if ( strlen( $film ) > 0 ) {

					$this->logger->log()->debug( '[Lumiere][' . self::CLASS_NAME . "] searching for $film" );

					$results = $search->search( $film, $this->config_class->lumiere_select_type_search() );

				}
				$results_mid = isset( $results ) && isset( $results[0] ) ? $results[0]->imdbid() : null;
				$mid_premier_resultat = isset( $results_mid ) ? filter_var( $results_mid, FILTER_SANITIZE_NUMBER_INT ) : null;

				// No result was found in imdbphp query.
				if ( $mid_premier_resultat === null ) {

					$this->logger->log()->info( '[Lumiere][' . self::CLASS_NAME . '] No ' . ucfirst( esc_html( $this->imdb_admin_values['imdbseriemovies'] ) ) . ' found for ' . $film . ', aborting.' );

					// no result, so jump to the next query and forget the current
					continue;

				}

				$this->logger->log()->debug( '[Lumiere][' . self::CLASS_NAME . "] Result found: $mid_premier_resultat." );

				// no movie's title but a movie's ID has been specified
			} elseif ( isset( $film['bymid'] ) ) {

				$mid_premier_resultat = filter_var( $film['bymid'], FILTER_SANITIZE_NUMBER_INT );
				$this->logger->log()->debug( '[Lumiere][' . self::CLASS_NAME . "] Movie ID provided: '$mid_premier_resultat'." );

			}

			if ( $film === null || ! isset( $mid_premier_resultat ) || $mid_premier_resultat === false ) {

				$this->logger->log()->debug( '[Lumiere][' . self::CLASS_NAME . '] No result found for this query.' );
				continue;

			}

			$this->logger->log()->debug( '[Lumiere][' . self::CLASS_NAME . "] Displaying rows for '$mid_premier_resultat'" );

			$output .= "\n\t\t\t\t\t\t\t\t\t" . '<!-- ### Lumière! movies plugin ### -->';
			$output .= "\n\t<div class='imdbincluded";

			// add dedicated class for themes
			$output .= ' imdbincluded_' . $this->imdb_admin_values['imdbintotheposttheme'];
			$output .= "'>";

			$output .= $this->lumiere_methods_factory( $mid_premier_resultat );
			$output .= "\n\t</div>";

		}

		return $output;
	}

	/**
	 * List of prohibited areas where the class won't run
	 *
	 * @since 3.10.2
	 *
	 * @return bool
	 */
	public function lumiere_prohibited_areas(): bool {
		return is_feed() || is_comment_feed();
	}

	/**
	 * Find in content the span to build the movies
	 * Looks for <span data-lum_movie_maker="[1]"></span> where [1] is movie_title or movie_id
	 *
	 * @since 3.10.2    The function always returns string, no null accepted -- PHP8.2 compatibility
	 *                  Also added a lumiere_prohibited_areas() check, no need to execute the plugin in feeds
	 *
	 * @param null|string $content HTML span tags + text inside
	 * @return string
	 */
	public function lumiere_parse_spans( ?string $content ): string {

		// if no content is availabe on the content or if it is a feed, abort
		if ( ! isset( $content ) || $this->lumiere_prohibited_areas() === true ) {
			return '';
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
		$imdb_id_or_title[]['bymid'] = sanitize_text_field( $block_span[1] );
		return $this->lumiere_show( $imdb_id_or_title );

	}

	/**
	 * Callback for movies by imdb title
	 *
	 * @param array<string> $block_span
	 */
	private function lumiere_parse_spans_callback_title( array $block_span ): string {

		$imdb_id_or_title = [];
		$imdb_id_or_title[]['byname'] = sanitize_text_field( $block_span[1] );
		return $this->lumiere_show( $imdb_id_or_title );

	}

	/**
	 * Replace [imdblt] shortcode by the movie
	 * @obsolete since v3.5, kept for compatibility purposes
	 *
	 * @param string|array<string> $atts array of attributes
	 * @param null|string $content shortcode content or null if not set
	 */
	public function parse_lumiere_tag_transform( $atts, ?string $content ): string {
		$movie_title = $content;
		return $this->lumiere_external_call( $movie_title, '', '' );
	}

	/**
	 * Replace [imdbltid] shortcode by the movie
	 * @obsolete since v3.5, kept for compatibility purposes
	 *
	 * @param string|array<string> $atts
	 * @param null|string $content shortcode content or null if not set
	 */
	public function parse_lumiere_tag_transform_id( $atts, ?string $content ): string {
		$movie_imdbid = $content;
		return $this->lumiere_external_call( '', $movie_imdbid, '' );
	}

	/**
	 *  Replace <span data-lum_link_maker="popup"> tags inside the posts
	 *
	 * Looks for what is inside tags <span data-lum_link_maker="popup"> ... </span>
	 * and builds a popup link
	 *
	 * @param array<int, string> $correspondances parsed data
	 */
	private function lumiere_link_finder( array $correspondances ): string {

		$correspondances = $correspondances[0];
		preg_match( '~<span data-lum_link_maker="popup">(.+)<\/span>~i', $correspondances, $matches );
		return $this->link_maker->lumiere_popup_film_link( $matches );
	}

	/**
	 * Replace <!--imdb--> tags inside the posts
	 *
	 * Looks for what is inside tags <!--imdb--> ... <!--/imdb-->
	 * and builds a popup link
	 *
	 * @obsolete since v3.1, kept for compatibility purposes
	 * @param array<string> $correspondances parsed data
	 */
	private function lumiere_link_finder_oldway( array $correspondances ): string {

		$correspondances = $correspondances[0];
		preg_match( '~<!--imdb-->(.*?)<!--\/imdb-->~i', $correspondances, $link_parsed );
		return $this->link_maker->lumiere_popup_film_link( $link_parsed );
	}

	/**
	 * Replace <span class="lumiere_link_maker"></span> with links
	 *
	 * @param null|string $text parsed data
	 */
	public function lumiere_link_popup_maker( ?string $text ): ?string {

		if ( ! isset( $text ) ) {
			return null;
		}

		// replace all occurences of <span class="lumiere_link_maker">(.+?)<\/span> into internal popup
		$pattern = '~<span data-lum_link_maker="popup">(.+?)<\/span>~i';
		$text = preg_replace_callback( $pattern, [ $this, 'lumiere_link_finder' ], $text ) ?? $text;

		// Kept for compatibility purposes:  <!--imdb--> still works
		$pattern_two = '~<!--imdb-->(.*?)<!--\/imdb-->~i';
		$text = preg_replace_callback( $pattern_two, [ $this, 'lumiere_link_finder_oldway' ], $text ) ?? $text;

		return $text;
	}

	/**
	 * Function external call (ie, inside a post)
	 * Utilized to build from shortcodes
	 * @obsolete since 3.1, not using shortcodes anymore, kept for compatibility purposes
	 *
	 * @param string|null $moviename
	 * @param string|null $filmid
	 * @param string|null $external set to 'external' for use from outside
	 */
	public function lumiere_external_call ( ?string $moviename = null, ?string $filmid = null, ?string $external = null ): string {

		$imdb_id_or_title = [];

		// Call function from external (using parameter "external" )
		// Especially made to be integrated (ie, inside a php code)
		if ( ( $external === 'external' ) && isset( $moviename ) ) {
			$imdb_id_or_title[]['byname'] = $moviename;
		}

		// Call function from external (using parameter "external" )
		// Especially made to be integrated (ie, inside a php code)
		if ( ( $external === 'external' ) && isset( $filmid ) ) {
			$imdb_id_or_title[]['bymid'] = $filmid;
		}

		//  Call with the parameter - imdb movie name (imdblt)
		if ( isset( $moviename ) && strlen( $moviename ) !== 0 && $external !== 'external' ) {
			$imdb_id_or_title[]['byname'] = $moviename;
		}

		//  Call with the parameter - imdb movie id (imdbltid)
		if ( isset( $filmid ) && strlen( $filmid ) !== 0 && ( $external !== 'external' ) ) {
			$imdb_id_or_title[]['bymid'] = $filmid;
		}

		return $this->lumiere_show( $imdb_id_or_title );
	}

	/**
	 * Build the methods to be called in class Movie_Data
	 *
	 * @param string $mid_premier_resultat -> IMDb ID, not as int since it loses its heading 0s
	 */
	private function lumiere_methods_factory( string $mid_premier_resultat ): string {

		$outputfinal = '';
		$mid_premier_resultat = esc_html( $mid_premier_resultat );

		// Find the Title based on $mid_premier_resultat
		$movie_title_object = new Title(
			$mid_premier_resultat, // The IMDb ID
			$this->imdbphp_class, // The settings
			$this->logger->log() // The logger
		);

		foreach ( $this->imdb_widget_values['imdbwidgetorder'] as $data_detail => $order ) {

			if (
			// Use order to select the position of the data detail.
			( $this->imdb_widget_values['imdbwidgetorder'][ $data_detail ] === $order )
			// Is the data detail activated?
			&& ( $this->imdb_widget_values[ 'imdbwidget' . $data_detail ] === '1' )
			) {
				// Build the method name according to the data detail name.
				$method = "lumiere_movies_{$data_detail}";

				// Get the child class with the methods
				$movie_data_class = new Movie_Data();

				// Build the final class+method with the movie_object
				if ( method_exists( $movie_data_class, $method ) ) {
					$outputfinal .= $this->lumiere_movie_wrapper( $movie_data_class->$method( $movie_title_object ), $data_detail );
				} else {
					$this->logger->log()->warning( '[Lumiere][' . self::CLASS_NAME . '] The method ' . $method . ' does not exist in the class' );
				}
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

		$outputfinal = '';
		$item = sanitize_text_field( $item );
		$item_caps = strtoupper( $item );

		if ( strlen( $html ) === 0 ) {
			return '';
		}

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
	 * @since 3.12 rewritten taxonomy system, not using Polylang anymore, links between languages created, hierarchical taxonomy terms
	 *
	 * @param string $type_item mandatory: the general category of the item, ie 'director', 'color'
	 * @param string $first_title mandatory: the name of the first string to display, ie "Stanley Kubrick"
	 * @param string|null $second_title optional: the name of a second string to display, utilised in $layout 'two', ie "director"
	 * @param string $layout optional: the type of the layout, either 'one' or 'two', one by default
	 *
	 * @return string the text to be outputed
	 */
	protected function lumiere_make_display_taxonomy( string $type_item, string $first_title, ?string $second_title = null, string $layout = 'one' ): string {

		/**
		 * Vars and sanitization
		 */
		$lang_term = strtok( get_bloginfo( 'language' ), '-' ); // Language to register the term with, English by default, first language characters if WP
		$output = '';
		$list_taxonomy_term = '';
		$layout = esc_attr( $layout );
		$taxonomy_category = esc_attr( $type_item );
		$taxonomy_term = esc_attr( $first_title );
		$second_title = $second_title !== null ? esc_attr( $second_title ) : '';
		$taxonomy_url_string_first = esc_attr( $this->imdb_admin_values['imdburlstringtaxo'] );
		$taxonomy_category_full = $taxonomy_url_string_first . $taxonomy_category;
		$page_id = get_the_ID();

		/**
		 * Insert the taxonomies, add a relationship if a previous taxo exists
		 * Insert the current language displayed and the hierarchical value (child_of) if a previous taxo exists (needs register taxonomy with hierarchical)
		 */
		if ( $page_id !== false && taxonomy_exists( $taxonomy_category_full ) ) {

			// delete if exists, for debugging purposes
			# if ( $term_already = get_term_by('name', $taxonomy_term, $taxonomy_category_full ) )
			#	 wp_delete_term( $term_already->term_id, $taxonomy_category_full) ;

			$existent_term = term_exists( $taxonomy_term, $taxonomy_category_full );
			// $array_term_existing = get_term_by('name', $taxonomy_term, $taxonomy_category_full );

			if ( ! isset( $existent_term ) ) {
				$term_inserted = wp_insert_term( $taxonomy_term, $taxonomy_category_full );
				$this->logger->log()->debug( '[Lumiere][' . self::CLASS_NAME . "] Taxonomy term $taxonomy_term added to $taxonomy_category_full (association numbers " . json_encode( $term_inserted ) );
			}

			// If no term was inserted, take the current term.
			$term_for_set_object = $term_inserted ?? $taxonomy_term;

			/**
			 * Taxo terms could be inserted without error (it doesn't exist already), so add a relationship between the taxo and the page id number
			 * wp_set_object_terms() is almost always executed in order to add new relationships even if a new term wasn't inserted
			 */
			if ( ! $term_for_set_object instanceof \WP_Error ) {
				$term_taxonomy_id = wp_set_object_terms( $page_id, $term_for_set_object, $taxonomy_category_full, true );
				// $this->logger->log()->debug( '[Lumiere][' . self::CLASS_NAME . '] Check (and made if needed) association for term_taxonomy_id ' . json_encode( $term_taxonomy_id ) );
			}

			// Add Lumière tags to the current WordPress post. But we don't want it!
			# wp_set_post_tags( $page_id, $list_taxonomy_term, 'post_tag', true);

		}

		/**
		 * Compatibility with Polylang WordPress plugin, add a language to the taxonomy term.
		 * Function in class Polylang.
		 * @obsolete since 3.12, WordPress functions do all what we need
		 * @TODO: make a function that even if Polylang custom taxonomies are not activated, taxos are registred with Polylang language anyway
		 */
		/* if ( $this->plugin_polylang instanceof Polylang && ! is_wp_error( $term_inserted ) && $page_id !== false ) {


			$find_term = get_term_by( 'name', $taxonomy_term, $taxonomy_category_full );

			$term = term_exists( $taxonomy_term, $taxonomy_category_full );
			$this->plugin_polylang->lumiere_polylang_add_lang_to_taxo( (array) $term );
			$this->logger->log()->debug(
				'[Lumiere][' . self::CLASS_NAME . '] Added to Polylang the terms:' . wp_json_encode( $term )
			);

			// Create a list of Lumière tags meant to be inserted to Lumière Taxonomy
			$list_taxonomy_term .= $taxonomy_term . ', ';

		}*/

		/**
		 * Layout
		 */
		// layout=two: display the layout for double entry details, ie actors
		if ( $layout === 'two' ) {

			$output .= "\n\t\t\t" . '<div align="center" class="lumiere_container">';
			$output .= "\n\t\t\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';
			$output .= "\n\t\t\t\t\t<a class=\"linkincmovie\" href=\""
					. esc_url( $this->lumiere_get_taxo_link( $taxonomy_term, $taxonomy_category_full ) )
					. '" title="' . esc_html__( 'Find similar taxonomy results', 'lumiere-movies' )
					. '">';
			$output .= "\n\t\t\t\t\t" . $taxonomy_term;
			$output .= "\n\t\t\t\t\t" . '</a>';
			$output .= "\n\t\t\t\t" . '</div>';
			$output .= "\n\t\t\t\t" . '<div class="lumiere_align_right lumiere_flex_auto">';
			$output .= preg_replace( '/\n/', '', esc_attr( $second_title ) ); # remove breaking space
			$output .= "\n\t\t\t\t" . '</div>';
			$output .= "\n\t\t\t" . '</div>';

			// layout=one: display the layout for all details separated by comas, ie keywords
		} elseif ( $layout === 'one' ) {

			$output .= '<a class="linkincmovie" '
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
	 * @since 3.12 New function taking out pieces from Movie::lumiere_make_display_taxonomy()
	 *
	 * @param string $name_searched The name searched, such as 'Stanley Kubrick'
	 * @param string $taxo_category The taxonomy category used, such as 'lumiere-director'
	 * @return string The WordPress full HTML link for the name with that category
	 */
	private function lumiere_get_taxo_link( string $name_searched, string $taxo_category ): string {

		$find_term = get_term_by( 'name', $name_searched, $taxo_category );
		$taxo_link = $find_term instanceof \WP_Term ? get_term_link( $find_term->term_id, $taxo_category ) : '';
		return is_wp_error( $taxo_link ) === false ? $taxo_link : '';
	}

	/**
	 * Create an html link for taxonomy
	 *
	 * @param string $taxonomy
	 */
	private function lumiere_make_taxonomy_link( string $taxonomy ): string {

		$taxonomy = preg_replace( '/\s/', '-', $taxonomy ) ?? $taxonomy;# replace space by hyphen
		$taxonomy = strtolower( $taxonomy ); # convert to small characters
		$taxonomy = remove_accents( $taxonomy ); # convert accentuated charaters to unaccentuated counterpart
		return $taxonomy;
	}

	/**
	 * Static call of the current class Movie
	 *
	 * @return void Build the class
	 */
	public static function lumiere_static_start (): void {
		$movie_class = new self();
	}
}
