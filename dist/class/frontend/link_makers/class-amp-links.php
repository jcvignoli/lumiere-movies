<?php declare( strict_types = 1 );
/**
 * Class to build no HTML Links
 * Is called by the Link Factory class, implements abstract Link Maker class
 *
 * This class is used for AMP pages
 *
 * External HTML links are kept but no popup link is created
 * Only links to 1/ taxonomy pages and 2/ internal links
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version 1.0
 * @since 3.7.1
 * @package lumiere-movies
 */

namespace Lumiere\Link_Makers;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'You can not call directly this page' );
}

use \Lumiere\Settings;

class AMP_Links extends Abstract_Link_Maker {

	// Trait including the database settings.
	use \Lumiere\Settings_Global;

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		// Construct Global Settings trait.
		$this->settings_open();

		/**
		 * Remove AMP mobile switcher
		 * Doesn't work!
		add_action(
			'amp_post_template_css',
			function (): string {
				?>
				#amp-mobile-version-switcher{display: none;}
				<?php
			}
		);
		 */

	}

	/**
	 * Build link (a popup in other link classes) internal link for IMDb people
	 *
	 * @param array<int, array<string, string>> $imdb_data_people Array with IMDB people data
	 * @param int $number The number of the loop $i
	 *
	 * @return string
	 */
	public function lumiere_link_popup_people ( array $imdb_data_people, int $number ): string {

		return "\n\t\t\t" . '<a class="linkpopup" href="' . $this->config_class->lumiere_urlpopupsperson . '?mid=' . esc_attr( $imdb_data_people[ $number ]['imdb'] ) . '" title="' . esc_html__( 'internal link to', 'lumiere-movies' ) . '">' . $imdb_data_people[ $number ]['name'] . '</a>';

	}

	/**
	 * Build picture of the movie
	 * @param string|bool $photo_localurl_false The picture of big size
	 * @param string|bool $photo_localurl_true The picture of small size
	 * @param string $movie_title Title of the movie
	 * @return string
	 */
	public function lumiere_link_picture ( string|bool $photo_localurl_false, string|bool $photo_localurl_true, string $movie_title ): string {

		$output = '';

		// Select picture: if 1/ use always thumbnail
		// @since 3.7
		$photo_url = is_string( $photo_localurl_true ) ? $photo_localurl_true : '';

		// Select picture: if 2/ thumbnail picture exists, use it (in 1), use no_pics otherwise
		$photo_url_final = strlen( $photo_url ) === 0 ? esc_url( $this->imdb_admin_values['imdbplugindirectory'] . 'pics/no_pics.gif' ) : $photo_url;

		$output .= "\n\t\t\t" . '<div class="imdbelementPIC">';

		// Build image HTML tag <img>
		$output .= "\n\t\t\t\t\t" . '<img class="imdbelementPICimg" src="';

		$output .= $photo_url_final
			. '" alt="'
			. esc_html__( 'Photo of', 'lumiere-movies' )
			. ' '
			. esc_attr( $movie_title ) . '"';

		// add width only if "Display only thumbnail" is unactive
		// @since 3.7
		if ( $this->imdb_admin_values['imdbcoversize'] === '0' ) {

			$output .= ' width="' . intval( $this->imdb_admin_values['imdbcoversizewidth'] ) . '"';

			// add 100px width if "Display only thumbnail" is active
		} elseif ( $this->imdb_admin_values['imdbcoversize'] === '1' ) {

			$output .= ' width="100em"';

		}

		$output .= ' />';

		$output .= "\n\t\t\t" . '</div>';

		return $output;

	}

	/**
	 * Build picture of the movie in taxonomy pages
	 * @param string|bool $photo_localurl_false The picture of big size
	 * @param string|bool $photo_localurl_true The picture of small size
	 * @param string $person_name Name of the person
	 *
	 * @return string
	 */
	public function lumiere_link_picture_taxonomy ( string|bool $photo_localurl_false, string|bool $photo_localurl_true, string $person_name ): string {

		// Function in abstract class, last param defines the output.
		return $this->lumiere_link_picture_taxonomy_abstract( $photo_localurl_false, $photo_localurl_true, $person_name, 1 );

	}

	/**
	 * Display mini biographical text, not all people have one
	 *
	 * @param array<array<string, string>> $bio_array Array of the object _IMDBPHPCLASS_->bio()
	 *
	 * @return ?string
	 */
	public function lumiere_medaillon_bio ( array $bio_array ): ?string {

		// Function in abstract class, last param cut the links.
		return $this->lumiere_medaillon_bio_abstract( $bio_array, 1 );

	}

	/**
	 * Convert an IMDb url into an internal link for People and Movies
	 * Meant to be used inside popups (not in posts or widgets)
	 *
	 * @param string $text Text that includes IMDb URL to convert into an internal link
	 */
	public function lumiere_imdburl_to_internalurl ( string $text ): string {

		// Function in abstract class.
		return $this->lumiere_imdburl_to_internalurl_abstract( $text );

	}

	/**
	 * Remove an IMDb url from the text
	 * In this class, no popup is allowed so no popup link is built
	 *
	 * @param string $text Text that includes IMDb URL to be removed
	 */
	public function lumiere_imdburl_to_popupurl ( string $text ): string {

		// Function in abstract class, last param for avoiding popups.
		return $this->lumiere_imdburl_to_popupurl_abstract( $text, 1 );

	}

	/**
	 * No Link function for movies links
	 * Builds an internal when movie's are entered, because if not, the whole purpose of the plugins is killed
	 *
	 * @param array<int, string> $link_parsed html tags and text to be modified
	 * @param string $popuplarg Not in use
	 * @param string $popuplong Not in use
	 */
	public function lumiere_popup_film_link ( array $link_parsed, string $popuplarg = null, string $popuplong = null ): string {

		// Function in abstract class, fourth param for AMP.
		return $this->lumiere_popup_film_link_abstract( $link_parsed, $popuplarg, $popuplong, 2 );

	}

	/**
	 * Trailer data details
	 *
	 * @param string $url Url to the trailer
	 * @param string $website_title website name
	 */
	public function lumiere_movies_trailer_details ( string $url, string $website_title ): string {

		return "\n\t\t\t<a href='" . esc_url( $url ) . "' title='" . esc_html__( 'Watch on IMBb website the trailer for ', 'lumiere-movies' ) . esc_html( $website_title ) . "'>" . sanitize_text_field( $website_title ) . '</a>';

	}

	/**
	 * Production company data details
	 *
	 * @param string $name prod company name
	 * @param string $url Url to the prod company
	 * @param string $notes prod company notes
	 * @return string
	 */
	public function lumiere_movies_prodcompany_details ( string $name, string $url = '', string $notes = '' ): string {
		return esc_attr( $name ) . '<br />';
	}

	/**
	 * Official websites data details
	 *
	 * @param string $url Url to the prod company
	 * @param string $name prod company name
	 * @return string
	 */
	public function lumiere_movies_officialsites_details ( string $url, string $name ): string {

		return "\n\t\t\t<a href='" . esc_url( $url ) . "' title='" . esc_attr( $name ) . "'>"
			. esc_html( $name )
			. '</a>';

	}

	/**
	 * Plots data details
	 *
	 * @param string $plot Text of the plot
	 * @return string
	 */
	public function lumiere_movies_plot_details ( string $plot ): string {

		// Function in abstract class.
		return $this->lumiere_movies_plot_details_abstract( $plot );

	}

	/**
	 * Source data details
	 *
	 * @param string $mid IMDb ID of the movie
	 * @return string
	 */
	public function lumiere_movies_source_details ( string $mid ): string {

		// imdbelementSOURCE-picture class breaks AMP
		return "\n\t\t\t" . '<img alt="link to imdb" width="33" height="15" src="' . esc_url( $this->imdb_admin_values['imdbplugindirectory'] . 'pics/imdb-link.png' ) . '" />'
		. '<a class="link-incmovie-sourceimdb" title="'
				. esc_html__( 'Go to IMDb website for this movie', 'lumiere-movies' ) . '" href="'
				. esc_url( 'https://www.imdb.com/title/tt' . $mid ) . '" >'
				. '&nbsp;&nbsp;'
				. esc_html__( "IMDb's page for this movie", 'lumiere-movies' ) . '</a>';

	}

	/**
	 * Image for the ratings
	 *
	 * @param int $rating mandatory Rating number
	 * @param int $votes mandatory Number of votes
	 * @param string $votes_average_txt mandatory Text mentionning "vote average"
	 * @param string $out_of_ten_txt mandatory Text mentionning "out of ten"
	 * @param string $votes_txt mandatory Text mentionning "votes"
	 *
	 * @return string
	 */
	public function lumiere_movies_rating_picture ( int $rating, int $votes, string $votes_average_txt, string $out_of_ten_txt, string $votes_txt ): string {

		// Function in abstract class, last param with 1 to display class="imdbelementRATING-picture".
		// class="imdbelementRATING-picture" breaks AMP, so remove it.
		return $this->lumiere_movies_rating_picture_abstract( $rating, $votes, $votes_average_txt, $out_of_ten_txt, $votes_txt, 0 );

	}
}
