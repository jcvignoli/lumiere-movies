<?php
/**
 * Admin class for displaying all Admin sections.   
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       1.0
 */

namespace Lumiere;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die('You can not call directly this page');
}

class Admin {

	/* Options vars
	 * 
	 */
	protected $imdb_admin_values, $imdb_widget_values,$imdb_cache_values;

	/* \Lumiere\Settings class
	 * 
	 */
	protected $configClass;

	/* \Lumière\Utils class
	 * 
	 * 
	 */
	protected $utilsClass;

	/* \Lumière\Utils class
	 * 
	 * 
	 */
	protected $logger;

	/* Store root directories of the plugin
	 * Path: absolute paths
	 * URL: start with https
	 *
	 */
	protected $rootPath = '';
	protected $rootURL = '';

	/* HTML allowed for use of wp_kses_post()
	 * Usefull for access from outside the class
	 */
	const allowed_html_for_esc_html_functions = [
		'i',
		'strong',
		'b',
		'a' => [
			'id' => true,
			'href'  => true,
			'title' => true,
			'data-*' => true,
		],
	];

	/* Constructor
	 * 
	 */
	public function __construct() {

		// Load all classes in class/Admin folder, will be loaded when needed
		spl_autoload_register( [ 'Lumiere\Admin', 'admin_loader' ] );

		// Start Settings class
		$this->configClass = new \Lumiere\Settings();

		// Start Utilities class
		$this->utilsClass = new \Lumiere\Utils();

		// Build constants
		$this->imdb_admin_values = $this->configClass->get_imdb_admin_option();
		$this->imdb_widget_values = $this->configClass->get_imdb_widget_option();
		$this->imdb_cache_values = $this->configClass->get_imdb_cache_option();
		$this->rootURL = plugin_dir_url( __DIR__ );
		$this->rootPath = plugin_dir_path( __DIR__ );

		// Start logger class if debug is selected
		if ( (isset($this->imdb_admin_values['imdbdebug'])) && ($this->imdb_admin_values['imdbdebug'] == 1) ){

			// Start the logger
			$this->configClass->lumiere_start_logger('adminLumiere');

			// Store the object
			$this->logger = $this->configClass->loggerclass;
		} 
	}


	/*  Loads all files included in class/Admin
	 *  Loaded in spl_autoload_register()
	 *
	 */
	public static function admin_loader($class_name) {

		// Remove 'Lumiere' and transforms '\' into '/'
		$class_name = str_replace('Lumiere/', '', str_replace('\\', '/', ltrim($class_name, '\\')));

		// Path for inclusion
		$path_to_file = plugin_dir_path( __DIR__ ) . 'class/' .$class_name . '.php';

		if (file_exists($path_to_file)) {

			require $path_to_file;

		}

	}

	/* Add the admin menu
	 * Called by class core
	 *
	 */
	public function lumiere_admin_menu() {

		add_action('admin_menu', [ $this, 'lumiere_add_left_menu' ] );

		// add imdblt menu in toolbar menu (top wordpress menu)
		if ($this->imdb_admin_values['imdbwordpress_tooladminmenu'] == 1 ) {

			add_action('admin_bar_menu', [ $this, 'admin_add_top_menu' ],70 );

		}
	}

	/* Add left admin menu
	 * 
	 *
	 */
	public function lumiere_add_left_menu() {

		$imdb_admin_values = $this->imdb_admin_values;

		// Menu inside settings
		if (function_exists('add_options_page') && ( (isset($imdb_admin_values['imdbwordpress_bigmenu'])) && ($imdb_admin_values['imdbwordpress_bigmenu'] == 0 ) ) ) {

			add_options_page(
				'Lumière Options',
				'<img src="'. $this->configClass->lumiere_pics_dir . 'lumiere-ico13x13.png" align="absmiddle"> Lumière',
				'administrator', 
				'lumiere_options', 
				[$this, 'lumiere_admin_pages' ] 
			);

		// Left menu
		} elseif (function_exists('add_submenu_page') && ( (isset($imdb_admin_values['imdbwordpress_bigmenu'])) && ($imdb_admin_values['imdbwordpress_bigmenu'] == 1 ) ) ) {

			add_menu_page( 
				'Lumière Options', 
				'<i>Lumière</i>' , 
				'administrator', 
				'lumiere_options', 
				[$this, 'lumiere_admin_pages' ], 
				$this->configClass->lumiere_pics_dir . 'lumiere-ico13x13.png', 
				65
			);
			add_submenu_page( 
				'lumiere_options' , 
				esc_html__('Lumière options page', 
				'lumiere-movies'), 
				esc_html__('General', 'lumiere-movies'), 
				'administrator', 
				'lumiere_options', 
				[$this, 'lumiere_admin_pages' ]
			);
			add_submenu_page( 
				'lumiere_options' , 
				esc_html__('Data management', 'lumiere-movies'), 
				esc_html__('Data', 'lumiere-movies'), 
				'administrator', 
				'lumiere_options&subsection=dataoption', 
				[$this, 'lumiere_admin_pages' ] 
			);
			add_submenu_page( 
				'lumiere_options',  
				esc_html__('Cache management options page', 'lumiere-movies'), 
				esc_html__('Cache', 'lumiere-movies'), 
				'administrator', 'lumiere_options&subsection=cache', 
				[$this, 'lumiere_admin_pages' ] 
			);
			add_submenu_page( 
				'lumiere_options' , 
				esc_html__('Help page', 'lumiere-movies'), 
				esc_html__('Help', 'lumiere-movies'), 
				'administrator', 
				'lumiere_options&subsection=help', 
				[$this, 'lumiere_admin_pages'] 
			);

		}

	}


	/* Add top admin menu
	 *  
	 */
	function admin_add_top_menu($admin_bar) {

		$imdb_admin_values = $this->imdb_admin_values;

		$admin_bar->add_menu( 
			array(
				'id'=>'lumiere_top_menu',
				'title' => "<img src='" . $this->configClass->lumiere_pics_dir . "lumiere-ico13x13.png' width='16' height='16' />&nbsp;&nbsp;". 'Lumière',
				'href'  => 'admin.php?page=lumiere_options', 
				'meta'  => 
					array('title' => esc_html__('Lumière Menu'), 
					),
			) 
		);

		$admin_bar->add_menu( 
			array(
				'parent' => 'lumiere_top_menu',
				'id' => 'lumiere_top_menu_general',
				'title' => "<img src='". $this->configClass->lumiere_pics_dir . "menu/admin-general.png' width='16px' />&nbsp;&nbsp;".esc_html__('General'),
				'href'  =>'admin.php?page=lumiere_options',
				'meta'  => 
					array('title' => esc_html__('Main and advanced options'),
					),
			) 
		);
		$admin_bar->add_menu( 
			array(
				'parent' => 'lumiere_top_menu',
				'id' => 'lumiere_top_menu_data',
				'title' => "<img src='". $this->configClass->lumiere_pics_dir . "menu/admin-widget-inside.png' width='16px' />&nbsp;&nbsp;".esc_html__('Data'),
				'href'  =>'admin.php?page=lumiere_options&subsection=dataoption',
				'meta'  => 
					array('title' => esc_html__('Data option and taxonomy'),
					),
			) 
		);
		$admin_bar->add_menu( 
			array(
				'parent' => 'lumiere_top_menu',
				'id' => 'lumiere_top_menu_cache',
				'title' => "<img src='" . $this->configClass->lumiere_pics_dir . "menu/admin-cache.png' width='16px' />&nbsp;&nbsp;".esc_html__('Cache'),
				'href'  =>'admin.php?page=lumiere_options&subsection=cache',
				'meta' => 
					array('title' => esc_html__('Cache options'),
				),
			) 
		);

		$admin_bar->add_menu( 
			array(
				'parent' => 'lumiere_top_menu',
				'id' => 'lumiere_top_menu_help',
				'title' => "<img src='" . $this->configClass->lumiere_pics_dir . "menu/admin-help.png' width='16px' />&nbsp;&nbsp;" . esc_html__('Help'),
				'href' =>'admin.php?page=lumiere_options&subsection=help',
				'meta'  => 
					array('title' => esc_html__('Get support and support plugin development'),

					),
			) 
		);

	}

	/* Display admin pages
	 *
	 */
	function lumiere_admin_pages() {

		$this->display_admin_menu();

		$this->display_admin_menu_subpages();

	}

	/* Display main menu
	 *
	 */
	function display_admin_menu() { 

		$imdb_admin_values = $this->imdb_admin_values;
		$imdb_widget_values = $this->imdb_widget_values;
		$imdb_cache_values = $this->imdb_cache_values;

?>

	<div class=wrap>

		<h2 class="imdblt_padding_bottom_right_fifteen"><img src="<?php echo esc_url ( $this->configClass->lumiere_pics_dir . 'lumiere-ico80x80.png'); ?>" width="80" height="80" align="absmiddle" />&nbsp;&nbsp;<i>Lumière!</i>&nbsp;<?php esc_html_e( 'admin options', 'lumiere-movies'); ?></h2>

		<div class="subpage">
			<div align="left" class="imdblt_double_container">

				<div class="imdblt_padding_five imdblt_flex_auto">
					<img src="<?php echo esc_url( $this->configClass->lumiere_pics_dir . 'menu/admin-general.png'); ?>" align="absmiddle" width="16px" />&nbsp;
					<a title="<?php esc_html_e( 'General Options', 'lumiere-movies'); ?>" href="<?php echo esc_url( admin_url() . "admin.php?page=lumiere_options"); ?>"> <?php esc_html_e( 'General Options', 'lumiere-movies'); ?></a>
				</div>

				<?php 	### Data subpage is relative to what is activated ?>

				<div class="imdblt_padding_five imdblt_flex_auto">
					<img src="<?php echo esc_url( $this->configClass->lumiere_pics_dir . 'menu/admin-widget-inside.png'); ?>" align="absmiddle" width="16px" />&nbsp;


					<a title="<?php esc_html_e( 'Data Management', 'lumiere-movies'); ?>" href="<?php echo esc_url ( admin_url() . "admin.php?page=lumiere_options&subsection=dataoption"); ?>"><?php esc_html_e( 'Data Management', 'lumiere-movies'); ?></a>

	<?php
				// Check if any widget is active:
				// is_active_widget() (pre 5.8 wordpress) or lumiere_block_widget_isactive() (post 5.8)
				if ( ( is_active_widget( '', '', \Lumiere\LumiereWidget::widget_name) == false ) && ( $utils->lumiere_block_widget_isactive() == false ) ) { ?>

					- <em><font size=-2><a href="<?php echo esc_url( admin_url() . 'widgets.php'); ?>"><?php esc_html_e( 'Widget unactivated', 'lumiere-movies'); ?></a></font></em>

	<?php 			} 
				if( ($imdb_admin_values['imdbtaxonomy'] == "0")  || (empty($imdb_admin_values['imdbtaxonomy'])) ) { ?>

					- <em><font size=-2><a href="<?php echo esc_url( admin_url() . "admin.php?page=lumiere_options&generaloption=advanced#imdb_imdbtaxonomy_yes"); ?>"><?php esc_html_e( 'Auto taxonomy', 'lumiere-movies'); ?></a> <?php esc_html_e( 'unactivated', 'lumiere-movies'); ?></font></em>

	<?php 			} ?>

				</div>

				<div class="imdblt_padding_five imdblt_flex_auto">			
					<img src="<?php echo esc_url ( $this->configClass->lumiere_pics_dir . 'menu/admin-cache.png'); ?>" align="absmiddle" width="16px" />&nbsp;
					<a title="<?php esc_html_e( 'Cache management', 'lumiere-movies'); ?>" href="<?php echo admin_url(); ?>admin.php?page=lumiere_options&subsection=cache"><?php esc_html_e( 'Cache management', 'lumiere-movies'); ?></a>
				</div>

				<div align="right" class="imdblt_padding_five imdblt_flex_auto" >
					<img src="<?php echo esc_url( $this->configClass->lumiere_pics_dir . 'menu/admin-help.png'); ?>" align="absmiddle" width="16px" />&nbsp;
					<a title="<?php esc_html_e( 'How to use Lumière!, check FAQs & changelog', 'lumiere-movies');?>" href="<?php echo esc_url( admin_url() . "admin.php?page=lumiere_options&subsection=help"); ?>">
						<i>Lumière!</i> <?php esc_html_e( 'help', 'lumiere-movies'); ?>
					</a>
				</div>
			</div>
		</div>
<?php
	}

	/*  Display admin submenu
	 *
	 */
	function display_admin_menu_subpages() {

		global $imdb_admin_values, $imdb_widget_values, $imdb_cache_values, $configClass, $utilsClass, $logger;

		$imdb_admin_values = $this->imdb_admin_values;
		$imdb_widget_values = $this->imdb_widget_values;
		$imdb_cache_values = $this->imdb_cache_values;
		$configClass = $this->configClass;
		$utilsClass = $this->utilsClass;
		$logger = $this->logger;

		// Make sure cache folder exists and is writable
		$this->configClass->lumiere_create_cache();

		 ### select the sub-page

		if (!isset($_GET['subsection'])) {

			require_once ( $this->rootPath. 'inc/options-general.php'  );

		}

		if ( (isset($_GET['subsection'])) && ($_GET['subsection'] == "dataoption") ) {

			require_once ( $this->rootPath . 'inc/options-data.php' ); 

		} elseif ( (isset($_GET['subsection'])) && ($_GET['subsection'] == "cache") ) {

			require_once ( $this->rootPath. 'inc/options-cache.php' );

		} elseif ( (isset($_GET['subsection'])) && ($_GET['subsection'] == "help") ) {

			$adminHelp = new \Lumiere\Admin\Help();

		}
		// end subselection 

		echo $this->utilsClass->lumiere_admin_signature(); 

	?></div><!-- .wrap -->

<?php
	} 

}


?>
