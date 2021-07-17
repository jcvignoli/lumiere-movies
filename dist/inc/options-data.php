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
 #  Function : Data management configuration admin page                      #
 #									              #
 #############################################################################

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die(esc_html__("You are not allowed to call this page directly.", "lumiere-movies"));
}

// Enter in debug mode
if ((isset($imdb_admin_values['imdbdebug'])) && ($imdb_admin_values['imdbdebug'] == "1")){

	// Start the class Utils to activate debug -> already started in admin_pages
	$utils->lumiere_activate_debug($imdb_widget_values, '', '');
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

		echo lumiere_notice(1, esc_html__( $messages["taxotemplatecopy_success"], 'lumiere-movies') );

	// Message for failure
	} elseif ( sanitize_text_field( $_GET['msg'] ) == "taxotemplatecopy_failed" ) {

		echo lumiere_notice(3, esc_html__( $messages["taxotemplatecopy_failed"] , 'lumiere-movies') );

	} 
}

/* Authorised user to submit the form
 *
 */
if (current_user_can( 'manage_options' ) ) { 

	/* Update options selected
	 *
	 */
	if ( (isset($_POST['update_imdbwidgetSettings'])) && check_admin_referer('imdbwidgetSettings_check', 'imdbwidgetSettings_check') ) { 

		// Bug: It doesn't refresh as it should when removing/adding a taxonomy
		flush_rewrite_rules();

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

			// Copy $_POST to $imdb_widget_values var
			if (isset($_POST["$key"])) {
				$imdb_widget_values["$keynoimdb"] = sanitize_text_field($_POST["$key_sanitized"]);
			}
		}

		// Special part related to details order
		if (isset($_POST['imdbwidgetorderContainer']) ){
			// Sanitize
			$myinputs_sanitized = $utils->lumiere_recursive_sanitize_text_field($_POST['imdbwidgetorderContainer']);
			// increment the $key of one
			$data = array_combine(range(1, count($myinputs_sanitized)), array_values($myinputs_sanitized));

			// flip $key with $value
			$data = array_flip($data);

			// Put in the option
			$imdb_widget_values['imdbwidgetorder'] = $data;
		}

		// update options
		update_option($config->imdbWidgetOptionsName, $imdb_widget_values);

		// display confirmation message
		echo lumiere_notice(1, '<strong>'. esc_html__( 'Options saved.', 'lumiere-movies') .'</strong>');

		// Display a refresh link otherwise refreshed data is not seen
		if (!headers_sent()) {
			/* 2021 07 06 Shouldn't do anything here, to be removed
			header("Location: ".esc_url($_SERVER[ "REQUEST_URI"]), false);
			die(); */
		} else {
			echo lumiere_notice(1, '<a href="'.wp_get_referer() .'">'. esc_html__( 'Go back', 'lumiere-movies') .'</a>');
			die();
		}

	 }

	/* Reset options selected
	 *
	 */
	if ( (isset($_POST['reset_imdbwidgetSettings'])) && check_admin_referer('imdbwidgetSettings_check', 'imdbwidgetSettings_check') ) { 

		// Bug: It doesn't refresh as it should when removing/adding a taxonomy
		flush_rewrite_rules();

		// Delete the options to reset
		delete_option($config->imdbWidgetOptionsName);

		// Display a refresh link otherwise refreshed data is not seen
		if (!headers_sent()) {

			/* 2021 07 06 Shouldn't do anything here, to be removed
			header("Refresh: 0;url=".$_SERVER[ "REQUEST_URI"]."&reset=true", false);
			wp_safe_redirect( wp_get_referer() ); 
			exit();*/

		} else {
			echo lumiere_notice(1, '<strong>'. esc_html__( 'Options reset.', 'lumiere-movies') .'</strong>');
			echo lumiere_notice(1, '<a href="'.wp_get_referer() .'">'. esc_html__( 'Go back', 'lumiere-movies') .'</a>');
			exit();
		}

		// display confirmation message
		echo lumiere_notice(1, '<strong>'. esc_html__( 'Options reset.', 'lumiere-movies') .'</strong>');

	}

?>
<div id="tabswrap">
	<div class="imdblt_double_container lumiere_padding_five">

		<div class="lumiere_flex_auto lumiere_align_center"><img src="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] . "pics/admin-widget-inside-whattodisplay.png"); ?>" align="absmiddle" width="16px" />&nbsp;<a title="<?php esc_html_e( "What to display", 'lumiere-movies');?>" href="<?php echo esc_url ( admin_url() . "admin.php?page=imdblt_options&subsection=dataoption&widgetoption=what"); ?>"><?php esc_html_e( 'Display', 'lumiere-movies'); ?></a></div>

		<div class="lumiere_flex_auto lumiere_align_center">&nbsp;&nbsp;<img src="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] . "pics/admin-widget-inside-order.png"); ?>" align="absmiddle" width="16px" />&nbsp;<a title="<?php esc_html_e( "Display order", 'lumiere-movies');?>" href="<?php echo esc_url ( admin_url() . "admin.php?page=imdblt_options&subsection=dataoption&widgetoption=order"); ?>"><?php esc_html_e( "Display order", 'lumiere-movies'); ?></a></div>

			<?php if ($imdb_admin_values['imdbtaxonomy'] == "1") { ?>
		<div class="lumiere_flex_auto lumiere_align_center">&nbsp;&nbsp;<img src="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] . "pics/admin-widget-inside-whattotaxo.png"); ?>" align="absmiddle" width="16px" />&nbsp;<a title="<?php esc_html_e( "What to taxonomize", 'lumiere-movies');?>" href="<?php echo esc_url ( admin_url() . "admin.php?page=imdblt_options&subsection=dataoption&widgetoption=taxo"); ?>"><?php esc_html_e( "Taxonomy", 'lumiere-movies'); ?></a></div>
			<?php } else { ?>
		<div class="lumiere_flex_auto lumiere_align_center">&nbsp;&nbsp;<img src="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] ."pics/admin-widget-inside-whattodisplay.png"); ?>" align="absmiddle" width="16px" />&nbsp;<i><?php esc_html_e( "Taxonomy unactivated", 'lumiere-movies');?></i></div>
			<?php }?>

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

				<div class="imdblt_double_container lumiere_align_center">

					<div class="imdblt_double_container_content_third lumiere_padding_five  lumiere_align_center">

						<?php if ($imdb_widget_values['imdbwidgetactor'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Actor', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Actor', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetactor_yes" name="imdb_imdbwidgetactor" value="1" <?php if ($imdb_widget_values['imdbwidgetactor'] == "1") { echo 'checked="checked"'; }?> data-modificator="yes" data-field_to_change="imdb_imdbwidgetactornumber" data-field_to_change_value="0" /><label for="imdb_imdbwidgetactor_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgetactor_no" name="imdb_imdbwidgetactor" value="" <?php if ($imdb_widget_values['imdbwidgetactor'] == 0) { echo 'checked="checked"'; } ?> data-modificator="yes" data-field_to_change="imdb_imdbwidgetactornumber" data-field_to_change_value="1" /><label for="imdb_imdbwidgetactor_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<input type="text" id="imdb_imdbwidgetactornumber" name="imdb_imdbwidgetactornumber" size="3" value="<?php esc_html_e( apply_filters('format_to_edit',$imdb_widget_values['imdbwidgetactornumber']), 'lumiere-movies') ?>" <?php if ($imdb_widget_values['imdbwidgetactor'] == 0){ echo 'disabled="disabled"'; }; ?> />

						<div class="explain"><?php esc_html_e( 'Display (how many) actors. These options also applies to the pop-up summary.', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'Yes', 'lumiere-movies'); ?> & 10</div>
					</div>

					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdb_widget_values['imdbwidgetalsoknow'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Also known as', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Also known as', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetalsoknow_yes" name="imdb_imdbwidgetalsoknow" value="1" <?php if ($imdb_widget_values['imdbwidgetalsoknow'] == "1") { echo 'checked="checked"'; }?>  data-modificator="yes" data-field_to_change="imdb_imdbwidgetalsoknownumber" data-field_to_change_value="0" /><label for="imdb_imdbwidgetalsoknow_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgetalsoknow_no" name="imdb_imdbwidgetalsoknow" value="" <?php if ($imdb_widget_values['imdbwidgetalsoknow'] == 0) { echo 'checked="checked"'; } ?>  data-modificator="yes" data-field_to_change="imdb_imdbwidgetalsoknownumber" data-field_to_change_value="1" /><label for="imdb_imdbwidgetalsoknow_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<input type="text" id="imdb_imdbwidgetalsoknownumber" name="imdb_imdbwidgetalsoknownumber" size="3" value="<?php esc_html_e( apply_filters('format_to_edit',$imdb_widget_values['imdbwidgetalsoknownumber']), 'lumiere-movies') ?>" <?php if ($imdb_widget_values['imdbwidgetalsoknow'] == 0){ echo 'disabled="disabled"'; }; ?> />

						<div class="explain"><?php esc_html_e( "Display (how many) alternative movie names and in other languages", 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>

					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdb_widget_values['imdbwidgetcolors'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Colors', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Colors', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetcolors_yes" name="imdb_imdbwidgetcolors" value="1" <?php if ($imdb_widget_values['imdbwidgetcolors'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetcolors_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgetcolorsno" name="imdb_imdbwidgetcolors" value="" <?php if ($imdb_widget_values['imdbwidgetcolors'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetcolors_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<div class="explain"><?php esc_html_e( "Display colors", 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>



<?php //-------------------------------------------------------------------=[composer, country, creator]=- ?>		


					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdb_widget_values['imdbwidgetcomposer'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Composer', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Composer', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetcomposer_yes" name="imdb_imdbwidgetcomposer" value="1" <?php if ($imdb_widget_values['imdbwidgetcomposer'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetcomposer_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgetcomposer_no" name="imdb_imdbwidgetcomposer" value="" <?php if ($imdb_widget_values['imdbwidgetcomposer'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetcomposer_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<div class="explain"><?php esc_html_e( 'Display composer', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>


					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdb_widget_values['imdbwidgetcountry'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Country', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Country', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetcountry_yes" name="imdb_imdbwidgetcountry" value="1" <?php if ($imdb_widget_values['imdbwidgetcountry'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetcountry_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgetcountry_no" name="imdb_imdbwidgetcountry" value="" <?php if ($imdb_widget_values['imdbwidgetcountry'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetcountry_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<div class="explain"><?php esc_html_e( 'Display country. This option also applies to the pop-up summary.', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>

					<div class="imdblt_double_container_content_third lumiere_padding_five">
						<?php if ($imdb_widget_values['imdbwidgetcreator'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Creator', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Creator', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetcreator_yes" name="imdb_imdbwidgetcreator" value="1" <?php if ($imdb_widget_values['imdbwidgetcreator'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetcreator_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>
						<input type="radio" id="imdb_imdbwidgetcreator_no" name="imdb_imdbwidgetcreator" value="" <?php if ($imdb_widget_values['imdbwidgetcreator'] == 0) { echo 'checked="checked"'; } ?>  /><label for="imdb_imdbwidgetcreator_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>	

						<div class="explain"><?php esc_html_e( 'Display Creator', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>


<?php //-------------------------------------------------------------------=[director, genre, goofs]=- ?>	

					<div class="imdblt_double_container_content_third lumiere_padding_five">
						<?php if ($imdb_widget_values['imdbwidgetdirector'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Director', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Director', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetdirector_yes" name="imdb_imdbwidgetdirector" value="1" <?php if ($imdb_widget_values['imdbwidgetdirector'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetdirector_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>
						<input type="radio" id="imdb_imdbwidgetdirector_no" name="imdb_imdbwidgetdirector" value="" <?php if ($imdb_widget_values['imdbwidgetdirector'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetdirector_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

					<div class="explain"><?php esc_html_e( 'Display directors. This option also applies to the pop-up summary.', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'Yes', 'lumiere-movies'); ?></div>

					</div>

					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdb_widget_values['imdbwidgetgenre'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Genre', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Genre', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetgenre_yes" name="imdb_imdbwidgetgenre" value="1" <?php if ($imdb_widget_values['imdbwidgetgenre'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetgenre_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgetgenre_no" name="imdb_imdbwidgetgenre" value="" <?php if ($imdb_widget_values['imdbwidgetgenre'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetgenre_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<div class="explain"><?php esc_html_e( 'Display genre. This option also applies to the pop-up summary.', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'Yes', 'lumiere-movies'); ?></div>

					</div>

					<div class="imdblt_double_container_content_third lumiere_padding_five">
						<?php if ($imdb_widget_values['imdbwidgetgoofs'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Goofs', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Goofs', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetgoofs_yes" name="imdb_imdbwidgetgoofs" value="1" <?php if ($imdb_widget_values['imdbwidgetgoofs'] == "1") { echo 'checked="checked"'; }?> data-modificator="yes" data-field_to_change="imdb_imdbwidgetgoofsnumber" data-field_to_change_value="0" /><label for="imdb_imdbwidgetgoofs_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgetgoofs_no" name="imdb_imdbwidgetgoofs" value="" <?php if ($imdb_widget_values['imdbwidgetgoofs'] == 0) { echo 'checked="checked"'; } ?> data-modificator="yes" data-field_to_change="imdb_imdbwidgetgoofsnumber" data-field_to_change_value="1" /><label for="imdb_imdbwidgetgoofs_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<input type="text" id="imdb_imdbwidgetgoofsnumber" name="imdb_imdbwidgetgoofsnumber" size="3" value="<?php esc_html_e( apply_filters('format_to_edit',$imdb_widget_values['imdbwidgetgoofsnumber']), 'lumiere-movies') ?>" <?php if ($imdb_widget_values['imdbwidgetgoofs'] == 0){ echo 'disabled="disabled"'; }; ?> />

						<div class="explain"><?php esc_html_e( 'Display (how many) goof', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>



<?php //-------------------------------------------------------------------=[keywords, language, official site]=- ?>


					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdb_widget_values['imdbwidgetkeywords'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Keywords', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Keywords', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetkeywords_yes" name="imdb_imdbwidgetkeywords" value="1" <?php if ($imdb_widget_values['imdbwidgetkeywords'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetkeywords_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgetkeywords_no" name="imdb_imdbwidgetkeywords" value="" <?php if ($imdb_widget_values['imdbwidgetkeywords'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetkeywords_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<div class="explain"><?php esc_html_e( 'Display keywords', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>

					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdb_widget_values['imdbwidgetlanguage'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Language', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Language', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetlanguage_yes" name="imdb_imdbwidgetlanguage" value="1" <?php if ($imdb_widget_values['imdbwidgetlanguage'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetlanguage_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgetlanguage_no" name="imdb_imdbwidgetlanguage" value="" <?php if ($imdb_widget_values['imdbwidgetlanguage'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetlanguage_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<div class="explain"><?php esc_html_e( 'Display languages. This option also applies to the pop-up summary.', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>


					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdb_widget_values['imdbwidgetofficialsites'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Official websites', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Official websites', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetofficialsites_yes" name="imdb_imdbwidgetofficialsites" value="1" <?php if ($imdb_widget_values['imdbwidgetofficialsites'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetofficialsites_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgetofficialsites_no" name="imdb_imdbwidgetofficialsites" value="" <?php if ($imdb_widget_values['imdbwidgetofficialsites'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetofficialsites_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<div class="explain"><?php esc_html_e( 'Display official websites', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>


<?php //-------------------------------------------------------------------=[picture, plot, producer]=- ?>

					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdb_widget_values['imdbwidgetpic'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Picture', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Picture', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetpic_yes" name="imdb_imdbwidgetpic" value="1" <?php if ($imdb_widget_values['imdbwidgetpic'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetpic_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>
						<input type="radio" id="imdb_imdbwidgetpic_no" name="imdb_imdbwidgetpic" value="" <?php if ($imdb_widget_values['imdbwidgetpic'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetpic_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<div class="explain"><?php esc_html_e( 'Display the picture', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'Yes', 'lumiere-movies'); ?></div>

					</div>

					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdb_widget_values['imdbwidgetplot'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Plot', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Plot', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetplot_yes" name="imdb_imdbwidgetplot" value="1" <?php if ($imdb_widget_values['imdbwidgetplot'] == "1") { echo 'checked="checked"'; }?> data-modificator="yes" data-field_to_change="imdb_imdbwidgetplotnumber" data-field_to_change_value="0" /><label for="imdb_imdbwidgetplot_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgetplot_no" name="imdb_imdbwidgetplot" value="" <?php if ($imdb_widget_values['imdbwidgetplot'] == 0) { echo 'checked="checked"'; } ?> data-modificator="yes" data-field_to_change="imdb_imdbwidgetplotnumber" data-field_to_change_value="1" /><label for="imdb_imdbwidgetplot_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<input type="text" id="imdb_imdbwidgetplotnumber" name="imdb_imdbwidgetplotnumber" size="3" value="<?php esc_html_e( apply_filters('format_to_edit',$imdb_widget_values['imdbwidgetplotnumber']), 'lumiere-movies') ?>" <?php if ($imdb_widget_values['imdbwidgetplot'] == 0){ echo 'disabled="disabled"'; }; ?> />

						<div class="explain"><?php esc_html_e( 'Display plots. Be careful, this field may need a lot of space. In ideal case, this plugin is used inside a post and not into a widget.', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>

					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdb_widget_values['imdbwidgetproducer'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Producer', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Producer', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetproducer_yes" name="imdb_imdbwidgetproducer" value="1" <?php if ($imdb_widget_values['imdbwidgetproducer'] == "1") { echo 'checked="checked"'; }?> data-modificator="yes" data-field_to_change="imdb_imdbwidgetproducernumber" data-field_to_change_value="0" /><label for="imdb_imdbwidgetproducer_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>
						<input type="radio" id="imdb_imdbwidgetproducer_no" name="imdb_imdbwidgetproducer" value="" <?php if ($imdb_widget_values['imdbwidgetproducer'] == 0) { echo 'checked="checked"'; } ?>  data-modificator="yes" data-field_to_change="imdb_imdbwidgetproducernumber" data-field_to_change_value="1" /><label for="imdb_imdbwidgetproducer_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<input type="text" id="imdb_imdbwidgetproducernumber" name="imdb_imdbwidgetproducernumber" size="3" value="<?php esc_html_e( apply_filters('format_to_edit',$imdb_widget_values['imdbwidgetproducernumber']), 'lumiere-movies') ?>" <?php if ($imdb_widget_values['imdbwidgetproducer'] == 0){ echo 'disabled="disabled"'; }; ?> />

						<div class="explain"><?php esc_html_e( 'Display (how many) producers', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>


<?php //-------------------------------------------------------------------=[produ company, quotes, rating]=- ?>

					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdb_widget_values['imdbwidgetprodcompany'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Production company', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Production company', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetprodcompany_yes" name="imdb_imdbwidgetprodcompany" value="1" <?php if ($imdb_widget_values['imdbwidgetprodcompany'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetprodcompany_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgetprodcompany_no" name="imdb_imdbwidgetprodcompany" value="" <?php if ($imdb_widget_values['imdbwidgetprodcompany'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetprodcompany_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<div class="explain"><?php esc_html_e( 'Display the production companies', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>

					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdb_widget_values['imdbwidgetquotes'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Quotes', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Quotes', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetquotes_yes" name="imdb_imdbwidgetquotes" value="1" <?php if ($imdb_widget_values['imdbwidgetquotes'] == "1") { echo 'checked="checked"'; }?> data-modificator="yes" data-field_to_change="imdb_imdbwidgetquotesnumber" data-field_to_change_value="0" /><label for="imdb_imdbwidgetquotes_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgetquotes_no" name="imdb_imdbwidgetquotes" value="" <?php if ($imdb_widget_values['imdbwidgetquotes'] == 0) { echo 'checked="checked"'; } ?> data-modificator="yes" data-field_to_change="imdb_imdbwidgetquotesnumber" data-field_to_change_value="1" /><label for="imdb_imdbwidgetquotes_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<input type="text" id="imdb_imdbwidgetquotesnumber" name="imdb_imdbwidgetquotesnumber" size="3" value="<?php esc_html_e( apply_filters('format_to_edit',$imdb_widget_values['imdbwidgetquotesnumber']), 'lumiere-movies') ?>" <?php if ($imdb_widget_values['imdbwidgetquotes'] == 0){ echo 'disabled="disabled"'; }; ?> />

						<div class="explain"><?php esc_html_e( "Display (how many) quotes from movie", 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>

					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdb_widget_values['imdbwidgetrating'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Rating', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Rating', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetrating_yes" name="imdb_imdbwidgetrating" value="1" <?php if ($imdb_widget_values['imdbwidgetrating'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetrating_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgetrating_no" name="imdb_imdbwidgetrating" value="" <?php if ($imdb_widget_values['imdbwidgetrating'] == 0) { echo 'checked="checked"'; } ?>  /><label for="imdb_imdbwidgetrating_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<div class="explain"><?php esc_html_e( 'Display rating. This option also applies to the pop-up summary.', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>



<?php //-------------------------------------------------------------------=[runtime, soundtrack, source]=- ?>

					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdb_widget_values['imdbwidgetruntime'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Runtime', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Runtime', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetruntime_yes" name="imdb_imdbwidgetruntime" value="1" <?php if ($imdb_widget_values['imdbwidgetruntime'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetruntime_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>
						<input type="radio" id="imdb_imdbwidgetruntime_no" name="imdb_imdbwidgetruntime" value="" <?php if ($imdb_widget_values['imdbwidgetruntime'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetruntime_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>
						<div class="explain"><?php esc_html_e( 'Display the runtime. This option also applies to the pop-up summary.', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>

					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdb_widget_values['imdbwidgetsoundtrack'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Soundtrack', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Soundtrack', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetsoundtrack_yes" name="imdb_imdbwidgetsoundtrack" value="1" <?php if ($imdb_widget_values['imdbwidgetsoundtrack'] == "1") { echo 'checked="checked"'; }?> data-modificator="yes" data-field_to_change="imdb_imdbwidgetsoundtracknumber" data-field_to_change_value="0" /><label for="imdb_imdbwidgetsoundtrack_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgetsoundtrack" name="imdb_imdbwidgetsoundtrack" value="" <?php if ($imdb_widget_values['imdbwidgetsoundtrack'] == 0) { echo 'checked="checked"'; } ?> data-modificator="yes" data-field_to_change="imdb_imdbwidgetsoundtracknumber" data-field_to_change_value="1" /><label for="imdb_imdbwidgetsoundtrack_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<input type="text" id="imdb_imdbwidgetsoundtracknumber" name="imdb_imdbwidgetsoundtracknumber" size="3" value="<?php esc_html_e( apply_filters('format_to_edit',$imdb_widget_values['imdbwidgetsoundtracknumber']), 'lumiere-movies') ?>" <?php if ($imdb_widget_values['imdbwidgetsoundtrack'] == 0){ echo 'disabled="disabled"'; }; ?> />

						<div class="explain"><?php esc_html_e( "Display (how many) soundtrack", 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>

					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdb_widget_values['imdbwidgetsource'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Source', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Source', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />

						<input type="radio" id="imdb_imdbwidgetsource_yes" name="imdb_imdbwidgetsource" value="1" <?php if ($imdb_widget_values['imdbwidgetsource'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetsource_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgetsource_no" name="imdb_imdbwidgetsource" value="" <?php if ($imdb_widget_values['imdbwidgetsource'] == 0) { echo 'checked="checked"'; } ?>  /><label for="imdb_imdbwidgetsource_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<div class="explain"><?php esc_html_e( 'Display website source at the end of the post', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'Yes', 'lumiere-movies'); ?></div>

					</div>


<?php //-------------------------------------------------------------------=[tagline, title, trailer]=- ?>

					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdb_widget_values['imdbwidgettaglines'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Tagline', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Tagline', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgettaglines_yes" name="imdb_imdbwidgettaglines" value="1" <?php if ($imdb_widget_values['imdbwidgettaglines'] == "1") { echo 'checked="checked"'; }?> data-modificator="yes" data-field_to_change="imdb_imdbwidgettaglinesnumber" data-field_to_change_value="0" /><label for="imdb_imdbwidgettaglines_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgettaglines_no" name="imdb_imdbwidgettaglines" value="" <?php if ($imdb_widget_values['imdbwidgettaglines'] == 0) { echo 'checked="checked"'; } ?> data-modificator="yes" data-field_to_change="imdb_imdbwidgettaglinesnumber" data-field_to_change_value="1" /><label for="imdb_imdbwidgettaglines_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<input type="text" id="imdb_imdbwidgettaglinesnumber" name="imdb_imdbwidgettaglinesnumber" size="3" value="<?php esc_html_e( apply_filters('format_to_edit',$imdb_widget_values['imdbwidgettaglinesnumber']), 'lumiere-movies') ?>" <?php if ($imdb_widget_values['imdbwidgettaglines'] == 0){ echo 'disabled="disabled"'; }; ?> />

						<div class="explain"><?php esc_html_e( 'Display (how many) tagline', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>


					<div class="imdblt_double_container_content_third lumiere_padding_five">
						<?php if ($imdb_widget_values['imdbwidgettitle'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Title', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Title', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						<input type="radio" id="imdb_imdbwidgettitle_yes" name="imdb_imdbwidgettitle" value="1" <?php if ($imdb_widget_values['imdbwidgettitle'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgettitle_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>
						<input type="radio" id="imdb_imdbwidgettitle_no" name="imdb_imdbwidgettitle" value="" <?php if ($imdb_widget_values['imdbwidgettitle'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgettitle_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<div class="explain"><?php esc_html_e( 'Display the title', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'Yes', 'lumiere-movies'); ?></div>

					</div>


					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdb_widget_values['imdbwidgettrailer'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Trailers', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Trailers', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgettrailer_yes" name="imdb_imdbwidgettrailer" value="1" <?php if ($imdb_widget_values['imdbwidgettrailer'] == "1") { echo 'checked="checked"'; }?> data-modificator="yes" data-field_to_change="imdb_imdbwidgettrailernumber" data-field_to_change_value="0" /><label for="imdb_imdbwidgettrailer_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgettrailer_no" name="imdb_imdbwidgettrailer" value="" <?php if ($imdb_widget_values['imdbwidgettrailer'] == 0) { echo 'checked="checked"'; } ?> data-modificator="yes" data-field_to_change="imdb_imdbwidgettrailernumber" data-field_to_change_value="1" /><label for="imdb_imdbwidgettrailer_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<input type="text" id="imdb_imdbwidgettrailernumber" name="imdb_imdbwidgettrailernumber" size="3" value="<?php esc_html_e( apply_filters('format_to_edit',$imdb_widget_values['imdbwidgettrailernumber']), 'lumiere-movies') ?>" <?php if ($imdb_widget_values['imdbwidgettrailernumber'] == 0){ echo 'disabled="disabled"'; }; ?> />

						<div class="explain"><?php esc_html_e( 'Display (how many) trailers', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>


<?php //-------------------------------------------------------------------=[user comment, writer, year]=- ?>

					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdb_widget_values['imdbwidgetcomments'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Users comment', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Users comment', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetcomments_yes" name="imdb_imdbwidgetcomments" value="1" <?php if ($imdb_widget_values['imdbwidgetcomments'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetcomments_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgetcomments_no" name="imdb_imdbwidgetcomments" value="" <?php if ($imdb_widget_values['imdbwidgetcomments'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetcomments_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<div class="explain"><?php esc_html_e( "Display the main user comment", 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>

					<div class="imdblt_double_container_content_third lumiere_padding_five">

						<?php if ($imdb_widget_values['imdbwidgetwriter'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Writer', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Writer', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetwriter_yes" name="imdb_imdbwidgetwriter" value="1" <?php if ($imdb_widget_values['imdbwidgetwriter'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetwriter_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

						<input type="radio" id="imdb_imdbwidgetwriter_no" name="imdb_imdbwidgetwriter" value="" <?php if ($imdb_widget_values['imdbwidgetwriter'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetwriter_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

						<div class="explain"><?php esc_html_e( 'Display writers', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'Yes', 'lumiere-movies'); ?></div>

					</div>

					<div class="imdblt_double_container_content_third lumiere_padding_five">
						<?php if ($imdb_widget_values['imdbwidgetyear'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Year', 'lumiere-movies'); echo '</span>'; } else { ?>
						<?php  esc_html_e( 'Year', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?><br /><br />
						
						<input type="radio" id="imdb_imdbwidgetyear_yes" name="imdb_imdbwidgetyear" value="1" <?php if ($imdb_widget_values['imdbwidgetyear'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetyear_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>
						<input type="radio" id="imdb_imdbwidgetyear_no" name="imdb_imdbwidgetyear" value="" <?php if ($imdb_widget_values['imdbwidgetyear'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetyear_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>
						<div class="explain"><?php esc_html_e( "Display release year. The release year will appear next to the movie's title into brackets.", 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

					</div>

				</div><!-- end double container -->
			</div>
		</div>
<?php	} 

//-------------------------------------------------------------------=[Taxonomy]=-

		if ( (isset($_GET['widgetoption'])) && ($_GET['widgetoption'] == "taxo") ) { 	// Taxonomy 

			if ($imdb_admin_values['imdbtaxonomy'] != "1") { //check if taxonomy is activated

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

				<input type="checkbox" id="imdb_imdbtaxonomyactor" name="imdb_imdbtaxonomyactor" value="<?php if ($imdb_widget_values['imdbtaxonomyactor'] == "1") { echo '0'; } else { echo '1'; }?>" />
				<label for="imdb_imdbtaxonomyactor">
					<?php if ($imdb_widget_values['imdbtaxonomyactor'] == "1") { 
							if ($imdb_widget_values['imdbwidgetactor'] == 1){echo '<span class="lumiere-option-taxo-activated">'; } else {	echo '<span class="lumiere-option-taxo-deactivated">'; }
							esc_html_e( 'Actors', 'lumiere-movies'); echo '</span>'; } else { ?><?php  esc_html_e( 'Actors', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?>
				</label> 
				<?php
				if ($imdb_widget_values['imdbtaxonomyactor'] == "1") {
					echo "<br />";
					echo "<a href='" . esc_url( admin_url() . "admin.php?page=imdblt_options&?page=imdblt_options&subsection=dataoption&widgetoption=taxo&taxotype=actor" ) . "' " .
						"title='" . esc_html__("Copy a standard taxonomy template to your template folder to display this taxonomy.", 'lumiere-movies') . "' >".
						"<img src='".esc_url( $imdb_admin_values['imdbplugindirectory'] . "pics/admin-widget-copy-theme.png") . "' alt='copy the taxonomy template' align='absmiddle' align='absmiddle' />".
						esc_html__("Copy template", 'lumiere-movies') .
					"</a>";
				}
				?> 

			</div>
			<div class="imdblt_double_container_content_third lumiere_padding_five">

				<input type="checkbox" id="imdb_imdbtaxonomycolor" name="imdb_imdbtaxonomycolor" value="<?php if ($imdb_widget_values['imdbtaxonomycolor'] == "1") { echo '0'; } else { echo '1'; }?>" />

				<label for="imdb_imdbtaxonomycolor">
					<?php if ($imdb_widget_values['imdbtaxonomycolor'] == "1") { 

							if ($imdb_widget_values['imdbwidgetcolors'] == 1){echo '<span class="lumiere-option-taxo-activated">'; } else {	echo '<span class="lumiere-option-taxo-deactivated">'; }

							esc_html_e( 'Colors', 'lumiere-movies'); echo '</span>'; } else { ?><?php  esc_html_e( 'Colors', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?>
				</label> 

				<?php
				if ($imdb_widget_values['imdbtaxonomycolor'] == "1") {
					echo "<br />";
					echo "<a href='" . esc_url( admin_url() . "admin.php?page=imdblt_options&?page=imdblt_options&subsection=dataoption&widgetoption=taxo&taxotype=color" ) . "' " .
						"title='" . esc_html__("Copy a standard taxonomy template to your template folder to display this taxonomy.", 'lumiere-movies') . "' >".
						"<img src='".esc_url( $imdb_admin_values['imdbplugindirectory'] . "pics/admin-widget-copy-theme.png") . "' alt='copy the taxonomy template' align='absmiddle' align='absmiddle' />".
						esc_html__("Copy template", 'lumiere-movies') .
					"</a>";
				}
				?> 

			</div>
			<div class="imdblt_double_container_content_third lumiere_padding_five">

				<input type="checkbox" id="imdb_imdbtaxonomycomposer" name="imdb_imdbtaxonomycomposer" value="<?php if ($imdb_widget_values['imdbtaxonomycomposer'] == "1") { echo '0'; } else { echo '1'; }?>" />
				<label for="imdb_imdbtaxonomycomposer">
					<?php if ($imdb_widget_values['imdbtaxonomycomposer'] == "1") { 

							if ($imdb_widget_values['imdbwidgetcomposer'] == 1){echo '<span class="lumiere-option-taxo-activated">'; } else { echo '<span class="lumiere-option-taxo-deactivated">'; }

							esc_html_e( 'Composers', 'lumiere-movies'); echo '</span>'; } else { ?><?php  esc_html_e( 'Composers', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?>
				</label> 
				<?php
				if ($imdb_widget_values['imdbtaxonomycomposer'] == "1") {
					echo "<br />";
					echo "<a href='" . esc_url( admin_url() . "admin.php?page=imdblt_options&?page=imdblt_options&subsection=dataoption&widgetoption=taxo&taxotype=composer" ) . "' " .
						"title='" . esc_html__("Copy a standard taxonomy template to your template folder to display this taxonomy.", 'lumiere-movies') . "' >".
						"<img src='".esc_url( $imdb_admin_values['imdbplugindirectory'] . "pics/admin-widget-copy-theme.png") . "' alt='copy the taxonomy template' align='absmiddle' align='absmiddle' />".
						esc_html__("Copy template", 'lumiere-movies') .
					"</a>";
				}
				?> 
			</div>

			<div class="imdblt_double_container_content_third lumiere_padding_five">

				<input type="checkbox" id="imdb_imdbtaxonomycountry" name="imdb_imdbtaxonomycountry" value="<?php if ($imdb_widget_values['imdbtaxonomycountry'] == "1") { echo '0'; } else { echo '1'; }?>" />
				<label for="imdb_imdbtaxonomycountry">
					<?php if ($imdb_widget_values['imdbtaxonomycountry'] == "1") { 

							if ($imdb_widget_values['imdbwidgetcountry'] == 1){echo '<span class="lumiere-option-taxo-activated">'; } else { echo '<span class="lumiere-option-taxo-deactivated">'; }

							esc_html_e( 'Countries', 'lumiere-movies'); echo '</span>'; } else { ?><?php  esc_html_e( 'Countries', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?>
				</label> 
				<?php
				if ($imdb_widget_values['imdbtaxonomycountry'] == "1") {
					echo "<br />";
					echo "<a href='" . esc_url( admin_url() . "admin.php?page=imdblt_options&?page=imdblt_options&subsection=dataoption&widgetoption=taxo&taxotype=country" ) . "' " .
						"title='" . esc_html__("Copy a standard taxonomy template to your template folder to display this taxonomy.", 'lumiere-movies') . "' >".
						"<img src='".esc_url( $imdb_admin_values['imdbplugindirectory'] . "pics/admin-widget-copy-theme.png") . "' alt='copy the taxonomy template' align='absmiddle' align='absmiddle' />".
						esc_html__("Copy template", 'lumiere-movies') .
					"</a>";
				}
				?> 

			</div>

			<div class="imdblt_double_container_content_third lumiere_padding_five">

				<input type="checkbox" id="imdb_imdbtaxonomycreator" name="imdb_imdbtaxonomycreator" value="<?php if ($imdb_widget_values['imdbtaxonomycreator'] == "1") { echo '0'; } else { echo '1'; }?>" />
				<label for="imdb_imdbtaxonomycreator">
					<?php if ($imdb_widget_values['imdbtaxonomycreator'] == "1") { 

							if ($imdb_widget_values['imdbwidgetcreator'] == 1){echo '<span class="lumiere-option-taxo-activated">'; } else { echo '<span class="lumiere-option-taxo-deactivated">'; }

							esc_html_e( 'Creators', 'lumiere-movies'); echo '</span>'; } else { ?><?php  esc_html_e( 'Creators', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?>
				</label> 
				<?php
				if ($imdb_widget_values['imdbtaxonomycreator'] == "1") {
					echo "<br />";
					echo "<a href='" . esc_url( admin_url() . "admin.php?page=imdblt_options&?page=imdblt_options&subsection=dataoption&widgetoption=taxo&taxotype=creator" ) . "' " .
						"title='" . esc_html__("Copy a standard taxonomy template to your template folder to display this taxonomy.", 'lumiere-movies') . "' >".
						"<img src='".esc_url( $imdb_admin_values['imdbplugindirectory'] . "pics/admin-widget-copy-theme.png") . "' alt='copy the taxonomy template' align='absmiddle' align='absmiddle' />".
						esc_html__("Copy template", 'lumiere-movies') .
					"</a>";
				}
				?> 

			</div>

			<div class="imdblt_double_container_content_third lumiere_padding_five">

				<input type="checkbox" id="imdb_imdbtaxonomydirector" name="imdb_imdbtaxonomydirector" value="<?php if ($imdb_widget_values['imdbtaxonomydirector'] == "1") { echo '0'; } else { echo '1'; }?>" />
				<label for="imdb_imdbtaxonomydirector">
					<?php if ($imdb_widget_values['imdbtaxonomydirector'] == "1") { 

							if ($imdb_widget_values['imdbwidgetdirector'] == 1){echo '<span class="lumiere-option-taxo-activated">'; } else { echo '<span class="lumiere-option-taxo-deactivated">'; }

							esc_html_e( 'Directors', 'lumiere-movies'); echo '</span>'; } else { ?><?php  esc_html_e( 'Directors', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?>
				</label> 
				<?php
				if ($imdb_widget_values['imdbtaxonomydirector'] == "1") {
					echo "<br />";
					echo "<a href='" . esc_url( admin_url() . "admin.php?page=imdblt_options&?page=imdblt_options&subsection=dataoption&widgetoption=taxo&taxotype=director" ) . "' " .
						"title='" . esc_html__("Copy a standard taxonomy template to your template folder to display this taxonomy.", 'lumiere-movies') . "' >".
						"<img src='".esc_url( $imdb_admin_values['imdbplugindirectory'] . "pics/admin-widget-copy-theme.png") . "' alt='copy the taxonomy template' align='absmiddle' align='absmiddle' />".
						esc_html__("Copy template", 'lumiere-movies') .
					"</a>";
				}
				?> 
			</div>

			<div class="imdblt_double_container_content_third lumiere_padding_five">

				<input type="checkbox" id="imdb_imdbtaxonomygenre" name="imdb_imdbtaxonomygenre" value="<?php if ($imdb_widget_values['imdbtaxonomygenre'] == "1") { echo '0'; } else { echo '1'; }?>" />
				<label for="imdb_imdbtaxonomygenre">
					<?php if ($imdb_widget_values['imdbtaxonomygenre'] == "1") { 

							if ($imdb_widget_values['imdbwidgetgenre'] == 1){echo '<span class="lumiere-option-taxo-activated">'; } else { echo '<span class="lumiere-option-taxo-deactivated">'; }

							esc_html_e( 'Genres', 'lumiere-movies'); echo '</span>'; } else { ?><?php  esc_html_e( 'Genres', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?>
				</label> 
				<?php
				if ($imdb_widget_values['imdbtaxonomygenre'] == "1") {
					echo "<br />";
					echo "<a href='" . esc_url( admin_url() . "admin.php?page=imdblt_options&?page=imdblt_options&subsection=dataoption&widgetoption=taxo&taxotype=genre" ) . "' " .
						"title='" . esc_html__("Copy a standard taxonomy template to your template folder to display this taxonomy.", 'lumiere-movies') . "' >".
						"<img src='".esc_url( $imdb_admin_values['imdbplugindirectory'] . "pics/admin-widget-copy-theme.png") . "' alt='copy the taxonomy template' align='absmiddle' align='absmiddle' />".
						esc_html__("Copy template", 'lumiere-movies') .
					"</a>";
				}
				?> 

			</div>
			<div class="imdblt_double_container_content_third lumiere_padding_five">

				<input type="checkbox" id="imdb_imdbtaxonomykeywords" name="imdb_imdbtaxonomykeywords" value="<?php if ($imdb_widget_values['imdbtaxonomykeywords'] == "1") { echo '0'; } else { echo '1'; }?>" />
				<label for="imdb_imdbtaxonomykeywords">
					<?php if ($imdb_widget_values['imdbtaxonomykeywords'] == "1") { 

							if ($imdb_widget_values['imdbwidgetkeywords'] == 1){echo '<span class="lumiere-option-taxo-activated">'; } else { echo '<span class="lumiere-option-taxo-deactivated">'; }

							esc_html_e( 'Keywords', 'lumiere-movies'); echo '</span>'; } else { ?><?php  esc_html_e( 'Keywords', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?>
				</label> 				
				<?php
				if ($imdb_widget_values['imdbtaxonomykeywords'] == "1") {
					echo "<br />";
					echo "<a href='" . esc_url( admin_url() . "admin.php?page=imdblt_options&?page=imdblt_options&subsection=dataoption&widgetoption=taxo&taxotype=keywords" ) . "' " .
						"title='" . esc_html__("Copy a standard taxonomy template to your template folder to display this taxonomy.", 'lumiere-movies') . "' >".
						"<img src='".esc_url( $imdb_admin_values['imdbplugindirectory'] . "pics/admin-widget-copy-theme.png") . "' alt='copy the taxonomy template' align='absmiddle' align='absmiddle' />".
						esc_html__("Copy template", 'lumiere-movies') .
					"</a>";
				}
				?> 
			</div>

			<div class="imdblt_double_container_content_third lumiere_padding_five">

				<input type="checkbox" id="imdb_imdbtaxonomylanguage" name="imdb_imdbtaxonomylanguage" value="<?php if ($imdb_widget_values['imdbtaxonomylanguage'] == "1") { echo '0'; } else { echo '1'; }?>" />
				<label for="imdb_imdbtaxonomylanguage">
					<?php if ($imdb_widget_values['imdbtaxonomylanguage'] == "1") { 

							if ($imdb_widget_values['imdbwidgetlanguage'] == 1){echo '<span class="lumiere-option-taxo-activated">'; } else { echo '<span class="lumiere-option-taxo-deactivated">'; }

							esc_html_e( 'Languages', 'lumiere-movies'); echo '</span>'; } else { ?><?php  esc_html_e( 'Languages', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?>
				</label> 
				<?php
				if ($imdb_widget_values['imdbtaxonomylanguage'] == "1") {
					echo "<br />";
					echo "<a href='" . esc_url( admin_url() . "admin.php?page=imdblt_options&?page=imdblt_options&subsection=dataoption&widgetoption=taxo&taxotype=language" ) . "' " .
						"title='" . esc_html__("Copy a standard taxonomy template to your template folder to display this taxonomy.", 'lumiere-movies') . "' >".
						"<img src='".esc_url( $imdb_admin_values['imdbplugindirectory'] . "pics/admin-widget-copy-theme.png") . "' alt='copy the taxonomy template' align='absmiddle' align='absmiddle' />".
						esc_html__("Copy template", 'lumiere-movies') .
					"</a>";
				}
				?> 

			</div>

			<div class="imdblt_double_container_content_third lumiere_padding_five">

				<input type="checkbox" id="imdb_imdbtaxonomyproducer" name="imdb_imdbtaxonomyproducer" value="<?php if ($imdb_widget_values['imdbtaxonomyproducer'] == "1") { echo '0'; } else { echo '1'; }?>" />
				<label for="imdb_imdbtaxonomyproducer">
					<?php if ($imdb_widget_values['imdbtaxonomyproducer'] == "1") { 

							if ($imdb_widget_values['imdbwidgetproducer'] == 1){echo '<span class="lumiere-option-taxo-activated">'; } else { echo '<span class="lumiere-option-taxo-deactivated">'; }

							esc_html_e( 'Producers', 'lumiere-movies'); echo '</span>'; } else { ?><?php  esc_html_e( 'Producers', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?>
				</label> 
				<?php
				if ($imdb_widget_values['imdbtaxonomyproducer'] == "1") {
					echo "<br />";
					echo "<a href='" . esc_url( admin_url() . "admin.php?page=imdblt_options&?page=imdblt_options&subsection=dataoption&widgetoption=taxo&taxotype=producer" ) . "' " .
						"title='" . esc_html__("Copy a standard taxonomy template to your template folder to display this taxonomy.", 'lumiere-movies') . "' >".
						"<img src='".esc_url( $imdb_admin_values['imdbplugindirectory'] . "pics/admin-widget-copy-theme.png") . "' alt='copy the taxonomy template' align='absmiddle' align='absmiddle' />".
						esc_html__("Copy template", 'lumiere-movies') .
					"</a>";
				}
				?> 
			</div>

			<div class="imdblt_double_container_content_third lumiere_padding_five">

				<input type="checkbox" id="imdb_imdbtaxonomywriter" name="imdb_imdbtaxonomywriter" value="<?php if ($imdb_widget_values['imdbtaxonomywriter'] == "1") { echo '0'; } else { echo '1'; }?>" />
				<label for="imdb_imdbtaxonomywriter">
					<?php if ($imdb_widget_values['imdbtaxonomywriter'] == "1") { 

							if ($imdb_widget_values['imdbwidgetwriter'] == 1){echo '<span class="lumiere-option-taxo-activated">'; } else { echo '<span class="lumiere-option-taxo-deactivated">'; }

							esc_html_e( 'Writers', 'lumiere-movies'); echo '</span>'; } else { ?><?php  esc_html_e( 'Writers', 'lumiere-movies'); echo '&nbsp;&nbsp;'; } ?>
				</label> 
				<?php
				if ($imdb_widget_values['imdbtaxonomywriter'] == "1") {
					echo "<br />";
					echo "<a href='" . esc_url( admin_url() . "admin.php?page=imdblt_options&?page=imdblt_options&subsection=dataoption&widgetoption=taxo&taxotype=writer") . "' " .
						"title='" . esc_html__("Copy a standard taxonomy template to your template folder to display this taxonomy.", 'lumiere-movies') . "' >".
						"<img src='".esc_url( $imdb_admin_values['imdbplugindirectory'] . "pics/admin-widget-copy-theme.png") . "' alt='copy the taxonomy template' align='absmiddle' align='absmiddle' />".
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

			<select id="imdbwidgetorderContainer" name="imdbwidgetorderContainer[]" class="imdbwidgetorderContainer" size="<?php echo (count( $imdb_widget_values['imdbwidgetorder'] )/2); ?>" style="height:100%;" multiple>
<?php 
				foreach ($imdb_widget_values['imdbwidgetorder'] as $key=>$value) {

					if (!empty ( $key ) ) { // to eliminate empty keys

						echo "\t\t\t\t\t<option value='".$key."'";

						// search if "imdbwidget'title'" (ie) is activated
						if ($imdb_widget_values["imdbwidget$key"] != 1 ) { 

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
	
//------------------------------------------------------------------ =[Submit selection]=- ?>

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
