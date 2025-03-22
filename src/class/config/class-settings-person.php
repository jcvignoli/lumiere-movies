<?php declare( strict_types = 1 );
/**
 * Settings for Persons
 *
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */
namespace Lumiere\Config;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) { // Don't check for Settings class since it's Settings class.
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

/**
 * Settings class for Person
 * Is extended by Get_Options_Person
 * If a new IMDB field is created it will automatically create new fields, be it in database and in the admin panel options
 * IMDB fields are automatically translated if plural
 *
 * @phpstan-type OPTIONS_DATA_PERSON_ORDER array{ order: array{ 'title': numeric-string, 'pic': numeric-string, 'bio': numeric-string, 'nickname': numeric-string, 'spouse': numeric-string, 'children': numeric-string, 'credit': numeric-string, 'news': numeric-string, 'pubinterview': numeric-string, 'pubmovies': numeric-string, 'pubportrayal': numeric-string, 'pubprints': numeric-string, 'quotes': numeric-string, 'trivia': numeric-string, 'trademark': numeric-string, 'award': numeric-string, 'birthname': numeric-string, 'born': numeric-string, 'died': numeric-string, 'name': numeric-string } }
 * @phpstan-type OPTIONS_DATA_PERSON_ACTIVATED array{ activated: array{ title_active: '1', pic_active: '1', bio_active: '0'|'1', nickname_active: '0'|'1', spouse_active?: '0'|'1', children_active?: '0'|'1', credit_active?: '0'|'1', news_active?: '0'|'1', pubinterview_active?: '0'|'1', pubmovies_active?: '0'|'1', pubportrayal_active?: '0'|'1', pubprints_active?: '0'|'1', quotes_active?: '0'|'1', trivia_active?: '0'|'1', trademark_active?: '0'|'1', award_active?: '0'|'1' } }
 * @phpstan-type OPTIONS_DATA_PERSON_NUMBER array{ number: array{award_number: numeric-string, credit_number: numeric-string, news_number: numeric-string, pubinterview_number: numeric-string, pubmovies_number: numeric-string, pubportrayal_number: numeric-string, pubprints_number: numeric-string, quotes_number: numeric-string, trivia_number: numeric-string, trademark_number: numeric-string} }
 *
 * // Final list of options
 * @phpstan-type OPTIONS_DATA_PERSON \Union<OPTIONS_DATA_PERSON_ORDER, OPTIONS_DATA_PERSON_ACTIVATED, OPTIONS_DATA_PERSON_NUMBER>
 * @phpstan-type OPTIONS_DATA_PERSON_PSALM OPTIONS_DATA_PERSON_ORDER|OPTIONS_DATA_PERSON_ACTIVATED|OPTIONS_DATA_PERSON_NUMBER
 *
 * @since 4.6 class created
 * @see \Imdb\Name Function and constants here are related to data coming from there
 */
class Settings_Person {

	/**
	 * Name of the databases as stored in WordPress db
	 * Only used in child class, has to be called in Get_Options
	 */
	protected const LUM_DATA_PERSON_OPTIONS         = 'lumiere_data_person_options';

	/**
	 * Partial namespace of modules
	 * Used to build the full person namespace
	 * @see \Lumiere\Frontend\Popup\Popup_Person
	 */
	public const LUM_PERSON_MODULE_CLASS            = '\Lumiere\Frontend\Module\Person\Person_';

	/**
	 * Internal URL pages constants
	 * Must be public, used everywhere
	 */
	public const LUM_POPUP_PERSON_PATH              = 'class/frontend/popups/class-popup-person.php';
	public const LUM_TAXO_PEOPLE_THEME              = 'class/theme/class-taxonomy-people-standard.php';

	/**
	 * Default imdb fields with numbers selection
	 */
	public const LUM_DATA_PERSON_DEFAULT_WITHNUMBER = [
		'award'        => '10',
		'credit'       => '10',
		'news'         => '10',
		'pubinterview' => '5',
		'pubmovies'    => '3',
		'pubportrayal' => '3',
		'pubprints'    => '3',
		'quotes'       => '10',
		'trivia'       => '3',
		'trademark'    => '10',
	];

	/**
	 * List of items that are unactive
	 * The list targets Settings_Person::define_list_items_person()
	 */
	public const LUM_DATA_PERSON_UNACTIVE          = [
		'pic',
		'title',
		'died',
		'born',
		'name',
		'birthname',
		'award',
	];

	/**
	 * Define the type items for Persons
	 * @see \Lumiere\Frontend\Post\Person_Factory::factory_person_items_methods() will be matched against this list to get the modules
	 * @see \Lumiere\Config\Settings::get_default_data_person_option() Build the database using this list
	 *
	 * @param int $number Optional: a number to turn into plural if needed
	 * @return array<string, string>
	 */
	protected static function define_list_items_person( int $number = 1 ): array {
		return [
			'title'        => __( 'title', 'lumiere-movies' ),
			'pic'          => __( 'pic', 'lumiere-movies' ),
			'bio'          => __( 'biography', 'lumiere-movies' ),
			'nickname'     => _n( 'nickname', 'nicknames', $number, 'lumiere-movies' ),
			'spouse'       => _n( 'spouse', 'spouses', $number, 'lumiere-movies' ),
			'children'     => _n( 'child', 'children', $number, 'lumiere-movies' ),
			'credit'       => _n( 'credit', 'credits', $number, 'lumiere-movies' ),
			'news'         => __( 'news', 'lumiere-movies' ),
			'pubinterview' => _n( 'publicity interview', 'publicity interviews', $number, 'lumiere-movies' ),
			'pubmovies'    => _n( 'biographical movie', 'biographical movies', $number, 'lumiere-movies' ),
			'pubportrayal' => __( 'portrayal movies', 'lumiere-movies' ),
			'pubprints'    => _n( 'printed publicity', 'printed publicity', $number, 'lumiere-movies' ),
			'quotes'       => _n( 'quote', 'quotes', $number, 'lumiere-movies' ),
			'trivia'       => _n( 'trivia', 'trivias', $number, 'lumiere-movies' ),
			'trademark'    => _n( 'trademark', 'trademarks', $number, 'lumiere-movies' ),
			'award'        => _n( 'award', 'awards', $number, 'lumiere-movies' ),
			'birthname'    => __( 'birthname', 'lumiere-movies' ),
			'born'         => __( 'born', 'lumiere-movies' ),
			'died'         => __( 'died', 'lumiere-movies' ),
			'name'         => __( 'name', 'lumiere-movies' ),
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
			'miscellaneous'      => __( 'miscellaneous', 'lumiere-movies' ),
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
	 * Define the list of data person comments
	 * This comment is display in admin data, explaining the purpose of selecting an item
	 * Must match list in Settings_Person::define_list_items_person()
	 *
	 * @since 4.6 method added
	 *
	 * @return array<string, string>
	 */
	protected static function define_items_person_details_comments(): array {
		return [
			'title'        => __( 'Display title', 'lumiere-movies' ),
			'pic'          => __( 'Display photography', 'lumiere-movies' ),
			'bio'          => __( 'Display biography', 'lumiere-movies' ),
			'nickname'     => __( 'Display nicknames', 'lumiere-movies' ),
			'spouse'       => __( 'Display spouses', 'lumiere-movies' ),
			'children'     => __( 'Display children', 'lumiere-movies' ),
			'credit'       => __( 'Display (a number of) movies that participated in (various categories)', 'lumiere-movies' ),
			'news'         => __( 'Display (a number of) latests news', 'lumiere-movies' ),
			'pubinterview' => __( 'Display (a number of) publicity interviews', 'lumiere-movies' ),
			'pubmovies'    => __( 'Display (a number of) biographical movies', 'lumiere-movies' ),
			'pubportrayal' => __( 'Display (a number of) portrayal movies', 'lumiere-movies' ),
			'pubprints'    => __( 'Display (a number of) printed publicity', 'lumiere-movies' ),
			'quotes'       => __( 'Display (a number of) quotes', 'lumiere-movies' ),
			'trivia'       => __( 'Display (a number of) trivias', 'lumiere-movies' ),
			'trademark'    => __( 'Display (a number of) trademarks', 'lumiere-movies' ),
			'award'        => __( 'Display (a number of) awards', 'lumiere-movies' ),
		];
	}
}

