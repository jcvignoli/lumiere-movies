<?php declare( strict_types = 1 );
/**
 * Popup for people
 *
 * @copyright (c) 2022, Lost Highway
 *
 * @version       3.0
 * @package       lumieremovies
 */

namespace Lumiere\Frontend\Popups;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Vendor\Imdb\Name;
use Lumiere\Frontend\Popups\Head_Popups;
use Lumiere\Frontend\Popups\Popup_Interface;
use Lumiere\Tools\Validate_Get;
use Lumiere\Config\Get_Options;
use Lumiere\Config\Get_Options_Person;
use Lumiere\Config\Settings_Popup;

/**
 * Display star information in a popup
 * Bots are banned before getting popups
 *
 * @see \Lumiere\Popups\Popup_Select Redirect to here according to the query var 'popup' in URL
 * @see \Lumiere\Frontend\Popups\Head_Popups Modify the popup header, Parent class, Bot banishement
 * @since 4.3 is child class
 * @since 4.6.2 Links are Polylang compatible
 */
final class Popup_Person extends Head_Popups implements Popup_Interface {

	/**
	 * The person queried as object result
	 */
	private Name $person_class;

	/**
	 * The person's name
	 */
	private string $page_title;

	/**
	 * Person's id, if provided
	 */
	private string $mid_sanitized;

	/**
	 * Full main URL
	 * @since 4.6.2 includes /lang polylang in URL if Polylang is active
	 */
	private string $popup_url;

	/**
	 * For wp_kses escaping
	 */
	private const ESC_HTML_POPUP_PERSON = [
		'span'   => [
			'class'   => [],
		],
		'font'   => [
			'size'    => [],
		],
		'div'    => [
			'align'   => [],
			'rel'     => [],
			'id'      => [],
			'class'   => [],
		],
		'strong' => [],
		'i'      => [],
		'img'    => [
			'loading' => [],
			'alt'     => [],
			'src'     => [],
			'class'   => [],
			'width'   => [],
			'height'  => [],
		],
		'a'      => [
			'href'    => [],
			'rel'     => [],
			'class'   => [],
			'title'   => [],
		],
	];

	/**
	 * Constructor
	 */
	public function __construct() {

		// Edit metas tags in popups and various checks in Parent class.
		parent::__construct();

		/**
		 * Build the properties.
		 */
		$this->mid_sanitized = Validate_Get::sanitize_url( 'mid' ) ?? '';
		$this->person_class = $this->get_result( $this->mid_sanitized );
		$this->page_title = $this->get_title( $this->person_class->name() );

		// If polylang plugin is active, rewrite the URL to append the lang string
		$this->popup_url = apply_filters( 'lum_polylang_rewrite_url_with_lang', Get_Options::get_popup_url( 'person', site_url() ) );

		/**
		 * Display title
		 * @since 4.3
		 */
		add_filter( 'document_title_parts', [ $this, 'edit_title' ] );
	}

	/**
	 * Edit the title of the page
	 *
	 * @param array<string, string> $title
	 * @phpstan-param array{title: string, page: string, tagline: string, site: string} $title
	 * @phpstan-return array{title: string, page: string, tagline: string, site: string}
	 */
	#[\Override]
	public function edit_title( array $title ): array {

		$new_title = strlen( $this->page_title ) > 0
			/* translators: %1s is a movie's title */
			? wp_sprintf( __( 'Informations about %1s', 'lumiere-movies' ), $this->page_title ) . ' - Lumi&egrave;re movies'
			: __( 'Unknown - Lumière movies', 'lumiere-movies' );

		$title['title'] = $new_title;

		return $title;
	}

	/**
	 * Get the title of the page
	 *
	 * @param string|null $title Person's name
	 * @return string
	 * @since 4.0 lowercase, less cache used.
	 */
	#[\Override]
	public function get_title( ?string $title ): string {
		return isset( $title ) ? str_replace( [ '\\', '+' ], [ '', ' ' ], esc_html( $title ) ) : '';
	}

	/**
	 * Search Name class for a given person_id
	 *
	 * @param string $person_id The IMDB id of the person
	 * @return Name
	 */
	private function get_result( string $person_id ): Name {

		$person_class = $this->plugins_classes_active['imdbphp']->get_name_class( $person_id, $this->logger->log );

		// if neither film nor mid are set, throw a 404 error
		if ( $person_class->name() === null ) {
			status_header( 404 );
			$text = __( 'Could not find any IMDb person with this query.', 'lumiere-movies' );
			$this->logger->log?->error( '[Popup_Person] ' . esc_html( $text ) );
			wp_die( esc_html( $text ) );
		}

		$this->logger->log?->debug( ' Movie person IMDb ID provided in URL: ' . esc_html( $person_id ) );
		return $person_class;
	}

	/**
	 * Display layout
	 *
	 * @return void
	 */
	#[\Override]
	public function display_layout(): void {

		echo "<!DOCTYPE html>\n<html " . wp_kses( get_language_attributes(), [ 'lang' => [] ] ) . ">\n<head>\n";
		wp_head();
		echo "\n</head>\n<body class=\"lum_body_popup";
		echo isset( $this->imdb_admin_values['imdbpopuptheme'] ) ? ' lum_body_popup_' . esc_attr( $this->imdb_admin_values['imdbpopuptheme'] ) . '">' : '">';

		/**
		 * Display a spinner when clicking a link with class .lum_add_spinner (a <div class="loader"> will be inserted inside by the js)
		 */
		echo '<div id="spinner-placeholder"></div>';

		$this->logger->log?->debug( '[Popup_Person] Using the link maker class: ' . str_replace( 'Lumiere\Link_Maker\\', '', get_class( $this->link_maker ) ) );

		// Show menu.
		$this->display_menu();

		//--------------------------------------------------------------------------- summary
		// display only when nothing is selected from the menu.
		$get_info_person = Validate_Get::sanitize_url( 'info_person' );
		if (
			$get_info_person === null || strlen( $get_info_person ) === 0
		) {

			$display_summary_intro = $this->get_items( $this->person_class, Settings_Popup::PERSON_SUMMARY_ROLES );
			echo strlen( $display_summary_intro ) > 0 ? wp_kses(
				$display_summary_intro,
				self::ESC_HTML_POPUP_PERSON
			) : '';

			$display_summary = $this->get_movies_credit( $this->person_class, Settings_Popup::PERSON_SUMMARY_ROLES );
			echo strlen( $display_summary ) > 0 ? wp_kses(
				$display_summary,
				self::ESC_HTML_POPUP_PERSON
			) : '<div class="lumiere_italic lumiere_align_center">' . esc_html__( 'No summary found ', 'lumiere-movies' ) . '</div>';
		}

		//--------------------------------------------------------------------------- full filmography
		if (
			$get_info_person === 'filmo'
		) {
			$display_full_filmo_intro = $this->get_items( $this->person_class, Settings_Popup::PERSON_ALL_ROLES );
			echo strlen( $display_full_filmo_intro ) > 0 ? wp_kses(
				$display_full_filmo_intro,
				self::ESC_HTML_POPUP_PERSON
			) : '';

			$display_full_filmo = $this->get_movies_credit( $this->person_class, Settings_Popup::PERSON_ALL_ROLES );
			echo strlen( $display_full_filmo ) > 0 ? wp_kses(
				$display_full_filmo,
				self::ESC_HTML_POPUP_PERSON
			) : '<div class="lumiere_italic lumiere_align_center">' . esc_html__( 'No filmography found ', 'lumiere-movies' ) . '</div>';

		}

		// ------------------------------------------------------------------------------ partie bio
		if (
			$get_info_person === 'bio'
		) {
			$display_bio = $this->get_items( $this->person_class, Settings_Popup::PERSON_DISPLAY_ITEMS_BIO );
			echo strlen( $display_bio ) > 0 ? wp_kses(
				$display_bio,
				self::ESC_HTML_POPUP_PERSON
			) : '<div class="lumiere_italic lumiere_align_center">' . esc_html__( 'No biography found ', 'lumiere-movies' ) . '</div>';
		}

		// ------------------------------------------------------------------------------ misc part
		if (
			$get_info_person === 'misc'
		) {
			$display_misc = $this->get_items( $this->person_class, Settings_Popup::PERSON_DISPLAY_ITEMS_MISC );
			echo strlen( $display_misc ) > 0 ? wp_kses(
				$display_misc,
				self::ESC_HTML_POPUP_PERSON
			) : '<div class="lumiere_italic lumiere_align_center">' . esc_html__( 'No misc info found ', 'lumiere-movies' ) . '</div>';
		}

		// The end.
		wp_meta();
		wp_footer();
		echo "</body>\n</html>";
	}

	/**
	 * Display navigation menu
	 */
	private function display_menu(): void {
		?>
												<!-- top page menu -->
		<div class="lumiere_container lumiere_font_em_11 lum_popup_titlemenu">
			<?php /* if ( isset( $_GET['info_person'] ) && strlen( $_GET['info_person'] ) > 0 ) {  ?>
			<div class="lumiere_flex_auto">
				<a rel="nofollow" id="lum_popup_link_back" class="lum_popup_menu_title lum_add_spinner" href="<?php
				$refer = wp_get_referer();
				echo $refer !== false ? esc_url( $refer ) : ''; ?>"><?php esc_html_e( 'Back', 'lumiere-movies' ); ?></a>
			</div>
			<?php }*/?>
			<div class="lumiere_flex_auto">
				<a rel="nofollow" class="lum_popup_menu_title lum_add_spinner" href="<?php echo esc_url( wp_nonce_url( $this->popup_url . '?mid=' . $this->mid_sanitized . '&info_person=' ) ); ?>" title="<?php echo esc_attr( $this->page_title ) . ': ' . esc_html__( 'Summary', 'lumiere-movies' ); ?>"><?php esc_html_e( 'Summary', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				<a rel="nofollow" class="lum_popup_menu_title lum_add_spinner" href="<?php echo esc_url( wp_nonce_url( $this->popup_url . '?mid=' . $this->mid_sanitized . '&info_person=filmo' ) ); ?>" title="<?php echo esc_attr( $this->page_title ) . ': ' . esc_html__( 'Full filmography', 'lumiere-movies' ); ?>"><?php esc_html_e( 'Full filmography', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				<a rel="nofollow" class="lum_popup_menu_title lum_add_spinner" href="<?php echo esc_url( wp_nonce_url( $this->popup_url . '?mid=' . $this->mid_sanitized . '&info_person=bio' ) ); ?>" title="<?php echo esc_attr( $this->page_title ) . ': ' . esc_html__( 'Full biography', 'lumiere-movies' ); ?>"><?php esc_html_e( 'Full biography', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				<a rel="nofollow" class="lum_popup_menu_title lum_add_spinner" href="<?php echo esc_url( wp_nonce_url( $this->popup_url . '?mid=' . $this->mid_sanitized . '&info_person=misc' ) ); ?>" title="<?php echo esc_attr( $this->page_title ) . ': ' . esc_html__( 'Misc', 'lumiere-movies' ); ?>"><?php esc_html_e( 'Misc', 'lumiere-movies' ); ?></a>
			</div>
		</div>

		<?php
	}

	/**
	 * Return a the list of Name items using modules
	 * @param Name $person_class
	 * @param list<string> $items list of items to convert to modules
	 * @phpstan-param Settings_Popup::PERSON_DISPLAY_ITEMS_BIO|Settings_Popup::PERSON_DISPLAY_ITEMS_MISC|Settings_Popup::PERSON_ALL_ROLES|Settings_Popup::PERSON_SUMMARY_ROLES $items
	 */
	private function get_items( Name $person_class, array $items ): string {
		$output = '';
		foreach ( $items as $module ) {
			$class_name = Get_Options_Person::LUM_PERSON_MODULE_CLASS . ucfirst( $module );
			if ( class_exists( $class_name ) === true ) {
				$class_module = new $class_name();
				// @phpstan-ignore method.notFound (Call to an undefined method object::get_module())
				$final_text = $class_module->get_module( $person_class, $module );
				if ( strlen( $final_text ) > 0 ) {
					$output .= $this->output_popup_class->person_element_embeded(
						$final_text,
						$module
					);
				}
			}
		}
		return $output;
	}

	/**
	 * Get movie's credits
	 *
	 * @param Name $person_class
	 * @param list<string> $list_roles List of the roles, translated and pluralised in \Lumiere\Config\Settings_Person::credits_role_all()
	 * @phpstan-param Settings_Popup::PERSON_ALL_ROLES|Settings_Popup::PERSON_SUMMARY_ROLES $list_roles
	 */
	private function get_movies_credit( Name $person_class, array $list_roles ): string {
		$output = '';
		foreach ( $list_roles as $module ) {
			$class_name = Get_Options_Person::LUM_PERSON_MODULE_CLASS . 'Credit';
			if ( class_exists( $class_name ) === true ) {
				$class_module = new $class_name();
				$final_text = $class_module->get_module( $person_class, $module );
				if ( strlen( $final_text ) > 0 ) {
					$output .= $this->output_popup_class->person_element_embeded(
						$final_text,
						$module
					);
				}
			}
		}
		return $output;
	}
}

