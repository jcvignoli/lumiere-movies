<?php declare( strict_types = 1 );
/**
 * Class to build Virtual Pages
 *
 * This class build pages that are not known by WordPress
 * Virtual pages do not need to be added to WordPress and do not need htaccess
 *
 * @author        Originaly Mr. Hosseini, https://wordpress.stackexchange.com/a/342719/206323, modified by Lost Higway
 * @copyright (c) 2022, Lost Highway
 *
 * @version 1.0
 * @since 3.7.1
 * @package lumiere-movies
 */

namespace Lumiere;

// If this file is called directly, abort.
if ( ( ! defined( 'ABSPATH' ) ) || ( ! class_exists( '\Lumiere\Settings' ) ) ) {
	wp_die( esc_html__( 'You are not allowed to call this page directly.', 'lumiere-movies' ) );
}

use \WP_Post;
use \stdClass;

class Virtual_Page {

	/**
	 * @var string $page_path Full of the page to become virtual, ie "https://example.blog/lumiere/search/
	 */
	private string $page_path = '';

	/**
	 * @var string $page_title Title of the virtual page
	 */
	private string $page_title = '';

	/**
	 * @var string|object $page_content Can be a single phrase or an object with the HTML content
	 */
	private string|object $page_content = '';

	/**
	 * @var ?WP_Post $wp_post Final object containing the post
	 */
	private ?WP_Post $wp_post = null;

	/**
	 * Constructor
	 *
	 * @param string $page_path Full of the page to become virtual, ie "/lumiere/search/"
	 * @param string|object $page_content Content to be displayed, can be a single phrase or an object
	 * @param string $page_title Title of the virtual page
	 *
	 */
	public function __construct( string $page_path = '/lumiere/', string|object $page_content = 'content of the page', string $page_title = 'Title of the page' ) {

		// Build the vars
		$this->page_path = filter_var( $page_path, FILTER_SANITIZE_URL ) !== false ? filter_var( $page_path, FILTER_SANITIZE_URL ) : '';
		$this->page_content = $page_content;
		$this->page_title = filter_var( $page_title, FILTER_SANITIZE_STRING ) !== false ? filter_var( $page_title, FILTER_SANITIZE_STRING ) : '';

		// Start the page creation
		$this->create_page();
	}

	/**
	 * Update the page with the data sent to the class
	 *
	 * @return void
	 */
	private function update_wp_query(): void {

		global $wp, $wp_query;

		if ( $this->wp_post === null ) {
			wp_die( 'Cannot create a virtual page.' );
		}

		// Update the main query
		$wp_query->current_post = $this->wp_post->ID;
		$wp_query->found_posts = 1;
		$wp_query->is_page = true;//important part
		$wp_query->is_singular = true;//important part
		$wp_query->is_single = false;
		$wp_query->is_attachment = false;
		$wp_query->is_archive = false;
		$wp_query->is_category = false;
		$wp_query->is_tag = false;
		$wp_query->is_tax = false;
		$wp_query->is_author = false;
		$wp_query->is_date = false;
		$wp_query->is_year = false;
		$wp_query->is_month = false;
		$wp_query->is_day = false;
		$wp_query->is_time = false;
		$wp_query->is_search = false;
		$wp_query->is_feed = false;
		$wp_query->is_comment_feed = false;
		$wp_query->is_trackback = false;
		$wp_query->is_home = false;
		$wp_query->is_embed = false;
		$wp_query->is_404 = false;
		$wp_query->is_paged = false;
		$wp_query->is_admin = false;
		$wp_query->is_preview = false;
		$wp_query->is_robots = false;
		$wp_query->is_posts_page = false;
		$wp_query->is_post_type_archive = false;
		$wp_query->max_num_pages = 1;
		$wp_query->post = $this->wp_post;
		$wp_query->posts = [ $this->wp_post ];
		$wp_query->post_count = 1;
		$wp_query->queried_object = $this->wp_post;
		$wp_query->queried_object_id = $this->wp_post->ID;
		$wp_query->query_vars['error'] = '';
		unset( $wp_query->query['error'] );

		/**
		 * Doesn't seem needed
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$GLOBALS['wp_query'] = $wp_query;

		$wp->query = [];
		$wp->register_globals();
		 */

	}

	/**
	 * Update the page with the data sent to the class
	 *
	 * @return ?WP_Post
	 */
	public function create_page(): ?WP_Post {

		if ( is_null( $this->wp_post ) ) {

			$post = new stdClass();
			$post->ID = -99;
			$post->ancestors = []; // 3.6
			$post->comment_status = 'closed';
			$post->comment_count = 0;
			$post->filter = 'raw';
			$post->guid = rand();
			$post->is_virtual = true;
			$post->menu_order = 0;
			$post->pinged = '';
			$post->ping_status = 'closed';
			$post->post_title = esc_html( $this->page_title );
			$post->post_name = esc_html( $this->page_path );
			$post->post_content = $this->page_content ?? '';
			$post->post_excerpt = '';
			$post->post_parent = 0;
			$post->post_type = 'page';
			$post->post_status = 'publish';
			$post->post_date = current_time( 'mysql' );
			$post->post_date_gmt = current_time( 'mysql', 1 );
			$post->modified = $post->post_date;
			$post->modified_gmt = $post->post_date_gmt;
			$post->post_password = '';
			$post->post_content_filtered = '';
			$post->post_author = is_user_logged_in() ? get_current_user_id() : 0;
			$post->post_content = '';
			$post->post_mime_type = '';
			$post->to_ping = '';

			$this->wp_post = new WP_Post( $post );
			$this->update_wp_query();

			@status_header( 200 );
			wp_cache_add( -99, $this->wp_post, 'posts' );
		}

		return $this->wp_post;
	}
}
