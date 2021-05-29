<?php

 #############################################################################
 # Lumière! wordpress plugin                                                 #
 # written by Lost Higway                                                    #
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
	'br',
];
$messages = array( /* highslide message notification options */
	'highslide_success' => 'Highslide successfully installed!',
	'highslide_failure' => 'Highslide installation failed!',
	'highslide_down' => 'Website to download Highslide is currently down, please try again later.',
	'highslide_website_unkown' => 'Website variable is not set.'
);

// included files
require_once ( $imdb_admin_values['imdbplugindirectory'] . 'inc/functions.php');

// If $_GET["msg"] is found, display a related notice
if ((isset($_GET['msg'])) && array_key_exists( sanitize_key( $_GET['msg'] ), $messages ) ){
	switch (sanitize_text_field( $_GET['msg'] )) {
		// Message for success
		case "highslide_success":
			lumiere_notice(1, esc_html__( $messages["highslide_success"], 'lumiere-movies') );
			break;
		// Message for failure
		case "highslide_failure":
			lumiere_notice(3, esc_html__( $messages["highslide_failure"] , 'lumiere-movies') . " " .  esc_html__( 'Your folder might be protected. Download highslide manually', 'lumiere-movies')." <a href='". esc_url ( IMDBBLOGHIGHSLIDE ) ."'>".esc_html__("here", 'lumiere-movies')."</a> ".esc_html__("and extract the zip into" ) . "<br />" .  esc_url( $imdb_admin_values['imdbpluginpath'] ."js/" ) );
			break;
		// Message for website down
		case "highslide_down":
			lumiere_notice(3, esc_html__( $messages["highslide_down"] , 'lumiere-movies')  );
			break;
		// Message for website unkown
		case "highslide_website_unkown":
			lumiere_notice(3, esc_html__( $messages["highslide_website_unkown"] , 'lumiere-movies')  );
			break;	
	}
}
?>

<div id="tabswrap">
	<ul id="tabs">
		<li><img src="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] . "pics/admin-general-path.png"); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e( "Paths & Layout", 'lumiere-movies');?>" href="<?php echo esc_url(admin_url() . "admin.php?page=imdblt_options&generaloption=base" ); ?>"><?php esc_html_e( 'Paths & Layout', 'lumiere-movies'); ?></a></li>

		<li>&nbsp;&nbsp;<img src="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] . "pics/admin-general-advanced.png"); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e( "Advanced", 'lumiere-movies');?>" href="<?php echo esc_url (admin_url() . "admin.php?page=imdblt_options&generaloption=advanced" ); ?>"><?php esc_html_e( "Advanced", 'lumiere-movies'); ?></a></li>
	</ul>
</div>


<div id="poststuff" class="metabox-holder">

<?php if ( ($_GET['generaloption'] == "base") || (!isset($_GET['generaloption'] )) ) { 	////////// Paths & Layout section  ?>

	<div class="intro_cache"><?php esc_html_e( "Below options usually don't need  any further action. Nevertheless, Lumiere can be widely customized to match your needs.", 'lumiere-movies'); ?></div>

	<div class="postbox">
		<h3 class="hndle" id="directories" name="directories"><?php esc_html_e( 'Paths: url & folders', 'lumiere-movies'); ?></h3>
	</div>

	<div class="inside imblt_border_shadow">
		<?php //------------------------------------------------------------------=[ web adresses ]=- ?>

		<div class="imdblt_double_container">
			<div class="imdblt_double_container_content_twenty">
				<label for="imdb_blog_adress"><?php esc_html_e( 'Blog address', 'lumiere-movies'); ?></label>
			</div>
			<div class="imdblt_double_container_content_eighty">
				<input class="imdblt_width_fillall" type="text" name="imdb_blog_adress" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptions['blog_adress']), 'lumiere-movies') ?>" >
				<div class="explain"><?php esc_html_e( 'Where the blog is installed.', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> "<?php echo esc_url( $imdbOptions['blog_adress'] ); ?>"</div>
			</div>
			
		</div>

		<div class="imdblt_double_container">
			<div class="imdblt_double_container_content_twenty">
				<label for="imdb_imdbplugindirectory"><?php esc_html_e( 'Plugin directory', 'lumiere-movies'); ?></label>
			</div>
			<div class="imdblt_double_container_content_eighty">
				<input type="text" class="imdblt_width_fillall" name="imdb_imdbplugindirectory" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptions['imdbplugindirectory']), 'lumiere-movies') ?>">
				<div class="explain"><?php wp_kses( _e( 'Where <strong>Lumiere</strong> is installed.', 'lumiere-movies'), $allowed_html_for_esc_html_functions ); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> "<?php echo IMDBLTURLPATH; ?>"</div>
		</div>
	</div>
		
	</div>

	
	<br />
	<br />

	<div class="postbox">
		<h3 class="hndle" id="layout" name="layout"><?php esc_html_e( 'Layout', 'lumiere-movies'); ?></h3>
	</div>

	<div class="inside">
		<div class="inside imblt_border_shadow">


		<?php //------------------------------------------------------------------ =[Popup]=- ?>

			<div class="titresection">
				<img src="<?php echo esc_url( $imdbOptions['imdbplugindirectory'] . "pics/popup.png"); ?>" width="60" align="absmiddle" />&nbsp;&nbsp;&nbsp;
				<?php esc_html_e( 'Popup', 'lumiere-movies'); ?>
			</div>
		
		<div class="imdblt_double_container">
			<div class="imdblt_double_container_content_third imdblt_padding_five">

				<label for="imdb_popupLarg"><?php esc_html_e( 'Width', 'lumiere-movies'); ?></label><br /><br />
				<input type="text" name="imdb_popupLarg" size="5" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptions['popupLarg']), 'lumiere-movies') ?>" >

				<div class="explain"> <?php esc_html_e( 'Popup width, in pixels', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?>"540"</div>

			</div>
			<div class="imdblt_double_container_content_third imdblt_padding_five">

				<label for="imdb_popupLong"><?php esc_html_e( 'Height', 'lumiere-movies'); ?></label><br /><br />
				<input type="text" name="imdb_popupLong" size="5" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptions['popupLong']), 'lumiere-movies') ?>" >

				<div class="explain"> <?php esc_html_e( 'Popup height, in pixels', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?>"350"</div>

			</div>
			<div class="imdblt_double_container_content_third imdblt_padding_five">

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
				lumiere_notice(4, '<span class="imdblt_red_bold">'.esc_html__('Warning! No Highslide folder was found.', 'lumiere-movies') .'</span>');
				echo "<br />";

				// Automatic download deactivated as per Wordpress's plugin staff request
				// echo "<a href='". esc_url( $imdbOptions['imdbplugindirectory'] . "inc/highslide_download.php?highslide=yes") . "' title='".esc_html__('Click here to install Highslide', 'lumiere-movies') ."'><img src='".esc_url($imdbOptions['imdbplugindirectory'] . "pics/admin-general-install-highslide.png")."' align='absmiddle' />&nbsp;&nbsp;".esc_html__('Install automatically Highslide', 'lumiere-movies') .'</a><br /><br />';

				// Add a link to highslide website
				echo '<a href="http://highslide.com/" title="' . esc_html__('Click here to visit Highslide website', 'lumiere-movies') .'"><img src="'.esc_url( $imdbOptions['imdbplugindirectory'] . 'pics/admin-general-install-highslide.png') . '" align="absmiddle" />&nbsp;&nbsp;'.esc_html__('Get Highslide JS library', 'lumiere-movies') . '</a><br /><br />';
			} 

?>

			</div>
		</div>

		
		<?php //------------------------------------------------------------------ =[Imdb link picture]=- ?>
			
			<div colspan="3" class="titresection">
				<img src="<?php echo esc_url( $imdbOptions['imdbplugindirectory'].$imdbOptions['imdbpicurl'] ); ?>" width="40" align="absmiddle" />
				<?php esc_html_e( 'Imdb link picture', 'lumiere-movies'); ?>
			</div>

		<div class="imdblt_double_container">
			<div class="imdblt_double_container_content_third imdblt_padding_five">

				<?php esc_html_e( 'Display imdb pic?', 'lumiere-movies'); ?><br /><br />

				<input type="radio" id="imdb_imdbdisplaylinktoimdb_yes" name="imdb_imdbdisplaylinktoimdb" value="1" <?php if ($imdbOptions['imdbdisplaylinktoimdb'] == "1") { echo 'checked="checked"'; }?> data-modificator="yes" data-field_to_change="imdb_imdbpicsize" data-field_to_change_value="1" />

				<label for="imdb_imdbdisplaylinktoimdb_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>
				<input type="radio" id="imdb_imdbdisplaylinktoimdb_no" name="imdb_imdbdisplaylinktoimdb" value="" <?php if ($imdbOptions['imdbdisplaylinktoimdb'] == 0) { echo 'checked="checked"'; } ?> data-modificator="yes" data-field_to_change="imdb_imdbpicsize" data-field_to_change_value="0" />

				<label for="imdb_imdbdisplaylinktoimdb_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

				<div class="explain"><?php esc_html_e( "Whether display imdb link (the yellow icon) or not. This picture can be found into the popup when looking for akas movies. If the option is unselected, visitors will no more have opportunity to follow links to IMDb (even if they could still follow internal links).", 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'Yes', 'lumiere-movies'); ?></div>

			</div>
			<div class="imdblt_double_container_content_third imdblt_padding_five">

				<label for="imdb_imdbpicsize"><?php esc_html_e( 'Size', 'lumiere-movies'); ?></label><br /><br />

				<input type="text" name="imdb_imdbpicsize" id="imdb_imdbpicsize" size="5" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptions['imdbpicsize']), 'lumiere-movies') ?>" <?php if ($imdbOptions['imdbdisplaylinktoimdb'] == 0) { echo 'disabled="disabled"'; }; ?> />

				<div class="explain"><?php esc_html_e( 'Size of the imdb picture. The value will correspond to the width in pixels.', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> "25"</div>

			</div>
			<div class="imdblt_double_container_content_third imdblt_padding_five">

				<label for="imdb_imdbpicurl"><?php esc_html_e( 'Url', 'lumiere-movies'); ?></label><br /><br />

				<input type="text" name="imdb_imdbpicurl" id="imdb_imdbpicurl" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptions['imdbpicurl']), 'lumiere-movies') ?>" <?php if ($imdbOptions['imdbdisplaylinktoimdb'] == 0) { echo 'disabled="disabled"'; }; ?> />

				<div class="explain"><?php esc_html_e( 'Url for the imdb picture', 'lumiere-movies'); ?><br /><?php esc_html_e( 'Default:','lumiere-movies');?> "pics/imdb-link.png"</div>

			</div>
		</div>
		
		<?php //------------------------------------------------------------------ =[Imdb cover picture]=- ?>

			<div class="titresection">
				<img src="<?php echo esc_url( $imdbOptions['imdbplugindirectory'] . "pics/cover.jpg"); ?>" width="60" align="absmiddle" />&nbsp;&nbsp;&nbsp;
				<?php esc_html_e( 'Imdb cover picture', 'lumiere-movies'); ?>
			</div>

		<div class="imdblt_double_container">
			<div class="imdblt_double_container_content_third imdblt_padding_five">

				<label for="imdb_popupLarg"><?php esc_html_e( 'Display only thumbnail', 'lumiere-movies'); ?><br /><br />
				<input type="radio" id="imdb_imdbcoversize_yes" name="imdb_imdbcoversize" value="1" <?php if ($imdbOptions['imdbcoversize'] == "1") { echo 'checked="checked"'; }?> data-modificator="yes" data-field_to_change="imdb_imdbcoversizewidth" data-field_to_change_value="1" />

				<label for="imdb_imdbcoversize_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

				<input type="radio" id="imdb_imdbcoversize_no" name="imdb_imdbcoversize" value="" <?php if ($imdbOptions['imdbcoversize'] == 0) { echo 'checked="checked"'; } ?> data-modificator="yes" data-field_to_change="imdb_imdbcoversizewidth" data-field_to_change_value="0" />

				<label for="imdb_imdbcoversize_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

				<div class="explain"><?php esc_html_e( 'Whether to display a thumbnail or a large image cover for movies inside a post or a widget. Select "No" to choose cover picture width (a new option on the right will be available).', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

			</div>
			<div class="imdblt_double_container_content_third imdblt_padding_five">

				<label for="imdb_imdbcoversizewidth"><?php esc_html_e( 'Size', 'lumiere-movies'); ?></label><br /><br />

				<input type="text" name="imdb_imdbcoversizewidth" id="imdb_imdbcoversizewidth" size="5" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptions['imdbcoversizewidth']), 'lumiere-movies'); ?>" />

				<div class="explain"><?php esc_html_e( 'Size of the imdb cover picture. The value will correspond to the width in pixels. Delete any value to get maximum width.', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> "100"</div>

			</div>
		</div>
	
	</div>
	
	<br />
	<br />


<?php	} 
	if ($_GET['generaloption'] == "advanced") { 				//////////////// Advanced section  ?>

	<div class="intro_cache"><?php esc_html_e( "Options below can break a lot of things. Edit them only if you know what you're doing.", 'lumiere-movies'); ?></div>


	<div class="inside">




		<?php //------------------------------------------------------------------ =[Search]=- ?>

	<div class="postbox">
		<h3 class="hndle" id="searchpart" name="searchpart"><?php esc_html_e( 'Search', 'lumiere-movies'); ?></h3>
	</div>

	<div class="inside imblt_border_shadow">

		<div class="imdblt_double_container">
			<div class="imdblt_double_container_content_third imdblt_padding_five">

				<label for="imdb_imdblanguage"><?php esc_html_e( 'Search language', 'lumiere-movies'); ?></label><br /><br />
				<select name="imdb_imdblanguage">
					<option <?php if( ($imdbOptions['imdblanguage'] == "en-US") || (empty($imdbOptions['imdblanguage'])) ) echo 'selected="selected"'; ?>value="en-US"><?php esc_html_e( 'English (default)', 'lumiere-movies'); ?></option>
					<option <?php if($imdbOptions['imdblanguage'] == "fr-FR") echo 'selected="selected"'; ?>value="fr-FR"><?php esc_html_e( 'French', 'lumiere-movies'); ?></option>
					<option <?php if($imdbOptions['imdblanguage'] == "es-ES") echo 'selected="selected"'; ?>value="es-ES"><?php esc_html_e( 'Spanish', 'lumiere-movies'); ?></option>
				</select>

				<div class="explain"><?php esc_html_e( 'Language used for the movie search. Very usefull for a non-English blog using Lumiere as a widget.', 'lumiere-movies'); ?></div>

			</div>

			<div class="imdblt_double_container_content_third imdblt_padding_five">
				<?php esc_html_e( 'Direct search', 'lumiere-movies'); ?><br /><br />
				<input type="radio" id="imdb_imdbdirectsearch_yes" name="imdb_imdbdirectsearch" value="1" <?php if ($imdbOptions['imdbdirectsearch'] == "1") { echo 'checked="checked"'; }?> data-modificator="yes" data-field_to_change="imdb_imdbmaxresults" data-field_to_change_value="1" />

				<label for="imdb_imdbdirectsearch_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label><input type="radio" id="imdb_imdbdirectsearch_no" name="imdb_imdbdirectsearch" value="0" <?php if ($imdbOptions['imdbdirectsearch'] == 0) { echo 'checked="checked"'; } ?> data-modificator="yes"  data-field_to_change="imdb_imdbmaxresults" data-field_to_change_value="0" />

				<label for="imdb_imdbdirectsearch_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

				<div class="explain"><?php wp_kses( _e( "When enabled, instead of displaying several results related to a name searched, only the first result is returned and directly displayed. That means no more window results is displayed, but straightforwardly related data. <br />This option allows to use the 'IMDb widget' and 'inside the post' options; if deactivated, these options will not work anymore. <br />Some options will be hidden and other will be shown depending if it is turned on yes or no.", 'lumiere-movies'), $allowed_html_for_esc_html_functions ); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'Yes', 'lumiere-movies'); ?></div>

			</div>

			<div class="imdblt_double_container_content_third imdblt_padding_five">

				<label for="imdb_imdbmaxresults"><?php esc_html_e( 'Limit results', 'lumiere-movies'); ?></label><br /><br />

				<input type="text" name="imdb_imdbmaxresults" id="imdb_imdbmaxresults" size="5" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptions['imdbmaxresults']), 'lumiere-movies') ?>" <?php if ($imdbOptions['imdbdirectsearch'] == 1) { echo 'disabled="disabled"'; }; ?> />

				<div class="explain"><?php esc_html_e( 'This the limit for the result set of researches. Use 0 for no limit, or the number of maximum entries you wish. When "direct search" option is turned to yes, this option is hidden.', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies'); ?> "10"</div>

			</div>
		</div>
	</div>

	<br /><br />
	
		<?php //------------------------------------------------------------------ =[misc]=- ?>


	<div class="postbox">
		<h3 class="hndle" id="miscpart" name="miscpart"><?php esc_html_e( 'Misc', 'lumiere-movies'); ?></h3>
	</div>

	<div class="inside imblt_border_shadow">

		<div class="imdblt_double_container">
			<div class="imdblt_double_container_content_third imdblt_padding_five">

				<?php esc_html_e( 'Left menu for Lumière options', 'lumiere-movies'); ?><br /><br />

				<input type="radio" id="imdb_imdbwordpress_bigmenu_yes" name="imdb_imdbwordpress_bigmenu" value="1" <?php if ($imdbOptions['imdbwordpress_bigmenu'] == "1") { echo 'checked="checked"'; }?> />

				<label for="imdb_imdbwordpress_bigmenu_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label><input type="radio" id="imdb_imdbwordpress_bigmenu_no" name="imdb_imdbwordpress_bigmenu" value="" <?php if ($imdbOptions['imdbwordpress_bigmenu'] == 0) { echo 'checked="checked"'; } ?>  />
				
				<label for="imdb_imdbwordpress_bigmenu_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

				<div class="explain"><?php esc_html_e( "When enabled, Lumiere options are fully displayed on the left menu - and not anymore limited to the settings directory. It creates a dedicated menu for Lumiere options.", 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

			</div>
			<div class="imdblt_double_container_content_third imdblt_padding_five">

				<?php esc_html_e( 'Top menu for Lumière options', 'lumiere-movies'); ?><br /><br />
				<input type="radio" id="imdb_imdbwordpress_tooladminmenu_yes" name="imdb_imdbwordpress_tooladminmenu" value="1" <?php if ($imdbOptions['imdbwordpress_tooladminmenu'] == "1") { echo 'checked="checked"'; }?> />

				<label for="imdb_imdbwordpress_tooladminmenu_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label><input type="radio" id="imdb_imdbwordpress_tooladminmenu_no" name="imdb_imdbwordpress_tooladminmenu" value="" <?php if ($imdbOptions['imdbwordpress_tooladminmenu'] == 0) { echo 'checked="checked"'; } ?>  />

				<label for="imdb_imdbwordpress_tooladminmenu_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

				<div class="explain"><?php esc_html_e( "When activated, Lumière options are displayed on the top menu of Wordpress. Deactivate it if you have many plugins already occupying that area.", 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'Yes', 'lumiere-movies'); ?></div>

			</div>

			<div class="imdblt_double_container_content_third imdblt_padding_five">

				<label for="imdb_imdbtaxonomy"><?php esc_html_e( 'Use automatical genre taxonomy?', 'lumiere-movies'); ?></label><br /><br />

				<input type="radio" id="imdb_imdbtaxonomy_yes" name="imdb_imdbtaxonomy" value="1" <?php if ($imdbOptions['imdbtaxonomy'] == "1") { echo 'checked="checked"'; }?> />

				<label for="imdb_imdbtaxonomy_yes"><?php esc_html_e( 'Yes', 'lumiere-movies'); ?></label>

				<input type="radio" id="imdb_imdbtaxonomy_no" name="imdb_imdbtaxonomy" value="" <?php if ($imdbOptions['imdbtaxonomy'] == 0) { echo 'checked="checked"'; } ?>  />

				<label for="imdb_imdbtaxonomy_no"><?php esc_html_e( 'No', 'lumiere-movies'); ?></label>

				<div class="explain"><?php esc_html_e( 'This will automatically add "genre" terms found for the movie into wordpress database, as ', 'lumiere-movies') ?><a href="http://codex.wordpress.org/WordPress_Taxonomy">taxonomy</a>. <?php esc_html_e( 'Activating this option opens ', 'lumiere-movies'); ?><a href="<?php echo admin_url(); ?>admin.php?page=imdblt_options&subsection=widgetoption&widgetoption=taxo"><?php esc_html_e( 'others taxonomy options', 'lumiere-movies');  ?></a>. <?php esc_html_e( 'Taxonomy terms are uninstalled when removing the plugin.', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies'); ?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

			</div>
		</div>


<?php	} // end of advanced section ?>
		
	</div>

	<?php //------------------------------------------------------------------ =[Submit selection]=- ?>
	<div class="submit submit-imdb" align="center">
		<?php wp_nonce_field('reset_imdbSettings_check', 'reset_imdbSettings_check'); //check that data has been sent only once ?>
		<input type="submit" class="button-primary" name="reset_imdbSettings" value="<?php esc_html_e( 'Reset settings', 'lumiere-movies') ?>" />
		<?php wp_nonce_field('update_imdbSettings_check', 'update_imdbSettings_check', false);  //check that data has been sent only once -- don't send _wp_http_referer twice, already sent with first wp_nonce_field -> 3rd option to "false" ?>
		<input type="submit" class="button-primary" name="update_imdbSettings" value="<?php esc_html_e( 'Update settings', 'lumiere-movies') ?>" />
	</div>
	<br />
</div>
