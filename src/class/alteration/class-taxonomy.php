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
use Lumiere\Tools\Data;
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
	use Data;

	/**
	 * @phpstan-var OPTIONS_DATA $imdb_data_values
	 */
	private array $imdb_data_values;

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
		$this->imdb_data_values = get_option( Settings::get_data_tablename(), [] );
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
	 * @param string $old_taxonomy the taxonomy to be replaced
	 * @param string $new_taxonomy the new taxonomy
	 * @return void The class was instanciated
	 */
	public function update_custom_terms( string $old_taxonomy, string $new_taxonomy ): void {

		$get_taxo_array = $this->lumiere_array_key_exists_wildcard( $this->imdb_data_values, 'imdbtaxonomy*', 'key-value' );

		$this->logger?->log()->debug( '[Lumiere][Taxonomy] Updating taxonomy ' . $old_taxonomy . ' with ' . $new_taxonomy );

		foreach ( $get_taxo_array as $option => $active ) { // Method in trait Data.

			// If the taxonomy is not active, don't go further.
			if ( $active !== '1' ) {
				continue;
			}

			// Build taxonomy name from the Lumière option row.
			$full_old_taxonomy = str_replace( 'imdbtaxonomy', '', esc_html( $old_taxonomy . $option ) );
			$full_new_taxonomy = str_replace( 'imdbtaxonomy', '', esc_html( $new_taxonomy . $option ) );

			/* register_taxonomy( $full_old_taxonomy, [ 'page', 'post' ] ); =>>> Removed so terms from old taxonomy that don't exist aren't processed => Saves time*/
			// Register new taxonomy to make sure they are available to below functions.
			register_taxonomy( $full_new_taxonomy, [ 'page', 'post' ] );

			// Get all terms available for the old taxonomy.
			// @phan-suppress-next-line PhanAccessMethodInternal -- Cannot access internal method \get_terms() of namespace \ defined at vendor/php-stubs/wordpress-stubs/wordpress-stubs.php:133181 from namespace \Lumiere\Plugins -> PHAN gets crazy with get_terms()!
			$terms = get_terms(
				[
					'taxonomy' => $full_old_taxonomy,
					'hide_empty' => true,
				]
			);

			if ( $terms instanceof \WP_Error ) {
				$this->logger?->log()->error( '[Lumiere][Taxonomy][Update terms] Invalid terms for taxonomy: "' . $full_old_taxonomy . '" error message: ' . $terms->get_error_message() );
				continue;
			}

			/** @psalm-suppress PossiblyInvalidIterator -- Cannot iterate over string -- this is the old WordPress way to have get_terms() return strings */
			foreach ( $terms as $term ) {

				// Retrieve and sanitize the term object vars.
				$term_id = intval( $term->term_id );
				$term_name = esc_html( $term->name );

				// If the term doesn't exist in the new taxonomy, insert it.
				if ( term_exists( $term_name, $full_new_taxonomy ) === null ) {
					wp_insert_term( $term_name, $full_new_taxonomy );
					$this->logger?->log()->debug( '[Lumiere][Taxonomy][Update terms][Created] New term "' . $term_name . '" was missing and was created for the new taxonomy ' . sanitize_text_field( $full_new_taxonomy ) );
				}

				// Run update query
				$this->lum_query_update_taxo(
					$full_old_taxonomy,
					$full_new_taxonomy,
					// The query arguments.
					[
						'post_type' => [ 'post', 'page' ],
						'post_status' => 'publish',
						'no_found_rows' => true,
						'tax_query' => [
							'taxonomy' => sanitize_text_field( $full_old_taxonomy ),
							'field' => 'slug',
							'terms' => sanitize_key( $term->slug ),
						],
					] // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				);

				$term_deleted = wp_delete_term( $term_id, sanitize_text_field( $full_old_taxonomy ) );

				if ( $term_deleted === true ) {
					$this->logger?->log()->debug( '[Lumiere][Taxonomy][Update terms][Deleted] Term "' . $term_name . '" deleted from taxonomy "' . sanitize_text_field( $full_old_taxonomy . '"' ) );
				}

				// Error deleting terms.
				if ( $term_deleted instanceof \WP_Error ) {
					$this->logger?->log()->error( '[Lumiere][Taxonomy][Update terms][*' . $term_deleted->get_error_message() . '*] Failed to delete the term "' . $term_name . '" from taxonomy "' . sanitize_text_field( $full_old_taxonomy ) . '"' );
				}

				$this->logger?->log()->debug( '[Lumiere][Taxonomy][Update terms] Term "' . $term_name . '" processed.' );
			}
			$this->logger?->log()->debug( '[Lumiere][Taxonomy][Update terms] Taxonomy "' . sanitize_text_field( $full_new_taxonomy ) . '" processed.' );
		}
		$this->logger?->log()->debug( '[Lumiere][Taxonomy][Update terms] Finished. All taxonomy terms have been processed.' );
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
		$get_taxo_array = $this->lumiere_array_key_exists_wildcard( $this->imdb_data_values, 'imdbtaxonomy*', 'key-value' ); // Method in trait Data

		foreach ( $get_taxo_array as $option => $activated ) {

			// Check if a specific taxonomy (such as actor, genre) is activated.
			if ( $activated !== '1' ) {
				continue;
			}

			$taxonomy_item = is_string( $option ) ? str_replace( 'imdbtaxonomy', '', $option ) : ''; // Such as "director"
			$taxonomy_name = $this->imdb_admin_values['imdburlstringtaxo'] . $taxonomy_item; // Such as "lumiere-director"

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
						'name' => 'Lumière ' . $taxonomy_item . 's ' . __( 'Tags', 'default' ),
						'parent_item' => __( 'Parent taxonomy', 'lumiere-movies' ) . ' ' . $taxonomy_item,
						'singular_name' => ucfirst( $taxonomy_item ) . ' name',
						'menu_name' => __( 'Tags', 'default' ) . ' Lumière ' . $taxonomy_item,
						'search_items' => __( 'Search', 'default' ) . ' ' . $taxonomy_item . 's',
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
	 * @param array<string, array<int|string, string>|string|true> $args The arguments for the WP_Query
	 * @return void
	 * @see WP_Query
	 */
	private function lum_query_update_taxo( string $full_old_taxonomy, string $full_new_taxonomy, array $args ): void {

		$query = new WP_Query( $args );

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {

				$query->the_post();

				if ( ! taxonomy_exists( $full_old_taxonomy ) ) {
					$this->logger?->log()->error( '[Lumiere][Taxonomy][Update terms]Taxonomy ' . $full_old_taxonomy . ' does not exit, aborting' );
					continue;
				}
				if ( ! taxonomy_exists( $full_new_taxonomy ) ) {
					$this->logger?->log()->error( '[Lumiere][Taxonomy][Update terms]Taxonomy ' . $full_new_taxonomy . ' does not exit, aborting' );
					continue;
				}

				$page_id = intval( get_the_ID() );
				$title = get_post_field( 'post_title', $page_id );
				$terms_post = get_the_terms( $page_id, $full_old_taxonomy );
				$this->logger?->log()->debug( '[Lumiere][Taxonomy][Update terms] Processing post "' . esc_html( $title ) . '"' );

				if ( is_array( $terms_post ) === false ) {
					continue;
				}

				// Insert only terms that a related to the current post.
				foreach ( $terms_post as $term_post ) {
					wp_set_object_terms(
						$page_id,
						$term_post->name,
						$full_new_taxonomy,
						true /* True: Append the term, False: Replace all previous terms by current one */
					);
				}

			}
		}
	}
}
