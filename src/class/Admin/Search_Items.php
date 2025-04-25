<?php declare( strict_types = 1 );
/**
 * Admin movies/persons search class
 *
 * @copyright   2021, Lost Highway
 *
 * @version     1.0
 * @package     lumieremovies
 */

namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ( ! defined( 'ABSPATH' ) ) || ( ! class_exists( '\Lumiere\Config\Settings' ) ) ) {
	wp_die( esc_html__( 'You are not allowed to call this page directly.', 'lumiere-movies' ) );
}

use Lumiere\Config\Get_Options;
use Lumiere\Config\Open_Options;
use Lumiere\Plugins\Logger;
use Lumiere\Plugins\Manual\Imdbphp;
use Lumiere\Tools\Validate_Get;

/**
 * Display search results related to a movie or person to get their IMDbID
 * Can be called to display a full page for searching movies/people
 * Is available at wp-admin/lumiere/search/, and in edit post interface. Also in Help admin section.
 *
 * @see \Lumiere\Admin Call this page in add_filter( 'template_include' )
 * @phpstan-import-type TITLESEARCH_RETURNSEARCH from Imdbphp
 * @phpstan-import-type NAMESEARCH_RETURNSEARCH from Imdbphp
 */
class Search_Items {

	/**
	 * Traits
	 */
	use Open_Options;

	/**
	 * Name of the movie/person queried
	 */
	private ?string $item_searched;

	/**
	 * Constructor
	 */
	public function __construct(
		private Logger $logger = new Logger( 'admin search' ),
		private Imdbphp $imdbphp_class = new Imdbphp(),
	) {

		// By default, it returns a 404, change that.
		status_header( 200 );

		// Get global settings class properties.
		$this->get_db_options(); // In Open_Options trait.
		$this->item_searched = Validate_Get::sanitize_url( Get_Options::LUM_SEARCH_ITEMS_QUERY_STRING );

		// Register admin scripts.
		add_action( 'wp_enqueue_scripts', [ $this, 'search_register_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'search_run_script' ] );

		// Unregister useless scripts
		add_action( 'wp_enqueue_scripts', [ $this, 'search_deregister_scripts' ] );

		/**
		 * Layout
		 * @since 4.1.2 using 'template_include' which is the proper way to include templates
		 */
		add_filter( 'template_include', [ $this, 'page_layout' ] );

		/**
		 * Display title
		 * @since 4.3
		 */
		add_filter( 'document_title_parts', [ $this, 'edit_title' ] );
	}

	/**
	 * Edit the title of the page
	 *
	 * @param array<string, string> $title
	 * @phpstan-param array{title: string, page: string, tagline: string, site: string} $title
	 * @phpstan-return array{title: string, page: string, tagline: string, site: string}
	 */
	public function edit_title( array $title ): array {

		$new_title = isset( $this->item_searched ) && strlen( $this->item_searched ) > 0
			/* translators: %1s is a movie's title */
			? '[ ' . wp_sprintf( __( 'Lumière Query Interface %1s', 'lumiere-movies' ), '] Search: &ldquo;' . esc_html( ucfirst( $this->item_searched ) ) . '&rdquo;' )
			: __( 'Lumière Query Interface', 'lumiere-movies' );

		$title['title'] = $new_title;

		return $title;
	}

	/**
	 * Register search script and unregister useless scripts
	 */
	public function search_register_scripts(): void {

		// Remove admin bar
		add_filter( 'show_admin_bar', '__return_false' );

		wp_register_style(
			'lumiere_search_admin_css',
			Get_Options::LUM_CSS_URL . 'lumiere_search_admin.min.css',
			[ 'lumiere_style_main' ],
			strval( filemtime( Get_Options::LUM_CSS_PATH . 'lumiere_search_admin.min.css' ) )
		);

		wp_register_script(
			'lumiere_search_admin',
			Get_Options::LUM_JS_URL . 'lumiere_scripts_search.min.js',
			[ 'jquery' ],
			lum_get_version(),
			true
		);
	}

	/**
	 * Deregister useless scripts
	 */
	public function search_deregister_scripts(): void {

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
	public function search_run_script(): void {
		wp_enqueue_script( 'lumiere_search_admin' );
		wp_enqueue_style( 'lumiere_search_admin_css' );
	}

	/**
	 * Display layout
	 */
	public function page_layout(): string {

		echo "<!DOCTYPE html>\n<html>\n<head>\n";
		wp_head();
		echo "\n</head>\n<body class=\"lum_search\">";

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
				! isset( $_GET[ Get_Options::LUM_SEARCH_ITEMS_QUERY_STRING ], $_GET['search_nonce'] )
				|| ( wp_verify_nonce( sanitize_key( $_GET['search_nonce'] ), 'lumiere_search' ) > 0 ) === false
				|| strlen( sanitize_key( $_GET[ Get_Options::LUM_SEARCH_ITEMS_QUERY_STRING ] ) ) === 0
			)
			&&
			(
				// If there is no nonce to verify, make sure it comes from editing post
				! isset( $_GET[ Get_Options::LUM_SEARCH_ITEMS_QUERY_STRING ] ) || strlen( sanitize_key( strval( $_GET[ Get_Options::LUM_SEARCH_ITEMS_QUERY_STRING ] ) ) ) === 0
				|| ! isset( $_SERVER['HTTP_REFERER'] ) || str_contains( esc_url_raw( wp_unslash( strval( $_SERVER['HTTP_REFERER'] ) ) ), 'post.php?post=' ) === false
			)
		) {
			echo wp_kses(
				$this->initial_form(),
				[
					'div'    => [
						'align'  => [],
					],
					'a'      => [],
					'h1'     => [
						'id'     => [],
					],
					'form'   => [
						'action' => [],
						'id'     => [],
						'method' => [],
					],
					'input'  => [
						'type'   => [],
						'id'     => [],
						'name'   => [],
						'value'  => [],
					],
					'select' => [
						'id'     => [],
						'name'   => [],
					],
					'option' => [
						'label'  => [],
						'value'  => [],
					],
				]
			);
			exit;
		}

		$this->logger->log?->debug( "[admin search] Querying *$this->item_searched* of type " . $_GET['select_search_type'] );

		$results = [];
		$first_column_header = 'title';
		$second_column_header = 'imdbid';
		$year = 'year';
		if ( esc_html( $_GET['select_search_type'] ) === 'movie' ) {

			/** @phpstan-var TITLESEARCH_RETURNSEARCH $results */
			$results = $this->imdbphp_class->search_movie_title(
				$this->item_searched ?? '',
				$this->logger->log,
			);
		} elseif ( esc_html( $_GET['select_search_type'] ) === 'person' ) {
			/** @phpstan-var NAMESEARCH_RETURNSEARCH $results */
			$results = $this->imdbphp_class->search_person_name(
				$this->item_searched ?? '',
				$this->logger->log,
			);
			$first_column_header = 'name';
			$second_column_header = 'id';
			$year = '';
		}

		$limit_search = isset( $this->imdb_admin_values['imdbmaxresults'] ) ? intval( $this->imdb_admin_values['imdbmaxresults'] ) : 5;
		$iterator = 1;
		?>
		
<h1 class="lum_search_title lumiere_italic"><?php esc_html_e( 'Results related to your query:', 'lumiere-movies' ); ?> <span class="lum_search_results"><?php echo esc_html( ucwords( $this->item_searched ?? '' ) ); ?></span></h1>
<div class="lumiere_container">
	<div class="lumiere_container_flex50 lumiere_align_center"><h2><?php esc_html_e( 'Items results', 'lumiere-movies' ); ?></h2></div>
	<div class="lumiere_container_flex50 lumiere_align_center"><h2><?php esc_html_e( 'IMDb Identification number', 'lumiere-movies' ); ?></h2></div>
</div>

		<?php
		if ( count( $results ) === 0 ) {
			echo "\n" . '<div class="lum_search_container lumiere_align_center">';
			esc_html_e( 'No results found.', 'lumiere-movies' );
			echo "\n</div>";
		}

		foreach ( $results as $res ) {
			if ( $iterator > $limit_search ) {
				$this->logger->log?->debug( "[admin search] Limit of '$limit_search' results reached." );
				echo '<div class="lumiere_italic lumiere_padding_five lumiere_align_center">' . esc_html__( 'Maximum number of results reached. You can increase this limit in the admin options.', 'lumiere-movies' ) . '</div>';
				break;
			}

			echo "\n" . '<div class="lumiere_container lum_search_container">';
			// ---- Movie title results
			$maybe_year = isset( $res['year'] ) && $first_column_header === 'title' ? ' (' . esc_html( strval( $res['year'] ) ) . ')' : '';
			/** @psalm-suppress PossiblyInvalidArgument */
			echo isset( $res[ $first_column_header ] ) ? "\n\t<div class='lumiere_container_flex50 lumiere_italic lum_search_results'>" . esc_html( $res[ $first_column_header ] ) . esc_html( $maybe_year ) . '</div>' : '(' . esc_html__( 'no year found', 'lumiere-movies' ) . ')';

			// ---- IMDb id results
			echo "\n\t<div class='lumiere_container_flex50 lumiere_align_center lum_search_results'>";
			echo "\n\t\t<span class='lumiere_bold'>" . esc_html__( 'IMDb ID:', 'lumiere-movies' ) . '</span> ';
			/** @psalm-suppress PossiblyInvalidArgument */
			echo isset( $res[ $second_column_header ] ) ? "\n\t\t" . '<span class="lum_search_imdbid" id="imdbid_' . esc_html( $res[ $second_column_header ] ) . '">' . esc_html( $res[ $second_column_header ] ) . '</span>' : '(' . esc_html__( 'no IMDb ID found', 'lumiere-movies' ) . ')';
			echo "\n\t</div>";
			echo "\n</div>";

			$iterator++;
		} ?>

<br>
<div align="center" class="lumiere_padding_five"><a href="<?php echo esc_url( site_url( '', 'relative' ) . Get_Options::LUM_SEARCH_ITEMS_URL_ADMIN ); ?>"><?php esc_html_e( 'Do a new query', 'lumiere-movies' ); ?></a></div>
<br>
<br><?php
	}

	/**
	 * Display the form for searching movies
	 */
	private function initial_form(): string {

		$this->logger->log?->debug( '[admin search] Waiting for a search' );

		$output = "\n<div align=\"center\">";
		$output .= "\n\t" . '<form action="" method="get" id="searchmovie">';
		$output .= "\n\t" . '<h1 id="lum_search_title">' . esc_html__( 'Search an IMDb ID for', 'lumiere-movies' );
		$output .= '<select id="select_search_type" name="select_search_type"><option label="movie" value="movie">' . esc_html__( 'movie', 'lumiere-movies' ) . '</option><option label="person" value="person">' . esc_html__( 'person', 'lumiere-movies' ) . '</option></select></h1>';

		$output .= "\n\t\t" . '<input type="text" id="lum_movie_input" name="' . Get_Options::LUM_SEARCH_ITEMS_QUERY_STRING . '" value="">';

		$output .= wp_nonce_field( 'lumiere_search', 'search_nonce', true, false );

		$output .= "\n\t\t" . '<input type="submit" value="Search">';
		$output .= "\n\t" . '</form>';
		$output .= "\n" . '</div>';
		return $output;
	}
}
