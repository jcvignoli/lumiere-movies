<?php

 #############################################################################
 # Lumiere!                                                                  #
 # written by Prometheus group                                               #
 # https://www.jcvignoli.com/blog                                            #
 # ------------------------------------------------------------------------- #
 # This program is free software; you can redistribute and/or modify it      #
 # under the terms of the GNU General Public License (see LICENSE)           #
 # ------------------------------------------------------------------------- #
 #									              #
 #  Function : Cache management for IMDbLT		                     #
 #									              #
 #############################################################################

// included files
require_once (dirname(__FILE__).'/../bootstrap.php');

use \Imdb\Title;
use \Imdb\Person;

// Vars
global $imdb_admin_values, $imdb_widget_values, $imdb_cache_values;
$allowed_html_for_esc_html_functions = [
    'strong',
];
$messages = array( /* highslide message notification options */
    'cache_update_success' => 'Cache succesfully refreshed.',
    'cache_doesnt_exist' => 'This file does not exist.',
    'cache_delete_success' => 'Cache successfully deleted.'
);

// Start config class for $config in below Imdb\Title class calls
if (class_exists("lumiere_settings_conf")) {
	$config = new lumiere_settings_conf();
	$config->cachedir = $imdb_cache_values['imdbcachedir'] ?? NULL;
	$config->photodir = $imdb_cache_values['imdbphotoroot'] ?? NULL; // ?imdbphotoroot? Bug imdbphp?
	$config->imdb_img_url = $imdb_cache_values['imdbimgdir'] ?? NULL;
	$config->cache_expire = $imdb_cache_values['imdbcacheexpire'] ?? NULL;
	$config->photoroot = $imdb_cache_values['imdbphotodir'] ?? NULL; // ?imdbphotodir? Bug imdbphp?
	$config->storecache = $imdb_cache_values['imdbstorecache'] ?? NULL;
	$config->usecache = $imdb_cache_values['imdbusecache'] ?? NULL;
}


// If $_GET["msg"] is found, display a related notice
if ((isset($_GET['msg'])) && array_key_exists( sanitize_text_field( $_GET['msg'] ), $messages) ){
	// Message Message for cache updated successfully
	if (sanitize_text_field($_GET['msg'])=="cache_update_success") {
		lumiere_notice(1, esc_html__( $messages["cache_update_success"], "imdb" ) );
	// Message for cache doesn't exist
	} elseif (sanitize_text_field($_GET['msg'])=="cache_doesnt_exist") {
		lumiere_notice(3, esc_html__( $messages["cache_doesnt_exist"], "imdb" ) );
	// Message for cache deleted successfully
	} elseif (sanitize_text_field($_GET['msg'])=="cache_delete_success") {
		lumiere_notice(1, esc_html__( $messages["cache_delete_success"], "imdb" ) );

	}
}


##################################### delete several files

if ( isset( $_POST['update_imdbltcache_check'] ) && wp_verify_nonce( $_POST['update_imdbltcache_check'], 'update_imdbltcache_check' ) ) {

	// prevent drama
	if ( is_null($imdb_cache_values['imdbcachedir']))
		exit( esc_html__("Cannot work this way.", "imdb") );

	if ( isset( $_POST['imdb_cachedeletefor'] ) ) {
		foreach( $_POST["imdb_cachedeletefor"] as $number_to_delete ) {

			// things to delete
			$filetodeletetitle=$imdb_cache_values['imdbcachedir']."title.tt".$number_to_delete;
			$filetodeletetaglines=$imdb_cache_values['imdbcachedir']."title.tt".$number_to_delete.".taglines";
			$filetodeletesoundtrack=$imdb_cache_values['imdbcachedir']."title.tt".$number_to_delete.".soundtrack";
			$filetodeletereleaseinfo=$imdb_cache_values['imdbcachedir']."title.tt".$number_to_delete.".releaseinfo";
			$filetodeletefullcredits=$imdb_cache_values['imdbcachedir']."title.tt".$number_to_delete.".fullcredits";
			$filetodeleteplotsummary=$imdb_cache_values['imdbcachedir']."title.tt".$number_to_delete.".plotsummary";
			$filetodeletecompanycredits=$imdb_cache_values['imdbcachedir']."title.tt".$number_to_delete.".companycredits";
			$filetodeletemovieconnections=$imdb_cache_values['imdbcachedir']."title.tt".$number_to_delete.".MovieConnections";
			$filetodeleteexternalsites=$imdb_cache_values['imdbcachedir']."title.tt".$number_to_delete.".externalsites";
			$filetodeleteplot=$imdb_cache_values['imdbcachedir']."title.tt".$number_to_delete."plot";
			$filetodeletequotes=$imdb_cache_values['imdbcachedir']."title.tt".$number_to_delete.".quotes";
			$filetodeletetrivia=$imdb_cache_values['imdbcachedir']."title.tt".$number_to_delete.".trivia";
			$filetodeletevideogallery=$imdb_cache_values['imdbcachedir']."title.tt".$number_to_delete.".videogallery.content_type-trailer";
			$filetodeletetechnical=$imdb_cache_values['imdbcachedir']."title.tt".$number_to_delete.".technical";
			$filetodeletetriviatab=$imdb_cache_values['imdbcachedir']."title.tt".$number_to_delete.".trivia.tab=gf";
			$filetodeletepics=$imdb_cache_values['imdbphotodir'].$number_to_delete."_big.jpg";
			$filetodeletepics2=$imdb_cache_values['imdbphotodir'].$number_to_delete.".jpg";

			// delete things
			if( file_exists($filetodeletetitle ) && fopen($filetodeletetitle, 'w')  ) {
			 	if (file_exists($filetodeletetitle )) unlink ($filetodeletetitle);
			 	if (file_exists($filetodeletetaglines )) unlink ($filetodeletetaglines);
			 	if (file_exists($filetodeletesoundtrack )) unlink ($filetodeletesoundtrack);
			 	if (file_exists($filetodeletereleaseinfo )) unlink ($filetodeletereleaseinfo);
			 	if (file_exists($filetodeletefullcredits )) unlink ($filetodeletefullcredits);
			 	if (file_exists($filetodeleteplotsummary )) unlink ($filetodeleteplotsummary);
			 	if (file_exists($filetodeletecompanycredits )) unlink ($filetodeletecompanycredits);
			 	if (file_exists($filetodeletemovieconnections )) unlink ($filetodeletemovieconnections);
			 	if (file_exists($filetodeleteexternalsites )) unlink ($filetodeleteexternalsites);
			 	if (file_exists($filetodeleteplot )) unlink ($filetodeleteplot);
			 	if (file_exists($filetodeletequotes )) unlink ($filetodeletequotes);
			 	if (file_exists($filetodeletetrivia )) unlink ($filetodeletetrivia);
			 	if (file_exists($filetodeletevideogallery )) unlink ($filetodeletevideogallery);
			 	if (file_exists($filetodeletetechnical )) unlink ($filetodeletetechnical);
			 	if (file_exists($filetodeletetriviatab )) unlink ($filetodeletetriviatab);
			 	if (file_exists($filetodeletepics )) unlink ($filetodeletepics);
			 	if (file_exists($filetodeletepics2 )) unlink ($filetodeletepics2);
			} else {
				wp_die( lumiere_notice(3, '<strong>'. esc_html__( 'This file does not exist.', 'imdb') .'</strong>') );
			}

		}
	}

	if ( isset( $_POST['imdb_cachedeletefor_people'] ) ) {
		foreach( $_POST["imdb_cachedeletefor_people"] as $number_to_delete ) {

			// things to delete
			$filetodeletebio=$imdb_cache_values['imdbcachedir']."name.nm".$number_to_delete.".bio";
			$filetodeletename=$imdb_cache_values['imdbcachedir']."name.nm".$number_to_delete;
			$filetodeletepublicity=$imdb_cache_values['imdbcachedir']."name.nm".$number_to_delete.".publicity";
			$filetodeletepics=$imdb_cache_values['imdbphotodir']."nm".$number_to_delete.".jpg";

			// delete things
			if( file_exists($filetodeletename ) && fopen($filetodeletename, 'w') ) {
			 	if (file_exists($filetodeletebio )) unlink ($filetodeletebio);
			 	if (file_exists($filetodeletename )) unlink ($filetodeletename);
			 	if (file_exists($filetodeletepublicity )) unlink ($filetodeletepublicity);
			 	if (file_exists($filetodeletepics )) unlink ($filetodeletepics);
			} else {
				wp_die( lumiere_notice(3, '<strong>'. esc_html__( 'This file does not exist.', 'imdb') .'</strong>') ); 
			}
		}
	}
}

##################################### delete a peliculiar file

if (($_GET['dothis'] == 'delete') && ($_GET['type'])) {

	// prevent drama
	if ( (is_null($imdb_cache_values['imdbcachedir'])) || (!is_numeric($_GET['where']))  )
		exit( esc_html__("Cannot work this way.", "imdb") );

	if (($_GET['type'])== 'movie') {
		$wheresanitized = filter_var( $_GET["where"], FILTER_SANITIZE_NUMBER_INT) ?? NULL;
		
		// things to delete
		$filetodeletetitle=$imdb_cache_values['imdbcachedir']."title.tt".$wheresanitized;
		$filetodeletetaglines=$imdb_cache_values['imdbcachedir']."title.tt".$wheresanitized.".taglines";
		$filetodeletesoundtrack=$imdb_cache_values['imdbcachedir']."title.tt".$wheresanitized.".soundtrack";
		$filetodeletereleaseinfo=$imdb_cache_values['imdbcachedir']."title.tt".$wheresanitized.".releaseinfo";
		$filetodeletefullcredits=$imdb_cache_values['imdbcachedir']."title.tt".$wheresanitized.".fullcredits";
		$filetodeleteplotsummary=$imdb_cache_values['imdbcachedir']."title.tt".$wheresanitized.".plotsummary";
		$filetodeletecompanycredits=$imdb_cache_values['imdbcachedir']."title.tt".$wheresanitized.".companycredits";
		$filetodeletemovieconnections=$imdb_cache_values['imdbcachedir']."title.tt".$wheresanitized.".MovieConnections";
		$filetodeleteexternalsites=$imdb_cache_values['imdbcachedir']."title.tt".$wheresanitized.".externalsites";
		$filetodeleteplot=$imdb_cache_values['imdbcachedir']."title.tt".$wheresanitized."plot";
		$filetodeletequotes=$imdb_cache_values['imdbcachedir']."title.tt".$wheresanitized.".quotes";
		$filetodeletetrivia=$imdb_cache_values['imdbcachedir']."title.tt".$wheresanitized.".trivia";
		$filetodeletevideogallery=$imdb_cache_values['imdbcachedir']."title.tt".$wheresanitized.".videogallery.content_type-trailer";
		$filetodeletetechnical=$imdb_cache_values['imdbcachedir']."title.tt".$wheresanitized.".technical";
		$filetodeletetriviatab=$imdb_cache_values['imdbcachedir']."title.tt".$wheresanitized.".trivia.tab=gf";
		$filetodeletepics=$imdb_cache_values['imdbphotodir'].$wheresanitized."_big.jpg";
		$filetodeletepics2=$imdb_cache_values['imdbphotodir'].$wheresanitized.".jpg";

		// delete things
		if( file_exists($filetodeletetitle ) && fopen($filetodeletetitle, 'w')  ) {
		 	if (file_exists($filetodeletetitle )) unlink ($filetodeletetitle);
		 	if (file_exists($filetodeletetaglines )) unlink ($filetodeletetaglines);
		 	if (file_exists($filetodeletesoundtrack )) unlink ($filetodeletesoundtrack);
		 	if (file_exists($filetodeletereleaseinfo )) unlink ($filetodeletereleaseinfo);
		 	if (file_exists($filetodeletefullcredits )) unlink ($filetodeletefullcredits);
		 	if (file_exists($filetodeleteplotsummary )) unlink ($filetodeleteplotsummary);
		 	if (file_exists($filetodeletecompanycredits )) unlink ($filetodeletecompanycredits);
		 	if (file_exists($filetodeletemovieconnections )) unlink ($filetodeletemovieconnections);
		 	if (file_exists($filetodeleteexternalsites )) unlink ($filetodeleteexternalsites);
		 	if (file_exists($filetodeleteplot )) unlink ($filetodeleteplot);
		 	if (file_exists($filetodeletequotes )) unlink ($filetodeletequotes);
		 	if (file_exists($filetodeletetrivia )) unlink ($filetodeletetrivia);
		 	if (file_exists($filetodeletevideogallery )) unlink ($filetodeletevideogallery);
		 	if (file_exists($filetodeletetechnical )) unlink ($filetodeletetechnical);
		 	if (file_exists($filetodeletetriviatab )) unlink ($filetodeletetriviatab);
		 	if (file_exists($filetodeletepics )) unlink ($filetodeletepics);
		 	if (file_exists($filetodeletepics2 )) unlink ($filetodeletepics2);
		}  else {
			wp_die( lumiere_notice(3, '<strong>'. esc_html__( 'This file does not exist.', 'imdb') .'</strong>') ) ;
		}
	}

	if (($_GET['type'])== 'people') {
		$wheresanitized = filter_var( $_GET["where"], FILTER_SANITIZE_NUMBER_INT) ?? NULL;

		// things to delete
		$filetodeletebio=$imdb_cache_values['imdbcachedir']."name.nm".$wheresanitized.".bio";
		$filetodeletename=$imdb_cache_values['imdbcachedir']."name.nm".$wheresanitized;
		$filetodeletepublicity=$imdb_cache_values['imdbcachedir']."name.nm".$wheresanitized.".publicity";
		$filetodeletepics=$imdb_cache_values['imdbphotodir']."nm".$wheresanitized.".jpg";

		// delete things
		if( file_exists($filetodeletename ) && fopen($filetodeletename, 'w')  ) {
		 	if (file_exists($filetodeletebio )) unlink ($filetodeletebio);
		 	if (file_exists($filetodeletename )) unlink ($filetodeletename);
		 	if (file_exists($filetodeletepublicity )) unlink ($filetodeletepublicity);
		 	if (file_exists($filetodeletepics )) unlink ($filetodeletepics);
		} else {
			wp_die( lumiere_notice(3, '<strong>'. esc_html__( 'This file does not exist.', 'imdb') .'</strong>') ) ;
		}
	}

	// display message on top
//	lumiere_notice(1, '<strong>'. esc_html__("Cache successfully deleted.", "imdb").'</strong>');
	wp_safe_redirect( add_query_arg( "msg", "cache_delete_success", wp_get_referer() ) );
	exit();
}

##################################### refresh a peliculiar file

if (($_GET['dothis'] == 'refresh') && ($_GET['type'])) {

	// prevent drama
	if ( (is_null($imdb_cache_values['imdbcachedir'])) || (!is_numeric($_GET['where']))  )
		exit( esc_html__("Cannot work this way.", "imdb") );

	if ( ($_GET['type']) == 'movie') {
		$wheresanitized = filter_var( $_GET["where"], FILTER_SANITIZE_NUMBER_INT) ?? NULL;

		// things to delete
		$filetodeletetitle=$imdb_cache_values['imdbcachedir']."title.tt".$wheresanitized;
		$filetodeletetaglines=$imdb_cache_values['imdbcachedir']."title.tt".$wheresanitized.".taglines";
		$filetodeletesoundtrack=$imdb_cache_values['imdbcachedir']."title.tt".$wheresanitized.".soundtrack";
		$filetodeletereleaseinfo=$imdb_cache_values['imdbcachedir']."title.tt".$wheresanitized.".releaseinfo";
		$filetodeletefullcredits=$imdb_cache_values['imdbcachedir']."title.tt".$wheresanitized.".fullcredits";
		$filetodeleteplotsummary=$imdb_cache_values['imdbcachedir']."title.tt".$wheresanitized.".plotsummary";
		$filetodeletecompanycredits=$imdb_cache_values['imdbcachedir']."title.tt".$wheresanitized.".companycredits";
		$filetodeletemovieconnections=$imdb_cache_values['imdbcachedir']."title.tt".$wheresanitized.".MovieConnections";
		$filetodeleteexternalsites=$imdb_cache_values['imdbcachedir']."title.tt".$wheresanitized.".externalsites";
		$filetodeleteplot=$imdb_cache_values['imdbcachedir']."title.tt".$wheresanitized."plot";
		$filetodeletequotes=$imdb_cache_values['imdbcachedir']."title.tt".$wheresanitized.".quotes";
		$filetodeletetrivia=$imdb_cache_values['imdbcachedir']."title.tt".$wheresanitized.".trivia";
		$filetodeletevideogallery=$imdb_cache_values['imdbcachedir']."title.tt".$wheresanitized.".videogallery.content_type-trailer";
		$filetodeletetechnical=$imdb_cache_values['imdbcachedir']."title.tt".$wheresanitized.".technical";
		$filetodeletetriviatab=$imdb_cache_values['imdbcachedir']."title.tt".$wheresanitized.".trivia.tab=gf";
		$filetodeletepics=$imdb_cache_values['imdbphotodir'].$wheresanitized."_big.jpg";
		$filetodeletepics2=$imdb_cache_values['imdbphotodir'].$wheresanitized.".jpg";

		// delete things
		if( file_exists($filetodeletetitle ) && fopen($filetodeletetitle, 'w') ) {
		 	if (file_exists($filetodeletetitle )) unlink ($filetodeletetitle);
		 	if (file_exists($filetodeletetaglines )) unlink ($filetodeletetaglines);
		 	if (file_exists($filetodeletesoundtrack )) unlink ($filetodeletesoundtrack);
		 	if (file_exists($filetodeletereleaseinfo )) unlink ($filetodeletereleaseinfo);
		 	if (file_exists($filetodeletefullcredits )) unlink ($filetodeletefullcredits);
		 	if (file_exists($filetodeleteplotsummary )) unlink ($filetodeleteplotsummary);
		 	if (file_exists($filetodeletecompanycredits )) unlink ($filetodeletecompanycredits);
		 	if (file_exists($filetodeletemovieconnections )) unlink ($filetodeletemovieconnections);
		 	if (file_exists($filetodeleteexternalsites )) unlink ($filetodeleteexternalsites);
		 	if (file_exists($filetodeleteplot )) unlink ($filetodeleteplot);
		 	if (file_exists($filetodeletequotes )) unlink ($filetodeletequotes);
		 	if (file_exists($filetodeletetrivia )) unlink ($filetodeletetrivia);
		 	if (file_exists($filetodeletevideogallery )) unlink ($filetodeletevideogallery);
		 	if (file_exists($filetodeletetechnical )) unlink ($filetodeletetechnical);
		 	if (file_exists($filetodeletetriviatab )) unlink ($filetodeletetriviatab);
		 	if (file_exists($filetodeletepics )) unlink ($filetodeletepics);
		 	if (file_exists($filetodeletepics2 )) unlink ($filetodeletepics2);
		} else {
			wp_die( lumiere_notice(3, '<strong>'. esc_html__( 'This file does not exist.', 'imdb') .'</strong>') ) ;
		}

		// get again the movie
		ob_start();
		$moviespecificid = $wheresanitized;
		$imdballmeta = "imdb-movie-widget-noname";
		include( IMDBLTABSPATH . 'inc/imdb-movie.inc.php');
		$out = ob_get_contents();
		ob_end_clean();
	}

	if (($_GET['type'])== 'people') {

		$wheresanitized = filter_var( $_GET["where"], FILTER_SANITIZE_NUMBER_INT) ?? NULL;

		// things to delete
		$filetodeletebio=$imdb_cache_values['imdbcachedir']."name.nm".$wheresanitized.".bio";
		$filetodeletename=$imdb_cache_values['imdbcachedir']."name.nm".$wheresanitized;
		$filetodeletepublicity=$imdb_cache_values['imdbcachedir']."name.nm".$wheresanitized.".publicity";
		$filetodeletepics=$imdb_cache_values['imdbphotodir']."nm".$wheresanitized.".jpg";

		// delete things
		if( file_exists($filetodeletename ) && fopen($filetodeletename, 'w') ) {
		 	if (file_exists($filetodeletebio )) unlink ($filetodeletebio);
		 	if (file_exists($filetodeletename )) unlink ($filetodeletename);
		 	if (file_exists($filetodeletepublicity )) unlink ($filetodeletepublicity);
		 	if (file_exists($filetodeletepics )) unlink ($filetodeletepics);
		} else {
			wp_die( lumiere_notice(3, '<strong>'. esc_html__( 'This file does not exist.', 'imdb') .'</strong>') );
		}

		// get again the person
		$person = new Imdb\Person($wheresanitized, $config);

		$name = $person->name(); // search title related to movie id
		$bio = $person->bio(); 
		$pubmovies = $person->pubmovies();
		$photo_url = $person->photo_localurl();
	}

	// display message on top
	wp_safe_redirect( add_query_arg( "msg", "cache_update_success", wp_get_referer() ) );
//	wp_safe_redirect( wp_get_referer() );
//	lumiere_notice(1, '<strong>'. esc_html__( 'Cache succesfully refreshed.', 'imdb') .'</strong>');
	exit();
}

##################################### let's display real cache option page
?>

<div id="tabswrap">
	<ul id="tabs">
		<li><img src="<?php echo esc_url( IMDBLTURLPATH . "pics/admin-cache-options.png"); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e("General options", 'imdb');?>" href="<?php echo esc_url( admin_url().'admin.php?page=imdblt_options&subsection=cache&cacheoption=option'); ?>"><?php esc_html_e( 'General options', 'imdb'); ?></a></li>
 		<?php if ($imdbOptionsc['imdbcachedetails'] == "1") { ?>
		<li>&nbsp;&nbsp;<img src="<?php echo esc_url( IMDBLTURLPATH . "pics/admin-cache-management.png"); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e("Manage Cache", 'imdb');?>" href="<?php echo esc_url( admin_url().'admin.php?page=imdblt_options&subsection=cache&cacheoption=manage'); ?>"><?php esc_html_e( "Manage Cache", 'imdb'); ?></a></li>
		<?php }; ?>
	</ul>
</div>

<div id="poststuff" class="metabox-holder">

	<div class="intro_cache"><?php esc_html_e( "Cache is crucial to Lumière! operation. As first imdb searchs are quite time consuming, if you do not want to kill your server but instead want quickest browsing experience, you will use cache.", 'imdb'); ?></div>

<?php if ( ($_GET['cacheoption'] == "option") || (!isset($_GET['cacheoption'] )) ) { 	/////////////////////////////////// Cache options  ?>


	<div class="postbox-container">
		<div id="left-sortables" class="meta-box-sortables" >

		<form method="post" name="imdbconfig_save" action="<?php echo $_SERVER[ "REQUEST_URI"]; ?>" >
		<div class="inside">


		<?php //------------------------------------------------------------------ =[cache directories]=- ?>

		<div class="postbox">
			<h3 class="hndle" id="cachedirectory" name="cachedirectory"><?php esc_html_e('Cache directories (folders have to be created and writable)', 'imdb'); ?></h3>
		</div>

	<div class="inside imblt_border_shadow">

		<div class="imdblt_double_container">
			<div class="imdblt_double_container_content_twenty imdblt_padding_five">

				<label for="imdb_imdbcachedir">
					<div class="titresection"><?php esc_html_e('Cache directory (absolute path)', 'imdb'); ?></div>

					<span class="imdblt_smaller">
					<?php 	// display cache folder size
					if (!lumiere_isEmptyDir($imdbOptionsc['imdbcachedir'])) { // from functions.php
						foreach (glob($imdbOptionsc['imdbcachedir']."*") as $filename) {
							$filenamesize1 += filesize($filename);
						}
						echo esc_html_e('Movies cache is using', 'imdb') . ' ' . lumiere_formatBytes( $filenamesize1 ) . "\n";
					} else {  echo esc_html_e('Movies cache is empty.', 'imdb'); }
					?>
					</span>
					</label>

			</div>
			<div class="imdblt_double_container_content_eighty imdblt_padding_five">

				<input type="text" name="imdb_imdbcachedir" class="imdblt_width_fillall" value="<?php esc_html_e(apply_filters('format_to_edit',$imdbOptionsc['imdbcachedir']), 'imdb') ?>">

				<div class="explain">
				<?php if (file_exists($imdbOptionsc['imdbcachedir'])) { // check if folder exists
					echo '<span class="imdblt_green">';
					esc_html_e("Folder exists.", 'imdb');
					echo '</span>';
				} else {
					echo '<span class="imdblt_red">';
					esc_html_e("Folder doesn't exist!", 'imdb');
					echo '</span>'; 
				}
				if (file_exists($imdbOptionsc['imdbcachedir'])) { // check if permissions are ok
					if ( substr(sprintf('%o', fileperms($imdbOptionsc['imdbcachedir'])), -3) == "777") { 
						echo ' <span class="imdblt_green">';
						esc_html_e("Permissions OK.", 'imdb');
						echo '</span>';
					} else { 
						echo ' <span class="imdblt_red">';
						esc_html_e("Check folder permissions!", 'imdb');
						echo '</span>'; 
					}
				} ?>
				</div>
			</div>

		</div>

		<div class="imdblt_double_container">
			<div class="imdblt_double_container_content_twenty imdblt_padding_five">

				<label for="imdb_imdbphotoroot">
				<div class="titresection"><?php esc_html_e('Photo directory (absolute path)', 'imdb'); ?></div>

					<span class="imdblt_smaller">
					<?php						// display cache folder size
					if (!lumiere_isEmptyDir($imdbOptionsc['imdbphotoroot'], "2")) { // from functions.php
						foreach (glob($imdbOptionsc['imdbphotoroot']."*") as $filename) {
							$filenamesize2 += filesize($filename);
						}
						echo esc_html_e('Images cache is using', 'imdb') . ' ' . lumiere_formatBytes($filenamesize2) . "\n";
					} else {  echo esc_html_e('Image cache is empty.', 'imdb') . "\n"; }
					?>
					</span>
				</label>

			</div>
			<div class="imdblt_double_container_content_eighty imdblt_padding_five">

				<input type="text" class="imdblt_width_fillall" name="imdb_imdbphotoroot" value="<?php esc_html_e(apply_filters('format_to_edit',$imdbOptionsc['imdbphotoroot']), 'imdb') ?>">

				<div class="explain">
				<?php if (file_exists($imdbOptionsc['imdbphotoroot'])) { // check if folder exists
				echo '<span class="imdblt_green">';
				esc_html_e("Folder exists.", 'imdb');
				echo '</span>';
				} else {
				echo '<span class="imdblt_red">';
				esc_html_e("Folder doesn't exist!", 'imdb');
				echo '</span>'; } 
				if (file_exists($imdbOptionsc['imdbphotoroot'])) { // check if permissions are ok
					if ( substr(sprintf('%o', fileperms($imdbOptionsc['imdbphotoroot'])), -3) == "777") { 
						echo ' <span class="imdblt_green">';
						esc_html_e("Permissions OK.", 'imdb');
						echo '</span>';
					} else { 
						echo ' <span class="imdblt_red">';
						esc_html_e("Check folder permissions!", 'imdb');
						echo '</span>'; 
					}
				} ?>
				</div>

				<div class="explain"><?php esc_html_e('Absolute path to store images retrieved from the IMDb website. Has to be ', 'imdb'); ?><a href="http://codex.wordpress.org/Changing_File_Permissions" title="permissions how-to on wordpress website">writable</a> <?php esc_html_e('by the webserver.', 'imdb');?> <br /><?php esc_html_e('Default:','imdb');?> "<?php esc_html_e ( IMDBLTABSPATH ); ?>cache/images/"</div>
			</div>
		</div>

		<div class="imdblt_double_container">
			<div class="imdblt_double_container_content_twenty imdblt_padding_five">

			<div class="titresection"><label for="imdb_imdbphotodir"><?php esc_html_e('Photo directory (url)', 'imdb'); ?></label></div>
			
			</div>
			<div class="imdblt_double_container_content_eighty imdblt_padding_five">

			<input type="text" name="imdb_imdbphotodir" class="imdblt_width_fillall" value="<?php esc_html_e(apply_filters('format_to_edit', $imdbOptionsc['imdbphotodir']), 'imdb') ?>">
				<div class="explain"><?php esc_html_e('URL corresponding to photo directory.','imdb');?> <br /><?php esc_html_e('Default:','imdb');?> "<?php echo esc_url( IMDBLTURLPATH . "cache/images/"); ?>"</div>
			</div>
		</div>
	</div>
	<br />
	<br />
			
		<?php //------------------------------------------------------------------ =[cache options]=- ?>

		<div class="postbox">
			<h3 class="hndle" id="cachegeneral" name="cachegeneral"><?php esc_html_e('Cache options', 'imdb'); ?></h3>
		</div>

	<div class="inside imblt_border_shadow">

	<div class="titresection"><?php esc_html_e('General options', 'imdb'); ?></div>

		<div class="imdblt_double_container">
			<div class="imdblt_double_container_content_third imdblt_padding_five">

				<?php esc_html_e('Store cache?', 'imdb'); ?><br /><br />
				<input type="radio" id="imdb_imdbstorecache_yes" name="imdb_imdbstorecache" value="1" <?php if ($imdbOptionsc['imdbstorecache'] == "1") { echo 'checked="checked"'; }?> data-modificator="yes" data-field_to_change="imdb_imdbusecache_yes" data-field_to_change_value="0" data-modificator2="yes" data-field_to_change2="imdb_imdbconverttozip_yes" data-field_to_change_value2="0" data-modificator3="yes" data-field_to_change3="imdb_imdbusezip_yes" data-field_to_change_value3="0" /><label for="imdb_imdbstorecache_yes"><?php esc_html_e('Yes', 'imdb'); ?></label><input type="radio" id="imdb_imdbstorecache_no" name="imdb_imdbstorecache" value="" <?php if ($imdbOptionsc['imdbstorecache'] == 0) { echo 'checked="checked"'; } ?> data-modificator="yes" data-field_to_change="imdb_imdbusecache_yes" data-field_to_change_value="1" data-modificator2="yes" data-field_to_change2="imdb_imdbconverttozip_yes" data-field_to_change_value2="1" data-modificator3="yes" data-field_to_change3="imdb_imdbusezip_yes" data-field_to_change_value3="1" /><label for="imdb_imdbstorecache_no"><?php esc_html_e('No', 'imdb'); ?></label>

				<div class="explain"><?php esc_html_e('Whether to store the pages retrieved for later use. When activated, you have to check you created the folders', 'imdb'); ?> <?php esc_html_e('Cache directory', 'imdb'); ?> <?php esc_html_e('and', 'imdb'); ?> <?php esc_html_e('Photo directory (folder)', 'imdb'); ?>. <br /><?php esc_html_e('Default:','imdb');?> <?php esc_html_e('Yes', 'imdb'); ?></div>

			</div>
			<div class="imdblt_double_container_content_third imdblt_padding_five">

				<?php esc_html_e('Use cache?', 'imdb'); ?><br /><br />
				<input type="radio" id="imdb_imdbusecache_yes" name="imdb_imdbusecache" value="1" <?php if ($imdbOptionsc['imdbusecache'] == "1") { echo 'checked="checked"'; }?> data-modificator="yes" data-field_to_change="imdb_imdbcacheexpire" data-field_to_change_value="0" /><label for="imdb_imdbusecache_yes"><?php esc_html_e('Yes', 'imdb'); ?></label><input type="radio" id="imdb_imdbconverttozip_no" name="imdb_imdbusecache" value="" <?php if ($imdbOptionsc['imdbusecache'] == 0) { echo 'checked="checked"'; } ?> data-modificator="yes" data-field_to_change="imdb_imdbcacheexpire" data-field_to_change_value="1" /><label for="imdb_imdbusecache_no"><?php esc_html_e('No', 'imdb'); ?></label>

				<div class="explain"><?php esc_html_e('Whether to use a cached page to retrieve the information (if available).', 'imdb'); ?> <br /><?php esc_html_e('Default:','imdb');?> <?php esc_html_e('Yes', 'imdb'); ?></div>

			</div>
			<div class="imdblt_double_container_content_third imdblt_padding_five">

				<label for="imdb_imdbcacheexpire"><?php esc_html_e('Cache expire', 'imdb'); ?></label><br /><br />
				<input type="text" id="imdb_imdbcacheexpire" name="imdb_imdbcacheexpire" size="7" value="<?php esc_html_e(apply_filters('format_to_edit',$imdbOptionsc['imdbcacheexpire']), 'imdb') ?>" <?php if ( ($imdbOptionsc['imdbusecache'] == 0) || ($imdbOptionsc['imdbstorecache'] == 0) ) { echo 'disabled="disabled"'; }; ?> />
				 
				<input type="checkbox" value="0" id="imdb_imdbcacheexpire_definitive" name="imdb_imdbcacheexpire_definitive" data-valuemodificator="yes" data-valuemodificator_field="imdb_imdbcacheexpire" data-valuemodificator_default="2592000"<?php if ($imdbOptionsc['imdbcacheexpire'] == 0) { echo 'checked="checked"'; }; ?> /><label for="imdb_imdbcacheexpire"><?php esc_html_e('(never)','imdb');?></label>

				<div class="explain"><?php esc_html_e('Cache files older than this value (in seconds) will be automatically deleted. Insert "0" or click "never" to keep cache files forever.', 'imdb'); ?> <br /><?php esc_html_e('Default:','imdb');?> "2592000" <?php esc_html_e('(one month)', 'imdb'); ?></div>

			</div>
		</div>


		<?php //------------------------------------------------------------------ =[zip]=- ?>
		<div class="titresection"><?php esc_html_e('Cache zip options', 'imdb'); ?></div>

		<div class="imdblt_double_container">
			<div class="imdblt_double_container_content_third imdblt_padding_five">

				<?php esc_html_e('Convert to zip?', 'imdb'); ?><br /><br />
				<input type="radio" id="imdb_imdbconverttozip_yes" name="imdb_imdbconverttozip" value="1" <?php if ($imdbOptionsc['imdbconverttozip'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbconverttozip_yes"><?php esc_html_e('Yes', 'imdb'); ?></label><input type="radio" id="imdb_imdbconverttozip_no" name="imdb_imdbconverttozip" value="" <?php if ($imdbOptionsc['imdbconverttozip'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbconverttozip_no"><?php esc_html_e('No', 'imdb'); ?></label>

				<div class="explain"><?php esc_html_e('Convert non-zip cache-files to zip (check file permissions!)', 'imdb'); ?> <br /><?php esc_html_e('Default:','imdb');?> <?php esc_html_e('Yes', 'imdb'); ?></div>

			</div>
			<div class="imdblt_double_container_content_third imdblt_padding_five">

				<?php esc_html_e('Use zip?', 'imdb'); ?><br /><br />;
				<input type="radio" id="imdb_imdbusezip_yes" name="imdb_imdbusezip" value="1" <?php if ($imdbOptionsc['imdbusezip'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbusezip_yes"><?php esc_html_e('Yes', 'imdb'); ?></label><input type="radio" id="imdb_imdbusezip_no" name="imdb_imdbusezip" value="" <?php if ($imdbOptionsc['imdbusezip'] == 0) { echo 'checked="checked"'; } ?>/><label for="imdb_imdbusezip_no"><?php esc_html_e('No', 'imdb'); ?></label>

				<div class="explain"><?php esc_html_e('Use zip compression for caching the retrieved html-files.', 'imdb'); ?> <br /><?php esc_html_e('Default:','imdb');?> <?php esc_html_e('Yes', 'imdb'); ?></div>
			</div>

			<div class="imdblt_double_container_content_third imdblt_padding_five">
			</div>

		</div>

		<?php //------------------------------------------------------------------ =[cache details]=- ?>
		<div class="titresection"><?php esc_html_e('Cache details', 'imdb'); ?></div>

		<div class="imdblt_double_container">
			<div class="imdblt_double_container_content_third imdblt_padding_five">

				<?php esc_html_e('Show advanced cache details', 'imdb'); ?><br /><br />
				<input type="radio" id="imdb_imdbcachedetails_yes" name="imdb_imdbcachedetails" value="1" <?php if ($imdbOptionsc['imdbcachedetails'] == "1") { echo 'checked="checked"'; }?> data-modificator="yes" data-field_to_change="imdb_imdbcachedetailsshort_yes" data-field_to_change_value="0" />
				<label for="imdb_imdbcachedetails_yes"><?php esc_html_e('Yes', 'imdb'); ?></label>
				<input type="radio" id="imdb_imdbcachedetails_no" name="imdb_imdbcachedetails" value="" <?php if ($imdbOptionsc['imdbcachedetails'] == 0) { echo 'checked="checked"'; } ?> data-modificator="yes" data-field_to_change="imdb_imdbcachedetailsshort_yes" data-field_to_change_value="1" />
				<label for="imdb_imdbcachedetails_no"><?php esc_html_e('No', 'imdb'); ?></label>

				<div class="explain"><?php esc_html_e('To show or not advanced cache details, which allows to specifically delete a movie cache. Be carefull with this option, if you have a lot of cached movies, it could crash this page. When yes is selected, an additional menu "manage cache" will appear next to the cache "General Options" menu.', 'imdb'); ?> <br /><?php esc_html_e('Default:','imdb');?> <?php esc_html_e('No', 'imdb'); ?></div>

			</div>
			<div class="imdblt_double_container_content_third imdblt_padding_five">

				<?php esc_html_e('Quick advanced cache details', 'imdb'); ?><br /><br />
				<input type="radio" id="imdb_imdbcachedetailsshort_yes" name="imdb_imdbcachedetailsshort" value="1" <?php if ($imdbOptionsc['imdbcachedetailsshort'] == "1") { echo 'checked="checked"'; }?> <?php if ($imdbOptionsc['imdbcachedetails'] == 0) { echo 'disabled="disabled"'; }; ?> />
				<label for="imdb_imdbcachedetailsshort_yes"><?php esc_html_e('Yes', 'imdb'); ?></label>

				<input type="radio" id="imdb_imdbcachedetailsshort_no" name="imdb_imdbcachedetailsshort" value="" <?php if ($imdbOptionsc['imdbcachedetailsshort'] == 0) { echo 'checked="checked"'; } ?> <?php if ($imdbOptionsc['imdbcachedetails'] == 0) { echo 'disabled="disabled"'; }; ?> />
				<label for="imdb_imdbcachedetailsshort_no"><?php esc_html_e('No', 'imdb'); ?></label>

				<div class="explain"><?php esc_html_e('Allow faster loading time for the "manage cache" page, by displaying shorter movies and people presentation. Usefull when you have several of those. This option is available when "Show advanced cache details" is activated.', 'imdb'); ?> <br /><?php esc_html_e('Default:','imdb');?> <?php esc_html_e('No', 'imdb'); ?></div>

			</div>

			<div class="imdblt_double_container_content_third imdblt_padding_five">
			</div>

		</div>
	</div>
</div>		
		<?php //------------------------------------------------------------------ =[Submit selection]=- ?>
			<div class="submit submit-imdb" align="center">
				<?php wp_nonce_field('reset_cache_options_check', 'reset_cache_options_check'); //check that data has been sent only once ?>
				<input type="submit" class="button-primary" name="reset_cache_options" value="<?php esc_html_e('Reset settings', 'imdb') ?>" />
				<?php wp_nonce_field('update_cache_options_check', 'update_cache_options_check', false);  //check that data has been sent only once -- don't send _wp_http_referer twice, already sent with first wp_nonce_field -> 3rd option to "false" ?>
				<input type="submit" class="button-primary" name="update_cache_options" value="<?php esc_html_e('Update settings', 'imdb') ?>" />
			</div>
		</form>

<?php	}  // end cache options

	if ($_GET['cacheoption'] == "manage")  { 	////////////////////////////////////////////// Cache management ?>


	<div class="postbox-container">
		<div id="left-sortables" class="meta-box-sortables" >


		<?php //------------------------------------------------------------------ =[cache delete]=- ?>
		<form method="post" name="manage_imdbltcache" id="manage_imdbltcache" action="<?php echo $_SERVER[ "REQUEST_URI"]; ?>">			
			<div class="inside">

				<div class="postbox">
					<h3 class="hndle" id="cachegeneral" name="cachegeneral"><?php esc_html_e('Cache management', 'imdb'); ?></h3>
					
					<div class="detailedcacheexplaination imdblt_padding_bottom_ten imdblt_align_center">
						<?php echo "<i>". esc_html__('Total cache size:', 'imdb'); 
						$imdltcacheFileCount = count( lumiere_glob_recursive($imdb_cache_values['imdbcachedir'] . '*') );
						$imdltcacheFileCountTotalSize=array_sum(array_map('filesize', lumiere_glob_recursive("{$wikileakscacheFileCount}*")));
						echo number_format( $imdltcacheFileCount, 0, ',', '\'' ) . "&nbsp;" . esc_html__( 'files', 'imdb');
						echo "&nbsp;" . esc_html__( 'for', 'imdb') . "&nbsp;" . lumiere_formatBytes( $imdltcacheFileCountTotalSize ). "</i>"; ?>
					</div>
				</div>
			</div>

			<?php	if (file_exists($imdbOptionsc['imdbcachedir']) && ($imdbOptionsc['imdbstorecache'])) { // check if folder exists & store cache option is selected
				if ($imdbOptionsc['imdbcachedetails'] == "1") { // imdbcachedetails options is selected 

			 //------------------------------------------------------------------ =[movies management]=- ?>

	<div class="postbox">
		<h3 class="hndle" id="cachemovies" name="cachemovies"><?php esc_html_e('Movie\'s detailed cache', 'imdb'); ?></h3>
	</div>

	<div class="inside imblt_border_shadow">

		<div class="lumiere_intro_options">

			<?php esc_html_e('If you want to refresh movie\'s cache regardless the cache expiration time, you may either tick movie\'s checkbox(es) related to the movie you want to delete and click on "delete cache", or you may click on individual movies "refresh". The first way will require an additional movie refresh - from you post, for instance.', 'imdb'); ?>
			<br />
			<br />
			<?php esc_html_e('You may also either delete individually the cache or by group.', 'imdb'); ?>
			<br />
			<br />
		</div>

		<div class="imdblt_double_container">

<?php
if (is_dir($imdb_cache_values['imdbcachedir'])) {
  $files = glob($imdb_cache_values['imdbcachedir'] . '{title.tt*,name.nm*}', GLOB_BRACE);
  foreach ($files as $file) {
    if (preg_match('!^title\.tt(\d{7,8})$!i', basename($file), $match)) {
      $results[] = new Title($match[1], $config);
    }
    if (preg_match('!^name\.nm(\d{7,8})$!i', basename($file), $match)) {
      $results[] = new Person($match[1], $config);
    }
  }
}

if (!empty($results)){
	foreach ($results as $res){
		if (get_class($res) === 'Imdb\Title') {
			$title_sanitized = sanitize_text_field( $res->title() ); // search title related to movie id
			$obj_sanitized = sanitize_text_field( $res->imdbid() );
			$filepath_sanitized = esc_url( $imdbOptionsc['imdbcachedir']."title.tt".substr($obj_sanitized, 0, 7) );
			if ($imdbOptionsc['imdbcachedetailsshort'] == 1)  { // display only cache movies' names, quicker loading
				$data[] = '<input type="checkbox" id="imdb_cachedeletefor_'.$title_sanitized.'" name="imdb_cachedeletefor[]" value="'.$obj_sanitized.'" /><label for="imdb_cachedeletefor[]">'.$title_sanitized.'</label>'; // send input and results into array
				flush();
			} else { // display every cache movie details, longer loading

			$moviepicturelink = (($photo_url = $res->photo_localurl() ) != FALSE) ? 'src="'.$imdb_cache_values['imdbphotodir'].$obj_sanitized.'.jpg" alt="'.$title_sanitized.'"' : 'src="'.IMDBLTURLPATH.'pics/no_pics.gif" alt="'.esc_html__('no picture', 'imdb').'"'; // get either local picture or if no local picture exists, display the default one


			// no flex class so the browser decides how many data to display per lines (up to ten data per line)
			// table so "row-actions" wordpress class works
			$data[] = '	<div><table><tr><td>
						<img id="pic_'.$title_sanitized.'" class="picfloat" '.$moviepicturelink.' width="40px">

						<input type="checkbox" id="imdb_cachedeletefor_'.$title_sanitized.'" name="imdb_cachedeletefor[]" value="'.$obj_sanitized.'" /><label for="imdb_cachedeletefor[]" class="imdblt_bold">'.$title_sanitized.'</label> <br />'. esc_html__("last updated on ", "imdb").date ("j M Y H:i:s", filemtime($filepath_sanitized)).' 
						<div id="refresh_edit_'.$title_sanitized.'" class="row-actions">
							<span class="edit"><a href="'.esc_url( admin_url().'admin.php?page=imdblt_options&subsection=cache&cacheoption=manage&dothis=refresh&where='.$obj_sanitized.'&type=movie').'" class="admin-cache-confirm-refresh" data-confirm="'. esc_html__("Refresh cache for *", "imdb") .$title_sanitized.'*?">'.esc_html__("refresh", "imdb").'</a></span>

							<span class="delete"><a href="'.esc_url( admin_url().'admin.php?page=imdblt_options&subsection=cache&cacheoption=manage&dothis=delete&where='.$obj_sanitized.'&type=movie').'" class="admin-cache-confirm-delete" data-confirm="'. esc_html__("Delete *", "imdb") . $title_sanitized.esc_html__("* from cache?", "imdb").'" title="'. esc_html__("Delete *", "imdb") . $title_sanitized.esc_html__("* from cache?", "imdb").'">'.esc_html__("delete", "imdb").'</a></span>
						</div></td></tr></table>
					</div>';// send input and results into array

			flush();

			} //end quick/long loading $imdbOptionsc['imdbcachedetailsshort']

		}
	} 
}

				if (empty($data)){
					echo '<div class="imdblt_error">'.esc_html__('No file found in cache folder.','imdb').'</div>';
				} else {
					asort ($data);
					$nbfilm="1";
					foreach ($data as $inputline) {
						echo $inputline;
						if ( ($nbfilm % 10) == "0" ) { // split into up to 10 movies by line
							echo "</div>\n<div class=\"imdblt_double_container\">";
						}
						$nbfilm++;
					}
				}
?>

				</div>

				<br />
				<div class="imdblt_align_center">
					<input type="button" name="CheckAll" value="Check All" data-check="">
					<input type="button" name="UnCheckAll" value="Uncheck All" data-uncheck="">
				</div>
				<br />
				<br />
				<div class="imdblt_align_center">
					<?php wp_nonce_field('update_imdbltcache_check', 'update_imdbltcache_check'); //check that data has been sent only once  ?>
					<input type="submit" class="button-primary" name="update_imdbltcache" data-confirm="<?php esc_html_e( "Delete selected cache?", "imdb"); ?>" value="<?php esc_html_e('Delete cache', 'imdb') ?>" />
					<br/>
					<?php echo esc_html_e('Warning!', 'imdb'); ?>
					<?php echo esc_html_e('This button will to delete specific cache files selected from cache folder.', 'imdb'); ?>
				</div>
			</div>
	<br />
	<br />


		<?php //------------------------------------------------------------------ =[people delete]=- ?>

	<div class="postbox">
		<h3 class="hndle" id="cachepeople" name="cachepeople"><?php esc_html_e('People\'s detailed cache', 'imdb'); ?></h3>
	</div>

	<div class="inside imblt_border_shadow">

	<div class="lumiere_intro_options">
		<?php esc_html_e('If you want to refresh people\'s cache regardless the cache expiration time, you may either tick people checkbox(es) related to the person you want to delete and click on "delete cache", or you may click on individual people\'s "refresh". The first way will require an additional people refresh - from you post, for instance.', 'imdb'); ?>
		<br />
		<br />
		<?php esc_html_e('You may also either delete individually the cache or by group.', 'imdb'); ?>
		<br />
		<br />
	</div>

	<div class="imdblt_double_container">

		<?php
if (!empty($results)){
	foreach ($results as $res){
		if (get_class($res) === 'Imdb\Person') {
			$name_sanitized = sanitize_text_field( $res->name() ); // search title related to movie id
			$objpiple_sanitized = sanitize_text_field( $res->imdbid() );
			$filepath_sanitized = esc_url($imdbOptionsc['imdbcachedir']."name.nm".substr($objpiple_sanitized, 0, 7));
			if ($imdbOptionsc['imdbcachedetailsshort'] == 1)  { // display only cache peoples' names, quicker loading
				$datapeople[] = '<input type="checkbox" id="imdb_cachedeletefor_people_'.$name_sanitized.'" name="imdb_cachedeletefor_people[]" value="'.$objpiple_sanitized.'" /><label for="imdb_cachedeletefor_people[]">'.$name_sanitized.'</label>'; // send input and results into array
				flush();
			} else { // display every cache people details, longer loading
				$picturelink = (($photo_url = $res->photo_localurl() ) != FALSE) ? 'src="'.esc_url($imdb_cache_values['imdbphotodir']."nm".$objpiple_sanitized.'.jpg').'" alt="'.$name_sanitized.'"' : 'src="'.esc_url( IMDBLTURLPATH.'pics/no_pics.gif').'" alt="'.esc_html__('no picture', 'imdb').'"'; // get either local picture or if no local picture exists, display the default one
				$datapeople[] = '	
						<div><table><tr><td>
							<img id="pic_'.$name_sanitized.'" class="picfloat" '.$picturelink.' width="40px" alt="no pic">
							<input type="checkbox" id="imdb_cachedeletefor_people_'.$name_sanitized.'" name="imdb_cachedeletefor_people[]" value="'.$objpiple_sanitized.'" /><label for="imdb_cachedeletefor_people_[]" class="imdblt_bold">'.$name_sanitized.'</label><br />'. esc_html__('last updated on ', 'imdb').date ("j M Y H:i:s", filemtime($filepath_sanitized)).'
							
							<div class="row-actions">
								<span class="view"><a href="'.esc_url( admin_url().'admin.php?page=imdblt_options&subsection=cache&cacheoption=manage&dothis=refresh&where='.$objpiple_sanitized.'&type=people').'" class="admin-cache-confirm-refresh" data-confirm="Refresh cache for *'.$name_sanitized.'*" title="Refresh cache for *'.$name_sanitized.'*">'.esc_html__("refresh", "imdb").'</a></span> 

								<span class="delete"><a href="'.esc_url( admin_url().'admin.php?page=imdblt_options&subsection=cache&cacheoption=manage&dothis=delete&where='.$objpiple_sanitized.'&type=people').'" class="admin-cache-confirm-delete" data-confirm="You are about to delete *'.$name_sanitized.'* from cache. Click Cancel to stop or OK to continue." title="Delete cache for *'.$name_sanitized.'*">'.esc_html__("delete", "imdb").'</a></span>
							</div></td></tr></table>
					</div>'; // send input and results into array

				flush();
			} //end quick/long loading $imdbOptionsc['imdbcachedetailsshort']

		}
	} 
}

				if (empty($datapeople)){
					echo '<div class="imdblt_error">'.esc_html__('No file found in cache folder.','imdb').'</div>';
				} else {
				asort ($datapeople);
				$nbperso="1";
					foreach ($datapeople as $inputline) {
						echo $inputline;
						if ( ($nbperso % 10) == "0" ) { // split into 5 movies by line
							echo '</div><div class="imdblt_double_container">';
						}
						$nbperso++;
					}
				} ?>
				</div>
				<br />
					<div align="center">
						<input type="button" name="CheckAll" value="Check All" data-check-people="">
						<input type="button" name="UnCheckAll" value="Uncheck All" data-uncheck-people="">
					</div>
					<br />
					<br />

					<div align="center">
						<?php wp_nonce_field('update_imdbltcache_check', 'update_imdbltcache_check'); //check that data has been sent only once  ?>
						<input type="submit" class="button-primary" data-confirm="<?php esc_html_e( "Delete selected cache?", "imdb"); ?>" name="update_imdbltcache" value="<?php esc_html_e('Delete cache', 'imdb') ?>" />
						<br />
						<?php echo esc_html_e('Warning!', 'imdb'); ?>
						<?php echo esc_html_e('This button will to delete specific cache files selected from cache folder.', 'imdb'); ?>
					</div>
			</div>
	</div>
	<br />
	<br />

		<?php		} // end $imdbOptionsc['imdbcachedetails'] check ?>


	<div class="postbox">
		<h3 class="hndle" id="globalcache" name="globalcache"><?php esc_html_e('Global cache', 'imdb'); ?></h3>
	</div>

	<div class="inside imblt_border_shadow">
		<div><?php esc_html_e('If you want to reset the entire cache (including names & pictures cache) click on "reset cache". Beware, there is no undo.', 'imdb'); ?></div>
		<div class="submit submit-imdb" align="center">
			<strong><?php echo esc_html__('Warning!', 'imdb'); ?></strong>

			<br /><br />
	<?php				 	//check that data has been sent only once -- don't send _wp_http_referer twice, 
			//already sent with first wp_nonce_field -> 3rd option to "false" 
			wp_nonce_field('reset_imdbltcache_check', 'reset_imdbltcache_check', false); ?>
			<input type="submit" class="button-primary" name="reset_imdbltcache"  data-confirm="<?php esc_html__( "Delete all cache? Really?", "imdb"); ?>" value="<?php esc_html_e('Delete all cache', 'imdb') ?>" /> 
			<br /><br />
			<?php wp_kses( _e('This button will <strong>delete all cache</strong> stored in cache folder.', 'imdb'), $allowed_html_for_esc_html_functions ); ?>
		</div>
	</div>
</div>


		<?php } else {  // else (if folder exists) -> if folder does not exist  ?>
<div>
	<?php echo esc_html_e('A cache folder has to be created and the cache storage option has to be activated before having the opportunity to manage cache!', 'imdb');?>
</div>

		<?php } // end "check if folder exists & store cache option is selected" ?>

</form>

<?php } //end cache management ?>

	</div>
</div>
<br clear="all">
<br />
<br />

