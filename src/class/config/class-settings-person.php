<?php declare( strict_types = 1 );
/**
 * Settings for Persons
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
 * Settings class for Person
  */
class Settings_Person {

	/**
	 * Partial namespace of modules
	 * Used to build the full person namespace
	 * @see \Lumiere\Frontend\Popup\Popup_Person
	 */
	public const LUM_PERSON_MODULE_CLASS = '\Lumiere\Frontend\Module\Person\Person_';

	/**
	 * Define the type items for Persons
	 *
	 * @param int $number Optional: a number to turn into plural if needed
	 * @return array<string, string>
	 */
	protected static function define_list_items_person( int $number = 1 ): array {
		return [
			'award'        => _n( 'award', 'awards', $number, 'lumiere-movies' ),
			'birthname'    => __( 'birthname', 'lumiere-movies' ),
			'born'         => __( 'born', 'lumiere-movies' ),
			'children'     => _n( 'child', 'children', $number, 'lumiere-movies' ),
			'credit'       => _n( 'credit', 'credits', $number, 'lumiere-movies' ),
			'died'         => __( 'died', 'lumiere-movies' ),
			'name'         => __( 'name', 'lumiere-movies' ),
			'news'         => __( 'news', 'lumiere-movies' ),
			'nickname'     => _n( 'nickname', 'nicknames', $number, 'lumiere-movies' ),
			'pubinterview' => _n( 'public interview', 'public interviews', $number, 'lumiere-movies' ),
			'pubmovies'    => _n( 'biographical movie', 'biographical movies', $number, 'lumiere-movies' ),
			'pubportrayal' => __( 'portrayed in', 'lumiere-movies' ),
			'pubprints'    => __( 'printed publicity', 'lumiere-movies' ),
			'quotes'       => _n( 'quote', 'quotes', $number, 'lumiere-movies' ),
			'spouse'       => _n( 'spouse', 'spouses', $number, 'lumiere-movies' ),
			'trivia'       => _n( 'trivia', 'trivias', $number, 'lumiere-movies' ),
			'trademark'    => _n( 'trademark', 'trademarks', $number, 'lumiere-movies' ),
		];
	}

	/**
	 * List of all roles, translating Settings_Popup::PERSON_SUMMARY_ROLES and Settings_Popup::PERSON_ALL_ROLES constants
	 *
	 * @see \IMDb\Name::credit() must match the list
	 * @see \Lumiere\Frontend\Popups\Popup_Person implemented there
	 * @param int $number Number of elements, most haven't
	 * @return array<array-key, string>
	 */
	protected static function credits_role_all( int $number = 1 ): array {
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
}

