<?php declare( strict_types = 1 );
/**
 * Detect new theme template based on custom Lumière taxonomy
 *
 * @copyright (c) 2024, Lost Highway
 *
 * @version       2.0
 * @package       lumieremovies
 */

namespace Lumiere\Admin\Copy_Templates;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Admin\Admin_General;
use Lumiere\Admin\Admin_Notifications;
use Lumiere\Config\Get_Options;
use Lumiere\Config\Get_Options_Movie;
use Lumiere\Config\Get_Options_Person;

/**
 * Detect if new templates templates are available, or templates should be installed
 * Taxonomy theme pages copy class is called here
 *
 * @phpstan-import-type OPTIONS_ADMIN from \Lumiere\Config\Settings
 * @phpstan-import-type OPTIONS_DATA from \Lumiere\Config\Settings_Movie
 * @since 4.1
 */
class Detect_New_Theme {

	/**
	 * Traits
	 */
	use Admin_General;

	/**
	 * Admin options vars
	 * @phpstan-var OPTIONS_ADMIN $imdb_admin_values
	 */
	public array $imdb_admin_values;

	/**
	 * Data options
	 * @phpstan-var OPTIONS_DATA $imdb_data_values
	 */
	public array $imdb_data_values;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->imdb_admin_values = get_option( Get_Options::get_admin_tablename() );
		$this->imdb_data_values = get_option( Get_Options_Movie::get_data_tablename() );
	}

	/**
	 * Static start
	 * Check if an new taxo template is available or if taxo template is missing
	 * @see \Lumiere\Admin\Admin_Menu\Data::lumiere_static_start() Calls this method
	 *
	 * @param string $page_data_taxo The name of the taxo page
	 * @return void
	 */
	public static function get_notif_templates( string $page_data_taxo ): void {
		$that = new self();

		$updated_template = $that->search_new_update();
		$install_new_template = $that->search_missing_template();

		if ( count( $updated_template ) > 0 ) {
			$class_admin_notif = new Admin_Notifications();
			add_action( 'admin_notices', fn() => $class_admin_notif->admin_msg_update_template( $updated_template, $page_data_taxo ), 11 );
		}

		if ( count( $install_new_template ) > 0 ) {
			$class_admin_notif = new Admin_Notifications();
			add_action( 'admin_notices', fn() => $class_admin_notif->admin_msg_install_missing_template( $install_new_template, $page_data_taxo ), 11 );
		}
	}

	/**
	 * Function checking if item/person template has been updated
	 * Use Detect_New_Theme::find_updated_template() method to check into them folder
	 *
	 * @since 4.1.1 added extra check for 'imdbtaxonomy'
	 * @see \Lumiere\Admin\Submenu\Data::lumiere_display_new_taxo_template() Calls this method
	 * @see Detect_New_Theme::get_notif_templates() Calls this method
	 *
	 * @param null|string $only_one_item If only one taxonomy item has to be checked, pass it, use a loop otherwise
	 * @return array<int, null|string> Array of updated templates or null if none
	 */
	public function search_new_update( ?string $only_one_item = null ): array {

		$output = [];

		if ( $this->imdb_admin_values['imdbtaxonomy'] !== '1' ) {
			return $output;
		}

		if ( isset( $only_one_item ) ) {
			$key = $this->find_updated_template( $only_one_item );
			if ( $key !== null ) {
				$output[] = $key;
			}
		} else {
			// Build array of people and items from config
			$array_all = array_merge( array_keys( Get_Options_Person::get_list_people_taxo() ), array_keys( Get_Options_Movie::get_list_items_taxo() ) );
			asort( $array_all );

			foreach ( $array_all as $item ) {
				$key = $this->find_updated_template( $item );
				if ( $key === null ) {
					continue;
				}
				$output[] = $key;
			}
		}
		return $output;
	}

	/**
	 * Function checking if item/person template is missing, should be installed
	 *
	 * @since 4.1.1 added extra check for 'imdbtaxonomy'
	 * @see Detect_New_Theme::get_notif_templates() Calls this method
	 *
	 * @return array<int, string> Array of updated templates or null if none
	 */
	private function search_missing_template(): array {

		$output = [];

		if ( $this->imdb_admin_values['imdbtaxonomy'] !== '1' ) {
			return $output;
		}

		// Build array of people and items from config
		$array_all = array_merge( Get_Options_Person::get_list_people_taxo(), Get_Options_Movie::get_list_items_taxo() );
		asort( $array_all );

		foreach ( $array_all as $item => $item_translated ) {

			$templates_paths = $this->get_template_paths( $item );
			$taxo_key = 'imdbtaxonomy' . $item;

			if (
				isset( $this->imdb_data_values[ $taxo_key ] )
				&& $this->imdb_data_values[ $taxo_key ] === '1'
				&& is_file( $templates_paths['destination'] ) === false
			) {
				$output[] = $item_translated;
			}
		}
		return $output;
	}

	/**
	 * Function checking if item/person template has been updated in the template
	 *
	 * @param string $item String used to build the taxonomy filename that will be checked against the standard taxo
	 * @return null|string
	 */
	public function find_updated_template( string $item ): ?string {

		global $wp_filesystem;

		$version_themes = [
			'destination' => '0',
			'origin' => '0',
		];
		$regex_pattern = '~Version: (.+)~i'; // pattern for regex

		// Files paths built based on $item value
		$templates_paths = $this->get_template_paths( $item );

		// Make sure we have the credentials to read the files
		$this->wp_filesystem_cred( $templates_paths['destination'] ); // Function in trait Admin_General.

		if ( $wp_filesystem === null || ! is_file( $templates_paths['destination'] ) ) {
			return null;
		}

		// Get the theme version in the lumiere plugins folder.
		$content_origin = $wp_filesystem->get_contents( $templates_paths['origin'] );
		if ( is_string( $content_origin ) && preg_match( $regex_pattern, $content_origin, $match ) === 1 ) {
			$version_themes['origin'] = $match[1];
		}

		// Get the theme version in the user theme folder.
		$content_destination = $wp_filesystem->get_contents( $templates_paths['destination'] );
		if ( is_string( $content_destination ) && preg_match( $regex_pattern, $content_destination, $match ) === 1 ) {
			$version_themes['destination'] = $match[1];
		}

		// If version in theme file is older, build the filename and the return it.
		if ( version_compare( $version_themes['origin'], $version_themes['destination'] ) > 0 ) {
			return $item;
		}
		return null;
	}

	/**
	 * Build templates paths
	 * $template_paths['origin']  is the standard template inside Lumière's plugin folder
	 * $template_paths['destination'] is the destination theme folder where the standard template will be copied
	 *
	 * @param string $item Taxonomy string, ie 'director'
	 * @return array<string, string>
	 * @phpstan-return array{origin: string, destination: string}
	 */
	public function get_template_paths( $item ): array {
		$template_paths = [];
		$original_in_plugin = in_array( $item, array_keys( Get_Options_Person::get_list_people_taxo() ), true )
			? Get_Options_Person::TAXO_PEOPLE_THEME
			: Get_Options::TAXO_ITEMS_THEME;
		$template_paths['origin'] = LUM_WP_PATH . $original_in_plugin;
		$template_paths['destination'] = get_stylesheet_directory() . '/' . Get_Options::LUM_THEME_TAXO_FILENAME_START . $this->imdb_admin_values['imdburlstringtaxo'] . $item . '.php';
		return $template_paths;
	}
}
