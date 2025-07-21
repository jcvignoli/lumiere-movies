<?php declare( strict_types = 1 );
/**
 * Class for displaying upcoming movies
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
 * Display the upcoming movies
 * Used in add_filter() with 'lum_coming_soon' hook
 * Used in coming-soon block
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
	 * Frontend Style is called in block.json
	 */
	public function __construct() {
		$this->start_main_trait(); // In Trait Main.
		$this->start_linkmaker(); // In Trait Main.
	}

	/**
	 * Static start
	 *
	 * @param string $region Two-position country name like DE, NL, US
	 * @param string $type Type is returned, MOVIE, TV or TV_EPISODE
	 * @param int $start_date_override This defines the startDate override like +3 or -5 of default todays day
	 * @param int $end_date_override This defines the endDate override like +3 or -5, default + 1 year
	 * @return void
	 */
	public static function init(
		string $region = 'US',
		string $type = 'MOVIE',
		int $start_date_override = 0,
		int $end_date_override = 0
	): void {
		$that = new self();

		// Display the calendar.
		$that->display( $region, $type, $start_date_override, $end_date_override );
	}

	/**
	 * Display info from comingSoon() method in Calendar class
	 * Use template file to display data
	 *
	 * @param string $region Two-position country name like DE, NL, US
	 * @param string $type Type is returned, MOVIE, TV or TV_EPISODE
	 * @param int $start_date_override This defines the startDate override like +3 or -5 of default todays day
	 * @param int $end_date_override This defines the endDate override like +3 or -5, default + 1 year
	 * @param Calendar $calendar_class Calendar class
	 * @return void
	 */
	private function display(
		string $region,
		string $type,
		int $start_date_override,
		int $end_date_override,
		Calendar $calendar_class = new Calendar()
	): void {

		// Get Calendar's method.
		$this->logger->log?->debug( '[Coming_Soon] Calling IMDB class ComingSoon with parameters => region:' . $region . ' type:' . $type . ' startDateOverride:' . (string) $start_date_override . ' endDateOverride:' . (string) $end_date_override );
		$all_data = $calendar_class->comingSoon( $region, $type, $start_date_override, $end_date_override );

		// Get template.
		$this->logger->log?->debug( '[Coming_Soon] Displaying the template' );
		$this->include_with_vars( // In Trait Files.
			'calendar', // template name.
			[ $all_data, $this->link_maker ], // data passed.
			'calendar_vars' // transient name.
		);
	}
}
