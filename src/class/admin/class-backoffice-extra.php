<?php declare( strict_types = 1 );
/**
 * Admin Extra: Complement natural backoffice WordPress functions
 *
 * @author      Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright   2023, Lost Highway
 *
 * @version     1.0
 * @package lumiere-movies
 */

namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) && ( ! class_exists( '\Lumiere\Settings' ) ) ) {
	wp_die( 'You can not call directly this page' );
}

/**
 * Add policy, sponsor pages, etc
 */
class Backoffice_Extra {

	public function __construct() {

		// Add sponsor on WP admin > Plugins
		add_filter( 'plugin_row_meta', [ $this, 'lumiere_add_table_links' ], 10, 2 );

		// Add settings links on WP admin > Plugins
		add_filter( 'plugin_action_links', [ $this, 'lumiere_add_left_links' ], 10, 2 );

		// Privacy
		add_action( 'admin_init', [ $this, 'lumiere_privacy_declarations' ], 20 );
	}

	/**
	 * Static instanciation of the class
	 * Needed to be called in add_actions()
	 *
	 * @return void The class was instanciated
	 */
	public static function lumiere_backoffice_start(): void {
		$backoffice_extra_class = new self();
	}

	/**
	 * Add link into the WordPress plugins left area
	 *
	 * @param string[] $plugin_meta An array of the plugin's metadata. Can be null.
	 * @param string $plugin_file_name Path to the plugin file relative to the plugins directory.
	 * @return array<string>|null $plugin_meta An array with the plugin's metadata.
	 * @since 3.9
	 */
	public function lumiere_add_left_links( array $plugin_meta, string $plugin_file_name ): ?array {

		if ( 'lumiere-movies/lumiere-movies.php' === $plugin_file_name ) {
			$plugin_meta['settings'] = sprintf( '<a href="%s"> %s </a>', admin_url( 'admin.php?page=lumiere_options' ), esc_html__( 'Settings', 'lumiere-movies' ) );
		}
		return $plugin_meta;
	}

	/**
	 * Add link into the WordPress plugins list table.
	 * Filters the array of row meta for each plugin to display Lumière's metas
	 *
	 * @param string[] $plugin_meta An array of the plugin's metadata. Can be null.
	 * @param string $plugin_file_name Path to the plugin file relative to the plugins directory.
	 * NOTINCLUDED @param array<string> $plugin_data An array of plugin data.
	 * NOTINCLUDED @param string $status Status filter currently applied to the plugin list.
	 *        Possible values are: 'all', 'active', 'inactive', 'recently_activated', 'upgrade', 'mustuse',
	 *        'dropins', 'search', 'paused', 'auto-update-enabled', 'auto-update-disabled'.
	 * @return array<string>|null $plugin_meta An array of the plugin's metadata.
	 */
	public function lumiere_add_table_links( array $plugin_meta, string $plugin_file_name ): ?array {

		if ( 'lumiere-movies/lumiere-movies.php' === $plugin_file_name ) {
			$plugin_meta[] = sprintf(
				'<a href="%1$s"><span class="dashicons dashicons-coffee" aria-hidden="true" style="font-size:14px;line-height:1.3"></span>%2$s</a>',
				'https://www.paypal.me/jcvignoli',
				esc_html__( 'Sponsor', 'lumiere-movies' )
			);
			$plugin_meta[] = sprintf(
				'<a href="%1$s"><span class="dashicons dashicons-cloud" aria-hidden="true" style="font-size:14px;line-height:1.3"></span>GIT repository</a>',
				'https://github.com/jcvignoli/lumiere-movies'
			);
		}
		return $plugin_meta;
	}

	/**
	 * Return the default suggested privacy policy content.
	 * A policy option is added to wp-admin/options-privacy.php?tab=policyguide
	 * The text is saved in WP database, and automatically displayed as updated should it change
	 *
	 * @return void The default policy content has been added to WP policy page
	 */
	public function lumiere_privacy_declarations(): void {

		if ( function_exists( 'wp_add_privacy_policy_content' ) ) {
			$content = $this->lumiere_get_default_privacy_content();
			wp_add_privacy_policy_content(
				'Lumiere! Movies',
				wp_kses_post( wpautop( $content ) )
			);
		}
	}

	/**
	 * Return the default suggested privacy policy content.
	 *
	 * @return string The default policy content.
	 */
	private function lumiere_get_default_privacy_content(): string {
		return sprintf(
			'<h2>' . __( 'What personal data does Lumière Movies plugin collect?', 'lumiere-movies' ) . '</h2>' .
			/* translators: %s: An HTML link to IMDb website */
			'<p>' . __( 'Although we are bound ourselves to the <a href="%s">IMDb privacy policy</a> when retrieving information from IMDb, Lumière Movies WordPress plugin does not collect data by itself. You remain anonymous to IMDb by visiting our website, your data is not sent to any third-party.', 'lumiere-movies' ) . '</p>',
			'https://www.imdb.com/privacy'
		);
	}

}
