<?php declare( strict_types = 1 );
/**
 * Popup for movie search
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       2.0
 * @package lumiere-movies
 */

namespace Lumiere\Frontend\Popups;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! class_exists( 'Lumiere\Settings' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Imdb\TitleSearch;
use Lumiere\Frontend\Popups\Head_Popups;
use Lumiere\Frontend\Main;
use Lumiere\Tools\Validate_Get;
use Lumiere\Tools\Data;

/**
 * Displays movie search results in a popup
 *
 * @see \Lumiere\Alteration\Rewrite_Rules Create the rules for building a virtual page
 * @see \Lumiere\Frontend\Frontend Redirect to this page using virtual pages {@link \Lumiere\Alteration\Virtual_Page}
 * @see \Lumiere\Frontend\Popups\Head_Popups Modify the popup header, Parent class
 *
 * Bots are banned before getting popups
 * @see \Lumiere\Frontend\Frontend::ban_bots_popups() Bot banishement happens there, before processing IMDb queries
 * @since 4.3 is child class
 *
 * @phpstan-import-type TITLESEARCH_RETURNSEARCH from \Lumiere\Tools\Settings_Global
 */
class Popup_Movie_Search extends Head_Popups {

	/**
	 * Traits
	 */
	use Main; // Using a new trait (not parent's) shows the correct class $this->classname

	/**
	 * Movie's title
	 */
	private string $film_sanitized;

	/**
	 * Constructor
	 */
	public function __construct() {

		// Edit metas tags in popups and various checks in Parent class.
		parent::__construct();

		// Build the vars.
		// @since 4.0 lowercase, less cache used.
		$film_sanitized = Validate_Get::sanitize_url( 'film' );
		$this->film_sanitized = $film_sanitized !== null ? str_replace( [ '\\', '+' ], [ '', ' ' ], strtolower( Data::lumiere_name_htmlize( $film_sanitized ) ) ) : '';

		/**
		 * Display layout
		 * @since 4.0 using 'the_posts' instead of the 'content', removed the 'get_header' for OceanWP
		 * @since 4.1.2 using 'template_include' which is the proper way to include templates
		 */
		add_filter( 'template_include', [ $this, 'popup_layout' ] );
	}

	/**
	 * Search a film according to its name
	 *
	 * @param string $film_sanitized Film name sanitized
	 * @param string $type_search Array of search types: movies, series, games, etc.
	 * @return array<array-key, mixed>
	 * @phpstan-return TITLESEARCH_RETURNSEARCH
	 */
	private function find_result( string $film_sanitized, string $type_search ): array {

		$search = new TitleSearch( $this->plugins_classes_active['imdbphp'], $this->logger->log() );
		$return = $search->search( $film_sanitized, $type_search );
		/** @phpstan-var TITLESEARCH_RETURNSEARCH $return */
		return $return;
	}

	/**
	 * Display layout
	 *
	 * @param string $template_path The path to the page of the theme currently in use - not utilised
	 * @return string
	 */
	public function popup_layout( string $template_path ): string {

		// Nonce. Always valid if admin is connected.
		$nonce_valid = ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ) ) > 0 ) || is_user_logged_in() === true ? true : false; // Created in Abstract_Link_Maker class.

		// Validate $_GET['film'], exit if failed.
		$get_info = Validate_Get::sanitize_url( 'film' );
		if ( $get_info === null || ! isset( $_GET['norecursive'] ) || $_GET['norecursive'] !== 'yes' || $nonce_valid === false ) {
			wp_die( esc_html__( 'Lumière Movies: Invalid search request.', 'lumiere-movies' ) );
		}

		echo "<!DOCTYPE html>\n<html>\n<head>\n";
		wp_head();
		echo "\n</head>\n<body class=\"lum_body_popup_search lum_body_popup";
		echo isset( $this->imdb_admin_values['imdbpopuptheme'] ) ? ' lum_body_popup_' . esc_attr( $this->imdb_admin_values['imdbpopuptheme'] ) . '">' : '">';

		// Get an array of results according to a film name using IMDB class.
		$movie_results = $this->find_result(
			$this->film_sanitized, // Title was sanitized.
			$this->config_class->lumiere_select_type_search() // Get the type of search according to a method in config class.
		);

		/**
		 * Display a spinner when clicking a link with class .lum_add_spinner (a <div class="loader"> will be inserted inside by the js)
		 */
		echo '<div id="spinner-placeholder"></div>'; ?>

		<h1 align="center">
			<?php
			esc_html_e( 'Results related to', 'lumiere-movies' );
			echo ' <i>' . esc_html( ucwords( $this->film_sanitized ) ) . '</i>';
			?>
		</h1>

		<?php
		// if no movie was found at all.
		if ( count( $movie_results ) === 0 ) {
			echo "<h2 align='center'><i>" . esc_html__( 'No result found.', 'lumiere-movies' ) . '</i></h2>';
			wp_footer();
			?></body>
			</html><?php
			die();
		}
		?>

		<div class="lumiere_display_flex lumiere_align_center">
			<h2 class="lumiere_flex_auto lumiere_width_fifty_perc">
				<?php esc_html_e( 'Matching titles', 'lumiere-movies' ); ?>
			</h2>
			<h2 class="lumiere_flex_auto lumiere_width_fifty_perc">
				<?php echo esc_attr( _n( 'Director', 'Directors', 1, 'lumiere-movies' ) ) // always singular; ?>
			</h2>
		</div>

			<?php
			$max_lines = isset( $this->imdb_admin_values['imdbmaxresults'] ) ? intval( $this->imdb_admin_values['imdbmaxresults'] ) : 10;
			$current_line = 0;
			foreach ( $movie_results as $res ) {

				// Limit the number of results according to value set in admin
				$current_line++;
				if ( $current_line > $max_lines ) {
					echo '</div>';
					echo '<div align="center"><i>';
					echo esc_html__( 'Maximum number of results reached.', 'lumiere-movies' );
					if ( current_user_can( 'manage_options' ) ) {
						echo '&nbsp' . esc_html__( 'You can increase the limit of results in the admin options.', 'lumiere-movies' );
					}
					echo '</div>';
					wp_footer();
					echo '</i></body></html>';
					exit();
				}

				echo "\n<div class='lumiere_display_flex lumiere_align_center'>";

				// ---- movie part
				echo "\n\t<div class='lumiere_flex_auto lumiere_width_fifty_perc lumiere_align_left'>";

				$year = $res['titleSearchObject']->year() > 0 ? $res['titleSearchObject']->year() : __( 'year unknown', 'lumiere-movies' );
				echo "\n\t\t<a rel=\"nofollow\" class=\"lum_popup_internal_link lum_add_spinner\" href=\""
					. esc_url(
						wp_nonce_url(
							$this->config_class->lumiere_urlpopupsfilms . '?mid=' . esc_html( $res['titleSearchObject']->imdbid() )
							. '&film=' . Data::lumiere_name_htmlize( $res['titleSearchObject']->title() )
						)
					)
					. '" title="' . esc_html__( 'more on', 'lumiere-movies' ) . ' '
					. esc_html( $res['titleSearchObject']->title() ) . '" >'
					. esc_html( $res['titleSearchObject']->title() )
					. ' (' . esc_html( $year ) . ')' . "</a> \n";

				echo "\n\t</div>";

				// ---- director part
				echo "\n\t<div class='lumiere_flex_auto lumiere_width_fifty_perc lumiere_align_right'>";

				$realisateur = $res['titleSearchObject']->director();
				if ( isset( $realisateur['0']['name'] ) && strlen( $realisateur['0']['name'] ) > 0 ) {

					echo "\n\t\t<a rel=\"nofollow\" class=\"lum_popup_internal_link lum_add_spinner\" href=\""
						. esc_url(
							wp_nonce_url(
								$this->config_class->lumiere_urlpopupsperson
								. '?mid=' . esc_html( $realisateur['0']['imdb'] ?? '' )
							)
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

		</div><?php

		wp_meta();
		wp_footer();
		echo "</body>\n</html>";

		// Avoid 'Filter callback return statement is missing.' from PHPStan
		return '';
	}
}

