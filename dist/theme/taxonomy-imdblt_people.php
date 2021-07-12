<?php

/* Template Person: Taxonomy for Lumière! person wordpress plugin (set up for standard taxonomy) 
*  This file can be edited, renamed, and then copied in your theme folder or you also can
*  use the admin taxonomy interface to do it automatically
*
*  This template retrieves automatically the first occurence for the name utilized in the taxonomy
*/

// Start Lumière config class
if (class_exists("\Lumiere\Settings")) {
	$config = new \Lumiere\Settings();
	$imdb_admin_values = $config->imdb_admin_values;
	$imdb_widget_values = $config->imdb_widget_values;
	$imdb_cache_values = $config->imdb_cache_values;

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
<?php if (!is_null($person_name_sanitized)){
						
	echo "\n\t\t\t\t\t\t\t\t\t\t\t" . '<!-- Photo & identity -->';
	echo "\n\t\t" . '<div class="lumiere_container lumiere_font_em_11 lumiere_align_center">';
	echo "\n\t\t\t" . '<div class="lumiere_flex_auto">';

	echo "\n\t\t\t\t" . '<div class="imdbelementTITLE ';
	if (isset($imdb_admin_values['imdbintotheposttheme']))
		echo ' imdbelementTITLE_' . $imdb_admin_values['imdbintotheposttheme']; 
	echo '">';
	echo $person_name_sanitized; 
	echo '</div>';

	echo "\n\n\t\t\t\t\t\t\t\t\t\t\t" . '<!-- star photo -->';

	echo "\n\t\t\t\t" . '<div class="lumiere-lines-common';
	if (isset($imdb_admin_values['imdbintotheposttheme'])) echo ' lumiere-lines-common_' . $imdb_admin_values['imdbintotheposttheme'];
	echo ' lumiere-padding-lines-common-picture">';

	if (($photo_url = $person->photo_localurl() ) != FALSE){ 

		echo "\n\t\t\t\t\t" . '<a id="highslide_pic" href="'.esc_url($photo_url).'">';
		echo "\n\t\t\t\t\t\t" . '<img loading="eager" class="imdbincluded-picture lumiere_float_right" src="'
			.esc_url($photo_url)
			.'" alt="'
			.$person_name_sanitized.'" '; 

		// add width only if "Display only thumbnail" is on "no"
		if ($imdb_admin_values['imdbcoversize'] == FALSE)
			echo 'width="' . intval($imdb_admin_values['imdbcoversizewidth']) . 'px" />';

		echo "\n\t\t\t\t\t" . '</a>'; 

	} else{

		echo "\n\t\t\t\t\t" . '<a id="highslide_pic">';
		echo  "\n\t\t\t\t\t\t" . '<img loading="eager" class="imdbincluded-picture lumiere_float_right" src="'
			.esc_url($imdb_admin_values['imdbplugindirectory']."pics/no_pics.gif")
			.'" alt="'
			.esc_html__('no picture', 'lumiere-movies')
			.'" '; 

		// add width only if "Display only thumbnail" is on "no"
		if ($imdb_admin_values['imdbcoversize'] == FALSE)
			echo 'width="' . intval($imdb_admin_values['imdbcoversizewidth']) . 'px" />';

		echo "\n\t\t\t\t\t" . '</a>'; 
      } 

	echo "\n\t\t\t\t" . '</div>';
	echo "\n\n\t\t\t\t\t\t\t\t\t\t\t" . '<!-- Birth -->';
	echo "\n\t\t\t\t" . '<div class="lumiere-lines-common';
	if (isset($imdb_admin_values['imdbintotheposttheme'])) echo ' lumiere-lines-common_' . $imdb_admin_values['imdbintotheposttheme'];
	echo '">';
	echo '<font size="-1">';

	# Birth
	$birthday = count($person->born() ) ? $person->born() : ""; 
	if ( (isset($birthday)) && (!empty($birthday)) ) {
		$birthday_day = (isset( $birthday["day"] ) ) ? intval($birthday["day"]) : "";
		$birthday_month = (isset( $birthday["month"] ) ) ? sanitize_text_field($birthday["month"]) : "";
		$birthday_year = (isset( $birthday["year"] ) ) ? intval($birthday["year"]) : "";

		echo "\n\t\t\t\t\t" . '<span class="imdbincluded-subtitle">'
			. esc_html__('Born on', 'lumiere-movies')."</span>"
			. $birthday_day . " " 
			. $birthday_month . " " 
			. $birthday_year ;
	} else {
		echo '&nbsp;';
	}

	if ( (isset($birthday["place"])) && (!empty($birthday["place"])) ){ 
		echo ", ".esc_html__('in', 'lumiere-movies')." ".sanitize_text_field($birthday["place"]);
	}

	echo "\n\t\t\t\t" . '</font></div>';
	echo "\n\n\t\t\t\t\t\t\t\t\t\t\t" . '<!-- Death -->';
	echo "\n\t\t\t\t" . '<div class="lumiere-lines-common';
	if (isset($imdb_admin_values['imdbintotheposttheme'])) echo ' lumiere-lines-common_' . $imdb_admin_values['imdbintotheposttheme'];
	echo '">';
	echo '<font size="-1">';

	# Death
	$death = (null !== $person->died() ) ? $person->died() : "";
	if ( (isset($death)) && (!empty($death)) ){

		echo "\n\t\t\t\t\t" . '<span class="imdbincluded-subtitle">' 
			. esc_html__('Died on', 'lumiere-movies')."</span>"
			.intval($death["day"])." "
			.sanitize_text_field($death["month"]) . " "
			.intval($death["year"]);

		if ( (isset($death["place"])) && (!empty($death["place"])) ) 
			echo ", ".esc_html__('in', 'lumiere-movies') . " " . sanitize_text_field($death["place"]);

		if ( (isset($death["cause"])) && (!empty($death["cause"])) )
			echo ", ".esc_html__('cause', 'lumiere-movies') . " " . sanitize_text_field($death["cause"]);
	} else {
		echo '&nbsp;';
	}

	echo "\n\t\t\t\t" .'</font></div>';
	echo "\n\n\t\t\t\t\t\t\t\t\t\t\t" . '<!-- Biography -->';
	echo "\n\t\t\t\t" . '<div class="lumiere-lines-common';
	if (isset($imdb_admin_values['imdbintotheposttheme'])) echo ' lumiere-lines-common_' . $imdb_admin_values['imdbintotheposttheme'];
	echo ' lumiere-lines-common-fix">';
	echo '<font size="-1">';

	# Biography
	$bio = $person->bio();
	$nbtotalbio = count($bio);

	if ( (isset($bio)) && (!empty($bio)) ) {
		echo "\n\t\t\t\t\t" . '<span class="imdbincluded-subtitle">' 
			. esc_html__('Biography', 'lumiere-movies') 
			. '</span>';

    		if ( $nbtotalbio < 2 ) $idx = 0; else $idx = 1;

		$bio_text = sanitize_text_field( $bio[$idx]["desc"] );
		$click_text = esc_html__('click to expand', 'lumiere-movies');
		$max_length = 300; # number of characters

		if( strlen( $bio_text ) > $max_length) {

			$str_one = substr( $bio_text, 0, $max_length);
			$str_two = substr( $bio_text, $max_length, strlen( $bio_text ) );
			$final_text = "\n\t\t\t\t\t" . $str_one
				. "\n\t\t\t\t\t" .'<span class="activatehidesection"><strong>&nbsp;(' . $click_text . ')</strong></span> '
				. "\n\t\t\t\t\t" .'<span class="hidesection">' 
				. "\n\t\t\t\t\t" . $str_two 
				. "\n\t\t\t\t\t" .'</span>';
			echo $final_text;

		} else {

			echo $bio_text;

		}

	} else {
		echo '&nbsp;';
	}


	echo "\n\t\t\t\t\t" . '</font></div>';
	echo "\n\t\t\t\t" . '</div>';
	echo "\n\t\t\t" . '</div>';
	echo "\n\t\t\t" . '<br />';
	echo "\n\t\t\t" . '<h2 align="center">' . esc_html__( 'Mentioned in:', 'lumiere-movies') . '</h2>';
	echo "\n\n\t\t\t" . '<hr>' . "\n";


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

				<div class="taxonomy"><?php
					esc_html_e( 'Taxonomy', 'lumiere-movies' ); 
					echo ' standard:';
					echo get_the_term_list(get_the_ID(), esc_html( $imdb_admin_values['imdburlstringtaxo'] ) . 'standard', ' ', ', ', '' ); ?>
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
