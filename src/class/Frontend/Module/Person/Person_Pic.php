<?php declare( strict_types = 1 );
/**
 * Class for displaying movies module Pic.
 *
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */

namespace Lumiere\Frontend\Module\Person;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Config\Get_Options;

/**
 * Method to display pic for persons
 *
 * @since 4.5 new class
 */
class Person_Pic extends \Lumiere\Frontend\Module\Parent_Module {

	/**
	 * Display the title and possibly the year
	 *
	 * @param \Lumiere\Vendor\Imdb\Name $person IMDbPHP title class
	 * @param 'pic' $item_name The name of the item
	 */
	public function get_module( \Lumiere\Vendor\Imdb\Name $person, string $item_name ): string {

		$person_name = $person->name() ?? '';

		// If cache is active, use the pictures from IMDBphp class.
		if ( $this->imdb_cache_values['imdbusecache'] === '1' ) {
			return $this->link_maker->get_picture( $person->photoLocalurl( false ), $person->photoLocalurl( true ), $person_name );
		}

		// If cache is deactivated, display no_pics.gif
		$no_pic_url = Get_Options::LUM_PICS_URL . 'no_pics.gif';
		return $this->link_maker->get_picture( $no_pic_url, $no_pic_url, $person_name );
	}
}
