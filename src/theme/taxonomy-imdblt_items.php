<?php 

/* Template Name: Taxonomy for Lumière! wordpress plugin (set up for standard taxonomy) 
*  This file should be edited, renamed, and then copied in your theme folder but you also can
*  use the admin taxonomy interface to do it automatically
*
*  Version: 1.0
*/

get_header();

echo "<br />";

// get_sidebar(); # unactivated, but can easily be activated!
?>

<main id="main" class="site-main clr" role="main">
	<div id="content-wrap" class="container clr">
		<h1 class="pagetitle"><?php esc_html_e( 'Taxonomy', 'lumiere-movies'); ?> <i>standard</i></h1>

<?php	if ( have_posts() ) { // there is post
		while ( have_posts() ) { 
			the_post(); ?>
			
			<div class="postList">
				<h3 id="post-<?php the_ID(); ?>">
					<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php esc_html_e( 'Open the blog ', 'lumiere-movies')?><?php the_title(); ?>">
						<?php the_title(); ?>
					</a>
				</h3>

				<?php if (get_terms('standard')){ ?>

				<div class="taxonomy">
					<?php echo get_the_term_list(get_the_ID(), esc_html( $imdb_admin_values['imdburlstringtaxo'] ) . 'standard', esc_html__( 'Taxonomy: '), ', ', '' ); ?>
					<br /><br />
				</div>
				<?php } ?>	
				
				<div class="entry">
					<?php the_excerpt() ?>
				</div>
		
				<p class="postmetadata">
					<span class="category"><?php esc_html_e( "Filed under: ", 'lumiere-movies'); ?> <?php the_category(', ') ?></span> 

					<?php if (has_tag()){ ?>
					<strong>|</strong>
					<span class="tags"><?php the_tags(esc_html__( 'Tags: ', 'lumiere-movies'), ' &bull; ', ' '); ?></span><?php } ?>

					<strong>|</strong> <?php edit_post_link('Edit','','<strong>|</strong>'); ?>  <?php comments_popup_link('No Comments &#187;', '1 Comment &#187;', '% Comments &#187;'); ?>
				</p>
			</div><?php

		}

	} else { // there is no post
			esc_html_e( 'No post found.', 'lumiere-movies'); 
			echo "<br /><br /><br />";
	} ?>

	
	</div>
</main>

<?php

wp_meta();

get_footer(); 
?>
