<?php

 #############################################################################
 # IMDb Link transformer                                                     #
 # written by Prometheus group                                               #
 # https://www.jcvignoli.com/blog                                            #
 # ------------------------------------------------------------------------- #
 # This program is free software; you can redistribute and/or modify it      #
 # under the terms of the GNU General Public License (see LICENSE)           #
 # ------------------------------------------------------------------------- #
 #									              #
 #  Function : widget configuration admin page                               #
 #									              #
 #############################################################################

global $imdb_admin_values;

// included files
require_once ( $imdb_admin_values['imdbplugindirectory'] . 'inc/functions.php');

?>

<div id="tabswrap">
	<ul id="tabs">
		<li><img src="<?php echo $imdb_admin_values['imdbplugindirectory']; ?>pics/admin-widget-inside-whattodisplay.png" align="absmiddle" width="16px" />&nbsp;<a title="<?php esc_html_e( "What to display", 'imdb');?>" href="<?php echo esc_url ( admin_url() . "admin.php?page=imdblt_options&subsection=widgetoption&widgetoption=what"); ?>"><?php esc_html_e( 'What to display', 'imdb'); ?></a></li>
			<?php if ($imdbOptions['imdbtaxonomy'] == "1") { ?>
		<li>&nbsp;&nbsp;<img src="<?php echo $imdb_admin_values['imdbplugindirectory']; ?>pics/admin-widget-inside-whattotaxo.png" align="absmiddle" width="16px" />&nbsp;<a title="<?php esc_html_e( "What to taxonomize", 'imdb');?>" href="<?php echo esc_url ( admin_url() . "admin.php?page=imdblt_options&subsection=widgetoption&widgetoption=taxo"); ?>"><?php esc_html_e( "What to taxonomize", 'imdb'); ?></a></li>
			<?php } else { ?>
		<li>&nbsp;&nbsp;<img src="<?php echo $imdb_admin_values['imdbplugindirectory']; ?>pics/admin-widget-inside-whattodisplay.png" align="absmiddle" width="16px" />&nbsp;<i><?php esc_html_e( "Taxonomy unactivated", 'imdb');?></i></li>
			<?php }?>
		<li>&nbsp;&nbsp;<img src="<?php echo $imdb_admin_values['imdbplugindirectory']; ?>pics/admin-widget-inside-order.png" align="absmiddle" width="16px" />&nbsp;<a title="<?php esc_html_e( "Display order", 'imdb');?>" href="<?php echo esc_url ( admin_url() . "admin.php?page=imdblt_options&subsection=widgetoption&widgetoption=order"); ?>"><?php esc_html_e( "Display order", 'imdb'); ?></a></li>
		<li>&nbsp;&nbsp;<img src="<?php echo $imdb_admin_values['imdbplugindirectory']; ?>pics/admin-widget-inside-misc.png" align="absmiddle" width="16px" />&nbsp;<a title="<?php esc_html_e( "Misc", 'imdb');?>" href="<?php echo esc_url ( admin_url() . "admin.php?page=imdblt_options&subsection=widgetoption&widgetoption=misc"); ?>"><?php esc_html_e( 'Misc', 'imdb'); ?></a></li>
	</ul>
</div>

<div id="poststuff" class="metabox-holder">

	<div class="inside">
	<table class="option widefat">
		
		<?php //-------------------------------------------------------------------=[title, pic, runtime]=- ?>		

<?php if ( ($_GET['widgetoption'] == "what") || (!isset($_GET['widgetoption'] )) ) { 	// What to display  ?>

		<tr>
			<td colspan="3" class="titresection"><?php esc_html_e( 'What to display', 'imdb'); ?></td>
		</tr>
		
		<tr>
			<td width="33%">
				<?php if ($imdbOptionsw['imdbwidgettitle'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Title', 'imdb'); echo '</span>'; } else { ?>
				<?php  esc_html_e( 'Title', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				<input type="radio" id="imdb_imdbwidgettitle_yes" name="imdb_imdbwidgettitle" value="1" <?php if ($imdbOptionsw['imdbwidgettitle'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgettitle_yes"><?php esc_html_e( 'Yes', 'imdb'); ?></label>
				<input type="radio" id="imdb_imdbwidgettitle_no" name="imdb_imdbwidgettitle" value="" <?php if ($imdbOptionsw['imdbwidgettitle'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgettitle_no"><?php esc_html_e( 'No', 'imdb'); ?></label>
			</td>
			<td width="33%">
				<?php if ($imdbOptionsw['imdbwidgetpic'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Picture', 'imdb'); echo '</span>'; } else { ?>
				<?php  esc_html_e( 'Picture', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				
				<input type="radio" id="imdb_imdbwidgetpic_yes" name="imdb_imdbwidgetpic" value="1" <?php if ($imdbOptionsw['imdbwidgetpic'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetpic_yes"><?php esc_html_e( 'Yes', 'imdb'); ?></label>
				<input type="radio" id="imdb_imdbwidgetpic_no" name="imdb_imdbwidgetpic" value="" <?php if ($imdbOptionsw['imdbwidgetpic'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetpic_no"><?php esc_html_e( 'No', 'imdb'); ?></label>
			</td>
			<td width="33%">
				<?php if ($imdbOptionsw['imdbwidgetruntime'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Runtime', 'imdb'); echo '</span>'; } else { ?>
				<?php  esc_html_e( 'Runtime', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				
				<input type="radio" id="imdb_imdbwidgetruntime_yes" name="imdb_imdbwidgetruntime" value="1" <?php if ($imdbOptionsw['imdbwidgetruntime'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetruntime_yes"><?php esc_html_e( 'Yes', 'imdb'); ?></label>
				<input type="radio" id="imdb_imdbwidgetruntime_no" name="imdb_imdbwidgetruntime" value="" <?php if ($imdbOptionsw['imdbwidgetruntime'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetruntime_no"><?php esc_html_e( 'No', 'imdb'); ?></label></td>
		</tr>
		<tr>
			<td class="td-aligntop"><div class="explain"><?php esc_html_e( 'Display the title', 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> <?php esc_html_e( 'Yes', 'imdb'); ?></div></td>
			<td class="td-aligntop"><div class="explain"><?php esc_html_e( 'Display the picture', 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> <?php esc_html_e( 'Yes', 'imdb'); ?></div></td>
			<td class="td-aligntop"><div class="explain"><?php esc_html_e( 'Display the runtime', 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> <?php esc_html_e( 'No', 'imdb'); ?></div></td>
		</tr>

		<?php //-------------------------------------------------------------------=[director, actor, country]=- ?>		

		<tr>
			<td>
				<?php if ($imdbOptionsw['imdbwidgetdirector'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Director', 'imdb'); echo '</span>'; } else { ?>
				<?php  esc_html_e( 'Director', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				
				<input type="radio" id="imdb_imdbwidgetdirector_yes" name="imdb_imdbwidgetdirector" value="1" <?php if ($imdbOptionsw['imdbwidgetdirector'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetdirector_yes"><?php esc_html_e( 'Yes', 'imdb'); ?></label>
				<input type="radio" id="imdb_imdbwidgetdirector_no" name="imdb_imdbwidgetdirector" value="" <?php if ($imdbOptionsw['imdbwidgetdirector'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetdirector_no"><?php esc_html_e( 'No', 'imdb'); ?></label>
			</td>
			<td>
				<?php if ($imdbOptionsw['imdbwidgetcountry'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Country', 'imdb'); echo '</span>'; } else { ?>
				<?php  esc_html_e( 'Country', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				
				<input type="radio" id="imdb_imdbwidgetcountry_yes" name="imdb_imdbwidgetcountry" value="1" <?php if ($imdbOptionsw['imdbwidgetcountry'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetcountry_yes"><?php esc_html_e( 'Yes', 'imdb'); ?></label>
				<input type="radio" id="imdb_imdbwidgetcountry_no" name="imdb_imdbwidgetcountry" value="" <?php if ($imdbOptionsw['imdbwidgetcountry'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetcountry_no"><?php esc_html_e( 'No', 'imdb'); ?></label>
			</td>
			<td>
				<?php if ($imdbOptionsw['imdbwidgetactor'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Actor', 'imdb'); echo '</span>'; } else { ?>
				<?php  esc_html_e( 'Actor', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				
				<input type="radio" id="imdb_imdbwidgetactor_yes" name="imdb_imdbwidgetactor" value="1" <?php if ($imdbOptionsw['imdbwidgetactor'] == "1") { echo 'checked="checked"'; }?> data-modificator="yes" data-field_to_change="imdb_imdbwidgetactornumber" data-field_to_change_value="0" /><label for="imdb_imdbwidgetactor_yes"><?php esc_html_e( 'Yes', 'imdb'); ?></label>
				<input type="radio" id="imdb_imdbwidgetactor_no" name="imdb_imdbwidgetactor" value="" <?php if ($imdbOptionsw['imdbwidgetactor'] == 0) { echo 'checked="checked"'; } ?> data-modificator="yes" data-field_to_change="imdb_imdbwidgetactornumber" data-field_to_change_value="1" /><label for="imdb_imdbwidgetactor_no"><?php esc_html_e( 'No', 'imdb'); ?></label>

				<input type="text" id="imdb_imdbwidgetactornumber" name="imdb_imdbwidgetactornumber" size="3" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptionsw['imdbwidgetactornumber']), 'imdb') ?>" <?php if ($imdbOptionsw['imdbwidgetactor'] == 0){ echo 'disabled="disabled"'; }; ?> />
			</td>
		</tr>
		<tr>
			<td class="td-aligntop"><div class="explain"><?php esc_html_e( 'Display directors', 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> <?php esc_html_e( 'Yes', 'imdb'); ?></div></td>
			<td><div class="explain"><?php esc_html_e( 'Display country', 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> <?php esc_html_e( 'No', 'imdb'); ?></div></td>
			<td><div class="explain"><?php esc_html_e( 'Display (how many) actors', 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> <?php esc_html_e( 'Yes', 'imdb'); ?> & 10</div></td>
		</tr>


		<?php //-------------------------------------------------------------------=[creator, release date, genre]=- ?>	
		<tr>
			<td>
				<?php if ($imdbOptionsw['imdbwidgetcreator'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Creator', 'imdb'); echo '</span>'; } else { ?>
				<?php  esc_html_e( 'Creator', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				
				<input type="radio" id="imdb_imdbwidgetcreator_yes" name="imdb_imdbwidgetcreator" value="1" <?php if ($imdbOptionsw['imdbwidgetcreator'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetcreator_yes"><?php esc_html_e( 'Yes', 'imdb'); ?></label>
				<input type="radio" id="imdb_imdbwidgetcreator_no" name="imdb_imdbwidgetcreator" value="" <?php if ($imdbOptionsw['imdbwidgetcreator'] == 0) { echo 'checked="checked"'; } ?>  /><label for="imdb_imdbwidgetcreator_no"><?php esc_html_e( 'No', 'imdb'); ?></label>	
			</td>
			<td>
				<?php if ($imdbOptionsw['imdbwidgetyear'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Year', 'imdb'); echo '</span>'; } else { ?>
				<?php  esc_html_e( 'Year', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				
				<input type="radio" id="imdb_imdbwidgetyear_yes" name="imdb_imdbwidgetyear" value="1" <?php if ($imdbOptionsw['imdbwidgetyear'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetyear_yes"><?php esc_html_e( 'Yes', 'imdb'); ?></label>
				<input type="radio" id="imdb_imdbwidgetyear_no" name="imdb_imdbwidgetyear" value="" <?php if ($imdbOptionsw['imdbwidgetyear'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetyear_no"><?php esc_html_e( 'No', 'imdb'); ?></label>
			</td>
			<td>
				<?php if ($imdbOptionsw['imdbwidgetgenre'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Genre', 'imdb'); echo '</span>'; } else { ?>
				<?php  esc_html_e( 'Genre', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				
				<input type="radio" id="imdb_imdbwidgetgenre_yes" name="imdb_imdbwidgetgenre" value="1" <?php if ($imdbOptionsw['imdbwidgetgenre'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetgenre_yes"><?php esc_html_e( 'Yes', 'imdb'); ?></label>
				<input type="radio" id="imdb_imdbwidgetgenre_no" name="imdb_imdbwidgetgenre" value="" <?php if ($imdbOptionsw['imdbwidgetgenre'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetgenre_no"><?php esc_html_e( 'No', 'imdb'); ?></label>
			</td>
		</tr>
		<tr>
			<td class="td-aligntop"><div class="explain"><?php esc_html_e( 'Display Creator', 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> <?php esc_html_e( 'No', 'imdb'); ?></div>
			</td>
			<td><div class="explain"><?php esc_html_e( "Display release year. Year will appear next title's movie, in brackets.", 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> <?php esc_html_e( 'No', 'imdb'); ?></div>
			</td>
			<td><div class="explain"><?php esc_html_e( 'Display genre(s)', 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> <?php esc_html_e( 'No', 'imdb'); ?></div>
			</td>
		</tr>
		


		<?php //-------------------------------------------------------------------=[writer, producer, plot]=- ?>		
		<tr>
			<td>
				<?php if ($imdbOptionsw['imdbwidgetwriter'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Writer', 'imdb'); echo '</span>'; } else { ?>
				<?php  esc_html_e( 'Writer', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				
				<input type="radio" id="imdb_imdbwidgetwriter_yes" name="imdb_imdbwidgetwriter" value="1" <?php if ($imdbOptionsw['imdbwidgetwriter'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetwriter_yes"><?php esc_html_e( 'Yes', 'imdb'); ?></label>
				<input type="radio" id="imdb_imdbwidgetwriter_no" name="imdb_imdbwidgetwriter" value="" <?php if ($imdbOptionsw['imdbwidgetwriter'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetwriter_no"><?php esc_html_e( 'No', 'imdb'); ?></label>	
			</td>
			<td>
				<?php if ($imdbOptionsw['imdbwidgetproducer'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Producer', 'imdb'); echo '</span>'; } else { ?>
				<?php  esc_html_e( 'Producer', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				
				<input type="radio" id="imdb_imdbwidgetproducer_yes" name="imdb_imdbwidgetproducer" value="1" <?php if ($imdbOptionsw['imdbwidgetproducer'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetproducer_yes"><?php esc_html_e( 'Yes', 'imdb'); ?></label>
				<input type="radio" id="imdb_imdbwidgetproducer_no" name="imdb_imdbwidgetproducer" value="" <?php if ($imdbOptionsw['imdbwidgetproducer'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetproducer_no"><?php esc_html_e( 'No', 'imdb'); ?></label>
			</td>
			<td>
				<?php if ($imdbOptionsw['imdbwidgetplot'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Plot', 'imdb'); echo '</span>'; } else { ?>
				<?php  esc_html_e( 'Plot', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				
				<input type="radio" id="imdb_imdbwidgetplot_yes" name="imdb_imdbwidgetplot" value="1" <?php if ($imdbOptionsw['imdbwidgetplot'] == "1") { echo 'checked="checked"'; }?> data-modificator="yes" data-field_to_change="imdb_imdbwidgetplotnumber" data-field_to_change_value="0" /><label for="imdb_imdbwidgetplot_yes"><?php esc_html_e( 'Yes', 'imdb'); ?></label>
				<input type="radio" id="imdb_imdbwidgetplot_no" name="imdb_imdbwidgetplot" value="" <?php if ($imdbOptionsw['imdbwidgetplot'] == 0) { echo 'checked="checked"'; } ?> data-modificator="yes" data-field_to_change="imdb_imdbwidgetplotnumber" data-field_to_change_value="1" /><label for="imdb_imdbwidgetplot_no"><?php esc_html_e( 'No', 'imdb'); ?></label>

				<input type="text" id="imdb_imdbwidgetplotnumber" name="imdb_imdbwidgetplotnumber" size="3" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptionsw['imdbwidgetplotnumber']), 'imdb') ?>" <?php if ($imdbOptionsw['imdbwidgetplot'] == 0){ echo 'disabled="disabled"'; }; ?> />
			</td>
		</tr>
		<tr>
			<td class="td-aligntop"><div class="explain"><?php esc_html_e( 'Display writers', 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> <?php esc_html_e( 'Yes', 'imdb'); ?></div></td>
			<td><div class="explain"><?php esc_html_e( 'Display producer(s)', 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> <?php esc_html_e( 'No', 'imdb'); ?></div></td>
			<td><div class="explain"><?php esc_html_e( 'Display plot(s). Be careful, this field may need a lot of space. In ideal case, this plugin is used inside a post and not into a widget.', 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> <?php esc_html_e( 'No', 'imdb'); ?></div></td>
		</tr>


		<?php //-------------------------------------------------------------------=[keywords, production companies, quotes]=- ?>
		<tr>
			<td>
				<?php if ($imdbOptionsw['imdbwidgetkeywords'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Keywords', 'imdb'); echo '</span>'; } else { ?>
				<?php  esc_html_e( 'Keywords', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				
				<input type="radio" id="imdb_imdbwidgetkeywords_yes" name="imdb_imdbwidgetkeywords" value="1" <?php if ($imdbOptionsw['imdbwidgetkeywords'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetkeywords_yes"><?php esc_html_e( 'Yes', 'imdb'); ?></label>
				<input type="radio" id="imdb_imdbwidgetkeywords_no" name="imdb_imdbwidgetkeywords" value="" <?php if ($imdbOptionsw['imdbwidgetkeywords'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetkeywords_no"><?php esc_html_e( 'No', 'imdb'); ?></label>
			</td>
			<td>
				<?php if ($imdbOptionsw['imdbwidgetprodCompany'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Production company', 'imdb'); echo '</span>'; } else { ?>
				<?php  esc_html_e( 'Production company', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				
				<input type="radio" id="imdb_imdbwidgetprodCompany_yes" name="imdb_imdbwidgetprodCompany" value="1" <?php if ($imdbOptionsw['imdbwidgetprodCompany'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetprodCompany_yes"><?php esc_html_e( 'Yes', 'imdb'); ?></label>
				<input type="radio" id="imdb_imdbwidgetprodCompany_no" name="imdb_imdbwidgetprodCompany" value="" <?php if ($imdbOptionsw['imdbwidgetprodCompany'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetprodCompany_no"><?php esc_html_e( 'No', 'imdb'); ?></label>
			</td>
			<td>
				<?php if ($imdbOptionsw['imdbwidgetquotes'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Quotes', 'imdb'); echo '</span>'; } else { ?>
				<?php  esc_html_e( 'Quotes', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				
				<input type="radio" id="imdb_imdbwidgetquotes_yes" name="imdb_imdbwidgetquotes" value="1" <?php if ($imdbOptionsw['imdbwidgetquotes'] == "1") { echo 'checked="checked"'; }?> data-modificator="yes" data-field_to_change="imdb_imdbwidgetquotesnumber" data-field_to_change_value="0" /><label for="imdb_imdbwidgetquotes_yes"><?php esc_html_e( 'Yes', 'imdb'); ?></label>
				<input type="radio" id="imdb_imdbwidgetquotes_no" name="imdb_imdbwidgetquotes" value="" <?php if ($imdbOptionsw['imdbwidgetquotes'] == 0) { echo 'checked="checked"'; } ?> data-modificator="yes" data-field_to_change="imdb_imdbwidgetquotesnumber" data-field_to_change_value="1" /><label for="imdb_imdbwidgetquotes_no"><?php esc_html_e( 'No', 'imdb'); ?></label>

				<input type="text" id="imdb_imdbwidgetquotesnumber" name="imdb_imdbwidgetquotesnumber" size="3" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptionsw['imdbwidgetquotesnumber']), 'imdb') ?>" <?php if ($imdbOptionsw['imdbwidgetquotes'] == 0){ echo 'disabled="disabled"'; }; ?> />
			</td>
		</tr>
		<tr>
			<td><div class="explain"><?php esc_html_e( 'Display keywords', 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> <?php esc_html_e( 'No', 'imdb'); ?></div></div></td>
			<td><div class="explain"><div class="explain"><?php esc_html_e( 'Display the production companies', 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> <?php esc_html_e( 'No', 'imdb'); ?></div></td>
			<td><div class="explain"><?php esc_html_e( "Display (how many) quotes from movie", 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> <?php esc_html_e( 'No', 'imdb'); ?></div></td>
		</tr>



		<?php //-------------------------------------------------------------------=[taglines, colors, alsoknow]=- ?>
		<tr>
			<td>
				<?php if ($imdbOptionsw['imdbwidgettaglines'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Tagline', 'imdb'); echo '</span>'; } else { ?>
				<?php  esc_html_e( 'Tagline', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				
				<input type="radio" id="imdb_imdbwidgettaglines_yes" name="imdb_imdbwidgettaglines" value="1" <?php if ($imdbOptionsw['imdbwidgettaglines'] == "1") { echo 'checked="checked"'; }?> data-modificator="yes" data-field_to_change="imdb_imdbwidgettaglinesnumber" data-field_to_change_value="0" /><label for="imdb_imdbwidgettaglines_yes"><?php esc_html_e( 'Yes', 'imdb'); ?></label>
				<input type="radio" id="imdb_imdbwidgettaglines_no" name="imdb_imdbwidgettaglines" value="" <?php if ($imdbOptionsw['imdbwidgettaglines'] == 0) { echo 'checked="checked"'; } ?> data-modificator="yes" data-field_to_change="imdb_imdbwidgettaglinesnumber" data-field_to_change_value="1" /><label for="imdb_imdbwidgettaglines_no"><?php esc_html_e( 'No', 'imdb'); ?></label>

				<input type="text" id="imdb_imdbwidgettaglinesnumber" name="imdb_imdbwidgettaglinesnumber" size="3" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptionsw['imdbwidgettaglinesnumber']), 'imdb') ?>" <?php if ($imdbOptionsw['imdbwidgettaglines'] == 0){ echo 'disabled="disabled"'; }; ?> />
			</td>
			<td>
				<?php if ($imdbOptionsw['imdbwidgetcolors'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Colors', 'imdb'); echo '</span>'; } else { ?>
				<?php  esc_html_e( 'Colors', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				
				<input type="radio" id="imdb_imdbwidgetcolors_yes" name="imdb_imdbwidgetcolors" value="1" <?php if ($imdbOptionsw['imdbwidgetcolors'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetcolors_yes"><?php esc_html_e( 'Yes', 'imdb'); ?></label>
				<input type="radio" id="imdb_imdbwidgetcolorsno" name="imdb_imdbwidgetcolors" value="" <?php if ($imdbOptionsw['imdbwidgetcolors'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetcolors_no"><?php esc_html_e( 'No', 'imdb'); ?></label>
			</td>
			<td>
				<?php if ($imdbOptionsw['imdbwidgetalsoknow'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Also known as', 'imdb'); echo '</span>'; } else { ?>
				<?php  esc_html_e( 'Also known as', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				
				<input type="radio" id="imdb_imdbwidgetalsoknow_yes" name="imdb_imdbwidgetalsoknow" value="1" <?php if ($imdbOptionsw['imdbwidgetalsoknow'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetalsoknow_yes"><?php esc_html_e( 'Yes', 'imdb'); ?></label>
				<input type="radio" id="imdb_imdbwidgetalsoknow_no" name="imdb_imdbwidgetalsoknow" value="" <?php if ($imdbOptionsw['imdbwidgetalsoknow'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetalsoknow_no"><?php esc_html_e( 'No', 'imdb'); ?></label>
			</td>
		</tr>
		<tr>
			<td class="td-aligntop"><div class="explain"><?php esc_html_e( 'Display (how many) tagline', 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> <?php esc_html_e( 'No', 'imdb'); ?></div></td>
			<td><div class="explain"><?php esc_html_e( "Display colors", 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> <?php esc_html_e( 'No', 'imdb'); ?></div></td>
			<td><div class="explain"><?php esc_html_e( "Display all movie's names", 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> <?php esc_html_e( 'No', 'imdb'); ?></div></td>
		</tr>



		<?php //-------------------------------------------------------------------=[composer, soundtrack, trailer]=- ?>
		<tr>
			<td>
				<?php if ($imdbOptionsw['imdbwidgetcomposer'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Composer', 'imdb'); echo '</span>'; } else { ?>
				<?php  esc_html_e( 'Composer', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				
				<input type="radio" id="imdb_imdbwidgetcomposer_yes" name="imdb_imdbwidgetcomposer" value="1" <?php if ($imdbOptionsw['imdbwidgetcomposer'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetcomposer_yes"><?php esc_html_e( 'Yes', 'imdb'); ?></label>
				<input type="radio" id="imdb_imdbwidgetcomposer_no" name="imdb_imdbwidgetcomposer" value="" <?php if ($imdbOptionsw['imdbwidgetcomposer'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetcomposer_no"><?php esc_html_e( 'No', 'imdb'); ?></label>
			</td>
			<td>
				<?php if ($imdbOptionsw['imdbwidgetsoundtrack'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Soundtrack', 'imdb'); echo '</span>'; } else { ?>
				<?php  esc_html_e( 'Soundtrack', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				
				<input type="radio" id="imdb_imdbwidgetsoundtrack_yes" name="imdb_imdbwidgetsoundtrack" value="1" <?php if ($imdbOptionsw['imdbwidgetsoundtrack'] == "1") { echo 'checked="checked"'; }?> data-modificator="yes" data-field_to_change="imdb_imdbwidgetsoundtracknumber" data-field_to_change_value="0" /><label for="imdb_imdbwidgetsoundtrack_yes"><?php esc_html_e( 'Yes', 'imdb'); ?></label>
				<input type="radio" id="imdb_imdbwidgetsoundtrack" name="imdb_imdbwidgetsoundtrack" value="" <?php if ($imdbOptionsw['imdbwidgetsoundtrack'] == 0) { echo 'checked="checked"'; } ?> data-modificator="yes" data-field_to_change="imdb_imdbwidgetsoundtracknumber" data-field_to_change_value="1" /><label for="imdb_imdbwidgetsoundtrack_no"><?php esc_html_e( 'No', 'imdb'); ?></label>

				<input type="text" id="imdb_imdbwidgetsoundtracknumber" name="imdb_imdbwidgetsoundtracknumber" size="3" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptionsw['imdbwidgetsoundtracknumber']), 'imdb') ?>" <?php if ($imdbOptionsw['imdbwidgetsoundtrack'] == 0){ echo 'disabled="disabled"'; }; ?> />
			</td>
			<td>
				<?php if ($imdbOptionsw['imdbwidgettrailer'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Trailers', 'imdb'); echo '</span>'; } else { ?>
				<?php  esc_html_e( 'Trailers', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				
				<input type="radio" id="imdb_imdbwidgettrailer_yes" name="imdb_imdbwidgettrailer" value="1" <?php if ($imdbOptionsw['imdbwidgettrailer'] == "1") { echo 'checked="checked"'; }?> data-modificator="yes" data-field_to_change="imdb_imdbwidgettrailernumber" data-field_to_change_value="0" /><label for="imdb_imdbwidgettrailer_yes"><?php esc_html_e( 'Yes', 'imdb'); ?></label>
				<input type="radio" id="imdb_imdbwidgettrailer_no" name="imdb_imdbwidgettrailer" value="" <?php if ($imdbOptionsw['imdbwidgettrailer'] == 0) { echo 'checked="checked"'; } ?> data-modificator="yes" data-field_to_change="imdb_imdbwidgettrailernumber" data-field_to_change_value="1" /><label for="imdb_imdbwidgettrailer_no"><?php esc_html_e( 'No', 'imdb'); ?></label>

				<input type="text" id="imdb_imdbwidgettrailernumber" name="imdb_imdbwidgettrailernumber" size="3" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptionsw['imdbwidgettrailernumber']), 'imdb') ?>" <?php if ($imdbOptionsw['imdbwidgettrailernumber'] == 0){ echo 'disabled="disabled"'; }; ?> />

			</td>
		</tr>
		<tr>
			<td class="td-aligntop"><div class="explain"><?php esc_html_e( 'Display composer', 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> <?php esc_html_e( 'No', 'imdb'); ?></div></td>
			<td><div class="explain"><?php esc_html_e( "Display (how many) soundtrack", 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> <?php esc_html_e( 'No', 'imdb'); ?></div></td>
			<td><div class="explain"><?php esc_html_e( 'Display (how many) trailers', 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> <?php esc_html_e( 'No', 'imdb'); ?></div></td>
		</tr>



		<?php //-------------------------------------------------------------------=[official websites, rating, language]=- ?>
		<tr>
			<td>
				<?php if ($imdbOptionsw['imdbwidgetofficialSites'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Official websites', 'imdb'); echo '</span>'; } else { ?>
				<?php  esc_html_e( 'Official websites', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				
				<input type="radio" id="imdb_imdbwidgetofficialSites_yes" name="imdb_imdbwidgetofficialSites" value="1" <?php if ($imdbOptionsw['imdbwidgetofficialSites'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetofficialSites_yes"><?php esc_html_e( 'Yes', 'imdb'); ?></label>
				<input type="radio" id="imdb_imdbwidgetofficialSites_no" name="imdb_imdbwidgetofficialSites" value="" <?php if ($imdbOptionsw['imdbwidgetofficialSites'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetofficialSites_no"><?php esc_html_e( 'No', 'imdb'); ?></label>
			</td>
			<td>
				<?php if ($imdbOptionsw['imdbwidgetrating'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Rating', 'imdb'); echo '</span>'; } else { ?>
				<?php  esc_html_e( 'Rating', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				
				<input type="radio" id="imdb_imdbwidgetrating_yes" name="imdb_imdbwidgetrating" value="1" <?php if ($imdbOptionsw['imdbwidgetrating'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetrating_yes"><?php esc_html_e( 'Yes', 'imdb'); ?></label>
				<input type="radio" id="imdb_imdbwidgetrating_no" name="imdb_imdbwidgetrating" value="" <?php if ($imdbOptionsw['imdbwidgetrating'] == 0) { echo 'checked="checked"'; } ?>  /><label for="imdb_imdbwidgetrating_no"><?php esc_html_e( 'No', 'imdb'); ?></label>
			</td>
			<td>
				<?php if ($imdbOptionsw['imdbwidgetlanguage'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Language', 'imdb'); echo '</span>'; } else { ?>
				<?php  esc_html_e( 'Language', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				
				<input type="radio" id="imdb_imdbwidgetlanguage_yes" name="imdb_imdbwidgetlanguage" value="1" <?php if ($imdbOptionsw['imdbwidgetlanguage'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetlanguage_yes"><?php esc_html_e( 'Yes', 'imdb'); ?></label>
				<input type="radio" id="imdb_imdbwidgetlanguage_no" name="imdb_imdbwidgetlanguage" value="" <?php if ($imdbOptionsw['imdbwidgetlanguage'] == 0) { echo 'checked="checked"'; } ?> /><label for="imdb_imdbwidgetlanguage_no"><?php esc_html_e( 'No', 'imdb'); ?></label>
			</td>
		</tr>
		<tr>
			<td class="td-aligntop">
				<div class="explain"><?php esc_html_e( 'Display official websites', 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> <?php esc_html_e( 'No', 'imdb'); ?></div>
			</td>
			<td class="td-aligntop">
				<div class="explain"><?php esc_html_e( 'Display rating', 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> <?php esc_html_e( 'No', 'imdb'); ?></div>
			</td>
			<td>
				<div class="explain"><?php esc_html_e( 'Display language(s)', 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> <?php esc_html_e( 'No', 'imdb'); ?></div>
			</td>
		</tr>

		<?php //-------------------------------------------------------------------=[goofs, user comments, source]=- ?>
		<tr>
			<td>
				<?php if ($imdbOptionsw['imdbwidgetgoofs'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Goofs', 'imdb'); echo '</span>'; } else { ?>
				<?php  esc_html_e( 'Goofs', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				
				<input type="radio" id="imdb_imdbwidgetgoofs_yes" name="imdb_imdbwidgetgoofs" value="1" <?php if ($imdbOptionsw['imdbwidgetgoofs'] == "1") { echo 'checked="checked"'; }?> data-modificator="yes" data-field_to_change="imdb_imdbwidgetgoofsnumber" data-field_to_change_value="0" /><label for="imdb_imdbwidgetgoofs_yes"><?php esc_html_e( 'Yes', 'imdb'); ?></label>
				<input type="radio" id="imdb_imdbwidgetgoofs_no" name="imdb_imdbwidgetgoofs" value="" <?php if ($imdbOptionsw['imdbwidgetgoofs'] == 0) { echo 'checked="checked"'; } ?> data-modificator="yes" data-field_to_change="imdb_imdbwidgetgoofsnumber" data-field_to_change_value="1" /><label for="imdb_imdbwidgetgoofs_no"><?php esc_html_e( 'No', 'imdb'); ?></label>

				<input type="text" id="imdb_imdbwidgetgoofsnumber" name="imdb_imdbwidgetgoofsnumber" size="3" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptionsw['imdbwidgetgoofsnumber']), 'imdb') ?>" <?php if ($imdbOptionsw['imdbwidgetgoofs'] == 0){ echo 'disabled="disabled"'; }; ?> />
			</td>
			<td>
				<?php if ($imdbOptionsw['imdbwidgetcomments'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Users comment', 'imdb'); echo '</span>'; } else { ?>
				<?php  esc_html_e( 'Users comment', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				
				<input type="radio" id="imdb_imdbwidgetcomments_yes" name="imdb_imdbwidgetcomments" value="1" <?php if ($imdbOptionsw['imdbwidgetcomments'] == "1") { echo 'checked="checked"'; }?> data-modificator="yes" data-field_to_change="imdb_imdbwidgetcommentsnumber" data-field_to_change_value="0" /><label for="imdb_imdbwidgetcomments_yes"><?php esc_html_e( 'Yes', 'imdb'); ?></label>
				<input type="radio" id="imdb_imdbwidgetcomments_no" name="imdb_imdbwidgetcomments" value="" <?php if ($imdbOptionsw['imdbwidgetcomments'] == 0) { echo 'checked="checked"'; } ?> data-modificator="yes" data-field_to_change="imdb_imdbwidgetcommentsnumber" data-field_to_change_value="1" /><label for="imdb_imdbwidgetcomments_no"><?php esc_html_e( 'No', 'imdb'); ?></label>

				<input type="text" id="imdb_imdbwidgetcommentsnumber" name="imdb_imdbwidgetcommentsnumber" size="3" value="<?php esc_html_e( apply_filters('format_to_edit',$imdbOptionsw['imdbwidgetcommentsnumber']), 'imdb') ?>" <?php if ($imdbOptionsw['imdbwidgetcomments'] == 0){ echo 'disabled="disabled"'; }; ?> />
			</td>
			<td>
				<?php if ($imdbOptionsw['imdbwidgetsource'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Source', 'imdb'); echo '</span>'; } else { ?>
				<?php  esc_html_e( 'Source', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				<input type="radio" id="imdb_imdbwidgetsource_yes" name="imdb_imdbwidgetsource" value="1" <?php if ($imdbOptionsw['imdbwidgetsource'] == "1") { echo 'checked="checked"'; }?> /><label for="imdb_imdbwidgetsource_yes"><?php esc_html_e( 'Yes', 'imdb'); ?></label>
				<input type="radio" id="imdb_imdbwidgetsource_no" name="imdb_imdbwidgetsource" value="" <?php if ($imdbOptionsw['imdbwidgetsource'] == 0) { echo 'checked="checked"'; } ?>  /><label for="imdb_imdbwidgetsource_no"><?php esc_html_e( 'No', 'imdb'); ?></label>
			</td>
		</tr>
		<tr>
			<td class="td-aligntop"><div class="explain"><?php esc_html_e( 'Display (how many) goof', 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> <?php esc_html_e( 'No', 'imdb'); ?></div></td>
			<td><div class="explain"><?php esc_html_e( "Display (how many) users' comments", 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> <?php esc_html_e( 'No', 'imdb'); ?></div></td>
			<td><div class="explain"><div class="explain"><?php esc_html_e( 'Display website source at the end of the post', 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> <?php esc_html_e( 'Yes', 'imdb'); ?></div>
		</tr>



<?php	} 
		if ($_GET['widgetoption'] == "taxo")  { 	// Taxonomy ?>
		<?php //-------------------------------------------------------------------=[Taxonomy]=-

			if ($imdbOptions['imdbtaxonomy'] != "1") { //check if taxonomy is activated
				echo "<div align='center' class='accesstaxo'>".__('Please ', 'imdb')."<a href='".esc_url ( admin_url().'admin.php?page=imdblt_options&generaloption=advanced') . '">' . __('activate taxonomy', 'imdb') . '</a>' . __(' priorly', 'imdb') . '<br />' . __('to access taxonomies options.', 'imdb') . "</div>";
			} else { // taxonomy is activated ?>
		<tr>
			<td colspan="4" class="titresection"><?php esc_html_e( 'Select details to use as taxonomy', 'imdb'); ?></td>
		</tr>
		<tr>
			<td colspan="4"><?php esc_html_e( "Use the checkbox to display (or not) details. When activated, details names are turned to blue. Check the box to turn it back to black (and thus deactivating detail as taxonomy).", 'imdb'); ?>
			<br /><br />
			<?php esc_html_e( "Select cautiously options you want to be displayed as taxonomies: it could happen it creates a conflict with other functions, especially if you display many movies in same post. When selecting one of the following taxonomy options, it will supersede any other function or link created; for instance, you won't have anymore access to popup links for directors, if directors taxonomy is chosen. Taxonomy will always prevail.", 'imdb'); ?>
			<br /><br />
			</td>
		</tr>
		
		<tr>
			<td width="10%">&nbsp;</td>
			<td width="27%">
				<input type="checkbox" id="imdb_imdbtaxonomyactor" name="imdb_imdbtaxonomyactor" value="<?php if ($imdbOptionsw['imdbtaxonomyactor'] == "1") { echo '0'; } else { echo '1'; }?>" />
				<label for="imdb_imdbtaxonomyactor">
					<?php if ($imdbOptionsw['imdbtaxonomyactor'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Actors', 'imdb'); echo '</span>'; } else { ?><?php  esc_html_e( 'Actors', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				</label> 
			</td>
			<td width="27%">
				<input type="checkbox" id="imdb_imdbtaxonomycolor" name="imdb_imdbtaxonomycolor" value="<?php if ($imdbOptionsw['imdbtaxonomycolor'] == "1") { echo '0'; } else { echo '1'; }?>" />
				<label for="imdb_imdbtaxonomycolor">
					<?php if ($imdbOptionsw['imdbtaxonomycolor'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Colors', 'imdb'); echo '</span>'; } else { ?><?php  esc_html_e( 'Colors', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				</label> 
			</td>
			<td width="27%">
				<input type="checkbox" id="imdb_imdbtaxonomycomposer" name="imdb_imdbtaxonomycomposer" value="<?php if ($imdbOptionsw['imdbtaxonomycomposer'] == "1") { echo '0'; } else { echo '1'; }?>" />
				<label for="imdb_imdbtaxonomycomposer">
					<?php if ($imdbOptionsw['imdbtaxonomycomposer'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Composers', 'imdb'); echo '</span>'; } else { ?><?php  esc_html_e( 'Composers', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				</label> 
			</td>
		</tr>

		<tr>
			<td width="10%">&nbsp;</td>
			<td width="27%">
				<input type="checkbox" id="imdb_imdbtaxonomycreator" name="imdb_imdbtaxonomycreator" value="<?php if ($imdbOptionsw['imdbtaxonomycreator'] == "1") { echo '0'; } else { echo '1'; }?>" />
				<label for="imdb_imdbtaxonomycreator">
					<?php if ($imdbOptionsw['imdbtaxonomycreator'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Creators', 'imdb'); echo '</span>'; } else { ?><?php  esc_html_e( 'Creators', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				</label> 
			</td>

			<td width="27%">
				<input type="checkbox" id="imdb_imdbtaxonomycountry" name="imdb_imdbtaxonomycountry" value="<?php if ($imdbOptionsw['imdbtaxonomycountry'] == "1") { echo '0'; } else { echo '1'; }?>" />
				<label for="imdb_imdbtaxonomycountry">
					<?php if ($imdbOptionsw['imdbtaxonomycountry'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Countries', 'imdb'); echo '</span>'; } else { ?><?php  esc_html_e( 'Countries', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				</label> 
			</td>

			<td width="27%">
				<input type="checkbox" id="imdb_imdbtaxonomydirector" name="imdb_imdbtaxonomydirector" value="<?php if ($imdbOptionsw['imdbtaxonomydirector'] == "1") { echo '0'; } else { echo '1'; }?>" />
				<label for="imdb_imdbtaxonomydirector">
					<?php if ($imdbOptionsw['imdbtaxonomydirector'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Directors', 'imdb'); echo '</span>'; } else { ?><?php  esc_html_e( 'Directors', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				</label> 
			</td>
		</tr>

		<tr>
			<td width="10%">&nbsp;</td>
			<td width="27%">
				<input type="checkbox" id="imdb_imdbtaxonomygenre" name="imdb_imdbtaxonomygenre" value="<?php if ($imdbOptionsw['imdbtaxonomygenre'] == "1") { echo '0'; } else { echo '1'; }?>" />
				<label for="imdb_imdbtaxonomygenre">
					<?php if ($imdbOptionsw['imdbtaxonomygenre'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Genres', 'imdb'); echo '</span>'; } else { ?><?php  esc_html_e( 'Genres', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				</label> 
			</td>

			<td width="27%">
				<input type="checkbox" id="imdb_imdbtaxonomylanguage" name="imdb_imdbtaxonomylanguage" value="<?php if ($imdbOptionsw['imdbtaxonomylanguage'] == "1") { echo '0'; } else { echo '1'; }?>" />
				<label for="imdb_imdbtaxonomylanguage">
					<?php if ($imdbOptionsw['imdbtaxonomylanguage'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Languages', 'imdb'); echo '</span>'; } else { ?><?php  esc_html_e( 'Languages', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				</label> 
			</td>
			<td width="27%">
				<input type="checkbox" id="imdb_imdbtaxonomyproducer" name="imdb_imdbtaxonomyproducer" value="<?php if ($imdbOptionsw['imdbtaxonomyproducer'] == "1") { echo '0'; } else { echo '1'; }?>" />
				<label for="imdb_imdbtaxonomyproducer">
					<?php if ($imdbOptionsw['imdbtaxonomyproducer'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Producers', 'imdb'); echo '</span>'; } else { ?><?php  esc_html_e( 'Producers', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				</label> 
			</td>
		</tr>

		<tr>
			<td width="10%">&nbsp;</td>
			<td width="27%">
				<input type="checkbox" id="imdb_imdbtaxonomytitle" name="imdb_imdbtaxonomytitle" value="<?php if ($imdbOptionsw['imdbtaxonomytitle'] == "1") { echo '0'; } else { echo '1'; }?>" />
				<label for="imdb_imdbtaxonomytitle">
					<?php if ($imdbOptionsw['imdbtaxonomytitle'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Titles', 'imdb'); echo '</span>'; } else { ?><?php  esc_html_e( 'Titles', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				</label> 
			</td>
			<td width="27%">
				<input type="checkbox" id="imdb_imdbtaxonomywriter" name="imdb_imdbtaxonomywriter" value="<?php if ($imdbOptionsw['imdbtaxonomywriter'] == "1") { echo '0'; } else { echo '1'; }?>" />
				<label for="imdb_imdbtaxonomywriter">
					<?php if ($imdbOptionsw['imdbtaxonomywriter'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Writers', 'imdb'); echo '</span>'; } else { ?><?php  esc_html_e( 'Writers', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				</label> 
			</td>
			<td width="27%">				
					<input type="checkbox" id="imdb_imdbtaxonomykeywords" name="imdb_imdbtaxonomykeywords" value="<?php if ($imdbOptionsw['imdbtaxonomykeywords'] == "1") { echo '0'; } else { echo '1'; }?>" />
				<label for="imdb_imdbtaxonomykeywords">
					<?php if ($imdbOptionsw['imdbtaxonomykeywords'] == "1") { echo '<span class="admin-option-selected">'; esc_html_e( 'Keywords', 'imdb'); echo '</span>'; } else { ?><?php  esc_html_e( 'Keywords', 'imdb'); echo '&nbsp;&nbsp;'; } ?>
				</label> 
			</td>
		</tr>
<?php } //end check taxonomy option
	} 






		if ($_GET['widgetoption'] == "order")  { 	// Order ?>
		<?php //-------------------------------------------------------------------=[Order]=- ?>		

		<tr>
			<td class="titresection"><?php esc_html_e( 'Position of data', 'imdb'); ?></td>
		</tr>
		<tr>
			<td width="30%" align="right" class="imdblt_valign_middle">
				<div class="explain">
					<?php esc_html_e( 'You can select the order for the information selected from "what to display" section. Select first the movie detail you want to move, use "up" or "down" to reorder IMDb Link Transformer display. Once you are happy with the new layout, click on "update settings" to keep it.', 'imdb'); ?>
					<br /><br />
					<?php esc_html_e( '"Source" movie detail cannot be selected; if it is selected from "what to display" section, it will always appear after others movie details', 'imdb'); ?>
				</div>
			</td>
			<td width="40%" align="center" class="imdblt_valign_middle">
				<select id="imdbwidgetorderContainer" name="imdbwidgetorderContainer[]" size="10" class="imdbwidgetorderContainer" multiple>
<?php 
 					foreach ($imdbOptionsw['imdbwidgetorder'] as $key=>$value) {
						if (!empty ( $key ) && ( $key ) != "source"  ) { // to eliminate empty keys, but also "source" which will always stays at the end (technical limitation, data outside the imdb-movie.inc.php loop)
							echo "\t\t\t\t\t<option value='".$key."'";
							if ($imdbOptionsw["imdbwidget$key"] != 1 ) { // search if "imdbwidget'title'" (ie) is activated
								echo ' label="'.$key.' (unactivated)">'.$key;
							} else { 
								echo ' label="'.$key.'">'.$key; 
							}
								echo "</option>\n"; 
						}
				      	}
				?>				</select>
			</td>

			<td width="30%" align="left" class="imdblt_valign_middle">

				<?php esc_html_e( 'Move selected movie detail:', 'imdb') ?><br />
				<input type="button" value="up" name="movemovieup" id="movemovieup" data-moveform="-1" /> 
				
				<input type="button" value="down" name="movemoviedown" id="movemoviedown" data-moveform="+1" />
				<? 
				// add "empty", to eliminate false submissions which could crush database values ?>	
				<input type="hidden" name="imdb_imdbwidgetorder" id="imdb_imdbwidgetorder" value="" class="imdblt_hidden" />

			</td>
		</tr>

<?php	} 
		if ($_GET['widgetoption'] == "misc")  { 	// Misc ?>
		<?php //-------------------------------------------------------------------=[Misc]=- ?>		

		<tr>
			<td colspan="3" class="titresection"><?php esc_html_e( 'Misc', 'imdb'); ?></td>
		</tr>
		
		<tr>
			<td width="33%">
				<?php esc_html_e( 'Remove all links?', 'imdb'); ?>&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" id="imdb_imdblinkingkill_yes" name="imdb_imdblinkingkill" value="1" <?php if ($imdbOptionsw['imdblinkingkill'] == "1") { echo 'checked="checked"'; }?> />
				<label for="imdb_imdblinkingkill_yes"><?php esc_html_e( 'Yes', 'imdb'); ?></label><input type="radio" id="imdb_imdblinkingkill_no" name="imdb_imdblinkingkill" value="" <?php if ($imdbOptionsw['imdblinkingkill'] == 0) { echo 'checked="checked"'; } ?>/><label for="imdb_imdblinkingkill_no"><?php esc_html_e( 'No', 'imdb'); ?></label>	
			</td>
			<td width="33%">
				<?php esc_html_e( 'Auto widget?', 'imdb'); ?>&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" id="imdb_imdbautopostwidget_yes" name="imdb_imdbautopostwidget" value="1" <?php if ($imdbOptionsw['imdbautopostwidget'] == "1") { echo 'checked="checked"'; }?> />
				<label for="imdb_imdbautopostwidget_yes"><?php esc_html_e( 'Yes', 'imdb'); ?></label><input type="radio" id="imdb_imdbautopostwidget_no" name="imdb_imdbautopostwidget" value="" <?php if ($imdbOptionsw['imdbautopostwidget'] == 0) { echo 'checked="checked"'; } ?>/><label for="imdb_imdbautopostwidget_no"><?php esc_html_e( 'No', 'imdb'); ?></label>
			</td>
			<td width="33%">
			</td>
		</tr>
		<tr>
			<td class="td-aligntop">				
				<div class="explain">
				<?php esc_html_e( "Remove all links (popup and external ones) which are automatically added. Especially made for people who are not interested in popup function, but it will remove every single HTML link too.", 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> <?php esc_html_e( 'No', 'imdb'); ?></div>
			</td>
			<td class="td-aligntop">
				<div class="explain">
				<?php esc_html_e( "Add automatically a widget according to post title. If 'imdb-movie-widget' or 'imdb-movie-widget-bymid' have also been added to post, the auto widget will be displayed before them.", 'imdb'); ?> <br /><?php esc_html_e( 'Default:','imdb');?> <?php esc_html_e( 'No', 'imdb'); ?></div>
			</td>
			<td class="td-aligntop">
			</td>
		</tr>
<?php	} // end of misc subsection ?>


	</table>
	</div>
	
	<?php //------------------------------------------------------------------ =[Submit selection]=- ?>
	<div class="submit submit-imdb" align="center">
		<?php wp_nonce_field('reset_imdbwidgetSettings_check', 'reset_imdbwidgetSettings_check'); //check that data has been sent only once ?>
		<input type="submit" class="button-primary" name="reset_imdbwidgetSettings" value="<?php esc_html_e( 'Reset settings', 'imdb') ?>" />
		<?php wp_nonce_field('update_imdbwidgetSettings_check', 'update_imdbwidgetSettings_check', false); //check that data has been sent only once -- don't send _wp_http_referer twice, already sent with first wp_nonce_field -> 3rd option to "false" ?>
		<input type="submit" class="button-primary" id="update_imdbwidgetSettings" name="update_imdbwidgetSettings" value="<?php esc_html_e( 'Update settings', 'imdb') ?>" />
	</div>
	<br />
</div>
