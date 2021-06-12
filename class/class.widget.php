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

/** Registers Lumiere Movies widget so it appears with the other available
**  widgets and can be dragged and dropped into any active sidebars
** 
*/

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
		     'lumiere-movies',  // Base ID
		     'Lumière! Movies'   // Name
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
  
		global $imdb_admin_values, $imdb_widget_values, $wp_query;

		extract($args);
		$options = get_option('widget_imdbwidget');
		$name_sanitized = get_post(intval( $filmid ));
		$title_box = empty($instance['title']) ? esc_html__('IMDb data', 'lumiere-movies') : $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title']; //this is the widget title, from *wordpress* widget options
		$filmid = intval( $wp_query->post->ID );

		// shows widget only for a post or a page, when option "direct search" is switched on
		if ( ((is_single()) OR (is_page())) && ($imdb_admin_values['imdbdirectsearch'] == true) ) {

			echo $args['before_widget'];

			//------ Display the movie according to the post's name (setting in -> widget -> misc)

			if ( $imdb_widget_values['imdbautopostwidget'] == true) {

				$imdballmeta[0] = sanitize_text_field( $name_sanitized->post_title );

				echo $title_box;
				echo "<div class='imdbincluded'>";
				require_once( plugin_dir_path( __DIR__ ) . 'inc/imdb-movie.inc.php');
				echo "</div>";

			}

			//------ Meta tag "imdb-movie-widget"

			foreach (get_post_meta($filmid, 'imdb-movie-widget', false) as $key => $value) {
				
				$imdballmeta[0] = $value;
				echo $title_box;
				echo "<div class='imdbincluded'>";
				require_once( plugin_dir_path( __DIR__ ) . 'inc/imdb-movie.inc.php');
				echo "</div>";

			}

			//------ ID movie provided in "imdb-movie-widget-bymid"

			foreach (get_post_meta($filmid, 'imdb-movie-widget-bymid', false) as $key => $value) {

				$imdballmeta = 'imdb-movie-widget-noname';
				$moviespecificid = str_pad($value, 7, "0", STR_PAD_LEFT);

				echo $title_box;
				echo "<div class='imdbincluded'>";
				require_once( plugin_dir_path( __DIR__ ) . 'inc/imdb-movie.inc.php');
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

		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Lumière! Movies', 'lumiere-movies' ); ?>

		<p class="lumiere_padding_ten">
		<div class="lumiere_display_inline_flex">
			<div class="lumiere_padding_ten">
				<img class="lumiere_flex_auto" width="40" height="40" src="<?php echo $imdb_admin_values['imdbplugindirectory'] . 'pics/lumiere-ico-noir80x80.png'; ?>" />
			</div>
			<div class="lumiere_flex_auto">
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'lumiere-movies' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			</div>
		</div>
		</p><!-- #lumiere-movies -->

<?php

	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();

		$instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

}
$my_widget = new LumiereWidget();


	
?>
