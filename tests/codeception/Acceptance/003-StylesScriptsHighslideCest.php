<?php

declare(strict_types=1);

namespace Tests\Support;

use Tests\Support\Helper\AcceptanceSettings;
use Codeception\Scenario;

# Class meant to test remote wordpress install (a WebDriver is needed for JS execution)

class StylesScriptsHighslideCest {

	public function _before(AcceptanceTester $I){
		$I->comment('#Code _before#');
	}

	public function _after(AcceptanceTester $I){
		$I->comment('#Code _after#');
	}

	/**
	 * Login to Wordpress
	 * Trait function to keep the cookie active
	 */
	private function login(AcceptanceTester $I) {
		$I->login_universal($I);
	}

	/**
	 * Check if auto title widget option display a widget based on the title of the page
	 * Can't use universal login due to plugin activation/deactivation
	 */
	public function checkStyleScripts(AcceptanceTester $I) {

		$I->loginAsAdmin();

		$I->comment("Check if scripts and styles are available on ". $I->getCustomBaseUrl() ); // In acceptance helper

			/** 
			 * Admin pages
			 */

		$I->comment(Helper\Color::set('Check Lumière admin pages', 'italic+bold+cyan'));

		// Check scripts and styles in admin
		$I->comment(Helper\Color::set('Check Lumière admin main advanced', 'italic+bold+cyan'));
		$I->amOnPage( AcceptanceSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->seeInPageSource('lumiere_css_admin-css');
		$I->seeInPageSource('lumiere_scripts_admin-js-before');
		$I->seeInPageSource('lumiere_scripts_admin-js');
		$I->seeInPageSource('lumiere_hide_show-js');

		// Check Lumière help page
		$I->comment(Helper\Color::set('Check Lumière help page', 'italic+bold+cyan'));
		$I->amOnPage( AcceptanceSettings::LUMIERE_HELP_GENERAL_URL );
		$I->seeInPageSource('lumiere_css_admin-css');
		$I->seeInPageSource('lumiere_scripts_admin-js-before');
		$I->seeInPageSource('lumiere_scripts_admin-js');
		$I->seeInPageSource('lumiere_hide_show-js');

		// Disable classic-editor so we can test Blocks editor
		$I->comment(Helper\Color::set('Disable classic-editor plugin so we can test Blocks editor', 'italic+bold+cyan'));
		$I->amOnPluginsPage();
		$I->waitPageLoad();
		$I->maybeDeactivatePlugin('classic-editor');
		$I->waitPageLoad();

		// Check Lumière (Gutenberg) Block Editor page
		$I->comment(Helper\Color::set('Check Lumière (Gutenberg) Block Editor page', 'italic+bold+cyan'));
		$I->amOnPage( ADMIN_POST_ID_TESTS );
		$I->waitPageLoad();
		$I->seeInPageSource("assets/blocks/post/index.js?ver="); 	# Gutenberg movie block js
		$I->seeInPageSource("assets/blocks/post/index.css?ver="); 	# Gutenberg movie block css
		$I->seeInPageSource("assets/blocks/addlink/index.js?ver="); 	# Gutenberg addlink block js
		$I->seeInPageSource("assets/blocks/widget/index.css?ver=");	# Gutenberg widget block css
		$I->seeInPageSource("assets/blocks/widget/index.js?ver=");	# Gutenberg widget block js
		$I->seeInPageSource("assets/blocks/opensearch/index.js?ver=");	# Gutenberg opensearch block js
		$I->seeInPageSource("lumiere_scripts_admin-js"); 		# Lumière main js
		$I->seeInPageSource('lumiere_css_admin-css'); 			# Lumière main css
		$I->seeInPageSource("lumiere_scripts_admin-js-before"); 	# Lumière js vars for scripts
		$I->seeInPageSource("lum_form_type_query"); 			# Lumière Metabox is available
		$I->seeInPageSource("lumiere_quicktag_addbutton-js"); 		# Quicktag Lumière plugin
		$I->seeInPageSource("lumiere_hide_show-js"); 			# hide/show script

		// Activate classic-editor so we can test Classic editor
		$I->comment(Helper\Color::set('Activate classic-editor plugin so we can test Blocks editor', 'italic+bold+cyan'));
		$I->amOnPluginsPage();
		$I->waitPageLoad();
		$I->maybeActivatePlugin('classic-editor');
		$I->waitPageLoad();

		// Check Lumière Classic Editor page (with Classic Editor plugin)
		$I->comment(Helper\Color::set('Check Lumière Classic Editor page (with Classic Editor plugin)', 'italic+bold+cyan'));
		$I->amOnPage( ADMIN_POST_ID_TESTS );
		$I->waitPageLoad();
		$I->dontSeeInPageSource("assets/blocks/post/index.js?ver="); 		# Gutenberg movie block js
		$I->dontSeeInPageSource("assets/blocks/post/index.css?ver="); 		# Gutenberg movie block css
		$I->dontSeeInPageSource("assets/blocks/addlink/index.js?ver="); 	# Gutenberg addlink block js
		$I->dontSeeInPageSource("assets/blocks/widget/index.css?ver=");		# Gutenberg widget block css
		$I->dontSeeInPageSource("assets/blocks/widget/index.js?ver=");		# Gutenberg widget block js
		$I->dontSeeInPageSource("assets/blocks/opensearch/index.js?ver=");	# Gutenberg opensearch block js
		$I->seeInPageSource("lumiere_scripts_admin-js"); 	 		# Lumière main js
		$I->seeInPageSource("lumiere_scripts_admin-js-before");			# Lumière js vars for scripts
		$I->seeInPageSource("lumiere_quicktag_addbutton-js"); 			# Quicktag Lumière plugin
		$I->seeInPageSource("lumiere_hide_show-js"); 				# hide/show script
		$I->seeInPageSource("lum_form_type_query"); 				# Lumière Metabox is available

			/** 
			 * Frontend pages
			 */

		// Make sure Highslide is active, following tests are run with Highslide
		$I->SwitchModalWindow('Highslide');

		$I->comment(Helper\Color::set('Checking normal page', 'italic+bold+cyan'));
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_BASE_URL );
		$I->waitPageLoad();
		$I->seeInPageSource("lumiere_highslide_core_style-css");	# Highslide CSS
		$I->seeInPageSource("lumiere_style_main-css"); 			# Lumière main css
		$I->seeInPageSource("lumiere_highslide_core-js");		# Highslide JS
		$I->seeInPageSource("lumiere_highslide_options-js");		# Highslide Lumière Options
		$I->seeInPageSource("lumiere_scripts-js"); 			# Lumière main JS
		$I->seeInPageSource("lumiere_scripts-js-after");		# Lumière vars for main JS
		$I->seeInPageSource("lumiere_hide_show-js"); 			# hide/show script

			// Taxonomy person director page

		$I->comment(Helper\Color::set('Checking taxonomy page', 'italic+bold+cyan'));
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_TAXONOMY_URL );
		$I->waitPageLoad();
		$I->seeInPageSource("lumiere_highslide_core_style-css"); 	# Highslide CSS
		$I->seeInPageSource("lumiere_style_main-css"); 			# Lumière main css
		$I->seeInPageSource("lumiere_highslide_core-js");		# Highslide JS
		$I->seeInPageSource("lumiere_highslide_options-js");		# Highslide Lumière Options
		$I->seeInPageSource("lumiere_scripts-js"); 			# Lumière main JS
		$I->seeInPageSource("lumiere_scripts-js-after");		# Lumière vars for main JS

			// Popup person page

		$I->comment(Helper\Color::set('Checking Popup person page', 'italic+bold+cyan'));
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_POPUP_PERSON_URL );
		$I->waitPageLoad();
		$I->seeInPageSource("lumiere-movies/assets/pics/favicon/favicon-16x16.png");	# Lumière favicon 16
		$I->seeInPageSource("lumiere-movies/assets/pics/favicon/favicon-32x32.png"); 	# Lumière favicon 32
		$I->seeInPageSource("lumiere-movies/assets/pics/favicon/apple-touch-icon.png"); # Lumière favicon Apple
		$I->seeInPageSource("lumiere-movies/assets/pics/favicon/site.webmanifest");	# Lumière webmanifest
		$I->seeInPageSource("lumiere_highslide_core_style-css"); 			# Highslide CSS
		$I->seeInPageSource("lumiere_style_main-css"); 					# Lumière main css
		$I->seeInPageSource("lumiere_highslide_core-js");				# Highslide JS
		$I->seeInPageSource("lumiere_highslide_options-js");				# Highslide Lumière Options
		$I->seeInPageSource("lumiere_scripts-js"); 					# Lumière main JS
		$I->seeInPageSource("lumiere_scripts-js-after");				# Lumière vars for main JS
		$I->seeInPageSource("lumiere_hide_show-js"); 					# hide/show script
		$I->click('Full filmography');
		$I->see('The Popcorn Chronicles');
		$I->click('Full biography');
		$I->see('and muscular Mexican leading man');
		$I->click('Misc');
		$I->see('Rivero soon became a sex symbol and');

			// Popup movie page

		$I->comment(Helper\Color::set('Checking Popup movie page', 'italic+bold+cyan'));
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_POPUP_FILM_URL );
		$I->waitPageLoad();
		$I->seeInPageSource("canonical");						# Meta tag
		$I->seeInPageSource("article:tag");					 	# Meta tag
		$I->seeInPageSource("lumiere-movies/assets/pics/favicon/favicon-16x16.png");	# Lumière favicon 16
		$I->seeInPageSource("lumiere-movies/assets/pics/favicon/favicon-32x32.png"); 	# Lumière favicon 32
		$I->seeInPageSource("lumiere-movies/assets/pics/favicon/apple-touch-icon.png"); # Lumière favicon Apple
		$I->seeInPageSource("lumiere-movies/assets/pics/favicon/site.webmanifest");	# Lumière webmanifest
		$I->seeInPageSource("lumiere_highslide_core_style-css"); 			# Highslide CSS
		$I->seeInPageSource("lumiere_style_main-css"); 					# Lumière main css
		$I->seeInPageSource("lumiere_highslide_core-js");				# Highslide JS
		$I->seeInPageSource("lumiere_highslide_options-js");				# Highslide Lumière Options
		$I->seeInPageSource("lumiere_scripts-js"); 					# Lumière main JS
		$I->seeInPageSource("lumiere_scripts-js-after");				# Lumière vars for main JS
		$I->seeInPageSource("lumiere_hide_show-js"); 					# hide/show script
		$I->click('Actors');
		$I->see('Ellen Burstyn');
		$I->click('Crew');
		$I->see('Christopher Nolan');
		$I->click('Plots');
		$I->see('a team of researchers, to find a new planet for humans.');
		$I->click('Misc');
		$I->see('Early in pre-production, Dr. Kip Thorne laid down tw');
	}

	/**
	 * Check if the change of layout styles in admin is reflection in the front end
	 *
	 * @before login
	 */
	public function checkStyleEdition(AcceptanceTester $I) {

		$I->comment(Helper\Color::set('Change layout', 'italic+bold+cyan'));

		$I->amOnPage( AcceptanceSettings::LUMIERE_MAIN_OPTIONS_URL );
		$I->waitPageLoad();
		
		// Try with selection black
		$I->scrollTo('#plainpages');
		$I->selectOption("form [name=imdb_imdbintotheposttheme]", "black");
		$I->click('#lumiere_update_main_settings');
		$I->comment(Helper\Color::set('[Action] Selection has been switched to "black"', 'italic+bold+cyan'));

		$I->waitPageLoad();
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_BASE_URL );
		$I->seeInPageSource("lum_results_frame_black");		// CSS for black layout.

		// Try with selection grey (default)
		$I->amOnPage( AcceptanceSettings::LUMIERE_MAIN_OPTIONS_URL );
		$I->waitPageLoad();
		$I->scrollTo('#plainpages');
		$I->selectOption("form [name=imdb_imdbintotheposttheme]", "grey");
		$I->click('#lumiere_update_main_settings');
		$I->comment(Helper\Color::set('[Action] Selection has been switched to "grey"', 'italic+bold+cyan'));

		$I->waitPageLoad();
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_BASE_URL );
		$I->seeInPageSource("lum_results_frame_grey");		// CSS for grey layout (default).
	}
	
	/**
	 * Accept a popup in the block editor if it exists -> avoid failure with the try
	 * Not in use
	 */
	private function getRidOfEditorPopup() {
		try {
			$I->seeInPageSource('"Welcome to the block editor":["Welcome to the block editor"]');
			$I->cancelPopup();
		} catch (Exception $e) {
			$I->comment('There was no poup, continuing...');
		}
	}
}
