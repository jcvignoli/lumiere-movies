<?php declare( strict_types = 1 );
/**
 * Popup for movies: Independant page that displays movie information inside a popup
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version       2.1
 * @package lumiere-movies
 */

namespace Lumiere;

// If this file is called directly, abort.
if ( ( ! defined( 'ABSPATH' ) ) || ( ! class_exists( '\Lumiere\Settings' ) ) ) {
	wp_die( esc_html__( 'You are not allowed to call this page directly.', 'lumiere-movies' ) );
}

use \Imdb\Title;
use \Imdb\TitleSearch;
use \Lumiere\Link_Makers\Link_Factory;

class Popup_Movie {

	// Use trait frontend
	use \Lumiere\Frontend {
		Frontend::__construct as public __constructFrontend;
	}

	/**
	 * HTML allowed for use of wp_kses()
	 */
	private const ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS = [
		'a' => [
			'href' => true,
			'id' => true,
			'class' => true,
		],
		'br' => [],
	];

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
	 */
	public function __construct() {

		// Construct Frontend trait.
		$this->__constructFrontend( 'popupMovie' );

		// Get the type of search: movies, series, games
		$this->type_search = $this->config_class->lumiere_select_type_search();

		// Remove admin bar
		add_filter( 'show_admin_bar', '__return_false' );

		// Display layout
		#add_action( 'wp', [ $this, 'lumiere_popup_movie_layout' ], 1 );
		// When set on get_header hook, the popup is fully included in WP environement
		add_action( 'get_header', [ $this, 'lumiere_popup_movie_layout' ], 1 );

	}

	/**
	 * Search movie id or title
	 * Must be called after wp_head(), so call it manually
	 */
	private function find_movie(): bool {

		do_action( 'lumiere_logger' );

		/* GET Vars sanitized */
		$this->movieid_sanitized = isset( $_GET['mid'] ) && strlen( $_GET['mid'] ) !== 0 ? esc_html( $_GET['mid'] ) : null;
		$this->film_title_sanitized = isset( $_GET['film'] ) && strlen( $_GET['film'] ) !== 0 ? esc_html( $_GET['film'] ) : null;

		// if neither film nor mid are set, wp_die()
		if ( $this->movieid_sanitized === null && $this->film_title_sanitized === null ) {

			status_header( 404 );
			$this->logger->log()->error( '[Lumiere] Neither movie title nor id provided.' );
			wp_die( esc_html__( 'Lumière Movies: Invalid query.', 'lumiere-movies' ) );

		}

		// A movie imdb id is provided in URL.
		if ( $this->movieid_sanitized !== null ) {

			$this->logger->log()->debug( '[Lumiere] Movie id provided in URL: ' . $this->movieid_sanitized );

			$this->movie = new Title( $this->movieid_sanitized, $this->imdbphp_class, $this->logger->log() );
			$this->film_title_sanitized = Utils::lumiere_name_htmlize( $this->movie->title() );

			return true;

			// No movie id is provided, but a title was.
		} elseif ( $this->film_title_sanitized !== null ) {

			$this->logger->log()->debug( '[Lumiere] Movie title provided in URL: ' . $this->film_title_sanitized );

			$title_search_class = new TitleSearch( $this->imdbphp_class, $this->logger->log() );
			$search = $title_search_class->search( $this->film_title_sanitized, $this->type_search );

			if ( array_key_exists( 0, $search ) === false ) {

				return false;

			}

			$this->movie = $search[0];

			return true;
		}

		return false;
	}

	/**
	 *  Display layout
	 *
	 */
	public function lumiere_popup_movie_layout(): void {

		?><!DOCTYPE html>
<html>
<head>
		<?php wp_head();?>
		</head>
		<body class="lumiere_body<?php
		if ( isset( $this->imdb_admin_values['imdbpopuptheme'] ) ) {
			echo ' lumiere_body_' . esc_attr( $this->imdb_admin_values['imdbpopuptheme'] );
		}
		echo '">';

		// Set up class properties.
		$this->find_movie();

		$movie_results = $this->movie;

		// Build Link Factory class
		$this->link_maker = $this->factory_class->lumiere_select_link_maker();
		$this->logger->log()->debug( '[Lumiere][popupPersonClass] Using the link maker class: ' . str_replace( 'Lumiere\Link_Makers\\', '', get_class( $this->link_maker ) ) );

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

		echo '<br />';
		wp_footer();

		?>
</body>
</html>
		<?php

		exit(); // quit the call of the page, to avoid double loading process

	}

	/**
	 * Show the menu
	 */
	private function display_menu( Title $movie_results ): void {

		?>
					<!-- top page menu -->

		<div class="lumiere_container lumiere_font_em_11 lumiere_titlemenu">
			<div class="lumiere_flex_auto">
				&nbsp;<a class="searchaka" href="<?php echo esc_url( $this->config_class->lumiere_urlpopupsearch . '?film=' . $this->film_title_sanitized . '&norecursive=yes' ); ?>" title="<?php esc_html_e( 'Search for other movies with the same title', 'lumiere-movies' ); ?>"><?php esc_html_e( 'Similar Titles', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				&nbsp;<a class='linkpopup' href="<?php echo esc_url( $this->config_class->lumiere_urlpopupsfilms . '/?mid=' . $movie_results->imdbid() . '&film=' . $this->film_title_sanitized . '&info=' ); ?>" title='<?php echo esc_attr( $movie_results->title() ) . ': ' . esc_html__( 'Movie', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Summary', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				&nbsp;<a class='linkpopup' href="<?php echo esc_url( $this->config_class->lumiere_urlpopupsfilms . '/?mid=' . $movie_results->imdbid() . '&film=' . $this->film_title_sanitized . '&info=actors' ); ?>" title='<?php echo esc_attr( $movie_results->title() ) . ': ' . esc_html__( 'Actors', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Actors', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				&nbsp;<a class='linkpopup' href="<?php echo esc_url( $this->config_class->lumiere_urlpopupsfilms . '/?mid=' . $movie_results->imdbid() . '&film=' . $this->film_title_sanitized . '&info=crew' ); ?>" title='<?php echo esc_attr( $movie_results->title() ) . ': ' . esc_html__( 'Crew', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Crew', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				&nbsp;<a class='linkpopup' href="<?php echo esc_url( $this->config_class->lumiere_urlpopupsfilms . '/?mid=' . $movie_results->imdbid() . '&film=' . $this->film_title_sanitized . '&info=resume' ); ?>" title='<?php echo esc_attr( $movie_results->title() ) . ': ' . esc_html__( 'Plots', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Plots', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				&nbsp;<a class='linkpopup' href="<?php echo esc_url( $this->config_class->lumiere_urlpopupsfilms . '/?mid=' . $movie_results->imdbid() . '&film=' . $this->film_title_sanitized . '&info=divers' ); ?>" title='<?php echo esc_attr( $movie_results->title() ) . ': ' . esc_html__( 'Misc', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Misc', 'lumiere-movies' ); ?></a>
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
				<div class="lumiere_align_center"><font size="-1"><?php echo esc_html( $movie_results->tagline() ); ?></font></div>
			</div> 
			<div class="lumiere_flex_auto lumiere_width_twenty_perc lumiere_padding_two">
												<!-- Movie's picture display -->
			<?php
				// Select pictures: big poster, if not small poster, if not 'no picture'.
				$photo_url = $movie_results->photo_localurl( false ) !== false ? esc_html( (string) $movie_results->photo_localurl( false ) ) : esc_html( (string) $movie_results->photo_localurl( true ) ); // create big picture, thumbnail otherwise.
				$photo_url_final = strlen( $photo_url ) === 0 ? $this->imdb_admin_values['imdbplugindirectory'] . 'pics/no_pics.gif' : $photo_url; // take big/thumbnail picture if exists, no_pics otherwise.

				echo '<a class="highslide_pic_popup" href="' . esc_url( $photo_url ) . '">';
				// loading="eager" to prevent WordPress loading lazy that doesn't go well with cache scripts.
				echo "\n\t\t" . '<img loading="eager" class="imdbincluded-picture" src="';

				echo esc_url( $photo_url_final ) . '" alt="' . esc_attr( $movie_results->title() ) . '"';

				// add width only if "Display only thumbnail" is unactive.
			if ( $this->imdb_admin_values['imdbcoversize'] === '0' ) {

				echo ' width="' . intval( $this->imdb_admin_values['imdbcoversizewidth'] ) . '"';

				// add 100px width if "Display only thumbnail" is active.
			} elseif ( $this->imdb_admin_values['imdbcoversize'] === '1' ) {

				echo ' width="100em"';

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

				echo '<a class="linkpopup" href="'
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
				echo '<a class="linkpopup" href="' . esc_url( $this->config_class->lumiere_urlpopupsperson . $cast[ $i ]['imdb'] . '/?mid=' . $cast[ $i ]['imdb'] ) . '" title="' . esc_html__( 'internal link', 'lumiere-movies' ) . '">';
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
			echo ' <img class="imdbelementRATING-picture" src="' . esc_url( $this->imdb_admin_values['imdbplugindirectory'] . 'pics/showtimes/' . ( round( $rating_int * 2, 0 ) / 0.2 ) . '.gif' ) . '"'
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
			echo "\n" . '<div id="lumiere_popup_pluts_group">';
			echo "\n\t" . '<span class="imdbincluded-subtitle">' . esc_html( _n( 'Trivia', 'Trivias', $nbtotaltrivia, 'lumiere-movies' ) ) . '</span>';

			for ( $i = 0; $i < $nbtotaltrivia; $i++ ) {
				$iterator_number = $i + 1;

				if ( $i === 0 ) {
					// @phpcs:ignore WordPress.Security.EscapeOutput
					echo "\n\t" . '<div>' . $this->link_maker->lumiere_imdburl_to_internalurl( $trivia[ $i ] )
					. '&nbsp;&nbsp;&nbsp;'
					. '<span class="activatehidesection"><strong>(' . esc_html__( 'click to show more trivias', 'lumiere-movies' ) . ')</strong></span>'
					. "\n\t" . '<div class="hidesection">'
					. '<br />';

				}

				if ( $i > 0 ) {
					// @phpcs:ignore WordPress.Security.EscapeOutput
					echo $this->link_maker->lumiere_imdburl_to_internalurl( $trivia[ $i ] )
					. "\n\t\t<hr>";
				}

			}

			echo "\n\t" . '</div>';
			echo "\n\t</div>";
			echo "\n</div>";

		}

		// Soundtrack.
		$soundtrack = $movie_results->soundtrack();
		$nbtotalsoundtracks = count( $soundtrack );
		if ( $nbtotalsoundtracks !== 0 ) {

			echo "\n\t\t\t\t\t\t\t" . ' <!-- Soundtrack -->';
			echo "\n" . '<div id="lumiere_popup_pluts_group">';
			echo "\n\t" . '<span class="imdbincluded-subtitle">' . esc_html( _n( 'Soundtrack', 'Soundtracks', $nbtotalsoundtracks, 'lumiere-movies' ) ) . '</span>';

			for ( $i = 0; $i < $nbtotalsoundtracks; $i++ ) {

				echo "\n\t\t";
				echo "\n\t\t\t" . esc_html( ucfirst( strtolower( $soundtrack[ $i ]['soundtrack'] ) ) );

				// @phpcs:ignore WordPress.Security.EscapeOutput
				echo "\n\t\t\t<i>" . str_replace(
					[ "\n", "\r", '<br>', '<br />' ],
					'',
					/**
					 * Use Highslide, Classical or No Links class links builder.
					 * Each one has its own class passed in $link_maker,
					 * according to which option the lumiere_select_link_maker() found in Frontend.
					 */
					$this->link_maker->lumiere_imdburl_to_internalurl( $soundtrack [ $i ]['credits_raw'] )
				) . '</i> ';

				if ( $i < $nbtotalsoundtracks - 1 ) {
					echo ', ';
				}

			}

			echo "\n</div>";

		}

		// Goof.
		$goof = $movie_results->goofs();
		$nbtotalgoof = count( $goof );

		if ( $nbtotalgoof > 0 ) {

			echo "\n\t\t\t\t\t\t\t" . ' <!-- Goofs -->';
			echo "\n" . '<div id="lumiere_popup_pluts_group">';
			echo "\n\t" . '<span class="imdbincluded-subtitle">' . esc_html( _n( 'Goof', 'Goofs', $nbtotalgoof, 'lumiere-movies' ) ) . '</span>';

			for ( $i = 0; $i < $nbtotalgoof; $i++ ) {
				$iterator_number = $i + 1;

				if ( $i === 0 ) {
					echo "\n\t" . '<div>'
					. '<strong>' . esc_html( $goof[ $i ]['type'] ) . '</strong>&nbsp;'
					// @phpcs:ignore WordPress.Security.EscapeOutput
					. $this->link_maker->lumiere_imdburl_to_internalurl( $goof[ $i ]['content'] )
					. '&nbsp;<span class="activatehidesection"><strong>(' . esc_html__( 'click to show more goofs', 'lumiere-movies' ) . ')</strong></span>'
					. "\n\t" . '<div class="hidesection">'
					. "\n\t\t" . '<br />';

				} elseif ( $i > 0 ) {
					echo "\n\t\t<strong>(" . intval( $iterator_number ) . ') ' . esc_html( $goof[ $i ]['type'] ) . '</strong>&nbsp;';
					// @phpcs:ignore WordPress.Security.EscapeOutput
					echo $this->link_maker->lumiere_imdburl_to_internalurl( $goof[ $i ]['content'] );
					echo "\n\t\t" . '<br />';
				}

			} //end endfor

			echo "\n\t" . '</div>';
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
		$nbactors = $this->imdb_widget_values['imdbwidgetactornumber'] === 0 ? '1' : intval( $this->imdb_widget_values['imdbwidgetactornumber'] );
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
				. '<a class="linkpopup" href="'
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
				. '<a class="linkpopup" href="'
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
				. '<a class="linkpopup" href="'
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
				. '<a class="linkpopup" href="'
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

			// Plot summary.
			$plotoutline = $movie_results->plotoutline();

		if ( strlen( $plotoutline ) > 0 ) {

			echo "\n\t\t\t\t\t\t\t" . ' <!-- Plot summary -->';
			echo "\n" . '<div id="lumiere_popup_plot_summary">';
			echo "\n\t" . '<span class="imdbincluded-subtitle">' . esc_html__( 'Plot summary', 'lumiere-movies' ) . '</span>';

			echo "\n\t" . '<div align="center" class="lumiere_container">';
			echo esc_html( $plotoutline );
			echo "\n\t</div>";
			echo "\n</div>";

		}

			// Plots.
			$plot = $movie_results->plot();
			$nbtotalplot = count( $plot );

		if ( $nbtotalplot > 1 ) { // Start with second plot, first is same as plotouline().

			echo "\n\t\t\t\t\t\t\t" . ' <!-- Plots -->';
			echo "\n" . '<div id="lumiere_popup_pluts_group">';
			echo "\n\t" . '<span class="imdbincluded-subtitle">' . esc_html( _n( 'Plot', 'Plots', $nbtotalplot, 'lumiere-movies' ) ) . '</span>';

			for ( $i = 1; $i < $nbtotalplot; $i++ ) {
				echo "\n\t" . '<div>';
				echo wp_kses( $plot[ $i ], self::ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS );
				if ( $i < $nbtotalplot - 1 ) {
					echo "\n<hr>";
				}
				echo "\n\t</div>";
			}

			echo "\n</div>";

		}
	}

	/**
	 * Static call of the current class Popup Movie
	 *
	 * @return void Build the class
	 */
	public static function lumiere_popup_movie_start (): void {

		new self();

	}

} // end of class


/**
 * Auto load the class
 * Conditions: not admin area
 */
//add_action( 'init', [ 'Lumiere\Popup_Movie', 'lumiere_popup_movie_start' ], 1 );

