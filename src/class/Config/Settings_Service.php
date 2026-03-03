<?php declare( strict_types = 1 );
/**
 * Settings Service
 *
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */

namespace Lumiere\Config;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Config\Get_Options;
use Lumiere\Config\Get_Options_Movie;
use Lumiere\Config\Get_Options_Person;

/**
 * Service for managing plugin settings.
 *
 * @phpstan-import-type OPTIONS_ADMIN from \Lumiere\Config\Settings
 * @phpstan-import-type OPTIONS_CACHE from \Lumiere\Config\Settings
 * @phpstan-import-type OPTIONS_DATA_MOVIE from \Lumiere\Config\Settings_Movie
 * @phpstan-import-type OPTIONS_DATA_PERSON from \Lumiere\Config\Settings_Person
 */
final class Settings_Service {

	/**
	 * @var array<string, mixed>
	 */
	private array $admin_options;

	/**
	 * @var array<string, mixed>
	 */
	private array $cache_options;

	/**
	 * @var array<string, mixed>
	 */
	private array $movie_options;

	/**
	 * @var array<string, mixed>
	 */
	private array $person_options;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->refresh();
	}

	/**
	 * Refresh options from database.
	 */
	public function refresh(): void {
		$this->admin_options  = (array) get_option( Get_Options::get_admin_tablename(), [] );
		$this->cache_options  = (array) get_option( Get_Options::get_cache_tablename(), [] );
		$this->movie_options  = (array) get_option( Get_Options_Movie::get_data_tablename(), [] );
		$this->person_options = (array) get_option( Get_Options_Person::get_data_person_tablename(), [] );
	}

	/**
	 * Check if plugin is installed (admin options exist).
	 *
	 * @return bool
	 */
	public function is_installed(): bool {
		return $this->admin_options !== [];
	}

	/**
	 * Get all admin options.
	 *
	 * @phpstan-return OPTIONS_ADMIN
	 * @return array<string, mixed>
	 */
	public function get_admin_options(): array {
		/** @phpstan-ignore-next-line */
		return $this->admin_options;
	}

	/**
	 * Get a specific admin option.
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function get_admin_option( string $key, mixed $default = null ): mixed {
		return $this->admin_options[ $key ] ?? $default;
	}

	/**
	 * Get all cache options.
	 *
	 * @phpstan-return OPTIONS_CACHE
	 * @return array<string, mixed>
	 */
	public function get_cache_options(): array {
		/** @phpstan-ignore-next-line */
		return $this->cache_options;
	}

	/**
	 * Get a specific cache option.
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function get_cache_option( string $key, mixed $default = null ): mixed {
		return $this->cache_options[ $key ] ?? $default;
	}

	/**
	 * Get all movie data options.
	 *
	 * @phpstan-return OPTIONS_DATA_MOVIE
	 * @return array<string, mixed>
	 */
	public function get_movie_options(): array {
		/** @phpstan-ignore-next-line */
		return $this->movie_options;
	}

	/**
	 * Get a specific movie data option.
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function get_movie_option( string $key, mixed $default = null ): mixed {
		return $this->movie_options[ $key ] ?? $default;
	}

	/**
	 * Get all person data options.
	 *
	 * @phpstan-return OPTIONS_DATA_PERSON
	 * @return array<string, mixed>
	 */
	public function get_person_options(): array {
		/** @phpstan-ignore-next-line */
		return $this->person_options;
	}

	/**
	 * Get a specific person data option.
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function get_person_option( string $key, mixed $default = null ): mixed {
		return $this->person_options[ $key ] ?? $default;
	}

	/**
	 * Update admin options.
	 *
	 * @param array<string, mixed> $options
	 */
	public function update_admin_options( array $options ): void {
		update_option( Get_Options::get_admin_tablename(), $options );
		$this->admin_options = $options;
	}
}

