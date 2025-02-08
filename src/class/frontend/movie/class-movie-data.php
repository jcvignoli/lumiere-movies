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

namespace Lumiere\Frontend\Movie;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Imdb\Title;
use Lumiere\Tools\Get_Options;
use Lumiere\Frontend\Main;
use Lumiere\Frontend\Movie\Movie_Layout;

/**
 * Those methods are utilised by class Movie to display the sections
 * The class uses \Lumiere\Link_Makers\Link_Factory to automatically select the appropriate Link maker class to display data ( i.e. Classic links, Highslide/Bootstrap, No Links, AMP)
 * It uses ImdbPHP Classes to display movies/people data
 * It uses Layout defined in Movie_Layout
 * It uses taxonomy functions in Movie_Layout
 * It is extended by Movie_Display, child class
 *
 * @since 4.0 new class, methods were extracted from Movie_Display class
 */
class Movie_Data {

	/**
	 * Traits
	 */
	use Main;

	public function __construct(
		protected Movie_Layout $movie_layout = new Movie_Layout(),
		protected Movie_Taxonomy $movie_taxo = new Movie_Taxonomy()
	) {
		// Construct Frontend Main trait with options and links.
		$this->start_main_trait();
	}

	/**
	 * Display the title and possibly the year
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param string $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_title( Title $movie, string $item_name ): string {

		$year = $movie->year();
		$title_sanitized = esc_html( $movie->$item_name() );

		$year_text = '';
		if ( strlen( strval( $year ) ) > 0 && isset( $this->imdb_data_values['imdbwidgetyear'] ) && $this->imdb_data_values['imdbwidgetyear'] === '1' ) {
			$year_text = ' (' . strval( $year ) . ')';
		}

		return $this->movie_layout->subtitle_item_title(
			$title_sanitized,
			$year_text
		);
	}

	/**
	 * Display the picture
	 *
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @since 3.7 improved compatibility with AMP WP plugin in relevant class
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param string $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_pic( Title $movie, string $item_name ): string {

		/**
		 * Use links builder classes.
		 * Each one has its own class passed in $link_maker,
		 * according to which option the lumiere_select_link_maker() found in Frontend.
		 */
		// If cache is active, use the pictures from IMDBphp class.
		if ( $this->imdb_cache_values['imdbusecache'] === '1' ) {
			return $this->link_maker->lumiere_link_picture( $movie->photoLocalurl( false ), $movie->photoLocalurl( true ), $movie->title() );
		}

		// If cache is deactivated, display no_pics.gif
		$no_pic_url = Get_Options::LUM_PICS_URL . 'no_pics.gif';
		return $this->link_maker->lumiere_link_picture( $no_pic_url, $no_pic_url, $movie->title() );
	}

	/**
	 * Display the country of origin
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param string $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_country( Title $movie, string $item_name ): string {

		$country = $movie->$item_name();
		$nbtotalcountry = count( $country );

		// if no result, exit.
		if ( $nbtotalcountry === 0 ) {
			return '';
		}

		$output = $this->movie_layout->subtitle_item(
			esc_html( _n( 'Country', 'Countries', $nbtotalcountry, 'lumiere-movies' ) )
		);

		// Taxonomy is active.
		if ( $this->imdb_admin_values['imdbtaxonomy'] === '1' && $this->imdb_data_values[ 'imdbtaxonomy' . $item_name ] === '1' ) {

			for ( $i = 0; $i < $nbtotalcountry; $i++ ) {

				$get_taxo_options = $this->movie_taxo->create_taxonomy_options( $item_name, esc_html( $country[ $i ] ), $this->imdb_admin_values );
				$output .= $this->movie_layout->get_layout_items( esc_html( $movie->title() ), $get_taxo_options );

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
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param string $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_runtime( Title $movie, string $item_name ): string {

		$runtime_sanitized = isset( $movie->$item_name()[0]['time'] ) ? esc_html( strval( $movie->$item_name()[0]['time'] ) ) : '';

		if ( strlen( $runtime_sanitized ) === 0 ) {
			return '';
		}

		return $this->movie_layout->subtitle_item(
			esc_html__( 'Runtime', 'lumiere-movies' )
		) . $runtime_sanitized . ' ' . esc_html__( 'minutes', 'lumiere-movies' );
	}

	/**
	 * Display the language
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param string $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_language( Title $movie, string $item_name ): string {

		$languages = $movie->$item_name();
		$nbtotallanguages = count( $languages );

		if ( $nbtotallanguages === 0 ) {
			return '';
		}

		$output = $this->movie_layout->subtitle_item(
			esc_html( _n( 'Language', 'Languages', $nbtotallanguages, 'lumiere-movies' ) )
		);

		// Taxonomy is active.
		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_data_values[ 'imdbtaxonomy' . $item_name ] === '1' ) ) {

			for ( $i = 0; $i < $nbtotallanguages; $i++ ) {

				$get_taxo_options = $this->movie_taxo->create_taxonomy_options( $item_name, esc_html( $languages[ $i ] ), $this->imdb_admin_values );
				$output .= $this->movie_layout->get_layout_items( esc_html( $movie->title() ), $get_taxo_options );

				if ( $i < $nbtotallanguages - 1 ) {
					$output .= ', ';
				}
			}
			return $output;
		}

		for ( $i = 0; $i < $nbtotallanguages; $i++ ) {

			$output .= sanitize_text_field( $languages[ $i ] );

			if ( $i < $nbtotallanguages - 1 ) {
				$output .= ', ';
			}
		}
		return $output;
	}

	/**
	 * Display connected/realted movies
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @since 4.4 New method
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param string $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_connection( Title $movie, string $item_name ): string {

		$connected_movies = $movie->$item_name();
		$admin_max_connected = intval( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] );
		$nbtotalconnected = count( $connected_movies );

		if ( $nbtotalconnected === 0 ) {
			return '';
		}

		$output = $this->movie_layout->subtitle_item(
			esc_html( _n( 'Related movie', 'Related movies', $nbtotalconnected, 'lumiere-movies' ) )
		);

		foreach ( Get_Options::define_list_connect_cat() as $category => $data_explain ) {

			// Total items for this category.
			$nb_items = count( $connected_movies[ $category ] );

			for ( $i = 0; $i < $admin_max_connected; $i++ ) {
				if ( isset( $connected_movies[ $category ][ $i ]['titleId'] ) && $connected_movies[ $category ][ $i ]['titleName'] ) {

					if ( $i === 0 ) {
						$output .= "<br><strong>$data_explain</strong>: ";
					}

					/**
					 * Use links builder classes.
					 * Each one has its own class passed in $link_maker,
					 * according to which option the lumiere_select_link_maker() found in Frontend.
					 */
					$output .= $this->link_maker->popup_film_link_inbox(
						$connected_movies[ $category ][ $i ]['titleName'],
						$connected_movies[ $category ][ $i ]['titleId']
					);

					$output .= isset( $connected_movies[ $category ][ $i ]['description'] ) ? ' (' . esc_html( $connected_movies[ $category ][ $i ]['description'] ) . ')' : '';
					if ( $i < ( $admin_max_connected - 1 ) && $i < ( $nbtotalconnected ) && $i < ( $nb_items - 1 ) ) {
						$output .= ', '; // add comma to every connected movie but the last.
					}
				}
			}
		}
		return $output;
	}

	/**
	 * Display the rating
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param string $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_rating( Title $movie, string $item_name ): string {

		$votes_sanitized = intval( $movie->votes() );
		$rating_sanitized = intval( $movie->$item_name() );

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
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param string $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_genre( Title $movie, string $item_name ): string {

		$genre = $movie->$item_name();
		$nbtotalgenre = count( $genre ) > 0 ? count( $genre ) : 0;

		if ( $nbtotalgenre === 0 ) {
			return '';
		}

		$output = $this->movie_layout->subtitle_item(
			esc_html( _n( 'Genre', 'Genres', $nbtotalgenre, 'lumiere-movies' ) )
		);

		// Taxonomy is active.
		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_data_values[ 'imdbtaxonomy' . $item_name ] === '1' ) ) {
			for ( $i = 0; $i < $nbtotalgenre; $i++ ) {

				$get_taxo_options = $this->movie_taxo->create_taxonomy_options( $item_name, esc_html( $genre[ $i ]['mainGenre'] ), $this->imdb_admin_values );
				$output .= isset( $genre[ $i ]['mainGenre'] ) ? $this->movie_layout->get_layout_items( esc_html( $movie->title() ), $get_taxo_options ) : '';

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
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param string $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_keyword( Title $movie, string $item_name ): string {

		$keywords = $movie->$item_name();
		$nbtotalkeywords = count( $keywords );
		$limit_keywords = 10;

		if ( $nbtotalkeywords === 0 ) {
			return '';
		}

		$output = $this->movie_layout->subtitle_item(
			esc_html( _n( 'Keyword', 'Keywords', $nbtotalkeywords, 'lumiere-movies' ) )
		);

		// Taxonomy is active.
		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_data_values[ 'imdbtaxonomy' . $item_name ] === '1' ) ) {

			for ( $i = 0; $i < $nbtotalkeywords && $i < $limit_keywords; $i++ ) {

				$get_taxo_options = $this->movie_taxo->create_taxonomy_options( $item_name, sanitize_text_field( $keywords[ $i ] ), $this->imdb_admin_values );
				$output .= $this->movie_layout->get_layout_items( esc_html( $movie->title() ), $get_taxo_options );

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
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param string $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_goof( Title $movie, string $item_name ): string {

		$goofs = $movie->$item_name();
		$admin_max_goofs = intval( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] );
		$filter_nbtotalgoofs = array_filter( $goofs, fn( array $goofs ) => ( count( array_values( $goofs ) ) > 0 ) ); // counts the actual goofs, not their categories
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

		$output = $this->movie_layout->subtitle_item(
			esc_html( _n( 'Goof', 'Goofs', $nbtotalgoofs, 'lumiere-movies' ) )
		);

		// Process goof type after goof type
		foreach ( $goofs_type as $type ) {
			// Loop conditions: less than the total number of goofs available AND less than the goof limit setting, using a loop counter.
			for ( $i = 0; ( $i < $nbtotalgoofs ) && ( $overall_loop <= $admin_max_goofs ); $i++ ) {
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
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param string $item_name The name of the item, ie 'director', 'writer'
	 * @return string Nothing
	 */
	protected function get_item_quote( Title $movie, string $item_name ): string {

		$quotes_merged = array_merge( ...$movie->$item_name() ); // Merge the multidimensional array to two dimensions.
		$admin_max_quotes = intval( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] );

		$nbtotalquotes = count( $quotes_merged );

		// If no result, exit.
		if ( $nbtotalquotes === 0 ) {
			return '';
		}

		$output = $this->movie_layout->subtitle_item(
			esc_html( _n( 'Quote', 'Quotes', $nbtotalquotes, 'lumiere-movies' ) )
		);

		for ( $i = 0; $i < $admin_max_quotes && ( $i < $nbtotalquotes ); $i++ ) {

			$output .= "\n\t\t\t&laquo; " . esc_html( $quotes_merged[ $i ] ) . ' &raquo; ';

			if ( $i < ( $admin_max_quotes - 1 ) && $i < ( $nbtotalquotes - 1 ) ) {
				$output .= "\n\t\t\t\t<hr>";
			}
		}
		return $output;
	}

	/**
	 * Display the taglines
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 */
	protected function get_item_tagline( Title $movie, string $item_name ): string {

		$taglines = $movie->$item_name();
		$admin_max_taglines = intval( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] );
		$nbtotaltaglines = count( $taglines );

		// If no result, exit.
		if ( $nbtotaltaglines === 0 ) {
			return '';
		}

		$output = $this->movie_layout->subtitle_item(
			esc_html( _n( 'Tagline', 'Taglines', $nbtotaltaglines, 'lumiere-movies' ) )
		);

		for ( $i = 0; $i < $admin_max_taglines && ( $i < $nbtotaltaglines ); $i++ ) {

			$output .= "\n\t\t\t&laquo; " . esc_html( $taglines[ $i ] ) . ' &raquo; ';
			if ( $i < ( $admin_max_taglines - 1 ) && $i < ( $nbtotaltaglines - 1 ) ) {
				$output .= ', '; // add comma to every tagline but the last.
			}
		}
		return $output;
	}

	/**
	 * Display the trailer
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param string $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_trailer( Title $movie, string $item_name ): string {

		$trailers = $movie->video(); // Title::video() works faster than Title::trailer()
		$trailers = $trailers['Trailer'] ?? null; // Two rows available: Clip and Trailer
		$admin_max_trailers = intval( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] );
		$nbtotaltrailers = isset( $trailers ) ? count( $trailers ) : null;

		// if no results, exit.
		if ( $nbtotaltrailers === 0 || $nbtotaltrailers === null ) {
			return '';
		}

		$output = $this->movie_layout->subtitle_item(
			esc_html( _n( 'Trailer', 'Trailers', $nbtotaltrailers, 'lumiere-movies' ) )
		);

		for ( $i = 0; ( $i < $admin_max_trailers && ( $i < $nbtotaltrailers ) ); $i++ ) {

			if ( ! isset( $trailers[ $i ]['playbackUrl'] ) ) {
				continue;
			}

			/**
			 * Use links builder classes.
			 * Each one has its own class passed in $link_maker,
			 * according to which option the lumiere_select_link_maker() found in Frontend.
			 */
			$output .= $this->link_maker->lumiere_movies_trailer_details( $trailers[ $i ]['playbackUrl'], $trailers[ $i ]['name'] );

			if ( $i < ( $admin_max_trailers - 1 ) && $i < ( $nbtotaltrailers - 1 ) ) {
				$output .= ', '; // add comma to every trailer but the last.
			}
		}
		return $output;
	}

	/**
	 * Display the color
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param string $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_color( Title $movie, string $item_name ): string {

		$colors = $movie->$item_name();
		$nbtotalcolors = count( $colors );

		// if no result, exit.
		if ( $nbtotalcolors === 0 ) {
			return '';
		}

		$output = $this->movie_layout->subtitle_item(
			esc_html( _n( 'Colorisation', 'Colorisations', $nbtotalcolors, 'lumiere-movies' ) )
		);

		// Taxonomy activated.
		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_data_values[ 'imdbtaxonomy' . $item_name ] === '1' ) ) {

			for ( $i = 0; $i < $nbtotalcolors; $i++ ) {

				$get_taxo_options = $this->movie_taxo->create_taxonomy_options( $item_name, sanitize_text_field( $colors[ $i ]['type'] ), $this->imdb_admin_values );
				$output .= $this->movie_layout->get_layout_items( esc_html( $movie->title() ), $get_taxo_options );

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
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param string $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_alsoknow( Title $movie, string $item_name ): string {

		$alsoknow = $movie->$item_name();
		$admin_max_aka = intval( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] ) + 1; // Adding 1 since first array line is the title
		$nbtotalalsoknow = count( $alsoknow );

		// if no result, exit.
		if ( $nbtotalalsoknow === 0 ) {
			return '';
		}

		$output = $this->movie_layout->subtitle_item(
			esc_html__( 'Also known as', 'lumiere-movies' )
		);

		for ( $i = 0; ( $i < $nbtotalalsoknow ) && ( $i < $admin_max_aka ); $i++ ) {

			// Orignal title, already using it in the box.
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

			if ( $i < ( $nbtotalalsoknow - 1 ) && $i < ( $admin_max_aka - 1 ) ) {
				$output .= ', ';
			}
		}
		return $output;
	}

	/**
	 * Display the composers
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param string $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_composer( Title $movie, string $item_name ): string {

		$composer = $movie->$item_name();
		$nbtotalcomposer = count( $composer );

		// if no results, exit.
		if ( $nbtotalcomposer === 0 ) {
			return '';
		}

		$output = $this->movie_layout->subtitle_item(
			esc_html( _n( 'Composer', 'Composers', $nbtotalcomposer, 'lumiere-movies' ) )
		);

		// Taxonomy
		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_data_values[ 'imdbtaxonomy' . $item_name ] === '1' ) ) {

			for ( $i = 0; $i < $nbtotalcomposer; $i++ ) {

				$get_taxo_options = $this->movie_taxo->create_taxonomy_options( $item_name, esc_html( $composer[ $i ]['name'] ), $this->imdb_admin_values );
				$output .= $this->movie_layout->get_layout_items( esc_html( $movie->title() ), $get_taxo_options );

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
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param string $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_soundtrack( Title $movie, string $item_name ): string {

		$soundtrack = $movie->$item_name();
		$admin_max_sndtrk = intval( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] );
		$nbtotalsoundtracks = count( $soundtrack );

		// if no results, exit.
		if ( $nbtotalsoundtracks === 0 ) {
			return '';
		}

		$output = $this->movie_layout->subtitle_item(
			esc_html( _n( 'Soundtrack', 'Soundtracks', $nbtotalsoundtracks, 'lumiere-movies' ) )
		);

		for ( $i = 0; $i < $admin_max_sndtrk && ( $i < $nbtotalsoundtracks ); $i++ ) {
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

			if ( $i < ( $admin_max_sndtrk - 1 ) && $i < ( $nbtotalsoundtracks - 1 ) ) {
				$output .= ', ';
			}
		}
		return $output;
	}

	/**
	 * Display the production companies
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param string $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_prodcompany( Title $movie, string $item_name ): string {

		$prodcompany = $movie->prodCompany();
		$nbtotalprodcompany = count( $prodcompany );

		// if no result, exit.
		if ( $nbtotalprodcompany === 0 ) {
			return '';
		}

		$output = $this->movie_layout->subtitle_item(
			esc_html( _n( 'Production company', 'Production companies', $nbtotalprodcompany, 'lumiere-movies' ) )
		);

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
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param string $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_officialsites( Title $movie, string $item_name ): string {

		$get_external_sites = $movie->extSites();
		$external_sites = $get_external_sites['official'] ?? $get_external_sites['misc'] ?? [];
		$nbtotalext_sites = count( $external_sites );

		// if no result, exit.
		if ( count( $external_sites ) === 0 ) {
			return '';
		}

		$output = $this->movie_layout->subtitle_item(
			esc_html( _n( 'Official website', 'Official websites', $nbtotalext_sites, 'lumiere-movies' ) )
		);

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
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param string $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_director( Title $movie, string $item_name ): string {

		$director = $movie->$item_name();
		$nbtotaldirector = count( $director );

		// if no result, exit.
		if ( $nbtotaldirector === 0 ) {
			return '';
		}

		$output = $this->movie_layout->subtitle_item(
			esc_html( _n( 'Director', 'Directors', $nbtotaldirector, 'lumiere-movies' ) )
		);

		// If Taxonomy is selected, build links to taxonomy pages
		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_data_values[ 'imdbtaxonomy' . $item_name ] === '1' )  ) {

			for ( $i = 0; $i < $nbtotaldirector; $i++ ) {

				$get_taxo_options = $this->movie_taxo->create_taxonomy_options( $item_name, esc_html( $director[ $i ]['name'] ), $this->imdb_admin_values );
				$output .= $this->movie_layout->get_layout_items( esc_html( $movie->title() ), $get_taxo_options );

				if ( $i < $nbtotaldirector - 1 ) {
					$output .= ', ';
				}
			}

			return $output;

		}

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
	 * Display the cinematographer (directeur photo)
	 * For historical reasons, imdb config has "creator", so the method's name is based on the word
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param string $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_creator( Title $movie, string $item_name ): string {

		$cinematographer = $movie->cinematographer();
		$nbtotalcinematographer = count( $cinematographer );

		// if no results, exit.
		if ( $nbtotalcinematographer === 0 ) {
			return '';
		}

		$output = $this->movie_layout->subtitle_item(
			esc_html( _n( 'Cinematographer', 'Cinematographers', $nbtotalcinematographer, 'lumiere-movies' ) )
		);

		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_data_values[ 'imdbtaxonomy' . $item_name ] === '1' ) ) {

			for ( $i = 0; $i < $nbtotalcinematographer; $i++ ) {

				$get_taxo_options = $this->movie_taxo->create_taxonomy_options( 'cinematographer', esc_html( $cinematographer[ $i ]['name'] ), $this->imdb_admin_values );
				$output .= $this->movie_layout->get_layout_items( esc_html( $movie->title() ), $get_taxo_options );

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
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param string $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_producer( Title $movie, string $item_name ): string {

		$producer = $movie->$item_name();
		$admin_max_producer = intval( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] );
		$nbtotalproducer = count( $producer );

		if ( $nbtotalproducer === 0 ) {
			return '';
		}

		$output = $this->movie_layout->subtitle_item(
			esc_html( _n( 'Producer', 'Producers', $nbtotalproducer, 'lumiere-movies' ) )
		);

		// Taxonomy is active.
		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_data_values[ 'imdbtaxonomy' . $item_name ] === '1' ) ) {

			for ( $i = 0; ( $i < $nbtotalproducer ) && ( $i < $admin_max_producer ); $i++ ) {

				$count_jobs = isset( $producer[ $i ]['jobs'] ) && count( $producer[ $i ]['jobs'] ) > 0 ? count( $producer[ $i ]['jobs'] ) : 0;

				$jobs = '';
				for ( $j = 0; $j < $count_jobs; $j++ ) {
					$jobs .= isset( $producer[ $i ]['jobs'][ $j ] ) ? esc_html( $producer[ $i ]['jobs'][ $j ] ) : '';
					if ( $j < ( $count_jobs - 1 ) ) {
						$jobs .= ', ';
					}
				}

				$get_taxo_options = $this->movie_taxo->create_taxonomy_options(
					$item_name,
					// @phan-suppress-next-line PhanTypeInvalidDimOffset, PhanTypeMismatchArgument (Invalid offset "name" of $producer[$i] of array type array{jobs:\Countable|non-empty-array<mixed,mixed>} -> would require to define $producer array, which would be a nightmare */
					isset( $producer[ $i ]['name'] ) ? esc_html( $producer[ $i ]['name'] ) : '',
					$this->imdb_admin_values
				);
				$output .= $this->movie_layout->get_layout_items( esc_html( $movie->title() ), $get_taxo_options, $jobs );
			}
			return $output;
		}

		// No taxonomy.
		for ( $i = 0; ( $i < $nbtotalproducer ) && ( $i < $admin_max_producer ); $i++ ) {

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
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param string $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_writer( Title $movie, string $item_name ): string {

		$writer = $movie->$item_name();
		$nbtotalwriters = count( $writer );

		if ( $nbtotalwriters === 0 ) {
			return '';
		}

		$output = $this->movie_layout->subtitle_item(
			esc_html( _n( 'Writer', 'Writers', $nbtotalwriters, 'lumiere-movies' ) )
		);

		// With taxonomy.
		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_data_values[ 'imdbtaxonomy' . $item_name ] === '1' ) ) {

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

				$get_taxo_options = $this->movie_taxo->create_taxonomy_options(
					$item_name,
					// @phan-suppress-next-line PhanTypeInvalidDimOffset,PhanTypeMismatchArgument (Invalid offset "name" of $producer[$i] of array type array{jobs:\Countable|non-empty-array<mixed,mixed>} -> would require to define $producer array, which would be a nightmare */
					isset( $writer[ $i ]['name'] ) ? esc_html( $writer[ $i ]['name'] ) : '',
					$this->imdb_admin_values
				);
				$output .= $this->movie_layout->get_layout_items( esc_html( $movie->title() ), $get_taxo_options, $jobs );

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
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param string $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_actor( Title $movie, string $item_name ): string {

		$actor = $movie->cast();
		$admin_total_actor = intval( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] );
		$nbtotalactors = count( $actor );

		if ( $nbtotalactors === 0 ) {
			return '';
		}

		$output = $this->movie_layout->subtitle_item(
			esc_html( _n( 'Actor', 'Actors', $nbtotalactors, 'lumiere-movies' ) )
		);

		// Taxonomy
		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_data_values[ 'imdbtaxonomy' . $item_name ] === '1' ) ) {

			for ( $i = 0; ( $i < $nbtotalactors ) && ( $i < $admin_total_actor ); $i++ ) {

				// If either name or character are not available, jump.
				if ( ! isset( $actor[ $i ]['character'][0] ) || ! isset( $actor[ $i ]['name'] ) ) {
					continue;
				}

				$get_taxo_options = $this->movie_taxo->create_taxonomy_options(
					'actor',
					esc_html( $actor[ $i ]['name'] ),
					$this->imdb_admin_values
				);
				$output .= $this->movie_layout->get_layout_items( esc_html( $movie->title() ), $get_taxo_options, esc_attr( $actor[ $i ]['character'][0] ) );

			}

			return $output;
		}

		for ( $i = 0; $i < $admin_total_actor && ( $i < $nbtotalactors ); $i++ ) {

			$output .= "\n\t\t\t" . '<div align="center" class="lumiere_container">';
			$output .= "\n\t\t\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';

			/**
			 * Use links builder classes.
			 * Each one has its own class passed in $link_maker,
			 * according to which option the lumiere_select_link_maker() found in Frontend.
			 */
			$output .= $this->link_maker->lumiere_link_popup_people( $actor, $i );

			$output .= '</div>';
			$output .= "\n\t\t\t\t" . '<div class="lumiere_align_right lumiere_flex_auto">';
			$output .= isset( $actor[ $i ]['character'][0] ) && strlen( $actor[ $i ]['character'][0] ) > 0 ? esc_html( $actor[ $i ]['character'][0] ) : '<i>' . __( 'role unknown', 'lumiere-movies' ) . '</i>';
			$output .= '</div>';
			$output .= "\n\t\t\t" . '</div>';

		}

		return $output;
	}

	/**
	 * Display plots
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param string $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_plot( Title $movie, string $item_name ): string {

		$plot = $movie->$item_name();
		$admin_max_plots = intval( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] );
		$nbtotalplots = count( $plot );

		// tested if the array contains data; if not, doesn't go further
		if ( $nbtotalplots === 0 ) {
			return '';
		}

		$output = $this->movie_layout->subtitle_item(
			esc_html( _n( 'Plot', 'Plots', $nbtotalplots, 'lumiere-movies' ) )
		);

		for ( $i = 0; ( $i < $nbtotalplots ) && ( $i < $admin_max_plots ); $i++ ) {

			/**
			 * Use links builder classes.
			 * Each one has its own class passed in $link_maker,
			 * according to which option the lumiere_select_link_maker() found in Frontend.
			 */
			$output .= $plot[ $i ]['plot'] !== null ? $this->link_maker->lumiere_movies_plot_details( $plot[ $i ]['plot'] ) : esc_html__( 'No plot found', 'lumiere-movies' );

			// add hr to every plot but the last.
			if ( $i < ( $nbtotalplots - 1 ) && $i < ( $admin_max_plots - 1 ) ) {
				$output .= "\n\t\t\t\t<hr>";
			}
		}

		return $output;
	}

	/**
	 * Display the credit link
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param string $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_source( Title $movie, string $item_name ): string {

		$get_mid = strlen( $movie->imdbid() ) > 0 ? strval( $movie->imdbid() ) : null;

		if ( $get_mid === null ) {
			return '';
		}

		$output = $this->movie_layout->subtitle_item(
			esc_html__( 'Source', 'lumiere-movies' )
		);

		/**
		 * Use links builder classes.
		 * Each one has its own class passed in $link_maker,
		 * according to which option the lumiere_select_link_maker() found in Frontend.
		 */
		$output .= $this->link_maker->lumiere_movies_source_details( $get_mid );

		return $output;
	}
}
