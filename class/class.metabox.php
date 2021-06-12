<?php

 #############################################################################
 # Lumière! Movies wordpress plugin                                          #
 # written by Lost Highway                                                   #
 # https://www.jcvignoli.com/blog                                            #
 # ------------------------------------------------------------------------- #
 # This program is free software; you can redistribute and/or modify it      #
 # under the terms of the GNU General Public License (see LICENSE)           #
 # ------------------------------------------------------------------------- #
 #									              #
 #  Class : Add a metabox in admin post editing interface                    #
 #									              #
 #############################################################################

namespace Lumiere;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die('You can not call directly this page');
}

class LumiereMetabox {

	/**
	 * Constructor. Sets up the metabox
	 */
	function __construct() {

		/**
		* Register the metabox
		*/
		add_action("add_meta_boxes", [$this, 'add_lumiere_metabox_customfields' ]);
		add_action("save_post", [$this, "save_custom_meta_box" ], 10, 3);
	}


	public function lumiere_metabox_customfields() {}

	public function add_lumiere_metabox_customfields() {

		global $imdb_admin_values;

		add_meta_box(  'lumiere_metabox_customfields', '<div>'.'<img class="lumiere_valign_middle" width="20" height="20" src="' . esc_url( $imdb_admin_values['imdbplugindirectory'] . 'pics/lumiere-ico-noir13x13.png' ) . '" />&nbsp;'.'Lumière! custom data'.'</div>' , [$this, 'custom_meta_box_markup'], array( 'post', 'page'), 'side', 'high', null ); 

	}

	/**
	 * Output in the edition 
	 *
	 *
	 * @param array $object Saved values from database.
	 */
	public function custom_meta_box_markup($object) {

		global $imdb_admin_values;
		
		// Option for the select, the two type of data to be taken over by imdb-movie.inc.php
		$option_values = array( esc_html__( 'By movie\'s IMDb ID', 'lumiere-movies') => 'imdb-movie-widget-bymid', esc_html__( 'By movie\'s title', 'lumiere-movies') => 'imdb-movie-widget');

		wp_nonce_field( basename(__FILE__), 'lumiere_metabox_nonce'); ?>

		<p>

		<div class="lumiere_padding_five">
			<?php esc_html_e( 'The movie you enter will be shown in your widget area.', 'lumiere-movies'); ?>
		</div>

		<div class="lumiere_display_flex">

			<div class="lumiere_padding_five">
				<label for="lumiere_queryid_widget"><?php esc_html_e( 'How to query the movie?', 'lumiere-movies'); ?></label>
				<select id="lumiere_queryid_widget" name="lumiere_queryid_widget">
				<?php 
				foreach($option_values as $key => $value) {
					if($value == get_post_meta($object->ID, 'lumiere_queryid_widget', true)){
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
				<label for="lumiere_queryid_widget_input"><?php esc_html_e( 'Title or ID:', 'lumiere-movies'); ?></label>
				<input name="lumiere_queryid_widget_input" class="imdblt_width_fillall" type="text" value="<?php echo get_post_meta($object->ID, "lumiere_queryid_widget_input", true); ?>">
			</div>

		</div>

		<div class="lumiere_padding_five">
			<?php esc_html_e( 'Use ', 'lumiere-movies'); ?><a id="lumiere_open_search_popup" class="linkpopup" data-lumiere_admin_popup="no data" title="<?php esc_html_e( 'Open a popup to search the movie\'s ID', 'lumiere-movies'); ?>"><?php esc_html_e( 'this tool ', 'lumiere-movies'); ?></a><?php esc_html_e( 'to find the IMDb\'s ID.', 'lumiere-movies'); ?> 
		</div>

		</p><!-- #lumiere-movies -->

<?php  
	}


	/**
	 * Save the data to the database
	 *
	 *
	 * @param string $post_id id of the post
	 * @param string $post
	 * @param string $update
	 */

	function save_custom_meta_box($post_id, $post, $update)	{

		if (!isset($_POST["lumiere_metabox_nonce"]) || !wp_verify_nonce($_POST["lumiere_metabox_nonce"], basename(__FILE__)))
			 return $post_id;

		if(!current_user_can("edit_post", $post_id))
			return $post_id;

		if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
			return $post_id;

		$slug = "post";
		if($slug != $post->post_type)
			return $post_id;

		$meta_box_text_value = "";
		$meta_box_dropdown_value = "";
		$lumiere_metabox_submit = "";

		/* Save the metabox data so the post will keep the data */
		if(isset($_POST["lumiere_queryid_widget_input"]))
			$meta_box_text_value = sanitize_text_field( $_POST["lumiere_queryid_widget_input"] );

		update_post_meta($post_id, "lumiere_queryid_widget_input", $meta_box_text_value);

		if(isset($_POST["lumiere_queryid_widget"]))
			$meta_box_dropdown_value = sanitize_text_field( $_POST["lumiere_queryid_widget"] );

		update_post_meta($post_id, "lumiere_queryid_widget", $meta_box_dropdown_value);

		/* Create the custom field */

		// Get the type of field, either "imdb-movie-widget-bymid" or "imdb-movie-widget"
		$lumiere_metabox_submit = sanitize_text_field( $_POST['lumiere_queryid_widget'] );

		// Save imdb-movie-widget with the posted data, delete imdb-movie-widget-bymid
		if ( $lumiere_metabox_submit == 'imdb-movie-widget' ){
			update_post_meta($post_id, 'imdb-movie-widget', sanitize_text_field( $_POST['lumiere_queryid_widget_input'] ));
			delete_post_meta($post_id, 'imdb-movie-widget-bymid');
		}

		// Save imdb-movie-widget-bymid with the posted data, delete imdb-movie-widget
		if ( $lumiere_metabox_submit == 'imdb-movie-widget-bymid' ) {
			update_post_meta($post_id, 'imdb-movie-widget-bymid', sanitize_text_field( $_POST['lumiere_queryid_widget_input'] ) );
			delete_post_meta($post_id, 'imdb-movie-widget');
		}
	}
}
$my_metabox = new \Lumiere\LumiereMetabox();


	
?>
