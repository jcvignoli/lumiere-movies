<?php

declare(strict_types=1);

namespace Tests\Support;

use Tests\Support\Helper\AcceptanceSettings;

/**
 * Test if pages that do not include Lumière tools are correctly displayed
 */
class NormalPagesCest {

	public function _before(AcceptanceTester $I){
		$I->comment( '#Code _before#' );
	}

	public function _after(AcceptanceTester $I){
		$I->comment( '#Code _after#' );
	}

	/** 
	 * Login to Wordpress
	 * Trait function to keep the cookie active
	 */
	private function login(AcceptanceTester $I) {
		$I->login_universal($I);
	}

	/**
	 * Prepare the options for the search
	 *
	 * @before login
	 */
	public function prepare(AcceptanceTester $I) {
		// Activate IRP
		$I->amOnPluginsPage();
		$I->maybeActivatePlugin('intelly-related-posts');
		$I->amOnPage( 'wp-admin/options-general.php?page=intelly-related-posts' );
		$I->CustomActivateCheckbox('input[name="irpActive"]', '/html/body/div[1]/div[2]/div[2]/div[1]/div[2]/form/input[4]' );
		// Activate Lucky TOC Plugin
		$I->amOnPluginsPage();
		$I->maybeActivatePlugin('luckywp-table-of-contents');
		// Activate Quotes Plugin
		$I->maybeActivatePlugin('quotes-llama');
		// Activate Footnotes Plugin
		$I->maybeActivatePlugin('footnotes-made-easy');
		// Activate Like/Dislike Plugin
		$I->maybeActivatePlugin('posts-like-dislike');
	}

	/**
	 * Check if search page can be displayed from edit page (metabox)
	 */
	public function checkNormalPage(AcceptanceTester $I) {

		$I->amOnPage( AcceptanceSettings::TESTING_NORMAL_PAGE );
		
		// No fatal error
		$I->customSeeResponseCodeIs( $I->getCustomBaseUrl() . AcceptanceSettings::TESTING_NORMAL_PAGE ); // Response code 200 by default
		$I->dontSeeElement('body#error-page');

		// Check if Plugins can be seen in posts
		$I->seeInPageSource( '<!-- INLINE RELATED POSTS' ); // IRP plugin
		$I->seeInPageSource( 'class="footnote-link footnote-identifier-link"' ); // Lucky TOC plugin
		$I->seeInPageSource( '<b class="lwptoc_title">Contents</b>' ); // Lucky TOC plugin
		$I->seeInPageSource( '<span class="quotes-llama-widget-more">' ); // Quotes plugin
		$I->seeInPageSource( '<ol class="footnotes"><li id="footnote_0_2915" class="footnote">' ); // Footnotes plugin
		$I->seeInPageSource( '<div class="pld-like-wrap' ); // Like/dislike plugin
		
		// Check if WP normal behaviour is working
		$I->seeInPageSource( '</h3><!-- .related-post-title -->' ); // related posts
		$I->seeInPageSource( 'Laisser un commentaire' ); // comments
		$I->seeInPageSource(  'Cerro Mongón, ou comment j\'ai découvert des ruines au' ); // Sidebar
	}

}
