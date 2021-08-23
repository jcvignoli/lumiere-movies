<?php declare( strict_types = 1 );
/**
 * IMDbPHP search: Display search results related to a movie to get their IMDbID
 *
 * @author      Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright       2021, Lost Highway
 *
 * @version     1.0
 * @package lumiere-movies
 */

namespace Lumiere;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( esc_html__( 'You are not allowed to call this page directly.', 'lumiere-movies' ) );
}

use \Lumiere\Settings;
use \Lumiere\Utils;
use \Imdb\TitleSearch;

class Search {

	/**
	 * Class \Lumiere\Utils
	 *
	 *  @var object
	 */
	private object $utilsClass;

	/**
	 * Class \Lumiere\Settings
	 *
	 *  @var object
	 */
	private object $config_class;

	/**
	 * Class \Monolog\Logger
	 *
	 *  @var object
	 */
	private object $logger;

	/**
	 * Settings from class \Lumiere\Settings
	 *
	 *  @var array
	 */
	private array $imdb_admin_values;

	/**
	 * Settings from class \Lumiere\Settings
	 * To include the type of (movie, TVshow, Games) search
	 *
	 *  @var array
	 */
	private array $typeSearch;

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		//As an external file, need to include manually bootstrap
		require_once plugin_dir_path( __DIR__ ) . 'bootstrap.php';

		if ( ! class_exists( '\Lumiere\Settings' ) ) {
			wp_die( esc_html__( 'Cannot start lumiere search, class Lumière Settings not found', 'lumiere-movies' ) );
		}

		// Get options from database
		$this->imdb_admin_values = get_option( Settings::LUMIERE_ADMIN_OPTIONS );

		// Start Settings class
		$this->config_class = new Settings( 'gutenbergSearch' );

		// Get the type of search: movies, series, games
		$this->typeSearch = $this->config_class->lumiere_select_type_search();

		// Start Utils Class
		$this->utilsClass = new Utils();

		// Start the debugging
		add_action( 'wp_head', [ $this, 'lumiere_maybe_start_debug' ], 0 );

		// Start the logger
		$this->config_class->lumiere_start_logger( 'gutenbergSearch' );
		$this->logger = $this->config_class->loggerclass;

		// Register admin scripts.
		add_action( 'wp_enqueue_scripts', [ $this, 'lumiere_register_search_script' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'lumiere_run_search_script' ] );

		$this->layout();

	}

	/**
	 *  Wrapps the start of the logger
	 *  Allows to start later in the process
	 */
	public function lumiere_maybe_start_debug(): void {

		if ( ( isset( $this->imdb_admin_values['imdbdebug'] ) ) && ( '1' === $this->imdb_admin_values['imdbdebug'] ) && ( $this->utilsClass->debug_is_active === false ) ) {

			$this->utilsClass->lumiere_activate_debug();

		}
	}

	/**
	 * Display layout
	 *
	 */
	private function layout() {

		echo '<!DOCTYPE html>';
		echo '<html>';
		echo '<head>';

		wp_head();

		echo '</head>';
		echo '<body id="gutenberg_search">';

		if ( ! isset( $_GET['moviesearched'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'lumiere_search' ) ) {
			$this->initial_form();
		}

		$this->maybe_results_page();

		wp_footer();

		echo '</body>';
		echo '</html>';

		// Avoid continuing displaying WP pages
		exit();

	}

	/**
	 * Display results page
	 * Conditionnal: only if form submitted
	 *
	 */
	private function maybe_results_page (): void {

		if ( ( isset( $_GET['moviesearched'] ) ) && ( ! empty( $_GET['moviesearched'] ) ) && wp_verify_nonce( $_GET['_wpnonce'], 'lumiere_search' ) > 0 ) {

			# Initialization of IMDBphp
			$search = new TitleSearch( $this->config_class, $this->logger );

			$search_term = sanitize_text_field( $_GET['moviesearched'] );

			$this->logger->debug( "[Lumiere][gutenbergSearch] Querying '$search_term'" );

			$results = $search->search( $search_term, $this->typeSearch );

			?>
<h1 class="searchmovie_title lumiere_italic"><?php esc_html_e( 'Results related to your query:', 'lumiere-movies' ); ?> <span class="lumiere_gutenberg_results"><?php echo $search_term; ?></span></h1>
<div class="lumiere_container">
	<div class="lumiere_container_flex50"><h2><?php esc_html_e( 'Titles results', 'lumiere-movies' ); ?></h2></div>
	<div class="lumiere_container_flex50"><h2><?php esc_html_e( 'Identification number', 'lumiere-movies' ); ?></h2></div>
</div>
			<?php
			$limit_search = isset( $this->imdb_admin_values['imdbmaxresults'] ) ? intval( $this->imdb_admin_values['imdbmaxresults'] ) : 5;
			$iterator = 1;
			foreach ( $results as $res ) {
				if ( $iterator > $limit_search ) {
					$this->logger->debug( "[Lumiere][gutenbergSearch] Limit of '$limit_search' results reached." );
					echo '<div class="lumiere_italic lumiere_padding_five lumiere_align_center">'
					. esc_html__( 'Maximum of results reached. You can increase it in admin options.', 'lumiere-movies' );
					echo '</div>';
					break;
				}

				echo "\n" . '<div class="lumiere_container lumiere_container_gutenberg_border">';

				// ---- Movie title results
				echo "\n\t<div class='lumiere_container_flex50 lumiere_italic lumiere_gutenberg_results'>" . esc_html( $res->title() ) . ' (' . intval( $res->year() ) . ')</div>';

				// ---- IMDb id results
				echo "\n\t<div class='lumiere_container_flex50 lumiere_align_center lumiere_gutenberg_results'>";
				echo "\n\t\t<span class='lumiere_bold'>" . esc_html__( 'IMDb ID:', 'lumiere-movies' ) . '</span> ';
				echo "\n\t\t" . '<span class="lumiere_gutenberg_copy_class"'
					. ' id="imdbid_' . esc_html( $res->imdbid() ) . '">'
					. esc_html( $res->imdbid() )
					. '</span>';

				echo "\n\t" . '</div>';
				echo "\n</div>";

				$iterator++;

			} // end foreach

			echo '<div align="center" class="lumiere_padding_five"><a href="'
			. esc_url( site_url( '', 'relative' ) . \Lumiere\Settings::GUTENBERG_SEARCH_URL )
			. '">Do a new query</a></div>';

		}

	}

	/**
	 * Display the form for searching movies
	 *
	 */
	private function initial_form (): void {
			echo "\n<div align='center'>";
			echo "\n\t" . '<h1 id="searchmovie_title">' . esc_html__( 'Search a movie IMDb ID', 'lumiere-movies' ) . '</h1>';
			echo "\n\t" . '<form action="" method="get" id="searchmovie">';
			echo "\n\t\t" . '<input type="text" id="moviesearched" name="moviesearched">';

			// Nonce field deactivated, since it can be called from everywhere
			wp_nonce_field( 'lumiere_search' );

			echo "\n\t\t" . '<input type="submit" value="Search">';
			echo "\n\t" . '</form>';
			echo "\n" . '</div>';
	}

	/**
	 * Register script and unregister useless scripts
	 *
	 */
	public function lumiere_register_search_script (): void {
		wp_register_script(
			'lumiere_search_admin',
			$this->config_class->lumiere_js_dir . 'lumiere_scripts_search.min.js',
			[ 'jquery' ],
			$this->config_class->lumiere_version,
			true
		);
		wp_deregister_script( 'lumiere_hide_show' );
		wp_deregister_script( 'lumiere_scripts' );
		wp_deregister_script( 'lumiere_highslide' );
		wp_deregister_style( 'lumiere_style_oceanwpfixes_general' );
		wp_deregister_style( 'lumiere_highslide' );

	}

	/**
	 * Run needed scripts
	 *
	 */
	public function lumiere_run_search_script (): void {
		wp_enqueue_script( 'lumiere_search_admin' );
	}
}

// Display only in admin area
if ( current_user_can( 'manage_options' ) ) {
	new Search();
}
