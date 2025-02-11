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
if ( ! defined( 'WPINC' ) || ! class_exists( 'Lumiere\Config\Settings' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Frontend\Popups\Head_Popups;
use Lumiere\Frontend\Popups\Popup_Basic;
use Lumiere\Tools\Data;
use Lumiere\Tools\Validate_Get;
use Lumiere\Config\Get_Options;

/**
 * Displays movie search results in a popup
 * Bots are banned before getting popups
 *
 * @see \Lumiere\Popups\Popup_Select Redirect to here according to the query var 'popup' in URL
 * @see \Lumiere\Frontend\Popups\Head_Popups Modify the popup header, Parent class, Bot banishement
 * @since 4.3 is child class
 * @phpstan-import-type TITLESEARCH_RETURNSEARCH from \Lumiere\Plugins\Manual\Imdbphp
 */
class Popup_Movie_Search extends Head_Popups implements Popup_Basic {

	/**
	 * Movie's title
	 */
	private string $page_title;

	/**
	 * Constructor
	 */
	public function __construct() {

		// Edit metas tags in popups and various checks in Parent class.
		parent::__construct();

		/**
		 * Build the properties.
		 */
		$this->page_title = $this->get_title( Validate_Get::sanitize_url( 'film' ) );

		/**
		 * Display title
		 * @since 4.3
		 */
		add_filter( 'document_title_parts', [ $this, 'edit_title' ] );
	}

	/**
	 * Edit the title of the page
	 *
	 * @param array<string, string> $title
	 * @phpstan-param array{title: string, page: string, tagline: string, site: string} $title
	 * @phpstan-return array{title: string, page: string, tagline: string, site: string}
	 */
	public function edit_title( array $title ): array {

		// Set the title.
		$filmname_complete = ' : [ ' . ucwords( $this->page_title ) . ' ]';

		/* translators: %1s is the title of a movie */
		$new_title = wp_sprintf( __( 'Lumiere Query Interface %1s', 'lumiere-movies' ), $filmname_complete );

		$title['title'] = $new_title;

		return $title;
	}

	/**
	 * Get the title of the page
	 *
	 * @param string|null $title Movie's name
	 * @return string
	 * @since 4.0 lowercase, less cache used.
	 */
	public function get_title( ?string $title ): string {
		return isset( $title ) ? str_replace( [ '\\', '+' ], [ '', ' ' ], strtolower( Data::lumiere_name_htmlize( $title ) ) ) : '';
	}

	/**
	 * Search a film according to its name
	 *
	 * @param string $title_name Film name sanitized
	 * @return array<array-key, mixed>
	 * @phpstan-return TITLESEARCH_RETURNSEARCH
	 */
	private function get_result( string $title_name ): array {

		$this->logger->log->debug( '[Popup_Movie_Search] Movie title name provided in URL: ' . esc_html( $title_name ) );

		return $this->plugins_classes_active['imdbphp']->search_movie_title(
			esc_html( $title_name ),
			$this->logger->log,
		);
	}

	/**
	 * Display layout
	 *
	 * @return void
	 */
	public function get_layout(): void {

		echo "<!DOCTYPE html>\n<html>\n<head>\n";
		wp_head();
		echo "\n</head>\n<body class=\"lum_body_popup_search lum_body_popup";
		echo isset( $this->imdb_admin_values['imdbpopuptheme'] ) ? ' lum_body_popup_' . esc_attr( $this->imdb_admin_values['imdbpopuptheme'] ) . '">' : '">';

		// Get an array of results according to a film name using IMDB class.
		$movie_results = $this->get_result( $this->page_title );

		/**
		 * Display a spinner when clicking a link with class .lum_add_spinner (a <div class="loader"> will be inserted inside by the js)
		 */
		echo '<div id="spinner-placeholder"></div>'; ?>

		<h1 align="center">
			<?php
			esc_html_e( 'Results related to', 'lumiere-movies' );
			echo ' <i>' . esc_html( ucwords( $this->page_title ) ) . '</i>';
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
							Get_Options::get_popup_url( 'film', site_url() ) . '?mid=' . esc_html( $res['titleSearchObject']->imdbid() )
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
								Get_Options::get_popup_url( 'person', site_url() )
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
	}
}

