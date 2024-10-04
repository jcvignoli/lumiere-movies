<?php declare( strict_types = 1 );
/**
 * Child class for displaying help sections.
 * Child of Admin_Menu
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */

namespace Lumiere\Admin\Submenu;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'You can not call directly this page' );
}

use Lumiere\Admin\Admin_Menu;
use Exception;

/**
 * Display help explanations
 *
 * @since 4.0.1 Using templates instead of having templates here
 */
class Help extends Admin_Menu {

	/**
	 * Pages name
	 */
	private const PAGES_NAMES = [
		'menu_first'    => 'admin-menu-first-part',
		'menu_submenu'  => 'help/admin-help-submenu',
		'menu_howto'    => 'help/admin-help-howto',
		'menu_faqs'    => 'help/admin-help-faqs',
		'menu_changelog'    => 'help/admin-help-changelog',
		'menu_support' => 'help/admin-help-support',
	];

	/**
	 * Paths to files to be read
	 * @var string
	 */
	private string $readmefile;
	private string $changelogfile;
	private string $acknowledgefile;

	/**
	 * Constructor
	 */
	protected function __construct() {

		// Construct parent class.
		parent::__construct();

		// Build file names with full path.
		$root = dirname( dirname( __DIR__ ) );
		$this->readmefile = plugin_dir_path( $root ) . 'README.txt';
		$this->changelogfile = plugin_dir_path( $root ) . 'CHANGELOG.md';
		$this->acknowledgefile = plugin_dir_path( $root ) . 'ACKNOWLEDGMENTS.md';
	}

	/**
	 * Display the layout
	 *
	 * @param \Lumiere\Admin\Cache_Tools $cache_tools_class Not utilised in this class, but needed in some other Submenu classes
	 * @param string $nonce nonce from Admin_Menu to be checked when doing $_GET checks
	 * @see \Lumiere\Admin\Admin_Menu::call_admin_subclass() Calls this method
	 */
	public function lum_submenu_start( \Lumiere\Admin\Cache_Tools $cache_tools_class, string $nonce ): void {

		do_action( 'lumiere_add_meta_boxes_help' );

		// First part of the menu.
		$this->include_with_vars(
			self::PAGES_NAMES['menu_first'],
			[ $this ], /** Add in an array all vars to send in the template */
			self::TRANSIENT_ADMIN,
		);

		// Always display the submenu.
		$this->include_with_vars(
			self::PAGES_NAMES['menu_submenu'],
			/** Add an array with vars to send in the template */
			[
				$this->config_class->lumiere_pics_dir,
				$this->page_help,
				$this->page_help_support,
				$this->page_help_faqs,
				$this->page_help_changelog,
			],
			self::TRANSIENT_ADMIN,
		);

		// Changelog section.
		if ( wp_verify_nonce( $nonce, 'check_display_page' ) > 0 && isset( $_GET['subsection'] ) && str_contains( $this->page_help_changelog, sanitize_text_field( wp_unslash( $_GET['subsection'] ) ) ) === true ) {
			$this->display_changelog();

			// Faqs section.
		} elseif ( isset( $_GET['subsection'] ) && str_contains( $this->page_help_faqs, sanitize_text_field( wp_unslash( $_GET['subsection'] ) ) ) === true && wp_verify_nonce( $nonce, 'check_display_page' ) > 0 ) {
			$this->display_faqs();

			// Support section.
		} elseif ( ( isset( $_GET['subsection'] ) ) && str_contains( $this->page_help_support, sanitize_text_field( wp_unslash( $_GET['subsection'] ) ) ) === true && wp_verify_nonce( $nonce, 'check_display_page' ) > 0 ) {
			$this->display_support();

			// How to section, default.
		} elseif ( ( isset( $_GET['subsection'] ) && str_contains( $this->page_help, sanitize_text_field( wp_unslash( $_GET['subsection'] ) ) ) === true ) || ! isset( $_GET['subsection'] ) && wp_verify_nonce( $nonce, 'check_display_page' ) > 0 ) {

			// Default.
			$this->include_with_vars(
				self::PAGES_NAMES['menu_howto'],
				/** Add an array with vars to send in the template */
				[
					$this->config_class->lumiere_pics_dir,
					$this->page_help,
					$this->page_help_support,
					$this->page_help_faqs,
					$this->page_help_changelog,
				],
				self::TRANSIENT_ADMIN,
			);
		}

	}

	/**
	 * Display the faqs
	 */
	private function display_faqs(): void {

		/** Vars */
		global $wp_filesystem;

		// If file doesn't exist, exit.
		if ( ! is_file( $this->readmefile ) ) {
			throw new Exception( 'File ' . esc_html( $this->readmefile ) . ' has wrong permissions or does not exist' );
		}

		// Make sure we got right credentials to use $wp_filesystem.
		$this->lumiere_wp_filesystem_cred( $this->readmefile ); // in trait Admin_General.

		// Open the file.
		$faqfile = $wp_filesystem !== null ? $wp_filesystem->get_contents( $this->readmefile ) : '';

		// Select FAQ section in readme file.
		$patterntitle = '/== Frequently Asked Questions ==(.*?)== Support ==/ms';
		preg_match( $patterntitle, $faqfile, $faqsection );

		// Split into array the section based upon '=' delimitors.
		$faqsectionarray = isset( $faqsection[1] ) ? preg_split( '/=(.*?)=/', $faqsection[1], -1, PREG_SPLIT_DELIM_CAPTURE ) : null;

		/**
		 * 1-replace links from (especially formated for WordPress website) readme with regular html.
		 * 2-replace ** with <i>
		 */
		$patterns = [
			'~(\\[{1}(.*?)\\]\()(https://)(([[:punct:]]|[[:alnum:]])*)( \"{1}(.*?)\"\))~',
			'~\*\*(.*?)\*\*~',
			'~`(.*)`~',
		];
		$replaces = [
			'<a href="${3}${4}" title="${7}">${2}</a>',
			'<i>${1}</i>',
			'<blockquote class="lumiere_bloquote_help">${1}</blockquote>',
		];
		$faqsection_replace = is_array( $faqsectionarray ) !== false ? preg_replace( $patterns, $replaces, $faqsectionarray ) : null;
		$faqsection_processed = $faqsection_replace ?? [];

		// Send the file text to the included file.
		$this->include_with_vars(
			self::PAGES_NAMES['menu_faqs'],
			/** Add an array with vars to send in the template */
			[
				$faqsection_processed,
			],
			self::TRANSIENT_ADMIN,
		);
	}

	/**
	 * Display the changelog
	 */
	private function display_changelog(): void {

		/** Vars */
		global $wp_filesystem;

		// If file doesn't exist, exit.
		if ( ! is_file( $this->changelogfile ) ) {
			throw new Exception( 'File ' . esc_html( $this->changelogfile ) . ' has wrong permissions or does not exist' );
		}

		// Make sure we got right credentials to use $wp_filesystem.
		$this->lumiere_wp_filesystem_cred( $this->changelogfile ); // in trait Admin_General.

		// Open the file (as an array).
		$changelogfile = $wp_filesystem !== null ? $wp_filesystem->get_contents_array( $this->changelogfile ) : '';

		/**
		 * 1-replace version number with <div>'s
		 * 2-replace **...** with <strong> and <i>
		 * 3-replace links from (formated for WordPress website) changelog with regular html
		 */
		$patterns = [
			'~(v\.)(\d)(.*)~',
			'~(\*\s\[)(.*?)(\])~',
			'~(\\[{1}(.*?)\\]\()(https://)(([[:punct:]]|[[:alnum:]])*)( \"{1}(.*?)\"\))~',
		];
		$replaces = [
			'<div class="titresection">version ${2}${3}</div>',
			'<strong><i>${2}</i></strong>',
			'<a href="${3}${4}" title="${7}">${2}</a>',
		];

		$changelogprocessed = preg_replace( $patterns, $replaces, $changelogfile ) ?? [];

		// Send the file text to the included file.
		$this->include_with_vars(
			self::PAGES_NAMES['menu_changelog'],
			/** Add an array with vars to send in the template */
			[
				$changelogprocessed,
			],
			self::TRANSIENT_ADMIN,
		);
	}

	/**
	 * Display the support
	 */
	private function display_support(): void {

		/** Vars */
		global $wp_filesystem;
		$number = 0;

		// If file doesn't exist, exit.
		if ( ! is_file( $this->acknowledgefile ) ) {
			throw new Exception( 'File ' . esc_html( $this->acknowledgefile ) . ' has wrong permissions or does not exist' );
		}

		// Make sure we got right credentials to use $wp_filesystem.
		$this->lumiere_wp_filesystem_cred( $this->acknowledgefile );

		// Open the file (as an array).
		$acknowledgefile = $wp_filesystem !== null ? $wp_filesystem->get_contents_array( $this->acknowledgefile ) : '';

		/**
		 * 1-replace # by div.
		 * 2-remove ** **.
		 * 3-replace \n by br.
		 * 4-replace links from (specially formated for WordPress website) readme with casual html.
		 */
		if ( is_array( $acknowledgefile ) === true ) {
			$patterns = [
				'~\# (.*)~',
				'~\*\*(.*)\*\*~',
				'~\n~',
				'~(\\[{1}(.*?)\\]\()(htt(p|ps)://)(([[:punct:]]|[[:alnum:]])*)( \"{1}(.*?)\"\))~',
			];
			$replaces = [
				'<div><strong>${1}</strong></div>',
				'${1}',
				'<br>',
				'<a href="${3}${5}" title="${7}">${2}</a>',
			];
			$acknowledgefile = preg_replace( $patterns, $replaces, $acknowledgefile );
		}

		// Send the file text to the included file.
		$this->include_with_vars(
			self::PAGES_NAMES['menu_support'],
			/** Add an array with vars to send in the template */
			[
				$this->page_help,
				$this->page_help_faqs,
				$acknowledgefile,
				$this->config_class->lumiere_pics_dir,
			],
			self::TRANSIENT_ADMIN,
		);
	}
}

