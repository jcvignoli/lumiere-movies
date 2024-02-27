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
use Exception;

/**
 * Display help explainations
 */
class Help extends \Lumiere\Admin\Admin_Menu {

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
		'b' => [],
		'a' => [
			'id' => true,
			'href' => true,
			'title' => true,
			'data-*' => true,
		],
		'font' => [
			'size' => true,
		],
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
		$this->include_with_vars( 'admin-menu-first-part', [ $this ] /** Add in an array all vars to send in the template */ );

		echo "\n\t" . '<div id="poststuff">';

		// Always display the menu
		if ( isset( $_GET['page'] ) && $_GET['page'] === 'lumiere_options_help' ) {
			$this->display_menu();
		}

		// Changelog section
		if ( ( isset( $_GET['subsection'] ) ) && $_GET['subsection'] === 'changelog' ) {
			echo "\n\t" . '<br clear="both" />';
			echo "\n\t\t" . '<div class="lumiere_border_shadow">';
			$this->display_changelog();
			echo "\n\t\t" . '</div>';
			echo "\n\t" . '<br clear="both" />';
			// Faqs section
		} elseif ( isset( $_GET['subsection'] ) && ( $_GET['subsection'] === 'faqs' ) ) {
			echo "\n\t" . '<br clear="both" />';
			echo "\n\t\t" . '<div class="lumiere_border_shadow">';
			$this->display_faqs();
			echo "\n\t\t" . '</div>';
			echo "\n\t" . '<br clear="both" />';
			// Support section
		} elseif ( ( isset( $_GET['subsection'] ) ) && ( $_GET['subsection'] === 'support' ) ) {
			echo "\n\t" . '<br clear="both" />';
			echo "\n\t\t" . '<div class="lumiere_border_shadow">';
			$this->display_support();
			echo "\n\t\t" . '</div>';
			echo "\n\t" . '<br clear="both" />';
			// How to section
		} elseif ( ( isset( $_GET['subsection'] ) && $_GET['subsection'] === 'howto' ) || ! isset( $_GET['subsection'] ) ) {
			$this->display_howto();
		}

		echo "\n\t" . '</div>';
	}

	/**
	 * Display how to with help sections
	 */
	private function display_howto(): void {
		?>
	<br />

	<div class="intro_cache">
		<?php esc_html_e( 'This section covers the three main ways to display movie data and some related options in Lumière.', 'lumiere-movies' ); ?>
		<br /><br />
		<?php esc_html_e( 'Lumiere Movies is a plugin under intense development; this help section might be innacurate with regard to the latest functions. Main functions of the plugin are explained below, aiming to be as much user-friendly as possible.', 'lumiere-movies' ); ?>
	</div>

	<div class="imdblt_double_container">

		<div class="postbox-container imdblt_double_container_content lumiere_flex_container_content_fourtyfive">
			<?php
				$this->explain_popup();
				$this->explain_inside_post();
				$this->explain_widget();
				$this->explain_addsearchform();
			?>
		</div>

		<div class="postbox-container imdblt_double_container_content lumiere_flex_container_content_fourtyfive">
			<?php
				$this->explain_keepcss();
				$this->explain_taxonomy();
				$this->explain_autowidget();
				$this->explain_searchoptions();
			?>
		</div>

	</div>
		<?php
	}

	/**
	 * Display the menu
	 */
	private function display_menu (): void { ?>
<div id="tabswrap">
	<div class="imdblt_double_container lumiere_padding_five">

		<div class="lumiere_flex_auto lumiere_align_center"><img src="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'menu/admin-help-howto.png' ); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e( 'How to use Lumiere Movies', 'lumiere-movies' ); ?>" href="<?php echo esc_url( $this->page_general_help ); ?>"><?php esc_html_e( 'How to', 'lumiere-movies' ); ?></a></div>

		<div class="lumiere_flex_auto lumiere_align_center"><img src="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'menu/admin-help-faq.png' ); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e( 'Frequently asked questions', 'lumiere-movies' ); ?>" href="<?php echo esc_url( $this->page_general_help . '&subsection=faqs' ); ?>"><?php esc_html_e( 'FAQs', 'lumiere-movies' ); ?></a></div>

		<div class="lumiere_flex_auto lumiere_align_center"><img src="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'menu/admin-help-changelog.png' ); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e( "What's new", 'lumiere-movies' ); ?>" href="<?php echo esc_url( $this->page_general_help . '&subsection=changelog' ); ?>"><?php esc_html_e( 'Changelog', 'lumiere-movies' ); ?></a></div>

		<div class="lumiere_flex_auto lumiere_align_center"><img src="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'menu/admin-help-support.png' ); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e( 'Get support and support me', 'lumiere-movies' ); ?>" href="<?php echo esc_url( $this->page_general_help . '&subsection=support' ); ?>"><?php esc_html_e( 'Support, donate & credits', 'lumiere-movies' ); ?></a></div>

	</div>
</div>
		<?php
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

		<div id="lumiere_help_plb_faq">
			<h3 class="hndle"><?php esc_html_e( 'Frequently asked questions', 'lumiere-movies' ); ?></h3>
			<div class="inside">
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
				];
				$replaces = [
					'<a href="${3}${4}" title="${7}">${2}</a>',
					'<i>${1}</i>',
				];
				$faqsection_replace = is_array( $faqsectionarray ) !== false ? preg_replace( $patterns, $replaces, $faqsectionarray ) : null;
				$faqsection_processed = $faqsection_replace ?? [];

				echo "\n<ol>\n";

				foreach ( $faqsection_processed as $texte ) {
					if ( $count_rows % 2 === 1 ) { // uneven number -> title
						echo "\t\t\t\t\t\t<li><strong>" . esc_html( $texte ) . "</strong></li>\n";
						$count_rows++;
						continue;
					}
					// even number -> title
					echo "\t\t\t\t\t\t<div class='imdblt_padding_twenty'>" . wp_kses( str_replace( "\n\n", "\n", $texte ), self::ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS )
							. "\t\t\t\t\t\t</div>\n";
					$count_rows++;
				}
				echo "\t\t\t\t\t</ol>\n";
				?>			</div>
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

		<h3 class="hndle"><?php esc_html_e( 'Changelog', 'lumiere-movies' ); ?></h3>

		<div class="inside">

			<div class="helpdiv">
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
				'<br /><font size=\'+0.1\'><strong>version ${2}${3}</strong></font>',
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
			echo "\n\t\t\t\t\t<br />";
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

		<h3 class="hndle" id="help_support" name="help_support"><?php esc_html_e( 'Two ways to support ', 'lumiere-movies' );
		echo ' <strong>';
		esc_html_e( 'Lumiere Movies', 'lumiere-movies' );
		echo '</strong> ';
		esc_html_e( 'plugin development', 'lumiere-movies' ); ?></h3>

		<h3 class="hndle"><?php esc_html_e( 'Be supported!', 'lumiere-movies' ); ?></h3>

		<div class="helpdiv-noborderimage">

			<?php esc_html_e( 'You will never believe there is so many ways to be supported. You can:', 'lumiere-movies' ); ?><br />

	<strong>1</strong>. <?php esc_html_e( 'visit', 'lumiere-movies' ); ?> <a href="<?php echo esc_attr( \Lumiere\Settings::IMDBHOMEPAGE ); ?>">Lumière website</a> <?php esc_html_e( 'to ask for help. ', 'lumiere-movies' ); ?><br />

	<strong>2</strong>. <?php esc_html_e( 'check the', 'lumiere-movies' ); ?> <a href="?page=lumiere_options&subsection=faqs"><?php esc_html_e( 'FAQs ', 'lumiere-movies' ); ?></a>.<br />

	<strong>3</strong>. <?php esc_html_e( 'check the', 'lumiere-movies' ); ?> <a href="?page=lumiere_options&subsection=howto"><?php esc_html_e( 'how to', 'lumiere-movies' ); ?></a>.<br />

		</div>


		<h3 class="hndle"><?php esc_html_e( 'Support me!', 'lumiere-movies' ); ?></h3>

		<div class="helpdiv-noborderimage">
			<?php esc_html_e( 'You will never believe there is so many ways to thank me. Yes, you can:', 'lumiere-movies' ); ?><br />
<strong>1</strong>. <?php esc_html_e( 'pay whatever you want on', 'lumiere-movies' ); ?> <a href="https://www.paypal.me/jcvignoli">paypal <img src="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'paypal-donate.png' ); ?>" width="40px" class="lumiere_align_bottom" /></a>.<br />
<strong>2</strong>. <?php esc_html_e( 'vote on', 'lumiere-movies' ); ?> <a href="<?php echo esc_attr( \Lumiere\Settings::IMDBHOMEPAGE ); ?>"><?php esc_html_e( "Lumière's website", 'lumiere-movies' ); ?></a> <?php esc_html_e( 'or on', 'lumiere-movies' ); ?> <a href="<?php echo esc_attr( \Lumiere\Settings::LUMIERE_WORDPRESS ); ?>"><?php esc_html_e( "WordPress' website", 'lumiere-movies' ); ?></a>.<br />
<strong>3</strong>. <?php esc_html_e( 'send as many bugfixes and propositions as you can on Lumiere Movies website.', 'lumiere-movies' ); ?><br />
<strong>4</strong>. <?php esc_html_e( 'translate the plugin into your own language.', 'lumiere-movies' ); ?><br />
<strong>5</strong>. <?php esc_html_e( 'help me to improve the plugin.', 'lumiere-movies' ); ?> <?php esc_html_e( 'Report at the development', 'lumiere-movies' ); ?> <a href="<?php echo esc_attr( \Lumiere\Settings::LUMIERE_GIT ); ?>">GIT</a>'s <?php esc_html_e( 'website', 'lumiere-movies' ); ?> <br />
<strong>6</strong>. <?php esc_html_e( 'do a trackback, make some noise about this plugin!', 'lumiere-movies' ); ?><br />
		</div>


		<h3 class="hndle"><span><?php esc_html_e( 'Credits:', 'lumiere-movies' ); ?></span></h3>

		<div class="helpdiv">
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

		<?php
	}

	/**
	 * Popup explaination
	 */
	public function explain_popup(): void {
		?>

		<div class="helpdiv">

			<h4 data-show-hidden="inside_help_explain_popup" class="help_titles"><?php esc_html_e( 'Why a popup window?', 'lumiere-movies' ); ?></h4>

			<div id="inside_help_explain_popup" class="hidesection">

			<br clear="both"/>

			<a href="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-1.jpg' ); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies' ); ?>"><img align="right" width="50%" src="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-1.jpg' ); ?>" alt="screenshot Link creator" /></a>

			<?php esc_html_e( 'The first way to use Lumiere Movies is to add links to movie titles that opens popups with information about that very same movies. It is a usefull for posts that mention movies title; just add a link to your movie title, and let visitors knowing more about the details of the movie you mention.', 'lumiere-movies' ); ?>

			<?php esc_html_e( "Popup is a window that opened on click, which allows to consult director's and movie's information; if browsing a movie, one can read more about the movie but also the people who took part in the movie, such as actors, directors, etc.", 'lumiere-movies' ); ?>

			<h4><?php esc_html_e( 'How to make a popup link', 'lumiere-movies' ); ?></h4>

			<?php echo wp_kses( __( "To create a link to a popup window, you only need to put the <b>movie's title</b> inside dedicated tags. Depending on the visual interface you use (modern WordPress, wysiwig old WordPress, or pure text interface), you may add these tags in different ways.", 'lumiere-movies' ), self::ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS ); ?>

			<br clear="both"/><br />

			<?php esc_html_e( 'If you use a recent WordPress and have not activated a plugin to continue using the old editor interface, you can add a Lumière link to a popup by selecting the title of your movie, then adding the link with the dedicated contextual menu option:', 'lumiere-movies' ); ?>

			<br clear="both"/>

			<div align="center"><a href="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'admin-help-addimdblink-gutenberg.png' ); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies' ); ?>"><img width="80%" src="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'admin-help-addimdblink-gutenberg.png' ); ?>" alt="add link in gutenberg" /></a></div>

			<br clear="both"/>

			<?php esc_html_e( "If you use an old WordPress version or a recent WordPress with the plugin 'classic editor' installed, you can access in the dedicated menu to 'add a popup link:", 'lumiere-movies' ); ?>

			<br clear="both"/>

			<div align="center"><a href="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-7.jpg' ); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies' ); ?>"><img width="80%" src="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-7.jpg' ); ?>" alt="screenshot Link creator button added for bloggers who prefer HTML writing way" /></a></div>

			<br clear="both"/>

			<?php esc_html_e( "No explanation is need for those who prefer to write directly in HTML editor; it goes without saying they know how to add an HTML tag. But even in that interface a button is available for adding the code. For references, here is the HTML tag to wrap your movie's title with:", 'lumiere-movies' ); ?>

			<div align="center" clear="both"><pre>
&lt;span data-lum_movie_maker="popup"&gt;
movie's title
&lt;/span&gt;
			</pre></div>


			<?php esc_html_e( "Whatever the tool you prefer, when you add such a link a small icon confirming that the link is Lumière compliant is added to your movie's title.", 'lumiere-movies' ); ?>

			</div>
		</div>

		<?php

	}

	/**
	 * Inside the post explaination
	 */
	public function explain_widget (): void {
		?>

		<div class="helpdiv">
			<h4 data-show-hidden="inside_help_explain_widget" class="help_titles"><?php esc_html_e( 'Why to use widget?', 'lumiere-movies' ); ?></h4>

			<div id="inside_help_explain_widget" class="hidesection">

			<a href="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-3.jpg' ); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies' ); ?>"><img align="right" width="50%" src="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-3.jpg' ); ?>" alt="<?php esc_html_e( 'key and value for custom fields', 'lumiere-movies' ); ?>" /></a>

			<?php esc_html_e( 'Widgets are widely used in WordPress. It allows to select which information display in a given area, usually in the sidebar. Lumière allows to display any movie into your sidebar.', 'lumiere-movies' ); ?>

			<br clear="both"/>

			<h4><?php esc_html_e( 'How to use the widget', 'lumiere-movies' ); ?></h4>

			<?php echo wp_kses( __( "<strong>First</strong>, prior to WordPress 5.8, go to <a href='widgets.php'>widget</a> administration (<i>appearance</i> tab), drag <i>Lumière widget</i> (from <i>inactive widgets</i>) to a sidebar, and modify the box's title (in case you don't want to have the default name). As of WordPress 5.8, widgets are blocks selected by the user, and adding them is very intuitive.", 'lumiere-movies' ), self::ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS ); ?>

			<br />
			<br />
			<?php echo wp_kses( __( '<strong>Second</strong>, edit your post and add the name of the movie in the box to the sidebar on your right-hand. Lumiere Movies will automatically display in the widget the movie selected.', 'lumiere-movies' ), self::ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS ); ?>

			<a href="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-5.jpg' ); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies' ); ?>"><img align="right" width="50%" src="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-5.jpg' ); ?>" alt="<?php esc_html_e( 'Lumière metabox to add a movie in a widget', 'lumiere-movies' ); ?>" /></a>

			<br />
			<br />

			<?php echo wp_kses( __( "As in many other sections of Lumière plugin, you can add the movie's IMDb id instead of the movie's title to make sure that the right movie is display. Should you want to find the movie's IMDb id, click on 'use this tool' and a new windows will be displayed; search for your movie, copy-paste its IMDb id into the sidebar, and select by 'movie id' in the dropdown list.", 'lumiere-movies' ), self::ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS ); ?>

			<a href="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-8.jpg' ); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies' ); ?>"><img align="left" width="50%" src="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-8.jpg' ); ?>" alt="<?php esc_html_e( 'Lumiere Movies query interface', 'lumiere-movies' ); ?>" /></a>

			<br /><br />

			<?php echo wp_kses( __( "Using the movie's IMDb id allows more security: instead of searching for a title, Lumiere Movies can display directly the movie you are looking for. Very useful when your movie's name does not work as it should, due to movies with the same title, if a incorrect movie is displayed, etc.", 'lumiere-movies' ), self::ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS ); ?>

			<br clear="both"/>

			<?php echo wp_kses( __( "Get IMDb ids from links provided everywhere in the plugin interface. Even <a data-lumiere_admin_popup='openApopup'>here</a>.", 'lumiere-movies' ), self::ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS );
			?>
			</div>
		</div>

		<?php
	}

	/**
	 * Inside the post explaination
	 */
	public function explain_inside_post (): void {
		?>

		<div class="helpdiv">
			<h4 data-show-hidden="inside_help_explain_inside_post" class="help_titles"><?php esc_html_e( "Why display movie's data inside my post?", 'lumiere-movies' ); ?></h4>

			<div id="inside_help_explain_inside_post" class="hidesection">

			<a href="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-2.jpg' ); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies' ); ?>"><img align="right" width="50%" src="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-2.jpg' ); ?>" alt="<?php esc_html_e( 'Lumière! Movies widget', 'lumiere-movies' ); ?>" /></a>
			<?php esc_html_e( 'Including movie information within your article is quite useful; it can ingeniously illustrate your post, displays crucial data (directors, actors) and at the same time add links to further popups that include even more detailed information.', 'lumiere-movies' ); ?>

			<h4><?php esc_html_e( 'How to display data inside my post', 'lumiere-movies' ); ?></h4>


			<?php esc_html_e( "Lumière provides you with tools to add 'HTML tags' (span) to wrapp your movie's title when writting your article. These 'HTML tags' will be then converted into movie's details. In the same way as for for popups, three tools are provided depending upon your the WordPress interface you used to publish your posts. If you use the modern WordPress interface, a Lumière block is provided; just enter the movie's title or IMDb id, and you are done!", 'lumiere-movies' ); ?>

			<a href="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-9.jpg' ); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies' ); ?>"><img width="90%" src="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-9.jpg' ); ?>" alt="<?php esc_html_e( 'Lumiere Movies Inside a post gutenberg block', 'lumiere-movies' ); ?>" /></a>

			<?php esc_html_e( 'You can add as many blocks as you whish; there is no limitation in the number of movies you can display per article.', 'lumiere-movies' ); ?>


			<h4><?php esc_html_e( 'How to display data inside my post - advanced users', 'lumiere-movies' ); ?></h4>

			<?php
			esc_html_e( "It could happen you don't want to use the previous solution to display movie's data. For exemple, if you wish to use Lumière outside a post (in a customised page), it won't work. Lumière is written around classes, so it is easy to achieve what you want.", 'lumiere-movies' );
			?>

			<br />

			<?php esc_html_e( "The function to be called is <strong>imdb_call_external ()</strong>. It has two parameters, and both are mandatory. The first is the movie's name, and the second take always 'external'. For exemple, one'd like to display 'The Descent' should call the function like this:", 'lumiere-movies' ); ?>

			<blockquote align="center" class="imdblt_padding_left">
				$movieClass = new \Lumière\LumiereMovies;<br />
				$movieClass->lumiere_external_call('Descent', false, 'external');
			</blockquote>

			<?php esc_html_e( 'Should you want to call the function using an IMDb ID instead:', 'lumiere-movies' ); ?>

			<blockquote align="center" class="imdblt_padding_left">
				$movieClass = new \Lumière\LumiereMovies;<br />
				$movieClass->lumiere_external_call(false, '0435625', 'external');
			</blockquote>


			<h4><?php esc_html_e( 'I want to get rid of thoses links opening popups', 'lumiere-movies' ); ?></h4>
			<?php esc_html_e( "It could happen you do not want popups at all. Since by default Lumière Movies adds links whenever relevant to movie's details inside your article, you may change that behaviour. In order to do so, look for 'General options / Advanced / Remove popup links' and uncheck the box. No links will be displayed, both for the widget and within your articles.", 'lumiere-movies' ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Add search form explaination
	 */
	public function explain_addsearchform(): void {
		?>

		<div class="helpdiv">
			<h4 data-show-hidden="inside_help_explain_addsearchform" class="help_titles"><?php esc_html_e( 'How to add a search function for movies in my page? (advanced users)', 'lumiere-movies' ); ?></h4>

			<div id="inside_help_explain_addsearchform" class="hidesection">

			<?php esc_html_e( 'It is doable. Lumière is versatile enough to handle this function. With the help of a form, you can add a query field to search for every movie on your blog. Here is the code:', 'lumiere-movies' ); ?>

			<blockquote class="imdblt_align_left">
				&lt;form action="" method="post" method="get" id="searchmovie" &gt<br />
					&lt;div&gt Search a movie: &lt;/div&gt<br />
					&lt;input type="text" id="moviesearched" name="moviesearched" &gt<br />
					&lt;input type="submit" value="Go"&gt<br />
				&lt;/form&gt<br />
			</blockquote>

			<?php esc_html_e( 'Then the PHP code:', 'lumiere-movies' ); ?>

			<blockquote class="imdblt_align_left">
				&lt;?php<br />
				if (class_exists("\Lumiere\Settings")) {<br />
					$config_class = new \Lumiere\Settings();<br />
					// Get the type of search: movies, series, games<br />
					$typeSearch = $config_class->lumiere_select_type_search();<br />
				}<br />
				<br />
				# Initialization of IMDBphp libraries<br />
				$search = new \Imdb\TitleSearch($config_class );<br />

				if ( (isset ($_GET["moviesearched"])) && (!empty ($_GET["moviesearched"])) ){<br />
					$search_sanitized = isset($_GET["moviesearched"]) ? sanitize_text_field( $_GET["moviesearched"] ) : NULL;<br />
					$results = $search->search ($search_sanitized, $typeSearch );<br />
				}<br />
				<br />
				foreach ($results as $res) {<br />
					// ---- movie title and year<br />
					echo "\n\t&lt;div class='lumiere_container_flex50 lumiere_italic lumiere_gutenberg_results'&gt".esc_html( $res->title() )." (".intval( $res->year() ).")".'&lt;/div&gt';<br />
				}<br />
				?&gt<br />
			</blockquote>

			<?php esc_html_e( 'It perfectly fits in your sidebar, for example.', 'lumiere-movies' ); ?>
			</div>
		</div>

		<?php

	}

	/**
	 * Keep CSS explaination
	 */
	public function explain_keepcss(): void {
		?>

		<div class="helpdiv">
			<h4 data-show-hidden="inside_help_explain_keepcss" class="help_titles"><?php esc_html_e( 'How to ensure that my css edits remain through plugin updates?', 'lumiere-movies' ); ?></h4>

			<div id="inside_help_explain_keepcss" class="hidesection">

			<?php esc_html_e( 'Are you tired of losing your carefully hand-made CSS edits at every Lumière! update? There is a solution.', 'lumiere-movies' ); ?>

			<br clear="both"/>
			<br clear="both"/>

			<?php
			/* translators: %1\$s is a URL tag */
			echo wp_kses( sprintf( esc_html__( "Any modification of the stylesheet you make should be done in your template folder rather than by editing lumiere-movies/css/lumiere.css. Download %1\$s from the GIT repository, and edit that very file so it suits your needs. Then copy the edited file into you template folder: that file will superseed the plugin's one. Whenever you will update, your template's file will remain untouched and your edits will make it. Just make sure you are using a child theme, otherwise your customised lumiere.css will be deleted at the next template update.", 'lumiere-movies' ), '<a href="https://github.com/jcvignoli/lumiere-movies/blob/master/src/css/lumiere.css">unminified css</a>' ), self::ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS ); ?>
			</div>
		</div>

		<?php
	}

	/**
	 * Taxonomy explaination
	 */
	public function explain_taxonomy(): void {
		?>

		<div class="helpdiv">
			<h4 data-show-hidden="inside_help_explain_taxonomy" class="help_titles"><?php esc_html_e( "What is WordPress' taxonomy?", 'lumiere-movies' ); ?></h4>

			<div id="inside_help_explain_taxonomy" class="hidesection">

			<?php esc_html_e( 'Since WordPress 2.3, WordPress users can use taxonomy.', 'lumiere-movies' ); ?>

			<?php esc_html_e( "Taxonomy is a feature adding an extra layer of category in addition to the already existing Categories and Tags; in other words, it is like having species (a block named 'genre') and subspecies (few words describing the genre, like 'Adventure', 'Terror'). It is not fundamentaly different from Categories and Tags, but it is extremly useful for Lumière Movies.", 'lumiere-movies' ); ?>

			<h4><?php esc_html_e( 'How to use taxonomy in WordPress?', 'lumiere-movies' ); ?></h4>

			<a href="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'admin-taxonomy-details.png' ); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies' ); ?>"><img align="right" width="40%" src="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'admin-taxonomy-details.png' ); ?>" alt="taxonomy details" /></a>

			<?php esc_html_e( "Taxonomy is automatically generated in Lumière! and already activated. You can however disable it by unchecking the box in 'General options -> Advanced -> 'Use taxonomy'. Note that since taxonomy is related to movie details (such as directors, actors, etc), the movie detail you want to be used as taxonomy has to be also activated in 'Data -> Display'.", 'lumiere-movies' ); ?><br />
			<?php esc_html_e( 'After that, pay a visit to your post or page; on the first refresh, links to taxonomy pages will be created. ', 'lumiere-movies' );
			/* translators: %s is an admin URL */
			echo wp_kses( sprintf( esc_html__( 'Important: you need to go to %1$s Permalink Settings %2$s to refresh the rewriting rules, otherwise you will get a page not found error (404).', 'lumiere-movies' ), '<a href="options-permalink.php">', '</a>' ), self::ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS );
			esc_html_e( 'You can now visit pages that include all your posts grouped by movie details. For instance, if you write a lot about the same movie director, the taxonomy page will include all your posts written about them.', 'lumiere-movies' ); ?>

			<h4><?php esc_html_e( "New option in 'Posts' menu", 'lumiere-movies' ); ?></h4>

			<a href="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'admin-taxonomy-postlist.png' ); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies' ); ?>"><img align="left" width="30%" src="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'admin-taxonomy-postlist.png' ); ?>" alt="taxonomy new options" /></a>

			<?php esc_html_e( "Once you have published your first article including a movie (both widget and inside a post trigger that function) WordPress will display the new taxonomy under 'posts' admin section. Depending on the movie details you have selected, you will find them in the menu.", 'lumiere-movies' ); ?>

			<br />

			<h4><?php esc_html_e( 'Advanced: Customize your theme for your taxonomy pages', 'lumiere-movies' ); ?></h4>

			<?php esc_html_e( "To fully enjoy this taxonomy, make sure you copy the template files located in the Lumière! movies folder 'lumiere-movies/theme/' into your 'theme' folder. This can be automatized by using the options available in Lumière! taxonomy :", 'lumiere-movies' ); ?>

			<br />

			<a href="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'admin-taxonomy-copytemplate.png' ); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies' ); ?>"><img align="right" width="40%" src="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'admin-taxonomy-copytemplate.png' ); ?>" alt="taxonomy new options" /></a>

			<?php esc_html_e( "Click on 'copy template', that's it! You will be notified when a new template is available. You can customize even further the template that was copied into your template to match your specific needs. By default, the new taxonomy template will show you the person/item and the posts and pages mentioning them:", 'lumiere-movies' ); ?><br />

			<br clear="both">
			<br clear="both">

			<div align="center"><a href="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-10.jpg' ); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies' ); ?>"><img align="center" width="40%" src="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-10.jpg' ); ?>" alt="taxonomy result" /></a></div>

			<br clear="both">
			</div>
		</div>

		<?php
	}

	/**
	 * Auto widget explaination
	 */
	public function explain_autowidget(): void {
		?>

		<div class="helpdiv">

			<h4 data-show-hidden="inside_help_explain_autowidget" class="help_titles"><?php esc_html_e( 'What is Lumière auto-widget?', 'lumiere-movies' ); ?></h4>

			<div id="inside_help_explain_autowidget" class="hidesection">

			<?php esc_html_e( "It is a special type of widget. Unlike the normal Lumière widget (see widget help section in this page), Lumière auto-widget does not require you to enter any IMDb ID or movie's title manually. It automatically query the IMDb according to title you gave to your post. Beware it does so for all posts you have published.", 'lumiere-movies' );
			echo '<br /><br />';
			?>

			<h4><?php esc_html_e( 'When should you use auto-widget?', 'lumiere-movies' ); ?></h4>

			<?php esc_html_e( "Should you have hundreds of posts named after movie's title, and that you don't want to edit them all to manually insert  widgets or a Lumière blocks inside the post. Lumière does everything for you.", 'lumiere-movies' );
			echo '<br /><br />';

			echo '<h4>';
			esc_html_e( 'How to use auto-widget?', 'lumiere-movies' );
			echo '</h4>';

			/* translators: %s are URL tags */
			echo wp_kses( sprintf( esc_html__( "Add a Lumières!'s %1\$s widget %2\$s to your sidebar, and go to 'General Options / Advanced' and check 'Auto widget' option.", 'lumiere-movies' ), '<a href="widgets.php">', '</a>' ), self::ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS );
			?>

			<div align="center">
				<a href="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'auto-widget.png' ); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies' ); ?>"><img align="center" width="80%" src="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'auto-widget.png' ); ?>" alt="auto widget option" /></a>
			</div>

			<?php esc_html_e( 'Next time you will look at your post, you will find the widget according to your post’s title.', 'lumiere-movies' );
			echo '<br /><br />';
			echo wp_kses( __( 'Notice: in order to have this feature work, you must add a widget using <a href="widgets.php">Widget Page</a> option. Take a look at the "Widget" section of this "how to" page.', 'lumiere-movies' ), self::ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS );
			echo '<br /><br />';
			esc_html_e( 'Known issue: Lumière offers widgets for both old and modern type of widgets. Old widgets (aka Legacy Widgets) are available if you installed a Classic Widget plugin or any plugin that simplified the way to display your widgets and posts, or if your WordPress install is prior to WordPress 5.8. Modern Widgets (aka Block Widgets) are standard in every new WordPress install as of 5.8 that does not include a Classic Widget plugin. Potential issues could arise should you install both types of Lumière widgets, namely Legacy and Block Widgets. In order to prevent such issues, make sure that both Block and Legacy Widgets are not activated together. Remove any Lumière Block Widget previously added if you use Lumière Legacy Widget when using the pre-5.8/Classic Editor. In the pre-5.8/Classic Widget Editor, no option to add a Block Widget is available, but you may find a Block based Widget previously added in Block Editor, which you need to remove.', 'lumiere-movies' );
			echo '<br /><br />';
			esc_html_e( 'Should you switch back to the new WordPress Block Editor standard again (by updating WordPress or uninstalling Classic Widget), remove the Legacy widget that will be visible in the block Editor, save, refresh and add a new Lumière Block Widget. In new Widget WordPress installs, only the standard Block Widget is available.', 'lumiere-movies' );

			?>
			</div>
		</div>

		<?php
	}

	/**
	 * Search explaination
	 */
	public function explain_searchoptions(): void {
		?>

		<div class="helpdiv">
			<h4 data-show-hidden="inside_help_explain_searchoptions" class="help_titles"><?php esc_html_e( 'Changing default IMDb search options', 'lumiere-movies' ); ?></h4>

			<div id="inside_help_explain_searchoptions" class="hidesection">

			<?php esc_html_e( "Lumière queries the IMDb for every movie or person you are looking for. You can modify the results from the IMDb displayed in your blog will tweaking the search options available in menu options 'General / Advanced'.", 'lumiere-movies' ); ?>

			<br clear="both" />
			<br clear="both" />
			<?php
			esc_html_e( 'Three options are available: search language, search categories and the limit of the number of results.', 'lumiere-movies' );
			?>

			<br clear="both" />
			<br clear="both" />
			<?php
			esc_html_e( "1. Search language allows to modify the language of the query. This is not the language of the output: the details will always be returned in English by the IMDb. What this options modifies is the lanuage query only; if the query is run in French, movie's original title will be first returned instead of the English title.", 'lumiere-movies' );
			?>

			<br clear="both" />
			<br clear="both" />
			<?php
			esc_html_e( "2. Search category allows to modify the type of material your searching. If your blog is about series only, select in the dropdown list 'series only'. If it is about videogames, select the option accordingly.", 'lumiere-movies' );
			?>

			<br clear="both" />
			<br clear="both" />
			<?php
			esc_html_e( '3. It is possible to limit the number of results in the queries using its dedicated option. The less results there is, the less server resources are required and the faster the output is displayed. This limit number applies to the search of movies with a similar name (menu option in movies popups) and in ', 'lumiere-movies' );
			/* translators: %s is replaced with a URL */
			echo wp_kses( sprintf( __( "<a href='%1\$s'>the admin tool of queries to find IMDb id</a>.", 'lumiere-movies' ), esc_url( admin_url() . \Lumiere\Settings::GUTENBERG_SEARCH_URL_STRING ) ), self::ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS );
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

