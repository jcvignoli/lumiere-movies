<?php declare( strict_types = 1 );
/**
 * Class for displaying upcoming movies
 *
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */

namespace Lumiere\Frontend;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Vendor\Imdb\Calendar;
use Lumiere\Frontend\Main;
use Lumiere\Tools\Files;
use Lumiere\Config\Get_Options;

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
	 * @param null|string $date_format_override Override the default WordPress date format
	 * @return void
	 */
	public static function init(
		string $region = 'US',
		string $type = 'MOVIE',
		int $start_date_override = 0,
		int $end_date_override = 0,
		?string $date_format_override = null
	): void {

		$that = new self();

		$that->maybe_load_assets();

		// Display the calendar.
		$that->display( $region, $type, $start_date_override, $end_date_override, $date_format_override );
	}

	/**
	 * Programmatically add the calendar stylesheet if it is not yet added
	 * Needed if WordPress 6.9 calls the class through filter lum_coming_soon
	 * It is included through in block.json (thus in inline script id "lumiere-coming-soon-style-inline-css")
	 *
	 * @return void
	 * @since 4.7.3 added
	 */
	private function maybe_load_assets(): void {

		// Execute in the first call of the filter only
		if ( did_filter( 'lum_coming_soon' ) !== 1 ) {
			return;
		}

		wp_register_style(
			'lumiere_style_calendar',
			Get_Options::LUM_CSS_URL . 'lum_calendar.min.css',
			[ 'lumiere_style_main' ],
			strval( filemtime( Get_Options::LUM_CSS_PATH . 'lum_calendar.min.css' ) )
		);

		wp_enqueue_style( 'lumiere_style_calendar' );
	}

	/**
	 * Display info from comingSoon() method in Calendar class
	 * Use template file to display data
	 *
	 * @param string $region Two-position country name like DE, NL, US
	 * @param string $type Type is returned, MOVIE, TV or TV_EPISODE
	 * @param int $start_date_override This defines the startDate override like +3 or -5 of default todays day
	 * @param int $end_date_override This defines the endDate override like +3 or -5, default + 1 year
	 * @param string|null $date_format_override Override the default WordPress date format
	 * @param Calendar $calendar_imdb_class Calendar class
	 * @return void
	 */
	private function display(
		string $region,
		string $type,
		int $start_date_override,
		int $end_date_override,
		?string $date_format_override,
		Calendar $calendar_imdb_class = new Calendar()
	): void {

		// Get Calendar's method.
		$date_format_override_comment = ! isset( $date_format_override ) ? 'null' : $date_format_override;
		$this->logger->log?->debug( '[Coming_Soon] Calling IMDB class ComingSoon with parameters => region:' . $region . ' type:' . $type . ' startDateOverride:' . (string) $start_date_override . ' endDateOverride:' . (string) $end_date_override . ', dateFormatOverride:' . $date_format_override_comment );
		$all_data = $calendar_imdb_class->comingSoon( $region, $type, $start_date_override, $end_date_override );
		$sorted_data = $this->array_sort_key( $all_data );
		$filtered_data = $this->convert_date( $sorted_data, $date_format_override );

		// Exit if no data found.
		if ( count( $filtered_data ) < 1 ) {
			$this->logger->log?->error( '[Coming_Soon] No data found' );
			echo '<div>' . esc_html__( 'No data found.', 'lumiere-movies' ) . '</div>';
			return;
		}

		// Get template.
		$this->logger->log?->debug( '[Coming_Soon] Displaying the template' );
		$this->include_with_vars( // In Trait Files.
			'calendar', // template name.
			[
				'lum_results'    => $filtered_data,
				'lum_link_maker' => $this->link_maker,
			],
		);
	}

	/**
	 * Sort the array from Calendar imdbgraphql class
	 *
	 * @param array<array-key, array<string, string>> $array Array as defined in @see(Calendar::buildDateString)
	 * @return array<array-key, array<string, string>>
	 * @since 4.7.2 added
	 */
	private function array_sort_key( array $array ): array {

		uksort(
			$array,
			fn( $a, $b ) => strtotime( $a ) <=> strtotime( $b )
		);
		return $array;
	}

	/**
	 * Convert date Key in array: 1/ apply WordPress date format to array key; 2/ Keep movies only from today and onwards (remove movies with old release date)
	 *
	 * @param array<array-key, array<string, string>> $array Array as defined in @see(Calendar::buildDateString)
	 * @param null|string $date_format_override Override the default WordPress date format
	 * @return array<array-key, array<string, string>>
	 * @since 4.7.3 added
	 */
	private function convert_date( array $array, ?string $date_format_override ): array {

		$new_array = [];
		$date_format = isset( $date_format_override ) && strlen( $date_format_override ) > 0 ? $date_format_override : get_option( 'date_format' );
		foreach ( $array as $key => $val ) {
			$date_int_movie = strtotime( $key );
			$today_int = current_time( 'timestamp' );

			// Remove movies with old release date
			if ( $date_int_movie >= $today_int && $date_int_movie !== false ) {
				// Convert array keys date to WordPress date
				$new_key = wp_date( $date_format, $date_int_movie );
				if ( $new_key !== false ) {
					$new_array[ $new_key ] = $val;
				}
			}
		}

		return $new_array;
	}
}
