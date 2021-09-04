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

class Taxonomy_Items_Standard {

	// Use trait frontend
	use \Lumiere\Frontend {
		Frontend::__construct as public __constructFrontend;
	}

	/**
	 * Set to true to activate the sidebar
	 */
	private bool $activate_sidebar = false;

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		// Construct Frontend trait.
		$this->__constructFrontend( 'taxonomy-standard' );

		// Display the page.
		add_action( 'wp', [ $this, self::lumiere_layout_taxo_standard() ], 0 );

	}

	/**
	 *  Display layout
	 *
	 */
	private function lumiere_layout_taxo_standard(): void {

		get_header();

		$lumiere_taxonomy_full = esc_html( $this->imdb_admin_values['imdburlstringtaxo'] ) . 'standard';

		echo '<br />';

		if ( $this->activate_sidebar === true ) {
			get_sidebar(); # selected in the above properties
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

new Taxonomy_Items_Standard();
