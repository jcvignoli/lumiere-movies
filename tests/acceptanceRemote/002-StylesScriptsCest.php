<?php

# Class meant to test remote wordpress install (a WebDriver is needed for JS execution)

class StylesScriptsCest {

	/** Stock the base remote URL
	 *
	 */
	var $url_base_remote = "";

	/** Stock the root remote path
	 *
	 */
	var $root_remote = "";

	public function __construct(){

		$this->url_base_remote = $_ENV['TEST_REMOTE_WP_URL'];
		$this->root_remote = $_ENV['WP_ROOT_REMOTE_FOLDER'];

	}

	public function _before(AcceptanceRemoteTester $I){
		$I->comment('#Code _before#');
	}

	public function _after(AcceptanceRemoteTester $I){
		$I->comment('#Code _after#');
	}

	/** Login to Wordpress
	 *  Trait function to keep the cookie active
	 *
	 */
	private function login(AcceptanceRemoteTester $I) {

		$I->login_universal($I);

	}

	/** Check if auto widget option display a widget based on the title of the page
	 *
	 *  Can't use universal login due to plugin activation/deactivation
	 *
	 */
	public function checkStyleScripts(AcceptanceRemoteTester $I) {

		$I->loginAsAdmin();

		$I->wantTo("Check if scripts and styles are available on ". $this->url_base_remote);

			/* 
			 * Admin pages
			 *
			 */

		$I->comment(\Helper\Color::set('Check Lumière admin pages', 'italic+bold+cyan'));

		// Check scripts and styles in admin
		$I->comment(\Helper\Color::set('Check Lumière admin general advanced', 'italic+bold+cyan'));
		$I->amOnPage("/wp-admin/admin.php?page=lumiere_options&generaloption=advanced");
		$I->seeInPageSource('lumiere_css_admin-css');
		$I->seeInPageSource('lumiere_scripts_admin-js-before');
		$I->seeInPageSource('lumiere_scripts_admin-js');
		$I->seeInPageSource('lumiere_hide_show-js');

		// Check Lumière help page
		$I->comment(\Helper\Color::set('Check Lumière help page', 'italic+bold+cyan'));
		$I->amOnPage("/wp-admin/admin.php?page=lumiere_options&subsection=help");
		$I->seeInPageSource('lumiere_css_admin-css');
		$I->seeInPageSource('lumiere_scripts_admin-js-before');
		$I->seeInPageSource('lumiere_scripts_admin-js');
		$I->seeInPageSource('lumiere_hide_show-js');
		$I->seeInPageSource("wp-lists-js"); # extra script in that page
		$I->seeInPageSource("postbox-js"); # extra script in that page
		$I->seeInPageSource("lumiere_help_scripts-js-after"); # dedicated script to help page
		$I->seeInPageSource("common-js"); # extra script in that page

/* Can't go through a WP popup
		// Check Lumière Widget page (without Classic Widget plugin)
		$I->comment(\Helper\Color::set('Check Lumière Widget page (without Classic Widget plugin)', 'italic+bold+cyan'));
		$I->amOnPage("/wp-admin/widgets.php");
		$I->seeInPageSource("lumiere_block_widget-css");
		$I->seeInPageSource("lumiere_block_widget-js");
		$I->seeInPageSource("lumiere_gutenberg_main-css");
		$I->seeInPageSource("lumiere_css_admin-css");
		$I->seeInPageSource("lumiere_movies_widget");
		$I->seeInPageSource("lumiere_gutenberg_main-js");
		$I->seeInPageSource("lumiere_gutenberg_buttons-js");
		$I->seeInPageSource("lumiere_scripts_admin-js");
		$I->seeInPageSource("lumiere_hide_show-js");
*/
		// Disable classic-editor so we can test Blocks editor
		$I->comment(\Helper\Color::set('Disable classic-editor plugin so we can test Blocks editor', 'italic+bold+cyan'));
		$I->amOnPage('/wp-admin/plugins.php');
		/*	Conditional plugin deactivation (in _support/AcceptanceTrait.php)
			Avoid to throw error if untrue, normal behaviour of codeception 
			If $plugin is activated, deactivate it */
		$I->wait(1);
		$I->maybeDeactivatePlugin('classic-editor');
		$I->wait(1);

		// Check Lumière (Gutenberg) Block Editor page
		$I->comment(\Helper\Color::set('Check Lumière (Gutenberg) Block Editor page', 'italic+bold+cyan'));
		$I->amOnPage("/wp-admin/post.php?post=4715&action=edit");
		$I->waitPageLoad();
		$I->seeInPageSource("lumiere_gutenberg_main-js"); 	# Gutenberg main block js
		$I->seeInPageSource("lumiere_gutenberg_buttons-js"); 	# Gutenberg button block js
		$I->seeInPageSource("lumiere_block_widget-css");		# Gutenberg widget block css
		$I->seeInPageSource("lumiere_block_widget-js");		# Gutenberg widget block js
		$I->seeInPageSource("lumiere_scripts_admin-js"); 	# Lumière main js
		$I->seeInPageSource('lumiere_css_admin-css'); 		# Lumière main css
		$I->seeInPageSource("lumiere_scripts_admin-js-before"); # Lumière js vars for scripts
		$I->seeInPageSource("lumiere_queryid_widget"); 		# Lumière Metabox is available
		$I->seeInPageSource("wp-tinymce-root-js"); 		# TinyMCE main plugin
		$I->seeInPageSource("lumiere_quicktag_addbutton-js"); 	# Quicktag Lumière plugin
		$I->seeInPageSource("lumiere_hide_show-js"); 		# hide/show script

		// Activate classic-editor so we can test Classic editor
		$I->comment(\Helper\Color::set('Activate classic-editor plugin so we can test Blocks editor', 'italic+bold+cyan'));
		$I->amOnPage('/wp-admin/plugins.php');
		$I->wait(1);
		$I->maybeActivatePlugin('classic-editor');
		$I->wait(1);

		// Check Lumière Classic Editor page (with Classic Editor plugin)
		$I->comment(\Helper\Color::set('Check Lumière Classic Editor page (with Classic Editor plugin)', 'italic+bold+cyan'));
		$I->amOnPage("/wp-admin/post.php?post=4715&action=edit");
		$I->waitPageLoad();
		$I->seeInPageSource("lumiere_scripts_admin-js"); 	# Lumière main js
		$I->seeInPageSource("lumiere_scripts_admin-js-before"); # Lumière js vars for scripts
		$I->seeInPageSource("lumiere_quicktag_addbutton-js"); 	# Quicktag Lumière plugin
		$I->seeInPageSource("lumiere_hide_show-js"); 		# hide/show script
		$I->seeInPageSource("lumiere_admin_tinymce_editor");	# TinyMCE Lumière plugin
		$I->seeInPageSource("wp-tinymce-root-js"); 		# TinyMCE main plugin
		$I->seeInPageSource("lumiere_queryid_widget"); 		# Lumière Metabox is available

			/* 
			 * Frontend pages
			 *
			 */

		$I->comment(\Helper\Color::set('Checking normal page', 'italic+bold+cyan'));
		$I->amOnPage('/2021/test-codeception/');
		$I->seeInPageSource("lumiere_highslide_core-css");		# Highslide CSS
		$I->seeInPageSource("lumiere_style_main-css"); 			# Lumière main css
		$I->seeInPageSource("lumiere_style_oceanwpfixes_general-css");	# Lumiere extra OceanWP fix
		$I->seeInPageSource("lumiere_highslide_core-js");		# Highslide JS
		$I->seeInPageSource("lumiere_highslide_options-js");		# Highslide Lumière Options
		$I->seeInPageSource("lumiere_highslide_options-js-before"); 	# Lumière js vars for scripts
		$I->seeInPageSource("lumiere_scripts-js"); 			# Lumière main JS
		$I->seeInPageSource("lumiere_scripts-js-before");		# Lumière vars for main JS
		$I->seeInPageSource("lumiere_hide_show-js"); 			# hide/show script

			// Taxonomy person director page

		$I->comment(\Helper\Color::set('Checking taxonomy page', 'italic+bold+cyan'));
		$I->amOnPage('/lumiere-director/tony-zarindast/');
		$I->seeInPageSource("lumiere_highslide_core-css"); 		# Highslide CSS
		$I->seeInPageSource("lumiere_style_main-css"); 			# Lumière main css
		$I->seeInPageSource("lumiere_style_oceanwpfixes_general-css");	# Lumiere extra OceanWP fix
		$I->seeInPageSource("lumiere_highslide_core-js");		# Highslide JS
		$I->seeInPageSource("lumiere_highslide_options-js");		# Highslide Lumière Options
		$I->seeInPageSource("lumiere_highslide_options-js-before"); 	# Lumière js vars for scripts
		$I->seeInPageSource("lumiere_scripts-js"); 			# Lumière main JS
		$I->seeInPageSource("lumiere_scripts-js-before");		# Lumière vars for main JS

			// Popup person page

		$I->comment(\Helper\Color::set('Checking Popup person page', 'italic+bold+cyan'));
		$I->amOnPage('/lumiere/person/?mid=0729473');
		$I->seeInPageSource("lumiere-movies/pics/favicon/favicon-16x16.png");	 	# Lumière favicon 16
		$I->seeInPageSource("lumiere-movies/pics/favicon/favicon-32x32.png"); 	# Lumière favicon 32
		$I->seeInPageSource("lumiere-movies/pics/favicon/apple-touch-icon.png"); 	# Lumière favicon Apple
		$I->seeInPageSource("lumiere-movies/pics/favicon/site.webmanifest");	 	# Lumière webmanifest
		$I->seeInPageSource("lumiere_style_oceanwpfixes_popups-css");			# Lumiere popup OceanWP fix
		$I->seeInPageSource("lumiere_highslide_core-css"); 				# Highslide CSS
		$I->seeInPageSource("lumiere_style_main-css"); 					# Lumière main css
		$I->seeInPageSource("lumiere_highslide_core-js");				# Highslide JS
		$I->seeInPageSource("lumiere_highslide_options-js");				# Highslide Lumière Options
		$I->seeInPageSource("lumiere_highslide_options-js-before"); 			# Lumière js vars for scripts
		$I->seeInPageSource("lumiere_scripts-js"); 					# Lumière main JS
		$I->seeInPageSource("lumiere_scripts-js-before");				# Lumière vars for main JS
		$I->seeInPageSource("lumiere_hide_show-js"); 					# hide/show script
		$I->click('Full filmography');
		$I->see('The Popcorn Chronicles');
		$I->click('Full biography');
		$I->see('Shock Cinema');
		$I->click('Misc');
		$I->see('His father Luis');

			// Popup movie page

		$I->comment(\Helper\Color::set('Checking Popup movie page', 'italic+bold+cyan'));
		$I->amOnPage('/lumiere/film/?mid=&film=interstellar');
		$I->seeInPageSource("canonical");						 	# Meta tag
		$I->seeInPageSource("article:tag");					 	# Meta tag
		$I->seeInPageSource("lumiere-movies/pics/favicon/favicon-16x16.png");	 	# Lumière favicon 16
		$I->seeInPageSource("lumiere-movies/pics/favicon/favicon-32x32.png"); 	# Lumière favicon 32
		$I->seeInPageSource("lumiere-movies/pics/favicon/apple-touch-icon.png"); 	# Lumière favicon Apple
		$I->seeInPageSource("lumiere-movies/pics/favicon/site.webmanifest");	 	# Lumière webmanifest
		$I->seeInPageSource("lumiere_style_oceanwpfixes_popups-css");			# Lumiere popup OceanWP fix
		$I->seeInPageSource("lumiere_highslide_core-css"); 				# Highslide CSS
		$I->seeInPageSource("lumiere_style_main-css"); 					# Lumière main css
		$I->seeInPageSource("lumiere_highslide_core-js");				# Highslide JS
		$I->seeInPageSource("lumiere_highslide_options-js");				# Highslide Lumière Options
		$I->seeInPageSource("lumiere_highslide_options-js-before"); 			# Lumière js vars for scripts
		$I->seeInPageSource("lumiere_scripts-js"); 					# Lumière main JS
		$I->seeInPageSource("lumiere_scripts-js-before");				# Lumière vars for main JS
		$I->seeInPageSource("lumiere_hide_show-js"); 					# hide/show script
		$I->click('Actors');
		$I->see('Ellen Burstyn');
		$I->click('Crew');
		$I->see('Christopher Nolan');
		$I->click('Plots');
		$I->see('A team of explorers travel');
		$I->click('Misc');
		$I->see('Early in pre-production');

	}

	/** Check if the change of layout styles in admin is reflection in the front end
	 *
	 * @before login
	 *
	 */
	public function checkStyleEdition(AcceptanceRemoteTester $I) {

		$I->comment(\Helper\Color::set('Change layout', 'italic+bold+cyan'));

		$I->amOnPage("/wp-admin/admin.php?page=lumiere_options");

		// Try with selection black
		$I->scrollTo('#plainpages');
		$I->selectOption("form [name=imdb_imdbintotheposttheme]", "black");
		$I->click('#update_imdbSettings');
		$I->comment(\Helper\Color::set('[Action] Selection has been switched to "black"', 'italic+bold+cyan'));
		$I->amOnPage('/2021/test-codeception/');
		$I->seeInPageSource("imdbincluded_black"); 	# CSS for black layout 

		// Try with selection grey (default)
		$I->amOnPage("/wp-admin/admin.php?page=lumiere_options");
		$I->scrollTo('#plainpages');
		$I->selectOption("form [name=imdb_imdbintotheposttheme]", "grey");
		$I->click('#update_imdbSettings');
		$I->comment(\Helper\Color::set('[Action] Selection has been switched to "grey"', 'italic+bold+cyan'));
		$I->amOnPage('/2021/test-codeception/');
		$I->seeInPageSource("imdbincluded_grey"); 	# CSS for grey layout (default)

	}
}




