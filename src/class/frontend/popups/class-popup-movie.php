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
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	wp_die( esc_html__( 'Lumière Movies: You can not call directly this page', 'lumiere-movies' ) );
}

use Imdb\Title;
use Imdb\TitleSearch;
use Lumiere\Tools\Utils;
use Lumiere\Frontend\Popups\Head_Popups;
use Exception;

/**
 * Independant class that displays movie information in a popup
 * @see \Lumiere\Alteration\Rewrite_Rules that creates rules for creating a virtual page
 * @see \Lumiere\Alteration\Redirect_Virtual_Page that redirects to this page
 * @see \Lumiere\Frontend\Popups\Head_Popups that modifies the popup header
 */
class Popup_Movie {

	// Use trait frontend
	use \Lumiere\Frontend\Main {
		\Lumiere\Frontend\Main::__construct as public __constructFrontend;
	}

	/**
	 * The movie queried
	 */
	private Title $movie;

	/**
	 * Movie's id, if provided
	 */
	private ?string $movieid_sanitized;

	/**
	 * Movie's title, if provided
	 */
	private ?string $film_title_sanitized;

	/**
	 * Settings from class \Lumiere\Settings
	 * To include the type of (movie, TVshow, Games) search
	 * @var array<string> $type_search
	 */
	private array $type_search;

	/**
	 * Constructor
	 *
	 * @since 4.0.1 Extra bot banishment in Redirect_Virtual_Page class
	 * Bots are banned from getting popups
	 * @see \Lumiere\Alteration\Redirect_Virtual_Page::lumiere_popup_redirect_include Bot banishement happens in Redirect_Virtual_Page::ban_bots_popups()
	 * @see \Lumiere\Tools\Ban_Bots::_construct() The action 'lumiere_ban_bots_now' caled in Redirect_Virtual_Page
	 */
	public function __construct() {

		// Edit metas tags in popups.
		add_action( 'template_redirect', fn() => Head_Popups::lumiere_static_start() );

		// Construct Frontend trait.
		$this->__constructFrontend( 'popupMovie' );

		// Get the type of search: movies, series, games
		$this->type_search = $this->config_class->lumiere_select_type_search();

		// Remove admin bar
		add_filter( 'show_admin_bar', '__return_false' );

		/**
		 * Display layout
		 * @since 4.0 using 'the_posts', removed the 'get_header' for OceanWP
		 */
		add_action( 'the_posts', [ $this, 'lumiere_popup_movie_layout' ], 1 );

	}

	/**
	 * Static call of the current class Popup Movie
	 *
	 * @return void Build the class
	 */
	public static function lumiere_popup_movie_start (): void {
		$popup_movie_class = new self();
	}

	/**
	 * Search movie id or title
	 *
	 * @return bool True if a movie was found
	 */
	private function find_movie(): bool {

		do_action( 'lumiere_logger' );

		/* GET Vars sanitized */
		$this->movieid_sanitized = isset( $_GET['mid'] ) && strlen( $_GET['mid'] ) > 0 ? esc_html( $_GET['mid'] ) : null;
		$this->film_title_sanitized = isset( $_GET['film'] ) && strlen( $_GET['film'] ) > 0 ? esc_html( $_GET['film'] ) : null;

		// if neither film nor mid are set, throw Exception.
		if ( $this->movieid_sanitized === null && $this->film_title_sanitized === null ) {

			$text = '[Lumiere][popupMovieClass] Neither movie title nor id provided.';
			$this->logger->log()->error( $text );
			return false;

		}

		// A movie imdb id is provided in URL.
		if ( isset( $this->movieid_sanitized ) && strlen( $this->movieid_sanitized ) > 0 ) {

			$this->logger->log()->debug( '[Lumiere][popupMovieClass] Movie id provided in URL: ' . $this->movieid_sanitized );

			$this->movie = new Title( $this->movieid_sanitized, $this->imdbphp_class, $this->logger->log() );
			$movie = Utils::lumiere_name_htmlize( $this->movie->title() );
			$this->film_title_sanitized = $movie !== null ? strtolower( $movie ) : null; // @since 4.0 lowercase, less cache used.

			return true;

			// No movie id is provided, but a title was.
		} elseif ( isset( $this->film_title_sanitized ) && strlen( $this->film_title_sanitized ) > 0 ) {

			$this->logger->log()->debug( '[Lumiere][popupMovieClass] Movie title provided in URL: ' . $this->film_title_sanitized );

			$title_search_class = new TitleSearch( $this->imdbphp_class, $this->logger->log() );

			$search = $title_search_class->search( $this->film_title_sanitized, $this->type_search );
			if ( count( $search ) === 0 || array_key_exists( 0, $search ) === false ) {

				$text = '[Lumiere][popupMovieClass] Fatal error: Could not find the movie title: ' . $this->film_title_sanitized;
				$this->logger->log()->critical( $text );
				return false;
			}

			$this->movie = $search[0];

			return true;
		}

		return false;
	}

	/**
	 * Display layout
	 *
	 * @throws Exception if errors occurs when searching for the movie
	 */
	public function lumiere_popup_movie_layout(): void {

		?> class="lumiere_body<?php

		echo isset( $this->imdb_admin_values['imdbpopuptheme'] ) ? ' lumiere_body_' . esc_attr( $this->imdb_admin_values['imdbpopuptheme'] ) . '">' : '">';

		// Exit if no movie was found.
if ( $this->find_movie() === false ) {
	status_header( 404 );
	$text = 'Could not find any IMDb movie with this query.';
	$this->logger->log()->error( '[Lumiere][popupMovieClass] ' . $text );
	wp_die( esc_html( $text ) );
}

		// Display spinner circle
		echo '<div class="parent__spinner">';
		echo "\n\t" . '<div class="loading__spinner"></div>';
		echo '</div>';

		$movie_results = $this->movie;

		$this->logger->log()->debug( '[Lumiere][popupMovieClass] Using the link maker class: ' . str_replace( 'Lumiere\Link_Makers\\', '', get_class( $this->link_maker ) ) );

		$this->display_menu( $this->movie );

		$this->display_portrait( $this->movie );

		// Introduction part.
		// Display something when nothing has been selected in the menu.
if ( ( ! isset( $_GET['info'] ) ) || ( strlen( $_GET['info'] ) === 0 ) ) {
	$this->display_intro( $this->movie );
}

		// Casting part.
if ( ( isset( $_GET['info'] ) ) && ( $_GET['info'] === 'actors' ) ) {
	$this->display_casting( $this->movie );
}

		// Crew part.
if ( ( isset( $_GET['info'] ) ) && ( $_GET['info'] === 'crew' ) ) {
	$this->display_crew( $this->movie );
}

		// Resume part.
if ( ( isset( $_GET['info'] ) ) && ( $_GET['info'] === 'resume' ) ) {
	$this->display_summary( $this->movie );
}

		// Misc part.
if ( isset( $_GET['info'] ) && $_GET['info'] === 'divers' ) {
	$this->display_misc( $movie_results );
}

		echo '<br>';
		wp_meta();
		wp_footer();

?>
		</body>
		</html><?php

		exit(); // quit the page, to avoid loading the proper WordPress page

	}

	/**
	 * Show the menu
	 */
	private function display_menu( Title $movie_results ): void {
		// If polylang exists, rewrite the URL to append the lang string
		$url_if_polylang = $this->lumiere_url_check_polylang_rewrite( $this->config_class->lumiere_urlpopupsfilms );
		$url_if_polylang_search = $this->lumiere_url_check_polylang_rewrite( $this->config_class->lumiere_urlpopupsearch );
		?>
					<!-- top page menu -->

		<div class="lumiere_container lumiere_font_em_11 lumiere_titlemenu">
			<div class="lumiere_flex_auto">
				&nbsp;<a rel="nofollow" class="searchaka" href="<?php echo esc_url( $url_if_polylang_search . '/?film=' . $this->film_title_sanitized . '&norecursive=yes' ); ?>" title="<?php esc_html_e( 'Search for other movies with the same title', 'lumiere-movies' ); ?>"><?php esc_html_e( 'Similar Titles', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				&nbsp;<a rel="nofollow" class='linkpopup' href="<?php echo esc_url( $url_if_polylang . '/?mid=' . $movie_results->imdbid() . '&film=' . $this->film_title_sanitized . '&info=' ); ?>" title='<?php echo esc_attr( $movie_results->title() ) . ': ' . esc_html__( 'Movie', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Summary', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				&nbsp;<a rel="nofollow" class='linkpopup' href="<?php echo esc_url( $url_if_polylang . '/?mid=' . $movie_results->imdbid() . '&film=' . $this->film_title_sanitized . '&info=actors' ); ?>" title='<?php echo esc_attr( $movie_results->title() ) . ': ' . esc_html__( 'Actors', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Actors', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				&nbsp;<a rel="nofollow" class='linkpopup' href="<?php echo esc_url( $url_if_polylang . '/?mid=' . $movie_results->imdbid() . '&film=' . $this->film_title_sanitized . '&info=crew' ); ?>" title='<?php echo esc_attr( $movie_results->title() ) . ': ' . esc_html__( 'Crew', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Crew', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				&nbsp;<a rel="nofollow" class='linkpopup' href="<?php echo esc_url( $url_if_polylang . '/?mid=' . $movie_results->imdbid() . '&film=' . $this->film_title_sanitized . '&info=resume' ); ?>" title='<?php echo esc_attr( $movie_results->title() ) . ': ' . esc_html__( 'Plots', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Plots', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				&nbsp;<a rel="nofollow" class='linkpopup' href="<?php echo esc_url( $url_if_polylang . '/?mid=' . $movie_results->imdbid() . '&film=' . $this->film_title_sanitized . '&info=divers' ); ?>" title='<?php echo esc_attr( $movie_results->title() ) . ': ' . esc_html__( 'Misc', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Misc', 'lumiere-movies' ); ?></a>
			</div>
		</div>
		<?php
	}

	/**
	 * Show the portrait (title, picture)
	 */
	public function display_portrait( Title $movie_results ): void {
		?>
		<div class="lumiere_display_flex lumiere_font_em_11">
			<div class="lumiere_flex_auto lumiere_width_eighty_perc">
				<div class="titrefilm">
				<?php
					// Get movie's title from imdbphp query, not from globals.
					echo esc_html( $movie_results->title() );
				?>
				&nbsp;(<?php echo intval( $movie_results->year() ); ?>)</div>
				<div class="lumiere_align_center"><font size="-1"><?php
					$taglines = $movie_results->taglines();
				if ( array_key_exists( 0, $taglines ) ) {
					echo esc_html( $taglines[0] );
				}
				?></font></div>
			</div> 
			<div class="lumiere_flex_auto lumiere_width_twenty_perc lumiere_padding_two">

												<!-- Movie's picture display -->
			<?php
				// Select pictures: big poster, if not small poster, if not 'no picture'.
				$photo_url = '';
				$photo_big = (string) $movie_results->photo_localurl( false );
				$photo_thumb = (string) $movie_results->photo_localurl( true );

			if ( $this->imdb_cache_values['imdbusecache'] === '1' ) { // use IMDBphp only if cache is active
				$photo_url = strlen( $photo_big ) > 1 ? esc_html( $photo_big ) : esc_html( $photo_thumb ); // create big picture, thumbnail otherwise.
			}

				// Picture for a href, takes big/thumbnail picture if exists, no_pics otherwise.
				$photo_url_href = strlen( $photo_url ) === 0 ? $this->config_class->lumiere_pics_dir . 'no_pics.gif' : $photo_url;

				// Picture for img: if 1/ thumbnail picture exists, use it, 2/ use no_pics otherwise
				$photo_url_img = strlen( $photo_thumb ) === 0 ? esc_url( $this->config_class->lumiere_pics_dir . 'no_pics.gif' ) : $photo_thumb;

				echo '<a class="highslide_pic_popup" href="' . esc_url( $photo_url_href ) . '">';
				// loading="eager" to prevent WordPress loading lazy that doesn't go well with cache scripts.
				echo "\n\t\t" . '<img loading="lazy" class="imdbincluded-picture" src="' . esc_url( $photo_url_img ) . '" alt="' . esc_attr( $movie_results->title() ) . '"';

				// add width only if "Display only thumbnail" is not active.
			if ( $this->imdb_admin_values['imdbcoversize'] === '0' ) {

				echo ' width="' . intval( $this->imdb_admin_values['imdbcoversizewidth'] ) . '"';

				// set width to 100px width if "Display only thumbnail" is active.
			} elseif ( $this->imdb_admin_values['imdbcoversize'] === '1' ) {

				echo ' width="100px"';

			}

				echo ' />';
				echo '</a>';
			?>

			</div> 
		</div> 
		<?php
	}

	/**
	 * Show intro part
	 */
	private function display_intro( Title $movie_results ): void {

		// Director summary, limited by admin options.
		$director = $movie_results->director();

		// director shown only if selected so in options.
		if ( count( $director ) !== 0 && $this->imdb_widget_values['imdbwidgetdirector'] === '1' ) {

			$nbtotaldirector = count( $director );
			echo "\n\t\t\t\t\t\t\t\t\t\t<!-- Director -->";
			echo "\n\t<div>";

			echo '<span class="imdbincluded-subtitle">'
			. esc_html( _n( 'Director', 'Directors', $nbtotaldirector, 'lumiere-movies' ) )
			. '</span>';
			for ( $i = 0; $i < $nbtotaldirector; $i++ ) {

				echo '<a rel="nofollow" class="linkpopup" href="'
					. esc_url(
						$this->config_class->lumiere_urlpopupsperson
						. $director[ $i ]['imdb']
						. '/?mid=' . $director[ $i ]['imdb']
					)
					. '" title="' . esc_html__( 'internal link', 'lumiere-movies' ) . '">';
				echo "\n\t\t\t" . esc_html( $director[ $i ]['name'] );
				if ( $i < $nbtotaldirector - 1 ) {
					echo ', ';
				}

				echo '</a>';

			} // endfor

			echo "\n\t</div>";

		}

		// Main actors, limited by admin options.
		$cast = $movie_results->cast();
		$nbactors = $this->imdb_widget_values['imdbwidgetactornumber'] === 0 ? 1 : intval( $this->imdb_widget_values['imdbwidgetactornumber'] );
		$nbtotalactors = count( $cast );

		// actor shown only if selected so in options.
		if ( $nbtotalactors !== 0 && ( $this->imdb_widget_values['imdbwidgetactor'] === '1' ) ) {

			echo "\n\t\t\t\t\t\t\t\t\t\t<!-- Main actors -->";
			echo "\n\t<div>";

			echo '<span class="imdbincluded-subtitle">' . esc_html__( 'Main actors', 'lumiere-movies' ) . '</span>';

			for ( $i = 0; ( $i < $nbactors ) && ( $i < $nbtotalactors ); $i++ ) {
				echo '<a rel="nofollow" class="linkpopup" href="' . esc_url( $this->config_class->lumiere_urlpopupsperson . $cast[ $i ]['imdb'] . '/?mid=' . $cast[ $i ]['imdb'] ) . '" title="' . esc_html__( 'internal link', 'lumiere-movies' ) . '">';
				echo "\n\t\t\t" . esc_html( $cast[ $i ]['name'] ) . '</a>';

				if ( ( $i < $nbactors - 1 ) && ( $i < $nbtotalactors - 1 ) ) {
					echo ', ';
				}
			}

			echo '</div>';

		}

		// Runtime, limited by admin options.
		$runtime = strval( $movie_results->runtime() );

		// Runtime shown only if selected so in admin options.
		if ( strlen( $runtime ) > 0 && ( $this->imdb_widget_values['imdbwidgetruntime'] === '1' ) ) {

			echo "\n\t\t\t\t\t\t\t\t\t\t<!-- Runtime -->";
			echo "\n\t<div>";
			echo '<span class="imdbincluded-subtitle">'
			. esc_html__( 'Runtime', 'lumiere-movies' )
			. '</span>'
			. esc_html( $runtime )
			. ' '
			. esc_html__( 'minutes', 'lumiere-movies' );
			echo "\n\t</div>";

		}

		// Votes, limited by admin options.
		// rating shown only if selected so in options.
		$votes_sanitized = intval( $movie_results->votes() );
		$rating_int = intval( $movie_results->rating() );
		$rating_string = strval( $movie_results->rating() );

		if ( strlen( $rating_string ) > 0 && ( $this->imdb_widget_values['imdbwidgetrating'] === '1' ) ) {

			echo "\n\t\t\t\t\t\t\t\t\t\t<!-- Rating -->";
			echo "\n\t<div>";

			echo '<span class="imdbincluded-subtitle">'
				. esc_html__( 'Rating', 'lumiere-movies' )
				. '</span>';
			echo ' <img class="imdbelementRATING-picture" src="' . esc_url( $this->config_class->lumiere_pics_dir . '/showtimes/' . ( round( $rating_int * 2, 0 ) / 0.2 ) . '.gif' ) . '"'
			. ' title="' . esc_html__( 'vote average ', 'lumiere-movies' ) . esc_attr( $rating_string ) . esc_html__( ' out of 10', 'lumiere-movies' ) . '"  / >';
			echo ' (' . number_format( $votes_sanitized, 0, '', "'" ) . ' ' . esc_html__( 'votes', 'lumiere-movies' ) . ')';

			echo "\n\t</div>";

		}

		// Language, limited by admin options.
		$languages = $movie_results->languages();
		$nbtotallanguages = count( $languages );

		// language shown only if selected so in options.
		if ( $nbtotallanguages > 0 && ( $this->imdb_widget_values['imdbwidgetlanguage'] === '1' ) ) {

			echo "\n\t\t\t\t\t\t\t<!-- Language -->";
			echo "\n\t<div>";

			echo '<span class="imdbincluded-subtitle">'
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
		$country = $movie_results->country();
		$nbtotalcountry = count( $country );

		// country shown only if selected so in options.
		if ( $nbtotalcountry > 0 && ( $this->imdb_widget_values['imdbwidgetcountry'] === '1' ) ) {

			echo "\n\t\t\t\t\t\t\t\t\t\t<!-- Country -->";
			echo "\n\t<div>";

			echo '<span class="imdbincluded-subtitle">'
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

		$genres = $movie_results->genres();
		$nbtotalgenre = count( $genres );

		// Genre shown only if selected so in options.
		if ( $nbtotalgenre > 0 && ( $this->imdb_widget_values['imdbwidgetgenre'] === '1' ) ) {

			echo "\n\t\t\t\t\t\t\t\t\t\t<!-- Genre -->";
			echo "\n\t<div>";

			echo '<span class="imdbincluded-subtitle">'
			. esc_attr( _n( 'Genre', 'Genres', $nbtotalgenre, 'lumiere-movies' ) )
			. '</span>';

			for ( $i = 0; $i < $nbtotalgenre; $i++ ) {

				echo esc_html( $genres[ $i ] );

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
	private function display_misc( Title $movie_results ): void {

		// Trivia.

		$trivia = $movie_results->trivia();
		$nbtotaltrivia = count( $trivia );

		if ( $nbtotaltrivia > 0 ) {

			echo "\n\t\t\t\t\t\t\t" . ' <!-- Trivia -->';
			echo "\n" . '<div id="lumiere_popup_plots_group">';
			echo "\n\t" . '<div class="imdbincluded-subtitle">' . esc_html( _n( 'Trivia', 'Trivias', $nbtotaltrivia, 'lumiere-movies' ) ) . '</div>';

			for ( $i = 0; $i < $nbtotaltrivia; $i++ ) {

				$text = isset( $trivia[ $i ] ) ? $this->link_maker->lumiere_imdburl_to_internalurl( $trivia[ $i ] ) : '';

				// It may be empty, continue to the next result.
				if ( strlen( $text ) === 0 ) {
					continue;
				}

				echo "\n\t\t\t\t<div>\n\t\t\t\t\t" . '[#' . esc_html( strval( $i + 1 ) ) . '] ' . wp_kses(
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

				if ( $i === 2 ) {
					echo "\n\t\t\t"
					. '<div class="activatehidesection lumiere_align_center"><strong>(' . esc_html__( 'click to show more trivias', 'lumiere-movies' ) . ')</strong></div>';
					echo "\n\t\t\t<div class=\"hidesection\">";

				}

				if ( $i > 2 && $i === ( $nbtotaltrivia - 1 ) ) {
					echo "\n\t\t</div>";
				}
			}

			echo "\n\t</div>";
			echo "\n</div>";
		}

		// Soundtrack.
		$soundtrack = $movie_results->soundtrack();
		$nbtotalsoundtracks = count( $soundtrack );
		if ( $nbtotalsoundtracks !== 0 ) {

			echo "\n\t\t\t\t\t\t\t" . ' <!-- Soundtrack -->';
			echo "\n" . '<div id="lumiere_popup_sdntrck_group">';
			echo "\n\t" . '<div class="imdbincluded-subtitle">' . esc_html( _n( 'Soundtrack', 'Soundtracks', $nbtotalsoundtracks, 'lumiere-movies' ) ) . '</div>';

			for ( $i = 0; $i < $nbtotalsoundtracks; $i++ ) {

				echo "\n\t\t";
				echo "\n\t\t\t" . esc_html( ucfirst( strtolower( $soundtrack[ $i ]['soundtrack'] ) ) );

				echo "\n\t\t\t<i>" . wp_kses(
					str_replace(
						[ "\n", "\r", '<br />', '<br>' ],
						'',
						/**
						* Use Highslide, Classical or No Links class links builder.
						* Each one has its own class passed in $link_maker,
						* according to which option the lumiere_select_link_maker() found in Frontend.
						*/
						$this->link_maker->lumiere_imdburl_to_internalurl( $soundtrack [ $i ]['credits'] )
					),
					[
						'a' => [
							'href' => [],
							'title' => [],
							'class' => [],
						],
					]
				) . '</i> ';

				if ( $i < $nbtotalsoundtracks - 1 ) {
					echo ', ';
				}

				if ( $i === 2 ) {
					echo "\n\t\t" . '<div class="activatehidesection lumiere_align_center"><strong>(' . esc_html__( 'click to show more soundtracks', 'lumiere-movies' ) . ')</strong></div>';
					echo "\n\t\t<div class=\"hidesection\">";

				}

				if ( $i > 2 && $i === ( $nbtotalsoundtracks - 1 ) ) {
					echo "\n\t\t</div>";
				}

			}

			echo "\n</div>";

		}

		// Goof.
		$goof = $movie_results->goofs();
		$nbtotalgoof = count( $goof );

		if ( $nbtotalgoof > 0 ) {

			echo "\n\t\t\t\t\t\t\t" . ' <!-- Goofs -->';
			echo "\n" . '<div id="lumiere_popup_goofs_group">';
			echo "\n\t" . '<div class="imdbincluded-subtitle">' . esc_html( _n( 'Goof', 'Goofs', $nbtotalgoof, 'lumiere-movies' ) ) . '</div>';

			for ( $i = 0; $i < $nbtotalgoof; $i++ ) {

				echo "\n\t\t\t<div>\n\t\t\t\t[#" . esc_html( strval( $i + 1 ) ) . '] <i>' . esc_html( $goof[ $i ]['type'] ) . '</i>&nbsp;';
				echo wp_kses(
					$this->link_maker->lumiere_imdburl_to_internalurl( $goof[ $i ]['content'] ),
					[
						'a' => [
							'href' => [],
							'title' => [],
							'class' => [],
						],
					]
				);
				echo "\n\t\t\t" . '</div>';

				if ( $i === 2 ) {
					echo "\n\t\t" . '<div class="activatehidesection lumiere_align_center"><strong>(' . esc_html__( 'click to show more goofs', 'lumiere-movies' ) . ')</strong></div>'
					. "\n\t\t" . '<div class="hidesection">';
				}

				if ( $i > 2 && $i === ( $nbtotalgoof - 1 ) ) {
					echo "\n\t\t</div>";
				}
			}

			echo "\n\t</div>";
			echo "\n</div>";

		}

	}

	/**
	 * Show casting.
	 */
	private function display_casting( Title $movie_results ): void {

		// Actors.
		$cast = $movie_results->cast();
		$nbtotalactors = count( $cast );

		if ( count( $cast ) > 0 ) {

			echo "\n\t\t\t\t\t\t\t\t\t\t<!-- Actors -->";
			echo "\n\t" . '<div class="imdbincluded-subtitle">' . esc_html( sprintf( _n( 'Actor', 'Actors', $nbtotalactors, 'lumiere-movies' ), number_format_i18n( $nbtotalactors ) ) ) . '</div>';

			for ( $i = 0; ( $i < $nbtotalactors ); $i++ ) {
				echo "\n\t\t" . '<div align="center" class="lumiere_container">';
				echo "\n\t\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';
				echo esc_html( $cast[ $i ]['role'] );
				echo '</div>';
				echo "\n\t\t\t" . '<div class="lumiere_align_right lumiere_flex_auto">';
				echo "\n\t\t\t\t"
				. '<a rel="nofollow" class="linkpopup" href="'
				. esc_url(
					$this->config_class->lumiere_urlpopupsperson
					. $cast[ $i ]['imdb']
					. '/?mid=' . $cast[ $i ]['imdb']
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
	private function display_crew( Title $movie_results ): void {

		// Directors.
		$director = $movie_results->director();
		$nbtotaldirector = count( $director );

		if ( $nbtotaldirector > 0 ) {

			echo "\n\t\t\t\t\t\t\t" . ' <!-- director -->';
			echo "\n" . '<div id="lumiere_popup_director_group">';
			echo "\n\t" . '<span class="imdbincluded-subtitle">' . esc_html( _n( 'Director', 'Directors', $nbtotaldirector, 'lumiere-movies' ) ) . '</span>';

			for ( $i = 0; $i < $nbtotaldirector; $i++ ) {
				echo "\n\t" . '<div align="center" class="lumiere_container">';
				echo "\n\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';
				echo "\n\t\t"
				. '<a rel="nofollow" class="linkpopup" href="'
				. esc_url(
					$this->config_class->lumiere_urlpopupsperson
					. $director[ $i ]['imdb']
					. '/?mid=' . $director[ $i ]['imdb']
				)
					. '" title="'
					. esc_html__( 'internal link', 'lumiere-movies' )
					. '">';

				echo "\n\t\t" . esc_html( $director[ $i ]['name'] );
				echo "\n\t\t</a>";
				echo "\n\t\t</div>";
				echo "\n\t\t" . '<div class="lumiere_align_right lumiere_flex_auto">';
				echo esc_html( $director[ $i ]['role'] );
				echo "\n\t\t" . '</div>';
				echo "\n\t</div>";
				echo "\n</div>";

			}

		}

		// Writers.
		$writer = $movie_results->writing();
		$nbtotalwriter = count( $writer );

		if ( $nbtotalwriter > 0 ) {

			echo "\n\t\t\t\t\t\t\t" . ' <!-- writers -->';
			echo "\n" . '<div id="lumiere_popup_director_group">';
			echo "\n\t" . '<span class="imdbincluded-subtitle">' . esc_html( _n( 'Writer', 'Writers', $nbtotalwriter, 'lumiere-movies' ) ) . '</span>';

			for ( $i = 0; $i < $nbtotalwriter; $i++ ) {
				echo "\n\t" . '<div align="center" class="lumiere_container">';
				echo "\n\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';
				echo "\n\t\t"
				. '<a rel="nofollow" class="linkpopup" href="'
				. esc_url(
					$this->config_class->lumiere_urlpopupsperson
					. $writer[ $i ]['imdb']
					. '/?mid=' . $writer[ $i ]['imdb']
				)
					. '" title="'
					. esc_html__( 'internal link', 'lumiere-movies' )
					. '">';
				echo "\n\t\t" . esc_html( $writer[ $i ]['name'] );
				echo "\n\t\t</a>";
				echo "\n\t\t</div>";
				echo "\n\t\t" . '<div class="lumiere_align_right lumiere_flex_auto">';
				echo esc_html( $writer[ $i ]['role'] );
				echo "\n\t\t" . '</div>';
				echo "\n\t</div>";
				echo "\n</div>";
			}
		}

		// Producers.
		$producer = $movie_results->producer();
		$nbtotalproducer = count( $producer );

		if ( $nbtotalproducer > 0 ) {

			echo "\n\t\t\t\t\t\t\t" . ' <!-- writers -->';
			echo "\n" . '<div id="lumiere_popup_writer_group">';
			echo "\n\t" . '<span class="imdbincluded-subtitle">' . esc_html( _n( 'Producer', 'Producers', $nbtotalproducer, 'lumiere-movies' ) ) . '</span>';

			for ( $i = 0; $i < $nbtotalproducer; $i++ ) {
				echo "\n\t" . '<div align="center" class="lumiere_container">';
				echo "\n\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';
				echo "\n\t\t"
				. '<a rel="nofollow" class="linkpopup" href="'
				. esc_url(
					$this->config_class->lumiere_urlpopupsperson
					. $producer[ $i ]['imdb']
					. '/?mid=' . $producer[ $i ]['imdb']
				)
					. '" title="'
					. esc_html__( 'internal link', 'lumiere-movies' )
					. '">';
				echo "\n\t\t" . esc_html( $producer[ $i ]['name'] );
				echo "\n\t\t</a>";
				echo "\n\t\t</div>";
				echo "\n\t\t" . '<div class="lumiere_align_right lumiere_flex_auto">';
				echo esc_html( $producer[ $i ]['role'] );
				echo "\n\t\t" . '</div>';
				echo "\n\t</div>";
				echo "\n</div>";
			}
		}
	}

	/**
	 * Show summary.
	 */
	private function display_summary( Title $movie_results ): void {

		// Plots.
		$plot = $movie_results->plot();
		$nbtotalplot = count( $plot );

		if ( $nbtotalplot > 0 ) { // Start with second plot, first is same as plotouline().

			echo "\n\t\t\t\t\t\t\t" . ' <!-- Plots -->';
			echo "\n" . '<div id="lumiere_popup_pluts_group">';
			echo "\n\t" . '<span class="imdbincluded-subtitle">' . esc_html( _n( 'Plot', 'Plots', $nbtotalplot, 'lumiere-movies' ) ) . '</span>';

			// Starts a
			for ( $i = 0; $i < $nbtotalplot; $i++ ) {
				echo "\n\t" . '<div>';
				echo ' [#' . esc_html( strval( $i + 1 ) ) . '] ' . wp_strip_all_tags( $plot[ $i ] );
				if ( $i < $nbtotalplot - 1 ) {
					echo "\n<br>";
				}
				echo "\n\t</div>";
			}

			echo "\n</div>";

		}
	}

}
