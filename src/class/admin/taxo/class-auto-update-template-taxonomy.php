<?php declare( strict_types = 1 );
/**
 * Auto Update Taxonomy templates
 *
 * @version 1.0
 * @package lumiere-movies
 */

namespace Lumiere\Admin\Taxo;

// If this file is called directly, abort.
if ( ( ! defined( 'ABSPATH' ) ) ) {
	wp_die( esc_html__( 'You are not allowed to call this page directly.', 'lumiere-movies' ) );
}

use Lumiere\Admin\Admin_General;
use Lumiere\Admin\Taxo\Copy_Template_Taxonomy;
use Lumiere\Admin\Taxo\Detect_New_Template_Taxo;
use Lumiere\Plugins\Logger;
use Lumiere\Tools\Get_Options;

/**
 * Auto Update taxonomy templates in user's template folder (wp-content/themes/current-theme)
 * The class checks if there are template files that need to be updated
 *
 * @see Lumiere\Core::lum_setup_cron_exec_once() Set up a cron that executes on updates (manual, auto) and plugin activation
 * @see Lumiere\Admin\Cron::lumiere_exec_once_update() Executes this file
 * @since 4.3.2
 */
class Auto_Update_Template_Taxonomy extends Copy_Template_Taxonomy {

	/**
	 * Traits.
	 */
	use Admin_General;

	/**
	 * Detect_New_Template_Taxo class
	 */
	private Detect_New_Template_Taxo $detect_new_template_taxo;

	/**
	 * Logger class
	 */
	private Logger $logger;

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		$this->detect_new_template_taxo = new Detect_New_Template_Taxo();

		// Start Logger class.
		$this->logger = new Logger( 'autoUpdateTemplateTaxonomy' );

		// add_action( 'admin_notices', [ 'Lumiere\Admin\Admin_Notifications', 'lumiere_static_start'], 11 ); // What for? Run in cron.
	}

	/**
	 * Update automatically the Lumiere themes files inside user theme folder
	 * @return void Taxonomy files in user theme folder have been updated
	 */
	public function update_auto_dest_theme(): void {

		$valid_taxos = $this->get_taxonomy_activated_items();
		foreach ( $valid_taxos as $item ) {
			// Check if the active taxonomy needs an update.
			if ( $this->detect_new_template_taxo->find_updated_template( $item ) === $item ) {
				$destination_file = $this->detect_new_template_taxo->get_template_paths( $item );
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
			$this->logger->log()->error( 'Missing origin or destination file' );
			return;
		}

		// Make sure we have the credentials to read the files.
		$this->lumiere_wp_filesystem_cred( $origin_file ); // Function in trait Admin_General.

		// If 'TemplateAutomaticUpdate' is found, auto update
		$content_destination = $wp_filesystem->get_contents( $destination_file );
		if ( is_string( $content_destination ) && preg_match( '~TemplateAutomaticUpdate~i', $content_destination ) > 0 ) {
			$templates_paths = $this->detect_new_template_taxo->get_template_paths( $item );
			// Copy files.
			parent::copy_taxonomy_template( $origin_file, $destination_file, $item );
			// set_transient( 'notice_lumiere_msg', 'taxotemplateautoupdate_success', 1 ); // What for? Run in cron.
			$this->logger->log()->debug( 'Template file ' . $destination_file . ' has been updated to the latest version' );
			return;
		}
		$this->logger->log()->info( 'Template file ' . $destination_file . ' was not updated, probably TemplateAutomaticUpdate was removed.' );
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
