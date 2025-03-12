<?php declare( strict_types = 1 );
/**
 * Class to build Highslide links
 * Is called by the Link Factory class, implements abstract Link Maker class
 *
 * This class is used when highslide option is ticked
 *
 * Highslide Popup links are created, included in taxonomy
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version 2.0
 * @package lumiere-movies
 */

namespace Lumiere\Frontend\Link_Maker;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Config\Get_Options;

/*
 * Class to build Highslide links
 * Is called by the {@see Link_Factory} class, implements and extends abstract {@see Link_Maker} class
 *
 * This class is used when highslide option is ticked
 *
 * Highslide Popup links are created, included in taxonomy
 * @since 3.7
 */
class Highslide_Links extends Implement_Link_Maker implements Interface_Link_Maker {

	/**
	 * Constructor
	 */
	public function __construct() {

		parent::__construct();

		// Registers javascripts and styles, they need to be registered after the Frontend ones.
		add_action( 'wp_enqueue_scripts', [ $this, 'register_extra_assets' ] ); // if not defered, must be after Frontend class call

		// Execute javascripts and styles, they need to be registered after the Frontend ones.
		add_action( 'wp_enqueue_scripts', [ $this, 'execute_extra_assets' ] ); // if not defered, must be after Frontend class call
	}

	/**
	 * Register scripts and style.
	 */
	public function register_extra_assets(): void {

		// Styles.
		wp_register_style(
			'lumiere_highslide_core_style',
			Get_Options::LUM_CSS_URL . 'lumiere-highslide.min.css',
			[ 'lumiere_style_main' ],
			strval( filemtime( Get_Options::LUM_CSS_PATH . 'lumiere-highslide.min.css' ) )
		);

		// Scripts.
		wp_register_script(
			'lumiere_highslide_core',
			Get_Options::LUM_JS_URL . 'highslide/highslide-with-html.min.js',
			[],
			strval( filemtime( Get_Options::LUM_JS_PATH . 'highslide/highslide-with-html.min.js' ) ),
			[ 'strategy' => 'defer' ]
		);
		wp_register_script(
			'lumiere_highslide_options',
			Get_Options::LUM_JS_URL . 'lumiere-highslide-options.min.js',
			[ 'lumiere_highslide_scripts' ],
			strval( filemtime( Get_Options::LUM_JS_PATH . 'lumiere-highslide-options.min.js' ) ),
			[ 'strategy' => 'defer' ]
		);
		wp_register_script(
			'lumiere_highslide_scripts',
			Get_Options::LUM_JS_URL . 'lumiere-highslide-links.min.js',
			[ 'jquery', 'lumiere_scripts' ],
			strval( filemtime( Get_Options::LUM_JS_PATH . 'lumiere-highslide-links.min.js' ) ),
			[ 'strategy' => 'defer' ]
		);
	}

	/**
	 * Enqueue stylesheet & javascript.
	 */
	public function execute_extra_assets (): void {

		wp_enqueue_style( 'lumiere_highslide_core_style' );

		wp_enqueue_script( 'lumiere_highslide_core' );
		wp_enqueue_script( 'lumiere_highslide_options' );
		wp_enqueue_script( 'lumiere_highslide_scripts' );
	}

	/**
	 * @inheritdoc
	 */
	public function get_rating_picture( int $rating, int $votes, string $votes_average_txt, string $out_of_ten_txt, string $votes_txt ): string {
		// Function in abstract class, last param with 1 to display class="imdbelementRATING-picture".
		return parent::get_rating_picture_details( $rating, $votes, $votes_average_txt, $out_of_ten_txt, $votes_txt, 1 );
	}

	/**
	 * @inheritdoc
	 */
	public function get_picture( string|bool $photo_localurl_false, string|bool $photo_localurl_true, string $movie_title ): string {
		// Function in abstract class, 2 before last param defines the output, before last param specific A class, last param specific IMG class.
		return parent::get_picture_details( $photo_localurl_false, $photo_localurl_true, $movie_title, parent::LINK_OPTIONS['highslide'], 'lum_pic_link_highslide', 'imdbelementPICimg' );
	}

	/**
	 * @inheritdoc
	 */
	public function get_medaillon_bio( array $bio_array, int $limit_text_bio = 0 ): ?string {
		// Function in abstract class.
		return parent::get_medaillon_bio_details( $bio_array, parent::LINK_OPTIONS['highslide'], $limit_text_bio );
	}

	/**
	 * @inheritdoc
	 *
	 * @param string $plot Text of the plot
	 */
	public function get_plot( string $plot ): string {
		// Function in abstract class.
		return parent::get_plot_details( $plot, parent::LINK_OPTIONS['highslide'] );
	}

	/**
	 * @inheritdoc
	 */
	public function get_popup_people( string $imdb_id, string $name ): string {
		// Function in abstract class, before last param defines the output, last param specific <A> class.
		return parent::get_popup_people_details( $imdb_id, $name, parent::LINK_OPTIONS['highslide'], 'lum_link_make_popup lum_link_with_people highslide' );
	}

	/**
	 * @inheritdoc
	 */
	public function get_popup_film_title( string $title, string $a_class = '' ): string {
		// Function in abstract class, second param for highslide.
		return parent::get_popup_film_title_details( $title, parent::LINK_OPTIONS['highslide'], $a_class );
	}

	/**
	 * @inherit
	 */
	public function get_popup_film_id( string $title, string $imdbid, string $a_class = '' ): string {
		// Function in abstract class, third param for highslide.
		return parent::get_popup_film_id_details( $title, $imdbid, parent::LINK_OPTIONS['highslide'], $a_class );
	}

	/**
	 * @inheritdoc
	 */
	public function get_trailer( string $url, string $website_title ): string {
		// Function in abstract class, third param for links.
		return parent::get_trailer_details( $url, $website_title, parent::LINK_OPTIONS['highslide'] );
	}

	/**
	 * @inheritdoc
	 */
	public function get_prodcompany( string $name, string $comp_id, string $notes ): string {
		// Function in abstract class, fourth param for links.
		return parent::get_prodcompany_details( $name, $comp_id, $notes, parent::LINK_OPTIONS['highslide'] );
	}

	/**
	 * @inheritdoc
	 *
	 * @param string $url Url to the offical website
	 * @param string $name offical website name
	 */
	public function get_officialsites( string $url, string $name ): string {
		// Function in abstract class, third param for links.
		return parent::get_officialsites_details( $url, $name, parent::LINK_OPTIONS['highslide'] );
	}

	/**
	 * @inheritdoc
	 *
	 * @param string $mid IMDb ID of the movie
	 */
	public function get_source( string $mid ): string {
		// Function in abstract class, second for normal display, third param to include imdbelementSOURCE-picture.
		return parent::get_source_details( $mid, parent::LINK_OPTIONS['highslide'], 'imdbelementSOURCE-picture' );
	}
}
