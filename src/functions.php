<?php declare( strict_types = 1 );
/**
 * Lumière Movies WordPress global functions
 * These functions can be executed before the Plugin activation
 * They are available anywhere in the Plugin or for any plugin
 *
 * @package           lumieremovies
 * @copyright         2005 https://www.jcvignoli.com/blog/en/lumiere-movies-wordpress-plugin
 */

if ( ! function_exists( 'lum_incompatible_plugin_text' ) ) {
	/**
	 * Notice of Plugins incompatibility
	 * @return void Notice was echoed
	 */
	function lum_incompatible_plugin_text(): void {
		$incompatible_name_plugins = ucwords( str_replace( '-', ' ', implode( ',', preg_replace( '#/.*#', '', LUMIERE_INCOMPATIBLE_PLUGINS ) ) ) ) . '. ';
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

if ( ! function_exists( 'lum_incompatible_php_text' ) ) {
	/**
	 * Notice of Plugins incompatibility
	 * @return void Notice was echoed
	 */
	function lum_incompatible_php_text(): void {
		printf(
			'<div class="%1$s"><p>%2$s <strong>%3$s</strong> %4$s</p></div>',
			'notice notice-error is-dismissible',
			esc_html__( 'Lumière is incompatible with your PHP Version', 'lumiere-movies' ),
			PHP_VERSION,
			esc_html__( 'This plugin been deactivated. It cannot be activated again unless you upgrade PHP version.', 'lumiere-movies' )
		);
	}
}

if ( ! function_exists( 'lum_incompatible_plugins_uninstall' ) ) {
	/**
	 * Uninstall lumiere if one of the incompatible plugins was found
	 *
	 * @param array<string> $incompat_plugins List of incompatible plugins
	 * @param string $plugin_lumiere Lumiere plugin file
	 * @return void An incompatiblity notice is echoed and Lumière is uninstalled
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

			add_action( 'admin_notices', 'lum_incompatible_plugin_text' );
			if ( ! function_exists( 'deactivate_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			deactivate_plugins( $plugin_lumiere );
			return;
		}
	}
}

if ( ! function_exists( 'lum_get_version' ) ) {
	/**
	 * Get the version of Lumière automatically from the Readme
	 * @return string
	 */
	function lum_get_version(): string {
		global $wp_filesystem;
		if ( $wp_filesystem === null ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}
		$readme_file = plugin_dir_path( __FILE__ ) . '/README.txt';
		$lumiere_version_recherche = isset( $wp_filesystem ) ? $wp_filesystem->get_contents( $readme_file ) : false;
		if ( $lumiere_version_recherche === false ) {
			throw new Exception( 'Lumière readme file is either missing or corrupted' );
		}
		preg_match( '#Stable tag:\s(.+)\n#', $lumiere_version_recherche, $lumiere_version_match );
		return $lumiere_version_match[1] ?? '0';
	}
}
