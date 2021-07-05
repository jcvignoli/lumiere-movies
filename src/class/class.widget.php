<?php

 #############################################################################
 # Lumière Movies wordpress plugin                                           #
 # written by Lost Highway                                                   #
 # https://www.jcvignoli.com/blog                                            #
 # ------------------------------------------------------------------------- #
 # This program is free software; you can redistribute and/or modify it      #
 # under the terms of the GNU General Public License (see LICENSE)           #
 # ------------------------------------------------------------------------- #
 #									              #
 #  Class : Add widget function                                              #
 #									              #
 #############################################################################


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die('You can not call directly this page');
}


class LumiereWidget extends WP_Widget {


	/**
	 * Constructor. Sets up the widget name, description, etc.
	 */
	function __construct() {

		global $imdb_admin_values;

		 parent::__construct(
			'lumiere-movies-widget',  // Base ID
			'Lumière! Movies',   // Name
			array( 'description' => __( 'Add movie details to your pages with Lumière!', 'lumiere-movies' ))
		 );

		/**
		 * Register the widget. Should be hooked to 'widgets_init'.
		 */
		 add_action( 'widgets_init', function() {
			register_widget( 'LumiereWidget' );
		 });

	}


	public $args = array(
		'before_title'  => '', /*'<h4 class="widgettitle">',*/
		'after_title'   => '', /*'</h4>',*/
		'before_widget' => '', /*'<div class="widget-wrap">',*/
		'after_widget'  => '', /*'</div></div>'*/
	);
 
	/**
	 * Front end output
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */

	public function widget( $args, $instance ) {
  
		global $imdb_admin_values, $imdballmeta;

		extract($args);

		// full title
		$title_box = empty($instance['title']) ? esc_html__('IMDb data', 'lumiere-movies') : $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title']; 

		// Initialize var for id/name of the movie to display
		$imdballmeta=array();

		$post_id = intval( get_the_ID() );

		// shows widget only for a post or a page
		if ( (is_single()) || ( is_page()) )  {


			//------ 

			if ( (isset($imdb_admin_values['imdbautopostwidget'])) && ($imdb_admin_values['imdbautopostwidget'] == true) ) {
				// Display the movie according to the post's title (setting in -> general -> advanced)
				$imdballmeta[]['byname'] = sanitize_text_field( get_the_title() ); # this var is global and sent to class.movie.php

			}

			//------  show widget only if custom field 'imdb-movie-widget' or 'imdb-movie-widget-bymid' is found
			if ( (get_post_meta($post_id, 'imdb-movie-widget', false)) || (get_post_meta($post_id, 'imdb-movie-widget-bymid', false)) ) {

				// "imdb-movie-widget"

				foreach (get_post_meta($post_id, 'imdb-movie-widget', false) as $key => $value) {

					$imdballmeta[]['byname'] = sanitize_text_field($value); # this var is global and sent to class.movie.php

				}

				// imdb-movie-widget-bymid" (with proper movie ID)

				foreach (get_post_meta($post_id, 'imdb-movie-widget-bymid', false) as $key => $value) {

					$moviespecificid = $value;
					$imdballmeta[]['bymid'] = $moviespecificid; # this var is global and sent to class.movie.php

				}

				// if imdbautopostwidget is set, the count has to be decreased
				$count_total = ($imdb_admin_values['imdbautopostwidget'] == true) ? (count( $imdballmeta ) -1) : count( $imdballmeta );
				for ($i=0; $i < $count_total; $i++) {

					echo $args['before_widget'];

					echo $title_box;

					$display = new \Lumiere\LumiereMovies();
					if ($output = $display->lumiere_result)
						echo $output;

					echo $args['after_widget'];
				}

			}
		}
	}

	/**
	* Back-end widget form.
	*
	* @see WP_Widget::form()
	*
	* @param array $instance Previously saved values from database.
	*/
	public function form( $instance ) {

		global $imdb_admin_values;

		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Lumière! Movies', 'lumiere-movies' ); 
		$lumiere_query_widget = ! empty( $instance['lumiere_queryid_widget'] ) ? $instance['lumiere_queryid_widget'] : '';
		$lumiere_queryid_widget_input = ! empty( $instance['lumiere_queryid_widget_input'] ) ? $instance['lumiere_queryid_widget_input'] : ''; ?>

		<p class="lumiere_padding_ten">

		<div class="lumiere_display_inline_flex">
			<div class="lumiere_padding_ten">
				<img class="lumiere_flex_auto" width="40" height="40" src="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] . 'pics/lumiere-ico-noir80x80.png'); ?>" />
			</div>

			<div class="lumiere_flex_auto">
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Widget title:', 'lumiere-movies' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			</div>
		</div>
		</p><!-- #lumiere-movies -->

<?php

	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();

		$instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['lumiere_queryid_widget'] = ( !empty( $new_instance['lumiere_queryid_widget'] ) ) ? $new_instance['lumiere_queryid_widget'] : '';
 		$instance['lumiere_queryid_widget_input'] = ( !empty( $new_instance['lumiere_queryid_widget_input'] ) ) ? $new_instance['lumiere_queryid_widget_input'] : '';

		return $instance;
	}

}
$my_widget = new LumiereWidget();


	
?>
