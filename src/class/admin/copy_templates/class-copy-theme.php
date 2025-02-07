<?php declare( strict_types = 1 );
/**
 * Copy Taxonomy Themes templates based on taxonomy
 *
 * @version 1.0
 * @package lumiere-movies
 */

namespace Lumiere\Admin\Copy_Templates;

// If this file is called directly, abort.
if ( ( ! defined( 'ABSPATH' ) ) ) {
	wp_die( esc_html__( 'You are not allowed to call this page directly.', 'lumiere-movies' ) );
}

use Lumiere\Plugins\Logger;
use Lumiere\Tools\Settings_Global;
use Lumiere\Tools\Get_Options;
use Lumiere\Admin\Admin_General;
use Exception;

/**
 * Copy Lumière taxonomy templates (located in class/theme) to user's template folder (wp-content/themes/current-theme)
 *
 * @since 4.0.1 removed wp_die()
 * @see \Lumiere\Admin\Admin_Menu This class is called in hook
 */
class Copy_Theme {

	/**
	 * Traits.
	 */
	use Settings_Global, Admin_General;

	/**
	 * Constructor
	 */
	public function __construct(
		protected Logger $logger = new Logger( 'copyTemplateTaxonomy' ),
	) {
		// Get Global Settings class properties.
		$this->get_db_options();
	}

	/**
	 * Regular static class call
	 * @param string $url_data_taxo_page The admin taxonomy page URL, used for redirects
	 */
	public static function start_copy_theme( string $url_data_taxo_page ): void {
		( new self() )->maybe_copy_theme_template( $url_data_taxo_page );
	}

	/**
	 * WP-CLI static class call
	 * @see \Lumiere\Tools\Cli_Commands::sub_copy_taxo()
	 * @param string $wp_cli_taxonomy The short taxonomy name, such as 'director'
	 */
	public static function wp_cli_copy_theme( string $wp_cli_taxonomy ): void {
		( new self() )->maybe_copy_theme_template( '', $wp_cli_taxonomy );
	}

	/**
	 * Maybe copy the standard taxonomy template to the theme folder
	 * @param string $url_data_taxo_page The admin taxonomy page URL, used for redirects
	 * @param string|null $wp_cli_taxonomy The short taxonomy name, such as 'director', passed only if wp-cli is used
	 * @return void Copy on success, display error message if failure
	 * @throws Exception if the template doesn't exist
	 */
	private function maybe_copy_theme_template( string $url_data_taxo_page, ?string $wp_cli_taxonomy = null ): void {

		// Use $_GET['taxotype'] if available (copy from admin page) or the taxonomy provided in second param if wp-cli called otherwise.
		$lumiere_taxo_title = esc_html( $this->get_taxotype_url() ?? $wp_cli_taxonomy ?? '' );

		// Build links and vars.
		if ( in_array( $lumiere_taxo_title, array_keys( Get_Options::get_list_people_taxo() ), true ) ) {
			$lumiere_taxo_file_tocopy = Get_Options::TAXO_PEOPLE_THEME;
		} elseif ( in_array( $lumiere_taxo_title, array_keys( Get_Options::get_list_items_taxo() ), true ) ) {
			$lumiere_taxo_file_tocopy = Get_Options::TAXO_ITEMS_THEME;
		} else {
			throw new Exception( 'This template ' . esc_html( $lumiere_taxo_title ) . ' does not exist, aborting' );
		}

		$lumiere_taxo_file_copied = 'taxonomy-' . $this->imdb_admin_values['imdburlstringtaxo'] . $lumiere_taxo_title . '.php';
		$lumiere_current_theme_path = get_stylesheet_directory() . '/';
		$lumiere_current_theme_path_file = $lumiere_current_theme_path . $lumiere_taxo_file_copied;
		$lumiere_taxonomy_theme_path = $this->imdb_admin_values['imdbpluginpath'];
		$lumiere_taxonomy_theme_file = $lumiere_taxonomy_theme_path . $lumiere_taxo_file_tocopy;

		// No $_GET["taxotype"] found or not in array, exit.
		if ( strlen( $lumiere_taxo_title ) === 0 ) {
			set_transient( 'notice_lumiere_msg', 'taxotemplatecopy_failed', 1 );
			$this->maybe_redirect( $url_data_taxo_page, $wp_cli_taxonomy );
		}

		/**
		 * Taxonomy is activated in the admin and
		 * $_GET['taxotype'] exists in $imdb_data_values
		 */
		if (
			$this->imdb_admin_values['imdbtaxonomy'] === '1'
			&& $this->imdb_data_values[ 'imdbtaxonomy' . $lumiere_taxo_title ] === '1'
		) {
			if ( $this->copy_theme_template( $lumiere_taxonomy_theme_file, $lumiere_current_theme_path_file, $lumiere_taxo_title ) === true ) {
				set_transient( 'notice_lumiere_msg', 'taxotemplatecopy_success', 1 );
				$this->logger->log->info( 'Template file ' . $lumiere_taxonomy_theme_file . ' was copied.' );
				$this->maybe_redirect( $url_data_taxo_page, $wp_cli_taxonomy );
				return;
			}
		}

		// If none of the previous conditions are met
		throw new Exception( 'Template copy failed for some reasons' );
	}

	/**
	 * Return the var $_GET['taxotype'] found in URL
	 * Used for admin panel copy
	 * @return string|null
	 */
	private function get_taxotype_url(): ?string {
		if (
			isset( $_GET['taxotype'] )
			&& isset( $_GET['_wpnonce_linkcopytaxo'] )
			&& wp_verify_nonce( sanitize_key( $_GET['_wpnonce_linkcopytaxo'] ), 'linkcopytaxo' ) > 0
		) {
			return sanitize_key( $_GET['taxotype'] );
		}
		return null;
	}

	/**
	 * Redirect to the calling page if it's not wp-cli calling
	 * @return void If it's an admin panel copy, exit, return if it wp-cli call
	 */
	private function maybe_redirect( string $url_data_taxo_page, ?string $wp_cli_taxonomy ): void {
		if ( isset( $wp_cli_taxonomy ) ) {
			return;
		}
		if ( wp_safe_redirect( $url_data_taxo_page ) ) {
			exit( 0 );
		}
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
	protected function copy_theme_template(
		string $lumiere_taxonomy_theme_file,
		string $lumiere_current_theme_path_file,
		string $lumiere_taxo_title
	): bool {

		global $wp_filesystem;

		// Make sure we got right credentials to use $wp_filesystem
		$this->lumiere_wp_filesystem_cred( $lumiere_taxonomy_theme_file ); // in trait Admin_General.

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
		if ( $content === false ) {
			// Copy failed.
			set_transient( 'notice_lumiere_msg', 'taxotemplatecopy_failed', 1 );
			return false;
		}

		$new_phpdoc_text = '* Automatically copied by Lumière! on ' . gmdate( 'd/m/Y @H:i:s' );
		$content_cleaned = preg_replace( '~\*\sYou can replace.*do it automatically~s', $new_phpdoc_text, $content );
		$content = $content_cleaned !== null ? str_replace( 'standard', $lumiere_taxo_title, $content_cleaned ) : $content;
		$content = str_replace( 'Standard', ucfirst( $lumiere_taxo_title ), $content );

		if ( $wp_filesystem->put_contents( $lumiere_current_theme_path_file, $content ) ) {
			return true;
		}

		// Copy failed.
		set_transient( 'notice_lumiere_msg', 'taxotemplatecopy_failed', 1 );
		return false;
	}
}
