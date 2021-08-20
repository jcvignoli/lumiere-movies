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

use \Lumiere\Settings;
use \Lumiere\Utils;

class PopupMovie {

	/**
	 * Class \Lumiere\Utils
	 *
	 */
	private $utils_class;

	/**
	 * Class \Lumiere\Settings
	 *
	 */
	private $config_class;

	/**
	 * Class \Monolog\Logger
	 *
	 */
	private $logger;

	/**
	 * Settings from class \Lumiere\Settings
	 *
	 */
	private $imdb_admin_values;
	private $imdb_widget_values;

	/**
	 * Settings from class \Lumiere\Settings
	 * To include the type of (movie, TVshow, Games) search
	 */
	private $type_search;

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		//As an external file, need to include manually bootstrap
		require_once plugin_dir_path( __DIR__ ) . 'bootstrap.php';

		if ( ! class_exists( '\Lumiere\Settings' ) ) {
			wp_die( esc_html__( 'Cannot start popup movie, class Lumière Settings not found', 'lumiere-movies' ) );
		}

		// Get database options
		$this->imdb_admin_values = get_option( Settings::LUMIERE_ADMIN_OPTIONS );
		$this->imdb_widget_values = get_option( Settings::LUMIERE_WIDGET_OPTIONS );

		$this->config_class = new Settings( 'popupMovie' );

		// Start the class Utils
		$this->utils_class = new Utils();

		// Get the type of search: movies, series, games
		$this->type_search = $this->config_class->lumiere_select_type_search();

		// Start the logger
		$this->config_class->lumiere_start_logger( 'popupMovie' );
		$this->logger = $this->config_class->loggerclass;

		// Start the debugging
		add_action( 'wp_head', [ $this, 'lumiere_maybe_start_debug' ], 0 );

		// Display layout
		$this->layout();

	}

	/**
	 *  Wrapps the start of the logger
	 *  Allows to start later in the process
	 */
	public function lumiere_maybe_start_debug() {

		if ( ( isset( $this->imdb_admin_values['imdbdebug'] ) ) && ( '1' === $this->imdb_admin_values['imdbdebug'] ) && ( $this->utils_class->debug_is_active === false ) ) {

			$this->utils_class->lumiere_activate_debug( null, 'no_var_dump' );

		}
	}

	/**
	 *  Display layout
	 *
	 */
	private function layout(): string {

		/* GET Vars sanitized */
		$movieid_sanitized = isset( $_GET['mid'] ) ? filter_var( $_GET['mid'], FILTER_SANITIZE_NUMBER_INT ) : null;
		$filmid_sanitized = isset( $_GET['film'] ) ? $this->utils_class->lumiere_name_htmlize( $_GET['film'] ) : null;
		$film_sanitized_for_title = isset( $_GET['film'] ) ? sanitize_text_field( $_GET['film'] ) : null;

		// if neither film nor mid are set, throw a 404 error
		if ( empty( $movieid_sanitized ) && empty( $filmid_sanitized ) ) {

			global $wp_query;

			$wp_query->set_404();

			// In case you need to make sure that `have_posts()` return false.
			// Maybe there's a reset function on WP_Query but I couldn't find one.
			$wp_query->post_count = 0;
			$wp_query->posts = [];
			$wp_query->post = false;

			status_header( 404 );

			$template = get_404_template();
			return $template;

		}

		if ( ( isset( $movieid_sanitized ) ) && ( ! empty( $movieid_sanitized ) ) && ( ! empty( $this->config_class ) ) ) {

			$movie = new \Imdb\Title( $movieid_sanitized, $this->config_class, $this->logger );
			$filmid_sanitized = $this->utils_class->lumiere_name_htmlize( $movie->title() );
			$film_sanitized_for_title = sanitize_text_field( $movie->title() );

		} elseif ( ! empty( $this->config_class ) ) {

			$titleSearchClass = new \Imdb\TitleSearch( $this->config_class, $this->logger );
			$movie = $titleSearchClass->search( $filmid_sanitized, $this->type_search )[0];

		} else {

			wp_die( esc_html__( 'No config option set', 'lumiere-movies' ) );
		}

		//------------------------- 1. search all results related to the name of the movie
		if ( ( isset( $_GET['norecursive'] ) ) && ( $_GET['norecursive'] === 'yes' ) ) {

			$results = $titleSearchClass->search( $filmid_sanitized, $this->type_search );
			$this->lumiere_popupup_search_title( $results, $film_sanitized_for_title );

			//------------------------- 2. accès direct, option spéciale

		} else {

			?><!DOCTYPE html>
		<html>
		<head>
			<?php wp_head(); ?>

		</head>
		<body class="lumiere_body
			<?php
			if ( isset( $this->imdb_admin_values['imdbpopuptheme'] ) ) {
				echo ' lumiere_body_' . $this->imdb_admin_values['imdbpopuptheme'];}
			?>
		">
												<!-- top page menu -->

		<div class="lumiere_container lumiere_font_em_11 lumiere_titlemenu">
			<div class="lumiere_flex_auto">
				&nbsp;<a class="searchaka" href="<?php echo esc_url( $this->config_class->lumiere_urlpopupssearch . '?film=' . $filmid_sanitized . '&norecursive=yes' ); ?>" title="<?php esc_html_e( 'Search for other movies with the same title', 'lumiere-movies' ); ?>"><?php esc_html_e( 'Similar Titles', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				&nbsp;<a class='linkpopup' href="<?php echo esc_url( $this->config_class->lumiere_urlpopupsfilms . $filmid_sanitized . '/?mid=' . $movieid_sanitized . '&film=' . $filmid_sanitized . '&info=' ); ?>" title='<?php echo sanitize_title( $movie->title() ) . ': ' . esc_html__( 'Movie', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Summary', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				&nbsp;<a class='linkpopup' href="<?php echo esc_url( $this->config_class->lumiere_urlpopupsfilms . $filmid_sanitized . '/?mid=' . $movieid_sanitized . '&film=' . $filmid_sanitized . '&info=actors' ); ?>" title='<?php echo esc_html( $movie->title() ) . ': ' . esc_html__( 'Actors', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Actors', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				&nbsp;<a class='linkpopup' href="<?php echo esc_url( $this->config_class->lumiere_urlpopupsfilms . $filmid_sanitized . '/?mid=' . $movieid_sanitized . '&film=' . $filmid_sanitized . '&info=crew' ); ?>" title='<?php echo esc_html( $movie->title() ) . ': ' . esc_html__( 'Crew', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Crew', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				&nbsp;<a class='linkpopup' href="<?php echo esc_url( $this->config_class->lumiere_urlpopupsfilms . $filmid_sanitized . '/?mid=' . $movieid_sanitized . '&film=' . $filmid_sanitized . '&info=resume' ); ?>" title='<?php echo esc_html( $movie->title() ) . ': ' . esc_html__( 'Plots', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Plots', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				&nbsp;<a class='linkpopup' href="<?php echo esc_url( $this->config_class->lumiere_urlpopupsfilms . $filmid_sanitized . '/?mid=' . $movieid_sanitized . '&film=' . $filmid_sanitized . '&info=divers' ); ?>" title='<?php echo esc_html( $movie->title() ) . ': ' . esc_html__( 'Misc', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Misc', 'lumiere-movies' ); ?></a>
			</div>
		</div>

		<div class="lumiere_display_flex lumiere_font_em_11">
			<div class="lumiere_flex_auto lumiere_width_eighty_perc">
				<div class="titrefilm">
				<?php
				$title_sanitized = sanitize_text_field( $movie->title() );
				echo $title_sanitized;
				?>
				&nbsp;&nbsp;(<?php echo sanitize_text_field( $movie->year() ); ?>)</div>
				<div class="lumiere_align_center"><font size="-1"><?php echo sanitize_text_field( $movie->tagline() ); ?></font></div>
			</div> 
			<div class="lumiere_flex_auto lumiere_width_twenty_perc lumiere_padding_two">
												<!-- Movie's picture display -->
			<?php
				## The picture is either taken from the movie itself or if it doesn't exist, from a standard "no exist" picture.
				## The width value is taken from plugin settings, and added if the "thumbnail" option is unactivated

				$small_picture = $movie->photo_localurl( false ); // get small poster for cache
				$big_picture = $movie->photo_localurl( true ); // get big poster for cache
				$photo_url = $small_picture ? $small_picture : $big_picture; // take the smaller first, the big if no small found
			if ( ( isset( $photo_url ) ) && ( ! empty( $photo_url ) ) ) {

				echo '<a class="highslide_pic_popup" class="highslide-image" href="' . esc_url( $photo_url ) . '">';
				// loading="eager" to prevent WordPress loading lazy that doesn't go well with cache scripts.
				echo "\n\t\t" . '<img loading="eager" class="imdbincluded-picture" src="';
				echo esc_url( $photo_url ) . '" alt="' . esc_attr( $movie->title() ) . '"';
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

			// display something when nothing has been selected in the menu
			if ( ( ! isset( $_GET['info'] ) ) || ( empty( $_GET['info'] ) ) ) {

				//---------------------------------------------------------------------------introduction part start
				// Director summary, limited by admin options.

				$director = $movie->director();
				// director shown only if selected so in options.
				$optiondirectoractive = intval( $this->imdb_widget_values['imdbwidgetdirector'] ) ?? null;

				if ( ( isset( $director ) ) && ( ! empty( $director ) ) && ( $optiondirectoractive === '1' ) ) {

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
							. '/?mid=' . $director[ $i ]['imdb'] . '&film=' . $title_sanitized
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

				$cast = $movie->cast();
				$nbactors = empty( $this->imdb_widget_values['imdbwidgetactornumber'] ) ? $nbactors = '1' : $nbactors = intval( $this->imdb_widget_values['imdbwidgetactornumber'] );
				// actor shown only if selected so in options.
				$optionactoractive = intval( $this->imdb_widget_values['imdbwidgetactor'] ) ?? null;

				$nbtotalactors = intval( count( $cast ) );

				if ( ( isset( $cast ) ) && ( ! empty( $cast ) ) && ( $optionactoractive === '1' ) ) {

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

				$runtime = sanitize_text_field( $movie->runtime() );
				// runtime shown only if selected so in admin options.
				$optionruntimeactive = intval( $this->imdb_widget_values['imdbwidgetruntime'] );

				if ( ( ! empty( $runtime ) ) && ( $optionruntimeactive === 1 ) ) {

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
				$optionratingactive = intval( $this->imdb_widget_values['imdbwidgetrating'] ) ?? null;
				if ( ( null !== ( $movie->votes() ) ) && ( $optionratingactive == 1 ) ) {
					$votes_sanitized = intval( $movie->votes() );
					$rating_sanitized = esc_html( $movie->rating() );

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

				$languages = $movie->languages();
				$nbtotallanguages = count( $languages );
				// language shown only if selected so in options.
				$optionlanguageactive = intval( $this->imdb_widget_values['imdbwidgetlanguage'] ) ?? null;

				if ( ( ( isset( $languages ) ) && ( ! empty( $languages ) ) ) && ( $optionlanguageactive == 1 ) ) {

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

				$country = $movie->country();
				$nbtotalcountry = count( $country );
				// country shown only if selected so in options.
				$optioncountryactive = intval( $this->imdb_widget_values['imdbwidgetcountry'] ) ?? null;

				if ( ( ( isset( $country ) ) && ( ! empty( $country ) ) ) && ( $optioncountryactive == 1 ) ) {

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

				// Genre shown only if selected so in options.
				$optiongenreactive = intval( $this->imdb_widget_values['imdbwidgetgenre'] );
				$genre = $movie->genre();

				if ( ( ( isset( $genre ) ) && ( ! empty( $genre ) ) ) && ( $optiongenreactive == 1 ) ) {

					$gen = $movie->genres();
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

				/*
												  <!-- Sound -->
				$sound = $movie->sound () ?? NULL;

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

				$cast = $movie->cast();
				$nbactors = empty( $this->imdb_widget_values['imdbwidgetactornumber'] ) ? $nbactors = '1' : $nbactors = intval( $this->imdb_widget_values['imdbwidgetactornumber'] );
				$optionactoractive = intval( $this->imdb_widget_values['imdbwidgetactor'] ) ?? null; # actor shown only if selected so in options
				$nbtotalactors = intval( count( $cast ) );

				if ( ( ! empty( $cast ) ) && ( $optionactoractive == 1 ) ) {

					echo "\n\t\t\t\t\t\t\t\t\t\t<!-- Actors -->";
					echo "\n\t" . '<div class="imdbincluded-subtitle">' . sprintf( esc_attr( _n( 'Actor', 'Actors', $nbtotalactors, 'lumiere-movies' ) ), number_format_i18n( $nbtotalactors ) ) . '</div>';

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
							. '&film=' . $title_sanitized
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

				$director = $movie->director();

				if ( ( isset( $director ) ) && ( ! empty( $director ) ) ) {

					$nbtotaldirector = count( $director );

					echo "\n\t\t\t\t\t\t\t" . ' <!-- director -->';
					echo "\n" . '<div id="lumiere_popup_director_group">';
					echo "\n\t" . '<span class="imdbincluded-subtitle">' . sprintf( esc_attr( _n( 'Director', 'Directors', $nbtotaldirector, 'lumiere-movies' ), number_format_i18n( $nbtotaldirector ) ) ) . '</span>';

					for ( $i = 0; $i < $nbtotaldirector; $i++ ) {
						echo "\n\t" . '<div align="center" class="lumiere_container">';
						echo "\n\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';
						echo "\n\t\t"
						. '<a class="linkpopup" href="'
						. esc_url(
							$this->config_class->lumiere_urlpopupsperson
							. $director[ $i ]['imdb']
							. '/?mid=' . $director[ $i ]['imdb']
							. '&film=' . $title_sanitized
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

				$writer = $movie->writing();
				if ( ( isset( $writer ) ) && ( ! empty( $writer ) ) ) {

					$nbtotalwriter = count( $writer );

					echo "\n\t\t\t\t\t\t\t" . ' <!-- writers -->';
					echo "\n" . '<div id="lumiere_popup_director_group">';
					echo "\n\t" . '<span class="imdbincluded-subtitle">' . sprintf( esc_attr( _n( 'Writer', 'Writers', $nbtotalwriter, 'lumiere-movies' ), number_format_i18n( $nbtotalwriter ) ) ) . '</span>';

					for ( $i = 0; $i < $nbtotalwriter; $i++ ) {
						echo "\n\t" . '<div align="center" class="lumiere_container">';
						echo "\n\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';
						echo "\n\t\t"
						. '<a class="linkpopup" href="'
						. esc_url(
							$this->config_class->lumiere_urlpopupsperson
							. $writer[ $i ]['imdb']
							. '/?mid=' . $writer[ $i ]['imdb']
							. '&film=' . $title_sanitized
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

				$producer = $movie->producer();
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
							. '&film=' . $title_sanitized
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

				$plotoutline = $movie->plotoutline();

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

				$plot = $movie->plot();
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

				// Trivia.

				$trivia = $movie->trivia();
				$nbtotaltrivia = count( $trivia );

				if ( ( isset( $trivia ) ) && ( ! empty( $trivia ) ) ) {

					echo "\n\t\t\t\t\t\t\t" . ' <!-- Trivia -->';
					echo "\n" . '<div id="lumiere_popup_pluts_group">';
					echo "\n\t" . '<span class="imdbincluded-subtitle">' . _n( 'Trivia', 'Trivias', $nbtotaltrivia, 'lumiere-movies' ) . '</span>';

					for ( $i = 0; $i < $nbtotaltrivia; $i++ ) {
						$ii = $i + 1;

						if ( $i == 0 ) {
							echo "\n\t" . '<div>'
							. preg_replace( '/https\:\/\/' . str_replace( '.', '\.', $movie->imdbsite ) . '\/name\/nm(\d{7})\//', $this->config_class->lumiere_urlpopupsperson . "popup-imdb_person.php?mid=\\1 class=\"linkpopup\"", sanitize_text_field( $trivia[ $i ] ) )
							. '&nbsp;&nbsp;&nbsp;'
							. '<span class="activatehidesection"><strong>(' . esc_html__( 'click to show more trivias', 'lumiere-movies' ) . ')</strong></span>'
							. "\n\t" . '<div class="hidesection">'
							. '<br />';

						} elseif ( $i > 0 ) {
							echo "\n\t\t<strong>($ii)</strong>&nbsp;" . preg_replace( '/https\:\/\/' . str_replace( '.', '\.', $movie->imdbsite ) . '\/name\/nm(\d{7})\//', $this->config_class->lumiere_urlpopupsperson . "popup-imdb_person.php?mid=\\1 class=\"linkpopup\"", sanitize_text_field( $trivia[ $i ] ) )
							. "\n\t\t<hr>";
						}

					} //end endfor

					echo "\n\t" . '</div>';
					echo "\n\t</div>";

					echo "\n</div>";

				}

				// Soundtrack.

				$soundtrack = $movie->soundtrack();
				$nbtotalsoundtrack = count( $soundtrack );

				if ( ( isset( $soundtrack ) ) && ( ! empty( $soundtrack ) ) ) {

					echo "\n\t\t\t\t\t\t\t" . ' <!-- Soundtrack -->';
					echo "\n" . '<div id="lumiere_popup_pluts_group">';
					echo "\n\t" . '<span class="imdbincluded-subtitle">' . _n( 'Soundtrack', 'Soundtracks', $nbtotalsoundtrack, 'lumiere-movies' ) . '</span>';

					for ( $i = 0; $i < $nbtotalsoundtrack; $i++ ) {

						$credit = preg_replace( '/http\:\/\/' . str_replace( '.', '\.', $movie->imdbsite ) . '\/name\/nm(\d{7})\//', $this->config_class->lumiere_urlpopupsperson . "popup-imdb_person.php?mid=\\1 class=\"linkpopup\"", sanitize_text_field( $soundtrack[ $i ]['credits'][0]['credit_to'] ) );
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

				$goof = $movie->goofs();
				$nbtotalgoof = count( $goof );

				if ( ( isset( $goof ) ) && ( ! empty( $goof ) ) ) {

					echo "\n\t\t\t\t\t\t\t" . ' <!-- Goofs -->';
					echo "\n" . '<div id="lumiere_popup_pluts_group">';
					echo "\n\t" . '<span class="imdbincluded-subtitle">' . _n( 'Goof', 'Goofs', $nbtotalgoof, 'lumiere-movies' ) . '</span>';

					for ( $i = 0; $i < $nbtotalgoof; $i++ ) {
						$ii = $i + 1;

						if ( $i == 0 ) {
							echo "\n\t" . '<div>'
							. '<strong>' . sanitize_text_field( $goof[ $i ]['type'] ) . '</strong>&nbsp;'
							. sanitize_text_field( $goof[ $i ]['content'] )
							. '&nbsp;<span class="activatehidesection"><strong>(' . esc_html__( 'click to show more goofs', 'lumiere-movies' ) . ')</strong></span>'
							. "\n\t" . '<div class="hidesection">'
							. "\n\t\t" . '<br />';

						} elseif ( $i > 0 ) {
							echo "\n\t\t<strong>($ii) " . sanitize_text_field( $goof[ $i ]['type'] ) . '</strong>&nbsp;'
							. sanitize_text_field( $goof[ $i ]['content'] );
							echo "\n\t\t" . '<br />';
						}

					} //end endfor

					echo "\n\t" . '</div>';
					echo "\n\t</div>";

					echo "\n</div>";

				}

			} // ------------------------------------------------------------------------------ misc part end

			echo '<br />';
			wp_footer();
			?>
		</body>
		</html>
			<?php
			exit(); // quit the call of the page, to avoid double loading process
		}
	}

	/**
	 * Search the title for the popup
	 *
	 * @param array $results outputed by IMDbPHP query
	 * @param string $film_sanitized_for_title
	 */
	private function lumiere_popupup_search_title ( array $results, string $film_sanitized_for_title ) {

		?>
		<!DOCTYPE html>
		<html>
		<head>
		<?php wp_head(); ?>
		</head>
		<body class="lumiere_body
		<?php
		if ( isset( $this->imdb_admin_values['imdbpopuptheme'] ) ) {
			echo ' lumiere_body_' . $this->imdb_admin_values['imdbpopuptheme'];}
		?>
		">

		<div id="lumiere_loader" class="lumiere_loader_center"></div>

		<?php
		// if no movie was found at all
		if ( empty( $results ) ) {
			echo "<h2 align='center'><i>" . esc_html__( 'No result found.', 'lumiere-movies' ) . '</i></h2>';
			wp_footer();
			?>
		</body></html>
			<?php
			die();
		}
		?>

		<h1 align="center">
		<?php
		esc_html_e( 'Results related to', 'lumiere-movies' );
		echo ' <i>' . ucfirst( $film_sanitized_for_title ) . '</i>';
		?>
		</h1>

		<div class="lumiere_display_flex lumiere_align_center">
			<h2 class="lumiere_flex_auto lumiere_width_fifty_perc">
				<?php esc_html_e( 'Matching titles', 'lumiere-movies' ); ?>
			</h2>
			<h2 class="lumiere_flex_auto lumiere_width_fifty_perc">
				<?php esc_html_e( 'Director', 'lumiere-movies' ); ?>
			</h2>
		</div>
			<?php

			$current_line = 0;
			$max_lines = isset( $this->imdb_admin_values['imdbmaxresults'] ) ? intval( $this->imdb_admin_values['imdbmaxresults'] ) : 10;

			foreach ( $results as $res ) {

				// Limit the number of results according to value set in admin
				$current_line++;
				if ( $current_line > $max_lines ) {
					echo '</div>';
					echo '<div align="center"><i>';
					echo esc_html__( 'Maximum of results reached.', 'lumiere-movies' );
					if ( current_user_can( 'manage_options' ) ) {
						echo '&nbsp' . esc_html__( 'You can increase the maximum number of results in admin options.', 'lumiere-movies' );
					}
					echo '</div>';
					wp_footer();
					echo '</i></body></html>';
					exit();
				}

				echo "\n<div class='lumiere_display_flex lumiere_align_center'>";

				// ---- movie part
				echo "\n\t<div class='lumiere_flex_auto lumiere_width_fifty_perc lumiere_align_left'>";

				echo "\n\t\t<a class=\"linkpopup\" href=\"" . esc_url(
					$this->config_class->lumiere_urlpopupsfilms
					. $this->utils_class->lumiere_name_htmlize( $res->title() )
					. '/?mid=' . esc_html( $res->imdbid() )
				)
					. '&film=' . $this->utils_class->lumiere_name_htmlize( $res->title() )
					. '" title="' . esc_html__( 'more on', 'lumiere-movies' ) . ' '
					. esc_html( $res->title() ) . '" >'
					. esc_html( $res->title() )
					. ' (' . intval( $res->year() ) . ')' . "</a> \n";

				echo "\n\t</div>";

				// ---- director part
				echo "\n\t<div class='lumiere_flex_auto lumiere_width_fifty_perc lumiere_align_right'>";

				$realisateur = $res->director();
				if ( ( isset( $realisateur['0']['name'] ) ) && ( ! is_null( $realisateur['0']['name'] ) ) ) {

					echo "\n\t\t<a class=\"linkpopup\" href=\""
						. esc_url(
							$this->config_class->lumiere_urlpopupsperson
							. esc_html( $realisateur['0']['imdb'] )
							. '/?mid=' . esc_html( $realisateur['0']['imdb'] )
						)
						. '" title="' . esc_html__( 'more on', 'lumiere-movies' )
						. ' ' . esc_html( $realisateur['0']['name'] )
						. '" >' . esc_html( $realisateur['0']['name'] )
						. '</a>';

				} else {

					echo "\n\t\t<i>" . esc_html__( 'No director found.', 'lumiere-movies' ) . '</i>';

				}

				echo "\n\t</div>";
				echo "\n</div>";

			} // end foreach
			?>
			

		</div>

		<?php
			wp_footer();
		?>
		</body>
		</html>
		<?php
			exit(); # quit the call of the page, to avoid double loading process

	}

}

new PopupMovie();

