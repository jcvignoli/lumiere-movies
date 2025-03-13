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

use Imdb\Name;
use Lumiere\Frontend\Popups\Head_Popups;
use Lumiere\Frontend\Popups\Popup_Basic;
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
 */
class Popup_Person extends Head_Popups implements Popup_Basic {

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
			$this->logger->log->error( '[Popup_Person] ' . esc_html( $text ) );
			wp_die( esc_html( $text ) );
		}

		$this->logger->log->debug( ' Movie person IMDb ID provided in URL: ' . esc_html( $person_id ) );
		return $person_class;
	}

	/**
	 * Display layout
	 *
	 * @return void
	 */
	public function display_layout(): void {

		echo "<!DOCTYPE html>\n<html>\n<head>\n";
		wp_head();
		echo "\n</head>\n<body class=\"lum_body_popup";
		echo isset( $this->imdb_admin_values['imdbpopuptheme'] ) ? ' lum_body_popup_' . esc_attr( $this->imdb_admin_values['imdbpopuptheme'] ) . '">' : '">';

		/**
		 * Display a spinner when clicking a link with class .lum_add_spinner (a <div class="loader"> will be inserted inside by the js)
		 */
		echo '<div id="spinner-placeholder"></div>';

		$this->logger->log->debug( '[Popup_Person] Using the link maker class: ' . str_replace( 'Lumiere\Link_Maker\\', '', get_class( $this->link_maker ) ) );

		// Show menu.
		$this->display_menu();

		// Show portrait.
		$this->display_portrait();

		//--------------------------------------------------------------------------- summary
		// display only when nothing is selected from the menu.
		$get_info_person = Validate_Get::sanitize_url( 'info_person' );
		if (
			$get_info_person === null || strlen( $get_info_person ) === 0
		) {
			$max_results = 9; /** max number of movies before breaking with "see all" */
			$display_summary = $this->get_movies_credit( $this->person_class, Settings_Popup::PERSON_SUMMARY_ROLES, $max_results );
			echo strlen( $display_summary ) > 0 ? wp_kses(
				$display_summary,
				[
					'span' => [ 'class' => [] ],
					'font' => [ 'size' => [] ],
					'strong' => [],
					'div' => [
						'align' => [],
						'rel' => [],
						'class' => [],
					],
					'i' => [],
					'a' => [
						'href' => [],
						'rel' => [],
						'class' => [],
						'title' => [],
					],
				]
			) : '<div class="lumiere_italic lumiere_align_center">' . esc_html__( 'No summary found ', 'lumiere-movies' ) . '</div>';
		}

		//--------------------------------------------------------------------------- full filmography
		if (
			$get_info_person === 'filmo'
		) {
			$max_results = 15; /** max number of movies before breaking with "see all" */
			$display_full_filmo = $this->get_movies_credit( $this->person_class, Settings_Popup::PERSON_ALL_ROLES, $max_results );
			echo strlen( $display_full_filmo ) > 0 ? wp_kses(
				$display_full_filmo,
				[
					'span' => [ 'class' => [] ],
					'font' => [ 'size' => [] ],
					'strong' => [],
					'div' => [
						'align' => [],
						'rel' => [],
						'class' => [],
					],
					'i' => [],
					'a' => [
						'href' => [],
						'rel' => [],
						'class' => [],
						'title' => [],
					],
				]
			) : '<div class="lumiere_italic lumiere_align_center">' . esc_html__( 'No filmography found ', 'lumiere-movies' ) . '</div>';

		}

		// ------------------------------------------------------------------------------ partie bio
		if (
			$get_info_person === 'bio'
		) {
			$display_bio = $this->get_items( $this->person_class, Settings_Popup::PERSON_DISPLAY_ITEMS_BIO );
			echo strlen( $display_bio ) > 0 ? wp_kses(
				$display_bio,
				[
					'span' => [ 'class' => [] ],
					'div' => [
						'align' => [],
						'class' => [],
						'id' => [],
					],
					'a' => [
						'href' => [],
						'rel' => [],
						'class' => [],
						'title' => [],
					],
					'font' => [ 'size' => [] ],
					'strong' => [],
					'i' => [],
				]
			) : '<div class="lumiere_italic lumiere_align_center">' . esc_html__( 'No biography found ', 'lumiere-movies' ) . '</div>';
		}

		// ------------------------------------------------------------------------------ misc part
		if (
			$get_info_person === 'misc'
		) {
			$display_misc = $this->get_items( $this->person_class, Settings_Popup::PERSON_DISPLAY_ITEMS_MISC );
			echo strlen( $display_misc ) > 0 ? wp_kses(
				$display_misc,
				[
					'span' => [ 'class' => [] ],
					'div' => [
						'align' => [],
						'class' => [],
						'id' => [],
					],
					'a' => [
						'href' => [],
						'rel' => [],
						'class' => [],
						'title' => [],
					],
					'font' => [ 'size' => [] ],
					'strong' => [],
					'i' => [],
				]
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
		// If polylang plugin is active, rewrite the URL to append the lang string
		$url_if_polylang = apply_filters( 'lum_polylang_rewrite_url_with_lang', Get_Options::get_popup_url( 'person', site_url() ) );
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
				<a rel="nofollow" class="lum_popup_menu_title lum_add_spinner" href="<?php echo esc_url( wp_nonce_url( $url_if_polylang . '?mid=' . $this->mid_sanitized . '&info_person=' ) ); ?>" title="<?php echo esc_attr( $this->page_title ) . ': ' . esc_html__( 'Summary', 'lumiere-movies' ); ?>"><?php esc_html_e( 'Summary', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				<a rel="nofollow" class="lum_popup_menu_title lum_add_spinner" href="<?php echo esc_url( wp_nonce_url( $url_if_polylang . '?mid=' . $this->mid_sanitized . '&info_person=filmo' ) ); ?>" title="<?php echo esc_attr( $this->page_title ) . ': ' . esc_html__( 'Full filmography', 'lumiere-movies' ); ?>"><?php esc_html_e( 'Full filmography', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				<a rel="nofollow" class="lum_popup_menu_title lum_add_spinner" href="<?php echo esc_url( wp_nonce_url( $url_if_polylang . '?mid=' . $this->mid_sanitized . '&info_person=bio' ) ); ?>" title="<?php echo esc_attr( $this->page_title ) . ': ' . esc_html__( 'Full biography', 'lumiere-movies' ); ?>"><?php esc_html_e( 'Full biography', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				<a rel="nofollow" class="lum_popup_menu_title lum_add_spinner" href="<?php echo esc_url( wp_nonce_url( $url_if_polylang . '?mid=' . $this->mid_sanitized . '&info_person=misc' ) ); ?>" title="<?php echo esc_attr( $this->page_title ) . ': ' . esc_html__( 'Misc', 'lumiere-movies' ); ?>"><?php esc_html_e( 'Misc', 'lumiere-movies' ); ?></a>
			</div>
		</div>

		<?php
	}

	/**
	 * Display portrait including the medaillon
	 */
	private function display_portrait(): void { ?>
												<!-- Photo & identity -->
		<div class="lumiere_display_flex lumiere_font_em_11 lumiere_align_center lum_padding_bott_2vh lum_padding_top_6vh">
			<div class="lumiere_flex_auto lum_width_fit_cont">
				<div class="identity"><?php echo esc_html( $this->page_title ); ?></div>

				<?php

				# Birth
				$get_birthday = $this->person_class->born();
				$birthday = $get_birthday !== null ? array_filter( $get_birthday, fn( $get_birthday ) => ( $get_birthday !== false && $get_birthday !== '' ) ) : [];
				if ( count( $birthday ) > 0 ) {
					echo "\n\t\t\t\t" . '<div id="birth"><font size="-1">';

					$birthday_day = isset( $birthday['day'] ) ? strval( $birthday['day'] ) . ' ' : '(' . __( 'day unknown', 'lumiere-movies' ) . ') ';
					$birthday_month = isset( $birthday['month'] ) && strlen( $birthday['month'] ) > 0 ? date_i18n( 'F', $birthday['month'] ) . ' ' : '(' . __( 'month unknown', 'lumiere-movies' ) . ') ';
					$birthday_year = isset( $birthday['year'] ) ? strval( $birthday['year'] ) : '(' . __( 'year unknown', 'lumiere-movies' ) . ')';

					echo "\n\t\t\t\t\t" . '<span class="lum_results_section_subtitle">'
						. esc_html__( 'Born on', 'lumiere-movies' ) . '</span>'
						. esc_html( $birthday_day . $birthday_month . $birthday_year );

					if ( isset( $birthday['place'] ) && strlen( $birthday['place'] ) > 0 ) {
						/** translators: 'in' like 'Born in' */
						echo ', ' . esc_html__( 'in', 'lumiere-movies' ) . ' ' . esc_html( $birthday['place'] );
					}

					echo "\n\t\t\t\t" . '</font></div>';
				}

				# Death
				$death = $this->person_class->died();
				if ( $death['status'] === 'DEAD' ) {

					echo "\n\t\t\t\t" . '<div id="death"><font size="-1">';

					$death_day = isset( $death['day'] ) && strlen( strval( $death['day'] ) ) > 0 ? (string) $death['day'] . ' ' : __( '(day unknown)', 'lumiere-movies' ) . ' ';
					$death_month = isset( $death['month'] ) && strlen( $death['month'] ) > 0 ? date_i18n( 'F', $death['month'] ) . ' ' : __( '(month unknown)', 'lumiere-movies' ) . ' ';
					$death_year = isset( $death['year'] ) && strlen( strval( $death['year'] ) ) > 0 ? (string) $death['year'] : __( '(year unknown)', 'lumiere-movies' );

					echo "\n\t\t\t\t\t" . '<span class="lum_results_section_subtitle">'
						. esc_html__( 'Died on', 'lumiere-movies' ) . '</span>'
						. esc_html( $death_day . $death_month . $death_year );

					if ( ( isset( $death['place'] ) ) && ( strlen( $death['place'] ) !== 0 ) ) {
						/** translators: 'in' like 'Died in' */
						echo ', ' . esc_html__( 'in', 'lumiere-movies' ) . ' ' . esc_html( $death['place'] );
					}

					if ( ( isset( $death['cause'] ) ) && ( strlen( $death['cause'] ) !== 0 ) ) {
						/** translators: 'cause' like 'Cause of death' */
						echo ' (' . esc_html( $death['cause'] . ')' );
					}

					echo "\n\t\t\t\t" . '</font></div>';
				}

				$bio = $this->link_maker->get_medaillon_bio( $this->person_class->bio() );

				if ( is_string( $bio ) && strlen( $bio ) > 0 ) {
					echo "\n\t\t\t\t" . '<div id="bio" class="lumiere_padding_two lumiere_align_left"><font size="-1">';
					echo "\n\t\t\t\t" . wp_kses(
						$bio,
						[
							'span' => [ 'class' => [] ],
							'a' => [
								'href' => [],
								'title' => [],
								'class' => [],
							],
							'strong' => [],
							'div' => [ 'class' => [] ],
							'br' => [],
						]
					) . '</font></div>';
				}
				?>

			</div>

					<!-- star photo -->
			<div class="lumiere_width_20_perc lumiere_padding_two lum_popup_img"><?php

			// Select pictures: big poster, if not small poster, if not 'no picture'.
			$photo_url = '';
			$photo_big = (string) $this->person_class->photoLocalurl( false );
			$photo_thumb = (string) $this->person_class->photoLocalurl( true );

			if ( $this->imdb_cache_values['imdbusecache'] === '1' ) { // use IMDBphp only if cache is active
				$photo_url = strlen( $photo_big ) > 1 ? esc_url( $photo_big ) : esc_url( $photo_thumb ); // create big picture, thumbnail otherwise.
			}

			// Picture for a href, takes big/thumbnail picture if exists, no_pics otherwise.
			$photo_url_href = strlen( $photo_url ) === 0 ? esc_url( Get_Options::LUM_PICS_URL . 'no_pics.gif' ) : $photo_url; // take big/thumbnail picture if exists, no_pics otherwise.

			// Picture for img: if 1/ thumbnail picture exists, use it, 2/ use no_pics otherwise
			$photo_url_img = strlen( $photo_thumb ) === 0 ? esc_url( Get_Options::LUM_PICS_URL . 'no_pics.gif' ) : $photo_thumb;

			echo "\n\t\t\t\t" . '<a class="lum_pic_inpopup" href="' . esc_url( $photo_url_href ) . '">';
			echo "\n\t\t\t\t\t" . '<img loading="lazy" src="'
				. esc_url( $photo_url_img )
				. '" alt="' . esc_attr( $this->page_title ) . '"';

			// add width only if "Display only thumbnail" is unactive.
			if ( $this->imdb_admin_values['imdbcoversize'] === '0' ) {
				$width = intval( $this->imdb_admin_values['imdbcoversizewidth'] );
				$height = $width * 1.4;
				echo ' width="' . esc_attr( strval( $width ) ) . '" height="' . esc_attr( strval( $height ) ) . '"';

				// add 100px width if "Display only thumbnail" is active.
			} elseif ( $this->imdb_admin_values['imdbcoversize'] === '1' ) {

				echo ' width="100" height="160"';

			}

			echo ' />';
			echo "\n\t\t\t\t</a>";

			?>

			</div> 
		</div> 
							
		<?php
	}

	/**
	 * Return a the list of Name items using modules
	 * @param Name $person_class
	 * @param list<string> $items list of items to convert to modules
	 * @phpstan-param Settings_Popup::PERSON_DISPLAY_ITEMS_BIO|Settings_Popup::PERSON_DISPLAY_ITEMS_MISC $items
	 */
	private function get_items( Name $person_class, array $items ): string {
		$output = '';
		foreach ( $items as $module ) {
			$class_name = Get_Options_Person::LUM_PERSON_MODULE_CLASS . ucfirst( $module );
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

	/**
	 * Get movie's credits
	 *
	 * @param Name $person_class
	 * @param list<string> $list_roles List of the roles, translated and pluralised in \Lumiere\Config\Settings_Person::credits_role_all()
	 * @phpstan-param Settings_Popup::PERSON_ALL_ROLES|Settings_Popup::PERSON_SUMMARY_ROLES $list_roles
	 * @param int<0, max> $max_movies Limit of the movies to display before breaking with "see all"
	 */
	private function get_movies_credit( Name $person_class, array $list_roles, int $max_movies ): string {
		$output = '';
		foreach ( $list_roles as $module ) {
			$class_name = Get_Options_Person::LUM_PERSON_MODULE_CLASS . 'Credit';
			if ( class_exists( $class_name ) === true ) {
				$class_module = new $class_name();
				$final_text = $class_module->get_module( $person_class, $module, $max_movies );
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

