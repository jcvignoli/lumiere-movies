<?php declare( strict_types = 1 );

// Autoload composer dependencies if not already done by Codeception.
require_once dirname( __DIR__, 3 ) . '/src/vendor/autoload.php';

// Mock ABSPATH.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__, 3 ) . '/src/' );
}

// Mock WPINC.
if ( ! defined( 'WPINC' ) ) {
	define( 'WPINC', 'wp-includes' );
}

if ( ! defined( 'OBJECT' ) ) {
	define( 'OBJECT', 'OBJECT' );
}

/**
 * Manually define WordPress function stubs.
 */

if ( ! function_exists( 'get_option' ) ) {
	function get_option( $option, $default = false ) {
		return ( $GLOBALS['wp_options'] ?? [] )[ $option ] ?? $default;
	}
}

if ( ! function_exists( 'update_option' ) ) {
	function update_option( $option, $value, $autoload = null ) {
		if ( ! isset( $GLOBALS['wp_options'] ) ) {
			$GLOBALS['wp_options'] = [];
		}
		$GLOBALS['wp_options'][ $option ] = $value;
		return true;
	}
}

if ( ! function_exists( 'is_admin' ) ) {
	function is_admin() {
		return $GLOBALS['is_admin'] ?? false;
	}
}

if ( ! function_exists( 'did_action' ) ) {
	function did_action( $tag ) {
		return ( $GLOBALS['did_actions'] ?? [] )[ $tag ] ?? 0;
	}
}

if ( ! function_exists( 'wp_verify_nonce' ) ) {
	function wp_verify_nonce( $nonce, $action = -1 ) {
		return ( ( $GLOBALS['wp_nonces'] ?? [] )[ $nonce ] ?? null ) === $action;
	}
}

if ( ! function_exists( 'is_user_logged_in' ) ) {
	function is_user_logged_in() {
		return $GLOBALS['is_user_logged_in'] ?? false;
	}
}

if ( ! function_exists( 'is_home' ) ) {
	function is_home() {
		return $GLOBALS['is_home'] ?? false;
	}
}

if ( ! function_exists( 'is_front_page' ) ) {
	function is_front_page() {
		return $GLOBALS['is_front_page'] ?? false;
	}
}

if ( ! function_exists( 'is_404' ) ) {
	function is_404() {
		return $GLOBALS['is_404'] ?? false;
	}
}

if ( ! function_exists( 'is_attachment' ) ) {
	function is_attachment() {
		return $GLOBALS['is_attachment'] ?? false;
	}
}

if ( ! function_exists( 'is_archive' ) ) {
	function is_archive() {
		return $GLOBALS['is_archive'] ?? false;
	}
}

if ( ! function_exists( 'is_author' ) ) {
	function is_author() {
		return $GLOBALS['is_author'] ?? false;
	}
}

if ( ! function_exists( 'get_the_ID' ) ) {
	function get_the_ID() {
		return $GLOBALS['the_ID'] ?? 0;
	}
}

if ( ! function_exists( 'is_active_widget' ) ) {
	function is_active_widget( $callback = false, $widget_id = false, $id_base = false, $skip_inactive = true ) {
		return ( $GLOBALS['active_widgets'] ?? [] )[ $id_base ] ?? false;
	}
}

if ( ! function_exists( 'get_post_meta' ) ) {
	function get_post_meta( $post_id, $key = '', $single = false ) {
		$val = ( $GLOBALS['post_meta'][ $post_id ] ?? [] )[ $key ] ?? ( $single ? '' : [] );
		if ( $single && is_array( $val ) ) {
			return $val[0] ?? '';
		}
		return $val;
	}
}

if ( ! function_exists( 'get_the_title' ) ) {
	function get_the_title( $post = 0 ) {
		return ( $GLOBALS['post_titles'] ?? [] )[ $post ] ?? '';
	}
}

if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $text ) {
		return $text;
	}
}

if ( ! function_exists( 'has_block' ) ) {
	function has_block( $name, $post = null ) {
		return str_contains( (string) $post, $name );
	}
}

if ( ! function_exists( '_n' ) ) {
	function _n( $single, $plural, $number, $domain = 'default' ) {
		return $number === 1 ? $single : $plural;
	}
}

if ( ! function_exists( 'current_user_can' ) ) {
	function current_user_can( $capability, ...$args ) {
		return $GLOBALS['current_user_can'][ $capability ] ?? false;
	}
}

if ( ! function_exists( 'wp_date' ) ) {
	function wp_date( $format, $timestamp = null ) {
		return date( $format, $timestamp ?? time() );
	}
}

if ( ! function_exists( 'get_transient' ) ) {
	function get_transient( $transient ) {
		return $GLOBALS['wp_transients'][ $transient ] ?? false;
	}
}

if ( ! function_exists( 'set_transient' ) ) {
	function set_transient( $transient, $value, $expiration = 0 ) {
		$GLOBALS['wp_transients'][ $transient ] = $value;
		return true;
	}
}

if ( ! function_exists( 'delete_transient' ) ) {
	function delete_transient( $transient ) {
		unset( $GLOBALS['wp_transients'][ $transient ] );
		return true;
	}
}

if ( ! function_exists( 'wp_next_scheduled' ) ) {
	function wp_next_scheduled( $hook, $args = [] ) {
		return $GLOBALS['wp_scheduled'][ $hook ] ?? false;
	}
}

if ( ! function_exists( 'wp_schedule_event' ) ) {
	function wp_schedule_event( $timestamp, $recurrence, $hook, $args = [], $wp_error = false ) {
		$GLOBALS['wp_scheduled'][ $hook ] = $timestamp;
		return true;
	}
}

if ( ! function_exists( 'wp_unschedule_event' ) ) {
	function wp_unschedule_event( $timestamp, $hook, $args = [], $wp_error = false ) {
		unset( $GLOBALS['wp_scheduled'][ $hook ] );
		return true;
	}
}

if ( ! function_exists( '_get_cron_array' ) ) {
	function _get_cron_array() {
		$cron = [];
		foreach ( $GLOBALS['wp_scheduled'] ?? [] as $hook => $time ) {
			$cron[ $time ][ $hook ] = [];
		}
		return $cron;
	}
}

if ( ! function_exists( 'wp_get_scheduled_event' ) ) {
	function wp_get_scheduled_event( $hook, $args = [], $timestamp = null ) {
		if ( isset( $GLOBALS['wp_scheduled'][ $hook ] ) ) {
			return (object) [ 'hook' => $hook, 'timestamp' => $GLOBALS['wp_scheduled'][ $hook ] ];
		}
		return false;
	}
}

if ( ! function_exists( 'sanitize_key' ) ) {
	function sanitize_key( $key ) {
		return strtolower( $key );
	}
}

if ( ! function_exists( 'wp_delete_file' ) ) {
	function wp_delete_file( $file ) {
		$GLOBALS['deleted_files'][] = $file;
		return true;
	}
}

if ( ! function_exists( 'wp_mkdir_p' ) ) {
	function wp_mkdir_p( $target ) {
		$GLOBALS['created_dirs'][] = $target;
		return true;
	}
}

if ( ! function_exists( 'taxonomy_exists' ) ) {
	function taxonomy_exists( $taxonomy ) {
		return $GLOBALS['taxonomies_exist'][ $taxonomy ] ?? false;
	}
}

if ( ! function_exists( 'term_exists' ) ) {
	function term_exists( $term, $taxonomy = '', $parent = null ) {
		return $GLOBALS['terms_exist'][ $taxonomy ][ $term ] ?? null;
	}
}

if ( ! function_exists( 'wp_insert_term' ) ) {
	function wp_insert_term( $term, $taxonomy, $args = [] ) {
		$GLOBALS['inserted_terms'][ $taxonomy ][] = $term;
		return [ 'term_id' => 1, 'term_taxonomy_id' => 1 ];
	}
}

if ( ! function_exists( 'wp_json_encode' ) ) {
	function wp_json_encode( $data, $options = 0, $depth = 512 ) {
		return json_encode( $data, $options, $depth );
	}
}

if ( ! function_exists( 'wp_set_object_terms' ) ) {
	function wp_set_object_terms( $object_id, $terms, $taxonomy, $append = false ) {
		$GLOBALS['object_terms'][ $object_id ][ $taxonomy ] = $terms;
		return true;
	}
}

if ( ! function_exists( 'get_term_by' ) ) {
	function get_term_by( $field, $value, $taxonomy = '', $output = OBJECT, $filter = 'raw' ) {
		return $GLOBALS['terms'][ $taxonomy ][ $value ] ?? false;
	}
}

if ( ! function_exists( 'get_term_link' ) ) {
	function get_term_link( $term, $taxonomy = '' ) {
		return 'http://example.org/' . $taxonomy . '/' . ( is_object( $term ) ? $term->slug : $term );
	}
}

if ( ! class_exists( 'WP_Term' ) ) {
	class WP_Term {
		public $term_id;
		public $slug;
		public function __construct( $id, $slug ) {
			$this->term_id = $id;
			$this->slug = $slug;
		}
	}
}

if ( ! class_exists( 'WP_Error' ) ) {
	class WP_Error {
		public function __construct( $code = '', $message = '', $data = '' ) {}
	}
}

if ( ! function_exists( 'esc_url_raw' ) ) {
	function esc_url_raw( $url ) {
		return $url;
	}
}

if ( ! function_exists( 'wp_unslash' ) ) {
	function wp_unslash( $data ) {
		return $data;
	}
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
	function sanitize_text_field( $str ) {
		return $str;
	}
}

if ( ! function_exists( 'plugin_dir_path' ) ) {
	function plugin_dir_path( $file ) {
		return dirname( $file ) . '/';
	}
}

if ( ! function_exists( 'plugin_dir_url' ) ) {
	function plugin_dir_url( $file ) {
		return 'http://example.org/wp-content/plugins/lumiere-movies/';
	}
}

if ( ! function_exists( 'get_bloginfo' ) ) {
	function get_bloginfo( $show = '', $filter = 'raw' ) {
		return ( $GLOBALS['wp_bloginfo'] ?? [] )[ $show ] ?? '';
	}
}

if ( ! function_exists( 'get_site_url' ) ) {
	function get_site_url( $blog_id = null, $path = '', $scheme = null ) {
		return 'http://example.org' . $path;
	}
}

if ( ! function_exists( 'site_url' ) ) {
	function site_url( $path = '', $scheme = null ) {
		return 'http://example.org' . $path;
	}
}

if ( ! function_exists( 'content_url' ) ) {
	function content_url( $path = '' ) {
		return 'http://example.org/wp-content' . $path;
	}
}

if ( ! function_exists( 'apply_filters' ) ) {
	function apply_filters( $tag, $value, ...$args ) {
		if ( $tag === 'lum_display_movies_box' ) {
			return '<div>Movie Box</div>';
		}
		if ( $tag === 'lum_display_persons_box' ) {
			return '<div>Person Box</div>';
		}
		return $value;
	}
}

if ( ! function_exists( 'add_action' ) ) {
	function add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
		return true;
	}
}

if ( ! function_exists( 'add_filter' ) ) {
	function add_filter( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
		return true;
	}
}

if ( ! function_exists( 'wp_enqueue_script' ) ) {
	function wp_enqueue_script( $handle, $src = '', $deps = [], $ver = false, $in_footer = false ) {
		return true;
	}
}

if ( ! function_exists( 'wp_enqueue_style' ) ) {
	function wp_enqueue_style( $handle, $src = '', $deps = [], $ver = false, $media = 'all' ) {
		return true;
	}
}

if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! function_exists( 'lum_get_version' ) ) {
	function lum_get_version() {
		return '4.8';
	}
}

if ( ! function_exists( 'request_filesystem_credentials' ) ) {
	function request_filesystem_credentials( $url, $type = '', $error = false, $context = false, $extra_fields = null ) {
		return true;
	}
}

if ( ! function_exists( 'WP_Filesystem' ) ) {
	function WP_Filesystem( $args = false, $url = false, $method = false ) {
		return true;
	}
}

if ( ! function_exists( 'wp_die' ) ) {
	function wp_die( $message = '', $title = '', $args = [] ) {
		throw new \Exception( "wp_die called: $message" );
	}
}

if ( ! function_exists( 'esc_html__' ) ) {
	function esc_html__( $text, $domain ) {
		return $text;
	}
}

if ( ! function_exists( 'amp_is_request' ) ) {
	function amp_is_request() {
		return $GLOBALS['amp_is_request'] ?? false;
	}
}

if ( ! class_exists( 'WP_Widget' ) ) {
	class WP_Widget {
		public function widget( $args, $instance ) {}
		public function update( $new_instance, $old_instance ) {}
		public function form( $instance ) {}
	}
}

if ( ! isset( $GLOBALS['wp_filesystem'] ) ) {
	$GLOBALS['wp_filesystem'] = new class {
		public function is_dir( $path ) { return true; }
		public function is_writable( $path ) { return true; }
		public function is_readable( $path ) { return true; }
		public function chmod( $path, $mode ) { return true; }
	};
}
