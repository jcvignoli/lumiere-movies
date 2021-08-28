<?php declare( strict_types = 1 );
/**
 * Template Item: Taxonomy for Lumière! WordPress plugin (set up for standard item taxonomy)
 * This file should be edited, renamed, and then copied in your theme folder but you also can
 * use the admin taxonomy interface to do it automatically
 *
 * Version: 2.0
 * @package lumiere-movies
 */

namespace Lumiere;

// If this file is called directly, abort.
if ( ( ! defined( 'ABSPATH' ) ) || ( ! class_exists( '\Lumiere\Settings' ) ) ) {
	wp_die( 'You can not call directly this page' );
}

use \Lumiere\Settings;
use \Lumiere\Utils;
use \Lumiere\Logger;

class Taxonomystandard {

	/**
	 * Set to true to activate the sidebar
	 *
	 */
	private const ACTIVATE_SIDEBAR = false;

	/**
	 * Class \Lumiere\Utils
	 *
	 */
	private Utils $utils_class;

	/**
	 * Class \Lumiere\Settings
	 *
	 */
	private Settings $config_class;

	/**
	 * Class \Lumiere\Logger
	 *
	 */
	private Logger $logger;

	/**
	 *  Admin settings from database
	 *
	 *  @var array<string> $imdb_admin_values
	 */
	private array $imdb_admin_values;

	/**
	 * Current page name from the tag taxonomy
	 *
	 */
	private string $page_title;

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		// Get database options
		$this->imdb_admin_values = get_option( Settings::LUMIERE_ADMIN_OPTIONS );

		$this->config_class = new Settings( 'taxonomy-standard' );

		// Start the class Utils to activate debug
		$this->utils_class = new Utils();

		// Build the current page name from the tag taxonomy
		$this->page_title = single_tag_title( '', false );

		// Start the logger.
		$this->logger = new Logger( 'taxonomy-standard' );

		// Start debug.
		add_action( 'init', [ $this, self::lumiere_maybe_start_debug() ], 0 );

		// Display the page.
		add_action( 'wp', [ $this, self::layout() ], 0 );

	}

	/**
	 *  Start debug mode
	 *
	 */
	private function lumiere_maybe_start_debug() {

		if ( ( isset( $this->imdb_admin_values['imdbdebug'] ) ) && ( '1' === $this->imdb_admin_values['imdbdebug'] ) && ( $this->utils_class->debug_is_active === false ) ) {

			// Start debugging mode
			$this->utils_class->lumiere_activate_debug();

		}

	}

	/**
	 *  Display layout
	 *
	 */
	private function layout(): void {

		get_header();

		$lumiere_taxonomy_full = esc_html( $this->imdb_admin_values['imdburlstringtaxo'] ) . 'standard';

		echo '<br />';

		if ( self::ACTIVATE_SIDEBAR === true ) {
			get_sidebar(); # selected in settings above
		}
		?>

		<main id="main" class="site-main clr" role="main">
			<div id="content-wrap" class="container clr">
				<h1 class="pagetitle"><?php esc_html_e( 'Taxonomy', 'lumiere-movies' ); ?> <i>standard</i></h1>

		<?php
		if ( have_posts() ) { // there is post
			while ( have_posts() ) {
				the_post();
				?>

					<div class="postList">
						<h3 id="post-<?php the_ID(); ?>">
							<a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php esc_html_e( 'Open the blog ', 'lumiere-movies' ); ?><?php the_title(); ?>">
								<?php the_title(); ?>
							</a>
						</h3>

						<?php if ( get_terms( 'standard' ) ) { ?>

						<div class="taxonomy">
							<?php echo get_the_term_list( get_the_ID(), $lumiere_taxonomy_full, esc_html__( 'Taxonomy: ', 'lumiere-movies' ), ', ', '' ); ?>
							<br /><br />
						</div>
						<?php } ?>	

						<div class="entry">
							<?php the_excerpt(); ?>
						</div>

						<p class="postmetadata">
							<span class="category"><?php esc_html_e( 'Filed under: ', 'lumiere-movies' ); ?> <?php the_category( ', ' ); ?></span> 

							<?php if ( has_tag() ) { ?>
							<strong>|</strong>
							<span class="tags"><?php the_tags( esc_html__( 'Tags: ', 'lumiere-movies' ), ' &bull; ', ' ' ); ?></span><?php } ?>

							<strong>|</strong> <?php edit_post_link( 'Edit', '', '<strong>|</strong>' ); ?>  <?php comments_popup_link( 'No Comments &#187;', '1 Comment &#187;', '% Comments &#187;' ); ?>
						</p>
					</div>
					<?php

			}

		} else { // there is no post
				esc_html_e( 'No post found.', 'lumiere-movies' );
				echo '<br /><br /><br />';
		}
		?>


			</div>
		</main>

		<?php

		wp_meta();

		get_footer();

	}

}

new Taxonomystandard();
