<?php declare( strict_types = 1 );
/**
 * Lumière Movies WordPress global functions
 *
 * @package           lumiere-movies
 * @author            jcvignoli
 * @copyright         2005 https://www.jcvignoli.com/blog/en/lumiere-movies-wordpress-plugin
 */

if ( ! function_exists( 'lum_install_incompat_notice' ) ) {
	/**
	 * Notice of Plugins incompatibility
	 * @return void Notice was echoed
	 */
	function lum_install_incompat_notice(): void {
		$incompatible_name_plugins = ucwords( str_replace( '-', ' ', implode( ',', preg_replace( '#/.*#', '', Lumiere\Settings::LUMIERE_INCOMPATIBLE_PLUGINS ) ) ) ) . '. ';
		printf(
			'<div class="%1$s"><p>%2$s <strong>%5$s</strong> %3$s</p><p>%4$s</p></div>',
			'notice notice-error is-dismissible',
			esc_html__( 'Lumière is incompatible with the following plugins: ', 'lumiere-movies' ),
			esc_html__( 'You installed one of them.', 'lumiere-movies' ),
			esc_html__( 'Lumière has been deactivated and cannot be activated again unless you deactivate them all.', 'lumiere-movies' ),
			esc_html( $incompatible_name_plugins )
		);
	}
}

if ( ! function_exists( 'lum_incompatible_plugins_uninstall' ) ) {
	/**
	 * Uninstall lumiere if one of the incompatible plugins was found
	 *
	 * @param array<string> $incompat_plugins List of incompatible plugins
	 * @param string $plugin_lumiere Lumiere plugin file
	 * @return void An incompatiblity notice was echoed and Lumière was uninstalled
	 */
	function lum_incompatible_plugins_uninstall( array $incompat_plugins, string $plugin_lumiere ): void {
		if (
			count(
				array_intersect(
					$incompat_plugins,
					apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) // @phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Modifying core WP hook!
				)
			) > 0
		) {

			add_action( 'admin_notices', 'lum_install_incompat_notice' );
			if ( ! function_exists( 'deactivate_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			deactivate_plugins( $plugin_lumiere );
			return;
		}
	}
}

