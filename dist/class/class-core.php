<?php declare( strict_types = 1 );
/**
 * Core Class : Main WordPress actions happen here
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       2.0
 * @package lumiere-movies
 */

namespace Lumiere;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'You can not call directly this page' );
}

use \Lumiere\Imdbphp;
use \Lumiere\Update_Options;
use \Lumiere\Utils;
use \Lumiere\Logger;
use \Imdb\Title;
use \Imdb\Person;

class Core {

	// Trait including the database settings.
	use \Lumiere\Settings_Global;

	/**
	 * \Lumière\Utils class
	 *
	 */
	private Utils $utils_class;

	/**
	 * \Lumiere\Logger class
	 *
	 */
	private Logger $logger;

	/**
	 * \Lumiere\Imdbphp class
	 *
	 */
	private Imdbphp $imdbphp_class;

	/**
	 * Constructor
	 *
	 */
	public function __construct () {

		// Construct Global Settings trait.
		$this->settings_open();

		// Start Utils class.
		$this->utils_class = new Utils();

		// Start Logger class.
		$this->logger = new Logger( 'coreClass' );

		// Start Imdbphp class.
		$this->imdbphp_class = new Imdbphp();

		// redirect popups URLs.
		add_action( 'init', [ $this, 'lumiere_popup_redirect' ], 0 );
		add_action( 'init', [ $this, 'lumiere_popup_redirect_include' ], 0 );

		/* ## Highslide download library, function deactivated upon WordPress plugin team request
		add_filter( 'init', function( $template ) {
			if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . '/wp-admin/admin.php?page=lumiere_options&highslide=yes' ) )
				require_once ( plugin_dir_path( __DIR__ ) . \Lumiere\Settings::HIGHSLIDE_DOWNLOAD_PAGE );

		} );*/

		// Redirect class-search.php
		add_filter(
			'init',
			function(): void {
				if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . \Lumiere\Settings::GUTENBERG_SEARCH_URL ) ) {
					require_once plugin_dir_path( __DIR__ ) . \Lumiere\Settings::GUTENBERG_SEARCH_PAGE;
				}

			}
		);

		// Add Lumière taxonomy.
		if ( ( isset( $this->imdb_admin_values['imdbtaxonomy'] ) ) && ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) ) {

			// Register taxomony and create custom taxonomy pages.
			add_action( 'init', [ $this, 'lumiere_create_taxonomies' ], 0 );

			/*
			 * Add specific class for html tags for functions building links towards taxonomy pages
			 * 1-search for all imdbtaxonomy* in config array,
			 * 2-if active write a filter to add a class to the link to the taxonomy page.
			 *
			 * Can be utilised by get_the_term_list() the_terms() WP function, such as in taxo templates
			 */
			foreach ( $this->utils_class->lumiere_array_key_exists_wildcard( $this->imdb_widget_values, 'imdbtaxonomy*', 'key-value' ) as $key => $value ) {

				if ( $value === '1' ) {

					// Build taxonomy raw name, such as 'lumiere-imdbtaxonomycolor'.
					$taxonomy_raw_string = $this->imdb_admin_values['imdburlstringtaxo'] . $key;
					// Build final hook name, such as 'term_links-lumiere-color'.
					$taxonomy_hook = str_replace( 'imdbtaxonomy', '', "term_links-{$taxonomy_raw_string}" );

					add_filter( $taxonomy_hook, [ $this, 'lumiere_taxonomy_add_class_to_links' ] );

				}
			}

			// redirect admin data taxonomy copy calls to tools/class-move_template_taxonomy.php.
			add_filter(
				'admin_init',
				function(): void {
					if ( isset( $_GET['taxotype'] ) ) {
						require plugin_dir_path( __DIR__ ) . \Lumiere\Settings::MOVE_TEMPLATE_TAXONOMY_PAGE;

					}
				}
			);

		}

		/**
		 * Admin interface.
		 */

		if ( is_admin() ) {

			// Add admin menu.
			require_once __DIR__ . '/class-admin.php';
			$lumiere_admin_class = new Admin();
			add_action( 'init', [ $lumiere_admin_class, 'lumiere_admin_menu' ] );

			// Add the metabox to editor.
			require_once __DIR__ . '/class-metabox.php';
			$lumiere_metabox_class = new Metabox();
			add_action( 'admin_init', [ $lumiere_metabox_class, 'lumiere_start_metabox' ] );

			add_filter( 'plugin_row_meta', [ $this, 'lumiere_add_sponsor_plugins_page' ], 10, 4 );
		}

		// Register admin scripts.
		add_action( 'admin_enqueue_scripts', [ $this, 'lumiere_register_admin_assets' ], 0 );

		// Add admin header.
		add_action( 'admin_enqueue_scripts', [ $this, 'lumiere_execute_admin_assets' ] );

		// Add admin tinymce button for wysiwig editor.
		add_action( 'admin_enqueue_scripts', [ $this, 'lumiere_execute_tinymce' ], 2 );

		/**
		 * Frontpage.
		 */

		// Registers javascripts and styles.
		add_action( 'init', [ $this, 'lumiere_register_assets' ], 0 );

		// Execute javascripts and styles.
		add_action( 'wp_enqueue_scripts', [ $this, 'lumiere_execute_assets' ], 0 );

		// Add metas tags.
		add_action( 'wp_head', [ $this, 'lumiere_add_metas' ], 5 );

		// Change title of popups.
		add_filter( 'pre_get_document_title', [ $this, 'lumiere_change_popup_title' ] );

		// Register Gutenberg blocks.
		add_action( 'init', [ $this, 'lumiere_register_gutenberg_blocks' ] );

		/**
		 * Updates.
		 */

		// On updating lumiere plugin.
		add_action( 'upgrader_process_complete', [ $this, 'lumiere_on_lumiere_upgrade_completed' ], 10, 2 );

		// Add cron schedules.
		add_action( 'lumiere_cron_hook', [ $this, 'lumiere_cron_exec_once' ], 0 );

	}

	/**
	 * Add sponsor link in the Plugins list table.
	 * Filters the array of row meta for each plugin to display Lumière's metas
	 *
	 * @param array<string>|null $plugin_meta An array of the plugin's metadata. Can be null.
	 * @param string $plugin_file_name Path to the plugin file relative to the plugins directory.
	 * @param array<string> $plugin_data An array of plugin data.
	 * @param string $status Status filter currently applied to the plugin list.
	 *        Possible values are: 'all', 'active', 'inactive', 'recently_activated', 'upgrade', 'mustuse',
	 *        'dropins', 'search', 'paused', 'auto-update-enabled', 'auto-update-disabled'.
	 * @return array<string>|null $plugin_meta An array of the plugin's metadata.
	 */
	public function lumiere_add_sponsor_plugins_page ( ?array $plugin_meta, string $plugin_file_name, array $plugin_data, string $status ): ?array {
		if ( 'lumiere-movies/lumiere-movies.php' === $plugin_file_name ) {
			$plugin_meta[] = sprintf(
				'<a href="%1$s"><span class="dashicons dashicons-coffee" aria-hidden="true" style="font-size:14px;line-height:1.3"></span>%2$s</a>',
				'https://www.paypal.me/jcvignoli',
				esc_html__( 'Sponsor', 'lumiere-movies' )
			);
		}
		return $plugin_meta;
	}

	/**
	 *  Register frontpage scripts and styles
	 *
	 */
	public function lumiere_register_assets(): void {

		// Common assets to admin and frontpage
		$this->lumiere_register_both_assets();

		// Register frontpage script
		wp_register_script(
			'lumiere_scripts',
			$this->config_class->lumiere_js_dir . 'lumiere_scripts.min.js',
			[ 'jquery' ],
			$this->config_class->lumiere_version,
			true
		);

		// Register highslide scripts and styles
		wp_register_script(
			'lumiere_highslide',
			$this->config_class->lumiere_js_dir . 'highslide/highslide-with-html.min.js',
			[],
			$this->config_class->lumiere_version,
			true
		);
		wp_register_script(
			'lumiere_highslide_options',
			$this->config_class->lumiere_js_dir . 'highslide-options.min.js',
			[ 'lumiere_highslide' ],
			$this->config_class->lumiere_version,
			true
		);
		wp_enqueue_style(
			'lumiere_highslide',
			$this->config_class->lumiere_css_dir . 'highslide.min.css',
			[],
			$this->config_class->lumiere_version
		);

		// Register customised main style, located in active theme directory
		if ( file_exists( get_stylesheet_directory_uri() . '/lumiere.css' ) ) {
			wp_register_style(
				'lumiere_style_custom',
				get_stylesheet_directory_uri() . '/lumiere.css',
				[],
				$this->config_class->lumiere_version
			);
		}

		// Register main style
		wp_register_style(
			'lumiere_style_main',
			$this->config_class->lumiere_css_dir . 'lumiere.min.css',
			[],
			$this->config_class->lumiere_version
		);

		// Register OceanWP theme fixes for popups only
		wp_register_style(
			'lumiere_style_oceanwpfixes_popups',
			$this->config_class->lumiere_css_dir . 'lumiere_subpages-oceanwpfixes.min.css',
			[],
			$this->config_class->lumiere_version
		);

		// Register OceanWP theme fixes for all pages but popups
		wp_register_style(
			'lumiere_style_oceanwpfixes_general',
			$this->config_class->lumiere_css_dir . 'lumiere_extrapages-oceanwpfixes.min.css',
			[],
			$this->config_class->lumiere_version
		);
	}

	/*  Register admin scripts and styles
	 *
	 */
	public function lumiere_register_admin_assets(): void {

		// Common assets to admin and frontpage
		$this->lumiere_register_both_assets();

		// Register paths, fake script to get a hook for add inline scripts
		wp_register_script(
			'lumiere_scripts_admin_vars',
			'',
			[],
			$this->config_class->lumiere_version,
			true
		);

		// Register admin styles
		wp_register_style(
			'lumiere_css_admin',
			$this->config_class->lumiere_css_dir . 'lumiere-admin.min.css',
			[],
			$this->config_class->lumiere_version
		);

		// Register admin scripts
		wp_register_script(
			'lumiere_scripts_admin',
			$this->config_class->lumiere_js_dir . 'lumiere_scripts_admin.min.js',
			[ 'jquery' ],
			$this->config_class->lumiere_version,
			false
		);

		// Register gutenberg admin scripts
		wp_register_script(
			'lumiere_scripts_admin_gutenberg',
			$this->config_class->lumiere_js_dir . 'lumiere_scripts_admin_gutenberg.min.js',
			[ 'jquery' ],
			$this->config_class->lumiere_version,
			false
		);

		// Register confirmation script upon deactivation
		wp_register_script(
			'lumiere_deactivation_plugin_message',
			$this->config_class->lumiere_js_dir . 'lumiere_admin_deactivation_msg.min.js',
			[ 'jquery' ],
			$this->config_class->lumiere_version,
			true
		);

		// Quicktag
		wp_register_script(
			'lumiere_quicktag_addbutton',
			$this->config_class->lumiere_js_dir . 'lumiere_admin_quicktags.min.js',
			[ 'quicktags' ],
			$this->config_class->lumiere_version,
			true
		);

	}

	/*  Common assets registration
	 *  For both admin and frontpage utilisation scripts and styles
	 *
	 */
	public function lumiere_register_both_assets(): void {

		// Register hide/show script
		wp_register_script(
			'lumiere_hide_show',
			$this->config_class->lumiere_js_dir . 'lumiere_hide_show.min.js',
			[ 'jquery' ],
			$this->config_class->lumiere_version,
			true
		);

	}

	/**
	 *  Register TinyMCE
	 * @param string $hook
	 */
	public function lumiere_execute_tinymce( string $hook ): void {

		// Don't bother doing this stuff if the current user lacks permissions
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		// Add only in Rich Editor mode for post.php and post-new.php pages
		if (
			( get_user_option( 'rich_editing' ) === 'true' )
			&& ( ( 'post.php' === $hook ) || ( 'post-new.php' === $hook ) )
		) {

			add_filter( 'mce_external_plugins', [ $this, 'lumiere_tinymce_addbutton' ] );
			add_filter( 'mce_buttons', [ $this, 'lumiere_tinymce_button_position' ] );

		}
	}

	/**
	 * Change TinyMCE buttons position
	 * @param mixed[] $buttons
	 * @return mixed[]
	 */
	public function lumiere_tinymce_button_position( array $buttons ): array {

		array_push( $buttons, 'separator', 'lumiere_tiny' );

		return $buttons;

	}

	/**
	 * Add TinyMCE buttons
	 * @param mixed[] $plugin_array
	 * @return mixed[]
	 */
	public function lumiere_tinymce_addbutton( array $plugin_array ): array {

		$plugin_array['lumiere_tiny'] = $this->config_class->lumiere_js_dir . 'lumiere_admin_tinymce_editor.min.js';

		return $plugin_array;

	}

	/**
	 *  Register gutenberg blocks
	 *
	 */
	public function lumiere_register_gutenberg_blocks(): void {

		wp_register_script(
			'lumiere_gutenberg_main',
			$this->config_class->lumiere_blocks_dir . 'main-block.min.js',
			[ 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-data' ],
			$this->config_class->lumiere_version,
			false
		);

		wp_register_script(
			'lumiere_gutenberg_buttons',
			$this->config_class->lumiere_blocks_dir . 'buttons.min.js',
			[ 'wp-element', 'wp-compose', 'wp-components', 'wp-i18n', 'wp-data' ],
			$this->config_class->lumiere_version,
			false
		);

		// Style.
		wp_register_style( 'lumiere_gutenberg_main', $this->config_class->lumiere_blocks_dir . 'main-block.min.css', [], $this->config_class->lumiere_version );
		register_block_style(
			'lumiere/main',
			[
				'name' => 'main-block',
				'label' => 'Main block',
				'style_handle' => 'lumiere_gutenberg_main',
			]
		);

		// Register block script and style.
		register_block_type(
			'lumiere/main',
			[
				'style' => 'lumiere_gutenberg_main', // Loads both on editor and frontend.
				'editor_script' => 'lumiere_gutenberg_main', // Loads only on editor.
			]
		);

		register_block_type(
			'lumiere/buttons',
			[
				'editor_script' => 'lumiere_gutenberg_buttons', // Loads only on editor.
			]
		);

		wp_enqueue_script( 'lumiere_scripts_admin_gutenberg' );

	}

	/**
	 * Add the stylesheet & javascript to frontpage.
	 *
	 */
	public function lumiere_execute_assets (): void {

		// Use local template lumiere.css if there is one in current theme folder.
		if ( file_exists( get_template_directory() . '/lumiere.css' ) ) { // a lumiere.css exists inside theme folder, use it!
			wp_enqueue_style( 'lumiere_style_custom' );

		} else {

			wp_enqueue_style( 'lumiere_style_main' );
		}

		// OceanWp template css fix.
		// enqueue lumiere.css only if using oceanwp template.
			// Popups.
		if (
			( 0 === stripos( get_template_directory_uri(), esc_url( site_url() . '/wp-content/themes/oceanwp' ) ) )
			&&
			( Utils::str_contains( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->config_class->lumiere_urlstring ) )
		) {

			wp_enqueue_style( 'lumiere_style_oceanwpfixes_popups' );

			// All other cases.
		} elseif ( 0 === stripos( get_template_directory_uri(), esc_url( site_url() . '/wp-content/themes/oceanwp' ) ) ) {

			wp_enqueue_style( 'lumiere_style_oceanwpfixes_general' );

		}

		// Highslide popup.
		if ( $this->imdb_admin_values['imdbpopup_highslide'] === '1' ) {

			wp_enqueue_style( 'lumiere_highslide' );

			wp_enqueue_script( 'lumiere_highslide' );

			// Pass variables to javascript highslide-options.js.
			wp_add_inline_script(
				'lumiere_highslide_options',
				$this->config_class->lumiere_scripts_highslide_vars,
				'before',
			);

			wp_enqueue_script( 'lumiere_highslide_options' );

		}

		wp_enqueue_script( 'lumiere_hide_show' );

		wp_enqueue_script( 'lumiere_scripts' );

		// Pass variable to javascript lumiere_scripts.js.
		wp_add_inline_script(
			'lumiere_scripts',
			$this->config_class->lumiere_scripts_vars,
			'before'
		);

	}

	/**
	 *  Add assets of Lumière admin pages
	 *
	 */
	public function lumiere_execute_admin_assets ( string $hook ): void {

		$imdb_admin_values = $this->imdb_admin_values;

		// Load scripts only on Lumière admin pages.
		// + WordPress edition pages + Lumière own pages (ie gutenberg search).
		if (
			( 'toplevel_page_lumiere_options' === $hook )
			|| ( 'widgets.php' === $hook )
			|| ( 'post.php' === $hook )
			|| ( 'post-new.php' === $hook )
			|| ( Utils::lumiere_array_contains_term( $this->config_class->lumiere_list_all_pages, $_SERVER['REQUEST_URI'] ) ) // All sort of Lumière pages.
			|| ( Utils::str_contains( $_SERVER['REQUEST_URI'], 'admin.php?page=lumiere_options' ) )
		) {

			// Load main css.
			wp_enqueue_style( 'lumiere_css_admin' );

			// Load main js.
			wp_enqueue_script( 'lumiere_scripts_admin' );

			// Pass path variables to javascripts.
			wp_add_inline_script(
				'lumiere_scripts_admin',
				$this->config_class->lumiere_scripts_admin_vars,
				'before'
			);

			// Load hide/show js.
			wp_enqueue_script( 'lumiere_hide_show' );

		}

		// On 'plugins.php' show a confirmation dialogue if.
		// 'imdbkeepsettings' is set on delete Lumière! options.
		if ( ( ( ! isset( $this->imdb_admin_values['imdbkeepsettings'] ) ) || ( $this->imdb_admin_values['imdbkeepsettings'] === '0' ) ) && ( 'plugins.php' === $hook )  ) {

			wp_enqueue_script( 'lumiere_deactivation_plugin_message' );

		}

		//  Add Quicktag.
		if ( ( ( 'post.php' === $hook ) || ( 'post-new.php' === $hook ) ) && ( wp_script_is( 'quicktags' ) ) ) {

			wp_enqueue_script( 'lumiere_quicktag_addbutton' );

		}

	}

	/**
	 *  Redirect the popups to a proper URL
	 */
	public function lumiere_popup_redirect(): void {

		// The popup is for films
		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . '/wp-content/plugins/lumiere-movies/' . \Lumiere\Settings::POPUP_SEARCH_URL ) ) {

			$query_film = preg_match_all( '#film=(.*)#', $_SERVER['REQUEST_URI'], $match_query_film, PREG_UNMATCHED_AS_NULL );
			$match_query_film_film = explode( '&', $match_query_film[1][0] );
			$query_mid = preg_match_all( '#mid=(.*)#', $_SERVER['REQUEST_URI'], $match_query_mid, PREG_UNMATCHED_AS_NULL );
			$match_query_film_mid = explode( '&', $match_query_mid[1][0] );
			$query_info = preg_match_all( '#info=(.*)#', $_SERVER['REQUEST_URI'], $match_query_info, PREG_UNMATCHED_AS_NULL );
			$query_norecursive = preg_match_all( '#norecursive=(.*)#', $_SERVER['REQUEST_URI'], $match_query_norecursive, PREG_UNMATCHED_AS_NULL );

			$url = ( isset( $match_query_film_film[0] ) ) ? $this->config_class->lumiere_urlstringfilms . $match_query_film_film[0] . '/' : $this->config_class->lumiere_urlstringfilms . $match_query_film_mid[0] . '/';
			wp_safe_redirect(
				add_query_arg(
					[
						'film' => $match_query_film_film[0],
						'mid' => $match_query_film_mid[0],
						'info' => $match_query_info[1][0],
						'norecursive' => $match_query_norecursive[1][0],
					],
					get_site_url( null, $url )
				)
			);
			exit();
		}
		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . '/wp-content/plugins/lumiere-movies/' . \Lumiere\Settings::POPUP_MOVIE_URL ) ) {

			$query_film = preg_match_all( '#film=(.*)#', $_SERVER['REQUEST_URI'], $match_query_film, PREG_UNMATCHED_AS_NULL );
			$match_query_film_film = explode( '&', $match_query_film[1][0] );
			$query_mid = preg_match_all( '#mid=(.*)#', $_SERVER['REQUEST_URI'], $match_query_mid, PREG_UNMATCHED_AS_NULL );
			$match_query_film_mid = explode( '&', $match_query_mid[1][0] );
			$query_info = preg_match_all( '#info=(.*)#', $_SERVER['REQUEST_URI'], $match_query_info, PREG_UNMATCHED_AS_NULL );
			$query_norecursive = preg_match_all( '#norecursive=(.*)#', $_SERVER['REQUEST_URI'], $match_query_norecursive, PREG_UNMATCHED_AS_NULL );
			$url = ( strlen( $match_query_film_film[0] ) > 0 ) ? $this->config_class->lumiere_urlstringfilms . $match_query_film_film[0] . '/' : $this->config_class->lumiere_urlstringfilms . $match_query_film_mid[0] . '/';

			wp_safe_redirect(
				add_query_arg(
					[
						'film' => $match_query_film_film[0],
						'mid' => $match_query_film_mid[0],
						'info' => $match_query_info[1][0],
						'norecursive' => $match_query_norecursive[1][0],
					],
					get_site_url( null, $url )
				)
			);
			exit();

		}
		// The popup is for persons.
		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . '/wp-content/plugins/lumiere-movies/' . \Lumiere\Settings::POPUP_PERSON_URL ) ) {
			$query_person_mid = preg_match( '#mid=(.*)#', $_SERVER['REQUEST_URI'], $match_query_mid, PREG_UNMATCHED_AS_NULL );
			$match_query_person_mid = explode( '&', $match_query_mid[1] );
			$query_person_info = preg_match_all( '#info=(.*)#', $_SERVER['REQUEST_URI'], $match_query_info, PREG_UNMATCHED_AS_NULL );
			$match_query_person_info = explode( '&', $match_query_info[1] );
			$query_person_film = preg_match_all( '#film=(.*)&?#', $_SERVER['REQUEST_URI'], $match_query_person_film, PREG_UNMATCHED_AS_NULL );
			$url = $this->config_class->lumiere_urlstringperson . $match_query_person_mid[0] . '/';

				//wp_redirect(  add_query_arg( 'mid' => $match_query_mid[1][0], $url ) , 301 ); # one arg only
			wp_safe_redirect(
				add_query_arg(
					[
						'mid' => $match_query_person_mid[0],
						'film' => $match_query_person_film[1][0],
						'info' => $match_query_person_info[0],
					],
					get_site_url( null, $url )
				)
			);
			exit();
		}
	}

	// pages to be included when the redirection is done.
	public function lumiere_popup_redirect_include(): void {

		// Include films popup.
		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->config_class->lumiere_urlstringsearch ) ) {
			require_once plugin_dir_path( __DIR__ ) . \Lumiere\Settings::POPUP_SEARCH_URL;
		}

		// Include films popup.
		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->config_class->lumiere_urlstringfilms ) ) {
			require_once plugin_dir_path( __DIR__ ) . \Lumiere\Settings::POPUP_MOVIE_URL;
		}

		// Include persons popup.
		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->config_class->lumiere_urlstringperson ) ) {
			require_once plugin_dir_path( __DIR__ ) . \Lumiere\Settings::POPUP_PERSON_URL;
		}

	}

	/**
	 *  Change the title of the popups according to the movie's or person's data
	 */
	public function lumiere_change_popup_title( string $title ): string {

		$imdb_cache_values = $this->imdb_cache_values;
		$config = $this->config_class;
		$filmid_sanitized = ''; // initialisation.

		// Change the title for the query search popup.
		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . \Lumiere\Settings::GUTENBERG_SEARCH_URL ) ) {
			return esc_html__( 'Lumiere Query Interface', 'lumiere-movies' );
		}

		// Change the titles for popups.
		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->config_class->lumiere_urlstring ) ) {

			// Add cache dir to properly save data in real cache dir.
			$this->imdbphp_class->cachedir = $imdb_cache_values['imdbcachedir'];

			// Display the title if /url/films.
			if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->config_class->lumiere_urlstringfilms ) ) {

				// If mid but no film, do a query using the mid
				if ( ( isset( $_GET['mid'] ) ) && ( ! isset( $_GET['film'] ) ) ) {

					$movieid_sanitized = isset( $_GET['mid'] ) ? sanitize_text_field( strval( $_GET['mid'] ) ) : '';
					$movie = new Title( $movieid_sanitized, $this->imdbphp_class );
					$filmid_sanitized = esc_html( $movie->title() );
				}

				$title_name = strlen( $filmid_sanitized ) !== 0 ? $filmid_sanitized : Utils::lumiere_name_htmlize( $_GET['film'] );

				$title = esc_html__( 'Informations about ', 'lumiere-movies' ) . $title_name . ' - Lumi&egrave;re movies';

				// Display the title if /url/person
			} elseif ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->config_class->lumiere_urlstringperson ) ) {

				if ( isset( $_GET['mid'] ) ) {

					$mid_sanitized = sanitize_text_field( strval( $_GET['mid'] ) );
					$person = new Person( $mid_sanitized, $this->imdbphp_class );
					$person_name_sanitized = sanitize_text_field( $person->name() );
				}

				$title = isset( $person_name_sanitized ) ? esc_html__( 'Informations about ', 'lumiere-movies' ) . $person_name_sanitized . ' - Lumi&egrave;re movies' : esc_html__( 'Unknown', 'lumiere-movies' ) . '- Lumi&egrave;re movies';

				// Display the title if /url/search
			} elseif ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->config_class->lumiere_urlstringsearch ) ) {
				$title_name = isset( $_GET['film'] ) ? esc_html( $_GET['film'] ) : esc_html__( 'No query entered', 'lumiere-movies' );
				$title = esc_html__( 'Search query for ', 'lumiere-movies' ) . $title_name . ' - Lumi&egrave;re movies ';
			}

		}

		return $title;
	}

	/**
	 * Add a class to taxonomy links constructed by WordPress
	 * @param array<string> $links
	 * @return array<string>
	 */
	public function lumiere_taxonomy_add_class_to_links( array $links ): array {

		return str_replace( '<a href="', '<a class="linktaxonomy" href="', $links );

	}

	/**
	 * Add new meta tags in popups <head>
	 */
	public function lumiere_add_metas(): void {

		$my_canon = '';

		// Change the metas only for popups.
		if ( ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->config_class->lumiere_urlstringfilms ) ) || ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->config_class->lumiere_urlstringsearch ) ) || ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->config_class->lumiere_urlstringperson ) ) ) {

			// ADD FAVICONS.
			echo "\t\t" . '<!-- Lumiere Movies -->';
			echo "\n" . '<link rel="apple-touch-icon" sizes="180x180" href="' . esc_url( plugin_dir_url( __DIR__ ) . 'pics/favicon/apple-touch-icon.png' ) . '" />';
			echo "\n" . '<link rel="icon" type="image/png" sizes="32x32" href="' . esc_url( plugin_dir_url( __DIR__ ) . 'pics/favicon/favicon-32x32.png' ) . '" />';
			echo "\n" . '<link rel="icon" type="image/png" sizes="16x16" href="' . esc_url( plugin_dir_url( __DIR__ ) . 'pics/favicon/favicon-16x16.png' ) . '" />';
			echo "\n" . '<link rel="manifest" href="' . esc_url( plugin_dir_url( __DIR__ ) . 'pics/favicon/site.webmanifest' ) . '" />';

			// ADD CANONICAL.
			// Canonical for search popup.
			if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->config_class->lumiere_urlstringsearch ) ) {
				$film_sanitized = '';
				$film_sanitized = isset( $_GET['film'] ) ? Utils::lumiere_name_htmlize( $_GET['film'] ) : '';
				$my_canon = $this->config_class->lumiere_urlpopupsearch . '?film=' . $film_sanitized . '&norecursive=yes';
				echo "\n" . '<link rel="canonical" href="' . esc_url( $my_canon ) . '" />';
			}

			// Canonical for movies popups.
			if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->config_class->lumiere_urlstringfilms ) ) {
				$mid_sanitized = isset( $_GET['mid'] ) ? sanitize_text_field( strval( $_GET['mid'] ) ) : '';
				$film_sanitized = '';
				$film_sanitized = isset( $_GET['film'] ) ? Utils::lumiere_name_htmlize( $_GET['film'] ) : '';
				$info_sanitized = '';
				$info_sanitized = isset( $_GET['info'] ) ? esc_html( $_GET['info'] ) : '';
				$my_canon = $this->config_class->lumiere_urlpopupsfilms . $film_sanitized . '/?film=' . $film_sanitized . '&mid=' . $mid_sanitized . '&info=' . $info_sanitized;
				if ( isset( $film_sanitized ) && strlen( $film_sanitized ) > 0 ) {
					echo "\n" . '<link rel="canonical" href="' . esc_url( $my_canon ) . '" />';
					echo "\n" . '<meta property="article:tag" content="' . esc_html( $film_sanitized ) . '" />';
				}
			}

			// Canonical for people popups.
			if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->config_class->lumiere_urlstringperson ) ) {
				$mid_sanitized = isset( $_GET['mid'] ) ? sanitize_text_field( $_GET['mid'] ) : '';
				$info_sanitized = isset( $_GET['info'] ) ? esc_html( $_GET['info'] ) : '';
				$my_canon = $this->config_class->lumiere_urlpopupsperson . $mid_sanitized . '/?mid=' . $mid_sanitized . '&info=' . $info_sanitized;
				if ( strlen( $mid_sanitized ) > 0 ) {
					$person = new Person( $mid_sanitized, $this->imdbphp_class );
					$person_name_sanitized = esc_html( $person->name() );
					echo "\n" . '<link rel="canonical" href="' . esc_url( $my_canon ) . '" />';
					echo "\n" . '<meta property="article:tag" content="' . esc_html( $person_name_sanitized ) . '" />';
				}
			}

			echo "\n\t\t" . '<!-- Lumiere Movies -->' . "\n";

			// Prevent WordPress from inserting a canonical tag.
			remove_action( 'wp_head', 'rel_canonical' );
			// Prevent WordPress from inserting favicons.
			remove_action( 'wp_head', 'wp_site_icon', 99 );

		}

	}

	/**
	 * Run on lumiere WordPress upgrade
	 *
	 * @param \WP_Upgrader $upgrader_object Type of action. Default 'update'.
	 * @param mixed[] $options Type of update process, such as 'plugin', 'theme', 'translation' or 'core'
	 */
	public function lumiere_on_lumiere_upgrade_completed( \WP_Upgrader $upgrader_object, array $options ): void {

		// Start the logger.
		do_action( 'lumiere_logger' );

		// If an update has taken place and the updated type is plugins and the plugins element exists.
		if ( $options['type'] === 'plugin' && $options['action'] === 'update' && isset( $options['plugins'] ) ) {

			// Iterate through the plugins being updated and check if ours is there.
			foreach ( $options['plugins'] as $plugin ) {

				// It is Lumière!, so update
				if ( $plugin === 'lumiere-movies/lumiere-movies.php' ) {

					// Call the class to update options
					require_once plugin_dir_path( __DIR__ ) . \Lumiere\Settings::UPDATE_OPTIONS_PAGE;

					$start_update_options = new Update_Options();

					$this->logger->log()->debug( '[Lumiere][coreClass][updater] Lumière _on_plugin_upgrade_ hook successfully run.' );

				}
			}
		}
	}

	/**
	 * Run on plugin activation
	 */
	public function lumiere_on_activation(): void {

		/* remove activation issue
		ob_start(); */

		// Start the logger.
		$this->logger->lumiere_start_logger( 'coreClass', false /* Deactivate the onscreen log, so WordPress activation doesn't trigger any error if debug is activated */ );

		$plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
		check_admin_referer( "activate-plugin_{$plugin}" );

		/* Create the value of number of updates on first install */
		// Start Settings class.
		if ( ! isset( $this->imdb_admin_values['imdbHowManyUpdates'] ) ) {

			new Settings();
			$this->logger->log()->info( "[Lumiere][coreClass][activation] Lumière option 'imdbHowManyUpdates' successfully created." );

		} else {

			$this->logger->log()->info( "[Lumiere][coreClass][activation] Lumière option 'imdbHowManyUpdates' already exists." );

		}

		/* Create the cache folders */
		if ( $this->config_class->lumiere_create_cache() === true ) {

			$this->logger->log()->info( '[Lumiere][coreClass][activation] Lumière cache successfully created.' );

		} else {

			$this->logger->log()->info( '[Lumiere][coreClass][activation] Lumière cache has not been created.' );

		}

		/* Set up WP Cron if it doesn't exist */
		if ( wp_next_scheduled( 'lumiere_cron_hook' ) === false ) {

			// Runned thee times to make sure that no update is missed

			// Cron to run once, in 10 minutes.
			wp_schedule_single_event( time() + 600, 'lumiere_cron_hook' );

			// Cron to run once, in 30 minutes.
			wp_schedule_single_event( time() + 1800, 'lumiere_cron_hook' );

			// Cron to run once, in 1 hour.
			wp_schedule_single_event( time() + 3600, 'lumiere_cron_hook' );

			$this->logger->log()->debug( '[Lumiere][coreClass][activation] Lumière crons successfully set up.' );

		} else {

			$this->logger->log()->error( '[Lumiere][coreClass][activation] Crons were not set up.' );

		}

		$this->logger->log()->debug( '[Lumiere][coreClass][activation] Lumière plugin activated.' );

		/* remove activation issue
		trigger_error(ob_get_contents(),E_USER_ERROR);*/
	}

	/**
	 *   Run on plugin deactivation
	 */
	public function lumiere_on_deactivation(): void {

		// Start the logger.
		$this->logger->lumiere_start_logger( 'coreClass', false /* Deactivate the onscreen log, so WordPress activation doesn't trigger any error if debug is activated */ );

		// Remove WP Cron shoud they exist.
		$wp_cron_list = is_iterable( _get_cron_array() ) ? _get_cron_array() : [];
		foreach ( $wp_cron_list as $time => $hook ) {
			if ( isset( $hook['lumiere_cron_hook'] ) ) {
				$timestamp = (int) wp_next_scheduled( 'lumiere_cron_hook' );
				wp_unschedule_event( $timestamp, 'lumiere_cron_hook' );
				$this->logger->log()->info( '[Lumiere][coreClass][deactivation] Cron removed' );
			}
		}

		$this->logger->log()->info( '[Lumiere][coreClass][deactivation] Lumière deactivated' );

	}

	/**
	 * Register taxomony and create custom taxonomy pages
	 */
	public function lumiere_create_taxonomies(): void {

		$imdb_admin_values = $this->imdb_admin_values;
		$imdb_widget_values = $this->imdb_widget_values;

		foreach ( $this->utils_class->lumiere_array_key_exists_wildcard( $imdb_widget_values, 'imdbtaxonomy*', 'key-value' ) as $key => $value ) {

			$filter_taxonomy = str_replace( 'imdbtaxonomy', '', $key );

			if ( $imdb_widget_values[ 'imdbtaxonomy' . $filter_taxonomy ] === '1' ) {

				register_taxonomy(
					$imdb_admin_values['imdburlstringtaxo'] . $filter_taxonomy,
					[ 'page', 'post' ],
					[
						/* remove metaboxes from edit interface, keep the menu of post */
						'show_ui' => true,        /* whether to manage taxo in UI */
						'show_in_quick_edit' => false,       /* whether to show taxo in edit interface */
						'meta_box_cb' => false,       /* whether to show taxo in metabox */
					/* other settings */
						'hierarchical' => false,
						'public' => true,
						/*      'args'              => array('lang' => 'en'), REMOVED 2021 08 07, what's the point? */
						'menu_icon' => $imdb_admin_values['imdbplugindirectory'] . 'pics/lumiere-ico13x13.png',
						'label' => 'Lumière ' . $filter_taxonomy,
						'query_var' => $imdb_admin_values['imdburlstringtaxo'] . $filter_taxonomy,
						'rewrite' => [ 'slug' => $imdb_admin_values['imdburlstringtaxo'] . $filter_taxonomy ],
					]
				);
			}
		}

	}

	/**
	 * Copy metas from one post in original language to another post in other language
	 * Polylang version
	 * not yet implemented, not sure if needed, maybe not, need further tests
	 * to call it: add_filter('pll_copy_post_metas', 'lumiere_copy_post_metas_polylang', 10, 2)
	 */
	/*
	public function lumiere_copy_post_metas_polylang( $metas, $sync) {

		if(!is_admin()) return false;
		if($sync) return $metas;
		global $current_screen;

		if($current_screen-post_type == 'wine'){ // substitue 'wine' with post type
			$keys = array_key(get_fields($_GET['imdbltid']));
			return array_merge($metas, $keys);
		}

		return $metas;

	}
	*/

	/**
	 *  Cron to run execute once
	 *
	 */
	public function lumiere_cron_exec_once(): void {

		$this->logger = new Logger( 'coreClass' );

		// Start the logger, since it is executed before the init.
		do_action( 'lumiere_logger' );

		$this->logger->log()->debug( '[Lumiere][coreClass] Cron running...' );

		// Update options
		// this udpate is also run in upgrader_process_complete, but the process is not always reliable
		require_once plugin_dir_path( __DIR__ ) . \Lumiere\Settings::UPDATE_OPTIONS_PAGE;
		$start_update_options = new Update_Options();

	}

}

