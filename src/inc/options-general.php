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
 #  Function : general configuration admin page                              #
 #									              #
 #############################################################################

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die(esc_html__("You are not allowed to call this page directly.", "lumiere-movies"));
}

// Enter in debug mode
if ((isset($imdbOptions['imdbdebug'])) && ($imdbOptions['imdbdebug'] == "1")){
	lumiere_debug_display($imdbOptions, '', '', $config); # $config comes from admin_page
}

/* Vars */
$allowed_html_for_esc_html_functions = [ /* accept these html tags in wp_kses escaping function */
	'strong',
	'br',
];
$messages = array( /* highslide message notification options */
	'highslide_success' => 'Highslide successfully installed!',
	'highslide_failure' => 'Highslide installation failed!',
	'highslide_down' => 'Website to download Highslide is currently down, please try again later.',
	'highslide_website_unkown' => 'Website variable is not set.'
);

// If $_GET["msg"] is found, display a related notice
if ((isset($_GET['msg'])) && array_key_exists( sanitize_key( $_GET['msg'] ), $messages ) ){
	switch (sanitize_text_field( $_GET['msg'] )) {
		// Message for success
		case "highslide_success":
			echo lumiere_notice(1, esc_html__( $messages["highslide_success"], 'lumiere-movies') );
			break;
		// Message for failure
		case "highslide_failure":
			echo lumiere_notice(3, esc_html__( $messages["highslide_failure"] , 'lumiere-movies') . " " .  esc_html__( 'Your folder might be protected. Download highslide manually', 'lumiere-movies')." <a href='". esc_url ( IMDBBLOGHIGHSLIDE ) ."'>".esc_html__("here", 'lumiere-movies')."</a> ".esc_html__("and extract the zip into" ) . "<br />" .  esc_url( $imdbOptions['imdbpluginpath'] ."js/" ) );
			break;
		// Message for website down
		case "highslide_down":
			echo lumiere_notice(3, esc_html__( $messages["highslide_down"] , 'lumiere-movies')  );
			break;
		// Message for website unkown
		case "highslide_website_unkown":
			echo lumiere_notice(3, esc_html__( $messages["highslide_website_unkown"] , 'lumiere-movies')  );
			break;	
	}
}

// Data is posted using the form
if (current_user_can( 'manage_options' ) ) { 

	if ( (isset($_POST['update_imdbSettings'])) && check_admin_referer('options_general_check', 'options_general_check') ) { //--------------------save data selected 

		// Check if $_POST['imdburlstringtaxo'] and $_POST['imdburlpopups'] are identical, as they can't
$post_imdb_imdburlstringtaxo = isset($_POST['imdb_imdburlstringtaxo']) ? filter_var($_POST['imdb_imdburlstringtaxo'], FILTER_SANITIZE_STRING) : NULL;
$post_imdb_imdburlpopups = isset($_POST['imdb_imdburlpopups']) ? filter_var($_POST['imdb_imdburlpopups'], FILTER_SANITIZE_STRING) : NULL;

			if ( 
			(isset($post_imdb_imdburlstringtaxo)) && 
(str_replace('/','',$post_imdb_imdburlstringtaxo) == str_replace('/','',$post_imdb_imdburlpopups) ) || isset($imdbOptions['imdburlpopups']) && (str_replace('/','',$post_imdb_imdburlstringtaxo) == str_replace('/','',$imdbOptions['imdburlpopups']) )
									||
			(isset($post_imdb_imdburlpopups)) && 
(str_replace('/','',$post_imdb_imdburlpopups) == str_replace('/','',$post_imdb_imdburlstringtaxo) ) || isset($imdbOptions['imdburlstringtaxo']) && (str_replace('/','',$post_imdb_imdburlpopups) == str_replace('/','',$imdbOptions['imdburlstringtaxo']) )
			)
{
			echo lumiere_notice(3, esc_html__( 'Wrong values. You can not select the same URL string for taxonomy pages and popups.', 'lumiere-movies') );
			echo lumiere_notice(1, '<a href="'.wp_get_referer() .'">'. esc_html__( 'Go back', 'lumiere-movies') .'</a>');
			exit();
		}

		foreach ($_POST as $key=>$postvalue) {
			$key_sanitized = sanitize_key($key);
			$keynoimdb = str_replace ( "imdb_", "", $key_sanitized);
			if (isset($_POST["$key_sanitized"])) {
				$imdbOptions["$keynoimdb"] = sanitize_text_field( $_POST["$key_sanitized"] );
			}
		}

		// update options
		update_option($config->imdbAdminOptionsName, $imdbOptions);

		// flush rewrite rules for matches the new $imdbOptions['imdbplugindirectory'] path
		add_action('admin_init', function (){ flush_rewrite_rules(); }, 0);

		# 2021 07 04 function obsolete
		// Rewrite the htaccess so it matches the new $imdbOptions['imdbplugindirectory'] path
		/*if ( lumiere_make_htaccess() ) {
			echo lumiere_notice(1, esc_html__( 'htaccess file successfully generated.', 'lumiere-movies') ); 
		} else {
			echo lumiere_notice(3, esc_html__( 'Can not write htaccess file, check permissions.', 'lumiere-movies') );
		}*/

		// display message on top
		echo lumiere_notice(1, '<strong>'. esc_html__( 'Options saved.', 'lumiere-movies') .'</strong>');

		// Display a refresh link otherwise refreshed data is not seen
		if (!headers_sent()) {
			/* 2021 07 06 Shouldn't do anything here, to be removed
			// header("Refresh: 0;url=".$_SERVER[ "REQUEST_URI"]."&reset=true", false);
			wp_safe_redirect( wp_get_referer() ); 
			exit();*/
		} else {
			echo lumiere_notice(1, '<a href="'.wp_get_referer() .'">'. esc_html__( 'Go back', 'lumiere-movies') .'</a>');
			exit();
		}


	} elseif ( (isset($_POST['reset_imdbSettings'])) && check_admin_referer('options_general_check', 'options_general_check') ){ //---------------------reset options selected

		delete_option($config->imdbAdminOptionsName);

		// display message on top
		echo lumiere_notice(1, '<strong>'. esc_html__( 'Options reset.', 'lumiere-movies') .'</strong>');

		// Display a refresh link otherwise refreshed data is not seen
		if (!headers_sent()) {
			/* 2021 07 06 Shouldn't do anything here, to be removed
			// header("Refresh: 0;url=".$_SERVER[ "REQUEST_URI"]."&reset=true", false);
			wp_safe_redirect( wp_get_referer() ); 
			exit(); */
		} else {
			echo lumiere_notice(1, '<a href="'.wp_get_referer() .'">'. esc_html__( 'Go back', 'lumiere-movies') .'</a>');
			exit();
		}

	}


echo '<form method="post" id="imdbconfig_save" name="imdbconfig_save" action="' . $_SERVER[ "REQUEST_URI" ] . '">';

?>
<div id="tabswrap">
	<div class="imdblt_double_container lumiere_padding_five">
		<div class="lumiere_flex_auto lumiere_align_center"><img src="<?php echo esc_url( $imdbOptions['imdbplugindirectory'] . "pics/admin-general-path.png"); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e( "Paths & Layout", 'lumiere-movies');?>" href="<?php echo esc_url(admin_url() . "admin.php?page=imdblt_options&generaloption=base" ); ?>"><?php esc_html_e( 'Layout', 'lumiere-movies'); ?></a></div>

		<div class="lumiere_flex_auto lumiere_align_center">&nbsp;&nbsp;<img src="<?php echo esc_url( $imdbOptions['imdbplugindirectory'] . "pics/admin-general-advanced.png"); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e( "Advanced", 'lumiere-movies');?>" href="<?php echo esc_url (admin_url() . "admin.php?page=imdblt_options&generaloption=advanced" ); ?>"><?php esc_html_e( "Advanced", 'lumiere-movies'); ?></a></div>
	</div>
</div>


<div id="poststuff" class="metabox-holder">

<?php if ( ( (isset($_GET['generaloption'])) && ($_GET['generaloption'] == "base") ) || (!isset($_GET['generaloption'] )) ) { 	////////// Paths & Layout section  ?>

	<div class="intro_cache"><?php esc_html_e( "The following options usually do not need further action. Nevertheless, Lumière! can be widely customized to match your needs.", 'lumiere-movies'); ?></div>


	<div class="postbox">
		<h3 class="hndle" id="layout" name="layout"><?php esc_html_e( 'Layout', 'lumiere-movies'); ?></h3>
	</div>

	<div class="inside">
		<div class="inside imblt_border_shadow">

		<?php //------------------------------------------------------------------ =[Popup]=- ?>

			<div class="titresection">
				<img src="<?php echo esc_url( $imdbOptions['imdbplugindirectory'] . "pics/admin-title-popup.png"); ?>" width="80" height="80" align="absmiddle" />&nbsp;&nbsp;&nbsp;
				<?php esc_html_e( 'Popup', 'lumiere-movies'); ?>
			</div>
		
			<div class="lumiere_flex_container">
				<div class="lumiere_flex_auto imdblt_padding_five">

					<label for="imdb_popupLarg"><?php esc_html_e( 'Width', 'lumiere-movies'); ?></label><br /><br />
					<input type="text" name="imdb_popupLarg" size="5" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptions['popupLarg']), 'lumiere-movies') ?>" >

					<div class="explain"> <?php esc_html_e( 'Popup width, in pixels', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?>"540"</div>

				</div>
				<div class="lumiere_flex_auto imdblt_padding_five">

					<label for="imdb_popupLong"><?php esc_html_e( 'Height', 'lumiere-movies'); ?></label><br /><br />
					<input type="text" name="imdb_popupLong" size="5" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptions['popupLong']), 'lumiere-movies') ?>" >

					<div class="explain"> <?php esc_html_e( 'Popup height, in pixels', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?>"350"</div>

				</div>

				<div class="lumiere_flex_auto imdblt_padding_five">

					<label for="imdb_imdbpopuptheme"><?php esc_html_e( 'Theme color', 'lumiere-movies'); ?></label><br /><br />

					<select name="imdb_imdbpopuptheme">
						<option <?php if( ($imdbOptions['imdbpopuptheme'] == "white") || (empty($imdbOptions['imdbpopuptheme'])) ) echo 'selected="selected"'; ?>value="white"><?php esc_html_e( 'white (default)', 'lumiere-movies'); ?></option>
						<option <?php if($imdbOptions['imdbpopuptheme'] == "black") echo 'selected="selected"'; ?>value="black"><?php esc_html_e( 'black', 'lumiere-movies'); ?></option>
						<option <?php if($imdbOptions['imdbpopuptheme'] == "lightgrey") echo 'selected="selected"'; ?>value="lightgrey"><?php esc_html_e( 'lightgrey', 'lumiere-movies'); ?></option>

					</select>

					<div class="explain"> <?php esc_html_e( 'Popup color theme', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?>"white"</div>

				</div>

				<div class="lumiere_flex_auto imdblt_padding_five">

				<?php 
				// If the folder "highslide" exists
				if(is_dir(IMDBLTABSPATH . 'js/highslide')) { 
					esc_html_e( 'Display highslide popup', 'lumiere-movies'); 
					echo '<br /><br /> 
					<input type="radio" id="imdb_imdbpopup_highslide_yes" name="imdb_imdbpopup_highslide" value="1" ';
					if ($imdbOptions['imdbpopup_highslide'] == "1") { echo 'checked="checked"'; }
					echo ' /><label for="imdb_imdbpopup_highslide_yes">';
					esc_html_e( 'Yes', 'lumiere-movies');
					echo '</label><input type="radio" id="imdb_imdbpopup_highslide_no" name="imdb_imdbpopup_highslide" value="" ';
					 if ($imdbOptions['imdbpopup_highslide'] == 0) { echo 'checked="checked"'; } 
					echo '/><label for="imdb_imdbpopup_highslide_no">';
					 esc_html_e( 'No', 'lumiere-movies'); 
					echo '</label>';

					echo '<div class="explain">' . esc_html__( 'Highslide popup is a more stylished popup, and allows to open movie details directly in the webpage instead of opening a new window.', 'lumiere-movies'). '<br />'. esc_html__( 'Default:','lumiere-movies') . esc_html__( 'Yes', 'lumiere-movies') .'</div>';

				// No "highslide" folder is found
				} else { 
					// Say so!
					echo lumiere_notice(4, '<span class="imdblt_red_bold">'.esc_html__('Warning! No Highslide folder was found.', 'lumiere-movies') .'</span>');
					echo "<br />";

					// Automatic download deactivated as per Wordpress's plugin staff request
					// echo "<a href='". esc_url( $imdbOptions['imdbplugindirectory'] . "inc/highslide_download.php?highslide=yes") . "' title='".esc_html__('Click here to install Highslide', 'lumiere-movies') ."'><img src='".esc_url($imdbOptions['imdbplugindirectory'] . "pics/admin-general-install-highslide.png")."' align='absmiddle' />&nbsp;&nbsp;".esc_html__('Install automatically Highslide', 'lumiere-movies') .'</a><br /><br />';

					// Add a link to highslide website
					echo '<a href="http://highslide.com/" title="' . esc_html__('Click here to visit Highslide website', 'lumiere-movies') .'"><img src="'.esc_url( $imdbOptions['imdbplugindirectory'] . 'pics/admin-general-install-highslide.png') . '" align="absmiddle" />&nbsp;&nbsp;'.esc_html__('Get Highslide JS library', 'lumiere-movies') . '</a><br /><br />';
				} 

	?>
				</div>

			</div>

	
		<?php //------------------------------------------------------------------ =[Theme taxo/inside post/widget]=- ?>

			<div class="titresection">
				<img src="<?php echo esc_url( $imdbOptions['imdbplugindirectory'] . "pics/admin-title-taxonomy.png"); ?>" width="80" height="80" align="absmiddle" />&nbsp;&nbsp;&nbsp;
				<?php esc_html_e( 'Plain pages', 'lumiere-movies'); ?>
			</div>


			<div class="lumiere_flex_container_content_third lumiere_padding_five">

				<label for="imdb_imdbintotheposttheme"><?php esc_html_e( 'Theme color', 'lumiere-movies'); ?></label><br /><br />

				<select name="imdb_imdbintotheposttheme">
					<option <?php if( ($imdbOptions['imdbintotheposttheme'] == "grey") || (empty($imdbOptions['imdbintotheposttheme'])) ) echo 'selected="selected"'; ?>value="grey"><?php esc_html_e( 'grey (default)', 'lumiere-movies'); ?></option>
					<option <?php if($imdbOptions['imdbintotheposttheme'] == "white") echo 'selected="selected"'; ?>value="white"><?php esc_html_e( 'white', 'lumiere-movies'); ?></option>
					<option <?php if($imdbOptions['imdbintotheposttheme'] == "black") echo 'selected="selected"'; ?>value="black"><?php esc_html_e( 'black', 'lumiere-movies'); ?></option>
				</select>

				<div class="explain"> <?php esc_html_e( 'Inside the post/widget/taxonomy color theme', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?>"grey"</div>

			</div>

		
		<?php //------------------------------------------------------------------ =[Cover picture]=- ?>

		<div class="titresection">
			<img src="<?php echo esc_url( $imdbOptions['imdbplugindirectory'] . "pics/cover.jpg"); ?>" height="80" align="absmiddle" />&nbsp;&nbsp;&nbsp;
			<?php esc_html_e( 'Cover picture', 'lumiere-movies'); ?>
		</div>

		<div class="lumiere_flex_container">
			<div class="lumiere_flex_container_content_third imdblt_padding_five">

				<label for="imdb_popupLarg"><?php esc_html_e( 'Display only thumbnail', 'lumiere-movies'); ?><br /><br />
				<input type="radio" id="imdb_imdbcoversize_yes" name="imdb_imdbcoversize" value="1" <?php if ($imdbOptions['imdbcoversize'] == "1") { echo 'checked="checked"'; }?> data-modificator="yes" data-field_to_change="imdb_imdbcoversizewidth" data-field_to_change_value="1" />

				<label for="imdb_imdbcoversize_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

				<input type="radio" id="imdb_imdbcoversize_no" name="imdb_imdbcoversize" value="" <?php if ($imdbOptions['imdbcoversize'] == 0) { echo 'checked="checked"'; } ?> data-modificator="yes" data-field_to_change="imdb_imdbcoversizewidth" data-field_to_change_value="0" />

				<label for="imdb_imdbcoversize_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

				<div class="explain"><?php esc_html_e( 'Whether to display a thumbnail or a large image cover for movies inside a post or a widget. Select "No" to choose cover picture width (a new option on the right will be available).', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

			</div>
			<div class="lumiere_flex_container_content_third imdblt_padding_five">

				<label for="imdb_imdbcoversizewidth"><?php esc_html_e( 'Size', 'lumiere-movies'); ?></label><br /><br />

				<input type="text" name="imdb_imdbcoversizewidth" id="imdb_imdbcoversizewidth" size="5" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptions['imdbcoversizewidth']), 'lumiere-movies'); ?>" />

				<div class="explain"><?php esc_html_e( 'Size of the imdb cover picture. The value will correspond to the width in pixels. Delete any value to get maximum width.', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> "100"</div>

			</div>
		</div>
	
	</div>
	
	<br />
	<br />


<?php	} 
	if ( (isset($_GET['generaloption'])) && ($_GET['generaloption'] == "advanced") ) { 				//////////////// Advanced section  ?>

	<div class="intro_cache"><?php esc_html_e( "The options hereafter can break a lot of things. Edit them only if you know what you are doing.", 'lumiere-movies'); ?></div>

	<div class="inside">

		<?php //------------------------------------------------------------------ =[Search]=- ?>

	<div class="postbox">
		<h3 class="hndle" id="searchpart" name="searchpart"><?php esc_html_e( 'Search', 'lumiere-movies'); ?></h3>
	</div>

	<div class="inside imblt_border_shadow">

		<div class="lumiere_display_flex lumiere_flex_make_responsive">
			<div class="lumiere_flex_container_content_thirty imdblt_padding_five">

				<label for="imdb_imdblanguage"><?php esc_html_e( 'Search language', 'lumiere-movies'); ?></label><br /><br />
				<select name="imdb_imdblanguage">
					<option <?php if( ($imdbOptions['imdblanguage'] == "en-US") || (empty($imdbOptions['imdblanguage'])) ) echo 'selected="selected"'; ?> value="en-US"><?php esc_html_e( 'English', 'lumiere-movies'); ?></option>
					<option <?php if($imdbOptions['imdblanguage'] == "fr-FR") echo 'selected="selected"'; ?>value="fr-FR"><?php esc_html_e( 'French', 'lumiere-movies'); ?></option>
					<option <?php if($imdbOptions['imdblanguage'] == "es-ES") echo 'selected="selected"'; ?>value="es-ES"><?php esc_html_e( 'Spanish', 'lumiere-movies'); ?></option>
				</select>

				<div class="explain"><?php esc_html_e( 'Language used for the movie search. Very usefull for a non-English blog using Lumière! as a widget.', 'lumiere-movies'); ?>
					<br /><br />
					<?php esc_html_e( 'Default:','lumiere-movies'); ?> "English"
				</div>
			</div>

			<div class="lumiere_flex_container_content_thirty imdblt_padding_five">

				<label for="imdb_imdbmaxresults"><?php esc_html_e( 'Limit number of results', 'lumiere-movies'); ?></label>
				<br />
				<br />

				<input type="text" name="imdb_imdbmaxresults" id="imdb_imdbmaxresults" size="5" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptions['imdbmaxresults']), 'lumiere-movies') ?>" />

				<div class="explain">
					<?php esc_html_e( 'This limits the number of results in a movie query.', 'lumiere-movies'); ?> 
					<br /><br />
					<?php esc_html_e( 'Default:','lumiere-movies'); ?> "10"
				</div>
			</div>
		</div>
	</div>

	<br /><br />
	
		<?php //------------------------------------------------------------------ =[misc]=- ?>


	<div class="postbox">
		<h3 class="hndle" id="miscpart" name="miscpart"><?php esc_html_e( 'Misc', 'lumiere-movies'); ?></h3>
	</div>

	<div class="inside imblt_border_shadow">

		<div class="lumiere_flex_container">

			<div class="lumiere_flex_container_content_thirty lumiere_padding_five">

				<?php esc_html_e( 'Left menu for Lumière options', 'lumiere-movies'); ?><br /><br />

				<input type="radio" id="imdb_imdbwordpress_bigmenu_yes" name="imdb_imdbwordpress_bigmenu" value="1" <?php if ($imdbOptions['imdbwordpress_bigmenu'] == "1") { echo 'checked="checked"'; }?> />

				<label for="imdb_imdbwordpress_bigmenu_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label><input type="radio" id="imdb_imdbwordpress_bigmenu_no" name="imdb_imdbwordpress_bigmenu" value="" <?php if ($imdbOptions['imdbwordpress_bigmenu'] == 0) { echo 'checked="checked"'; } ?>  />
				
				<label for="imdb_imdbwordpress_bigmenu_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

				<div class="explain"><?php esc_html_e( "If enabled, Lumiere options are displayed in a dedicated menu on the left panel instead of being displayed in the settings menu.", 'lumiere-movies'); ?> <br /><br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

			</div>
			<div class="lumiere_flex_container_content_thirty lumiere_padding_five">

				<?php esc_html_e( 'Top menu for Lumière options', 'lumiere-movies'); ?><br /><br />
				<input type="radio" id="imdb_imdbwordpress_tooladminmenu_yes" name="imdb_imdbwordpress_tooladminmenu" value="1" <?php if ($imdbOptions['imdbwordpress_tooladminmenu'] == "1") { echo 'checked="checked"'; }?> />

				<label for="imdb_imdbwordpress_tooladminmenu_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label><input type="radio" id="imdb_imdbwordpress_tooladminmenu_no" name="imdb_imdbwordpress_tooladminmenu" value="" <?php if ($imdbOptions['imdbwordpress_tooladminmenu'] == 0) { echo 'checked="checked"'; } ?>  />

				<label for="imdb_imdbwordpress_tooladminmenu_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

				<div class="explain"><?php esc_html_e( "If activated, Lumière options are displayed in a  top menu. Not recommended f you have many plugins already occupying that area.", 'lumiere-movies'); ?> <br /><br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'Yes', 'lumiere-movies'); ?></div>

			</div>

			<div class="lumiere_flex_container_content_thirty lumiere_padding_five">

				<label for="imdb_imdbtaxonomy"><?php esc_html_e( 'Use taxonomy', 'lumiere-movies'); ?></label><br /><br />

				<input type="radio" id="imdb_imdbtaxonomy_yes" name="imdb_imdbtaxonomy" value="1" <?php if ($imdbOptions['imdbtaxonomy'] == "1") { echo 'checked="checked"'; }?> />

				<label for="imdb_imdbtaxonomy_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

				<input type="radio" id="imdb_imdbtaxonomy_no" name="imdb_imdbtaxonomy" value="" <?php if ($imdbOptions['imdbtaxonomy'] == 0) { echo 'checked="checked"'; } ?>  />

				<label for="imdb_imdbtaxonomy_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

				<div class="explain"><?php esc_html_e( 'This will add taxonomy terms found for the movie when display a page with a widget or a into a post. Taxonomy allows to group posts by a series of chosen terms, as explained in', 'lumiere-movies') ?> <a href="http://codex.wordpress.org/WordPress_Taxonomy">taxonomy</a>. <?php esc_html_e( 'Taxonomy terms are uninstalled when removing the plugin if you selected not to keep the settings upon uninstall.', 'lumiere-movies'); ?> <br /><br /><?php esc_html_e( 'Default:','lumiere-movies'); ?> <?php esc_html_e( 'Yes', 'lumiere-movies'); ?> <?php esc_html_e( '(Activated for "genre" taxonomy only)', 'lumiere-movies'); ?></div>

			</div>

			<div class="lumiere_flex_container_content_thirty lumiere_padding_five">

				<?php esc_html_e( 'Remove all links?', 'lumiere-movies'); ?><br /><br />

				<input type="radio" id="imdb_imdblinkingkill_yes" name="imdb_imdblinkingkill" value="1" <?php if ($imdbOptions['imdblinkingkill'] == "1") { echo 'checked="checked"'; }?> />

				<label for="imdb_imdblinkingkill_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label><input type="radio" id="imdb_imdblinkingkill_no" name="imdb_imdblinkingkill" value="" <?php if ($imdbOptions['imdblinkingkill'] == 0) { echo 'checked="checked"'; } ?>/><label for="imdb_imdblinkingkill_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

				<div class="explain"><?php esc_html_e( "Remove all links (popup and external ones) which are automatically added. Usefull for users who are not interested in popup function. Please note that it will remove every single HTML link as well, such as the the links to the official IMDb website.", 'lumiere-movies'); ?><br /><br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

			</div>

			<div class="lumiere_flex_container_content_thirty lumiere_padding_five">

				<?php esc_html_e( 'Auto widget?', 'lumiere-movies'); ?><br /><br />

				<input type="radio" id="imdb_imdbautopostwidget_yes" name="imdb_imdbautopostwidget" value="1" <?php if ($imdbOptions['imdbautopostwidget'] == "1") { echo 'checked="checked"'; }?> />

				<label for="imdb_imdbautopostwidget_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label><input type="radio" id="imdb_imdbautopostwidget_no" name="imdb_imdbautopostwidget" value="" <?php if ($imdbOptions['imdbautopostwidget'] == 0) { echo 'checked="checked"'; } ?>/><label for="imdb_imdbautopostwidget_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

				<div class="explain"><?php esc_html_e( "Add automatically a widget according to your post title. If regular widgets have been added to post too, the auto widget will be displayed before them. Usefull if blog a lot about movies; if a query does not bring any result with the post title, nothing is displayed.", 'lumiere-movies'); ?><br /><br /><?php esc_html_e( 'Default:','lumiere-movies');?><?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

			</div>

			<div class="lumiere_flex_container_content_thirty lumiere_padding_five">

				<?php esc_html_e( 'Keep settings upon deactivation', 'lumiere-movies'); ?><br /><br />

				<input type="radio" id="imdb_imdbkeepsettings_yes" name="imdb_imdbkeepsettings" value="1" <?php if ($imdbOptions['imdbkeepsettings'] == "1") { echo 'checked="checked"'; }?> />

				<label for="imdb_imdbkeepsettings_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label><input type="radio" id="imdb_imdbkeepsettings_no" name="imdb_imdbkeepsettings" value="" <?php if ($imdbOptions['imdbkeepsettings'] == 0) { echo 'checked="checked"'; } ?>  />
				
				<label for="imdb_imdbkeepsettings_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

				<div class="explain"><?php esc_html_e( "Whether to keep or delete Lumière! settings upon plugin deactivation. Prevent from deleting the taxonomy terms and the cache too.", 'lumiere-movies'); ?><br /><br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'Yes', 'lumiere-movies'); ?></div>

			</div>

			<div class="lumiere_flex_container_content_thirty lumiere_padding_five">

				<?php esc_html_e( 'Debug Lumière!', 'lumiere-movies'); ?><br /><br />

				<input type="radio" id="imdb_imdbdebug_yes" name="imdb_imdbdebug" value="1" <?php if ($imdbOptions['imdbdebug'] == "1") { echo 'checked="checked"'; }?> />

				<label for="imdb_imdbdebug_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label><input type="radio" id="imdb_imdbdebug_no" name="imdb_imdbdebug" value="" <?php if ($imdbOptions['imdbdebug'] == 0) { echo 'checked="checked"'; } ?>  />
				
				<label for="imdb_imdbdebug_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

				<div class="explain"><?php esc_html_e( "Activate to debug Lumière! plugin.", 'lumiere-movies'); ?><br /><br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>
			</div>

		</div>

	</div>

	<br />
	<br />

	<div class="postbox">
		<h3 class="hndle" id="directories" name="directories"><?php esc_html_e( 'Paths: url & folders', 'lumiere-movies'); ?></h3>
	</div>

	<div class="inside imblt_border_shadow">
		<div class="lumiere_intro_options"><?php esc_html_e('Edit the following values only if need so. You can break the plugin.', 'lumiere-movies'); ?></div>
		<br />
		<br />
	<div class="activatehidesection" align="center">[+] <?php esc_html_e('Click here to display options', 'lumiere-movies'); ?> [+]</div>

	<div class="hidesection">
		<br />
		<br />

		<?php //------------------------------------------------------------------=[ URL blog ]=- ?>

		<div class="lumiere_flex_container">
			<div class="lumiere_flex_container_content_twenty">
				<label for="imdb_blog_adress"><?php esc_html_e( 'Blog address', 'lumiere-movies'); ?></label>
			</div>
			<div class="lumiere_flex_container_content_eighty">
				<input class="lumiere_border_width_medium imdblt_width_fillall" type="text" name="imdb_blog_adress" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptions['blog_adress']), 'lumiere-movies') ?>" >
				<div class="explain"><?php esc_html_e( 'Where the blog is installed.', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> "<?php echo esc_url( $imdbOptions['blog_adress'] ); ?>"</div>
			</div>
		</div>

		<br /><br />

		<?php //------------------------------------------------------------------=[ PATH Lumière! ]=- ?>
		<div class="lumiere_flex_container">
			<div class="lumiere_flex_container_content_twenty">
				<label for="imdb_imdbplugindirectory"><?php esc_html_e( 'Plugin directory', 'lumiere-movies'); ?></label>
			</div>
			<div class="lumiere_flex_container_content_eighty">
				<div class="lumiere_align_items_center">
					<?php echo $imdbOptions['blog_adress']; ?>
					<input type="text" class="lumiere_border_width_medium" name="imdb_imdbplugindirectory_partial" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptions['imdbplugindirectory_partial']), 'lumiere-movies') ?>">
				</div>
				<div class="explain">
					<?php wp_kses( _e( 'Where <strong>Lumiere</strong> is installed.', 'lumiere-movies'), $allowed_html_for_esc_html_functions ); ?> 
					<br />
					<?php esc_html_e( 'Default:','lumiere-movies');?> "<?php echo IMDBLTURLPATH; ?>"

				</div>
			</div>
		</div>

		<br /><br />

		<?php //------------------------------------------------------------------=[ URL Popups ]=---- ?>
		<div class="lumiere_flex_container">
			<div class="lumiere_flex_container_content_twenty">
				<label for="imdb_imdburlpopups"><?php esc_html_e( 'URL for the popups', 'lumiere-movies'); ?></label>
			</div>
			<div class="lumiere_flex_container_content_eighty">
				<div class="lumiere_align_items_center">
					<?php echo $imdbOptions['blog_adress']; ?>
					<input type="text" class="lumiere_border_width_medium" name="imdb_imdburlpopups" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptions['imdburlpopups']), 'lumiere-movies') ?>">
				</div>
				<div class="explain"><?php esc_html_e( 'The URL that will be displayed for the movies\' and people\'s popups.', 'lumiere-movies'); ?> 
				<br />
				<?php esc_html_e( 'Default:','lumiere-movies');?> "<?php echo "/imdblt/"; ?>"
				<br />
				<br />
				<?php esc_html_e( 'Example: the full URL utilized for the movies\' popups will be:', 'lumiere-movies'); ?>
				<br />
				<?php echo $imdbOptions['blog_adress'] . $imdbOptions['imdburlpopups'] . 'film' ; ?>
				<br />
				<br />
				<?php esc_html_e( 'Example: the full URL utilized for the people\'s popup will be:', 'lumiere-movies'); ?>
				<br />
				<?php echo $imdbOptions['blog_adress'] . $imdbOptions['imdburlpopups'] . 'person' ; ?>
				</div>
			</div>
		</div>

		<br /><br />

		<?php //------------------------------------------------------------------=[ URL Taxonomy ]=---- ?>
		<div class="lumiere_flex_container">
			<div class="lumiere_flex_container_content_twenty">
				<label for="imdb_imdburlstringtaxo"><?php esc_html_e( 'URL for the taxonomy pages', 'lumiere-movies'); ?></label>
			</div>
			<div class="lumiere_flex_container_content_eighty">
				<div class="lumiere_align_items_center">
					<?php echo $imdbOptions['blog_adress']; ?>/
					<input type="text" class="lumiere_border_width_medium" name="imdb_imdburlstringtaxo" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptions['imdburlstringtaxo']), 'lumiere-movies') ?>">
				</div>
				<div class="explain"><?php esc_html_e( 'The URL that will be displayed for the taxonomy\'s pages. Warning! It cannot be identical to the URL of popups above.', 'lumiere-movies'); ?> 
				<br />
				<?php esc_html_e( 'Default:','lumiere-movies');?> "<?php echo "imdblt_"; ?>"
				<br />
				<br />
				<?php esc_html_e( 'The full URL utilized for the director taxonomy page will be:', 'lumiere-movies'); ?>
				<br />
				<?php echo $imdbOptions['blog_adress'] . '/' . $imdbOptions['imdburlstringtaxo'] . 'director' ; ?>
				</div>
			</div>
		</div>

	</div>
</div>

<?php	} // end of advanced section ?>
		

	<?php //------------------------------------------------------------------ =[Submit selection]=- ?>
	<div class="submit submit-imdb lumiere_sticky_boxshadow lumiere_align_center">
		<?php wp_nonce_field('options_general_check', 'options_general_check');   ?>
		<input type="submit" class="button-primary" name="reset_imdbSettings" value="<?php esc_html_e( 'Reset settings', 'lumiere-movies') ?>" />
		<input type="submit" class="button-primary" name="update_imdbSettings" value="<?php esc_html_e( 'Update settings', 'lumiere-movies') ?>" />
	</div>
	<br />
</form>
</div>
<?php
} // end user can manage options 
?>
