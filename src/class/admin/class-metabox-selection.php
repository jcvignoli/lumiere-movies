<?php declare( strict_types = 1 );
/**
 * Metabox Class
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       2.0
 * @package lumiere-movies
 */

namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	wp_die( esc_html__( 'You can not call directly this page', 'lumiere-movies' ) );
}

use Lumiere\Tools\Settings_Global;

/**
 * Add a metabox in admin post editing interface
 * It saves in metadata the value entered for IMDb title/ID
 * The metabox includes options to display IMDb results for a given IMDb movie ID/movie name
 *
 * @since 4.1 added auto title widget perpost exclusion/inclusion, simplified the class
 * @see \Lumiere\Admin\Admin Calls this class
 * @see \Lumiere\Frontend\Widget_Frontpage Output the metabox selection: movie id/name, auto title widget if not removed on a per-post basis
 *
 * @todo This should be a block!
 */
class Metabox_Selection {

	/**
	 * Traits
	 */
	use Settings_Global;

	/**
	 * Constructor
	 */
	public function __construct() {

		// Get Global Settings class properties.
		$this->get_settings_class();
		$this->get_db_options();
	}

	/**
	 * Static instanciation of the class
	 *
	 * @return void The class was instanciated
	 * @see \Lumiere\Admin call this class
	 */
	public static function lumiere_static_start(): void {
		$metabox_class = new self();

		/**
		 * Register the metabox
		 */
		add_meta_box( 'lumiere_metabox_customfields', __( 'Lumière! widget area', 'lumiere-movies' ), [ $metabox_class, 'lum_show_metabox' ], [ 'post', 'page' ], 'side', 'high' );
		add_action( 'save_post', [ $metabox_class, 'save_custom_meta_box' ], 10, 2 );
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

		// Option for the select, the two type of data to be taken over by imdb-movie.inc.php
		$select_options = [
			__( 'Movie by IMDb ID', 'lumiere-movies' ) => 'lumiere_widget_movieid',
			__( 'Movie by title', 'lumiere-movies' ) => 'lumiere_widget_movietitle',
		];

		?>

		<p>
		
		<div class="lum_metabox_subtitle"><img src="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'lumiere-ico-noir80x80.png' ); ?>" width="20px" valign="middle" />&nbsp;<?php esc_html_e( 'Enter a movie to display', 'lumiere-movies' ); ?></div>

		<div class="lumiere_display_flex lumiere_flex_make_responsive_metabox">

			<div class="lumiere_padding_five">
				<label for="lum_form_type_query"><?php esc_html_e( 'How to query the movie?', 'lumiere-movies' ); ?></label>
				<select id="lum_form_type_query" name="lum_form_type_query">
				<?php
				foreach ( $select_options as $key => $value ) {
					echo '<option value="' . esc_attr( $value ) . '"';
					echo strlen( get_post_meta( $object->ID, $value, true ) ) > 0 ? ' selected' : '';
					echo '>' . esc_attr( $key ) . '</option>';
				}
				?>
				</select>
			</div>

			<div class="lumiere_padding_five">
				<label for="lum_form_query_value"><?php esc_html_e( 'Title or ID:', 'lumiere-movies' ); ?></label>
				<input name="lum_form_query_value" class="lum_width_fillall" type="text" value="<?php echo esc_attr( get_post_meta( $object->ID, 'lumiere_widget_movieid', true ) );
				echo esc_attr( get_post_meta( $object->ID, 'lumiere_widget_movietitle', true ) );?>">
			</div>

		</div>

		<div class="lumiere_padding_five">
			<?php
			echo wp_kses(
				sprintf(
					/* translators: %1$s and %2$s are HTML tags */
					__( 'Use %1$sthe query tool%2$s to find the IMDb\'s ID.', 'lumiere-movies' ),
					'<a class="lum_adm_make_popup" data-lumiere_admin_search_popup="noInfoNeeded" title="' . esc_html__( 'Open a popup to search the movie\'s ID', 'lumiere-movies' ) . '">',
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

		<div class="lum_metabox_subtitle"><img src="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'lumiere-ico-noir80x80.png' ); ?>" width="20px" valign="middle" />&nbsp;<?php esc_html_e( 'Extra options', 'lumiere-movies' ); ?></div>
		
		<div class="lumiere_padding_five">
			
			<input id="lumiere_autotitlewidget_perpost" name="lumiere_autotitlewidget_perpost" type="checkbox" value="disabled"<?php echo get_post_meta( $object->ID, 'lumiere_autotitlewidget_perpost', true ) === 'disabled' ? ' checked' : ''; ?>>
			<label class="lum_metabox_label_checkbox" for="lumiere_autotitlewidget_perpost">
			<?php
			echo wp_kses(
				sprintf(
					/* translators: %1$s and %2$s are HTML tags */
					__( 'Deactivate %1$sauto title widget%2$s for this post', 'lumiere-movies' ),
					'<a id="lin_to_imdbautopostwidget" href="' . admin_url( 'admin.php?page=lumiere_options&subsection=advanced#imdbautopostwidget' ) . '">',
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

		$lum_form_type_query = isset( $_POST['lum_form_type_query'] ) ? sanitize_text_field( wp_unslash( $_POST['lum_form_type_query'] ) ) : null;
		$lum_form_query_value = isset( $_POST['lum_form_query_value'] ) ? sanitize_text_field( wp_unslash( $_POST['lum_form_query_value'] ) ) : null;
		$lumiere_autotitlewidget_perpost = isset( $_POST['lumiere_autotitlewidget_perpost'] ) ? sanitize_text_field( wp_unslash( $_POST['lumiere_autotitlewidget_perpost'] ) ) : 'enabled';

		// Create or update the metas.
		if ( isset( $lum_form_query_value ) && isset( $lum_form_type_query ) ) {
			update_post_meta( $post_id, $lum_form_type_query, $lum_form_query_value );
		}
		if ( strlen( $lumiere_autotitlewidget_perpost ) > 0 ) {
			update_post_meta( $post_id, 'lumiere_autotitlewidget_perpost', $lumiere_autotitlewidget_perpost );
		}

		// Delete the other custom data.
		if ( $lum_form_type_query === 'lumiere_widget_movieid' ) {
			delete_post_meta( $post_id, 'lumiere_widget_movietitle' );
		}
		if ( $lum_form_type_query === 'lumiere_widget_movietitle' ) {
			delete_post_meta( $post_id, 'lumiere_widget_movieid' );
		}

		// Delete every custom data if no value was passed.
		if ( ! isset( $lum_form_query_value ) || strlen( $lum_form_query_value ) === 0 ) {
			delete_post_meta( $post_id, 'lumiere_widget_movieid' );
			delete_post_meta( $post_id, 'lumiere_widget_movietitle' );
		}
	}
}

