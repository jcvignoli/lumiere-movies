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
if ( ( ! defined( 'WPINC' ) ) && ( ! class_exists( '\Lumiere\Settings' ) ) ) {
	wp_die( 'You can not call directly this page' );
}

use Lumiere\Plugins\Logger;
use Lumiere\Settings;

/**
 * Rules for all types of *popups*
 *
 * This class makes sure that Lumière! rewriting rules are
 * 1/ added only if they don't exist in WP options table
 * 2/ Rules for Polylang are always installed (even if Polylang is not)
 * 3/ On closing the class, check if the rules are correctly added. If they aren't, a flush_rewrite_rules() is done
 * @since 3.11
 */
class Rewrite_Rules {

	private Logger $logger_class;

	/**
	 * Rules to be added in add_rewrite_rule()
	 */
	private const LUMIERE_REWRITE_RULES = [
		// All popups
		'lumiere/([^/]+)/?' => 'index.php?popup=$matches[1]',
		// All popups with Polylang
		'([a-zA-Z]{2}\|?+)/?lumiere/([^/]+)/?' => 'index.php?lang=$matches[1]&popup=$matches[2]',
	];

	/**
	 * Query vars to be added in URL query vars strings
	 */
	private const LUMIERE_QUERY_VARS = [ 'popup' ];

	/**
	 * Rules modified to take into account possible change of property $lumiere_urlstring in Settings class
	 * @var array<string, string> $final_array_rules
	 */
	private array $final_array_rules;

	/**
	 * Constructor
	 */
	public function __construct() {

		$this->logger_class = new Logger( 'RewriteRules' );

		$this->final_array_rules = $this->get_real_array_rules( self::LUMIERE_REWRITE_RULES );

		// Add 'popup' as as valid query var in WP query_vars.
		add_filter( 'query_vars', [ $this, 'add_query_vars' ] );

		// Add rewrite rules
		add_action( 'init', [ $this, 'lumiere_add_rewrite_rules' ] );

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
	 * Add the extra query vars that will be available in URL query string use LUMIERE_QUERY_VARS
	 *
	 * @param array<int, string> $query_vars The array of existing query vars
	 * @return array<int, string> The query vars with the extra ones
	 */
	public static function add_query_vars( array $query_vars ): array {
		foreach ( self::LUMIERE_QUERY_VARS as $lumiere_query_var ) {
			$query_vars[] = $lumiere_query_var;
		}
		return $query_vars;
	}

	/**
	 * Rewrite the rules in the keys of LUMIERE_REWRITE_RULES should have $settings_class->lumiere_urlstring been edited by user
	 *
	 * @param array<string, string> $rules
	 * @return array<string, string>
	 */
	public function get_real_array_rules( array $rules ): array {

		$settings_class = new Settings();
		$url_string_trimmed = trim( $settings_class->lumiere_urlstring, '/' );
		$array_key_replaced = [];
		foreach ( $rules as $key => $value ) {
			$new_key = str_replace( 'lumiere', $url_string_trimmed, $key );
			$array_key_replaced[ $new_key ] = $value;
		}
		return $array_key_replaced;
	}

	/**
	 * Add rewrite rules if they're not already in WP options table
	 * For /lumiere/(search|person|movie)/ url string.
	 *
	 * @return void
	 */
	public function lumiere_add_rewrite_rules(): void {

		$wordpress_rewrite_rules = get_option( 'rewrite_rules' );
		$rules_added = [];

		if ( ! isset( $wordpress_rewrite_rules ) || is_bool( $wordpress_rewrite_rules ) ) {
			return;
		}

		foreach ( $this->final_array_rules as $key => $value ) {
			// Created only if the rule doesn't exists, so we avoid using flush_rewrite_rules() unecessarily
			if ( array_key_exists( $key, $wordpress_rewrite_rules ) === false ) {
				add_rewrite_rule(
					$key,
					$value,
					'top'
				);
				$rules_added[] = $key;
			}
		}
		if ( count( $rules_added ) > 0 ) {
			$this->need_flush_rules( $rules_added );
		}
	}

	/**
	 * Add rules to polylang
	 *
	 * @param array<string, string> $existing_rules
	 * @return array<string, string> $rules merged
	 */
	public function add_polylang_rules( array $existing_rules ): array {
		return array_merge( $existing_rules, $this->final_array_rules );
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

		if ( has_filter( 'pll_rewrite_rules' ) ) {
			// add the filter (without '_rewrite_rules') to the Polylang list
			add_filter( 'pll_rewrite_rules', [ $this, 'add_polylang_rules' ] );
			$this->logger_class->log()->notice( '[RewriteRules] Added rewrite rules to Polylang WordPress Plugin' );
		};

		flush_rewrite_rules();

		$this->logger_class->log()->notice(
			'[RewriteRules] Rewrite rules for Lumière was missing, flushed *' . count( $rules_added ) . '* ' . wp_json_encode( $rules_added )
		);
	}
}
