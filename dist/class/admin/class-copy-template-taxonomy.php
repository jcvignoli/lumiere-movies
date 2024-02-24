<?php declare( strict_types = 1 );
/**
 * Copy Taxonomy templates
 *
 * @version 1.0
 * @package lumiere-movies
 */

namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ( ! defined( 'ABSPATH' ) ) ) {
	wp_die( esc_html__( 'You are not allowed to call this page directly.', 'lumiere-movies' ) );
}

use Lumiere\Settings_Global;
use Lumiere\Tools\Utils;

/**
 * Move automatically the taxonomy templates (in class/theme) to user's template folder (wp-content/themes/current-theme)
 * @see \Lumiere\Alteration\Taxonomy Is called in hook
 */
class Copy_Template_Taxonomy {

	// Trait including the database settings.
	use Settings_Global;

	/**
	 * Constructor
	 */
	public function __construct() {

		// Construct Global Settings trait.
		$this->settings_open();
	}

	/**
	 * Static class call for add_action()
	 */
	public static function lumiere_start_copy_taxo(): void {
		$class = new self();
		$class->maybe_copy_taxonomy_template();
	}

	/**
	 * Maybe copy the standard taxonomy template to the theme folder
	 * @return void Exit on failure, redirect on success
	 */
	private function maybe_copy_taxonomy_template(): void {

		// Escape gets and get taxotype and nonce.
		$lumiere_taxo_title =
			isset( $_GET['taxotype'] )
			&& ( isset( $_GET['_wpnonce_linkcopytaxo'] ) && wp_verify_nonce( $_GET['_wpnonce_linkcopytaxo'], 'linkcopytaxo' ) !== false )
				? esc_html( $_GET['taxotype'] )
				: null;

		// Build links and vars.
		$lumiere_taxo_file_tocopy = in_array( $lumiere_taxo_title, $this->config_class->array_people, true ) ? $lumiere_taxo_file_tocopy = $this->config_class::TAXO_PEOPLE_THEME : $lumiere_taxo_file_tocopy = $this->config_class::TAXO_ITEMS_THEME;
		$lumiere_taxo_file_copied = 'taxonomy-' . $this->imdb_admin_values['imdburlstringtaxo'] . $lumiere_taxo_title . '.php';
		$lumiere_current_theme_path = get_stylesheet_directory() . '/';
		$lumiere_current_theme_path_file = $lumiere_current_theme_path . $lumiere_taxo_file_copied;
		$lumiere_taxonomy_theme_path = $this->imdb_admin_values['imdbpluginpath'];
		$lumiere_taxonomy_theme_file = $lumiere_taxonomy_theme_path . $lumiere_taxo_file_tocopy;

		// No $_GET["taxotype"] found or not in array, exit.
		if ( ( ! isset( $lumiere_taxo_title ) ) || ( strlen( $lumiere_taxo_title ) === 0 ) ) {
			set_transient( 'notice_lumiere_msg', 'taxotemplatecopy_failed', 1 );
			$referer = wp_get_referer();
			if ( $referer !== false && wp_safe_redirect( $referer ) ) {
				exit;
			}
		}

		/* Taxonomy is activated in the panel, and $_GET['taxotype'] exists
		   as a $imdb_widget_values, and there is a nonce from Data class */
		if (
			$this->imdb_admin_values['imdbtaxonomy'] === '1'
			&& ( isset( $lumiere_taxo_title ) && $this->imdb_widget_values[ 'imdbtaxonomy' . $lumiere_taxo_title ] === '1' )
		) {

			$this->copy_taxonomy_template( $lumiere_taxonomy_theme_file, $lumiere_current_theme_path_file, $lumiere_taxo_title );
		}

		// If none of the previous conditions are met, wp_die()
		wp_die( esc_html__( 'Lumiere: you are not allowed to copy.', 'lumiere-movies' ) );

	}

	/**
	 * Copy the standard taxonomy template to the theme folder
	 * @param string $lumiere_taxonomy_theme_file Full name with path of the taxonomy file in Lumiere! class theme folder
	 * @param string $lumiere_current_theme_path_file Full name with path of the taxonomy file to be copied to the user theme folder
	 * @param string $lumiere_taxo_title The taxonomy title, ie "Director"
	 * @return void Exit on failure, File copy and redirects on success
	 */
	private function copy_taxonomy_template( string $lumiere_taxonomy_theme_file, string $lumiere_current_theme_path_file, string $lumiere_taxo_title ): void {

		global $wp_filesystem;

		// Make sure we got right credentials to use $wp_filesystem
		Utils::lumiere_wp_filesystem_cred( $lumiere_taxonomy_theme_file );

		if ( $wp_filesystem === null ) {
			esc_html_e( 'Could not get the credentials wp_filesystem for copying', 'lumiere-movies' );
			return;
		}

		if ( $wp_filesystem->copy( $lumiere_taxonomy_theme_file, $lumiere_current_theme_path_file, true ) === false ) {
			// Copy failed.
			set_transient( 'notice_lumiere_msg', 'taxotemplatecopy_failed', 1 );
			$referer = wp_get_referer();
			if ( $referer !== false && wp_safe_redirect( $referer ) ) {
				exit;
			}
		}

		$content = $wp_filesystem->get_contents( $lumiere_current_theme_path_file );
		$content = str_replace( 'standard', $lumiere_taxo_title, $content );
		$content = str_replace( 'Standard', ucfirst( $lumiere_taxo_title ), $content );
		$chmod = defined( 'FS_CHMOD_FILE' ) ? FS_CHMOD_FILE : false;
		$wp_filesystem->put_contents( $lumiere_current_theme_path_file, $content, $chmod );
		set_transient( 'notice_lumiere_msg', 'taxotemplatecopy_success', 1 );
		$referer = wp_get_referer();
		if ( $referer !== false && wp_safe_redirect( $referer ) ) {
			exit;
		}
	}
}
