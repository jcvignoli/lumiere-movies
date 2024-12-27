<?php declare( strict_types = 1 );
/**
 * Class to build Bootstrap links
 * Is called by the Link Factory class, implements abstract Link Maker class
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version 1.0
 * @since 3.8
 * @package lumiere-movies
 */

namespace Lumiere\Link_Makers;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'You can not call directly this page' );
}

/**
 *
 * This class is used when bootstrap option is selected
 *
 * Bootstrap Popup links are created, including in taxonomy pages
 */
class Bootstrap_Links extends Abstract_Link_Maker {

	/**
	 * Constructor
	 */
	public function __construct() {

		parent::__construct();

		// Registers javascripts and styles, they need to be registered before the Frontend ones.
		add_action( 'wp_enqueue_scripts', [ $this, 'lumiere_bootstrap_register_assets' ], 9 ); // must be priority < 10, 1 less than class frontend.

		// Execute javascripts and styless, they need to be executed before the Frontend ones.
		add_action( 'wp_enqueue_scripts', [ $this, 'lumiere_bootstrap_execute_assets' ], 9 ); // must be priority < 10, 1 less than class frontend.
	}

	/**
	 *  Register frontpage scripts and styles
	 */
	public function lumiere_bootstrap_register_assets(): void {

		// Styles.
		wp_register_style(
			'lumiere_bootstrap_core',
			$this->imdb_admin_values['imdbplugindirectory'] . 'vendor/twbs/bootstrap/dist/css/bootstrap.min.css',
			[ 'lumiere_style_main' ],
			strval( filemtime( LUMIERE_WP_PATH . 'vendor/twbs/bootstrap/dist/css/bootstrap.min.css' ) )
		);
		wp_register_style(
			'lumiere_bootstrap_custom',
			$this->config_class->lumiere_css_dir . 'lumiere-bootstrap-custom.min.css',
			[ 'lumiere_bootstrap_core' ],
			strval( filemtime( $this->config_class->lumiere_css_path . 'lumiere-bootstrap-custom.min.css' ) )
		);

		// Scripts.
		wp_register_script(
			'lumiere_bootstrap_core',
			$this->imdb_admin_values['imdbplugindirectory'] . 'vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js',
			[],
			strval( filemtime( LUMIERE_WP_PATH . 'vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js' ) ),
			[ 'strategy' => 'defer' ]
		);
		wp_register_script(
			'lumiere_bootstrap_scripts',
			$this->config_class->lumiere_js_dir . 'lumiere-bootstrap-links.min.js',
			[ 'lumiere_scripts' ],
			strval( filemtime( $this->config_class->lumiere_js_path . 'lumiere-bootstrap-links.min.js' ) ),
			[ 'strategy' => 'defer' ]
		);
	}

	/**
	 * Enqueue stylesheet & javascript.
	 */
	public function lumiere_bootstrap_execute_assets (): void {

		wp_enqueue_style( 'lumiere_bootstrap_core' );
		wp_enqueue_style( 'lumiere_bootstrap_custom' );

		wp_enqueue_script( 'lumiere_bootstrap_core' );
		wp_enqueue_script( 'lumiere_bootstrap_scripts' );
	}

	/**
	 * @inherit
	 */
	public function lumiere_link_popup_people( array $imdb_data_people, int $number ): string {
		// Function in abstract class, before last param defines the output, last param specific <A> class.
		return parent::lumiere_link_popup_people_abstract( $imdb_data_people[ $number ]['imdb'], $imdb_data_people[ $number ]['name'], 1, 'lum_link_make_popup lum_link_with_people' );
	}

	/**
	 * @inherit
	 */
	public function lumiere_link_picture( string|bool $photo_localurl_false, string|bool $photo_localurl_true, string $movie_title ): string {
		// Function in abstract class, 2 before last param defines the output, before last param specific A class, last param specific IMG class.
		return parent::lumiere_link_picture_abstract( $photo_localurl_false, $photo_localurl_true, $movie_title, 2, '', 'img-thumbnail' );
	}

	/**
	 * @inheritdoc
	 */
	public function lumiere_medaillon_bio( array $bio_array, int $limit_text_bio = 0 ): ?string {
		// Function in abstract class
		return parent::lumiere_medaillon_bio_abstract( $bio_array, 0, $limit_text_bio );
	}

	/**
	 * @inherit
	 */
	public function lumiere_imdburl_to_internalurl( string $text ): string {
		// Function in abstract class.
		return parent::lumiere_imdburl_to_internalurl_abstract( $text );

	}

	/**
	 * @inherit
	 */
	public function lumiere_imdburl_of_taxonomy( string $text ): string {
		// Function in abstract class, second param for bootstrap.
		return parent::lumiere_imdburl_of_taxonomy_abstract( $text, 3 );
	}

	/**
	 * @inherit
	 */
	public function lumiere_imdburl_of_soundtrack( string $text ): string {
		// Function in abstract class, second param for bootstrap.
		return parent::lumiere_imdburl_of_soundtrack_abstract( $text, 3 );
	}

	/**
	 * @inherit
	 */
	public function lumiere_popup_film_link( array $link_parsed, ?string $popuplarg = null, ?string $popuplong = null ): string {
		// Function in abstract class, fourth param for bootstrap.
		return parent::lumiere_popup_film_link_abstract( $link_parsed, $popuplarg, $popuplong, 1 );
	}

	/**
	 * @inherit
	 */
	public function lumiere_movies_trailer_details( string $url, string $website_title ): string {
		// Function in abstract class, third param for links.
		return parent::lumiere_movies_trailer_details_abstract( $url, $website_title, 0 );
	}

	/**
	 * @inherit
	 */
	public function lumiere_movies_prodcompany_details( string $name, string $comp_id, string $notes ): string {
		// Function in abstract class, fourth param for links.
		return parent::lumiere_movies_prodcompany_details_abstract( $name, $comp_id, $notes, 0 );
	}

	/**
	 * @inherit
	 */
	public function lumiere_movies_officialsites_details( string $url, string $name ): string {
		// Function in abstract class, third param for links.
		return parent::lumiere_movies_officialsites_details_abstract( $url, $name, 0 );
	}

	/**
	 * @inherit
	 */
	public function lumiere_movies_plot_details( string $plot ): string {
		// Function in abstract class.
		return parent::lumiere_movies_plot_details_abstract( $plot );
	}

	/**
	 * @inherit
	 *
	 * @param string $mid IMDb ID of the movie
	 */
	public function lumiere_movies_source_details( string $mid ): string {
		// Function in abstract class, second for normal display, third param to include imdbelementSOURCE-picture.
		return parent::lumiere_movies_source_details_abstract( $mid, 0, 'imdbelementSOURCE-picture' );
	}

	/**
	 * @inherit
	 */
	public function lumiere_movies_rating_picture( int $rating, int $votes, string $votes_average_txt, string $out_of_ten_txt, string $votes_txt ): string {
		// Function in abstract class, last param with 1 to display class="imdbelementRATING-picture".
		return parent::lumiere_movies_rating_picture_abstract( $rating, $votes, $votes_average_txt, $out_of_ten_txt, $votes_txt, 1 );
	}
}

