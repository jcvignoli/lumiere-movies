<?php declare( strict_types = 1 );
/**
 * Popup for people
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

use Imdb\Person;
use Lumiere\Frontend\Popups\Head_Popups;
use Lumiere\Frontend\Main;

/**
 * Independant class that displays star information in a popup
 * @see \Lumiere\Alteration\Rewrite_Rules that creates rules for creating a virtual page
 * @see \Lumiere\Alteration\Redirect_Virtual_Page that redirects to this page
 * @see \Lumiere\Frontend\Popups\Head_Popups that modifies the popup header
 */
class Popup_Person {

	/**
	 * Traits
	 */
	use Main;

	/**
	 * The person queried as object result
	 */
	private Person $person;

	/**
	 * The person queried
	 */
	private string $person_name;

	/**
	 * Person's id, if provided
	 */
	private ?string $mid_sanitized;

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
		$this->start_main_trait();

		// Remove admin bar if user is logged in.
		if ( is_user_logged_in() === true ) {
			add_filter( 'show_admin_bar', '__return_false' );
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
		 * @since 4.0 using 'the_posts' instead of the 'content'
		 */
		add_action( 'the_posts', [ $this, 'lumiere_popup_person_layout' ] );
	}

	/**
	 * Static construct call
	 *
	 * @return void Class is built
	 */
	public static function lumiere_popup_person_start (): void {
		$popup_person_class = new self();
	}

	/**
	 * Search movie title
	 */
	private function find_person(): bool {

		/* GET Vars sanitized */
		$this->mid_sanitized = isset( $_GET['mid'] ) && strlen( strval( $_GET['mid'] ) ) !== 0 ? esc_html( $_GET['mid'] ) : null;

		// if neither film nor mid are set, throw a 404 error
		if ( $this->mid_sanitized === null ) {

			status_header( 404 );
			$this->logger->log()->error( '[Lumiere] No person entered' );
			wp_die( esc_html__( 'Lumière Movies: Invalid person search query.', 'lumiere-movies' ) );

		} elseif ( strlen( $this->mid_sanitized ) !== 0 ) {

			$this->logger->log()->debug( '[Lumiere] Movie person IMDb ID provided in URL: ' . $this->mid_sanitized );

			$this->person = new Person( $this->mid_sanitized, $this->plugins_classes_active['imdbphp'], $this->logger->log() );
			$this->person_name = $this->person->name();

			return true;

		}
		return false;
	}

	/**
	 *  Display layout
	 */
	public function lumiere_popup_person_layout(): void {

		?> class="lum_body_popup<?php

		echo isset( $this->imdb_admin_values['imdbpopuptheme'] ) ? ' lum_body_popup_' . esc_attr( $this->imdb_admin_values['imdbpopuptheme'] ) . '">' : '">';

		/**
		 * Display a spinner when clicking a link with class .lum_add_spinner (a <div class="loader"> will be inserted inside by the js)
		 */
		echo '<div id="spinner-placeholder"></div>';

		// Get the movie's title.
		$this->find_person();

		$this->logger->log()->debug( '[Lumiere][' . $this->classname . '] Using the link maker class: ' . str_replace( 'Lumiere\Link_Makers\\', '', get_class( $this->link_maker ) ) );

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

		echo '<br><br>';

		wp_meta();
		wp_footer();

?>
		</body>
		</html><?php

		exit(); // quit the page, to avoid loading the proper WordPress page

	}

	/**
	 * Display navigation menu
	 */
	private function display_menu(): void {
		// If polylang exists, rewrite the URL to append the lang string
		$url_if_polylang = $this->lumiere_url_check_polylang_rewrite( $this->config_class->lumiere_urlpopupsperson );
		?>
												<!-- top page menu -->
		<div class="lumiere_container lumiere_font_em_11 lum_popup_titlemenu">
			<?php if ( isset( $_GET['info'] ) && strlen( $_GET['info'] ) > 0 ) { ?>
			<div class="lumiere_flex_auto">
				<a rel="nofollow" id="lum_popup_link_back" class="lum_popup_menu_title lum_add_spinner" href="<?php
				$refer = wp_get_referer();
				echo $refer !== false ? esc_url( $refer ) : ''; ?>"><?php esc_html_e( 'Back', 'lumiere-movies' ); ?></a>
			</div>
			<?php } ?>
			<div class="lumiere_flex_auto">
				<a rel="nofollow" class="lum_popup_menu_title lum_add_spinner" href="<?php echo esc_url( $url_if_polylang . '?mid=' . $this->mid_sanitized . '&info=' ); ?>" title="<?php echo esc_attr( $this->person_name ) . ': ' . esc_html__( 'Summary', 'lumiere-movies' ); ?>"><?php esc_html_e( 'Summary', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				<a rel="nofollow" class="lum_popup_menu_title lum_add_spinner" href="<?php echo esc_url( $url_if_polylang . '?mid=' . $this->mid_sanitized . '&info=filmo' ); ?>" title="<?php echo esc_attr( $this->person_name ) . ': ' . esc_html__( 'Full filmography', 'lumiere-movies' ); ?>"><?php esc_html_e( 'Full filmography', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				<a rel="nofollow" class="lum_popup_menu_title lum_add_spinner" href="<?php echo esc_url( $url_if_polylang . '?mid=' . $this->mid_sanitized . '&info=bio' ); ?>" title="<?php echo esc_attr( $this->person_name ) . ': ' . esc_html__( 'Full biography', 'lumiere-movies' ); ?>"><?php esc_html_e( 'Full biography', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				<a rel="nofollow" class="lum_popup_menu_title lum_add_spinner" href="<?php echo esc_url( $url_if_polylang . '?mid=' . $this->mid_sanitized . '&info=misc' ); ?>" title="<?php echo esc_attr( $this->person_name ) . ': ' . esc_html__( 'Misc', 'lumiere-movies' ); ?>"><?php esc_html_e( 'Misc', 'lumiere-movies' ); ?></a>
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
			$all_movies_functions = "movies_{$var}";

			$filmo = $this->person->$all_movies_functions();

			$catname = ucfirst( $var );

			if ( ( isset( $filmo ) ) && count( $filmo ) !== 0 ) {

				$nbfilmpercat = 0;
				$nbtotalfilmo = count( $filmo );
				$nbtotalfilms = $nbtotalfilmo - $nbfilmpercat;

				echo "\n\t\t\t\t\t\t\t" . ' <!-- ' . esc_html( $catname ) . ' filmography -->';
				echo "\n\t" . '<div align="center" class="lumiere_container">';
				echo "\n\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';

				echo "\n\t" . '<div>';
				echo "\n\t\t" . '<span class="imdbincluded-subtitle">' . esc_html( $catname ) . ' filmography </span>';

				for ( $i = 0; $i < $nbtotalfilmo; $i++ ) {

					echo " <a rel=\"nofollow\" class='lum_popup_internal_link lum_add_spinner' href='" . esc_url( $this->config_class->lumiere_urlpopupsfilms . '?mid=' . esc_html( $filmo[ $i ]['mid'] ) ) . "'>" . esc_html( $filmo[ $i ]['name'] ) . '</a>';

					if ( strlen( $filmo[ $i ]['year'] ) !== 0 ) {
						echo ' (';
						echo intval( $filmo[ $i ]['year'] );
						echo ')';
					}

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

			$filmo = $this->person->$all_movies_functions();

			$catname = ucfirst( $var );

			if ( ( isset( $filmo ) ) && count( $filmo ) !== 0 ) {
				$nbfilmpercat = 0;
				$nbtotalfilmo = count( $filmo );
				$nbtotalfilms = $nbtotalfilmo - $nbfilmpercat;

				echo "\n\t\t\t\t\t\t\t" . ' <!-- ' . esc_html( $catname ) . ' filmography -->';
				echo "\n" . '<div>';
				echo "\n\t" . '<span class="imdbincluded-subtitle">' . esc_html( $catname ) . ' filmography</span> (' . esc_html( strval( $nbtotalfilms ) ) . ')';

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
						echo "\n\t\t\t\t <a rel=\"nofollow\" class='lum_popup_internal_link lum_add_spinner' href='"
							. esc_url(
								$this->config_class->lumiere_urlpopupsfilms
								. '?mid=' . esc_html( $filmo[ $i ]['mid'] )
							)
							. "'>"
							. esc_html( $filmo[ $i ]['name'] )
							. '</a>';
						if ( strlen( $filmo[ $i ]['year'] ) !== 0 ) {
							echo ' (';
							echo intval( $filmo[ $i ]['year'] );
							echo ')';
						}
						echo "\n\t\t\t" . '</div>';
						echo "\n\t\t\t" . '<div class="lumiere_align_right lumiere_flex_auto">';

						if ( ( ! isset( $filmo['chid'] ) || strlen( $filmo['chid'] ) === 0 ) && ( strlen( $filmo[ $i ]['chname'] ) !== 0 ) ) {
							echo ' as <i>'
								. esc_html( $filmo[ $i ]['chname'] )
								. '</i>';

						} elseif ( isset( $filmo['chid'] ) && count( $filmo['chid'] ) !== 0 ) {
							echo ' as <i><a rel="nofollow" class="lum_popup_internal_link lum_add_spinner" href="'
							. esc_url(
								'https://'
								. $this->person->imdbsite
								. '/character/ch'
								. intval( $filmo['chid'] )
							)
							. '/">'
							. esc_html( $filmo[ $i ]['chname'] )
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

					echo "\n\t <a rel=\"nofollow\" class='lum_popup_internal_link lum_add_spinner' href='"
							. esc_url(
								$this->config_class->lumiere_urlpopupsfilms
								. '?mid=' . esc_html( $filmo[ $i ]['mid'] )
							)
							. "'>" . esc_html( $filmo[ $i ]['name'] )
							. '</a>';

					if ( strlen( $filmo[ $i ]['year'] ) !== 0 ) {
						echo ' (';
						echo intval( $filmo[ $i ]['year'] );
						echo ')';
					}

					if ( ( ! isset( $filmo['chid'] ) || strlen( $filmo['chid'] ) === 0 ) && ( strlen( $filmo[ $i ]['chname'] ) !== 0 ) ) {

						echo ' as <i>' . esc_html( $filmo[ $i ]['chname'] ) . '</i>';

					} elseif ( isset( $filmo['chid'] ) && strlen( $filmo['chid'] ) !== 0 ) {

						echo ' as <i><a rel="nofollow" class="lum_popup_internal_link lum_add_spinner" href="'
							. esc_url(
								'https://'
								. $this->person->imdbsite
								. '/character/ch'
								. intval( $filmo['chid'] )
							)
							. '/">'
							. esc_html( $filmo[ $i ]['chname'] )
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
			echo "\n\t" . '<span class="imdbincluded-subtitle">' . esc_html( _n( 'Biographical movie', 'Biographical movies', $nbtotalbiomovie, 'lumiere-movies' ) ) . '</span>';

			for ( $i = 0; $i < $nbtotalbiomovie; ++$i ) {

				echo "<a rel=\"nofollow\" class='lum_popup_internal_link lum_add_spinner' href='" . esc_url( $this->config_class->lumiere_urlpopupsfilms . '?mid=' . intval( $biomovie[ $i ]['id'] ) ) . "'>" . esc_html( $biomovie[ $i ]['title'] ) . '</a>';

				if ( isset( $biomovie[ $i ]['year'] ) && $biomovie[ $i ]['year'] > 0 ) {
					echo ' (' . intval( $biomovie[ $i ]['year'] ) . ') ';
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

				echo "<a rel=\"nofollow\" class='lum_popup_internal_link lum_add_spinner' href='" . esc_url( $this->config_class->lumiere_urlpopupsfilms . '?mid=' . intval( $portrayedmovie[ $i ]['imdb'] ) ) . "'>" . esc_html( $portrayedmovie[ $i ]['name'] ) . '</a>';

				if ( isset( $portrayedmovie[ $i ]['year'] ) && strlen( $portrayedmovie[ $i ]['year'] ) !== 0 ) {
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
			echo "\n\t" . '<span class="imdbincluded-subtitle">' . esc_html( _n( 'Interview', 'Interviews', $nbtotalinterviews, 'lumiere-movies' ) ) . '</span>';

			for ( $i = 0; $i < $nbtotalinterviews; $i++ ) {

				echo esc_html( $interviews[ $i ]['name'] ) . ' ';

				if ( isset( $interviews[ $i ]['full'] ) && strlen( $interviews[ $i ]['full'] ) !== 0 ) {
					echo ' (' . intval( $interviews[ $i ]['full'] ) . ') ';
				}

				if ( isset( $interviews[ $i ]['details'] ) && strlen( $interviews[ $i ]['details'] ) !== 0 ) {
					echo esc_html( $interviews[ $i ]['details'] );
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
				. esc_html( _n( 'Print ads', 'Printed ads', $nbtotalpubprints, 'lumiere-movies' ) )
				. '</span>';
			for ( $i = 0; $i < $nbtotalpubprints; $i++ ) {

				// Display a "show more" after XX results
				if ( $i === $nblimitpubprints ) {
					echo "\n\t" . '<span class="activatehidesection"><font size="-1"><strong>&nbsp;('
						. esc_html__( 'see all', 'lumiere-movies' )
						. ')</strong></font></span> '
						. "\n\t" . '<span class="hidesection">';
				}

				if ( isset( $pubprints[ $i ]['author'][0] ) && strlen( $pubprints[ $i ]['author'][0] ) !== 0 ) {
					echo "\n\t\t" . esc_html( $pubprints[ $i ]['author'][0] );
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
			echo "\n\t" . '<span class="imdbincluded-subtitle">' . esc_html( _n( 'Trivia', 'Trivias', $nbtotaltrivia, 'lumiere-movies' ) ) . ' </span>(' . intval( $nbtotaltrivia ) . ') <br>';

			for ( $i = 0; $i <= $nbtotaltrivia; $i++ ) {

				$text = isset( $trivia[ $i ] ) ? $this->link_maker->lumiere_imdburl_to_internalurl( $trivia[ $i ] ) : '';

				// It may be empty, continue to the next result.
				if ( strlen( $text ) === 0 ) {
					continue;
				}

				// Display a "show more" after 3 results
				if ( $i === $nblimittrivia ) {
					echo "\n\t\t" . '<div class="activatehidesection lumiere_align_center"><font size="-1"><strong>('
						. esc_html__( 'see all', 'lumiere-movies' )
						. ')</strong></font></div>'
						. "\n\t\t" . '<div class="hidesection">';
				}

				echo "\n\t\t\t" . '<div>';
				$text_cleaned = preg_replace( '~^\s\s\s\s\s\s\s(.*)<br \/>\s\s\s\s\s$~', "\\1", $text );
				echo "\n\t\t\t\t" . ' [#' . esc_html( strval( $i + 1 ) ) . '] ' . wp_kses(
					$text_cleaned ?? '',
					[
						'a' => [
							'href' => [],
							'title' => [],
							'class' => [],
						],
					]
				);
				echo "\n\t\t\t" . '</div>';

				if ( $i === $nbtotaltrivia ) {
					echo "\n\t\t" . '</div>';
				}

			}

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

				foreach ( $nickname as $nick ) {

					if ( is_string( $nick ) === false || strlen( $nick ) === 0 ) {
						continue;
					}

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
			echo "\n\t" . '<span class="imdbincluded-subtitle">' . esc_html__( 'Personal quotes', 'lumiere-movies' ) . ' </span> (' . intval( $nbtotalquotes ) . ')';

			for ( $i = 0; $i < $nbtotalquotes; $i++ ) {

				$text = isset( $quotes[ $i ] ) ? $this->link_maker->lumiere_imdburl_to_internalurl( $quotes[ $i ] ) : '';

				// It may be empty, continue to the next result.
				if ( strlen( $text ) === 0 ) {
					continue;
				}

				// Display a "show more" after XX results
				if ( $i === $nblimitquotes ) {
					echo "\n\t\t" . '<div class="activatehidesection lumiere_align_center"><font size="-1"><strong>('
						. esc_html__( 'see all', 'lumiere-movies' )
						. ')</strong></font></div>'
						. "\n\t\t" . '<div class="hidesection">';
				}

				echo "\n\t\t\t" . '<div>';
				echo ' [#' . esc_html( strval( $i + 1 ) ) . '] ' . wp_kses(
					$text,
					[
						'a' => [
							'href' => [],
							'title' => [],
							'class' => [],
						],
					]
				);
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
			echo "\n\t" . '<span class="imdbincluded-subtitle">' . esc_html__( 'Trademarks', 'lumiere-movies' ) . ' </span> (' . intval( $nbtotaltrademark ) . ')';

			for ( $i = 0; $i < $nbtotaltrademark; $i++ ) {

				$text = isset( $trademark[ $i ] ) ? $this->link_maker->lumiere_imdburl_to_internalurl( $trademark[ $i ] ) : '';

				// It may be empty, continue to the next result.
				if ( strlen( $text ) === 0 ) {
					continue;
				}

				// Display a "show more" after XX results
				if ( $i === $nblimittradmark ) {
					echo "\n\t\t" . '<div class="activatehidesection lumiere_align_center"><font size="-1"><strong>('
						. esc_html__( 'see all', 'lumiere-movies' )
						. ')</strong></font></div>'
						. "\n\t\t" . '<div class="hidesection">';
				}

				echo "\n\t\t\t" . '<div>';

				echo ' [@' . esc_html( strval( $i + 1 ) ) . '] ' . wp_kses(
					$text,
					[
						'a' => [
							'href' => [],
							'title' => [],
							'class' => [],
						],
					]
				);
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
		<div class="lumiere_display_flex lumiere_font_em_11 lumiere_align_center lum_padding_bott_2vh">
			<div class="lumiere_flex_auto lum_width_fit_cont">
				<div class="identity"><?php echo esc_html( $this->person_name ); ?></div>

				<?php

				# Birth
				$birthday = $this->person->born() !== null ? array_filter( $this->person->born() ) : [];
				if ( count( $birthday ) > 0 ) {
					echo "\n\t\t\t\t" . '<div id="birth"><font size="-1">';

					$birthday_day = isset( $birthday['day'] ) && strlen( $birthday['day'] ) > 0 ? (string) $birthday['day'] . ' ' : __( '(day unknown)', 'lumiere-movies' ) . ' ';
					$birthday_month = isset( $birthday['month'] ) && strlen( $birthday['month'] ) > 0 ? date_i18n( 'F', $birthday['month'] ) . ' ' : __( '(month unknown)', 'lumiere-movies' ) . ' ';
					$birthday_year = isset( $birthday['year'] ) && strlen( $birthday['year'] ) > 0 ? (string) $birthday['year'] : __( '(year unknown)', 'lumiere-movies' );

					echo "\n\t\t\t\t\t" . '<span class="imdbincluded-subtitle">'
						. esc_html__( 'Born on', 'lumiere-movies' ) . '</span>'
						. esc_html( $birthday_day . $birthday_month . $birthday_year );

					if ( isset( $birthday['place'] ) && strlen( $birthday['place'] ) > 0 ) {
						/** translators: 'in' like 'Born in' */
						echo ', ' . esc_html__( 'in', 'lumiere-movies' ) . ' ' . esc_html( $birthday['place'] );
					}

					echo "\n\t\t\t\t" . '</font></div>';
				}

				# Death
				$death = array_filter( $this->person->died() );
				if ( count( $death ) > 0 ) {

					echo "\n\t\t\t\t" . '<div id="death"><font size="-1">';

					$death_day = isset( $death['day'] ) && strlen( $death['day'] ) > 0 ? (string) $death['day'] . ' ' : __( '(day unknown)', 'lumiere-movies' ) . ' ';
					$death_month = isset( $death['month'] ) && strlen( $death['month'] ) > 0 ? date_i18n( 'F', $death['month'] ) . ' ' : __( '(month unknown)', 'lumiere-movies' ) . ' ';
					$death_year = isset( $death['year'] ) && strlen( $death['year'] ) > 0 ? (string) $death['year'] : __( '(year unknown)', 'lumiere-movies' );

					echo "\n\t\t\t\t\t" . '<span class="imdbincluded-subtitle">'
						. esc_html__( 'Died on', 'lumiere-movies' ) . '</span>'
						. esc_html( $death_day . $death_month . $death_year );

					if ( ( isset( $death['place'] ) ) && ( strlen( $death['place'] ) !== 0 ) ) {
						/** translators: 'in' like 'Died in' */
						echo ', ' . esc_html__( 'in', 'lumiere-movies' ) . ' ' . esc_html( $death['place'] );
					}

					if ( ( isset( $death['cause'] ) ) && ( strlen( $death['cause'] ) !== 0 ) ) {
						/** translators: 'cause' like 'Cause of death' */
						echo ' (' . esc_html( $death['cause'] . ')' );
					}

					echo "\n\t\t\t\t" . '</font></div>';
				}

				$bio = $this->link_maker->lumiere_medaillon_bio( $this->person->bio() );

				if ( is_string( $bio ) && strlen( $bio ) > 0 ) {
					echo "\n\t\t\t\t" . '<div id="bio" class="lumiere_padding_two lumiere_align_left"><font size="-1">';
					echo "\n\t\t\t\t" . wp_kses(
						$bio,
						[
							'span' => [ 'class' => [] ],
							'a' => [
								'href' => [],
								'title' => [],
								'class' => [],
							],
							'strong' => [],
							'div' => [ 'class' => [] ],
							'br' => [],
						]
					) . '</font></div>';
				}
				?>

			</div>

					<!-- star photo -->
			<div class="lumiere_width_20_perc lumiere_padding_two lum_popup_img"><?php

			// Select pictures: big poster, if not small poster, if not 'no picture'.
			$photo_url = '';
			$photo_big = (string) $this->person->photo_localurl( false );
			$photo_thumb = (string) $this->person->photo_localurl( true );

			if ( $this->imdb_cache_values['imdbusecache'] === '1' ) { // use IMDBphp only if cache is active
				$photo_url = strlen( $photo_big ) > 1 ? esc_url( $photo_big ) : esc_url( $photo_thumb ); // create big picture, thumbnail otherwise.
			}

			// Picture for a href, takes big/thumbnail picture if exists, no_pics otherwise.
			$photo_url_href = strlen( $photo_url ) === 0 ? esc_url( $this->config_class->lumiere_pics_dir . 'no_pics.gif' ) : $photo_url; // take big/thumbnail picture if exists, no_pics otherwise.

			// Picture for img: if 1/ thumbnail picture exists, use it, 2/ use no_pics otherwise
			$photo_url_img = strlen( $photo_thumb ) === 0 ? esc_url( $this->config_class->lumiere_pics_dir . 'no_pics.gif' ) : $photo_thumb;

			echo "\n\t\t\t\t" . '<a class="highslide_pic_popup" href="' . esc_url( $photo_url_href ) . '">';
			echo "\n\t\t\t\t\t" . '<img loading="lazy" src="'
				. esc_url( $photo_url_img )
				. '" alt="'
				. esc_attr( $this->person_name ) . '"';

			// add width only if "Display only thumbnail" is unactive.
			if ( $this->imdb_admin_values['imdbcoversize'] === '0' ) {

				echo ' width="' . intval( $this->imdb_admin_values['imdbcoversizewidth'] ) . '"';

				// add 100px width if "Display only thumbnail" is active.
			} elseif ( $this->imdb_admin_values['imdbcoversize'] === '1' ) {

				echo ' width="100px"';

			}

			echo ' />';
			echo "\n\t\t\t\t</a>";

			?>

			</div> 
		</div> 
							
		<?php
	}

}

