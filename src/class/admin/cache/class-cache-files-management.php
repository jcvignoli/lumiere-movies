<?php declare( strict_types = 1 );
/**
 * Cache files management
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       3.0
 * @package lumiere-movies
 */

namespace Lumiere\Admin\Cache;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Plugins\Manual\Imdbphp;
use Lumiere\Plugins\Logger;
use Lumiere\Admin\Admin_General;
use Lumiere\Tools\Get_Options;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Exception;

/**
 * Class used by Submenu\Cache to delete and build cache
 * Class used by Cron to set refreshes and delete files
 * Method lumiere_create_cache() is used by many classes that need to check if cache folders exist
 *
 * @see \Lumiere\Admin\Submenu\Cache
 * @see \Lumiere\Admin\Cron
 * @since 4.0 Methods extracted from Submenu\Cache class and refactored
 *
 * @phpstan-import-type OPTIONS_CACHE from \Lumiere\Tools\Settings_Global
 */
class Cache_Files_Management {

	/**
	 * Traits.
	 */
	use Admin_General; // it includes trait Files.

	/**
	 * Cache options
	 * @phpstan-var OPTIONS_CACHE $imdb_cache_values
	 */
	private array $imdb_cache_values;

	/**
	 *  Constructor
	 */
	public function __construct(
		private Logger $logger = new Logger( 'cacheFilesManagement' ),
		private Imdbphp $imdbphp_class = new Imdbphp()
	) {
		$this->imdb_cache_values = get_option( Get_Options::get_cache_tablename() );
	}

	/**
	 * Find all files in relation of an IMDBID and a type of data (movie or people)
	 *
	 * @param string $imdb_id the people's or movie's IMDb ID
	 * @param 'movie'|'people'|string $movie_or_people Define the type of data to delete
	 * @return list<string>|null Null on error or files not found, array of files found otherwise
	 */
	private function find_files( string $imdb_id, string $movie_or_people ): ?array {
		$id_sanitized = esc_html( $imdb_id );
		$pattern_glob = [
			'movie' => '*tt' . $id_sanitized . '*',
			'people' => '*nm' . $id_sanitized . '*',
		];
		$files_found = glob( $this->imdb_cache_values['imdbcachedir'] . $pattern_glob[ $movie_or_people ] );
		return $files_found !== false && count( $files_found ) > 0 ? $files_found : null;
	}

	/**
	 * Delete a unique file, either movie or people
	 *
	 * @see \Lumiere\Admin\Save_Options::do_delete_cache_linked_file()
	 *
	 * @param 'movie'|'people'|string $movie_or_people Define the type of data to delete
	 * @param string $imdb_id the people's or movie's IMDb ID
	 * @return bool True if a file was deleted
	 */
	public function delete_file( string $movie_or_people, string $imdb_id ): bool {

		$this->lumiere_wp_filesystem_cred( $this->imdb_cache_values['imdbcachedir'] ); // from Files trait.

		global $wp_filesystem;

		// prevent drama.
		if ( ! isset( $this->imdb_cache_values['imdbcachedir'] ) || $wp_filesystem->is_dir( $this->imdb_cache_values['imdbcachedir'] ) === false ) {
			throw new Exception( 'Cache directory does not exist.' );
		}

		$id_sanitized = esc_html( $imdb_id );

		$list_items = $this->find_files( sanitize_key( $id_sanitized ), sanitize_key( $movie_or_people ) );

		// Check if the file exist.
		if ( $list_items === null ) {
			$this->logger->log->error( '[Lumiere][Cache_Tools] The file ' . $id_sanitized . ' does not exist ' );
			return false;
		}

		$pattern_pictures = [
			'movie' => [
				'small' => 'tt' . $id_sanitized . '.jpg',
				'big' => 'tt' . $id_sanitized . '_big.jpg',
			],
			'people' => [
				'small' => 'nm' . $id_sanitized . '.jpg',
				'big' => 'nm' . $id_sanitized . '_big.jpg',
			],
		];

		foreach ( $list_items as $cache_to_delete ) {
			wp_delete_file( $cache_to_delete );
		}

		// Delete pictures, small and big.
		$pic_small_sanitized = $this->imdb_cache_values['imdbphotoroot'] . $pattern_pictures[ $movie_or_people ]['small'];
		$pic_big_sanitized = $this->imdb_cache_values['imdbphotoroot'] . $pattern_pictures[ $movie_or_people ]['big'];
		if ( file_exists( $pic_small_sanitized ) ) {
			wp_delete_file( $pic_small_sanitized );
		}
		if ( file_exists( $pic_big_sanitized ) ) {
			wp_delete_file( $pic_big_sanitized );
		}
		return true;
	}

	/**
	 * Refresh a unique file
	 *
	 * @param 'movie'|'people'|string $movie_or_people Define either 'people' or 'movie'
	 * @param string $imdb_id the people's or movie's IMDb ID
	 * @return void File was refreshed (deleted and got back)
	 */
	public function refresh_file( string $movie_or_people, string $imdb_id ): void {

		// Delete the specific item.
		if ( $this->delete_file( sanitize_key( $movie_or_people ), sanitize_key( $imdb_id ) ) === true ) {
			// Get again the item.
			$function_movie_or_people = 'create_' . $movie_or_people . '_file'; // Methods create_movie_file() or create_people_file()
			$this->$function_movie_or_people( esc_html( $imdb_id ) );
		}
	}

	/**
	 * Refresh multiple files
	 *
	 * @param array<string> $ids_array The list of ids of movies/people to refresh
	 * @param 'movie'|'people'|string $movie_or_people The kind of data passed
	 *
	 * @since 4.3.3 Method created
	 */
	public function refresh_multiple_files( array $ids_array, string $movie_or_people ): void {
		foreach ( $ids_array as $id_found ) {
			$this->refresh_file( esc_html( $movie_or_people ), esc_html( $id_found ) );
		}
	}

	/**
	 * Delete multiple files
	 *
	 * @param array<string> $ids_array The list of ids of movies/people to delete
	 * @param 'movie'|'people'|string $movie_or_people The kind of data passed
	 */
	public function delete_multiple_files( array $ids_array, string $movie_or_people ): void {
		foreach ( $ids_array as $id_found ) {
			$this->delete_file( esc_html( $movie_or_people ), esc_html( $id_found ) );
		}
	}

	/**
	 * Refresh all cache files => called by cron, using transients to keep track of already refreshed files
	 *
	 * 1/ Refresh the cache using $this->refresh_file()
	 * 2/ Use transient to initialy store all movies to refresh in an array (transient's names either 'lum_cache_cron_refresh_store_movie' or 'lum_cache_cron_refresh_store_people'
	 * 3/ The movies refreshed will be deleted from the array in the transiant
	 * 4/ Only $batch_limit movies are processed per batch
	 * 5/ A new round of refresh will happen when $days_next_start is passed
	 * 6/ If the transients get deleted for whatever reason, it will start over
	 *
	 * @see \Lumiere\Admin\Cron::lumiere_cron_exec_autorefresh() Cron refreshes all cache
	 * @since 4.0 Method created
	 * @since 4.3.3 Deeply reviewed, removed sleep, using batches, using transients => needs to be executed more often
	 *
	 * @param int $batch_limit OPTIONAL: number of files processed in every call to this method (every cron call) for each $types (double the number)
	 * @param int $days_next_start OPTIONAL: Number of days before starting a brand new serie of refresh.
	 * @return void All cache files has been refreshed
	 */
	public function cron_all_cache_refresh( int $batch_limit = 5, int $days_next_start = 5 ): void {

		$refresh_ids = [];
		$types = [ 'movie', 'people' ];
		$day_in_seconds = 24 * 60 * 60;

		foreach ( $types as $movie_or_people ) {

			$array_all_items = get_transient( 'lum_cache_cron_refresh_store_' . $movie_or_people );

			// Transient didn't exist, so create an array of all movies/people in the cache and put it in a transient.
			if ( $array_all_items === false ) {
				foreach ( $this->get_imdb_object_per_cat( $movie_or_people ) as $movie_title_object ) { // Build array of movies to refresh.
					$refresh_ids[ $movie_or_people ][] = $movie_title_object->imdbid();
				}
				if ( isset( $refresh_ids[ $movie_or_people ] ) ) {
					$array_all_items = $refresh_ids[ $movie_or_people ];
					set_transient( 'lum_cache_cron_refresh_store_' . $movie_or_people, $array_all_items, $days_next_start * $day_in_seconds );
					$this->logger->log->error( '[Lumiere][Cache_Tools] Set transient lum_cache_cron_refresh_store_' . $movie_or_people );
				}
				// Everything has already been processed, exit.
			} elseif ( count( $array_all_items ) === 0 ) {
				$this->logger->log->debug( '[Lumiere][Cache_Tools] Already processed all rows for *' . $movie_or_people . '*, a new batch of refresh will start after the defined time ' . gmdate( 'd \d\a\y\s', $days_next_start * $day_in_seconds ) . ' since the first run' );
				continue;
			}

			if ( $array_all_items === false ) {
				$this->logger->log->info( '[Lumiere][Cache_Tools] Could not retrieve any file to refresh' );
				continue;
			}

			$last_row = intval( array_keys( $array_all_items )[0] );
			$nb_remaining_rows = count( $array_all_items );

			for ( $i = 0 + $last_row; $i < ( $batch_limit + $last_row ) && $i < ( $nb_remaining_rows + $last_row ); $i++ ) {
				$this->logger->log->debug( '[Lumiere][Cache_Tools] Processed *' . $movie_or_people . '* id: ' . $array_all_items[ $i ] . ' (' . count( $array_all_items ) . ' rows remaining)' ); // don't use $nb_remaining_rows, as it doesn't decrease.
				// Refresh (delete and get it again) the item.
				$this->refresh_file( $movie_or_people, $array_all_items[ $i ] );
				// Delete the row in the array we just processed so it won't be processed again.
				unset( $array_all_items[ $i ] );
			}
			// Set the transient with an updated array (processed lines were removed).
			set_transient( 'lum_cache_cron_refresh_store_' . $movie_or_people, $array_all_items );
		}
	}

	/**
	 * Create Movie files
	 * @param string $id The movie's ID
	 */
	private function create_movie_file( string $id ): void {

		$movie = $this->imdbphp_class->get_title_class( $id, $this->logger->log_null() /* keep it quiet, no logger */ );

		// create cache for everything.
		$movie->alsoknow();
		$movie->cast();
		$movie->color();
		$movie->composer();
		$movie->country();
		$movie->cinematographer();
		$movie->director();
		$movie->genre();
		$movie->goof();
		$movie->keyword();
		$movie->language();
		$movie->extSites();
		$movie->photoLocalurl( true );
		$movie->photoLocalurl( false );
		$movie->plot();
		$movie->prodCompany();
		$movie->producer();
		$movie->quote();
		$movie->rating();
		$movie->runtime();
		$movie->soundtrack();
		$movie->tagline();
		$movie->title();
		$movie->video();
		$movie->votes();
		$movie->writer();
		$movie->year();
	}

	/**
	 * Create People files
	 * @param string $id The People's ID
	 */
	private function create_people_file( string $id ): void {

		// Get again the person.
		$person = $this->imdbphp_class->get_name_class( $id, $this->logger->log_null() /* keep it quiet, no logger */ );

		// Create cache for everything.
		$person->bio();
		$person->birthname();
		$person->born();
		$person->died();
		$person->name();
		$person->photoLocalurl( true );
		$person->photoLocalurl( false );
		$person->pubmovies();
		$person->quotes();
		$person->trivia();
		$person->trademark();
	}

	/**
	 * Delete all query cache files
	 * Done by clicking on "delete query cache"
	 * @throws Exception if no cache folder is found, query files are found (not supposed to call the function if there are no query files)
	 */
	public function delete_query_cache_files(): void {

		// prevent drama.
		if ( ! isset( $this->imdb_cache_values['imdbcachedir'] ) ) {
			throw new Exception( 'Cache folder does not exist' );
		}

		// Delete cache.
		$files_query = glob( $this->imdb_cache_values['imdbcachedir'] . 'gql.Search.*' );

		// if file doesn't exist.
		if ( $files_query === false || count( $files_query ) === 0 ) {
			throw new Exception( 'No query files found.' );
		}

		foreach ( $files_query as $cache_to_delete ) {

			if ( $cache_to_delete === $this->imdb_cache_values['imdbcachedir'] . '.' || $cache_to_delete === $this->imdb_cache_values['imdbcachedir'] . '..' ) {
				continue;
			}
			// the file exists, it is neither . nor .., so delete!
			wp_delete_file( $cache_to_delete );
		}
	}

	/**
	 * Retrieve all files in cache folder
	 *
	 * @return array<int, mixed>|array<string> Sorted by size list of all files found in Lumière cache folder
	 */
	private function get_cache_list_bysize(): array {
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
	 * Get size and number of query files
	 *
	 * @return array<int, string> Number of query files, cache query filesize
	 */
	public function get_cache_query_info( string $folder ): array {

		$cache_query_folder = glob( $folder . 'gql.Search.*' );

		// Found no file, exit.
		if ( $cache_query_folder === false || count( $cache_query_folder ) === 0 ) {
			return [];
		}

		$cache_query_count = strval( count( $cache_query_folder ) );
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
	public function cache_getfoldersize( ?string $folder = null ): int {

		global $wp_filesystem;
		$final_folder = $folder ?? $this->imdb_cache_values['imdbcachedir'];
		$this->lumiere_wp_filesystem_cred( $final_folder ); // in trait Admin_General that includes trait Files.

		if ( ! is_dir( $final_folder ) ) {
			return 0;
		}
		$folder_iterator = $wp_filesystem->is_writable( $final_folder ) ? new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $final_folder, RecursiveDirectoryIterator::SKIP_DOTS )
		) : null;

		if ( ! isset( $folder_iterator ) ) {
			return 0;
		}

		$final_size = 0;

		foreach ( $folder_iterator as $file ) {
			if ( $file->isDir() === true ) {
				continue;
			}
			$final_size += $wp_filesystem->is_readable( $file ) ? $file->getSize() : 0;
		}
		return $final_size;
	}

	/**
	 * Count the number of files in given folder (cache lumiere by default )
	 *
	 * @param null|string $folder Folder path, internally changed into cachedir if null
	 * @return int Number of files found in given folder
	 */
	public function cache_countfolderfiles( ?string $folder = null ): int {

		global $wp_filesystem;
		$final_folder = $folder ?? $this->imdb_cache_values['imdbcachedir'];
		$this->lumiere_wp_filesystem_cred( $final_folder ); // in trait Admin_General that includes trait Files.

		$folder_iterator = $wp_filesystem->is_writable( $final_folder ) ? new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $final_folder, RecursiveDirectoryIterator::SKIP_DOTS )
		) : null;
		return isset( $folder_iterator ) ? iterator_count( $folder_iterator ) : 0;
	}

	/**
	 * Retrieve files that are over a given limit
	 * The size is provided in megabits and then internally processed as bits
	 *
	 * @param int $size_limit Limit in megabits ( '100' = 100 MB )
	 * @return null|array<int, string> Array of files paths that exceeds the passed size_limit
	 */
	private function find_cache_files_over_limit( int $size_limit ): ?array {

		$size_limit_in_bits = $size_limit * 1000000; // convert in bits
		$current_size = 0;
		$list_files_over_size_limit = [];
		foreach ( $this->get_cache_list_bysize() as $array ) {
			$current_size += $array[1];
			if ( $current_size >= $size_limit_in_bits ) {
				$list_files_over_size_limit[] = $array[2];
			}
		}
		return count( $list_files_over_size_limit ) > 0 ? $list_files_over_size_limit : null;
	}

	/**
	 * Delete files that are over a given limit
	 *
	 * Cron deletes files if their size is above a given limit in {@see \Lumiere\Admin\Cron::lumiere_cron_exec_cache()}
	 *
	 * @param int $size_limit Limit in megabits
	 * @return void Files exceeding provided limited are deleted
	 */
	public function lumiere_cache_delete_files_over_limit( int $size_limit ): void {

		$files = $this->find_cache_files_over_limit( $size_limit ) ?? [];

		foreach ( $files as $file ) {
			if ( is_file( $file ) ) {
				wp_delete_file( $file );
			}
		}
		if ( count( $files ) > 0 ) {
			$this->logger->log->debug( '[Lumiere][Cache_Tools] Oversized Cache cron deleted the following files: ' . join( $files ) );
			return;
		}
		$this->logger->log->debug( '[Lumiere][Cache_Tools] Oversized Cache cron did not find any file to delete' );
	}

	/**
	 * Return an array of cache per category
	 *
	 * @see \Lumiere\Admin\Submenu\Cache Use this method to display the categories
	 * @see self::all_cache_refresh() Use this method to refresh all cache
	 *
	 * @param 'movie'|'people'|string $movie_or_people Define either 'people' or 'movie'
	 * @return array<int, \Imdb\Title|\Imdb\Name>
	 */
	public function get_imdb_object_per_cat( string $movie_or_people ): array {

		$results = [];

		$patterns = [
			'movie' => [
				'glob'          => 'gql.TitleYear.{.id...tt*',
				'preg'          => '!gql\.TitleYear\.\{\.id\.\.\.tt(\d{7,8})\.!i',
				'imdbphpmethod' => 'get_title_class', // method in \Lumiere\Plugins\Manual\Imdbphp.
			],
			'people' => [
				'glob'          => 'gql.Name.{.id...nm*',
				'preg'          => '!gql\.Name\.\{\.id\.\.\.nm(\d{7,8})\.!i',
				'imdbphpmethod' => 'get_name_class', // method in \Lumiere\Plugins\Manual\Imdbphp.
			],
		];

		// Find related files
		$cache_files = glob( $this->imdb_cache_values['imdbcachedir'] . $patterns[ $movie_or_people ]['glob'] );

		if ( $cache_files === false || count( $cache_files ) === 0 ) {
			return $results;
		}

		foreach ( $cache_files as $file ) {

			// Retrieve imdb id in file.
			if ( preg_match( $patterns[ $movie_or_people ]['preg'], basename( $file ), $match ) === 1 ) {
				// Do a query using imdb id but the method will depend on the category.
				$method = $patterns[ $movie_or_people ]['imdbphpmethod'];
				$results[] = $this->imdbphp_class->$method( $match[1], $this->logger->log_null() /* keep it quiet, no logger */ );
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
	 * @param bool $screen_log whether to display logging on screen or not
	 * @return bool True if cache could be created
	 */
	public function lumiere_create_cache( bool $screen_log = false ): bool {

		global $wp_filesystem;

		// Restart logger in manner acceptable for class core and early execution.
		$this->logger = new Logger( 'cacheToolsClass', $screen_log /* Deactivate the onscreen log, so WordPress activation doesn't trigger any error if debug is activated, such as upon plugin activation */ );

		// Cache folder paths.
		$options_cache = get_option( Get_Options::get_cache_tablename() );
		$lumiere_folder_cache = $options_cache['imdbcachedir'];
		$lumiere_folder_cache_images = $options_cache['imdbphotoroot'];

		// If cache is not active, exit.
		if ( $options_cache['imdbusecache'] !== '1' ) {
			$this->logger->log->debug( '[Lumiere][config][cachefolder] Cache is inactive, folders are not checked.' );
			return false;
		}

		$this->lumiere_wp_filesystem_cred( $lumiere_folder_cache ); // in trait Admin_General that includes trait Files.

		// Everything is fine, exit.
		if ( $wp_filesystem->is_writable( $lumiere_folder_cache ) && $wp_filesystem->is_writable( $lumiere_folder_cache_images ) ) {
			$this->logger->log->debug( '[Lumiere][config][cachefolder] Cache folders exist and permissions are ok.' );
			return true;
		}

		// Create the cache folders.
		wp_mkdir_p( $lumiere_folder_cache );
		wp_mkdir_p( $lumiere_folder_cache_images );
		if (
			$wp_filesystem->is_writable( $lumiere_folder_cache ) === false
			|| $wp_filesystem->is_writable( $lumiere_folder_cache_images ) === false
		) {
			$wp_filesystem->chmod( $lumiere_folder_cache, 0777 );
			$wp_filesystem->chmod( $lumiere_folder_cache_images, 0777 );

			$this->logger->log->debug( '[Lumiere][config][cachefolder] Tried to change cache folder permissions.' );
		}
		// Exit if cache is now created and writable.
		if (
			$wp_filesystem->is_dir( $lumiere_folder_cache ) === true
			&& $wp_filesystem->is_dir( $lumiere_folder_cache_images ) === true
			&& $wp_filesystem->is_writable( $lumiere_folder_cache ) === true
			&& $wp_filesystem->is_writable( $lumiere_folder_cache_images ) === true
		) {
			$this->logger->log->debug( '[Lumiere][config][cachefolder] Cache folders have been created.' );
			return true;
		}

		$this->logger->log->debug( '[Lumiere][config][cachefolder] The cache folder located at ' . $lumiere_folder_cache . ' is not writable, creating an alternative cache ' );

		$lumiere_alt_folder_cache = LUMIERE_WP_PATH . 'cache';
		$lumiere_alt_folder_cache_images = $lumiere_alt_folder_cache . '/images';

		// Let's create an alternative cache folder inside the plugins, make sure permissions are ok
		if (
			wp_mkdir_p( $lumiere_alt_folder_cache ) === true && $wp_filesystem->chmod( $lumiere_alt_folder_cache, 0777 ) === true
			&& wp_mkdir_p( $lumiere_alt_folder_cache_images ) === true && $wp_filesystem->chmod( $lumiere_alt_folder_cache_images, 0777 ) === true
		) {

			// the partial path
			/** @psalm-suppress PossiblyInvalidOperand (Cannot concatenate with a array<array-key, string>|string, psalm can't dynamic const */
			$lumiere_alt_folder_cache_partial = str_replace( WP_CONTENT_DIR, '', LUMIERE_WP_PATH ) . 'cache/';

			// Update database with the new value for cache path.
			$options_cache['imdbcachedir'] = $lumiere_alt_folder_cache;
			$options_cache['imdbphotoroot'] = $lumiere_alt_folder_cache_images;
			$options_cache['imdbcachedir_partial'] = $lumiere_alt_folder_cache_partial;
			update_option( Get_Options::get_cache_tablename(), $options_cache );

			$this->logger->log->debug( "[Lumiere][config][cachefolder] Alternative cache folder $lumiere_folder_cache created." );
			return true;
		}

		$this->logger->log->error( '[Lumiere][config][cachefolder] Cannot create either a regular or alternative cache folder.' );
		return false;
	}
}

