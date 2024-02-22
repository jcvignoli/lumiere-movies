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
	wp_die( 'You can not call directly this page' );
}

use Lumiere\Settings;
use Lumiere\Tools\Utils;
use Lumiere\Admin\Copy_Template_Taxonomy;

/**
 * Create Lumière! Taxonomy system
 * Taxonomy Pages names are added to the database
 * Pages are made availabe by using taxonomy templates (if copied in template folder)
 *
 * @phpstan-import-type OPTIONS_DATA from Settings
 * @phpstan-import-type OPTIONS_ADMIN from Settings
 */
class Taxonomy {

	private Utils $utils_class;

	/**
	 * @phpstan-var OPTIONS_DATA $imdb_widget_values
	 */
	private array $imdb_widget_values;

	/**
	 * @phpstan-var OPTIONS_ADMIN $imdb_admin_values
	 */
	private array $imdb_admin_values;

	/**
	 * Constructor
	 */
	public function __construct() {

		$this->imdb_widget_values = get_option( Settings::LUMIERE_WIDGET_OPTIONS );
		$this->imdb_admin_values = get_option( Settings::LUMIERE_ADMIN_OPTIONS );
		$this->utils_class = new Utils();

		// If taxonomy is not activated, exit.
		if ( ! isset( $this->imdb_admin_values['imdbtaxonomy'] ) || $this->imdb_admin_values['imdbtaxonomy'] !== '1' ) {
			return;
		}

		// Register taxomony and create custom taxonomy pages.
		add_action( 'init', [ $this, 'lumiere_create_taxonomies' ], 1 );

		// Make function available for copying taxonomy templates in Lumière! admin panel
		add_action( 'admin_init', [ $this, 'lumiere_copy_taxonomy_template' ] );
	}

	/**
	 * Static instanciation of the class
	 * Needed to be called in add_actions()
	 *
	 * @return void The class was instanciated
	 */
	public static function lumiere_static_start(): void {
		$taxonomy_class = new self();
	}

	/**
	 * Copy the standard Lumière taxonomy template to the user template folder
	 *
	 * @TODO The $_GET check should be done in the taxo template, not here!
	 *
	 * @return void The class was instanciated
	 */
	public function lumiere_copy_taxonomy_template(): void {
		if ( isset( $_GET['taxotype'] ) ) {
			$copy_class = new Copy_Template_Taxonomy();
			$copy_class->copy_template_taxonomy();
		}
	}

	/**
	 * Register taxomony and create custom taxonomy pages in the database
	 *
	 * 1/ Register taxonomy
	 * 2/ Add specific class for html tags for functions building links towards taxonomy pages --------- This part seems useless, not working, to remove!
	 * a search for all imdbtaxonomy* in config array,
	 * b if active write a filter to add a class to the link to the taxonomy page.
	 * c Can be utilised by get_the_term_list() the_terms() WP function, such as in taxo templates
	 */
	public function lumiere_create_taxonomies(): void {

		$get_taxo_array = $this->utils_class->lumiere_array_key_exists_wildcard( $this->imdb_widget_values, 'imdbtaxonomy*', 'key-value' );
		foreach ( $get_taxo_array as $key => $value ) {

			if ( is_string( $key ) === false ) {
				throw new \Exception( __( 'Could not find this taxo ', 'lumiere-movies' ) . $key );
			}

			$filter_taxonomy = str_replace( 'imdbtaxonomy', '', $key );

			// Check if a specific taxonomy (such as actor, genre) is activated.
			if ( $this->imdb_widget_values[ 'imdbtaxonomy' . $filter_taxonomy ] === '1' ) {

				// Register activated taxonomies
				register_taxonomy(
					$this->imdb_admin_values['imdburlstringtaxo'] . $filter_taxonomy,
					[ 'page', 'post' ],
					[
						/* remove metaboxes from edit interface, keep the menu of post */
						'show_ui' => true,          /* whether to manage taxo in UI */
						'show_in_quick_edit' => false,      /* whether to show taxo in edit interface */
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

				// Add hooks for each taxonomy
				/** Removed 2023 09
				if ( $value === '1' ) {
					// Build taxonomy raw name, such as 'lumiere-imdbtaxonomycolor'.
					$taxonomy_raw_string = $this->imdb_admin_values['imdburlstringtaxo'] . $key;
					// Build final hook name, such as 'term_links-lumiere-color'.
					$taxonomy_hook = str_replace( 'imdbtaxonomy', '', "term_links-{$taxonomy_raw_string}" );

					add_filter( $taxonomy_hook, [ $this, 'lumiere_taxonomy_add_class_to_links' ] );
				}*/
			}
		}
	}

	/**
	 * Add a class to taxonomy links constructed by WordPress
	 *
	 * @param array<string> $links
	 * @return array<string>
	 *
	 * @obsolete doesn't seem to have any effect or even to be referenced anywhere but in a .css
	 * @todo to fully remove it, 2023 09
	 */
	public function lumiere_taxonomy_add_class_to_links( array $links ): array {
		return str_replace( '<a href="', '<a class="linktaxonomy" href="', $links );
	}
}
