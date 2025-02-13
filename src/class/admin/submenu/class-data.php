<?php declare( strict_types = 1 );
/**
 * Child class for displaying data option selection
 * Child of Admin_Menu
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       2.0
 * @package lumiere-movies
 */

namespace Lumiere\Admin\Submenu;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! class_exists( 'Lumiere\Config\Settings' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Admin\Admin_Menu;
use Lumiere\Admin\Copy_Templates\Detect_New_Theme;
use Lumiere\Tools\Debug;
use Lumiere\Config\Get_Options;

/**
 * Display data options for taxonomy, data order and data selection
 *
 * @since 4.0 Using templates file instead of the HTML code here
 * @see \Lumiere\Admin\Admin_Menu for templates copy, if put it here the transiant is not passed to { @link \Lumiere\Admin\Copy_Templates\Copy_Theme }
 */
class Data extends Admin_Menu {

	/**
	 * List of data comments
	 * @var array<string>
	 */
	private array $details_comments;

	/**
	 * Constructor
	 */
	protected function __construct() {

		// Construct parent class
		parent::__construct();

		// Build the list of data comments
		$this->details_comments = [
			'actor'         => esc_html__( 'Display (a number of) actors.', 'lumiere-movies' ),
			'alsoknow'      => esc_html__( 'Display (a number of) alternative movie names and in other languages', 'lumiere-movies' ),
			'color'         => esc_html__( 'Display colors', 'lumiere-movies' ),
			'composer'      => esc_html__( 'Display composer', 'lumiere-movies' ),
			'connection'    => esc_html__( 'Display (a number of) related movies', 'lumiere-movies' ),
			'country'       => esc_html__( 'Display country.', 'lumiere-movies' ),
			'creator'       => esc_html__( 'Display Creator', 'lumiere-movies' ),
			'director'      => esc_html__( 'Display directors.', 'lumiere-movies' ),
			'genre'         => esc_html__( 'Display genre.', 'lumiere-movies' ),
			'goof'          => esc_html__( 'Display (a number of) goofs', 'lumiere-movies' ),
			'keyword'       => esc_html__( 'Display keywords', 'lumiere-movies' ),
			'language'      => esc_html__( 'Display languages.', 'lumiere-movies' ),
			'officialsites' => esc_html__( 'Display official websites', 'lumiere-movies' ),
			'pic'           => esc_html__( 'Display the main poster', 'lumiere-movies' ),
			'plot'          => esc_html__( 'Display plots. This field may require much size in your page.', 'lumiere-movies' ),
			'producer'      => esc_html__( 'Display (a number of) producers', 'lumiere-movies' ),
			'prodcompany'   => esc_html__( 'Display the production companies', 'lumiere-movies' ),
			'quote'         => esc_html__( 'Display (a number of) quotes.', 'lumiere-movies' ),
			'rating'        => esc_html__( 'Display rating.', 'lumiere-movies' ),
			'runtime'       => esc_html__( 'Display the runtime.', 'lumiere-movies' ),
			'soundtrack'    => esc_html__( 'Display (a number of) soundtracks', 'lumiere-movies' ),
			'source'        => esc_html__( 'Display IMDb website source of the movie', 'lumiere-movies' ),
			'tagline'       => esc_html__( 'Display (a number of) taglines', 'lumiere-movies' ),
			'title'         => esc_html__( 'Display the title', 'lumiere-movies' ),
			'trailer'       => esc_html__( 'Display (a number of) trailers', 'lumiere-movies' ),
			'writer'        => esc_html__( 'Display writers', 'lumiere-movies' ),
			'year'          => esc_html__( 'Display release year. The release year will appear next to the movie title into brackets', 'lumiere-movies' ),
		];

	}

	/**
	 * Display the body
	 *
	 * @param \Lumiere\Admin\Cache\Cache_Files_Management $cache_mngmt_class Not utilised in this class, but needed in some other Submenu classes
	 * @param string $nonce nonce from Admin_Menu to be checked when doing $_GET checks
	 * @see \Lumiere\Admin\Admin_Menu::call_admin_subclass() Calls this method
	 */
	protected function lum_submenu_start( \Lumiere\Admin\Cache\Cache_Files_Management $cache_mngmt_class, string $nonce ): void {

		// First part of the menu
		$this->include_with_vars(
			'admin/admin-menu-first-part',
			[ $this ], /** Add an array with vars to send in the template */
			self::TRANSIENT_ADMIN,
		);

		// Show the vars if debug is activated.
		if ( ( isset( $this->imdb_admin_values['imdbdebug'] ) ) && ( $this->imdb_admin_values['imdbdebug'] === '1' ) ) {
			Debug::display_lum_vars( $this->imdb_data_values, 'no_var_dump', null );
		}

		// Display submenu
		$this->include_with_vars(
			'data/admin-data-submenu',
			[ $this ], /** Add an array with vars to send in the template */
			self::TRANSIENT_ADMIN,
		);

		if (
			wp_verify_nonce( $nonce, 'check_display_page' ) > 0
			&& isset( $_GET['page'] ) && str_contains( $this->page_data, sanitize_key( $_GET['page'] ) ) === true
			&& ! isset( $_GET['subsection'] )
		) {

			/**
			 * Display data template
			 * The template will retrieve the args. In parent class.
			 */
			$this->include_with_vars(
				'data/admin-data-display',
				[
					$this->imdb_data_values, // data options.
					$this->get_display_select_options()[0], // list of items and people with two extra lists.
					$this->get_display_select_options()[1], // explaination of items and people with the two extra lists.
					Get_Options::get_items_with_numbers(), // data details in a field to fill in.
				], /** Add an array with vars to send in the template */
				self::TRANSIENT_ADMIN,
			);

		} elseif (
			isset( $_GET['page'] ) && str_contains( $this->page_data_order, sanitize_key( $_GET['page'] ) ) === true
			&& isset( $_GET['subsection'] ) && str_contains( $this->page_data_order, sanitize_key( $_GET['subsection'] ) )
			&& wp_verify_nonce( $nonce, 'check_display_page' ) > 0
		) {

			/**
			 * Display data order
			 * The template will retrieve the args. In parent class.
			 */
			$this->include_with_vars(
				'data/admin-data-order',
				[
					$this,
					Get_Options::get_all_items(), // list of items and people with two extra lists.
				], /** Add an array with vars to send in the template */
				self::TRANSIENT_ADMIN,
			);

		} elseif (
			isset( $_GET['page'] ) && str_contains( $this->page_data_taxo, sanitize_key( $_GET['page'] ) ) === true
			&& isset( $_GET['subsection'] ) && str_contains( $this->page_data_taxo, sanitize_key( $_GET['subsection'] ) )
			&& wp_verify_nonce( $nonce, 'check_display_page' ) > 0
		) {

			/**
			 * Taxonomy data template
			 * The template will retrieve the args. In parent class.
			 */
			$this->include_with_vars(
				'data/admin-data-taxonomy',
				[
					$this,
					$this->get_taxo_fields(),
					( new Detect_New_Theme() )->search_new_update(),
					$this->page_data_taxo . '&taxotype=',
				], /** Add an array with vars to send in the template */
				self::TRANSIENT_ADMIN,
			);
		}
	}

	/**
	 * Get the fields for taxonomy selection
	 *
	 * @return array<string, string>
	 */
	private function get_taxo_fields(): array {
		$all_taxo_elements = Get_Options::get_list_all_elements_taxo();
		asort( $all_taxo_elements );
		return $all_taxo_elements;
	}

	/**
	 * Build the options for selection display
	 *
	 * @return array<int, array<string>>
	 */
	private function get_display_select_options(): array {

		// Merge the list of items and people with two extra lists
		$array_full = Get_Options::get_all_items();

		// Sort the array to display in alphabetical order
		asort( $array_full );

		// Add the comments to the arrays of items and people
		return [ $array_full, $this->details_comments ];
	}
}

