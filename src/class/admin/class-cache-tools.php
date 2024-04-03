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

use Imdb\Title;
use Imdb\Person;
use Lumiere\Settings;
use Lumiere\Tools\Utils;
use Lumiere\Plugins\Imdbphp;
use Lumiere\Plugins\Logger;
use Lumiere\Admin\Admin_General;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Exception;

/**
 * Methods utilized by Class Cache to delete and build cache
 * Cron class calls { @see Cache_Tools::lumiere_all_cache_refresh() }
 *
 * @see \Lumiere\Admin\Cache
 * @see \Lumiere\Admin\Cron
 * @since 4.0 Methods extracted from Class cache and factorized here
 *
 * @phpstan-import-type OPTIONS_CACHE from \Lumiere\Settings
 */
class Cache_Tools {

	/**
	 * Traits.
	 */
	use Admin_General;

	/**
	 * Cache options
	 * @phpstan-var OPTIONS_CACHE $imdb_cache_values
	 */
	private array $imdb_cache_values;

	/**
	 * Classes
	 */
	private Imdbphp $imdbphp_class;
	private Logger $logger;

	/**
	 *  Constructor
	 */
	public function __construct() {

		// Start Logger class.
		$this->logger = new Logger( 'adminClass' );

		// Get options from database.
		$this->imdb_cache_values = get_option( Settings::LUMIERE_CACHE_OPTIONS );

		// Start Imdbphp class.
		$this->imdbphp_class = new Imdbphp();
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

			$name_sanitized = glob( $this->imdb_cache_values['imdbcachedir'] . '*tt' . $id_sanitized . '*' );

			// if file doesn't exist or can't get credentials.
			if ( $name_sanitized === false || count( $name_sanitized ) === 0 ) {
				throw new Exception( esc_html__( 'This file does does not exist.', 'lumiere-movies' ) );
			}

			foreach ( $name_sanitized as $key => $cache_to_delete ) {
				$this->lumiere_wp_filesystem_cred( $cache_to_delete ); // in trait Admin_General that includes trait Files.
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

			$name_sanitized = glob( $this->imdb_cache_values['imdbcachedir'] . '*nm' . $id_sanitized . '*' );

			// if file doesn't exist or can't get credentials.
			if ( $name_sanitized === false || count( $name_sanitized ) === 0 ) {
				throw new Exception( esc_html__( 'This file does does not exist.', 'lumiere-movies' ) );
			}

			foreach ( $name_sanitized as $key => $cache_to_delete ) {
				$this->lumiere_wp_filesystem_cred( $cache_to_delete ); // in trait Admin_General that includes trait Files.
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
	 *
	 * Meant to be called by cron
	 * @see \Lumiere\Admin\Cron::lumiere_cron_exec_autorefresh()
	 * @since 4.0 new method
	 *
	 * @param int<0, max> $sleep Optional, the time to sleep before each query to IMDb (this is incremental, each new file adds 0.25 seconds by default)
	 * @return void All cache has been refreshed
	 */
	public function lumiere_all_cache_refresh( int $sleep = 250000 ): void {

		$refresh_ids = [];

		// Get movies ids.
		foreach ( $this->lumiere_get_movie_cache() as $movie_title_object ) {
			$refresh_ids['movies'][] = $movie_title_object->imdbid();
		}
		// Get people ids.
		foreach ( $this->lumiere_get_people_cache() as $people_person_object ) {
			$refresh_ids['people'][] = $people_person_object->imdbid();
		}

		// Delete all cache, otherwise neither gql files nor pictures won't be deleted, in Admin_General Files.
		$this->lumiere_unlink_recursive( $this->imdb_cache_values['imdbcachedir'] );

		// Make sure cache folder exists and is writable.
		$this->lumiere_create_cache( true );

		// Get back the movie's cache by querying the IMDb.
		if ( isset( $refresh_ids['movies'] ) ) {
			$i = 1;
			foreach ( $refresh_ids['movies'] as $movie_id ) {
				usleep( $i * $sleep ); // Add an incremental sleep, to minimize the number of queries made to IMDb
				$this->lumiere_create_movie_file( $movie_id );
				$i++;
			}
		}

		// @since 4.0.1 added a sleep
		sleep( 10 );

		// Get back the people's cache by querying the IMDb.
		if ( isset( $refresh_ids['people'] ) ) {
			$i = 1;
			foreach ( $refresh_ids['people'] as $person_id ) {
				usleep( $i * $sleep ); // Add an incremental sleep, to minimize the number of queries made to IMDb
				$this->lumiere_create_people_cache( $person_id );
				$i++;
			}
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
			wp_die( esc_html__( 'Cannot work this way.', 'lumiere-movies' ) );
		}

		// delete single movie.
		if ( $type === 'movie' ) {

			$name_sanitized = glob( $this->imdb_cache_values['imdbcachedir'] . '*tt' . $id_sanitized . '*' );

			// if file doesn't exist.
			if ( $name_sanitized === false || count( $name_sanitized ) === 0 ) {
				throw new Exception( esc_html__( 'This file does not exist.', 'lumiere-movies' ) );
			}

			foreach ( $name_sanitized as $key => $cache_to_delete ) {
				$this->lumiere_wp_filesystem_cred( $cache_to_delete ); // in trait Admin_General that includes trait Files.
				$wp_filesystem->delete( sanitize_text_field( $cache_to_delete ) );
			}

			// delete pictures, small and big.
			$pic_small_sanitized = $this->imdb_cache_values['imdbphotoroot'] . $id_sanitized . '.jpg';
			$pic_big_sanitized = $this->imdb_cache_values['imdbphotoroot'] . $id_sanitized . '_big.jpg';
			if ( file_exists( $pic_small_sanitized ) ) {
				$wp_filesystem->delete( sanitize_text_field( $pic_small_sanitized ) );
			}
			if ( file_exists( $pic_big_sanitized ) ) {
				$wp_filesystem->delete( sanitize_text_field( $pic_big_sanitized ) );
			}

			// Get again the movie.
			$this->lumiere_create_movie_file( $id_sanitized );
		}

		if ( $type === 'people' ) {

			$name_people_sanitized = glob( $this->imdb_cache_values['imdbcachedir'] . '*nm' . $id_sanitized . '*' );

			// if file doesn't exist
			if ( $name_people_sanitized === false || count( $name_people_sanitized ) < 1 ) {
				throw new Exception( esc_html__( 'This file does not exist.', 'lumiere-movies' ) );
			}

			foreach ( $name_people_sanitized as $key => $cache_to_delete ) {
				$this->lumiere_wp_filesystem_cred( $cache_to_delete ); // in trait Admin_General that includes trait Files.
				$wp_filesystem->delete( sanitize_text_field( $cache_to_delete ) );
			}

			// delete pictures, small and big.
			$pic_small_sanitized = $this->imdb_cache_values['imdbphotoroot'] . 'nm' . $id_sanitized . '.jpg';
			$pic_big_sanitized = $this->imdb_cache_values['imdbphotoroot'] . 'nm' . $id_sanitized . '_big.jpg';
			if ( file_exists( $pic_small_sanitized ) ) {
				$wp_filesystem->delete( sanitize_text_field( $pic_small_sanitized ) );
			}
			if ( file_exists( $pic_big_sanitized ) ) {
				$wp_filesystem->delete( sanitize_text_field( $pic_big_sanitized ) );
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

		$movie = new Title( $id, $this->imdbphp_class, /* $this->logger->log() no need for a logger */ );

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
		$person = new Person( $id, $this->imdbphp_class, /** $this->logger->log() no need for a logger */ );

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
		$person->photo_localurl( true );
		$person->photo_localurl( false );
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
			wp_die( '<strong>' . esc_html__( 'No cache folder found.', 'lumiere-movies' ) . '</strong>' );
		}

		// Delete cache.
		$files_query = glob( $this->imdb_cache_values['imdbcachedir'] . 'find.s*' );

		// if file doesn't exist.
		if ( $files_query === false || count( $files_query ) < 1 ) {
			throw new Exception( esc_html__( 'No query files found.', 'lumiere-movies' ) );
		}

		$this->lumiere_wp_filesystem_cred( $files_query[0] ); // in trait Admin_General that includes trait Files.

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
	 * @param 'movie'|'people'|string $type_to_delete The kind of data passed
	 */
	public function cache_delete_ticked_files( array $list_ids_to_delete, string $type_to_delete ): void {

		global $wp_filesystem;

		// Prevent drama.
		if ( ! isset( $this->imdb_cache_values['imdbcachedir'] ) ) {
			wp_die( '<strong>' . esc_html__( 'No cache folder found.', 'lumiere-movies' ) . '</strong>' );
		}

		// Any of the WordPress data sanitization functions can be used here
		$ids_sanitized = array_map( 'sanitize_key', $list_ids_to_delete );

		$cache_to_delete_files = false;
		$pic_small_sanitized = '';
		$pic_big_sanitized = '';

		foreach ( $ids_sanitized as $id_found ) {

			// For movies.
			if ( $type_to_delete === 'movie' ) {

				$cache_to_delete_files = glob( $this->imdb_cache_values['imdbcachedir'] . '*tt' . $id_found . '*' );
				$pic_small_sanitized = $this->imdb_cache_values['imdbphotoroot'] . $id_found . '.jpg';
				$pic_big_sanitized = $this->imdb_cache_values['imdbphotoroot'] . $id_found . '_big.jpg';

				// For people.
			} elseif ( $type_to_delete === 'people' ) {

				$cache_to_delete_files = glob( $this->imdb_cache_values['imdbcachedir'] . '*nm' . $id_found . '*' );
				$pic_small_sanitized = $this->imdb_cache_values['imdbphotoroot'] . 'nm' . $id_found . '.jpg';
				$pic_big_sanitized = $this->imdb_cache_values['imdbphotoroot'] . 'nm' . $id_found . '_big.jpg';
			}

			// If file doesn't exist.
			if ( $cache_to_delete_files === false || count( $cache_to_delete_files ) === 0 ) {
				throw new Exception( esc_html__( 'No files found for deletion.', 'lumiere-movies' ) );
			}

			// Get the permissions for deletion and delete.
			foreach ( $cache_to_delete_files as $key => $cache_to_delete ) {
				$this->lumiere_wp_filesystem_cred( $cache_to_delete ); // in trait Admin_General that includes trait Files.
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
	 * Get size and number of files of the queries
	 *
	 * @return array<int, string> Number of query files, cache query filesize
	 */
	public function lumiere_get_cache_query_info( string $folder ): array {

		$cache_query_folder = Utils::lumiere_glob_recursive( $folder . 'find.s*' );

		$cache_query_count = (string) count( $cache_query_folder );

		$size_cache_query = 0;

		foreach ( $cache_query_folder as $cache_query_file ) {
			$file_size = filesize( $cache_query_file );
			if ( $file_size !== false ) {
				$size_cache_query += $file_size;
			}
		}

		return [ $cache_query_count, (string) $size_cache_query ];
	}

	/**
	 * Get size of all files in given folder (cache lumiere by default )
	 *
	 * @param null|string $folder Folder path, internally changed into cachedir if null
	 * @return int Total size of all files found in given folder
	 */
	public function lumiere_cache_getfoldersize( ?string $folder = null ): int {

		$final_folder = $folder ?? $this->imdb_cache_values['imdbcachedir'];

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
		$this->logger->log()->info( '[Lumiere] Oversized Cache cron called with the following value: ' . $size_limit . ' MB' );
		$files = $this->lumiere_cache_find_files_over_limit( $size_limit ) ?? [];
		foreach ( $files as $file ) {
			if ( is_file( $file ) ) {
				wp_delete_file( $file );
			}
		}
		if ( count( $files ) > 0 ) {
			$this->logger->log()->info( '[Lumiere] Oversized Cache cron deleted the following files: ' . join( $files ) );
			return;
		}
		$this->logger->log()->info( '[Lumiere] Oversized Cache cron did not find any file to delete' );
	}

	/**
	 * Return the cache for movies
	 *
	 * @return array<int, \Imdb\Title>
	 */
	public function lumiere_get_movie_cache(): array {

		// Find related files
		$cache_files = glob( $this->imdb_cache_values['imdbcachedir'] . 'title.tt*' );

		if ( $cache_files === false || count( $cache_files ) === 0 ) {
			return [];
		}

		$results = [];
		foreach ( $cache_files as $file ) {
			if ( preg_match( '!^title\.tt(\d{7,8})$!i', basename( $file ), $match ) === 1 ) {
				$results[] = new Title( $match[1], $this->imdbphp_class /*, $this->logger->log() why a logger? */ );
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

		$cache_files = glob( $this->imdb_cache_values['imdbcachedir'] . 'name.nm*' );

		if ( $cache_files === false || count( $cache_files ) === 0 ) {
			return [];
		}

		$results = [];
		foreach ( $cache_files as $file ) {
			if ( preg_match( '!^name\.nm(\d{7,8})$!i', basename( $file ), $match ) === 1 ) {
				$results[] = new Person( $match[1], $this->imdbphp_class /*, $this->logger->log() why a logger? */ );
			}
		}
		return $results;
	}

	/**
	 * Create cache folder if it does not exist
	 * Create folder based on 'imdbcachedir' cache option value, if not using alternative folders (inside plugin)
	 * Return false if:
	 * 1/ Cache is not active;
	 * 2/ Can't created alternative cache folders inside Lumière plugin
	 * 3/ Cache folders already exist & are writable
	 *
	 * @info Can't use $wp_filesystem at this stage, since it is called early during plugin activation in class core
	 *
	 * @param bool $screen_log whether to display logging on screen or not
	 * @return bool false if cache already exist or can't be created, true if cache folders were created
	 */
	public function lumiere_create_cache( bool $screen_log = false ): bool {

		global $wp_filesystem;

		// Restart logger in manner acceptable for class core and early execution.
		$this->logger = new Logger( 'settingsClass', $screen_log /* Deactivate the onscreen log, so WordPress activation doesn't trigger any error if debug is activated, such as upon plugin activation */ );
		do_action( 'lumiere_logger' ); // Restart the logger, without this, error on activation.

		// Cache folder paths.
		$options_cache = get_option( Settings::LUMIERE_CACHE_OPTIONS );
		$lumiere_folder_cache = $options_cache['imdbcachedir'];
		$lumiere_folder_cache_images = $options_cache['imdbphotoroot'];

		// If cache is not active, exit.
		if ( $options_cache['imdbusecache'] !== '1' ) {
			$this->logger->log()->debug( '[Lumiere][config][cachefolder] Cache is inactive, folders are not checked.' );
			return false;
		}

		// Cache folders exist with good permissions, exit.
		$this->lumiere_wp_filesystem_cred( $lumiere_folder_cache ); // in trait Admin_General that includes trait Files.
		wp_mkdir_p( $lumiere_folder_cache );
		$wp_filesystem->chmod( $lumiere_folder_cache, 0777 );
		wp_mkdir_p( $lumiere_folder_cache_images );
		// chmod( $lumiere_folder_cache_images, 0777 ); => throws locally an chmod error.
		if ( $wp_filesystem->is_writable( $lumiere_folder_cache ) && $wp_filesystem->is_writable( $lumiere_folder_cache_images ) ) {
			$this->logger->log()->debug( '[Lumiere][config][cachefolder] Cache folders exist and permissions are ok.' );
			return false;
		}

		$this->logger->log()->debug( '[Lumiere][config][cachefolder] The cache folder located at ' . $lumiere_folder_cache . ' is not writable, creating an alternative cache ' );

		$lumiere_alt_folder_cache = plugin_dir_path( dirname( __DIR__ ) ) . 'cache';
		$lumiere_alt_folder_cache_images = $lumiere_alt_folder_cache . '/images';

		// If we can write in $options_cache['imdbcachedir'] (ie: wp-content/cache), make sure permissions are ok
		if ( wp_mkdir_p( $lumiere_folder_cache ) && $wp_filesystem->chmod( $lumiere_folder_cache, 0777 ) ) {

			$this->logger->log()->debug( "[Lumiere][config][cachefolder] Cache folder $lumiere_folder_cache created." );

			// We can't write in $options_cache['imdbphotoroot'], so write in wp-content/plugins/lumiere/cache instead
		} elseif ( wp_mkdir_p( $lumiere_alt_folder_cache ) && $wp_filesystem->chmod( $lumiere_alt_folder_cache, 0777 ) ) {

			// Create partial var
			$lumiere_alt_folder_cache_partial = str_replace( WP_CONTENT_DIR, '', plugin_dir_path( __DIR__ ) ) . '../cache/';

			// Update the option imdbcachedir for new cache path values
			$options_cache['imdbcachedir'] = $lumiere_alt_folder_cache;
			$options_cache['imdbcachedir_partial'] = $lumiere_alt_folder_cache_partial;
			update_option( Settings::LUMIERE_CACHE_OPTIONS, $options_cache );

			$this->logger->log()->info( "[Lumiere][config][cachefolder] Alternative cache folder $lumiere_alt_folder_cache created." );
		} else {

			$this->logger->log()->error( "[Lumiere][config][cachefolder] Cannot create alternative cache folder $lumiere_alt_folder_cache." );
			return false;

		}

		// We can write in wp-content/cache/images
		if ( wp_mkdir_p( $lumiere_folder_cache_images ) && $wp_filesystem->chmod( $lumiere_folder_cache_images, 0775 ) ) {

			$this->logger->log()->debug( "[Lumiere][config][cachefolder] Image folder $lumiere_folder_cache_images created." );

			// We can't write in wp-content/cache/images, so write in wp-content/plugins/lumiere/cache/images instead
		} elseif ( wp_mkdir_p( $lumiere_alt_folder_cache_images ) && $wp_filesystem->chmod( $lumiere_alt_folder_cache_images, 0777 ) ) {

			$lumiere_folder_cache_partial = str_replace( WP_CONTENT_DIR, '', plugin_dir_path( __DIR__ ) ) . 'cache/';

			// Update the option imdbcachedir for new cache path values
			$options_cache['imdbcachedir_partial'] = $lumiere_folder_cache_partial;
			$options_cache['imdbphotodir'] = get_site_url() . '/' . $lumiere_folder_cache_partial . '/images/';
			$options_cache['imdbphotoroot'] = $lumiere_alt_folder_cache_images;
			update_option( Settings::LUMIERE_CACHE_OPTIONS, $options_cache );

			$this->logger->log()->info( "[Lumiere][config][cachefolder] Alternative cache image folder $lumiere_alt_folder_cache_images created." );

		} else {

			$this->logger->log()->error( "[Lumiere][config][cachefolder] Cannot create alternative cache image folder $lumiere_alt_folder_cache_images." );
			return false;

		}

		return true;
	}
}

