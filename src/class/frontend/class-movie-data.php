<?php declare( strict_types = 1 );
/**
 * Class for displaying movies data.
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2024, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */

namespace Lumiere\Frontend;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Imdb\Title;

/**
 * Those methods are utilised by class Movie to display the sections
 * The class uses \Lumiere\Link_Makers\Link_Factory to automatically select the appropriate Link maker class to display data ( i.e. Classic links, Highslide/Bootstrap, No Links, AMP)
 * It uses ImdbPHP Classes to display movies/people data
 *
 * @since 4.0 new class, methods were extracted from Movie class
 */
class Movie_Data extends Movie {

	/**
	 * Class constructor
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Display the title and possibly the year
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lum_movies_title( Title $movie ): string {

		$year = strlen( strval( $movie->year() ) ) !== 0 ? strval( $movie->year() ) : null;
		$title_sanitized = esc_html( $movie->title() );

		$output = "\n\t\t\t<span id=\"title_$title_sanitized\">" . $title_sanitized;

		if ( $year !== null && $this->imdb_data_values['imdbwidgetyear'] === '1' ) {
			$output .= ' (' . esc_html( $year ) . ')';
		}

		$output .= '</span>';

		return $output;
	}

	/**
	 * Display the picture
	 *
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @since 3.7 improved compatibility with AMP WP plugin in relevant class
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lum_movies_pic( Title $movie ): string {

		/**
		 * Use links builder classes.
		 * Each one has its own class passed in $link_maker,
		 * according to which option the lumiere_select_link_maker() found in Frontend.
		 */
		// If cache is active, use the pictures from IMDBphp class.
		if ( $this->imdb_cache_values['imdbusecache'] === '1' ) {
			return $this->link_maker->lumiere_link_picture( $movie->photoLocalurl( false ), $movie->photoLocalurl( true ), $movie->title() );
		}

		// If cache is deactived, display no_pics.gif
		$no_pic_url = $this->config_class->lumiere_pics_dir . 'no_pics.gif';
		return $this->link_maker->lumiere_link_picture( $no_pic_url, $no_pic_url, $movie->title() );
	}

	/**
	 * Display the country of origin
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lum_movies_country( Title $movie ): string {

		$country = $movie->country();
		$nbtotalcountry = count( $country );

		// if no result, exit.
		if ( $nbtotalcountry === 0 ) {
			return '';
		}

		$output = "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
		$output .= sprintf( esc_html( _n( 'Country', 'Countries', $nbtotalcountry, 'lumiere-movies' ) ), number_format_i18n( $nbtotalcountry ) );
		$output .= ':</span>';

		// Taxonomy is active.
		if ( $this->imdb_admin_values['imdbtaxonomy'] === '1' && $this->imdb_data_values['imdbtaxonomycountry'] === '1' ) {

			for ( $i = 0; $i < $nbtotalcountry; $i++ ) {

				$output .= $this->lumiere_make_display_taxonomy( 'country', esc_attr( $country[ $i ] ), '', 'one', sanitize_text_field( $movie->title() ) );
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
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lum_movies_runtime( Title $movie ): string {

		$runtime_sanitized = isset( $movie->runtime()[0]['time'] ) ? esc_html( strval( $movie->runtime()[0]['time'] ) ) : '';

		if ( strlen( $runtime_sanitized ) === 0 ) {
			return '';
		}

		$output = "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
		$output .= esc_html__( 'Runtime', 'lumiere-movies' );
		$output .= ':</span>';
		$output .= $runtime_sanitized . ' ' . esc_html__( 'minutes', 'lumiere-movies' );

		return $output;
	}

	/**
	 * Display the language
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lum_movies_language( Title $movie ): string {

		// @var array<int, string> $languages
		$languages = $movie->language();
		$nbtotallanguages = count( $languages );

		if ( $nbtotallanguages === 0 ) {
			return '';
		}

		$output = "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
		$output .= sprintf( esc_html( _n( 'Language', 'Languages', $nbtotallanguages, 'lumiere-movies' ) ), number_format_i18n( $nbtotallanguages ) );
		$output .= ':</span>';

		// Taxonomy is active.
		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_data_values['imdbtaxonomylanguage'] === '1' ) ) {

			for ( $i = 0; $i < $nbtotallanguages; $i++ ) {

				$output .= $this->lumiere_make_display_taxonomy( 'language', esc_attr( $languages[ $i ] ), '', 'one', sanitize_text_field( $movie->title() ) );
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
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lum_movies_rating( Title $movie ): string {

		$votes_sanitized = intval( $movie->votes() );
		$rating_sanitized = intval( $movie->rating() );

		if ( $votes_sanitized === 0 ) {
			return '';
		}

		/**
		 * Use links builder classes.
		 * Each one has its own class passed in $link_maker,
		 * according to which option the lumiere_select_link_maker() found in Frontend.
		 */
		return $this->link_maker->lumiere_movies_rating_picture(
			$rating_sanitized,
			$votes_sanitized,
			esc_html__( 'vote average', 'lumiere-movies' ),
			esc_html__( 'out of 10', 'lumiere-movies' ),
			esc_html__( 'votes', 'lumiere-movies' )
		);
	}

	/**
	 * Display the genre
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lum_movies_genre( Title $movie ): string {

		$genre = $movie->genre();
		$nbtotalgenre = count( $genre ) > 0 ? count( $genre ) : 0;

		if ( $nbtotalgenre === 0 ) {
			return '';
		}

		$output = "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
		$output .= sprintf( esc_html( _n( 'Genre', 'Genres', $nbtotalgenre, 'lumiere-movies' ) ), number_format_i18n( $nbtotalgenre ) );

		$output .= ':</span>';

		// Taxonomy is active.
		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_data_values['imdbtaxonomygenre'] === '1' ) ) {
			for ( $i = 0; $i < $nbtotalgenre; $i++ ) {

				$output .= isset( $genre[ $i ]['mainGenre'] ) ? $this->lumiere_make_display_taxonomy( 'genre', sanitize_text_field( $genre[ $i ]['mainGenre'] ), '', 'one', sanitize_text_field( $movie->title() ) ) : '';
				if ( $i < $nbtotalgenre - 1 ) {
					$output .= ', ';
				}
			}
			return $output;
		}

		// No taxonomy
		for ( $i = 0; $i < $nbtotalgenre; $i++ ) {
			$output .= isset( $genre[ $i ]['mainGenre'] ) ? esc_html( $genre[ $i ]['mainGenre'] ) : '';
			if ( $i < $nbtotalgenre - 1 ) {
				$output .= ', ';
			}
		}

		return $output;
	}

	/**
	 * Display the keywords
	 * Using $limit_keywords var to limit the total (not selected in the plugin options, hardcoded here)
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lum_movies_keyword( Title $movie ): string {

		$keywords = $movie->keyword();
		$nbtotalkeywords = count( $keywords );
		$limit_keywords = 10;

		if ( $nbtotalkeywords === 0 ) {
			return '';
		}

		$output = "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
		$output .= sprintf( esc_html( _n( 'Keyword', 'Keywords', $nbtotalkeywords, 'lumiere-movies' ) ), number_format_i18n( $nbtotalkeywords ) );
		$output .= ':</span>';

		// Taxonomy is active.
		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_data_values['imdbtaxonomykeyword'] === '1' ) ) {

			for ( $i = 0; $i < $nbtotalkeywords && $i < $limit_keywords; $i++ ) {

				$output .= $this->lumiere_make_display_taxonomy( 'keyword', esc_attr( $keywords[ $i ] ), '', 'one', sanitize_text_field( $movie->title() ) );
				if ( $i < $nbtotalkeywords - 1 ) {
					$output .= ', ';
				}
			}
			return $output;
		}

		// Taxonomy is unactive.
		for ( $i = 0; $i < $nbtotalkeywords && $i < $limit_keywords; $i++ ) {

			$output .= esc_attr( $keywords[ $i ] );

			if ( $i < $nbtotalkeywords - 1 && $i < $limit_keywords - 1 ) {
				$output .= ', ';
			}
		}
		return $output;
	}

	/**
	 * Display the goofs
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lum_movies_goof( Title $movie ): string {

		$goofs = $movie->goof();
		$settings_nbgoofs = intval( $this->imdb_data_values['imdbwidgetgoofnumber'] ) === 0 || $this->imdb_data_values['imdbwidgetgoofnumber'] === false ? '1' : intval( $this->imdb_data_values['imdbwidgetgoofnumber'] );
		$filter_nbtotalgoofs = array_filter( $goofs, fn( $goofs ) => ( count( array_values( $goofs ) ) > 0 ) ); // counts the actual goofs, not their categories
		$nbtotalgoofs = count( $filter_nbtotalgoofs );
		$overall_loop = 1;

		// Build all types of goofs by making an array.
		$goofs_type = [];
		foreach ( $goofs as $type => $info ) {
			$goofs_type[] = $type;
		}

		// if no result, exit.
		if ( $nbtotalgoofs === 0 ) {
			return '';
		}

		$output = "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
		$output .= sprintf( esc_html( _n( 'Goof', 'Goofs', $nbtotalgoofs, 'lumiere-movies' ) ), number_format_i18n( $nbtotalgoofs ) );
		$output .= ':</span><br>';

		// Process goof type after goof type
		foreach ( $goofs_type as $type ) {
			// Loop conditions: less than the total number of goofs available AND less than the goof limit setting, using a loop counter.
			for ( $i = 0; ( $i < $nbtotalgoofs ) && ( $overall_loop <= $settings_nbgoofs ); $i++ ) {
				if ( isset( $goofs[ $type ][ $i ]['content'] ) && strlen( $goofs[ $type ][ $i ]['content'] ) > 0 ) {
					$text_final_edited = preg_replace( '/\B([A-Z])/', '&nbsp;$1', $type ); // type is agglutinated, add space before capital letters.
					/** @psalm-suppress PossiblyInvalidOperand,PossiblyInvalidArgument (PossiblyInvalidOperand: Cannot concatenate with a array<array-key, string>|string -- it's always string according to PHPStan) */
					$output .= isset( $text_final_edited ) ? "\n\t\t\t\t<strong>" . esc_html( strtolower( $text_final_edited ) ) . '</strong>&nbsp;' : '';
					$output .= esc_html( $goofs[ $type ][ $i ]['content'] ) . '<br>';
				}
				$overall_loop++; // this loop counts the exact goof number processed
			}
		}
		return $output;
	}

	/**
	 * Display the quotes
	 * Quotes are what People said, Quotes do not exists in Movie's pages, which do not display people's data
	 * Kept for compatibility purposes: the function lum_movies_quote() is automatically created from config data, the class would complain that method doesn't exist
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @since 4.0 Removed the method's content, since this function is for compatibility and does nothing
	 *
	 * @param Title $movie IMDbPHP title class
	 * @return string Nothing
	 */
	protected function lum_movies_quote( Title $movie ): string {
		return '';
	}

	/**
	 * Display the taglines
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lum_movies_tagline( Title $movie ): string {

		$taglines = $movie->tagline();
		$nbtaglines = intval( $this->imdb_data_values['imdbwidgettaglinenumber'] ) === 0 || $this->imdb_data_values['imdbwidgettaglinenumber'] === false ? '1' : intval( $this->imdb_data_values['imdbwidgettaglinenumber'] );
		$nbtotaltaglines = count( $taglines );

		// If no result, exit.
		if ( $nbtotaltaglines === 0 ) {
			return '';
		}

		$output = "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
		$output .= sprintf( esc_html( _n( 'Tagline', 'Taglines', $nbtotaltaglines, 'lumiere-movies' ) ), number_format_i18n( $nbtotaltaglines ) ) . ':';
		$output .= '</span>';

		for ( $i = 0; $i < $nbtaglines && ( $i < $nbtotaltaglines ); $i++ ) {

			$output .= "\n\t\t\t&laquo; " . sanitize_text_field( $taglines[ $i ] ) . ' &raquo; ';
			if ( $i < ( $nbtaglines - 1 ) && $i < ( $nbtotaltaglines - 1 ) ) {
				$output .= ', '; // add comma to every quote but the last.
			}
		}
		return $output;
	}

	/**
	 * Display the trailer
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lum_movies_trailer( Title $movie ): string {

		$trailers = $movie->video(); // Title::video() works faster than Title::trailer()
		$trailers = $trailers['Trailer'] ?? null; // Two rows available: Clip and Trailer
		$nbtrailers = intval( $this->imdb_data_values['imdbwidgettrailernumber'] ) === 0 || $this->imdb_data_values['imdbwidgettrailernumber'] === false ? '1' : intval( $this->imdb_data_values['imdbwidgettrailernumber'] );
		$nbtotaltrailers = isset( $trailers ) ? count( $trailers ) : null;

		// if no results, exit.
		if ( $nbtotaltrailers === 0 || $nbtotaltrailers === null ) {
			return '';
		}

		$output = "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
		$output .= sprintf( esc_html( _n( 'Trailer', 'Trailers', $nbtotaltrailers, 'lumiere-movies' ) ), number_format_i18n( $nbtotaltrailers ) );
		$output .= ':</span>';

		for ( $i = 0; ( $i < $nbtrailers && ( $i < $nbtotaltrailers ) ); $i++ ) {

			if ( ! isset( $trailers[ $i ]['playbackUrl'] ) ) {
				continue;
			}

			/**
			 * Use links builder classes.
			 * Each one has its own class passed in $link_maker,
			 * according to which option the lumiere_select_link_maker() found in Frontend.
			 */
			$output .= $this->link_maker->lumiere_movies_trailer_details( $trailers[ $i ]['playbackUrl'], $trailers[ $i ]['name'] );

			if ( $i < ( $nbtrailers - 1 ) && $i < ( $nbtotaltrailers - 1 ) ) {
				$output .= ', '; // add comma to every trailer but the last.
			}
		}
		return $output;
	}

	/**
	 * Display the color
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lum_movies_color( Title $movie ): string {

		$colors = $movie->color();
		$nbtotalcolors = count( $colors );

		// if no result, exit.
		if ( $nbtotalcolors === 0 ) {
			return '';
		}

		$output = "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
		$output .= sprintf( esc_html( _n( 'Colorisation', 'Colorisations', $nbtotalcolors, 'lumiere-movies' ) ), number_format_i18n( $nbtotalcolors ) );
		$output .= ':</span>';

		// Taxonomy activated.
		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_data_values['imdbtaxonomycolor'] === '1' ) ) {

			for ( $i = 0; $i < $nbtotalcolors; $i++ ) {

				$output .= $this->lumiere_make_display_taxonomy( 'coloration', esc_attr( $colors[ $i ]['type'] ), '', 'one', sanitize_text_field( $movie->title() ) );
				if ( $i < $nbtotalcolors - 1 ) {
					$output .= ', ';
				}
			}
			return $output;

		}

		// No taxonomy.
		$count_colors = count( $colors );
		for ( $i = 0; $i < $count_colors; $i++ ) {

			$output .= "\n\t\t\t" . sanitize_text_field( $colors[ $i ]['type'] );
			if ( $i < $nbtotalcolors - 1 ) {
				$output .= ', ';
			}
		}
		return $output;
	}

	/**
	 * Display the as known as, aka
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lum_movies_alsoknow( Title $movie ): string {

		$alsoknow = $movie->alsoknow();
		$nbalsoknow = intval( $this->imdb_data_values['imdbwidgetalsoknownumber'] ) === 0 || $this->imdb_data_values['imdbwidgetalsoknownumber'] === false ? '1' : intval( $this->imdb_data_values['imdbwidgetalsoknownumber'] ) + 1; // Adding 1 since first array line is the title
		$nbtotalalsoknow = count( $alsoknow );

		// if no result, exit.
		if ( $nbtotalalsoknow === 0 ) {
			return '';
		}

		$output = "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
		$output .= esc_html__( 'Also known as', 'lumiere-movies' );
		$output .= ':</span>';

		for ( $i = 0; ( $i < $nbtotalalsoknow ) && ( $i < $nbalsoknow ); $i++ ) {

			// Title line, not returning it.
			if ( $i === 0 ) {
				continue;
			}
			$output .= "\n\t\t\t<i>" . sanitize_text_field( $alsoknow[ $i ]['title'] ) . '</i>';

			if ( isset( $alsoknow[ $i ]['countryId'] ) ) {
				$output .= ' (';
				$output .= sanitize_text_field( $alsoknow[ $i ]['country'] );
				if ( isset( $alsoknow[ $i ]['comment'][0] ) ) {
					$output .= ' - ';
					$output .= sanitize_text_field( $alsoknow[ $i ]['comment'][0] );
				}
				$output .= ')';
			}

			if ( $i < ( $nbtotalalsoknow - 1 ) && $i < ( $nbalsoknow - 1 ) ) {
				$output .= ', ';
			}
		}
		return $output;
	}

	/**
	 * Display the composers
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lum_movies_composer( Title $movie ): string {

		$composer = $movie->composer();
		$nbtotalcomposer = count( $composer );

		// if no results, exit.
		if ( $nbtotalcomposer === 0 ) {
			return '';
		}

		$output = "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
		$output .= sprintf( esc_html( _n( 'Composer', 'Composers', $nbtotalcomposer, 'lumiere-movies' ) ), number_format_i18n( $nbtotalcomposer ) );
		$output .= ':</span>';

		// Taxonomy
		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_data_values['imdbtaxonomycomposer'] === '1' ) ) {

			for ( $i = 0; $i < $nbtotalcomposer; $i++ ) {

				$output .= $this->lumiere_make_display_taxonomy( 'composer', esc_attr( $composer[ $i ]['name'] ), '', 'one', sanitize_text_field( $movie->title() ) );
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
		}
		return $output;
	}

	/**
	 * Display the soundtrack
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lum_movies_soundtrack( Title $movie ): string {

		$soundtrack = $movie->soundtrack();
		$nbsoundtracks = intval( $this->imdb_data_values['imdbwidgetsoundtracknumber'] ) === 0 || $this->imdb_data_values['imdbwidgetsoundtracknumber'] === false ? '1' : intval( $this->imdb_data_values['imdbwidgetsoundtracknumber'] );
		$nbtotalsoundtracks = count( $soundtrack );

		// if no results, exit.
		if ( $nbtotalsoundtracks === 0 ) {
			return '';
		}

		$output = "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
		$output .= sprintf( esc_html( _n( 'Soundtrack', 'Soundtracks', $nbtotalsoundtracks, 'lumiere-movies' ) ), number_format_i18n( $nbtotalsoundtracks ) );
		$output .= ':</span>';

		for ( $i = 0; $i < $nbsoundtracks && ( $i < $nbtotalsoundtracks ); $i++ ) {
			$soundtrack_name = "\n\t\t\t" . ucfirst( strtolower( $soundtrack[ $i ]['soundtrack'] ) );

			$output .= "\n\t\t\t" .
				/**
				 * Use links builder classes.
				 * Each one has its own class passed in $link_maker,
				 * according to which option the lumiere_select_link_maker() found in Frontend.
				 */
				$this->link_maker->lumiere_imdburl_of_soundtrack( sanitize_text_field( $soundtrack_name ) )
			. ' ';

			$output .= isset( $soundtrack[ $i ]['credits'][0] ) ? ' <i>' . $soundtrack[ $i ]['credits'][0] . '</i>' : '';
			$output .= isset( $soundtrack[ $i ]['credits'][1] ) ? ' <i>' . $soundtrack[ $i ]['credits'][1] . '</i>' : '';

			if ( $i < ( $nbsoundtracks - 1 ) && $i < ( $nbtotalsoundtracks - 1 ) ) {
				$output .= ', ';
			}
		}
		return $output;
	}

	/**
	 * Display the production companies
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lum_movies_prodcompany( Title $movie ): string {

		$prodcompany = $movie->prodCompany();
		$nbtotalprodcompany = count( $prodcompany );

		// if no result, exit.
		if ( $nbtotalprodcompany === 0 ) {
			return '';
		}

		$output = "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
		$output .= sprintf( esc_html( _n( 'Production company', 'Production companies', $nbtotalprodcompany, 'lumiere-movies' ) ), number_format_i18n( $nbtotalprodcompany ) );
		$output .= ':</span>';

		for ( $i = 0; $i < $nbtotalprodcompany; $i++ ) {
			$comment = isset( $prodcompany[ $i ]['attribute'][0] ) ? '"' . $prodcompany[ $i ]['attribute'][0] . '"' : '';
			/**
			 * Use links builder classes.
			 * Each one has its own class passed in $link_maker,
			 * according to which option the lumiere_select_link_maker() found in Frontend.
			 */
			$output .= $this->link_maker->lumiere_movies_prodcompany_details(
				$prodcompany[ $i ]['name'],
				$prodcompany[ $i ]['id'],
				$comment,
			);
		}
		return $output;
	}

	/**
	 * Display the official site
	 * @since 4.3 using extSites from new IMDBphpGraphQL, but kept ol official sites names
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lum_movies_officialsites( Title $movie ): string {

		$get_external_sites = $movie->extSites();
		$external_sites = $get_external_sites['official'] ?? $get_external_sites['misc'] ?? [];
		$nbtotalext_sites = count( $external_sites );

		// if no result, exit.
		if ( count( $external_sites ) === 0 ) {
			return '';
		}

		$output = "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
		$output .= esc_html( _n( 'Official website', 'Official websites', $nbtotalext_sites, 'lumiere-movies' ) );
		$output .= ':</span>';

		// Hardcoded 7 sites max.
		for ( $i = 0; $i < $nbtotalext_sites && $i < 7; $i++  ) {

			/**
			 * Use links builder classes.
			 * Each one has its own class passed in $link_maker,
			 * according to which option the lumiere_select_link_maker() found in Frontend.
			 */
			$output .= $this->link_maker->lumiere_movies_officialsites_details(
				$external_sites[ $i ]['url'],
				$external_sites[ $i ]['label']
			);

			if ( $i < ( $nbtotalext_sites - 1 ) && $i < 6 ) {
				$output .= ', ';
			}

		}
		return $output;
	}

	/**
	 * Display the director
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lum_movies_director( Title $movie ): string {

		$director = $movie->director();
		$nbtotaldirector = count( $director );

		// if no result, exit.
		if ( $nbtotaldirector === 0 ) {
			return '';
		}

		$output = "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
		$output .= sprintf( esc_html( _n( 'Director', 'Directors', $nbtotaldirector, 'lumiere-movies' ) ), number_format_i18n( $nbtotaldirector ) );
		$output .= ':</span>';

		// If Taxonomy is selected, build links to taxonomy pages
		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_data_values['imdbtaxonomydirector'] === '1' )  ) {

			for ( $i = 0; $i < $nbtotaldirector; $i++ ) {

				$output .= $this->lumiere_make_display_taxonomy(
					'director',
					esc_html( $director[ $i ]['name'] ),
					'',
					'one',
					sanitize_text_field( $movie->title() )
				);

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
		}

		return $output;

	}

	/**
	 * Display the cinemoatographer (directeur photo)
	 * For historical reasons, imdb config has "creator", so the method's name is based on the word
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lum_movies_creator( Title $movie ): string {

		$cinematographer = $movie->cinematographer();
		$nbtotalcinematographer = count( $cinematographer );

		// if no results, exit.
		if ( $nbtotalcinematographer === 0 ) {
			return '';
		}

		$output = "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
		$output .= sprintf( esc_html( _n( 'Cinematographer', 'Cinematographers', $nbtotalcinematographer, 'lumiere-movies' ) ), number_format_i18n( $nbtotalcinematographer ) );
		$output .= ':</span>&nbsp;';

		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_data_values['imdbtaxonomycreator'] === '1' ) ) {

			for ( $i = 0; $i < $nbtotalcinematographer; $i++ ) {

				$output .= $this->lumiere_make_display_taxonomy(
					'cinematographer',
					esc_attr( $cinematographer[ $i ]['name'] ),
					'',
					'one',
					sanitize_text_field( $movie->title() )
				);

				if ( $i < $nbtotalcinematographer - 1 ) {
					$output .= ', ';
				}

			}

			return $output;
		}

		for ( $i = 0; $i < $nbtotalcinematographer; $i++ ) {

			/**
			 * Use links builder classes.
			 * Each one has its own class passed in $link_maker,
			 * according to which option the lumiere_select_link_maker() found in Frontend.
			 */
			$output .= $this->link_maker->lumiere_link_popup_people( $cinematographer, $i );

			if ( $i < $nbtotalcinematographer - 1 ) {
				$output .= ', ';
			}

		}
		return $output;
	}

	/**
	 * Display the producer
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lum_movies_producer( Title $movie ): string {

		$producer = $movie->producer();
		$nbproducer = intval( $this->imdb_data_values['imdbwidgetproducernumber'] ) === 0 || $this->imdb_data_values['imdbwidgetproducernumber'] === false ? '1' : intval( $this->imdb_data_values['imdbwidgetproducernumber'] );
		$nbtotalproducer = count( $producer );

		if ( $nbtotalproducer === 0 ) {
			return '';
		}

		$output = "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
		$output .= sprintf( esc_html( _n( 'Producer', 'Producers', $nbtotalproducer, 'lumiere-movies' ) ), number_format_i18n( $nbtotalproducer ) );

		$output .= ':</span>';

		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_data_values['imdbtaxonomyproducer'] === '1' ) ) {

			for ( $i = 0; ( $i < $nbtotalproducer ) && ( $i < $nbproducer ); $i++ ) {

				$count_jobs = isset( $producer[ $i ]['jobs'] ) && count( $producer[ $i ]['jobs'] ) > 0 ? count( $producer[ $i ]['jobs'] ) : 0;

				$jobs = '';
				for ( $j = 0; $j < $count_jobs; $j++ ) {
					$jobs .= isset( $producer[ $i ]['jobs'][ $j ] ) ? esc_html( $producer[ $i ]['jobs'][ $j ] ) : '';
					if ( $j < ( $count_jobs - 1 ) ) {
						$jobs .= ', ';
					}
				}
				$output .= $this->lumiere_make_display_taxonomy(
					'producer',
					// @phan-suppress-next-line PhanTypeInvalidDimOffset, PhanTypeMismatchArgument (Invalid offset "name" of $producer[$i] of array type array{jobs:\Countable|non-empty-array<mixed,mixed>} -> would require to define $producer array, which would be a nightmare */
					isset( $producer[ $i ]['name'] ) ? esc_html( $producer[ $i ]['name'] ) : '',
					$jobs,
					'two',
					sanitize_text_field( $movie->title() )
				);
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

			$count_jobs = isset( $producer[ $i ]['jobs'] ) && count( $producer[ $i ]['jobs'] ) > 0 ? count( $producer[ $i ]['jobs'] ) : 0;

			if ( $count_jobs > 0 ) {
				for ( $j = 0; $j < $count_jobs; $j++ ) {
					$output .= esc_html( $producer[ $i ]['jobs'][ $j ] );
					if ( $j < ( $count_jobs - 1 ) ) {
						$output .= ', ';
					}
				}
			} else {
				$output .= '&nbsp;';
			}

			$output .= '</div>';
			$output .= "\n\t\t\t" . '</div>';

		}
		return $output;
	}

	/**
	 * Display the writers
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lum_movies_writer( Title $movie ): string {

		$writer = $movie->writer();
		$nbtotalwriters = count( $writer );

		if ( $nbtotalwriters === 0 ) {
			return '';
		}

		$output = "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
		$output .= sprintf( esc_html( _n( 'Writer', 'Writers', $nbtotalwriters, 'lumiere-movies' ) ), number_format_i18n( $nbtotalwriters ) );
		$output .= ':</span>';

		// With taxonomy.
		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_data_values['imdbtaxonomywriter'] === '1' ) ) {

			for ( $i = 0; $i < $nbtotalwriters; $i++ ) {

				$count_jobs = isset( $writer[ $i ]['jobs'] ) && count( $writer[ $i ]['jobs'] ) > 0 ? count( $writer[ $i ]['jobs'] ) : 0;
				$jobs = '';

				for ( $j = 0; $j < $count_jobs; $j++ ) {

					// Add number of episode and year they worked in.
					$dates_episodes = '';
					// @phan-suppress-next-line PhanTypeInvalidDimOffset */
					if ( $writer[ $i ]['episode'] !== null && count( $writer[ $i ]['episode'] ) > 0 && isset( $writer[ $i ]['episode']['total'] ) && $writer[ $i ]['episode']['total'] !== 0 ) {
						$total = $writer[ $i ]['episode']['total'] > 0 ? esc_html( $writer[ $i ]['episode']['total'] ) . ' ' . esc_html( _n( 'episode', 'episodes', $writer[ $i ]['episode']['total'], 'lumiere-movies' ) ) : '';
						/* translators: "From" like in "from 2025" */
						$year_from_or_in = isset( $writer[ $i ]['episode']['endYear'] ) ? __( 'from', 'lumiere-movies' ) : __( 'in', 'lumiere-movies' );
						/* translators: "To" like in "to 2025" */
						$year_to_or_in = isset( $writer[ $i ]['episode']['year'] ) ? __( 'to', 'lumiere-movies' ) : __( 'in', 'lumiere-movies' );
						$year = isset( $writer[ $i ]['episode']['year'] ) ? ' ' . esc_html( $year_from_or_in ) . ' ' . esc_html( $writer[ $i ]['episode']['year'] ) : '';
						$end_year = isset( $writer[ $i ]['episode']['endYear'] ) ? ' ' . esc_html( $year_to_or_in ) . ' ' . esc_html( $writer[ $i ]['episode']['endYear'] ) : '';
						$dates_episodes = strlen( $total . $year . $end_year ) > 0 ? ' (<i>' . $total . $year . $end_year . '</i>)' : '';
					}
					$jobs .= isset( $writer[ $i ]['jobs'][ $j ] ) && strlen( $writer[ $i ]['jobs'][ $j ] ) > 0 ? $writer[ $i ]['jobs'][ $j ] . $dates_episodes : '';
					if ( $j < ( $count_jobs - 1 ) ) {
						$jobs .= ', ';
					}

				}
				$output .= $this->lumiere_make_display_taxonomy(
					'writer',
					// @phan-suppress-next-line PhanTypeInvalidDimOffset,PhanTypePossiblyInvalidDimOffset (Invalid offset "name" of $producer[$i] of array type array{jobs:\Countable|non-empty-array<mixed,mixed>} -> would require to define $producer array, which would be a nightmare */
					isset( $writer[ $i ]['name'] ) && is_string( $writer[ $i ]['name'] ) ? esc_attr( $writer[ $i ]['name'] ) : '',
					$jobs,
					'two',
					sanitize_text_field( $movie->title() )
				);
			}
			return $output;
		}

		// No taxonomy.
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

			$count_jobs = isset( $writer[ $i ]['jobs'] ) && count( $writer[ $i ]['jobs'] ) > 0 ? count( $writer[ $i ]['jobs'] ) : 0;

			for ( $j = 0; $j < $count_jobs; $j++ ) {
				$output .= sanitize_text_field( $writer[ $i ]['jobs'][ $j ] );
				if ( $j < ( $count_jobs - 1 ) ) {
					$output .= ', ';
				}
			}

			// Add number of episode and year they worked in.
			// @phan-suppress-next-line PhanTypeInvalidDimOffset */
			if ( $writer[ $i ]['episode'] !== null && count( $writer[ $i ]['episode'] ) > 0 && isset( $writer[ $i ]['episode']['total'] ) && $writer[ $i ]['episode']['total'] !== 0 ) {
				$total = isset( $writer[ $i ]['episode']['total'] ) ? esc_html( $writer[ $i ]['episode']['total'] ) . ' ' . esc_html( _n( 'episode', 'episodes', $writer[ $i ]['episode']['total'], 'lumiere-movies' ) ) : '';
				/* translators: "In" like in "in 2025" */
				$year_from_or_in = isset( $writer[ $i ]['episode']['endYear'] ) ? __( 'from', 'lumiere-movies' ) : __( 'in', 'lumiere-movies' );
				$year = isset( $writer[ $i ]['episode']['year'] ) ? ' ' . esc_html( $year_from_or_in ) . ' ' . esc_html( $writer[ $i ]['episode']['year'] ) : '';
				/* translators: "To" like in "to 2025" */
				$end_year = isset( $writer[ $i ]['episode']['endYear'] ) ? ' ' . esc_html__( 'to', 'lumiere-movies' ) . ' ' . esc_html( $writer[ $i ]['episode']['endYear'] ) : '';
				$output .= ' (<i>' . $total . $year . $end_year . '</i>)';
			}

			$output .= "\n\t\t\t\t" . '</div>';
			$output .= "\n\t\t\t" . '</div>';

		}

		return $output;
	}

	/**
	 * Display actors
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lum_movies_actor( Title $movie ): string {

		$cast = $movie->cast();
		$nbactors = intval( $this->imdb_data_values['imdbwidgetactornumber'] ) === 0 ? 1 : intval( $this->imdb_data_values['imdbwidgetactornumber'] );
		$nbtotalactors = count( $cast );

		if ( $nbtotalactors === 0 ) {
			return '';
		}

		$output = "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
		$output .= esc_html( _n( 'Actor', 'Actors', $nbtotalactors, 'lumiere-movies' ) );
		$output .= ':</span>';

		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_data_values['imdbtaxonomyactor'] === '1' ) ) {

			for ( $i = 0; ( $i < $nbtotalactors ) && ( $i < $nbactors ); $i++ ) {

				// If either name or character are not available, jump.
				if ( ! isset( $cast[ $i ]['character'][0] ) || ! isset( $cast[ $i ]['name'] ) ) {
					continue;
				}

				$output .= $this->lumiere_make_display_taxonomy(
					'actor',
					esc_attr( $cast[ $i ]['name'] ),
					esc_attr( $cast[ $i ]['character'][0] ),
					'two',
					sanitize_text_field( $movie->title() )
				);
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
			$output .= isset( $cast[ $i ]['character'][0] ) && strlen( $cast[ $i ]['character'][0] ) > 0 ? esc_html( $cast[ $i ]['character'][0] ) : '<i>' . __( 'role unknown', 'lumiere-movies' ) . '</i>';
			$output .= '</div>';
			$output .= "\n\t\t\t" . '</div>';

		}

		return $output;
	}

	/**
	 * Display plots
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lum_movies_plot( Title $movie ): string {

		$plot = $movie->plot();
		$nbplots = intval( $this->imdb_data_values['imdbwidgetplotnumber'] ) === 0 ? 1 : intval( $this->imdb_data_values['imdbwidgetplotnumber'] );
		$nbtotalplots = count( $plot );

		// tested if the array contains data; if not, doesn't go further
		if ( $nbtotalplots === 0 ) {
			return '';
		}

		$output = "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
		$output .= esc_html( _n( 'Plot', 'Plots', $nbplots, 'lumiere-movies' ) );
		$output .= ':</span><br />';

		for ( $i = 0; ( $i < $nbtotalplots ) && ( $i < $nbplots ); $i++ ) {

			/**
			 * Use links builder classes.
			 * Each one has its own class passed in $link_maker,
			 * according to which option the lumiere_select_link_maker() found in Frontend.
			 */
			$output .= $plot[ $i ]['plot'] !== null ? $this->link_maker->lumiere_movies_plot_details( $plot[ $i ]['plot'] ) : esc_html__( 'No plot found', 'lumiere-movies' );

			// add hr to every plot but the last.
			if ( $i < ( $nbtotalplots - 1 ) && $i < ( $nbplots - 1 ) ) {
				$output .= "\n\t\t\t\t<hr>";
			}
		}

		return $output;
	}

	/**
	 * Display the credit link
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lum_movies_source( Title $movie ): string {

		$mid_premier_resultat_sanitized = strlen( $movie->imdbid() ) > 0 ? strval( $movie->imdbid() ) : null;

		if ( $mid_premier_resultat_sanitized === null ) {
			return '';
		}

		$output = "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
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
}
