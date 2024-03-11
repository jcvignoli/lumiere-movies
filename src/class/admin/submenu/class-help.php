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

use Lumiere\Tools\Utils;
use Lumiere\Admin\Admin_Menu;
use Exception;

/**
 * Display help explanations
 * @TODO refactorize and use templates
 */
class Help extends Admin_Menu {

	/**
	 * Pages name
	 */
	private const PAGES_NAMES = [
		'menu_first'    => 'admin-menu-first-part',
		'menu_submenu'  => 'help/admin-help-submenu',
		'menu_howto'    => 'help/admin-help-howto',
	];

	/**
	 * Paths to files to be read
	 * @var string
	 */
	private string $readmefile;
	private string $changelogfile;
	private string $acknowledgefile;

	/**
	 * HTML allowed for use of wp_kses()
	 */
	const ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS = [
		'i' => [],
		'strong' => [],
		'div' => [ 'class' => [] ],
		'b' => [],
		'a' => [
			'id' => [],
			'href' => [],
			'title' => [],
			'data-*' => [],
		],
		'font' => [
			'size' => [],
		],
		'blockquote' => [ 'class' => [] ],
		'br' => [],
	];

	/**
	 * Constructor
	 */
	protected function __construct() {

		// Construct parent class
		parent::__construct();

		// Build constants not in parent class
		$root = dirname( dirname( __DIR__ ) );
		$this->readmefile = plugin_dir_path( $root ) . 'README.txt';
		$this->changelogfile = plugin_dir_path( $root ) . 'CHANGELOG.md';
		$this->acknowledgefile = plugin_dir_path( $root ) . 'ACKNOWLEDGMENTS.md';

		// Add specific script for metaboxes
		//add_action('admin_enqueue_scripts', [$this, 'lumiere_help_extrascript' ]); # can't use add_action, call in parent class too late
		$this->lumiere_help_extrascript();

	}

	/**
	 * Display the layout
	 */
	public function display_help_layout(): void {

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
			[
				/** Add an array with vars to send in the template */
												$this->config_class->lumiere_pics_dir,
				$this->page_help,
				$this->page_help_support,
				$this->page_help_faqs,
				$this->page_help_changelog,
			],
			self::TRANSIENT_ADMIN,
		);

		// Changelog section
		if ( ( isset( $_GET['subsection'] ) ) && $_GET['subsection'] === 'changelog' ) {
			$this->display_changelog();

			// Faqs section
		} elseif ( isset( $_GET['subsection'] ) && ( $_GET['subsection'] === 'faqs' ) ) {
			$this->display_faqs();

			// Support section
		} elseif ( ( isset( $_GET['subsection'] ) ) && ( $_GET['subsection'] === 'support' ) ) {
			$this->display_support();

			// How to section
		} elseif ( ( isset( $_GET['subsection'] ) && $_GET['subsection'] === 'howto' ) || ! isset( $_GET['subsection'] ) ) {
			$this->include_with_vars(
				self::PAGES_NAMES['menu_howto'],
				[
					/** Add an array with vars to send in the template */
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
		$count_rows = 0;

		// If file doesn't exist, exit.
		if ( ! is_file( $this->readmefile ) ) {
			throw new Exception( 'File ' . $this->readmefile . ' has wrong permissions or does not exist' );
		}

		// Make sure we got right credentials to use $wp_filesystem.
		Utils::lumiere_wp_filesystem_cred( $this->readmefile );

		// Open the file.
		$faqfile = $wp_filesystem !== null ? $wp_filesystem->get_contents( $this->readmefile ) : '';
		?>

		<div class="lumiere_wrap">

			<div class="lumiere_title_options lumiere_border_shadow">
				<h3 id="layout" name="layout"><?php esc_html_e( 'Frequently asked questions', 'lumiere-movies' ); ?></h3>
			</div>

			<div id="lumiere_help_plb_faq" class="lumiere_border_shadow">
				<?php
				// Select FAQ section in readme file.
				$patterntitle = '/== Frequently Asked Questions ==(.*?)== Support ==/ms';
				preg_match( $patterntitle, $faqfile, $faqsection );

				// Split into array the section based upon '=' delimitors.
				$faqsectionarray = preg_split( '/=(.*?)=/', $faqsection[1], -1, PREG_SPLIT_DELIM_CAPTURE );
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

				echo "\n<ol>\n";

				foreach ( $faqsection_processed as $texte ) {
					if ( $count_rows % 2 === 1 ) { // uneven number -> title
						echo "\t\t\t\t\t\t<li class=\"titresection\">" . esc_html( $texte ) . "</li>\n";
						$count_rows++;
						continue;
					}
					// even number -> title
					echo "\t\t\t\t\t\t<div class=\"imdblt_padding_twenty\">" . wp_kses( str_replace( "\n\n", "\n", $texte ), self::ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS );
					echo "\t\t\t\t\t\t</div>\n";
					$count_rows++;
				}
				echo "\t\t\t\t\t</ol>\n";
				?>
			</div>
		</div>

		<?php
	}

	/**
	 * Display the changelog
	 */
	private function display_changelog(): void {

		/** Vars */
		global $wp_filesystem;
		$number = 0;

		// If file doesn't exist, exit.
		if ( ! is_file( $this->changelogfile ) ) {
			throw new Exception( 'File ' . $this->changelogfile . ' has wrong permissions or does not exist' );
		}

		// Make sure we got right credentials to use $wp_filesystem.
		Utils::lumiere_wp_filesystem_cred( $this->changelogfile );

		// Open the file (as an array).
		$changelogfile = $wp_filesystem !== null ? $wp_filesystem->get_contents_array( $this->changelogfile ) : '';
		?>

		<div class="lumiere_wrap">
			<div class="lumiere_title_options lumiere_border_shadow">
				<h3 id="layout" name="layout"><?php esc_html_e( 'Changelog', 'lumiere-movies' ); ?></h3>
			</div>

			<div class="lumiere_border_shadow helpdiv">
				<?php

				/**
				 * 1-replace **...** by strong and <i>.
				 * 2-replace links from (especially formated for WordPress website) changlog with regular html.
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

				if ( is_iterable( $changelogprocessed ) ) {
					foreach ( $changelogprocessed as $texte ) {
						if ( $number > '1' ) {
							// display text formatted
							/** @psalm-suppress PossiblyInvalidArgument -- Wrong, it's always string! */
							echo "\n\t\t\t\t\t\t" . wp_kses( str_replace( "\n", '', $texte ), self::ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS ) . '<br />';
						}
						$number++;
					}
				}
				?>

			</div>
		</div>

		<?php
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
			throw new Exception( 'File ' . $this->acknowledgefile . ' has wrong permissions or does not exist' );
		}

		// Make sure we got right credentials to use $wp_filesystem.
		Utils::lumiere_wp_filesystem_cred( $this->acknowledgefile );

		// Open the file (as an array).
		$acknowledgefile = $wp_filesystem !== null ? $wp_filesystem->get_contents_array( $this->acknowledgefile ) : '';
		?>

		<div class="lumiere_wrap">
			<div class="lumiere_title_options lumiere_border_shadow">
				<h3 id="layout" name="layout">
					<?php
					/* translators: %1$s and %2$s are HTML tags */
					echo wp_kses( sprintf( __( 'Two ways to support %1$sLumiere Movies%2$s plugin development', 'lumiere-movies' ), '<i>', '</i>' ), [ 'i' => [] ] ); ?>
				</h3>
			</div>
			
			<div class="lumiere_border_shadow helpdiv">

				<div class="titresection"><?php esc_html_e( 'Be supported!', 'lumiere-movies' ); ?></div>
			
					<?php esc_html_e( 'You will never believe there is so many ways to be supported. You can:', 'lumiere-movies' ); ?><br />

			<strong>1</strong>. <?php esc_html_e( 'visit', 'lumiere-movies' ); ?> <a href="<?php echo esc_attr( \Lumiere\Settings::IMDBHOMEPAGE ); ?>">Lumière website</a> <?php esc_html_e( 'to ask for help. ', 'lumiere-movies' ); ?><br />

			<strong>2</strong>. <?php esc_html_e( 'check the', 'lumiere-movies' ); ?> <a href="?page=lumiere_options&subsection=faqs"><?php esc_html_e( 'FAQs ', 'lumiere-movies' ); ?></a>.<br />

			<strong>3</strong>. <?php esc_html_e( 'check the', 'lumiere-movies' ); ?> <a href="?page=lumiere_options&subsection=howto"><?php esc_html_e( 'how to', 'lumiere-movies' ); ?></a>.<br />


				<div class="titresection"><?php esc_html_e( 'Support me!', 'lumiere-movies' ); ?></div>

				<?php esc_html_e( 'You will never believe there is so many ways to thank me. Yes, you can:', 'lumiere-movies' ); ?><br />
				<strong>1</strong>. <?php esc_html_e( 'pay whatever you want on', 'lumiere-movies' ); ?> <a href="https://www.paypal.me/jcvignoli">paypal <img src="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'paypal-donate.png' ); ?>" width="40px" class="paypal lumiere_valign_middle" /></a>.<br />
				<strong>2</strong>. <?php esc_html_e( 'vote on', 'lumiere-movies' ); ?> <a href="<?php echo esc_attr( \Lumiere\Settings::IMDBHOMEPAGE ); ?>"><?php esc_html_e( "Lumière's website", 'lumiere-movies' ); ?></a> <?php esc_html_e( 'or on', 'lumiere-movies' ); ?> <a href="<?php echo esc_attr( \Lumiere\Settings::LUMIERE_WORDPRESS ); ?>"><?php esc_html_e( "WordPress' website", 'lumiere-movies' ); ?></a>.<br />
				<strong>3</strong>. <?php esc_html_e( 'send as many bugfixes and propositions as you can on Lumiere Movies website.', 'lumiere-movies' ); ?><br />
				<strong>4</strong>. <?php esc_html_e( 'translate the plugin into your own language.', 'lumiere-movies' ); ?><br />
				<strong>5</strong>. <?php esc_html_e( 'help me to improve the plugin.', 'lumiere-movies' ); ?> <?php esc_html_e( 'Report at the development', 'lumiere-movies' ); ?> <a href="<?php echo esc_attr( \Lumiere\Settings::LUMIERE_GIT ); ?>">GIT</a>'s <?php esc_html_e( 'website', 'lumiere-movies' ); ?> <br />
				<strong>6</strong>. <?php esc_html_e( 'do a trackback, make some noise about this plugin!', 'lumiere-movies' ); ?><br />


				<div class="titresection"><?php esc_html_e( 'Credits:', 'lumiere-movies' ); ?></div>

				<?php
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
						'<br />',
						'<a href="${3}${5}" title="${7}">${2}</a>',
					];
					$acknowledgefile = preg_replace( $patterns, $replaces, $acknowledgefile ) ?? $acknowledgefile;
				}

				echo '<ul>';

				if ( is_iterable( $acknowledgefile ) ) {
					foreach ( $acknowledgefile as $texte ) {
						if ( $number > '1' ) {

							// display text formatted
							$texte_string = is_string( $texte ) ? $texte : '';
							echo "\t\t\t\t\t\t<li>" . wp_kses( str_replace( "\n", '', $texte_string ), self::ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS ) . "</li>\n";
						}
						$number++;
					}
				}
				echo "\t\t\t\t\t</ul>\n";
				?>
			</div>
		</div>

		<?php
	}

	/**
	 * Add extra scripts to this page only
	 */
	public function lumiere_help_extrascript (): void {

		wp_register_script(
			'lumiere_help_scripts',
			'',
			[
				'common',   // script needed for meta_boxes
				'wp-lists', // script needed for meta_boxes
				'postbox',   // script needed for meta_boxes
			],
			$this->config_class->lumiere_version,
			true
		);

		wp_enqueue_script( 'lumiere_help_scripts' );

		$lumiere_help_extrascript = "document.addEventListener('DOMContentLoaded', function () {
			if (jQuery('.if-js-closed')){
				// close postboxes that should be closed
				jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed');

				// postboxes
				postboxes.add_postbox_toggles('lumiere_help');
			}
		});";

		wp_add_inline_script( 'lumiere_help_scripts', $lumiere_help_extrascript );
	}
}

