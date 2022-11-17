<?php declare( strict_types = 1 );
/**
 * Move automatically the Lumière! template for taxonomy (class/theme/class-taxonomy*.php)
 * To the current user template folder.
 *
 * @version 1.0
 * @package lumiere-movies
 */

namespace Lumiere;

// If this file is called directly, abort.
if ( ( ! defined( 'ABSPATH' ) ) ) {
	wp_die( esc_html__( 'You are not allowed to call this page directly.', 'lumiere-movies' ) );
}

use Lumiere\Settings_Global;

class Copy_Template_Taxonomy {

	// Trait including the database settings.
	use Settings_Global;

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		// Construct Global Settings trait.
		$this->settings_open();

	}

	/**
	 * Copy the standard taxonomy template to the theme folder
	 */
	public function copy_template_taxonomy(): void {

		global $wp_filesystem;

		// Escape gets and get taxotype and nonce.
		// phpcs:ignore WordPress.Security.NonceVerification
		$lumiere_taxo_title = isset( $_GET['taxotype'] ) ? esc_html( $_GET['taxotype'] ) : null;

		// Make various links and vars.
		$lumiere_taxo_file_tocopy = in_array( $lumiere_taxo_title, $this->config_class->array_people, true ) ? $lumiere_taxo_file_tocopy = \Lumiere\Settings::TAXO_PEOPLE_THEME : $lumiere_taxo_file_tocopy = \Lumiere\Settings::TAXO_ITEMS_THEME;
		$lumiere_taxo_file_copied = 'taxonomy-' . $this->imdb_admin_values['imdburlstringtaxo'] . $lumiere_taxo_title . '.php';
		$lumiere_current_theme_path = get_stylesheet_directory() . '/';
		$lumiere_current_theme_path_file = $lumiere_current_theme_path . $lumiere_taxo_file_copied;
		$lumiere_taxonomy_theme_path = $this->imdb_admin_values['imdbpluginpath'];
		$lumiere_taxonomy_theme_file = $lumiere_taxonomy_theme_path . $lumiere_taxo_file_tocopy;

		// No $_GET["taxotype"] found or not in array, exit.
		if ( ( ! isset( $lumiere_taxo_title ) ) || ( strlen( $lumiere_taxo_title ) === 0 ) ) {
			wp_safe_redirect( add_query_arg( 'msg', 'taxotemplatecopy_failed', wp_get_referer() ) );
			exit();
		}

		/* Taxonomy is activated in the panel, and $_GET["taxotype"] exists
		   as a $imdb_widget_values, and there is a nonce from Data class */
		if ( ( $this->imdb_admin_values['imdbtaxonomy'] === '1' )
		&& ( $this->imdb_widget_values[ 'imdbtaxonomy' . $lumiere_taxo_title ] === '1' )
		&& isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'taxo' ) !== false ) {

			// Make sure we got right credentials to use $wp_filesystem
			Utils::lumiere_wp_filesystem_cred( $lumiere_taxonomy_theme_file );
			if ( $wp_filesystem->copy( $lumiere_taxonomy_theme_file, $lumiere_current_theme_path_file, true ) === false ) {
				// Copy failed.
				wp_safe_redirect( add_query_arg( 'msg', 'taxotemplatecopy_failed', wp_get_referer() ) );
				exit();
			}

			$content = $wp_filesystem->get_contents( $lumiere_current_theme_path_file );
			$content = str_replace( 'standard', $lumiere_taxo_title, $content );
			$content = str_replace( 'Standard', ucfirst( $lumiere_taxo_title ), $content );
			$wp_filesystem->put_contents( $lumiere_current_theme_path_file, $content, FS_CHMOD_FILE );
			wp_safe_redirect( add_query_arg( 'msg', 'taxotemplatecopy_success', wp_get_referer() ) );
			exit();
		}

		// If any condition is not met, wp_die()
		wp_die( esc_html__( 'Lumiere: you are not allowed to copy.', 'lumiere-movies' ) );

	}

}
