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
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Imdb\Name;
use Lumiere\Frontend\Popups\Head_Popups;
use Lumiere\Frontend\Main;
use Lumiere\Tools\Validate_Get;

/**
 * Display star information in a popup
 *
 * @see \Lumiere\Alteration\Rewrite_Rules Create the rules for building a virtual page
 * @see \Lumiere\Frontend\Frontend Redirect to this page using virtual pages {@link \Lumiere\Alteration\Virtual_Page}
 * @see \Lumiere\Frontend\Popups\Head_Popups Modify the popup header, Parent class
 * @since 4.3 is child class
 *
 * Bots are banned before getting popups
 * @see \Lumiere\Frontend\Frontend::ban_bots_popups() Bot banishement happens there, before processing IMDb queries
 */
class Popup_Person extends Head_Popups {

	/**
	 * Traits
	 */
	use Main; // Using a new trait (not parent's) shows the correct class $this->classname

	/**
	 * The person queried as object result
	 */
	private Name $person;

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
	 */
	public function __construct() {

		// Edit metas tags in popups and various checks in Parent class.
		parent::__construct();

		/**
		 * Display layout
		 * @since 4.0 using 'the_posts' instead of the 'content', removed the 'get_header' for OceanWP
		 * @since 4.1.2 using 'template_include' which is the proper way to include templates
		 */
		add_filter( 'template_include', [ $this, 'popup_layout' ], 12 ); // 12, 1 more than Virtual_Page, otherwise the <head> doesn't show up.
	}

	/**
	 * Search movie title
	 *
	 * @return bool True if movie was found
	 */
	private function find_result(): bool {

		/* GET Vars sanitized */
		$mid_sanitized = Validate_Get::sanitize_url( 'mid' );
		$this->mid_sanitized = $mid_sanitized !== null && strlen( $mid_sanitized ) > 0 ? $mid_sanitized : null;

		// if neither film nor mid are set, throw a 404 error
		if ( $this->mid_sanitized === null ) {
			status_header( 404 );
			$this->logger->log()->error( '[Lumiere] No person id entered' );
			return false;
		}

		$this->logger->log()->debug( '[Lumiere] Movie person IMDb ID provided in URL: ' . $this->mid_sanitized );

		$this->person = new Name( $this->mid_sanitized, $this->plugins_classes_active['imdbphp'], $this->logger->log() );
		$this->person_name = $this->person->name();
		return true;
	}

	/**
	 * Display layout
	 *
	 * @return string
	 */
	public function popup_layout(): string {

		// Nonce. Always valid if admin is connected.
		$nonce_valid = ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ) ) > 0 ) || is_user_logged_in() === true ? true : false; // Created in Abstract_Link_Maker class.

		// Validate $_GET['info_person'], exit if failed.
		$get_info_person = Validate_Get::sanitize_url( 'info_person' );
		if ( isset( $_GET['info_person'] ) && $get_info_person === null || $nonce_valid === false ) {
			wp_die( esc_html__( 'Lumière Movies: Invalid person search query.', 'lumiere-movies' ) );
		}

		echo "<!DOCTYPE html>\n<html>\n<head>\n";
		wp_head();
		echo "\n</head>\n<body class=\"lum_body_popup";
		echo isset( $this->imdb_admin_values['imdbpopuptheme'] ) ? ' lum_body_popup_' . esc_attr( $this->imdb_admin_values['imdbpopuptheme'] ) . '">' : '">';

		// Exit if no person was found.
		if ( $this->find_result() === false ) {
			status_header( 404 );
			$text = 'Could not find any IMDb person with this query.';
			$this->logger->log()->error( '[Lumiere][' . $this->classname . '] ' . $text );
			wp_die( esc_html( $text ) );
		}

		/**
		 * Display a spinner when clicking a link with class .lum_add_spinner (a <div class="loader"> will be inserted inside by the js)
		 */
		echo '<div id="spinner-placeholder"></div>';

		$this->logger->log()->debug( '[Lumiere][' . $this->classname . '] Using the link maker class: ' . str_replace( 'Lumiere\Link_Makers\\', '', get_class( $this->link_maker ) ) );

		// Show menu.
		$this->display_menu();

		// Show portrait.
		$this->display_portrait();

		//--------------------------------------------------------------------------- summary
		// display only when nothing is selected from the menu.
		if (
			$get_info_person === null || strlen( $get_info_person ) === 0
		) {
			echo strlen( $this->display_summary() ) > 0 ? wp_kses(
				$this->display_summary(),
				[
					'span' => [ 'class' => [] ],
					'font' => [ 'size' => [] ],
					'strong' => [],
					'div' => [
						'align' => [],
						'rel' => [],
						'class' => [],
					],
					'i' => [],
					'a' => [
						'href' => [],
						'rel' => [],
						'class' => [],
					],
				]
			) : '<div class="lumiere_italic lumiere_align_center">' . esc_html__( 'No summary found ', 'lumiere-movies' ) . '</div>';
		}

		//--------------------------------------------------------------------------- full filmography
		if (
			$get_info_person === 'filmo'
		) {
			echo strlen( $this->display_full_filmo() ) > 0 ? wp_kses(
				$this->display_full_filmo(),
				[
					'span' => [ 'class' => [] ],
					'font' => [ 'size' => [] ],
					'strong' => [],
					'div' => [
						'align' => [],
						'rel' => [],
						'class' => [],
					],
					'i' => [],
					'a' => [
						'href' => [],
						'rel' => [],
						'class' => [],
					],
				]
			) : '<div class="lumiere_italic lumiere_align_center">' . esc_html__( 'No filmography found ', 'lumiere-movies' ) . '</div>';

		}

		// ------------------------------------------------------------------------------ partie bio
		if (
			$get_info_person === 'bio'
		) {
			echo strlen( $this->display_bio() ) > 0 ? wp_kses(
				$this->display_bio(),
				[
					'span' => [ 'class' => [] ],
					'div' => [
						'align' => [],
						'class' => [],
						'id' => [],
					],
					'a' => [
						'href' => [],
						'rel' => [],
						'class' => [],
					],
					'font' => [ 'size' => [] ],
					'strong' => [],
					'i' => [],
				]
			) : '<div class="lumiere_italic lumiere_align_center">' . esc_html__( 'No biography found ', 'lumiere-movies' ) . '</div>';
		}

		// ------------------------------------------------------------------------------ misc part
		if (
			$get_info_person === 'misc'
		) {
			echo strlen( $this->display_misc() ) > 0 ? wp_kses(
				$this->display_misc(),
				[
					'span' => [ 'class' => [] ],
					'div' => [
						'align' => [],
						'class' => [],
						'id' => [],
					],
					'a' => [
						'href' => [],
						'rel' => [],
						'class' => [],
					],
					'font' => [ 'size' => [] ],
					'strong' => [],
					'i' => [],
				]
			) : '<div class="lumiere_italic lumiere_align_center">' . esc_html__( 'No misc info found ', 'lumiere-movies' ) . '</div>';
		}

		// The end.
		wp_meta();
		wp_footer();
		echo "</body>\n</html>";

		// Prevent the proper template to be displayed
		return '';
	}

	/**
	 * Display navigation menu
	 */
	private function display_menu(): void {
		// If polylang plugin is active, rewrite the URL to append the lang string
		$url_if_polylang = apply_filters( 'lum_polylang_rewrite_url_with_lang', $this->config_class->lumiere_urlpopupsperson );
		?>
												<!-- top page menu -->
		<div class="lumiere_container lumiere_font_em_11 lum_popup_titlemenu">
			<?php /* if ( isset( $_GET['info_person'] ) && strlen( $_GET['info_person'] ) > 0 ) {  ?>
			<div class="lumiere_flex_auto">
				<a rel="nofollow" id="lum_popup_link_back" class="lum_popup_menu_title lum_add_spinner" href="<?php
				$refer = wp_get_referer();
				echo $refer !== false ? esc_url( $refer ) : ''; ?>"><?php esc_html_e( 'Back', 'lumiere-movies' ); ?></a>
			</div>
			<?php }*/?>
			<div class="lumiere_flex_auto">
				<a rel="nofollow" class="lum_popup_menu_title lum_add_spinner" href="<?php echo esc_url( wp_nonce_url( $url_if_polylang . '?mid=' . $this->mid_sanitized . '&info_person=' ) ); ?>" title="<?php echo esc_attr( $this->person_name ) . ': ' . esc_html__( 'Summary', 'lumiere-movies' ); ?>"><?php esc_html_e( 'Summary', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				<a rel="nofollow" class="lum_popup_menu_title lum_add_spinner" href="<?php echo esc_url( wp_nonce_url( $url_if_polylang . '?mid=' . $this->mid_sanitized . '&info_person=filmo' ) ); ?>" title="<?php echo esc_attr( $this->person_name ) . ': ' . esc_html__( 'Full filmography', 'lumiere-movies' ); ?>"><?php esc_html_e( 'Full filmography', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				<a rel="nofollow" class="lum_popup_menu_title lum_add_spinner" href="<?php echo esc_url( wp_nonce_url( $url_if_polylang . '?mid=' . $this->mid_sanitized . '&info_person=bio' ) ); ?>" title="<?php echo esc_attr( $this->person_name ) . ': ' . esc_html__( 'Full biography', 'lumiere-movies' ); ?>"><?php esc_html_e( 'Full biography', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				<a rel="nofollow" class="lum_popup_menu_title lum_add_spinner" href="<?php echo esc_url( wp_nonce_url( $url_if_polylang . '?mid=' . $this->mid_sanitized . '&info_person=misc' ) ); ?>" title="<?php echo esc_attr( $this->person_name ) . ': ' . esc_html__( 'Misc', 'lumiere-movies' ); ?>"><?php esc_html_e( 'Misc', 'lumiere-movies' ); ?></a>
			</div>
		</div>

		<?php
	}

	/**
	 * Display summary page
	 * Director actor and producer filmography
	 */
	private function display_summary(): string {

		$list_roles = [ // list of types of movies to query translated
			'director' => esc_html__( 'director', 'lumiere-movies' ),
			'actor' => esc_html__( 'actor', 'lumiere-movies' ),
			'actress' => esc_html__( 'actress', 'lumiere-movies' ),
		];
		$max_films = 9; // max number of movies before breaking with "see all"

		return $this->get_movies( $list_roles, $max_films );
	}

	/**
	 * Display full filmography page
	 */
	private function display_full_filmo(): string {

		$list_roles = [ // list of types of movies to query translated
			'director' => esc_html__( 'director', 'lumiere-movies' ),
			'actor' => esc_html__( 'actor', 'lumiere-movies' ),
			'actress' => esc_html__( 'actress', 'lumiere-movies' ),
			'assistantDirector' => esc_html__( 'assistant director', 'lumiere-movies' ),
			'showrunner' => esc_html__( 'showrunner', 'lumiere-movies' ),
			'writer' => esc_html__( 'writer', 'lumiere-movies' ),
			'cinematographer' => esc_html__( 'cinematographer', 'lumiere-movies' ),
			'producer' => esc_html__( 'producer', 'lumiere-movies' ),
			'editor' => esc_html__( 'editor', 'lumiere-movies' ),
			'self' => esc_html__( 'self', 'lumiere-movies' ),
			'soundtrack' => esc_html__( 'soundtrack', 'lumiere-movies' ),
			'archiveFootage' => esc_html__( 'archive footage', 'lumiere-movies' ),
			'thanks' => esc_html__( 'thanks', 'lumiere-movies' ),
			'stunts' => esc_html__( 'stunts', 'lumiere-movies' ),
			'artDepartment' => esc_html__( 'art department', 'lumiere-movies' ),
		];
		$max_films = 15; // max number of movies before breaking with "see all"

		return $this->get_movies( $list_roles, $max_films );
	}

	/**
	 * Display biography
	 */
	private function display_bio(): string {

		$output = '';

		############## Spouses

		$spouses = $this->person->spouse();
		$nbtotalspouses = count( $spouses );

		if ( $nbtotalspouses > 0 ) {
			$output .= "\n\t\t\t\t\t\t\t" . ' <!-- Spouses -->';
			$output .= "\n" . '<div id="lumiere_popup_spouses">';
			$output .= "\n\t" . '<span class="lum_results_section_subtitle">' . esc_html( _n( 'Spouse', 'Spouses', $nbtotalspouses, 'lumiere-movies' ) ) . '</span>';
			for ( $i = 0; $i < $nbtotalspouses; ++$i ) {

				if ( isset( $spouses[ $i ]['imdb'] ) && strlen( $spouses[ $i ]['imdb'] ) > 0 ) {
					$output .= "<a rel=\"nofollow\" class='lum_popup_internal_link lum_add_spinner' href='" . esc_url( wp_nonce_url( $this->config_class->lumiere_urlpopupsperson . '?mid=' . intval( $spouses[ $i ]['imdb'] ) ) ) . "'>";
				}

				if ( isset( $spouses[ $i ]['name'] ) && strlen( $spouses[ $i ]['name'] ) > 0 ) {
					$output .= esc_html( $spouses[ $i ]['name'] );
				}

				if ( isset( $spouses[ $i ]['imdb'] ) && strlen( $spouses[ $i ]['imdb'] ) > 0 ) {
					$output .= '</a>';
				}

				if ( isset( $spouses[ $i ]['dateText'] ) && strlen( $spouses[ $i ]['dateText'] ) > 0 ) {
					$output .= ' (' . esc_html( $spouses[ $i ]['dateText'] ) . ') ';
				}
			}

			$output .= "\n" . '</div>';
		}

		############## Children

		$children = $this->person->children();
		$nbtotalchildren = count( $children );
		$nbtotalchildren_bugged = $children[0]['name'] ?? ''; // Sometimes return an array even if name is empty, but name is always empty if no children are found

		if ( $nbtotalchildren_bugged > 0 && strlen( $nbtotalchildren_bugged ) > 0 ) { // Extra check with _bugged to make sure it's really empty
			$output .= "\n\t\t\t\t\t\t\t" . ' <!-- Children -->';
			$output .= "\n" . '<div id="lumiere_popup_children">';
			$output .= "\n\t" . '<span class="lum_results_section_subtitle">' . esc_html( _n( 'Child', 'Children', $nbtotalchildren, 'lumiere-movies' ) ) . '</span>';
			for ( $i = 0; $i < $nbtotalchildren; ++$i ) {

				if ( isset( $children[ $i ]['imdb'] ) && strlen( $children[ $i ]['imdb'] ) > 0 ) {
					$output .= "<a rel=\"nofollow\" class='lum_popup_internal_link lum_add_spinner' href='" . esc_url( wp_nonce_url( $this->config_class->lumiere_urlpopupsperson . '?mid=' . intval( $children[ $i ]['imdb'] ) ) ) . "'>";
				}

				if ( isset( $children[ $i ]['name'] ) && strlen( $children[ $i ]['name'] ) > 0 ) {
					$output .= esc_html( $children[ $i ]['name'] );
				}

				if ( isset( $children[ $i ]['imdb'] ) && strlen( $children[ $i ]['imdb'] ) > 0 ) {
					$output .= '</a>';
				}

				if ( isset( $children[ $i ]['name'] ) && strlen( $children[ $i ]['name'] ) > 0 ) {
					$output .= ' (<span class="lumiere_italic">' . esc_html( $children[ $i ]['relType'] ) . '</span>) ';
				}

			}

			$output .= "\n" . '</div>';
		}

		##############  Bio movies

		$biomovie = $this->person->pubmovies();
		$nbtotalbiomovie = count( $biomovie );

		if ( $nbtotalbiomovie !== 0 ) {

			$output .= "\n\t\t\t\t\t\t\t" . ' <!-- Biographical movies -->';
			$output .= "\n" . '<div id="lumiere_popup_biomovies">';
			$output .= "\n\t" . '<span class="lum_results_section_subtitle">' . esc_html( _n( 'Biographical movie', 'Biographical movies', $nbtotalbiomovie, 'lumiere-movies' ) ) . '</span>';

			for ( $i = 0; $i < $nbtotalbiomovie; ++$i ) {

				$output .= "<a rel=\"nofollow\" class='lum_popup_internal_link lum_add_spinner' href='" . esc_url( wp_nonce_url( $this->config_class->lumiere_urlpopupsfilms . '?mid=' . intval( $biomovie[ $i ]['id'] ) ) ) . "'>" . esc_html( $biomovie[ $i ]['title'] ) . '</a>';

				if ( isset( $biomovie[ $i ]['year'] ) && $biomovie[ $i ]['year'] > 0 ) {
					$output .= ' (' . intval( $biomovie[ $i ]['year'] ) . ') ';
				}
			}

			$output .= "\n" . '</div>';
		}

		############## Portrayed in

		$portrayedmovie = $this->person->pubportrayal();
		$nbtotalportrayedmovie = count( $portrayedmovie );

		if ( $nbtotalportrayedmovie !== 0 ) {

			$output .= "\n\t\t\t\t\t\t\t" . ' <!-- Portrayed in -->';
			$output .= "\n" . '<div id="lumiere_popup_biomovies">';
			$output .= "\n\t" . '<span class="lum_results_section_subtitle">' . esc_html__( 'Portrayed in', 'lumiere-movies' ) . '</span>';

			for ( $i = 0; $i < $nbtotalportrayedmovie; ++$i ) {

				$output .= "<a rel=\"nofollow\" class='lum_popup_internal_link lum_add_spinner' href='" . esc_url( wp_nonce_url( $this->config_class->lumiere_urlpopupsfilms . '?mid=' . esc_html( $portrayedmovie[ $i ]['id'] ) ) ) . "'>" . esc_html( $portrayedmovie[ $i ]['title'] ) . '</a>';

				if ( isset( $portrayedmovie[ $i ]['year'] ) && strlen( strval( $portrayedmovie[ $i ]['year'] ) ) > 0 ) {
					$output .= ' (' . esc_html( $portrayedmovie[ $i ]['year'] ) . ') ';
				}
			}

			$output .= "\n" . '</div>';

		}

		############## Interviews

		$interviews = $this->person->pubinterview();
		$nbtotalinterviews = isset( $interviews ) ? count( $interviews ) : 0;

		if ( $nbtotalinterviews > 0 ) {

			$output .= "\n\t\t\t\t\t\t\t" . ' <!-- Interviews -->';
			$output .= "\n" . '<div id="lumiere_popup_biomovies">';
			$output .= "\n\t" . '<span class="lum_results_section_subtitle">' . esc_html( _n( 'Interview', 'Interviews', $nbtotalinterviews, 'lumiere-movies' ) ) . '</span>';

			for ( $i = 0; $i < $nbtotalinterviews; $i++ ) {

				$output .= isset( $interviews[ $i ] ) && isset( $interviews[ $i ]['title'] ) ? '<i>' . esc_html( $interviews[ $i ]['title'] ) . '</i> ' : '';

				if ( isset( $interviews[ $i ]['date']['year'] ) && strlen( strval( $interviews[ $i ]['date']['year'] ) ) !== 0 ) {
					$output .= ' (' . esc_html( strval( $interviews[ $i ]['date']['year'] ) ) . ') ';
				}

				if ( isset( $interviews[ $i ]['reference'] ) && strlen( $interviews[ $i ]['reference'] ) !== 0 ) {
					$output .= esc_html( $interviews[ $i ]['reference'] );
				}

				if ( $i < $nbtotalinterviews - 1 ) {
					$output .= ', ';
				}

			}

			$output .= '</div>';

		}

		############## Publicity printed

		$pubprints = $this->person->pubprints();
		$nbtotalpubprints = count( $pubprints );
		$nblimitpubprints = 9;

		if ( $nbtotalpubprints !== 0 ) {

			$output .= "\n\t\t\t\t\t\t\t" . ' <!-- Publicity printed -->';
			$output .= "\n" . '<div id="lumiere_popup_biomovies">';
			$output .= "\n\t" . '<span class="lum_results_section_subtitle">'
				. esc_html( _n( 'Print ads', 'Printed ads', $nbtotalpubprints, 'lumiere-movies' ) )
				. '</span>';
			for ( $i = 0; $i < $nbtotalpubprints; $i++ ) {

				// Display a "show more" after XX results
				if ( $i === $nblimitpubprints ) {
					$output .= "\n\t" . '<span class="activatehidesection"><font size="-1"><strong>&nbsp;('
						. esc_html__( 'see all', 'lumiere-movies' )
						. ')</strong></font></span> '
						. "\n\t" . '<span class="hidesection">';
				}

				if ( isset( $pubprints[ $i ]['author'][0] ) && strlen( $pubprints[ $i ]['author'][0] ) !== 0 ) {
					$output .= "\n\t\t" . esc_html( $pubprints[ $i ]['author'][0] );
				}

				if ( isset( $pubprints[ $i ]['title'] ) && strlen( $pubprints[ $i ]['title'] ) !== 0 ) {
					$output .= ' <i>' . esc_html( $pubprints[ $i ]['title'] ) . '</i> ';
				}

				if ( isset( $pubprints[ $i ]['year'] ) && strlen( $pubprints[ $i ]['year'] ) !== 0 ) {
					$output .= '(' . intval( $pubprints[ $i ]['year'] ) . ')';
				}

				if ( isset( $pubprints[ $i ]['details'] ) && strlen( $pubprints[ $i ]['details'] ) !== 0 ) {
					$output .= esc_html( $pubprints[ $i ]['details'] ) . ' ';
				}

				if ( $i < ( $nbtotalpubprints - 1 ) ) {
					$output .= ', ';
				}

				if ( $i === ( $nbtotalpubprints - 1 ) ) {
					$output .= "\n\t" . '</span>';
				}
			}

			$output .= "\n" . '</div>';

		}
		return $output;
	}

	/**
	 * Display miscellaenous infos
	 * @return string The text to display
	 */
	private function display_misc(): string {

		############## Trivia

		$trivia = $this->person->trivia();
		$nbtotaltrivia = count( $trivia );
		$nblimittrivia = 3; # max number of trivias before breaking with "see all"
		$output = '';

		if ( $nbtotaltrivia !== 0 ) {

			$output .= "\n\t\t\t\t\t\t\t" . ' <!-- Trivia -->';
			$output .= "\n" . '<div id="lumiere_popup_biomovies">';
			$output .= "\n\t" . '<span class="lum_results_section_subtitle">' . esc_html( _n( 'Trivia', 'Trivias', $nbtotaltrivia, 'lumiere-movies' ) ) . ' </span>(' . intval( $nbtotaltrivia ) . ') <br>';

			for ( $i = 0; $i <= $nbtotaltrivia; $i++ ) {

				$text = isset( $trivia[ $i ] ) ? $this->link_maker->lumiere_imdburl_to_internalurl( $trivia[ $i ] ) : '';

				// It may be empty, continue to the next result.
				if ( strlen( $text ) === 0 ) {
					continue;
				}

				// Display a "show more" after 3 results
				if ( $i === $nblimittrivia ) {
					$output .= "\n\t\t" . '<div class="activatehidesection lumiere_align_center"><font size="-1"><strong>('
						. esc_html__( 'see all', 'lumiere-movies' )
						. ')</strong></font></div>'
						. "\n\t\t" . '<div class="hidesection">';
				}

				$output .= "\n\t\t\t" . '<div>';
				$text_cleaned = preg_replace( '~^\s\s\s\s\s\s\s(.*)<br \/>\s\s\s\s\s$~', "\\1", $text );
				$output .= "\n\t\t\t\t" . ' [#' . esc_html( strval( $i + 1 ) ) . '] ' . wp_kses(
					$text_cleaned ?? '',
					[
						'a' => [
							'href' => [],
							'title' => [],
							'class' => [],
						],
					]
				);
				$output .= "\n\t\t\t" . '</div>';

				if ( $i === $nbtotaltrivia ) {
					$output .= "\n\t\t" . '</div>';
				}

			}

			$output .= "\n" . '</div>';
		}

		############## Nicknames

		$nickname = $this->person->nickname();
		$nbtotalnickname = count( $nickname );

		if ( $nbtotalnickname !== 0 ) {

			$output .= "\n\t\t\t\t\t\t\t" . ' <!-- Nicknames -->';
			$output .= "\n" . '<div id="lumiere_popup_biomovies">';
			$output .= "\n\t" . '<span class="lum_results_section_subtitle">' . esc_html__( 'Nicknames', 'lumiere-movies' ) . ' </span>';

			for ( $i = 0; $i < $nbtotalnickname; $i++ ) {

				foreach ( $nickname as $nick ) {

					if ( is_string( $nick ) === false || strlen( $nick ) === 0 ) {
						continue;
					}

					$txt = str_replace( '<br>', ', ', $nick );
					$output .= esc_html( $txt );
				}
			}

			$output .= "\n" . '</div>';

		}

		############## Personal quotes

		$quotes = $this->person->quotes();
		$nbtotalquotes = count( $quotes );
		$nblimitquotes = 3;

		if ( $nbtotalquotes !== 0 ) {

			$output .= "\n\t\t\t\t\t\t\t" . ' <!-- Personal quotes -->';
			$output .= "\n" . '<div id="lumiere_popup_quotes">';
			$output .= "\n\t" . '<span class="lum_results_section_subtitle">' . esc_html__( 'Personal quotes', 'lumiere-movies' ) . ' </span> (' . intval( $nbtotalquotes ) . ')';

			for ( $i = 0; $i < $nbtotalquotes; $i++ ) {

				$text = isset( $quotes[ $i ] ) ? $this->link_maker->lumiere_imdburl_to_internalurl( $quotes[ $i ] ) : '';

				// It may be empty, continue to the next result.
				if ( strlen( $text ) === 0 ) {
					continue;
				}

				// Display a "show more" after XX results
				if ( $i === $nblimitquotes ) {
					$output .= "\n\t\t" . '<div class="activatehidesection lumiere_align_center"><font size="-1"><strong>('
						. esc_html__( 'see all', 'lumiere-movies' )
						. ')</strong></font></div>'
						. "\n\t\t" . '<div class="hidesection">';
				}

				$output .= "\n\t\t\t" . '<div>';
				$output .= ' [#' . esc_html( strval( $i + 1 ) ) . '] ' . wp_kses(
					$text,
					[
						'a' => [
							'href' => [],
							'title' => [],
							'class' => [],
						],
					]
				);
				$output .= '</div>';

				if ( $i === ( $nbtotalquotes - 1 ) ) {
					$output .= "\n\t\t" . '</div>';
				}

			}

			$output .= "\n" . '</div>';

		}

		############## Trademarks

		$trademark = $this->person->trademark();
		$nbtotaltrademark = count( $trademark );
		$nblimittradmark = 5;

		if ( $nbtotaltrademark !== 0 ) {

			$output .= "\n\t\t\t\t\t\t\t" . ' <!-- Trademarks -->';
			$output .= "\n" . '<div id="lumiere_popup_biomovies">';
			$output .= "\n\t" . '<span class="lum_results_section_subtitle">' . esc_html__( 'Trademarks', 'lumiere-movies' ) . ' </span> (' . intval( $nbtotaltrademark ) . ')';

			for ( $i = 0; $i < $nbtotaltrademark; $i++ ) {

				$text = isset( $trademark[ $i ] ) ? $this->link_maker->lumiere_imdburl_to_internalurl( $trademark[ $i ] ) : '';

				// It may be empty, continue to the next result.
				if ( strlen( $text ) === 0 ) {
					continue;
				}

				// Display a "show more" after XX results
				if ( $i === $nblimittradmark ) {
					$output .= "\n\t\t" . '<div class="activatehidesection lumiere_align_center"><font size="-1"><strong>('
						. esc_html__( 'see all', 'lumiere-movies' )
						. ')</strong></font></div>'
						. "\n\t\t" . '<div class="hidesection">';
				}

				$output .= "\n\t\t\t" . '<div>';

				$output .= ' [@' . esc_html( strval( $i + 1 ) ) . '] ' . wp_kses(
					$text,
					[
						'a' => [
							'href' => [],
							'title' => [],
							'class' => [],
						],
					]
				);
				$output .= '</div>';

				if ( $i === $nbtotaltrademark - 1 ) {
					echo "\n\t\t" . '</div>';
				}

			}

			$output .= "\n" . '</div>';

		}

		return $output;
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
				$get_birthday = $this->person->born();
				$birthday = $get_birthday !== null ? array_filter( $get_birthday, fn( $get_birthday ) => ( $get_birthday !== false && $get_birthday !== '' ) ) : [];
				if ( count( $birthday ) > 0 ) {
					echo "\n\t\t\t\t" . '<div id="birth"><font size="-1">';

					$birthday_day = isset( $birthday['day'] ) ? strval( $birthday['day'] ) . ' ' : '(' . __( 'day unknown', 'lumiere-movies' ) . ') ';
					$birthday_month = isset( $birthday['month'] ) && strlen( $birthday['month'] ) > 0 ? date_i18n( 'F', $birthday['month'] ) . ' ' : '(' . __( 'month unknown', 'lumiere-movies' ) . ') ';
					$birthday_year = isset( $birthday['year'] ) ? strval( $birthday['year'] ) : '(' . __( 'year unknown', 'lumiere-movies' ) . ')';

					echo "\n\t\t\t\t\t" . '<span class="lum_results_section_subtitle">'
						. esc_html__( 'Born on', 'lumiere-movies' ) . '</span>'
						. esc_html( $birthday_day . $birthday_month . $birthday_year );

					if ( isset( $birthday['place'] ) && strlen( $birthday['place'] ) > 0 ) {
						/** translators: 'in' like 'Born in' */
						echo ', ' . esc_html__( 'in', 'lumiere-movies' ) . ' ' . esc_html( $birthday['place'] );
					}

					echo "\n\t\t\t\t" . '</font></div>';
				}

				# Death
				$death = $this->person->died();
				if ( $death['status'] === 'DEAD' ) {

					echo "\n\t\t\t\t" . '<div id="death"><font size="-1">';

					$death_day = isset( $death['day'] ) && strlen( strval( $death['day'] ) ) > 0 ? (string) $death['day'] . ' ' : __( '(day unknown)', 'lumiere-movies' ) . ' ';
					$death_month = isset( $death['month'] ) && strlen( $death['month'] ) > 0 ? date_i18n( 'F', $death['month'] ) . ' ' : __( '(month unknown)', 'lumiere-movies' ) . ' ';
					$death_year = isset( $death['year'] ) && strlen( strval( $death['year'] ) ) > 0 ? (string) $death['year'] : __( '(year unknown)', 'lumiere-movies' );

					echo "\n\t\t\t\t\t" . '<span class="lum_results_section_subtitle">'
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
			$photo_big = (string) $this->person->photoLocalurl( false );
			$photo_thumb = (string) $this->person->photoLocalurl( true );

			if ( $this->imdb_cache_values['imdbusecache'] === '1' ) { // use IMDBphp only if cache is active
				$photo_url = strlen( $photo_big ) > 1 ? esc_url( $photo_big ) : esc_url( $photo_thumb ); // create big picture, thumbnail otherwise.
			}

			// Picture for a href, takes big/thumbnail picture if exists, no_pics otherwise.
			$photo_url_href = strlen( $photo_url ) === 0 ? esc_url( $this->config_class->lumiere_pics_dir . 'no_pics.gif' ) : $photo_url; // take big/thumbnail picture if exists, no_pics otherwise.

			// Picture for img: if 1/ thumbnail picture exists, use it, 2/ use no_pics otherwise
			$photo_url_img = strlen( $photo_thumb ) === 0 ? esc_url( $this->config_class->lumiere_pics_dir . 'no_pics.gif' ) : $photo_thumb;

			echo "\n\t\t\t\t" . '<a class="lum_pic_inpopup" href="' . esc_url( $photo_url_href ) . '">';
			echo "\n\t\t\t\t\t" . '<img loading="lazy" src="'
				. esc_url( $photo_url_img )
				. '" alt="' . esc_attr( $this->person_name ) . '"';

			// add width only if "Display only thumbnail" is unactive.
			if ( $this->imdb_admin_values['imdbcoversize'] === '0' ) {
				$width = intval( $this->imdb_admin_values['imdbcoversizewidth'] );
				$height = $width * 1.4;
				echo ' width="' . esc_attr( strval( $width ) ) . '" height="' . esc_attr( strval( $height ) ) . '"';

				// add 100px width if "Display only thumbnail" is active.
			} elseif ( $this->imdb_admin_values['imdbcoversize'] === '1' ) {

				echo ' width="100" height="160"';

			}

			echo ' />';
			echo "\n\t\t\t\t</a>";

			?>

			</div> 
		</div> 
							
		<?php
	}

	/**
	 * Helper method to get all movies
	 * @param array<string, string> $list_roles List of the roles with translation
	 * @param int $max_films
	 * @return string
	 */
	private function get_movies( array $list_roles, int $max_films = 10 ): string {

		$output = '';
		$all_movies = $this->person->credit(); // retrieve all movies for current person.
		$list_roles_english = array_keys( $list_roles );

		foreach ( $list_roles_english as $role ) {

			$nb_films = count( $all_movies[ $role ] ); // Count the total number of movies.
			$i = 0;

			if ( $nb_films < 1 ) { // If not movies for current category found, jump to the next.
				continue;
			}

			$output .= "\n\t\t\t\t\t\t\t" . ' <!-- ' . esc_html( $role ) . ' filmography -->';
			$output .= "\n\t" . '<div align="center" class="lumiere_container">';
			$output .= "\n\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';

			$output .= "\n\t" . '<div>';
			$output .= "\n\t\t" . '<span class="lum_results_section_subtitle">' . ucfirst( esc_html( $list_roles[ $role ] ) ) . ' </span>';

			foreach ( $all_movies[ $role ] as $credit_role ) {

				$output .= " <a rel=\"nofollow\" class='lum_popup_internal_link lum_add_spinner' href='" . esc_url( wp_nonce_url( $this->config_class->lumiere_urlpopupsfilms . '?mid=' . esc_html( $credit_role['titleId'] ) ) ) . "'>" . esc_html( $credit_role['titleName'] ) . '</a>';

				if ( isset( $credit_role['year'] ) ) {
					$output .= ' (';
					$output .= intval( $credit_role['year'] );
					$output .= ')';
				}

				if ( isset( $credit_role['characters'] ) && count( $credit_role['characters'] ) > 0 ) {
					$output .= ' as <i>' . esc_html( $credit_role['characters'][0] ) . '</i>';

				}

				// Display a "show more" after XX results
				if ( $i === $max_films ) {
					$output .= '&nbsp;<span class="activatehidesection"><font size="-1"><strong>('
						. esc_html__( 'see all', 'lumiere-movies' )
						. ')</strong></font></span> '
						. '<span class="hidesection">';
				}

				if ( $i === $nb_films ) {
					$output .= '</span>';
				}

				$i++;
			}

			// Close the div for current movie category.
			$output .= "\n\t" . '</div>';
		}

		return $output;
	}

}

