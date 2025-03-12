<?php declare( strict_types = 1 );
/**
 * Class for displaying Link_Maker layout.
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */

namespace Lumiere\Frontend\Layout;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Frontend\Layout\Output;

/**
 * Layouts for Link_Maker system
 *
 * @see \Lumiere\Frontend\Link_Maker\Implement_Link_Maker calling class
 * @phpstan-import-type OPTIONS_ADMIN from \Lumiere\Config\Settings
 * @since 4.5
 */
class Output_Linkmaker extends Output {

	/**
	 * @see Output_Linkmaker::bootstrap_convert_modal_size()
	 */
	private const MODAL_STANDARD_WITH = [
		300 => 'modal-sm',
		500 => '',
		800 => 'modal-lg',
		1140 => 'modal-xl',
	];

	/**
	 * Display Linkmaker layouts
	 *
	 * @param string $selector Select which column to return
	 * @param string $text_one Optional, an extra text to use
	 * @param string $text_two Optional, an extra text to use
	 * @param string $text_three Optional, an extra text to use
	 * @return string
	 */
	public function main_layout( string $selector, string $text_one = '', string $text_two = '', string $text_three = '' ): string {
		$container = [
			'item_picture'            => "\n\t\t\t<div class=\"imdbelementPIC\">" . $text_one . "\n\t\t\t</div>",
		];
		return $container[ $selector ];
	}

	/**
	 * Build bootstrap HTML part
	 * This HTML code enable to display bootstrap modal window
	 * Using spans instead of divs to not break the regex replace in content (WP adds extra <p> when divs are used)
	 *
	 * @param string $imdb_id Id of the IMDB person/movie
	 * @param string $imdb_data Name/title of the IMDB person/movie
	 * @param non-empty-array<string, string> $imdb_admin_values
	 * @phpstan-param OPTIONS_ADMIN $imdb_admin_values
	 *
	 * @since 4.0.1 Added spinner and dialog size
	 * @return string
	 */
	public function bootstrap_modal( string $imdb_id, string $imdb_data, array $imdb_admin_values ): string {
		return "\n\t\t\t\t\t" . '<span class="modal fade" id="theModal' . $imdb_id . '">'
			. "\n\t\t\t\t\t\t" . '<span id="bootstrap' . $imdb_id . '" class="modal-dialog modal-dialog-centered' . $this->bootstrap_convert_modal_size( $imdb_admin_values ) . '" role="dialog">'
			. "\n\t\t\t\t\t\t\t" . '<span class="modal-content">'
			. "\n\t\t\t\t\t\t\t\t" . '<span class="modal-header bootstrap_black">'
			. "\n\t\t\t\t\t\t\t\t\t" . '<span id="lumiere_bootstrap_spinner_id" role="status" class="spinner-border">'
			. "\n\t\t\t\t\t\t\t\t\t\t" . '<span class="sr-only"></span>'
			. "\n\t\t\t\t\t\t\t\t\t" . '</span>'
			/**
			 * Deactivated: Title's popup doesn't change when navigating
			 * . esc_html__( 'Information about', 'lumiere-movies' ) . ' ' . esc_html( ucfirst( $imdb_data ) )
			 */
			. "\n\t\t\t\t\t\t\t\t\t" . '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" data-target="theModal' . $imdb_id . '"></button>'
			. "\n\t\t\t\t\t\t\t\t" . '</span>'
			. "\n\t\t\t\t\t\t\t\t" . '<span class="modal-body embed-responsive embed-responsive-16by9"></span>'
			. "\n\t\t\t\t\t\t\t" . '</span>'
			. "\n\t\t\t\t\t\t" . '</span>'
			. "\n\t\t\t\t\t" . '</span>';
	}

	/**
	 * Get popup width and convert it to an incremental bootstrap size
	 *
	 * @param non-empty-array<string, string> $imdb_admin_values
	 * @phpstan-param OPTIONS_ADMIN $imdb_admin_values
	 * @return string
	 *
	 * @since 4.0.1
	 */
	private function bootstrap_convert_modal_size( array $imdb_admin_values ): string {
		$modal_size_name = '';
		foreach ( self::MODAL_STANDARD_WITH as $size_width => $size_name ) {
			if ( $imdb_admin_values['imdbpopuplarg'] >= $size_width ) {
				$modal_size_name = ' ' . $size_name;
			}
		}
		return $modal_size_name;
	}
}
