<?php declare( strict_types = 1 );
/**
 * Move automatically the Lumière! template for taxonomy (theme/taxonomy-imdblt_standard.php)
 * to the current user template folder
 *
 * @version 1.0
 * @package lumiere-movies
 */

namespace Lumiere;

// If this file is called directly, abort.
if ( ( ! defined( 'ABSPATH' ) ) || ( ! class_exists( '\Lumiere\Settings' ) ) ) {
	wp_die( esc_html__( 'You are not allowed to call this page directly.', 'lumiere-movies' ) );
}

use \Lumiere\Settings;

class Copy_Template {

	/**
	 * Store admin options
	 *
	 */
	private array $imdb_admin_values;

	/**
	 * Store widget options
	 *
	 */
	private array $imdb_widget_values;

	/**
	 * Store list of items related to people
	 * From Settings class
	 *
	 */
	private array $array_people;

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		// Get database options
		$this->imdb_admin_values = get_option( Settings::LUMIERE_ADMIN_OPTIONS );
		$this->imdb_widget_values = get_option( Settings::LUMIERE_WIDGET_OPTIONS );

		// Settings class and vars.
		$config_class = new Settings();

		// List of potential types for a person.
		$this->array_people = $config_class->array_people;

		// Copy the template file
		$this->lumiere_copy_template_taxonomy();

	}

	/**
	 * Request WP_Filesystem credentials if file doesn't have it.
	 *
	 */
	private function lumiere_wp_filesystem_cred ( string $file ): bool {

		$creds = request_filesystem_credentials( $file, '', false );
		if ( false === ( $creds ) ) {

			// if we get here, then we don't have credentials yet,
			// but have just produced a form for the user to fill in,
			// so stop processing for now

			return true; // stop the normal page form from displaying.
		}

		// now we have some credentials, try to get the wp_filesystem running.
		if ( ! WP_Filesystem( $creds ) ) {
			// our credentials were no good, ask the user for them again
			request_filesystem_credentials( $file, '', true, false, null );
			return true;
		}

		return false;
	}

	/**
	 * Copy the standard taxonomy template to the theme folder
	 */
	private function lumiere_copy_template_taxonomy(): void {
		global $wp_filesystem;
		// Escape gets and get taxotype and nonce.
		// phpcs:ignore
		$lumiere_taxo_title = isset( $_GET['taxotype'] ) ? esc_html( $_GET['taxotype'] ) : false;
		// phpcs:ignore
		$retrieved_nonce = isset( $_GET['_wpnonce'] ) ? esc_html( $_GET['_wpnonce'] ) : false;
		// Make various links and vars.
		$lumiere_taxo_file_tocopy = in_array( $lumiere_taxo_title, $this->array_people, true ) ? $lumiere_taxo_file_tocopy = \Lumiere\Settings::TAXO_PEOPLE_THEME : $lumiere_taxo_file_tocopy = \Lumiere\Settings::TAXO_ITEMS_THEME;
		$lumiere_taxo_file_copied = 'taxonomy-' . $this->imdb_admin_values['imdburlstringtaxo'] . $lumiere_taxo_title . '.php';
		$lumiere_current_theme_path = get_stylesheet_directory() . '/';
		$lumiere_current_theme_path_file = $lumiere_current_theme_path . $lumiere_taxo_file_copied;
		$lumiere_taxonomy_theme_path = $this->imdb_admin_values['imdbpluginpath'] . 'theme/';
		$lumiere_taxonomy_theme_file = $lumiere_taxonomy_theme_path . $lumiere_taxo_file_tocopy;
		/* Taxonomy is activated in the panel, and $_GET["taxotype"] exists
		   as a $imdb_widget_values, and there is a nonce from Data class */
		if ( ( isset( $this->imdb_admin_values['imdbtaxonomy'] ) ) && ( ! empty( $this->imdb_admin_values['imdbtaxonomy'] ) )
		&& ( isset( $this->imdb_widget_values[ 'imdbtaxonomy' . $lumiere_taxo_title ] ) ) && ( ! empty( $this->imdb_widget_values[ 'imdbtaxonomy' . $lumiere_taxo_title ] ) )
		&& wp_verify_nonce( $retrieved_nonce, 'taxo' ) ) {
			// No $_GET["taxotype"] found or not in array, exit.
			if ( ( ! isset( $lumiere_taxo_title ) ) || ( empty( $lumiere_taxo_title ) ) ) {
				wp_safe_redirect( add_query_arg( 'msg', 'taxotemplatecopy_failed', wp_get_referer() ) );
				exit();
			}
			// Make sure we got right credentials to use $wp_filesystem
			$this->lumiere_wp_filesystem_cred( $lumiere_taxonomy_theme_file );
			if ( $wp_filesystem->copy( $lumiere_taxonomy_theme_file, $lumiere_current_theme_path_file ) === false ) {
				// Copy failed.
				wp_safe_redirect( add_query_arg( 'msg', 'taxotemplatecopy_failed', wp_get_referer() ) );
				exit();
			}
			// Copy successful.
			$content = $wp_filesystem->get_contents( $lumiere_current_theme_path_file );
			$content = str_replace( 'standard', $lumiere_taxo_title, $content );
			$wp_filesystem->put_contents( $lumiere_current_theme_path_file, $content, FS_CHMOD_FILE );
			wp_safe_redirect( add_query_arg( 'msg', 'taxotemplatecopy_success', wp_get_referer() ) );
			exit();
		}
		// If any condition is not met, wp_die()
		wp_die( esc_html__( 'You are not allowed to call this page directly.', 'lumiere-movies' ) );
	}

}

$lumiere_copy_template = new Copy_Template();