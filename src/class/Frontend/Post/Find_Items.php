<?php declare( strict_types = 1 );
/**
 * Class for displaying movies data.
 *
 * @copyright (c) 2024, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */

namespace Lumiere\Frontend\Post;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Frontend\Post\Front_Parser;

/**
 * Find items
 * Look for movies and persons
 *
 * @since 4.6 new class, method find_movie_imdb_id() was extracted from Front_Parser class, method find_person_imdb_id() was created
 * @phpstan-import-type TITLESEARCH_RETURNSEARCH from \Lumiere\Plugins\Manual\Imdbphp
 */
class Find_Items extends Front_Parser {

	/**
	 * Singleton: Make sure events are runned once in this class
	 *
	 * @var bool $movie_run_once
	 */
	private bool $movie_run_once = false;

	/**
	 * Keep track of the number of movies called
	 * Static public property meant to be called from another class
	 */
	public static int $nb_of_movies = 0;

	/**
	 * Search movies: if title is provied, search its imdbid; use directly imdbid otherwise
	 * Is a hook declared in {@see \Lumiere\Frontend\Frontend::__construct()}
	 *
	 * @since 4.3.2 method created
	 * @since 4.4 An array of movies's name without ['bymid'] can be passed
	 *
	 * @param array<array-key, array<string, string>|string> $films_array Th
	 * @phpstan-param array<array-key|string, array{bymid?: string, byname?: string}|string> $films_array
	 * @return list<string> Array of results of imdbids
	 */
	public function find_movie_imdb_id( array $films_array ): array {

		self::$nb_of_movies = count( $films_array );
		$movies_found = [];

		// Using singleton to display only once.
		if ( $this->movie_run_once === false ) {
			$this->logger->log?->debug( '[Front_Parser] Using the link maker class: ' . str_replace( 'Lumiere\Link_Maker\\', '', get_class( $this->link_maker ) ) );
			$this->logger->log?->debug( '[Front_Parser] The following plugins compatible with Lumière! are in use: [' . join( ', ', array_keys( $this->plugins_classes_active ) ) . ']' );
			$this->movie_run_once = true;
		}

		for ( $i = 0; $i < self::$nb_of_movies; $i++ ) {

			// A movie's ID was passed, which is a numeric-string.
			if ( isset( $films_array[ $i ]['bymid'] ) && ctype_digit( $films_array[ $i ]['bymid'] ) ) {
				$movies_found[] = esc_html( strval( $films_array[ $i ]['bymid'] ) );
				$this->logger->log?->debug( '[Find_Items] Storing IMDb ID: *' . $movies_found[0] . '*' );
				continue;
				// A movie's title was provided
			} elseif ( isset( $films_array[ $i ]['byname'] ) ) {
				$movie_name = strtolower( $films_array[ $i ]['byname'] );
				$this->logger->log?->debug( '[Find_Items] ' . ucfirst( 'The following "' . esc_html( $this->imdb_admin_values['imdbseriemovies'] ) ) . '" title provided: ' . esc_html( $movie_name ) );
				// If ['byname'] is not provided, assume a movie's name was in the array (string in the loop)
			} elseif ( is_string( $films_array[ $i ] ) ) {
				$movie_name = strtolower( $films_array[ $i ] );
				$this->logger->log?->debug( '[Find_Items] ' . ucfirst( 'The following "' . esc_html( $this->imdb_admin_values['imdbseriemovies'] ) ) . '" title provided: ' . esc_html( $movie_name ) );
			} else {
				$this->logger->log?->debug( '[Find_Items] Invalid IMDb ID or title provided, aborting' );
				continue;
			}

			$this->logger->log?->debug( '[Find_Items] Searching ' . esc_html( $movie_name ) );

			/** @phpstan-var TITLESEARCH_RETURNSEARCH|null $results */
			$results = strlen( $movie_name ) > 0 ? $this->plugins_classes_active['imdbphp']->search_movie_title(
				$movie_name,
				$this->logger->log,
			) : null;

			// No results were found in imdbphp query.
			if ( ! isset( $results[0] ) ) {
				$this->logger->log?->info( '[Find_Items] No ' . ucfirst( esc_html( $this->imdb_admin_values['imdbseriemovies'] ) ) . ' found for ' . esc_html( $movie_name ) . ', aborting.' );
				continue;
			}

			// Get the first result from the search
			$movies_found[] = esc_html( $results[0]['imdbid'] );
			$this->logger->log?->debug( '[Find_Items] IMDb ID found: *' . $results[0]['imdbid'] . '*' );

		}
		return $movies_found;
	}

	/**
	 * Search persons: if name is provied, search its id; use directly id otherwise
	 * Is a hook declared in {@see \Lumiere\Frontend\Frontend::__construct()}
	 *
	 * @since 4.6 method created
	 *
	 * @param array<array-key, array<string, string>|string> $persons_array Th
	 * @phpstan-param array<array-key|string, array{bymid?: string, byname?: string}|string> $persons_array
	 * @return list<string> Array of results of imdbids
	 */
	public function find_person_imdb_id( array $persons_array ): array {

		self::$nb_of_movies = count( $persons_array );
		$names_found = [];

		// Using singleton to display only once.
		if ( $this->movie_run_once === false ) {
			$this->logger->log?->debug( '[Find_Items] Using the link maker class: ' . str_replace( 'Lumiere\Link_Maker\\', '', get_class( $this->link_maker ) ) );
			$this->logger->log?->debug( '[Find_Items] The following plugins compatible with Lumière! are in use: [' . join( ', ', array_keys( $this->plugins_classes_active ) ) . ']' );
			$this->movie_run_once = true;
		}

		for ( $i = 0; $i < self::$nb_of_movies; $i++ ) {

			// A movie's ID was passed, which is a numeric-string.
			if ( isset( $persons_array[ $i ]['bymid'] ) && ctype_digit( $persons_array[ $i ]['bymid'] ) ) {
				$names_found[] = esc_html( strval( $persons_array[ $i ]['bymid'] ) );
				$this->logger->log?->debug( '[Find_Items] Storing Name IMDb ID: *' . $names_found[0] . '*' );
				continue;
				// A person's name was provided
			} elseif ( isset( $persons_array[ $i ]['byname'] ) ) {
				$name_nm = strtolower( $persons_array[ $i ]['byname'] );
				$this->logger->log?->debug( '[Find_Items] ' . ucfirst( 'The following "' . esc_html( $this->imdb_admin_values['imdbseriemovies'] ) ) . '" name provided: ' . esc_html( $name_nm ) );
				// If ['byname'] is not provided, assume a person's name was in the array (string in the loop)
			} elseif ( is_string( $persons_array[ $i ] ) ) {
				$name_nm = strtolower( $persons_array[ $i ] );
				$this->logger->log?->debug( '[Find_Items] ' . ucfirst( 'The following "' . esc_html( $this->imdb_admin_values['imdbseriemovies'] ) ) . '" name provided: ' . esc_html( $name_nm ) );
			} else {
				$this->logger->log?->debug( '[Find_Items] Invalid IMDb ID or name provided, aborting' );
				continue;
			}

			$this->logger->log?->debug( '[Find_Items] Searching ' . esc_html( $name_nm ) );

			$results = strlen( $name_nm ) > 0 ? $this->plugins_classes_active['imdbphp']->search_person_name(
				$name_nm,
				$this->logger->log,
			) : null;

			// No results were found in imdbphp query.
			if ( ! isset( $results[0] ) ) {
				$this->logger->log?->info( '[Find_Items] No ' . ucfirst( esc_html( $this->imdb_admin_values['imdbseriemovies'] ) ) . ' found for ' . esc_html( $name_nm ) . ', aborting.' );
				continue;
			}

			// Get the first result from the search
			$names_found[] = esc_html( $results[0]['id'] );
			$this->logger->log?->debug( '[Find_Items] IMDb ID found: *' . $results[0]['id'] . '*' );
		}
		return $names_found;
	}
}
