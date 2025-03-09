<?php declare( strict_types = 1 );
/**
 * Parent class for all modules
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */

namespace Lumiere\Frontend\Module;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Frontend\Layout\Output;
use Lumiere\Frontend\Main;

/**
 * Simplify coding, using most usefull classes
 * @see \Lumiere\Frontend\Movie\Movie_Taxonomy extra class is only used in modules that need it
 *
 * @since 4.4.3 new class
 */
class Parent_Module {

	/**
	 * Traits
	 */
	use Main;

	/**
	 * Constructor
	 */
	public function __construct(
		protected Output $output_class = new Output(),
	) {
		/**
		 * Get the properties and the linkmakers.
		 */
		$this->start_main_trait(); // In Trait Main.
	}
}
