<?php declare( strict_types = 1 );
/**
 * Factory class for selecting the link maker
 *
 * @copyright (c) 2022, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */

namespace Lumiere\Frontend\Link_Maker;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Frontend\Main;
use Exception;

/**
 * The class select the "link makers" according to the current settings and plugins used
 * Some "link makers" build links that can open popups (Highslide, Classic) or remove HTML links (No_Links, AMP)
 *
 * @phpstan-type LINKMAKERCLASSES \Lumiere\Frontend\Link_Maker\AMP_Links|\Lumiere\Frontend\Link_Maker\Bootstrap_Links|\Lumiere\Frontend\Link_Maker\Classic_Links|\Lumiere\Frontend\Link_Maker\Highslide_Links|\Lumiere\Frontend\Link_Maker\No_Links
 * @phpstan-import-type OPTIONS_ADMIN from \Lumiere\Config\Settings
 * @since 3.8
 */
final class Link_Factory {

	/**
	 * Traits
	 */
	use Main;

	/**
	 * Class for building links, i.e. Highslide
	 * Built in class Link Factory
	 *
	 * @var Interface_Linkmaker $link_maker The factory class will determine which class to use
	 */
	public Interface_Linkmaker $link_maker;

	/**
	 * Select which class to use to build the HTML links.
	 * @param array<string, string> $imdb_admin_values
	 * @phpstan-param OPTIONS_ADMIN $imdb_admin_values
	 * @phpstan-return LINKMAKERCLASSES Class to build the links with.
	 *
	 * @see \Lumiere\Frontend\Main::is_amp_page() Detects if AMP is active and current
	 * @throws Exception if no link class was found
	 */
	public function select_link_maker( $imdb_admin_values ): Interface_Linkmaker {

		// Checks if the current page is AMP
		if ( $this->is_amp_page() === true ) { // Method in Main trait.
			return new AMP_Links();

			// No display Lumière! links is selected in admin options
		} elseif ( $imdb_admin_values['imdblinkingkill'] === '1' ) {
			return new No_Links();

			// Bootstrap is selected in admin options
		} elseif ( $imdb_admin_values['imdbpopup_modal_window'] === 'bootstrap' ) {
			return new Bootstrap_Links();

			// Highslide is selected in admin options
		} elseif ( $imdb_admin_values['imdbpopup_modal_window'] === 'highslide' ) {
			return new Highslide_Links();

			// Classic is selected in admin options
		} elseif ( $imdb_admin_values['imdbpopup_modal_window'] === 'classic' ) {
			return new Classic_Links();
		}

		throw new Exception( 'No Link Lumière class found, aborting!' );
	}

	/**
	 * Static call of the current class
	 * @param array<string, string> $imdb_admin_values
	 * @phpstan-param OPTIONS_ADMIN $imdb_admin_values
	 *
	 * @phpstan-return LINKMAKERCLASSES Instance the relevant class
	 */
	public static function select_link_type( $imdb_admin_values ): Interface_Linkmaker {
		return ( new self() )->select_link_maker( $imdb_admin_values );
	}
}
