<?php

 #############################################################################
 # Lumiere Movies                                                            #
 # written by Prometheus group                                               #
 # https://www.jcvignoli.com/blog                                            #
 # ------------------------------------------------------------------------- #
 # This program is free software; you can redistribute and/or modify it      #
 # under the terms of the GNU General Public License (see LICENSE)           #
 # ------------------------------------------------------------------------- #
 #									              #
 #  Function : general configuration admin page                              #
 #									              #
 #############################################################################

global $imdb_admin_values;

$allowed_html_for_esc_html_functions = [ /* accept these html tags in wp_kses escaping function */
    'strong',
];
$messages = array( /* highslide message notification options */
    'highslide_success' => 'Highslide successfully installed!',
    'highslide_failure' => 'Highslide installation failed!',
    'highslide_down' => 'Website to download Highslide is currently down, please try again later.'
);

// included files
require_once ( $imdb_admin_values['imdbplugindirectory'] . 'inc/functions.php');

// If a certain
if ((isset($_GET['msg'])) && array_key_exists($_GET['msg'], $messages) ){
	// Message for success
	if ($_GET['msg']=="highslide_success") {
		imdblt_notice(1, esc_html__( $messages["highslide_success"], 'imdb') );
	// Message for failure
	} elseif ($_GET['msg']=="highslide_failure") {
		imdblt_notice(3, esc_html__( $messages["highslide_failure"] , 'imdb') . " " .  esc_html__( 'Your folder might be protected. Download highslide manually', 'imdb')." <a href='". IMDBBLOGHIGHSLIDE ."'>".esc_html__("here", "imdb")."</a> ".esc_html__("and extract the zip into" ) . "<br />" .  esc_url( $imdb_admin_values['imdbpluginpath'] ."js/" ) );

	} elseif ($_GET['msg']=="highslide_down") {
		imdblt_notice(3, esc_html__( $messages["highslide_down"] , 'imdb')  );
	}
}
?>

<div id="tabswrap">
	<ul id="tabs">
		<li><img src="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] . "pics/admin-general-path.png"); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e( "Paths & Layout", 'imdb');?>" href="<?php echo esc_url(admin_url() . "admin.php?page=imdblt_options&generaloption=base" ); ?>"><?php esc_html_e( 'Paths & Layout', 'imdb'); ?></a></li>

		<li>&nbsp;&nbsp;<img src="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] . "pics/admin-general-advanced.png"); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e( "Advanced", 'imdb');?>" href="<?php echo esc_url (admin_url() . "admin.php?page=imdblt_options&generaloption=advanced" ); ?>"><?php esc_html_e( "Advanced", 'imdb'); ?></a></li>
	</ul>
</div>


<div id="poststuff" class="metabox-holder">

<?php if ( ($_GET['generaloption'] == "base") || (!isset($_GET['generaloption'] )) ) { 	////////// Paths & Layout section  ?>

	<div class="intro_cache"><?php esc_html_e( "Below options usually don't need  any further action. Nevertheless, Lumiere Movies can be widely customized to match your needs.", 'imdb'); ?></div>

	<div class="postbox">
		<h3 class="hndle" id="directories" name="directories"><?php esc_html_e( 'Paths: url & folders', 'imdb'); ?></h3>
	</div>

	<div class="inside">
	<table class="option widefat">

		<?php //------------------------------------------------------------------=[ web adresses ]=- ?>

		<tr>
			<td class="td-aligntop"><label for="imdb_blog_adress"><?php esc_html_e( 'Blog adress', 'imdb'); ?></label>
			</td>
			<td width="80%"><input type="text" name="imdb_blog_adress" size="70" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptions['blog_adress']), 'imdb') ?>" >
				<div class="explain"><?php esc_html_e( 'Where the blog is installed.', 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> "<?php echo esc_url( $imdbOptions['blog_adress'] ); ?>"</div>
			</td>
			
		</tr>
		<tr>
			<td class="td-aligntop"><label for="imdb_imdbplugindirectory"><?php esc_html_e( 'Plugin directory', 'imdb'); ?></label>
			</td>
			<td><input type="text" name="imdb_imdbplugindirectory" size="70" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptions['imdbplugindirectory']), 'imdb') ?>">
				<div class="explain"><?php wp_kses( _e( 'Where <strong>Lumiere Movies</strong> is installed.', 'imdb'), $allowed_html_for_esc_html_functions ); ?> <br /><?php esc_html_e( 'Default:','imdb');?> "<?php echo IMDBLTURLPATH; ?>"</div>
			</td>
		</tr>
		
	</table>
	</div>

	
	<br />
	<br />

	<div class="postbox">
		<h3 class="hndle" id="layout" name="layout"><?php esc_html_e( 'Layout', 'imdb'); ?></h3>
	</div>

	<div class="inside">
	<table class="option widefat">
		<?php //------------------------------------------------------------------ =[Popup]=- ?>
		<tr>
			<td colspan="3" class="titresection">
				<img src="<?php echo esc_url( $imdbOptions['imdbplugindirectory'] . "pics/popup.png"); ?>" width="60" align="absmiddle" />&nbsp;&nbsp;&nbsp;
				<?php esc_html_e( 'Popup', 'imdb'); ?></td>
		</tr>
		
		<tr>
			<td width="33%">
				<label for="imdb_popupLarg"><?php esc_html_e( 'Width', 'imdb'); ?></label>
				<input type="text" name="imdb_popupLarg" size="5" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptions['popupLarg']), 'imdb') ?>" >
			</td>
			<td width="33%">
				<label for="imdb_popupLong"><?php esc_html_e( 'Height', 'imdb'); ?></label>
				<input type="text" name="imdb_popupLong" size="5" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptions['popupLong']), 'imdb') ?>" >
			</td>
			<td width="33%">
				<?php 

				// Warning message displayed if highslide option is set but no "highslide" folder exists
				if(!is_dir( IMDBLTABSPATH . 'js/highslide')) { 
					imdblt_notice(4, '<span class="imdblt_red_bold">'.esc_html__('Warning! No Highslide folder was found.', 'imdb') .'</span>');
					echo "<br />";
					echo "<a href='". $imdbOptions['imdbplugindirectory'] . "inc/highslide_download.php?highslide=yes'>".esc_html__('Install automatically Highslide by clicking here', 'imdb') .'</a>';
				} 
?>

				<?php if(is_dir(IMDBLTABSPATH.'js/highslide')) { // If the folder "highslide" exists (manually added)
					esc_html_e( 'Display highslide popup', 'imdb'); 
					echo '&nbsp;&nbsp;&nbsp;&nbsp; 
					<input type="radio" id="imdb_imdbpopup_highslide_yes" name="imdb_imdbpopup_highslide" value="1" ';
					if ($imdbOptions['imdbpopup_highslide'] == "1") { echo 'checked="checked"'; }
					echo ' /><label for="imdb_imdbpopup_highslide_yes">';
					esc_html_e( 'Yes', 'imdb');
					echo '</label><input type="radio" id="imdb_imdbpopup_highslide_no" name="imdb_imdbpopup_highslide" value="" ';
					 if ($imdbOptions['imdbpopup_highslide'] == 0) { echo 'checked="checked"'; } 
					echo '/><label for="imdb_imdbpopup_highslide_no">';
					 esc_html_e( 'No', 'imdb'); 
					echo '</label>';
				 }; ?>
				
			</td>
		</tr>
		<tr>
			<td class="td-aligntop">
				<div class="explain"> <?php esc_html_e( 'Popup width, in pixels', 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?>"540"</div>
			</td>
			<td class="td-aligntop">
				<div class="explain"> <?php esc_html_e( 'Popup height, in pixels', 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?>"350"</div>
			</td>
			<td class="td-aligntop">
				<?php if(is_dir( $imdbOptions['imdbplugindirectory'] . 'js/highslide')) { // If the folder "highslide" exists (manually added) ?>
				<div class="explain"><?php esc_html_e( 'Highslide popup is a more stylished popup, and allows to open movie details directly in the webpage instead of opening a new window.', 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> <?php esc_html_e( 'Yes', 'imdb'); ?></div>
				<?php } else { // If no folder "highslide" exists, explanations
				echo '<div class="explain">';
				esc_html_e( 'Please note Highslide JS is licensed under a Creative Commons Attribution-NonCommercial 2.5 License, which means you need the author\'s permission to use Highslide JS on commercial websites.','imdb');
				echo '<br />';
				echo esc_html__( 'Website:','imdb').'&nbsp;<a href="https://highslide.com/">Highslide</a>';
				echo '</div>';
				}; ?>
			</td>
		</tr>
		
		<?php //------------------------------------------------------------------ =[Imdb link picture]=- ?>
			
		<tr>
			<td colspan="3" class="titresection">
				<img src="<?php echo esc_url( $imdbOptions['imdbplugindirectory'].$imdbOptions['imdbpicurl'] ); ?>" width="40" align="absmiddle" />&nbsp;&nbsp;&nbsp;
				<?php esc_html_e( 'Imdb link picture', 'imdb'); ?>
			</td>
		</tr>
		
		<tr>
			<td class="td-aligntop">
				<?php esc_html_e( 'Display imdb pic?', 'imdb'); ?>&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" id="imdb_imdbdisplaylinktoimdb_yes" name="imdb_imdbdisplaylinktoimdb" value="1" <?php if ($imdbOptions['imdbdisplaylinktoimdb'] == "1") { echo 'checked="checked"'; }?> data-modificator="yes" data-field_to_change="imdb_imdbpicsize" data-field_to_change_value="1" />
				<label for="imdb_imdbdisplaylinktoimdb_yes"><?php esc_html_e( 'Yes', 'imdb'); ?></label>
				<input type="radio" id="imdb_imdbdisplaylinktoimdb_no" name="imdb_imdbdisplaylinktoimdb" value="" <?php if ($imdbOptions['imdbdisplaylinktoimdb'] == 0) { echo 'checked="checked"'; } ?> data-modificator="yes" data-field_to_change="imdb_imdbpicsize" data-field_to_change_value="0" />
				<label for="imdb_imdbdisplaylinktoimdb_no"><?php esc_html_e( 'No', 'imdb'); ?></label>
			</td>
			<td class="td-aligntop">
				<label for="imdb_imdbpicsize"><?php esc_html_e( 'Size', 'imdb'); ?></label>
				<input type="text" name="imdb_imdbpicsize" id="imdb_imdbpicsize" size="5" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptions['imdbpicsize']), 'imdb') ?>" <?php if ($imdbOptions['imdbdisplaylinktoimdb'] == 0) { echo 'disabled="disabled"'; }; ?> />
			</td>
			<td class="td-aligntop">
				<label for="imdb_imdbpicurl"><?php esc_html_e( 'Url', 'imdb'); ?></label>
				<input type="text" name="imdb_imdbpicurl" id="imdb_imdbpicurl" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptions['imdbpicurl']), 'imdb') ?>" <?php if ($imdbOptions['imdbdisplaylinktoimdb'] == 0) { echo 'disabled="disabled"'; }; ?> />
			</td>
		</tr>
		<tr>
			<td class="td-aligntop">
				<div class="explain"><?php esc_html_e( "Whether display imdb link (the yellow icon) or not. This picture can be found into the popup when looking for akas movies. If the option is unselected, visitors will no more have opportunity to follow links to IMDb (even if they could still follow internal links).", 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> <?php esc_html_e( 'Yes', 'imdb'); ?></div>
			</td>
			<td class="td-aligntop">
				<div class="explain"><?php esc_html_e( 'Size of the imdb picture. The value will correspond to the width in pixels.', 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> "25"</div>
			</td>
			<td>
				<div class="explain"><?php esc_html_e( 'Url for the imdb picture', 'imdb'); ?><br /><?php esc_html_e( 'Default:','imdb');?> "pics/imdb-link.png"</div>
			</td>
		</tr>
		
		<?php //------------------------------------------------------------------ =[Imdb cover picture]=- ?>
		<tr>
			<td colspan="3" class="titresection">
				<img src="<?php echo esc_url( $imdbOptions['imdbplugindirectory'] . "pics/cover.jpg"); ?>" width="60" align="absmiddle" />&nbsp;&nbsp;&nbsp;
				<?php esc_html_e( 'Imdb cover picture', 'imdb'); ?>
			</td>
		</tr>
		
		<tr>
			<td width="33%">
				<label for="imdb_popupLarg"><?php esc_html_e( 'Display only thumbnail', 'imdb'); ?>&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" id="imdb_imdbcoversize_yes" name="imdb_imdbcoversize" value="1" <?php if ($imdbOptions['imdbcoversize'] == "1") { echo 'checked="checked"'; }?> data-modificator="yes" data-field_to_change="imdb_imdbcoversizewidth" data-field_to_change_value="1" /><label for="imdb_imdbcoversize_yes"><?php esc_html_e( 'Yes', 'imdb'); ?></label><input type="radio" id="imdb_imdbcoversize_no" name="imdb_imdbcoversize" value="" <?php if ($imdbOptions['imdbcoversize'] == 0) { echo 'checked="checked"'; } ?> data-modificator="yes" data-field_to_change="imdb_imdbcoversizewidth" data-field_to_change_value="0" /><label for="imdb_imdbcoversize_no"><?php esc_html_e( 'No', 'imdb'); ?></label>
			</td>
			<td width="33%">
				<label for="imdb_imdbcoversizewidth"><?php esc_html_e( 'Size', 'imdb'); ?></label>
				<input type="text" name="imdb_imdbcoversizewidth" id="imdb_imdbcoversizewidth" size="5" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptions['imdbcoversizewidth']), 'imdb'); ?>" />
			</td>
			<td width="33%">
			</td>
		</tr>
		<tr>
			<td class="td-aligntop">
				<div class="explain"><?php esc_html_e( 'Whether to display a thumbnail or a large image cover for movies inside a post or a widget. Select "No" to choose cover picture width (a new option on the right will be available).', 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> <?php esc_html_e( 'No', 'imdb'); ?></div>
			</td>
			<td class="td-aligntop">
				<div class="explain"><?php esc_html_e( 'Size of the imdb cover picture. The value will correspond to the width in pixels. Delete any value to get maximum width.', 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> "100"</div>
			</td>
			<td class="td-aligntop">
			</td>
		</tr>
		
	</table>
	</div>
	
	<br />
	<br />


<?php	} 
	if ($_GET['generaloption'] == "advanced") { 				//////////////// Advanced section  ?>

	<div class="intro_cache"><?php esc_html_e( "Options below can break a lot of things. Edit them only if you know what you're doing.", 'imdb'); ?></div>


	<div class="inside">
	<table class="option widefat">
		<?php //------------------------------------------------------------------ =[Search]=- ?>
		<tr>
			<td colspan="3" class="titresection"><?php esc_html_e( 'Search, imdb part', 'imdb'); ?></td>
		</tr>

		<tr>
			<td width="33%" class="td-aligntop">
<?php /* 2021 05 10 unactivated
				<label for="imdb_imdbwebsite"><?php esc_html_e( 'Imdb address', 'imdb'); ?></label>
				<select name="imdb_imdbwebsite">
					<option <?php if($imdbOptions['imdbwebsite'] == "akas.imdb.com") echo 'selected="selected"'; ?> value="akas.imdb.com">akas.imdb.com (default)</option>
					<option <?php if($imdbOptions['imdbwebsite'] == "www.imdb.fr") echo 'selected="selected"'; ?> value="www.imdb.fr">french imdb</option>
					<option <?php if($imdbOptions['imdbwebsite'] == "uk.imdb.com") echo 'selected="selected"'; ?> value="uk.imdb.com">uk imdb</option>
					<option <?php if($imdbOptions['imdbwebsite'] == "www.imdb.de") echo 'selected="selected"'; ?> value="www.imdb.de">german imdb</option>			
					<option <?php if($imdbOptions['imdbwebsite'] == "www.imdb.it") echo 'selected="selected"'; ?> value="www.imdb.it">italian imdb</option>
				</select>
*/ ?>
				<label for="imdb_imdblanguage"><?php esc_html_e( 'Language', 'imdb'); ?></label>
				<select name="imdb_imdblanguage">
					<option <?php if( ($imdbOptions['imdblanguage'] == "en-US") || (empty($imdbOptions['imdblanguage'])) ) echo 'selected="selected"'; ?>value="en-US"><?php esc_html_e( 'English (default)', 'imdb'); ?></option>
					<option <?php if($imdbOptions['imdblanguage'] == "fr-FR") echo 'selected="selected"'; ?>value="fr-FR"><?php esc_html_e( 'French', 'imdb'); ?></option>
					<option <?php if($imdbOptions['imdblanguage'] == "es-ES") echo 'selected="selected"'; ?>value="es-ES"><?php esc_html_e( 'Spanish', 'imdb'); ?></option>
				</select>
			<td width="33%">
			</td>
			<td width="33%">
			</td>
		</tr>
		<tr>
			<td class="td-aligntop">
<?php /* 2021 05 10 unactivated
				<div class="explain"><?php esc_html_e( "This is the imdb server to use. The localized ones (i.e. italian and german) are only qualified to find the movies ID - but parsing for the details will fail at the moment.", 'imdb'); ?></div>
*/ ?>
				<div class="explain"><?php esc_html_e( 'Language used for the movie search. Very usefull for a non-English blog using Lumiere as a widget.', 'imdb'); ?></div>

			</td>
			<td class="td-aligntop">
			</td>
			<td class="td-aligntop">
			</td>
		</tr>		
		
		<?php //------------------------------------------------------------------ =[misc]=- ?>
		<tr>
			<td colspan="3" class="titresection"><?php esc_html_e( 'Misc', 'imdb'); ?></td>
		</tr>

		<tr>
			<td>
				<?php esc_html_e( 'Debug mode', 'imdb'); ?>&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" id="imdb_imdbdebug_yes" name="imdb_imdbdebug" value="1" <?php if ($imdbOptions['imdbdebug'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbdebug_yes"><?php esc_html_e( 'Yes', 'imdb'); ?></label><input type="radio" id="imdb_imdbdebug_no" name="imdb_imdbdebug" value="" <?php if ($imdbOptions['imdbdebug'] == 0) { echo 'checked="checked"'; } ?>/><label for="imdb_imdbdebug_no"><?php esc_html_e( 'No', 'imdb'); ?></label>				
			</td>
			<td>
				<?php esc_html_e( 'Direct search', 'imdb'); ?>&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" id="imdb_imdbdirectsearch_yes" name="imdb_imdbdirectsearch" value="1" <?php if ($imdbOptions['imdbdirectsearch'] == "1") { echo 'checked="checked"'; }?> data-modificator="yes" data-field_to_change="imdb_imdbmaxresults" data-field_to_change_value="1" /><label for="imdb_imdbdirectsearch_yes"><?php esc_html_e( 'Yes', 'imdb'); ?></label><input type="radio" id="imdb_imdbdirectsearch_no" name="imdb_imdbdirectsearch" value="0" <?php if ($imdbOptions['imdbdirectsearch'] == 0) { echo 'checked="checked"'; } ?> data-modificator="yes"  data-field_to_change="imdb_imdbmaxresults" data-field_to_change_value="0" /><label for="imdb_imdbdirectsearch_no"><?php esc_html_e( 'No', 'imdb'); ?></label>
			</td>
			<td>
				<?php esc_html_e( 'Menu for IMDB LT options', 'imdb'); ?>&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" id="imdb_imdbwordpress_bigmenu_yes" name="imdb_imdbwordpress_bigmenu" value="1" <?php if ($imdbOptions['imdbwordpress_bigmenu'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwordpress_bigmenu_yes"><?php esc_html_e( 'Yes', 'imdb'); ?></label><input type="radio" id="imdb_imdbwordpress_bigmenu_no" name="imdb_imdbwordpress_bigmenu" value="" <?php if ($imdbOptions['imdbwordpress_bigmenu'] == 0) { echo 'checked="checked"'; } ?>  /><label for="imdb_imdbwordpress_bigmenu_no"><?php esc_html_e( 'No', 'imdb'); ?></label>
			</td>
		</tr>
		<tr>
			<td class="td-aligntop">
				<div class="explain"><?php esc_html_e( 'Enable debug mode?', 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> <?php esc_html_e( 'No', 'imdb'); ?></div>
			</td>
			<td class="td-aligntop">
				<div class="explain">
				<?php esc_html_e( "When enabled, instead of displaying several results related to a name searched, only the first result is returned and directly displayed. That means no more window results is displayed, but straightforwardly related data. <br />This option allows to use the 'IMDb widget' and 'inside the post' options; if deactivated, these options will not work anymore. <br />Some options will be hidden and other will be shown depending if it is turned on yes or no.", 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> <?php esc_html_e( 'Yes', 'imdb'); ?></div>
			</td>
			<td class="td-aligntop">
				<div class="explain">
				<?php esc_html_e( "When enabled, Lumiere Movies options are displayed separately from the settings menu. It will create a dedicated menu to the Lumiere Movies options.", 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> <?php esc_html_e( 'No', 'imdb'); ?></div>
			</td>
		</tr>


		<tr>
			<td>
				<label for="imdb_imdbmaxresults"><?php esc_html_e( 'Limit results', 'imdb'); ?></label>
				<input type="text" name="imdb_imdbmaxresults" id="imdb_imdbmaxresults" size="5" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptions['imdbmaxresults']), 'imdb') ?>" <?php if ($imdbOptions['imdbdirectsearch'] == 1) { echo 'disabled="disabled"'; }; ?> />
			</td>
			<td>	
				<label for="imdb_imdbtaxonomy"><?php esc_html_e( 'Use automatical genre taxonomy?', 'imdb'); ?></label>
				<input type="radio" id="imdb_imdbtaxonomy_yes" name="imdb_imdbtaxonomy" value="1" <?php if ($imdbOptions['imdbtaxonomy'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbtaxonomy_yes"><?php esc_html_e( 'Yes', 'imdb'); ?></label><input type="radio" id="imdb_imdbtaxonomy_no" name="imdb_imdbtaxonomy" value="" <?php if ($imdbOptions['imdbtaxonomy'] == 0) { echo 'checked="checked"'; } ?>  /><label for="imdb_imdbtaxonomy_no"><?php esc_html_e( 'No', 'imdb'); ?></label>
			</td>
			<td>
				<?php esc_html_e( 'Toolbar IMDB LT admin menu', 'imdb'); ?>&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" id="imdb_imdbwordpress_tooladminmenu_yes" name="imdb_imdbwordpress_tooladminmenu" value="1" <?php if ($imdbOptions['imdbwordpress_tooladminmenu'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwordpress_tooladminmenu_yes"><?php esc_html_e( 'Yes', 'imdb'); ?></label><input type="radio" id="imdb_imdbwordpress_tooladminmenu_no" name="imdb_imdbwordpress_tooladminmenu" value="" <?php if ($imdbOptions['imdbwordpress_tooladminmenu'] == 0) { echo 'checked="checked"'; } ?>  /><label for="imdb_imdbwordpress_tooladminmenu_no"><?php esc_html_e( 'No', 'imdb'); ?></label>
			</td>
		</tr>
		<tr>
			<td class="td-aligntop">
				<div class="explain"><?php esc_html_e( 'This the limit for the result set of researches. Use 0 for no limit, or the number of maximum entries you wish. When "direct search" option is turned to yes, this option is hidden.', 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb'); ?> "10"</div>
			</td>
			<td class="td-aligntop">
				<div class="explain"><?php esc_html_e( 'This will automatically add "genre" terms found for the movie into wordpress database, as ', 'imdb') ?><a href="http://codex.wordpress.org/WordPress_Taxonomy">taxonomy</a>. <?php esc_html_e( 'Activating this option opens ', 'imdb'); ?><a href="<?php echo admin_url(); ?>admin.php?page=imdblt_options&subsection=widgetoption&widgetoption=taxo"><?php esc_html_e( 'others taxonomy options', 'imdb');  ?></a>. <?php esc_html_e( 'Taxonomy terms are uninstalled when removing the plugin.', 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb'); ?> <?php esc_html_e( 'No', 'imdb'); ?></div>
			</td>
			<td class="td-aligntop">
				<div class="explain">
				<?php esc_html_e( "When activated, Lumiere Movies options are displayed in the toolbar admin menu of Wordpress.", 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> <?php esc_html_e( 'Yes', 'imdb'); ?></div>
			</td>

		</tr>
<?php	} // end of advanced section ?>
		
	</table>
	</div>

	<?php //------------------------------------------------------------------ =[Submit selection]=- ?>
	<div class="submit submit-imdb" align="center">
		<?php wp_nonce_field('reset_imdbSettings_check', 'reset_imdbSettings_check'); //check that data has been sent only once ?>
		<input type="submit" class="button-primary" name="reset_imdbSettings" value="<?php esc_html_e( 'Reset settings', 'imdb') ?>" />
		<?php wp_nonce_field('update_imdbSettings_check', 'update_imdbSettings_check', false);  //check that data has been sent only once -- don't send _wp_http_referer twice, already sent with first wp_nonce_field -> 3rd option to "false" ?>
		<input type="submit" class="button-primary" name="update_imdbSettings" value="<?php esc_html_e( 'Update settings', 'imdb') ?>" />
	</div>
	<br />
</div>
