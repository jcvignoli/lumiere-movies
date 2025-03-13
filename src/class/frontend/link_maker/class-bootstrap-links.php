<?php declare( strict_types = 1 );
/**
 * Class to build Bootstrap links
 *
 * @copyright (c) 2022, Lost Highway
 *
 * @version       2.0
 * @package       lumieremovies
 */
namespace Lumiere\Frontend\Link_Maker;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Config\Get_Options;

/**
 * This class is used when bootstrap option is selected
 *
 * Bootstrap Popup links are created, including in taxonomy pages
 * @since 3.8
 */
class Bootstrap_Links extends Implement_Methods implements Interface_Linkmaker {

	/**
	 * Constructor
	 */
	public function __construct() {

		parent::__construct();

		// Registers javascripts and styles, they need to be registered before the Frontend ones.
		add_action( 'wp_enqueue_scripts', [ $this, 'register_extra_assets' ], 9 ); // must be priority < 10, 1 less than class frontend.

		// Execute javascripts and styless, they need to be executed before the Frontend ones.
		add_action( 'wp_enqueue_scripts', [ $this, 'execute_extra_assets' ], 9 ); // must be priority < 10, 1 less than class frontend.
	}

	/**
	 *  Register frontpage scripts and styles
	 */
	public function register_extra_assets(): void {

		// Styles.
		wp_register_style(
			'lumiere_bootstrap_core',
			$this->imdb_admin_values['imdbplugindirectory'] . 'vendor/twbs/bootstrap/dist/css/bootstrap.min.css',
			[ 'lumiere_style_main' ],
			strval( filemtime( LUM_WP_PATH . 'vendor/twbs/bootstrap/dist/css/bootstrap.min.css' ) )
		);
		wp_register_style(
			'lumiere_bootstrap_custom',
			Get_Options::LUM_CSS_URL . 'lumiere-bootstrap-custom.min.css',
			[ 'lumiere_bootstrap_core' ],
			strval( filemtime( Get_Options::LUM_CSS_PATH . 'lumiere-bootstrap-custom.min.css' ) )
		);

		// Scripts.
		wp_register_script(
			'lumiere_bootstrap_core',
			$this->imdb_admin_values['imdbplugindirectory'] . 'vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js',
			[],
			strval( filemtime( LUM_WP_PATH . 'vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js' ) ),
			[ 'strategy' => 'defer' ]
		);
		wp_register_script(
			'lumiere_bootstrap_scripts',
			Get_Options::LUM_JS_URL . 'lumiere-bootstrap-links.min.js',
			[ 'lumiere_scripts' ],
			strval( filemtime( Get_Options::LUM_JS_PATH . 'lumiere-bootstrap-links.min.js' ) ),
			[ 'strategy' => 'defer' ]
		);
	}

	/**
	 * Enqueue stylesheet & javascript.
	 */
	public function execute_extra_assets (): void {
		wp_enqueue_style( 'lumiere_bootstrap_core' );
		wp_enqueue_style( 'lumiere_bootstrap_custom' );

		wp_enqueue_script( 'lumiere_bootstrap_core' );
		wp_enqueue_script( 'lumiere_bootstrap_scripts' );
	}

	/**
	 * @inherit
	 */
	public function get_rating_picture( int $rating, int $votes, string $votes_average_txt, string $out_of_ten_txt, string $votes_txt ): string {
		// Function in abstract class, last param with 1 to display class="imdbelementRATING-picture".
		return parent::get_rating_picture_details( $rating, $votes, $votes_average_txt, $out_of_ten_txt, $votes_txt, 1 );
	}

	/**
	 * @inherit
	 */
	public function get_picture( string|bool $photo_localurl_false, string|bool $photo_localurl_true, string $movie_title ): string {
		// Function in abstract class, 2 before last param defines the output, before last param specific A class, last param specific IMG class.
		return parent::get_picture_details( $photo_localurl_false, $photo_localurl_true, $movie_title, parent::LINK_OPTIONS['bootstrap'], '', 'img-thumbnail' );
	}

	/**
	 * @inheritdoc
	 */
	public function get_medaillon_bio( array $bio_array, int $limit_text_bio = 0 ): ?string {
		// Function in abstract class
		return parent::get_medaillon_bio_details( $bio_array, parent::LINK_OPTIONS['bootstrap'], $limit_text_bio );
	}

	/**
	 * @inherit
	 */
	public function get_plot( string $plot ): string {
		// Function in abstract class.
		return parent::get_plot_details( $plot, parent::LINK_OPTIONS['bootstrap'] );
	}

	/**
	 * @inherit
	 */
	public function get_popup_people( string $imdb_id, string $name ): string {
		// Function in abstract class, before last param defines the output, last param specific <A> class.
		return parent::get_popup_people_details( $imdb_id, $name, parent::LINK_OPTIONS['bootstrap'], 'lum_link_make_popup lum_link_with_people' );
	}

	/**
	 * @inherit
	 */
	public function get_popup_film_title( string $title, string $a_class = '' ): string {
		// Function in abstract class, second param for bootstrap.
		return parent::get_popup_film_title_details( $title, parent::LINK_OPTIONS['bootstrap'], $a_class );
	}

	/**
	 * @inherit
	 */
	public function get_popup_film_id( string $title, string $imdbid, string $a_class = '' ): string {
		// Function in abstract class, third param for bootstrap.
		return parent::get_popup_film_id_details( $title, $imdbid, parent::LINK_OPTIONS['bootstrap'], $a_class );
	}

	/**
	 * @inherit
	 */
	public function get_trailer( string $url, string $website_title ): string {
		// Function in abstract class, third param for links.
		return parent::get_trailer_details( $url, $website_title, parent::LINK_OPTIONS['bootstrap'] );
	}

	/**
	 * @inherit
	 */
	public function get_prodcompany( string $name, string $comp_id, string $notes ): string {
		// Function in abstract class, fourth param for links.
		return parent::get_prodcompany_details( $name, $comp_id, $notes, parent::LINK_OPTIONS['bootstrap'] );
	}

	/**
	 * @inherit
	 */
	public function get_officialsites( string $url, string $name ): string {
		// Function in abstract class, third param for links.
		return parent::get_officialsites_details( $url, $name, parent::LINK_OPTIONS['bootstrap'] );
	}

	/**
	 * @inherit
	 */
	public function get_source( string $mid ): string {
		// Function in abstract class, second for normal display, third param to include imdbelementSOURCE-picture.
		return parent::get_source_details( $mid, parent::LINK_OPTIONS['bootstrap'], 'imdbelementSOURCE-picture' );
	}
}

