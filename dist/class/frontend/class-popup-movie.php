<?php declare( strict_types = 1 );
/**
 * Popup for movies: Independant page that displays movie information inside a popup
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       2.0
 * @package lumiere-movies
 */

namespace Lumiere;

// If this file is called directly, abort.
if ( ( ! defined( 'ABSPATH' ) ) || ( ! class_exists( '\Lumiere\Settings' ) ) ) {
	wp_die( esc_html__( 'You are not allowed to call this page directly.', 'lumiere-movies' ) );
}

use \Imdb\Title;
use \Imdb\TitleSearch;

class Popup_Movie {

	use \Lumiere\Frontend;

	// Use trait frontend
	use Frontend {
		Frontend::__construct as public __constructFrontend;
	}

	/**
	 * The movie queried
	 */
	private ?object $movie;

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
	 * @var array $type_search
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

		/* GET Vars sanitized */
		$this->movieid_sanitized = isset( $_GET['mid'] ) ? filter_var( $_GET['mid'], FILTER_SANITIZE_NUMBER_INT ) : null;
		$this->film_title_sanitized = isset( $_GET['film'] ) ? $this->utils_class->lumiere_name_htmlize( $_GET['film'] ) : null;

		// Display layout
		add_action( 'wp', [ $this, 'lumiere_popup_movie_layout' ], 1 );

	}

	/**
	 *  Search movie title
	 *
	 */
	private function find_movie(): bool {

		do_action( 'lumiere_logger' );

		// if neither film nor mid are set, redirect to class PopupSearch
		if ( ( empty( $this->movieid_sanitized ) && empty( $this->film_title_sanitized ) ) || ( is_null( $this->movieid_sanitized ) && is_null( $this->film_title_sanitized ) ) ) {

			status_header( 404 );
			$this->logger->log()->error( '[Lumiere] No movie title or id entered' );
			wp_die( esc_html__( 'Lumière Movies: Invalid search request.', 'lumiere-movies' ) );

		}

		// A movie imdb id is provided
		if ( ( isset( $this->movieid_sanitized ) ) && ( ! empty( $this->movieid_sanitized ) ) ) {

			$this->movie = new Title( $this->movieid_sanitized, $this->imdbphp_class, $this->logger->log() );
			$this->film_title_sanitized = $this->utils_class->lumiere_name_htmlize( $this->movie->title() );
			return true;

		}

		// No movie id is provided, use the title to get the movie.
		if ( ( isset( $this->film_title_sanitized ) ) && ( ! empty( $this->film_title_sanitized ) ) ) {

			$titleSearchClass = new TitleSearch( $this->imdbphp_class, $this->logger->log() );
			$search = $titleSearchClass->search( $this->film_title_sanitized, $this->type_search );
			if ( array_key_exists( 0, $search ) === false ) {
				$this->movie = null;
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
			echo ' lumiere_body_' . $this->imdb_admin_values['imdbpopuptheme'];
		}
		?>">

		<?php
		// Get the movie's title
		$this->find_movie();
		$movie_results = $this->movie;
		// If no movie was found, exit.
		if ( $movie_results === null ) {
			$this->logger->log()->error( '[Lumiere] No movie found.' );
			wp_die( esc_html__( 'Lumiere movies: No movie found with this title', 'lumiere-movies' ) );
		}
		?>

		<?php $this->display_menu( $movie_results ); ?>

		<?php $this->display_portrait( $movie_results ); ?>

		<?php

			// display something when nothing has been selected in the menu
		if ( ( ! isset( $_GET['info'] ) ) || ( empty( $_GET['info'] ) ) ) {

			//---------------------------------------------------------------------------introduction part start

			$this->display_intro( $movie_results );

			/*
											<!-- Sound -->
			$sound = $this->movie->sound () ?? NULL;

			if ( (isset($sound)) && (!empty($sound)) ) { ?>
			<tr>
			<td class="TitreSousRubriqueColGauche">
				<div class="TitreSousRubrique"><?php esc_html_e('Sound', 'lumiere-movies'); ?>&nbsp;</div>
			</td>

			<td colspan="2" class="TitreSousRubriqueColDroite">
			<li><?php
				for ($i = 0; $i + 1 < count ($sound); $i++) {
					echo sanitize_text_field( $sound[$i] );
					echo ", ";
				}
				echo sanitize_text_field( $sound[0] );
			?></li>
			</td>
			</tr>
			<?php
			} */

		}   //------------------------------------------------------------------------------ introduction part end

			// ------------------------------------------------------------------------------ casting part start
		if ( ( isset( $_GET['info'] ) ) && ( $_GET['info'] === 'actors' ) ) {

			// Actors.
			$cast = $this->movie->cast();
			$nbactors = empty( $this->imdb_widget_values['imdbwidgetactornumber'] ) ? $nbactors = '1' : $nbactors = intval( $this->imdb_widget_values['imdbwidgetactornumber'] );
			$nbtotalactors = intval( count( $cast ) );

			if ( isset( $cast ) && ! empty( $cast ) ) {

				echo "\n\t\t\t\t\t\t\t\t\t\t<!-- Actors -->";
				echo "\n\t" . '<div class="imdbincluded-subtitle">' . esc_attr( sprintf( _n( 'Actor', 'Actors', $nbtotalactors, 'lumiere-movies' ) ), number_format_i18n( $nbtotalactors ) ) . '</div>';

				for ( $i = 0; ( $i < $nbtotalactors ); $i++ ) {
					echo "\n\t\t" . '<div align="center" class="lumiere_container">';
					echo "\n\t\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';
					echo sanitize_text_field( $cast[ $i ]['role'] );
					echo '</div>';
					echo "\n\t\t\t" . '<div class="lumiere_align_right lumiere_flex_auto">';
					echo "\n\t\t\t\t"
					. '<a class="linkpopup" href="'
					. esc_url(
						$this->config_class->lumiere_urlpopupsperson
						. $cast[ $i ]['imdb']
						. '/?mid=' . $cast[ $i ]['imdb']
						. '&film=' . $this->film_title_sanitized
					)
						. '" title="'
						. esc_html__( 'link to imdb', 'lumiere-movies' )
						. '">';
					echo "\n\t\t\t\t" . sanitize_text_field( $cast[ $i ]['name'] );
					echo '</a>';
					echo "\n\t\t\t</div>";
					echo "\n\t\t</div>";
					echo "\n\t</div>";

				} // endfor

			} //end endisset

		}

			// ------------------------------------------------------------------------------ crew part start

		if ( ( isset( $_GET['info'] ) ) && ( $_GET['info'] === 'crew' ) ) {

			// Directors.
			$director = $movie_results->director();
			if ( ( isset( $director ) ) && ( ! empty( $director ) ) ) {

				$nbtotaldirector = count( $director );

				echo "\n\t\t\t\t\t\t\t" . ' <!-- director -->';
				echo "\n" . '<div id="lumiere_popup_director_group">';
				echo "\n\t" . '<span class="imdbincluded-subtitle">' . _n( 'Director', 'Directors', $nbtotaldirector, 'lumiere-movies' ) . '</span>';

				for ( $i = 0; $i < $nbtotaldirector; $i++ ) {
					echo "\n\t" . '<div align="center" class="lumiere_container">';
					echo "\n\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';
					echo "\n\t\t"
					. '<a class="linkpopup" href="'
					. esc_url(
						$this->config_class->lumiere_urlpopupsperson
						. $director[ $i ]['imdb']
						. '/?mid=' . $director[ $i ]['imdb']
						. '&film=' . $this->film_title_sanitized
					)
						. '" title="'
						. esc_html__( 'link to imdb', 'lumiere-movies' )
						. '">';

					echo "\n\t\t" . sanitize_text_field( $director[ $i ]['name'] );
					echo "\n\t\t</a>";
					echo "\n\t\t</div>";
					echo "\n\t\t" . '<div class="lumiere_align_right lumiere_flex_auto">';
					echo sanitize_text_field( $director[ $i ]['role'] );
					echo "\n\t\t" . '</div>';
					echo "\n\t</div>";
					echo "\n</div>";

				} // endfor

			}

			// Writers.
			$writer = $movie_results->writing();
			if ( ( isset( $writer ) ) && ( ! empty( $writer ) ) ) {

				$nbtotalwriter = count( $writer );

				echo "\n\t\t\t\t\t\t\t" . ' <!-- writers -->';
				echo "\n" . '<div id="lumiere_popup_director_group">';
				echo "\n\t" . '<span class="imdbincluded-subtitle">' . _n( 'Writer', 'Writers', $nbtotalwriter, 'lumiere-movies' ) . '</span>';

				for ( $i = 0; $i < $nbtotalwriter; $i++ ) {
					echo "\n\t" . '<div align="center" class="lumiere_container">';
					echo "\n\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';
					echo "\n\t\t"
					. '<a class="linkpopup" href="'
					. esc_url(
						$this->config_class->lumiere_urlpopupsperson
						. $writer[ $i ]['imdb']
						. '/?mid=' . $writer[ $i ]['imdb']
						. '&film=' . $this->film_title_sanitized
					)
						. '" title="'
						. esc_html__( 'link to imdb', 'lumiere-movies' )
						. '">';
					echo "\n\t\t" . sanitize_text_field( $writer[ $i ]['name'] );
					echo "\n\t\t</a>";
					echo "\n\t\t</div>";
					echo "\n\t\t" . '<div class="lumiere_align_right lumiere_flex_auto">';
					echo sanitize_text_field( $writer[ $i ]['role'] );
					echo "\n\t\t" . '</div>';
					echo "\n\t</div>";
					echo "\n</div>";
				} // endfor
			}

			// Producers.
			$producer = $movie_results->producer();
			if ( ( isset( $producer ) ) && ( ! empty( $producer ) ) ) {

				$nbtotalproducer = count( $producer );

				echo "\n\t\t\t\t\t\t\t" . ' <!-- writers -->';
				echo "\n" . '<div id="lumiere_popup_writer_group">';
				echo "\n\t" . '<span class="imdbincluded-subtitle">' . _n( 'Producer', 'Producers', $nbtotalproducer, 'lumiere-movies' ) . '</span>';

				for ( $i = 0; $i < $nbtotalproducer; $i++ ) {
					echo "\n\t" . '<div align="center" class="lumiere_container">';
					echo "\n\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';
					echo "\n\t\t"
					. '<a class="linkpopup" href="'
					. esc_url(
						$this->config_class->lumiere_urlpopupsperson
						. $producer[ $i ]['imdb']
						. '/?mid=' . $producer[ $i ]['imdb']
						. '&film=' . $this->film_title_sanitized
					)
						. '" title="'
						. esc_html__( 'link to imdb', 'lumiere-movies' )
						. '">';
					echo "\n\t\t" . sanitize_text_field( $producer[ $i ]['name'] );
					echo "\n\t\t</a>";
					echo "\n\t\t</div>";
					echo "\n\t\t" . '<div class="lumiere_align_right lumiere_flex_auto">';
					echo sanitize_text_field( $producer[ $i ]['role'] );
					echo "\n\t\t" . '</div>';
					echo "\n\t</div>";
					echo "\n</div>";
				} // endfor
			}

		}   //----------------------------------------------------------------------------- crew part end

			// ------------------------------------------------------------------------------ resume part start
		if ( ( isset( $_GET['info'] ) ) && ( $_GET['info'] === 'resume' ) ) {

			// Plot summary.

			$plotoutline = $movie_results->plotoutline();

			if ( ( isset( $plotoutline ) ) && ( ! empty( $plotoutline ) ) ) {

				echo "\n\t\t\t\t\t\t\t" . ' <!-- Plot summary -->';
				echo "\n" . '<div id="lumiere_popup_plot_summary">';
				echo "\n\t" . '<span class="imdbincluded-subtitle">' . esc_html__( 'Plot summary', 'lumiere-movies' ) . '</span>';

				echo "\n\t" . '<div align="center" class="lumiere_container">';
				echo sanitize_text_field( $plotoutline );
				echo "\n\t</div>";
				echo "\n</div>";

			}

			// Plots.

			$plot = $movie_results->plot();
			$nbtotalplot = count( $plot );

			if ( ( isset( $plot ) ) && ( ! empty( $plot ) ) ) {

				echo "\n\t\t\t\t\t\t\t" . ' <!-- Plots -->';
				echo "\n" . '<div id="lumiere_popup_pluts_group">';
				echo "\n\t" . '<span class="imdbincluded-subtitle">' . _n( 'Plot', 'Plots', $nbtotalplot, 'lumiere-movies' ) . '</span>';

				for ( $i = 1; $i < $nbtotalplot; $i++ ) {
					echo "\n\t" . '<div>';
					echo sanitize_text_field( $plot[ $i ] );
					if ( $i < $nbtotalplot - 1 ) {
						echo "\n<hr>";
					}
					echo "\n\t</div>";
				} //end endfor

				echo "\n</div>";

			}

		}

			// ------------------------------------------------------------------------------ misc part start
		if ( ( isset( $_GET['info'] ) ) && ( $_GET['info'] === 'divers' ) ) {

			$this->display_misc( $movie_results );

		} // ------------------------------------------------------------------------------ misc part end

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
	 *
	 */
	public function display_menu( object $movie_results ) {
		?>
												<!-- top page menu -->

		<div class="lumiere_container lumiere_font_em_11 lumiere_titlemenu">
			<div class="lumiere_flex_auto">
				&nbsp;<a class="searchaka" href="<?php echo esc_url( $this->config_class->lumiere_urlpopupsearch . '?film=' . $this->film_title_sanitized . '&norecursive=yes' ); ?>" title="<?php esc_html_e( 'Search for other movies with the same title', 'lumiere-movies' ); ?>"><?php esc_html_e( 'Similar Titles', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				&nbsp;<a class='linkpopup' href="<?php echo esc_url( $this->config_class->lumiere_urlpopupsfilms . $this->film_title_sanitized . '/?mid=' . $movie_results->imdbid() . '&film=' . $this->film_title_sanitized . '&info=' ); ?>" title='<?php echo sanitize_title( $movie_results->title() ) . ': ' . esc_html__( 'Movie', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Summary', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				&nbsp;<a class='linkpopup' href="<?php echo esc_url( $this->config_class->lumiere_urlpopupsfilms . $this->film_title_sanitized . '/?mid=' . $movie_results->imdbid() . '&film=' . $this->film_title_sanitized . '&info=actors' ); ?>" title='<?php echo esc_html( $movie_results->title() ) . ': ' . esc_html__( 'Actors', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Actors', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				&nbsp;<a class='linkpopup' href="<?php echo esc_url( $this->config_class->lumiere_urlpopupsfilms . $this->film_title_sanitized . '/?mid=' . $movie_results->imdbid() . '&film=' . $this->film_title_sanitized . '&info=crew' ); ?>" title='<?php echo esc_html( $movie_results->title() ) . ': ' . esc_html__( 'Crew', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Crew', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				&nbsp;<a class='linkpopup' href="<?php echo esc_url( $this->config_class->lumiere_urlpopupsfilms . $this->film_title_sanitized . '/?mid=' . $movie_results->imdbid() . '&film=' . $this->film_title_sanitized . '&info=resume' ); ?>" title='<?php echo esc_html( $movie_results->title() ) . ': ' . esc_html__( 'Plots', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Plots', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				&nbsp;<a class='linkpopup' href="<?php echo esc_url( $this->config_class->lumiere_urlpopupsfilms . $this->film_title_sanitized . '/?mid=' . $movie_results->imdbid() . '&film=' . $this->film_title_sanitized . '&info=divers' ); ?>" title='<?php echo esc_html( $movie_results->title() ) . ': ' . esc_html__( 'Misc', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Misc', 'lumiere-movies' ); ?></a>
			</div>
		</div>
		<?php
	}

	/**
	 * Show the portrait (title, picture)
	 */
	public function display_portrait( object $movie_results ) {
		?>
		<div class="lumiere_display_flex lumiere_font_em_11">
			<div class="lumiere_flex_auto lumiere_width_eighty_perc">
				<div class="titrefilm">
				<?php
					// Get movie's title from imdbphp query, not from globals.
					echo sanitize_text_field( $movie_results->title() );
				?>
				&nbsp;(<?php echo sanitize_text_field( $movie_results->year() ); ?>)</div>
				<div class="lumiere_align_center"><font size="-1"><?php echo sanitize_text_field( $movie_results->tagline() ); ?></font></div>
			</div> 
			<div class="lumiere_flex_auto lumiere_width_twenty_perc lumiere_padding_two">
												<!-- Movie's picture display -->
			<?php
				## The picture is either taken from the movie itself or if it doesn't exist, from a standard "no exist" picture.
				## The width value is taken from plugin settings, and added if the "thumbnail" option is unactivated

				$small_picture = $movie_results->photo_localurl( false ); // get small poster for cache
				$big_picture = $movie_results->photo_localurl( true ); // get big poster for cache
				$photo_url = $small_picture ? $small_picture : $big_picture; // take the smaller first, the big if no small found
			if ( ( isset( $photo_url ) ) && ( ! empty( $photo_url ) ) ) {

				echo '<a class="highslide_pic_popup" class="highslide-image" href="' . esc_url( $photo_url ) . '">';
				// loading="eager" to prevent WordPress loading lazy that doesn't go well with cache scripts.
				echo "\n\t\t" . '<img loading="eager" class="imdbincluded-picture" src="';
				echo esc_url( $photo_url ) . '" alt="' . esc_attr( $movie_results->title() ) . '"';
				// add width only if "Display only thumbnail" is on "no"
				if ( $this->imdb_admin_values['imdbcoversize'] === false ) {
					echo ' width="' . intval( $this->imdb_admin_values['imdbcoversizewidth'] ) . 'px"';
				}
				echo ' />';
				echo '</a>';

			} else {

				echo '<a class="highslide_pic_popup">';
				echo "\n\t\t"
				. '<img loading="eager" class="imdbincluded-picture" src="'
				. esc_url( $this->imdb_admin_values['imdbplugindirectory'] . 'pics/no_pics.gif' )
				. '" alt="'
				. esc_html__( 'no picture', 'lumiere-movies' )
				. '" ';

				// add width only if "Display only thumbnail" is on "no".
				if ( $this->imdb_admin_values['imdbcoversize'] === false ) {
					echo ' width="' . intval( $this->imdb_admin_values['imdbcoversizewidth'] ) . 'px"';
				}
				echo ' />';
				echo '</a>';
			}
			?>

			</div> 
		</div> 
		<?php
	}

	/**
	 * Show intro part
	 */
	private function display_intro( object $movie_results ) {
		// Director summary, limited by admin options.
		$director = $movie_results->director();
		// director shown only if selected so in options.
		if ( ( isset( $director ) ) && ( ! empty( $director ) ) && ( $this->imdb_widget_values['imdbwidgetdirector'] === '1' ) ) {

			$nbtotaldirector = count( $director );
			echo "\n\t\t\t\t\t\t\t\t\t\t<!-- Director -->";
			echo "\n\t<div>";

			echo '<span class="imdbincluded-subtitle">'
			. _n( 'Director', 'Directors', $nbtotaldirector, 'lumiere-movies' )
			. '</span>';
			for ( $i = 0; $i < $nbtotaldirector; $i++ ) {

				echo '<a class="linkpopup" href="'
				. esc_url(
					$this->config_class->lumiere_urlpopupsperson . $director[ $i ]['imdb']
					. '/?mid=' . $director[ $i ]['imdb'] . '&film=' . $this->film_title_sanitized
				)
					. '" title="' . esc_html__( 'link to imdb', 'lumiere-movies' ) . '">';
				echo "\n\t\t\t" . sanitize_text_field( $director[ $i ]['name'] );
				if ( $i < $nbtotaldirector - 1 ) {
					echo ', ';
				}

				echo '</a>';

			} // endfor

			echo "\n\t</div>";

		}

		// Main actors, limited by admin options.
		$cast = $movie_results->cast();
		$nbactors = empty( $this->imdb_widget_values['imdbwidgetactornumber'] ) ? $nbactors = '1' : $nbactors = intval( $this->imdb_widget_values['imdbwidgetactornumber'] );
		$nbtotalactors = intval( count( $cast ) );

		// actor shown only if selected so in options.
		if ( ( isset( $cast ) ) && ( ! empty( $cast ) ) && ( $this->imdb_widget_values['imdbwidgetactor'] === '1' ) ) {

			echo "\n\t\t\t\t\t\t\t\t\t\t<!-- Main actors -->";
			echo "\n\t<div>";

			echo '<span class="imdbincluded-subtitle">' . esc_html__( 'Main actors', 'lumiere-movies' ) . '</span>';

			for ( $i = 0; ( $i < $nbactors ) && ( $i < $nbtotalactors ); $i++ ) {
				echo '<a class="linkpopup" href="' . esc_url( $this->config_class->lumiere_urlpopupsperson . $cast[ $i ]['imdb'] . '/?mid=' . $cast[ $i ]['imdb'] ) . '" title="' . esc_html__( 'link to imdb', 'lumiere-movies' ) . '">';
				echo "\n\t\t\t" . sanitize_text_field( $cast[ $i ]['name'] ) . '</a>';

				if ( ( $i < $nbactors - 1 ) && ( $i < $nbtotalactors - 1 ) ) {
					echo ', ';
				}
			} // endfor

			echo '</div>';

		}

		// Runtime, limited by admin options.
		$runtime = sanitize_text_field( $movie_results->runtime() );
		// runtime shown only if selected so in admin options.
		if ( ( ! empty( $runtime ) ) && ( $this->imdb_widget_values['imdbwidgetruntime'] === '1' ) ) {

			echo "\n\t\t\t\t\t\t\t\t\t\t<!-- Runtime -->";
			echo "\n\t<div>";
			echo '<span class="imdbincluded-subtitle">'
			. esc_html__( 'Runtime', 'lumiere-movies' )
			. '</span>'
			. $runtime
			. ' '
			. esc_html__( 'minutes', 'lumiere-movies' );
			echo "\n\t</div>";

		}

		// Votes, limited by admin options.
		// rating shown only if selected so in options.
		if ( ( null !== ( $movie_results->votes() ) ) && ( $this->imdb_widget_values['imdbwidgetrating'] === '1' ) ) {
			$votes_sanitized = intval( $movie_results->votes() );
			$rating_sanitized = esc_html( $movie_results->rating() );

			echo "\n\t\t\t\t\t\t\t\t\t\t<!-- Rating -->";
			echo "\n\t<div>";

			echo '<span class="imdbincluded-subtitle">'
				. esc_html__( 'Rating', 'lumiere-movies' )
				. '</span>';
			echo ' <img src="' . $this->imdb_admin_values['imdbplugindirectory'] . 'pics/showtimes/' . ( round( $rating_sanitized * 2, 0 ) / 0.2 )
			. '.gif" title="' . esc_html__( 'vote average ', 'lumiere-movies' ) . $rating_sanitized . esc_html__( ' out of 10', 'lumiere-movies' ) . '"  / >';
			echo ' (' . number_format( $votes_sanitized, 0, '', "'" ) . ' ' . esc_html__( 'votes', 'lumiere-movies' ) . ')';

			echo "\n\t</div>";

		}

		// Language, limited by admin options.
		$languages = $movie_results->languages();
		$nbtotallanguages = count( $languages );
		// language shown only if selected so in options.
		if ( ( ( isset( $languages ) ) && ( ! empty( $languages ) ) ) && ( $this->imdb_widget_values['imdbwidgetlanguage'] === '1' ) ) {

			echo "\n\t\t\t\t\t\t\t<!-- Language -->";
			echo "\n\t<div>";

			echo '<span class="imdbincluded-subtitle">'
			. sprintf( esc_attr( _n( 'Language', 'Languages', $nbtotallanguages, 'lumiere-movies' ) ) )
			. '</span>';
			for ( $i = 0; $i < $nbtotallanguages; $i++ ) {
				echo sanitize_text_field( $languages[ $i ] );
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
		if ( ( ( isset( $country ) ) && ( ! empty( $country ) ) ) && ( $this->imdb_widget_values['imdbwidgetcountry'] === '1' ) ) {

			echo "\n\t\t\t\t\t\t\t\t\t\t<!-- Country -->";
			echo "\n\t<div>";

			echo '<span class="imdbincluded-subtitle">'
			. sprintf( esc_attr( _n( 'Country', 'Countries', $nbtotalcountry, 'lumiere-movies' ) ) )
			. '</span>';
			for ( $i = 0; $i < $nbtotalcountry; $i++ ) {
				echo sanitize_text_field( $country[ $i ] );
				if ( $i < $nbtotalcountry - 1 ) {
					echo ', ';
				}
			}

			echo "\n\t</div>";

		}

		$genre = $movie_results->genre();

		// Genre shown only if selected so in options.
		if ( ( ( isset( $genre ) ) && ( ! empty( $genre ) ) ) && ( $this->imdb_widget_values['imdbwidgetgenre'] === '1' ) ) {

			$gen = $movie_results->genres();
			$nbtotalgenre = count( $gen );

			echo "\n\t\t\t\t\t\t\t\t\t\t<!-- Genre -->";
			echo "\n\t<div>";

			echo '<span class="imdbincluded-subtitle">'
			. sprintf( esc_attr( _n( 'Genre', 'Genres', $nbtotalgenre, 'lumiere-movies' ) ) )
			. '</span>';

			for ( $i = 0; $i < $nbtotalgenre; $i++ ) {
				echo sanitize_text_field( $gen[ $i ] );
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
	private function display_misc( object $movie_results ) {

		// Trivia.

		$trivia = $movie_results->trivia();
		$nbtotaltrivia = count( $trivia );

		if ( ( isset( $trivia ) ) && ( ! empty( $trivia ) ) ) {

			echo "\n\t\t\t\t\t\t\t" . ' <!-- Trivia -->';
			echo "\n" . '<div id="lumiere_popup_pluts_group">';
			echo "\n\t" . '<span class="imdbincluded-subtitle">' . _n( 'Trivia', 'Trivias', $nbtotaltrivia, 'lumiere-movies' ) . '</span>';

			for ( $i = 0; $i < $nbtotaltrivia; $i++ ) {
				$iterator_number = $i + 1;

				if ( $i === 0 ) {
					echo "\n\t" . '<div>'
					. preg_replace( '/https\:\/\/' . str_replace( '.', '\.', $movie_results->imdbsite ) . '\/name\/nm(\d{7})\//', $this->config_class->lumiere_urlpopupsperson . "popup-imdb_person.php?mid=\\1 class=\"linkpopup\"", sanitize_text_field( $trivia[ $i ] ) )
					. '&nbsp;&nbsp;&nbsp;'
					. '<span class="activatehidesection"><strong>(' . esc_html__( 'click to show more trivias', 'lumiere-movies' ) . ')</strong></span>'
					. "\n\t" . '<div class="hidesection">'
					. '<br />';

				} elseif ( $i > 0 ) {
					echo "\n\t\t<strong>($iterator_number)</strong>&nbsp;" . preg_replace( '/https\:\/\/' . str_replace( '.', '\.', $movie_results->imdbsite ) . '\/name\/nm(\d{7})\//', $this->config_class->lumiere_urlpopupsperson . "popup-imdb_person.php?mid=\\1 class=\"linkpopup\"", sanitize_text_field( $trivia[ $i ] ) )
					. "\n\t\t<hr>";
				}

			} //end endfor

			echo "\n\t" . '</div>';
			echo "\n\t</div>";

			echo "\n</div>";

		}

		// Soundtrack.

		$soundtrack = $movie_results->soundtrack();
		$nbtotalsoundtrack = count( $soundtrack );
		if ( ( isset( $soundtrack ) ) && ( ! empty( $soundtrack ) ) ) {

			echo "\n\t\t\t\t\t\t\t" . ' <!-- Soundtrack -->';
			echo "\n" . '<div id="lumiere_popup_pluts_group">';
			echo "\n\t" . '<span class="imdbincluded-subtitle">' . _n( 'Soundtrack', 'Soundtracks', $nbtotalsoundtrack, 'lumiere-movies' ) . '</span>';

			for ( $i = 0; $i < $nbtotalsoundtrack; $i++ ) {

				$credit = preg_replace( '/http\:\/\/' . str_replace( '.', '\.', $movie_results->imdbsite ) . '\/name\/nm(\d{7})\//', $this->config_class->lumiere_urlpopupsperson . "popup-imdb_person.php?mid=\\1 class=\"linkpopup\"", sanitize_text_field( $soundtrack[ $i ]['credits'][0]['credit_to'] ) );
				echo "\n\t\t"
				. $credit
				. '&nbsp;<i>'
				. sanitize_text_field( $soundtrack[ $i ]['soundtrack'] )
				. '</i>';

				if ( $i < $nbtotalsoundtrack - 1 ) {
					echo ', ';
				}

			} //end endfor

			echo "\n</div>";

		}

		// Goof.

		$goof = $movie_results->goofs();
		$nbtotalgoof = count( $goof );

		if ( ( isset( $goof ) ) && ( ! empty( $goof ) ) ) {

			echo "\n\t\t\t\t\t\t\t" . ' <!-- Goofs -->';
			echo "\n" . '<div id="lumiere_popup_pluts_group">';
			echo "\n\t" . '<span class="imdbincluded-subtitle">' . _n( 'Goof', 'Goofs', $nbtotalgoof, 'lumiere-movies' ) . '</span>';

			for ( $i = 0; $i < $nbtotalgoof; $i++ ) {
				$iterator_number = $i + 1;

				if ( $i === 0 ) {
					echo "\n\t" . '<div>'
					. '<strong>' . sanitize_text_field( $goof[ $i ]['type'] ) . '</strong>&nbsp;'
					. sanitize_text_field( $goof[ $i ]['content'] )
					. '&nbsp;<span class="activatehidesection"><strong>(' . esc_html__( 'click to show more goofs', 'lumiere-movies' ) . ')</strong></span>'
					. "\n\t" . '<div class="hidesection">'
					. "\n\t\t" . '<br />';

				} elseif ( $i > 0 ) {
					echo "\n\t\t<strong>($iterator_number) " . sanitize_text_field( $goof[ $i ]['type'] ) . '</strong>&nbsp;'
					. sanitize_text_field( $goof[ $i ]['content'] );
					echo "\n\t\t" . '<br />';
				}

			} //end endfor

			echo "\n\t" . '</div>';
			echo "\n\t</div>";

			echo "\n</div>";

		}

	}

}

new Popup_Movie();

