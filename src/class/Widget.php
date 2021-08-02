<?php
/**
 * Class of widget: Add a widget including a moviev(either by auto widget option or the editor metabox) 
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       2.0
 */

namespace Lumiere;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die('You can not call directly this page');
}


class LumiereWidget extends \WP_Widget {

	/* Store the class of Lumière settings
	 * Usefull to start a new IMDbphp query
	 */
	private $configClass;

	/* Vars from Lumière settings
	 *
	 */
	private $imdb_admin_values, $imdb_widget_values, $imdb_cache_values;

	/* Store the class for movies
	 *
	 */
	private $movieClass;

	/* Store the class of utilities
	 *
	 */
	private $utilsClass;

	/* Shortcode to be used by add_shortcodes, ie [lumiereWidget][/lumiereWidget]
	 * This shortcode is a temporary one only created by the widget
	 * It doesn't need to be deleted when uninstalling Lumière plugin
	 *
	 */
	const widget_shortcode = 'lumiereWidget';

	/* Add layout wrapping html to the widget
	 *
	 */
	var $args = array(
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
		'before_widget' => '<div id="lumiere_movies_widget" class="sidebar-box widget_lumiere_movies_widget clr">',
		'after_widget'  => '</div>',
	);

	/* Name of the block widget
	 *
	 */
	const block_widget_name = 'lumiere/widget';
	const widget_name = 'lumiere_movies_widget';

	/**
	 * Constructor. Sets up the widget name, description, etc.
	 */
	function __construct() {

		 parent::__construct(
			self::widget_name,  // Base ID
			'Lumière! Widget (legacy)',   // Name
			array( 'description' => esc_html__( 'Add movie details to your pages with Lumière! Legacy version: as of WordPress 5.8, prefer the new widget.', 'lumiere-movies' ),
				'show_instance_in_rest' => true,/* # we use a proper gutenberg block */
				)
		 );

		// Start config class and get the vars
		if (class_exists("\Lumiere\Settings")) {

			$configClass = new \Lumiere\Settings();
			$this->configClass = $configClass;
			$this->imdb_admin_values = $configClass->get_imdb_admin_option();
			$this->imdb_widget_values = $configClass->get_imdb_widget_option();
			$this->imdb_cache_values = $configClass->get_imdb_widget_option();

			// Start the movie class
			$movieClass = new \Lumiere\LumiereMovies();
			$this->movieClass = $movieClass;

			// Start the utilities class
			$utilsClass = new \Lumiere\Utils();
			$this->utilsClass = $utilsClass;

		} else {

			wp_die( esc_html__('Cannot start class movie, class Lumière Settings not found', 'lumiere-movies') );

		}

		/**
		 * Activate debug
		 */
		if ( (isset($this->imdb_admin_values['imdbdebug'])) && ($this->imdb_admin_values['imdbdebug'] == 1) ){

			# Must match add_action('wp', [$this, 'lumiere_start_logger_wrapper']) timing in class.movie
			add_action('wp', [ $this, 'lumiere_widget_start_debug' ]); 

		} 

		/**
		 * Register the widget. Should be hooked to 'widgets_init'.
		 */
		 add_action( 'widgets_init', function() { # Register legacy widget
			register_widget( '\Lumiere\LumiereWidget' );
		 });
		add_action('enqueue_block_editor_assets', [ $this, 'lumiere_register_widget_block' ]); #Register new block

		/**
		 * Hide the widget in legacy widgets menu, but we don't want this
		 */
		 #add_action( 'widget_types_to_hide_from_legacy_widget_block', 'hide_widget' );

		/**
		 * Add shortcode in the block-based widget
		 */
		add_shortcode( self::widget_shortcode, [$this, 'lumiere_widget_shortcodes_parser'] );

	}


	/* Wrapper for activating debug and logging
	 * 
	 *
	 */
	function lumiere_widget_start_debug() {

		// Start debugging mode
		$this->utilsClass->lumiere_activate_debug();

		// Start the logger
		$this->configClass->lumiere_start_logger('lumiereWidget');

	}


	/* Shortcode [lumiereWidget][/lumiereWidget]
	 * Find that shortcode in the sidebar, retrieve the widget title and activate the function widget()
	 *
	 * Used to retrieve widget block title and call widget() function
	 */
	function lumiere_widget_shortcodes_parser($atts = array(), $content = null, $tag){

		$this->configClass->lumiere_maybe_log('debug', "[Lumiere][widget] Shortcode [" . self::widget_shortcode . "][/" . self::widget_shortcode . "] found. Using block-based widget.");

		// Get the widget title and pass it to the correct format
		$instance['title'] = $content;

		// Send to widget() with class var $args and the widget title
		return $this->widget($this->args, $instance);
	}


	/* Hide Lumière legacy widget from WordPress legacy widgets list
	 *
	 */
	function hide_widget( $widget_types ) {

		$widget_types[] = 'lumiere_movies_widget';
		return $widget_types;

	}

	/* Register widget block (>= WordPress 5.8)
	 *
	 */
	function lumiere_register_widget_block() {

		wp_register_script( "lumiere_block_widget", 
			$this->imdb_admin_values['imdbplugindirectory'] . 'blocks/widget-block.js',
			[ 'wp-blocks', 'wp-element', 'wp-editor','wp-components','wp-i18n','wp-data'  ], 
			$this->configClass->lumiere_version );

		wp_register_style( "lumiere_block_widget", 
			$this->imdb_admin_values['imdbplugindirectory'] . 'blocks/widget-block.css',
			array(), 
			$this->configClass->lumiere_version );

		register_block_type( self::block_widget_name, [
			'style' => 'lumiere_block_widget', // Loads both on editor and frontend.
			'editor_script' => 'lumiere_block_widget', // Loads only on editor.
		] );


	}

	/**
	 * Front end output
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array mandatory $args widget arguments.
	 * @param array mandatory $instance the widget with its values.
	 */
	public function widget( $args = self::args, $instance ) {

		/* Vars */  
		$output = "";
		$imdb_admin_values = $this->imdb_admin_values;
		$args['before_title'] = $this->args['before_title']; # Consistancy in title h2 class, otherwise it's sometimes <h4 class="widget-title"> and sometimes <h4 class="widgettitle">

		extract($args);

		// full title
		$title_box = empty($instance['title']) ? esc_html__('Lumière! Movies (legacy)', 'lumiere-movies') : $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title']; 

		// Initialize var for id/name of the movie to display
		$imdbIdOrTitle = array();

		// Get the post id
		$post_id = intval( get_the_ID() );

		$configClass = $this->configClass;

		// shows widget only for a post or a page
		if ( (is_single()) || ( is_page()) )  {

			// Display the movie according to the post's title (option in -> general -> advanced)
			if ( (isset($imdb_admin_values['imdbautopostwidget'])) && ($imdb_admin_values['imdbautopostwidget'] == true) ) {
				$imdbIdOrTitle[]['byname'] = sanitize_text_field( get_the_title() ); 

				$configClass->lumiere_maybe_log('debug', "[Lumiere][widget] Auto widget activated, using the post title for querying");

			} else {

				$configClass->lumiere_maybe_log('debug', "[Lumiere][widget] Auto widget is disabled, no query made using current post title.");

			}

			// Log what type of widget is utilised
			if ( $this->utilsClass->lumiere_block_widget_isactive() == true ){
				// Post 5.8 WordPress
				$configClass->lumiere_maybe_log('debug', "[Lumiere][widget] Block-based widget found");
			}
			if ( is_active_widget( '', '', self::widget_name) == true ){
				// Pre 5.8 WordPress
				$configClass->lumiere_maybe_log('debug', "[Lumiere][widget] Pre-5.8 wordpress widget found");
			}

			// Show widget only if custom fields or imdbautopostwidget option is found
			if ( (get_post_meta($post_id, 'imdb-movie-widget', false)) || (get_post_meta($post_id, 'imdb-movie-widget-bymid', false)) || (isset($imdb_admin_values['imdbautopostwidget'])) ) {

				// Custom field "imdb-movie-widget"
				foreach (get_post_meta($post_id, 'imdb-movie-widget', false) as $key => $value) {

					$imdbIdOrTitle[]['byname'] = sanitize_text_field($value); 

					$configClass->lumiere_maybe_log('debug', "[Lumiere][widget] Custom field imdb-movie-widget found, using $value for querying");

				}

				// Custom field imdb-movie-widget-bymid" (with proper movie ID)
				foreach (get_post_meta($post_id, 'imdb-movie-widget-bymid', false) as $key => $value) {

					$moviespecificid = $value;
					$imdbIdOrTitle[]['bymid'] = $moviespecificid; 

					$configClass->lumiere_maybe_log('debug', "[Lumiere][widget] Custom field imdb-movie-widget-bymid found, using $value for querying");

				}


				// Aggreate all the fields into global var $imdallmeta and send it to class movie
				for ($i=0; $i < count( $imdbIdOrTitle[$i] ); $i++) {


					// If there is a result in var $lumiere_result of class, display the widget
					if ($movie = $this->movieClass->lumiere_show($imdbIdOrTitle)) {

						$output .= $args['before_widget'];

						$output .= $title_box; // title of widget

						$output .= $movie;

						$output .= $args['after_widget'];

					} else {

						$query = implode("-", $imdbIdOrTitle[$i]);

						$configClass->lumiere_maybe_log('debug', "[Lumiere][widget] Not showing $query");

					}

				}

			}
			
		}

		// Display debug message if no metabox was found with an IMDb id/title
		if ( (empty(get_post_meta($post_id, 'imdb-movie-widget', false))) && (empty(get_post_meta($post_id, 'imdb-movie-widget-bymid', false))) ) {

			$configClass->lumiere_maybe_log('debug', "[Lumiere][widget] No metabox with IMDb id/title was added to this post.");

		}

		echo $output;
	}

	/**
	* Back-end widget form.
	*
	* @see WP_Widget::form()
	*
	* @param array $instance Previously saved values from database.
	*/
	public function form( $instance ) {

		$imdb_admin_values = $this->imdb_admin_values;

		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Lumière! Movies', 'lumiere-movies' ); 
		$lumiere_query_widget = ! empty( $instance['lumiere_queryid_widget'] ) ? $instance['lumiere_queryid_widget'] : '';
		$lumiere_queryid_widget_input = ! empty( $instance['lumiere_queryid_widget_input'] ) ? $instance['lumiere_queryid_widget_input'] : ''; ?>

		<p class="lumiere_padding_ten">

		<div class="lumiere_display_inline_flex">
			<div class="lumiere_padding_ten">
				<img class="lumiere_flex_auto" width="40" height="40" src="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] . 'pics/lumiere-ico80x80.png'); ?>" />
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

// Auto start, run on all pages
$lumiere_widget = new \Lumiere\LumiereWidget();
?>
