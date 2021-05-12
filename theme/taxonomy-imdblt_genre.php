<?php 
/*Template Name: Taxonomy for IMDb Link Transformer (-> genre) 
* ( This file should be copied in your theme folder (root) )
*
*/
do_action('wp_loaded'); // execute wordpress first codes

get_header();

get_sidebar(); ?>

<br />
<br />

	<div id="content" class="narrowcolumn">


		<h1 class="pagetitle"><?php esc_html_e( 'Taxonomy'); ?></h1>

<?php	if ( have_posts() ) { // there is post
		while ( have_posts() ) { 
			the_post(); ?>
			
			<div class="postList">
				<h3 id="post-<?php the_ID(); ?>">
					<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php esc_html_e( 'Open the blog ', 'imdb')?><?php the_title(); ?>">
						<?php the_title(); ?>
					</a>
				</h3>
				<small><?php the_date("l j F Y"); ?></small> 
				
				<div class="entry">
					<?php the_excerpt() ?>
				</div>
		
				<p class="postmetadata">
					<span class="category"><?php esc_html_e( "Filed under: "); ?> <?php the_category(', ') ?></span> 

					<?php if (has_tag()){ ?>
					<strong>|</strong>
					<span class="tags"><?php the_tags(esc_html__( 'Tags: '), ' &bull; ', ' '); ?></span><?php } ?>

					<?php if (get_terms('genre')){ ?>
					<strong>|</strong> 
					<span class="taxonomy"><?php echo get_the_term_list($wp_query->post->ID, 'imdblt_genre', esc_html__( 'Taxonomy: '), ', ', '' ); ?></span><?php } ?>
					<strong>|</strong> <?php edit_post_link('Edit','','<strong>|</strong>'); ?>  <?php comments_popup_link('No Comments &#187;', '1 Comment &#187;', '% Comments &#187;'); ?>
				</p>
			</div><?php

		}

	} else { // there is no post
			esc_html_e( 'No post found.'); 
			echo "<br /><br /><br />";
	} ?>

		
	</div>


<?php 	// call wordpress footer functions;
	wp_meta();
	//get_footer(); // this one gets too much uneeded information
	get_footer(); 
?>
