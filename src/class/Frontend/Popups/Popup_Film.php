<?php declare( strict_types = 1 );
/**
 * Popup for movies
 *
 * @copyright (c) 2022, Lost Highway
 *
 * @version       3.0
 * @package       lumieremovies
 */

namespace Lumiere\Frontend\Popups;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! class_exists( 'Lumiere\Config\Settings' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Frontend\Popups\Head_Popups;
use Lumiere\Frontend\Popups\Popup_Interface;
use Lumiere\Tools\Validate_Get;
use Lumiere\Config\Get_Options;
use Lumiere\Config\Get_Options_Movie;
use Lumiere\Config\Settings_Popup;
use Lumiere\Vendor\Imdb\Title;

/**
 * Display movie information in a popup
 * Bots are banned before getting popups
 *
 * @see \Lumiere\Popups\Popup_Select Redirect to here according to the query var 'popup' in URL
 * @see \Lumiere\Frontend\Popups\Head_Popups Modify the popup header, Parent class, Bot banishement
 * @since 4.3 is child class
 * @since 4.6.2 Links are Polylang compatible
 */
final class Popup_Film extends Head_Popups implements Popup_Interface {

	/**
	 * The movie Title class instanciated with title
	 */
	private Title $movie_class;

	/**
	 * The movie Title
	 */
	private string $page_title;

	/**
	 * Full main URL
	 * @since 4.6.2 includes /lang polylang in URL if Polylang is active
	 */
	private string $popup_url;

	/**
	 * Full URL to search URL
	 * @since 4.6.2 includes /lang polylang in URL if Polylang is active
	 */
	private string $popup_url_search;

	/**
	 * Constructor
	 */
	public function __construct() {

		// Edit metas tags in popups and various checks in Parent class.
		parent::__construct();

		/**
		 * Build the properties.
		 */
		$movie_id = $this->get_movieid( Validate_Get::sanitize_url( 'mid' ), Validate_Get::sanitize_url( 'film' ) );
		$this->movie_class = $this->get_title_class( $movie_id );
		$this->page_title = $this->get_title( null /** must pass something due to interface, but will with Movie class the title */ );
		// If polylang plugin is active, rewrite the URL to append the lang string
		$this->popup_url = apply_filters( 'lum_polylang_rewrite_url_with_lang', Get_Options::get_popup_url( 'film', site_url() ) );
		$this->popup_url_search = apply_filters( 'lum_polylang_rewrite_url_with_lang', Get_Options::get_popup_url( 'movie_search', site_url() ) );

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
			? wp_sprintf( __( 'Informations about %1s', 'lumiere-movies' ), esc_html( $this->page_title ) ) . ' - Lumi&egrave;re movies'
			: __( 'Unknown - Lumière movies', 'lumiere-movies' );

		$title['title'] = $new_title;

		return $title;
	}

	/**
	 * Get the title of the page
	 *
	 * @param string|null $title Movie's name sanitized -- Here not in use, using Title imdb class to get the title
	 * @return string
	 * @since 4.0 lowercase, less cache used.
	 */
	#[\Override]
	public function get_title( ?string $title ): string {
		return ucfirst( $this->movie_class->title() );
	}

	/**
	 * Find the movie id of the film
	 * A movie id can be provided or just the movie's title
	 * If movie's title, do a IMDbphp query to get the ID
	 *
	 * @param string|null $movie_id
	 * @param string|null $movie_title
	 * @return string The movie's ID
	 */
	private function get_movieid( ?string $movie_id, ?string $movie_title ): string {

		$final_movie_id = null;

		// A movie imdb id is provided in URL.
		if ( isset( $movie_id ) && strlen( $movie_id ) > 0 ) {

			$this->logger->log?->debug( '[Popup_Movie] Movie id provided in URL: ' . esc_html( $movie_id ) );

			$final_movie_id = $movie_id;

			// No movie id is provided, but a title was.
		} elseif ( isset( $movie_title ) && strlen( $movie_title ) > 0 ) {

			$this->logger->log?->debug( '[Popup_Movie] Movie title provided in URL: ' . esc_html( $movie_title ) );

			// Search the movie's ID according to the title.
			$search = $this->plugins_classes_active['imdbphp']->search_movie_title(
				esc_html( $movie_title ),
				$this->logger->log,
			);

			// Keep the first occurrence.
			$final_movie_id = isset( $search[0] ) ? esc_html( $search[0]['imdbid'] ) : null;
		}

		// Exit if no movie was found.
		if ( $final_movie_id === null ) {
			status_header( 404 );
			$text = __( 'Could not find any IMDb movie with this query.', 'lumiere-movies' );
			$this->logger->log?->error( '[Popup_Movie] ' . esc_html( $text ) );
			wp_die( esc_html( $text ) );
		}

		return $final_movie_id;
	}

	/**
	 * Search movie id or title
	 *
	 * @return Title The title or null
	 */
	private function get_title_class( string $movieid ): Title {
		return $this->plugins_classes_active['imdbphp']->get_title_class( $movieid, $this->logger->log );
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

		$this->logger->log?->debug( '[Popup_Movie] Using the link maker class: ' . str_replace( 'Lumiere\Link_Maker\\', '', get_class( $this->link_maker ) ) );

		$this->display_menu( $this->movie_class, $this->page_title );

		// Introduction part.
		// Display something when nothing has been selected in the menu.
		$get_info = Validate_Get::sanitize_url( 'info' );
		if ( $get_info === null || strlen( $get_info ) === 0 ) {
			echo wp_kses(
				$this->get_items( $this->movie_class, Settings_Popup::FILM_DISPLAY_ITEMS_INTRO ),
				[
					'div'  => [
						'class'  => [],
						'align'  => [],
						'id'     => [],
					],
					'span' => [
						'class'  => [],
					],
					'img'  => [
						'src'    => [],
						'title'  => [],
						'width'  => [],
						'height' => [],
						'class'  => [],
					],
					'a'    => [
						'href'   => [],
						'rel'    => [],
						'class'  => [],
						'title'  => [],
					],
					'i'              => [],
					'br'             => [],
					'strong'         => [],
				]
			);
		}

		// Casting part.
		if ( $get_info === 'actors' ) {
			$actor_info = $this->get_items_two_columns( $this->movie_class, Settings_Popup::FILM_DISPLAY_ITEMS_CASTING );
			echo strlen( $actor_info ) > 0 ? wp_kses(
				$actor_info,
				[
					'div'  => [
						'class'  => [],
						'align'  => [],
					],
					'i'    => [],
					'span' => [
						'class'  => [],
					],
					'a'    => [
						'href'   => [],
						'rel'    => [],
						'class'  => [],
						'title'  => [],
					],
				]
			) : '<div class="lumiere_italic lumiere_align_center">' . esc_html__( 'No actors info found ', 'lumiere-movies' ) . '</div>';

		}

		// Crew part.
		if ( $get_info === 'crew' ) {
			$crew_info = $this->get_items_two_columns( $this->movie_class, Settings_Popup::FILM_DISPLAY_ITEMS_CREW );
			echo strlen( $crew_info ) > 0 ? wp_kses(
				$crew_info,
				[
					'div'  => [
						'class'  => [],
						'align'  => [],
					],
					'i'    => [],
					'span' => [
						'class'  => [],
					],
					'a'    => [
						'href'   => [],
						'rel'    => [],
						'class'  => [],
						'title'  => [],
					],
				]
			) : '<div class="lumiere_italic lumiere_align_center">' . esc_html__( 'No crew info found ', 'lumiere-movies' ) . '</div>';
		}

		// Resume part.
		if ( $get_info === 'resume' ) {
			$resume_info = $this->get_items( $this->movie_class, Settings_Popup::FILM_DISPLAY_ITEMS_PLOT );
			echo strlen( $resume_info ) > 0 ? wp_kses(
				$resume_info,
				[
					'div'  => [
						'class'  => [],
						'align'  => [],
						'id'     => [],
					],
					'span' => [
						'class'  => [],
					],
					'img'  => [
						'src'    => [],
						'title'  => [],
						'width'  => [],
						'height' => [],
						'class'  => [],
					],
					'a'    => [
						'href'   => [],
						'rel'    => [],
						'class'  => [],
						'title'  => [],
					],
					'i'              => [],
					'br'             => [],
					'strong'         => [],
				]
			) : '<div class="lumiere_italic lumiere_align_center">' . esc_html__( 'No summary info found ', 'lumiere-movies' ) . '</div>';
		}

		// Misc part.
		if ( $get_info === 'divers' ) {
			$misc_info = $this->get_items( $this->movie_class, Settings_Popup::FILM_DISPLAY_ITEMS_MISC );
			echo strlen( $misc_info ) > 0 ? wp_kses(
				$misc_info,
				[
					'div'  => [
						'class'  => [],
						'align'  => [],
						'id'     => [],
					],
					'span' => [
						'class'  => [],
					],
					'img'  => [
						'src'    => [],
						'title'  => [],
						'width'  => [],
						'height' => [],
						'class'  => [],
					],
					'a'    => [
						'href'   => [],
						'rel'    => [],
						'class'  => [],
						'title'  => [],
					],
					'i'              => [],
					'br'             => [],
					'strong'         => [],
				]
			) : '<div class="lumiere_italic lumiere_align_center">' . esc_html__( 'No misc info found ', 'lumiere-movies' ) . '</div>';
		}

		// The end.
		wp_meta();
		wp_footer();
		echo "</body>\n</html>";
	}

	/**
	 * Show the menu
	 */
	private function display_menu( Title $movie_class, string $film_title ): void {

		?>
					<!-- top page menu -->

		<div class="lumiere_container lumiere_font_em_11 lum_popup_titlemenu">
			<div class="lumiere_flex_auto">
				&nbsp;<a rel="nofollow" id="searchaka" class="lum_popup_menu_title lum_add_spinner" href="<?php echo esc_url( wp_nonce_url( $this->popup_url_search . '?film=' . $film_title ) ); ?>" title="<?php esc_html_e( 'Search for other movies with the same title', 'lumiere-movies' ); ?>"><?php esc_html_e( 'Similar Titles', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				&nbsp;<a rel="nofollow" class="lum_popup_menu_title lum_add_spinner" href="<?php echo esc_url( wp_nonce_url( $this->popup_url . '?mid=' . $movie_class->imdbid() . '&film=' . $film_title . '&info=' ) ); ?>" title='<?php echo esc_attr( $movie_class->title() ) . ': ' . esc_html__( 'Movie', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Summary', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				&nbsp;<a rel="nofollow" class="lum_popup_menu_title lum_add_spinner" href="<?php echo esc_url( wp_nonce_url( $this->popup_url . '?mid=' . $movie_class->imdbid() . '&film=' . $film_title . '&info=actors' ) ); ?>" title='<?php echo esc_attr( $movie_class->title() ) . ': ' . esc_html__( 'Actors', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Actors', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				&nbsp;<a rel="nofollow" class="lum_popup_menu_title lum_add_spinner" href="<?php echo esc_url( wp_nonce_url( $this->popup_url . '?mid=' . $movie_class->imdbid() . '&film=' . $film_title . '&info=crew' ) ); ?>" title='<?php echo esc_attr( $movie_class->title() ) . ': ' . esc_html__( 'Crew', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Crew', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				&nbsp;<a rel="nofollow" class="lum_popup_menu_title lum_add_spinner" href="<?php echo esc_url( wp_nonce_url( $this->popup_url . '?mid=' . $movie_class->imdbid() . '&film=' . $film_title . '&info=resume' ) ); ?>" title='<?php echo esc_attr( $movie_class->title() ) . ': ' . esc_html__( 'Plots', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Plots', 'lumiere-movies' ); ?></a>
			</div>
			<div class="lumiere_flex_auto">
				&nbsp;<a rel="nofollow" class="lum_popup_menu_title lum_add_spinner" href="<?php echo esc_url( wp_nonce_url( $this->popup_url . '?mid=' . $movie_class->imdbid() . '&film=' . $film_title . '&info=divers' ) ); ?>" title='<?php echo esc_attr( $movie_class->title() ) . ': ' . esc_html__( 'Misc', 'lumiere-movies' ); ?>'><?php esc_html_e( 'Misc', 'lumiere-movies' ); ?></a>
			</div>
		</div>
		<?php
	}

	/**
	 * Return a the list of Title items using modules
	 *
	 * @param Title $movie_class
	 * @param list<string> $items list of items to convert to modules
	 * @phpstan-param Settings_Popup::FILM_DISPLAY_ITEMS_MISC|Settings_Popup::FILM_DISPLAY_ITEMS_PLOT|Settings_Popup::FILM_DISPLAY_ITEMS_INTRO $items
	 */
	private function get_items( Title $movie_class, array $items ): string {
		$output = '';
		foreach ( $items as $module ) {
			$class_name = Get_Options_Movie::LUM_FILM_MODULE_CLASS . ucfirst( $module );
			if ( class_exists( $class_name ) === true ) {
				$class_module = new $class_name();
				$final_text = $class_module->get_module( $movie_class, $module );
				if ( strlen( $final_text ) > 0 ) {
					$output .= $this->output_popup_class->movie_element_embeded(
						$final_text,
						$module
					);
				}
			}
		}
		return $output;
	}

	/**
	 * Return a the list of Title items using modules
	 * Using two columns layout
	 *
	 * @param Title $movie_class
	 * @param list<string> $items list of items to convert to modules
	 * @phpstan-param Settings_Popup::FILM_DISPLAY_ITEMS_CREW|Settings_Popup::FILM_DISPLAY_ITEMS_CASTING $items
	 */
	private function get_items_two_columns( Title $movie_class, array $items ): string {
		$output = '';
		foreach ( $items as $module ) {
			$class_name = Get_Options_Movie::LUM_FILM_MODULE_CLASS . ucfirst( $module );
			if ( class_exists( $class_name ) === true ) {
				$class_module = new $class_name();
				$final_text = $class_module->get_module_popup_two_columns( $movie_class, $module );
				if ( strlen( $final_text ) > 0 ) {
					$output .= $this->output_popup_class->movie_element_embeded(
						$final_text,
						$module
					);
				}
			}
		}
		return $output;
	}
}
