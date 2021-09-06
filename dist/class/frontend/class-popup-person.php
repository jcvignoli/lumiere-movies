<?php declare( strict_types = 1 );
/**
 * Popup for people: Independant page that displays star information inside a popup
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

use \Imdb\Person;

class Popup_Person {

	// Use trait frontend
	use \Lumiere\Frontend {
		Frontend::__construct as public __constructFrontend;
	}

	/**
	 * The person queried as object result
	 */
	private Person $person;

	/**
	 * The person queried
	 */
	private ?string $person_name_sanitized;

	/**
	 * Person's id, if provided
	 */
	private ?string $mid_sanitized;

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		// Construct Frontend trait.
		$this->__constructFrontend( 'popupPerson' );

		// Display layout
		add_action( 'wp', [ $this, 'lumiere_popup_person_layout' ], 1 );
	}

	/**
	 *  Search movie title
	 *
	 */
	private function find_person(): bool {

		do_action( 'lumiere_logger' );

		/* GET Vars sanitized */
		$this->mid_sanitized = isset( $_GET['mid'] ) && strlen( strval( $_GET['mid'] ) ) !== 0 ? esc_html( $_GET['mid'] ) : null;

		// if neither film nor mid are set, throw a 404 error
		if ( $this->mid_sanitized === null ) {

			status_header( 404 );
			$this->logger->log()->error( '[Lumiere] No person entered' );
			wp_die( esc_html__( 'Lumière Movies: Invalid person search query.', 'lumiere-movies' ) );

		} elseif ( strlen( $this->mid_sanitized ) !== 0 ) {

			$this->logger->log()->debug( '[Lumiere] Movie person IMDb ID provided in URL: ' . $this->mid_sanitized );
			$this->person = new Person( $this->mid_sanitized, $this->imdbphp_class, $this->logger->log() );
			$this->person_name_sanitized = sanitize_text_field( $this->person->name() );

			return true;

		}

		return false;

	}

	/**
	 *  Display layout
	 *
	 */
	public function lumiere_popup_person_layout(): void {

		?><!DOCTYPE html>
		<html>
		<head>
		<?php wp_head(); ?>

		</head>
		<body class="lumiere_body<?php
		if ( isset( $this->imdb_admin_values['imdbpopuptheme'] ) ) {
			echo ' lumiere_body_' . $this->imdb_admin_values['imdbpopuptheme'];
		}
		?>">

		<?php

		// Get the movie's title.
		$this->find_person();

		// Show menu.
		$this->display_menu();

		// Show portrait.
		$this->display_portrait();

		//---------------------------------------------------------------------------summary
		// display only when nothing is selected from the menu.
		if ( ( ! isset( $_GET['info'] ) ) || ( strlen( $_GET['info'] ) === 0 ) ) {

			$this->display_summary();

		}

		//---------------------------------------------------------------------------full filmography
		if ( ( isset( $_GET['info'] ) ) && ( $_GET['info'] === 'filmo' ) ) {

			$this->display_full_filmo();

		}

		// ------------------------------------------------------------------------------ partie bio
		if ( ( isset( $_GET['info'] ) ) && ( $_GET['info'] === 'bio' ) ) {

			$this->display_bio();

		}

		// ------------------------------------------------------------------------------ misc part
		if ( ( isset( $_GET['info'] ) ) && ( $_GET['info'] === 'misc' ) ) {

			$this->display_misc();
		}
		//------------------------------------------------------------------------------ end misc part
		?>

		<br /><br />
		<?php

		wp_meta();
		wp_footer();

		?>
		</body>
		</html>
		<?php

		exit(); // quit the call of the page, to avoid double loading process

	}

	/**
	 * Display navigation menu
	 */
	private function display_menu(): void { ?>
												<!-- top page menu -->

		<div class="lumiere_container lumiere_font_em_11 lumiere_titlemenu">
			<div class="lumiere_flex_auto">
				<a class="historyback"><?php esc_html_e( 'Back', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				<a class='linkpopup' href="<?php echo esc_url( $this->config_class->lumiere_urlpopupsperson . $this->mid_sanitized . '/?mid=' . $this->mid_sanitized . '&info=' ); ?>" title='<?php echo $this->person_name_sanitized . ': ' . esc_html__( 'Summary', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Summary', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				<a class='linkpopup' href="<?php echo esc_url( $this->config_class->lumiere_urlpopupsperson . $this->mid_sanitized . '/?mid=' . $this->mid_sanitized . '&info=filmo' ); ?>" title='<?php echo $this->person_name_sanitized . ': ' . esc_html__( 'Full filmography', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Full filmography', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				<a class='linkpopup' href="<?php echo esc_url( $this->config_class->lumiere_urlpopupsperson . $this->mid_sanitized . '/?mid=' . $this->mid_sanitized . '&info=bio' ); ?>" title='<?php echo $this->person_name_sanitized . ': ' . esc_html__( 'Full biography', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Full biography', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				<a class='linkpopup' href="<?php echo esc_url( $this->config_class->lumiere_urlpopupsperson . $this->mid_sanitized . '/?mid=' . $this->mid_sanitized . '&info=misc' ); ?>" title='<?php echo $this->person_name_sanitized . ': ' . esc_html__( 'Misc', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Misc', 'lumiere-movies' ); ?></a>
			</div>
		</div>

		<?php
	}

	/**
	 * Display summary page
	 * Director actor and producer filmography
	 */
	private function display_summary(): void {

		$list_all_movies_functions = [ 'director', 'actor' ];
		$nblimitcatmovies = 9;

		foreach ( $list_all_movies_functions as $var ) {

			// Build function name based on var $list_all_movies_functions list.
			$all_movies_functions = "movies_$var";

			// @phpstan-ignore-next-line 'Variable method call on Imdb\Person'.
			$filmo = $this->person->$all_movies_functions();

			$catname = ucfirst( $var );

			if ( ( isset( $filmo ) ) && count( $filmo ) !== 0 ) {

				$nbfilmpercat = 0;
				$nbtotalfilmo = count( $filmo );
				$nbtotalfilms = $nbtotalfilmo - $nbfilmpercat;

				echo "\n\t\t\t\t\t\t\t" . ' <!-- ' . sanitize_text_field( $catname ) . ' filmography -->';
				echo "\n\t" . '<div align="center" class="lumiere_container">';
				echo "\n\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';

				echo "\n\t" . '<div>';
				echo "\n\t\t" . '<span class="imdbincluded-subtitle">' . sanitize_text_field( $catname ) . ' filmography </span>';

				for ( $i = 0; $i < $nbtotalfilmo; $i++ ) {

					echo " <a class='linkpopup' href='" . esc_url( $this->config_class->lumiere_urlpopupsfilms . '?mid=' . esc_html( $filmo[ $i ]['mid'] ) ) . "'>" . sanitize_text_field( $filmo[ $i ]['name'] ) . '</a>';

					if ( strlen( $filmo[ $i ]['year'] ) !== 0 ) {
						echo ' (';
						echo intval( $filmo[ $i ]['year'] );
						echo ')';
					}

					/** 2021 09 Dunno if this check is still needed
					if ( $filmo[ $i ]['chname'] == "\n" ) {
						echo '';
					} else {
					*/
					if ( isset( $filmo[ $i ]['chname'] ) && strlen( $filmo[ $i ]['chname'] ) !== 0 ) {
						echo ' as <i>' . esc_html( $filmo[ $i ]['chname'] ) . '</i>';

					}

					// Display a "show more" after XX results
					if ( $i === $nblimitcatmovies ) {
						echo '&nbsp;<span class="activatehidesection"><font size="-1"><strong>('
							. esc_html__( 'see all', 'lumiere-movies' )
							. ')</strong></font></span> '
							. '<span class="hidesection">';
					}

					if ( $i === $nbtotalfilmo ) {
						echo '</span>';
					}

					$nbfilmpercat++;

				}

				echo "\n\t" . '</div>';

			}

		}

	}

	/**
	 * Display full filmography page
	 */
	private function display_full_filmo(): void {

		/* vars */
		$list_all_movies_functions = [ 'director', 'actor', 'producer', 'archive', 'crew', 'self', 'soundtrack', 'thanx', 'writer' ]; # list of types of movies to query
		$nblimitmovies = 5; # max number of movies before breaking with "see all"

		foreach ( $list_all_movies_functions as $var ) {

			// Build the function using the vars.
			$all_movies_functions = "movies_$var";

			// @phpstan-ignore-next-line 'Variable method call on Imdb\Person'.
			$filmo = $this->person->$all_movies_functions();

			$catname = ucfirst( $var );

			if ( ( isset( $filmo ) ) && count( $filmo ) !== 0 ) {
				$nbfilmpercat = 0;
				$nbtotalfilmo = count( $filmo );
				$nbtotalfilms = $nbtotalfilmo - $nbfilmpercat;

				echo "\n\t\t\t\t\t\t\t" . ' <!-- ' . sanitize_text_field( $catname ) . ' filmography -->';
				echo "\n" . '<div>';
				echo "\n\t" . '<span class="imdbincluded-subtitle">' . sanitize_text_field( $catname ) . ' filmography</span> (' . $nbtotalfilms . ')';

				for ( $i = 0; $i < $nbtotalfilmo; $i++ ) {

					// Display a "show more" after XX results
					if ( $i === $nblimitmovies ) {
						echo "\n\t" . '<span class="activatehidesection"><font size="-1"><strong>&nbsp;('
							. esc_html__( 'see all', 'lumiere-movies' )
							. ')</strong></font></span> '
							. "\n\t" . '<div class="hidesection">'; # start of hidden div
					}

					// after XX results, show a table like list of results

					if ( $i >= $nblimitmovies ) {

						echo "\n\t\t" . '<div align="center" class="lumiere_container">';
						echo "\n\t\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';
						echo "\n\t\t\t\t <a class='linkpopup' href='"
							. esc_url(
								$this->config_class->lumiere_urlpopupsfilms
								. '?mid=' . esc_html( $filmo[ $i ]['mid'] )
							)
							. "'>"
							. sanitize_text_field( $filmo[ $i ]['name'] )
							. '</a>';
						if ( strlen( $filmo[ $i ]['year'] ) !== 0 ) {
							echo ' (';
							echo intval( $filmo[ $i ]['year'] );
							echo ')';
						}
						echo "\n\t\t\t" . '</div>';
						echo "\n\t\t\t" . '<div class="lumiere_align_right lumiere_flex_auto">';

						/**
						 * 2021 09 Dunno if this check is still needed
						if ( $filmo[ $i ]['chname'] == "\n" ) {
							echo '';
						} else { */

						if ( ( ! isset( $filmo['chid'] ) || strlen( $filmo['chid'] ) === 0 ) && ( strlen( $filmo[ $i ]['chname'] ) !== 0 ) ) {
							echo ' as <i>'
								. sanitize_text_field( $filmo[ $i ]['chname'] )
								. '</i>';

						} elseif ( isset( $filmo['chid'] ) && count( $filmo['chid'] ) !== 0 ) {
							echo ' as <i><a class="linkpopup" href="'
							. esc_url(
								'https://'
								. $this->person->imdbsite
								. '/character/ch'
								. intval( $filmo['chid'] )
							)
							. '/">'
							. sanitize_text_field( $filmo[ $i ]['chname'] )
							. '</a></i>';
						}

						echo "\n\t\t\t</div>";
						echo "\n\t\t</div>";

						// Last cat movie, close the hidden div
						if ( $i === ( $nbtotalfilmo - 1 ) ) {
							echo "\n\t" . '</div>';

						}
						continue;
					}

					// before XX results, show a shortened list of results

					echo "\n\t <a class='linkpopup' href='"
							. esc_url(
								$this->config_class->lumiere_urlpopupsfilms
								. '?mid=' . esc_html( $filmo[ $i ]['mid'] )
							)
							. "'>" . sanitize_text_field( $filmo[ $i ]['name'] )
							. '</a>';

					if ( strlen( $filmo[ $i ]['year'] ) !== 0 ) {
						echo ' (';
						echo intval( $filmo[ $i ]['year'] );
						echo ')';
					}

					/**
					 * 2021 09 Dunno if this check is still needed
					if ( $filmo[ $i ]['chname'] == "\n" ) {
						echo '';
					} else { */

					if ( ( ! isset( $filmo['chid'] ) || strlen( $filmo['chid'] ) === 0 ) && ( strlen( $filmo[ $i ]['chname'] ) !== 0 ) ) {
						echo ' as <i>' . sanitize_text_field( $filmo[ $i ]['chname'] ) . '</i>';
					} elseif ( isset( $filmo['chid'] ) && strlen( $filmo['chid'] ) !== 0 ) {
						echo ' as <i><a class="linkpopup" href="'
							. esc_url(
								'https://'
								. $this->person->imdbsite
								. '/character/ch'
								. intval( $filmo['chid'] )
							)
							. '/">'
							. $filmo[ $i ]['chname']
							. '</a></i>';
					}

					$nbfilmpercat++;

				} //end for each filmo

				echo "\n" . '</div>';

			} // end if

		} // endforeach main

	}

	/**
	 * Display biography
	 */
	private function display_bio(): void {

		$biomovie = $this->person->pubmovies();
		$nbtotalbiomovie = count( $biomovie );

		if ( $nbtotalbiomovie !== 0 ) {

			echo "\n\t\t\t\t\t\t\t" . ' <!-- Biographical movies -->';
			echo "\n" . '<div id="lumiere_popup_biomovies">';
			echo "\n\t" . '<span class="imdbincluded-subtitle">' . esc_html__( 'Biographical movies', 'lumiere-movies' ) . '</span>';

			for ( $i = 0; $i < $nbtotalbiomovie; ++$i ) {
				echo "<a class='linkpopup' href='" . esc_url( $this->config_class->lumiere_urlpopupsfilms . '?mid=' . intval( $biomovie[ $i ]['imdb'] ) ) . "'>" . $biomovie[ $i ]['name'] . '</a>';
				if ( strlen( $biomovie[ $i ]['year'] ) !== 0 ) {
					echo ' (' . intval( $biomovie[ $i ]['year'] ) . ')';
				}
			}

			echo '</div>';

		}

		############## Portrayed in

		$portrayedmovie = $this->person->pubportraits();
		$nbtotalportrayedmovie = count( $portrayedmovie );

		if ( $nbtotalportrayedmovie !== 0 ) {

			echo "\n\t\t\t\t\t\t\t" . ' <!-- Portrayed in -->';
			echo "\n" . '<div id="lumiere_popup_biomovies">';
			echo "\n\t" . '<span class="imdbincluded-subtitle">' . esc_html__( 'Portrayed in', 'lumiere-movies' ) . '</span>';

			for ( $i = 0; $i < $nbtotalportrayedmovie; ++$i ) {
				echo "<a class='linkpopup' href='" . esc_url( $this->config_class->lumiere_urlpopupsfilms . '?mid=' . intval( $portrayedmovie[ $i ]['imdb'] ) ) . "'>" . $portrayedmovie[ $i ]['name'] . '</a>';
				if ( strlen( $portrayedmovie[ $i ]['year'] ) !== 0 ) {
					echo ' (' . intval( $portrayedmovie[ $i ]['year'] ) . ') ';
				}
			}

			echo '</div>';

		}

		############## Interviews

		$interviews = $this->person->interviews();
		$nbtotalinterviews = count( $interviews );

		if ( $nbtotalinterviews !== 0 ) {

			echo "\n\t\t\t\t\t\t\t" . ' <!-- Interviews -->';
			echo "\n" . '<div id="lumiere_popup_biomovies">';
			echo "\n\t" . '<span class="imdbincluded-subtitle">' . esc_html__( 'Interviews', 'lumiere-movies' ) . '</span>';

			for ( $i = 0; $i < $nbtotalinterviews; $i++ ) {

				echo $interviews[ $i ]['name'] . ' ';

				if ( isset( $interviews[ $i ]['full'] ) && strlen( $interviews[ $i ]['full'] ) !== 0 ) {
					echo ' (' . intval( $interviews[ $i ]['full'] ) . ') ';
				}

				if ( isset( $interviews[ $i ]['details'] ) && strlen( $interviews[ $i ]['details'] ) !== 0 ) {
					echo $interviews[ $i ]['details'] . '';
				}

				if ( $i < $nbtotalinterviews - 1 ) {
					echo ', ';
				}

			}

			echo '</div>';

		}

		############## Publicity printed

		$pubprints = $this->person->pubprints();
		$nbtotalpubprints = count( $pubprints );
		$nblimitpubprints = 9;

		if ( $nbtotalpubprints !== 0 ) {

			echo "\n\t\t\t\t\t\t\t" . ' <!-- Publicity printed -->';
			echo "\n" . '<div id="lumiere_popup_biomovies">';
			echo "\n\t" . '<span class="imdbincluded-subtitle">'
				. esc_html__( 'Printed publicity', 'lumiere-movies' )
				. '</span>';

			for ( $i = 0; $i < $nbtotalpubprints; $i++ ) {

				// Display a "show more" after XX results
				if ( $i === $nblimitpubprints ) {
					echo "\n\t" . '<span class="activatehidesection"><font size="-1"><strong>&nbsp;('
						. esc_html__( 'see all', 'lumiere-movies' )
						. ')</strong></font></span> '
						. "\n\t" . '<span class="hidesection">';
				}

				if ( isset( $pubprints[ $i ]['author'] ) && strlen( $pubprints[ $i ]['author'] ) !== 0 ) {
					$text = preg_replace( '~/name/nm(\d{7})\/\"~', $this->config_class->lumiere_urlpopupsperson . "popup-imdb_person.php?mid=\\1\" class=\"linkpopup\"", $pubprints[ $i ]['author'] ); # transform imdb to local link
					echo "\n\t\t" . $text;
				}

				if ( isset( $pubprints[ $i ]['title'] ) && strlen( $pubprints[ $i ]['title'] ) !== 0 ) {
					echo ' <i>' . esc_html( $pubprints[ $i ]['title'] ) . '</i> ';
				}

				if ( isset( $pubprints[ $i ]['year'] ) && strlen( $pubprints[ $i ]['year'] ) !== 0 ) {
					echo '(' . intval( $pubprints[ $i ]['year'] ) . ')';
				}

				if ( isset( $pubprints[ $i ]['details'] ) && strlen( $pubprints[ $i ]['details'] ) !== 0 ) {
					echo esc_html( $pubprints[ $i ]['details'] ) . ' ';
				}

				if ( $i < ( $nbtotalpubprints - 1 ) ) {
					echo ', ';
				}

				if ( $i === ( $nbtotalpubprints - 1 ) ) {
					echo "\n\t" . '</span>';
				}

			}

			echo "\n" . '</div>';

		}

	}

	/**
	 * Display miscellaenous infos
	 */
	private function display_misc(): void {

		############## Trivia

		$trivia = $this->person->trivia();
		$nbtotaltrivia = count( $trivia );
		$nblimittrivia = 3; # max number of trivias before breaking with "see all"

		if ( $nbtotaltrivia !== 0 ) {

			echo "\n\t\t\t\t\t\t\t" . ' <!-- Trivia -->';
			echo "\n" . '<div id="lumiere_popup_biomovies">';
			echo "\n\t" . '<span class="imdbincluded-subtitle">' . esc_html__( 'Trivia', 'lumiere-movies' ) . ' </span>(' . $nbtotaltrivia . ') <br />';

			for ( $i = 0; $i < $nbtotaltrivia; $i++ ) {

				// Display a "show more" after 3 results
				if ( $i === $nblimittrivia ) {
					echo "\n\t\t" . '<div class="activatehidesection lumiere_align_center"><font size="-1"><strong>('
						. esc_html__( 'see all', 'lumiere-movies' )
						. ')</strong></font></div>'
						. "\n\t\t" . '<div class="hidesection">';
				}

				echo "\n\t\t\t" . '<div>';
				$text = $this->lumiere_imdburl_to_internalurl( $trivia[ $i ] );
				$text = preg_replace( '~^\s\s\s\s\s\s\s(.*)<br \/>\s\s\s\s\s$~', "\\1", $text ); # clean output

				echo "\n\t\t\t\t" . ' * ' . $text;
				echo "\n\t\t\t" . '</div>';

				if ( $i === $nbtotaltrivia ) {
					echo "\n\t\t" . '</div>';
				}

			}

			echo "\n\t" . '</div>';
			echo "\n" . '</div>';

		}

		############## Nicknames

		$nickname = $this->person->nickname();
		$nbtotalnickname = count( $nickname );

		if ( $nbtotalnickname !== 0 ) {

			echo "\n\t\t\t\t\t\t\t" . ' <!-- Nicknames -->';
			echo "\n" . '<div id="lumiere_popup_biomovies">';
			echo "\n\t" . '<span class="imdbincluded-subtitle">' . esc_html__( 'Nicknames', 'lumiere-movies' ) . ' </span>';

			for ( $i = 0; $i < $nbtotalnickname; $i++ ) {

				$txt = '';

				foreach ( $nickname as $nick ) {
					$txt = str_replace( '<br>', ', ', $nick );
					echo esc_html( $txt );
				}
			}

			echo "\n" . '</div>';

		}

		############## Personal quotes

		$quotes = $this->person->quotes();
		$nbtotalquotes = count( $quotes );
		$nblimitquotes = 3;

		if ( $nbtotalquotes !== 0 ) {

			echo "\n\t\t\t\t\t\t\t" . ' <!-- Personal quotes -->';
			echo "\n" . '<div id="lumiere_popup_quotes">';
			echo "\n\t" . '<span class="imdbincluded-subtitle">' . esc_html__( 'Personal quotes', 'lumiere-movies' ) . ' </span> (' . $nbtotalquotes . ')';

			for ( $i = 0; $i < $nbtotalquotes; $i++ ) {

				// Display a "show more" after XX results
				if ( $i === $nblimitquotes ) {
					echo "\n\t\t" . '<div class="activatehidesection lumiere_align_center"><font size="-1"><strong>('
						. esc_html__( 'see all', 'lumiere-movies' )
						. ')</strong></font></div>'
						. "\n\t\t" . '<div class="hidesection">';
				}

				echo "\n\t\t\t" . '<div>';
				echo ' * ' . $this->lumiere_imdburl_to_internalurl( $quotes[ $i ] );
				echo '</div>';

				if ( $i === ( $nbtotalquotes - 1 ) ) {
					echo "\n\t\t" . '</div>';
				}

			}

			echo "\n" . '</div>';

		}

		############## Trademarks

		$trademark = $this->person->trademark();
		$nbtotaltrademark = count( $trademark );
		$nblimittradmark = 5;

		if ( $nbtotaltrademark !== 0 ) {

			echo "\n\t\t\t\t\t\t\t" . ' <!-- Trademarks -->';
			echo "\n" . '<div id="lumiere_popup_biomovies">';
			echo "\n\t" . '<span class="imdbincluded-subtitle">' . esc_html__( 'Trademarks', 'lumiere-movies' ) . ' </span>';

			for ( $i = 0; $i < $nbtotaltrademark; $i++ ) {

				// Display a "show more" after XX results
				if ( $i === $nblimittradmark ) {
					echo "\n\t\t" . '<div class="activatehidesection lumiere_align_center"><font size="-1"><strong>('
						. esc_html__( 'see all', 'lumiere-movies' )
						. ')</strong></font></div>'
						. "\n\t\t" . '<div class="hidesection">';
				}

				echo "\n\t\t\t" . '<div>@ ';
				echo $this->lumiere_imdburl_to_internalurl( $trademark[ $i ] );
				echo '</div>';

				if ( $i === $nbtotaltrademark - 1 ) {
					echo "\n\t\t" . '</div>';
				}

			}

			echo "\n" . '</div>';

		}
	}

	/**
	 * Display portrait including the medaillon
	 */
	private function display_portrait(): void { ?>
												<!-- Photo & identity -->
		<div class="lumiere_display_flex lumiere_font_em_11 lumiere_align_center">
			<div class="lumiere_flex_auto lumiere_width_eighty_perc">
				<div class="identity"><?php echo $this->person_name_sanitized; ?></div>
				<div class=""><font size="-1">
				<?php

				# Birth
				$birthday = $this->person->born() !== null ? $this->person->born() : null;
				if ( $birthday !== null && count( $birthday ) !== 0 ) {
					$birthday_day = ( isset( $birthday['day'] ) ) ? intval( $birthday['day'] ) : '';
					$birthday_month = ( isset( $birthday['month'] ) ) ? sanitize_text_field( $birthday['month'] ) : '';
					$birthday_year = ( isset( $birthday['year'] ) ) ? intval( $birthday['year'] ) : '';

					echo "\n\t\t\t" . '<span class="imdbincluded-subtitle">'
						. esc_html__( 'Born on', 'lumiere-movies' ) . '</span>'
						. $birthday_day . ' '
						. $birthday_month . ' '
						. $birthday_year;
				}

				if ( isset( $birthday['place'] ) && strlen( $birthday['place'] ) !== 0 ) {
					echo ', ' . esc_html__( 'in', 'lumiere-movies' ) . ' ' . sanitize_text_field( $birthday['place'] );
				}

				echo "\n\t\t" . '</font></div>';
				echo "\n\t\t" . '<div class=""><font size="-1">';

				# Death
				$death = count( $this->person->died() ) !== 0 ? $this->person->died() : null;
				if ( $death !== null ) {

					echo "\n\t\t\t" . '<span class="imdbincluded-subtitle">'
						. esc_html__( 'Died on', 'lumiere-movies' ) . '</span>'
						. intval( $death['day'] ) . ' '
						. sanitize_text_field( $death['month'] ) . ' '
						. intval( $death['year'] );

					if ( ( isset( $death['place'] ) ) && ( strlen( $death['place'] ) !== 0 ) ) {
						echo ', ' . esc_html__( 'in', 'lumiere-movies' ) . ' ' . sanitize_text_field( $death['place'] );
					}

					if ( ( isset( $death['cause'] ) ) && ( strlen( $death['cause'] ) !== 0 ) ) {
						echo ', ' . esc_html__( 'cause', 'lumiere-movies' ) . ' ' . sanitize_text_field( $death['cause'] );
					}
				}

				echo "\n\t\t" . '</font></div>';

				echo "\n\t\t" . '<div class="lumiere_padding_two lumiere_align_left"><font size="-1">';

				echo $this->lumiere_medaillon_bio( $this->person->bio(), false );
				?>

				</font></div>
			</div> 
												<!-- star photo -->
			<div class="lumiere_flex_auto lumiere_width_twenty_perc lumiere_padding_two"><?php

			// Select pictures: big poster, if not small poster, if not 'no picture'.
			$photo_url = $this->person->photo_localurl( false ) !== false ? esc_url( $this->person->photo_localurl( false ) ) : esc_url( $this->person->photo_localurl( true ) ); // create big picture, thumbnail otherwise.
			$photo_url_final = strlen( $photo_url ) === 0 ? esc_url( $this->imdb_admin_values['imdbplugindirectory'] . 'pics/no_pics.gif' ) : $photo_url; // take big/thumbnail picture if exists, no_pics otherwise.

			echo "\n\t\t\t\t" . '<a class="highslide_pic_popup" href="' . esc_attr( $photo_url_final ) . '">';
			echo "\n\t\t\t\t\t" . '<img loading="eager" class="imdbincluded-picture" src="'
				. esc_attr( $photo_url_final )
				. '" alt="'
				. $this->person_name_sanitized . '"';

			// add width only if "Display only thumbnail" is unactive.
			if ( $this->imdb_admin_values['imdbcoversize'] === '0' ) {

				echo ' width="' . intval( $this->imdb_admin_values['imdbcoversizewidth'] ) . '"';

				// add 100px width if "Display only thumbnail" is active.
			} elseif ( $this->imdb_admin_values['imdbcoversize'] === '1' ) {

				echo ' width="100em"';

			}

			echo ' />';
			echo "\n\t\t\t\t</a>";

			?>

			</div> 
		</div> 

		<hr><?php
	}

}

new Popup_Person();

