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
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Imdb\Title;
use Lumiere\Config\Get_Options;
use Lumiere\Frontend\Main;
use Lumiere\Frontend\Layout\Output;

/**
 * Those methods are utilised by class Movie to display the sections
 * The class uses \Lumiere\Link_Makers\Link_Factory to automatically select the appropriate Link maker class to display data ( i.e. Classic links, Highslide/Bootstrap, No Links, AMP)
 * It uses ImdbPHP Classes to display movies/people data
 * It uses Layout defined in Output
 * It uses taxonomy functions in Movie_Taxonomy
 * It is extended by Movie_Display, child class
 *
 * @since 4.0 new class, methods were extracted from Movie_Display class
 */
class Movie_Factory {

	/**
	 * Traits
	 */
	use Main;

	public function __construct(
		protected Output $output_class = new Output(),
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
	 * @param 'title' $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_title( Title $movie, string $item_name ): string {

		$class_name = '\Lumiere\Frontend\Module\Movie_' . ucfirst( $item_name );

		if ( class_exists( $class_name ) === false ) {
			return '';
		}

		$module = new $class_name();
		return $module->get_module( $movie, $item_name );
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
	 * @param 'country' $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_country( Title $movie, string $item_name ): string {

		$class_name = '\Lumiere\Frontend\Module\Movie_' . ucfirst( $item_name );

		if ( class_exists( $class_name ) === false ) {
			return '';
		}

		$module = new $class_name();

		// Taxonomy is active.
		if ( $this->imdb_admin_values['imdbtaxonomy'] === '1' && isset( $this->imdb_data_values[ 'imdbtaxonomy' . $item_name ] ) && $this->imdb_data_values[ 'imdbtaxonomy' . $item_name ] === '1' ) {
			return $module->get_module_taxo( $movie, $item_name );
		}

		return $module->get_module( $movie, $item_name );
	}

	/**
	 * Display the runtime
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'runtime' $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_runtime( Title $movie, string $item_name ): string {

		$class_name = '\Lumiere\Frontend\Module\Movie_' . ucfirst( $item_name );

		if ( class_exists( $class_name ) === false ) {
			return '';
		}

		$module = new $class_name();

		return $module->get_module( $movie, $item_name );
	}

	/**
	 * Display the language
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'language' $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_language( Title $movie, string $item_name ): string {

		$class_name = '\Lumiere\Frontend\Module\Movie_' . ucfirst( $item_name );

		if ( class_exists( $class_name ) === false ) {
			return '';
		}

		$module = new $class_name();

		// Taxonomy is active.
		if ( $this->imdb_admin_values['imdbtaxonomy'] === '1' && isset( $this->imdb_data_values[ 'imdbtaxonomy' . $item_name ] ) && $this->imdb_data_values[ 'imdbtaxonomy' . $item_name ] === '1' ) {
			return $module->get_module_taxo( $movie, $item_name );
		}

		return $module->get_module( $movie, $item_name );

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

		// count the actual results in values associative arrays
		$connected_movies_sub = array_filter( $connected_movies, fn( array $connected_movies ) => ( count( array_values( $connected_movies ) ) > 0 ) );
		$nbtotalconnected_sub = count( $connected_movies_sub );

		if ( $nbtotalconnected === 0 || $nbtotalconnected_sub === 0 ) {
			return '';
		}

		$output = $this->output_class->subtitle_item(
			esc_html( ucfirst( Get_Options::get_all_fields( $nbtotalconnected )[ $item_name ] ) )
		);

		foreach ( Get_Options::define_list_connect_cat() as $category => $data_explain ) {

			// Total items for this category.
			$nb_items_connected_movies = count( $connected_movies[ $category ] );

			for ( $i = 0; $i < $admin_max_connected; $i++ ) {
				if ( isset( $connected_movies[ $category ][ $i ]['titleId'] ) && $connected_movies[ $category ][ $i ]['titleName'] ) {

					if ( $i === 0 ) {
						$output .= '<br><span class="lum_results_section_subtitle_parent"><span class="lum_results_section_subtitle_subcat">' . $data_explain . '</span>: ';
					}

					$output .= '<span class="lum_results_section_subtitle_subcat_content">';

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
					if ( $i < ( $admin_max_connected - 1 ) && $i < $nbtotalconnected && $i < ( $nb_items_connected_movies - 1 ) ) {
						$output .= ', '; // add comma to every connected movie but the last.
					}
					$output .= '</span></span>';
				}
			}
		}
		return $output;
	}

	/**
	 * Fake method year
	 * For compatibility with Data settings that have a 'year' option
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param string $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_year( Title $movie, string $item_name ): string {
		return '';
	}

	/**
	 * Display the rating
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'rating' $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_rating( Title $movie, string $item_name ): string {

		$class_name = '\Lumiere\Frontend\Module\Movie_' . ucfirst( $item_name );

		if ( class_exists( $class_name ) === false ) {
			return '';
		}

		$module = new $class_name();

		return $module->get_module( $movie, $item_name );
	}

	/**
	 * Display the genre
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'genre' $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_genre( Title $movie, string $item_name ): string {

		$class_name = '\Lumiere\Frontend\Module\Movie_' . ucfirst( $item_name );

		if ( class_exists( $class_name ) === false ) {
			return '';
		}

		$module = new $class_name();

		// Taxonomy is active.
		if ( $this->imdb_admin_values['imdbtaxonomy'] === '1' && isset( $this->imdb_data_values[ 'imdbtaxonomy' . $item_name ] ) && $this->imdb_data_values[ 'imdbtaxonomy' . $item_name ] === '1' ) {
			return $module->get_module_taxo( $movie, $item_name );
		}

		return $module->get_module( $movie, $item_name );
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

		$total_displayed = $limit_keywords > $nbtotalkeywords ? $nbtotalkeywords : $limit_keywords;
		$output = $this->output_class->subtitle_item(
			esc_html( ucfirst( Get_Options::get_all_fields( $total_displayed )[ $item_name ] ) )
		);

		// Taxonomy is active.
		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_data_values[ 'imdbtaxonomy' . $item_name ] === '1' ) ) {

			for ( $i = 0; $i < $nbtotalkeywords && $i < $limit_keywords; $i++ ) {

				$get_taxo_options = $this->movie_taxo->create_taxonomy_options( $item_name, sanitize_text_field( $keywords[ $i ] ), $this->imdb_admin_values );
				$output .= $this->output_class->get_layout_items( esc_html( $movie->title() ), $get_taxo_options );

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

		// if no result, exit.
		if ( $nbtotalgoofs === 0 ) {
			return '';
		}

		$total_displayed = $admin_max_goofs > $nbtotalgoofs ? $nbtotalgoofs : $admin_max_goofs;
		$output = $this->output_class->subtitle_item(
			esc_html( ucfirst( Get_Options::get_all_fields( $total_displayed )[ $item_name ] ) )
		);

		// Process goof category
		foreach ( Get_Options::get_list_goofs_cat() as $category => $data_explain ) {

			// Loop conditions: less than the total number of goofs available AND less than the goof limit setting, using a loop counter.
			for ( $i = 0; $i < $admin_max_goofs; $i++ ) {
				if ( isset( $goofs[ $category ][ $i ]['content'] ) ) {
					if ( $i === 0 ) {
						$output .= '<br><span class="lum_results_section_subtitle_parent"><span class="lum_results_section_subtitle_subcat">' . $data_explain . '</span>: ';
					}

					if ( isset( $goofs[ $category ][ $i ]['content'] ) && strlen( $goofs[ $category ][ $i ]['content'] ) > 0 ) {
						$output .= "\n\t\t\t\t" . '<span class="lum_results_section_subtitle_subcat_content">' . esc_html( $goofs[ $category ][ $i ]['content'] ) . '</span>&nbsp;';
					}
					$output .= '</span>';
				}
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

		$quotes = $movie->$item_name(); // Merge the multidimensional array to two dimensions.
		$nbtotalquotes = count( $quotes );
		$admin_max_quotes = intval( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] );

		// If no result, exit.
		if ( $nbtotalquotes === 0 ) {
			return '';
		}

		$total_displayed = $admin_max_quotes > $nbtotalquotes ? $nbtotalquotes : $admin_max_quotes;
		$output = $this->output_class->subtitle_item(
			esc_html( ucfirst( Get_Options::get_all_fields( $total_displayed )[ $item_name ] ) )
		);

		for ( $i = 0; $i < $admin_max_quotes && ( $i < $nbtotalquotes ); $i++ ) {
			if ( is_array( $quotes[ $i ] ) ) {
				foreach ( $quotes[ $i ] as $sub_quote ) {
					$output .= str_starts_with( $sub_quote, '[' ) ? "\n\t\t\t" : "\n\t\t\t&laquo; ";
					$output .= esc_html( $sub_quote );
					$output .= str_ends_with( $sub_quote, ']' ) ? "\n\t\t\t" : "\n\t\t\t&raquo; ";
				}
				$output .= "\n\t\t\t\t<br>";
				continue;
			}
			$output .= "\n\t\t\t&laquo; " . esc_html( $quotes[ $i ] ) . ' &raquo; ';
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

		$total_displayed = $admin_max_taglines > $nbtotaltaglines ? $nbtotaltaglines : $admin_max_taglines;
		$output = $this->output_class->subtitle_item(
			esc_html( ucfirst( Get_Options::get_all_fields( $total_displayed )[ $item_name ] ) )
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

		$total_displayed = $admin_max_trailers > $nbtotaltrailers ? $nbtotaltrailers : $admin_max_trailers;
		$output = $this->output_class->subtitle_item(
			esc_html( ucfirst( Get_Options::get_all_fields( $total_displayed )[ $item_name ] ) )
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

		$output = $this->output_class->subtitle_item(
			esc_html( ucfirst( Get_Options::get_all_fields( $nbtotalcolors )[ $item_name ] ) )
		);

		// Taxonomy activated.
		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_data_values[ 'imdbtaxonomy' . $item_name ] === '1' ) ) {

			for ( $i = 0; $i < $nbtotalcolors; $i++ ) {

				$get_taxo_options = $this->movie_taxo->create_taxonomy_options( $item_name, sanitize_text_field( $colors[ $i ]['type'] ), $this->imdb_admin_values );
				$output .= $this->output_class->get_layout_items( esc_html( $movie->title() ), $get_taxo_options );

				if ( $i < $nbtotalcolors - 1 ) {
					$output .= ', ';
				}
			}
			return $output;
		}

		// No taxonomy.
		$count_colors = count( $colors );
		for ( $i = 0; $i < $count_colors; $i++ ) {

			/**
			 * Attributes are more specific than type, so take it first if it exists
			 * It may be an array with various row, but we keep the first only
			 * If found, do not bother searching for type in this iteration
			 */
			if ( isset( $colors[ $i ]['attributes'][0] ) ) {
				$output .= "\n\t\t\t" . sanitize_text_field( $colors[ $i ]['attributes'][0] );
				if ( $i < $nbtotalcolors - 1 ) {
					$output .= ', ';
				}
				continue;
			}

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
		if ( $nbtotalalsoknow < 2 ) { // Since the first result is the original title, must be greater than 1
			return '';
		}

		$output = $this->output_class->subtitle_item(
			esc_html( ucfirst( Get_Options::get_all_fields( /* no number because no plural here */ )[ $item_name ] ) )
		);

		for ( $i = 0; ( $i < $nbtotalalsoknow ) && ( $i < $admin_max_aka ); $i++ ) {

			// Original title, already using it in the box.
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

		$output = $this->output_class->subtitle_item(
			esc_html( ucfirst( Get_Options::get_all_fields( $nbtotalcomposer )[ $item_name ] ) )
		);

		// Taxonomy
		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_data_values[ 'imdbtaxonomy' . $item_name ] === '1' ) ) {

			for ( $i = 0; $i < $nbtotalcomposer; $i++ ) {

				$get_taxo_options = $this->movie_taxo->create_taxonomy_options( $item_name, esc_html( $composer[ $i ]['name'] ), $this->imdb_admin_values );
				$output .= $this->output_class->get_layout_items( esc_html( $movie->title() ), $get_taxo_options );

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

		$total_displayed = $admin_max_sndtrk > $nbtotalsoundtracks ? $nbtotalsoundtracks : $admin_max_sndtrk;
		$output = $this->output_class->subtitle_item(
			esc_html( ucfirst( Get_Options::get_all_fields( $total_displayed )[ $item_name ] ) )
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

		$output = $this->output_class->subtitle_item(
			esc_html( ucfirst( Get_Options::get_all_fields( $nbtotalprodcompany )[ $item_name ] ) )
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
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param string $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_extSites( Title $movie, string $item_name ): string { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid

		$get_external_sites = $movie->$item_name();
		$external_sites = $get_external_sites['official'] ?? $get_external_sites['misc'] ?? [];
		$nbtotalext_sites = count( $external_sites );
		$hardcoded_max_sites = 8;               /* max sites 8, so 7 displayed */

		// if no result, exit.
		if ( count( $external_sites ) === 0 ) {
			return '';
		}

		$total_displayed = $hardcoded_max_sites > $nbtotalext_sites ? $nbtotalext_sites : $hardcoded_max_sites;
		$output = $this->output_class->subtitle_item(
			esc_html( ucfirst( Get_Options::get_all_fields( $total_displayed )[ $item_name ] ) )
		);

		// Hardcoded 7 sites max.
		for ( $i = 0; $i < $nbtotalext_sites && $i < $hardcoded_max_sites; $i++  ) {

			/**
			 * Use links builder classes.
			 * Each one has its own class passed in $link_maker,
			 * according to which option the lumiere_select_link_maker() found in Frontend.
			 */
			$output .= $this->link_maker->lumiere_movies_officialsites_details(
				$external_sites[ $i ]['url'],
				$external_sites[ $i ]['label']
			);

			if ( $i < ( $nbtotalext_sites - 1 ) && $i < ( $hardcoded_max_sites - 1 ) ) {
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
	 * @param 'director' $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_director( Title $movie, string $item_name ): string {

		$class_name = '\Lumiere\Frontend\Module\Movie_' . ucfirst( $item_name );

		if ( class_exists( $class_name ) === false ) {
			return '';
		}

		$module = new $class_name();

		// Taxonomy is active.
		if ( $this->imdb_admin_values['imdbtaxonomy'] === '1' && isset( $this->imdb_data_values[ 'imdbtaxonomy' . $item_name ] ) && $this->imdb_data_values[ 'imdbtaxonomy' . $item_name ] === '1' ) {
			return $module->get_module_taxo( $movie, $item_name );
		}

		return $module->get_module( $movie, $item_name );
	}

	/**
	 * Display the cinematographer (directeur photo)
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param string $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_cinematographer( Title $movie, string $item_name ): string {

		$cinematographer = $movie->$item_name();
		$nbtotalcinematographer = count( $cinematographer );

		// if no results, exit.
		if ( $nbtotalcinematographer === 0 ) {
			return '';
		}

		$output = $this->output_class->subtitle_item(
			esc_html( ucfirst( Get_Options::get_all_fields( $nbtotalcinematographer )[ $item_name ] ) )
		);

		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) && ( $this->imdb_data_values[ 'imdbtaxonomy' . $item_name ] === '1' ) ) {

			for ( $i = 0; $i < $nbtotalcinematographer; $i++ ) {

				$get_taxo_options = $this->movie_taxo->create_taxonomy_options( 'cinematographer', esc_html( $cinematographer[ $i ]['name'] ), $this->imdb_admin_values );
				$output .= $this->output_class->get_layout_items( esc_html( $movie->title() ), $get_taxo_options );

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
	 * @param 'producer' $item_name The name of the item
	 */
	protected function get_item_producer( Title $movie, string $item_name ): string {

		$class_name = '\Lumiere\Frontend\Module\Movie_' . ucfirst( $item_name );

		if ( class_exists( $class_name ) === false ) {
			return '';
		}

		$module = new $class_name();

		// Taxonomy is active.
		if ( $this->imdb_admin_values['imdbtaxonomy'] === '1' && isset( $this->imdb_data_values[ 'imdbtaxonomy' . $item_name ] ) && $this->imdb_data_values[ 'imdbtaxonomy' . $item_name ] === '1' ) {
			return $module->get_module_taxo( $movie, $item_name );
		}

		return $module->get_module( $movie, $item_name );
	}

	/**
	 * Display the writers
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'writer' $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_writer( Title $movie, string $item_name ): string {

		$class_name = '\Lumiere\Frontend\Module\Movie_' . ucfirst( $item_name );

		if ( class_exists( $class_name ) === false ) {
			return '';
		}

		$module = new $class_name();

		// Taxonomy is active.
		if ( $this->imdb_admin_values['imdbtaxonomy'] === '1' && isset( $this->imdb_data_values[ 'imdbtaxonomy' . $item_name ] ) && $this->imdb_data_values[ 'imdbtaxonomy' . $item_name ] === '1' ) {
			return $module->get_module_taxo( $movie, $item_name );
		}

		return $module->get_module( $movie, $item_name );
	}

	/**
	 * Display actors
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'actor' $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_actor( Title $movie, string $item_name ): string {

		$class_name = '\Lumiere\Frontend\Module\Movie_' . ucfirst( $item_name );

		if ( class_exists( $class_name ) === false ) {
			return '';
		}

		$module = new $class_name();

		// Taxonomy is active.
		if ( $this->imdb_admin_values['imdbtaxonomy'] === '1' && isset( $this->imdb_data_values[ 'imdbtaxonomy' . $item_name ] ) && $this->imdb_data_values[ 'imdbtaxonomy' . $item_name ] === '1' ) {
			return $module->get_module_taxo( $movie, $item_name );
		}

		return $module->get_module( $movie, $item_name );
	}

	/**
	 * Display plots
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'plot' $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_plot( Title $movie, string $item_name ): string {

		$class_name = '\Lumiere\Frontend\Module\Movie_' . ucfirst( $item_name );

		if ( class_exists( $class_name ) === false ) {
			return '';
		}

		$module = new $class_name();

		return $module->get_module( $movie, $item_name );
	}

	/**
	 * Display the credit link
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'source' $item_name The name of the item, ie 'director', 'writer'
	 */
	protected function get_item_source( Title $movie, string $item_name ): string {

		$get_mid = strlen( $movie->imdbid() ) > 0 ? strval( $movie->imdbid() ) : null;

		if ( $get_mid === null ) {
			return '';
		}

		$output = $this->output_class->subtitle_item(
			esc_html( ucfirst( Get_Options::get_all_fields( /* no number because no plural here */ )[ $item_name ] ) )
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
