<?php declare( strict_types = 1 );
/**
 * IMDbPHP search class
 *
 * @author      Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright   2021, Lost Highway
 *
 * @version     1.0
 * @package lumiere-movies
 */

namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ( ! defined( 'ABSPATH' ) ) || ( ! class_exists( '\Lumiere\Settings' ) ) ) {
	wp_die( esc_html__( 'You are not allowed to call this page directly.', 'lumiere-movies' ) );
}

use Lumiere\Tools\Settings_Global;
use Lumiere\Plugins\Logger;
use Lumiere\Plugins\Imdbphp;
use Lumiere\Settings;
use Imdb\TitleSearch;

/**
 * Display search results related to a movie to get their IMDbID
 * Can be called to display a full page for searching movies
 *
 * @see \Lumiere\Alteration\Redirect_Virtual_Page
 */
class Search {

	/**
	 * Traits
	 */
	use Settings_Global;

	/**
	 * Include the type of (movie, TVshow, Games) search
	 * @var array<string> $type_search
	 */
	private array $type_search;

	/**
	 * Class \Lumiere\Logger
	 *
	 */
	private Logger $logger;

	/**
	 * Class \Lumiere\Imdbphp
	 */
	private Imdbphp $imdbphp_class;

	/**
	 * Constructor
	 */
	public function __construct() {

		// Get Global Settings class properties.
		$this->get_settings_class();
		$this->get_db_options();

		// Get the type of search: movies, series, games
		$this->type_search = $this->config_class->lumiere_select_type_search();

		// Start logger class.
		$this->logger = new Logger( 'gutenbergSearch' );

		// Start Imdbphp class.
		$this->imdbphp_class = new Imdbphp();

		// Register admin scripts.
		add_action( 'wp_enqueue_scripts', [ $this, 'lumiere_search_register_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'lumiere_search_run_script' ] );

		// Unregister useless scripts
		add_action( 'wp_enqueue_scripts', [ $this, 'lumiere_search_deregister_scripts' ] );

		/**
		 * Layout
		 * @since 4.1.2 using 'template_include' which is the proper way to include templates
		 */
		add_filter( 'template_include', [ $this, 'lumiere_search_layout' ] );
	}

	/**
	 * Display layout
	 */
	public function lumiere_search_layout( string $template ): string {

		echo "<!DOCTYPE html>\n<html>\n<head>\n";
		wp_head();
		echo "\n</head>\n<body class=\"gutenberg_search\">";

		$this->maybe_results_page();

		wp_meta();
		wp_footer();
		echo "</body>\n</html>";
		return ''; // Delete the template used.
	}

	/**
	 * Display results page
	 * Conditionnal: only if form submitted, nonce verified OR if form submitted and referer from an editing page
	 * @since 4.1 rewritten the way to check $_GET and $_SERVER,
	 */
	private function maybe_results_page(): void {

		if (
			(
				! isset( $_GET['moviesearched'], $_GET['search_nonce'] )
				|| wp_verify_nonce( sanitize_key( $_GET['search_nonce'] ), 'lumiere_search' ) === 0
				|| strlen( $_GET['moviesearched'] ) === 0
			)
			&&
			(
				// If there is no nonce to verify, make sure it comes from editing post
				! isset( $_GET['moviesearched'] ) || strlen( $_GET['moviesearched'] ) === 0
				|| ! isset( $_SERVER['HTTP_REFERER'] ) || str_contains( $_SERVER['HTTP_REFERER'], 'post.php?post=' ) === false
			)
		) {
			echo wp_kses(
				$this->initial_form(),
				[
					'div' => [ 'align' => [] ],
					'a' => [],
					'h1' => [ 'id' => [] ],
					'form' => [
						'action' => [],
						'id' => [],
						'method' => [],
					],
					'input' => [
						'type' => [],
						'id' => [],
						'name' => [],
						'value' => [],
					],
				]
			);
			exit;
		}

		// Initialization of search and vars
		$search = new TitleSearch( $this->imdbphp_class, $this->logger->log() );
		$search_term = sanitize_text_field( $_GET['moviesearched'] );
		$this->logger->log()->debug( "[Lumiere][gutenbergSearch] Querying '$search_term'" );
		$results = $search->search( $search_term, $this->type_search );
		$limit_search = isset( $this->imdb_admin_values['imdbmaxresults'] ) ? intval( $this->imdb_admin_values['imdbmaxresults'] ) : 5;
		$iterator = 1;
		?>
		
<h1 class="searchmovie_title lumiere_italic"><?php esc_html_e( 'Results related to your query:', 'lumiere-movies' ); ?> <span class="lumiere_gutenberg_results"><?php echo esc_html( $search_term ); ?></span></h1>
<div class="lumiere_container">
	<div class="lumiere_container_flex50 lumiere_align_center"><h2><?php esc_html_e( 'Titles results', 'lumiere-movies' ); ?></h2></div>
	<div class="lumiere_container_flex50 lumiere_align_center"><h2><?php esc_html_e( 'Identification number', 'lumiere-movies' ); ?></h2></div>
</div>

		<?php
		foreach ( $results as $res ) {
			if ( $iterator > $limit_search ) {
				$this->logger->log()->debug( "[Lumiere][gutenbergSearch] Limit of '$limit_search' results reached." );
				echo '<div class="lumiere_italic lumiere_padding_five lumiere_align_center">' . esc_html__( 'Maximum number of results reached. You can increase this limit in the admin options.', 'lumiere-movies' ) . '</div>';
				break;
			}

			echo "\n" . '<div class="lumiere_container lumiere_container_gutenberg_border">';

			// ---- Movie title results
			echo "\n\t<div class='lumiere_container_flex50 lumiere_italic lumiere_gutenberg_results'>" . esc_html( $res->title() ) . ' (' . intval( $res->year() ) . ')</div>';

			// ---- IMDb id results
			echo "\n\t<div class='lumiere_container_flex50 lumiere_align_center lumiere_gutenberg_results'>";
			echo "\n\t\t<span class='lumiere_bold'>" . esc_html__( 'IMDb ID:', 'lumiere-movies' ) . '</span> ';
			echo "\n\t\t" . '<span class="lumiere_gutenberg_copy_class" id="imdbid_' . esc_html( $res->imdbid() ) . '">' . esc_html( $res->imdbid() ) . '</span>';
			echo "\n\t</div>";
			echo "\n</div>";

			$iterator++;
		} ?>

<br>
<div align="center" class="lumiere_padding_five"><a href="<?php echo esc_url( site_url( '', 'relative' ) . Settings::GUTENBERG_SEARCH_URL ); ?>"><?php esc_html_e( 'Do a new query', 'lumiere-movies' ); ?></a></div>
<br>
<br><?php
	}

	/**
	 * Display the form for searching movies
	 */
	private function initial_form (): string {

		$ouput = "\n<div align=\"center\">";
		$ouput .= "\n\t" . '<h1 id="searchmovie_title">' . esc_html__( 'Search a movie IMDb ID', 'lumiere-movies' ) . '</h1>';
		$ouput .= "\n\t" . '<form action="" method="get" id="searchmovie">';

		$ouput .= "\n\t\t" . '<input type="text" id="moviesearched" name="moviesearched" value="">';

		$ouput .= wp_nonce_field( 'lumiere_search', 'search_nonce', true, false );

		$ouput .= "\n\t\t" . '<input type="submit" value="Search">';
		$ouput .= "\n\t" . '</form>';
		$ouput .= "\n" . '</div>';
		return $ouput;
	}

	/**
	 * Register search script and unregister useless scripts
	 */
	public function lumiere_search_register_scripts(): void {

		// Remove admin bar
		add_filter( 'show_admin_bar', '__return_false' );

		wp_register_script(
			'lumiere_search_admin',
			$this->config_class->lumiere_js_dir . 'lumiere_scripts_search.min.js',
			[ 'jquery' ],
			$this->config_class->lumiere_version,
			true
		);
	}

	/**
	 * Deregister useless scripts
	 */
	public function lumiere_search_deregister_scripts(): void {

		// Scripts.
		wp_deregister_script( 'lumiere_hide_show' );
		wp_deregister_script( 'lumiere_scripts' );
		wp_deregister_script( 'lumiere_highslide_core' );
		wp_deregister_script( 'lumiere_bootstrap_core' );
		wp_deregister_script( 'lumiere_bootstrap_scripts' );
		// Styles.
		wp_deregister_style( 'lumiere_style_oceanwpfixes_general' );
		wp_deregister_style( 'lumiere_highslide_core' );
		wp_deregister_style( 'lumiere_bootstrap_core' );
		wp_deregister_style( 'lumiere_bootstrap_custom' );
		wp_deregister_style( 'lumiere_gutenberg_main' );
		wp_deregister_style( 'lumiere_block_widget' );
	}

	/**
	 * Run needed scripts
	 */
	public function lumiere_search_run_script(): void {

		wp_enqueue_script( 'lumiere_search_admin' );
	}
}
