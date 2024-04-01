<?php declare( strict_types = 1 );
/**
 * Class for displaying movies data.
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */

namespace Lumiere\Frontend;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	wp_die( esc_html__( 'Lumière Movies: You can not call directly this page', 'lumiere-movies' ) );
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
	protected function lumiere_movies_title ( Title $movie ): string {

		$output = '';
		$year = strlen( strval( $movie->year() ) ) !== 0 ? intval( $movie->year() ) : null;
		$title_sanitized = sanitize_text_field( $movie->title() );

		$output .= "\n\t\t\t<span id=\"title_$title_sanitized\">" . $title_sanitized;

		if ( $year !== null && $this->imdb_data_values['imdbwidgetyear'] === '1' ) {
			$output .= ' (' . $year . ')';
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
	protected function lumiere_movies_pic ( Title $movie ): string {

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
		return $this->link_maker->lumiere_link_picture(
			$this->config_class->lumiere_pics_dir . 'no_pics.gif',
			$this->config_class->lumiere_pics_dir . 'no_pics.gif',
			$movie->title()
		);
	}

	/**
	 * Display the country of origin
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lumiere_movies_country ( Title $movie ): string {

		$output = '';
		$country = $movie->country();
		$nbtotalcountry = count( $country );

		// if no result, exit.
		if ( $nbtotalcountry === 0 ) {
			return $output;
		}

		$output .= "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
		$output .= sprintf( esc_attr( _n( 'Country', 'Countries', $nbtotalcountry, 'lumiere-movies' ) ), number_format_i18n( $nbtotalcountry ) );
		$output .= ':</span>';

		// Taxonomy is active.
		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_data_values['imdbtaxonomycountry'] === '1' ) ) {

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
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lumiere_movies_runtime( Title $movie ): string {

		$output = '';
		$runtime_sanitized = strval( $movie->runtime() );

		if ( strlen( $runtime_sanitized ) === 0 ) {
			return $output;
		}

		$output .= "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
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
	protected function lumiere_movies_language( Title $movie ): string {

		$output = '';
		$languages = $movie->languages();
		$nbtotallanguages = count( $languages );

		if ( $nbtotallanguages === 0 ) {
			return $output;
		}

		$output .= "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
		$output .= sprintf( esc_attr( _n( 'Language', 'Languages', $nbtotallanguages, 'lumiere-movies' ) ), number_format_i18n( $nbtotallanguages ) );
		$output .= ':</span>';

		// Taxonomy is active.
		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_data_values['imdbtaxonomylanguage'] === '1' ) ) {

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
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lumiere_movies_rating( Title $movie ): string {

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
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lumiere_movies_genre( Title $movie ): string {

		$output = '';
		$genre = $movie->genres();
		$nbtotalgenre = count( $genre );

		if ( $nbtotalgenre === 0 ) {
			return $output;
		}

		$output .= "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
		$output .= sprintf( esc_attr( _n( 'Genre', 'Genres', $nbtotalgenre, 'lumiere-movies' ) ), number_format_i18n( $nbtotalgenre ) );

		$output .= ':</span>';

		// Taxonomy is active.
		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_data_values['imdbtaxonomygenre'] === '1' ) ) {

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
	 * Using $limit_keywords var to limit the total (not selected in the plugin options, hardcoded here)
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lumiere_movies_keyword( Title $movie ): string {

		$output = '';
		$keywords = $movie->keywords();
		$nbtotalkeywords = count( $keywords );
		$limit_keywords = 10;

		if ( $nbtotalkeywords === 0 ) {
			return $output;
		}

		$output .= "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
		$output .= sprintf( esc_attr( _n( 'Keyword', 'Keywords', $nbtotalkeywords, 'lumiere-movies' ) ), number_format_i18n( $nbtotalkeywords ) );
		$output .= ':</span>';

		// Taxonomy is active.
		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_data_values['imdbtaxonomykeyword'] === '1' ) ) {

			for ( $i = 0; $i < $nbtotalkeywords && $i < $limit_keywords; $i++ ) {

				$output .= $this->lumiere_make_display_taxonomy( 'keyword', esc_attr( $keywords[ $i ] ), '', 'one' );
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
	protected function lumiere_movies_goof( Title $movie ): string {

		$output = '';

		$goofs = $movie->goofs();
		$nbgoofs = intval( $this->imdb_data_values['imdbwidgetgoofnumber'] ) === 0 || $this->imdb_data_values['imdbwidgetgoofnumber'] === false ? '1' : intval( $this->imdb_data_values['imdbwidgetgoofnumber'] );
		$nbtotalgoofs = count( $goofs );

		// if no result, exit.
		if ( $nbtotalgoofs === 0 ) {

			return $output;

		}

		$output .= "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
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
	 * Quotes are what People said, Quotes do not exists in Movie's pages, which do not display people's data
	 * Kept for compatibility purposes: the function lumiere_movies_quote() is automatically created from config data, the class would complain that method doesn't exist
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @since 4.0 Removed the method's content, since this function is for compatibility and does nothing
	 *
	 * @param Title $movie IMDbPHP title class
	 * @return string Nothing
	 */
	protected function lumiere_movies_quote( Title $movie ): string {
		return '';
	}

	/**
	 * Display the taglines
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lumiere_movies_tagline( Title $movie ): string {

		$output = '';
		$taglines = $movie->taglines();
		$nbtaglines = intval( $this->imdb_data_values['imdbwidgettaglinenumber'] ) === 0 || $this->imdb_data_values['imdbwidgettaglinenumber'] === false ? '1' : intval( $this->imdb_data_values['imdbwidgettaglinenumber'] );

		$nbtotaltaglines = count( $taglines );

		// If no result, exit.
		if ( $nbtotaltaglines === 0 ) {

			return $output;

		}

		$output .= "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
		$output .= sprintf( esc_attr( _n( 'Tagline', 'Taglines', $nbtotaltaglines, 'lumiere-movies' ) ), number_format_i18n( $nbtotaltaglines ) );
		$output .= ':</span>';

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
	protected function lumiere_movies_trailer( Title $movie ): string {

		$output = '';
		$trailers = $movie->trailers( true );
		$nbtrailers = intval( $this->imdb_data_values['imdbwidgettrailernumber'] ) === 0 || $this->imdb_data_values['imdbwidgettrailernumber'] === false ? '1' : intval( $this->imdb_data_values['imdbwidgettrailernumber'] );

		$nbtotaltrailers = intval( count( $trailers ) );

		// if no results, exit.
		if ( $nbtotaltrailers === 0 ) {

			return $output;

		}

		$output .= "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
		$output .= sprintf( esc_attr( _n( 'Trailer', 'Trailers', $nbtotaltrailers, 'lumiere-movies' ) ), number_format_i18n( $nbtotaltrailers ) );
		$output .= ':</span>';

		for ( $i = 0; ( $i < $nbtrailers && ( $i < $nbtotaltrailers ) ); $i++ ) {

			/**
			 * Use links builder classes.
			 * Each one has its own class passed in $link_maker,
			 * according to which option the lumiere_select_link_maker() found in Frontend.
			 */
			$output .= $this->link_maker->lumiere_movies_trailer_details( $trailers[ $i ]['videoUrl'], $trailers[ $i ]['title'] );

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
	protected function lumiere_movies_color( Title $movie ): string {

		$output = '';
		$colors = $movie->colors();
		$nbtotalcolors = count( $colors );

		// if no result, exit.
		if ( $nbtotalcolors === 0 ) {

			return $output;

		}

		$output .= "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
		$output .= sprintf( esc_attr( _n( 'Color', 'Colors', $nbtotalcolors, 'lumiere-movies' ) ), number_format_i18n( $nbtotalcolors ) );
		$output .= ':</span>';

		// Taxonomy activated.
		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_data_values['imdbtaxonomycolor'] === '1' ) ) {

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
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lumiere_movies_alsoknow( Title $movie ): string {

		$output = '';
		$alsoknow = $movie->alsoknow();
		$nbalsoknow = intval( $this->imdb_data_values['imdbwidgetalsoknownumber'] ) === 0 || $this->imdb_data_values['imdbwidgetalsoknownumber'] === false ? '1' : intval( $this->imdb_data_values['imdbwidgetalsoknownumber'] ) + 1; // Adding 1 since first array line is the title
		$nbtotalalsoknow = count( $alsoknow );

		// if no result, exit.
		if ( $nbtotalalsoknow === 0 ) {

			return $output;

		}

		$output .= "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
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
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lumiere_movies_composer( Title $movie ): string {

		$output = '';
		$composer = $movie->composer();
		$nbtotalcomposer = count( $composer );

		// if no results, exit.
		if ( $nbtotalcomposer === 0 ) {
			return $output;
		}

		$output .= "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
		$output .= sprintf( esc_attr( _n( 'Composer', 'Composers', $nbtotalcomposer, 'lumiere-movies' ) ), number_format_i18n( $nbtotalcomposer ) );
		$output .= ':</span>';

		// Taxonomy
		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_data_values['imdbtaxonomycomposer'] === '1' ) ) {

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
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lumiere_movies_soundtrack( Title $movie ): string {

		$output = '';
		$soundtrack = $movie->soundtrack();
		$nbsoundtracks = intval( $this->imdb_data_values['imdbwidgetsoundtracknumber'] ) === 0 || $this->imdb_data_values['imdbwidgetsoundtracknumber'] === false ? '1' : intval( $this->imdb_data_values['imdbwidgetsoundtracknumber'] );
		$nbtotalsoundtracks = count( $soundtrack );

		// if no results, exit.
		if ( $nbtotalsoundtracks === 0 ) {
			return $output;
		}

		$output .= "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
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
				$this->link_maker->lumiere_imdburl_of_soundtrack( $soundtrack [ $i ]['credits'] )
			) . '</i> ';

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
	protected function lumiere_movies_prodcompany( Title $movie ): string {

		$output = '';
		$prodcompany = $movie->prodCompany();
		$nbtotalprodcompany = count( $prodcompany );

		// if no result, exit.
		if ( $nbtotalprodcompany === 0 ) {
			return $output;
		}

		$output .= "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
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
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lumiere_movies_officialsites( Title $movie ): string {

		$output = '';
		$official_sites = $movie->officialSites();
		$nbtotalofficial_sites = count( $official_sites );

		// if no result, exit.
		if ( $nbtotalofficial_sites === 0 ) {
			return $output;
		}

		$output .= "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
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
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lumiere_movies_director( Title $movie ): string {

		$output = '';
		$director = $movie->director();
		$nbtotaldirector = count( $director );

		// if no result, exit.
		if ( $nbtotaldirector === 0 ) {
			return $output;
		}

		$output .= "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
		$output .= sprintf( esc_attr( _n( 'Director', 'Directors', $nbtotaldirector, 'lumiere-movies' ) ), number_format_i18n( $nbtotaldirector ) );
		$output .= ':</span>';

		// If Taxonomy is selected, build links to taxonomy pages
		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_data_values['imdbtaxonomydirector'] === '1' )  ) {

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
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lumiere_movies_creator( Title $movie ): string {

		$output = '';
		$creator = $movie->creator();
		$nbtotalcreator = count( $creator );

		// if no results, exit.
		if ( $nbtotalcreator === 0 ) {
			return $output;
		}

		$output .= "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
		$output .= sprintf( esc_attr( _n( 'Creator', 'Creators', $nbtotalcreator, 'lumiere-movies' ) ), number_format_i18n( $nbtotalcreator ) );
		$output .= ':</span>&nbsp;';

		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_data_values['imdbtaxonomycreator'] === '1' ) ) {

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
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lumiere_movies_producer( Title $movie ): string {

		$output = '';
		$producer = $movie->producer();
		$nbproducer = intval( $this->imdb_data_values['imdbwidgetproducernumber'] ) === 0 || $this->imdb_data_values['imdbwidgetproducernumber'] === false ? '1' : intval( $this->imdb_data_values['imdbwidgetproducernumber'] );
		$nbtotalproducer = count( $producer );

		if ( $nbtotalproducer === 0 ) {
			return $output;
		}

		$output .= "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
		$output .= sprintf( esc_attr( _n( 'Producer', 'Producers', $nbtotalproducer, 'lumiere-movies' ) ), number_format_i18n( $nbtotalproducer ) );

		$output .= ':</span>';

		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_data_values['imdbtaxonomyproducer'] === '1' ) ) {

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
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lumiere_movies_writer( Title $movie ): string {

		$output = '';
		$writer = $movie->writing();
		$nbtotalwriters = count( $writer );

		if ( $nbtotalwriters === 0 ) {
			return $output;
		}

		$output .= "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
		$output .= sprintf( esc_attr( _n( 'Writer', 'Writers', $nbtotalwriters, 'lumiere-movies' ) ), number_format_i18n( $nbtotalwriters ) );
		$output .= ':</span>';

		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_data_values['imdbtaxonomywriter'] === '1' ) ) {

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
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lumiere_movies_actor( Title $movie ): string {

		$output = '';
		$cast = $movie->cast();
		$nbactors = intval( $this->imdb_data_values['imdbwidgetactornumber'] ) === 0 ? '1' : intval( $this->imdb_data_values['imdbwidgetactornumber'] );
		$nbtotalactors = count( $cast );

		if ( $nbtotalactors === 0 ) {
			return $output;
		}

		$output .= "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
		$output .= sprintf( esc_attr( _n( 'Actor', 'Actors', $nbtotalactors, 'lumiere-movies' ) ), number_format_i18n( $nbtotalactors ) );
		$output .= ':</span>';

		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_data_values['imdbtaxonomyactor'] === '1' ) ) {

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
			$isset_cast = isset( $cast[ $i ]['role'] ) && is_string( $cast[ $i ]['role'] ) ? preg_replace( '/\n/', '', $cast[ $i ]['role'] ) : null;
			$output .= isset( $isset_cast ) ? esc_attr( $isset_cast ) : ''; # remove the <br> that breaks the layout
			$output .= '</div>';
			$output .= "\n\t\t\t" . '</div>';

		} // endfor

		return $output;
	}

	/**
	 * Display plots
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lumiere_movies_plot( Title $movie ): string {

		$output = '';
		$plot = $movie->plot();
		$nbplots = intval( $this->imdb_data_values['imdbwidgetplotnumber'] ) === 0 ? '1' : intval( $this->imdb_data_values['imdbwidgetplotnumber'] );
		$nbtotalplots = count( $plot );

		// tested if the array contains data; if not, doesn't go further
		if ( $nbtotalplots === 0 ) {
			return $output;
		}

		$output .= "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
		$output .= sprintf( esc_attr( _n( 'Plot', 'Plots', $nbtotalplots, 'lumiere-movies' ) ), number_format_i18n( $nbtotalplots ) );
		$output .= ':</span><br />';

		for ( $i = 0; ( $i < $nbtotalplots ) && ( $i < $nbplots ); $i++ ) {

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
	 * @see Movie::lumiere_movie_design() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function lumiere_movies_source( Title $movie ): string {

		$output = '';
		$mid_premier_resultat_sanitized = strlen( $movie->imdbid() ) > 0 ? strval( $movie->imdbid() ) : null;

		if ( $mid_premier_resultat_sanitized === null ) {
			return $output;
		}

		$output .= "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
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
