<?php declare( strict_types = 1 );
/**
 * Class for displaying coming soon data.
 *
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */

namespace Lumiere\Frontend\Calendar;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Vendor\Imdb\Calendar;
use Lumiere\Frontend\Main;
use Lumiere\Tools\Files;

/**
 * Coming soon
 *
 * @since 4.7 new class
 */
final class Coming_Soon {

	/**
	 * Traits
	 */
	use Main, Files;

	/**
	 * Constructor
	 *
	 * @param string $region Two-position country name like DE, NL, US
	 * @param string $type Type is returned, MOVIE, TV or TV_EPISODE
	 * @param int $start_date_override This defines the startDate override like +3 or -5 of default todays day
	 * @param int $end_date_override This defines the endDate override like +3 or -5, default + 1 year
	 */
	public function __construct( string $region, string $type, int $start_date_override, int $end_date_override ) {
		$this->start_main_trait(); // In Trait Main.
		$this->start_linkmaker(); // In Trait Main.
		$this->display( $region, $type, $start_date_override, $end_date_override );
	}

	/**
	 * Called in add_filter()
	 *
	 * @param string $region Two-position country name like DE, NL, US
	 * @param string $type Type is returned, MOVIE, TV or TV_EPISODE
	 * @param int $start_date_override This defines the startDate override like +3 or -5 of default todays day
	 * @param int $end_date_override This defines the endDate override like +3 or -5, default + 1 year
	 */
	public static function init( string $region = 'US', string $type = 'TV', int $start_date_override = 0, int $end_date_override = 0 ): void {

		$that = new self( $region, $type, $start_date_override, $end_date_override );

		// Registered in Frontend, but enqueued only here.
		wp_enqueue_style( 'lum_calendar' );
	}

	/**
	 * Display info from comingSoon() method in Calendar class
	 * Use template file to display data
	 *
	 * @param string $region Two-position country name like DE, NL, US
	 * @param string $type Type is returned, MOVIE, TV or TV_EPISODE
	 * @param int $start_date_override This defines the startDate override like +3 or -5 of default todays day
	 * @param int $end_date_override This defines the endDate override like +3 or -5, default + 1 year
	 * @parameter $calendar_class Calendar class
	 */
	public function display( string $region, string $type, int $start_date_override, int $end_date_override, Calendar $calendar_class = new Calendar() ): void {
		$all_data = $calendar_class->comingSoon( $region, $type, $start_date_override, $end_date_override );
		$this->include_with_vars( // In Trait Files.
			'calendar', // template name.
			[ $all_data, $this->link_maker ], // data passed.
			'calendar_vars' // transient name.
		);
	}
}
