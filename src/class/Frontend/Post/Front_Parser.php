<?php
/**
 * Class for displaying movies. This class automatically catches spans. It displays taxonomy links and add taxonomy according to the selected options
 *
 * @copyright (c) 2022, Lost Highway
 *
 * @version       3.0
 * @package       lumieremovies
 */
declare( strict_types = 1 );

namespace Lumiere\Frontend\Post;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Config\Settings_Service;
use Lumiere\Frontend\Post\Front_Parser_Obsolete;
use Lumiere\Frontend\Post\Movie_Factory;
use Lumiere\Frontend\Post\Person_Factory;
use Lumiere\Frontend\Layout\Output;
use Lumiere\Frontend\Link_Maker\Link_Factory;
use Lumiere\Plugins\Logger;
use Lumiere\Plugins\Plugins_Start;
use Lumiere\Plugins\Manual\Imdbphp;

/**
 * Main class display items
 * Meant to be used as a filter
 *
 * @phpstan-import-type TITLESEARCH_RETURNSEARCH from \Lumiere\Plugins\Manual\Imdbphp
 * @phpstan-type MOVIE_QUERY array{bymid?: string, byname?: string}
 * @since 4.8 Stopped using add_filter( 'the_content') using block/render.php instead
 * @since 4.8.2 moved obsolete methods to Front_Parser_Obsolete
 */
class Front_Parser {

	/**
	 * Class for building links, i.e. Highslide
	 * Built in class Link Factory
	 *
	 * @INFO: if import-type instead of putting in full the info Var, phpstan requires to add this property to all classes that use it!
	 *  The factory class will determine which class to use
	 */
	public readonly \Lumiere\Frontend\Link_Maker\Interface_Linkmaker $link_maker;

	/**
	 * Lumière plugins started
	 *
	 * @var array<string, \Lumiere\Plugins\Plugins_Interface>
	 */
	protected readonly array $plugins_classes_active;

	/**
	 * Imdb plugin is needed by child classes
	 *
	 * @var \Lumiere\Plugins\Manual\Imdbphp
	 */
	protected readonly Imdbphp $imdb_plugin;

	/**
	 * Constructor
	 */
	public function __construct(
		protected Settings_Service $settings,
		protected readonly Plugins_Start $plugins = new Plugins_Start( [ 'imdbphp' ] ),
		protected readonly Output $output_class = new Output(),
		protected Logger $logger = new Logger( __CLASS__ ),
	) {
		$this->link_maker = ( new Link_Factory( $this->settings ) )->select_link_maker();
		$this->plugins_classes_active = $this->plugins->get_active_plugins();

		$imdb_plug = $this->plugins_classes_active['imdbphp'];
		if ( ! $imdb_plug instanceof Imdbphp ) {
			throw new \Exception( 'Plugin imdbphp was not loaded' );
		}
		$this->imdb_plugin = $imdb_plug;
	}

	/**
	 * Register hooks
	 *
	 * @return void Hooks registered
	 * @see \Lumiere\Frontend\Frontend::lumiere_static_start() Call this method
	 * @since 4.74 removed add_filter('parse_spans') since using render.php in gutenberg block, which uses
	 * @since 4.8, removed add_filter( 'the_content', [ $this, 'parse_spans' ] ) using the proper block mechanism in render.php
	 */
	public function register_hooks(): void {

		// Transform spans into links to popups.
		add_filter( 'the_content', $this->link_popup_maker( ... ) );
		add_filter( 'the_excerpt', $this->link_popup_maker( ... ) );

		/**
		 * Detect the shortcodes [imdblt][/imdblt] and [imdbltid][/imdbltid] to display the movies, old way
		 * @obsolete 3.5 kept for compatibility purpose
		 */
		add_action( 'init', fn() => ( new Front_Parser_Obsolete( settings: $this->settings ) )->register_hooks(), 12 );
	}

	/**
	 * Display the movies in the box
	 * It is a hook add_filter() declared in {@see \Lumiere\Frontend\Frontend::register()}
	 *
	 * @since 4.4 method created
	 * @see used in {@see Front_Parser::display_persons()} and render.php in post block
	 *
	 * @param list<string> $movies_searched
	 * @phpstan-param MOVIE_QUERY $movies_searched
	 */
	public function lum_display_movies_box( array $movies_searched ): string {
		$output = '';
		foreach ( $movies_searched as $movie_found ) {
			$this->logger->debug( "[Front_Parser] Displaying rows for *$movie_found*" );
			$output .= $this->output_class->front_main_wrapper(
				$this->settings->get_admin_options(),
				( new Movie_Factory( settings: $this->settings ) )->factory_movie_items_methods( $movie_found )
			);
		}
		return $output;
	}

	/**
	 * Display the persons in the box
	 * It is a hook add_filter() declared in {@see \Lumiere\Frontend\Frontend::register()}
	 *
	 * @since 4.6 method created
	 * @see used in {@see Front_Parser::display_persons()} and render.php in post block
	 *
	 * @param list<string> $persons_searched
	 * @phpstan-param MOVIE_QUERY $persons_searched
	 */
	public function lum_display_persons_box( array $persons_searched ): string {
		$output = '';
		foreach ( $persons_searched as $person_found ) {
			$this->logger->debug( "[Front_Parser] Displaying rows for *$person_found*" );
			$output .= $this->output_class->front_main_wrapper(
				$this->settings->get_admin_options(),
				( new Person_Factory( settings: $this->settings ) )->factory_person_items_methods( $person_found )
			);
		}
		return $output;
	}

	/**
	 * List of autorized areas where the class will run
	 *
	 * @since 4.2.3
	 * @return bool True if page is autorized
	 */
	private function movies_autorized_areas(): bool {
		return is_singular( [ 'post', 'page' ] );
	}

	/**
	 * Replace <span class="lumiere_link_maker"(anything)?></span> with links
	 *
	 * @param null|string $text parsed data
	 * @return null|string Null if text was already null, text otherwhise
	 * @since 4.1 Added the possibility to have some text after the data with [^>]*
	 * @since 4.2.3 The function will return if not executed in autorized area
	 */
	public function link_popup_maker( ?string $text ): ?string {

		if ( ! isset( $text ) ) {
			return null;
		}

		// if not run on page or post, return the content untouched.
		if ( $this->movies_autorized_areas() === false ) {
			return $text;
		}

		return preg_replace_callback_array(
			[
				// replace all occurences of <span class="lumiere_link_maker">(.+?)<\/span> into internal popup
				'~<span[^>]*data-lum_link_maker="popup"[^>]*>(.+)<\/span>~iU' => function ( array $match ): string {
					return $this->get_popup_link( $match );
				},
				// Kept for compatibility purposes:  <!--imdb--> still works -- it's really old, should be @deprecated
				 '~<!--imdb-->(.*?)<!--\/imdb-->~i' => function ( array $match ): string {
					return $this->get_popup_link( $match );
				 },
			],
			$text
		) ?? $text;
	}

	/**
	 * Replace <span data-lum_link_maker="popup"> by a link
	 *
	 * @param array<int, string> $correspondances parsed data
	 * @return string the link replaced
	 *
	 * @since 4.1 Replaced preg_match() by str_replace() and simplified the method
	 */
	private function get_popup_link( array $correspondances ): string {
		$result = isset( $correspondances[0] )
			? str_replace( $correspondances[0], $this->link_maker->get_popup_film_title( $correspondances[1], 'lum_link_with_movie' /* the class that adds the movie ico */ ), $correspondances[0] )
			: '';
		return $result;
	}
}
