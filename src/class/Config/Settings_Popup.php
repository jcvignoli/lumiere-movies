<?php declare( strict_types = 1 );
/**
 * Settings for Popups
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
 * Settings class for Popups, (films/persons/search)
 * Meant to select which data display in which popup menus
 * Doesn't include any Get_Options_class as there are only constants
 */
final class Settings_Popup {

	/**
	 * The selection to display on FULL filmo page in Popup_Person
	 * The order of the list will be the order displayed in the Popup
	 * @see \IMDb\Name::credit() must match the list
	 * @var list<string>
	 */
	public const PERSON_ALL_ROLES           = [
		'title',
		'pic',
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
	public const PERSON_SUMMARY_ROLES       = [
		'title',
		'born',
		'died',
		'pic',
		'bio',
		'director',
		'actor',
		'actress',
	];

	/**
	 * The selection to display on Biography page in Popup_Person
	 * The order of the list will be the order displayed in the Popup
	 * @var list<string>
	 */
	public const PERSON_DISPLAY_ITEMS_BIO   = [
		'title',
		'born',
		'died',
		'pic',
		'bio',
		'spouse',
		'children',
		'pubmovies',
		'pubportrayal',
		'pubinterview',
		'pubprints',
	];

	/**
	 * The selection to display on Misc page in Popup_Person
	 * The order of the list will be the order displayed in the Popup
	 * @var list<string>
	 */
	public const PERSON_DISPLAY_ITEMS_MISC  = [
		'title',
		'pic',
		'nickname',
		'trivia',
		'quotes',
		'trademark',
	];

	/**
	 * The selection to display on Intro filmo page in Popup_Film
	 * The order of the list will be the order displayed in the Popup
	 * @var list<string>
	 */
	public const FILM_DISPLAY_ITEMS_INTRO   = [
		'title',
		'pic',
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
		'title',
		'pic',
		'actor',
	];

	/**
	 * The selection to display on Misc filmo page in Popup_Film
	 * The order of the list will be the order displayed in the Popup
	 * @var list<string>
	 */
	public const FILM_DISPLAY_ITEMS_CREW    = [
		'title',
		'pic',
		'director',
		'writer',
		'producer',
	];

	/**
	 * The selection to display on Plot filmo page in Popup_Film
	 * The order of the list will be the order displayed in the Popup
	 * @var list<string>
	 */
	public const FILM_DISPLAY_ITEMS_PLOT    = [
		'title',
		'pic',
		'plot',
	];

	/**
	 * The selection to display on Misc filmo page in Popup_Film
	 * The order of the list will be the order displayed in the Popup
	 * @var list<string>
	 */
	public const FILM_DISPLAY_ITEMS_MISC    = [
		'title',
		'pic',
		'connection',
		'trivia',
		'soundtrack',
		'goof',
	];
}

