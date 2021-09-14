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
use \Lumiere\Utils;
use \Lumiere\Logger;

class Widget extends \WP_Widget {

	use \Lumiere\Settings_Global;

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
	 * HTML wrapping to the widget name
	 */
	private const ARGS = [
		'before_title' => '<h4 id="lumiere_movies_widget_title" class="widget-title">',
		'after_title' => '</h4>',
		'before_widget' => '<div id="lumiere_movies_widget" class="sidebar-box widget_lumiere_movies_widget clr">',
		'after_widget' => '</div>',
	];

	/**
	 * HTML allowed for use of wp_kses()
	 */
	private const ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS = [
		'div' => [
			'id' => true,
			'class' => true,
		],
		'h4' => [
			'id' => true,
			'class' => true,
		],
		'a' => [
			'id' => true,
			'class' => true,
			'href' => true,
			'title' => true,
			'data-*' => true,
		],
		'span' => [
			'id' => true,
			'class' => true,
		],
		'img' => [
			'width' => true,
			'alt' => true,
			'loading' => true,
			'src' => true,
			'id' => true,
			'class' => true,
		],
	];

	/**
	 *  Names of the block widget
	 */
	const BLOCK_WIDGET_NAME = 'lumiere/widget'; // post-WP 5.8 widget block name.
	const WIDGET_NAME = 'lumiere_movies_widget'; // pre-WP 5.8 widget name.
	const WIDGET_CLASS = '\Lumiere\Widget'; // pre-WP 5.8. Must match class name.

	/**
	 * Constructor. Sets up the widget name, description, etc.
	 *
	 */
	public function __construct() {

		parent::__construct(
			self::WIDGET_NAME,  // Base ID.
			'Lumière! Widget (legacy)',   // Name.
			[
				'description' => esc_html__( 'Add movie details to your pages with Lumière! Legacy version: as of WordPress 5.8, prefer the new widget.', 'lumiere-movies' ),
				'show_instance_in_rest' => true, /** use WP REST API */
			]
		);

		// Construct Global Settings trait.
		$this->settings_open();

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

		//Register new block.
		add_action( 'init', [ $this, 'lumiere_register_widget_block' ] );

		if ( Utils::lumiere_block_widget_isactive() === false ) {

			// Should be hooked to 'widgets_init'.
			add_action(
				'widgets_init',
				function(): void {
					// Register legacy widget.
					register_widget( self::WIDGET_CLASS );
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
		add_shortcode( self::WIDGET_SHORTCODE, [ $this, 'lumiere_widget_shortcodes_parser' ] );

	}

	/**
	 * Wrapper for calling the function from outside
	 *
	 */
	public static function lumiere_widget_start(): void {
		new self();
	}

	/**
	 * Wrapper for activating debug
	 *
	 */
	public function lumiere_widget_maybe_start_debug(): void {

		if ( ( isset( $this->imdb_admin_values['imdbdebug'] ) ) && ( '1' === $this->imdb_admin_values['imdbdebug'] ) && ( $this->utils_class->debug_is_active === false ) ) {

			// Start debugging mode.
			$this->utils_class->lumiere_activate_debug();

		}

	}

	/**
	 * Shortcode [lumiereWidget][/lumiereWidget]
	 * Find that shortcode in the sidebar, retrieve the widget title and activate the function widget()
	 *
	 * @param array<string>|string $atts
	 * Used to retrieve widget block title and call widget() function
	 */
	public function lumiere_widget_shortcodes_parser( $atts, string $content, string $tag ): void {

		// Start logging using hook defined in settings class.
		do_action( 'lumiere_logger' );

		$this->logger->log()->debug( '[Lumiere][widget] Shortcode [' . self::WIDGET_SHORTCODE . '] found.' );

		$instance = [];

		// Get the widget title and pass it to the correct format.
		$instance['title'] = $content;

		// Send to widget() with self::ARGS and the widget title.
		$this->widget( $atts, $instance );
	}

	/**
	 * Hide Lumière legacy widget from WordPress legacy widgets list
	 * @param array<string> $widget_types
	 * @return array<string>
	 */
	public function hide_widget( array $widget_types ): array {

		$widget_types[] = 'lumiere_movies_widget';
		return $widget_types;

	}

	/**
	 * Register widget block (>= WordPress 5.8)
	 */
	public function lumiere_register_widget_block(): void {

		wp_register_script(
			'lumiere_block_widget',
			$this->config_class->lumiere_blocks_dir . 'widget-block.min.js',
			[ 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-data' ],
			$this->config_class->lumiere_version,
			false
		);

		wp_register_style(
			'lumiere_block_widget',
			$this->config_class->lumiere_blocks_dir . 'widget-block.min.css',
			[],
			$this->config_class->lumiere_version
		);

		// Fix; Avoid registering the block twice, register only if not already registered.
		// Avoid WP notice 'WP_Block_Type_Registry::register was called incorrectly. Block type is already registered'.
		if ( function_exists( 'register_block_type' ) && class_exists( '\WP_Block_Type_Registry' ) && ! \WP_Block_Type_Registry::get_instance()->is_registered( self::BLOCK_WIDGET_NAME ) ) {

			register_block_type(
				self::BLOCK_WIDGET_NAME,
				[
					'style' => 'lumiere_block_widget', // Loads both on editor and frontend.
					'editor_script' => 'lumiere_block_widget', // Loads only on editor.
				]
			);

		}
	}

	/**
	 * Front end output
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array<string>|string $args Display arguments including 'before_title', 'after_title', 'before_widget', and 'after_widget'.
	 * @param array<string> $instance The settings for the particular instance of the widget.
	 * @return void
	 * @phpstan-ignore-next-line inherited constraints from parent, can't comply with declaration requirements
	 */
	public function widget( $args, $instance ) {

		// Start Movie class.
		$movie_class = new Movie();

		// Execute logging.
		do_action( 'lumiere_logger' );

		// Build title, use a default text if title has not been edited in the widget interface.
		$title_box = isset( $instance['title'] ) ? self::ARGS['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . self::ARGS['after_title'] : self::ARGS['before_title'] . esc_html__( 'Lumière! Movies widget', 'lumiere-movies' ) . self::ARGS['after_title'];

		// Initialize var for id/name of the movie to display.
		$imdb_id_or_title = [];

		// Get the post id
		$post_id = intval( get_the_ID() );

		$config_class = $this->config_class;

		// Show widget only for a post or a page.
		if ( ( is_single() ) || ( is_page() ) ) {

			// Display the movie according to the post's title (option in -> general -> advanced).
			if ( $this->imdb_admin_values['imdbautopostwidget'] === '1' ) {
				$imdb_id_or_title[]['byname'] = sanitize_text_field( get_the_title() );

				$this->logger->log()->debug( '[Lumiere][widget] Auto widget activated, using the post title for querying' );

			} else {

				$this->logger->log()->debug( '[Lumiere][widget] Auto widget is disabled, no query will be made using current post title.' );

			}

			// Log what type of widget is utilised.
			if ( Utils::lumiere_block_widget_isactive() === true ) {
				// Post 5.8 WordPress.
				$this->logger->log()->debug( '[Lumiere][widget] Block-based widget found' );
			}
			if ( is_active_widget( false, false, self::WIDGET_NAME, false ) !== false ) {
				// Pre 5.8 WordPress.
				$this->logger->log()->debug( '[Lumiere][widget] Pre-5.8 WordPress widget found' );
			}

			// Show widget only if custom fields or if imdbautopostwidget option is active.
			if ( count( get_post_meta( $post_id, 'imdb-movie-widget', false ) ) !== 0 || count( get_post_meta( $post_id, 'imdb-movie-widget-bymid', false ) ) !== 0 || ( $this->imdb_admin_values['imdbautopostwidget'] === '1' ) ) {

				// Custom field "imdb-movie-widget"
				foreach ( get_post_meta( $post_id, 'imdb-movie-widget', false ) as $key => $value ) {

					$imdb_id_or_title[]['byname'] = sanitize_text_field( $value );

					$this->logger->log()->debug( "[Lumiere][widget] Custom field imdb-movie-widget found, using $value for querying" );

				}

				// Custom field imdb-movie-widget-bymid" (with proper movie ID).
				foreach ( get_post_meta( $post_id, 'imdb-movie-widget-bymid', false ) as $key => $value ) {

					$moviespecificid = $value;
					$imdb_id_or_title[]['bymid'] = sanitize_text_field( strval( $moviespecificid ) );

					$this->logger->log()->debug( "[Lumiere][widget] Custom field imdb-movie-widget-bymid found, using $value for querying" );

				}

				// If there is a result in class movie, display the widget.
				$movie = $movie_class->lumiere_show( $imdb_id_or_title );
				if ( strlen( $movie ) !== 0 ) {

					echo wp_kses( self::ARGS['before_widget'], self::ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS );

					echo wp_kses( $title_box, self::ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS ); // title of widget.

					echo wp_kses( $movie, self::ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS );

					echo wp_kses( self::ARGS['after_widget'], self::ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS );

				}

			}

		}

		// Display preview image only in widget block editor interface.
		$referer = strlen( $_SERVER['REQUEST_URI'] ) > 0 ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$pages_authorised = [ '/wp-admin/widgets.php', '/wp-json/wp/v2/widget-types' ];
		if ( Utils::lumiere_array_contains_term( $pages_authorised, $referer ) ) {

			echo '<div align="center"><img src="' . esc_url( $this->config_class->lumiere_pics_dir . 'widget-preview.png' ) . '" /></div>';
			echo '<br />';

		}

	}

	/**
	 * Outputs the settings update form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Current settings.
	 * @return string Default return is 'noform'.
	 *
	 * @phpstan-ignore-next-line inherited constraints from parent, can't comply with declaration requirements
	 */
	public function form( $instance ): string {

		$title = isset( $instance['title'] ) ? $instance['title'] : esc_html__( 'Lumière! Movies', 'lumiere-movies' );
		$lumiere_query_widget = isset( $instance['lumiere_queryid_widget'] ) ? $instance['lumiere_queryid_widget'] : '';

		$lumiere_queryid_widget_input = isset( $instance['lumiere_queryid_widget_input'] ) ? $instance['lumiere_queryid_widget_input'] : '';

		echo "\n" . '<p class="lumiere_padding_ten">';

		echo "\n\t" . '<div class="lumiere_display_inline_flex">';
		echo "\n\t\t" . '<div class="lumiere_padding_ten">';
		echo "\n\t\t\t" . '<img class="lumiere_flex_auto" width="40" height="40" src="'
				. esc_url( $this->config_class->lumiere_pics_dir . 'lumiere-ico80x80.png' ) . '" />';
		echo "\n\t\t" . '</div>';

		echo "\n\t\t" . '<div class="lumiere_flex_auto">';
		echo "\n\t\t\t" . '<label for="'
					. esc_attr( $this->get_field_id( 'title' ) ) . '">'
					. esc_html__( 'Widget title:', 'lumiere-movies' ) . '</label>';
		echo "\n\t\t\t" . '<input class="widefat" id="' . esc_attr( $this->get_field_id( 'title' ) ) . '" name="' . esc_attr( $this->get_field_name( 'title' ) ) . '" type="text" value="' . esc_attr( $title ) . '" />';
		echo "\n\t\t\t" . '</div>';
		echo "\n\t\t" . '</div>';
		echo "\n\t" . '</p><!-- #lumiere-movies -->';

		return 'noform';

	}

	/**
	 * Updates a particular instance of a widget.
	 *
	 * This function checks that `$new_instance` is set correctly. The newly-calculated
	 * value of `$instance` should be returned. If false is returned, the instance won't be
	 * saved/updated.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Settings to save or bool false to cancel saving.
	 * @phpstan-ignore-next-line inherited constraints from parent, can't comply with declaration requirements
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = [];

		$instance['title'] = ( isset( $new_instance['title'] ) ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		$instance['lumiere_queryid_widget'] = ( isset( $new_instance['lumiere_queryid_widget'] ) ) ? $new_instance['lumiere_queryid_widget'] : '';
		$instance['lumiere_queryid_widget_input'] = ( isset( $new_instance['lumiere_queryid_widget_input'] ) ) ? $new_instance['lumiere_queryid_widget_input'] : '';

		return $instance;
	}

}

// Instead of starting the class, add an action.
add_action( 'set_current_user', [ 'Lumiere\Widget', 'lumiere_widget_start' ] );
