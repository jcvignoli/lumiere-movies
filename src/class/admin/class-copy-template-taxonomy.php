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

use Lumiere\Tools\Settings_Global;
use Lumiere\Tools\Utils;

/**
 * Move automatically the taxonomy templates (in class/theme) to user's template folder (wp-content/themes/current-theme)
 *
 * @since 4.0.1 removed wp_die()
 * @see \Lumiere\Admin\Admin_Menu This class is called in hook
 */
class Copy_Template_Taxonomy {

	// Trait including the database settings.
	use Settings_Global;

	/**
	 * Constructor
	 */
	public function __construct() {

		// Get Global Settings class properties.
		$this->get_settings_class();
		$this->get_db_options();

	}

	/**
	 * Static class call for add_action()
	 * @param string $url_data_taxo_page The admin taxonomy page URL, used for redirects
	 */
	public static function lumiere_start_copy_taxo( string $url_data_taxo_page ): void {
		$class = new self();
		$class->maybe_copy_taxonomy_template( $url_data_taxo_page );
	}

	/**
	 * Maybe copy the standard taxonomy template to the theme folder
	 * @param string $url_data_taxo_page The admin taxonomy page URL, used for redirects
	 * @return void Copy on success, display error message if failure
	 */
	private function maybe_copy_taxonomy_template( string $url_data_taxo_page ): void {

		// Escape gets and get taxotype and nonce.
		/** @psalm-suppress PossiblyNullArgument -- it's already checked in calling class */
		$lumiere_taxo_title = sanitize_title( $_GET['taxotype'] );

		// Build links and vars.
		$lumiere_taxo_file_tocopy = in_array( $lumiere_taxo_title, $this->config_class->array_people, true ) ? $lumiere_taxo_file_tocopy = $this->config_class::TAXO_PEOPLE_THEME : $lumiere_taxo_file_tocopy = $this->config_class::TAXO_ITEMS_THEME;
		$lumiere_taxo_file_copied = 'taxonomy-' . $this->imdb_admin_values['imdburlstringtaxo'] . $lumiere_taxo_title . '.php';
		$lumiere_current_theme_path = get_stylesheet_directory() . '/';
		$lumiere_current_theme_path_file = $lumiere_current_theme_path . $lumiere_taxo_file_copied;
		$lumiere_taxonomy_theme_path = $this->imdb_admin_values['imdbpluginpath'];
		$lumiere_taxonomy_theme_file = $lumiere_taxonomy_theme_path . $lumiere_taxo_file_tocopy;

		// No $_GET["taxotype"] found or not in array, exit.
		if ( strlen( $lumiere_taxo_title ) === 0 ) {

			set_transient( 'notice_lumiere_msg', 'taxotemplatecopy_failed', 1 );

			// Get the taxonomy option page from calling class
			if ( wp_safe_redirect( $url_data_taxo_page ) ) {
				exit;
			}
		}

		/* Taxonomy is activated in the panel, and $_GET['taxotype'] exists
		   as a $imdb_widget_values, and there is a nonce from Data class */
		if (
			$this->imdb_admin_values['imdbtaxonomy'] === '1'
			&& $this->imdb_widget_values[ 'imdbtaxonomy' . $lumiere_taxo_title ] === '1'
		) {

			if ( $this->copy_taxonomy_template( $lumiere_taxonomy_theme_file, $lumiere_current_theme_path_file, $lumiere_taxo_title ) === true ) {
				set_transient( 'notice_lumiere_msg', 'taxotemplatecopy_success', 1 );
			}

			if ( wp_safe_redirect( $url_data_taxo_page ) ) {
				exit;
			}
		}

		// If none of the previous conditions are met
		echo Utils::lumiere_notice( 3, esc_html__( 'Template copy failed for some reasons.', 'lumiere-movies' ) );
	}

	/**
	 * Copy the standard taxonomy template to the theme folder
	 *
	 * @param string $lumiere_taxonomy_theme_file Full name with path of the taxonomy file in Lumiere! class theme folder
	 * @param string $lumiere_current_theme_path_file Full name with path of the taxonomy file to be copied to the user theme folder
	 * @param string $lumiere_taxo_title The taxonomy title, ie "Director"
	 * @return bool True if file copy worked out
	 *
	 * @since 4.0.1 Returns bool
	 */
	private function copy_taxonomy_template( string $lumiere_taxonomy_theme_file, string $lumiere_current_theme_path_file, string $lumiere_taxo_title ): bool {

		global $wp_filesystem;

		// Make sure we got right credentials to use $wp_filesystem
		Utils::lumiere_wp_filesystem_cred( $lumiere_taxonomy_theme_file );

		if ( $wp_filesystem === null ) {
			esc_html_e( 'Could not get the credentials wp_filesystem for copying', 'lumiere-movies' );
			return false;
		}

		if ( $wp_filesystem->copy( $lumiere_taxonomy_theme_file, $lumiere_current_theme_path_file, true ) === false ) {
			// Copy failed.
			set_transient( 'notice_lumiere_msg', 'taxotemplatecopy_failed', 1 );
			return false;
		}

		$content = $wp_filesystem->get_contents( $lumiere_current_theme_path_file );
		$content = str_replace( 'standard', $lumiere_taxo_title, $content );
		$content = str_replace( 'Standard', ucfirst( $lumiere_taxo_title ), $content );
		$chmod = defined( 'FS_CHMOD_FILE' ) ? FS_CHMOD_FILE : false;
		if ( $wp_filesystem->put_contents( $lumiere_current_theme_path_file, $content, $chmod ) ) {
			return true;
		}

		// Copy failed.
		set_transient( 'notice_lumiere_msg', 'taxotemplatecopy_failed', 1 );
		return false;
	}
}
