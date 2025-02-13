<?php declare( strict_types = 1 );
/**
 * Rewrite Rules
 *
 * @author      Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright   2023, Lost Highway
 *
 * @version     1.0
 * @package lumiere-movies
 */

namespace Lumiere\Alteration;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) && ( ! class_exists( '\Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Plugins\Logger;
use Lumiere\Config\Get_Options;

/**
 * Rewrite Rules for Popups: create rewrite rules but also add query_vars
 *
 * @since 3.11
 * @since 4.4 much simplified
 *
 * @see \Lumiere\Config\Get_Options (=>Settings) Includes the constants POPUP_STRING and LUM_REWRITE_RULES
 */
class Rewrite_Rules {

	/**
	 * Constructor
	 */
	public function __construct(
		private Logger $logger_class = new Logger( 'RewriteRules' ),
	) {
		// Add an extra query var for use in URLs.
		add_filter( 'query_vars', [ $this, 'lum_add_query_vars' ] );

		// Add rewrite rules when generating rewrite rules.
		add_filter( 'generate_rewrite_rules', [ $this, 'lum_add_rewrite_rules' ] );
	}

	/**
	 * Static instanciation of the class
	 * Needed to be called in add_actions()
	 *
	 * @return void The class was instanciated
	 */
	public static function lumiere_static_start(): void {
		$rewrite_class = new self();
	}

	/**
	 * Add the extra query vars that will be available in URL query string use Get_Options::POPUP_STRING
	 *
	 * @param array<int, string> $query_vars The array of existing query vars
	 * @return array<int, string> The query vars with the extra ones
	 */
	public function lum_add_query_vars( array $query_vars ): array {
		$loop = [ Get_Options::POPUP_STRING ];
		foreach ( $loop as $lumiere_query_var ) {
			$query_vars[] = $lumiere_query_var;
		}
		return $query_vars;
	}

	/**
	 * Add Lumiere rules to WordPress
	 *
	 * @param \WP_Rewrite $wp_rewrite Class passed from generate_rewrite_rules hook
	 * @return string[] Array of rewrite rules keyed by their regex pattern.
	 */
	public function lum_add_rewrite_rules( \WP_Rewrite $wp_rewrite ): array {
		$wp_rewrite->rules = Get_Options::LUM_REWRITE_RULES + $wp_rewrite->rules;
		$this->logger_class->log->debug( '[RewriteRules] Rules added to WP' );
		$this->add_polylang_rules( $wp_rewrite->rules );
		return $wp_rewrite->rules;
	}

	/**
	 * Add rules to polylang, if installed
	 *
	 * @param array<string, string> $existing_rules
	 * @return void
	 */
	private function add_polylang_rules( array $existing_rules ): void {
		if ( has_filter( 'pll_init' ) === false ) {
			return;
		}
		$this->logger_class->log->debug( '[RewriteRules] Rules added to Polylang' );
		add_filter(
			'pll_rewrite_rules',
			function( array $existing_rules ): array {
				return array_merge( $existing_rules, [ 'lumiere' ] );
			}
		);
	}

}
