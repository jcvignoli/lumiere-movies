<?php declare( strict_types = 1 );
/**
 * Class for IRP plugin (Intelly Related Post)
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version 1.0
 * @package lumiere-movies
 */

namespace Lumiere\Plugins\Auto;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Frontend\Movie;
use Lumiere\Settings;

/**
 * Plugin to ensure Lumiere compatibility with IRP (Intelly Related Post) plugin
 * Will remove the IRP filter that displays IRP sections when Lumiere movie(s) is displayed
 *
 * @phpstan-import-type OPTIONS_ADMIN from \Lumiere\Tools\Settings_Global
 * @see \Lumiere\Plugins\Plugins_Start Class calling if the plugin is activated in \Lumiere\Plugins\Plugins_Detect
 */
class Irp {

	/**
	 * Lumière Admin options.
	 * @phpstan-var OPTIONS_ADMIN $imdb_admin_values
	 * @var array<string, string>
	 */
	private array $imdb_admin_values;

	/**
	 * List of plugins active (including current class)
	 * @var array<string> $active_plugins
	 * @phpstan-ignore-next-line -- Property Lumiere\Plugins\Amp::$active_plugins is never read, only written -- want to keep the possibility in the future
	 */
	private array $active_plugins;

	/**
	 * Constructor
	 * @param array<string> $active_plugins
	 */
	final public function __construct( array $active_plugins ) {

		// Get the list of active plugins.
		$this->active_plugins = $active_plugins;

		// Get the values from database.
		$this->imdb_admin_values = get_option( Settings::get_admin_tablename() );

		// Disable IRP plugin in Lumiere pages, it breaks them
		add_filter( 'the_content', [ $this, 'lumiere_remove_irp_if_relevant' ], 11, 1 );

	}

	/**
	 * Static start for extra functions not to be run in self::__construct. No $this available!
	 */
	public static function start_init_hook(): void {}

	/**
	 * Detect if there is a movie in the post
	 * If there is any, remove the IRP filter that adds a related post
	 * If the default behaviour is overriden with imdbirpdisplay var activated in advanced general admin options, doesn't remove the filter
	 * Doesn't detect movies in widget, as related posts are not added to widgets
	 *
	 * @param null|string $content Text in the_content
	 * @return string Text in the_content untouched
	 *
	 * @see {Lumiere\Frontend\Movie::$nb_of_movies} for the static property that includes if one or more movies are displayed in the post
	 * @see intelly-related-posts/includes/core.php for the IRP filter
	 */
	public function lumiere_remove_irp_if_relevant( ?string $content ): string {

		if ( $this->imdb_admin_values['imdbirpdisplay'] === '1' ) {
			return $content ?? '';
		}

		// Remove the filter is one or more movies are detected using a static property in the relevant class.
		if ( Movie::$nb_of_movies > 0 ) {
			remove_filter( 'the_content', 'irp_the_content', intval( get_option( 'IRP_HookPriority', '99999' ) ) );
		}

		// Return untouched text
		return $content ?? '';
	}
}

