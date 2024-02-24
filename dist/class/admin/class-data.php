<?php declare( strict_types = 1 );
/**
 * Child class for displaying data option selection
 * Child of Admin
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */

namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'You can not call directly this page' );
}

use Lumiere\Settings;
use Lumiere\Tools\Utils;

/**
 * Display data options for taxonomy, data order and data selection
 */
class Data extends \Lumiere\Admin {

	/**
	 * List of data details that display a field to enter
	 * A limit number in "Display" section
	 * @var array<string> $details_with_numbers
	 */
	private array $details_with_numbers = [];

	/**
	 * List of data details missing in the previous lists
	 * These are not meant to be limited in their numbers, are no taxo items or people
	 * @var array<string> $details_extra
	 */
	private array $details_extra = [];

	/**
	 * Constructor
	 *
	 */
	protected function __construct() {

		// Construct parent class
		parent::__construct();

		// Start logger
		$this->logger->lumiere_start_logger( 'adminData' );

		// Build the list of data details that include a number limit
		$this->details_with_numbers = [
			'actor' => __( 'actor', 'lumiere-movies' ),
			'alsoknow' => __( 'also known as', 'lumiere-movies' ),
			'goof' => __( 'goof', 'lumiere-movies' ),
			'plot' => __( 'plot', 'lumiere-movies' ),
			'producer' => __( 'producer', 'lumiere-movies' ),
			'quote' => __( 'quote', 'lumiere-movies' ),
			'soundtrack' => __( 'soundtrack', 'lumiere-movies' ),
			'tagline' => __( 'tagline', 'lumiere-movies' ),
			'trailer' => __( 'trailer', 'lumiere-movies' ),
		];

		// Build the list of the rest
		$this->details_extra = [
			'officialsites' => __( 'official websites', 'lumiere-movies' ),
			'prodcompany' => __( 'production company', 'lumiere-movies' ),
			'rating' => __( 'rating', 'lumiere-movies' ),
			'runtime' => __( 'runtime', 'lumiere-movies' ),
			'source' => __( 'source', 'lumiere-movies' ),
			'year' => __( 'year of release', 'lumiere-movies' ),
		];

		// Debugging mode
		if ( ( isset( $this->imdb_admin_values['imdbdebug'] ) ) && ( $this->imdb_admin_values['imdbdebug'] === '1' ) ) {

			// Start the class Utils to activate debug -> already started in admin_pages
			$this->utils_class->lumiere_activate_debug( $this->imdb_widget_values, 'no_var_dump', null );
		}

	}

	/**
	 * Display the body
	 */
	protected function lumiere_data_display_body(): void {

		echo "\n\t" . '<div id="poststuff" class="metabox-holder">';
		echo "\n\t\t" . '<div class="inside">';

		//------------------------------------------------------------------ =[Submit selection]=-
		echo "\n\t\t" . '<form method="post" id="imdbconfig_save" name="imdbconfig_save" action="' . esc_url( $_SERVER['REQUEST_URI'] ?? '' ) . '" >';

		//-------------------------------------------------------------------=[Data selection]=-
		if ( isset( $_GET['page'] ) && ( $_GET['page'] === 'lumiere_options_data' ) && ! isset( $_GET['widgetoption'] ) ) {

			// The template will retrieve the args. In parent class.
			$this->include_with_vars( 'data/admin-data-display', [ $this, $this->build_display_options()[0], $this->build_display_options()[1], $this->details_with_numbers ] );

		} elseif ( isset( $_GET['widgetoption'] ) && $_GET['widgetoption'] === 'taxo' ) {

			$this->lumiere_data_display_taxonomy();

		} elseif ( isset( $_GET['widgetoption'] ) && $_GET['widgetoption'] === 'order' ) {

			// The template will retrieve the args. In parent class.
			$this->include_with_vars( 'data/admin-data-order', [ $this ] );
		}

		//------------------------------------------------------------------ =[Submit selection]=-
		echo "\n\t\t\t\t" . '<div class="submit submit-imdb lumiere_sticky_boxshadow lumiere_align_center">' . "\n";
		wp_nonce_field( 'lumiere_nonce_data_settings', '_nonce_data_settings' );
		echo "\n\t\t\t\t" . '<input type="submit" class="button-primary" name="lumiere_reset_data_settings" value="'
			. esc_html__( 'Reset settings', 'lumiere-movies' )
			. '" />&nbsp;&nbsp;';
		echo "\n\t\t\t"
			. '<input type="submit" class="button-primary" id="lumiere_update_data_settings" name="lumiere_update_data_settings" value="'
			. esc_html__( 'Update settings', 'lumiere-movies' )
			. '" />';
		echo "\n\t\t\t" . '</div>';
		echo "\n\t\t" . '</form>';
		echo "\n\t\t" . '</div>';

	}

	/**
	 *  Display the fields for taxonomy selection
	 */
	private function lumiere_data_display_taxo_fields(): string {

		$output = '';
		$array_all = [];
		$array_all = array_merge( $this->config_class->array_people, $this->config_class->array_items );
		asort( $array_all );

		foreach ( $array_all as $item ) {

			$output .= "\n\t" . '<div class="imdblt_double_container_content_third lumiere_padding_five">';

			$output .= "\n\t\t" . '<input type="hidden" id="' . esc_attr( 'imdb_imdbtaxonomy' . $item . '_no' ) . '" name="' . esc_attr( 'imdb_imdbtaxonomy' . $item ) . '" value="0" />';

			$output .= "\n\t\t" . '<input type="checkbox" id="' . esc_attr( 'imdb_imdbtaxonomy' . $item . '_yes' ) . '" name="' . esc_attr( 'imdb_imdbtaxonomy' . $item ) . '" value="1"';

			if ( $this->imdb_widget_values[ 'imdbtaxonomy' . $item ] === '1' ) {
				$output .= ' checked="checked"';
			}

			$output .= ' />';
			$output .= "\n\t\t" . '<label for="' . esc_attr( 'imdb_imdbtaxonomy' . $item ) . '_yes">';

			if ( $this->imdb_widget_values[ 'imdbtaxonomy' . $item ] === '1' ) {
				if ( $this->imdb_widget_values[ 'imdbwidget' . $item ] === '1' ) {
					$output .= "\n\t\t" . '<span class="lumiere-option-taxo-activated">';
				} else {
					$output .= "\n\t\t" . '<span class="lumiere-option-taxo-deactivated">';
				}

				$output .= esc_html( ucfirst( $item ) );
				$output .= '</span>';

			} else {
				$output .= esc_html( ucfirst( $item ) );
				$output .= '&nbsp;&nbsp;';
			}
			$output .= "\n\t\t" . '</label>';

			// If template is activated, notify to copy or to update.
			if ( $this->imdb_widget_values[ 'imdbtaxonomy' . $item ] === '1' ) {
				$output .= $this->lumiere_display_new_taxo_template( $item );
			}
			$output .= "\n\t" . '</div>';

		}

		return $output;
	}

	/**
	 * Display Page Taxonomy
	 */
	private function lumiere_data_display_taxonomy(): void {

		// taxonomy is disabled
		if ( $this->imdb_admin_values['imdbtaxonomy'] !== '1' ) {

			echo "<div align='center' class='accesstaxo'>"
				. esc_html__( 'Please ', 'lumiere-movies' )
				. "<a href='" . esc_url( $this->page_general_advanced ) . "'>"
				. esc_html__( 'activate taxonomy', 'lumiere-movies' ) . '</a>'
				. esc_html__( ' priorly', 'lumiere-movies' ) . '<br />'
				. esc_html__( 'to access taxonomies options.', 'lumiere-movies' ) . '</div>';
			return;
		}

		// The template will retrieve the args. In parent class.
		$this->include_with_vars( 'data/admin-data-taxonomy', [ $this->lumiere_data_display_taxo_fields() ] );
	}

	/**
	 * Build the options for display
	 * @return array<int, array<string>>
	 */
	private function build_display_options(): array {

		// Merge the list of items and people with two extra lists
		$array_full = array_unique(
			array_merge(
				$this->config_class->array_people,
				$this->config_class->array_items,
				$this->details_extra,
				$this->details_with_numbers,
			)
		);

		// Sort the array to display in alphabetical order
		asort( $array_full );

		$comment = [
			'actor' => esc_html__( 'Display (how many) actors. This option also applies to the pop-up summary', 'lumiere-movies' ),
			'alsoknow' => esc_html__( 'Display (how many) alternative movie names and in other languages', 'lumiere-movies' ),
			'color' => esc_html__( 'Display colors', 'lumiere-movies' ),
			'composer' => esc_html__( 'Display composer', 'lumiere-movies' ),
			'country' => esc_html__( 'Display country. This option also applies to the pop-up summary', 'lumiere-movies' ),
			'creator' => esc_html__( 'Display Creator', 'lumiere-movies' ),
			'director' => esc_html__( 'Display directors. This option also applies to the pop-up summary', 'lumiere-movies' ),
			'genre' => esc_html__( 'Display genre. This option also applies to the pop-up summary', 'lumiere-movies' ),
			'goof' => esc_html__( 'Display (how many) goofs', 'lumiere-movies' ),
			'keyword' => esc_html__( 'Display keywords', 'lumiere-movies' ),
			'language' => esc_html__( 'Display languages. This option also applies to the pop-up summary', 'lumiere-movies' ),
			'officialsites' => esc_html__( 'Display official websites', 'lumiere-movies' ),
			'pic' => esc_html__( 'Display the main poster', 'lumiere-movies' ),
			'plot' => esc_html__( 'Display plots. This field may require much size in your page.', 'lumiere-movies' ),
			'producer' => esc_html__( 'Display (how many) producers', 'lumiere-movies' ),
			'prodcompany' => esc_html__( 'Display the production companies', 'lumiere-movies' ),
			'quote' => esc_html__( 'Display (how many) quotes of the person. This applies only to people pop-up summary.', 'lumiere-movies' ),
			'rating' => esc_html__( 'Display rating. This option also applies to the pop-up summary', 'lumiere-movies' ),
			'runtime' => esc_html__( 'Display the runtime. This option also applies to the pop-up summary', 'lumiere-movies' ),
			'soundtrack' => esc_html__( 'Display (how many) soundtracks', 'lumiere-movies' ),
			'source' => esc_html__( 'Display IMDb website source of the movie', 'lumiere-movies' ),
			'tagline' => esc_html__( 'Display (how many) taglines', 'lumiere-movies' ),
			'title' => esc_html__( 'Display the title', 'lumiere-movies' ),
			'trailer' => esc_html__( 'Display (how many) trailers', 'lumiere-movies' ),
			'writer' => esc_html__( 'Display writers', 'lumiere-movies' ),
			'year' => esc_html__( 'Display release year. The release year will appear next to the movie title into brackets', 'lumiere-movies' ),
		];

		return [ $array_full, $comment ];
	}

	/**
	 * Function checking if item/person template is missing or if a new one is available
	 * This function is triggered only if a the template option is activated
	 *
	 * @param string $type type to search (actor, genre, etc)
	 * @return string Link to copy the template if true and a message explaining if missing/update the template
	 */
	private function lumiere_display_new_taxo_template( string $type ): string {

		$output = '';

		// Get updated items/people from parent class method. Null if not template to update found.
		$list_updated_fields = $this->lumiere_new_taxo( $type );

		// Get the type to build the links
		$lumiere_taxo_title = esc_html( $type );

		// Files paths
		$lumiere_taxo_file_tocopy = in_array( $lumiere_taxo_title, $this->config_class->array_people, true ) ? Settings::TAXO_PEOPLE_THEME : Settings::TAXO_ITEMS_THEME;
		$lumiere_taxo_file_copied = 'taxonomy-' . $this->imdb_admin_values['imdburlstringtaxo'] . $lumiere_taxo_title . '.php';
		$lumiere_current_theme_path = get_stylesheet_directory() . '/';
		$lumiere_current_theme_path_file = $lumiere_current_theme_path . $lumiere_taxo_file_copied;
		$lumiere_taxonomy_theme_path = $this->imdb_admin_values['imdbpluginpath'];
		$lumiere_taxonomy_theme_file = $lumiere_taxonomy_theme_path . $lumiere_taxo_file_tocopy;

		// Make sure we have the credentials
		Utils::lumiere_wp_filesystem_cred( $lumiere_current_theme_path_file );

		// Make the HTML link with a nonce, checked in move_template_taxonomy.php.

		$link_taxo_copy = add_query_arg( '_wpnonce_linkcopytaxo', wp_create_nonce( 'linkcopytaxo' ), $this->page_data_taxo . '&taxotype=' . $lumiere_taxo_title );

		// No file in the theme folder found and no template to be updated found, offer to copy it and exit.
		if ( file_exists( $lumiere_current_theme_path_file ) === false && ! isset( $list_updated_fields ) ) {

			$output .= "\n\t" . '<br />';
			$output .= "\n\t" . '<div id="lumiere_copy_' . $lumiere_taxo_title . '">';
			$output .= "\n\t\t<a href='"
					. $link_taxo_copy
					. "' title='"
					. esc_html__( 'Create a taxonomy template into your theme folder.', 'lumiere-movies' )
					. "' ><img src='"
					. esc_url( $this->config_class->lumiere_pics_dir . 'menu/admin-widget-copy-theme.png' )
					. "' alt='copy the taxonomy template' align='absmiddle' align='absmiddle' /> "
					. esc_html__( 'Copy template', 'lumiere-movies' )
					. '</a>';

			$output .= "\n\t" . '<div><font color="red">' . esc_html( "No $lumiere_taxo_title template found" ) . '</font></div>';
			$output .= "\n\t" . '</div>';

			return $output;

			// No taxonomy template file in Lumière! theme folder found, notify and exit.
		} elseif ( is_file( $lumiere_taxonomy_theme_file ) === false ) {

			return "\n\t" . '<br /><div><i>' . esc_html__( 'Missing Lumiere template file. A problem has been detected with your installation.', 'lumiere-movies' ) . '</i></div>';

			// No template updated, template file exists, so it is up-to-date, notify and exit.
		} elseif ( ! isset( $list_updated_fields ) ) {
			return "\n\t" . '<br /><div><i>' . $output . ucfirst( $lumiere_taxo_title ) . ' ' . esc_html__( 'template up-to-date', 'lumiere-movies' ) . '</i></div>';
		}

		// Template file exists and need to be updated, notify there is a new version of the template and exit.
		$output .= "\n\t" . '<br />';
		$output .= "\n\t" . '<div id="lumiere_copy_' . $lumiere_taxo_title . '">';
		$output .= "\n\t\t<a href='"
				. $link_taxo_copy
				. "' title='"
				. esc_html__( 'Update your taxonomy template in your theme folder.', 'lumiere-movies' )
				. "' ><img src='" . esc_url( $this->config_class->lumiere_pics_dir . 'menu/admin-widget-copy-theme.png' ) . "' alt='copy the taxonomy template' align='absmiddle' /> "
				. esc_html__( 'Update template', 'lumiere-movies' ) . '</a>';

		$output .= "\n\t" . '<div><font color="red">'
			. esc_html( "New $lumiere_taxo_title template version available" )
			. '</font></div>';
		$output .= "\n\t" . '</div>';

		return $output;

	}

}

