<?php declare( strict_types = 1 );
/**
 * Cache tools class
 * Child of Admin
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 * @TODO: rewrite and factorize the class
 */

namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	wp_die( esc_html__( 'You can not call directly this page', 'lumiere-movies' ) );
}

// Use IMDbPHP library for cache creation
use Imdb\Title;
use Imdb\Person;
use Lumiere\Tools\Utils;
use Lumiere\Plugins\Imdbphp;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Exception;

/**
 * Functions utilized by Class Cache
 * @see \Lumiere\Admin\Cache
 * @since 3.12 Methods extracted from Class cache and factorized here
 */
class Cache_Tools extends \Lumiere\Admin {

	/**
	 * Class \Lumiere\Imdbphp
	 *
	 */
	private Imdbphp $imdbphp_class;

	/**
	 *  Constructor
	 */
	public function __construct() {

		// Construct parent class
		parent::__construct();

		// Start Imdbphp class.
		$this->imdbphp_class = new Imdbphp();

		// Logger: set to true to display debug on screen.
		$this->logger->lumiere_start_logger( get_class(), false );

	}

	/**
	 * Delete a specific file by clicking on it
	 * @param null|bool|string $type Comes from $_GET['type']
	 * @param null|bool|string $where Comes from $_GET['where']
	 */
	public function cache_delete_specific_file( mixed $type, mixed $where ): void {

		if ( ! is_string( $type ) ) {
			return;
		}

		global $wp_filesystem;
		$id_sanitized = isset( $where ) && is_string( $where ) ? esc_html( $where ) : '';

		// prevent drama.
		if ( ! isset( $this->imdb_cache_values['imdbcachedir'] ) || ! isset( $_GET['where'] ) ) {
			wp_die( esc_html__( 'Cannot work this way.', 'lumiere-movies' ) );
		}

		// delete single movie.
		if ( $type === 'movie' ) {

			$name_sanitized = glob( $this->imdb_cache_values['imdbcachedir'] . '{*tt' . $id_sanitized . '*}', GLOB_BRACE );

			// if file doesn't exist or can't get credentials.
			if ( $name_sanitized === false || count( $name_sanitized ) === 0 ) {
				throw new Exception( esc_html__( 'This file does does not exist.', 'lumiere-movies' ) );
			}

			foreach ( $name_sanitized as $key => $cache_to_delete ) {
				Utils::lumiere_wp_filesystem_cred( $cache_to_delete );
				$wp_filesystem->delete( $cache_to_delete );
			}

			// Delete pictures, small and big.
			$pic_small_sanitized = $this->imdb_cache_values['imdbphotoroot'] . $id_sanitized . '.jpg';
			$pic_big_sanitized = $this->imdb_cache_values['imdbphotoroot'] . $id_sanitized . '_big.jpg';
			if ( file_exists( $pic_small_sanitized ) ) {
				$wp_filesystem->delete( $pic_small_sanitized );
			}
			if ( file_exists( $pic_big_sanitized ) ) {
				$wp_filesystem->delete( $pic_big_sanitized );
			}
		}

		// delete single person.
		if ( $type === 'people' ) {

			$name_sanitized = glob( $this->imdb_cache_values['imdbcachedir'] . '{*nm' . $id_sanitized . '*}', GLOB_BRACE );

			// if file doesn't exist or can't get credentials.
			if ( $name_sanitized === false || count( $name_sanitized ) === 0 ) {
				throw new Exception( esc_html__( 'This file does does not exist.', 'lumiere-movies' ) );
			}

			foreach ( $name_sanitized as $key => $cache_to_delete ) {
				Utils::lumiere_wp_filesystem_cred( $cache_to_delete );
				$wp_filesystem->delete( $cache_to_delete );
			}

			// Delete pictures, small and big.
			$pic_small_sanitized = $this->imdb_cache_values['imdbphotoroot'] . 'nm' . $id_sanitized . '.jpg';
			$pic_big_sanitized = $this->imdb_cache_values['imdbphotoroot'] . 'nm' . $id_sanitized . '_big.jpg';
			if ( file_exists( $pic_small_sanitized ) ) {
				$wp_filesystem->delete( $pic_small_sanitized );
			}
			if ( file_exists( $pic_big_sanitized ) ) {
				$wp_filesystem->delete( $pic_big_sanitized );
			}
		}
	}

	/**
	 * Refresh all cache that is already cached
	 * 1/ Retrieve the movies and people's IMDb IDs already cached
	 * 2/ Delete all cache
	 * 3/ Recreate the cache folder (needed for images)
	 * 4/ Recreate the cache by querying the IMDb with an incremental sleep (to avoid HTTP errors)
	 * Meant to be called by cron
	 * @see \Lumiere\Admin\Cron::lumiere_cron_exec_autorefresh
	 * @since 3.12
	 *
	 * @param int<0, max> $sleep Optional, the time to sleep before each query to IMDb (this is incremental, each new file adds 0.25 seconds by default)
	 * @return void All cache has been refreshed
	 */
	public function lumiere_all_cache_refresh( int $sleep = 250000 ): void {

		// Get movies ids.
		$movies_ids = [];
		foreach ( $this->lumiere_get_movie_cache() as $movie_title_object ) {
			$movies_ids[] = $movie_title_object->imdbid();
		}
		// Get people ids.
		$people_ids = [];
		foreach ( $this->lumiere_get_people_cache() as $people_person_object ) {
			$people_ids[] = $people_person_object->imdbid();
		}

		// Delete all cache, otherwise neither gql files nor pictures won't be deleted.
		Utils::lumiere_unlink_recursive( $this->imdb_cache_values['imdbcachedir'] );

		// Make sure cache folder exists and is writable.
		$this->config_class->lumiere_create_cache( true );

		// Get back the cache by querying the IMDb.
		$i = 1;
		foreach ( $movies_ids as $movie_id ) {
			usleep( $i * $sleep ); // Add an incremental sleep, to minimize the number of queries made to IMDb
			$this->lumiere_create_movie_file( $movie_id );
			$i++;
		}
		foreach ( $people_ids as $person_id ) {
			usleep( $i * $sleep ); // Add an incremental sleep, to minimize the number of queries made to IMDb
			$this->lumiere_create_people_cache( $person_id );
			$i++;
		}
	}

	/**
	 * Refresh a specific file by clicking on it
	 * @param null|bool|string $type Comes from $_GET['type']
	 * @param null|bool|string $where Comes from $_GET['where']
	 */
	public function cache_refresh_specific_file( mixed $type, mixed $where ): void {

		if ( ! is_string( $type ) ) {
			return;
		}

		global $wp_filesystem;
		$id_sanitized = isset( $where ) && is_string( $where ) ? esc_html( $where ) : '';

		// prevent drama.
		if ( ( ! isset( $this->imdb_cache_values['imdbcachedir'] ) ) || ( ! isset( $_GET['where'] ) )  ) {
			exit( esc_html__( 'Cannot work this way.', 'lumiere-movies' ) );
		}

		// delete single movie.
		if ( $type === 'movie' ) {

			$name_sanitized = glob( $this->imdb_cache_values['imdbcachedir'] . '{*tt' . $id_sanitized . '*}', GLOB_BRACE );

			// if file doesn't exist.
			if ( $name_sanitized === false || count( $name_sanitized ) === 0 ) {
				throw new Exception( esc_html__( 'This file does not exist.', 'lumiere-movies' ) );
			}

			foreach ( $name_sanitized as $key => $cache_to_delete ) {
				Utils::lumiere_wp_filesystem_cred( $cache_to_delete );
				$wp_filesystem->delete( esc_url( $cache_to_delete ) );
			}

			// delete pictures, small and big.
			$pic_small_sanitized = $this->imdb_cache_values['imdbphotoroot'] . $id_sanitized . '.jpg';
			$pic_big_sanitized = $this->imdb_cache_values['imdbphotoroot'] . $id_sanitized . '_big.jpg';
			if ( file_exists( $pic_small_sanitized ) ) {
				$wp_filesystem->delete( $pic_small_sanitized );
			}
			if ( file_exists( $pic_big_sanitized ) ) {
				$wp_filesystem->delete( $pic_big_sanitized );
			}

			// Get again the movie.
			$this->lumiere_create_movie_file( $id_sanitized );
		}

		if ( $type === 'people' ) {

			$name_people_sanitized = glob( $this->imdb_cache_values['imdbcachedir'] . '{*nm' . $id_sanitized . '*}', GLOB_BRACE );

			// if file doesn't exist
			if ( $name_people_sanitized === false || count( $name_people_sanitized ) < 1 ) {
				throw new Exception( esc_html__( 'This file does not exist.', 'lumiere-movies' ) );
			}

			// delete pictures, small and big.
			$pic_small_sanitized = $this->imdb_cache_values['imdbphotoroot'] . 'nm' . $id_sanitized . '.jpg';
			$pic_big_sanitized = $this->imdb_cache_values['imdbphotoroot'] . 'nm' . $id_sanitized . '_big.jpg';
			if ( file_exists( $pic_small_sanitized ) ) {
				$wp_filesystem->delete( $pic_small_sanitized );
			}
			if ( file_exists( $pic_big_sanitized ) ) {
				$wp_filesystem->delete( $pic_big_sanitized );
			}

			foreach ( $name_people_sanitized as $key => $cache_to_delete ) {
				Utils::lumiere_wp_filesystem_cred( $cache_to_delete );
				$wp_filesystem->delete( esc_url( $cache_to_delete ) );
			}

			// Get again the person.
			$this->lumiere_create_people_cache( $id_sanitized );

		}
	}

	/**
	 * Create Movie files
	 * @param string $id The movie's ID
	 */
	public function lumiere_create_movie_file( $id ): void {

		$movie = new Title( $id, $this->imdbphp_class, $this->logger->log() );

		// create cache for everything.
		$movie->alsoknow();
		$movie->cast();
		$movie->colors();
		$movie->composer();
		$movie->comment_split();
		$movie->country();
		$movie->creator();
		$movie->director();
		$movie->genres();
		$movie->goofs();
		$movie->keywords();
		$movie->languages();
		$movie->officialSites();
		$movie->photo_localurl( true );
		$movie->photo_localurl( false );
		$movie->plot();
		$movie->prodCompany();
		$movie->producer();
		$movie->quotes();
		$movie->rating();
		$movie->runtime();
		$movie->soundtrack();
		$movie->taglines();
		$movie->title();
		$movie->trailers( true );
		$movie->votes();
		$movie->writing();
		$movie->year();
	}

	/**
	 * Create People files
	 * @param string $id The People's ID
	 */
	public function lumiere_create_people_cache( $id ): void {

		// Get again the person.
		$person = new Person( $id, $this->imdbphp_class, $this->logger->log() );

		// Create cache for everything.
		$person->bio();
		$person->birthname();
		$person->born();
		$person->died();
		$person->movies_all();
		$person->movies_archive();
		$person->movies_soundtrack();
		$person->movies_writer();
		$person->name();
		$person->photo_localurl();
		$person->pubmovies();
		$person->pubportraits();
		$person->quotes();
		$person->trivia();
		$person->trademark();
	}

	/**
	 * Delete query cache files
	 */
	public function cache_delete_query_cache_files(): void {

		global $wp_filesystem;

		// prevent drama.
		if ( ! isset( $this->imdb_cache_values['imdbcachedir'] ) ) {
			wp_die( Utils::lumiere_notice( 3, '<strong>' . esc_html__( 'No cache folder found.', 'lumiere-movies' ) . '</strong>' ) );
		}

		// Delete cache.
		$files_query = glob( $this->imdb_cache_values['imdbcachedir'] . 'find.s*' );

		// if file doesn't exist.
		if ( $files_query === false || count( $files_query ) < 1 ) {
			throw new Exception( esc_html__( 'No query files found.', 'lumiere-movies' ) );
		}

		Utils::lumiere_wp_filesystem_cred( $files_query[0] );

		foreach ( $files_query as $cache_to_delete ) {

			if ( $cache_to_delete === $this->imdb_cache_values['imdbcachedir'] . '.' || $cache_to_delete === $this->imdb_cache_values['imdbcachedir'] . '..' ) {
				continue;
			}

			// the file exists, it is neither . nor .., so delete!
			$wp_filesystem->delete( $cache_to_delete );
		}
	}

	/**
	 * Delete several ticked files
	 *
	 * @param array<string> $list_ids_to_delete The list of ids of movies/people to delete
	 * @param 'movie'|'people' $type_to_delete The kind of data passed
	 */
	public function cache_delete_ticked_files( array $list_ids_to_delete, string $type_to_delete ): void {

		global $wp_filesystem;

		// Prevent drama.
		if ( ! isset( $this->imdb_cache_values['imdbcachedir'] ) ) {
			wp_die( Utils::lumiere_notice( 3, '<strong>' . esc_html__( 'No cache folder found.', 'lumiere-movies' ) . '</strong>' ) );
		}

		// Any of the WordPress data sanitization functions can be used here
		$ids_sanitized = array_map( 'sanitize_key', $list_ids_to_delete );

		$cache_to_delete_files = false;
		$pic_small_sanitized = '';
		$pic_big_sanitized = '';

		foreach ( $ids_sanitized as $id_found ) {

			// For movies.
			if ( $type_to_delete === 'movie' ) {

				$cache_to_delete_files = glob( $this->imdb_cache_values['imdbcachedir'] . '{*tt' . $id_found . '*}', GLOB_BRACE );
				$pic_small_sanitized = $this->imdb_cache_values['imdbphotoroot'] . $id_found . '.jpg';
				$pic_big_sanitized = $this->imdb_cache_values['imdbphotoroot'] . $id_found . '_big.jpg';

				// For people.
			} elseif ( $type_to_delete === 'people' ) {

				$cache_to_delete_files = glob( $this->imdb_cache_values['imdbcachedir'] . '{*nm' . $id_found . '*}', GLOB_BRACE );
				$pic_small_sanitized = $this->imdb_cache_values['imdbphotoroot'] . 'nm' . $id_found . '.jpg';
				$pic_big_sanitized = $this->imdb_cache_values['imdbphotoroot'] . 'nm' . $id_found . '_big.jpg';
			}

			// If file doesn't exist.
			if ( $cache_to_delete_files === false || count( $cache_to_delete_files ) === 0 ) {
				throw new Exception( esc_html__( 'No files found for deletion.', 'lumiere-movies' ) );
			}

			// Get the permissions for deletion and delete.
			foreach ( $cache_to_delete_files as $key => $cache_to_delete ) {
				Utils::lumiere_wp_filesystem_cred( $cache_to_delete );
				$wp_filesystem->delete( $cache_to_delete );
			}

			// Delete pictures, small and big.
			if ( file_exists( $pic_small_sanitized ) ) {
				$wp_filesystem->delete( $pic_small_sanitized );
			}
			if ( file_exists( $pic_big_sanitized ) ) {
				$wp_filesystem->delete( $pic_big_sanitized );
			}
		}
	}

	/**
	 * Retrieve all files in cache folder
	 *
	 * @return array<int, mixed>|array<string> Sorted by size list of all files found in Lumière cache folder
	 */
	public function lumiere_get_cache_list_bysize(): array {
		$folder_iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $this->imdb_cache_values['imdbcachedir'], RecursiveDirectoryIterator::SKIP_DOTS )
		);
		$file_line = [];
		foreach ( $folder_iterator as $file ) {
			if ( $file->isDir() === true ) {
				continue;
			}
			$file_date_modif = $file->getMTime();
			$file_name = $file->getPathname();
			$file_size = $file->getSize();
			$file_line[] = [ $file_date_modif, $file_size, $file_name ];
		}
		sort( $file_line );
		return count( $file_line ) > 0 ? $file_line : [];
	}

	/**
	 * Get size of all files in given folder (cache lumiere by default )
	 *
	 * @param null|string $folder Folder path, internally changed into cachedir if null
	 * @return int Total size of all files found in given folder
	 */
	public function lumiere_cache_getfoldersize( ?string $folder = null ): int {
		$final_folder = $folder ?? $this->imdb_cache_values['imdbcachedir'];
		// After deleting all cache, the display of the cache folder can throw a fatal error if dir is null
		if ( ! is_dir( $final_folder ) ) {
			return 0;
		}
		$folder_iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $final_folder, RecursiveDirectoryIterator::SKIP_DOTS )
		);
		$final_size = 0;
		foreach ( $folder_iterator as $file ) {
			if ( $file->isDir() === true ) {
				continue;
			}
			$final_size += $file->getSize();
		}
		return $final_size;
	}

	/**
	 * Count the number of files in given folder (cache lumiere by default )
	 *
	 * @param null|string $folder Folder path, internally changed into cachedir if null
	 * @return int Number of files found in given folder
	 */
	public function lumiere_cache_countfolderfiles( ?string $folder = null ): int {
		$final_folder = $folder ?? $this->imdb_cache_values['imdbcachedir'];
		$folder_iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $final_folder, RecursiveDirectoryIterator::SKIP_DOTS )
		);
		return iterator_count( $folder_iterator );
	}

	/**
	 * Retrieve files that are over a given limit
	 * The size is provided in megabits and then internally processed as bits
	 *
	 * @param int $size_limit Limit in megabits ( '100' = 100 MB )
	 * @return null|array<int, string> Array of files paths that exceeds the passed size_limit
	 */
	private function lumiere_cache_find_files_over_limit( int $size_limit ): ?array {

		$size_limit_in_bits = $size_limit * 1000000; // convert in bits
		$current_size = 0;
		$list_files_over_size_limit = [];
		foreach ( $this->lumiere_get_cache_list_bysize() as $array ) {
			$current_size += $array[1];
			if ( $current_size >= $size_limit_in_bits ) {
				$list_files_over_size_limit[] = $array[2];
			}
		}
		return count( $list_files_over_size_limit ) > 0 ? $list_files_over_size_limit : null;
	}

	/**
	 * Delete files that are over a given limit
	 * Visibility 'public' because called in cron task in Core class
	 *
	 * @param int $size_limit Limit in megabits
	 * @return void Files exceeding provided limited are deleted
	 */
	public function lumiere_cache_delete_files_over_limit( int $size_limit ): void {
		$this->logger->log()->info( '[Lumiere] Daily Cache cron called with the following value: ' . $size_limit );
		$files = $this->lumiere_cache_find_files_over_limit( $size_limit ) ?? [];
		foreach ( $files as $file ) {
			if ( is_file( $file ) ) {
				unlink( $file );
			}
		}
		$this->logger->log()->info( '[Lumiere] Daily Cache cron deleted the following files: ' . join( $files ) );
	}

	/**
	 * Return the cache for movies
	 *
	 * @return array<int, \Imdb\Title>
	 */
	public function lumiere_get_movie_cache(): array {

		// Find related files
		$cache_files = glob( $this->imdb_cache_values['imdbcachedir'] . '{title.tt*}', GLOB_BRACE );

		if ( $cache_files === false || count( $cache_files ) === 0 ) {
			return [];
		}

		$results = [];
		foreach ( $cache_files as $file ) {
			if ( preg_match( '!^title\.tt(\d{7,8})$!i', basename( $file ), $match ) === 1 ) {
				$results[] = new Title( $match[1], $this->imdbphp_class, $this->logger->log() );
			}
		}
		return $results;
	}

	/**
	 * Return the cache for people
	 *
	 * @return array<int, \Imdb\Person>
	 */
	public function lumiere_get_people_cache(): array {

		$cache_files = glob( $this->imdb_cache_values['imdbcachedir'] . '{name.nm*}', GLOB_BRACE );

		if ( $cache_files === false || count( $cache_files ) === 0 ) {
			return [];
		}

		$results = [];
		foreach ( $cache_files as $file ) {
			if ( preg_match( '!^name\.nm(\d{7,8})$!i', basename( $file ), $match ) === 1 ) {
				$results[] = new Person( $match[1], $this->imdbphp_class, $this->logger->log() );
			}
		}
		return $results;
	}
}

