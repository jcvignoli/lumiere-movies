<?php declare( strict_types = 1 );
/**
 * Popup for movie search: Independant page that displays movie search inside a popup
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

class Popup_Search {

	// Use trait frontend
	use \Lumiere\Frontend {
		Frontend::__construct as public __constructFrontend;
	}

	/**
	 * Settings from class \Lumiere\Settings
	 * To include the type of (movie, TVshow, Games) search
	 * @var array<string> $type_search
	 */
	private array $type_search;

	/**
	 * Movie's title
	 */
	private ?string $film_sanitized;

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
	 */
	public function __construct() {

		// Construct Frontend trait.
		$this->__constructFrontend( 'popupSearch' );

		// Get the type of search: movies, series, games.
		$this->type_search = $this->config_class->lumiere_select_type_search();

		// Build the vars.
		$this->film_sanitized = Utils::lumiere_name_htmlize( $_GET['film'] );
		$this->film_sanitized_for_title = esc_html( $_GET['film'] );

		// Display layout
		add_action( 'wp', [ $this, 'layout' ] );
		// When set on get_header hook, the popup is fully included in WP environement
		#add_action( 'get_header', [ $this, 'layout' ], 1 );

	}

	/**
	 *  Display layout
	 *
	 */
	private function film_search(): void {

		do_action( 'lumiere_logger' );

		// Die if wrong gets.
		if ( ( ! isset( $_GET['norecursive'] ) ) || ( $_GET['norecursive'] !== 'yes' ) || ( ! isset( $_GET['film'] ) ) || ( strlen( $_GET['film'] ) === 0 ) || $this->film_sanitized === null ) {

			wp_die( esc_html__( 'Lumière Movies: Invalid search request.', 'lumiere-movies' ) );

		}

		# Run the query.
		$search = new TitleSearch( $this->imdbphp_class, $this->logger->log() );

		$this->movie_results = $search->search( $this->film_sanitized, $this->type_search );

	}

	/**
	 *  Display layout
	 *
	 */
	public function layout(): void {
		?><!DOCTYPE html>
<html>
<head>
		<?php wp_head(); ?>
</head>
		<body class="lumiere_body<?php
		if ( isset( $this->imdb_admin_values['imdbpopuptheme'] ) ) {
			echo ' lumiere_body_' . esc_attr( $this->imdb_admin_values['imdbpopuptheme'] );
		}
		?>">
		<?php
		// Do the film query.
		$this->film_search();
		?>
		<div id="lumiere_loader" class="lumiere_loader_center"></div>

		<h1 align="center">
			<?php
			esc_html_e( 'Results related to', 'lumiere-movies' );
			echo ' <i>' . esc_html( $this->film_sanitized_for_title ) . '</i>';
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
				<?php esc_html_e( 'Director', 'lumiere-movies' ); ?>
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
					. Utils::lumiere_name_htmlize( $res->title() )
					. '/?mid=' . esc_html( $res->imdbid() )
				)
					. '&film=' . Utils::lumiere_name_htmlize( $res->title() )
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
		<?php wp_footer(); ?>
		</body>
		</html>
		<?php
		exit(); // quit the call of the page, to avoid double loading process

	}

}

new Popup_Search();

