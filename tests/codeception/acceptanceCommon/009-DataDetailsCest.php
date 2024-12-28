<?php

# Class meant to test activation/deactivation of Data details (a WebDriver is needed for JS execution)

class DataDetailsCest {

	public function _before(AcceptanceRemoteTester $I){
		$I->comment(\Helper\Color::set("#Code _before#", "italic+bold+cyan"));

	}

	public function _after(AcceptanceRemoteTester $I){
		$I->comment(\Helper\Color::set("#Code _after#", "italic+bold+cyan"));
	}

	/**
	 * Login to Wordpress
	 * Trait function to keep the cookie active
	 */
	private function login(AcceptanceRemoteTester $I) {
		$I->login_universal($I);
	}

	/**
	 * Check if data details deactivation works
	 *
	 * @before login
	 */
	public function checkDataDeactivation(AcceptanceRemoteTester $I) {

		$I->comment(\Helper\Color::set("Check that deactivated data details are not seen", "italic+bold+cyan"));

		// Activate $item in 'what to display'
		# first row
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#taxodetails');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetactor_yes', '#lumiere_update_data_settings' );
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#taxodetails');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetalsoknow_yes', '#lumiere_update_data_settings' );
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#taxodetails');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetcolor_yes', '#lumiere_update_data_settings' );
		# second row
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetactor_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetcomposer_yes', '#lumiere_update_data_settings' );
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetactor_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetcountry_yes', '#lumiere_update_data_settings' );
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetactor_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetcreator_yes', '#lumiere_update_data_settings' );
		# third row
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetcomposer_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetdirector_yes', '#lumiere_update_data_settings' );
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetcomposer_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetgenre_yes', '#lumiere_update_data_settings' );
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetcomposer_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetgoof_yes', '#lumiere_update_data_settings' );
		# fourth row
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetdirector_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetkeyword_yes', '#lumiere_update_data_settings' );
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetdirector_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetlanguage_yes', '#lumiere_update_data_settings' );
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetdirector_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetofficialsites_yes', '#lumiere_update_data_settings' );
		# fifth row
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetkeyword_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetplot_yes', '#lumiere_update_data_settings' );
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetkeyword_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetproducer_yes', '#lumiere_update_data_settings' );
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetkeyword_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetprodcompany_yes', '#lumiere_update_data_settings' );
		# sixth row
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetplot_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetquote_yes', '#lumiere_update_data_settings' );
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetplot_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetrating_yes', '#lumiere_update_data_settings' );
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetplot_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetruntime_yes', '#lumiere_update_data_settings' );
		# seventh row
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetquote_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetsoundtrack_yes', '#lumiere_update_data_settings' );
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetquote_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetsource_yes', '#lumiere_update_data_settings' );
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetquote_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgettagline_yes', '#lumiere_update_data_settings' );
		# eighth row
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetsoundtrack_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgettrailer_yes', '#lumiere_update_data_settings' );
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetsoundtrack_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetwriter_yes', '#lumiere_update_data_settings' );
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetsoundtrack_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetyear_yes', '#lumiere_update_data_settings' );

		// See if data is not available
		$I->comment(\Helper\Color::set("Check if data is available", "italic+bold+cyan"));
		$I->amOnPage('/en/2021/test-codeception/');
		$I->dontSee('Directors');
		$I->dontSee('Countries');
		$I->dontSee('Actors');
		$I->dontSee('Cinematographers');
		$I->dontSee('Rating');
		$I->dontSee('Language');
		$I->dontSee('Genre');
		$I->dontsee('Runtime');
		$I->dontsee('minutes');
		$I->dontSee('Writers');
		$I->dontSee('Producers');
		$I->dontSee('Keywords');
		$I->dontSee('queen');
		$I->dontSee('Production companies');
		$I->dontSee('Plots');
		$I->dontSee('continent of Westeros');
		$I->dontSee('where yet another monolith is found');
		$I->dontSee('Taglines');
		$I->dontSee('Winter is coming');
		$I->dontSee('Barry Lyndon (Egypt)');
		$I->dontSee('Color');
		$I->dontSee('Also known as');
		$I->dontSee('Game of Thrones (Argentina)');
		$I->dontSee('Composer');
		$I->dontSee('Ramin Djawadi');
		$I->dontSee('Soundtracks');
		$I->dontSeeInSource('Lux aeterna  <i>(1966)</i> <i>Music by György Ligeti</i>');
		$I->dontSee('Trailers');
		$I->dontSee('Main title (uncredited) Written and Performed by Ramin Djawadi');
		$I->dontSee('Official websites');
		$I->dontSee('Official Facebook, Official Instagram, Official Site');
		$I->dontSee('Bighead Littlehead');
		$I->dontSee('Grok! Television');
		$I->dontSee('1 episode in 2011');
		$I->dontSee('Jane Espenson');
		$I->dontSee('Seven Kingdoms');
		$I->dontSee('votes');
		$I->dontSee('D.B. Weiss');
		$I->dontSee('Theon Greyjoy');
		$I->dontSee('Alfie Allen');
		$I->dontSee('United Kingdom');
		$I->dontSee('Matt Shakman');
		$I->dontSee('Goofs');
		$I->dontSee('throughout the film');
		$I->dontSee('Barry joins the British Army to fight in the');

	}

	/**
	 * Check if data details activation works
	 *
	 * @before login
	 */
	public function checkDataActivation(AcceptanceRemoteTester $I) {

		$I->comment(\Helper\Color::set("Check that activated data details are seen", "italic+bold+cyan"));

		// Activate $item in 'what to display'
		# first row
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#taxodetails');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetactor_yes', '#lumiere_update_data_settings' );
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#taxodetails');
		$I->fillField('#imdb_imdbwidgetactornumber', '10');
		$I->click('#lumiere_update_data_settings');
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#taxodetails');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetalsoknow_yes', '#lumiere_update_data_settings' );
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#taxodetails');
		$I->fillField('#imdb_imdbwidgetalsoknownumber', '10');
		$I->click('#lumiere_update_data_settings');
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#taxodetails');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetcolor_yes', '#lumiere_update_data_settings' );
		# second row
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetactor_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetcomposer_yes', '#lumiere_update_data_settings' );
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetactor_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetcountry_yes', '#lumiere_update_data_settings' );
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetactor_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetcreator_yes', '#lumiere_update_data_settings' );
		# third row
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetcomposer_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetdirector_yes', '#lumiere_update_data_settings' );
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetcomposer_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetgenre_yes', '#lumiere_update_data_settings' );
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetcomposer_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetgoof_yes', '#lumiere_update_data_settings' );
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetcomposer_yes');
		$I->fillField('#imdb_imdbwidgetgoofnumber', '2');
		$I->click('#lumiere_update_data_settings');
		# fourth row
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetdirector_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetkeyword_yes', '#lumiere_update_data_settings' );
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetdirector_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetlanguage_yes', '#lumiere_update_data_settings' );
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetdirector_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetofficialsites_yes', '#lumiere_update_data_settings' );
		# fifth row
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetkeyword_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetplot_yes', '#lumiere_update_data_settings' );
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetcomposer_yes');
		$I->fillField('#imdb_imdbwidgetplotnumber', '5');
		$I->click('#lumiere_update_data_settings');
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetkeyword_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetproducer_yes', '#lumiere_update_data_settings' );
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetcomposer_yes');
		$I->fillField('#imdb_imdbwidgetproducernumber', '3');
		$I->click('#lumiere_update_data_settings');
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetkeyword_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetprodcompany_yes', '#lumiere_update_data_settings' );
		# sixth row
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetplot_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetquote_yes', '#lumiere_update_data_settings' );
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetplot_yes');
		$I->fillField('#imdb_imdbwidgetquotenumber', '2');
		$I->click('#lumiere_update_data_settings');
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetplot_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetrating_yes', '#lumiere_update_data_settings' );
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetplot_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetruntime_yes', '#lumiere_update_data_settings' );
		# seventh row
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetquote_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetsoundtrack_yes', '#lumiere_update_data_settings' );
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetquote_yes');
		$I->fillField('#imdb_imdbwidgetsoundtracknumber', '3');
		$I->click('#lumiere_update_data_settings');
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetquote_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetsource_yes', '#lumiere_update_data_settings' );
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetquote_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgettagline_yes', '#lumiere_update_data_settings' );
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgettrailer_yes');
		$I->fillField('#imdb_imdbwidgettaglinenumber', '5');
		$I->click('#lumiere_update_data_settings');
		# eighth row
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgettrailer_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgettrailer_yes', '#lumiere_update_data_settings' );
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgettrailer_yes');
		$I->fillField('#imdb_imdbwidgettrailernumber', '2');
		$I->click('#lumiere_update_data_settings');
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetwriter_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetwriter_yes', '#lumiere_update_data_settings' );
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL);
		$I->scrollTo('#imdb_imdbwidgetyear_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetyear_yes', '#lumiere_update_data_settings' );

		// See if data is available
		$I->comment(\Helper\Color::set("Check if data is available", "italic+bold+cyan"));
		$I->amOnPage('/en/2021/test-codeception/');
		$I->see('Directors');
		$I->see('Countries');
		$I->see('Actors');
		$I->see('Cinematographers');
		$I->see('Game of Thrones');
		$I->see('2011');
		$I->see('Rating');
		$I->see('Language');
		$I->see('Genre');
		$I->see('Writers');
		$I->see('Producers');
		$I->see('Keywords');
		$I->see('queen');
		$I->see('Production companies');
		$I->see('Plots');
		$I->see('continent of Westeros');
		$I->see('where yet another monolith is found');
		$I->see('Taglines');
		$I->see('Winter is coming');
		$I->see('Color');
		$I->see('Also known as');
		$I->see('Game Of Thrones (Argentina)');
		$I->see('Composer');
		$I->see('Runtime');
		$I->see('minutes');
		$I->see('Barry Lyndon (Egypt)');
		$I->see('Ramin Djawadi');
		$I->see('Soundtracks');
		$I->seeInSource('Lux aeterna  <i>(1966)</i> <i>Music by György Ligeti</i>');
		$I->see('Trailers');
		$I->see('Main title (uncredited) Written and Performed by Ramin Djawadi');
		$I->see('Official websites');
		$I->see('Official Facebook, Official Instagram, Official Site');
		$I->see('Bighead Littlehead');
		$I->see('Grok! Television');
		$I->see('1 episode in 2011');
		$I->see('Jane Espenson');
		$I->see('Seven Kingdoms');
		$I->see('votes');
		$I->see('D.B. Weiss');
		$I->see('Theon Greyjoy');
		$I->see('Alfie Allen');
		$I->see('United Kingdom');
		$I->see('Matt Shakman');
		$I->see('Goofs');
		$I->see('throughout the film');
		$I->see('Barry joins the British Army to fight in the');
	}
}
