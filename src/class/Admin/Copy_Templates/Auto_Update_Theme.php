<?php declare( strict_types = 1 );
/**
 * Auto Update Taxonomy templates
 *
 * @version       1.0
 * @package       lumieremovies
 */

namespace Lumiere\Admin\Copy_Templates;

// If this file is called directly, abort.
if ( ( ! defined( 'ABSPATH' ) ) ) {
	wp_die( esc_html__( 'You are not allowed to call this page directly.', 'lumiere-movies' ) );
}

use Lumiere\Admin\Admin_General;
use Lumiere\Admin\Copy_Templates\Copy_Theme;
use Lumiere\Admin\Copy_Templates\Detect_New_Theme;
use Lumiere\Plugins\Logger;
use Lumiere\Config\Get_Options;

/**
 * Auto Update taxonomy templates in user's template folder (wp-content/themes/current-theme)
 * The class checks if there are template files that need to be updated
 *
 * @see Lumiere\Core::lum_setup_cron_exec_once() Set up a cron that executes on updates (manual, auto) and plugin activation
 * @see Lumiere\Admin\Cron\Cron::lumiere_exec_once_update() Executes this file
 * @since 4.3.2
 */
class Auto_Update_Theme extends Copy_Theme {

	/**
	 * Traits.
	 */
	use Admin_General;

	/**
	 * Constructor
	 */
	public function __construct(
		private Detect_New_Theme $detect_new_theme = new Detect_New_Theme(),
		protected Logger $logger = new Logger( 'autoUpdateTemplateTaxonomy' ),
	) {
		parent::__construct( $logger ); // Override logger name.
	}

	/**
	 * Update automatically the Lumiere themes files inside user theme folder
	 * @return void Taxonomy files in user theme folder have been updated
	 */
	public function update_auto_dest_theme(): void {

		$valid_taxos = $this->get_taxonomy_activated_items();
		foreach ( $valid_taxos as $item ) {
			// Check if the active taxonomy needs an update.
			if ( $this->detect_new_theme->find_updated_template( $item ) === $item ) {
				$destination_file = $this->detect_new_theme->get_template_paths( $item );
				// Update the template files in user theme folder.
				$this->update_new_templates( $item, $destination_file['origin'], $destination_file['destination'] );
			}
		}
	}

	/**
	 * Run a copy file from origin to destination (from Lumière theme plugin folder to user theme folder)
	 * Detect if 'TemplateAutomaticUpdate' exists in destination theme, and update it if it does
	 *
	 * @param string $item Taxonomy string, ie 'director'
	 * @param null|string $origin_file Lumiere template file
	 * @param null|string $destination_file File in user theme folder
	 * @return void Template updated if 'TemplateAutomaticUpdate' was found
	 */
	private function update_new_templates( string $item, ?string $origin_file, ?string $destination_file ): void {

		global $wp_filesystem;

		if ( ! isset( $destination_file ) || ! isset( $origin_file ) ) {
			$this->logger->log?->error( '[Auto_update_Theme] Missing origin or destination file, aborting' );
			return;
		}

		// Make sure we have the credentials to read the files.
		$this->wp_filesystem_cred( $origin_file ); // Function in trait Admin_General.

		// Lumiere's taxonomy file in user theme folder.
		$content_destination = $wp_filesystem->get_contents( $destination_file );

		// If 'TemplateAutomaticUpdate' is found, auto update
		if ( is_string( $content_destination ) && preg_match( '~TemplateAutomaticUpdate~i', $content_destination ) > 0 ) {
			parent::copy_theme_template( $origin_file, $destination_file, $item );
			$this->logger->log?->debug( '[Auto_update_Theme] Template file ' . $destination_file . ' has been updated to the latest version' );
			return;
		}
		$this->logger->log?->info( '[Auto_update_Theme] Template file ' . $destination_file . ' was not updated, probably TemplateAutomaticUpdate was removed.' );
	}

	/**
	 * Get an array of activated taxonomies and remove the 'imdburlstringtaxo' (ie, 'lumiere-')
	 * @return list<string> An array including only the custom taxonomy term (ie, 'director')
	 */
	private function get_taxonomy_activated_items(): array {

		$items = Get_Options::get_taxonomy_activated();
		$cleaned_item = [];

		foreach ( $items as $item ) {
			$cleaned_item[] = str_replace( $this->imdb_admin_values['imdburlstringtaxo'], '', $item );
		}
		return $cleaned_item;

	}
}
