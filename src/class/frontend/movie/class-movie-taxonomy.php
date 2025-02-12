<?php declare( strict_types = 1 );
/**
 * Class for dealing with movies' taxonomy movies.
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */

namespace Lumiere\Frontend\Movie;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Plugins\Logger;

/**
 * The class is meant to deal with taxonomy
 *
 * @since 4.4 Class created, using methods that were in Movie_Display
 * @phpstan-import-type OPTIONS_ADMIN from \Lumiere\Config\Settings
 */
class Movie_Taxonomy {

	public function __construct(
		private Logger $logger = new Logger( 'Movie_Taxonomy' ),
	) {}

	/**
	 * Insert taxonomy and return final options
	 *
	 * @since 4.0 Rewritten taxonomy system, not using Polylang anymore, links between languages created, hierarchical taxonomy terms
	 * @since 4.4 Splitted Taxonomy from layout, now the method is meant to create and get taxonomy details only
	 *
	 * @param string $type_item The general category of the item, ie 'director', 'color'
	 * @param string $taxonomy_term The name of the first string to display, ie "Stanley Kubrick"
	 * @param non-empty-array<string, string> $imdb_admin_values
	 * @phpstan-param OPTIONS_ADMIN $imdb_admin_values
	 * @return array<string, string>
	 * @phstan-return array{'custom_taxonomy_fullname': string, 'taxonomy_term': string}
	 */
	public function create_taxonomy_options( string $type_item, string $taxonomy_term, array $imdb_admin_values ): array {

		$taxonomy_term = esc_html( $taxonomy_term );
		$custom_taxonomy_fullname = esc_html( $imdb_admin_values['imdburlstringtaxo'] . $type_item ); // ie 'lumiere-director'
		$page_id = get_the_ID();

		/**
		 * Insert the taxonomies, add a relationship if a previous taxo exists
		 * Insert the current language displayed and the hierarchical value (child_of) if a previous taxo exists (needs register taxonomy with hierarchical)
		 */
		if ( $page_id !== false && taxonomy_exists( $custom_taxonomy_fullname ) ) {

			// delete if exists, for debugging purposes
			# $array_term_existing = get_term_by('name', $taxonomy_term, $custom_taxonomy_fullname );
			# if ( $array_term_existing )
			#	 wp_delete_term( $array_term_existing->term_id, $custom_taxonomy_fullname) ;

			$existent_term = term_exists( $taxonomy_term, $custom_taxonomy_fullname );

			// The term doesn't exist in the post.
			if ( $existent_term === null ) {
				$term_inserted = wp_insert_term( $taxonomy_term, $custom_taxonomy_fullname );
				$term_for_log = wp_json_encode( $term_inserted );
				if ( $term_for_log !== false ) {
					$this->logger->log->debug( '[Movie_Taxonomy] Taxonomy term *' . $taxonomy_term . '* added to *' . $custom_taxonomy_fullname . '* (association numbers ' . $term_for_log . ' )' );
				}
			}

			// If no term was inserted, take the current term.
			$term_for_set_object = $term_inserted ?? $taxonomy_term;

			/**
			 * Taxo terms could be inserted without error (it doesn't exist already), so add a relationship between the taxo and the page id number
			 * wp_set_object_terms() is almost always executed in order to add new relationships even if a new term wasn't inserted
			 */
			if ( ! $term_for_set_object instanceof \WP_Error ) {
				$term_taxonomy_id = wp_set_object_terms( $page_id, $term_for_set_object, $custom_taxonomy_fullname, true );
				$this->logger->log->debug( '[Movie_Taxonomy] Check (and made if needed) association for term_taxonomy_id ' . json_encode( $term_taxonomy_id ) );
			}
		}

		return [
			'custom_taxonomy_fullname' => $custom_taxonomy_fullname,
			'taxonomy_term' => $taxonomy_term,
		];
	}
}
