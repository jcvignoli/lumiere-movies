<?php declare( strict_types = 1 );
/**
 * Validator Gets URL in popups
 *
 * @copyright     2024, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */
namespace Lumiere\Tools;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) && ( ! class_exists( '\Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Exception;
use Lumiere\Config\Get_Options;

/**
 * Validate and sanitize $_GET['XXXXX']
 * All methods must be static
 *
 * @since 4.6 New method allowing the use of dynamic vars (using get_valid_list() instead of a constant)
 */
class Validate_Get {

	/**
	 * List of $_GET variable accepted in Lumière! url for popups
	 *
	 * 'filter' for sanitization
	 * 'flags' for extra instructions and returns null if the validation failed
	 * @return array<string, array<string, array<string, string>|int|null>>
	 */
	private static function get_valid_list(): array {
		return [
			'film'                                           => [ // Lumiere\Frontend\Popups\Head_Popups, Lumiere\Frontend\Popups\Popup_Movie
				'filter' => FILTER_SANITIZE_SPECIAL_CHARS,
				'flags'  => FILTER_FLAG_QUERY_REQUIRED | FILTER_NULL_ON_FAILURE,
			],
			'mid'                                            => [  // Lumiere\Frontend\Popups\Head_Popups, Lumiere\Frontend\Popups\Popup_Movie
				'filter' => FILTER_SANITIZE_NUMBER_INT,
				'flags'  => FILTER_FLAG_QUERY_REQUIRED | FILTER_NULL_ON_FAILURE,
			],
			'info'                                           => [ // Lumiere\Frontend\Popups\Popup_Movie
				'filter'  => FILTER_VALIDATE_REGEXP,
				'options' => [ 'regexp' => '~(actors|crew|resume|divers|^$)~' ], // Matches also empty string, which is needed for at first.
				'flags'   => FILTER_FLAG_QUERY_REQUIRED | FILTER_NULL_ON_FAILURE,
			],
			'info_person'                                    => [ // Lumiere\Frontend\Popups\Popup_Person
				'filter'  => FILTER_VALIDATE_REGEXP,
				'options' => [ 'regexp' => '~(filmo|bio|misc|^$)~' ], // Matches also empty string, which is needed at first.
				'flags'   => FILTER_FLAG_QUERY_REQUIRED | FILTER_NULL_ON_FAILURE,
			],
			'tag_lang'                                       => [ // Lumiere\Plugins\Auto\Polylang
				'filter' => FILTER_SANITIZE_SPECIAL_CHARS,
				'flags'  => FILTER_FLAG_QUERY_REQUIRED | FILTER_NULL_ON_FAILURE,
			],
			'submit_lang'                                    => [ // Lumiere\Plugins\Auto\Polylang
				'filter' => FILTER_SANITIZE_SPECIAL_CHARS,
				'flags'  => FILTER_FLAG_QUERY_REQUIRED | FILTER_NULL_ON_FAILURE,
			],
			Get_Options::LUM_SEARCH_ITEMS_QUERY_STRING => [ // var used in Lumiere\Admin\Search_Movie, ie 'moviesearched'
				'filter' => FILTER_SANITIZE_SPECIAL_CHARS,
				'flags'  => FILTER_SANITIZE_ADD_SLASHES | FILTER_FLAG_QUERY_REQUIRED | FILTER_NULL_ON_FAILURE,
			],
		];
	}

	/**
	 * Validate and sanitize server url strings ($_GET)
	 * Use filter_input_array() to validate and sanitize
	 *
	 * @param string $url_key The Server key, as defined in get_valid_list()
	 * @return null|string the url string validated and sanitized
	 *
	 * @throws Exception if the $url_key is not found in get_valid_list()
	 */
	public static function sanitize_url( string $url_key ): ?string {

		$valid_list = self::get_valid_list();
		if ( ! isset( $valid_list[ $url_key ] ) ) {
			throw new Exception( 'Lumière ' . __CLASS__ . ': This key "' . sanitize_key( $url_key ) . '" string does not exists' );
		}

		$filtered_url_key = filter_input_array( INPUT_GET, $valid_list, true );

		return isset( $filtered_url_key[ $url_key ] ) && is_string( $filtered_url_key[ $url_key ] )
			? preg_replace( '/[^A-Za-z0-9\s.\s-]/', '', $filtered_url_key[ $url_key ] )
			: null;
	}
}
