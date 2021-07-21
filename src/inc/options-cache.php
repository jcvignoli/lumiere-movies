<?php

 #############################################################################
 # Lumière! Movies WordPress Plugin                                          #
 # written by Lost Highway                                                   #
 # https://www.jcvignoli.com/blog                                            #
 # ------------------------------------------------------------------------- #
 # This program is free software; you can redistribute and/or modify it      #
 # under the terms of the GNU General Public License (see LICENSE)           #
 # ------------------------------------------------------------------------- #
 #									              #
 #  Function : Cache management				                     #
 #									              #
 #############################################################################

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die(esc_html__("You are not allowed to call this page directly.", "lumiere-movies"));
}

// Vars
global $imdb_cache_values;
$allowed_html_for_esc_html_functions = [
    'strong',
];

// If $_GET["msg"] is found, display a related notice
 	/* get the $_GET */
if ((isset($_GET['msg'])) && array_key_exists( sanitize_text_field( $_GET['msg'] ), $messages) ){
	if (sanitize_text_field($_GET['msg'])=="cache_options_update_success_msg") {
		echo $utils->lumiere_notice(1, esc_html__( $messages["cache_options_update_success_msg"], 'lumiere-movies' ) );
	} elseif (sanitize_text_field($_GET['msg'])=="cache_options_refresh_success_msg") {
		echo $utils->lumiere_notice(1, esc_html__( $messages["cache_options_refresh_success_msg"], 'lumiere-movies' ) );
	} elseif (sanitize_text_field($_GET['msg'])=="cache_delete_all_msg") {
		echo $utils->lumiere_notice(1, esc_html__( $messages["cache_delete_all_msg"], 'lumiere-movies' ) );
	} elseif (sanitize_text_field($_GET['msg'])=="cache_delete_ticked_msg") {
		echo $utils->lumiere_notice(1, esc_html__( $messages["cache_delete_ticked_msg"], 'lumiere-movies' ) );
	} elseif (sanitize_text_field($_GET['msg'])=="cache_delete_individual_msg") {
		echo $utils->lumiere_notice(1, esc_html__( $messages["cache_delete_individual_msg"], 'lumiere-movies' ) );
	} elseif (sanitize_text_field($_GET['msg'])=="cache_refresh_individual_msg") {
		echo $utils->lumiere_notice(1, esc_html__( $messages["cache_refresh_individual_msg"], 'lumiere-movies' ) );
	}
}
	/* message notification options */
$messages = array(
	'cache_options_update_success_msg' => 'Cache options saved.',
	'cache_options_refresh_success_msg' => 'Cache options successfully reset.',
	'cache_delete_all_msg' => 'All cache files deleted.',
	'cache_delete_ticked_msg' => 'Selected ticked file(s) deleted.',
	'cache_delete_individual_msg' => 'Selected cache file successfully deleted.',
	'cache_refresh_individual_msg' => 'Selected cache file successfully refreshed.'
);

// Start config class for imdbphp
use \Imdb\Title;
use \Imdb\Person;

// Enter in debug mode, for development version only
if ((isset($imdb_admin_values['imdbdebug'])) && ($imdb_admin_values['imdbdebug'] == "1")) {

	// Start the class Utils to activate debug -> already started in admin_pages
	$utils->lumiere_activate_debug($imdb_cache_values, 'no_var_dump', NULL); # don't display set_error_handler("var_dump") that gets the page stuck in an endless loop

}

// Data is posted using the form
if (current_user_can( 'manage_options' ) ) { 

	##################################### Saving options

	// save data selected
	if ( (isset($_POST['update_cache_options'])) && (check_admin_referer('cache_options_check', 'cache_options_check')) ) { 

		foreach ($_POST as $key=>$postvalue) {
			// Sanitize
			$key_sanitized = sanitize_key($key);

			$keynoimdb = str_replace ( "imdb_", "", $key_sanitized);
			if (isset($_POST["$key_sanitized"])) {
				$imdb_cache_values["$keynoimdb"] = sanitize_text_field($_POST["$key_sanitized"]);
			}
		}

		update_option($config->imdbCacheOptionsName, $imdb_cache_values);

		// display message on top
		echo $utils->lumiere_notice(1, '<strong>'. esc_html__( 'Cache options saved.', 'lumiere-movies') .'</strong>');
		if (!headers_sent()) {
			/* 2021 07 06 Shouldn't do anything here, to be removed
			//header("Refresh: 0;url=".$_SERVER[ "REQUEST_URI"]."&reset=true", false);
			wp_safe_redirect( add_query_arg( "msg", "cache_options_update_success_msg", wp_get_referer() ) ); 
			exit();*/
		} else {
			echo $utils->lumiere_notice(1, '<a href="'.wp_get_referer() .'">'. esc_html__( 'Go back', 'lumiere-movies') .'</a>');
			exit();
		}
	}

	// reset options selected
	if ( (isset($_POST['reset_cache_options'])) && (check_admin_referer('cache_options_check', 'cache_options_check')) ){ 

		delete_option($config->imdbCacheOptionsName);

		// display message on top
		echo $utils->lumiere_notice(1, '<strong>'. esc_html__( 'Cache options reset.', 'lumiere-movies') .'</strong>');

		// Display a refresh link otherwise refreshed data is not seen
		if (!headers_sent()){
			/* 2021 07 06 Shouldn't do anything here, to be removed
			wp_safe_redirect( add_query_arg( "msg", "cache_options_refresh_success_msg", wp_get_referer() ) ); 
			exit();*/
		} else {
			echo $utils->lumiere_notice(1, '<a href="'.wp_get_referer() .'">'. esc_html__( 'Go back', 'lumiere-movies') .'</a>');
			exit();
		}
	}

	// delete all cache files
	if ( (isset($_POST['delete_all_cache'])) && (check_admin_referer('cache_all_and_query_check', 'cache_all_and_query_check')) ){  

		// prevent drama
		if ( (!isset($imdb_cache_values['imdbcachedir'])) || ( is_null($imdb_cache_values['imdbcachedir'])) )
			wp_die( $utils->lumiere_notice(3, '<strong>'. esc_html__( 'No cache folder found.', 'lumiere-movies') .'</strong>') );
		
		// Delete cache
		$utils->lumiere_unlinkRecursive( $imdb_cache_values['imdbcachedir'] );

		// display message on top
		echo $utils->lumiere_notice(1, '<strong>'. esc_html__( 'All cache files deleted.', 'lumiere-movies') .'</strong>');
		if (!headers_sent){
			/* 2021 07 06 Shouldn't do anything here, to be removed
			wp_safe_redirect( add_query_arg( "msg", "cache_delete_all_msg", wp_get_referer() ) );
			exit();*/
		} else {
			echo $utils->lumiere_notice(1, '<a href="'. wp_get_referer() .'">'. esc_html__( 'Go back', 'lumiere-movies') .'</a>');
			exit();
		}
	}

	// delete all cache files
	if ( (isset($_POST['delete_query_cache'])) && (check_admin_referer('cache_all_and_query_check', 'cache_all_and_query_check')) ){  

		// prevent drama
		if ( is_null($imdb_cache_values['imdbcachedir']))
			wp_die( $utils->lumiere_notice(3, '<strong>'. esc_html__( 'No cache folder found.', 'lumiere-movies') .'</strong>') );
		
		// Delete cache
		$files_query = glob($imdb_cache_values['imdbcachedir'] . "find.s*") ?? NULL;
		foreach ( $files_query as $cacheTOdelete) {

			// if file doesn't exist
			if  ( (!isset($cacheTOdelete)) || (is_null($cacheTOdelete)) || (count($cacheTOdelete) < 1) ) {
				echo $utils->lumiere_notice(3, esc_html__( 'No query files found.', 'lumiere-movies')) ;
				echo $utils->lumiere_notice(1, '<a href="'. wp_get_referer() .'">'. esc_html__( 'Go back', 'lumiere-movies') .'</a>');
				wp_die( ) ;
			}

			if($cacheTOdelete == $imdb_cache_values['imdbcachedir'].'.' || $cacheTOdelete == $imdb_cache_values['imdbcachedir'].'..')
				continue; 

			// the file exist, is not . or .., so delete!
			unlink( $cacheTOdelete );
		}

		// display messages on top
		echo $utils->lumiere_notice(1, '<strong>'. esc_html__( 'Query cache files deleted.', 'lumiere-movies') .'</strong>');
		if (!headers_sent){
			/* 2021 07 06 Shouldn't do anything here, to be removed
			wp_safe_redirect( add_query_arg( "msg", "cache_delete_query_msg", wp_get_referer() ) );
			exit();*/
		} else {
			echo $utils->lumiere_notice(1, '<a href="'. wp_get_referer() .'">'. esc_html__( 'Go back', 'lumiere-movies') .'</a>');
			exit();
		}
	}


	##################################### delete several ticked files

	if ( (isset($_POST['delete_ticked_cache'])) && (check_admin_referer('cache_options_check', 'cache_options_check')) ){  

		// prevent drama
		if ( is_null($imdb_cache_values['imdbcachedir']))
			wp_die( $utils->lumiere_notice(3, '<strong>'. esc_html__( 'No cache folder found.', 'lumiere-movies') .'</strong>') );


		// for movies
		if (isset($_POST ['imdb_cachedeletefor_movies'])) {
			for ($i = 0; $i < count ($_POST ['imdb_cachedeletefor_movies']); $i++) {
				$id_sanitized = sanitize_key( $_POST['imdb_cachedeletefor_movies'][$i] );

				foreach ( glob($imdb_cache_values['imdbcachedir'].'title.tt'.$id_sanitized."*") as $cacheTOdelete) {
					if($cacheTOdelete == $imdb_cache_values['imdbcachedir'].'.' || $cacheTOdelete == $imdb_cache_values['imdbcachedir'].'..') {
						continue;
					}
					if (file_exists($cacheTOdelete )) {
						unlink( esc_url( $cacheTOdelete ));
					}  else {
						wp_die( $utils->lumiere_notice(3, esc_html__( 'This file does not exist.', 'lumiere-movies')) ) ;
					}
				}
			}

			// delete pictures, small and big
			$pic_small_sanitized = $imdb_cache_values['imdbphotoroot'].$id_sanitized.".jpg" ?? NULL;
			$pic_big_sanitized = $imdb_cache_values['imdbphotoroot'].$id_sanitized."_big.jpg" ?? NULL;
			if ( file_exists($pic_small_sanitized) )
				unlink( $pic_small_sanitized );
			if ( file_exists($pic_big_sanitized) )
				unlink( $pic_big_sanitized );
		}

		// for people
		if (isset($_POST ['imdb_cachedeletefor_people'])) {
			for ($i = 0; $i < count ($_POST ['imdb_cachedeletefor_people']); $i++) {

				$id_sanitized = sanitize_key( $_POST['imdb_cachedeletefor_people'][$i] );

				foreach ( glob($imdb_cache_values['imdbcachedir'].'name.nm'.$id_sanitized."*") as $cacheTOdelete) {
					if($cacheTOdelete == $imdb_cache_values['imdbcachedir'].'.' || $cacheTOdelete == $imdb_cache_values['imdbcachedir'].'..') {
						continue;
					}
					if (file_exists($cacheTOdelete )) {
						unlink( esc_url( $cacheTOdelete ));
					}  else {
						wp_die( $utils->lumiere_notice(3, esc_html__( 'This file does not exist.', 'lumiere-movies')) ) ;
					}
				}

			}
		}

		// display message on top
		echo $utils->lumiere_notice(1, esc_html__( 'Selected ticked cache file(s) deleted.', 'lumiere-movies') );
		if (!headers_sent){
			/* 2021 07 06 Shouldn't do anything here, to be removed
			wp_safe_redirect( add_query_arg( "msg", "cache_delete_ticked_msg", wp_get_referer() ) );
			exit();*/
		} else {
			echo $utils->lumiere_notice(1, '<div align="center"><a href="'. wp_get_referer() .'">'. esc_html__( 'Go back', 'lumiere-movies') .'</a></div>');
			exit();
		}

	}

	##################################### delete a specific file by clicking on it

	if ( (isset($_GET['dothis'])) && ($_GET['dothis'] == 'delete') && ($_GET['type'])) {

		// prevent drama
		if ( (is_null($imdb_cache_values['imdbcachedir'])) || (!is_numeric($_GET['where']))  )
			exit( esc_html__("Cannot work this way.", 'lumiere-movies') );

		// delete single movie
		if (($_GET['type'])== 'movie')  {

			$id_sanitized =  sanitize_key( $_GET['where'] ) ?? NULL;
			$name_sanitized = glob($imdb_cache_values['imdbcachedir'].'title.tt'.$id_sanitized."*") ?? NULL;

			// if file doesn't exist
			if  ( (is_null($name_sanitized)) || (count($name_sanitized) < 1) )
				wp_die( $utils->lumiere_notice(3, esc_html__( 'This file does not exist.', 'lumiere-movies')) ) ;

			foreach ( $name_sanitized as $cacheTOdelete) {

				if($cacheTOdelete == $imdb_cache_values['imdbcachedir'].'.' || $cacheTOdelete == $imdb_cache_values['imdbcachedir'].'..') {
					continue;
				}

				unlink( esc_url( $cacheTOdelete ));
			}

			// delete pictures, small and big
			$pic_small_sanitized = $imdb_cache_values['imdbphotoroot'].$id_sanitized.".jpg" ?? NULL;
			$pic_big_sanitized = $imdb_cache_values['imdbphotoroot'].$id_sanitized."_big.jpg" ?? NULL;
			if ( file_exists($pic_small_sanitized) )
				unlink( $pic_small_sanitized );
			if ( file_exists($pic_big_sanitized) )
				unlink( $pic_big_sanitized );
		}

		// delete single person
		if (($_GET['type'])== 'people') {

			$id_sanitized =  sanitize_key( $_GET['where'] ) ?? NULL;
			$name_sanitized = glob($imdb_cache_values['imdbcachedir'].'name.nm'.$id_sanitized."*") ?? NULL;

			// if file doesn't exist
			if  ( (is_null($name_sanitized)) || (count($name_sanitized) < 1) )
				wp_die( $utils->lumiere_notice(3, esc_html__( 'This file does not exist.', 'lumiere-movies')) ) ;

			foreach ( $name_sanitized as $cacheTOdelete) {

				if($cacheTOdelete == $imdb_cache_values['imdbcachedir'].'.' || $cacheTOdelete == $imdb_cache_values['imdbcachedir'].'..') {
					continue;
				}

				unlink( esc_url( $cacheTOdelete ));
			}
		}

		echo $utils->lumiere_notice(1, esc_html__( 'Selected cache file deleted.', 'lumiere-movies') );
	}


	##################################### refresh a specific file by clicking on it

	if ( (isset($_GET['dothis'])) && ($_GET['dothis'] == 'refresh') && (isset($_GET['type'])) ) {

		// prevent drama
		if ( (is_null($imdb_cache_values['imdbcachedir'])) || (!is_numeric($_GET['where']))  )
			exit( esc_html__("Cannot work this way.", 'lumiere-movies') );

		if ( ($_GET['type']) == 'movie') {
			$id_sanitized = filter_var( $_GET["where"], FILTER_SANITIZE_NUMBER_INT) ?? NULL;

			$name_sanitized = glob($imdb_cache_values['imdbcachedir'].'title.tt'.$id_sanitized."*") ?? NULL;

			// if file doesn't exist
			if  ( (is_null($name_sanitized)) || (count($name_sanitized) < 1) )
				wp_die( $utils->lumiere_notice(3, esc_html__( 'This file does not exist.', 'lumiere-movies')) ) ;

			foreach ( $name_sanitized as $cacheTOdelete) {

				if($cacheTOdelete == $imdb_cache_values['imdbcachedir'].'.' || $cacheTOdelete == $imdb_cache_values['imdbcachedir'].'..') {
					continue;
				}

				unlink( esc_url( $cacheTOdelete ));
			}

			// delete pictures, small and big
			$pic_small_sanitized = $imdb_cache_values['imdbphotoroot'].$id_sanitized.".jpg" ?? NULL;
			$pic_big_sanitized = $imdb_cache_values['imdbphotoroot'].$id_sanitized."_big.jpg" ?? NULL;
			if ( file_exists($pic_small_sanitized) )
				unlink( $pic_small_sanitized );
			if ( file_exists($pic_big_sanitized) )
				unlink( $pic_big_sanitized );

			// get again the movie
			$movie = new \Imdb\Title($id_sanitized, $config, $logger);

			// create cache for everything
			$movie->alsoknow(); $movie->cast(); $movie->colors(); $movie->composer(); $movie->comment_split(); $movie->country(); $movie->creator(); $movie->director(); $movie->genres(); $movie->goofs(); $movie->keywords(); $movie->languages(); $movie->officialSites(); $movie->photo_localurl(true); $movie->photo_localurl(false); $movie->plot(); $movie->prodCompany(); $movie->producer(); $movie->quotes(); $movie->rating(); $movie->runtime(); $movie->soundtrack(); $movie->taglines(); $movie->title(); $movie->trailers(TRUE); $movie->votes(); $movie->writing(); $movie->year();

		}

		if (($_GET['type'])== 'people') {

			$id_people_sanitized =  $_GET['where'] ?? NULL;
			$name_people_sanitized = glob($imdb_cache_values['imdbcachedir'].'name.nm'.$id_people_sanitized."*") ?? NULL;

			// if file doesn't exist
			if  ( (is_null($name_people_sanitized)) || (count($name_people_sanitized) < 1) )
				wp_die( $utils->lumiere_notice(3, esc_html__( 'This file does not exist.', 'lumiere-movies')) ) ;

			foreach ( $name_people_sanitized as $cacheTOdelete) {

				if($cacheTOdelete == $imdb_cache_values['imdbcachedir'].'.' || $cacheTOdelete == $imdb_cache_values['imdbcachedir'].'..') {
					continue;
				}

				unlink( esc_url( $cacheTOdelete ));

			}

			// get again the person
			$person = new \Imdb\Person($id_people_sanitized, $config, $logger);

			// Create cache for everything
			$person->bio(); $person->birthname();$person->born();$person->died();	$person->movies_all(); $person->movies_archive(); $person->movies_soundtrack(); $person->movies_writer(); $person->name(); $person->photo_localurl(); $person->pubmovies(); $person->pubportraits(); $person->quotes(); $person->trivia(); $person->trademark();

		}

		echo $utils->lumiere_notice(1, esc_html__( 'Selected cache file successfully refreshed.', 'lumiere-movies') );
	}


##################################### Cache option page
?>

<div id="tabswrap">
	<div class="imdblt_double_container lumiere_padding_five">
		<div class="lumiere_flex_auto lumiere_align_center"><img src="<?php echo esc_url( plugin_dir_url( __DIR__ ) . "pics/admin-cache-options.png"); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e("Cache options", 'lumiere-movies');?>" href="<?php echo esc_url( admin_url().'admin.php?page=imdblt_options&subsection=cache&cacheoption=option'); ?>"><?php esc_html_e( 'Cache options', 'lumiere-movies'); ?></a></div>
 		<?php 
	if ($imdb_cache_values['imdbusecache'] == "1") { ?>
		<div class="lumiere_flex_auto lumiere_align_center">&nbsp;&nbsp;<img src="<?php echo esc_url( plugin_dir_url( __DIR__ ) . "pics/admin-cache-management.png"); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e("Manage Cache", 'lumiere-movies');?>" href="<?php echo esc_url( admin_url().'admin.php?page=imdblt_options&subsection=cache&cacheoption=manage'); ?>"><?php esc_html_e( "Manage Cache", 'lumiere-movies'); ?></a></div>
<?php 
	}; ?>
	</div>
</div>

<div id="poststuff" class="metabox-holder">

	<div class="intro_cache"><?php esc_html_e( "Cache is crucial for Lumière! operations. Initial IMDb queries are quite time consuming, so if you do not want to kill your server and look for a smooth experience for your users, do not delete often your cache.", 'lumiere-movies'); ?></div>

<?php 
/////////////////////////////////// Cache options
if ( ((isset($_GET['cacheoption'])) && ($_GET['cacheoption'] == "option")) || (!isset($_GET['cacheoption'] )) ) { 


	echo "\n\t". '<div class="postbox-container">';
	echo "\n\t\t". '<div id="left-sortables" class="meta-box-sortables" >';

	echo "\n\t\t\t". '<form method="post" name="imdbconfig_save" action="' .  $_SERVER[ "REQUEST_URI" ] . '">';



		 //------------------------------------------------------------------ =[cache options]=- ?>

		<div class="inside imblt_border_shadow">
			<h3 class="hndle" id="cachegeneral" name="cachegeneral"><?php esc_html_e('Cache options', 'lumiere-movies'); ?></h3>
		</div>

	<div class="inside imblt_border_shadow">

	<div class="titresection"><?php esc_html_e('General options', 'lumiere-movies'); ?></div>

		<div class="lumiere_display_flex lumiere_flex_make_responsive">
<?php 
/* don't need to change those options, unactivated

			<div class="lumiere_flex_container_content_third imdblt_padding_five">

				<?php esc_html_e('Store cache?', 'lumiere-movies'); ?><br /><br />
				<input type="radio" id="imdb_imdbstorecache_yes" name="imdb_imdbstorecache" value="1" <?php if ($imdb_cache_values['imdbstorecache'] == "1") { echo 'checked="checked"'; }?> data-modificator="yes" data-field_to_change="imdb_imdbusecache_yes" data-field_to_change_value="0" data-modificator2="yes" data-field_to_change2="imdb_imdbconverttozip_yes" data-field_to_change_value2="0" data-modificator3="yes" data-field_to_change3="imdb_imdbusezip_yes" data-field_to_change_value3="0" /><label for="imdb_imdbstorecache_yes"><?php esc_html_e('Yes', 'lumiere-movies'); ?></label><input type="radio" id="imdb_imdbstorecache_no" name="imdb_imdbstorecache" value="" <?php if ($imdb_cache_values['imdbstorecache'] == 0) { echo 'checked="checked"'; } ?> data-modificator="yes" data-field_to_change="imdb_imdbusecache_yes" data-field_to_change_value="1" data-modificator2="yes" data-field_to_change2="imdb_imdbconverttozip_yes" data-field_to_change_value2="1" data-modificator3="yes" data-field_to_change3="imdb_imdbusezip_yes" data-field_to_change_value3="1" /><label for="imdb_imdbstorecache_no"><?php esc_html_e('No', 'lumiere-movies'); ?></label>

				<div class="explain"><?php esc_html_e('Whether to store the pages retrieved for later use. When activated, you have to check you created the folders', 'lumiere-movies'); ?> <?php esc_html_e('Cache directory', 'lumiere-movies'); ?> <?php esc_html_e('and', 'lumiere-movies'); ?> <?php esc_html_e('Photo directory (folder)', 'lumiere-movies'); ?>. <br /><?php esc_html_e('Default:','lumiere-movies');?> <?php esc_html_e('Yes', 'lumiere-movies'); ?></div>

			</div>
*/
?>
			<div class="lumiere_flex_container_content_thirty imdblt_padding_five">

				<?php esc_html_e('Use cache?', 'lumiere-movies'); ?><br /><br />
				<input type="radio" id="imdb_imdbusecache_yes" name="imdb_imdbusecache" value="1" <?php if ($imdb_cache_values['imdbusecache'] == "1") { echo 'checked="checked"'; }?> data-modificator="yes" data-field_to_change="imdb_imdbcacheexpire" data-field_to_change_value="0" data-modificator2="yes" data-field_to_change2="imdb_imdbcachedetailsshort_yes" data-field_to_change_value2="0" /><label for="imdb_imdbusecache_yes"><?php esc_html_e('Yes', 'lumiere-movies'); ?></label><input type="radio" id="imdb_imdbconverttozip_no" name="imdb_imdbusecache" value="" <?php if ($imdb_cache_values['imdbusecache'] == 0) { echo 'checked="checked"'; } ?> data-modificator="yes" data-field_to_change="imdb_imdbcacheexpire" data-field_to_change_value="1" data-modificator2="yes" data-field_to_change2="imdb_imdbcachedetailsshort_no" data-field_to_change_value2="1"/><label for="imdb_imdbusecache_no"><?php esc_html_e('No', 'lumiere-movies'); ?></label>

				<div class="explain"><?php esc_html_e('Whether to use a cached page to retrieve the information (if available).', 'lumiere-movies'); ?> <br /><?php esc_html_e('Default:','lumiere-movies');?> <?php esc_html_e('Yes', 'lumiere-movies'); ?></div>

			</div>
			<div class="lumiere_flex_container_content_thirty imdblt_padding_five">

				<label for="imdb_imdbcacheexpire"><?php esc_html_e('Cache expire', 'lumiere-movies'); ?></label><br /><br />
				<div class="lumiere_flex_container">

					<div>
						<input type="text" id="imdb_imdbcacheexpire" name="imdb_imdbcacheexpire" size="7" value="<?php esc_html_e(apply_filters('format_to_edit',$imdb_cache_values['imdbcacheexpire']), 'lumiere-movies') ?>" <?php if ( ($imdb_cache_values['imdbusecache'] == 0) || ($imdb_cache_values['imdbstorecache'] == 0) ) { echo 'disabled="disabled"'; }; ?> />
					</div>				
 
					<div class="imdblt_padding_ten">
						<input type="checkbox" value="0" id="imdb_imdbcacheexpire_definitive" name="imdb_imdbcacheexpire_definitive" data-valuemodificator="yes" data-valuemodificator_field="imdb_imdbcacheexpire" data-valuemodificator_default="2592000"<?php if ($imdb_cache_values['imdbcacheexpire'] == 0) { echo 'checked="checked"'; }; ?> /><label for="imdb_imdbcacheexpire"><?php esc_html_e('(never)','lumiere-movies');?></label>
					</div>
				</div>

				<div class="explain"><?php esc_html_e('Cache files older than this value (in seconds) will be automatically deleted. Insert "0" or click "never" to keep cache files forever.', 'lumiere-movies'); ?> <br /><?php esc_html_e('Default:','lumiere-movies');?> "2592000" <?php esc_html_e('(one month)', 'lumiere-movies'); ?></div>

			</div>
		</div>


		<?php //------------------------------------------------------------------ =[zip]=- 
/* don't need to change those options, unactivated
?>
		<div class="titresection"><?php esc_html_e('Cache zip options', 'lumiere-movies'); ?></div>

		<div class="lumiere_flex_container">
			<div class="lumiere_flex_container_content_third imdblt_padding_five">

				<?php esc_html_e('Convert to zip?', 'lumiere-movies'); ?><br /><br />
				<input type="radio" id="imdb_imdbconverttozip_yes" name="imdb_imdbconverttozip" value="1" <?php if ($imdb_cache_values['imdbconverttozip'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbconverttozip_yes"><?php esc_html_e('Yes', 'lumiere-movies'); ?></label><input type="radio" id="imdb_imdbconverttozip_no" name="imdb_imdbconverttozip" value="" <?php if ($imdb_cache_values['imdbconverttozip'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbconverttozip_no"><?php esc_html_e('No', 'lumiere-movies'); ?></label>

				<div class="explain"><?php esc_html_e('Convert non-zip cache-files to zip (check file permissions!)', 'lumiere-movies'); ?> <br /><?php esc_html_e('Default:','lumiere-movies');?> <?php esc_html_e('Yes', 'lumiere-movies'); ?></div>

			</div>
			<div class="lumiere_flex_container_content_third imdblt_padding_five">

				<?php esc_html_e('Use zip?', 'lumiere-movies'); ?><br /><br />
				<input type="radio" id="imdb_imdbusezip_yes" name="imdb_imdbusezip" value="1" <?php if ($imdb_cache_values['imdbusezip'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbusezip_yes"><?php esc_html_e('Yes', 'lumiere-movies'); ?></label><input type="radio" id="imdb_imdbusezip_no" name="imdb_imdbusezip" value="" <?php if ($imdb_cache_values['imdbusezip'] == 0) { echo 'checked="checked"'; } ?>/><label for="imdb_imdbusezip_no"><?php esc_html_e('No', 'lumiere-movies'); ?></label>

				<div class="explain"><?php esc_html_e('Use zip compression for caching the retrieved html-files.', 'lumiere-movies'); ?> <br /><?php esc_html_e('Default:','lumiere-movies');?> <?php esc_html_e('Yes', 'lumiere-movies'); ?></div>
			</div>

		

		</div>

		<?php */
		//------------------------------------------------------------------ =[cache details]=- ?>
		<div class="titresection"><?php esc_html_e('Cache details', 'lumiere-movies'); ?></div>

		<div class="lumiere_flex_container">
		<?php
/* don't need to change this options, unactivated
?>

			<div class="lumiere_flex_container_content_third imdblt_padding_five">

				<?php esc_html_e('Show advanced cache details', 'lumiere-movies'); ?><br /><br />
				<input type="radio" id="imdb_imdbcachedetails_yes" name="imdb_imdbcachedetails" value="1" <?php if ($imdb_cache_values['imdbcachedetails'] == "1") { echo 'checked="checked"'; }?> data-modificator="yes" data-field_to_change="imdb_imdbcachedetailsshort_yes" data-field_to_change_value="0" />
				<label for="imdb_imdbcachedetails_yes"><?php esc_html_e('Yes', 'lumiere-movies'); ?></label>
				<input type="radio" id="imdb_imdbcachedetails_no" name="imdb_imdbcachedetails" value="" <?php if ($imdb_cache_values['imdbcachedetails'] == 0) { echo 'checked="checked"'; } ?> data-modificator="yes" data-field_to_change="imdb_imdbcachedetailsshort_yes" data-field_to_change_value="1" />
				<label for="imdb_imdbcachedetails_no"><?php esc_html_e('No', 'lumiere-movies'); ?></label>

				<div class="explain"><?php esc_html_e('To show or not advanced cache details, which allows to specifically delete a movie cache. Be carefull with this option, if you have a lot of cached movies, it could crash this page. When yes is selected, an additional menu "manage cache" will appear next to the cache "General Options" menu.', 'lumiere-movies'); ?> <br /><?php esc_html_e('Default:','lumiere-movies');?> <?php esc_html_e('Yes', 'lumiere-movies'); ?></div>

			</div>
<?php
*/
?>
			<div class="lumiere_flex_container_content_third imdblt_padding_five">

				<?php esc_html_e('Simplified cache details', 'lumiere-movies'); ?><br /><br />
				<input type="radio" id="imdb_imdbcachedetailsshort_yes" name="imdb_imdbcachedetailsshort" value="1" <?php if ($imdb_cache_values['imdbcachedetailsshort'] == "1") { echo 'checked="checked"'; }?> <?php if ($imdb_cache_values['imdbcachedetails'] == 0) { echo 'disabled="disabled"'; }; ?> />
				<label for="imdb_imdbcachedetailsshort_yes"><?php esc_html_e('Yes', 'lumiere-movies'); ?></label>

				<input type="radio" id="imdb_imdbcachedetailsshort_no" name="imdb_imdbcachedetailsshort" value="" <?php if ($imdb_cache_values['imdbcachedetailsshort'] == 0) { echo 'checked="checked"'; } ?> <?php if ($imdb_cache_values['imdbcachedetails'] == 0) { echo 'disabled="disabled"'; }; ?> />
				<label for="imdb_imdbcachedetailsshort_no"><?php esc_html_e('No', 'lumiere-movies'); ?></label>

				<div class="explain"><?php esc_html_e('Allow faster loading time for the "manage cache" page, by displaying shorter movies and people presentation. Usefull when you have several of those. This option is available when "Show advanced cache details" is activated.', 'lumiere-movies'); ?> <br /><?php esc_html_e('Default:','lumiere-movies');?> <?php esc_html_e('No', 'lumiere-movies'); ?></div>

			</div>


		</div>
	</div>
</div>		
<?php 
	//------------------------------------------------------------------ =[Submit selection] ?>
			<div class="submit submit-imdb lumiere_sticky_boxshadow lumiere_align_center">
			<?php wp_nonce_field('cache_options_check', 'cache_options_check'); ?>
				<input type="submit" class="button-primary" name="reset_cache_options" value="<?php  esc_html_e('Reset settings', 'lumiere-movies'); ?>" />
				<input type="submit" class="button-primary" name="update_cache_options" value="<?php  esc_html_e('Update settings', 'lumiere-movies'); ?>" />
			</div>
		</form>

<?php 
}  // end $_GET['cacheoption'] == "option"

if ( (isset($_GET['cacheoption'])) && ($_GET['cacheoption'] == "manage") ){ 	////////////////////////////////////////////// Cache management 

	// check if folder exists & store cache option is selected
	if (file_exists($imdb_cache_values['imdbcachedir']) && ($imdb_cache_values['imdbusecache'])) { ?>

	<div>

		<?php //------------------------------------------------------------------ =[cache delete]=- ?>

		<div class="inside imblt_border_shadow">
			<h3 class="hndle" id="cachegeneral" name="cachegeneral"><?php esc_html_e('Global cache management', 'lumiere-movies'); ?></h3>
		</div>

		<div class="inside imblt_border_shadow">
			<form method="post" name="imdbconfig_save" action="<?php echo $_SERVER[ "REQUEST_URI"]; ?>" >
<?php 			wp_nonce_field('cache_all_and_query_check', 'cache_all_and_query_check');
			echo "\n";

$imdltcacheFile = $utils->lumiere_glob_recursive( $imdb_cache_values['imdbcachedir'] . '*');
$imdltcacheFileCount = (count( $imdltcacheFile ) ) -1; /* -1 do not count images folder */

if (!$utils->lumiere_isEmptyDir($imdltcacheFile)) {

	echo "\n\t\t\t" . '<div class="detailedcacheexplaination imdblt_padding_bottom_ten imdblt_align_center">';

	echo "<strong>". esc_html__('Total cache size:', 'lumiere-movies'); 
	$size_cache_tmp=0;
	foreach ( $imdltcacheFile as $filename ){
		if ( is_numeric(filesize($filename)) )
			$size_cache_tmp += intval(filesize($filename));
	}
	$size_cache_total = $size_cache_tmp;
	/* translators: %s is replaced with the number of files */
	echo "&nbsp;" . sprintf( _n( '%s file', '%s files', $imdltcacheFileCount, 'lumiere-movies'), number_format_i18n( $imdltcacheFileCount )) ;
	echo "&nbsp;" . esc_html__( 'using', 'lumiere-movies'); 
	echo ' ' . $utils->lumiere_formatBytes( intval($size_cache_total) ) ;
	echo "</strong>\n"; 

?>
			</div>

			<div>
<?php	if ($imdltcacheFileCount > 0) { 
			esc_html_e('If you want to reset the entire cache (this includes queries, names, and pictures) click on the button below.', 'lumiere-movies');
			echo "<br />";
			esc_html_e('Beware, there is no undo.', 'lumiere-movies'); ?></div>
				<div class="submit submit-imdb" align="center">

				<input type="submit" class="button-primary" name="delete_all_cache" data-confirm="<?php esc_html_e( "Delete all cache? Really?", 'lumiere-movies'); ?>" value="<?php esc_html_e('Delete all cache', 'lumiere-movies') ?>" /> 

				<br />
				<br />
<?php 
	wp_kses( _e('This button will <strong>delete all cache</strong> stored in cache folder.', 'lumiere-movies'), $allowed_html_for_esc_html_functions ); 

	// No files in cache
	} else {  

		echo '<div class="imdblt_error">' . esc_html__('Lumière! cache is empty.', 'lumiere-movies') . '</div>'; 

	}  // end no cache files
}// end no cache folder
?>

				</div>
				<br />
				<br />
<?php
$imdltcacheFileQuery = $utils->lumiere_glob_recursive($imdb_cache_values['imdbcachedir'] . 'find.s*');
$imdltcacheFileQueryCount = count( $imdltcacheFileQuery ) ; 

if (!empty($imdb_cache_values['imdbcachedir'])) { 

	echo "\n\t\t\t" . '<div class="detailedcacheexplaination imdblt_padding_bottom_ten imdblt_align_center">';

	echo "\n\t\t\t\t" . "<strong>". esc_html__('Total query cache size:', 'lumiere-movies'); 
	$size_cache_query_tmp=0;
	foreach ( $imdltcacheFileQuery as $filenamecachequery){
		if (is_numeric(filesize($filenamecachequery)))
			$size_cache_query_tmp += intval(filesize($filenamecachequery));
	}
	$size_cache_query_total = $size_cache_query_tmp;
	/* translators: %s is replaced with the number of files */
	echo "&nbsp;" . sprintf( _n( '%s file', '%s files', $imdltcacheFileQueryCount, 'lumiere-movies'), number_format_i18n( $imdltcacheFileQueryCount )) ;
	echo "&nbsp;" . esc_html__( 'using', 'lumiere-movies'); 
	echo ' ' . $utils->lumiere_formatBytes( intval($size_cache_query_total) ) ;
	echo "</strong>"; 
?>
			</div>

			<div>
<?php	if ($imdltcacheFileQueryCount > 0) { 
			esc_html_e('If you want to reset the query cache (every search creates a cache file) click on the button below.', 'lumiere-movies');
			echo "<br />";
			?></div>
			<div class="submit submit-imdb" align="center">

			<input type="submit" class="button-primary" name="delete_query_cache" data-confirm="<?php esc_html_e( "Delete query cache?", 'lumiere-movies'); ?>" value="<?php esc_html_e('Delete query cache', 'lumiere-movies') ?>" /> 
<?php 
	// No files in cache
	} else {  

		echo '<div class="imdblt_error">' . esc_html__('Lumière! query cache is empty.', 'lumiere-movies') . '</div>'; 

	} // end no cache files

} // end no cache folder
?>

		</div>
		</form>
	</div>
	<br />
	<br />
	<form method="post" name="lumiere_delete_ticked_cache" action="<?php echo $_SERVER[ "REQUEST_URI"]; ?>" >

<?php		 //------------------------------------------------------------------ =[movies management]=- ?>

	<div class="inside imblt_border_shadow">
		<h3 class="hndle" id="cachemovies" name="cachemovies"><?php esc_html_e('Movie\'s detailed cache', 'lumiere-movies'); ?></h3>
	</div>

	<div class="inside imblt_border_shadow">
<?php
	// Scope of the files to be managed
	$files = glob($imdb_cache_values['imdbcachedir'] . '{title.tt*}', GLOB_BRACE);

	if (is_dir($imdb_cache_values['imdbcachedir'])) {
		foreach ($files as $file) {
			if (preg_match('!^title\.tt(\d{7,8})$!i', basename($file), $match)) {
				$results[] = new Title($match[1], $config, $logger);
			}
		}
	}

	if ((!isset($files)) || (empty($files)) ){
		echo '<div class="imdblt_error">'.esc_html__('No movie\'s cache found.','lumiere-movies').'</div>';
	} else {
?>

		<div class="lumiere_intro_options">

			<?php esc_html_e('If you want to refresh movie\'s cache regardless the cache expiration time, you may either tick movie\'s checkbox(es) related to the movie you want to delete and click on "delete cache". you may also click on "refresh" to update a movie series of details.', 'lumiere-movies'); ?>
			<br />
			<br />
			<?php esc_html_e('You may also select a group of movies to delete.', 'lumiere-movies'); ?>
			<br />
			<br />
		</div>

		<div class="lumiere_flex_container">

<?php
	if (!empty($results)){
		foreach ($results as $res){
			if (get_class($res) === 'Imdb\Title') {
				$title_sanitized = sanitize_text_field( $res->title() ); // search title related to movie id
				$obj_sanitized = sanitize_text_field( $res->imdbid() );
				$filepath_sanitized = esc_url( $imdb_cache_values['imdbcachedir']."title.tt".substr($obj_sanitized, 0, 8) );
				if ($imdb_cache_values['imdbcachedetailsshort'] == 1)  { // display only cache movies' names, quicker loading
					$data[] = '<span class="lumiere_short_titles"><input type="checkbox" id="imdb_cachedeletefor_movies_'.$title_sanitized.'" name="imdb_cachedeletefor_movies[]" value="'.$obj_sanitized.'" /><label for="imdb_cachedeletefor_movies[]">'.$title_sanitized.'</label></span>'."\n"; // send input and results into array
					flush();
				} else { // display every cache movie details, longer loading
					// get either local picture or if no local picture exists, display the default one
					if (false === $res->photo_localurl() ){
						$moviepicturelink = 'src="'.plugin_dir_url( __DIR__ ).'pics/no_pics.gif" alt="'.esc_html__('no picture', 'lumiere-movies').'"';							
					} else {
						$moviepicturelink = 'src="'.$imdb_cache_values['imdbphotodir'].$obj_sanitized.'.jpg" alt="'.$title_sanitized.'"'; 
					}


				// no flex class so the browser decides how many data to display per lines
				// table so "row-actions" wordpress class works
				$data[] = '	<div class="lumiere_flex_container_content_third lumiere_breakall"><table><tr><td>
							<img id="pic_'.$title_sanitized.'" class="picfloat" '.$moviepicturelink.' width="40px">

							<input type="checkbox" id="imdb_cachedeletefor_movies_'.$title_sanitized.'" name="imdb_cachedeletefor_movies[]" value="'.$obj_sanitized.'" /><label for="imdb_cachedeletefor_movies[]" class="imdblt_bold">'.$title_sanitized.'</label> <br />'. esc_html__("last updated on ", 'lumiere-movies').date ("j M Y H:i:s", filemtime($filepath_sanitized)).' 
							<div id="refresh_edit_'.$title_sanitized.'" class="row-actions">
								<span class="edit"><a href="'.esc_url( admin_url().'admin.php?page=imdblt_options&subsection=cache&cacheoption=manage&dothis=refresh&where='.$obj_sanitized.'&type=movie').'" class="admin-cache-confirm-refresh" data-confirm="'. esc_html__("Refresh cache for *", 'lumiere-movies') .$title_sanitized.'*?">'.esc_html__("refresh", 'lumiere-movies').'</a></span>

								<span class="delete"><a href="'.esc_url( admin_url().'admin.php?page=imdblt_options&subsection=cache&cacheoption=manage&dothis=delete&where='.$obj_sanitized.'&type=movie').'" class="admin-cache-confirm-delete" data-confirm="'. esc_html__("Delete *", 'lumiere-movies') . $title_sanitized.esc_html__("* from cache?", 'lumiere-movies').'" title="'. esc_html__("Delete *", 'lumiere-movies') . $title_sanitized.esc_html__("* from cache?", 'lumiere-movies').'">'.esc_html__("delete", 'lumiere-movies').'</a></span>
							</div></td></tr></table>
						</div>';// send input and results into array

				} //end quick/long loading $imdb_cache_values['imdbcachedetailsshort']

			}
		} 
	}

	// sort alphabetically the data
	asort ($data);

	// print all lines
	foreach ($data as $inputline) {
		echo $inputline;
	}
?>
				</div>
				<br />

				<div class="imdblt_align_center">
					<input type="button" name="CheckAll" value="Check All" data-check-movies="">
					<input type="button" name="UnCheckAll" value="Uncheck All" data-uncheck-movies="">
				</div>

				<br />
				<br />

				<div class="imdblt_align_center">
					<input type="submit" class="button-primary" name="delete_ticked_cache" data-confirm="<?php esc_html_e( "Delete selected cache?", 'lumiere-movies'); ?>" value="<?php esc_html_e('Delete cache', 'lumiere-movies') ?>" />
					<br/>
					<br/>
					<?php echo esc_html_e('Warning!', 'lumiere-movies'); ?>
					<br />
					<?php echo esc_html_e('This button will delete the selected movies\' cache files.', 'lumiere-movies'); ?>
				</div>

<?php } // end if cache folder is empty
?>
			</div>
	<br />
	<br />

	<?php //------------------------------------------------------------------------ =[people delete]=- ?>

	<div class="inside imblt_border_shadow">
		<h3 class="hndle" id="cachepeople" name="cachepeople"><?php esc_html_e('People\'s detailed cache', 'lumiere-movies'); ?></h3>
	</div>

	<div class="inside imblt_border_shadow">

<?php
// Scope of the files to be managed
$files = glob($imdb_cache_values['imdbcachedir'] . '{name.nm*}', GLOB_BRACE);

if (is_dir($imdb_cache_values['imdbcachedir'])) {
	foreach ($files as $file) {
		if (preg_match('!^name\.nm(\d{7,8})$!i', basename($file), $match)) {
			$results[] = new Person($match[1], $config, $logger);
		}
	}
}

if ((!isset($files)) || (empty($files)) ){
	echo '<div class="imdblt_error">'.esc_html__('No people\'s cache found.','lumiere-movies').'</div>';
} else {
?>

	<div class="lumiere_intro_options">
		<?php esc_html_e('If you want to refresh people\'s cache regardless the cache expiration time, you may either tick people checkbox(es) related to the person you want to delete and click on "delete cache", or you may click on individual people\'s "refresh". The first way will require an additional people refresh - from you post, for instance.', 'lumiere-movies'); ?>
		<br />
		<br />
		<?php esc_html_e('You may also either delete individually the cache or by group.', 'lumiere-movies'); ?>
		<br />
		<br />
	</div>

	<div class="lumiere_flex_container">

		<?php
if (!empty($results)){
	foreach ($results as $res){
		if (get_class($res) === 'Imdb\Person') {
			$name_sanitized = sanitize_text_field( $res->name() ); // search title related to movie id
			$objpiple_sanitized = sanitize_text_field( $res->imdbid() );
			$filepath_sanitized = esc_url($imdb_cache_values['imdbcachedir']."name.nm".substr($objpiple_sanitized, 0, 8));
			if ($imdb_cache_values['imdbcachedetailsshort'] == 1)  { // display only cache peoples' names, quicker loading
				$datapeople[] = '<span class="lumiere_short_titles"><input type="checkbox" id="imdb_cachedeletefor_people_'.$name_sanitized.'" name="imdb_cachedeletefor_people[]" value="'.$objpiple_sanitized.'" /><label for="imdb_cachedeletefor_people[]">'.$name_sanitized.'</label></span>'; // send input and results into array

			} else { // display every cache people details, longer loading
				// get either local picture or if no local picture exists, display the default one
				if (false === $res->photo_localurl() ){
					$picturelink = 'src="'.esc_url( plugin_dir_url( __DIR__ ).'pics/no_pics.gif').'" alt="'.esc_html__('no picture', 'lumiere-movies').'"'; 	
				} else {
					$picturelink = 'src="'.esc_url($imdb_cache_values['imdbphotodir']."nm".$objpiple_sanitized.'.jpg').'" alt="'.$name_sanitized.'"';
				}

				$datapeople[] = '	
						<div class="lumiere_flex_container_content_third lumiere_breakall"><table><tr><td>
							<img id="pic_'.$name_sanitized.'" class="picfloat" '.$picturelink.' width="40px" alt="no pic">
							<input type="checkbox" id="imdb_cachedeletefor_people_'.$name_sanitized.'" name="imdb_cachedeletefor_people[]" value="'.$objpiple_sanitized.'" /><label for="imdb_cachedeletefor_people_[]" class="imdblt_bold">'.$name_sanitized.'</label><br />'. esc_html__('last updated on ', 'lumiere-movies').date ("j M Y H:i:s", filemtime($filepath_sanitized)).'
							
							<div class="row-actions">
								<span class="view"><a href="'.esc_url( admin_url().'admin.php?page=imdblt_options&subsection=cache&cacheoption=manage&dothis=refresh&where='.$objpiple_sanitized.'&type=people').'" class="admin-cache-confirm-refresh" data-confirm="Refresh cache for *'.$name_sanitized.'*" title="Refresh cache for *'.$name_sanitized.'*">'.esc_html__("refresh", 'lumiere-movies').'</a></span> 

								<span class="delete"><a href="'.esc_url( admin_url().'admin.php?page=imdblt_options&subsection=cache&cacheoption=manage&dothis=delete&where='.$objpiple_sanitized.'&type=people').'" class="admin-cache-confirm-delete" data-confirm="You are about to delete *'.$name_sanitized.'* from cache. Click Cancel to stop or OK to continue." title="Delete cache for *'.$name_sanitized.'*">'.esc_html__("delete", 'lumiere-movies').'</a></span>
							</div></td></tr></table>
					</div>'; // send input and results into array

				flush();
			} //end quick/long loading $imdb_cache_values['imdbcachedetailsshort']

		}
	} 
}

	// sort alphabetically the data
	asort ($datapeople);

	// print all lines
	foreach ($datapeople as $inputline) {
		echo $inputline;
	}
	?>
				</div>
				<br />
					<div align="center">
						<input type="button" name="CheckAll" value="Check All" data-check-people="">
						<input type="button" name="UnCheckAll" value="Uncheck All" data-uncheck-people="">
					</div>
					<br />
					<br />

					<div align="center">
						<input type="submit" class="button-primary" data-confirm="<?php esc_html_e( "Delete selected cache?", 'lumiere-movies'); ?>" name="delete_ticked_cache" value="<?php esc_html_e('Delete cache', 'lumiere-movies') ?>" />
						<br/>
						<br/>
						<?php echo esc_html_e('Warning!', 'lumiere-movies'); ?>
						<br />
						<?php echo esc_html_e('This button will delete the selected people\'s cache files.', 'lumiere-movies'); ?>
					</div>
			</div>
	<?php
	} // end if data found 

		// End of form for ticked cache to delete
		wp_nonce_field('cache_options_check', 'cache_options_check'); ?>

		</form>
	</div>
	<br />
	<br />

	<?php //------------------------------------------------------------------ =[cache directories]=- ?>

	<div class="inside imblt_border_shadow">
		<h3 class="hndle" id="cachedirectory" name="cachedirectory"><?php esc_html_e('Cache directories', 'lumiere-movies'); ?></h3>
	</div>

	<div class="inside imblt_border_shadow">

		<form method="post" name="imdbconfig_save" action="<?php echo $_SERVER[ "REQUEST_URI"]; ?>" >

		<div class="titresection lumiere_padding_five"><?php esc_html_e('Cache directory (absolute path)', 'lumiere-movies'); ?></div>

		<div class="lumiere_padding_five">
			<span class="imdblt_smaller">
			<?php 	// display cache folder size
			if (!$utils->lumiere_isEmptyDir($imdb_cache_values['imdbcachedir'])) {

				echo esc_html__('Movies\' cache is using', 'lumiere-movies') . ' ' . $utils->lumiere_formatBytes( intval($size_cache_total) ) . "\n";

			} else {  

				echo esc_html__('Movies\' cache is empty.', 'lumiere-movies'); 

			}
			?>
			</span>

		</div>
		<div class="imdblt_padding_five">

			<div class="lumiere_breakall">
				<?php echo ABSPATH; ?>
				<input type="text" name="imdbcachedir_partial" class="lumiere_border_width_medium" value="<?php esc_html_e(apply_filters('format_to_edit',$imdb_cache_values['imdbcachedir_partial']), 'lumiere-movies') ?>">
			</div>

			<div class="explain">
			<?php if (file_exists($imdb_cache_values['imdbcachedir'])) { // check if folder exists
				echo '<span class="imdblt_green">';
				esc_html_e("Folder exists.", 'lumiere-movies');
				echo '</span>';
			} else {
				echo '<span class="imdblt_red">';
				esc_html_e("Folder doesn't exist!", 'lumiere-movies');
				echo '</span>'; 
			}
			if (file_exists($imdb_cache_values['imdbcachedir'])) { // check if permissions are ok
				if ( substr(sprintf('%o', fileperms($imdb_cache_values['imdbcachedir'])), -3) == "777") { 
					echo ' <span class="imdblt_green">';
					esc_html_e("Permissions OK.", 'lumiere-movies');
					echo '</span>';
				} else { 
					echo ' <span class="imdblt_red">';
					esc_html_e("Check folder permissions!", 'lumiere-movies');
					echo '</span>'; 
				}
			} ?>
			</div>

			<div class="explain lumiere_breakall">
				<?php esc_html_e('Absolute path to store cache retrieved from the IMDb website. Has to be ', 'lumiere-movies'); ?>
				<a href="http://codex.wordpress.org/Changing_File_Permissions" title="permissions how-to on wordpress website">writable</a> 
				<?php esc_html_e('by the webserver.', 'lumiere-movies');?> 
				<br />
				<?php esc_html_e('Default:','lumiere-movies');?> "<?php echo esc_url ( WP_CONTENT_DIR . '/cache/lumiere/' ); ?>"
			</div>
		</div>


		<div>

			<div class="titresection lumiere_padding_five">
				<?php esc_html_e('Photo path (relative to the cache path)', 'lumiere-movies'); ?>
			</div>

			<div class="explain">
			<?php // display cache folder size
		if (!$utils->lumiere_isEmptyDir($imdb_cache_values['imdbphotoroot'], "2")) {
			$path = realpath($imdb_cache_values['imdbphotoroot']);
			if($path!==false && $path!='' && file_exists($path)){
				foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $object){
					$size_cache_pics += $object->getSize();
				}
			}
			echo esc_html_e('Images cache is using', 'lumiere-movies') . ' ' . $utils->lumiere_formatBytes( intval( $size_cache_pics) ) . "\n";
		} else {  echo esc_html_e('Image cache is empty.', 'lumiere-movies') . "\n"; }?>
			</div>

			<div class="explain lumiere_breakall">
				<?php esc_html_e('Absolute path to store images retrieved from the IMDb website. Has to be ', 'lumiere-movies'); ?>
				<a href="http://codex.wordpress.org/Changing_File_Permissions" title="permissions how-to on wordpress website">writable</a> 
				<?php esc_html_e('by the webserver.', 'lumiere-movies');?>
				<br />
			</div>

			<div class="imdblt_smaller lumiere_breakall">
			<?php esc_html_e('Current:','lumiere-movies');?> "<?php echo esc_url ( $imdb_cache_values['imdbphotoroot'] ); ?>"
			</div>
			<br />

			<div class="imdblt_smaller">
<?php 				if (file_exists($imdb_cache_values['imdbphotoroot'])) { // check if folder exists
			echo '<span class="imdblt_green">';
			esc_html_e("Folder exists.", 'lumiere-movies');
			echo '</span>';
			} else {
			echo '<span class="imdblt_red">';
			esc_html_e("Folder doesn't exist!", 'lumiere-movies');
			echo '</span>'; } 
			if (file_exists($imdb_cache_values['imdbphotoroot'])) { // check if permissions are ok
				if ( substr(sprintf('%o', fileperms($imdb_cache_values['imdbphotoroot'])), -3) == "777") { 
					echo ' <span class="imdblt_green">';
					esc_html_e("Permissions OK.", 'lumiere-movies');
					echo '</span>';
				} else { 
					echo ' <span class="imdblt_red">';
					esc_html_e("Check folder permissions!", 'lumiere-movies');
					echo '</span>'; 
				}
			} 

			?>

			</div>
		</div>

		<div>
			<div class="titresection imdblt_padding_five">
				<?php esc_html_e('Photo URL (relative to the website and the cache path)', 'lumiere-movies'); ?>
			</div>			

			<div class="explain lumiere_breakall">
				<?php esc_html_e('URL corresponding to photo directory.','lumiere-movies');?> 
				<br />
				<?php esc_html_e('Current:','lumiere-movies');?> "<?php echo esc_url ( $imdb_cache_values['imdbphotodir'] ); ?>"
			</div>

		</div>

	</div>

	<br />
	<br />

	<div class="submit submit-imdb" align="center">

		<?php wp_nonce_field('cache_options_check', 'cache_options_check'); ?>
		<input type="submit" class="button-primary" name="reset_cache_options" value="<?php esc_html_e( 'Reset settings', 'lumiere-movies') ?>" />
		<input type="submit" class="button-primary" name="update_cache_options" value="<?php esc_html_e( 'Update settings', 'lumiere-movies') ?>" />

		</form>
	</div>

<?php		} else { // end if cache folder exists ?>

		<div class="inside lumiere_border_shadow_red">
		<?php esc_html_e('A cache folder has to be created and the cache storage option has to be activated before you can manage the cache.', 'lumiere-movies'); ?>
			<br /><br />
			<?php esc_html_e('Apparently, you have not such a cache folder.', 'lumiere-movies'); ?> 
			<br /><br />
			<?php esc_html_e( 'Click on "reset settings" to refresh the values.', 'lumiere-movies'); ?>
		</div>

		<div class="submit submit-imdb" align="center">
			<form method="post" name="imdbconfig_save" action="<?php echo $_SERVER[ "REQUEST_URI"]; ?>">
			<?php	//check that data has been sent only once 
				wp_nonce_field('cache_options_check', 'cache_options_check'); 
				get_submit_button(	esc_html__('Reset settings', 'lumiere-movies'), 'primary large', 'reset_cache_options'); ?>
				<input type="submit" class="button-primary" name="reset_cache_options" value="<?php esc_html_e('Reset settings', 'lumiere-movies'); ?> " />
			</form>
		</div>
<?php
	} // end else cache folder exists

}  //end if $_GET['cacheoption'] == "manage"   ?>
</div>
<br clear="all">
<br />
<br />

<?php	} // end user can manage options ?>
