<?php

/* Template Person: Taxonomy for Lumière! Movies WordPress plugin (set up for standard taxonomy) 
*  You can replace the occurences of the word s'tandard, rename this file, and then copy it in your theme folder
*  Or easier: just use Lumière admin interface to do it automatically
*
*  Version: 3.0
*
*  This template retrieves automaticaly the occurence of the name selected
*  If used along with Polylang WordPress plugin, a form is displayed to filter by available language 
*/

namespace Lumiere;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die('You can not call directly this page');
}

class Taxonomystandard {

	/* Set to true to activate the sidebar
	 *
	 */
	private const activate_sidebar = false; 

	/* Class \Lumiere\Utils
	 *
	 */
	private $utilsClass;

	/* Class \Lumiere\Settings
	 *
	 */
	private $configClass;

	/* Class \Monolog\Logger
	 *
	 */
	private $loggerClass;

	/* class \Imdb\Person
	 *
	 */
	private $personClass;

	/* Settings from class \Lumiere\Settings
	 *
	 */
	private $imdb_admin_values;

	/* Array of registered type of people from class \Lumiere\Settings
	 *
	 */
	private $array_people;

	/* Name of the person sanitized
	 *
	 */
	private $person_name_sanitized;

	/* Current page name from the tag taxonomy
	 *
	 */
	private $page_title;

	function __construct(){

		// Start Lumière config class
		if (class_exists("\Lumiere\Settings")) {

			$this->configClass = new \Lumiere\Settings();
			$this->imdb_admin_values = $this->configClass->imdb_admin_values;

			// Start the class Utils to activate debug
			$this->utilsClass = new \Lumiere\Utils();

			// List of potential parameters for a person
			$this->array_people = $this->configClass->array_people; # array

			// Build the current page name from the tag taxonomy
			$this->page_title = single_tag_title('', false);

			// Start the logger class
			add_action('init', [$this, 'lumiere_start_logger_wrapper'], 0);

			// Start the logger class
			add_action('init', [$this, 'lumiere_process_imdbphp_search'], 0);

			$this->layout();

		}

	}

	private function lumiere_process_imdbphp_search(){

		// Get the info from imdbphp libraries
		if ( (class_exists("\Imdb\Person")) && !empty($this->page_title) && isset($this->page_title) ) {

			$search = new \Imdb\PersonSearch( $this->configClass, $this->loggerClass );
			$results = $search->search( $this->page_title ) ?? NULL; # search for the person using the taxonomy tag
			$mid = $results[0]->imdbid() ?? NULL; # keep the first result only
			$mid_sanitized = intval( $mid ); # sanitize the first result
			$this->personClass = new \Imdb\Person( $mid_sanitized, $this->configClass, $this->loggerClass) ?? NULL; # search the profile using the first result
			$this->person_name_sanitized = sanitize_text_field( $this->personClass->name() ) ?? NULL;

		} else {

			$this->page_title = NULL;

		}
	}

	private function lumiere_start_logger_wrapper(){

		// Start logger class if debug is selected and user can manage options
		if ( (isset($this->imdb_admin_values['imdbdebug'])) && ($this->imdb_admin_values['imdbdebug'] == 1) && (current_user_can( 'manage_options' )) ){

			// Activate debug
			$this->utilsClass->lumiere_activate_debug($this->imdb_admin_values);

			// Start the logger
			$this->configClass->lumiere_start_logger('taxonomy-standard');
			$this->loggerClass = $this->configClass->loggerclass;

		} else {

			$this->loggerClass = NULL;
		}

	}

	private function layout(){

		get_header();

		$this->lumiere_start_logger_wrapper();

		$this->lumiere_process_imdbphp_search();

		$lumiere_taxonomy_full = esc_html( $this->imdb_admin_values['imdburlstringtaxo'] ) . 'standard';

		echo "<br />";

		if (self::activate_sidebar === true)
			get_sidebar(); # selected in settings above
		?>

		<main id="main" class="site-main clr" role="main">
			<div id="content-wrap" class="container clr">
		<?php if (!is_null($this->person_name_sanitized)){
			echo "\n\t\t\t\t\t\t\t\t\t\t\t" . '<!-- Photo & identity -->';
			echo "\n\t\t" . '<div class="lumiere_container lumiere_font_em_11 lumiere_align_center">';
			echo "\n\t\t\t" . '<div class="lumiere_flex_auto">';

			echo "\n\t\t\t\t" . '<div class="imdbelementTITLE ';
			if (isset($this->imdb_admin_values['imdbintotheposttheme'])){
				echo ' imdbelementTITLE_' . $this->imdb_admin_values['imdbintotheposttheme']; 
			}
			echo '">';
			echo $this->person_name_sanitized; 
			echo '</div>';


			echo "\n\n\t\t\t\t\t\t\t\t\t\t\t" . '<!-- star photo -->';

			echo "\n\t\t\t\t" . '<div class="lumiere-lines-common';
			if (isset($this->imdb_admin_values['imdbintotheposttheme'])) {
				echo ' lumiere-lines-common_' . $this->imdb_admin_values['imdbintotheposttheme'];
			}
			echo ' lumiere-padding-lines-common-picture">';


			$small_picture = $this->personClass->photo_localurl(false); // get small poster for cache
			$big_picture = $this->personClass->photo_localurl(true); // get big poster for cache
			$photo_url = isset($small_picture) ? $small_picture : $big_picture; // take the smaller first, the big if no small found
			if ( (isset($photo_url)) && (!empty($photo_url)) ){ 

				echo "\n\t\t\t\t\t" . '<a id="highslide_pic_popup" href="'.esc_url($photo_url).'">';
				echo "\n\t\t\t\t\t\t" . '<img loading="eager" class="imdbincluded-picture lumiere_float_right" src="'
					.esc_url($photo_url)
					.'" alt="'
					.$this->person_name_sanitized.'" '; 

				// add width only if "Display only thumbnail" is on "no"
				if ($this->imdb_admin_values['imdbcoversize'] == FALSE)
					echo 'width="' . intval($this->imdb_admin_values['imdbcoversizewidth']) . 'px" />';

				echo "\n\t\t\t\t\t" . '</a>'; 

			// No picture was downloaded, display "no picture"
			} else{

				echo "\n\t\t\t\t\t" . '<a id="highslide_pic">';
				echo  "\n\t\t\t\t\t\t" . '<img loading="eager" class="imdbincluded-picture lumiere_float_right" src="'
					.esc_url($this->imdb_admin_values['imdbplugindirectory']."pics/no_pics.gif")
					.'" alt="'
					.esc_html__('no picture', 'lumiere-movies')
					.'" '; 

				// add width only if "Display only thumbnail" is on "no"
				if ($this->imdb_admin_values['imdbcoversize'] == FALSE)
					echo 'width="' . intval($this->imdb_admin_values['imdbcoversizewidth']) . 'px" />';

				echo "\n\t\t\t\t\t" . '</a>'; 

			} 

			echo "\n\t\t\t\t" . '</div>';
			echo "\n\n\t\t\t\t\t\t\t\t\t\t\t" . '<!-- Birth -->';
			echo "\n\t\t\t\t" . '<div class="lumiere-lines-common';
			if (isset($this->imdb_admin_values['imdbintotheposttheme'])) echo ' lumiere-lines-common_' . $this->imdb_admin_values['imdbintotheposttheme'];
			echo '">';
			echo '<font size="-1">';

			# Birth
			$birthday = count($this->personClass->born() ) ? $this->personClass->born() : ""; 
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
			if (isset($this->imdb_admin_values['imdbintotheposttheme'])) echo ' lumiere-lines-common_' . $this->imdb_admin_values['imdbintotheposttheme'];
			echo '">';
			echo '<font size="-1">';

			# Death
			$death = (null !== $this->personClass->died() ) ? $this->personClass->died() : "";
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
			if (isset($this->imdb_admin_values['imdbintotheposttheme'])) echo ' lumiere-lines-common_' . $this->imdb_admin_values['imdbintotheposttheme'];
			echo ' lumiere-lines-common-fix">';
			echo '<font size="-1">';

			# Biography
			$bio = $this->personClass->bio();
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

			$this->lumiere_get_form_polylang_selection( $lumiere_taxonomy_full );

			echo "\n\t\t\t" . '<br />';

		} else { # end of section if a result was found for the taxonomy

			// No imdb result, so display a basic title	
			echo "\n\t\t".'<h1 class="pagetitle">'.esc_html__( 'Taxonomy for ', 'lumiere-movies') . ' ' . single_tag_title('', false). ' as <i>standard</i></h1>';

		} 

		// Language from the form
		$form_id_language = isset($_GET['tag_lang']) ? intval( $_GET['tag_lang'] ) : "";

		/* For every type of role (writer, director) do a WP Query Loop
		*
		*/

		// Var to include all rows and check if it is null
		$check_if_no_result = "";

		foreach ($this->array_people as $people) {

			// No value has been passed in the form
			if ( !isset($form_id_language) || empty($form_id_language) ){

				$args = array(
					'post_type'      => ['post', 'page'],
					'post_status'    => 'publish',
					'tax_query' => array(
						array(
							'taxonomy' => esc_html( $this->imdb_admin_values['imdburlstringtaxo'] ) . $people,
							'field'    => 'name',
							'terms'    => $this->person_name_sanitized,
						),
					),
				);

			// A value has been passed in the form
			} else {

				$args = array(
					'post_type'      => ['post', 'page'],
					'post_status'    => 'publish',
					'numberposts' => -1,
					'nopaging'    => true,
					'tax_query' => array(
						'relation' => 'AND',
						array(
							'taxonomy' => esc_html( $this->imdb_admin_values['imdburlstringtaxo'] ) . $people,
							'field'    => 'name',
							'terms'    => $this->person_name_sanitized,
						),
						array(
							'taxonomy' => 'language',
							'field'    => 'term_taxonomy_id',
							'terms'    => $form_id_language,
						),
					),
				);

			}

			// The Query
			$the_query = new \WP_Query( $args );
			 

			// The loop
			if ( $the_query->have_posts() )  { // there is post

				echo "\n\t\t\t\t" . '<h2 class="lumiere_italic lumiere_align_center">' . esc_html__( 'In the role of', 'lumiere-movies') . ' ' . $people . '</h2>';

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
						<?php } */
						?>	<div class="lumiere_display_flex">
								<div class="lumiere_padding_15">	
									<?php
						 				// Display the post's thumbnail
										if ($thumbnail = get_the_post_thumbnail('', '', array( 'class' => '' ))){
											echo $thumbnail;
										}
										echo "\n";
									?>
								</div>
								<div class="">
									<?php the_excerpt() ?>
								</div>
							</div>
							<p class="postmetadata lumiere_align_center lumiere_padding_five">
								<span class="category"><?php esc_html_e( "Filed under: ", 'lumiere-movies'); ?> <?php the_category(', ') ?></span>
		<?php					if ($the_query->has_tag()){ ?>
								<strong>|</strong>
								<span class="tags"><?php the_tags(esc_html__( 'Tags: ', 'lumiere-movies'), ' &bull; ', ' '); ?></span><?php 		
								echo "\n"; 
							} 
		?>						<strong>|</strong> <?php comments_popup_link('No Comments &#187;', '1 Comment &#187;', '% Comments &#187;'); 
		echo "\n";
		?>					</p>
						</div><?php

				}

			$check_if_no_result .= get_the_title();

			// there is no post
			} else { 

				$this->configClass->lumiere_maybe_log('debug', "[Lumiere][taxonomy_$lumiere_taxonomy_full] No post found for $this->person_name_sanitized in $people");

			} 

		}

		// Restore original Post Data
		wp_reset_postdata();

		/* If no results are found at all
		 * Say so!
		 */
		if ( (isset( $check_if_no_result )) && (empty( $check_if_no_result )) ){ 

			$this->configClass->lumiere_maybe_log('info', "[Lumiere][taxonomy_$lumiere_taxonomy_full] No post found for $this->person_name_sanitized in $lumiere_taxonomy_full");

			echo "<div class=\"lumiere_align_center lumiere_italic lumiere_padding_five\">No post written about $this->person_name_sanitized</div>";

		} ?>
			
			</div>
		</main>

<?php		wp_meta();

		get_footer(); 

	}


	 /**  Polylang form
	  **  Display a form to change the language if Polylang plugin is active
	  **
	  ** @ param string mandatory $taxonomy -> the current taxonomy to check and build the form according to it
	  **/
	private function lumiere_get_form_polylang_selection($taxonomy) {

		// Is Polylang plugin active?
		if (!function_exists('pll_is_translated_taxonomy') ) {

			$this->configClass->lumiere_maybe_log('debug', "[Lumiere][taxonomy_$taxonomy] Polylang is not active.");
			return;

		}

		// Is the current taxonomy, such as "lumiere_actor", registered and activated for translation?
		if ( pll_is_translated_taxonomy($taxonomy) ) {

			// Retrieve all Polylang languages taxonomy
			$pll_lang = get_terms('term_language', ['hide_empty' => false]);

			if (empty($pll_lang)){
				$this->configClass->lumiere_maybe_log('debug', "[Lumiere][taxonomy_$taxonomy] No Polylang language is set.");
				return;
			}

			// Build the form
			echo "\n\t\t\t" . '<div align="center">';
			echo "\n\t\t\t\t" . '<form method="get" id="lang_form" name="lang_form" action="#lang_form">';
			echo "\n\t\t\t\t\t" .'<select name="tag_lang" style="width:100px;">';

			echo "\n\t\t\t\t\t\t". '<option value="">'.esc_html__('All', 'lumiere-movies' ).'</option>';
			// Build an option html tag for every language
			foreach ($pll_lang as $lang){

				echo "\n\t\t\t\t\t\t".'<option value="'.intval($lang->term_id).'"';
				if ( (isset($_GET['tag_lang'])) && ( $_GET['tag_lang'] == intval($lang->term_id)) ) {
					echo 'selected="selected"';
				}
				echo ">".esc_html(ucfirst($lang->name))."</option>";
			}

			echo "\n\t\t\t\t\t".'</select>&nbsp;&nbsp;&nbsp;';

			echo "\n\t\t\t\t\t";
			wp_nonce_field('submit_lang', 'submit_lang'); 

			if (function_exists('submit_button')){
				echo "\n\t\t\t\t\t";
				submit_button( esc_html__('Filter language', 'lumiere-movies' ), 'primary', 'submit_lang', false);
			} else {
				echo "\n\t\t\t\t\t".'<input type="submit" class="button-primary" id="submit_lang" name="submit_lang" value="'.esc_html__('Filter language', 'lumiere-movies' ).'">'; 
			}

			echo "\n\t\t\t\t" . '</form>';

			echo "\n\t\t\t" . '</div>';
		} else {

			$this->configClass->lumiere_maybe_log('debug', "[Lumiere][taxonomy_$taxonomy][polylang plugin] No activated taxonomy found for $this->person_name_sanitized with $taxonomy.");
			return false;

		}

	}

}

$NewTaxonomystandard = new \Lumiere\Taxonomystandard();


