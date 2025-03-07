<?php declare( strict_types = 1 );
/**
 * Settings for Popups
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */
namespace Lumiere\Config;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) { // Don't check for Settings class since it's Settings class.
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

/**
 * Settings class for Popups
  */
class Settings_Popup {

	/**
	 * The selection to display on FULL filmo page in Popup_Person
	 * The order of the list will be the order displayed in the Popup
	 * @see \IMDb\Name::credit() must match the list
	 * @var list<string>
	 */
	public const PERSON_ALL_ROLES  = [
		'director',
		'actor',
		'actress',
		'assistantDirector',
		'showrunner',
		'writer',
		'cinematographer',
		'producer',
		'editor',
		'self',
		'soundtrack',
		'artDepartment',
		'stunts',
		'costume_department',
		'costume_supervisor',
		'archiveFootage',
		'thanks',
		'miscellaneous',
	];

	/**
	 * The selection to display on SUMMARY filmo page in Popup_Person
	 * The order of the list will be the order displayed in the Popup
	 * @see \IMDb\Name::credit() must match the list
	 * @var list<string>
	 */
	public const PERSON_SUMMARY_ROLES = [
		'director',
		'actor',
		'actress',
	];

	/**
	 * The selection to display on Intro filmo page in Popup_Film
	 * The order of the list will be the order displayed in the Popup
	 * @var list<string>
	 */
	public const FILM_DISPLAY_ITEMS_INTRO = [
		'director',
		'actor',
		'runtime',
		'rating',
		'language',
		'country',
		'genre',
	];

	/**
	 * The selection to display on Casting filmo page in Popup_Film
	 * The order of the list will be the order displayed in the Popup
	 * @var list<string>
	 */
	public const FILM_DISPLAY_ITEMS_CASTING = [
		'actor',
	];

	/**
	 * The selection to display on Misc filmo page in Popup_Film
	 * The order of the list will be the order displayed in the Popup
	 * @var list<string>
	 */
	public const FILM_DISPLAY_ITEMS_CREW = [
		'director',
		'writer',
		'producer',
	];

	/**
	 * The selection to display on Plot filmo page in Popup_Film
	 * The order of the list will be the order displayed in the Popup
	 * @var list<string>
	 */
	public const FILM_DISPLAY_ITEMS_PLOT = [
		'plot',
	];

	/**
	 * The selection to display on Misc filmo page in Popup_Film
	 * The order of the list will be the order displayed in the Popup
	 * @var list<string>
	 */
	public const FILM_DISPLAY_ITEMS_MISC = [
		'connection',
		'trivia',
		'soundtrack',
		'goof',
	];

	/**
	 * The selection to display on Misc filmo page in Popup_Person
	 * The order of the list will be the order displayed in the Popup
	 * @var list<string>
	 */
	public const PERSON_DISPLAY_ITEMS_BIO = [
		'spouse',
		'children',
	/*      'pubportrayal',
		'pubinterview',
		'pubprints',
	*/
	];

	/**
	 * List of all roles, translating PERSON_SUMMARY_ROLES and PERSON_ALL_ROLES constants
	 *
	 * @see \IMDb\Name::credit() must match the list
	 * @see \Lumiere\Frontend\Popups\Popup_Person implemented there
	 * @param int $number Number of elements, most haven't
	 * @return array<array-key, string>
	 */
	public static function credits_role_all( int $number = 1 ): array {
		return [
			'archiveFootage'     => _n( 'archive footage', 'archive footages', $number, 'lumiere-movies' ),
			'artDepartment'      => __( 'art department', 'lumiere-movies' ),
			'cinematographer'    => __( 'cinematographer', 'lumiere-movies' ),
			'costume_supervisor' => __( 'costume supervisor', 'lumiere-movies' ),
			'costume_department' => __( 'costume department', 'lumiere-movies' ),
			'director'           => __( 'director', 'lumiere-movies' ),
			'actor'              => __( 'actor', 'lumiere-movies' ),
			'actress'            => __( 'actress', 'lumiere-movies' ),
			'assistantDirector'  => __( 'assistant director', 'lumiere-movies' ),
			'editor'             => __( 'editor', 'lumiere-movies' ),
			'miscellaneous'      => __( 'divers', 'lumiere-movies' ),
			'producer'           => __( 'producer', 'lumiere-movies' ),
			'self'               => __( 'self', 'lumiere-movies' ),
			'showrunner'         => __( 'showrunner', 'lumiere-movies' ),
			'soundtrack'         => _n( 'soundtrack', 'soundtracks', $number, 'lumiere-movies' ),
			'stunts'             => __( 'stunts', 'lumiere-movies' ),
			'thanks'             => _n( 'thanks movie', 'thanks movies', $number, 'lumiere-movies' ),
			'writer'             => __( 'writer', 'lumiere-movies' ),
		];
	}

	/**
	 * Define the type items for Persons
	 *
	 * @param int $number Optional: a number to turn into plural if needed
	 * @return array<string, string>
	 */
	protected static function define_list_items_person( int $number = 1 ): array {
		return [
			'spouse'       => _n( 'spouse', 'spouses', $number, 'lumiere-movies' ),
			'children'     => _n( 'child', 'children', $number, 'lumiere-movies' ),
			'pubportrayal' => __( 'portrayed in', 'lumiere-movies' ),
			'pubinterview' => _n( 'public interview', 'public interviews', $number, 'lumiere-movies' ),
			'pubprints'    => __( 'printed publicity', 'lumiere-movies' ),
		];
	}
}

