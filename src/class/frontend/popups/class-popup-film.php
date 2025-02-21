<?php declare( strict_types = 1 );
/**
 * Popup for movies
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version       2.1
 * @package lumiere-movies
 */

namespace Lumiere\Frontend\Popups;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! class_exists( 'Lumiere\Config\Settings' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Frontend\Popups\Head_Popups;
use Lumiere\Frontend\Popups\Popup_Basic;
use Lumiere\Tools\Validate_Get;
use Lumiere\Config\Get_Options;
use Imdb\Title;

/**
 * Display movie information in a popup
 * Bots are banned before getting popups
 *
 * @see \Lumiere\Popups\Popup_Select Redirect to here according to the query var 'popup' in URL
 * @see \Lumiere\Frontend\Popups\Head_Popups Modify the popup header, Parent class, Bot banishement
 * @since 4.3 is child class
 */
class Popup_Film extends Head_Popups implements Popup_Basic {

	/**
	 * The movie Title class instanciated with title
	 */
	private Title $movie_class;

	/**
	 * The movie Title
	 */
	private string $page_title;

	/**
	 * Constructor
	 */
	public function __construct() {

		// Edit metas tags in popups and various checks in Parent class.
		parent::__construct();

		/**
		 * Build the properties.
		 */
		$movie_id = $this->get_movieid( Validate_Get::sanitize_url( 'mid' ), Validate_Get::sanitize_url( 'film' ) );
		$this->movie_class = $this->get_title_class( $movie_id );
		$this->page_title = $this->get_title( null /** must pass something due to interface, but will with Movie class the title */ );

		/**
		 * Display title
		 * @since 4.3
		 */
		add_filter( 'document_title_parts', [ $this, 'edit_title' ] );
	}

	/**
	 * Edit the title of the page
	 *
	 * @param array<string, string> $title
	 * @phpstan-param array{title: string, page: string, tagline: string, site: string} $title
	 * @phpstan-return array{title: string, page: string, tagline: string, site: string}
	 */
	public function edit_title( array $title ): array {

		$new_title = strlen( $this->page_title ) > 0
			/* translators: %1s is a movie's title */
			? wp_sprintf( __( 'Informations about %1s', 'lumiere-movies' ), esc_html( $this->page_title ) ) . ' - Lumi&egrave;re movies'
			: __( 'Unknown - Lumière movies', 'lumiere-movies' );

		$title['title'] = $new_title;

		return $title;
	}

	/**
	 * Get the title of the page
	 *
	 * @param string|null $title Movie's name sanitized -- Here not in use, using Title imdb class to get the title
	 * @return string
	 * @since 4.0 lowercase, less cache used.
	 */
	public function get_title( ?string $title ): string {
		return ucfirst( $this->movie_class->title() );
	}

	/**
	 * Find the movie id of the film
	 * A movie id can be provided or just the movie's title
	 * If movie's title, do a IMDbphp query to get the ID
	 *
	 * @param string|null $movie_id
	 * @param string|null $movie_title
	 * @return string The movie's ID
	 */
	private function get_movieid( ?string $movie_id, ?string $movie_title ): string {

		$final_movie_id = null;

		// A movie imdb id is provided in URL.
		if ( isset( $movie_id ) && strlen( $movie_id ) > 0 ) {

			$this->logger->log->debug( '[Popup_Movie] Movie id provided in URL: ' . esc_html( $movie_id ) );

			$final_movie_id = $movie_id;

			// No movie id is provided, but a title was.
		} elseif ( isset( $movie_title ) && strlen( $movie_title ) > 0 ) {

			$this->logger->log->debug( '[Popup_Movie] Movie title provided in URL: ' . esc_html( $movie_title ) );

			// Search the movie's ID according to the title.
			$search = $this->plugins_classes_active['imdbphp']->search_movie_title(
				esc_html( $movie_title ),
				$this->logger->log,
			);

			// Keep the first occurrence.
			$final_movie_id = isset( $search[0] ) ? esc_html( $search[0]['imdbid'] ) : null;
		}

		// Exit if no movie was found.
		if ( $final_movie_id === null ) {
			status_header( 404 );
			$text = __( 'Could not find any IMDb movie with this query.', 'lumiere-movies' );
			$this->logger->log->error( '[Popup_Movie] ' . esc_html( $text ) );
			wp_die( esc_html( $text ) );
		}

		return $final_movie_id;
	}

	/**
	 * Search movie id or title
	 *
	 * @return Title The title or null
	 */
	private function get_title_class( string $movieid ): Title {
		return $this->plugins_classes_active['imdbphp']->get_title_class( $movieid, $this->logger->log );
	}

	/**
	 * Display layout
	 *
	 * @return void
	 */
	public function get_layout(): void {

		echo "<!DOCTYPE html>\n<html>\n<head>\n";
		wp_head();
		echo "\n</head>\n<body class=\"lum_body_popup";
		echo isset( $this->imdb_admin_values['imdbpopuptheme'] ) ? ' lum_body_popup_' . esc_attr( $this->imdb_admin_values['imdbpopuptheme'] ) . '">' : '">';

		/**
		 * Display a spinner when clicking a link with class .lum_add_spinner (a <div class="loader"> will be inserted inside by the js)
		 */
		echo '<div id="spinner-placeholder"></div>';

		$this->logger->log->debug( '[Popup_Movie] Using the link maker class: ' . str_replace( 'Lumiere\Link_Makers\\', '', get_class( $this->link_maker ) ) );

		$this->display_menu( $this->movie_class, $this->page_title );

		$this->display_portrait( $this->movie_class );

		// Introduction part.
		// Display something when nothing has been selected in the menu.
		$get_info = Validate_Get::sanitize_url( 'info' );
		if ( $get_info === null || strlen( $get_info ) === 0 ) {
			$this->display_intro( $this->movie_class );
		}

		// Casting part.
		if ( $get_info === 'actors' ) {
			$this->display_casting( $this->movie_class );
		}

		// Crew part.
		if ( $get_info === 'crew' ) {
			$this->display_crew( $this->movie_class );
		}

		// Resume part.
		if ( $get_info === 'resume' ) {
			$this->display_summary( $this->movie_class );
		}

		// Misc part.
		if ( $get_info === 'divers' ) {
			$this->display_misc( $this->movie_class );
		}

		// The end.
		wp_meta();
		wp_footer();
		echo "</body>\n</html>";
	}

	/**
	 * Show the menu
	 */
	private function display_menu( Title $movie_class, string $film_title_sanitized ): void {
		// If polylang plugin is active, rewrite the URL to append the lang string
		$url_if_polylang = apply_filters( 'lum_polylang_rewrite_url_with_lang', Get_Options::get_popup_url( 'film', site_url() ) );
		$url_if_polylang_search = apply_filters( 'lum_polylang_rewrite_url_with_lang', Get_Options::get_popup_url( 'movie_search', site_url() ) );
		?>
					<!-- top page menu -->

		<div class="lumiere_container lumiere_font_em_11 lum_popup_titlemenu">
			<div class="lumiere_flex_auto">
				&nbsp;<a rel="nofollow" id="searchaka" class="lum_popup_menu_title lum_add_spinner" href="<?php echo esc_url( wp_nonce_url( $url_if_polylang_search . '?film=' . $film_title_sanitized ) ); ?>" title="<?php esc_html_e( 'Search for other movies with the same title', 'lumiere-movies' ); ?>"><?php esc_html_e( 'Similar Titles', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				&nbsp;<a rel="nofollow" class="lum_popup_menu_title lum_add_spinner" href="<?php echo esc_url( wp_nonce_url( $url_if_polylang . '?mid=' . $movie_class->imdbid() . '&film=' . $film_title_sanitized . '&info=' ) ); ?>" title='<?php echo esc_attr( $movie_class->title() ) . ': ' . esc_html__( 'Movie', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Summary', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				&nbsp;<a rel="nofollow" class="lum_popup_menu_title lum_add_spinner" href="<?php echo esc_url( wp_nonce_url( $url_if_polylang . '?mid=' . $movie_class->imdbid() . '&film=' . $film_title_sanitized . '&info=actors' ) ); ?>" title='<?php echo esc_attr( $movie_class->title() ) . ': ' . esc_html__( 'Actors', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Actors', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				&nbsp;<a rel="nofollow" class="lum_popup_menu_title lum_add_spinner" href="<?php echo esc_url( wp_nonce_url( $url_if_polylang . '?mid=' . $movie_class->imdbid() . '&film=' . $film_title_sanitized . '&info=crew' ) ); ?>" title='<?php echo esc_attr( $movie_class->title() ) . ': ' . esc_html__( 'Crew', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Crew', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				&nbsp;<a rel="nofollow" class="lum_popup_menu_title lum_add_spinner" href="<?php echo esc_url( wp_nonce_url( $url_if_polylang . '?mid=' . $movie_class->imdbid() . '&film=' . $film_title_sanitized . '&info=resume' ) ); ?>" title='<?php echo esc_attr( $movie_class->title() ) . ': ' . esc_html__( 'Plots', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Plots', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				&nbsp;<a rel="nofollow" class="lum_popup_menu_title lum_add_spinner" href="<?php echo esc_url( wp_nonce_url( $url_if_polylang . '?mid=' . $movie_class->imdbid() . '&film=' . $film_title_sanitized . '&info=divers' ) ); ?>" title='<?php echo esc_attr( $movie_class->title() ) . ': ' . esc_html__( 'Misc', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Misc', 'lumiere-movies' ); ?></a>
			</div>
		</div>
		<?php
	}

	/**
	 * Show the portrait (title, picture)
	 */
	public function display_portrait( Title $movie_class ): void {
		?>
		<div class="lumiere_display_flex lumiere_font_em_11 lumiere_align_center lum_padding_bott_2vh lum_padding_top_6vh">
			<div class="lumiere_flex_auto lum_width_fit_cont">
				<div class="titrefilm">
				<?php
					// Get movie's title from imdbphp query, not from globals.
					echo esc_html( $movie_class->title() );
				?>
				&nbsp;(<?php echo $movie_class->year() > 0 ? esc_html( $movie_class->year() ) : esc_html__( 'year unknown', 'lumiere-movies' ); ?>)</div>
				<div class="lumiere_align_center"><font size="-1"><?php
				$taglines = $movie_class->tagline();
				if ( array_key_exists( 0, $taglines ) ) {
					echo esc_html( $taglines[0] );
				}
				?></font></div>
			</div> 
												<!-- Movie's picture display -->
			<div class="lumiere_width_20_perc lumiere_padding_two lum_popup_img">
			<?php
				// Select pictures: big poster, if not small poster, if not 'no picture'.
				$photo_url = '';
				$photo_big = (string) $movie_class->photoLocalurl( false );
				$photo_thumb = (string) $movie_class->photoLocalurl( true );

			if ( $this->imdb_cache_values['imdbusecache'] === '1' ) { // use IMDBphp only if cache is active
				$photo_url = strlen( $photo_big ) > 1 ? esc_html( $photo_big ) : esc_html( $photo_thumb ); // create big picture, thumbnail otherwise.
			}

				// Picture for a href, takes big/thumbnail picture if exists, no_pics otherwise.
				$photo_url_href = strlen( $photo_url ) === 0 ? Get_Options::LUM_PICS_URL . 'no_pics.gif' : $photo_url;

				// Picture for img: if 1/ thumbnail picture exists, use it, 2/ use no_pics otherwise
				$photo_url_img = strlen( $photo_thumb ) === 0 ? esc_url( Get_Options::LUM_PICS_URL . 'no_pics.gif' ) : $photo_thumb;

				echo '<a class="lum_pic_inpopup" href="' . esc_url( $photo_url_href ) . '">';
				// loading="eager" to prevent WordPress loading lazy that doesn't go well with cache scripts.
				echo "\n\t\t" . '<img loading="lazy" src="' . esc_url( $photo_url_img ) . '" alt="' . esc_attr( $movie_class->title() ) . '"';

				// add width only if "Display only thumbnail" is not active.
			if ( $this->imdb_admin_values['imdbcoversize'] === '0' ) {
				$width = intval( $this->imdb_admin_values['imdbcoversizewidth'] );
				$height = $width * 1.4;
				echo ' width="' . esc_attr( strval( $width ) ) . '" height="' . esc_attr( strval( $height ) ) . '"';

				// set width to 100px width if "Display only thumbnail" is active.
			} elseif ( $this->imdb_admin_values['imdbcoversize'] === '1' ) {

				echo ' height="160" width="100"';

			}

			echo ' />';
			echo "\n\t\t\t\t</a>";
			?>

			</div> 
		</div> 
		<?php
	}

	/**
	 * Show intro part
	 */
	private function display_intro( Title $movie_class ): void {

		// Director summary, limited by admin options.
		$director = $movie_class->director();

		// director shown only if selected so in options.
		if ( count( $director ) !== 0 ) {

			$nbtotaldirector = count( $director );
			echo "\n\t\t\t\t\t\t\t\t\t\t<!-- Director -->";
			echo "\n\t<div>";

			echo '<span class="lum_results_section_subtitle">'
			. esc_html( _n( 'Director', 'Directors', $nbtotaldirector, 'lumiere-movies' ) )
			. '</span>';
			for ( $i = 0; $i < $nbtotaldirector; $i++ ) {

				echo '<a rel="nofollow" class="lum_popup_internal_link lum_add_spinner" href="'
					. esc_url(
						wp_nonce_url( Get_Options::get_popup_url( 'person', site_url() ) . '?mid=' . $director[ $i ]['imdb'] )
					)
					. '" title="' . esc_html__( 'internal link', 'lumiere-movies' ) . '">';
				echo "\n\t\t\t" . esc_html( $director[ $i ]['name'] );
				if ( $i < $nbtotaldirector - 1 ) {
					echo ', ';
				}

				echo '</a>';

			}
			echo "\n\t</div>";
		}

		// Main actors, limited by admin options.
		$cast = $movie_class->cast();
		$nbtotalactors = count( $cast );

		// actor shown only if selected so in options.
		if ( $nbtotalactors !== 0 ) {

			echo "\n\t\t\t\t\t\t\t\t\t\t<!-- Main actors -->";
			echo "\n\t<div>";

			echo '<span class="lum_results_section_subtitle">' . esc_html__( 'Main actors', 'lumiere-movies' ) . '</span>';

			for ( $i = 0; $i < $nbtotalactors; $i++ ) {
				echo '<a rel="nofollow" class="lum_popup_internal_link lum_add_spinner" href="' . esc_url( wp_nonce_url( Get_Options::get_popup_url( 'person', site_url() ) . '?mid=' . $cast[ $i ]['imdb'] ) ) . '" title="' . esc_html__( 'internal link', 'lumiere-movies' ) . '">';
				echo "\n\t\t\t" . esc_html( $cast[ $i ]['name'] ) . '</a>';

				if ( $i < $nbtotalactors - 1 ) {
					echo ', ';
				}
			}
			echo '</div>';
		}

		// Runtime, limited by admin options.
		$runtime = $movie_class->runtime();
		$runtime = isset( $runtime[0]['time'] ) ? esc_html( strval( $runtime[0]['time'] ) ) : '';

		// Runtime shown only if selected so in admin options.
		if ( strlen( $runtime ) > 0 ) {

			echo "\n\t\t\t\t\t\t\t\t\t\t<!-- Runtime -->";
			echo "\n\t<div>";
			echo '<span class="lum_results_section_subtitle">'
			. esc_html__( 'Runtime', 'lumiere-movies' )
			. '</span>'
			. esc_html( $runtime )
			. ' '
			. esc_html__( 'minutes', 'lumiere-movies' );
			echo "\n\t</div>";
		}

		// Votes, limited by admin options.
		// rating shown only if selected so in options.
		$votes_sanitized = intval( $movie_class->votes() );
		$rating_int = intval( $movie_class->rating() );
		$rating_string = strval( $movie_class->rating() );

		if ( strlen( $rating_string ) > 0 ) {

			echo "\n\t\t\t\t\t\t\t\t\t\t<!-- Rating -->";
			echo "\n\t<div>";

			echo '<span class="lum_results_section_subtitle">'
				. esc_html__( 'Rating', 'lumiere-movies' )
				. '</span>';
			echo ' <img class="imdbelementRATING-picture" src="' . esc_url( Get_Options::LUM_PICS_SHOWTIMES_URL . ( round( $rating_int * 2, 0 ) / 0.2 ) . '.gif' ) . '"'
			. ' title="' . esc_html__( 'vote average ', 'lumiere-movies' ) . esc_attr( $rating_string ) . esc_html__( ' out of 10', 'lumiere-movies' ) . '"  width="102" height="12" / >';
			echo ' (' . number_format( $votes_sanitized, 0, '', "'" ) . ' ' . esc_html__( 'votes', 'lumiere-movies' ) . ')';

			echo "\n\t</div>";
		}

		// Language, limited by admin options.
		$languages = $movie_class->language();
		$nbtotallanguages = count( $languages );

		// language shown only if selected so in options.
		if ( $nbtotallanguages > 0 ) {

			echo "\n\t\t\t\t\t\t\t<!-- Language -->";
			echo "\n\t<div>";

			echo '<span class="lum_results_section_subtitle">'
			. esc_attr( _n( 'Language', 'Languages', $nbtotallanguages, 'lumiere-movies' ) )
			. '</span>';
			for ( $i = 0; $i < $nbtotallanguages; $i++ ) {
				echo esc_html( $languages[ $i ] );
				if ( $i < $nbtotallanguages - 1 ) {
					echo ', ';
				}
			}

			echo "\n\t</div>";

		}

		// Country, limited by admin options.
		$country = $movie_class->country();
		$nbtotalcountry = count( $country );

		// country shown only if selected so in options.
		if ( $nbtotalcountry > 0 ) {

			echo "\n\t\t\t\t\t\t\t\t\t\t<!-- Country -->";
			echo "\n\t<div>";

			echo '<span class="lum_results_section_subtitle">'
			. esc_attr( _n( 'Country', 'Countries', $nbtotalcountry, 'lumiere-movies' ) )
			. '</span>';
			for ( $i = 0; $i < $nbtotalcountry; $i++ ) {

				echo esc_html( $country[ $i ] );

				if ( $i < $nbtotalcountry - 1 ) {
					echo ', ';
				}
			}

			echo "\n\t</div>";

		}

		$genre = $movie_class->genre();
		$nbtotalgenre = count( $genre );

		// Genre shown only if selected so in options.
		if ( $nbtotalgenre > 0 ) {

			echo "\n\t\t\t\t\t\t\t\t\t\t<!-- Genre -->";
			echo "\n\t<div>";

			echo '<span class="lum_results_section_subtitle">'
			. esc_attr( _n( 'Genre', 'Genres', $nbtotalgenre, 'lumiere-movies' ) )
			. '</span>';

			for ( $i = 0; $i < $nbtotalgenre; $i++ ) {
				echo isset( $genre[ $i ]['mainGenre'] ) ? esc_html( $genre[ $i ]['mainGenre'] ) : '';
				if ( $i < $nbtotalgenre - 1 ) {
					echo ', ';
				}
			}

			echo "\n\t</div>";
		}

	}

	/**
	 * Show misc part
	 */
	private function display_misc( Title $movie_class ): void {

		// Connected movies
		$connected_movies = $movie_class->connection();
		$admin_max_connected = isset( $this->imdb_data_values['imdbwidgetconnectionnumber'] ) ? intval( $this->imdb_data_values['imdbwidgetconnectionnumber'] ) : 0;
		$nbtotalconnected = count( $connected_movies );

		// count the actual results in values associative arrays
		$connected_movies_sub = array_filter( $connected_movies, fn( array $connected_movies ) => ( count( array_values( $connected_movies ) ) > 0 ) );
		$nbtotalconnected_sub = count( $connected_movies_sub );

		echo "\n\t\t\t\t\t\t\t" . ' <!-- Connected movies -->';
		echo "\n" . '<div id="lumiere_popup_plots_group">';
		echo "\n\t" . '<div class="lum_results_section_subtitle">' . esc_html( _n( 'Connected movie', 'Connected movies', $nbtotalconnected, 'lumiere-movies' ) ) . '</div>';

		if ( $nbtotalconnected === 0 || $nbtotalconnected_sub === 0 ) {
			esc_html_e( 'No connected movies found.', 'lumiere-movies' );
		}

		foreach ( Get_Options::get_list_connect_cat() as $category => $data_explain ) {
			// Total items for this category.
			$nb_items = count( $connected_movies[ $category ] );

			for ( $i = 0; $i < $admin_max_connected && $i < $nbtotalconnected; $i++ ) {
				if ( isset( $connected_movies[ $category ][ $i ]['titleId'] ) && $connected_movies[ $category ][ $i ]['titleName'] ) {

					if ( $i === 0 ) {
						echo '<strong>' . esc_html( $data_explain ) . '</strong>: ';
					}

					echo "\n\t\t\t\t"
						. '<a rel="nofollow" class="lum_popup_internal_link lum_add_spinner" href="'
						. esc_url(
							wp_nonce_url(
								Get_Options::get_popup_url( 'film', site_url() ) . '?mid=' . $connected_movies[ $category ][ $i ]['titleId']
							)
						)
						. '" title="' . esc_html( $connected_movies[ $category ][ $i ]['titleName'] ) . '">';
					echo "\n\t\t\t\t" . esc_html( $connected_movies[ $category ][ $i ]['titleName'] );
					echo '</a>';

					echo isset( $connected_movies[ $category ][ $i ]['description'] ) ? ' (' . esc_html( $connected_movies[ $category ][ $i ]['year'] ) . ') (<i>' . esc_html( $connected_movies[ $category ][ $i ]['description'] ) . '</i>)' : '';
					if ( $i < ( $admin_max_connected - 1 ) && $i < ( $nbtotalconnected ) && $i < ( $nb_items - 1 ) ) {
						echo ', '; // add comma to every connected movie but the last.
					}
					if ( $i === ( $admin_max_connected - 1 ) ) {
						echo '<br>';
					}
				}
			}
		}
		echo "\n\t</div>";
		echo "\n</div>";

		// Trivia.

		$trivia = $movie_class->trivia();
		$nbtotaltrivia = 0;
		$nb_total_trivia_processed = 1;

		// Get the real total number of trivias.
		foreach ( $trivia as $trivia_type => $trivia_content ) {
			$nbtotaltrivia += count( $trivia_content );
		}

		echo "\n\t\t\t\t\t\t\t" . ' <!-- Trivia -->';
		echo "\n" . '<div id="lumiere_popup_plots_group">';
		echo "\n\t" . '<div class="lum_results_section_subtitle">' . esc_html( _n( 'Trivia', 'Trivias', $nbtotaltrivia, 'lumiere-movies' ) ) . '</div>';

		if ( $nbtotaltrivia < 1 ) {
			esc_html_e( 'No trivias found.', 'lumiere-movies' );
		}

		for ( $i = 0; $i < $nbtotaltrivia; $i++ ) {
			foreach ( $trivia as $trivia_type => $trivia_content ) {
				$text = isset( $trivia_content[ $i ]['content'] ) ? $this->link_maker->lumiere_imdburl_to_internalurl( $trivia_content[ $i ]['content'] ) : '';

				// It may be empty, continue to the next result.
				if ( strlen( $text ) === 0 ) {
					continue;
				}

				echo "\n\t\t\t\t<div>\n\t\t\t\t\t" . '[#' . esc_html( strval( $nb_total_trivia_processed ) ) . '] <i>' . esc_html( $trivia_type )
					. '</i> ' . wp_kses(
						$text,
						[
							'a' => [
								'href' => [],
								'title' => [],
								'class' => [],
							],
						]
					);
				echo "\n\t\t\t\t</div>";

				if ( $nb_total_trivia_processed === 5 ) {
					$isset_next = isset( $trivia_content[ $i + 1 ] ) ? true : false;
					echo $isset_next === true ? "\n\t\t\t" . '<div class="activatehidesection lumiere_align_center"><strong>(' . esc_html__( 'click to show more trivias', 'lumiere-movies' ) . ')</strong></div>' . "\n\t\t\t<div class=\"hidesection\">" : '';

				}

				if ( $nb_total_trivia_processed > 2 && $nb_total_trivia_processed === $nbtotaltrivia ) {
					echo "\n\t\t</div>";
				}
				$nb_total_trivia_processed++;
			}
		}
		echo "\n\t</div>";
		echo "\n</div>";

		// Soundtrack.
		$soundtrack = $movie_class->soundtrack();
		$nbtotalsoundtracks = count( $soundtrack );

		echo "\n\t\t\t\t\t\t\t" . ' <!-- Soundtrack -->';
		echo "\n" . '<div id="lumiere_popup_sdntrck_group">';
		echo "\n\t" . '<div class="lum_results_section_subtitle">' . esc_html( _n( 'Soundtrack', 'Soundtracks', $nbtotalsoundtracks, 'lumiere-movies' ) ) . '</div>';

		if ( $nbtotalsoundtracks === 0 ) {
			esc_html_e( 'No soundtracks found.', 'lumiere-movies' );
		}

		for ( $i = 0; $i < $nbtotalsoundtracks; $i++ ) {

			$soundtrack_name = "\n\t\t\t" . esc_html( ucfirst( strtolower( $soundtrack[ $i ]['soundtrack'] ) ) );
			echo "\n\t\t\t" . wp_kses(
				/**
				* Use Highslide, Classical or No Links class links builder.
				* Each one has its own class passed in $link_maker,
				* according to which option the lumiere_select_link_maker() found in Frontend.
				*/
				$this->link_maker->lumiere_imdburl_to_internalurl( $soundtrack_name ),
				[
					'a' => [
						'href' => [],
						'title' => [],
						'class' => [],
					],
				]
			) . ' ';

			echo isset( $soundtrack[ $i ]['credits'][0] ) ? ' <i>' . esc_html( $soundtrack[ $i ]['credits'][0] ) . '</i>' : '';
			echo isset( $soundtrack[ $i ]['credits'][1] ) ? ' <i>' . esc_html( $soundtrack[ $i ]['credits'][1] ) . '</i>' : '';

			if ( $i < $nbtotalsoundtracks - 1 ) {
				echo ', ';
			}

			if ( $i === 4 ) {
				$isset_next = isset( $soundtrack[ $i + 1 ] ) ? true : false;
				echo $isset_next === true ? "\n\t\t" . '<div class="activatehidesection lumiere_align_center"><strong>(' . esc_html__( 'click to show more soundtracks', 'lumiere-movies' ) . ')</strong></div>' . "\n\t\t<div class=\"hidesection\">" : '';

			}

			if ( $i > 2 && $i === $nbtotalsoundtracks ) {
				echo "\n\t\t</div>";
			}

		}

		echo "\n</div>";

		// Goof.
		$goof = $movie_class->goof();
		$filter_nbtotalgoof = array_filter( $goof, fn( $goof ) => ( count( array_values( $goof ) ) > 0 ) ); // counts the actual goofs, not their categories
		$nbtotalgoof = count( $filter_nbtotalgoof );
		$overall_loop = 1;

		// Build all types of goofs by making an array.
		$goof_type = [];
		foreach ( $goof as $type => $info ) {
			$goof_type[] = $type;
		}

		echo "\n\t\t\t\t\t\t\t" . ' <!-- Goofs -->';
		echo "\n" . '<div id="lumiere_popup_goofs_group">';
		echo "\n\t" . '<div class="lum_results_section_subtitle">' . esc_html( _n( 'Goof', 'Goofs', $nbtotalgoof, 'lumiere-movies' ) ) . '</div>';

		if ( $nbtotalgoof === 0 ) {
			esc_html_e( 'No goofs found.', 'lumiere-movies' );
		}

		// Process goof type after goof type
		foreach ( $goof_type as $type ) {
			// Loop conditions: less than the total number of goofs available AND less than the goof limit setting, using a loop counter.
			for ( $i = 0; ( $i < $nbtotalgoof ) && ( $overall_loop <= $nbtotalgoof ); $i++ ) {
				if ( isset( $goof[ $type ][ $i ]['content'] ) && strlen( $goof[ $type ][ $i ]['content'] ) > 0 ) {
					$text_final_edited = preg_replace( '/\B([A-Z])/', '&nbsp;$1', $type ); // type is agglutinated, add space before capital letters.
					if ( ! isset( $text_final_edited ) ) {
						continue;
					}
					/** @psalm-suppress PossiblyInvalidArgument (Argument 1 of strtolower expects string, but possibly different type array<array-key, string>|string provided -- according to PHPStan, always string) */
					echo "\n\t\t\t<div>\n\t\t\t\t[#" . esc_html( strval( $overall_loop ) ) . '] <i>' . esc_html( strtolower( $text_final_edited ) ) . '</i>&nbsp;';
					echo wp_kses(
						$this->link_maker->lumiere_imdburl_to_internalurl( $goof[ $type ][ $i ]['content'] ),
						[
							'a' => [
								'href' => [],
								'title' => [],
								'class' => [],
							],
						]
					);

					echo "\n\t\t\t" . '</div>';
				}

				if ( $overall_loop === 4 ) {
					$isset_next = isset( $goof[ $type ][ $i + 1 ] ) ? true : false;
					echo $isset_next === true ? "\n\t\t" . '<div class="activatehidesection lumiere_align_center"><strong>(' . esc_html__( 'click to show more goofs', 'lumiere-movies' ) . ')</strong></div>' . "\n\t\t" . '<div class="hidesection">' : '';
				}

				if ( $overall_loop > 4 && $overall_loop === $nbtotalgoof ) {
					echo "\n\t\t</div>";
				}

				$overall_loop++; // this loop counts the exact goof number processed
			}
		}
		echo "\n\t</div>";
		echo "\n</div>";
	}

	/**
	 * Show casting.
	 */
	private function display_casting( Title $movie_class ): void {

		// Actors.
		$cast = $movie_class->cast();
		$nbtotalactors = count( $cast );

		if ( count( $cast ) > 0 ) {

			echo "\n\t\t\t\t\t\t\t\t\t\t<!-- Actors -->";
			echo "\n\t" . '<div class="lum_results_section_subtitle">' . esc_html( _n( 'Actor', 'Actors', $nbtotalactors, 'lumiere-movies' ) ) . '</div>';

			for ( $i = 0; ( $i < $nbtotalactors ); $i++ ) {
				echo "\n\t\t" . '<div align="center" class="lumiere_container">';
				echo "\n\t\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';
				echo isset( $cast[ $i ]['character'][0] ) ? esc_html( $cast[ $i ]['character'][0] ) : '<i>' . esc_html__( 'role unknown', 'lumiere-movies' ) . '</i>';
				echo '</div>';
				echo "\n\t\t\t" . '<div class="lumiere_align_right lumiere_flex_auto">';
				echo "\n\t\t\t\t"
					. '<a rel="nofollow" class="lum_popup_internal_link lum_add_spinner" href="'
					. esc_url(
						wp_nonce_url(
							Get_Options::get_popup_url( 'person', site_url() ) . $cast[ $i ]['imdb'] . '/?mid=' . $cast[ $i ]['imdb']
						)
					)
					. '" title="'
					. esc_html__( 'internal link', 'lumiere-movies' )
					. ' ' . esc_html( $cast[ $i ]['name'] )
					. '">';
				echo "\n\t\t\t\t" . esc_html( $cast[ $i ]['name'] );
				echo '</a>';
				echo "\n\t\t\t</div>";
				echo "\n\t\t</div>";
				echo "\n\t</div>";

			}
		}
	}

	/**
	 * Show crew.
	 */
	private function display_crew( Title $movie_class ): void {

		// Directors.
		$director = $movie_class->director();
		$nbtotaldirector = count( $director );

		if ( $nbtotaldirector > 0 ) {

			echo "\n\t\t\t\t\t\t\t" . ' <!-- director -->';
			echo "\n" . '<div id="lumiere_popup_director_group">';
			echo "\n\t" . '<span class="lum_results_section_subtitle">' . esc_html( _n( 'Director', 'Directors', $nbtotaldirector, 'lumiere-movies' ) ) . '</span>';

			for ( $i = 0; $i < $nbtotaldirector; $i++ ) {
				echo "\n\t" . '<div align="center" class="lumiere_container">';
				echo "\n\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';
				echo "\n\t\t"
				. '<a rel="nofollow" class="lum_popup_internal_link lum_add_spinner" href="'
				. esc_url(
					wp_nonce_url(
						Get_Options::get_popup_url( 'person', site_url() ) . $director[ $i ]['imdb'] . '/?mid=' . $director[ $i ]['imdb']
					)
				)
					. '" title="'
					. esc_html__( 'internal link', 'lumiere-movies' )
					. '">';

				echo "\n\t\t" . esc_html( $director[ $i ]['name'] );
				echo "\n\t\t</a>";
				echo "\n\t\t</div>";
				echo "\n\t\t" . '<div class="lumiere_align_right lumiere_flex_auto">';
				// echo isset( $director[ $i ]['jobs'][0] ) ? esc_html( $director[ $i ]['jobs'][0] ) : ''; // Nothing such a role for a director
				echo "\n\t\t" . '</div>';
				echo "\n\t</div>";
				echo "\n</div>";

			}

		}

		// Writers.
		$writer = $movie_class->writer();
		$nbtotalwriter = count( $writer );

		if ( $nbtotalwriter > 0 ) {

			echo "\n\t\t\t\t\t\t\t" . ' <!-- writers -->';
			echo "\n" . '<div id="lumiere_popup_director_group">';
			echo "\n\t" . '<span class="lum_results_section_subtitle">' . esc_html( _n( 'Writer', 'Writers', $nbtotalwriter, 'lumiere-movies' ) ) . '</span>';

			for ( $i = 0; $i < $nbtotalwriter; $i++ ) {
				echo "\n\t" . '<div align="center" class="lumiere_container">';
				echo "\n\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';
				echo "\n\t\t"
				. '<a rel="nofollow" class="lum_popup_internal_link lum_add_spinner" href="'
				. esc_url(
					wp_nonce_url(
						Get_Options::get_popup_url( 'person', site_url() ) . $writer[ $i ]['imdb'] . '/?mid=' . $writer[ $i ]['imdb']
					)
				)
					. '" title="'
					. esc_html__( 'internal link', 'lumiere-movies' )
					. '">';
				echo "\n\t\t" . esc_html( $writer[ $i ]['name'] );
				echo "\n\t\t</a>";
				echo "\n\t\t</div>";
				echo "\n\t\t" . '<div class="lumiere_align_right lumiere_flex_auto">';
				echo isset( $writer[ $i ]['jobs'][0] ) ? esc_html( $writer[ $i ]['jobs'][0] ) : '';
				echo "\n\t\t" . '</div>';
				echo "\n\t</div>";
				echo "\n</div>";
			}
		}

		// Producers.
		$producer = $movie_class->producer();
		$nbtotalproducer = count( $producer );

		if ( $nbtotalproducer > 0 ) {

			echo "\n\t\t\t\t\t\t\t" . ' <!-- writers -->';
			echo "\n" . '<div id="lumiere_popup_writer_group">';
			echo "\n\t" . '<span class="lum_results_section_subtitle">' . esc_html( _n( 'Producer', 'Producers', $nbtotalproducer, 'lumiere-movies' ) ) . '</span>';

			for ( $i = 0; $i < $nbtotalproducer; $i++ ) {
				echo "\n\t" . '<div align="center" class="lumiere_container">';
				echo "\n\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';
				echo "\n\t\t"
				. '<a rel="nofollow" class="lum_popup_internal_link lum_add_spinner" href="'
				. esc_url(
					wp_nonce_url(
						Get_Options::get_popup_url( 'person', site_url() ) . $producer[ $i ]['imdb'] . '/?mid=' . $producer[ $i ]['imdb']
					)
				)
					. '" title="'
					. esc_html__( 'internal link', 'lumiere-movies' )
					. '">';
				echo "\n\t\t" . esc_html( $producer[ $i ]['name'] );
				echo "\n\t\t</a>";
				echo "\n\t\t</div>";
				echo "\n\t\t" . '<div class="lumiere_align_right lumiere_flex_auto">';
				echo isset( $producer[ $i ]['jobs'][0] ) ? esc_html( $producer[ $i ]['jobs'][0] ) : '';
				echo "\n\t\t" . '</div>';
				echo "\n\t</div>";
				echo "\n</div>";
			}
		}
	}

	/**
	 * Show summary.
	 */
	private function display_summary( Title $movie_class ): void {

		// Plots.
		$plot = $movie_class->plot();
		$nbtotalplot = count( $plot );

		echo "\n\t\t\t\t\t\t\t" . ' <!-- Plots -->';
		echo "\n" . '<div id="lumiere_popup_pluts_group">';
		echo "\n\t" . '<span class="lum_results_section_subtitle">' . esc_html( _n( 'Plot', 'Plots', $nbtotalplot, 'lumiere-movies' ) ) . '</span>';

		if ( $nbtotalplot < 1 ) {
			esc_html_e( 'No plot found', 'lumiere-movies' );
		}

		for ( $i = 0; $i < $nbtotalplot; $i++ ) {
			echo "\n\t" . '<div>';
			echo ' [#' . esc_html( strval( $i + 1 ) ) . '] ' . esc_html( $plot[ $i ]['plot'] );
			if ( $i < $nbtotalplot - 1 ) {
				echo "\n<br>";
			}
			echo "\n\t</div>";
		}

		echo "\n</div>";
	}
}
