<?php declare( strict_types = 1 );
/**
 * Metabox Class
 *
 * @copyright (c) 2021, Lost Highway
 *
 * @version       3.0
 * @package       lumieremovies
 */

namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Config\Open_Options;
use Lumiere\Config\Get_Options;

/**
 * Add a metabox in admin post editing interface
 * It saves in metadata the value entered for IMDb title/ID
 * The metabox includes options to display IMDb results for a given IMDb movie ID/movie name
 * Use blocks instead of this class if gutenberg is available
 *
 * @since 4.1 added auto title widget perpost exclusion/inclusion, simplified the class
 * @since 4.6.1 Use this class for pre-gutenberg, blocks for post-gutenberg
 *
 * @see \Lumiere\Admin\Admin Calls this class
 * @see \Lumiere\Frontend\Widget_Frontpage Output the metabox selection: movie id/name, auto title widget if not removed on a per-post basis
 */
class Metabox_Selection {

	/**
	 * Traits
	 */
	use Open_Options;

	/**
	 * Store the custom meta post values
	 * @var array<string, string>
	 */
	private array $custom_meta_selection = [];

	/**
	 * Constructor
	 */
	public function __construct() {
		// Get global settings class properties.
		$this->get_db_options(); // In Open_Options trait.

		// Option for the select, starts with '_' and ends with '_widget'
		$this->custom_meta_selection = Get_Options::get_lum_all_type_search_metabox(); // ie: _lum_person_id_widget.
	}

	/**
	 * Instanciation of the class
	 *
	 * @return void The class was instanciated
	 * @see \Lumiere\Admin call this class
	 */
	public static function init(): void {

		$that = new self();

		/**
		 * Register the metabox
		 * Pre-5.8 WordPress, since we now use blocks {@see Metabox_Selection::register_post_meta_sidebar()}
		 */
		add_meta_box(
			'lumiere_metabox_customfields',
			__( 'Lumière! widget area', 'lumiere-movies' ),
			[ $that, 'lum_show_metabox' ],
			[ 'post', 'page' ],
			'side',
			'high',
			[ '__back_compat_meta_box' => true ] // Remove the metabox if post-gutenberg (post-5.8 WordPress).
		);
		add_action( 'save_post', [ $that, 'save_custom_meta_box' ], 10, 2 );
	}

	/**
	 * Register the sidebar's custom meta post data in Post settings
	 * Only works with Gutenberg active (post-5.8 WordPress)
	 * Method completely independent of the rest of the class
	 *
	 * @see \Lumiere\Admin Call this method
	 * @see Block Post-options needs it
	 * @return void Custom meta post data are registered
	 */
	public static function register_post_meta_sidebar(): void {
		// Extra selection for the type of query, agregating all selection in Get_Options::get_lum_all_type_search_metabox(). Utilised in post/widget options.
		register_post_meta(
			'post',
			'_lum_form_type_query',
			[
				'show_in_rest'      => true,
				'single'            => true,
				'sanitize_callback' => 'sanitize_text_field',
				'type'              => 'string',
				'auth_callback'     => function() {
					return current_user_can( 'manage_options' );
				},
			]
		);
		// Option for the select, add '_widget' to the value column
		foreach ( Get_Options::get_lum_all_type_search_metabox() as $key => $value ) {
			register_post_meta(
				'post',
				$value,
				[
					'show_in_rest'      => true,
					'single'            => true,
					'type'              => 'string',
					'default'           => '',
					'sanitize_callback' => 'sanitize_text_field',
					'auth_callback'     => function() {
						return current_user_can( 'manage_options' );
					},
				]
			);
		}

		// Extra selection for autotitle. Utilised in post/widget options.
		register_post_meta(
			'post',
			Get_Options::LUM_AUTOTITLE_METADATA_FIELD_NAME,
			[
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'boolean',
				'default'       => '0',
				'auth_callback' => function() {
					return current_user_can( 'manage_options' );
				},
			]
		);

	}

	/**
	 * Output in the edition
	 *
	 * @param \WP_Post $object Saved values from database.
	 *
	 * @since 4.1 simplified the use of variables, changed 'imdb-movie-widget' by 'lumiere_widget_movietitle' and 'imdb-movie-widget-bymid' by 'lumiere_widget_movieid'
	 */
	public function lum_show_metabox( \WP_Post $object ): void {

		wp_nonce_field( basename( __FILE__ ), 'lum_metabox_nonce' );
		?>

		<p>
		
		<div class="lum_metabox_subtitle"><img src="<?php echo esc_url( Get_Options::LUM_PICS_URL . 'lumiere-ico-noir80x80.png' ); ?>" width="20px" valign="middle" />&nbsp;<?php esc_html_e( 'Select items to display', 'lumiere-movies' ); ?></div>

		<div class="lumiere_display_flex lumiere_flex_make_responsive_metabox">

			<div class="lumiere_padding_five">
				<label for="lum_form_type_query"><?php esc_html_e( 'How to query the items in the widget?', 'lumiere-movies' ); ?></label>
				<br>
				<select id="lum_form_type_query" name="lum_form_type_query">
				<?php
				foreach ( $this->custom_meta_selection as $key => $value ) {
					echo '<option value="' . esc_attr( $value ) . '"';
					echo strlen( get_post_meta( $object->ID, $value, true ) ) > 0 ? ' selected' : '';
					echo '>' . esc_attr( $key ) . '</option>';
				}
				?>
				</select>
			</div>

			<div class="lumiere_padding_five">
				<label for="lum_form_query_value"><?php esc_html_e( 'Title/Name or ID:', 'lumiere-movies' ); ?></label>
				<input id="lum_form_query_value" name="lum_form_query_value" class="lum_width_fillall" type="text" value="<?php
				foreach ( $this->custom_meta_selection as $key => $value ) {
					echo esc_attr( get_post_meta( $object->ID, $value, true ) );
				}
				?>">
			</div>

		</div>

		<div class="lumiere_padding_five">
			<?php
			echo wp_kses(
				wp_sprintf(
					/* translators: %1$s and %2$s are HTML tags */
					__( 'Use %1$sthe query tool%2$s to find the IMDb\'s ID.', 'lumiere-movies' ),
					'<a class="lum_adm_make_popup" data-lumiere_admin_search_popup="noInfoNeeded" title="' . esc_html__( 'Open a popup to search the movie or person ID', 'lumiere-movies' ) . '">',
					'</a>'
				),
				[
					'a' => [
						'id' => [],
						'class' => [],
						'data-lumiere_admin_search_popup' => [],
						'title' => [],
					],
				]
			); ?> 
		</div>
		
		<?php if ( $this->imdb_admin_values['imdbautopostwidget'] === '1' ) { ?>

		<div class="lum_metabox_subtitle"><img src="<?php echo esc_url( Get_Options::LUM_PICS_URL . 'lumiere-ico-noir80x80.png' ); ?>" width="20px" valign="middle" />&nbsp;<?php esc_html_e( 'Extra options', 'lumiere-movies' ); ?></div>
		
		<div class="lumiere_padding_five">
			
			<input id="<?php echo esc_attr( Get_Options::LUM_AUTOTITLE_METADATA_FIELD_NAME ); ?>" name="<?php echo esc_attr( Get_Options::LUM_AUTOTITLE_METADATA_FIELD_NAME ); ?>" type="checkbox" <?php echo get_post_meta( $object->ID, Get_Options::LUM_AUTOTITLE_METADATA_FIELD_NAME, true ) === '1' ? 'value="0" checked' : 'value="1"'; ?>>
			<label class="lum_metabox_label_checkbox" for="<?php echo esc_attr( Get_Options::LUM_AUTOTITLE_METADATA_FIELD_NAME ); ?>">
			
			<?php
			echo wp_kses(
				wp_sprintf(
					/* translators: %1$s and %2$s are HTML tags */
					__( 'Deactivate %1$sauto title widget%2$s for this post', 'lumiere-movies' ),
					'<a id="link_to_imdbautopostwidget" href="' . admin_url( 'admin.php?page=lumiere_options&subsection=advanced#imdbautopostwidget' ) . '">',
					'</a>'
				),
				[
					'a' => [
						'href' => [],
						'id' => [],
						'title' => [],
					],
				]
			); ?>
			</label>
		</div>
		
		<?php } ?>

		</p><!-- #lumiere-movies -->

		<?php
	}

	/**
	 * Save the data in the database
	 *
	 * @param int $post_id ID of the post
	 * @param \WP_Post $post the post
	 *
	 * @since 4.1 removed the condition that only posts can save the widget
	 */
	public function save_custom_meta_box( int $post_id, \WP_Post $post ): void {

		if ( ! isset( $_POST['lum_metabox_nonce'] ) || wp_verify_nonce( sanitize_key( $_POST['lum_metabox_nonce'] ), basename( __FILE__ ) ) === false ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		/** @psalm-suppress UndefinedConstant, RedundantCondition -- Psalm can't deal with dynamic constants */
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Only pages and posts include widgets, so exit if it's not the case.
		/*if ( $post->post_type !== 'post' && $post->post_type !== 'page' ) {
			return;
		}*/

		$post = sanitize_post( $_POST );
		$lum_form_type_query = $post['lum_form_type_query'] ?? null;
		$lum_form_query_value = $post['lum_form_query_value'] ?? null;
		/** @psalm-var '_lumiere_autotitlewidget_perpost'|string @lumiere_autotitlewidget_perpost */
		$lumiere_autotitlewidget_perpost = $post[ Get_Options::LUM_AUTOTITLE_METADATA_FIELD_NAME ] ?? false;

		// Create or update the metas.
		if ( isset( $lum_form_query_value ) && isset( $lum_form_type_query ) ) {
			update_post_meta( $post_id, $lum_form_type_query, $lum_form_query_value );
		}

		// Detect if auto title is active, add '0' otherwise
		if ( $lumiere_autotitlewidget_perpost === '1' ) { // Is supposed to be '1'|'0'
			update_post_meta( $post_id, Get_Options::LUM_AUTOTITLE_METADATA_FIELD_NAME, $lumiere_autotitlewidget_perpost );
		} else {
			update_post_meta( $post_id, Get_Options::LUM_AUTOTITLE_METADATA_FIELD_NAME, '0' );
		}

		// Delete all custom post fields but the one passed in $_POST.
		foreach ( $this->custom_meta_selection as $key => $value ) {
			if ( $lum_form_type_query === $value ) {
				continue;
			}
			delete_post_meta( $post_id, $value );
		}

		// Delete all custom post fields if no value was passed.
		if ( ! isset( $lum_form_query_value ) || strlen( $lum_form_query_value ) === 0 ) {
			foreach ( $this->custom_meta_selection as $key => $value ) {
				delete_post_meta( $post_id, $value );
			}
		}
	}
}

