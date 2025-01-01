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
 * @version 1.0
 * @package lumiere-movies
 */

namespace Lumiere\Link_Makers;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'You can not call directly this page' );
}

/*
 * Class to build Highslide links
 * Is called by the {@see Link_Factory} class, implements and extends abstract {@see Link_Maker} class
 *
 * This class is used when highslide option is ticked
 *
 * Highslide Popup links are created, included in taxonomy
 * @since 3.7
 */
class Highslide_Links extends Abstract_Link_Maker {

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
			$this->config_class->lumiere_css_dir . 'lumiere-highslide.min.css',
			[ 'lumiere_style_main' ],
			strval( filemtime( $this->config_class->lumiere_css_path . 'lumiere-highslide.min.css' ) )
		);

		// Scripts.
		wp_register_script(
			'lumiere_highslide_core',
			$this->config_class->lumiere_js_dir . 'highslide/highslide-with-html.min.js',
			[],
			strval( filemtime( $this->config_class->lumiere_js_path . 'highslide/highslide-with-html.min.js' ) ),
			[ 'strategy' => 'defer' ]
		);
		wp_register_script(
			'lumiere_highslide_options',
			$this->config_class->lumiere_js_dir . 'lumiere-highslide-options.min.js',
			[ 'lumiere_highslide_scripts' ],
			strval( filemtime( $this->config_class->lumiere_js_path . 'lumiere-highslide-options.min.js' ) ),
			[ 'strategy' => 'defer' ]
		);
		wp_register_script(
			'lumiere_highslide_scripts',
			$this->config_class->lumiere_js_dir . 'lumiere-highslide-links.min.js',
			[ 'jquery', 'lumiere_scripts' ],
			strval( filemtime( $this->config_class->lumiere_js_path . 'lumiere-highslide-links.min.js' ) ),
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
	public function lumiere_link_popup_people ( array $imdb_data_people, int $number ): string {

		// Function in abstract class, before last param defines the output, last param specific <A> class.
		return parent::lumiere_link_popup_people_abstract( $imdb_data_people[ $number ]['imdb'], $imdb_data_people[ $number ]['name'], 0, 'lum_link_make_popup lum_link_with_people highslide' );

	}

	/**
	 * @inheritdoc
	 */
	public function lumiere_link_picture ( string|bool $photo_localurl_false, string|bool $photo_localurl_true, string $movie_title ): string {

		// Function in abstract class, 2 before last param defines the output, before last param specific A class, last param specific IMG class.
		return parent::lumiere_link_picture_abstract( $photo_localurl_false, $photo_localurl_true, $movie_title, 0, 'lum_pic_link_highslide', 'imdbelementPICimg' );
	}

	/**
	 * @inheritdoc
	 */
	public function lumiere_medaillon_bio ( array $bio_array, int $limit_text_bio = 0 ): ?string {

		// Function in abstract class.
		return parent::lumiere_medaillon_bio_abstract( $bio_array, 0, $limit_text_bio );

	}

	/**
	 * @inheritdoc
	 */
	public function lumiere_imdburl_to_internalurl( string $text ): string {

		// Function in abstract class.
		return parent::lumiere_imdburl_to_internalurl_abstract( $text );

	}

	/**
	 * @inheritdoc
	 */
	public function lumiere_imdburl_of_taxonomy( string $text ): string {

		// Function in abstract class.
		return parent::lumiere_imdburl_of_taxonomy_abstract( $text, 0, 'highslide' );

	}

	/**
	 * @inheritdoc
	 */
	public function lumiere_imdburl_of_soundtrack( string $text ): string {

		// Function in abstract class.
		return parent::lumiere_imdburl_of_soundtrack_abstract( $text, 0 );

	}

	/**
	 * @inheritdoc
	 */
	public function lumiere_popup_film_link( array $link_parsed, ?string $popuplarg = null, ?string $popuplong = null ): string {

		// Function in abstract class, fourth param for bootstrap.
		return parent::lumiere_popup_film_link_abstract( $link_parsed, $popuplarg, $popuplong );
	}

	/**
	 * @inheritdoc
	 */
	public function lumiere_movies_trailer_details( string $url, string $website_title ): string {

		// Function in abstract class, third param for links.
		return parent::lumiere_movies_trailer_details_abstract( $url, $website_title, 0 );

	}

	/**
	 * @inheritdoc
	 */
	public function lumiere_movies_prodcompany_details( string $name, string $comp_id, string $notes ): string {

		// Function in abstract class, fourth param for links.
		return parent::lumiere_movies_prodcompany_details_abstract( $name, $comp_id, $notes, 0 );

	}

	/**
	 * @inheritdoc
	 *
	 * @param string $url Url to the offical website
	 * @param string $name offical website name
	 */
	public function lumiere_movies_officialsites_details( string $url, string $name ): string {

		// Function in abstract class, third param for links.
		return parent::lumiere_movies_officialsites_details_abstract( $url, $name, 0 );

	}

	/**
	 * @inheritdoc
	 *
	 * @param string $plot Text of the plot
	 */
	public function lumiere_movies_plot_details( string $plot ): string {

		// Function in abstract class.
		return parent::lumiere_movies_plot_details_abstract( $plot );
	}

	/**
	 * @inheritdoc
	 *
	 * @param string $mid IMDb ID of the movie
	 */
	public function lumiere_movies_source_details( string $mid ): string {

		// Function in abstract class, second for normal display, third param to include imdbelementSOURCE-picture.
		return parent::lumiere_movies_source_details_abstract( $mid, 0, 'imdbelementSOURCE-picture' );

	}

	/**
	 * @inheritdoc
	 */
	public function lumiere_movies_rating_picture( int $rating, int $votes, string $votes_average_txt, string $out_of_ten_txt, string $votes_txt ): string {

		// Function in abstract class, last param with 1 to display class="imdbelementRATING-picture".
		return parent::lumiere_movies_rating_picture_abstract( $rating, $votes, $votes_average_txt, $out_of_ten_txt, $votes_txt, 1 );

	}
}
