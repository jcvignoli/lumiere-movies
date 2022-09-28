<?php declare( strict_types = 1 );
/**
 * Class Polylang plugin
 * This class offers specific functions if Polylang is in use
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version 1.0
 * @since 3.7.1
 * @package lumiere-movies
 */

namespace Lumiere\Plugins;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'You can not call directly this page' );
}

use Lumiere\Settings;
use Lumiere\Plugins\Logger;

class Polylang {

	// Trait including the database settings.
	use \Lumiere\Settings_Global;

	/**
	 * Class \Lumiere\Logger
	 *
	 */
	public Logger $logger;

	/**
	 * Constant Polylang slug
	 *
	 */
	const POLYLANGSLUG = 'polylang/polylang.php';

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		// Start Logger class.
		$this->logger = new Logger( 'Polylang' );

		// Construct Global Settings trait.
		$this->settings_open();

	}

	/**
	 * Determine whether Polylang is activated
	 *
	 * @return bool true if Polylang plugin is active
	 */
	public function polylang_is_active(): bool {

		if ( function_exists( 'pll_count_posts' ) && is_plugin_active( self::POLYLANGSLUG ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Add the language in use to taxonomy terms ------- In class movie
	 *
	 * @param array<string|int, string|int> $term
	 */
	public function lumiere_polylang_add_lang_to_taxo( array $term ): void {

		// Get the language of the term already registred.
		$term_registred_lang = pll_get_term_language( intval( $term['term_id'] ), 'slug' );
		// Get the language of the page.
		$lang = filter_var( pll_current_language( 'slug' ), FILTER_SANITIZE_STRING ) !== false ? filter_var( pll_current_language( 'slug' ), FILTER_SANITIZE_STRING ) : '';

		// If the language for this term is not already registered, register it.
		// Check current page language, compare against already registred term.
		if ( $term_registred_lang !== $lang ) {

			//      if ( pll_default_language() == $lang )
			//          pll_save_term_translations( array ( $lang, $term_id) );

			pll_set_term_language( intval( $term['term_id'] ), $lang );
			$this->logger->log()->debug(
				'[Lumiere][polylangClass] Taxonomy id ' . $term['term_id'] . ' added to ' . $lang
			);

		}
	}

	/**
	 *  Polylang form: Display a form to change the language if Polylang plugin is active ---- FROM class-taxonomy-people-standard
	 *
	 * @param string $taxonomy -> the current taxonomy to check and build the form according to it
	 * @param string $person_name name of the current person in taxonomy
	 */
	public function lumiere_get_form_polylang_selection( string $taxonomy, string $person_name ): void {

		// Is the current taxonomy, such as "lumiere_actor", registered and activated for translation?
		if ( ! pll_is_translated_taxonomy( $taxonomy ) ) {
			$this->logger->log()->debug( "[Lumiere][taxonomy_$taxonomy][polylang plugin] No activated taxonomy found for $person_name with $taxonomy." );
			return;
		}
		$pll_lang_init = get_terms( 'term_language', [ 'hide_empty' => false ] );
		$pll_lang = is_wp_error( $pll_lang_init ) === false && is_iterable( $pll_lang_init ) ? $pll_lang_init : null;

		if ( ! isset( $pll_lang ) ) {
			$this->logger->log()->debug( "[Lumiere][taxonomy_$taxonomy] No Polylang language is set." );
			return;
		}

		// Build the form.
		echo "\n\t\t\t" . '<div align="center">';
		echo "\n\t\t\t\t" . '<form method="post" id="lang_form" name="lang_form" action="#lang_form">';
		echo "\n\t\t\t\t\t" . '<select name="tag_lang" style="width:100px;">';
		echo "\n\t\t\t\t\t\t" . '<option value="">' . esc_html__( 'All', 'lumiere-movies' ) . '</option>';

		// Build an option html tag for every language.
		foreach ( $pll_lang as $lang ) {

			if ( ( $lang instanceof \WP_Term ) === false ) {
				continue;
			}

			echo "\n\t\t\t\t\t\t" . '<option value="' . intval( $lang->term_id ) . '"';

			// @phpcs:ignore WordPress.Security.NonceVerification
			if ( ( isset( $_POST['tag_lang'] ) ) && ( intval( $lang->term_id ) === intval( $_POST['tag_lang'] ) ) && isset( $_POST['_wpnonce'] ) && ( wp_verify_nonce( $_POST['_wpnonce'], 'submit_lang' ) !== false ) ) {
				echo 'selected="selected"';
			}

			echo '>' . esc_html( ucfirst( $lang->name ) ) . '</option>';

		}
		echo "\n\t\t\t\t\t" . '</select>&nbsp;&nbsp;&nbsp;';
		// @phpcs:ignore WordPress.Security.EscapeOutput
		echo "\n\t\t\t\t\t" . wp_nonce_field( 'submit_lang' );
		if ( function_exists( 'submit_button' ) ) {
			echo "\n\t\t\t\t\t";
			// WP submit_button() doesn't seem to be compatible with AMP plugin
			submit_button( esc_html__( 'Filter language', 'lumiere-movies' ), 'primary', 'submit_lang', false );
		} else {
			echo "\n\t\t\t\t\t" . '<input type="submit" class="button-primary" id="submit_lang" name="submit_lang" value="' . esc_html__( 'Filter language', 'lumiere-movies' ) . '">';
		}
		echo "\n\t\t\t\t" . '</form>';
		echo "\n\t\t\t" . '</div>';

	}

	/**
	 * Copy metas from one post in original language to another post in other language
	 * Polylang version
	 * @TODO: not yet implemented, not sure if needed, maybe not, need further tests
	 * to call it: add_filter('pll_copy_post_metas', 'lumiere_copy_post_metas_polylang', 10, 2)
	 */
	/*
	public function lumiere_copy_post_metas_polylang( $metas, $sync) {

		if(!is_admin()) return false;
		if($sync) return $metas;
		global $current_screen;

		if($current_screen-post_type == 'wine'){ // substitue 'wine' with post type
			$keys = array_key(get_fields($_GET['imdbltid']));
			return array_merge($metas, $keys);
		}

		return $metas;

	}
	*/

}
