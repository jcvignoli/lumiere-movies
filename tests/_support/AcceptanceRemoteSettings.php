<?php

/** 
 * Class including general options for testing
 * Meant to be compatible with posts created with
 * tests/IMPORT_WP_POST_MAIN.txt
 * tests/IMPORT_WP_POST_AUTOWIDGET.txt
 * Constant ADMIN_POST_ID_TESTS must be edited according to the post ID of IMPORT_WP_POST_MAIN.txt
 */
class AcceptanceRemoteSettings {

	/** Admin pages */
	public const LUMIERE_GENERAL_OPTIONS_URL = '/wp-admin/admin.php?page=lumiere_options';
	public const LUMIERE_ADVANCED_OPTIONS_URL = '/wp-admin/admin.php?page=lumiere_options&generaloption=advanced';
	public const LUMIERE_HELP_GENERAL_URL = '/wp-admin/admin.php?page=lumiere_options&subsection=help';
	public const LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL = '/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what';
	public const LUMIERE_DATA_OPTIONS_TAXO_URL = '/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=taxo';
	public const LUMIERE_CACHE_OPTIONS_MANAGE_URL = '/wp-admin/admin.php?page=lumiere_options&subsection=cache&cacheoption=manage';

	public const ADMIN_PLUGINS_URL = '/wp-admin/plugins.php';
	public const ADMIN_PERMALINK_URL = '/wp-admin/options-permalink.php';

	// This needs admin cron tool WP Plugin to be installed in order to work
	public const ADMIN_POST_CRON_MANAGE ='/wp-admin/tools.php?page=crontrol_admin_manage_page';

	/** Testing pages */
	public const TESTING_PAGE_BASE_URL = '/2021/test-codeception/';
	public const TESTING_PAGE_BASE_A_DIRECTOR = 'Christopher Nolan';
	public const TESTING_PAGE_TAXONOMY_URL = '/lumiere-director/stanley-kubrick/';
	public const TESTING_PAGE_POPUP_FILM_URL = '/lumiere/film/?mid=&film=interstellar';
	public const TESTING_PAGE_POPUP_FILM_URL_WITHOUTMID = '/lumiere/film/?film=interstellar';
	public const TESTING_PAGE_POPUP_FILM_TITLE = 'interstellar';
	// Here, Jorge Rivero
	public const TESTING_PAGE_POPUP_PERSON_URL = '/lumiere/person/?mid=0729473';
	public const TESTING_PAGE_POPUP_PERSON_MID = '0729473';
	public const TESTING_PAGE_BASE_ELEMENT = 'Pajarero';
	// WP Post including a test for auto widget, important to have a movie's name as a title.
	// An example is available in tests/IMPORT_WP_POST_AUTOWIDGET.txt
	public const TESTING_PAGE_AUTOWIDGET_URL = '/2021/y-tu-mama-tambien/';
	public const TESTING_PAGE_AUTOWIDGET_TITLE = 'And Your Mother Too';

}
