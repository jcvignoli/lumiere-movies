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
 * @see \Lumiere\Admin Calls this class
 */
class Metabox_Selection {

	/**
	 * Traits
	 */
	use Settings_Global;

	/**
	 * List of options available in the select form
	 *
	 * @var array<string, string> $select_options
	 */
	private array $select_options;

	/**
	 * Constructor
	 */
	public function __construct() {

		// Get Global Settings class properties.
		$this->get_settings_class();
		$this->get_db_options();

		// Option for the select, the two type of data to be taken over by imdb-movie.inc.php
		$this->select_options = [
			esc_html__( 'By movie IMDb ID', 'lumiere-movies' ) => 'imdb-movie-widget-bymid',
			esc_html__( 'By movie title', 'lumiere-movies' ) => 'imdb-movie-widget',
		];

		/**
		 * Register the metabox
		 */
		add_action( 'add_meta_boxes', [ $this, 'add_lumiere_metabox_customfields' ] );
		add_action( 'save_post', [ $this, 'save_custom_meta_box' ], 10, 2 );
	}

	/**
	 * Static instanciation of the class
	 *
	 * @return void The class was instanciated
	 * @see \Lumiere\Admin call this class
	 */
	public static function lumiere_static_start(): void {
		$metabox_class = new self();
	}

	public function lumiere_metabox_customfields(): void {}

	public function add_lumiere_metabox_customfields(): void {

		add_meta_box( 'lumiere_metabox_customfields', 'Lumière! movies', [ $this, 'custom_meta_box_markup' ], [ 'post', 'page' ], 'side', 'high' );

	}

	/**
	 * Output in the edition
	 *
	 * @param \WP_Post $object Saved values from database.
	 */
	public function custom_meta_box_markup( \WP_Post $object ): void {

		wp_nonce_field( basename( __FILE__ ), 'lumiere_metabox_nonce' ); ?>

		<p>

		<div class="lumiere_padding_five lumiere_flex_container">
			<div class="lumiere_flex_container_content_twenty">
			<img src="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'lumiere-ico-noir80x80.png' ); ?>" width="40px" valign="middle" />
			</div>
			<div class="lumiere_flex_container_content_eighty"><?php
				esc_html_e( 'The movie you enter will be shown in your widget area.', 'lumiere-movies' );
			?></div>
		</div>

		<div class="lumiere_display_flex lumiere_flex_make_responsive_metabox">

			<div class="lumiere_padding_five">
				<label for="lumiere_queryid_widget"><?php esc_html_e( 'How to query the movie?', 'lumiere-movies' ); ?></label>
				<select id="lumiere_queryid_widget" name="lumiere_queryid_widget">
				<?php
				foreach ( $this->select_options as $key => $value ) {
					if ( $value === get_post_meta( $object->ID, 'lumiere_queryid_widget', true ) ) {
						?>
					<option value="<?php echo esc_attr( $value ); ?>" selected><?php echo esc_attr( $key ); ?></option>
						<?php
					} else {
						?>
					<option value="<?php echo esc_attr( $value ); ?>" ><?php echo esc_attr( $key ); ?></option>
						<?php
					}
				}
				?>
				</select>
			</div>

			<div class="lumiere_padding_five">
				<label for="lumiere_queryid_widget_input"><?php esc_html_e( 'Title or ID:', 'lumiere-movies' ); ?></label>
				<input name="lumiere_queryid_widget_input" class="lum_width_fillall" type="text" value="<?php echo esc_attr( get_post_meta( $object->ID, 'lumiere_queryid_widget_input', true ) ); ?>">
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

		</p><!-- #lumiere-movies -->

		<?php
	}

	/**
	 * Save the data in the database
	 *
	 * @param int $post_id ID of the post
	 * @param \WP_Post $post the post
	 */
	public function save_custom_meta_box( int $post_id, \WP_Post $post ): void {

		if ( ! isset( $_POST['lumiere_metabox_nonce'] ) || wp_verify_nonce( $_POST['lumiere_metabox_nonce'], basename( __FILE__ ) ) === false ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( $post->post_type !== 'post' ) {
			return;
		}

		$meta_box_text_value = '';
		$meta_box_dropdown_value = '';
		$lumiere_metabox_submit = '';

		/* Save the metabox data so the post will keep the data */
		if ( isset( $_POST['lumiere_queryid_widget_input'] ) ) {
			$meta_box_text_value = sanitize_text_field( $_POST['lumiere_queryid_widget_input'] );
		}

		update_post_meta( $post_id, 'lumiere_queryid_widget_input', $meta_box_text_value );

		if ( isset( $_POST['lumiere_queryid_widget'] ) ) {
			$meta_box_dropdown_value = sanitize_text_field( $_POST['lumiere_queryid_widget'] );
		}

		update_post_meta( $post_id, 'lumiere_queryid_widget', $meta_box_dropdown_value );

		/* Create the custom field */

		// Get the type of field, either "imdb-movie-widget-bymid" or "imdb-movie-widget"
		$lumiere_metabox_submit = sanitize_text_field( $_POST['lumiere_queryid_widget'] ?? '' );

		// Save imdb-movie-widget with the posted data, delete imdb-movie-widget-bymid
		if ( $lumiere_metabox_submit === 'imdb-movie-widget' ) {
			update_post_meta( $post_id, 'imdb-movie-widget', sanitize_text_field( $_POST['lumiere_queryid_widget_input'] ?? '' ) );
			delete_post_meta( $post_id, 'imdb-movie-widget-bymid' );
		}

		// Save imdb-movie-widget-bymid with the posted data, delete imdb-movie-widget
		if ( $lumiere_metabox_submit === 'imdb-movie-widget-bymid' ) {
			update_post_meta( $post_id, 'imdb-movie-widget-bymid', sanitize_text_field( $_POST['lumiere_queryid_widget_input'] ?? '' ) );
			delete_post_meta( $post_id, 'imdb-movie-widget' );
		}

		// Delete the custom data if removed
		if ( ( ! isset( $_POST['lumiere_queryid_widget_input'] ) ) || ( strlen( $_POST['lumiere_queryid_widget_input'] ) < 1 ) ) {
			delete_post_meta( $post_id, 'imdb-movie-widget-bymid' );
			delete_post_meta( $post_id, 'imdb-movie-widget' );
		}

	}
}

