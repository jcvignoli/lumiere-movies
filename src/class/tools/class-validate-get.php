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

/**
 * Validate and sanitize $_GET['XXXXX']
 * All methods must be static
 */
class Validate_Get {

	/**
	 * List of $_GET variable accepted in Lumière! url for popups
	 *
	 * 'filter' sanitizes
	 * 'flags' validates and returns null if the validation didn't work
	 */
	private const VALIDABLE_KEYS = [
		'film'         => [ // Lumiere\Frontend\Popups\Head_Popups, Lumiere\Frontend\Popups\Popup_Movie
			'filter' => FILTER_SANITIZE_SPECIAL_CHARS,
			'flags'  => [ FILTER_FLAG_QUERY_REQUIRED, FILTER_NULL_ON_FAILURE ],
		],
		'mid' => [  // Lumiere\Frontend\Popups\Head_Popups, Lumiere\Frontend\Popups\Popup_Movie
			'filter' => FILTER_SANITIZE_NUMBER_INT,
			'flags'  => [ FILTER_FLAG_QUERY_REQUIRED, FILTER_NULL_ON_FAILURE ],
		],
		'info'         => [ // Lumiere\Frontend\Popups\Popup_Movie
			'filter'  => FILTER_VALIDATE_REGEXP,
			'options' => [ 'regexp' => '~(actors|crew|resume|divers|^$)~' ], // Matches also empty string, which is needed for at first.
			'flags'   => [ FILTER_FLAG_QUERY_REQUIRED, FILTER_NULL_ON_FAILURE ],
		],
		'info_person'         => [ // Lumiere\Frontend\Popups\Popup_Person
			'filter'  => FILTER_VALIDATE_REGEXP,
			'options' => [ 'regexp' => '~(filmo|bio|misc|^$)~' ], // Matches also empty string, which is needed at first.
			'flags'   => [ FILTER_FLAG_QUERY_REQUIRED, FILTER_NULL_ON_FAILURE ],
		],
		'tag_lang'         => [ // Lumiere\Plugins\Auto\Polylang
			'filter' => FILTER_SANITIZE_SPECIAL_CHARS,
			'flags'  => [ FILTER_FLAG_QUERY_REQUIRED, FILTER_NULL_ON_FAILURE ],
		],
		'submit_lang'         => [ // Lumiere\Plugins\Auto\Polylang
			'filter' => FILTER_SANITIZE_SPECIAL_CHARS,
			'flags'  => [ FILTER_FLAG_QUERY_REQUIRED, FILTER_NULL_ON_FAILURE ],
		],
		'moviesearched'         => [ // Lumiere\Admin\Search
			'filter' => FILTER_SANITIZE_SPECIAL_CHARS,
			'flags'  => [ FILTER_FLAG_QUERY_REQUIRED, FILTER_NULL_ON_FAILURE ],
		],
	];

	/**
	 * Validate and sanitize Server url strings
	 * Use filter_input_array() to validate and sanitize
	 *
	 * @param string $url_key The Server key, as defined in VALIDABLE_KEYS
	 * @return null|string the value of the VALIDABLE_KEYS[ $url_key ]('path')?
	 *
	 * @throws Exception if the $url_key is not found in VALIDABLE_KEYS
	 */
	public static function sanitize_url( string $url_key ): ?string {

		if ( ! isset( self::VALIDABLE_KEYS[ $url_key ] ) ) {
			throw new Exception( __CLASS__ . ': This key "' . sanitize_key( $url_key ) . '" string does not exists' );
		}

		$sanitize_server = filter_input_array( INPUT_GET, self::VALIDABLE_KEYS, true );
		return isset( $sanitize_server[ $url_key ] ) && $sanitize_server[ $url_key ] !== false
			? esc_html( $sanitize_server[ $url_key ] )
			: null;
	}
}
