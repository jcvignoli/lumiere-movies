<?php

/* Template Person: Taxonomy for Lumière! person wordpress plugin (set up for standard taxonomy) 
*  This file can be edited, renamed, and then copied in your theme folder or you also can
*  use the admin taxonomy interface to do it automaticaly
*
*  Version: 2.0
*
*  This template retrieves automaticaly the occurence of the name selected
*  If used along with Polylang WordPress plugin, an automatic selection to filter by language will be displayed
*/

$activate_sidebar = false; # set on true to activate the sidebar

get_header();

// Start Lumière config class
if (class_exists("\Lumiere\Settings")) {

	$config = new \Lumiere\Settings();
	$imdb_admin_values = $config->imdb_admin_values;
	$imdb_widget_values = $config->imdb_widget_values;
	$imdb_cache_values = $config->imdb_cache_values;

	// Start logger class if debug is selected
	if ( (isset($config->imdb_admin_values['imdbdebug'])) && ($config->imdb_admin_values['imdbdebug'] == 1) ){

		// Start the class Utils to activate debug
		$utilsClass = new \Lumiere\Utils();
		$utilsClass->lumiere_activate_debug($imdb_admin_values);

		// If admin, store the class so we can use it later for imdbphp class call
		if ( current_user_can( 'manage_options' ) ) {
			// Start the logger
			$config->lumiere_start_logger('taxonomy-standard');

			$logger = $config->loggerclass;
		} else {
			$logger = NULL;
		}

	} 

	// List of potential parameters for a person
	$array_people = $config->array_people; # array

	// Get the tag name from the taxonomy in current page
	$name = single_tag_title('', false);

	// Get the info from imdbphp libraries
	if ( (class_exists("\Imdb\Person")) && !empty($name) && isset($name) ) {

		$search = new \Imdb\PersonSearch( $config, $logger );
		$results = $search->search( $name ) ?? NULL; # search for the person using the taxonomy tag
		$mid = $results[0]->imdbid() ?? NULL; # keep the first result only
		$mid_sanitized = intval( $mid ); # sanitize the first result
		$person = new \Imdb\Person( $mid_sanitized, $config, $logger) ?? NULL; # search the profile using the first result
		$person_name_sanitized = sanitize_text_field( $person->name() ) ?? NULL;

	} else {

		$name = NULL;

	}

}

$lumiere_taxonomy_full = esc_html( $imdb_admin_values['imdburlstringtaxo'] ) . 'standard';

echo "<br />";

if ($activate_sidebar === true)
	get_sidebar(); # selected in settings above
?>

<main id="main" class="site-main clr" role="main">
	<div id="content-wrap" class="container clr">
<?php if (!is_null($person_name_sanitized)){
						
	echo "\n\t\t\t\t\t\t\t\t\t\t\t" . '<!-- Photo & identity -->';
	echo "\n\t\t" . '<div class="lumiere_container lumiere_font_em_11 lumiere_align_center">';
	echo "\n\t\t\t" . '<div class="lumiere_flex_auto">';

	echo "\n\t\t\t\t" . '<div class="imdbelementTITLE ';
	if (isset($imdb_admin_values['imdbintotheposttheme'])){
		echo ' imdbelementTITLE_' . $imdb_admin_values['imdbintotheposttheme']; 
	}
	echo '">';
	echo $person_name_sanitized; 
	echo '</div>';

	echo "\n\n\t\t\t\t\t\t\t\t\t\t\t" . '<!-- star photo -->';

	echo "\n\t\t\t\t" . '<div class="lumiere-lines-common';
	if (isset($imdb_admin_values['imdbintotheposttheme'])) {
		echo ' lumiere-lines-common_' . $imdb_admin_values['imdbintotheposttheme'];
	}
	echo ' lumiere-padding-lines-common-picture">';

	$small_picture = $person->photo_localurl(false); // get small poster for cache
	$big_picture = $person->photo_localurl(true); // get big poster for cache
	$photo_url = isset($small_picture) ? $small_picture : $big_picture; // take the smaller first, the big if no small found
	if ( (isset($photo_url)) && (!empty($photo_url)) ){ 

		echo "\n\t\t\t\t\t" . '<a id="highslide_pic_popup" href="'.esc_url($photo_url).'">';
		echo "\n\t\t\t\t\t\t" . '<img loading="eager" class="imdbincluded-picture lumiere_float_right" src="'
			.esc_url($photo_url)
			.'" alt="'
			.$person_name_sanitized.'" '; 

		// add width only if "Display only thumbnail" is on "no"
		if ($imdb_admin_values['imdbcoversize'] == FALSE)
			echo 'width="' . intval($imdb_admin_values['imdbcoversizewidth']) . 'px" />';

		echo "\n\t\t\t\t\t" . '</a>'; 

	// No picture was downloaded, display "no picture"
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
			. '&#9788;&nbsp;'
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
			. '&#8224;&nbsp;'
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

	lumiere_get_form_polylang_selection( $lumiere_taxonomy_full );

	echo "\n\t\t\t" . '<br />';

} else { # end of section if a result was found for the taxonomy

	// No imdb result, so display a basic title	
	echo "\n\t\t".'<h1 class="pagetitle">'.esc_html__( 'Taxonomy for ', 'lumiere-movies') . ' ' . single_tag_title('', false). ' as <i>standard</i></h1>';

} 

// Language from the form
$language = isset($_GET['tag_lang']) ? sanitize_text_field($_GET['tag_lang']) : "";

/* For every type of role (writer, director) do a WP Query Loop
*
*/

// Var to include all rows and check if it is null
$check_if_no_result = "";

foreach ($array_people as $people) {

	// The query arguments
	$args = array(
		'lang' => $language,
		'tax_query' => array(
			array(
				'taxonomy' => esc_html( $imdb_admin_values['imdburlstringtaxo'] ) . $people,
				'field'    => 'name',
				'terms'    => $person_name_sanitized,
			),
		),
	);

	// The Query
	$the_query = new WP_Query( $args );
	 

	// The loop
	if ( $the_query->have_posts() )  { // there is post

		echo "\n\t\t\t\t" . '<h3 class="lumiere_italic lumiere_align_center">' . esc_html__( 'In the role of', 'lumiere-movies') . ' ' . $people . '</h3>';

		while ( $the_query->have_posts() ) { 
			$the_query->the_post(); ?>
			
			<div class="postList">
				<h3 id="post-<?php the_ID(); ?>">
					<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php esc_html_e( 'Open the blog ', 'lumiere-movies')?><?php the_title(); ?>">
						<?php the_title(); ?> <span class="lumiere_font_12">(<?php the_time("d/m/Y") ?>)</span>
					</a>
				</h3>

				<?php /* Too many results, deactivated
					if (get_terms( esc_html( $lumiere_taxonomy_full )){ ?>

				<div class="taxonomy"><?php
					esc_html_e( 'Taxonomy', 'lumiere-movies' ); 
					echo " $people:";
					echo get_the_term_list(get_the_ID(), $lumiere_taxonomy_full, ' ', ', ', '' ); ?>
				<br /><br />
				</div>
				<?php } */?>	
				
				<div class="lumiere_display_flex">
					<div class="lumiere_padding_15">	
						<?php
			 				// Display the post's thumbnail
							echo get_the_post_thumbnail('', '', array( 'class' => '' )); 
						?>
					</div>
					<div class="">
						<?php the_excerpt() ?>
					</div>
				</div>
		
				<p class="postmetadata lumiere_align_center lumiere_padding_five">
					<span class="category"><?php esc_html_e( "Filed under: ", 'lumiere-movies'); ?> <?php the_category(', ') ?></span> 

					<?php if ($the_query->has_tag()){ ?>
					<strong>|</strong>
					<span class="tags"><?php the_tags(esc_html__( 'Tags: ', 'lumiere-movies'), ' &bull; ', ' '); ?></span><?php } ?>

					<strong>|</strong> <?php edit_post_link('Edit','','<strong>|</strong>'); ?>  <?php comments_popup_link('No Comments &#187;', '1 Comment &#187;', '% Comments &#187;'); ?>
				</p>
			</div><?php

		}

	$check_if_no_result .= get_the_title();

	// there is no post
	} else { 

		if($logger !== NULL) {
			$logger->debug("[Lumiere][taxonomy] No post found for $person_name_sanitized in $people");
		}

	} 

}

/* Restore original Post Data */
wp_reset_postdata();

/* If no results are found at all
 *
 */
if ( (isset( $check_if_no_result )) && (empty( $check_if_no_result )) ){ 

	if($logger !== NULL) {
		$logger->debug("[Lumiere] No post found for $person_name_sanitized about $people");
	}

	echo "<div class=\"lumiere_align_center lumiere_italic lumiere_padding_five\">No post written about $person_name_sanitized</div>";

} ?>
	
	</div>
</main>

<?php

wp_meta();

get_footer(); 


 /**  Polylang form
  **  Display a form to change the language if Polylang plugin is active
  **
  ** @ param string mandatory $taxonomy -> the current taxonomy to check and build the form according to it
  **/
function lumiere_get_form_polylang_selection($taxonomy) {

	global $logger, $person_name_sanitized;

	// Is Polylang plugin active?
	if (!function_exists('pll_is_translated_taxonomy') ) {

		if($logger !== NULL) {
			$logger->debug("[taxonomy] Polylang is not active.");
		}
		return;

	}

	// Is the current taxonomy, such as "lumiere_actor", registered and activated for translation?
	if ( pll_is_translated_taxonomy($taxonomy) ) {

		// Retrieve all languages
		$polylang_array_lang_list = pll_languages_list();

		// Build the form
		echo "\n\t\t\t" . '<div align="center">';
		echo "\n\t\t\t\t" . '<form method="get" id="lang_form" name="lang_form" action="' . esc_url( $_SERVER[ "REQUEST_URI" ] ) . '">';
		echo "\n\t\t\t\t\t" .'<select name="tag_lang" style="width:100px;">';

		// Build an option html tag for every language
		foreach ($polylang_array_lang_list as $lang){
			$lang = esc_html( $lang );
			echo "<option value=\"$lang\"";
			if( $_GET['tag_lang'] == $lang ) 
				echo 'selected="selected"';
			echo ">$lang</option>";
		}

		echo '</select>&nbsp;&nbsp;&nbsp;';

		wp_nonce_field('submit_lang', 'submit_lang'); 

		submit_button( esc_html__('Change language', 'lumiere-movies' ), 'primary', 'submit_lang', false);

		echo '</form>';

		echo "\n\t\t\t" . '</div>';
	} else {

		if($logger !== NULL){
			$logger->debug("[Lumiere][taxonomy][polylang plugin] No activated taxonomy found for $person_name_sanitized with $taxonomy.");
		}
		return false;

	}

}
?>
