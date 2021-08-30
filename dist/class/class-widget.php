<?php declare( strict_types = 1 );
/**
 * Widget class: Add a widget including a movie (either by auto widget option or the editor metabox)
 *
 * @author Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version 2.0
 * @package lumiere-movies
 */

namespace Lumiere;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! class_exists( '\Lumiere\Settings' ) ) {
	wp_die( 'You can not call directly this page' );
}

use \Lumiere\Movie;
use \Lumiere\Settings;
use \Lumiere\Utils;
use \Lumiere\Logger;

class Widget extends \WP_Widget {

	/**
	 * Store the class of Lumière settings
	 * Usefull to start a new IMDbphp query
	 */
	private Settings $config_class;

	/**
	 * Vars from Lumière settings
	 * @var array<string> $imdb_admin_values
	 */
	private array $imdb_admin_values;

	/**
	 *  Store the class of utilities
	 */
	private Utils $utils_class;

	/**
	 * Class \Lumiere\Logger
	 *
	 */
	private Logger $logger;

	/**
	 * Shortcode to be used by add_shortcodes, ie [lumiereWidget][/lumiereWidget]
	 * This shortcode is a temporary one only created by the widget
	 * Doesn't need to be deleted when uninstalling Lumière plugin
	 *
	 */
	const WIDGET_SHORTCODE = 'lumiereWidget';

	/**
	 *  Add layout wrapping html to the widget
	 */
	private $args = [
		'before_title' => '<h4 class="widget-title">',
		'after_title' => '</h4>',
		'before_widget' => '<div id="lumiere_movies_widget" class="sidebar-box widget_lumiere_movies_widget clr">',
		'after_widget' => '</div>',
	];

	/**
	 *  Name of the block widget
	 */
	const BLOCK_WIDGET_NAME = 'lumiere/widget';
	const WIDGET_NAME = 'lumiere_movies_widget';

	/**
	 * Constructor. Sets up the widget name, description, etc.
	 */
	public function __construct() {

		parent::__construct(
			self::WIDGET_NAME,  // Base ID.
			'Lumière! Widget (legacy)',   // Name.
			[
				'description' => esc_html__( 'Add movie details to your pages with Lumière! Legacy version: as of WordPress 5.8, prefer the new widget.', 'lumiere-movies' ),
				'show_instance_in_rest' => true, /* we use a proper gutenberg block */
			]
		);

		// Start Settings class.
		$this->config_class = new Settings();

		// Get database options.
		$this->imdb_admin_values = get_option( Settings::LUMIERE_ADMIN_OPTIONS );

		// Start Utilities class.
		$this->utils_class = new Utils();

		// Start Logger class.
		$this->logger = new Logger( 'widgetClass' );

		// Activate debugging.
		add_action( 'widget_init', [ $this, 'lumiere_widget_maybe_start_debug' ] );

		/**
		 * Register the widget
		 * Give priority to post-5.8 WordPress Widget block. If not found, register pre-5.8 widget.
		 */
		add_action( 'enqueue_block_editor_assets', [ $this, 'lumiere_register_widget_block' ] );//Register new block.
		if ( $this->utils_class->lumiere_block_widget_isactive() === false ) {

			// Should be hooked to 'widgets_init'.
			add_action(
				'widgets_init',
				function() {
					// Register legacy widget.
					register_widget( '\Lumiere\LumiereWidget' );
				}
			);
		}

		/**
		 * Hide the widget in legacy widgets menu, but we don't want this
		 */
		// add_action( 'widget_types_to_hide_from_legacy_widget_block', 'hide_widget' );.

		/**
		 * Add shortcode in the block-based widget
		 */
		add_shortcode( self::WIDGET_SHORTCODE, [ $this, 'lumiere_widget_shortcodes_parser' ], 99 );

	}

	/**
	 *  Wrapper for activating debug and logging
	 *
	 */
	public function lumiere_widget_maybe_start_debug() {

		if ( ( isset( $this->imdb_admin_values['imdbdebug'] ) ) && ( '1' === $this->imdb_admin_values['imdbdebug'] ) && ( $this->utils_class->debug_is_active === false ) ) {

			// Start debugging mode.
			$this->utils_class->lumiere_activate_debug();

		}

	}

	/**
	 * Shortcode [lumiereWidget][/lumiereWidget]
	 * Find that shortcode in the sidebar, retrieve the widget title and activate the function widget()
	 *
	 * Used to retrieve widget block title and call widget() function
	 */
	public function lumiere_widget_shortcodes_parser( ?string $atts, ?string $content = null, ?string $tag ): ?string {

		// Start logging using hook defined in settings class.
		do_action( 'lumiere_logger' );

		$this->logger->log()->debug( '[Lumiere][widget] Shortcode [' . self::WIDGET_SHORTCODE . '][/' . self::WIDGET_SHORTCODE . '] found. Using block-based widget.' );

		$instance = [];

		// Get the widget title and pass it to the correct format.
		$instance['title'] = $content;

		// Send to widget() with class var $args and the widget title.
		return $this->widget( $this->args, $instance );
	}

	/**
	 *  Hide Lumière legacy widget from WordPress legacy widgets list
	 */
	public function hide_widget( $widget_types ) {

		$widget_types[] = 'lumiere_movies_widget';
		return $widget_types;

	}

	/**
	 *   Register widget block (>= WordPress 5.8)
	 */
	public function lumiere_register_widget_block() {

		wp_register_script(
			'lumiere_block_widget',
			$this->config_class->lumiere_blocks_dir . 'widget-block.min.js',
			[ 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-data' ],
			$this->config_class->lumiere_version
		);

		wp_register_style(
			'lumiere_block_widget',
			$this->config_class->lumiere_blocks_dir . 'widget-block.min.css',
			[],
			$this->config_class->lumiere_version
		);

		register_block_type(
			self::BLOCK_WIDGET_NAME,
			[
				'style' => 'lumiere_block_widget', // Loads both on editor and frontend.
				'editor_script' => 'lumiere_block_widget', // Loads only on editor.
			]
		);

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

		// Start Movie class.
		$movieClass = new Movie();

		/* Vars */
		$output = '';
		$imdb_admin_values = $this->imdb_admin_values;
		$args['before_title'] = $this->args['before_title']; //Consistancy in title h2 class, otherwise it's sometimes <h4 class="widget-title"> and sometimes <h4 class="widgettitle">.

		// Execute logging.
		do_action( 'lumiere_logger' );

		// full title.
		$title_box = empty( $instance['title'] ) ? esc_html__( 'Lumière! Movies (legacy)', 'lumiere-movies' ) : $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];

		// Initialize var for id/name of the movie to display.
		$imdbIdOrTitle = [];

		// Get the post id
		$post_id = intval( get_the_ID() );

		$config_class = $this->config_class;

		// shows widget only for a post or a page.
		if ( ( is_single() ) || ( is_page() ) ) {

			// Display the movie according to the post's title (option in -> general -> advanced).
			if ( ( isset( $imdb_admin_values['imdbautopostwidget'] ) ) && ( $imdb_admin_values['imdbautopostwidget'] === '1' ) ) {
				$imdbIdOrTitle[]['byname'] = sanitize_text_field( get_the_title() );

				$this->logger->log()->debug( '[Lumiere][widget] Auto widget activated, using the post title for querying' );

			} else {

				$this->logger->log()->debug( '[Lumiere][widget] Auto widget is disabled, no query made using current post title.' );

			}

			// Log what type of widget is utilised.
			if ( $this->utils_class->lumiere_block_widget_isactive() === true ) {
				// Post 5.8 WordPress.
				$this->logger->log()->debug( '[Lumiere][widget] Block-based widget found' );
			}
			if ( is_active_widget( '', '', self::WIDGET_NAME ) === true ) {
				// Pre 5.8 WordPress.
				$this->logger->log()->debug( '[Lumiere][widget] Pre-5.8 WordPress widget found' );
			}

			// Show widget only if custom fields or imdbautopostwidget option is found.
			if ( ( get_post_meta( $post_id, 'imdb-movie-widget', false ) ) || ( get_post_meta( $post_id, 'imdb-movie-widget-bymid', false ) ) || ( isset( $imdb_admin_values['imdbautopostwidget'] ) ) ) {

				// Custom field "imdb-movie-widget"
				foreach ( get_post_meta( $post_id, 'imdb-movie-widget', false ) as $key => $value ) {

					$imdbIdOrTitle[]['byname'] = sanitize_text_field( $value );

					$this->logger->log()->debug( "[Lumiere][widget] Custom field imdb-movie-widget found, using $value for querying" );

				}

				// Custom field imdb-movie-widget-bymid" (with proper movie ID).
				foreach ( get_post_meta( $post_id, 'imdb-movie-widget-bymid', false ) as $key => $value ) {

					$moviespecificid = $value;
					$imdbIdOrTitle[]['bymid'] = $moviespecificid;

					$this->logger->log()->debug( "[Lumiere][widget] Custom field imdb-movie-widget-bymid found, using $value for querying" );

				}

				// If there is a result in var $lumiere_result of class, display the widget.
				$movie = $movieClass->lumiere_show( $imdbIdOrTitle );
				if ( ( isset( $movie ) ) && ( ! empty( $movie ) ) ) {

					$output .= $args['before_widget'];

					$output .= $title_box; // title of widget.

					$output .= $movie;

					$output .= $args['after_widget'];

				} else {

					$this->logger->log()->debug( "[Lumiere][widget] Not showing $movie" );

				}

			}

		}

		// Display debug message if no metabox was found with an IMDb id/title
		if ( ( empty( get_post_meta( $post_id, 'imdb-movie-widget', false ) ) ) && ( empty( get_post_meta( $post_id, 'imdb-movie-widget-bymid', false ) ) ) ) {

			$this->logger->log()->debug( '[Lumiere][widget] No metabox with IMDb id/title was added to this post.' );

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
				<img class="lumiere_flex_auto" width="40" height="40" src="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] . 'pics/lumiere-ico80x80.png' ); ?>" />
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

		$instance = [];

		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		$instance['lumiere_queryid_widget'] = ( ! empty( $new_instance['lumiere_queryid_widget'] ) ) ? $new_instance['lumiere_queryid_widget'] : '';
		$instance['lumiere_queryid_widget_input'] = ( ! empty( $new_instance['lumiere_queryid_widget_input'] ) ) ? $new_instance['lumiere_queryid_widget_input'] : '';

		return $instance;
	}

}

// Auto start, autorun when called
new \Lumiere\Widget();

