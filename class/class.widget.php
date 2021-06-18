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
  
		global $imdb_admin_values, $imdb_widget_values, $wp_query,$imdballmeta;

		extract($args);
		$options = get_option('widget_imdbwidget');

		// full title
		$title_box = empty($instance['title']) ? esc_html__('IMDb data', 'lumiere-movies') : $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title']; 
		// id/name of the movie to display
		$imdballmeta=array();

		$filmid = intval( $wp_query->post->ID );

		// shows widget only for a post or a page, when option "direct search" is switched on
		if ( (is_single()) || ( is_page()) )  {

			echo $args['before_widget'];

			//------ Display the movie according to the post's name (setting in -> widget -> misc)

			if ( (isset($imdb_widget_values['imdbautopostwidget'])) && ($imdb_widget_values['imdbautopostwidget'] == true) ) {

				$imdballmeta[]['byname'] = sanitize_text_field( $name_sanitized->post_title );

				echo $title_box;
				echo "<div class='imdbincluded'>";
				$display = new \Lumiere\LumiereMovies();
				echo "</div>";

			}

			//------ Meta tag "imdb-movie-widget"

			foreach (get_post_meta($filmid, 'imdb-movie-widget', false) as $key => $value) {

				$imdballmeta[]['byname'] = $value;

				echo $title_box;
				echo "<div class='imdbincluded'>";
				$display = new \Lumiere\LumiereMovies();
				echo "</div>";

			}

			//------ ID movie provided in "imdb-movie-widget-bymid"

			foreach (get_post_meta($filmid, 'imdb-movie-widget-bymid', false) as $key => $value) {

				$moviespecificid = esc_html($value);
				$imdballmeta[]['bymid'] = $moviespecificid;
				echo $title_box;
				echo "<div class='imdbincluded'>";
				$display = new \Lumiere\LumiereMovies();
				echo "</div>";

			}

			echo $args['after_widget'];

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
		$lumiere_queryid_widget_input = ! empty( $instance['lumiere_queryid_widget_input'] ) ? $instance['lumiere_queryid_widget_input'] : '';
?>

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
