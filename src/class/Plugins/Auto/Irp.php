<?php
/**
 * Class for IRP plugin (Intelly Related Post)
 *
 * @copyright (c) 2021, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */
declare( strict_types = 1 );

namespace Lumiere\Plugins\Auto;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Config\Settings_Service;
use Lumiere\Frontend\Post\Find_Items;
use Lumiere\Plugins\Plugins_Interface;

/**
 * Plugin to ensure Lumiere compatibility with IRP (Intelly Related Post) plugin
 * The styles/scripts are supposed to go in construct with add_action()
 * Can method get_active_plugins() to get an extra property $active_plugins, as available in {@link Plugins_Start::activate_plugins()}
 * Executed in Frontend only
 *
 * @phpstan-import-type OPTIONS_ADMIN from \Lumiere\Config\Settings
 * @see \Lumiere\Plugins\Plugins_Start Class calling if the plugin is activated in \Lumiere\Plugins\Plugins_Detect
 */
final class Irp implements Plugins_Interface {

	/**
	 * Constructor
	 */
	final public function __construct(
		private Settings_Service $settings = new Settings_Service()
	) {}

	/**
	 * Determine whether IRP is activated
	 *
	 * @return bool true if IRP is active
	 */
	#[\Override]
	public static function is_active(): bool {
		return defined( 'IRP_PLUGIN_FILE' );
	}

	/**
	 * Start the plugin
	 * @param array<string, class-string<Plugins_Interface>> $active_plugins Plugins that are activated
	 */
	#[\Override]
	public function init( array $active_plugins ): void {
		// Disable IRP plugin in Lumiere pages, it breaks them
		add_filter( 'the_content', [ $this, 'remove_irp_if_relevant' ], 11, 1 );
	}

	/**
	 * Detect if there is a movie in the post
	 * If there is any, remove the IRP filter that adds a related post
	 * If the default behaviour is overriden with imdbirpdisplay var activated in advanced main admin options, doesn't remove the filter
	 * Doesn't detect movies in widget, as related posts are not added to widgets
	 *
	 * @param null|string $content Text in the_content
	 * @return string Text in the_content untouched
	 *
	 * @see Lumiere\Frontend\Post\Front_Parser::$nb_of_movies for the static property that includes if one or more movies are displayed in the post
	 * @info check intelly-related-posts/includes/core.php for the IRP filter
	 */
	public function remove_irp_if_relevant( ?string $content ): string {

		if ( $this->settings->get_admin_option( 'imdbirpdisplay' ) === '1' ) {
			return $content ?? '';
		}

		// Remove the filter is one or more movies are detected using a static property in the relevant class.
		if ( Find_Items::$nb_of_movies > 0 ) {
			remove_filter( 'the_content', 'irp_the_content', intval( get_option( 'IRP_HookPriority', '99999' ) ) );
		}

		// Return untouched text
		return $content ?? '';
	}
}
