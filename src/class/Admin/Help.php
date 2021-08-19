<?php
/**
 * Child class for displaying help sections.   
 * Child of Admin
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       1.0
 */

namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die('You can not call directly this page');
}

class Help extends \Lumiere\Admin {

	/* Paths to files to be read
	 * 
	 */
	private $readmefile = '';
	private $changelogfile = '';
	private $acknowfile = '';

	function __construct() {

		// Construct parent class
		parent::__construct();

		// Build constants not in parent class
		$this->readmefile = $this->rootPath . "README.txt";
		$this->changelogfile = $this->rootPath . "CHANGELOG.md";
		$this->acknowfile = $this->rootPath . "ACKNOWLEDGMENTS.md";

		// Add specific script for metaboxes
		//add_action('admin_enqueue_scripts', [$this, 'lumiere_help_extrascript' ]); # can't use add_action, call in parent class too late
		$this->lumiere_help_extrascript ();

		// Meta boxes
		add_meta_box('lumiere_explain_popup', esc_html__( 'Popup link builder', 'lumiere-movies'), [$this, 'explain_popup'], 'lumiere_help', 'left', 'core');
		add_meta_box('lumiere_explain_inside_post', esc_html__( 'Inside the post', 'lumiere-movies'), [$this, 'explain_inside_post' ], 'lumiere_help', 'right', 'core');
		add_meta_box('explain_widget', esc_html__( 'Widget', 'lumiere-movies'), [$this, 'explain_widget' ], 'lumiere_help', 'left', 'core');
		add_meta_box('lumiere_explain_addsearchform', esc_html__( 'Add a search form', 'lumiere-movies'), [$this, 'explain_addsearchform' ], 'lumiere_help', 'left', 'core');
		add_meta_box('lumiere_explain_keepcss', esc_html__( 'Keep css through update', 'lumiere-movies'), [$this, 'explain_keepcss' ], 'lumiere_help', 'right', 'core');
		add_meta_box('lumiere_explain_taxonomy', esc_html__( 'Taxonomy with Wordpress', 'lumiere-movies'), [$this, 'explain_taxonomy' ], 'lumiere_help', 'left', 'core');
		add_meta_box('lumiere_explain_autowidget', esc_html__( 'Widget auto according post\'s title', 'lumiere-movies'), [$this, 'explain_autowidget' ], 'lumiere_help', 'right', 'core');
		add_meta_box('lumiere_explain_searchoptions', esc_html__( 'Search options', 'lumiere-movies'), [$this, 'explain_searchoptions' ], 'lumiere_help', 'right', 'core');

		$this->layout();

	}


	/* Display the layout
	 *
	 *
	 */
	private function layout () { 

		echo "\n\t".'<div id="poststuff">';

		// Always display the menu
		if (isset($_GET['subsection']) && ($_GET['subsection'] == "help"))  {
			$this->display_menu();
		}


		// Changelog section
		if ((isset($_GET['helpsub'])) && ($_GET['helpsub'] == "changelog"))  { 	
			echo "\n\t".'<br clear="both" />';
			echo "\n\t\t".'<div class="imblt_border_shadow">';
			$this->display_changelog();
			echo "\n\t\t".'</div>';
			echo "\n\t".'<br clear="both" />';
		// Faqs section
		} elseif (isset($_GET['helpsub']) && ($_GET['helpsub'] == "faqs")) {
			echo "\n\t".'<br clear="both" />';
			echo "\n\t\t".'<div class="imblt_border_shadow">';
			$this->display_faqs();
			echo "\n\t\t".'</div>';
			echo "\n\t".'<br clear="both" />';
		// Support section
		} elseif ((isset($_GET['helpsub'])) && ($_GET['helpsub'] == "support") ) {
			echo "\n\t".'<br clear="both" />';
			echo "\n\t\t".'<div class="imblt_border_shadow">';
			$this->display_support();
			echo "\n\t\t".'</div>';
			echo "\n\t".'<br clear="both" />';
		// How to section
		}  else {
			$this->display_howto();
		}

		echo "\n\t".'</div>';

	}

	/* Display the menu
	 *
	 *
	 */
	function display_menu () { ?>
<div id="tabswrap">
	<div class="imdblt_double_container lumiere_padding_five">

		<div class="lumiere_flex_auto lumiere_align_center"><img src="<?php echo esc_url( $this->configClass->lumiere_pics_dir . 'menu/admin-help-howto.png'); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e( "How to use Lumiere Movies", 'lumiere-movies');?>" href="<?php echo esc_url( admin_url() . "admin.php?page=lumiere_options&subsection=help&helpsub=howto"); ?>"><?php esc_html_e( 'How to', 'lumiere-movies'); ?></a></div>

		<div class="lumiere_flex_auto lumiere_align_center"><img src="<?php echo esc_url( $this->configClass->lumiere_pics_dir . 'menu/admin-help-faq.png'); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e( "Frequently asked questions", 'lumiere-movies');?>" href="<?php echo esc_url( admin_url() . "admin.php?page=lumiere_options&subsection=help&helpsub=faqs"); ?>"><?php esc_html_e( 'FAQs', 'lumiere-movies'); ?></a></div>

		<div class="lumiere_flex_auto lumiere_align_center"><img src="<?php echo esc_url( $this->configClass->lumiere_pics_dir . 'menu/admin-help-changelog.png'); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e( "What's new", 'lumiere-movies');?>" href="<?php echo esc_url( admin_url() . "admin.php?page=lumiere_options&subsection=help&helpsub=changelog"); ?>"><?php esc_html_e( 'Changelog', 'lumiere-movies'); ?></a></div>

		<div class="lumiere_flex_auto lumiere_align_center"><img src="<?php echo esc_url( $this->configClass->lumiere_pics_dir . 'menu/admin-help-support.png'); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e( "To get support and to support what you get", 'lumiere-movies');?>" href="<?php echo esc_url( admin_url() . "admin.php?page=lumiere_options&subsection=help&helpsub=support"); ?>"><?php esc_html_e( 'Support, donate & credits', 'lumiere-movies'); ?></a></div>

	</div>
</div>
<?php
	}


	/* Display the faqs
	 *
	 *
	 */
	function display_faqs () { ?>

		<div id="lumiere_help_plb_faq">
			<div class="handlediv" title="Click to toggle"><br></div>
			<h3 class="hndle"><?php esc_html_e("Frequently asked questions", 'lumiere-movies'); ?></h3>
			<div class="inside">
				<div class="helpdiv">
				<?php
				$patterntitle = '/== Frequently Asked Questions ==(.*?)== Support ==/ms';
				$faqfile = file_get_contents($this->readmefile);
				preg_match($patterntitle, $faqfile, $faqsection);
				$faqsection = $faqsection[1];
				$faqsectionarray = preg_split ('/=(.*?)=/', $faqsection, -1, PREG_SPLIT_DELIM_CAPTURE);

				// replace links from (specially formated for wordpress website) readme with casual html
				$patternlink = '~(\\[{1}(.*?)\\]\()(https://)(([[:punct:]]|[[:alnum:]])*)( \"{1}(.*?)\"\))~';
				$faqsectionarray = preg_replace($patternlink,"<a href=\"\${3}\${4}\" title=\"\${7}\">\${2}</a>",$faqsectionarray);
				$faqsectionarray = preg_replace("~\*\*(.*?)\*\*~","<i>\${1}</i>",$faqsectionarray);

				$i=0;
				echo "<br />\n<ol>\n";
					foreach ($faqsectionarray as $texte) {
						if ($i > "0") {
							if ($i%2 == 1) { // uneven number -> title
							// display text formatted
							echo "\t\t\t\t\t\t<li><strong>".$texte."</strong></li>\n";
							} else { // even number -> text
							// display text formatted
							echo "\t\t\t\t\t\t<div class='imdblt_padding_twenty'>".nl2br(str_replace("\n\n", "\n", $texte))."\t\t\t\t\t\t</div>\n";
							}
						}
					$i++;
					}
				echo "\t\t\t\t\t</ol>\n"; ?>
				</div>
			</div>
		</div>

<?php
	}

	/* Display the changelog
	 *
	 *
	 */
	function display_changelog () { ?>

		<h3 class="hndle"><?php esc_html_e("Changelog", 'lumiere-movies'); ?></h3>

		<div class="inside">

			<div class="helpdiv">
			<?php
			$changelogfile = file($this->changelogfile, FILE_BINARY);
			// replace **...** by strong and i
			$changelogfile = preg_replace("~(\*\s\[)(.*?)(\])~","<strong><i>\${2}</i></strong>",$changelogfile);
			// replace links from (specially formated for wordpress website) changlog with casual html
			$patternlink = '~(\\[{1}(.*?)\\]\()(https://)(([[:punct:]]|[[:alnum:]])*)( \"{1}(.*?)\"\))~';

			$changelogfile = preg_replace($patternlink,"<a href=\"\${3}\${4}\" title=\"\${7}\">\${2}</a>",$changelogfile);
			$i=0;
			echo "<ul>";
			foreach ($changelogfile as $texte) {
				if  ($i > "1") {

					// display text formatted
					echo "\t\t\t\t\t\t<li>".str_replace("\n", "", nl2br($texte))."</li>\n";
				}
			$i++;
			}
			echo "\t\t\t\t\t</ul>\n";
			?>

			</div>

		</div>

<?php
	}


	/* Display the support
	 *
	 *
	 */
	function display_support() { ?>

		<h3 class="hndle" id="help_support" name="help_support"><?php wp_kses( _e( 'Two ways to support <strong>Lumiere Movies</strong> plugin development', 'lumiere-movies'), self::allowed_html_for_esc_html_functions); ?></h3>
		
		<h3 class="hndle"><?php esc_html_e( 'Be supported!', 'lumiere-movies'); ?></h3>

		<div class="helpdiv-noborderimage">

			<?php esc_html_e( 'You will never believe there is so many ways to be supported. You can:', 'lumiere-movies'); ?><br />

	<strong>1</strong>. <?php esc_html_e( 'visit', 'lumiere-movies'); ?> <a href="<?php echo esc_attr( \Lumiere\Settings::IMDBHOMEPAGE ) ?>">Lumière website</a> <?php esc_html_e( 'to ask for help. ', 'lumiere-movies'); ?><br />

	<strong>2</strong>. <?php esc_html_e( 'check the', 'lumiere-movies'); ?> <a href="?page=lumiere_options&subsection=help&helpsub=faqs"><?php esc_html_e( 'FAQs ', 'lumiere-movies'); ?></a>.<br />

	<strong>3</strong>. <?php esc_html_e( 'check the', 'lumiere-movies'); ?> <a href="?page=lumiere_options&subsection=help&helpsub=howto"><?php esc_html_e( 'how to', 'lumiere-movies'); ?></a>.<br />

		</div>


		<h3 class="hndle"><?php esc_html_e( 'Support me!', 'lumiere-movies'); ?></h3>

		<div class="helpdiv-noborderimage">
			<?php esc_html_e( 'You will never believe there is so many ways to thank me. Yes, you can:', 'lumiere-movies'); ?><br />
<strong>1</strong>. <?php esc_html_e( 'pay whatever you want on', 'lumiere-movies'); ?> <a href="https://www.paypal.me/jcvignoli">paypal <img src="<?php echo esc_url( $this->configClass->lumiere_pics_dir . 'paypal-donate.png'); ?>" width="40px" class="imdblt_align_bottom" /></a> <?php esc_html_e( 'or on', 'lumiere-movies'); ?> <a href="https://en.tipeee.com/lost-highway">tipeee.com</a>.<br />
<strong>2</strong>. <?php esc_html_e( 'vote on', 'lumiere-movies'); ?> <a href="<?php echo esc_attr( \Lumiere\Settings::IMDBHOMEPAGE ); ?>"><?php esc_html_e( "Lumière's website", 'lumiere-movies'); ?></a> <?php esc_html_e( 'or on', 'lumiere-movies'); ?> <a href="<?php echo esc_attr( \Lumiere\Settings::LUMIERE_WORDPRESS ); ?>"><?php esc_html_e( "Wordpress' website", 'lumiere-movies'); ?></a>.<br />
<strong>3</strong>. <?php esc_html_e( "send as many bugfixes and propositions as you can on Lumiere Movies website.", 'lumiere-movies'); ?><br />
<strong>4</strong>. <?php esc_html_e( "translate the plugin into your own language.", 'lumiere-movies'); ?><br />
<strong>5</strong>. <?php esc_html_e( "help me to improve the plugin.", 'lumiere-movies'); ?> <?php esc_html_e( "Report at the development", 'lumiere-movies'); ?> <a href="<?php echo esc_attr( \Lumiere\Settings::LUMIERE_GIT ); ?>">GIT</a>'s <?php esc_html_e( 'website', 'lumiere-movies'); ?> <br />
<strong>6</strong>. <?php esc_html_e( 'do a trackback, make some noise about this plugin!', 'lumiere-movies'); ?><br />
		</div>


		<h3 class="hndle"><span><?php esc_html_e( 'Credits:', 'lumiere-movies'); ?></span></h3>

		<div class="helpdiv">
<?php
			$acknowfile = file($this->acknowfile, FILE_BINARY);

			// replace # by div
			$acknowfile = preg_replace('~\# (.*)~','<div><strong>${1}</strong></div>',$acknowfile);
			// remove ** **
			$acknowfile = preg_replace('~\*\*(.*)\*\*~','${1}',$acknowfile);

			// replace \n by br
			$acknowfile = preg_replace('~\n~','<br />',$acknowfile);

			// replace links from (specially formated for wordpress website) readme with casual html
			$patternlink = '~(\\[{1}(.*?)\\]\()(htt(p|ps)://)(([[:punct:]]|[[:alnum:]])*)( \"{1}(.*?)\"\))~';

			$acknowfile = preg_replace($patternlink,"<a href=\"\${3}\${5}\" title=\"\${7}\">\${2}</a>",$acknowfile);
			$i=0;
			echo "<ul>";
			foreach ($acknowfile as $texte) {
				if  ($i > "1") {

					// display text formatted
					echo "\t\t\t\t\t\t<li>".str_replace("\n", "", nl2br($texte))."</li>\n";
				}
			$i++;
			}
			echo "\t\t\t\t\t</ul>\n";
			?>
		</div>

<?php
	}


	/* Display how to with metaboxes
	 *
	 *
	 */
	function display_howto() { ?>
	<br />

	<div class="intro_cache">
		<?php esc_html_e( "This section covers the three main ways to display movie data and some related options in Lumière.", 'lumiere-movies'); ?>
		<br /><br />
		<?php esc_html_e( "Lumiere Movies is a plugin under intense development; this help section might be innacurate with regard to the latest functions. Main functions of the plugin are explained below, aiming to be as much user-friendly as possible.", 'lumiere-movies'); ?>
	</div>

	<div class="imdblt_double_container">

		<div class="postbox-container imdblt_double_container_content" style="flex:40%">
			<?php do_meta_boxes( 'lumiere_help', 'left', null); ?>
		</div>

		<div class="postbox-container imdblt_double_container_content" style="flex:40%">
			<?php do_meta_boxes( 'lumiere_help', 'right', null); ?>
		</div>

	</div>
<?php
	wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false );
	wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false );

	}

	/* Popup explaination
	 *
	 *
	 */
	function explain_popup () { ?>

		<div class="helpdiv">

			<h4><?php esc_html_e( 'Why a popup window?', 'lumiere-movies'); ?></h4>

			<br clear="both"/>

			<a href="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-1.jpg' ); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies'); ?>"><img align="right" width="50%" src="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-1.jpg' ); ?>" alt="screenshot Link creator" /></a>

			<?php esc_html_e( "The first way to use Lumiere Movies is to add links to movie titles that opens popups with information about that very same movies. It is a usefull for posts that mention movies title; just add a link to your movie title, and let visitors knowing more about the details of the movie you mention.", 'lumiere-movies'); ?>

			<?php esc_html_e( "Popup is a window that opened on click, which allows to consult director's and movie's information; if browsing a movie, one can read more about the movie but also the people who took part in the movie, such as actors, directors, etc.", 'lumiere-movies'); ?>

			<h4><?php esc_html_e( 'How to make a popup link', 'lumiere-movies'); ?></h4>

			<?php echo wp_kses( __( "To create a link to a popup window, you only need to put the <b>movie's title</b> inside dedicated tags. Depending on the visual interface you use (modern WordPress, wysiwig old WordPress, or pure text interface), you may add these tags in different ways.", 'lumiere-movies'), self::allowed_html_for_esc_html_functions); ?>

			<br clear="both"/><br />

			<?php esc_html_e( "If you use a recent WordPress and have not activated a plugin to continue using the old editor interface, you can add a Lumière link to a popup by selecting the title of your movie, then adding the link with the dedicated contextual menu option:", 'lumiere-movies'); ?>

			<br clear="both"/>

			<div align="center"><a href="<?php echo esc_url( $this->configClass->lumiere_pics_dir . 'admin-help-addimdblink-gutenberg.png'); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies'); ?>"><img width="80%" src="<?php echo esc_url( $this->configClass->lumiere_pics_dir . 'admin-help-addimdblink-gutenberg.png'); ?>" alt="add link in gutenberg" /></a></div>

			<br clear="both"/>

			<?php esc_html_e( "If you use an old WordPress version or a recent WordPress with the plugin 'classic editor' installed, you can access in the dedicated menu to 'add a popup link:", 'lumiere-movies'); ?>

			<br clear="both"/>

			<div align="center"><a href="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-7.jpg' ); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies'); ?>"><img width="80%" src="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-7.jpg' ); ?>" alt="screenshot Link creator button added for bloggers who prefer HTML writing way" /></a></div>

			<br clear="both"/>

			<?php esc_html_e( "No explanation is need for those who prefer to write directly in HTML editor; it goes without saying they know how to add an HTML tag. But even in that interface a button is available for adding the code. For references, here is the HTML tag to wrap your movie's title with:", 'lumiere-movies'); ?>

			<div align="center" clear="both"><pre>
&lt;span data-lum_movie_maker="popup"&gt;
movie's title
&lt;/span&gt;
			</pre></div>


			<?php esc_html_e( "Whatever the tool you prefer, when you add such a link a small icon confirming that the link is Lumière compliant is added to your movie's title.", 'lumiere-movies'); ?>
		</div>

	<?php

	}


	/* Inside the post explaination
	 *
	 *
	 */
	function explain_widget () { ?>

		<div class="helpdiv">
			<h4><?php esc_html_e( "Why to use widget?", 'lumiere-movies'); ?></h4>

			<a href="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-3.jpg'); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies'); ?>"><img align="right" width="50%" src="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-3.jpg'); ?>" alt="<?php esc_html_e( 'key and value for custom fields', 'lumiere-movies'); ?>" /></a>

			<?php esc_html_e( 'Widgets are widely used in WordPress. It allows to easily select what information to display in an area, usually on sidebar. Lumière allows to display any movie in your sidebar.', 'lumiere-movies'); ?>

			<br clear="both"/>

			<h4><?php esc_html_e( "How to use the widget", 'lumiere-movies'); ?></h4>

			<?php echo wp_kses( __( "<strong>First</strong>, prior to WordPress 5.8, go to <a href='widgets.php'>widget</a> administration (<i>appearance</i> tab), drag <i>imdb widget</i> (from <i>inactive widgets</i>) to a sidebar, and modify the box's title (in case you don't want to have the box named <i>IMDb data</i>). After WordPress 5.8, widgets are blocks selected by the user, but the process is identical.", 'lumiere-movies'), self::allowed_html_for_esc_html_functions); ?>

			<br />
			<br />

			<?php wp_kses( _e( "<strong>Second</strong>, edit your post and add the name of the movie in the box to the sidebar on your right-hand. Lumiere Movies will automatically display in the widget the movie selected.", 'lumiere-movies'), self::allowed_html_for_esc_html_functions); ?>

			<a href="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-5.jpg' ); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies'); ?>"><img align="right" width="50%" src="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-5.jpg' ); ?>" alt="<?php esc_html_e( 'Lumière metabox to add a movie in a widget', 'lumiere-movies'); ?>" /></a>

			<br />
			<br />

			<?php echo wp_kses( __( "As in many other sections of Lumière plugin, you can add the movie's IMDb id instead of the movie's title to make sure that the right movie is display. Should you want to find the movie's IMDb id, click on 'use this tool' and a new windows will be displayed; search for your movie, copy-paste its IMDb id into the sidebar, and select by 'movie id' in the dropdown list.", 'lumiere-movies'), self::allowed_html_for_esc_html_functions); ?>

			<a href="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-8.jpg'); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies'); ?>"><img align="left" width="50%" src="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-8.jpg' ); ?>" alt="<?php esc_html_e( 'Lumiere Movies query interface', 'lumiere-movies'); ?>" /></a>

			<br /><br />

			<?php wp_kses( _e( "Using the movie's IMDb id allows more security: instead of searching for a title, Lumiere Movies can display directly the movie you are looking for. Very useful when your movie's name does not work as it should, due to movies with the same title, if a incorrect movie is displayed, etc.", 'lumiere-movies'),  self::allowed_html_for_esc_html_functions); ?>

			<br clear="both"/>

			<?php
			echo wp_kses( __( "Get IMDb ids from links provided everywhere in the plugin interface. Even <a data-lumiere_admin_popup='openApopup'>here</a>.", 'lumiere-movies'), self::allowed_html_for_esc_html_functions ); ?>
		</div>

	<?php } 


	/* Inside the post explaination
	 *
	 *
	 */
	function explain_inside_post () {  ?>

		<div class="helpdiv">
			<h4><?php esc_html_e( "Why display movie's data inside my post?", 'lumiere-movies'); ?></h4>
			<a href="<?php echo esc_url (\Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-2.jpg'); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies'); ?>"><img align="right" width="50%" src="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-2.jpg'); ?>" alt="<?php esc_html_e( 'Lumiere Movies widget', 'lumiere-movies'); ?>" /></a>
			<?php esc_html_e( "Including movie information within your article is quite useful; it can ingeniously illustrate your post, displays crucial data (directors, actors) and at the same time add links to further popups that include even more detailed information.", 'lumiere-movies'); ?>

		</div>

		<div class="helpdiv">
			<h4><?php esc_html_e( 'How to display data inside my post', 'lumiere-movies'); ?></h4>


			<?php esc_html_e( "Lumière provides you with tools to add 'HTML tags' (span) to wrapp your movie's title when writting your article. These 'HTML tags' will be then converted into movie's details. In the same way as for for popups, three tools are provided depending upon your the WordPress interface you used to publish your posts. If you use the modern WordPress interface, a Lumière block is provided; just enter the movie's title or IMDb id, and you are done!", 'lumiere-movies'); ?>
			
			<a href="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-9.jpg' ); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies'); ?>"><img width="90%" src="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-9.jpg' ); ?>" alt="<?php esc_html_e( 'Lumiere Movies Inside a post gutenberg block', 'lumiere-movies'); ?>" /></a>

			<?php esc_html_e( "You can add as many blocks as you whish; there is no limitation in the number of movies you can display per article.", 'lumiere-movies'); ?>


			<h4><?php esc_html_e( 'How to display data inside my post - advanced users', 'lumiere-movies'); ?></h4>

			<?php esc_html_e( "It could happen you don't want to use the previous solution to display movie's data. For exemple, if you wish to use Lumière outside a post (in a customised page), it won't work. Lumière is written around classes, so it is easy to achieve what you want.", 'lumiere-movies'); ; ?>

			<br />

			<?php esc_html_e( "The function to be called is <strong>imdb_call_external ()</strong>. It has two parameters, and both are mandatory. The first is the movie's name, and the second take always 'external'. For exemple, one'd like to display 'The Descent' should call the function like this:", 'lumiere-movies'); ?>

			<blockquote align="center" class="imdblt_padding_left">
				$movieClass = new \Lumière\LumiereMovies;<br />
				$movieClass->lumiere_external_call('Descent', false, 'external');
			</blockquote>

			<?php esc_html_e( "Should you want to call the function using an IMDb ID instead:", 'lumiere-movies'); ?>

			<blockquote align="center" class="imdblt_padding_left">
				$movieClass = new \Lumière\LumiereMovies;<br />
				$movieClass->lumiere_external_call(false, '0435625', 'external');
			</blockquote>


			<h4><?php esc_html_e( "I want to get rid of thoses links opening popups", 'lumiere-movies'); ?></h4>
			<?php esc_html_e( "It could happen you do not want popups at all. Since by default Lumière Movies adds links whenever relevant to movie's details inside your article, you may change that behaviour. In order to do so, look for 'General options / Advanced / Remove popup links' and uncheck the box. No links will be displayed, both for the widget and within your articles.", 'lumiere-movies'); ?>
		</div>
	<?php }


	/* Add search form explaination
	 *
	 *
	 */
	function explain_addsearchform() { ?>

		<div class="helpdiv">
			<h4><?php esc_html_e( "How to add a search function for movies in my page? (advanced users)", 'lumiere-movies'); ?></h4>

			<?php esc_html_e( "It is doable. Lumière is versatile enough to handle this function. With the help of a form, you can add a query field to search for every movie on your blog. Here is the code:", 'lumiere-movies'); ?>


			<blockquote class="imdblt_align_left">
				&lt;form action="" method="post" method="get" id="searchmovie" &gt<br />
					&lt;div&gt Search a movie: &lt;/div&gt<br />
					&lt;input type="text" id="moviesearched" name="moviesearched" &gt<br />
					&lt;input type="submit" value="Go"&gt<br />
				&lt;/form&gt<br />
			</blockquote>

			<?php esc_html_e( "Then the PHP code:", 'lumiere-movies'); ?>

			<blockquote class="imdblt_align_left">
				&lt;?php<br />
				if (class_exists("\Lumiere\Settings")) {<br />
					$configClass = new \Lumiere\Settings();<br />
					// Get the type of search: movies, series, games<br />
					$typeSearch = $configClass->lumiere_select_type_search();<br />
				}<br />
				<br />
				# Initialization of IMDBphp libraries<br />
				$search = new \Imdb\TitleSearch($configClass );<br />

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

			<?php esc_html_e( "It perfectly fits in your sidebar, for example.", 'lumiere-movies'); ?>
		</div>




	<?php } 


	/* Keep CSS explaination
	 *
	 *
	 */
	function explain_keepcss () {  ?>

		<div class="helpdiv">
			<h4><?php esc_html_e( "How to ensure that my css edits remain through plugin updates?", 'lumiere-movies'); ?></h4>

			<?php esc_html_e( "Are you tired of losing your carefully hand-made CSS edits at every Lumière! update? There is a solution.", 'lumiere-movies'); ?>

			<br clear="both"/>
			<br clear="both"/>

			<?php esc_html_e( "Every modification you make should be done in your template folder instead of using lumiere-movies/css/lumiere.css file. Add a file named 'lumiere.css' in you template folder; that css file will superseed the plugin's one. Whenever you update, your template's file will remain untouched and your edits will make it. Just make sure you are using a child theme, not an official theme, otherwise your customised lumiere.css will be deleted during the next template update.", 'lumiere-movies'); ?>
		</div>


	<?php }


	/* Taxonomy explaination
	 *
	 *
	 */
	function explain_taxonomy() {  ?>

		<div class="helpdiv">
			<h4><?php esc_html_e( "What is Wordpress' taxonomy?", 'lumiere-movies'); ?></h4>

			<?php esc_html_e( "Since Wordpress 2.3, Wordpress users can use taxonomy.", 'lumiere-movies'); ?>

			<?php esc_html_e( "Taxonomy is a feature adding an extra layer of category in addition to the already existing Categories and Tags; in other words, it is like having species (a block named 'genre') and subspecies (few words describing the genre, like 'Adventure', 'Terror'). It is not fundamentaly different from Categories and Tags, but it is extremly useful for Lumière Movies.", 'lumiere-movies'); ?>

			<h4><?php esc_html_e( "How to use taxonomy in Wordpress?", 'lumiere-movies'); ?></h4>

			<a href="<?php echo esc_url( $this->configClass->lumiere_pics_dir . 'admin-taxonomy-details.png' ); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies'); ?>"><img align="right" width="40%" src="<?php echo esc_url( $this->configClass->lumiere_pics_dir . 'admin-taxonomy-details.png'); ?>" alt="taxonomy details" /></a>

			<?php esc_html_e( "Taxonomy is automatically generated in Lumière! and already activated. You can however disable it by unchecking the box in 'General options -> Advanced -> 'Use automatical genre taxonomy?'. Note that since taxonomy is related to movie details (such as directors, actors, etc), the movie detail you want to be used as taxonomy has to be also activated in 'Data -> Display'.", 'lumiere-movies'); ?><br />

			<?php esc_html_e( "After that, pay a visit to your post or page; on the first refresh, links to taxonomy pages will be processed. You can now visit pages that include all your posts grouped by movie details. For instance, if you write a lot about the same movie director, the taxonomy page will include all your posts written about them.", 'lumiere-movies'); ?>

			<h4><?php esc_html_e( "New option in 'Posts' menu", 'lumiere-movies'); ?></h4>

			<a href="<?php echo esc_url( $this->configClass->lumiere_pics_dir . 'admin-taxonomy-postlist.png' ); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies'); ?>"><img align="left" width="30%" src="<?php echo esc_url( $this->configClass->lumiere_pics_dir . 'admin-taxonomy-postlist.png'); ?>" alt="taxonomy new options" /></a>

			<?php esc_html_e( "Once you have published your first article including a movie (both widget and inside a post trigger that function) WordPress will display the new taxonomy under 'posts' admin section. Depending on the movie details you have selected, you will find them in the menu.", 'lumiere-movies'); ?>

			<br />

			<h4><?php esc_html_e( "Advanced: Customize your theme for your taxonomy pages", 'lumiere-movies'); ?></h4>

			<?php esc_html_e( "To fully enjoy this taxonomy, make sure you copy the template files located in the Lumière! movies folder 'lumiere-movies/theme/' into your 'theme' folder. This can be automatized by using the options available in Lumière! taxonomy :", 'lumiere-movies'); ?>

			<br />

			<a href="<?php echo esc_url( $this->configClass->lumiere_pics_dir . 'admin-taxonomy-copytemplate.png'); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies'); ?>"><img align="right" width="40%" src="<?php echo esc_url( $this->configClass->lumiere_pics_dir . 'admin-taxonomy-copytemplate.png'); ?>" alt="taxonomy new options" /></a>

			<?php esc_html_e( "Click on 'copy template, that's it! A warning will be given to you when a new template is available. You can customized even further the template that was copied into your template to match your specific needs. By default, the new taxonomy template will show you the person/item and the posts and pages mentioning them:", 'lumiere-movies'); ?><br />

			<br clear="both">
			<br clear="both">

			<div align="center"><a href="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-10.jpg' ); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies'); ?>"><img align="center" width="40%" src="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-10.jpg' ); ?>" alt="taxonomy result" /></a></div>

			<br clear="both">

		</div>
	<?php }


	/* Auto widget explaination
	 *
	 *
	 */
	function explain_autowidget() {  ?>

		<div class="helpdiv">

			<h4><?php esc_html_e( "How to get movies retrieved automatically according to post's titles?", 'lumiere-movies'); ?></h4>

			<?php esc_html_e( "You have hundreds of posts, carefully named after movies. You don't want to edit all of them and widget/inside the post section but you want to display information about movies anyway. There is a  solution.", 'lumiere-movies'); ?>

			<?php 
			/* translators: %s are URL tags */
			echo wp_kses( __( sprintf( "Add a Lumières!'s %s widget %s to your sidebar, and go to 'General Options / Advanced and check « Auto widget » option.", '<a href="widgets.php">', '</a>'), 'lumiere-movies'), self::allowed_html_for_esc_html_functions ); ?>

			<div align="center">
				<a href="<?php echo esc_url( $this->configClass->lumiere_pics_dir . 'auto-widget.png'); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies'); ?>"><img align="center" width="80%" src="<?php echo esc_url( $this->configClass->lumiere_pics_dir . 'auto-widget.png' ); ?>" alt="auto widget option" /></a>
			</div>

			<?php esc_html_e( "Next time you will look at your post, you will find the widget according to your post’s title.", 'lumiere-movies'); ?>
		</div>
	<?php
	} 

	/* Search explaination
	 *
	 *
	 */
	function explain_searchoptions() {  ?>

		<div class="helpdiv">
			<h4><?php esc_html_e( "Changing default IMDb search options", 'lumiere-movies'); ?></h4>

			<?php esc_html_e( "Lumière queries the IMDb for every movie or person you are looking for. You can modify the results from the IMDb displayed in your blog will tweaking the search options available in menu options 'General / Advanced'.", 'lumiere-movies'); ?>

			<br clear="both" />
			<br clear="both" />
			<?php 
			esc_html_e( "Three options are available: search language, search categories and the limit of the number of results.", 'lumiere-movies'); ?>

			<br clear="both" />
			<br clear="both" />
			<?php 
			esc_html_e( "1. Search language allows to modify the language of the query. This is not the language of the output: the details will always be returned in English by the IMDb. What this options modifies is the lanuage query only; if the query is run in French, movie's original title will be first returned instead of the English title.", 'lumiere-movies'); ?>

			<br clear="both" />
			<br clear="both" />
			<?php 
			esc_html_e( "2. Search category allows to modify the type of material your searching. If your blog is about series only, select in the dropdown list 'series only'. If it is about videogames, select the option accordingly.", 'lumiere-movies'); ?>

			<br clear="both" />
			<br clear="both" />
			<?php 
			esc_html_e( "3. It is possible to limit the number of results in the queries using its dedicated option. The less results there is, the less server resources are required and the faster the output is displayed. This limit number applies to the search of movies with a similar name (menu option in movies popups) and in ", 'lumiere-movies'); 
			/* translators: %s is replaced with an URL */
			echo wp_kses( __( sprintf( "<a href='%s'>the admin tool of queries to find IMDb id</a>.",  esc_url( admin_url() .  \Lumiere\Settings::GUTENBERG_SEARCH_URL_STRING ) ), 'lumiere-movies'), self::allowed_html_for_esc_html_functions ); ?>

		</div>
	<?php
	} 


	/* Add extra scripts to this page only
	 *
	 *
	 */
	function lumiere_help_extrascript () {

		wp_register_script(
			'lumiere_help_scripts', 
			'', 
			[						
				'common',	// script needed for meta_boxes
				'wp-lists',	// script needed for meta_boxes
				'postbox'	// script needed for meta_boxes
			], 
			$this->configClass->lumiere_version, 
			true 
		);

		wp_enqueue_script( 'lumiere_help_scripts');

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
?>
