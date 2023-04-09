<?php declare( strict_types = 1 );
/**
 * Class for displaying movies. This class automatically catches spans. It displays taxonomy links and add taxonomy according to the selected options
 *
 * The class uses \Lumiere\Link_Makers\Link_Factory to automatically select the appropriate Link maker class to display data ( i.e. Classic links, Highslide/Bootstrap, No Links, AMP)
 * It is compatible with Polylang WP plugin
 * It uses ImdbPHP Classes to display movies/people data
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version       2.2
 * @package lumiere-movies
 */

namespace Lumiere;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	wp_die( esc_html__( 'Lumière Movies: You can not call directly this page', 'lumiere-movies' ) );
}

use Imdb\Title;
use Imdb\TitleSearch;
use Lumiere\Plugins\Polylang;

class Movie {

	// Use trait frontend
	use \Lumiere\Frontend {
		Frontend::__construct as public __constructFrontend;
	}

	/**
	 * Polylang plugin object from its class
	 * Can be null if Polylang is not active
	 *
	 * @var Polylang $plugin_polylang
	 */
	private ?Polylang $plugin_polylang = null;

	/**
	 * Make sure events are runned once in this class
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
	 * @param ?Polylang $plugin_polylang Polylang plugin object
	 */
	public function __construct( ?Polylang $plugin_polylang = null ) {

		// Construct Frontend trait.
		$this->__constructFrontend( self::CLASS_NAME );

		// Instanciate $plugin_polylang.
		if ( ( class_exists( 'Polylang' ) ) && ( $plugin_polylang instanceof Polylang ) && $plugin_polylang->polylang_is_active() === true ) {
			$this->plugin_polylang = $plugin_polylang;
		}

		// Run the initialisation of the class.
		// Not needed since lumiere_show() is called by lumiere_parse_spans().
		// add_action ('the_loop', [$this, 'lumiere_show'], 0);

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
	 *  Search the movie and output the results
	 *
	 * @param array<int, array<string, string>>|null $imdb_id_or_title_outside Name or IMDbID of the movie to find in array
	 */
	public function lumiere_show( ?array $imdb_id_or_title_outside = null ): string {

		/**
		 * Start PluginsDetect class
		 * Is instanciated only if not instanciated already
		 * Use lumiere_set_plugins_array() in trait to set $plugins_in_use var in trait
		 * @since 3.8
		 */
		if ( count( $this->plugins_in_use ) === 0 ) {
			$this->lumiere_set_plugins_array();
		}

		do_action( 'lumiere_logger' );
		$logger = $this->logger->log();

		/**
		 * Show log for link maker and plugin detect
		 * Is instantiated only if not instanciated already, using $run_once in this class
		 * @since 3.8
		 */
		if ( $this->movie_run_once === false ) {

			// Log the current link maker
			$logger->debug( '[Lumiere][' . self::CLASS_NAME . '] Using the link maker class: ' . str_replace( 'Lumiere\Link_Makers\\', '', get_class( $this->link_maker ) ) );

			// Log PluginsDetect, $this->plugins_in_use in trait
			$logger->debug( '[Lumiere][' . self::CLASS_NAME . '] The following plugins compatible with Lumière! are in use: [' . join( ', ', $this->plugins_in_use ) . ']' );
			$logger->debug( '[Lumiere][' . self::CLASS_NAME . '] Calling IMDbPHP class.' );

			// Set the trigger to true so this is not called again.
			$this->movie_run_once = true;
		}

		$imdb_id_or_title = $imdb_id_or_title_outside ?? null;
		$output = '';

		$search = new TitleSearch( $this->imdbphp_class, $logger );

		// $imdb_id_or_title var comes from custom post's field in widget or in post
		$counter_imdb_id_or_title = $imdb_id_or_title !== null ? count( $imdb_id_or_title ) : 0;

		for ( $i = 0; $i < $counter_imdb_id_or_title; $i++ ) {

			// sanitize
			$film = $imdb_id_or_title !== null ? $imdb_id_or_title[ $i ] : null;

			// A movie's title has been specified, get its imdbid.
			if ( isset( $film['byname'] ) ) {

				$film = $film['byname'];

				$logger->debug( '[Lumiere][' . self::CLASS_NAME . "] Movie title provided: $film" );

				// check a the movie title exists.
				if ( strlen( $film ) !== 0 ) {

					$logger->debug( '[Lumiere][' . self::CLASS_NAME . "] searching for $film" );

					$results = $search->search( $film, $this->config_class->lumiere_select_type_search() );

				}

				$mid_premier_resultat = isset( $results[0] ) ? filter_var( $results[0]->imdbid(), FILTER_SANITIZE_NUMBER_INT ) : null;

				// No result was found in imdbphp query.
				if ( $mid_premier_resultat === null ) {

					$logger->info( '[Lumiere][' . self::CLASS_NAME . "] No movie found for $film, aborting." );

					// no result, so jump to the next query and forget the current
					continue;

				}

				$logger->debug( '[Lumiere][' . self::CLASS_NAME . "] Result found: $mid_premier_resultat." );

				// no movie's title but a movie's ID has been specified
			} elseif ( isset( $film['bymid'] ) ) {

				$mid_premier_resultat = filter_var( $film['bymid'], FILTER_SANITIZE_NUMBER_INT );
				$logger->debug( '[Lumiere][' . self::CLASS_NAME . "] Movie ID provided: '$mid_premier_resultat'." );

			}

			if ( $film === null || ! isset( $mid_premier_resultat ) || $mid_premier_resultat === false ) {

				$logger->debug( '[Lumiere][' . self::CLASS_NAME . '] No result found for this query.' );
				continue;

			}

			$logger->debug( '[Lumiere][' . self::CLASS_NAME . "] Displaying rows for '$mid_premier_resultat'" );

			$output .= "\n\t\t\t\t\t\t\t\t\t" . '<!-- ### Lumière! movies plugin ### -->';
			$output .= "\n\t<div class='imdbincluded";

			// add dedicated class for themes
			$output .= ' imdbincluded_' . $this->imdb_admin_values['imdbintotheposttheme'];
			$output .= "'>";

			$output .= $this->lumiere_movie_design( $mid_premier_resultat ); # passed those two values to the design
			$output .= "\n\t</div>";

		}

		/** seems useless
		 * unset( $counter_imdb_id_or_title ); // avoid displaying several times same movie, close "for" loop.
		 */
		return $output;

	}

	/**
	 * Find in content the span to build the movies
	 * Looks for <span data-lum_movie_maker="[1]"></span> where [1] is movie_title or movie_id
	 *
	 * @param string $content HTML span tags + text inside
	 * @return string
	 */
	public function lumiere_parse_spans( string $content ): string {

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
	 *  Callback for movies by IMDb ID
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
	 * Obsolete, kept for compatibility purposes
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
	 * @obsolete Kept for compatibility purposes
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
		preg_match( '/<span data-lum_link_maker="popup">(.+?)<\/span>/i', $correspondances, $link_parsed );

		return $this->link_maker->lumiere_popup_film_link( $link_parsed );

	}

	/**
	 *  Replace <!--imdb--> tags inside the posts
	 *
	 * Looks for what is inside tags <!--imdb--> ... <!--/imdb-->
	 * and builds a popup link
	 *
	 * @obsolete Kept for compatibility purposes
	 * @param array<string> $correspondances parsed data
	 */
	private function lumiere_link_finder_oldway( array $correspondances ): string {

		$correspondances = $correspondances[0];
		preg_match( '/<!--imdb-->(.*?)<!--\/imdb-->/i', $correspondances, $link_parsed );

		return $this->link_maker->lumiere_popup_film_link( $link_parsed );

	}

	/**
	 *  Replace <span class="lumiere_link_maker"></span> with links
	 *
	 * @param string $text parsed data
	 */
	public function lumiere_link_popup_maker( string $text ): ?string {

		// replace all occurences of <span class="lumiere_link_maker">(.+?)<\/span> into internal popup
		$pattern = '/<span data-lum_link_maker="popup">(.+?)<\/span>/i';
		$text = preg_replace_callback( $pattern, [ $this, 'lumiere_link_finder' ], $text ) ?? $text;

		// Kept for compatibility purposes:  <!--imdb--> still works
		$pattern_two = '/<!--imdb-->(.*?)<!--\/imdb-->/i';
		$text = preg_replace_callback( $pattern_two, [ $this, 'lumiere_link_finder_oldway' ], $text ) ?? $text;

		return $text;
	}

	/**
	 * Function external call (ie, inside a post)
	 * Utilized to build from shortcodes
	 * @obsolete not using shortcodes anymore
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
	 * Function to display the layout and call all subfonctions
	 *
	 * @param string $mid_premier_resultat -> IMDb ID, not as int since it loses its heading 0s
	 */
	private function lumiere_movie_design( string $mid_premier_resultat ): string {

		$mid_premier_resultat = esc_html( $mid_premier_resultat );

		// Simplify the coding.
		$logger = $this->logger->log();

		// initialise the output.
		$outputfinal = '';

		/* Start imdbphp class for new query based upon $mid_premier_resultat */
		$movie = new Title( $mid_premier_resultat, $this->imdbphp_class, $logger );

		foreach ( $this->imdb_widget_values['imdbwidgetorder'] as $data_detail => $order ) {

			if (
			// Use order to select the position of the data detail.
			( $this->imdb_widget_values['imdbwidgetorder'][ $data_detail ] === $order )
			// Is the data detail activated?
			&& ( $this->imdb_widget_values[ 'imdbwidget' . $data_detail ] === '1' )
			) {

				// Build the function name according to the data detail name.
				$function = "lumiere_movies_{$data_detail}";

				// Call the wrapper using the built function.
				if ( method_exists( '\Lumiere\Movie', $function ) ) {
					// @phpstan-ignore-next-line 'Variable method call on $this(Lumiere\Movie)'.
					$outputfinal .= $this->lumiere_movie_design_addwrapper( $this->$function( $movie ), $data_detail );
				} else {
					$logger->warning( '[Lumiere][' . self::CLASS_NAME . '] The method ' . $function . ' does not exist in the class' );
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
	 *
	 * @return string
	 */
	private function lumiere_movie_design_addwrapper( string $html, string $item ): string {

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
	 * Display the title and possibly the year
	 *
	 * @param \Imdb\Title $movie IMDbPHP title class
	 */
	private function lumiere_movies_title ( \Imdb\Title $movie ): string {

		$output = '';
		$year = strlen( strval( $movie->year() ) ) !== 0 ? intval( $movie->year() ) : null;
		$title_sanitized = sanitize_text_field( $movie->title() );

		$output .= "\n\t\t\t<span id=\"title_$title_sanitized\">" . $title_sanitized;

		if ( $year !== null && $this->imdb_widget_values['imdbwidgetyear'] === '1' ) {
			$output .= ' (' . $year . ')';
		}

		$output .= '</span>';

		return $output;

	}

	/**
	 * Display the picture
	 *
	 * @since 3.7 improved compatibility with AMP WP plugin in relevant class
	 *
	 * @param \Imdb\Title $movie IMDbPHP title class
	 */
	private function lumiere_movies_pic ( \Imdb\Title $movie ): string {

		/**
		 * Use links builder classes.
		 * Each one has its own class passed in $link_maker,
		 * according to which option the lumiere_select_link_maker() found in Frontend.
		 */
		// If cache is active, use the pictures from IMDBphp class.
		if ( $this->imdb_cache_values['imdbusecache'] === '1' ) {
			return $this->link_maker->lumiere_link_picture( $movie->photo_localurl( false ), $movie->photo_localurl( true ), $movie->title() );
		}

		// If cache is deactived, display no_pics.gif
		return $this->link_maker->lumiere_link_picture( $this->config_class->lumiere_pics_dir . '/no_pics.gif', $this->config_class->lumiere_pics_dir . '/no_pics.gif', $movie->title() );
	}

	/**
	 * Display the country of origin
	 *
	 * @param \Imdb\Title $movie IMDbPHP title class
	 */
	private function lumiere_movies_country ( \Imdb\Title $movie ): string {

		$output = '';
		$country = $movie->country();
		$nbtotalcountry = count( $country );

		// if no result, exit.
		if ( $nbtotalcountry === 0 ) {
			return $output;
		}

		$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
		$output .= sprintf( esc_attr( _n( 'Country', 'Countries', $nbtotalcountry, 'lumiere-movies' ) ), number_format_i18n( $nbtotalcountry ) );
		$output .= ':</span>';

		// Taxonomy is active.
		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_widget_values['imdbtaxonomycountry'] === '1' ) ) {

			for ( $i = 0; $i < $nbtotalcountry; $i++ ) {

				$output .= $this->lumiere_make_display_taxonomy( 'country', esc_attr( $country[ $i ] ), '', 'one' );
				if ( $i < $nbtotalcountry - 1 ) {
					$output .= ', ';
				}

			}

			return $output;

		}

		// Taxonomy is unactive.
		for ( $i = 0; $i < $nbtotalcountry; $i++ ) {
			$output .= sanitize_text_field( $country[ $i ] );
			if ( $i < $nbtotalcountry - 1 ) {
				$output .= ', ';
			}
		}

		return $output;

	}

	/**
	 * Display the runtime
	 *
	 * @param \Imdb\Title $movie IMDbPHP title class
	 */
	private function lumiere_movies_runtime( \Imdb\Title $movie ): string {

		$output = '';
		$runtime_sanitized = strval( $movie->runtime() );

		if ( strlen( $runtime_sanitized ) === 0 ) {
			return $output;
		}

		$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
		$output .= esc_html__( 'Runtime', 'lumiere-movies' );
		$output .= ':</span>';
		$output .= $runtime_sanitized . ' ' . esc_html__( 'minutes', 'lumiere-movies' );

		return $output;

	}

	/**
	 * Display the language
	 *
	 * @param \Imdb\Title $movie IMDbPHP title class
	 */
	private function lumiere_movies_language( \Imdb\Title $movie ): string {

		$output = '';
		$languages = $movie->languages();
		$nbtotallanguages = count( $languages );

		if ( $nbtotallanguages === 0 ) {
			return $output;
		}

		$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
		$output .= sprintf( esc_attr( _n( 'Language', 'Languages', $nbtotallanguages, 'lumiere-movies' ) ), number_format_i18n( $nbtotallanguages ) );
		$output .= ':</span>';

		// Taxonomy is active.
		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_widget_values['imdbtaxonomylanguage'] === '1' ) ) {

			for ( $i = 0; $i < $nbtotallanguages; $i++ ) {

				$output .= $this->lumiere_make_display_taxonomy( 'language', esc_attr( $languages[ $i ] ), '', 'one' );
				if ( $i < $nbtotallanguages - 1 ) {
					$output .= ', ';
				}

			}

			return $output;

		}

		// Taxonomy is unactive.
		for ( $i = 0; $i < $nbtotallanguages; $i++ ) {

			$output .= sanitize_text_field( $languages[ $i ] );

			if ( $i < $nbtotallanguages - 1 ) {
				$output .= ', ';
			}

		}

		return $output;
	}

	/**
	 * Display the rating
	 *
	 * @param \Imdb\Title $movie IMDbPHP title class
	 */
	private function lumiere_movies_rating( \Imdb\Title $movie ): string {

		$output = '';
		$votes_sanitized = intval( $movie->votes() );
		$rating_sanitized = intval( $movie->rating() );

		if ( $votes_sanitized === 0 ) {
			return $output;
		}

		/**
		 * Use links builder classes.
		 * Each one has its own class passed in $link_maker,
		 * according to which option the lumiere_select_link_maker() found in Frontend.
		 */
		$output .= $this->link_maker->lumiere_movies_rating_picture( $rating_sanitized, $votes_sanitized, esc_html__( 'vote average', 'lumiere-movies' ), esc_html__( 'out of 10', 'lumiere-movies' ), esc_html__( 'votes', 'lumiere-movies' ) );

		return $output;

	}

	/**
	 * Display the genre
	 *
	 * @param \Imdb\Title $movie IMDbPHP title class
	 */
	private function lumiere_movies_genre( \Imdb\Title $movie ): string {

		$output = '';
		$genre = $movie->genres();
		$nbtotalgenre = count( $genre );

		if ( $nbtotalgenre === 0 ) {
			return $output;
		}

		$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
		$output .= sprintf( esc_attr( _n( 'Genre', 'Genres', $nbtotalgenre, 'lumiere-movies' ) ), number_format_i18n( $nbtotalgenre ) );

		$output .= ':</span>';

		// Taxonomy is active.
		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_widget_values['imdbtaxonomygenre'] === '1' ) ) {

			for ( $i = 0; $i < $nbtotalgenre; $i++ ) {

				$output .= $this->lumiere_make_display_taxonomy( 'genre', esc_attr( $genre[ $i ] ), '', 'one' );
				if ( $i < $nbtotalgenre - 1 ) {
					$output .= ', ';
				}

			}

			return $output;

		}

		// Taxonomy is unactive.
		for ( $i = 0; $i < $nbtotalgenre; $i++ ) {

			$output .= esc_attr( $genre[ $i ] );
			if ( $i < $nbtotalgenre - 1 ) {
				$output .= ', ';
			}

		}

		return $output;
	}

	/**
	 * Display the keywords
	 *
	 * @param \Imdb\Title $movie IMDbPHP title class
	 */
	private function lumiere_movies_keyword( \Imdb\Title $movie ): string {

		$output = '';
		$keywords = $movie->keywords();
		$nbtotalkeywords = count( $keywords );

		if ( $nbtotalkeywords === 0 ) {
			return $output;
		}

		$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
		$output .= sprintf( esc_attr( _n( 'Keyword', 'Keywords', $nbtotalkeywords, 'lumiere-movies' ) ), number_format_i18n( $nbtotalkeywords ) );
		$output .= ':</span>';

		// Taxonomy is active.
		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_widget_values['imdbtaxonomykeyword'] === '1' ) ) {

			for ( $i = 0; $i < $nbtotalkeywords; $i++ ) {

				$output .= $this->lumiere_make_display_taxonomy( 'keyword', esc_attr( $keywords[ $i ] ), '', 'one' );
				if ( $i < $nbtotalkeywords - 1 ) {
					$output .= ', ';
				}

			}

			return $output;

		}

		// Taxonomy is unactive.
		for ( $i = 0; $i < $nbtotalkeywords; $i++ ) {

			$output .= esc_attr( $keywords[ $i ] );

			if ( $i < $nbtotalkeywords - 1 ) {
				$output .= ', ';
			}
		}

		return $output;

	}

	/* Display the goofs
	 *
	 * @param \Imdb\Title $movie IMDbPHP title class
	 */
	private function lumiere_movies_goof( \Imdb\Title $movie ): string {

		$output = '';

		$goofs = $movie->goofs();
		$nbgoofs = intval( $this->imdb_widget_values['imdbwidgetgoofnumber'] ) === 0 || $this->imdb_widget_values['imdbwidgetgoofnumber'] === false ? '1' : intval( $this->imdb_widget_values['imdbwidgetgoofnumber'] );
		$nbtotalgoofs = count( $goofs );

		// if no result, exit.
		if ( $nbtotalgoofs === 0 ) {

			return $output;

		}

		$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
		$output .= sprintf( esc_attr( _n( 'Goof', 'Goofs', $nbtotalgoofs, 'lumiere-movies' ) ), number_format_i18n( $nbtotalgoofs ) );
		$output .= ':</span><br />';

		for ( $i = 0; $i < $nbgoofs && ( $i < $nbtotalgoofs ); $i++ ) {

			$output .= "\n\t\t\t\t<strong>" . sanitize_text_field( $goofs[ $i ]['type'] ) . '</strong>&nbsp;';
			$output .= sanitize_text_field( $goofs[ $i ]['content'] ) . "<br />\n";

		}

		return $output;
	}

	/**
	 * Display the quotes
	 *
	 * @param \Imdb\Title $movie IMDbPHP title class
	 */
	private function lumiere_movies_quote( \Imdb\Title $movie ): string {

		$output = '';
		$quotes = $movie->quotes();
		$nbquotes = intval( $this->imdb_widget_values['imdbwidgetquotenumber'] ) === 0 || $this->imdb_widget_values['imdbwidgetquotenumber'] === false ? '1' : intval( $this->imdb_widget_values['imdbwidgetquotenumber'] );

		$nbtotalquotes = count( $quotes );

		// if no result, exit.
		if ( $nbtotalquotes === 0 ) {

			return $output;

		}

		$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
		$output .= sprintf( esc_attr( _n( 'Quote', 'Quotes', $nbtotalquotes, 'lumiere-movies' ) ), number_format_i18n( $nbtotalquotes ) );
		$output .= ':</span><br />';

		for ( $i = 0; $i < $nbquotes && ( $i < $nbtotalquotes ); $i++ ) {

			//transform <p> tags into <div> tags so there is no layout impact by the theme.
			$currentquotes = preg_replace( '~<p>~', "\n\t\t\t<div>", $quotes[ $i ] ) ?? $quotes[ $i ];
			$currentquotes = preg_replace( '~</p>~', "\n\t\t\t</div>", $currentquotes ) ?? $currentquotes;

			/**
			 * Use links builder classes.
			 * Each one has its own class passed in $link_maker,
			 * according to which option the lumiere_select_link_maker() found in Frontend.
			 */
			$output .= "\n\t\t\t" . $this->link_maker->lumiere_imdburl_to_popupurl( $currentquotes );

			if ( $i < ( $nbquotes - 1 ) && $i < ( $nbtotalquotes - 1 ) ) {
				$output .= "\n\t\t\t<hr>"; // add hr to every quote but the last
			}

		}

		return $output;
	}

	/**
	 * Display the taglines
	 *
	 * @param \Imdb\Title $movie IMDbPHP title class
	 */
	private function lumiere_movies_tagline( \Imdb\Title $movie ): string {

		$output = '';
		$taglines = $movie->taglines();
		$nbtaglines = intval( $this->imdb_widget_values['imdbwidgettaglinenumber'] ) === 0 || $this->imdb_widget_values['imdbwidgettaglinenumber'] === false ? '1' : intval( $this->imdb_widget_values['imdbwidgettaglinenumber'] );

		$nbtotaltaglines = count( $taglines );

		// If no result, exit.
		if ( $nbtotaltaglines === 0 ) {

			return $output;

		}

		$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
		$output .= sprintf( esc_attr( _n( 'Tagline', 'Taglines', $nbtotaltaglines, 'lumiere-movies' ) ), number_format_i18n( $nbtotaltaglines ) );
		$output .= ':</span>';

		for ( $i = 0; $i < $nbtaglines && ( $i < $nbtotaltaglines ); $i++ ) {

			$output .= "\n\t\t\t&laquo; " . sanitize_text_field( $taglines[ $i ] ) . ' &raquo; ';
			if ( $i < ( $nbtaglines - 1 ) && $i < ( $nbtotaltaglines - 1 ) ) {
				$output .= ', '; // add comma to every quote but the last
			}

		}

		return $output;

	}

	/**
	 * Display the trailer
	 *
	 * @param \Imdb\Title $movie IMDbPHP title class
	 */
	private function lumiere_movies_trailer( \Imdb\Title $movie ): string {

		$output = '';
		$trailers = $movie->trailers( true );
		$nbtrailers = intval( $this->imdb_widget_values['imdbwidgettrailernumber'] ) === 0 || $this->imdb_widget_values['imdbwidgettrailernumber'] === false ? '1' : intval( $this->imdb_widget_values['imdbwidgettrailernumber'] );

		$nbtotaltrailers = intval( count( $trailers ) );

		// if no results, exit.
		if ( $nbtotaltrailers === 0 ) {

			return $output;

		}

		$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
		$output .= sprintf( esc_attr( _n( 'Trailer', 'Trailers', $nbtotaltrailers, 'lumiere-movies' ) ), number_format_i18n( $nbtotaltrailers ) );
		$output .= ':</span>';

		for ( $i = 0; ( $i < $nbtrailers && ( $i < $nbtotaltrailers ) ); $i++ ) {

			/**
			 * Use links builder classes.
			 * Each one has its own class passed in $link_maker,
			 * according to which option the lumiere_select_link_maker() found in Frontend.
			 */
			$output .= $this->link_maker->lumiere_movies_trailer_details( $trailers[ $i ]['url'], $trailers[ $i ]['title'] );

			if ( $i < ( $nbtrailers - 1 ) && $i < ( $nbtotaltrailers - 1 ) ) {
				$output .= ', '; // add comma to every trailer but the last.
			}
		}

		return $output;

	}

	/**
	 * Display the color
	 *
	 * @param \Imdb\Title $movie IMDbPHP title class
	 */
	private function lumiere_movies_color( \Imdb\Title $movie ): string {

		$output = '';
		$colors = $movie->colors();
		$nbtotalcolors = count( $colors );

		// if no result, exit.
		if ( $nbtotalcolors === 0 ) {

			return $output;

		}

		$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
		$output .= sprintf( esc_attr( _n( 'Color', 'Colors', $nbtotalcolors, 'lumiere-movies' ) ), number_format_i18n( $nbtotalcolors ) );
		$output .= ':</span>';

		// Taxonomy activated.
		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_widget_values['imdbtaxonomycolor'] === '1' ) ) {

			for ( $i = 0; $i < $nbtotalcolors; $i++ ) {

				$output .= $this->lumiere_make_display_taxonomy( 'color', esc_attr( $colors[ $i ] ), '', 'one' );
				if ( $i < $nbtotalcolors - 1 ) {
					$output .= ', ';
				}

			}

			return $output;

		}

		// No taxonomy.
		$count_colors = count( $colors );
		for ( $i = 0; $i < $count_colors; $i++ ) {

			$output .= "\n\t\t\t" . sanitize_text_field( $colors[ $i ] );
			if ( $i < $nbtotalcolors - 1 ) {
				$output .= ', ';
			}
		}

		return $output;

	}

	/**
	 * Display the as known as, aka
	 *
	 * @param \Imdb\Title $movie IMDbPHP title class
	 */
	private function lumiere_movies_alsoknow( \Imdb\Title $movie ): string {

		$output = '';
		$alsoknow = $movie->alsoknow();
		$nbalsoknow = intval( $this->imdb_widget_values['imdbwidgetalsoknownumber'] ) === 0 || $this->imdb_widget_values['imdbwidgetalsoknownumber'] === false ? '1' : intval( $this->imdb_widget_values['imdbwidgetalsoknownumber'] ) + 1; // Adding 1 since first array line is the title
		$nbtotalalsoknow = count( $alsoknow );

		// if no result, exit.
		if ( $nbtotalalsoknow === 0 ) {

			return $output;

		}

		$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
		$output .= esc_html__( 'Also known as', 'lumiere-movies' );
		$output .= ':</span>';

		for ( $i = 0; ( $i < $nbtotalalsoknow ) && ( $i < $nbalsoknow ); $i++ ) {

			// Title line, not returning it.
			if ( $i === 0 ) {
				continue;
			}

			$output .= "\n\t\t\t<i>" . sanitize_text_field( $alsoknow[ $i ]['title'] ) . '</i>';

			if ( strlen( $alsoknow[ $i ]['country'] ) !== 0 || strlen( $alsoknow[ $i ]['comment'] ) !== 0 ) {
				$output .= ' ( ';
				$output .= sanitize_text_field( $alsoknow[ $i ]['country'] );

				if ( strlen( $alsoknow[ $i ]['comment'] ) !== 0 && strlen( $alsoknow[ $i ]['country'] ) !== 0 ) {
					$output .= ' - ';
				}
				$output .= sanitize_text_field( $alsoknow[ $i ]['comment'] );
				$output .= ' )';
			}

			if ( $i < ( $nbtotalalsoknow - 1 ) && $i < ( $nbalsoknow - 1 ) ) {
				$output .= ', ';
			}

		} // endfor

		return $output;
	}

	/**
	 * Display the composers
	 *
	 * @param \Imdb\Title $movie IMDbPHP title class
	 */
	private function lumiere_movies_composer( \Imdb\Title $movie ): string {

		$output = '';
		$composer = $movie->composer();
		$nbtotalcomposer = count( $composer );

		// if no results, exit.
		if ( $nbtotalcomposer === 0 ) {
			return $output;
		}

		$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
		$output .= sprintf( esc_attr( _n( 'Composer', 'Composers', $nbtotalcomposer, 'lumiere-movies' ) ), number_format_i18n( $nbtotalcomposer ) );
		$output .= ':</span>';

		// Taxonomy
		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_widget_values['imdbtaxonomycomposer'] === '1' ) ) {

			for ( $i = 0; $i < $nbtotalcomposer; $i++ ) {

				$output .= $this->lumiere_make_display_taxonomy( 'composer', esc_attr( $composer[ $i ]['name'] ), '', 'one' );
				if ( $i < $nbtotalcomposer - 1 ) {
					$output .= ', ';
				}

			}

			return $output;

		}

		for ( $i = 0; $i < $nbtotalcomposer; $i++ ) {

			/**
			 * Use links builder classes.
			 * Each one has its own class passed in $link_maker,
			 * according to which option the lumiere_select_link_maker() found in Frontend.
			 */
			$output .= $this->link_maker->lumiere_link_popup_people( $composer, $i );

			if ( $i < $nbtotalcomposer - 1 ) {
				$output .= ', ';
			}

		} // endfor

		return $output;

	}

	/**
	 * Display the soundtrack
	 *
	 * @param \Imdb\Title $movie IMDbPHP title class
	 */
	private function lumiere_movies_soundtrack( \Imdb\Title $movie ): string {

		$output = '';
		$soundtrack = $movie->soundtrack();
		$nbsoundtracks = intval( $this->imdb_widget_values['imdbwidgetsoundtracknumber'] ) === 0 || $this->imdb_widget_values['imdbwidgetsoundtracknumber'] === false ? '1' : intval( $this->imdb_widget_values['imdbwidgetsoundtracknumber'] );
		$nbtotalsoundtracks = count( $soundtrack );

		// if no results, exit.
		if ( $nbtotalsoundtracks === 0 ) {
			return $output;
		}

		$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
		$output .= sprintf( esc_attr( _n( 'Soundtrack', 'Soundtracks', $nbtotalsoundtracks, 'lumiere-movies' ) ), number_format_i18n( $nbtotalsoundtracks ) );
		$output .= ':</span>';

		for ( $i = 0; $i < $nbsoundtracks && ( $i < $nbtotalsoundtracks ); $i++ ) {

			$output .= "\n\t\t\t" . ucfirst( strtolower( $soundtrack[ $i ]['soundtrack'] ) );

			$output .= "\n\t\t\t<i>" . str_replace(
				[ "\n", "\r", '<br>', '<br />' ],
				'',
				/**
				 * Use links builder classes.
				 * Each one has its own class passed in $link_maker,
				 * according to which option the lumiere_select_link_maker() found in Frontend.
				 */
				$this->link_maker->lumiere_imdburl_to_popupurl( $soundtrack [ $i ]['credits_raw'] )
			) . '</i> ';

			if ( $i < ( $nbsoundtracks - 1 ) && $i < ( $nbtotalsoundtracks - 1 ) ) {
				$output .= ', ';
			}

		}

		return $output;

	}

	/**
	 * Display the production companies
	 *
	 * @param \Imdb\Title $movie IMDbPHP title class
	 */
	private function lumiere_movies_prodcompany( \Imdb\Title $movie ): string {

		$output = '';
		$prodcompany = $movie->prodCompany();
		$nbtotalprodcompany = count( $prodcompany );

		// if no result, exit.
		if ( $nbtotalprodcompany === 0 ) {
			return $output;
		}

		$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
		$output .= sprintf( esc_attr( _n( 'Production company', 'Production companies', $nbtotalprodcompany, 'lumiere-movies' ) ), number_format_i18n( $nbtotalprodcompany ) );
		$output .= ':</span>';

		for ( $i = 0; $i < $nbtotalprodcompany; $i++ ) {

			/**
			 * Use links builder classes.
			 * Each one has its own class passed in $link_maker,
			 * according to which option the lumiere_select_link_maker() found in Frontend.
			 */
			$output .= $this->link_maker->lumiere_movies_prodcompany_details( $prodcompany[ $i ]['name'], $prodcompany[ $i ]['url'], $prodcompany[ $i ]['notes'] );

		}  // endfor

		return $output;

	}

	/**
	 * Display the official site
	 *
	 * @param \Imdb\Title $movie IMDbPHP title class
	 */
	private function lumiere_movies_officialsites( \Imdb\Title $movie ): string {

		$output = '';
		$official_sites = $movie->officialSites();
		$nbtotalofficial_sites = count( $official_sites );

		// if no result, exit.
		if ( $nbtotalofficial_sites === 0 ) {
			return $output;
		}

		$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
		$output .= sprintf( esc_attr( _n( 'Official website', 'Official websites', $nbtotalofficial_sites, 'lumiere-movies' ) ), number_format_i18n( $nbtotalofficial_sites ) );
		$output .= ':</span>';

		for ( $i = 0; $i < $nbtotalofficial_sites; $i++ ) {

			/**
			 * Use links builder classes.
			 * Each one has its own class passed in $link_maker,
			 * according to which option the lumiere_select_link_maker() found in Frontend.
			 */
			$output .= $this->link_maker->lumiere_movies_officialsites_details( $official_sites[ $i ]['url'], $official_sites[ $i ]['name'] );

			if ( $i < $nbtotalofficial_sites - 1 ) {
				$output .= ', ';
			}

		}

		return $output;
	}

	/**
	 * Display the director
	 *
	 * @param \Imdb\Title $movie IMDbPHP title class
	 */
	private function lumiere_movies_director( \Imdb\Title $movie ): string {

		$output = '';
		$director = $movie->director();
		$nbtotaldirector = count( $director );

		// if no result, exit.
		if ( $nbtotaldirector === 0 ) {
			return $output;
		}

		$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
		$output .= sprintf( esc_attr( _n( 'Director', 'Directors', $nbtotaldirector, 'lumiere-movies' ) ), number_format_i18n( $nbtotaldirector ) );
		$output .= ':</span>';

		// If Taxonomy is selected, build links to taxonomy pages
		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_widget_values['imdbtaxonomydirector'] === '1' )  ) {

			for ( $i = 0; $i < $nbtotaldirector; $i++ ) {

				$output .= $this->lumiere_make_display_taxonomy( 'director', esc_attr( $director[ $i ]['name'] ), '', 'one' );
				if ( $i < $nbtotaldirector - 1 ) {
					$output .= ', ';
				}

			}

			return $output;

		}

		// Taxonomy is not selected
		for ( $i = 0; $i < $nbtotaldirector; $i++ ) {

			/**
			 * Use links builder classes.
			 * Each one has its own class passed in $link_maker,
			 * according to which option the lumiere_select_link_maker() found in Frontend.
			 */
			$output .= $this->link_maker->lumiere_link_popup_people( $director, $i );

			if ( $i < $nbtotaldirector - 1 ) {
				$output .= ', ';
			}

		} // endfor

		return $output;

	}

	/**
	 * Display the creator (for series only)
	 *
	 * @param \Imdb\Title $movie IMDbPHP title class
	 */
	private function lumiere_movies_creator( \Imdb\Title $movie ): string {

		$output = '';
		$creator = $movie->creator();
		$nbtotalcreator = count( $creator );

		// if no results, exit.
		if ( $nbtotalcreator === 0 ) {
			return $output;
		}

		$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
		$output .= sprintf( esc_attr( _n( 'Creator', 'Creators', $nbtotalcreator, 'lumiere-movies' ) ), number_format_i18n( $nbtotalcreator ) );
		$output .= ':</span>&nbsp;';

		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_widget_values['imdbtaxonomycreator'] === '1' ) ) {

			for ( $i = 0; $i < $nbtotalcreator; $i++ ) {

				$output .= $this->lumiere_make_display_taxonomy( 'creator', esc_attr( $creator[ $i ]['name'] ), '', 'one' );
				if ( $i < $nbtotalcreator - 1 ) {
					$output .= ', ';
				}

			}

			return $output;

		}

		for ( $i = 0; $i < $nbtotalcreator; $i++ ) {

			/**
			 * Use links builder classes.
			 * Each one has its own class passed in $link_maker,
			 * according to which option the lumiere_select_link_maker() found in Frontend.
			 */
			$output .= $this->link_maker->lumiere_link_popup_people( $creator, $i );

			if ( $i < $nbtotalcreator - 1 ) {
				$output .= ', ';
			}

		}

		return $output;

	}

	/**
	 * Display the producer
	 *
	 * @param \Imdb\Title $movie IMDbPHP title class
	 */
	private function lumiere_movies_producer( \Imdb\Title $movie ): string {

		$output = '';
		$producer = $movie->producer();
		$nbproducer = intval( $this->imdb_widget_values['imdbwidgetproducernumber'] ) === 0 || $this->imdb_widget_values['imdbwidgetproducernumber'] === false ? '1' : intval( $this->imdb_widget_values['imdbwidgetproducernumber'] );
		$nbtotalproducer = count( $producer );

		if ( $nbtotalproducer === 0 ) {
			return $output;
		}

		$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
		$output .= sprintf( esc_attr( _n( 'Producer', 'Producers', $nbtotalproducer, 'lumiere-movies' ) ), number_format_i18n( $nbtotalproducer ) );

		$output .= ':</span>';

		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_widget_values['imdbtaxonomyproducer'] === '1' ) ) {

			for ( $i = 0; ( $i < $nbtotalproducer ) && ( $i < $nbproducer ); $i++ ) {

				$output .= $this->lumiere_make_display_taxonomy( 'producer', esc_attr( $producer[ $i ]['name'] ), esc_attr( $producer[ $i ]['role'] ), 'two' );

			}

			return $output;

		}

		for ( $i = 0; ( $i < $nbtotalproducer ) && ( $i < $nbproducer ); $i++ ) {

			$output .= "\n\t\t\t" . '<div align="center" class="lumiere_container">';
			$output .= "\n\t\t\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';

			/**
			 * Use links builder classes.
			 * Each one has its own class passed in $link_maker,
			 * according to which option the lumiere_select_link_maker() found in Frontend.
			 */
			$output .= $this->link_maker->lumiere_link_popup_people( $producer, $i );

			$output .= "\n\t\t\t\t" . '</div>';
			$output .= "\n\t\t\t\t" . '<div align="right">';

			if ( $producer[ $i ]['role'] !== null && strlen( $producer[ $i ]['role'] ) !== 0 ) {
				$output .= esc_attr( $producer[ $i ]['role'] );
			} else {
				$output .= '&nbsp;';
			}

			$output .= '</div>';
			$output .= "\n\t\t\t" . '</div>';

		} // endfor

		return $output;

	}

	/**
	 * Display the writers
	 *
	 * @param \Imdb\Title $movie IMDbPHP title class
	 */
	private function lumiere_movies_writer( \Imdb\Title $movie ): string {

		$output = '';
		$writer = $movie->writing();
		$nbtotalwriters = count( $writer );

		if ( $nbtotalwriters === 0 ) {
			return $output;
		}

		$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
		$output .= sprintf( esc_attr( _n( 'Writer', 'Writers', $nbtotalwriters, 'lumiere-movies' ) ), number_format_i18n( $nbtotalwriters ) );
		$output .= ':</span>';

		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_widget_values['imdbtaxonomywriter'] === '1' ) ) {

			for ( $i = 0; $i < $nbtotalwriters; $i++ ) {

				$output .= $this->lumiere_make_display_taxonomy( 'writer', esc_attr( $writer[ $i ]['name'] ), esc_attr( $writer[ $i ]['role'] ), 'two' );

			}

			return $output;

		}

		for ( $i = 0; $i < $nbtotalwriters; $i++ ) {

			$output .= "\n\t\t\t" . '<div align="center" class="lumiere_container">';
			$output .= "\n\t\t\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';

			/**
			 * Use links builder classes.
			 * Each one has its own class passed in $link_maker,
			 * according to which option the lumiere_select_link_maker() found in Frontend.
			 */
			$output .= $this->link_maker->lumiere_link_popup_people( $writer, $i );

			$output .= "\n\t\t\t\t" . '</div>';
			$output .= "\n\t\t\t\t" . '<div align="right">';

			if ( $writer[ $i ]['role'] !== null && strlen( $writer[ $i ]['role'] ) !== 0 ) {
				$output .= sanitize_text_field( $writer[ $i ]['role'] );
			} else {
				$output .= '&nbsp;';
			}

				$output .= "\n\t\t\t\t" . '</div>';
				$output .= "\n\t\t\t" . '</div>';

		} // endfor

		return $output;
	}

	/**
	 * Display actors
	 *
	 * @param \Imdb\Title $movie IMDbPHP title class
	 */
	private function lumiere_movies_actor( \Imdb\Title $movie ): string {

		$output = '';
		$cast = $movie->cast();
		$nbactors = intval( $this->imdb_widget_values['imdbwidgetactornumber'] ) === 0 ? '1' : intval( $this->imdb_widget_values['imdbwidgetactornumber'] );
		$nbtotalactors = count( $cast );

		if ( $nbtotalactors === 0 ) {
			return $output;
		}

		$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
		$output .= sprintf( esc_attr( _n( 'Actor', 'Actors', $nbtotalactors, 'lumiere-movies' ) ), number_format_i18n( $nbtotalactors ) );
		$output .= ':</span>';

		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_widget_values['imdbtaxonomyactor'] === '1' ) ) {

			for ( $i = 0; ( $i < $nbtotalactors ) && ( $i < $nbactors ); $i++ ) {

				$output .= $this->lumiere_make_display_taxonomy( 'actor', esc_attr( $cast[ $i ]['name'] ), esc_attr( $cast[ $i ]['role'] ), 'two' );

			}

			return $output;

		}

		for ( $i = 0; $i < $nbactors && ( $i < $nbtotalactors ); $i++ ) {

			$output .= "\n\t\t\t" . '<div align="center" class="lumiere_container">';
			$output .= "\n\t\t\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';

			/**
			 * Use links builder classes.
			 * Each one has its own class passed in $link_maker,
			 * according to which option the lumiere_select_link_maker() found in Frontend.
			 */
			$output .= $this->link_maker->lumiere_link_popup_people( $cast, $i );

			$output .= '</div>';
			$output .= "\n\t\t\t\t" . '<div class="lumiere_align_right lumiere_flex_auto">';
			// @since 3.9.8 added isset()
			$output .= isset( $cast[ $i ]['role'] ) ? esc_attr( preg_replace( '/\n/', '', $cast[ $i ]['role'] ) ) : ''; # remove the <br> that breaks the layout
			$output .= '</div>';
			$output .= "\n\t\t\t" . '</div>';

		} // endfor

		return $output;
	}

	/**
	 * Display plots
	 *
	 * @param \Imdb\Title $movie IMDbPHP title class
	 */
	private function lumiere_movies_plot( \Imdb\Title $movie ): string {

		$output = '';
		$plot = $movie->plot();
		$nbplots = intval( $this->imdb_widget_values['imdbwidgetplotnumber'] ) === 0 ? '1' : intval( $this->imdb_widget_values['imdbwidgetplotnumber'] );
		$nbtotalplots = count( $plot );

		// tested if the array contains data; if not, doesn't go further
		if ( $nbtotalplots === 0 ) {
			return $output;
		}

		$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
		$output .= sprintf( esc_attr( _n( 'Plot', 'Plots', $nbtotalplots, 'lumiere-movies' ) ), number_format_i18n( $nbtotalplots ) );
		$output .= ':</span><br />';

		for ( $i = 0; ( ( $i < $nbtotalplots ) && ( $i < $nbplots ) ); $i++ ) {

			/**
			 * Use links builder classes.
			 * Each one has its own class passed in $link_maker,
			 * according to which option the lumiere_select_link_maker() found in Frontend.
			 */
			$output .= $this->link_maker->lumiere_movies_plot_details( $plot[ $i ] );

			// add hr to every plot but the last.
			if ( $i < ( $nbtotalplots - 1 ) && $i < ( $nbplots - 1 ) ) {
				$output .= "\n\t\t\t\t<hr>";
			}
		}

		return $output;
	}

	/**
	 * Display the credit link
	 *
	 * @param \Imdb\Title $movie IMDbPHP title class
	 */
	private function lumiere_movies_source( \Imdb\Title $movie ): string {

		$output = '';
		$mid_premier_resultat = $movie->imdbid();
		$mid_premier_resultat_sanitized = filter_var( $mid_premier_resultat, FILTER_SANITIZE_NUMBER_INT ) !== false ? filter_var( $mid_premier_resultat, FILTER_SANITIZE_NUMBER_INT ) : null;

		if ( $mid_premier_resultat_sanitized === null ) {
			return $output;
		}

		$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
		$output .= esc_html__( 'Source', 'lumiere-movies' );
		$output .= ':</span>';

		/**
		 * Use links builder classes.
		 * Each one has its own class passed in $link_maker,
		 * according to which option the lumiere_select_link_maker() found in Frontend.
		 */
		$output .= $this->link_maker->lumiere_movies_source_details( $mid_premier_resultat_sanitized );

		return $output;
	}

	/**
	 * Do taxonomy layouts and register taxonomy terms
	 *
	 * @param string $type_item mandatory: the general category of the item, ie 'director', 'color'
	 * @param string $first_title mandatory: the name of the first string to display, ie "Stanley Kubrick"
	 * @param string|null $second_title optional: the name of a second string to display, utilised in $layout 'two', ie "director"
	 * @param string $layout optional: the type of the layout, either 'one' or 'two', one by default
	 *
	 * @return string the text to be outputed
	 */
	private function lumiere_make_display_taxonomy( string $type_item, string $first_title, ?string $second_title = null, string $layout = 'one' ): string {

		// ************** Vars and sanitization */
		$lang_term = 'en'; # language to register the term with, English by default
		$output = '';
		$list_taxonomy_term = '';
		$layout = esc_attr( $layout );
		$taxonomy_category = esc_attr( $type_item );
		$taxonomy_term = esc_attr( $first_title );
		$second_title = $second_title !== null ? esc_attr( $second_title ) : '';
		$taxonomy_url_string_first = esc_attr( $this->imdb_admin_values['imdburlstringtaxo'] );
		$taxonomy_category_full = $taxonomy_url_string_first . $taxonomy_category;

		// ************** Add taxonomy

		if ( false !== ( get_the_ID() ) ) {

			// delete if exists, for debugging purposes
			# if ( $term_already = get_term_by('name', $taxonomy_term, $taxonomy_category_full ) )
			#	 wp_delete_term( $term_already->term_id, $taxonomy_category_full) ;

			if ( taxonomy_exists( $taxonomy_category_full ) ) {

				$term = term_exists( $taxonomy_term, $taxonomy_category_full );

				// if the tag exists.
				if ( $term === null ) {

					// insert it and get its id
					// $term = wp_insert_term($taxonomy_term, $taxonomy_category_full, array('lang' => $lang_term) );
					// I believe adding the above option 'lang' is useless, inserting without 'lang'.
					$term = wp_insert_term( $taxonomy_term, $taxonomy_category_full );
					$this->logger->log()->debug( '[Lumiere][' . self::CLASS_NAME . "] Taxonomy term $taxonomy_term added to $taxonomy_category_full" );
				}

				// Create a list of Lumière tags meant to be inserted to Lumière Taxonomy
				$list_taxonomy_term .= $taxonomy_term . ', ';

			}
		}
		if ( isset( $term ) && ! is_wp_error( $term ) && false !== get_the_ID() ) {

			// Link Lumière tags to Lumière Taxonomy
			wp_set_post_terms( get_the_ID(), $list_taxonomy_term, $taxonomy_category_full, true );

			// Add Lumière tags to the current WordPress post. But we don't want it!
			# wp_set_post_tags(get_the_ID(), $list_taxonomy_term, 'post_tag', true);

			// Compatibility with Polylang WordPress plugin, add a language to the taxonomy term.
			// Function in class Polylang.
			if ( $this->plugin_polylang !== null ) {

				$this->plugin_polylang->lumiere_polylang_add_lang_to_taxo( (array) $term );

			}

		}

		// ************** Return layout

		$taxo_link = get_term_link( $taxonomy_term, $taxonomy_category_full );
		$taxo_link = is_wp_error( $taxo_link ) === false ? $taxo_link : '';

		// layout=two: display the layout for double entry details, ie actors
		if ( $layout === 'two' ) {

			$output .= "\n\t\t\t" . '<div align="center" class="lumiere_container">';
			$output .= "\n\t\t\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';
			$output .= "\n\t\t\t\t\t<a class=\"linkincmovie\" href=\""
					. esc_url( $taxo_link )
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
					. 'href="' . esc_url( $taxo_link )
					. '" '
					. 'title="' . esc_html__( 'Find similar taxonomy results', 'lumiere-movies' ) . '">';
			$output .= $taxonomy_term;
			$output .= '</a>';

		}

		return $output;

	}

	/**
	 * Create an html link for taxonomy
	 *
	 * @param string $taxonomy
	 */
	private function lumiere_make_taxonomy_link ( string $taxonomy ): string {

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
	public static function lumiere_movie_start (): void {

		new self( new Polylang() );

	}

}
