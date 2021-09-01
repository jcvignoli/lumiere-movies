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
if ( ( ! defined( 'ABSPATH' ) ) || ( ! class_exists( '\Lumiere\Settings' ) ) ) {
	wp_die( esc_html__( 'You are not allowed to call this page directly.', 'lumiere-movies' ) );
}

use \Lumiere\Settings;

class Copy_Template_Taxonomy {

	/**
	 * Admin options
	 * @var array<string> $imdb_admin_values
	 */
	private array $imdb_admin_values;

	/**
	 * Widget options
	 * @var array<string> $imdb_widget_values
	 */
	private array $imdb_widget_values;

	/**
	 * List of items related to people
	 * From Settings class
	 * @var array<string> $array_people
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
	 * Copy the standard taxonomy template to the theme folder
	 */
	private function lumiere_copy_template_taxonomy(): void {

		global $wp_filesystem;

		// Escape gets and get taxotype and nonce.
		// phpcs:ignore WordPress.Security.NonceVerification
		$lumiere_taxo_title = isset( $_GET['taxotype'] ) ? esc_html( $_GET['taxotype'] ) : null;
		// Make various links and vars.
		$lumiere_taxo_file_tocopy = in_array( $lumiere_taxo_title, $this->array_people, true ) ? $lumiere_taxo_file_tocopy = \Lumiere\Settings::TAXO_PEOPLE_THEME : $lumiere_taxo_file_tocopy = \Lumiere\Settings::TAXO_ITEMS_THEME;
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
		if ( ( isset( $this->imdb_admin_values['imdbtaxonomy'] ) ) && ( strlen( $this->imdb_admin_values['imdbtaxonomy'] ) !== 0 )
		&& ( isset( $this->imdb_widget_values[ 'imdbtaxonomy' . $lumiere_taxo_title ] ) ) && ( strlen( $this->imdb_widget_values[ 'imdbtaxonomy' . $lumiere_taxo_title ] ) !== 0 )
		&& isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'taxo' ) ) {

			// Make sure we got right credentials to use $wp_filesystem
			Utils::lumiere_wp_filesystem_cred( $lumiere_taxonomy_theme_file );
			if ( $wp_filesystem->copy( $lumiere_taxonomy_theme_file, $lumiere_current_theme_path_file ) === false ) {
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

new Copy_Template_Taxonomy();
