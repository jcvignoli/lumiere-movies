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
 * Taxonomy Pages names are added to the database
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
		if ( current_user_can( 'manage_options' ) ) {
			$this->logger = new Logger( 'Taxonomy' ); // @info Use the Logger class for debug only, otherwhise "Notice: Function _load_textdomain_just_in_time was called incorrectly."
		}

		// Register new taxonomy and create custom taxonomy pages.
		add_action( 'init', [ $this, 'create_custom_taxonomy' ] );

		// Must be registered before in order to delete its terms.
		if ( $action === 'remove_old_taxo' ) {
			add_action( 'init', fn() => $this->update_custom_terms( $old_taxonomy, $new_taxonomy ), 13 );
		}
	}

	/**
	 * Static instanciation of the class
	 *
	 * @return void The class was instanciated
	 */
	public static function lumiere_static_start( string $old_taxonomy = '', string $new_taxonomy = '', string $action = '' ): void {
		$taxonomy_class = new self( $old_taxonomy, $new_taxonomy, $action );
	}

	/**
	 * Update all terms according to a new taxonomy.
	 * @see \Lumiere\Admin\Save_Options
	 *
	 * @param string $old_taxonomy the taxonomy to be replaced
	 * @param string $new_taxonomy the new taxonomy
	 * @return void The class was instanciated
	 */
	public function update_custom_terms( string $old_taxonomy, string $new_taxonomy ): void {

		global $wpdb;
		$get_taxo_array = $this->lumiere_array_key_exists_wildcard( $this->imdb_data_values, 'imdbtaxonomy*', 'key-value' );

		$this->logger?->log()->debug( '[Lumiere][Taxonomy] Updating taxonomy ' . $old_taxonomy . ' with ' . $new_taxonomy );

		foreach ( $get_taxo_array as $key => $value ) { // Method in trait Data.
			$full_old_taxonomy = str_replace( 'imdbtaxonomy', '', esc_html( $old_taxonomy . $key ) );
			$full_new_taxonomy = str_replace( 'imdbtaxonomy', '', esc_html( $new_taxonomy . $key ) );

			// Register both old and new taxonomy to make sure they are available to below functions.
			register_taxonomy(
				$full_old_taxonomy,
				[ 'page', 'post' ],
				[
					'labels' => [ 'name' => 'Lumière ' . $full_old_taxonomy . 's' ],
					'public' => false,
					'query_var' => $full_old_taxonomy,
					'rewrite' => [ 'slug' => $full_old_taxonomy ],
				]
			);
			register_taxonomy(
				$full_new_taxonomy,
				[ 'page', 'post' ],
				[
					'labels' => [ 'name' => 'Lumière ' . $full_new_taxonomy . 's' ],
					'public' => false,
					'query_var' => $full_new_taxonomy,
					'rewrite' => [ 'slug' => $full_new_taxonomy ],
				]
			);

			// Get all terms available for the old taxonomy.
			// @phan-suppress-next-line PhanAccessMethodInternal -- Cannot access internal method \get_terms() of namespace \ defined at vendor/php-stubs/wordpress-stubs/wordpress-stubs.php:133181 from namespace \Lumiere\Plugins -> PHAN gets crazy with get_terms()!
			$terms = get_terms(
				[
					'taxonomy' => $full_old_taxonomy,
					'hide_empty' => true,
				]
			);

			if ( $terms instanceof \WP_Error ) {
				$this->logger?->log()->error( '[Lumiere][Taxonomy][Update terms] Invalid terms: ' . $terms->get_error_message() );
				continue;
			}

			/** @psalm-suppress PossiblyInvalidIterator -- Cannot iterate over string -- this is the old WordPress way to have get_terms() return strings */
			foreach ( $terms as $term ) {

				// Retrieve and sanitize the term object vars.
				$term_id = intval( $term->term_id );
				$term_name = esc_html( $term->name );

				if ( strlen( $term_name ) === 0 ) {
					$this->logger?->log()->error( '[Lumiere][Taxonomy][Update terms] This term does not exist ' . $term_name . ' in ' . sanitize_text_field( $full_new_taxonomy ) );
					continue;
				}

				// If the term already exists, don't insert it again.
				if ( term_exists( $term_name, $full_new_taxonomy ) === null ) {
					wp_insert_term( $term_name, $full_new_taxonomy );
					$this->logger?->log()->debug( '[Lumiere][Taxonomy][Update terms][Created] New term "' . $term_name . '" was missing and was created for the new taxonomy ' . sanitize_text_field( $full_new_taxonomy ) );
				}

				$args = [
					'post_type' => [ 'post', 'page' ],
					'post_status' => 'publish',
					'no_found_rows' => true,
					'tax_query' => [
						'taxonomy' => sanitize_text_field( $full_old_taxonomy ),
						'field' => 'slug',
						'terms' => sanitize_key( $term->slug ),
					],
				]; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query

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
							wp_set_object_terms( $page_id, $term_post->name, $full_new_taxonomy, true /** Append the term or delete previous terms? */ );
						}

					}
				}
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
	 *
	 * 1/ Register taxonomy
	 * 2/ Add specific class for HTML tags for functions building links towards taxonomy pages --------- This part seems useless, not working, to remove!
	 *  a search for all imdbtaxonomy* in config array,
	 *  b if active write a filter to add a class to the link to the taxonomy page.
	 *  c Can be utilised by get_the_term_list() the_terms() WP function, such as in taxo templates
	 *
	 * @param string $taxonomy Optional. Used when using add_action()
	 * @param string $object_type Optional. Used when using add_action()
	 * @param array<string, string|array<string>> $args Optional. Used when using add_action()
	 * @throws Exception if the taxonomy doesn't exist
	 * @return void The taxonomy has been created
	 */
	public function create_custom_taxonomy( string $taxonomy = '', string $object_type = '', array $args = [] ): void {

		// $this->logger?->log()->debug( '[Lumiere][Taxonomy] create_custom_taxonomy()' . $taxonomy);
		$get_taxo_array = $this->lumiere_array_key_exists_wildcard( $this->imdb_data_values, 'imdbtaxonomy*', 'key-value' ); // Method in trait Data

		foreach ( $get_taxo_array as $key => $value ) {

			if ( is_string( $key ) === false ) {
				throw new Exception( esc_html__( 'Wrong taxonomy ', 'lumiere-movies' ) . esc_html( strval( $key ) ) );
			}

			$filter_taxonomy = str_replace( 'imdbtaxonomy', '', $key );

			// Check if a specific taxonomy (such as actor, genre) is activated.
			if ( $this->imdb_data_values[ 'imdbtaxonomy' . $filter_taxonomy ] === '1' ) {

				// Register activated taxonomies
				register_taxonomy(
					$this->imdb_admin_values['imdburlstringtaxo'] . $filter_taxonomy,
					[ 'page', 'post' ],
					[
						/* remove metaboxes from edit interface, keep the menu of post */
						'show_ui' => true,          /* whether to manage taxo in UI */
						'show_in_quick_edit' => false,      /* whether to show taxo in edit interface */
						'show_tagcloud' => false, /* whether to show Tag Cloud Widget controls */
						'meta_box_cb' => false,         /* whether to show taxo in metabox */
						/* other settings */
						'labels' => [
							'name' => 'Lumière ' . $filter_taxonomy . 's',
							'parent_item' => __( 'Parent taxonomy', 'lumiere-movies' ) . ' ' . $filter_taxonomy,
							'singular_name' => ucfirst( $filter_taxonomy ) . ' name',
							'menu_name' => 'Lumière ' . $filter_taxonomy,
							'search_items' => __( 'Search', 'lumiere-movies' ) . ' ' . $filter_taxonomy . 's',
							'add_new_item' => __( 'Add new', 'lumiere-movies' ) . ' ' . $filter_taxonomy,
						],
						'hierarchical' => true,         /* Whether there is a relationship between added terms, it's true!
						'public' => true,
						/* 'args' => [ 'lang' => 'en' ], 	REMOVED 2021 08 07, what's the point? */
						'query_var' => $this->imdb_admin_values['imdburlstringtaxo'] . $filter_taxonomy,
						'rewrite' => [
							'slug' => $this->imdb_admin_values['imdburlstringtaxo'] . $filter_taxonomy,
						],
					]
				);
			}
		}
	}
}
