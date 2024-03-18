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
	wp_die( esc_html__( 'You can not call directly this page', 'lumiere-movies' ) );
}

use Lumiere\Admin\Admin_Check_Taxo;

/**
 * Display the notice messages definition called by child Admin_Menu classes when form submission took place
 *
 * @since 4.0.3
 * @see \Lumiere\Admin\Admin_Check_Taxo to check if a message regarding the taxonomy should be displayed
 */
class Admin_Notifications {

	/**
	 * Class to check if new taxonomy templates are available
	 */
	private Admin_Check_Taxo $class_check_taxo;

	/**
	 * Notification messages
	 * @var array<string, array<int, int|string>> The messages with their color
	 * @phpstan-var array<string, array{0:string, 1:int}> The messages with their color
	 */
	const ADMIN_NOTICE_MESSAGES = [
		'options_updated' => [ 'Options saved.', 1 ],
		'options_reset' => [ 'Options reset.', 1 ],
		'general_options_error_identical_value' => [ 'Wrong values. You can not select the same URL string for taxonomy pages and popups.', 3 ],
		'cache_delete_all_msg' => [ 'All cache files deleted.', 1 ],
		'cache_delete_ticked_msg' => [ 'Ticked file(s) deleted.', 1 ],
		'cache_delete_individual_msg' => [ 'The selected cache file was deleted.', 1 ],
		'cache_refresh_individual_msg' => [ 'The selected cache file was refreshed.', 1 ],
		'cache_query_deleted' => [ 'Query cache files deleted.', 1 ],
		'taxotemplatecopy_success' => [ 'Lumière template successfully copied in your theme folder.', 1 ],
		'taxotemplatecopy_failed' => [ 'Template copy failed! Check the permissions in you theme folder.', 3 ],
	];

	/**
	 * Constructor
	 */
	public function __construct() {

		// Get the needed function that
		$this->class_check_taxo = new Admin_Check_Taxo();
	}

	/**
	 * Static start
	 */
	public static function lumiere_static_start( string $page_data_taxo ): void {
		$class = new self();
		$class->admin_display_messages( $page_data_taxo );
	}

	/**
	 * Display admin notices
	 *
	 * @since 4.0 using transients for displaying Admin notice messages
	 */
	public function admin_display_messages( string $page_data_taxo ): void {

		// Display message for missing taxonomy found.
		$missing_taxo_template = $this->class_check_taxo->lumiere_missing_taxo();
		if ( isset( $missing_taxo_template ) ) {
			$nb_missing = count( $missing_taxo_template );
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

		// Display message for new taxonomy found.
		$new_taxo_template = $this->class_check_taxo->lumiere_new_taxo();
		if ( isset( $new_taxo_template ) ) {
			$nb_new = count( $new_taxo_template );
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

		// Messages for Admin notification using transiants.
		$notif_msg = get_transient( 'notice_lumiere_msg' );
		if ( is_string( $notif_msg ) && array_key_exists( $notif_msg, self::ADMIN_NOTICE_MESSAGES ) ) {
			echo wp_kses(
				$this->lumiere_notice(
					self::ADMIN_NOTICE_MESSAGES[ $notif_msg ][1],
					esc_html( self::ADMIN_NOTICE_MESSAGES[ $notif_msg ][0] )
				),
				[
					'div' => [ 'class' => [] ],
					'p' => [],
				]
			);
		}
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
