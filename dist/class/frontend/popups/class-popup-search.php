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

/**
 * Independant class that displays movie search results in a popup
 * @see \Lumiere\Alteration\Rewrite_Rules that creates rules for creating a virtual page
 * @see \Lumiere\Alteration\Redirect_Virtual_Page that redirects to this page
 */
class Popup_Search {

	/**
	 * Traits
	 */
	use Main;

	/**
	 * Settings from class \Lumiere\Settings
	 * To include the type of (movie, TVshow, Games) search
	 * @var array<string> $type_search
	 */
	private array $type_search;

	/**
	 * Movie's title
	 */
	private string $film_sanitized;

	/**
	 * Movie's title sanitized
	 */
	private string $film_sanitized_for_title;

	/**
	 * The movie queried
	 * @var array<\Imdb\Title> $movie_results
	 */
	private array $movie_results;

	/**
	 * Constructor
	 *
	 * @since 4.0.1 Bot banishment using action 'lumiere_ban_bots_now' in Redirect_Virtual_Page
	 * @see \Lumiere\Alteration\Redirect_Virtual_Page::lumiere_popup_redirect_include() Bot banishement happens in Redirect_Virtual_Page::ban_bots_popups()
	 * @see \Lumiere\Tools\Ban_Bots::_construct() The action 'lumiere_ban_bots_now' is cacled in Redirect_Virtual_Page
	 */
	public function __construct() {

		// Die if wrong $_GETs.
		if (
			! isset( $_GET['norecursive'] )
			|| $_GET['norecursive'] !== 'yes'
			|| ! isset( $_GET['film'] )
			|| strlen( $_GET['film'] ) === 0
		) {
			wp_die( esc_html__( 'Lumière Movies: Invalid search request.', 'lumiere-movies' ) );
		}

		// Edit metas tags in popups.
		add_action( 'template_redirect', fn() => Head_Popups::lumiere_static_start() );

		// Construct Frontend trait.
		$this->start_main_trait();

		// Get the type of search: movies, series, games.
		$this->type_search = $this->config_class->lumiere_select_type_search();

		// Build the vars.
		// @since 4.0 lowercase, less cache used.
		$this->film_sanitized = isset( $_GET['film'] ) ? str_replace( [ '\\', '+' ], [ '', ' ' ], strtolower( $this->lumiere_name_htmlize( $_GET['film'] ) ) ) : ''; // In trait Data, which is in trait Main.
		$this->film_sanitized_for_title = $this->film_sanitized;

		/**
		 * Start Plugins_Start class
		 * Is instanciated only if not instanciated already
		 * Use lumiere_set_plugins_array() in trait to set $plugins_active_names var in trait
		 */
		if ( count( $this->plugins_active_names ) === 0 ) {
			$this->activate_plugins();
		}

		// Remove admin bar if user is logged in.
		if ( is_user_logged_in() === true ) {
			add_filter( 'show_admin_bar', '__return_false' );
			wp_deregister_style( 'admin-bar' );
		}

		/**
		 * Display layout
		 * @since 4.0 using 'the_posts', removed the 'get_header' for OceanWP
		 */
		add_action( 'the_posts', [ $this, 'layout' ] );

	}

	/**
	 *  Display layout
	 */
	private function film_search(): void {

		// Run the query.
		$search = new TitleSearch( $this->plugins_classes_active['imdbphp'], $this->logger->log() );

		$this->movie_results = $search->search( esc_html( $this->film_sanitized ), $this->type_search );

	}

	/**
	 *  Display layout
	 *
	 */
	public function layout(): void {

		?> class="lum_body_popup_search lum_body_popup<?php

		echo isset( $this->imdb_admin_values['imdbpopuptheme'] ) ? ' lum_body_popup_' . esc_attr( $this->imdb_admin_values['imdbpopuptheme'] ) . '">' : '">';

		// Do the film query.
		$this->film_search();

		/**
		 * Display a spinner when clicking a link with class .lum_add_spinner (a <div class="loader"> will be inserted inside by the js)
		 */
		echo '<div id="spinner-placeholder"></div>';
?>
		 
		<h1 align="center">
			<?php
			esc_html_e( 'Results related to', 'lumiere-movies' );
			echo ' <i>' . esc_html( ucwords( $this->film_sanitized_for_title ) ) . '</i>';
			?>
		</h1>

		<?php
		// if no movie was found at all.
		if ( count( $this->movie_results ) === 0 ) {
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
			foreach ( $this->movie_results as $res ) {

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

				echo "\n\t\t<a rel=\"nofollow\" class=\"lum_popup_internal_link lum_add_spinner\" href=\"" . esc_url(
					$this->config_class->lumiere_urlpopupsfilms
					. '?mid=' . esc_html( $res->imdbid() )
				)
					. '&film=' . esc_url( $this->lumiere_name_htmlize( $res->title() ) ) // Method in trait Data, which is in trait Main.
					. '" title="' . esc_html__( 'more on', 'lumiere-movies' ) . ' '
					. esc_html( $res->title() ) . '" >'
					. esc_html( $res->title() )
					. ' (' . intval( $res->year() ) . ')' . "</a> \n";

				echo "\n\t</div>";

				// ---- director part
				echo "\n\t<div class='lumiere_flex_auto lumiere_width_fifty_perc lumiere_align_right'>";

				$realisateur = $res->director();
				if ( isset( $realisateur['0']['name'] ) && strlen( $realisateur['0']['name'] ) > 0 ) {

					echo "\n\t\t<a rel=\"nofollow\" class=\"lum_popup_internal_link lum_add_spinner\" href=\""
						. esc_url(
							$this->config_class->lumiere_urlpopupsperson
							. '?mid=' . esc_html( $realisateur['0']['imdb'] ?? '' )
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
		<?php wp_footer(); ?>
		</body>
		</html>
		<?php
		exit(); // quit to avoid double loading process
	}
}

