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
 #  Function : Widget configuration admin page                               #
 #									              #
 #############################################################################

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die(esc_html__("You are not allowed to call this page directly.", "lumiere-movies"));
}

// Enter in debug mode
if ((isset($imdbOptions['imdbdebug'])) && ($imdbOptions['imdbdebug'] == "1")){
	lumiere_debug_display($imdbOptionsw, 'SetError', ''); 
}

/* vars */

$messages = array( /* Template message notification options */
    'taxotemplatecopy_success' => 'Template successfully copied.',
    'taxotemplatecopy_failed' => 'Template copy failed!',
);

// If $_GET["msg"] is found, display a related notice
if ((isset($_GET['msg'])) && array_key_exists( sanitize_key( $_GET['msg'] ), $messages ) ){

	// Message for success
	if (sanitize_text_field( $_GET['msg'] ) == "taxotemplatecopy_success" ) {

		lumiere_notice(1, esc_html__( $messages["taxotemplatecopy_success"], 'lumiere-movies') );

	// Message for failure
	} elseif ( sanitize_text_field( $_GET['msg'] ) == "taxotemplatecopy_failed" ) {

		lumiere_notice(3, esc_html__( $messages["taxotemplatecopy_failed"] , 'lumiere-movies') );

	} 
}

// Data is posted using the form
if (current_user_can( 'manage_options' ) ) { 

	if ( (isset($_POST['update_imdbwidgetSettings'])) && check_admin_referer('imdbwidgetSettings_check', 'imdbwidgetSettings_check') ) { //--------------save data selected (widget options)

		foreach ($_POST as $key=>$postvalue) {
			// Sanitize
			$key_sanitized = sanitize_key($key);

			// Keep $_POST['imdbwidgetorderContainer'] untouched 
			if ($key == 'imdbwidgetorderContainer') continue;

			// Those $_POST values shouldn't be processed
			if ($key_sanitized == 'imdbwidgetsettings_check') continue;
			if ($key_sanitized == 'update_imdbwidgetsettings') continue;

			// remove "imdb_" from $key
			$keynoimdb = str_replace ( "imdb_", "", $key_sanitized);

			// Copy $_POST to $imdbOptionsw var
			if (isset($_POST["$key"])) {
				$imdbOptionsw["$keynoimdb"] = sanitize_text_field($_POST["$key_sanitized"]);
			}
		}

		// Special part related to details order
		if (isset($_POST['imdbwidgetorderContainer']) ){
			// Sanitize
			$myinputs_sanitized = lumiere_recursive_sanitize_text_field($_POST['imdbwidgetorderContainer']);
			// increment the $key of one
			$data = array_combine(range(1, count($myinputs_sanitized)), array_values($myinputs_sanitized));

			// flip $key with $value
			$data = array_flip($data);

			// Put in the option
			$imdbOptionsw['imdbwidgetorder'] = $data;
		}

		// update options
		update_option($imdb_ft->imdbWidgetOptionsName, $imdbOptionsw);

		// display confirmation message
		lumiere_notice(1, '<strong>'. esc_html__( 'Options saved.', 'lumiere-movies') .'</strong>');

		// Display a refresh link otherwise refreshed data is not seen
		if (!headers_sent()) {
			header("Location: ".esc_url($_SERVER[ "REQUEST_URI"]), false);
			die();
		} else {
			lumiere_notice(1, '<a href="'.wp_get_referer() .'">'. esc_html__( 'Go back', 'lumiere-movies') .'</a>');
			die();
		}

	 }

	// reset options selected  (widget options)
	if ( (isset($_POST['reset_imdbwidgetSettings'])) && check_admin_referer('imdbwidgetSettings_check', 'imdbwidgetSettings_check') ) { 

		// Delete the options to reset
		delete_option($imdb_ft->imdbWidgetOptionsName);

		// Display a refresh link otherwise refreshed data is not seen
		if (!headers_sent()) {

			//header("Refresh: 0;url=".$_SERVER[ "REQUEST_URI"]."&reset=true", false);
			wp_safe_redirect( wp_get_referer() ); 
			exit();

		} else {

			lumiere_notice(1, '<a href="'.wp_get_referer() .'">'. esc_html__( 'Go back', 'lumiere-movies') .'</a>');
			exit();
		}

		// display confirmation message
		lumiere_notice(1, '<strong>'. esc_html__( 'Options reset.', 'lumiere-movies') .'</strong>');

	}

?>
<div id="tabswrap">
	<div class="imdblt_double_container lumiere_padding_five">
		<div class="imdblt_flex_auto"><img src="<?php echo esc_url( $imdbOptions['imdbplugindirectory'] . "pics/admin-widget-inside-whattodisplay.png"); ?>" align="absmiddle" width="16px" />&nbsp;<a title="<?php esc_html_e( "What to display", 'lumiere-movies');?>" href="<?php echo esc_url ( admin_url() . "admin.php?page=imdblt_options&subsection=widgetoption&widgetoption=what"); ?>"><?php esc_html_e( 'What to display', 'lumiere-movies'); ?></a></div>
			<?php if ($imdbOptions['imdbtaxonomy'] == "1") { ?>
		<div class="imdblt_flex_auto">&nbsp;&nbsp;<img src="<?php echo esc_url( $imdbOptions['imdbplugindirectory'] . "pics/admin-widget-inside-whattotaxo.png"); ?>" align="absmiddle" width="16px" />&nbsp;<a title="<?php esc_html_e( "What to taxonomize", 'lumiere-movies');?>" href="<?php echo esc_url ( admin_url() . "admin.php?page=imdblt_options&subsection=widgetoption&widgetoption=taxo"); ?>"><?php esc_html_e( "What to taxonomize", 'lumiere-movies'); ?></a></div>
			<?php } else { ?>
		<div class="imdblt_flex_auto">&nbsp;&nbsp;<img src="<?php echo esc_url( $imdbOptions['imdbplugindirectory'] ."pics/admin-widget-inside-whattodisplay.png"); ?>" align="absmiddle" width="16px" />&nbsp;<i><?php esc_html_e( "Taxonomy unactivated", 'lumiere-movies');?></i></div>
			<?php }?>
		<div class="imdblt_flex_auto">&nbsp;&nbsp;<img src="<?php echo esc_url( $imdbOptions['imdbplugindirectory'] . "pics/admin-widget-inside-order.png"); ?>" align="absmiddle" width="16px" />&nbsp;<a title="<?php esc_html_e( "Display order", 'lumiere-movies');?>" href="<?php echo esc_url ( admin_url() . "admin.php?page=imdblt_options&subsection=widgetoption&widgetoption=order"); ?>"><?php esc_html_e( "Display order", 'lumiere-movies'); ?></a></div>
		<div class=" imdblt_flex_auto">&nbsp;&nbsp;<img src="<?php echo esc_url( $imdbOptions['imdbplugindirectory']. "pics/admin-widget-inside-misc.png"); ?>" align="absmiddle" width="16px" />&nbsp;<a title="<?php esc_html_e( "Misc", 'lumiere-movies');?>" href="<?php echo esc_url ( admin_url() . "admin.php?page=imdblt_options&subsection=widgetoption&widgetoption=misc"); ?>"><?php esc_html_e( 'Misc', 'lumiere-movies'); ?></a></div>
	</div>
</div>

<div id="poststuff" class="metabox-holder">

	<div class="inside">

	<form method="post" id="imdbconfig_save" name="imdbconfig_save" action="<?php echo $_SERVER[ "REQUEST_URI"]; ?>" >
		
<?php //--------------------------------------------------------------=[actors, aka, colors]=- 

// What to display 

if ( (isset($_GET['widgetoption']) && ($_GET['widgetoption'] == "what")) || (!isset($_GET['widgetoption'] )) ) {?>

		<div class="imblt_border_shadow">
			<div class="titresection"><?php esc_html_e( 'What to display', 'lumiere-movies'); ?></div>

				<div class="imdblt_double_container">

					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdbOptionsw['imdbwidgetactor'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Actor', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Actor', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetactor_yes" name="imdb_imdbwidgetactor" value="1" <?php if ($imdbOptionsw['imdbwidgetactor'] == "1") { echo 'checked="checked"'; }?> data-modificator="yes" data-field_to_change="imdb_imdbwidgetactornumber" data-field_to_change_value="0" /><label for="imdb_imdbwidgetactor_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>
						<input type="radio" id="imdb_imdbwidgetactor_no" name="imdb_imdbwidgetactor" value="" <?php if ($imdbOptionsw['imdbwidgetactor'] == 0) { echo 'checked="checked"'; } ?> data-modificator="yes" data-field_to_change="imdb_imdbwidgetactornumber" data-field_to_change_value="1" /><label for="imdb_imdbwidgetactor_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<input type="text" id="imdb_imdbwidgetactornumber" name="imdb_imdbwidgetactornumber" size="3" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptionsw['imdbwidgetactornumber']), 'lumiere-movies') ?>" <?php if ($imdbOptionsw['imdbwidgetactor'] == 0){ echo 'disabled="disabled"'; }; ?> />

						<div class="explain"><?php esc_html_e( 'Display (how many) actors', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'Yes', 'lumiere-movies'); ?> & 10</div>
					</div>

					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdbOptionsw['imdbwidgetalsoknow'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Also known as', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Also known as', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetalsoknow_yes" name="imdb_imdbwidgetalsoknow" value="1" <?php if ($imdbOptionsw['imdbwidgetalsoknow'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetalsoknow_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgetalsoknow_no" name="imdb_imdbwidgetalsoknow" value="" <?php if ($imdbOptionsw['imdbwidgetalsoknow'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetalsoknow_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<div class="explain"><?php esc_html_e( "Display all movie's names", 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>

					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdbOptionsw['imdbwidgetcolors'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Colors', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Colors', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetcolors_yes" name="imdb_imdbwidgetcolors" value="1" <?php if ($imdbOptionsw['imdbwidgetcolors'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetcolors_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgetcolorsno" name="imdb_imdbwidgetcolors" value="" <?php if ($imdbOptionsw['imdbwidgetcolors'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetcolors_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<div class="explain"><?php esc_html_e( "Display colors", 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>



<?php //-------------------------------------------------------------------=[composer, country, creator]=- ?>		


					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdbOptionsw['imdbwidgetcomposer'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Composer', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Composer', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetcomposer_yes" name="imdb_imdbwidgetcomposer" value="1" <?php if ($imdbOptionsw['imdbwidgetcomposer'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetcomposer_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgetcomposer_no" name="imdb_imdbwidgetcomposer" value="" <?php if ($imdbOptionsw['imdbwidgetcomposer'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetcomposer_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<div class="explain"><?php esc_html_e( 'Display composer', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>


					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdbOptionsw['imdbwidgetcountry'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Country', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Country', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetcountry_yes" name="imdb_imdbwidgetcountry" value="1" <?php if ($imdbOptionsw['imdbwidgetcountry'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetcountry_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgetcountry_no" name="imdb_imdbwidgetcountry" value="" <?php if ($imdbOptionsw['imdbwidgetcountry'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetcountry_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<div class="explain"><?php esc_html_e( 'Display country', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>

					<div class="imdblt_double_container_content_third lumiere_padding_five">
						<?php if ($imdbOptionsw['imdbwidgetcreator'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Creator', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Creator', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetcreator_yes" name="imdb_imdbwidgetcreator" value="1" <?php if ($imdbOptionsw['imdbwidgetcreator'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetcreator_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>
						<input type="radio" id="imdb_imdbwidgetcreator_no" name="imdb_imdbwidgetcreator" value="" <?php if ($imdbOptionsw['imdbwidgetcreator'] == 0) { echo 'checked="checked"'; } ?>  /><label for="imdb_imdbwidgetcreator_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>	

						<div class="explain"><?php esc_html_e( 'Display Creator', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>


<?php //-------------------------------------------------------------------=[director, genre, goofs]=- ?>	

					<div class="imdblt_double_container_content_third lumiere_padding_five">
						<?php if ($imdbOptionsw['imdbwidgetdirector'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Director', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Director', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetdirector_yes" name="imdb_imdbwidgetdirector" value="1" <?php if ($imdbOptionsw['imdbwidgetdirector'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetdirector_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>
						<input type="radio" id="imdb_imdbwidgetdirector_no" name="imdb_imdbwidgetdirector" value="" <?php if ($imdbOptionsw['imdbwidgetdirector'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetdirector_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

					<div class="explain"><?php esc_html_e( 'Display directors', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'Yes', 'lumiere-movies'); ?></div>

					</div>

					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdbOptionsw['imdbwidgetgenre'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Genre', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Genre', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetgenre_yes" name="imdb_imdbwidgetgenre" value="1" <?php if ($imdbOptionsw['imdbwidgetgenre'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetgenre_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgetgenre_no" name="imdb_imdbwidgetgenre" value="" <?php if ($imdbOptionsw['imdbwidgetgenre'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetgenre_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<div class="explain"><?php esc_html_e( 'Display genre(s)', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'Yes', 'lumiere-movies'); ?></div>

					</div>

					<div class="imdblt_double_container_content_third lumiere_padding_five">
						<?php if ($imdbOptionsw['imdbwidgetgoofs'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Goofs', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Goofs', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetgoofs_yes" name="imdb_imdbwidgetgoofs" value="1" <?php if ($imdbOptionsw['imdbwidgetgoofs'] == "1") { echo 'checked="checked"'; }?> data-modificator="yes" data-field_to_change="imdb_imdbwidgetgoofsnumber" data-field_to_change_value="0" /><label for="imdb_imdbwidgetgoofs_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgetgoofs_no" name="imdb_imdbwidgetgoofs" value="" <?php if ($imdbOptionsw['imdbwidgetgoofs'] == 0) { echo 'checked="checked"'; } ?> data-modificator="yes" data-field_to_change="imdb_imdbwidgetgoofsnumber" data-field_to_change_value="1" /><label for="imdb_imdbwidgetgoofs_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<input type="text" id="imdb_imdbwidgetgoofsnumber" name="imdb_imdbwidgetgoofsnumber" size="3" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptionsw['imdbwidgetgoofsnumber']), 'lumiere-movies') ?>" <?php if ($imdbOptionsw['imdbwidgetgoofs'] == 0){ echo 'disabled="disabled"'; }; ?> />

						<div class="explain"><?php esc_html_e( 'Display (how many) goof', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>



<?php //-------------------------------------------------------------------=[keywords, language, official site]=- ?>


					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdbOptionsw['imdbwidgetkeywords'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Keywords', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Keywords', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetkeywords_yes" name="imdb_imdbwidgetkeywords" value="1" <?php if ($imdbOptionsw['imdbwidgetkeywords'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetkeywords_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgetkeywords_no" name="imdb_imdbwidgetkeywords" value="" <?php if ($imdbOptionsw['imdbwidgetkeywords'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetkeywords_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<div class="explain"><?php esc_html_e( 'Display keywords', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>

					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdbOptionsw['imdbwidgetlanguage'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Language', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Language', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetlanguage_yes" name="imdb_imdbwidgetlanguage" value="1" <?php if ($imdbOptionsw['imdbwidgetlanguage'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetlanguage_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgetlanguage_no" name="imdb_imdbwidgetlanguage" value="" <?php if ($imdbOptionsw['imdbwidgetlanguage'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetlanguage_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<div class="explain"><?php esc_html_e( 'Display language(s)', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>


					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdbOptionsw['imdbwidgetofficialsites'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Official websites', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Official websites', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetofficialsites_yes" name="imdb_imdbwidgetofficialsites" value="1" <?php if ($imdbOptionsw['imdbwidgetofficialsites'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetofficialsites_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgetofficialsites_no" name="imdb_imdbwidgetofficialsites" value="" <?php if ($imdbOptionsw['imdbwidgetofficialsites'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetofficialsites_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<div class="explain"><?php esc_html_e( 'Display official websites', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>


<?php //-------------------------------------------------------------------=[picture, plot, producer]=- ?>

					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdbOptionsw['imdbwidgetpic'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Picture', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Picture', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetpic_yes" name="imdb_imdbwidgetpic" value="1" <?php if ($imdbOptionsw['imdbwidgetpic'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetpic_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>
						<input type="radio" id="imdb_imdbwidgetpic_no" name="imdb_imdbwidgetpic" value="" <?php if ($imdbOptionsw['imdbwidgetpic'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetpic_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<div class="explain"><?php esc_html_e( 'Display the picture', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'Yes', 'lumiere-movies'); ?></div>

					</div>

					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdbOptionsw['imdbwidgetplot'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Plot', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Plot', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetplot_yes" name="imdb_imdbwidgetplot" value="1" <?php if ($imdbOptionsw['imdbwidgetplot'] == "1") { echo 'checked="checked"'; }?> data-modificator="yes" data-field_to_change="imdb_imdbwidgetplotnumber" data-field_to_change_value="0" /><label for="imdb_imdbwidgetplot_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgetplot_no" name="imdb_imdbwidgetplot" value="" <?php if ($imdbOptionsw['imdbwidgetplot'] == 0) { echo 'checked="checked"'; } ?> data-modificator="yes" data-field_to_change="imdb_imdbwidgetplotnumber" data-field_to_change_value="1" /><label for="imdb_imdbwidgetplot_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<input type="text" id="imdb_imdbwidgetplotnumber" name="imdb_imdbwidgetplotnumber" size="3" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptionsw['imdbwidgetplotnumber']), 'lumiere-movies') ?>" <?php if ($imdbOptionsw['imdbwidgetplot'] == 0){ echo 'disabled="disabled"'; }; ?> />

						<div class="explain"><?php esc_html_e( 'Display plot(s). Be careful, this field may need a lot of space. In ideal case, this plugin is used inside a post and not into a widget.', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>

					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdbOptionsw['imdbwidgetproducer'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Producer', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Producer', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetproducer_yes" name="imdb_imdbwidgetproducer" value="1" <?php if ($imdbOptionsw['imdbwidgetproducer'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetproducer_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>
						<input type="radio" id="imdb_imdbwidgetproducer_no" name="imdb_imdbwidgetproducer" value="" <?php if ($imdbOptionsw['imdbwidgetproducer'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetproducer_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<div class="explain"><?php esc_html_e( 'Display producer(s)', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>


<?php //-------------------------------------------------------------------=[produ company, quotes, rating]=- ?>

					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdbOptionsw['imdbwidgetprodcompany'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Production company', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Production company', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetprodcompany_yes" name="imdb_imdbwidgetprodcompany" value="1" <?php if ($imdbOptionsw['imdbwidgetprodcompany'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetprodcompany_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgetprodcompany_no" name="imdb_imdbwidgetprodcompany" value="" <?php if ($imdbOptionsw['imdbwidgetprodcompany'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetprodcompany_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<div class="explain"><?php esc_html_e( 'Display the production companies', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>

					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdbOptionsw['imdbwidgetquotes'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Quotes', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Quotes', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetquotes_yes" name="imdb_imdbwidgetquotes" value="1" <?php if ($imdbOptionsw['imdbwidgetquotes'] == "1") { echo 'checked="checked"'; }?> data-modificator="yes" data-field_to_change="imdb_imdbwidgetquotesnumber" data-field_to_change_value="0" /><label for="imdb_imdbwidgetquotes_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgetquotes_no" name="imdb_imdbwidgetquotes" value="" <?php if ($imdbOptionsw['imdbwidgetquotes'] == 0) { echo 'checked="checked"'; } ?> data-modificator="yes" data-field_to_change="imdb_imdbwidgetquotesnumber" data-field_to_change_value="1" /><label for="imdb_imdbwidgetquotes_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<input type="text" id="imdb_imdbwidgetquotesnumber" name="imdb_imdbwidgetquotesnumber" size="3" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptionsw['imdbwidgetquotesnumber']), 'lumiere-movies') ?>" <?php if ($imdbOptionsw['imdbwidgetquotes'] == 0){ echo 'disabled="disabled"'; }; ?> />

						<div class="explain"><?php esc_html_e( "Display (how many) quotes from movie", 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>

					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdbOptionsw['imdbwidgetrating'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Rating', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Rating', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetrating_yes" name="imdb_imdbwidgetrating" value="1" <?php if ($imdbOptionsw['imdbwidgetrating'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetrating_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgetrating_no" name="imdb_imdbwidgetrating" value="" <?php if ($imdbOptionsw['imdbwidgetrating'] == 0) { echo 'checked="checked"'; } ?>  /><label for="imdb_imdbwidgetrating_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<div class="explain"><?php esc_html_e( 'Display rating', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>



<?php //-------------------------------------------------------------------=[runtime, soundtrack, source]=- ?>

					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdbOptionsw['imdbwidgetruntime'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Runtime', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Runtime', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetruntime_yes" name="imdb_imdbwidgetruntime" value="1" <?php if ($imdbOptionsw['imdbwidgetruntime'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetruntime_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>
						<input type="radio" id="imdb_imdbwidgetruntime_no" name="imdb_imdbwidgetruntime" value="" <?php if ($imdbOptionsw['imdbwidgetruntime'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetruntime_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>
						<div class="explain"><?php esc_html_e( 'Display the runtime', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>

					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdbOptionsw['imdbwidgetsoundtrack'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Soundtrack', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Soundtrack', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetsoundtrack_yes" name="imdb_imdbwidgetsoundtrack" value="1" <?php if ($imdbOptionsw['imdbwidgetsoundtrack'] == "1") { echo 'checked="checked"'; }?> data-modificator="yes" data-field_to_change="imdb_imdbwidgetsoundtracknumber" data-field_to_change_value="0" /><label for="imdb_imdbwidgetsoundtrack_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgetsoundtrack" name="imdb_imdbwidgetsoundtrack" value="" <?php if ($imdbOptionsw['imdbwidgetsoundtrack'] == 0) { echo 'checked="checked"'; } ?> data-modificator="yes" data-field_to_change="imdb_imdbwidgetsoundtracknumber" data-field_to_change_value="1" /><label for="imdb_imdbwidgetsoundtrack_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<input type="text" id="imdb_imdbwidgetsoundtracknumber" name="imdb_imdbwidgetsoundtracknumber" size="3" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptionsw['imdbwidgetsoundtracknumber']), 'lumiere-movies') ?>" <?php if ($imdbOptionsw['imdbwidgetsoundtrack'] == 0){ echo 'disabled="disabled"'; }; ?> />

						<div class="explain"><?php esc_html_e( "Display (how many) soundtrack", 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>

					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdbOptionsw['imdbwidgetsource'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Source', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Source', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />

						<input type="radio" id="imdb_imdbwidgetsource_yes" name="imdb_imdbwidgetsource" value="1" <?php if ($imdbOptionsw['imdbwidgetsource'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetsource_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgetsource_no" name="imdb_imdbwidgetsource" value="" <?php if ($imdbOptionsw['imdbwidgetsource'] == 0) { echo 'checked="checked"'; } ?>  /><label for="imdb_imdbwidgetsource_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<div class="explain"><?php esc_html_e( 'Display website source at the end of the post', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'Yes', 'lumiere-movies'); ?></div>

					</div>


<?php //-------------------------------------------------------------------=[tagline, title, trailer]=- ?>

					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdbOptionsw['imdbwidgettaglines'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Tagline', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Tagline', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgettaglines_yes" name="imdb_imdbwidgettaglines" value="1" <?php if ($imdbOptionsw['imdbwidgettaglines'] == "1") { echo 'checked="checked"'; }?> data-modificator="yes" data-field_to_change="imdb_imdbwidgettaglinesnumber" data-field_to_change_value="0" /><label for="imdb_imdbwidgettaglines_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgettaglines_no" name="imdb_imdbwidgettaglines" value="" <?php if ($imdbOptionsw['imdbwidgettaglines'] == 0) { echo 'checked="checked"'; } ?> data-modificator="yes" data-field_to_change="imdb_imdbwidgettaglinesnumber" data-field_to_change_value="1" /><label for="imdb_imdbwidgettaglines_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<input type="text" id="imdb_imdbwidgettaglinesnumber" name="imdb_imdbwidgettaglinesnumber" size="3" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptionsw['imdbwidgettaglinesnumber']), 'lumiere-movies') ?>" <?php if ($imdbOptionsw['imdbwidgettaglines'] == 0){ echo 'disabled="disabled"'; }; ?> />

						<div class="explain"><?php esc_html_e( 'Display (how many) tagline', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>


					<div class="imdblt_double_container_content_third lumiere_padding_five">
						<?php if ($imdbOptionsw['imdbwidgettitle'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Title', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Title', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						<input type="radio" id="imdb_imdbwidgettitle_yes" name="imdb_imdbwidgettitle" value="1" <?php if ($imdbOptionsw['imdbwidgettitle'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgettitle_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>
						<input type="radio" id="imdb_imdbwidgettitle_no" name="imdb_imdbwidgettitle" value="" <?php if ($imdbOptionsw['imdbwidgettitle'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgettitle_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<div class="explain"><?php esc_html_e( 'Display the title', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'Yes', 'lumiere-movies'); ?></div>

					</div>


					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdbOptionsw['imdbwidgettrailer'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Trailers', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Trailers', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgettrailer_yes" name="imdb_imdbwidgettrailer" value="1" <?php if ($imdbOptionsw['imdbwidgettrailer'] == "1") { echo 'checked="checked"'; }?> data-modificator="yes" data-field_to_change="imdb_imdbwidgettrailernumber" data-field_to_change_value="0" /><label for="imdb_imdbwidgettrailer_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgettrailer_no" name="imdb_imdbwidgettrailer" value="" <?php if ($imdbOptionsw['imdbwidgettrailer'] == 0) { echo 'checked="checked"'; } ?> data-modificator="yes" data-field_to_change="imdb_imdbwidgettrailernumber" data-field_to_change_value="1" /><label for="imdb_imdbwidgettrailer_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<input type="text" id="imdb_imdbwidgettrailernumber" name="imdb_imdbwidgettrailernumber" size="3" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptionsw['imdbwidgettrailernumber']), 'lumiere-movies') ?>" <?php if ($imdbOptionsw['imdbwidgettrailernumber'] == 0){ echo 'disabled="disabled"'; }; ?> />

						<div class="explain"><?php esc_html_e( 'Display (how many) trailers', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>


<?php //-------------------------------------------------------------------=[user comment, writer, year]=- ?>

					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdbOptionsw['imdbwidgetcomments'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Users comment', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Users comment', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetcomments_yes" name="imdb_imdbwidgetcomments" value="1" <?php if ($imdbOptionsw['imdbwidgetcomments'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetcomments_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgetcomments_no" name="imdb_imdbwidgetcomments" value="" <?php if ($imdbOptionsw['imdbwidgetcomments'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetcomments_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<div class="explain"><?php esc_html_e( "Display the main user comment", 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>

					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdbOptionsw['imdbwidgetwriter'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Writer', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Writer', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetwriter_yes" name="imdb_imdbwidgetwriter" value="1" <?php if ($imdbOptionsw['imdbwidgetwriter'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetwriter_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgetwriter_no" name="imdb_imdbwidgetwriter" value="" <?php if ($imdbOptionsw['imdbwidgetwriter'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetwriter_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<div class="explain"><?php esc_html_e( 'Display writers', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'Yes', 'lumiere-movies'); ?></div>

					</div>

					<div class="imdblt_double_container_content_third lumiere_padding_five">
						<?php if ($imdbOptionsw['imdbwidgetyear'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Year', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Year', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetyear_yes" name="imdb_imdbwidgetyear" value="1" <?php if ($imdbOptionsw['imdbwidgetyear'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetyear_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>
						<input type="radio" id="imdb_imdbwidgetyear_no" name="imdb_imdbwidgetyear" value="" <?php if ($imdbOptionsw['imdbwidgetyear'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetyear_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>
						<div class="explain"><?php esc_html_e( "Display release year. Year will appear next title's movie, in brackets.", 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>

				</div><!-- end double container -->
			</div>
		</div>
<?php	} 

//-------------------------------------------------------------------=[Taxonomy]=-

		if ( (isset($_GET['widgetoption'])) && ($_GET['widgetoption'] == "taxo") ) { 	// Taxonomy 

			if ($imdbOptions['imdbtaxonomy'] != "1") { //check if taxonomy is activated

				echo "<div align='center' class='accesstaxo'>".esc_html__('Please ', 'lumiere-movies')."<a href='".esc_url ( admin_url().'admin.php?page=imdblt_options&generaloption=advanced') . "'>" . __('activate taxonomy', 'lumiere-movies') . '</a>' . esc_html__(' priorly', 'lumiere-movies') . '<br />' . esc_html__('to access taxonomies options.', 'lumiere-movies') . "</div>";

			} else { // taxonomy is activated ?>

	<div class="postbox">
		<h3 class="hndle" id="taxodetails" name="taxodetails"><?php esc_html_e( 'Select details to use as taxonomy', 'lumiere-movies'); ?></h3>
	</div>

	<div class="imblt_border_shadow">

		<div class="lumiere_intro_options"><?php esc_html_e( "Use the checkbox to display the taxonomy tags. When activated, selected taxonomy will become blue if it is activated into 'What to display' section and will turn red otherwise.", 'lumiere-movies'); ?>
		<br /><br />
		<?php esc_html_e( "Cautiously select the categories you want to display: it may have some unwanted effects, in particular if you display many movies in the same post at once. When selecting one of the following taxonomy options, it will supersede any other function or link created; for instance, you will not have access anymore to the popups for directors, if directors taxonomy is chosen. Taxonomy will always prevail over other Lumiere functionalities.", 'lumiere-movies'); ?>

		<br /><br />
		<?php esc_html_e( "Note: once activated, each taxonomy category will show a new option to copy a taxonomy template directy into your template folder.", 'lumiere-movies'); ?>
		</div>
		<br /><br />

		<div class="imdblt_double_container">
			<div class="imdblt_double_container_content_third lumiere_padding_five">

				<input type="checkbox" id="imdb_imdbtaxonomyactor" name="imdb_imdbtaxonomyactor" value="<?php if ($imdbOptionsw['imdbtaxonomyactor'] == "1") { echo '0'; } else { echo '1'; }?>" />
				<label for="imdb_imdbtaxonomyactor">
					<?php if ($imdbOptionsw['imdbtaxonomyactor'] == "1") { 
							if ($imdbOptionsw['imdbwidgetactor'] == 1){echo '<span class="lumiere-option-taxo-activated">'; } else {	echo '<span class="lumiere-option-taxo-deactivated">'; }
							esc_html_e( 'Actors', 'lumiere-movies'); echo '</span>'; } else { ?><?php  esc_html_e( 'Actors', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?>
				</label> 
				<?php
				if ($imdbOptionsw['imdbtaxonomyactor'] == "1") {
					echo "<br />";
					echo "<a href='" . esc_url( $imdbOptions['imdbplugindirectory'] . "inc/move_template_taxonomy.php?taxotype=actor") . "' " .
						"title='" . esc_html__("Copy a standard taxonomy template to your template folder to display this taxonomy.", 'lumiere-movies') . "' >".
						"<img src='".esc_url( $imdbOptions['imdbplugindirectory'] . "pics/admin-widget-copy-theme.png") . "' alt='copy the taxonomy template' align='absmiddle' align='absmiddle' />".
						esc_html__("Copy template", 'lumiere-movies') .
					"</a>";
				}
				?> 

			</div>
			<div class="imdblt_double_container_content_third lumiere_padding_five">

				<input type="checkbox" id="imdb_imdbtaxonomycolor" name="imdb_imdbtaxonomycolor" value="<?php if ($imdbOptionsw['imdbtaxonomycolor'] == "1") { echo '0'; } else { echo '1'; }?>" />

				<label for="imdb_imdbtaxonomycolor">
					<?php if ($imdbOptionsw['imdbtaxonomycolor'] == "1") { 

							if ($imdbOptionsw['imdbwidgetcolors'] == 1){echo '<span class="lumiere-option-taxo-activated">'; } else {	echo '<span class="lumiere-option-taxo-deactivated">'; }

							esc_html_e( 'Colors', 'lumiere-movies'); echo '</span>'; } else { ?><?php  esc_html_e( 'Colors', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?>
				</label> 

				<?php
				if ($imdbOptionsw['imdbtaxonomycolor'] == "1") {
					echo "<br />";
					echo "<a href='" . esc_url( $imdbOptions['imdbplugindirectory'] . "inc/move_template_taxonomy.php?taxotype=color") . "' " .
						"title='" . esc_html__("Copy a standard taxonomy template to your template folder to display this taxonomy.", 'lumiere-movies') . "' >".
						"<img src='".esc_url( $imdbOptions['imdbplugindirectory'] . "pics/admin-widget-copy-theme.png") . "' alt='copy the taxonomy template' align='absmiddle' align='absmiddle' />".
						esc_html__("Copy template", 'lumiere-movies') .
					"</a>";
				}
				?> 

			</div>
			<div class="imdblt_double_container_content_third lumiere_padding_five">

				<input type="checkbox" id="imdb_imdbtaxonomycomposer" name="imdb_imdbtaxonomycomposer" value="<?php if ($imdbOptionsw['imdbtaxonomycomposer'] == "1") { echo '0'; } else { echo '1'; }?>" />
				<label for="imdb_imdbtaxonomycomposer">
					<?php if ($imdbOptionsw['imdbtaxonomycomposer'] == "1") { 

							if ($imdbOptionsw['imdbwidgetcomposer'] == 1){echo '<span class="lumiere-option-taxo-activated">'; } else { echo '<span class="lumiere-option-taxo-deactivated">'; }

							esc_html_e( 'Composers', 'lumiere-movies'); echo '</span>'; } else { ?><?php  esc_html_e( 'Composers', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?>
				</label> 
				<?php
				if ($imdbOptionsw['imdbtaxonomycomposer'] == "1") {
					echo "<br />";
					echo "<a href='" . esc_url( $imdbOptions['imdbplugindirectory'] . "inc/move_template_taxonomy.php?taxotype=composer") . "' " .
						"title='" . esc_html__("Copy a standard taxonomy template to your template folder to display this taxonomy.", 'lumiere-movies') . "' >".
						"<img src='".esc_url( $imdbOptions['imdbplugindirectory'] . "pics/admin-widget-copy-theme.png") . "' alt='copy the taxonomy template' align='absmiddle' align='absmiddle' />".
						esc_html__("Copy template", 'lumiere-movies') .
					"</a>";
				}
				?> 
			</div>
		</div>

		<div class="imdblt_double_container">

			<div class="imdblt_double_container_content_third lumiere_padding_five">

				<input type="checkbox" id="imdb_imdbtaxonomycountry" name="imdb_imdbtaxonomycountry" value="<?php if ($imdbOptionsw['imdbtaxonomycountry'] == "1") { echo '0'; } else { echo '1'; }?>" />
				<label for="imdb_imdbtaxonomycountry">
					<?php if ($imdbOptionsw['imdbtaxonomycountry'] == "1") { 

							if ($imdbOptionsw['imdbwidgetcountry'] == 1){echo '<span class="lumiere-option-taxo-activated">'; } else { echo '<span class="lumiere-option-taxo-deactivated">'; }

							esc_html_e( 'Countries', 'lumiere-movies'); echo '</span>'; } else { ?><?php  esc_html_e( 'Countries', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?>
				</label> 
				<?php
				if ($imdbOptionsw['imdbtaxonomycountry'] == "1") {
					echo "<br />";
					echo "<a href='" . esc_url( $imdbOptions['imdbplugindirectory'] . "inc/move_template_taxonomy.php?taxotype=country") . "' " .
						"title='" . esc_html__("Copy a standard taxonomy template to your template folder to display this taxonomy.", 'lumiere-movies') . "' >".
						"<img src='".esc_url( $imdbOptions['imdbplugindirectory'] . "pics/admin-widget-copy-theme.png") . "' alt='copy the taxonomy template' align='absmiddle' align='absmiddle' />".
						esc_html__("Copy template", 'lumiere-movies') .
					"</a>";
				}
				?> 

			</div>

			<div class="imdblt_double_container_content_third lumiere_padding_five">

				<input type="checkbox" id="imdb_imdbtaxonomycreator" name="imdb_imdbtaxonomycreator" value="<?php if ($imdbOptionsw['imdbtaxonomycreator'] == "1") { echo '0'; } else { echo '1'; }?>" />
				<label for="imdb_imdbtaxonomycreator">
					<?php if ($imdbOptionsw['imdbtaxonomycreator'] == "1") { 

							if ($imdbOptionsw['imdbwidgetcreator'] == 1){echo '<span class="lumiere-option-taxo-activated">'; } else { echo '<span class="lumiere-option-taxo-deactivated">'; }

							esc_html_e( 'Creators', 'lumiere-movies'); echo '</span>'; } else { ?><?php  esc_html_e( 'Creators', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?>
				</label> 
				<?php
				if ($imdbOptionsw['imdbtaxonomycreator'] == "1") {
					echo "<br />";
					echo "<a href='" . esc_url( $imdbOptions['imdbplugindirectory'] . "inc/move_template_taxonomy.php?taxotype=creator") . "' " .
						"title='" . esc_html__("Copy a standard taxonomy template to your template folder to display this taxonomy.", 'lumiere-movies') . "' >".
						"<img src='".esc_url( $imdbOptions['imdbplugindirectory'] . "pics/admin-widget-copy-theme.png") . "' alt='copy the taxonomy template' align='absmiddle' align='absmiddle' />".
						esc_html__("Copy template", 'lumiere-movies') .
					"</a>";
				}
				?> 

			</div>

			<div class="imdblt_double_container_content_third lumiere_padding_five">

				<input type="checkbox" id="imdb_imdbtaxonomydirector" name="imdb_imdbtaxonomydirector" value="<?php if ($imdbOptionsw['imdbtaxonomydirector'] == "1") { echo '0'; } else { echo '1'; }?>" />
				<label for="imdb_imdbtaxonomydirector">
					<?php if ($imdbOptionsw['imdbtaxonomydirector'] == "1") { 

							if ($imdbOptionsw['imdbwidgetdirector'] == 1){echo '<span class="lumiere-option-taxo-activated">'; } else { echo '<span class="lumiere-option-taxo-deactivated">'; }

							esc_html_e( 'Directors', 'lumiere-movies'); echo '</span>'; } else { ?><?php  esc_html_e( 'Directors', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?>
				</label> 
				<?php
				if ($imdbOptionsw['imdbtaxonomydirector'] == "1") {
					echo "<br />";
					echo "<a href='" . esc_url( $imdbOptions['imdbplugindirectory'] . "inc/move_template_taxonomy.php?taxotype=director") . "' " .
						"title='" . esc_html__("Copy a standard taxonomy template to your template folder to display this taxonomy.", 'lumiere-movies') . "' >".
						"<img src='".esc_url( $imdbOptions['imdbplugindirectory'] . "pics/admin-widget-copy-theme.png") . "' alt='copy the taxonomy template' align='absmiddle' align='absmiddle' />".
						esc_html__("Copy template", 'lumiere-movies') .
					"</a>";
				}
				?> 
			</div>
		</div>

		<div class="imdblt_double_container">
			<div class="imdblt_double_container_content_third lumiere_padding_five">

				<input type="checkbox" id="imdb_imdbtaxonomygenre" name="imdb_imdbtaxonomygenre" value="<?php if ($imdbOptionsw['imdbtaxonomygenre'] == "1") { echo '0'; } else { echo '1'; }?>" />
				<label for="imdb_imdbtaxonomygenre">
					<?php if ($imdbOptionsw['imdbtaxonomygenre'] == "1") { 

							if ($imdbOptionsw['imdbwidgetgenre'] == 1){echo '<span class="lumiere-option-taxo-activated">'; } else { echo '<span class="lumiere-option-taxo-deactivated">'; }

							esc_html_e( 'Genres', 'lumiere-movies'); echo '</span>'; } else { ?><?php  esc_html_e( 'Genres', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?>
				</label> 
				<?php
				if ($imdbOptionsw['imdbtaxonomygenre'] == "1") {
					echo "<br />";
					echo "<a href='" . esc_url( $imdbOptions['imdbplugindirectory'] . "inc/move_template_taxonomy.php?taxotype=genre") . "' " .
						"title='" . esc_html__("Copy a standard taxonomy template to your template folder to display this taxonomy.", 'lumiere-movies') . "' >".
						"<img src='".esc_url( $imdbOptions['imdbplugindirectory'] . "pics/admin-widget-copy-theme.png") . "' alt='copy the taxonomy template' align='absmiddle' align='absmiddle' />".
						esc_html__("Copy template", 'lumiere-movies') .
					"</a>";
				}
				?> 

			</div>
			<div class="imdblt_double_container_content_third lumiere_padding_five">

				<input type="checkbox" id="imdb_imdbtaxonomykeywords" name="imdb_imdbtaxonomykeywords" value="<?php if ($imdbOptionsw['imdbtaxonomykeywords'] == "1") { echo '0'; } else { echo '1'; }?>" />
				<label for="imdb_imdbtaxonomykeywords">
					<?php if ($imdbOptionsw['imdbtaxonomykeywords'] == "1") { 

							if ($imdbOptionsw['imdbwidgetkeywords'] == 1){echo '<span class="lumiere-option-taxo-activated">'; } else { echo '<span class="lumiere-option-taxo-deactivated">'; }

							esc_html_e( 'Keywords', 'lumiere-movies'); echo '</span>'; } else { ?><?php  esc_html_e( 'Keywords', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?>
				</label> 				
				<?php
				if ($imdbOptionsw['imdbtaxonomykeywords'] == "1") {
					echo "<br />";
					echo "<a href='" . esc_url( $imdbOptions['imdbplugindirectory'] . "inc/move_template_taxonomy.php?taxotype=keywords") . "' " .
						"title='" . esc_html__("Copy a standard taxonomy template to your template folder to display this taxonomy.", 'lumiere-movies') . "' >".
						"<img src='".esc_url( $imdbOptions['imdbplugindirectory'] . "pics/admin-widget-copy-theme.png") . "' alt='copy the taxonomy template' align='absmiddle' align='absmiddle' />".
						esc_html__("Copy template", 'lumiere-movies') .
					"</a>";
				}
				?> 
			</div>

			<div class="imdblt_double_container_content_third lumiere_padding_five">

				<input type="checkbox" id="imdb_imdbtaxonomylanguage" name="imdb_imdbtaxonomylanguage" value="<?php if ($imdbOptionsw['imdbtaxonomylanguage'] == "1") { echo '0'; } else { echo '1'; }?>" />
				<label for="imdb_imdbtaxonomylanguage">
					<?php if ($imdbOptionsw['imdbtaxonomylanguage'] == "1") { 

							if ($imdbOptionsw['imdbwidgetlanguage'] == 1){echo '<span class="lumiere-option-taxo-activated">'; } else { echo '<span class="lumiere-option-taxo-deactivated">'; }

							esc_html_e( 'Languages', 'lumiere-movies'); echo '</span>'; } else { ?><?php  esc_html_e( 'Languages', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?>
				</label> 
				<?php
				if ($imdbOptionsw['imdbtaxonomylanguage'] == "1") {
					echo "<br />";
					echo "<a href='" . esc_url( $imdbOptions['imdbplugindirectory'] . "inc/move_template_taxonomy.php?taxotype=language") . "' " .
						"title='" . esc_html__("Copy a standard taxonomy template to your template folder to display this taxonomy.", 'lumiere-movies') . "' >".
						"<img src='".esc_url( $imdbOptions['imdbplugindirectory'] . "pics/admin-widget-copy-theme.png") . "' alt='copy the taxonomy template' align='absmiddle' align='absmiddle' />".
						esc_html__("Copy template", 'lumiere-movies') .
					"</a>";
				}
				?> 

			</div>
		</div>

		<div class="imdblt_double_container">
			<div class="imdblt_double_container_content_third lumiere_padding_five">

				<input type="checkbox" id="imdb_imdbtaxonomyproducer" name="imdb_imdbtaxonomyproducer" value="<?php if ($imdbOptionsw['imdbtaxonomyproducer'] == "1") { echo '0'; } else { echo '1'; }?>" />
				<label for="imdb_imdbtaxonomyproducer">
					<?php if ($imdbOptionsw['imdbtaxonomyproducer'] == "1") { 

							if ($imdbOptionsw['imdbwidgetproducer'] == 1){echo '<span class="lumiere-option-taxo-activated">'; } else { echo '<span class="lumiere-option-taxo-deactivated">'; }

							esc_html_e( 'Producers', 'lumiere-movies'); echo '</span>'; } else { ?><?php  esc_html_e( 'Producers', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?>
				</label> 
				<?php
				if ($imdbOptionsw['imdbtaxonomyproducer'] == "1") {
					echo "<br />";
					echo "<a href='" . esc_url( $imdbOptions['imdbplugindirectory'] . "inc/move_template_taxonomy.php?taxotype=producer") . "' " .
						"title='" . esc_html__("Copy a standard taxonomy template to your template folder to display this taxonomy.", 'lumiere-movies') . "' >".
						"<img src='".esc_url( $imdbOptions['imdbplugindirectory'] . "pics/admin-widget-copy-theme.png") . "' alt='copy the taxonomy template' align='absmiddle' align='absmiddle' />".
						esc_html__("Copy template", 'lumiere-movies') .
					"</a>";
				}
				?> 
			</div>

			<div class="imdblt_double_container_content_third lumiere_padding_five">

				<input type="checkbox" id="imdb_imdbtaxonomywriter" name="imdb_imdbtaxonomywriter" value="<?php if ($imdbOptionsw['imdbtaxonomywriter'] == "1") { echo '0'; } else { echo '1'; }?>" />
				<label for="imdb_imdbtaxonomywriter">
					<?php if ($imdbOptionsw['imdbtaxonomywriter'] == "1") { 

							if ($imdbOptionsw['imdbwidgetwriter'] == 1){echo '<span class="lumiere-option-taxo-activated">'; } else { echo '<span class="lumiere-option-taxo-deactivated">'; }

							esc_html_e( 'Writers', 'lumiere-movies'); echo '</span>'; } else { ?><?php  esc_html_e( 'Writers', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?>
				</label> 
				<?php
				if ($imdbOptionsw['imdbtaxonomywriter'] == "1") {
					echo "<br />";
					echo "<a href='" . esc_url( $imdbOptions['imdbplugindirectory'] . "inc/move_template_taxonomy.php?taxotype=writer") . "' " .
						"title='" . esc_html__("Copy a standard taxonomy template to your template folder to display this taxonomy.", 'lumiere-movies') . "' >".
						"<img src='".esc_url( $imdbOptions['imdbplugindirectory'] . "pics/admin-widget-copy-theme.png") . "' alt='copy the taxonomy template' align='absmiddle' align='absmiddle' />".
						esc_html__("Copy template", 'lumiere-movies') .
					"</a>";
				}
				?> 

			</div>

			<div class="imdblt_double_container_content_third lumiere_padding_five">
			</div>
			
		</div>
	</div>

<?php 
	} //end check taxonomy option
} 


		if ( (isset($_GET['widgetoption'])) && ($_GET['widgetoption'] == "order") ) { 	// Order ?>
		<?php //-------------------------------------------------------------------=[Order]=- ?>		

	<div class="postbox">
		<h3 class="hndle" id="taxoorder" name="taxoorder"><?php esc_html_e( 'Position of data', 'lumiere-movies'); ?></h3>
	</div>

		<div class="imblt_border_shadow imdblt_align_webkit_center">


		<div class="lumiere_intro_options_small">
			<?php esc_html_e( 'You can select the order for the information selected from "what to display" section. Select first the movie detail you want to move, use "up" or "down" to reorder Lumiere Movies display. Once you are happy with the new layout, click on "update settings" to keep it.', 'lumiere-movies'); ?>
			<br /><br />
			<?php esc_html_e( '"Source" movie detail cannot be selected; if it is selected from "what to display" section, it will always appear after others movie details', 'lumiere-movies'); ?>
		</div>

		<div id="container_imdbwidgetorderContainer" class="imdblt_double_container imdblt_padding_top_twenty lumiere_align_center lumiere_writing_vertical">

			<div class="imdblt_padding_ten imdblt_align_last_center imdblt_flex_auto">

				<input type="button" value="up" name="movemovieup" id="movemovieup" data-moveform="-1" /> 
				
				<input type="button" value="down" name="movemoviedown" id="movemoviedown" data-moveform="+1" />

				<div><?php esc_html_e( 'Move selected movie detail:', 'lumiere-movies') ?></div>

				<? 
				// add "empty", to eliminate false submissions which could crush database values ?>	
				<input type="hidden" name="imdb_imdbwidgetorder" id="imdb_imdbwidgetorder" value="" class="imdblt_hidden" />
			</div>

			<div class="imdblt_padding_ten imdblt_align_last_center imdblt_flex_auto">

			<select id="imdbwidgetorderContainer" name="imdbwidgetorderContainer[]" class="imdbwidgetorderContainer" size="<?php echo (count( $imdbOptionsw['imdbwidgetorder'] )/2); ?>" style="height:100%;" multiple>
<?php 
				foreach ($imdbOptionsw['imdbwidgetorder'] as $key=>$value) {

					if (!empty ( $key ) ) { // to eliminate empty keys

						echo "\t\t\t\t\t<option value='".$key."'";

						// search if "imdbwidget'title'" (ie) is activated
						if ($imdbOptionsw["imdbwidget$key"] != 1 ) { 

							echo ' label="'.$key.' (unactivated)">'.$key;
						} else { 
							echo ' label="'.$key.'">'.$key; 
						}
							echo "</option>\n"; 
					}
			      	}
			?>				</select>
			</div>

		</div>
	</div>

<?php	} 
		if ( (isset($_GET['widgetoption'])) && ($_GET['widgetoption'] == "misc") ) { 	// Misc ?>
		<?php //-------------------------------------------------------------------=[Misc]=- ?>		

		<div class="imblt_border_shadow">
			<div class="titresection"><?php esc_html_e( 'Misc', 'lumiere-movies'); ?></div>

			<div class="lumiere_flex_container">
				<div class="lumiere_flex_container_content_third lumiere_padding_five">

					<?php esc_html_e( 'Remove all links?', 'lumiere-movies'); ?><br /><br />

					<input type="radio" id="imdb_imdblinkingkill_yes" name="imdb_imdblinkingkill" value="1" <?php if ($imdbOptionsw['imdblinkingkill'] == "1") { echo 'checked="checked"'; }?> />

					<label for="imdb_imdblinkingkill_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label><input type="radio" id="imdb_imdblinkingkill_no" name="imdb_imdblinkingkill" value="" <?php if ($imdbOptionsw['imdblinkingkill'] == 0) { echo 'checked="checked"'; } ?>/><label for="imdb_imdblinkingkill_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

					<div class="explain"><?php esc_html_e( "Remove all links (popup and external ones) which are automatically added. Usefull for users who are not interested in popup function. Please note it will remove every single HTML link too, such as the the links to official movie websites.", 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

				</div>

				<div class="lumiere_flex_container_content_third lumiere_padding_five">

					<?php esc_html_e( 'Auto widget?', 'lumiere-movies'); ?><br /><br />

					<input type="radio" id="imdb_imdbautopostwidget_yes" name="imdb_imdbautopostwidget" value="1" <?php if ($imdbOptionsw['imdbautopostwidget'] == "1") { echo 'checked="checked"'; }?> />

					<label for="imdb_imdbautopostwidget_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label><input type="radio" id="imdb_imdbautopostwidget_no" name="imdb_imdbautopostwidget" value="" <?php if ($imdbOptionsw['imdbautopostwidget'] == 0) { echo 'checked="checked"'; } ?>/><label for="imdb_imdbautopostwidget_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

					<div class="explain"><?php esc_html_e( "Add automatically a widget according to your post titles. If 'imdb-movie-widget' or 'imdb-movie-widget-bymid' have also been added to post, the auto widget will be displayed before them. Usefull if your entire blog is about movies; if a query the does not bring any result when using your post title, a message will be displayed saying so.", 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

				</div>

				<div class="lumiere_flex_container_content_third lumiere_padding_five">

					<label for="imdb_imdbintotheposttheme"><?php esc_html_e( 'Theme color', 'lumiere-movies'); ?></label><br /><br />

					<select name="imdb_imdbintotheposttheme">
						<option <?php if( ($imdbOptionsw['imdbintotheposttheme'] == "grey") || (empty($imdbOptionsw['imdbintotheposttheme'])) ) echo 'selected="selected"'; ?>value="grey"><?php esc_html_e( 'grey (default)', 'lumiere-movies'); ?></option>
						<option <?php if($imdbOptionsw['imdbintotheposttheme'] == "white") echo 'selected="selected"'; ?>value="white"><?php esc_html_e( 'white', 'lumiere-movies'); ?></option>
						<option <?php if($imdbOptionsw['imdbintotheposttheme'] == "black") echo 'selected="selected"'; ?>value="black"><?php esc_html_e( 'black', 'lumiere-movies'); ?></option>
					</select>

					<div class="explain"> <?php esc_html_e( 'Inside the post/widget color theme', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?>"grey"</div>

				</div>

			</div>
		</div>

<?php	} // end of misc subsection ?>
	
		<?php //------------------------------------------------------------------ =[Submit selection]=- ?>
		<div class="submit submit-imdb lumiere_sticky_boxshadow lumiere_align_center">
			<?php wp_nonce_field('imdbwidgetSettings_check', 'imdbwidgetSettings_check'); //check that data has been sent only once ?>
			<input type="submit" class="button-primary" name="reset_imdbwidgetSettings" value="<?php esc_html_e( 'Reset settings', 'lumiere-movies') ?>" />
			<input type="submit" class="button-primary" id="update_imdbwidgetSettings" name="update_imdbwidgetSettings" value="<?php esc_html_e( 'Update settings', 'lumiere-movies') ?>" />
		</div>
	</form>
</div>

<?php	
} // end user can manage options 
?>
