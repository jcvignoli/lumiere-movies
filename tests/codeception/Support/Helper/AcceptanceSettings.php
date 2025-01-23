<?php

declare(strict_types=1);

namespace Tests\Support\Helper;

/** 
 * Class including global general options for all testing (Local+Remote)
 */
class AcceptanceSettings {

	/** Admin pages */
	public const LUMIERE_GENERAL_OPTIONS_URL = '/wp-admin/admin.php?page=lumiere_options';
	public const LUMIERE_ADVANCED_OPTIONS_URL = '/wp-admin/admin.php?page=lumiere_options&subsection=advanced';
	public const LUMIERE_HELP_GENERAL_URL = '/wp-admin/admin.php?page=lumiere_options_help';
	public const LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL = '/wp-admin/admin.php?page=lumiere_options_data';
	public const LUMIERE_DATA_OPTIONS_TAXO_URL = '/wp-admin/admin.php?page=lumiere_options_data&subsection=taxo';
	public const LUMIERE_DATA_OPTIONS_ORDER_URL = '/wp-admin/admin.php?page=lumiere_options_data&subsection=order';
	public const LUMIERE_CACHE_OPTIONS_MANAGE_URL = '/wp-admin/admin.php?page=lumiere_options_cache&subsection=manage';
	public const LUMIERE_CACHE_OPTIONS_URL = '/wp-admin/admin.php?page=lumiere_options_cache';

	public const ADMIN_PERMALINK_URL = '/wp-admin/options-permalink.php';

	// This needs admin cron tool WP Plugin to be installed in order to work
	public const ADMIN_POST_CRON_MANAGE ='/wp-admin/tools.php?page=wp-crontrol';

	/** Testing pages */
	public const TESTING_PAGE_BASE_URL = '/en/2021/test-codeception/';
	public const TESTING_PAGE_BASE_A_DIRECTOR = 'Christopher Nolan';
	public const TESTING_PAGE_TAXONOMY_URL = '/en/lumiere-director/stanley-kubrick-en/';
	public const TESTING_PAGE_POPUP_FILM_URL = '/en/lumiere/film/?mid=&film=interstellar';
	public const TESTING_PAGE_POPUP_FILM_URL_WITHOUTMID = '/en/lumiere/film/?film=interstellar';
	public const TESTING_PAGE_POPUP_FILM_TITLE = 'interstellar';
	public const TESTING_PAGE_BASE_URL_FR = '/2023/test-codeception-french/';
	public const TESTING_PAGE_BASE_URL_FR_TWO = '/blogpourext/2021/y-tu-mama-tambien/';
	public const TESTING_NORMAL_PAGE = '/2020/le-site-de-kotosh-et-le-temple-des-mains-croisees/';
	// Here, Jorge Rivero
	public const TESTING_PAGE_POPUP_PERSON_URL = '/en/lumiere/person/?mid=0729473';
	public const TESTING_PAGE_POPUP_PERSON_MID = '0729473';
	public const TESTING_PAGE_BASE_ELEMENT = 'Pajarero';
	// WP Post including a test for auto title widget, important to have a movie's name as a title.
	public const TESTING_PAGE_AUTOTITLEWIDGET_URL = '/2021/y-tu-mama-tambien/';
	public const TESTING_PAGE_AUTOTITLEWIDGET_TITLE = 'Y Tu Mamá También';
}
