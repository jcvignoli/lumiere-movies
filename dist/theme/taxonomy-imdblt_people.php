<?php

/* Template Person: Taxonomy for Lumière! person wordpress plugin (set up for standard taxonomy) 
*  This file can be edited, renamed, and then copied in your theme folder or you also can
*  use the admin taxonomy interface to do it automatically
*
*  This template retrieves automatically the first occurence for the name utilized in the taxonomy
*/

// Start config class for imdbphp research
if (class_exists("\Lumiere\Settings")) {
	$config = new \Lumiere\Settings();
	$imdb_admin_values = $config->get_imdb_admin_option();
	$imdb_widget_values = $config->get_imdb_widget_option();
	$imdb_cache_values = $config->get_imdb_cache_option();
	$config->cachedir = $imdb_cache_values['imdbcachedir'] ?? NULL;
	$config->photodir = $imdb_cache_values['imdbphotoroot'] ?? NULL; // ?imdbphotoroot? Bug imdbphp?
	$config->imdb_img_url = $imdb_cache_values['imdbimgdir'] ?? NULL;
	$config->photoroot = $imdb_cache_values['imdbphotodir'] ?? NULL; // ?imdbphotodir? Bug imdbphp?
	$config->language = $imdb_admin_values['imdblanguage'] ?? NULL;

	// Get the tag name from the taxonomy in current page
	$name = single_tag_title('', false);

	// Get the info from imdbphp libraries
	if ( (class_exists("\Imdb\Person")) && !empty($name) && isset($name) ) {

		$search = new \Imdb\PersonSearch( $config );
		$results = $search->search( $name ) ?? NULL; # search for the person using the taxonomy tag
		$mid = $results[0]->imdbid() ?? NULL; # keep the first result only
		$mid_sanitized = intval( $mid ); # sanitize the first result
		$person = new \Imdb\Person( $mid_sanitized, $config ) ?? NULL; # search the profile using the first result
		$person_name_sanitized = sanitize_text_field( $person->name() ) ?? NULL;

	} else {

		$name = NULL;

	}

}
get_header();

echo "<br />";

// get_sidebar(); # unactivated, but you can uncomment it to activate!
?>

<main id="main" class="site-main clr" role="main">
	<div id="content-wrap" class="container clr">

<?php if (!is_null($person_name_sanitized)){ ?>

						<!-- Photo & identity -->
		<div class="lumiere_display_flex lumiere_font_em_11 lumiere_align_center">
			<div class="lumiere_flex_auto lumiere_width_eighty_perc">

<div class="imdbelementTITLE <?php  if (isset($imdb_widget_values['imdbintotheposttheme'])) echo ' imdbelementTITLE_' . $imdb_widget_values['imdbintotheposttheme']; ?>"><?php echo $person_name_sanitized; ?></div><?php  

				echo "\n\t\t" . '<div class="lumiere-lines-common';
				if (isset($imdb_widget_values['imdbintotheposttheme'])) echo ' lumiere-lines-common_' . $imdb_widget_values['imdbintotheposttheme'];
				echo '">';
				echo '<font size="-1">';

				# Birth
				$birthday = count($person->born() ) ? $person->born() : ""; 
				if ( (isset($birthday)) && (!empty($birthday)) ) {
					$birthday_day = (isset( $birthday["day"] ) ) ? intval($birthday["day"]) : "";
					$birthday_month = (isset( $birthday["month"] ) ) ? sanitize_text_field($birthday["month"]) : "";
					$birthday_year = (isset( $birthday["year"] ) ) ? intval($birthday["year"]) : "";

					echo "\n\t\t\t" . '<span class="imdbincluded-subtitle">'
						. esc_html__('Born on', 'lumiere-movies')."</span>"
						. $birthday_day . " " 
						. $birthday_month . " " 
						. $birthday_year ;
				}

				if ( (isset($birthday["place"])) && (!empty($birthday["place"])) ){ 
					echo ", ".esc_html__('in', 'lumiere-movies')." ".sanitize_text_field($birthday["place"]);
				}

				echo "\n\t\t" . '</font></div>';
				echo "\n\t\t" . '<div class="lumiere-lines-common';
				if (isset($imdb_widget_values['imdbintotheposttheme'])) echo ' lumiere-lines-common_' . $imdb_widget_values['imdbintotheposttheme'];
				echo '">';
				echo '<font size="-1">';

				# Death
				$death = (null !== $person->died() ) ? $person->died() : "";
				if ( (isset($death)) && (!empty($death)) ){

					echo "\n\t\t\t" . '<span class="imdbincluded-subtitle">' 
						. esc_html__('Died on', 'lumiere-movies')."</span>"
						.intval($death["day"])." "
						.sanitize_text_field($death["month"]) . " "
						.intval($death["year"]);

					if ( (isset($death["place"])) && (!empty($death["place"])) ) 
						echo ", ".esc_html__('in', 'lumiere-movies') . " " . sanitize_text_field($death["place"]);

					if ( (isset($death["cause"])) && (!empty($death["cause"])) )
						echo ", ".esc_html__('cause', 'lumiere-movies') . " " . sanitize_text_field($death["cause"]);
				}

				echo "\n\t\t" .'</font></div>';
				echo "\n\t\t" . '<div class="lumiere-lines-common';
				if (isset($imdb_widget_values['imdbintotheposttheme'])) echo ' lumiere-lines-common_' . $imdb_widget_values['imdbintotheposttheme'];
				echo '">';
				echo '<font size="-1">';

				# Biography
				$bio = $person->bio();
				$nbtotalbio = count($bio);

				if ( (isset($bio)) && (!empty($bio)) ) {
					echo "\n\t\t\t" . '<span class="imdbincluded-subtitle">' 
						. esc_html__('Biography', 'lumiere-movies') 
						. '</span>';

			    		if ( $nbtotalbio < 2 ) $idx = 0; else $idx = 1;

					$bio_text = sanitize_text_field( $bio[$idx]["desc"] );
					$click_text = esc_html__('click to expand', 'lumiere-movies');
					$max_length = 200; # number of characters

					if( strlen( $bio_text ) > $max_length) {

						$str_one = substr( $bio_text, 0, $max_length);
						$str_two = substr( $bio_text, $max_length, strlen( $bio_text ) );
						$final_text = "\n\t\t\t" . $str_one
							. "\n\t\t\t" .'<span class="activatehidesection"><strong>&nbsp;(' . $click_text . ')</strong></span> '
							. "\n\t\t\t" .'<span class="hidesection">' 
							. "\n\t\t\t" . $str_two 
							. "\n\t\t\t" .'</span>';
						echo $final_text;

					} else {

						echo $bio_text;

					}

				}?>

				</font></div>
			</div> 
					                           <!-- star photo -->
			<div class="lumiere_flex_auto lumiere_width_twenty_perc lumiere_padding_two"><?php 		

				if (($photo_url = $person->photo_localurl() ) != FALSE){ 

					echo '<a id="highslide_pic" href="'.esc_url($photo_url).'">';
					echo "\n\t\t" . '<img loading="eager" class="imdbincluded-picture" src="'
						.esc_url($photo_url)
						.'" alt="'
						.$person_name_sanitized.'" '; 

					// add width only if "Display only thumbnail" is on "no"
					if ($imdb_admin_values['imdbcoversize'] == FALSE)
						echo 'width="' . intval($imdb_admin_values['imdbcoversizewidth']) . 'px" />';

					echo '</a>'; 

				} else{
		 
					echo '<a id="highslide_pic">';
					echo "\n\t\t" 
						. '<img loading="eager" class="imdbincluded-picture" src="'
						.esc_url($imdb_admin_values['imdbplugindirectory']."pics/no_pics.gif")
						.'" alt="'
						.esc_html__('no picture', 'lumiere-movies')
						.'" '; 

					// add width only if "Display only thumbnail" is on "no"
					if ($imdb_admin_values['imdbcoversize'] == FALSE)
						echo 'width="' . intval($imdb_admin_values['imdbcoversizewidth']) . 'px" />';

					echo '</a>'; 
			      } ?>

			</div> 
		</div> 

						<!-- Photo & identity -->
		<div class="lumiere_display_flex lumiere_font_em_11 lumiere_align_center">
			<div class="lumiere_flex_auto lumiere_width_eighty_perc"><font size="-1"><?php  



			?></font></div> 
		</div> 

		<hr>

<?php	
} else { # end of section if a result was found for the taxonomy

	// No imdb result, so display a basic title	
	echo "\n\t\t".'<h1 class="pagetitle">'.esc_html__( 'Taxonomy for ', 'lumiere-movies') . ' ' . single_tag_title('', false). ' as <i>standard</i></h1>';

} 

	if ( have_posts() ) { // there is post
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
					<?php echo get_the_term_list($wp_query->post->ID, esc_html( $imdb_admin_values['imdburlstringtaxo'] ) . 'standard', esc_html__( 'Taxonomy: '), ', ', '' ); ?>
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
