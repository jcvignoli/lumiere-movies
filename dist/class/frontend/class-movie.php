<?php declare( strict_types = 1 );
/**
 * Class for displaying movies. This class automatically catches spans. It displays taxonomy links and add taxonomy according to the selected options
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       2.0
 * @package lumiere-movies
 */

namespace Lumiere;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( '\Lumiere\Settings' ) ) ) {
	wp_die( 'You can not call directly this page' );
}

use \Imdb\Title;
use \Imdb\TitleSearch;

class Movie {

	// Use trait frontend
	use \Lumiere\Frontend {
		Frontend::__construct as public __constructFrontend;
	}

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
	 *  Class constructor
	 */
	public function __construct() {

		// Construct Frontend trait.
		$this->__constructFrontend( 'movieClass' );

		// Run the initialisation of the class.
		// Not needed since lumiere_show() is called by lumiere_parse_spans().
		// add_action ('the_loop', [$this, 'lumiere_show'], 0);

		// Parse the content to add the movies.
		add_action( 'the_content', [ $this, 'lumiere_parse_spans' ] );

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
	 * @param array<int, array<string, string>> $imdbIdOrTitleOutside Name or IMDbID of the movie to find in array
	 */
	public function lumiere_show( array $imdbIdOrTitleOutside = null ): string {

		/* Vars */
		global $lumiere_count_me_siffer;

		$logger = $this->logger->log();
		$config_class = $this->config_class;
		$lumiere_count_me_siffer = isset( $lumiere_count_me_siffer ) ? $lumiere_count_me_siffer : 0; # var for counting only one results
		$imdbIdOrTitle = $imdbIdOrTitleOutside !== null ? $imdbIdOrTitleOutside : null;
		$output = '';

		$logger->debug( '[Lumiere][movieClass] Calling IMDbPHP class.' );

		$search = new TitleSearch( $this->imdbphp_class, $logger );

		// $imdbIdOrTitle var comes from custom post's field in widget or in post
		$counter_imdbIdOrTitle = $imdbIdOrTitle !== null ? count( $imdbIdOrTitle ) : 0;

		for ( $i = 0; $i < $counter_imdbIdOrTitle; $i++ ) {

			// sanitize
			$film = $imdbIdOrTitle !== null ? $imdbIdOrTitle[ $i ] : null;

			// A movie's title has been specified, get its imdbid.
			if ( isset( $film['byname'] ) ) {

				$film = $film['byname'];

				$logger->debug( "[Lumiere][movieClass] Movie title provided: $film" );

				// check a the movie title exists.
				if ( strlen( $film ) !== 0 ) {

					$logger->debug( "[Lumiere][movieClass] searching for $film" );

					$results = $search->search( $film, $this->config_class->lumiere_select_type_search() );

				}

				$midPremierResultat = isset( $results[0] ) ? filter_var( $results[0]->imdbid(), FILTER_SANITIZE_NUMBER_INT ) : null;

				// No result was found in imdbphp query.
				if ( $midPremierResultat === null ) {

					$logger->info( "[Lumiere][movieClass] No movie found for $film, aborting." );

					// no result, so jump to the next query and forget the current
					continue;

				}

				$logger->debug( "[Lumiere][movieClass] Result found: $midPremierResultat." );

				// no movie's title but a movie's ID has been specified
			} elseif ( isset( $film['bymid'] ) ) {

				$midPremierResultat = filter_var( $film['bymid'], FILTER_SANITIZE_NUMBER_INT );
				$logger->debug( "[Lumiere][movieClass] Movie ID provided: '$midPremierResultat'." );

			}

			if ( $film === null || ! isset( $midPremierResultat ) || $midPremierResultat === false ) {

				$logger->debug( '[Lumiere][movieClass] No result found for this query.' );
				continue;

			}

			if ( $this->lumiere_filter_single_movies( $midPremierResultat, $lumiere_count_me_siffer ) === true ) {

				$logger->debug( "[Lumiere][movieClass] $midPremierResultat already called, skipping" );
				continue;

			}

			$logger->debug( "[Lumiere][movieClass] Displaying rows for '$midPremierResultat'" );

			$output .= "\n\t\t\t\t\t\t\t\t\t" . '<!-- ### Lumière! movies plugin ### -->';
			$output .= "\n\t<div class='imdbincluded";

			// add dedicated class for themes
			$output .= ' imdbincluded_' . $this->imdb_admin_values['imdbintotheposttheme'];
			$output .= "'>";

			$output .= $this->lumiere_movie_design( $midPremierResultat ); # passed those two values to the design
			$output .= "\n\t</div>";

			$lumiere_count_me_siffer++; # increment counting only one results

		}

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
	 *  Callback for movies by imdb id
	 *
	 * @param array<string> $block_span
	 */
	private function lumiere_parse_spans_callback_id( array $block_span ): string {

		$imdbIdOrTitle = [];
		$imdbIdOrTitle[]['bymid'] = sanitize_text_field( $block_span[1] );
		return $this->lumiere_show( $imdbIdOrTitle );

	}

	/**
	 * Callback for movies by imdb title
	 *
	 * @param array<string> $block_span
	 */
	private function lumiere_parse_spans_callback_title( array $block_span ): string {

		$imdbIdOrTitle = [];
		$imdbIdOrTitle[]['byname'] = sanitize_text_field( $block_span[1] );
		return $this->lumiere_show( $imdbIdOrTitle );

	}

	/**
	 * Replace [imdblt] shortcode by the movie
	 * Obsolete, kept for compatibility purposes
	 *
	 * @param string|array<string> $atts
	 */
	public function parse_lumiere_tag_transform( $atts = [], string $content ): string {

		//shortcode_atts(array( 'id' => 'default id', 'film' => 'default film'), $atts);

		$movie_title = $content;

		return $this->lumiere_external_call( $movie_title, '', '' );

	}

	/**
	 * Replace [imdbltid] shortcode by the movie
	 * @obsolete Kept for compatibility purposes
	 *
	 * @param string|array<string> $atts
	 */
	public function parse_lumiere_tag_transform_id( $atts = [], string $content ): string {

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

		// highslide popup
		if ( $this->imdb_admin_values['imdbpopup_highslide'] === '1' ) {

			$link_parsed = $this->lumiere_popup_highslide_film_link( $link_parsed );
			return $link_parsed;
		}

		// classic popup
		$link_parsed = $this->lumiere_popup_classical_film_link( $link_parsed );
		return $link_parsed;

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

		// highslide popup
		if ( $this->imdb_admin_values['imdbpopup_highslide'] === '1' ) {

			$link_parsed = $this->lumiere_popup_highslide_film_link( $link_parsed );
			return $link_parsed;

		}

		// classic popup
		$link_parsed = $this->lumiere_popup_classical_film_link( $link_parsed );
		return $link_parsed;

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
	 * Highslide popup function
	 * Build an HTML link to open a popup with highslide for searching a movie (using js/lumiere_scripts.js)
	 *
	 * @param array<int, string> $link_parsed html tags and text to be modified
	 * @param string $popuplarg -> window width, if nothing passed takes database value
	 * @param string $popuplong -> window height, if nothing passed takes database value
	 */
	private function lumiere_popup_highslide_film_link ( array $link_parsed, string $popuplarg = null, string $popuplong = null ): string {

		if ( $popuplarg !== null ) {
			$popuplarg = $this->imdb_admin_values['imdbpopuplarg'];
		}

		if ( $popuplong !== null ) {
			$popuplong = $this->imdb_admin_values['imdbpopuplong'];
		}

		$parsed_result = '<a class="link-imdblt-highslidefilm" data-highslidefilm="' . Utils::lumiere_name_htmlize( $link_parsed[1] ) . '" title="' . esc_html__( 'Open a new window with IMDb informations', 'lumiere-movies' ) . '">' . $link_parsed[1] . '</a>&nbsp;';

		return $parsed_result;

	}

	/**
	 * Classical popup function
	 * Build an HTML link to open a popup for searching a movie (using js/lumiere_scripts.js)
	 *
	 * @param array<int, string> $link_parsed html tags and text to be modified
	 * @param string $popuplarg -> window width, if nothing passed takes database value
	 * @param string $popuplong -> window height, if nothing passed takes database value
	 */
	private function lumiere_popup_classical_film_link ( array $link_parsed, string $popuplarg = null, string $popuplong = null ): string {

		if ( $popuplarg !== null ) {
			$popuplarg = $this->imdb_admin_values['imdbpopuplarg'];
		}

		if ( $popuplong !== null ) {
			$popuplong = $this->imdb_admin_values['imdbpopuplong'];
		}

		$parsed_result = '<a class="link-imdblt-classicfilm" data-classicfilm="' . Utils::lumiere_name_htmlize( $link_parsed[1] ) . '" title="' . esc_html__( 'Open a new window with IMDb informations', 'lumiere-movies' ) . '">' . $link_parsed[1] . '</a>&nbsp;';

		return $parsed_result;
	}

	/**
	 * Function external call (ie, inside a post)
	 * Utilized to build from shortcodes
	 * @obsolete, not using shortcodes anymore
	 *
	 * @param string $moviename
	 * @param string $filmid
	 * @param string $external set to 'external' for use from outside
	 */
	public function lumiere_external_call ( string $moviename = null, string $filmid = null, string $external = null ): string {

		$imdbIdOrTitle = [];

		// Call function from external (using parameter "external" )
		// Especially made to be integrated (ie, inside a php code)
		if ( ( $external === 'external' ) && isset( $moviename ) ) {

			$imdbIdOrTitle[]['byname'] = $moviename;

		}

		// Call function from external (using parameter "external" )
		// Especially made to be integrated (ie, inside a php code)
		if ( ( $external === 'external' ) && isset( $filmid ) ) {

			$imdbIdOrTitle[]['bymid'] = $filmid;

		}

		//  Call with the parameter - imdb movie name (imdblt)
		if ( isset( $moviename ) && strlen( $moviename ) !== 0 && $external !== 'external' ) {

			$imdbIdOrTitle[]['byname'] = $moviename;

		}

		//  Call with the parameter - imdb movie id (imdbltid)
		if ( isset( $filmid ) && strlen( $filmid ) !== 0 && ( $external !== 'external' ) ) {

			$imdbIdOrTitle[]['bymid'] = $filmid;

		}

		return $this->lumiere_show( $imdbIdOrTitle );

	}

	/**
	 * Function to display the layout and call all subfonctions
	 *
	 * @param string $midPremierResultat -> IMDb ID, not as int since it loses its heading 0s
	 */
	private function lumiere_movie_design( string $midPremierResultat ): string {

		// Simplify the coding.
		$logger = $this->logger->log();

		// initialise the output.
		$outputfinal = '';

		/* Start imdbphp class for new query based upon $midPremierResultat */
		$movie = new Title( $midPremierResultat, $this->imdbphp_class, $logger );

		foreach ( $this->imdb_widget_values['imdbwidgetorder'] as $lumiere_magicnumber ) {

			if ( ( $lumiere_magicnumber === $this->imdb_widget_values['imdbwidgetorder']['title'] )
			&& ( $this->imdb_widget_values['imdbwidgettitle'] === '1' ) ) {
				$outputfinal .= $this->lumiere_movie_design_addwrapper( $this->lumiere_movies_title( $movie ), 'title' );
			}

			if ( ( $lumiere_magicnumber === $this->imdb_widget_values['imdbwidgetorder']['pic'] )
			&& ( $this->imdb_widget_values['imdbwidgetpic'] === '1' ) ) {
				$outputfinal .= $this->lumiere_movies_pics( $movie );
			}

			if ( ( $lumiere_magicnumber === $this->imdb_widget_values['imdbwidgetorder']['country'] )
			&& ( $this->imdb_widget_values['imdbwidgetcountry'] === '1' ) ) {
				$outputfinal .= $this->lumiere_movie_design_addwrapper( $this->lumiere_movies_country( $movie ), 'country' );
			}

			if ( ( $lumiere_magicnumber === $this->imdb_widget_values['imdbwidgetorder']['runtime'] )
			&& ( $this->imdb_widget_values['imdbwidgetruntime'] === '1' ) ) {
				$outputfinal .= $this->lumiere_movie_design_addwrapper( $this->lumiere_movies_runtime( $movie ), 'runtime' );
			}

			if ( ( $lumiere_magicnumber === $this->imdb_widget_values['imdbwidgetorder']['rating'] )
			&& ( $this->imdb_widget_values['imdbwidgetrating'] === '1' ) ) {
				$outputfinal .= $this->lumiere_movie_design_addwrapper( $this->lumiere_movies_rating( $movie ), 'rating' );
			}

			if ( ( $lumiere_magicnumber === $this->imdb_widget_values['imdbwidgetorder']['language'] )
			&& ( $this->imdb_widget_values['imdbwidgetlanguage'] === '1' ) ) {
				$outputfinal .= $this->lumiere_movie_design_addwrapper( $this->lumiere_movies_language( $movie ), 'language' );
			}

			if ( ( $lumiere_magicnumber === $this->imdb_widget_values['imdbwidgetorder']['genre'] )
			&& ( $this->imdb_widget_values['imdbwidgetgenre'] === '1' ) ) {
				$outputfinal .= $this->lumiere_movie_design_addwrapper( $this->lumiere_movies_genre( $movie ), 'genre' );
			}

			if ( ( $lumiere_magicnumber === $this->imdb_widget_values['imdbwidgetorder']['keyword'] )
			&& ( $this->imdb_widget_values['imdbwidgetkeyword'] === '1' ) ) {
				$outputfinal .= $this->lumiere_movie_design_addwrapper( $this->lumiere_movies_keywords( $movie ), 'keyword' );
			}

			if ( ( $lumiere_magicnumber === $this->imdb_widget_values['imdbwidgetorder']['goof'] )
			&& ( $this->imdb_widget_values['imdbwidgetgoof'] === '1' ) ) {
				$outputfinal .= $this->lumiere_movie_design_addwrapper( $this->lumiere_movies_goofs( $movie ), 'goof' );
			}

			if ( ( $lumiere_magicnumber === $this->imdb_widget_values['imdbwidgetorder']['comment'] )
			&& ( $this->imdb_widget_values['imdbwidgetcomment'] === '1' ) ) {
				$outputfinal .= $this->lumiere_movie_design_addwrapper( $this->lumiere_movies_comment( $movie ), 'comment' );
			}

			if ( ( $lumiere_magicnumber === $this->imdb_widget_values['imdbwidgetorder']['quote'] )
			&& ( $this->imdb_widget_values['imdbwidgetquote'] === '1' ) ) {
				$outputfinal .= $this->lumiere_movie_design_addwrapper( $this->lumiere_movies_quotes( $movie ), 'quote' );
			}

			if ( ( $lumiere_magicnumber === $this->imdb_widget_values['imdbwidgetorder']['tagline'] )
			&& ( $this->imdb_widget_values['imdbwidgettagline'] === '1' ) ) {
				$outputfinal .= $this->lumiere_movie_design_addwrapper( $this->lumiere_movies_taglines( $movie ), 'tagline' );
			}

			if ( ( $lumiere_magicnumber === $this->imdb_widget_values['imdbwidgetorder']['trailer'] )
			&& ( $this->imdb_widget_values['imdbwidgettrailer'] === '1' ) ) {
				$outputfinal .= $this->lumiere_movie_design_addwrapper( $this->lumiere_movies_trailer( $movie ), 'trailer' );
			}

			if ( ( $lumiere_magicnumber === $this->imdb_widget_values['imdbwidgetorder']['color'] )
			&& ( $this->imdb_widget_values['imdbwidgetcolor'] === '1' ) ) {
				$outputfinal .= $this->lumiere_movie_design_addwrapper( $this->lumiere_movies_color( $movie ), 'color' );
			}

			if ( ( $lumiere_magicnumber === $this->imdb_widget_values['imdbwidgetorder']['alsoknow'] )
			&& ( $this->imdb_widget_values['imdbwidgetalsoknow'] === '1' ) ) {
				$outputfinal .= $this->lumiere_movie_design_addwrapper( $this->lumiere_movies_aka( $movie ), 'alsoknown' );
			}

			if ( ( $lumiere_magicnumber === $this->imdb_widget_values['imdbwidgetorder']['composer'] )
			&& ( $this->imdb_widget_values['imdbwidgetcomposer'] === '1' ) ) {
				$outputfinal .= $this->lumiere_movie_design_addwrapper( $this->lumiere_movies_composer( $movie ), 'composer' );
			}

			if ( ( $lumiere_magicnumber === $this->imdb_widget_values['imdbwidgetorder']['soundtrack'] )
			&& ( $this->imdb_widget_values['imdbwidgetsoundtrack'] === '1' ) ) {
				$outputfinal .= $this->lumiere_movie_design_addwrapper( $this->lumiere_movies_soundtrack( $movie ), 'soundtrack' );
			}

			if ( ( $lumiere_magicnumber === $this->imdb_widget_values['imdbwidgetorder']['prodcompany'] )
			&& ( $this->imdb_widget_values['imdbwidgetprodcompany'] === '1' ) ) {
				$outputfinal .= $this->lumiere_movie_design_addwrapper( $this->lumiere_movies_prodcompany( $movie ), 'prodcompany' );
			}

			if ( ( $lumiere_magicnumber === $this->imdb_widget_values['imdbwidgetorder']['officialsites'] )
			&& ( $this->imdb_widget_values['imdbwidgetofficialsites'] === '1' ) ) {
				$outputfinal .= $this->lumiere_movie_design_addwrapper( $this->lumiere_movies_officialsite( $movie ), 'officialsites' );
			}

			if ( ( $lumiere_magicnumber === $this->imdb_widget_values['imdbwidgetorder']['source'] )
			&& ( $this->imdb_widget_values['imdbwidgetsource'] === '1' ) ) {
				$outputfinal .= $this->lumiere_movie_design_addwrapper( $this->lumiere_movies_creditlink( $midPremierResultat ), 'source' ); # doesn't need class but movie id
			}

			if ( ( $lumiere_magicnumber === $this->imdb_widget_values['imdbwidgetorder']['director'] )
			&& ( $this->imdb_widget_values['imdbwidgetdirector'] === '1' ) ) {
				$outputfinal .= $this->lumiere_movie_design_addwrapper( $this->lumiere_movies_director( $movie ), 'director' );
			}

			if ( ( $lumiere_magicnumber === $this->imdb_widget_values['imdbwidgetorder']['creator'] )
			&& ( $this->imdb_widget_values['imdbwidgetcreator'] === '1' ) ) {
				$outputfinal .= $this->lumiere_movie_design_addwrapper( $this->lumiere_movies_creator( $movie ), 'creator' );
			}

			if ( ( $lumiere_magicnumber === $this->imdb_widget_values['imdbwidgetorder']['producer'] )
			&& ( $this->imdb_widget_values['imdbwidgetproducer'] === '1' ) ) {
				$outputfinal .= $this->lumiere_movie_design_addwrapper( $this->lumiere_movies_producer( $movie ), 'producer' );
			}

			if ( ( $lumiere_magicnumber === $this->imdb_widget_values['imdbwidgetorder']['writer'] )
			&& ( $this->imdb_widget_values['imdbwidgetwriter'] === '1' ) ) {
				$outputfinal .= $this->lumiere_movie_design_addwrapper( $this->lumiere_movies_writer( $movie ), 'writer' );
			}

			if ( ( $lumiere_magicnumber === $this->imdb_widget_values['imdbwidgetorder']['actor'] )
			&& ( $this->imdb_widget_values['imdbwidgetactor'] === '1' ) ) {
				$outputfinal .= $this->lumiere_movie_design_addwrapper( $this->lumiere_movies_actor( $movie ), 'actor' );
			}

			if ( ( $lumiere_magicnumber === $this->imdb_widget_values['imdbwidgetorder']['plot'] )
			&& ( $this->imdb_widget_values['imdbwidgetplot'] === '1' ) ) {
				$outputfinal .= $this->lumiere_movie_design_addwrapper( $this->lumiere_movies_plot( $movie ), 'plot' );
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
	 * @param \Imdb\Title $movie -> takes the value of IMDbPHP class
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
	 * Display the picture of the movie
	 * Does not go through lumiere_movie_design_addwrapper()
	 *
	 * @param \Imdb\Title $movie -> takes the value of IMDbPHP class
	 */
	private function lumiere_movies_pics ( \Imdb\Title $movie ): string {

		$output = '';

		$photo_url = $movie->photo_localurl( false ) !== false ? esc_html( $movie->photo_localurl( false ) ) : esc_html( $movie->photo_localurl( true ) ); // create big picture, thumbnail otherwise.

		$photo_url_final = strlen( $photo_url ) === 0 ? esc_url( $this->imdb_admin_values['imdbplugindirectory'] . 'pics/no_pics.gif' ) : $photo_url; // take big/thumbnail picture if exists, no_pics otherwise

		$output .= "\n\t\t\t\t\t\t\t" . '<!-- pic -->';
		$output .= "\n\t\t" . '<div class="imdbelementPIC">';

		if ( $this->imdb_admin_values['imdbpopup_highslide'] === '1' ) {

			// Link
			$output .= "\n\t\t\t" . '<a class="highslide_pic" href="'
				. $photo_url_final
				. '" title="'
				. esc_attr( $movie->title() )
				. '">';

		}

		// loading=\"eager\" to prevent WordPress loading lazy that doesn't go well with cache scripts
		$output .= "\n\t\t\t" . '<img loading="eager" class="imdbelementPICimg" src="';

		$output .= $photo_url_final
			. '" alt="'
			. esc_html__( 'Photo of', 'lumiere-movies' )
			. ' '
			. esc_attr( $movie->title() ) . '" '
			. 'width="' . intval( $this->imdb_admin_values['imdbcoversizewidth'] ) . '" />' . "\n";

		// new verification, closure code related to highslide
		if ( $this->imdb_admin_values['imdbpopup_highslide'] === '1' ) {
			$output .= "\n\t\t\t</a>\n";
		}

		$output .= "\n\t\t" . '</div>';

		return $output;
	}

	/**
	 * Display the country of origin
	 *
	 * @param \Imdb\Title $movie -> takes the value of IMDbPHP class
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

		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_widget_values['imdbtaxonomycountry'] === '1' ) ) {

			for ( $i = 0; $i < $nbtotalcountry; $i++ ) {

				$output .= $this->lumiere_make_display_taxonomy( 'country', esc_attr( $country[ $i ] ), '', 'one' );
				if ( $i < $nbtotalcountry - 1 ) {
					$output .= ', ';
				}

			}

		} else {

			for ( $i = 0; $i < $nbtotalcountry; $i++ ) {
				$output .= sanitize_text_field( $country[ $i ] );
				if ( $i < $nbtotalcountry - 1 ) {
					$output .= ', ';
				}
			} // endfor

		}

		return $output;

	}

	/**
	 * Display the runtime
	 *
	 * @param \Imdb\Title $movie -> takes the value of IMDbPHP class
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
	 * @param \Imdb\Title $movie -> takes the value of IMDbPHP class
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

		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_widget_values['imdbtaxonomylanguage'] === '1' ) ) {

			for ( $i = 0; $i < $nbtotallanguages; $i++ ) {

				$output .= $this->lumiere_make_display_taxonomy( 'language', esc_attr( $languages[ $i ] ), '', 'one' );
				if ( $i < $nbtotallanguages - 1 ) {
					$output .= ', ';
				}

			}

		} else {
			for ( $i = 0; $i < $nbtotallanguages; $i++ ) {

				$output .= sanitize_text_field( $languages[ $i ] );

				if ( $i < $nbtotallanguages - 1 ) {
					$output .= ', ';
				}

			}
		}

		return $output;
	}

	/**
	 * Display the rating
	 *
	 * @param \Imdb\Title $movie -> takes the value of IMDbPHP class
	 */
	private function lumiere_movies_rating( \Imdb\Title $movie ): string {

		$output = '';
		$votes_sanitized = intval( $movie->votes() );
		$rating_sanitized = intval( $movie->rating() );

		if ( $votes_sanitized === 0 ) {
			return $output;
		}

		$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
		$output .= esc_html__( 'Rating', 'lumiere-movies' );
		$output .= ':</span>';

		$output .= ' <img src="' . $this->imdb_admin_values['imdbplugindirectory'] . 'pics/showtimes/' . ( round( $rating_sanitized * 2, 0 ) / 0.2 ) .
			'.gif" title="' . esc_html__( 'vote average ', 'lumiere-movies' ) . $rating_sanitized . esc_html__( ' out of 10', 'lumiere-movies' ) . '"  / >';
		$output .= ' (' . number_format( $votes_sanitized, 0, '', "'" ) . ' ' . esc_html__( 'votes', 'lumiere-movies' ) . ')';

		return $output;

	}

	/**
	 * Display the genre
	 *
	 * @param \Imdb\Title $movie -> takes the value of IMDbPHP class
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

		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_widget_values['imdbtaxonomygenre'] === '1' ) ) {

			for ( $i = 0; $i < $nbtotalgenre; $i++ ) {

				$output .= $this->lumiere_make_display_taxonomy( 'genre', esc_attr( $genre[ $i ] ), '', 'one' );
				if ( $i < $nbtotalgenre - 1 ) {
					$output .= ', ';
				}

			}

		} else {

			for ( $i = 0; $i < $nbtotalgenre; $i++ ) {

				$output .= esc_attr( $genre[ $i ] );
				if ( $i < $nbtotalgenre - 1 ) {
					$output .= ', ';
				}

			}
		}

		return $output;
	}

	/**
	 * Display the keywords
	 *
	 * @param \Imdb\Title $movie -> takes the value of IMDbPHP class
	 */
	private function lumiere_movies_keywords( \Imdb\Title $movie ): string {

		$output = '';
		$keywords = $movie->keywords();
		$nbtotalkeywords = count( $keywords );

		if ( $nbtotalkeywords === 0 ) {
			return $output;
		}

		$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
		$output .= sprintf( esc_attr( _n( 'Keyword', 'Keywords', $nbtotalkeywords, 'lumiere-movies' ) ), number_format_i18n( $nbtotalkeywords ) );
		$output .= ':</span>';

		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_widget_values['imdbtaxonomykeyword'] === '1' ) ) {

			for ( $i = 0; $i < $nbtotalkeywords; $i++ ) {

				$output .= $this->lumiere_make_display_taxonomy( 'keyword', esc_attr( $keywords[ $i ] ), '', 'one' );
				if ( $i < $nbtotalkeywords - 1 ) {
					$output .= ', ';
				}

			}

		} else {
			for ( $i = 0; $i < $nbtotalkeywords; $i++ ) {

				$output .= esc_attr( $keywords[ $i ] );

				if ( $i < $nbtotalkeywords - 1 ) {
					$output .= ', ';
				}
			}
		}

		return $output;

	}

	/* Display the goofs
	 *
	 * @param \Imdb\Title $movie -> takes the value of IMDbPHP class
	 */
	private function lumiere_movies_goofs( \Imdb\Title $movie ): string {

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

	/* Display the main user comment
	 *
	 * @param \Imdb\Title $movie -> takes the value of IMDbPHP class
	 */
	private function lumiere_movies_comment( \Imdb\Title $movie ): string {

		$output = '';
		$comment = [];
		$comment = $movie->comment();

		if ( strlen( $comment ) !== 0 ) {

			$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
			$output .= esc_html__( 'User comment', 'lumiere-movies' );
			$output .= ':</span><br />';

			/* Deactivated, seems that method has changed
			$output .= '<';
			$output .= '<i>' . sanitize_text_field( $comment[0]['title'] ) . '</i> by ';

			// if "Remove all links" option is not selected
			if ( ( isset( $this->imdb_admin_values['imdblinkingkill'] ) ) && ( $this->imdb_admin_values['imdblinkingkill'] == false ) ) {

				$output .= '<a href="' . esc_url( $comment[0]['author']['url'] ) . '">' . sanitize_text_field( $comment[0]['author']['name'] ) . '</a>';

			} else {

				$output .= sanitize_text_field( $comment[0]['author']['name'] );

			}

			$output .= '>&nbsp;';
			$output .= sanitize_text_field( $comment[0]['comment'] );
			*/

			$output .= sanitize_text_field( $comment );
		}

		return $output;

	}

	/**
	 *  Display the quotes
	 *
	 *  @param \Imdb\Title $movie -> takes the value of IMDbPHP class
	 */
	private function lumiere_movies_quotes( \Imdb\Title $movie ): string {

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

			//transform <p> tags into <div> tags so they're not impacted by the theme
			$currentquotes = preg_replace( '~<p>~', "\n\t\t\t<div>", $quotes[ $i ] ) ?? $quotes[ $i ];
			$currentquotes = preg_replace( '~</p>~', "\n\t\t\t</div>", $currentquotes ) ?? $currentquotes;

			// if "Remove all links" option is not selected
			if ( $this->imdb_admin_values['imdblinkingkill'] === '0' ) {
				$output .= "\n\t\t\t";
				$output .= $this->lumiere_convert_txtwithhtml_into_popup_people( $currentquotes );

			} else {

				$output .= "\n\t\t" . $this->lumiere_remove_link( $currentquotes );

			}
			if ( $i < ( $nbquotes - 1 ) ) {
				$output .= "\n\t\t\t<hr>"; // add hr to every quote but the last
			}

		}

		return $output;
	}

	/**
	 * Display the taglines
	 *
	 * @param \Imdb\Title $movie -> takes the value of IMDbPHP class
	 */
	private function lumiere_movies_taglines( \Imdb\Title $movie ): string {

		$output = '';
		$taglines = $movie->taglines();
		$nbtaglines = intval( $this->imdb_widget_values['imdbwidgettaglinenumber'] ) === 0 || $this->imdb_widget_values['imdbwidgettaglinenumber'] === false ? '1' : intval( $this->imdb_widget_values['imdbwidgettaglinenumber'] );

		$nbtotaltaglines = intval( count( $taglines ) );

		// If no result, exit.
		if ( $nbtotaltaglines === 0 ) {

			return $output;

		}

		$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
		$output .= sprintf( esc_attr( _n( 'Tagline', 'Taglines', $nbtotaltaglines, 'lumiere-movies' ) ), number_format_i18n( $nbtotaltaglines ) );
		$output .= ':</span>';

		for ( $i = 0; $i < $nbtaglines && ( $i < $nbtotaltaglines ); $i++ ) {

			$output .= "\n\t\t\t&laquo; " . sanitize_text_field( $taglines[ $i ] ) . ' &raquo; ';
			if ( $i < ( $nbtaglines - 1 ) ) {
				$output .= ', '; // add comma to every quote but the last
			}

		}

		return $output;

	}

	/**
	 * Display the trailer
	 *
	 * @param \Imdb\Title $movie -> takes the value of IMDbPHP class
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

			if ( $this->imdb_admin_values['imdblinkingkill'] == false ) { // if "Remove all links" option is not selected
				$output .= "\n\t\t\t<a href='" . esc_url( $trailers[ $i ]['url'] ) . "' title='" . esc_html__( 'Watch on IMBb website the trailer for ', 'lumiere-movies' ) . esc_html( $trailers[ $i ]['title'] ) . "'>" . sanitize_text_field( $trailers[ $i ]['title'] ) . "</a>\n";

			} else { // if "Remove all links" option is selected

				$output .= "\n\t\t\t" . sanitize_text_field( $trailers[ $i ]['title'] ) . ', ' . esc_url( $trailers[ $i ]['url'] );

			}

			if ( ( $i < ( $nbtrailers - 1 ) ) && ( $i < ( $nbtotaltrailers - 1 ) ) ) {
				$output .= ', '; // add comma to every quote but the last
			}
		}

		return $output;

	}

	/**
	 * Display the color
	 *
	 * @param \Imdb\Title $movie -> takes the value of IMDbPHP class
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

		// Taxonomy
		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_widget_values['imdbtaxonomycolor'] === '1' ) ) {

			for ( $i = 0; $i < $nbtotalcolors; $i++ ) {

				$output .= $this->lumiere_make_display_taxonomy( 'color', esc_attr( $colors[ $i ] ), '', 'one' );
				if ( $i < $nbtotalcolors - 1 ) {
					$output .= ', ';
				}

			}

			// No taxonomy
		} else {

			$count_colors = count( $colors );
			for ( $i = 0; $i < $count_colors; $i++ ) {

				$output .= "\n\t\t\t" . sanitize_text_field( $colors[ $i ] );
				if ( $i < $nbtotalcolors - 1 ) {
					$output .= ', ';
				}
			}

		}

		return $output;

	}

	/**
	 * Display the as known as, aka
	 *
	 * @param \Imdb\Title $movie -> takes the value of IMDbPHP class
	 */
	private function lumiere_movies_aka( \Imdb\Title $movie ): string {

		$output = '';
		$alsoknow = $movie->alsoknow();
		$nbalsoknow = intval( $this->imdb_widget_values['imdbwidgetalsoknownumber'] ) === 0 || $this->imdb_widget_values['imdbwidgetalsoknownumber'] === false ? '1' : intval( $this->imdb_widget_values['imdbwidgetalsoknownumber'] );
		$nbtotalalsoknow = intval( count( $alsoknow ) );

		// if no result, exit.
		if ( count( $alsoknow ) === 0 ) {

			return $output;

		}

		$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
		$output .= esc_html__( 'Also known as', 'lumiere-movies' );
		$output .= ':</span>';

		for ( $i = 0; ( $i < $nbtotalalsoknow ) && ( $i < $nbalsoknow ); $i++ ) {

			$output .= "\n\t\t\t<strong>" . sanitize_text_field( $alsoknow[ $i ]['title'] ) . '</strong> (' . sanitize_text_field( $alsoknow[ $i ]['country'] );

			if ( strlen( $alsoknow[ $i ]['comment'] ) !== 0 ) {
				$output .= ' - <i>' . sanitize_text_field( $alsoknow[ $i ]['comment'] ) . '</i>';
			}

			$output .= ')';

			if ( ( $i < ( $nbtotalalsoknow - 1 ) ) && ( $i < ( $nbalsoknow - 1 ) ) ) {
				$output .= ', ';
			}

		} // endfor

		return $output;
	}

	/**
	 * Display the composers
	 *
	 * @param \Imdb\Title $movie -> takes the value of IMDbPHP class
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

			// No taxonomy
		} else {

			for ( $i = 0; $i < $nbtotalcomposer; $i++ ) {

				if ( $this->imdb_admin_values['imdblinkingkill'] === '0' ) { // if "Remove all links" option is not selected
					if ( $this->imdb_admin_values['imdbpopup_highslide'] === '1' ) { // highslide popup

						$output .= "\n\t\t\t" . '<a class="link-imdblt-highslidepeople highslide" data-highslidepeople="' . sanitize_text_field( $composer[ $i ]['imdb'] ) . '" title="' . esc_html__( 'Link to local IMDb', 'lumiere-movies' ) . '">' . sanitize_text_field( $composer[ $i ]['name'] ) . '</a>';

					} else {// classic popup

						$output .= "\n\t\t\t" . '<a class="link-imdblt-highslidepeople" data-classicpeople="' . sanitize_text_field( $composer[ $i ]['imdb'] ) . '" title="' . esc_html__( 'Link to local IMDb', 'lumiere-movies' ) . '">' . sanitize_text_field( $composer[ $i ]['name'] ) . '</a>';

					}

					// if "Remove all links" option is selected
				} else {

					$output .= sanitize_text_field( $composer[ $i ]['name'] );

				}

				if ( $i < $nbtotalcomposer - 1 ) {
					$output .= ', ';
				}

			} // endfor

		}

		return $output;

	}

	/**
	 * Display the soundtrack
	 *
	 * @param \Imdb\Title $movie -> takes the value of IMDbPHP class
	 */
	private function lumiere_movies_soundtrack( \Imdb\Title $movie ): string {

		$output = '';
		$soundtrack = $movie->soundtrack();
		$nbsoundtracks = intval( $this->imdb_widget_values['imdbwidgetsoundtracknumber'] ) === 0 || $this->imdb_widget_values['imdbwidgetsoundtracknumber'] === false ? '1' : intval( $this->imdb_widget_values['imdbwidgetsoundtracknumber'] );
		$nbtotalsountracks = count( $soundtrack );

		// if no results, exit.
		if ( $nbtotalsountracks === 0 ) {
			return $output;
		}

		$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
		$output .= sprintf( esc_attr( _n( 'Soundtrack', 'Soundtracks', $nbtotalsountracks, 'lumiere-movies' ) ), number_format_i18n( $nbtotalsountracks ) );
		$output .= ':</span>';

		for ( $i = 0; $i < $nbsoundtracks && ( $i < $nbtotalsountracks ); $i++ ) {

			$output .= "\n\t\t\t<strong>" . $soundtrack[ $i ]['soundtrack'] . '</strong>';

			// if "Remove all links" option is not selected
			if ( $this->imdb_admin_values['imdblinkingkill'] === '0' ) {

				if ( ( isset( $soundtrack[ $i ]['credits'][0] ) ) && ( count( $soundtrack[ $i ]['credits'][0] ) !== 0 ) ) {
					$output .= "\n\t\t\t - <i>" . $this->lumiere_convert_txtwithhtml_into_popup_people( $soundtrack[ $i ]['credits'][0]['credit_to'] ) . '</i> ';
				}

				$output .= ' (' . $this->lumiere_convert_txtwithhtml_into_popup_people( $soundtrack[ $i ]['credits'][0]['desc'] ) . ') ';

				if ( ( isset( $soundtrack[ $i ]['credits'][1] ) ) && ( count( $soundtrack[ $i ]['credits'][1] ) !== 0 ) ) {
					if ( ( isset( $soundtrack[ $i ]['credits'][1]['credit_to'] ) ) && ( strlen( $soundtrack[ $i ]['credits'][1]['credit_to'] ) !== 0 ) ) {
						$output .= "\n\t\t\t - <i>" . $this->lumiere_convert_txtwithhtml_into_popup_people( $soundtrack[ $i ]['credits'][1]['credit_to'] ) . '</i> ';
					}
				}

				if ( ( isset( $soundtrack[ $i ]['credits'][1]['desc'] ) ) && ( strlen( $soundtrack[ $i ]['credits'][1]['desc'] ) !== 0 ) ) {
					$output .= ' (' . $this->lumiere_convert_txtwithhtml_into_popup_people( $soundtrack[ $i ]['credits'][1]['desc'] ) . ') ';
				}

			} else {

				if ( ( isset( $soundtrack[ $i ]['credits'][0] ) ) && ( count( $soundtrack[ $i ]['credits'][0] ) !== 0 ) ) {
					$output .= "\n\t\t\t - <i>" . $this->lumiere_remove_link( $soundtrack[ $i ]['credits'][0]['credit_to'] ) . '</i> ';
				}

				$output .= ' (' . $this->lumiere_remove_link( $soundtrack[ $i ]['credits'][0]['desc'] ) . ') ';

				if ( count( $soundtrack[ $i ]['credits'][1] ) !== 0 ) {

					$output .= "\n\t\t\t - <i>" . $this->lumiere_remove_link( $soundtrack[ $i ]['credits'][1]['credit_to'] ) . '</i> ';
				}
					$output .= ' (' . $this->lumiere_remove_link( $soundtrack[ $i ]['credits'][1]['desc'] ) . ') ';
			} // end if remove popup

		}

		return $output;

	}

	/**
	 * Display the production companies
	 *
	 * @param \Imdb\Title $movie -> takes the value of IMDbPHP class
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

			if ( $this->imdb_admin_values['imdblinkingkill'] === '0' ) { // if "Remove all links" option is not selected.
				$output .= "\n\t\t\t\t" . '<div align="center" class="lumiere_container">';
				$output .= "\n\t\t\t\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';
				$output .= "<a href='" . esc_url( $prodcompany[ $i ]['url'] ) . "' title='" . esc_html( $prodcompany[ $i ]['name'] ) . "'>";
				$output .= esc_attr( $prodcompany[ $i ]['name'] );
				$output .= '</a>';
				$output .= '</div>';
				$output .= "\n\t\t\t\t\t" . '<div class="lumiere_align_right lumiere_flex_auto">';
				if ( strlen( $prodcompany[ $i ]['notes'] ) !== 0 ) {
					$output .= esc_attr( $prodcompany[ $i ]['notes'] );
				} else {
					$output .= '&nbsp;';
				}
				$output .= '</div>';
				$output .= "\n\t\t\t\t" . '</div>';

			} else { // if "Remove all links" option is selected

				$output .= esc_attr( $prodcompany[ $i ]['name'] ) . '<br />';

			}  // end if remove popup

		}  // endfor

		return $output;

	}

	/**
	 * Display the official site
	 *
	 * @param \Imdb\Title $movie -> takes the value of IMDbPHP class
	 */
	private function lumiere_movies_officialsite( \Imdb\Title $movie ): string {

		$output = '';
		$officialSites = $movie->officialSites();
		$nbtotalofficialSites = count( $officialSites );

		// if no result, exit.
		if ( $nbtotalofficialSites === 0 ) {
			return $output;
		}

		$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
		$output .= sprintf( esc_attr( _n( 'Official website', 'Official websites', $nbtotalofficialSites, 'lumiere-movies' ) ), number_format_i18n( $nbtotalofficialSites ) );
		$output .= ':</span>';

		for ( $i = 0; $i < $nbtotalofficialSites; $i++ ) {

			$output .= "\n\t\t\t<a href='" . esc_url( $officialSites[ $i ]['url'] ) . "' title='" . esc_html( $officialSites[ $i ]['name'] ) . "'>";
			$output .= sanitize_text_field( $officialSites[ $i ]['name'] );
			$output .= '</a>';
			if ( $i < $nbtotalofficialSites - 1 ) {
				$output .= ', ';
			}

		}

		return $output;
	}

	/**
	 * Display the director
	 *
	 * @param \Imdb\Title $movie -> takes the value of IMDbPHP class
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

		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_widget_values['imdbtaxonomydirector'] === '1' )  ) {

			for ( $i = 0; $i < $nbtotaldirector; $i++ ) {

				$output .= $this->lumiere_make_display_taxonomy( 'director', esc_attr( $director[ $i ]['name'] ), '', 'one' );
				if ( $i < $nbtotaldirector - 1 ) {
					$output .= ', ';
				}

			}

		} else {

			for ( $i = 0; $i < $nbtotaldirector; $i++ ) {

				if ( $this->imdb_admin_values['imdblinkingkill'] === '0' ) { // if "Remove all links" option is not selected.
					if ( $this->imdb_admin_values['imdbpopup_highslide'] === '1' ) { // highslide popup.

						$output .= "\n\t\t\t\t" . '<a class="linkincmovie link-imdblt-highslidepeople highslide" data-highslidepeople="' . esc_attr( $director[ $i ]['imdb'] ) . '" title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '">' . esc_attr( $director[ $i ]['name'] ) . '</a>';

						// classic popup
					} else {

						$output .= "\n\t\t\t\t" . '<a class="linkincmovie link-imdblt-classicpeople highslide" data-classicpeople="' . $director[ $i ]['imdb'] . '" title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '">' . $director[ $i ]['name'] . '</a>';
					}

				} else { // if "Remove all links" option is selected

					$output .= esc_attr( $director[ $i ]['name'] );

				}  // end if remove popup

				if ( $i < $nbtotaldirector - 1 ) {
					$output .= ', ';
				}

			} // endfor

		}

		return $output;

	}

	/**
	 * Display the creator (for series only)
	 *
	 * @param \Imdb\Title $movie -> takes the value of IMDbPHP class
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

		if ( ( $this->imdb_admin_values['imdbtaxonomy'] == true ) && ( $this->imdb_widget_values['imdbtaxonomycreator'] == true ) ) {

			for ( $i = 0; $i < $nbtotalcreator; $i++ ) {

				$output .= $this->lumiere_make_display_taxonomy( 'creator', esc_attr( $creator[ $i ]['name'] ), '', 'one' );
				if ( $i < $nbtotalcreator - 1 ) {
					$output .= ', ';
				}

			}

		} else {

			for ( $i = 0; $i < $nbtotalcreator; $i++ ) {

				// if "Remove all links" option is not selected
				if ( $this->imdb_admin_values['imdblinkingkill'] === '0' ) {

					// highslide popup
					if ( $this->imdb_admin_values['imdbpopup_highslide'] === '1' ) {
						$output .= '<a class="linkincmovie link-imdblt-highslidepeople highslide" data-highslidepeople="' . esc_attr( $creator[ $i ]['imdb'] ) . '" title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '">' . esc_attr( $creator[ $i ]['name'] ) . '</a>';

						// classic popup
					} else {

						$output .= '<a class="linkincmovie link-imdblt-classicpeople" data-classicpeople="' . esc_attr( $creator[ $i ]['imdb'] ) . '" title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '">' . esc_attr( $creator[ $i ]['name'] ) . '</a>';

					}

					if ( $i < $nbtotalcreator - 1 ) {
						$output .= ', ';
					}

					// if "Remove all links" option is selected
				} else {

					$output .= sanitize_text_field( $creator[ $i ]['name'] );
					if ( $i < $nbtotalcreator - 1 ) {
						$output .= ', ';
					}
				}
			}

		}

		return $output;

	}

	/**
	 * Display the producer
	 *
	 * @param \Imdb\Title $movie -> takes the value of IMDbPHP class
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

			// no taxonomy
		} else {

			for ( $i = 0; ( $i < $nbtotalproducer ) && ( $i < $nbproducer ); $i++ ) {

				$output .= "\n\t\t\t\t" . '<div align="center" class="lumiere_container">';
				$output .= "\n\t\t\t\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';

				// if "Remove all links" option is not selected
				if ( $this->imdb_admin_values['imdblinkingkill'] === '0' ) {

					// highslide popup
					if ( $this->imdb_admin_values['imdbpopup_highslide'] === '1' ) {

						$output .= "\n\t\t\t\t\t" . '<a class="linkincmovie link-imdblt-highslidepeople highslide" data-highslidepeople="' . esc_attr( $producer[ $i ]['imdb'] ) . '" title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '">' . esc_attr( $producer[ $i ]['name'] ) . '</a>';

					} else {  // classic popup

						$output .= "\n\t\t\t\t\t" . '<a class="linkincmovie link-imdblt-classicpeople highslide" data-classicpeople="' . esc_attr( $producer[ $i ]['imdb'] ) . '" title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '">' . esc_attr( $producer[ $i ]['name'] ) . '</a>';

					}

					// if "Remove all links" option is selected
				} else {

					$output .= esc_attr( $producer[ $i ]['name'] );

				}
				$output .= "\n\t\t\t\t\t" . '</div>';
				$output .= "\n\t\t\t\t\t" . '<div align="right">';

				if ( $producer[ $i ]['role'] !== null && strlen( $producer[ $i ]['role'] ) !== 0 ) {
					$output .= esc_attr( $producer[ $i ]['role'] );
				} else {
					$output .= '&nbsp;';
				}

				$output .= "\n\t\t\t\t" . '</div>';
				$output .= "\n\t\t\t" . '</div>';

			} // endfor

		}

		return $output;

	}

	/**
	 * Display the writer
	 *
	 * @param \Imdb\Title $movie -> takes the value of IMDbPHP class
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

		} else {

			for ( $i = 0; $i < $nbtotalwriters; $i++ ) {

				$output .= "\n\t\t\t" . '<div align="center" class="lumiere_container">';
				$output .= "\n\t\t\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';

				// if "Remove all links" option is not selected
				if ( $this->imdb_admin_values['imdblinkingkill'] === '0' ) {

					// highslide popup
					if ( $this->imdb_admin_values['imdbpopup_highslide'] === '1' ) {

						$output .= '<a class="linkincmovie link-imdblt-highslidepeople highslide" data-highslidepeople="' . esc_attr( $writer[ $i ]['imdb'] ) . '" title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '">' . sanitize_text_field( $writer[ $i ]['name'] ) . '</a>';

						// classic popup
					} else {

						$output .= '<a class="linkincmovie link-imdblt-classicpeople highslide" data-classicpeople="' . esc_attr( $writer[ $i ]['imdb'] ) . '" title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '">' . sanitize_text_field( $writer[ $i ]['name'] ) . '</a>';

					}

					// if "Remove all links" option is selected
				} else {

					$output .= sanitize_text_field( $writer[ $i ]['name'] );

				}
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

		}

		return $output;
	}

	/**
	 * Display actors
	 *
	 * @param \Imdb\Title $movie -> takes the value of IMDbPHP class
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

		} else {

			for ( $i = 0; $i < $nbactors && ( $i < $nbtotalactors ); $i++ ) {

				$output .= "\n\t\t\t\t" . '<div align="center" class="lumiere_container">';
				$output .= "\n\t\t\t\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';

				// if "Remove all links" option is not selected
				if ( $this->imdb_admin_values['imdblinkingkill'] === '0' ) {

					// highslide popup
					if ( $this->imdb_admin_values['imdbpopup_highslide'] === '1' ) {

						$output .= "\n\t\t\t\t\t" . '<a class="linkincmovie link-imdblt-highslidepeople highslide" data-highslidepeople="' . esc_attr( $cast[ $i ]['imdb'] ) . '" title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '">' . esc_attr( $cast[ $i ]['name'] ) . '</a>';

						// classic popup
					} else {

						$output .= "\n\t\t\t\t\t" . '<a class="linkincmovie link-imdblt-classicpeople" data-classicpeople="' . esc_attr( $cast[ $i ]['imdb'] ) . '" title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '">' . esc_attr( $cast[ $i ]['name'] ) . '</a>';

					}

				} else { // if "Remove all links" option is selected

					$output .= esc_attr( $cast[ $i ]['name'] );

				}

				$output .= '</div>';
				$output .= "\n\t\t\t\t\t" . '<div class="lumiere_align_right lumiere_flex_auto">';
				$output .= esc_attr( preg_replace( '/\n/', '', $cast[ $i ]['role'] ) ); # remove the <br> which break the layout
				$output .= '</div>';
				$output .= "\n\t\t\t\t" . '</div>';

			} // endfor

		}

		return $output;
	}

	/**
	 * Display plots
	 *
	 * @param \Imdb\Title $movie -> takes the value of IMDbPHP class
	 */
	private function lumiere_movies_plot( \Imdb\Title $movie ): string {

		$output = '';
		$plot = $movie->plot();
		$nbplots = intval( $this->imdb_widget_values['imdbwidgetplotnumber'] ) === 0 ? '1' : intval( $this->imdb_widget_values['imdbwidgetplotnumber'] );
		$nbtotalplots = count( $plot );

		// tested if the array contains data; if not, doesn't go further
		if ( ! Utils::lumiere_is_multi_array_empty( $plot ) ) {

			$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
			$output .= sprintf( esc_attr( _n( 'Plot', 'Plots', $nbtotalplots, 'lumiere-movies' ) ), number_format_i18n( $nbtotalplots ) );
			$output .= ':</span><br />';

			for ( $i = 0; ( ( $i < $nbtotalplots ) && ( $i < $nbplots ) ); $i++ ) {

				// if "Remove all links" option is not selected
				if ( $this->imdb_admin_values['imdblinkingkill'] === '1' ) {

					$output .= $this->lumiere_remove_link( $plot[ $i ] ) . "\n";
				} else {

					$output .= $plot[ $i ] . "\n";

				}

				if ( $i < ( ( $i < ( $nbtotalplots - 1 ) ) && ( $i < ( $nbplots - 1 ) ) ) ) {
					$output .= "\n<hr>\n";
				} // add hr to every quote but the last
			}

		}

		return $output;
	}

	/**
	 * Display the credit link
	 *
	 * @param string $midPremierResultat -> IMDb ID, not as int since it loses its leading 0
	 */
	private function lumiere_movies_creditlink( string $midPremierResultat ): string {

		$output = '';
		$midPremierResultat_sanitized = filter_var( $midPremierResultat, FILTER_SANITIZE_NUMBER_INT );

		// if "Remove all links" option is not selected
		if ( $this->imdb_admin_values['imdblinkingkill'] === '0' ) {

			$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
			$output .= esc_html__( 'Source', 'lumiere-movies' );
			$output .= ':</span>';

			$output .= "\n\t\t\t\t" . '<img class="imdbelementSOURCE-picture" width="33" height="15" src="' . esc_url( $this->imdb_admin_values['imdbplugindirectory'] . 'pics/imdb-link.png' ) . '" />';
			$output .= '<a class="link-incmovie-sourceimdb" title="'
					. esc_html__( 'Go to IMDb website for this movie', 'lumiere-movies' ) . '" href="'
					. esc_url( 'https://www.imdb.com/title/tt' . $midPremierResultat_sanitized ) . '" >'
					. '&nbsp;&nbsp;'
					. esc_html__( "IMDb's page for this movie", 'lumiere-movies' ) . '</a>';

		}

		return $output;
	}

	/**
	 * Do taxonomy layouts and register taxonomy terms
	 *
	 * @param string $typeItem the general category of the item, ie 'director', 'color'
	 * @param string $firstTitle the name of the first string to display, ie "Stanley Kubrick"
	 * @param string $secondTitle the name of a second string to display, utilised in $layout 'two', ie "director"
	 * @param string $layout the type of the layout, either 'one' or 'two'
	 *
	 * @return string the text to be outputed
	 */
	private function lumiere_make_display_taxonomy( string $typeItem, string $firstTitle, string $secondTitle = null, string $layout = 'one' ) {

		// ************** Vars and sanitization */
		$lang_term = 'en'; # language to register the term with, English by default
		$output = '';
		$list_taxonomy_term = '';
		$layout = esc_attr( $layout );
		$taxonomy_category = esc_attr( $typeItem );
		$taxonomy_term = esc_attr( $firstTitle );
		$secondTitle = $secondTitle !== null ? esc_attr( $secondTitle ) : '';
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
				if ( $term === null || $term === 0 ) {

					// insert it and get its id
					// $term = wp_insert_term($taxonomy_term, $taxonomy_category_full, array('lang' => $lang_term) );
					// I believe adding the above option 'lang' is useless, inserting without 'lang'.
					$term = wp_insert_term( $taxonomy_term, $taxonomy_category_full );
					$this->logger->log()->debug( "[Lumiere][movieClass] Taxonomy term $taxonomy_term added to $taxonomy_category_full" );
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

			// Compatibility with Polylang WordPress plugin, add a language to the taxonomy term
			if ( function_exists( 'pll_set_term_language' ) ) {

				// Get the language of the term already registred.
				$term_registred_lang = pll_get_term_language( intval( $term['term_id'] ), 'slug' );
				// Get the language of the page.
				$lang = filter_var( pll_current_language( 'slug' ), FILTER_SANITIZE_STRING ) !== false ? filter_var( pll_current_language( 'slug' ), FILTER_SANITIZE_STRING ) : '';

				// If the language for this term is not already registered, register it.
				// Check current page language, compare against already registred term.
				if ( $term_registred_lang !== $lang ) {

					$this->lumiere_add_taxo_lang_to_polylang( intval( $term['term_id'] ), $lang );

				}

			}

		}

		// ************** Return layout

		// layout=two: display the layout for double entry details, ie actors
		if ( $layout == 'two' ) {

			$output .= "\n\t\t\t" . '<div align="center" class="lumiere_container">';
			$output .= "\n\t\t\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';
			$output .= "\n\t\t\t\t\t<a class=\"linkincmovie\" href=\""
					. esc_url(
						site_url() . '/' . $taxonomy_category_full
						. '/' . $this->lumiere_make_taxonomy_link( $taxonomy_term )
					)
					. '" title="' . esc_html__( 'Find similar taxonomy results', 'lumiere-movies' )
					. '">';
			$output .= "\n\t\t\t\t\t" . $taxonomy_term;
			$output .= "\n\t\t\t\t\t" . '</a>';
			$output .= "\n\t\t\t\t" . '</div>';
			$output .= "\n\t\t\t\t" . '<div class="lumiere_align_right lumiere_flex_auto">';
			$output .= preg_replace( '/\n/', '', esc_attr( $secondTitle ) ); # remove breaking space
			$output .= "\n\t\t\t\t" . '</div>';
			$output .= "\n\t\t\t" . '</div>';

			// layout=one: display the layout for all details separated by comas, ie keywords
		} elseif ( $layout == 'one' ) {

			$output .= '<a class="linkincmovie" '
					. 'href="' . site_url() . '/'
					. $taxonomy_category_full . '/'
					. $this->lumiere_make_taxonomy_link( $taxonomy_term ) . '" '
					. 'title="' . esc_html__( 'Find similar taxonomy results', 'lumiere-movies' ) . '">';
			$output .= $taxonomy_term;
			$output .= '</a>';

		}

		return $output;

	}

	/**
	 * Convert an imdb link to a highslide/classic popup link
	 *
	 * @param string $convert Link to be converted into popup highslide link
	 */

	private function lumiere_convert_txtwithhtml_into_popup_people ( string $convert ): string {

		// highslide popup.
		if ( $this->imdb_admin_values['imdbpopup_highslide'] === '1' ) {
			$result = '<a class="link-imdblt-highslidepeople highslide" data-highslidepeople="${4}" title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '">';

			// classic popup.
		} else {
			$result = '<a class="link-imdblt-classicpeople" data-classicpeople="${4}" title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '">';
		}

		$convert = preg_replace( '~(<a href=)(.+?)(name\/nm)(\d{7})\/\"\>~', $result, $convert ) ?? $convert;

		return $convert;
	}

	/**
	 * Avoid that the same movie to be displayed twice or more
	 * allows movie total count (how many time a movie is called by the plugin)
	 *
	 */
	private function lumiere_filter_single_movies( string $midPremierResultat, int &$lumiere_count_me_siffer ): bool {

		global $lumiere_count_me_siffer, $lumiere_counter;

		$lumiere_count_me_siffer++;
		$lumiere_counter[ $lumiere_count_me_siffer ] = $midPremierResultat;
		$ici = array_count_values( $lumiere_counter );

		if ( $ici[ $midPremierResultat ] < 2 ) {
			return false;
		}

		return true;

	}

	/**
	 * Remove html link <a>
	 *
	 * @param string $text text to be cleaned from every html link
	 */
	private function lumiere_remove_link ( string $text ): string {

		$output = preg_replace( '/<a(.*?)>/', '', $text ) ?? $text;

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

	/* Polylang WordPress Plugin Compatibility
	 * Add a language to the taxonomy term in Polylang
	 *
	 * @param int $term_id -> id of the taxonomy term, usually got after taxonomy term insert
	 * @param string $lang -> language of the taxonomy term utilised by Polylang
	 */
	private function lumiere_add_taxo_lang_to_polylang( int $term_id, string $lang ): void {

		//      if ( pll_default_language() == $lang )
		//          pll_save_term_translations( array ( $lang, $term_id) );

		pll_set_term_language( $term_id, $lang );
		$this->logger->log()->debug( '[Lumiere][movieClass][polylang] Taxonomy id ' . $term_id . ' added to ' . $lang );
	}

} // end of class


/* Auto load the class
 * Conditions: not admin area
 * @TODO: Pass this into core class
 */
if ( ! is_admin() ) {

	new Movie();

}

