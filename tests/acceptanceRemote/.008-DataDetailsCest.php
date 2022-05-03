<?php

# Class meant to test remote wordpress install (a WebDriver is needed for JS execution)

class DataDetailsCest {

	public function _before(AcceptanceRemoteTester $I){

		$I->comment(\Helper\Color::set("#Code _before#", "italic+bold+cyan"));

	}

	public function _after(AcceptanceRemoteTester $I){

		$I->comment(\Helper\Color::set("#Code _after#", "italic+bold+cyan"));

	}

	/** Login to Wordpress
	 *  Trait function to keep the cookie active
	 *
	 */
	private function login(AcceptanceRemoteTester $I) {

		$I->login_universal($I);

	}

	/** Check if data details deactivation works
	 *
	 * @before login
	 *
	 */
	public function checkDataDeactivation(AcceptanceRemoteTester $I) {

		$I->wantTo(\Helper\Color::set("Check that deactivated data details are not seen", "italic+bold+cyan"));

		// Activate $item in 'what to display'
		# first row
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#taxodetails');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetactor_yes', '#update_imdbwidgetSettings' );
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#taxodetails');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetalsoknow_yes', '#update_imdbwidgetSettings' );
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#taxodetails');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetcolor_yes', '#update_imdbwidgetSettings' );
		# second row
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetactor_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetcomposer_yes', '#update_imdbwidgetSettings' );
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetactor_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetcountry_yes', '#update_imdbwidgetSettings' );
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetactor_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetcreator_yes', '#update_imdbwidgetSettings' );
		# third row
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetcomposer_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetdirector_yes', '#update_imdbwidgetSettings' );
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetcomposer_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetgenre_yes', '#update_imdbwidgetSettings' );
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetcomposer_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetgoof_yes', '#update_imdbwidgetSettings' );
		# fourth row
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetdirector_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetkeyword_yes', '#update_imdbwidgetSettings' );
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetdirector_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetlanguage_yes', '#update_imdbwidgetSettings' );
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetdirector_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetofficialsites_yes', '#update_imdbwidgetSettings' );
		# fifth row
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetkeyword_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetplot_yes', '#update_imdbwidgetSettings' );
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetkeyword_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetproducer_yes', '#update_imdbwidgetSettings' );
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetkeyword_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetprodcompany_yes', '#update_imdbwidgetSettings' );
		# sixth row
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetplot_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetquote_yes', '#update_imdbwidgetSettings' );
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetplot_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetrating_yes', '#update_imdbwidgetSettings' );
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetplot_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetruntime_yes', '#update_imdbwidgetSettings' );
		# seventh row
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetquote_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetsoundtrack_yes', '#update_imdbwidgetSettings' );
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetquote_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetsource_yes', '#update_imdbwidgetSettings' );
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetquote_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgettagline_yes', '#update_imdbwidgetSettings' );
		# eighth row
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetsoundtrack_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgettrailer_yes', '#update_imdbwidgetSettings' );
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetsoundtrack_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetwriter_yes', '#update_imdbwidgetSettings' );
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetsoundtrack_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetyear_yes', '#update_imdbwidgetSettings' );

		// See if data is not available
		$I->comment(\Helper\Color::set("Check if data is available", "italic+bold+cyan"));
		$I->amOnPage('/2021/test-codeception/');
		$I->dontSee('Directors');
		$I->dontSee('Countries');
		$I->dontSee('Actors');
		$I->dontSee('Creators');
		$I->dontSee('Game of Thrones');
		$I->dontSee('2011');
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
		$I->dontSee('Quotes');
		$I->dontSee('you win or you die');
		$I->dontSee('Taglines');
		$I->dontSee('Five Kings');
		$I->dontSee('Color');
		$I->dontSee('Also known as');
		$I->dontSee('English title');
		$I->dontSee('Composer');
		$I->dontSee('Ramin Djawadi');
		$I->dontSee('Soundtracks');
		$I->dontSee('performer');
		$I->dontSee('Trailers');
		$I->dontSee('Episode 6 Teaser');
		$I->dontSee('Official websites');
		$I->dontSee('Official YouTube channel');
		$I->dontSee('Bighead Littlehead');
		$I->dontSee('Grok! Television');
		$I->dontSee('1 episode, 2011');
		$I->dontSee('Jane Espenson');
		$I->dontSee('Fantasy');
		$I->dontSee('votes');
		$I->dontSee('D.B. Weiss');
		$I->dontSee('Theon Greyjoy');
		$I->dontSee('Alfie Allen');
		$I->dontSee('United Kingdom');
		$I->dontSee('Matt Shakman');
		$I->dontSee('Goofs');
		$I->dontSee('throughout the film');
		$I->dontSee('to believe me but');

	}

	/** Check if data details activation works
	 *
	 * @before login
	 *
	 */
	public function checkDataActivation(AcceptanceRemoteTester $I) {

		$I->wantTo(\Helper\Color::set("Check that activated data details are seen", "italic+bold+cyan"));

		// Activate $item in 'what to display'
		# first row
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#taxodetails');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetactor_yes', '#update_imdbwidgetSettings' );
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#taxodetails');
		$I->fillField('#imdb_imdbwidgetactornumber', '10');
		$I->click('#update_imdbwidgetSettings');
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#taxodetails');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetalsoknow_yes', '#update_imdbwidgetSettings' );
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#taxodetails');
		$I->fillField('#imdb_imdbwidgetalsoknownumber', '10');
		$I->click('#update_imdbwidgetSettings');
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#taxodetails');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetcolor_yes', '#update_imdbwidgetSettings' );
		# second row
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetactor_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetcomposer_yes', '#update_imdbwidgetSettings' );
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetactor_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetcountry_yes', '#update_imdbwidgetSettings' );
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetactor_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetcreator_yes', '#update_imdbwidgetSettings' );
		# third row
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetcomposer_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetdirector_yes', '#update_imdbwidgetSettings' );
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetcomposer_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetgenre_yes', '#update_imdbwidgetSettings' );
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetcomposer_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetgoof_yes', '#update_imdbwidgetSettings' );
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetcomposer_yes');
		$I->fillField('#imdb_imdbwidgetgoofnumber', '2');
		$I->click('#update_imdbwidgetSettings');
		# fourth row
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetdirector_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetkeyword_yes', '#update_imdbwidgetSettings' );
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetdirector_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetlanguage_yes', '#update_imdbwidgetSettings' );
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetdirector_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetofficialsites_yes', '#update_imdbwidgetSettings' );
		# fifth row
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetkeyword_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetplot_yes', '#update_imdbwidgetSettings' );
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetcomposer_yes');
		$I->fillField('#imdb_imdbwidgetplotnumber', '5');
		$I->click('#update_imdbwidgetSettings');
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetkeyword_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetproducer_yes', '#update_imdbwidgetSettings' );
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetcomposer_yes');
		$I->fillField('#imdb_imdbwidgetproducernumber', '3');
		$I->click('#update_imdbwidgetSettings');
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetkeyword_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetprodcompany_yes', '#update_imdbwidgetSettings' );
		# sixth row
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetplot_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetquote_yes', '#update_imdbwidgetSettings' );
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetplot_yes');
		$I->fillField('#imdb_imdbwidgetquotenumber', '2');
		$I->click('#update_imdbwidgetSettings');
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetplot_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetrating_yes', '#update_imdbwidgetSettings' );
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetplot_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetruntime_yes', '#update_imdbwidgetSettings' );
		# seventh row
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetquote_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetsoundtrack_yes', '#update_imdbwidgetSettings' );
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetquote_yes');
		$I->fillField('#imdb_imdbwidgetsoundtracknumber', '3');
		$I->click('#update_imdbwidgetSettings');
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetquote_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetsource_yes', '#update_imdbwidgetSettings' );
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetquote_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgettagline_yes', '#update_imdbwidgetSettings' );
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetquote_yes');
		$I->fillField('#imdb_imdbwidgettaglinenumber', '5');
		$I->click('#update_imdbwidgetSettings');
		# eighth row
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetsoundtrack_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgettrailer_yes', '#update_imdbwidgetSettings' );
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetsoundtrack_yes');
		$I->fillField('#imdb_imdbwidgettrailernumber', '2');
		$I->click('#update_imdbwidgetSettings');
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetsoundtrack_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetwriter_yes', '#update_imdbwidgetSettings' );
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetsoundtrack_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetyear_yes', '#update_imdbwidgetSettings' );

		// See if data is available
		$I->comment(\Helper\Color::set("Check if data is available", "italic+bold+cyan"));
		$I->amOnPage('/2021/test-codeception/');
		$I->see('Directors');
		$I->see('Countries');
		$I->see('Actors');
		$I->see('Creators');
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
		$I->see('Quotes');
		$I->see('you win or you die');
		$I->see('Taglines');
		$I->see('Five Kings');
		$I->see('Color');
		$I->see('Also known as');
		$I->see('English title');
		$I->see('Composer');
		$I->see('Runtime');
		$I->see('minutes');
		$I->see('Ramin Djawadi');
		$I->see('Soundtracks');
		$I->see('performer');
		$I->see('Trailers');
		$I->see('Episode 6 Teaser');
		$I->see('Official websites');
		$I->see('Official YouTube channel');
		$I->see('Bighead Littlehead');
		$I->see('Grok! Television');
		$I->see('1 episode, 2011');
		$I->see('Jane Espenson');
		$I->see('Fantasy');
		$I->see('votes');
		$I->see('D.B. Weiss');
		$I->see('Theon Greyjoy');
		$I->see('Alfie Allen');
		$I->see('United Kingdom');
		$I->see('Matt Shakman');
		$I->see('Goofs');
		$I->see('throughout the film');
		$I->see('to believe me but');

	}
}




