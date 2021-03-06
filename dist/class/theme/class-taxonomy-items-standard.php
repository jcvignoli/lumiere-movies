<?php declare( strict_types = 1 );
/**
 * Template Item: Taxonomy for Lumière! WordPress plugin (set up for standard item taxonomy)
 * This file should be edited, renamed, and then copied in your theme folder but you also can
 * use the admin taxonomy interface to do it automatically
 *
 * Version: 2.1
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
	private bool $activate_sidebar = true;

	/**
	 * HTML allowed for use of wp_kses()
	 */
	private const ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS = [
		'a' => [
			'class' => true,
			'href' => true,
			'rel' => true,
		],
	];

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		// Construct Frontend trait.
		$this->__constructFrontend( 'taxonomy-standard' );

		// Display the page.
		$this->lumiere_layout_taxo_standard();

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
		if ( have_posts() ) {
			// @phpstan-ignore-next-line WordPress coding standard, it might make no sense...
			while ( have_posts() ) {
				the_post();
				?>

					<div class="postList">
						<h3 id="post-<?php the_ID(); ?>">
							<a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php esc_html_e( 'Open the blog ', 'lumiere-movies' ); ?><?php the_title(); ?>">
								<?php the_title(); ?>
							</a>
						</h3>

						<?php
						$term_exist = (array) get_terms( 'standard' );
						if ( count( $term_exist ) !== 0 ) {
							?>

						<div class="taxonomy">
							<?php
							$the_post_id = is_integer( get_the_ID() ) !== false ? get_the_ID() : 0;
							$terms_list = get_the_term_list( $the_post_id, $lumiere_taxonomy_full, esc_html__( 'Lumiere taxonomy: ', 'lumiere-movies' ), ', ', '' );
							$terms_list_final = $terms_list !== false && is_wp_error( $terms_list ) === false ? $terms_list : '';
							echo wp_kses( $terms_list_final, self::ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS );
							?>
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
				esc_html_e( 'No post found with this taxonomy.', 'lumiere-movies' );
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
