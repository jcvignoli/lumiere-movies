<?php declare( strict_types = 1 );
/**
 * Rewrite Rules
 *
 * @copyright     2023, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
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
 * It uses the correct hook 'generate_rewrite_rules' that is triggered when visiting ie Permalinks
 * It also triggers custom hook 'lum_add_rewrite_rules_if_admin' when visiting a Lumiere admin menu, which detects if rules are needed, add them an flushs
 * Polylang compatibility made here, so no need to use Plugins_Detect
 *
 * @since 3.11
 * @since 4.4 much simplified
 *
 * @see \Lumiere\Config\Get_Options (=>Settings) Includes the constants LUM_POPUP_STRING and LUM_REWRITE_RULES
 */
final class Rewrite_Rules {

	/**
	 * Constructor
	 */
	public function __construct(
		private Logger $logger = new Logger( 'RewriteRules', false /* deactivate the screen logging as it is executed early */ ),
	) {
		// Add an extra query var for use in URLs.
		add_filter( 'query_vars', [ $this, 'lum_add_query_vars' ] );

		// Add rewrite rules when generating rewrite rules
		add_filter( 'generate_rewrite_rules', [ $this, 'lum_add_rewrite_rules' ] );

		/**
		 * Call anytime the custom filter
		 * Called in Lumiere settings zone
		 * @see \Lumiere\Admin\Menu::lumiere_static_start
		 */
		add_filter( 'lum_add_rewrite_rules_if_admin', [ $this, 'lum_add_rewrite_rules_if_admin' ], 10, 0 );
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
	 * Add the extra query vars that will be available in URL query string use Get_Options::LUM_POPUP_STRING
	 *
	 * @param array<int, string> $query_vars The array of existing query vars
	 * @return array<int, string> The query vars with the extra ones
	 */
	public function lum_add_query_vars( array $query_vars ): array {
		$query_vars[] = Get_Options::LUM_POPUP_STRING;
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
		$this->logger->log?->debug( '[RewriteRules] Rules added to WP' );
		$this->add_polylang_rules( $wp_rewrite->rules );
		return $wp_rewrite->rules;
	}

	/**
	 * Add rules to polylang, if installed
	 *
	 * @param string[] $existing_rules
	 * @return void
	 */
	private function add_polylang_rules( array $existing_rules ): void {
		if ( has_filter( 'pll_init' ) === false ) {
			return;
		}
		$this->logger->log?->debug( '[RewriteRules] Rules added to Polylang' );
		add_filter(
			'pll_rewrite_rules',
			function( array $existing_rules ): array {
				return array_merge( $existing_rules, [ 'lumiere' ] );
			}
		);
	}

	/**
	 * Add rewrite rules if they're not already in WP options table
	 * Use Get_Options::LUM_REWRITE_RULES as standard Lumiere rules
	 *
	 * @return string[] Array of rewrite rules keyed by their regex pattern.
	 */
	public function lum_add_rewrite_rules_if_admin(): array {

		global $wp_rewrite;

		$wordpress_rewrite_rules = $wp_rewrite->rules ?? null;
		$wordpress_rewrite_rules_db = get_option( 'rewrite_rules' );
		$my_rules_filtered = apply_filters( 'lumiere_rewrite_rules', Get_Options::LUM_REWRITE_RULES );

		// Use standard way if no options were found in rewrite_rules in database, no need for flush
		if (
			! isset( $wordpress_rewrite_rules_db ) // No rule found in DB
			&& isset( $wordpress_rewrite_rules )
			// Created only if the rule doesn't exists, but it seems that it never exists as it's not saved in the database
			&& in_array( array_keys( Get_Options::LUM_REWRITE_RULES ), $wordpress_rewrite_rules, true ) === false
		) {

			$this->logger->log?->notice( '[RewriteRules] Added rewrite rules using WP_Rewrite class' );
			$wp_rewrite->rules = array_merge( $my_rules_filtered, $wordpress_rewrite_rules );
			$this->add_polylang_rules( $my_rules_filtered );
			return $my_rules_filtered;
		}

		// First way failed, so use add_rewrite_rule(), needs flush if rules do not exist
		$rules_added = [];

		foreach ( Get_Options::LUM_REWRITE_RULES as $key => $value ) {
			if (
				isset( $wordpress_rewrite_rules_db )
				&& is_array( $wordpress_rewrite_rules_db ) === true
				// Created only if the rule doesn't exists, so we avoid using flush_rewrite_rules() unecessarily
				&& array_key_exists( $key, $wordpress_rewrite_rules_db ) === false
			) {
				add_rewrite_rule(
					$key,
					$value,
					'top'
				);
				$rules_added[] = $key;
				$this->logger->log?->notice( '[RewriteRules] Added rewrite rules using add_rewrite_rule()' );
			}
		}

		if ( count( $rules_added ) > 0 ) {
			$this->add_polylang_rules( $rules_added );
			$this->need_flush_rules( $rules_added );
		}

		return $rules_added;
	}

	/**
	 * Detect if rules were added previously and abort if not (saves much time)
	 * If rewrite rules don't exist, do a flush_rewrite_rules()
	 * Other plugins may flush and we lose the rules, so this adds them again.
	 * Add rules to polylang if it is installed
	 *
	 * @param array<int, string> $rules_added
	 * @return void
	 */
	private function need_flush_rules( array $rules_added ) {

		flush_rewrite_rules();

		$this->logger->log?->notice(
			'[RewriteRules] Rewrite rules for Lumière was missing, flushed *' . (string) count( $rules_added ) . '* ' . implode( '<br>', $rules_added )
		);
	}
}
