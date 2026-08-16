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

use Lumiere\Config\Get_Options;
use Lumiere\Config\Settings_Service;
use Lumiere\Plugins\Logger;
use Lumiere\Plugins\Plugins_Start;

/**
 * Class including obsolete way to display items
 *
 * @phpstan-import-type MOVIE_QUERY from Front_Parser
 */
final class Front_Parser_Obsolete {

	/**
	 * Lumière plugins started
	 *
	 * @var array<string, \Lumiere\Plugins\Plugins_Interface>
	 */
	protected readonly array $plugins_classes_active;

	/**
	 * Constructor
	 */
	public function __construct(
		protected Settings_Service $settings,
		protected readonly Plugins_Start $plugins = new Plugins_Start( [ 'imdbphp' ] ),
		protected Logger $logger = new Logger( __CLASS__ ),
	) {
		$this->plugins_classes_active = $this->plugins->get_active_plugins();
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

		/**
		 * Detect the shortcodes [imdblt][/imdblt] and [imdbltid][/imdbltid] to display the movies, old way
		 * @deprecated 3.5 kept for compatibility purpose
		 */
		add_shortcode( 'imdblt', $this->parse_lumiere_tag_transform( ... ) );
		add_shortcode( 'imdbltid', $this->parse_lumiere_tag_transform_id( ... ) );
	}

	/**
	 * Search the movie and output the results
	 *
	 * @since 3.8 Extra logs are shown once only using singleton $this->movie_run_once
	 * @since 4.3.2 added is_amp_validating() method
	 * @since 4.8 obsolete: using render.php in gutenberg block
	 * @info deprecated since 4.8, using render.php in gutenberg block
	 *
	 * @phpstan-param list<MOVIE_QUERY> $imdb_id_or_title
	 */
	private function display_movies( array $imdb_id_or_title ): string {

		/**
		 * If it is an AMP validation test, exit
		 * Create much cache and may lead to a PHP Fatal error
		 */
		$amp_plug = $this->plugins->is_plugin_active( 'amp' ) ? $this->plugins_classes_active['amp'] : null;
		if ( $amp_plug instanceof \Lumiere\Plugins\Auto\Amp && $amp_plug->is_amp_validating() ) {
			$this->logger->debug( '[Front_Parser] This is an AMP validation test, exiting to save server resources' );
			return '';
		}

		/**
		 * Filter to find movies by ID or title.
		 *
		 * @since 4.3.2
		 *
		 * @var list<MOVIE_QUERY> $imdb_id_or_title List of movie IDs or titles.
		 */
		$array_movies_with_imdbid = apply_filters( 'lumiere_find_movie_id', $imdb_id_or_title );

		/**
		 * Filter to display movies box.
		 *
		 * @since 4.4.0
		 *
		 * @var list<MOVIE_QUERY> $array_movies_with_imdbid List of movies with IMDb IDs.
		 */
		return apply_filters( 'lumiere_display_movies_box', $array_movies_with_imdbid );
	}

	/**
	 * Search the persons and output the results
	 *
	 * @since 3.8 Extra logs are shown once only using singleton $this->movie_run_once
	 * @since 4.3.2 added is_amp_validating() method
	 * @since 4.8 obsolete: using render.php in gutenberg block
	 * @info deprecated since 4.8, using render.php in gutenberg block
	 *
	 * @phpstan-param list<MOVIE_QUERY> $imdb_id_or_title
	 */
	private function display_persons( array $imdb_id_or_title ): string {

		/**
		 * If it is an AMP validation test, exit
		 * Create much cache and may lead to a PHP Fatal error
		 */
		$amp_plug = $this->plugins->is_plugin_active( 'amp' ) ? $this->plugins_classes_active['amp'] : null;
		if ( $amp_plug instanceof \Lumiere\Plugins\Auto\Amp && $amp_plug->is_amp_validating() ) {
			$this->logger->debug( '[Front_Parser] This is an AMP validation test, exiting to save server resources' );
			return '';
		}

		/**
		 * Filter to find persons by ID or name.
		 *
		 * @since 4.6.0
		 *
		 * @var list<MOVIE_QUERY> $imdb_id_or_title List of person IDs or names.
		 */
		$array_persons_with_imdbid = apply_filters( 'lumiere_find_person_id', $imdb_id_or_title );

		/**
		 * Filter to display persons box.
		 *
		 * @since 4.6.0
		 *
		 * @var list<MOVIE_QUERY> $array_persons_with_imdbid List of persons with IMDb IDs.
		 */
		return apply_filters( 'lumiere_display_persons_box', $array_persons_with_imdbid );
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
	 * Find in content the span to build the movies
	 * Looks for <span data-lum_movie_maker="[1]"></span> where [1] is movie_title or movie_id
	 *
	 * @since 3.10.2 The function always returns string, no null accepted -- PHP8.2 compatibility
	 * @since 4.2.3 The function will return with the content if not executed in autorized area
	 * @since 4.6.1 Use preg_replace_callback() instead of preg_replace_callback_array(), foreach loop, use {@see Get_Options::get_lum_all_type_search()}
	 * @since 4.8 obsolete: using render.php in gutenberg block
	 * @deprecated since 4.8
	 *
	 * @param null|string $content HTML span tags + text inside
	 * @return string The spans have been replaced with movies/persons boxes
	 */
	public function parse_spans( ?string $content ): string {

		// if no content is available, abort.
		if ( ! isset( $content ) ) {
			return '';
		}

		// if not run on page or post, return the content untouched.
		if ( $this->movies_autorized_areas() === false ) {
			return $content;
		}

		foreach ( Get_Options::get_lum_all_type_search() as $key => $value ) {
			$value_array = explode( '_', $value['value'] );
			$col1 = $value_array[1] ?? ''; // Either movie or person.
			$col2 = isset( $value_array[2] ) && str_contains( $value_array[2], 'id' ) ? 'bymid' : 'byname';
			$callback_name = 'replace_' . $col1 . '_spans';
			$content = preg_replace_callback(
				'~<span data-lum_movie_maker="' . $value['value'] . '">(.+?)<\/span>~',
				function ( $match ) use ( $col2, $callback_name ): string {
					/** @var 'bymid'|'byname' $col2 */
					return $this->{$callback_name}( $match[1], $col2 );
				},
				$content
			) ?? $content;
		}

		return $content;
	}

	/**
	 * Callback for movies, helper method
	 * It applies method {@see Front_Parser::display_movies()} on the text found
	 *
	 * @see Front_Parser::parse_spans() use this method
	 * @deprecated since 4.8, using render.php in gutenberg block
	 *
	 * @param string $text_found Text found inside <span></span>
	 * @param 'byname'|'bymid' $search_type Searching type of the movie
	 */
	private function replace_movie_spans( string $text_found, string $search_type ): string {
		/** @var list<MOVIE_QUERY> $imdb_id_or_title */
		$imdb_id_or_title = [
			[ $search_type => esc_html( $text_found ) ],
		];
		return $this->display_movies( $imdb_id_or_title );
	}

	/**
	 * Callback for persons, helper method
	 * It applies method {@see Front_Parser::display_persons()} on the text found
	 *
	 * @see Front_Parser::parse_spans() use this method
	 * @since 4.8 obsolete: using render.php in gutenberg block
	 * @deprecated since 4.8
	 *
	 * @param string $text_found Text found inside <span></span>
	 * @param 'byname'|'bymid' $search_type Searching type of the person
	 */
	private function replace_person_spans( string $text_found, string $search_type ): string {
		/** @var list<MOVIE_QUERY> $imdb_id_or_title */
		$imdb_id_or_title = [
			[ $search_type => esc_html( $text_found ) ],
		];
		return $this->display_persons( $imdb_id_or_title );
	}

	/**
	 * Replace [imdblt] shortcode by the movie
	 * @info deprecated 3.5, kept for compatibility purposes
	 *
	 * @param string|array<string> $atts array of attributes
	 * @param null|string $content shortcode content or null if not set
	 */
	public function parse_lumiere_tag_transform( string|array $atts, ?string $content ): string {

		// if not run on page or post, return the content untouched.
		if ( $this->movies_autorized_areas() === false ) {
			return $content ?? '';
		}

		_deprecated_function( 'shortcode imdblt', '3.5', '"span" with data-lum_movie_maker="movie_title" to embed your movies' );
		return $this->lumiere_external_call( $content, '' );
	}

	/**
	 * Replace [imdbltid] shortcode by the movie
	 * @info deprecated 3.5, kept for compatibility purposes
	 *
	 * @param string|array<string> $atts
	 * @param null|string $content shortcode content or null if not set
	 */
	public function parse_lumiere_tag_transform_id( $atts, ?string $content ): string {

		// if not run on page or post, return the content untouched.
		if ( $this->movies_autorized_areas() === false ) {
			return $content ?? '';
		}

		_deprecated_function( 'shortcode imdbltid', '3.5', '"span" with data-lum_movie_maker="movie_id" to embed your movies' );
		return $this->lumiere_external_call( '', $content );
	}

	/**
	 * Function external call (ie, inside a post)
	 * Utilized to build from shortcodes
	 * @obsolete since 3.1 not using shortcodes anymore, kept for compatibility purposes -- not marking @deprecated, which return phan error
	 *
	 * @param string|null $moviename
	 * @param string|null $filmid
	 */
	private function lumiere_external_call( ?string $moviename, ?string $filmid ): string {
		/** @var list<MOVIE_QUERY> $imdb_id_or_title */
		$imdb_id_or_title = [];

		//  Call with the parameter - imdb movie name (imdblt)
		if ( isset( $moviename ) && strlen( $moviename ) > 0 ) {
			$imdb_id_or_title[]['byname'] = esc_html( $moviename );
		}

		//  Call with the parameter - imdb movie id (imdbltid)
		if ( isset( $filmid ) && strlen( $filmid ) > 0 ) {
			$imdb_id_or_title[]['bymid'] = esc_html( $filmid );
		}
		/** @psalm-suppress InvalidArgument */
		return $this->display_movies( $imdb_id_or_title );
	}
}
