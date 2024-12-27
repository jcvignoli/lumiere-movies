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
lum_check_display();

use Imdb\Title;
use Imdb\TitleSearch;
use Lumiere\Frontend\Main;
use Lumiere\Frontend\Popups\Head_Popups;
use Lumiere\Tools\Validate_Get;
use Exception;

/**
 * Independant class that displays movie information in a popup
 *
 * @see \Lumiere\Alteration\Rewrite_Rules Create the rules for building a virtual page
 * @see \Lumiere\Frontend\Frontend Redirect to this page using virtual pages {@link \Lumiere\Alteration\Virtual_Page}
 * @see \Lumiere\Frontend\Popups\Head_Popups Modify the popup header
 *
 * Bots are banned before getting popups
 * @see \Lumiere\Frontend\Frontend::ban_bots_popups() Bot banishement happens there, before processing IMDb queries
 *
 * @phpstan-import-type TITLESEARCH_RETURNSEARCH from \Lumiere\Tools\Settings_Global
 */
class Popup_Movie {

	/**
	 * Traits
	 */
	use Main;

	/**
	 * The movie queried
	 */
	private Title $movie;

	/**
	 * Movie's title, if provided
	 */
	private ?string $film_title_sanitized;

	/**
	 * Constructor
	 */
	public function __construct() {

		// Edit metas tags in popups.
		add_action( 'template_redirect', fn() => Head_Popups::lumiere_static_start() );

		// Construct Frontend trait.
		$this->start_main_trait();

		// Remove admin bar if user is logged in.
		// Also check if AMP page (in trait Main), as AMP plugin needs admin bar if logged in otherwise returns notices.
		if ( is_user_logged_in() === true && $this->lumiere_is_amp_page() !== true ) {
			add_filter( 'show_admin_bar', '__return_false' );
			wp_dequeue_style( 'admin-bar' );
			wp_deregister_style( 'admin-bar' );
		}

		/**
		 * Start Plugins_Start class
		 * Is instanciated only if not instanciated already
		 * Use lumiere_set_plugins_array() in trait to set $plugins_active_names var in trait
		 */
		if ( count( $this->plugins_active_names ) === 0 ) {
			$this->activate_plugins();
		}

		/**
		 * Display layout
		 * @since 4.0 using 'the_posts' instead of the 'content', removed the 'get_header' for OceanWP
		 * @since 4.1.2 using 'template_include' which is the proper way to include templates
		 */
		add_filter( 'template_include', [ $this, 'lumiere_popup_movie_layout' ] );
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

		/* GET Vars sanitized */
		$movieid_sanitized = Validate_Get::sanitize_url( 'mid' );
		$movieid_sanitized = $movieid_sanitized !== null && strlen( $movieid_sanitized ) > 0 ? $movieid_sanitized : null;
		$film_title_sanitized = Validate_Get::sanitize_url( 'film' );
		$this->film_title_sanitized = $film_title_sanitized !== null && strlen( $film_title_sanitized ) > 0 ? $film_title_sanitized : null;

		// if neither film nor mid are set, return false.
		if ( $movieid_sanitized === null && $this->film_title_sanitized === null ) {
			$text = '[Lumiere][' . $this->classname . '] Neither a movie title nor an id were provided.';
			$this->logger->log()->error( $text );
			return false;
		}

		// A movie imdb id is provided in URL.
		if ( isset( $movieid_sanitized ) ) {

			$this->logger->log()->debug( '[Lumiere][' . $this->classname . '] Movie id provided in URL: ' . $movieid_sanitized );

			$this->movie = new Title( $movieid_sanitized, $this->plugins_classes_active['imdbphp'], $this->logger->log() );
			// @since 4.0 lowercase, less cache used.
			$this->film_title_sanitized = strtolower( $this->lumiere_name_htmlize( $this->movie->title() ) ); // Method in trait Data, which is in trait Main.
			return true;

			// No movie id is provided, but a title was.
		} elseif ( isset( $this->film_title_sanitized ) ) {

			$this->logger->log()->debug( '[Lumiere][' . $this->classname . '] Movie title provided in URL: ' . $this->film_title_sanitized );

			$title_search_class = new TitleSearch( $this->plugins_classes_active['imdbphp'], $this->logger->log() );

			/**
			 * @var array<array-key, mixed> $search
			 * @phpstan-var TITLESEARCH_RETURNSEARCH $search */
			$search = $title_search_class->search( $this->film_title_sanitized, $this->config_class->lumiere_select_type_search() );

			if ( count( $search ) === 0 || array_key_exists( 0, $search ) === false ) {

				$text = '[Lumiere][' . $this->classname . '] Fatal error: Could not find the movie title: ' . $this->film_title_sanitized;
				$this->logger->log()->critical( $text );
				return false;
			}

			$this->movie = new Title( $search[0]['imdbid'], $this->plugins_classes_active['imdbphp'], $this->logger->log() );

			return true;
		}

		return false;
	}

	/**
	 * Display layout
	 *
	 * @param string $template_path The path to the page of the theme currently in use - not utilised
	 * @return string
	 *
	 * @throws Exception if errors occurs when searching for the movie
	 */
	public function lumiere_popup_movie_layout( string $template_path ): string {

		// Nonce. Always valid if admin is connected.
		$nonce_valid = ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ) ) > 0 ) || current_user_can( 'administrator' ) === true ? true : false; // Created in Abstract_Link_Maker class.

		// Validate $_GET['info'], exit if failed.
		$get_info = Validate_Get::sanitize_url( 'info' );
		if (
			( isset( $_GET['info'] ) && $get_info === null )
			|| $nonce_valid === false
		) {
			wp_die( esc_html__( 'Lumière Movies: Invalid movie search query.', 'lumiere-movies' ) );
		}

		// Exit if no movie was found.
		if ( $this->find_movie() === false ) {
			status_header( 404 );
			$text = 'Could not find any IMDb movie with this query.';
			$this->logger->log()->error( '[Lumiere][' . $this->classname . '] ' . $text );
			wp_die( esc_html( $text ) );
		}

		echo "<!DOCTYPE html>\n<html>\n<head>\n";
		wp_head();
		echo "\n</head>\n<body class=\"lum_body_popup";

		echo isset( $this->imdb_admin_values['imdbpopuptheme'] ) ? ' lum_body_popup_' . esc_attr( $this->imdb_admin_values['imdbpopuptheme'] ) . '">' : '">';

		/**
		 * Display a spinner when clicking a link with class .lum_add_spinner (a <div class="loader"> will be inserted inside by the js)
		 */
		echo '<div id="spinner-placeholder"></div>';

		$this->logger->log()->debug( '[Lumiere][' . $this->classname . '] Using the link maker class: ' . str_replace( 'Lumiere\Link_Makers\\', '', get_class( $this->link_maker ) ) );

		$this->display_menu( $this->movie );

		$this->display_portrait( $this->movie );

		// Introduction part.
		// Display something when nothing has been selected in the menu.

		if ( $get_info === null || strlen( $get_info ) === 0 ) {
			$this->display_intro( $this->movie );
		}

		// Casting part.
		if ( $get_info === 'actors' ) {
			$this->display_casting( $this->movie );
		}

		// Crew part.
		if ( $get_info === 'crew' ) {
			$this->display_crew( $this->movie );
		}

		// Resume part.
		if ( $get_info === 'resume' ) {
			$this->display_summary( $this->movie );
		}

		// Misc part.
		if ( $get_info === 'divers' ) {
			$this->display_misc( $this->movie );
		}

		// The end.
		wp_meta();
		wp_footer();
		echo "</body>\n</html>";

		// Avoid 'Filter callback return statement is missing.' from PHPStan
		return '';
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

		<div class="lumiere_container lumiere_font_em_11 lum_popup_titlemenu">
			<div class="lumiere_flex_auto">
				&nbsp;<a rel="nofollow" id="searchaka" class="lum_popup_menu_title lum_add_spinner" href="<?php echo esc_url( wp_nonce_url( $url_if_polylang_search . '/?film=' . $this->film_title_sanitized . '&norecursive=yes' ) ); ?>" title="<?php esc_html_e( 'Search for other movies with the same title', 'lumiere-movies' ); ?>"><?php esc_html_e( 'Similar Titles', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				&nbsp;<a rel="nofollow" class="lum_popup_menu_title lum_add_spinner" href="<?php echo esc_url( wp_nonce_url( $url_if_polylang . '/?mid=' . $movie_results->imdbid() . '&film=' . $this->film_title_sanitized . '&info=' ) ); ?>" title='<?php echo esc_attr( $movie_results->title() ) . ': ' . esc_html__( 'Movie', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Summary', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				&nbsp;<a rel="nofollow" class="lum_popup_menu_title lum_add_spinner" href="<?php echo esc_url( wp_nonce_url( $url_if_polylang . '/?mid=' . $movie_results->imdbid() . '&film=' . $this->film_title_sanitized . '&info=actors' ) ); ?>" title='<?php echo esc_attr( $movie_results->title() ) . ': ' . esc_html__( 'Actors', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Actors', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				&nbsp;<a rel="nofollow" class="lum_popup_menu_title lum_add_spinner" href="<?php echo esc_url( wp_nonce_url( $url_if_polylang . '/?mid=' . $movie_results->imdbid() . '&film=' . $this->film_title_sanitized . '&info=crew' ) ); ?>" title='<?php echo esc_attr( $movie_results->title() ) . ': ' . esc_html__( 'Crew', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Crew', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				&nbsp;<a rel="nofollow" class="lum_popup_menu_title lum_add_spinner" href="<?php echo esc_url( wp_nonce_url( $url_if_polylang . '/?mid=' . $movie_results->imdbid() . '&film=' . $this->film_title_sanitized . '&info=resume' ) ); ?>" title='<?php echo esc_attr( $movie_results->title() ) . ': ' . esc_html__( 'Plots', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Plots', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				&nbsp;<a rel="nofollow" class="lum_popup_menu_title lum_add_spinner" href="<?php echo esc_url( wp_nonce_url( $url_if_polylang . '/?mid=' . $movie_results->imdbid() . '&film=' . $this->film_title_sanitized . '&info=divers' ) ); ?>" title='<?php echo esc_attr( $movie_results->title() ) . ': ' . esc_html__( 'Misc', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Misc', 'lumiere-movies' ); ?></a>
			</div>
		</div>
		<?php
	}

	/**
	 * Show the portrait (title, picture)
	 */
	public function display_portrait( Title $movie_results ): void {
		?>
		<div class="lumiere_display_flex lumiere_font_em_11 lumiere_align_center lum_padding_bott_2vh">
			<div class="lumiere_flex_auto lum_width_fit_cont">
				<div class="titrefilm">
				<?php
					// Get movie's title from imdbphp query, not from globals.
					echo esc_html( $movie_results->title() );
				?>
				&nbsp;(<?php echo $movie_results->year() > 0 ? esc_html( $movie_results->year() ) : esc_html__( 'year unknown', 'lumiere-movies' ); ?>)</div>
				<div class="lumiere_align_center"><font size="-1"><?php
					$taglines = $movie_results->tagline();
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
				$photo_big = (string) $movie_results->photoLocalurl( false );
				$photo_thumb = (string) $movie_results->photoLocalurl( true );

			if ( $this->imdb_cache_values['imdbusecache'] === '1' ) { // use IMDBphp only if cache is active
				$photo_url = strlen( $photo_big ) > 1 ? esc_html( $photo_big ) : esc_html( $photo_thumb ); // create big picture, thumbnail otherwise.
			}

				// Picture for a href, takes big/thumbnail picture if exists, no_pics otherwise.
				$photo_url_href = strlen( $photo_url ) === 0 ? $this->config_class->lumiere_pics_dir . 'no_pics.gif' : $photo_url;

				// Picture for img: if 1/ thumbnail picture exists, use it, 2/ use no_pics otherwise
				$photo_url_img = strlen( $photo_thumb ) === 0 ? esc_url( $this->config_class->lumiere_pics_dir . 'no_pics.gif' ) : $photo_thumb;

				echo '<a class="lum_pic_inpopup" href="' . esc_url( $photo_url_href ) . '">';
				// loading="eager" to prevent WordPress loading lazy that doesn't go well with cache scripts.
				echo "\n\t\t" . '<img loading="lazy" src="' . esc_url( $photo_url_img ) . '" alt="' . esc_attr( $movie_results->title() ) . '"';

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
	private function display_intro( Title $movie_results ): void {

		// Director summary, limited by admin options.
		$director = $movie_results->director();

		// director shown only if selected so in options.
		if ( count( $director ) !== 0 && $this->imdb_data_values['imdbwidgetdirector'] === '1' ) {

			$nbtotaldirector = count( $director );
			echo "\n\t\t\t\t\t\t\t\t\t\t<!-- Director -->";
			echo "\n\t<div>";

			echo '<span class="lum_results_section_subtitle">'
			. esc_html( _n( 'Director', 'Directors', $nbtotaldirector, 'lumiere-movies' ) )
			. '</span>';
			for ( $i = 0; $i < $nbtotaldirector; $i++ ) {

				echo '<a rel="nofollow" class="lum_popup_internal_link lum_add_spinner" href="'
					. esc_url(
						wp_nonce_url( $this->config_class->lumiere_urlpopupsperson . '?mid=' . $director[ $i ]['imdb'] )
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
		$cast = $movie_results->cast();
		$nbactors = $this->imdb_data_values['imdbwidgetactornumber'] === 0 ? 1 : intval( $this->imdb_data_values['imdbwidgetactornumber'] );
		$nbtotalactors = count( $cast );

		// actor shown only if selected so in options.
		if ( $nbtotalactors !== 0 && ( $this->imdb_data_values['imdbwidgetactor'] === '1' ) ) {

			echo "\n\t\t\t\t\t\t\t\t\t\t<!-- Main actors -->";
			echo "\n\t<div>";

			echo '<span class="lum_results_section_subtitle">' . esc_html__( 'Main actors', 'lumiere-movies' ) . '</span>';

			for ( $i = 0; ( $i < $nbactors ) && ( $i < $nbtotalactors ); $i++ ) {
				echo '<a rel="nofollow" class="lum_popup_internal_link lum_add_spinner" href="' . esc_url( wp_nonce_url( $this->config_class->lumiere_urlpopupsperson . '?mid=' . $cast[ $i ]['imdb'] ) ) . '" title="' . esc_html__( 'internal link', 'lumiere-movies' ) . '">';
				echo "\n\t\t\t" . esc_html( $cast[ $i ]['name'] ) . '</a>';

				if ( ( $i < $nbactors - 1 ) && ( $i < $nbtotalactors - 1 ) ) {
					echo ', ';
				}
			}
			echo '</div>';
		}

		// Runtime, limited by admin options.
		$runtime = $movie_results->runtime();
		$runtime = isset( $runtime[0]['time'] ) ? esc_html( strval( $runtime[0]['time'] ) ) : '';

		// Runtime shown only if selected so in admin options.
		if ( strlen( $runtime ) > 0 && ( $this->imdb_data_values['imdbwidgetruntime'] === '1' ) ) {

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
		$votes_sanitized = intval( $movie_results->votes() );
		$rating_int = intval( $movie_results->rating() );
		$rating_string = strval( $movie_results->rating() );

		if ( strlen( $rating_string ) > 0 && ( $this->imdb_data_values['imdbwidgetrating'] === '1' ) ) {

			echo "\n\t\t\t\t\t\t\t\t\t\t<!-- Rating -->";
			echo "\n\t<div>";

			echo '<span class="lum_results_section_subtitle">'
				. esc_html__( 'Rating', 'lumiere-movies' )
				. '</span>';
			echo ' <img class="imdbelementRATING-picture" src="' . esc_url( $this->config_class->lumiere_pics_dir . 'showtimes/' . ( round( $rating_int * 2, 0 ) / 0.2 ) . '.gif' ) . '"'
			. ' title="' . esc_html__( 'vote average ', 'lumiere-movies' ) . esc_attr( $rating_string ) . esc_html__( ' out of 10', 'lumiere-movies' ) . '"  width="102" height="12" / >';
			echo ' (' . number_format( $votes_sanitized, 0, '', "'" ) . ' ' . esc_html__( 'votes', 'lumiere-movies' ) . ')';

			echo "\n\t</div>";
		}

		// Language, limited by admin options.
		$languages = $movie_results->language();
		$nbtotallanguages = count( $languages );

		// language shown only if selected so in options.
		if ( $nbtotallanguages > 0 && ( $this->imdb_data_values['imdbwidgetlanguage'] === '1' ) ) {

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
		$country = $movie_results->country();
		$nbtotalcountry = count( $country );

		// country shown only if selected so in options.
		if ( $nbtotalcountry > 0 && ( $this->imdb_data_values['imdbwidgetcountry'] === '1' ) ) {

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

		$genre = $movie_results->genre();
		$nbtotalgenre = count( $genre );

		// Genre shown only if selected so in options.
		if ( $nbtotalgenre > 0 && ( $this->imdb_data_values['imdbwidgetgenre'] === '1' ) ) {

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
	private function display_misc( Title $movie_results ): void {

		// Trivia.

		$trivia = $movie_results->trivia();
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

				if ( $nb_total_trivia_processed === 2 ) {
					echo "\n\t\t\t"
					. '<div class="activatehidesection lumiere_align_center"><strong>(' . esc_html__( 'click to show more trivias', 'lumiere-movies' ) . ')</strong></div>';
					echo "\n\t\t\t<div class=\"hidesection\">";

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
		$soundtrack = $movie_results->soundtrack();
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
				echo "\n\t\t" . '<div class="activatehidesection lumiere_align_center"><strong>(' . esc_html__( 'click to show more soundtracks', 'lumiere-movies' ) . ')</strong></div>';
				echo "\n\t\t<div class=\"hidesection\">";

			}

			if ( $i > 2 && $i === $nbtotalsoundtracks ) {
				echo "\n\t\t</div>";
			}

		}

		echo "\n</div>";

		// Goof.
		$goof = $movie_results->goof();
		$filter_nbtotalgoof = array_filter( $goof, fn( $goofs ) => ( count( array_values( $goof ) ) > 0 ) ); // counts the actual goofs, not their categories
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
					echo "\n\t\t" . '<div class="activatehidesection lumiere_align_center"><strong>(' . esc_html__( 'click to show more goofs', 'lumiere-movies' ) . ')</strong></div>'
					. "\n\t\t" . '<div class="hidesection">';
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
	private function display_casting( Title $movie_results ): void {

		// Actors.
		$cast = $movie_results->cast();
		$nbtotalactors = count( $cast );

		if ( count( $cast ) > 0 ) {

			echo "\n\t\t\t\t\t\t\t\t\t\t<!-- Actors -->";
			echo "\n\t" . '<div class="lum_results_section_subtitle">' . esc_html( sprintf( _n( 'Actor', 'Actors', $nbtotalactors, 'lumiere-movies' ), number_format_i18n( $nbtotalactors ) ) ) . '</div>';

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
							$this->config_class->lumiere_urlpopupsperson . $cast[ $i ]['imdb'] . '/?mid=' . $cast[ $i ]['imdb']
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
	private function display_crew( Title $movie_results ): void {

		// Directors.
		$director = $movie_results->director();
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
						$this->config_class->lumiere_urlpopupsperson . $director[ $i ]['imdb'] . '/?mid=' . $director[ $i ]['imdb']
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
		$writer = $movie_results->writer();
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
						$this->config_class->lumiere_urlpopupsperson . $writer[ $i ]['imdb'] . '/?mid=' . $writer[ $i ]['imdb']
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
		$producer = $movie_results->producer();
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
						$this->config_class->lumiere_urlpopupsperson . $producer[ $i ]['imdb'] . '/?mid=' . $producer[ $i ]['imdb']
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
	private function display_summary( Title $movie_results ): void {

		// Plots.
		$plot = $movie_results->plot();
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
