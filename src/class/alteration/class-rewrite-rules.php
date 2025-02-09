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
 * Rules for all types of *popups*
 *
 * This class makes sure that Lumière! rewriting rules are
 * 1/ added only if they don't exist in WP options table
 * 2/ Rules for Polylang are always installed (even if Polylang is not)
 * 3/ On closing the class, check if the rules are correctly added. If they aren't, a flush_rewrite_rules() is done
 * @since 3.11
 * @see \Lumiere\Frontend\Popups Folder that includes the popup classes
  * @phpstan-import-type OPTIONS_ADMIN from \Lumiere\Config\Settings
 */
class Rewrite_Rules {

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
	 * @var array<string, string> $imdb_admin_option
	 * @phpstan-var OPTIONS_ADMIN $imdb_admin_option
	 */
	private array $imdb_admin_option;

	/**
	 * Constructor
	 */
	public function __construct(
		private Logger $logger_class = new Logger( 'RewriteRules' ),
	) {
		/** @phpstan-var OPTIONS_ADMIN|false $database_options */
		$database_options = get_option( Get_Options::get_admin_tablename() );

		if ( is_array( $database_options ) === false ) {
			$this->logger_class->log->info( '[Lumiere][RewriteRules] Admin options in database are not available, probably first install, exit' );
			return;
		}

		$this->imdb_admin_option = $database_options;

		$this->final_array_rules = $this->get_real_array_rules( self::LUMIERE_REWRITE_RULES );

		// Add 'popup' as as valid query var in WP query_vars.
		add_filter( 'query_vars', [ $this, 'add_query_vars' ] );

		// Add rewrite rules
		add_action( 'admin_init', [ $this, 'lumiere_add_rewrite_rules' ] );

		/* @TODO Should use the way, way easier
		add_filter( 'generate_rewrite_rules', function ( $wp_rewrite ) {
				$wp_rewrite->rules = array_merge(
					self::LUMIERE_REWRITE_RULES,
					$wp_rewrite->rules
				);
			}
		);*/
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
	 * Rewrite the rules in the keys of LUMIERE_REWRITE_RULES should have $this->imdb_admin_option['imdburlpopups'] been edited by user
	 *
	 * @param array<string, string> $rules
	 * @return array<string, string>
	 */
	public function get_real_array_rules( array $rules ): array {

		$url_string_trimmed = trim( $this->imdb_admin_option['imdburlpopups'], '/' );
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

		global $wp_rewrite;

		$wordpress_rewrite_rules = $wp_rewrite->rules ?? null;
		$wordpress_rewrite_rules_db = get_option( 'rewrite_rules' );
		$my_rules_filtered = apply_filters( 'lumiere_rewrite_rules', $this->final_array_rules );

		// Use standard way if no options were found in rewrite_rules in database, no need for flush
		if (
			! isset( $wordpress_rewrite_rules_db ) // No rule found in DB
			&& isset( $wordpress_rewrite_rules )
			// Created only if the rule doesn't exists, but it seems that it never exists as it's not saved in the database
			&& in_array( array_keys( $this->final_array_rules ), $wordpress_rewrite_rules, true ) === false
		) {

			$this->logger_class->log->notice( '[Lumiere][RewriteRules] Added rewrite rules using WP_Rewrite class' );
			$wp_rewrite->rules = array_merge( $my_rules_filtered, $wordpress_rewrite_rules );
			$this->add_polylang_rules( $my_rules_filtered );
			return;
		}

		// First way failed, so use add_rewrite_rule(), needs flush if rules do not exist
		$rules_added = [];

		foreach ( $this->final_array_rules as $key => $value ) {
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
				$this->logger_class->log->notice( '[Lumiere][RewriteRules] Added rewrite rules using add_rewrite_rule()' );
			}
		}

		if ( count( $rules_added ) > 0 ) {
			$this->add_polylang_rules( $my_rules_filtered );
			$this->need_flush_rules( $rules_added );
		}
	}

	/**
	 * Add rules to polylang, if installed
	 *
	 * @param array<string, string> $existing_rules
	 * @return void
	 */
	public function add_polylang_rules( array $existing_rules ): void {
		if ( has_filter( 'pll_init' ) === true ) {
			$this->logger_class->log->debug( '[Lumiere][RewriteRules] Rules added to Polylang' );
			add_filter(
				'pll_rewrite_rules',
				function( array $existing_rules ): array {
					return array_merge( $existing_rules, [ 'lumiere' ] );
				}
			);
		};
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

		$this->logger_class->log->notice(
			'[Lumiere][RewriteRules] Rewrite rules for Lumière was missing, flushed *' . count( $rules_added ) . '* ' . implode( '<br>', $rules_added )
		);
	}
}
