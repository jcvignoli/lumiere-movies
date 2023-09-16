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
	 * Number of rules related to lumiere found in db
	 */
	private int $lumiere_nb_rules_found;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->logger_class = new Logger( 'RewriteRules' );
		$this->lumiere_nb_rules_found = 0;
		add_action( 'init', [ $this, 'lumiere_add_rewrite_rules' ] );
	}

	/**
	 * Static instanciation of the class
	 * Needed to be called in add_actions()
	 *
	 * @return void The class was instanciated
	 */
	public static function lumiere_rewrite_start(): void {
		$rewrite_class = new self();
	}

	/**
	 * Add rewrite rules if they're not already in WP options table
	 * For /lumiere/(search|person|movie)/ url string.
	 *
	 * @return void
	 */
	public function lumiere_add_rewrite_rules(): void {

		$wordpress_rewrite_rules = get_option( 'rewrite_rules' );

		foreach ( self::LUMIERE_REWRITE_RULES as $key => $value ) {
			// Created only if the rule doesn't exists, so we avoid using flush_rewrite_rules() unecessarily
			if ( ! isset( $wordpress_rewrite_rules [ $key ] ) ) {
				add_rewrite_rule(
					$key,
					$value,
					'top'
				);
				$this->lumiere_nb_rules_found++;
			}
		}
	}

	/**
	 * Destructor
	 *
	 * Detect if rules were added previously and abort if not (saves much time)
	 * If rewrite rules don't exist, do a flush_rewrite_rules()
	 * Other plugins may flush and we lose the rules, so this adds them again.
	 *
	 * @return void
	 */
	public function __destruct() {

		if ( $this->lumiere_nb_rules_found === 0 ) {
			return;
		}

		flush_rewrite_rules();
		$this->logger_class->log()->debug( $this->lumiere_nb_rules_found . 'Rewrite rules for Lumière were missing, flushed' );
	}
}
