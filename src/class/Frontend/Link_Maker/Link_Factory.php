<?php declare( strict_types = 1 );
/**
 * Factory class for selecting the link maker
 *
 * @copyright (c) 2022, Lost Highway
 *
 * @version       1.1
 * @package       lumieremovies
 */

namespace Lumiere\Frontend\Link_Maker;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Frontend\Main;
use Lumiere\Config\Settings_Service;
use Lumiere\Enums\Modal_Type;

/**
 * The class select the "link makers" according to the current settings and plugins used
 * Some "link makers" build links that can open popups (Highslide, Classic) or remove HTML links (No_Links, AMP)
 *
 * @since 3.8
 * @since 4.8 removed static method select_link_type(), not started in Main trait anymore
 *
 * @phpstan-type LINKMAKERCLASSES \Lumiere\Frontend\Link_Maker\AMP_Links|\Lumiere\Frontend\Link_Maker\Bootstrap_Links|\Lumiere\Frontend\Link_Maker\Classic_Links|\Lumiere\Frontend\Link_Maker\Highslide_Links|\Lumiere\Frontend\Link_Maker\No_Links
 */
final class Link_Factory {

	/**
	 * Traits
	 */
	use Main;

	/**
	 * Constructor.
	 *
	 * @param Settings_Service $settings
	 */
	public function __construct(
		private readonly Settings_Service $settings
	) {}

	/**
	 * Select which class to use to build the HTML links.
	 *
	 * @phpstan-return LINKMAKERCLASSES Class to build the links with.
	 *
	 * @see \Lumiere\Frontend\Main::is_amp_page() Detects if AMP is active and current
	 * @throws \ValueError If no link class was found or the configured modal type is invalid.
	 */
	public function select_link_maker(): Interface_Linkmaker {

		$link_maker = null;

		// Checks if the current page is AMP
		if ( $this->is_amp_page() === true ) { // Method in Main trait.
			$link_maker = new AMP_Links();

			// No display Lumière! links is selected in admin options
		} elseif ( $this->settings->get_admin_option( 'imdblinkingkill' ) === '1' ) {
			$link_maker = new No_Links();

		} else {
			$modal_type = Modal_Type::from_string( $this->settings->get_admin_option( 'imdbpopup_modal_window' ) );

			$link_maker = match ( $modal_type ) {
				Modal_Type::BOOTSTRAP => new Bootstrap_Links(),
				Modal_Type::HIGHSLIDE => new Highslide_Links(),
				Modal_Type::CLASSIC   => new Classic_Links(),
				Modal_Type::NO_LINKS  => new No_Links(),
				Modal_Type::AMP       => new AMP_Links(),
			};
		}

		$link_maker->register_hooks();

		return $link_maker;
	}
}
