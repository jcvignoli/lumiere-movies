<?php declare( strict_types = 1 );
/**
 * Detect new taxonomy templates
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2024, Lost Highway
 *
 * @version       2.0
 * @package lumiere-movies
 */

namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Settings;
use Lumiere\Admin\Admin_General;
use Lumiere\Admin\Admin_Notifications;
use Lumiere\Tools\Get_Options;

/**
 * Detect if new templates templates are available, or templates should be installed
 * Taxonomy theme pages copy class is called here
 * @phpstan-import-type OPTIONS_ADMIN from \Lumiere\Tools\Settings_Global
 * @phpstan-import-type OPTIONS_DATA from \Lumiere\Tools\Settings_Global
 * @since 4.1
 */
class Detect_New_Template_Taxo {

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
	 * Class \Lumiere\Settings
	 */
	public Settings $config_class;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->imdb_admin_values = get_option( Get_Options::get_admin_tablename() );
		$this->imdb_data_values = get_option( Get_Options::get_data_tablename() );
		$this->config_class = new Settings();
	}

	/**
	 * Static start
	 * Check if an new taxo template is available or if taxo template is missing
	 * @param string $page_data_taxo The name of the taxo page
	 * @return void
	 * @see \Lumiere\Admin\Admin_Menu\Data::lumiere_static_start() Calls this method
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
	 * Uses self::find_updated_template() method to check into them folder
	 *
	 * @since 4.1.1 added extra check for 'imdbtaxonomy'
	 *
	 * @param null|string $only_one_item If only one taxonomy item has to be checked, pass it, use a loop otherwise
	 * @return array<int, null|string> Array of updated templates or null if none
	 * @see \Lumiere\Admin\Submenu\Data::lumiere_display_new_taxo_template() Calls this method
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
			$array_all = array_merge( array_keys( $this->config_class->array_people ), array_keys( $this->config_class->array_items ) );
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
	 *
	 * @return array<int, string> Array of updated templates or null if none
	 */
	private function search_missing_template(): array {

		$output = [];

		if ( $this->imdb_admin_values['imdbtaxonomy'] !== '1' ) {
			return $output;
		}

		// Build array of people and items from config
		$array_all = array_merge( $this->config_class->array_people, $this->config_class->array_items );
		asort( $array_all );

		foreach ( $array_all as $item => $item_translated ) {

			$lumiere_taxo_file = 'taxonomy-' . $this->imdb_admin_values['imdburlstringtaxo'] . $item . '.php';
			$lumiere_current_theme_path_file = get_stylesheet_directory() . '/' . $lumiere_taxo_file;
			$taxo_key = 'imdbtaxonomy' . $item;

			if (
				isset( $this->imdb_data_values[ $taxo_key ] )
				&& $this->imdb_data_values[ $taxo_key ] === '1'
				&& is_file( $lumiere_current_theme_path_file ) === false
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
	private function find_updated_template( string $item ): ?string {

		global $wp_filesystem;

		$return = '';

		// Initial vars
		$version_theme = 'no_theme';
		$version_origin = '';
		$pattern = '~Version: (.+)~i'; // pattern for regex

		// Files paths built based on $item value
		$lumiere_taxo_file_tocopy = in_array( $item, array_keys( $this->config_class->array_people ), true ) ? $this->config_class::TAXO_PEOPLE_THEME : $this->config_class::TAXO_ITEMS_THEME;
		$lumiere_taxo_file_copied = 'taxonomy-' . $this->imdb_admin_values['imdburlstringtaxo'] . $item . '.php';
		$lumiere_current_theme_path_file = get_stylesheet_directory() . '/' . $lumiere_taxo_file_copied;
		$lumiere_taxonomy_theme_file = $this->imdb_admin_values['imdbpluginpath'] . $lumiere_taxo_file_tocopy;

		// Make sure we have the credentials to read the files - Function in trait Admin_General.
		$this->lumiere_wp_filesystem_cred( $lumiere_current_theme_path_file );

		// Exit if no current file found.
		if ( is_file( $lumiere_current_theme_path_file ) === false ) {
			return null;
		}

		// Get the taxonomy file version in the theme.
		$content_intheme = $wp_filesystem !== null ? $wp_filesystem->get_contents( $lumiere_current_theme_path_file ) : null;
		if ( is_string( $content_intheme ) && preg_match( $pattern, $content_intheme, $match ) === 1 ) {
			$version_theme = $match[1];
		}

		// Get the taxonomy file version in the lumiere theme folder.
		$content_inplugin = $wp_filesystem !== null ? $wp_filesystem->get_contents( $lumiere_taxonomy_theme_file ) : null;
		if ( is_string( $content_inplugin ) && preg_match( $pattern, $content_inplugin, $match ) === 1 ) {
			$version_origin = $match[1];
		}

		// If version in theme file is older, build the filename and the return it.
		if ( $version_theme !== $version_origin ) {
			$return = $item;
		}
		return strlen( $return ) > 0 ? $return : null;
	}
}
