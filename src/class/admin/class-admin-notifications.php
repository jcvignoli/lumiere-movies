<?php declare( strict_types = 1 );
/**
 * Admin class for displaying all Admin sections.
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2024, Lost Highway
 *
 * @version       2.0
 * @package lumiere-movies
 */

namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

/**
 * Display the notice messages definition called by child Admin_Menu classes when form submission took place
 *
 * @since 4.1
 * @since 4.1.1 More OOP, self::admin_msg_missing_taxo() and self::admin_msg_new_taxo() are called by Detect_New_Template_Taxo class, admin_notice_messages are now translated
 * @see \Lumiere\Admin\Detect_New_Template_Taxo to check if a message regarding the taxonomy should be displayed
 */
class Admin_Notifications {

	/**
	 * Notification messages
	 * @var array<string, array<int, int|string>> The messages with their color
	 * @phpstan-var array<string, array{string, int}> The messages with their color
	 */
	private array $admin_notice_messages;

	/**
	 * Constructor
	 */
	public function __construct() {

		$this->admin_notice_messages = [
			'options_updated' => [ __( 'Options saved.', 'lumiere-movies' ), 1 ],
			'options_reset' => [ __( 'Options reset.', 'lumiere-movies' ), 1 ],
			'main_options_error_identical_value' => [ __( 'Wrong values. You can not select the same URL string for taxonomy pages and popups.', 'lumiere-movies' ) . ' ' . __( 'No data was saved.', 'lumiere-movies' ), 3 ],
			'main_options_error_imdburlpopups_invalid' => [ __( 'Wrong value. The popup URL path must respect the specified value.', 'lumiere-movies' ) . ' ' . __( 'No data was saved.', 'lumiere-movies' ), 3 ],
			'cache_delete_all_msg' => [ __( 'All cache files deleted.', 'lumiere-movies' ), 5 ],
			'cache_delete_ticked_msg' => [ __( 'Ticked file(s) deleted.', 'lumiere-movies' ), 5 ],
			'cache_delete_individual_msg' => [ __( 'The selected cache file was deleted.', 'lumiere-movies' ), 5 ],
			'cache_refresh_individual_msg' => [ __( 'The selected cache file was refreshed.', 'lumiere-movies' ), 5 ],
			'cache_query_deleted' => [ __( 'Query cache files deleted.', 'lumiere-movies' ), 5 ],
			'taxotemplatecopy_success' => [ __( 'Lumière template successfully copied in your theme folder.', 'lumiere-movies' ), 5 ],
			'taxotemplatecopy_failed' => [ __( 'Template copy failed! Check the permissions in you theme folder.', 'lumiere-movies' ), 7 ],
			'taxotemplateautoupdate_success' => [ __( 'Taxonomy templates in your theme folder have been automatically updated.', 'lumiere-movies' ), 5 ],
			'lum_plugin_updated' => [ __( 'Lumière! plugin has been updated to the latest version.', 'lumiere-movies' ), 6 ],
			'options_update_failed' => [ __( 'Options could not be saved.', 'lumiere-movies' ), 7 ],
			'invalid_nonce' => [ __( 'Nonce is invalid, no change has been made.', 'lumiere-movies' ), 7 ],
		];

	}

	/**
	 * Static start, display notification if transients are found
	 */
	public static function lumiere_static_start(): void {

		$class = new self();
		add_action( 'admin_notices', [ $class, 'admin_msg_transients' ], 11 );
	}

	/**
	 * Display admin notice for missing taxonomy templates
	 *
	 * @param array<string> $missing_taxo_template Name(s) of the missing taxonomy templates
	 * @param string $page_data_taxo The URL of the taxonomy page option
	 * @return void Display notification message if relevant
	 * @see \Lumiere\Admin\Detect_New_Template_Taxo::lumiere_static_start()
	 */
	public function admin_msg_install_missing_template( array $missing_taxo_template, string $page_data_taxo ): void {

		$nb_missing = count( $missing_taxo_template );

		if ( $nb_missing === 0 ) {
			return;
		}

		echo wp_kses(
			$this->lumiere_notice(
				6,
				sprintf(
					/* translators: %1$s is one or many items like director, composer, etc., %2$s and %3$s are HTML tags */
					_n( 'Taxonomy template is activated, but the following template is missing: %2$s%1$s%3$s.', 'Taxonomy template is activated, but the following templates are missing: %2$s%1$s%3$s.', $nb_missing, 'lumiere-movies' ),
					implode( ', ', $missing_taxo_template ),
					'<i>',
					'</i>'
				)
				/* translators: %1$s and %2$s are HTML 'a' tags links */
				. ' ' . sprintf( _n( 'Please %1$sinstall%2$s it.', 'Please %1$sinstall%2$s them.', $nb_missing, 'lumiere-movies' ), '<a href="' . $page_data_taxo . '#imdb_imdbtaxonomyactor_yes">', '</a>' )
			),
			[
				'a' => [ 'href' => [] ],
				'div' => [ 'class' => [] ],
				'i' => [],
				'p' => [],
			]
		);

	}

	/**
	 * Display admin notice for new taxonomy templates found
	 *
	 * @param array<int, null|string> $new_taxo_template Name(s) of the new taxonomy templates found
	 * @param string $page_data_taxo The URL of the taxonomy page option
	 * @return void Display notification message if relevant
	 * @see \Lumiere\Admin\Detect_New_Template_Taxo::lumiere_static_start()
	 */
	public function admin_msg_update_template( array $new_taxo_template, string $page_data_taxo ): void {

		$nb_new = count( $new_taxo_template );

		if ( $nb_new === 0 ) {
			return;
		}

		echo wp_kses(
			$this->lumiere_notice(
				6,
				sprintf(
					/* translators: %1$s is one or many items like director, composer, etc., %2$s and %3$s are HTML tags */
					_n( 'New taxonomy template file found: %2$s%1$s%3$s.', 'New taxonomy template files found: %2$s%1$s%3$s.', $nb_new, 'lumiere-movies' ),
					implode( ', ', $new_taxo_template ),
					'<i>',
					'</i>'
				)
				/* translators: %1$s and %2$s are HTML 'a' tags links */
				. ' ' . sprintf( _n( 'Please %1$supdate%2$s it.', 'Please %1$supdate%2$s them.', $nb_new, 'lumiere-movies' ), '<a href="' . $page_data_taxo . '#imdb_imdbtaxonomyactor_yes">', '</a>' )
			),
			[
				'a' => [ 'href' => [] ],
				'div' => [ 'class' => [] ],
				'i' => [],
				'p' => [],
			]
		);
	}

	/**
	 * Display admin notices for any transients found
	 *
	 * @see \Lumiere\Admin\Admin_Menu::__construct()
	 * @since 4.1.1 added delete_transient()
	 */
	public function admin_msg_transients(): void {

		$notif_msg = get_transient( 'notice_lumiere_msg' );
		delete_transient( 'notice_lumiere_msg' );

		// Is a transient available and does the transient message exist?
		if ( is_string( $notif_msg ) === false || array_key_exists( $notif_msg, $this->admin_notice_messages ) === false ) {
			return;
		}

		echo wp_kses(
			$this->lumiere_notice(
				$this->admin_notice_messages[ $notif_msg ][1],
				esc_html( $this->admin_notice_messages[ $notif_msg ][0] )
			),
			[
				'div' => [ 'class' => [] ],
				'p' => [],
			]
		);
	}

	/**
	 * Display a confirmation notice, such as "options saved"
	 *
	 * @param int $code type of message
	 * @param string $msg text to display
	 */
	private function lumiere_notice( int $code, string $msg ): string {

		switch ( $code ) {
			default:
			case 1: // success notice, green
				return '<div class="notice notice-success"><p>' . $msg . '</p></div>';
			case 2: // info notice, blue
				return '<div class="notice notice-info"><p>' . $msg . '</p></div>';
			case 3: // simple error, red
				return '<div class="notice notice-error"><p>' . $msg . '</p></div>';
			case 4: // warning, yellow
				return '<div class="notice notice-warning"><p>' . $msg . '</p></div>';
			case 5: // success notice, green, dismissible
				return '<div class="notice notice-success is-dismissible"><p>' . $msg . '</p></div>';
			case 6: // info notice, blue, dismissible
				return '<div class="notice notice-info is-dismissible"><p>' . $msg . '</p></div>';
			case 7: // simple error, red, dismissible
				return '<div class="notice notice-error is-dismissible"><p>' . $msg . '</p></div>';
			case 8: // warning, yellow, dismissible
				return '<div class="notice notice-warning is-dismissible"><p>' . $msg . '</p></div>';
		}
	}
}
