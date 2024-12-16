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
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	wp_die( esc_html__( 'Lumière Movies: You can not call directly this page', 'lumiere-movies' ) );
}

use Imdb\TitleSearch;
use Lumiere\Frontend\Popups\Head_Popups;
use Lumiere\Frontend\Main;
use Lumiere\Tools\Validate_Get;

/**
 * Independant class that displays movie search results in a popup
 *
 * @see \Lumiere\Alteration\Rewrite_Rules Create the rules for building a virtual page
 * @see \Lumiere\Frontend\Frontend Redirect to this page using virtual pages {@link \Lumiere\Alteration\Virtual_Page}
 * @see \Lumiere\Frontend\Popups\Head_Popups Modify the popup header
 *
 * Bots are banned before getting popups
 * @see \Lumiere\Frontend\Frontend::ban_bots_popups() Bot banishement happens there, before processing IMDb queries
 */
class Popup_Search {

	/**
	 * Traits
	 */
	use Main;

	/**
	 * Movie's title
	 */
	private string $film_sanitized;

	/**
	 * Constructor
	 */
	public function __construct() {

		// Die if wrong $_GETs.
		if (
			! isset( $_GET['_wpnonce'] ) || ! ( wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ) ) > 0 )
			|| ! isset( $_GET['norecursive'] )
			|| $_GET['norecursive'] !== 'yes'
			|| ! isset( $_GET['film'] )
			|| strlen( sanitize_key( $_GET['film'] ) ) === 0
		) {
			wp_die( esc_html__( 'Lumière Movies: Invalid search request.', 'lumiere-movies' ) );
		}

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

		// Build the vars.
		// @since 4.0 lowercase, less cache used.
		$film_sanitized = Validate_Get::sanitize_url( 'film' );
		$this->film_sanitized = $film_sanitized !== null ? str_replace( [ '\\', '+' ], [ '', ' ' ], strtolower( $this->lumiere_name_htmlize( $film_sanitized ) ) ) : ''; // Method lumiere_name_htmlize() is in trait Data, which is in trait Main.

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
		add_filter( 'template_include', [ $this, 'layout' ] );
	}

	/**
	 * Search a film according to its name
	 *
	 * @param string $film_sanitized Film name sanitized
	 * @param array<string> $type_search Array of search types: movies, series, games, etc.
	 * @return array<\Imdb\Title> An array of IMDB Title results
	 */
	private function film_search( string $film_sanitized, array $type_search ): array {
		$search = new TitleSearch( $this->plugins_classes_active['imdbphp'], $this->logger->log() );
		return $search->search( $film_sanitized, $type_search );
	}

	/**
	 * Display layout
	 *
	 * @param string $template_path The path to the page of the theme currently in use - not utilised
	 * @return string
	 */
	public function layout( string $template_path ): string {

		echo "<!DOCTYPE html>\n<html>\n<head>\n";
		wp_head();
		echo "\n</head>\n<body class=\"lum_body_popup_search lum_body_popup";

		echo isset( $this->imdb_admin_values['imdbpopuptheme'] ) ? ' lum_body_popup_' . esc_attr( $this->imdb_admin_values['imdbpopuptheme'] ) . '">' : '">';

		// Get an array of results according to a film name using IMDB class.
		$movie_results = $this->film_search(
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

				$year = $res->year() > 0 ? $res->year() : __( 'year unknown', 'lumiere-movies' );
				echo "\n\t\t<a rel=\"nofollow\" class=\"lum_popup_internal_link lum_add_spinner\" href=\""
					. esc_url(
						wp_nonce_url(
							$this->config_class->lumiere_urlpopupsfilms . '?mid=' . esc_html( $res->imdbid() )
							. '&film=' . $this->lumiere_name_htmlize( $res->title() ) // Method in trait Data, which is in trait Main.
						)
					)
					. '" title="' . esc_html__( 'more on', 'lumiere-movies' ) . ' '
					. esc_html( $res->title() ) . '" >'
					. esc_html( $res->title() )
					. ' (' . esc_html( $year ) . ')' . "</a> \n";

				echo "\n\t</div>";

				// ---- director part
				echo "\n\t<div class='lumiere_flex_auto lumiere_width_fifty_perc lumiere_align_right'>";

				$realisateur = $res->director();
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

