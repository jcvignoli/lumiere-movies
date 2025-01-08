<?php declare( strict_types = 1 );
/**
 * Taxonomy
 *
 * @author      Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright   2023, Lost Highway
 *
 * @version     1.0
 * @package lumiere-movies
 */

namespace Lumiere\Alteration;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) && ( ! class_exists( '\Lumiere\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Settings;
use Lumiere\Tools\Get_Options;
use Lumiere\Plugins\Logger;
use Exception;
use WP_Query;

/**
 * Create Lumière! Taxonomy system
 * 1/ Taxonomy terms are made available
 * 2/ Once taxonomy is registered, by visiting a post/page the terms are saved in db
 * 2/ URL based on taxonomy  Taxonomy Pages names are added to the database
 * @Info Use of conditions for instanciate Logger class, otherwhise "Notice: Function _load_textdomain_just_in_time was called incorrectly."
 *
 * @phpstan-import-type OPTIONS_ADMIN from \Lumiere\Tools\Settings_Global
 * @phpstan-import-type OPTIONS_DATA from \Lumiere\Tools\Settings_Global
 */
class Taxonomy {

	/**
	 * Traits
	 */
	use Get_Options;

	/**
	 * @phpstan-var OPTIONS_ADMIN $imdb_admin_values
	 */
	private array $imdb_admin_values;

	/**
	 * Logging class
	 */
	private ?Logger $logger = null;

	/**
	 * Constructor
	 */
	public function __construct( string $old_taxonomy = '', string $new_taxonomy = '', string $action = '' ) {

		$this->imdb_admin_values = get_option( Settings::get_admin_tablename(), [] );

		// If taxonomy is not activated, exit.
		if ( ! isset( $this->imdb_admin_values['imdbtaxonomy'] ) || $this->imdb_admin_values['imdbtaxonomy'] !== '1' ) {
			return;
		}

		// Start Logger class.
		if ( current_user_can( 'manage_options' ) && is_admin() ) {
			$this->logger = new Logger( 'Taxonomy' );
		}

		// Register new taxonomy and create custom taxonomy pages.
		add_action( 'init', [ $this, 'create_custom_taxonomy' ], 11 );

		// Must be registered before in order to delete its terms.
		if ( $action === 'update_old_taxo' ) {
			add_action( 'init', fn() => $this->update_custom_terms( $old_taxonomy, $new_taxonomy ), 13 );
		}
	}

	/**
	 * Static instanciation of the class
	 *
	 * @return void The class was instanciated
	 * @see \Lumiere\Core class calling in ini hook (no arguments used)
	 * @see \Lumiere\Admin\Save_Options in init hook (with arguments passed)
	 */
	public static function lumiere_static_start( string $old_taxonomy = '', string $new_taxonomy = '', string $action = '' ): void {
		$taxonomy_class = new self( $old_taxonomy, $new_taxonomy, $action );
	}

	/**
	 * Update all terms according to a new taxonomy.
	 *
	 * @param string $old_taxonomy the taxonomy to be replaced, ie 'lumiere-'
	 * @param string $new_taxonomy the new taxonomy, , ie 'lumiere-'
	 * @return void The class was instanciated
	 */
	public function update_custom_terms( string $old_taxonomy, string $new_taxonomy ): void {

		// Method executed in init so logging prevents throws a "headers already sent" -> trick to prevent logger to be run
		if ( did_action( 'wp_loaded' ) !== 1 ) {
			$this->logger = null;
		}

		$this->logger?->log()->debug( '[Lumiere][Taxonomy][Start] Replacing taxonomy *' . $old_taxonomy . '* by ' . $new_taxonomy . ' started' );

		$get_taxo_array = $this->get_taxonomy_activated(); // Method in trait Get_Options, retrieve an array of varsuch as "lumiere-director"

		foreach ( $get_taxo_array as $taxonomy_name ) {

			$taxonomy_item = str_replace( $this->imdb_admin_values['imdburlstringtaxo'], '', $taxonomy_name ); // Such as "director"

			// Build taxonomy name from the Lumière option row.
			$full_old_taxonomy = str_replace( 'imdbtaxonomy', '', esc_html( $old_taxonomy . $taxonomy_item ) );
			$full_new_taxonomy = str_replace( 'imdbtaxonomy', '', esc_html( $new_taxonomy . $taxonomy_item ) );

			/* register_taxonomy( $full_old_taxonomy, [ 'page', 'post' ] ); =>>> Removed so terms from old taxonomy that don't exist aren't processed => Saves time*/
			// Register new taxonomy to make sure they are available to below functions.
			register_taxonomy( $full_new_taxonomy, [ 'page', 'post' ] );

			// Run update query
			$this->lum_query_update_taxo(
				$full_old_taxonomy,
				$full_new_taxonomy,
				// Retrieve all posts with the old taxonomy.
				[
					'post_type' => [ 'post', 'page' ],
					'post_status' => 'publish',
					'showposts' => -1,
					'lang' => '', // query posts in all languages for Polylang -- no effect otherwise.
					'fields' => 'ids',
					'tax_query' => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
						[
						'taxonomy' => $full_old_taxonomy,
						'operator' => 'EXISTS',
						],
					],
				]
			);

			// Get all terms available for the old taxonomy.
			$terms = get_terms( [ 'taxonomy' => $full_old_taxonomy ] );

			if ( ! $terms instanceof \WP_Error ) {
				/** @psalm-suppress PossiblyInvalidIterator -- Cannot iterate over string -- this is the old WordPress way to have get_terms() return strings */
				foreach ( $terms as $term ) {
					$term_deleted = wp_delete_term( intval( $term->term_id ), sanitize_text_field( $full_old_taxonomy ), [ 'force_default' => true ] );
					if ( $term_deleted === true ) {
						$this->logger?->log()->debug( '[Lumiere][Taxonomy][Update terms][Deleted] Term "' . $term->name . '" deleted from taxonomy "' . sanitize_text_field( $full_old_taxonomy . '"' ) );
					}
				}
			}
			$this->logger?->log()->debug( '[Lumiere][Taxonomy][Update terms][Processed] Term "' . sanitize_text_field( $full_new_taxonomy ) );
		}
		$this->logger?->log()->debug( '[Lumiere][Taxonomy][Update terms][End] taxonomy terms have been processed.' );
	}

	/**
	 * Register custom taxonomy
	 *  a Taxonomies are available in admin menu under Posts (=> 'show_ui')
	 *  b URL rewrite activated (=>'query_var' and 'rewrite', but it is by default)
	 *
	 * @param string $taxonomy Optional. Used when using add_action()
	 * @param string $object_type Optional. Used when using add_action()
	 * @param array<string, string|array<string>> $args Optional. Used when using add_action()
	 * @return void The taxonomy has been created
	 * @throws Exception if the taxonomy doesn't exist
	 */
	public function create_custom_taxonomy( string $taxonomy = '', string $object_type = '', array $args = [] ): void {

		// $this->logger?->log()->debug( '[Lumiere][Taxonomy] create_custom_taxonomy()' . $taxonomy);
		$get_taxo_array = $this->get_taxonomy_activated(); // Method in trait Get_Options, retrieve an array of varsuch as "lumiere-director"

		foreach ( $get_taxo_array as $taxonomy_name ) {

			$taxonomy_item = str_replace( $this->imdb_admin_values['imdburlstringtaxo'], '', $taxonomy_name ); // Such as "director"

			// Register activated taxonomies
			register_taxonomy(
				$taxonomy_name,
				[ 'page', 'post' ],
				[
					/* remove metaboxes from edit interface, keep the menu of post */
					'show_ui' => true,              /* whether to manage taxo in UI */
					'show_in_quick_edit' => false,  /* whether to show taxo in edit interface */
					'show_tagcloud' => false,       /* whether to show Tag Cloud Widget controls */
					'meta_box_cb' => false,         /* whether to show taxo in metabox */
					/* other settings */
					'labels' => [
						'name' => 'Lumière ' . $taxonomy_item . 's ' . __( 'Tags', 'lumiere-movies' ),
						'parent_item' => __( 'Parent taxonomy', 'lumiere-movies' ) . ' ' . $taxonomy_item,
						'singular_name' => ucfirst( $taxonomy_item ) . ' name',
						'menu_name' => __( 'Tags', 'lumiere-movies' ) . ' Lumière ' . $taxonomy_item,
						'search_items' => __( 'Search', 'lumiere-movies' ) . ' ' . $taxonomy_item . 's',
						'add_new_item' => __( 'Add new', 'lumiere-movies' ) . ' ' . ucfirst( $taxonomy_item ),
					],
					'hierarchical' => false,        /* Whether there is a relationship between added terms, it's true! */
					'public' => true,
					// These will allow to reach in URL ie /lumiere-director/stanley-kubrick/
					'query_var' => $taxonomy_name,  /* Optional, use by default $taxonomy_name for ?query_var */
					'rewrite' => true,              /* Optional, use by default $taxonomy_name as URL rewrite for slug */
				]
			);
		}
	}

	/**
	 * Run a query to update taxonomy
	 *
	 * @param string $full_old_taxonomy the taxonomy to be replaced
	 * @param string $full_new_taxonomy the new taxonomy
	 * @param array{post_type:array<string>, post_status:'publish', showposts:-1,lang:'', fields:'ids', tax_query: array{0:array{taxonomy:string,operator:'EXISTS'}}} $args The arguments for the WP_Query
	 * @phpstan-param array{post_type: array<string>, post_status: string, showposts: int, lang: string, fields: string, tax_query: array{array{taxonomy: string, operator: 'EXISTS'}}} $args
	 * @return void
	 * @see WP_Query
	 */
	private function lum_query_update_taxo( string $full_old_taxonomy, string $full_new_taxonomy, array $args ): void {

		$query = new WP_Query( $args );

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {

				$query->the_post();

				$page_id = intval( get_the_ID() );
				$title = get_post_field( 'post_title', $page_id );
				$terms_post = get_the_terms( $page_id, $full_old_taxonomy );
				$this->logger?->log()->debug( '[Lumiere][Taxonomy][Update terms][Post] Title "' . esc_html( $title ) . '" being processed' );

				if ( $terms_post === false || $terms_post instanceof \WP_Error ) {
					continue;
				}

				// Trick: Convert WP_Term objects to array, so can be processed in Polylang (otherwise fails).
				if ( ! isset( $terms_post[1] ) ) {
					$terms_post[] = array_values( $terms_post );
				}

				// Execute Polylang update taxonomy terms if it is active.
				if ( has_action( 'lum_polylang_update_taxonomy_terms' ) === true ) {
					do_action( 'lum_polylang_update_taxonomy_terms', $terms_post, $page_id, $full_new_taxonomy, $full_old_taxonomy, $title );
					continue;
				}

				// Normal update.
				$this->update_taxonomy_terms( $terms_post, $page_id, $full_new_taxonomy, $full_old_taxonomy, $title );
			}
		}
	}
	/**
	 * Import the taxonomy terms
	 *
	 * @param array<\WP_Term|array<\WP_Term>> $terms_post Object of terms in the Post
	 * @param int $page_id Post Id
	 * @param string $full_new_taxonomy the new taxonomy
	 * @param string $full_old_taxonomy the taxonomy to be replaced
	 * @param string $title Post title
	 *
	 * @return void The taxonomy terms have been imported
	 */
	private function update_taxonomy_terms( array $terms_post, int $page_id, string $full_new_taxonomy, string $full_old_taxonomy, string $title ): void {

		foreach ( $terms_post as $term_post ) {

			// Due to the trick, needs to convert back to from array to object if it's an array.
			$term_post = is_array( $term_post ) ? $term_post[0] : $term_post;

			$adding_terms = wp_set_object_terms(
				$page_id,
				$term_post->name,
				$full_new_taxonomy,
				true /* True: Append the term, False: Replace all previous terms by current one */
			);

			// No term found
			if ( ! $adding_terms instanceof \WP_Error && count( $adding_terms ) > 0 ) {
				$this->logger?->log()->debug( '[Lumiere][Taxonomy][Update terms][Added] Term *' . esc_html( $term_post->name ) . '* to post *' . esc_html( $title ) . '*' );
			}
			$this->logger?->log()->debug( '[Lumiere][Taxonomy][Update terms][Process] Term *' . esc_html( $term_post->name ) );
		}
		$this->logger?->log()->debug( '[Lumiere][Taxonomy][Update terms][Post] Title *' . esc_html( $title ) . '* processed.' );
	}
}
