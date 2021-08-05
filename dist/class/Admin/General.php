<?php
/**
 * Genral options class
 * Child of Admin
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       1.0
 *
 * @TODO: rewrite and mainstream the class
 */

namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die('You can not call directly this page');
}

class General extends \Lumiere\Admin {

	private $allowed_html_for_esc_html_functions = [
		'strong',
		'br',
	];

	/* message notification options */
	private $messages = array(
		'highslide_success' => 'Highslide successfully installed!',
		'highslide_failure' => 'Highslide installation failed!',
		'highslide_down' => 'Website to download Highslide is currently down, please try again later.',
		'highslide_website_unkown' => 'Website variable is not set.'
	);

	function __construct() {

		// Construct parent class
		parent::__construct();

		// Enter in debug mode
		if ((isset($this->imdb_admin_values['imdbdebug'])) && ($this->imdb_admin_values['imdbdebug'] == "1")){

			// Start the class Utils to activate debug -> already started in admin_pages
			$this->utilsClass->lumiere_activate_debug($this->imdb_admin_values, NULL, NULL);
		}

		// Display the page
		$this->lumiere_general_layout();

	}


	/* Display the layout
	 *
	 *
	 */
	private function lumiere_general_layout () { 

		if (current_user_can( 'manage_options' ) ) { 

			echo $this->lumiere_general_head();
			echo $this->lumiere_general_display_submenu();
			echo $this->lumiere_general_display_body() ;

		} 
 
	}

	/*
	 *  Display head
	 *
	 */
	private function lumiere_general_head() { 

		if ((isset($_GET['msg'])) && array_key_exists( sanitize_key( $_GET['msg'] ), $this->messages ) ){
			switch (sanitize_text_field( $_GET['msg'] )) {
				// Message for success
				case "highslide_success":
					echo $this->utilsClass->lumiere_notice(1, esc_html__( $this->messages["highslide_success"], 'lumiere-movies') );
					break;
				// Message for failure
				case "highslide_failure":
					echo $this->utilsClass->lumiere_notice(3, esc_html__( $this->messages["highslide_failure"] , 'lumiere-movies') . " " .  esc_html__( 'Your folder might be protected. Download highslide manually', 'lumiere-movies')." <a href='". esc_url ( \Lumiere\Settings::IMDBBLOGHIGHSLIDE ) ."'>".esc_html__("here", 'lumiere-movies')."</a> ".esc_html__("and extract the zip into" ) . "<br />" .  esc_url( $this->imdb_admin_values['imdbpluginpath'] ."js/" ) );
					break;
				// Message for website down
				case "highslide_down":
					echo $this->utilsClass->lumiere_notice(3, esc_html__( $this->messages["highslide_down"] , 'lumiere-movies')  );
					break;
				// Message for website unkown
				case "highslide_website_unkown":
					echo $this->utilsClass->lumiere_notice(3, esc_html__( $this->messages["highslide_website_unkown"] , 'lumiere-movies')  );
					break;
			}

		}


		if ( (isset($_POST['update_imdbSettings'])) && check_admin_referer('options_general_check', 'options_general_check') ) { //--------------------save data selected

			// Check if $_POST['imdburlstringtaxo'] and $_POST['imdburlpopups'] are identical, as they can't
	$post_imdb_imdburlstringtaxo = isset($_POST['imdb_imdburlstringtaxo']) ? filter_var($_POST['imdb_imdburlstringtaxo'], FILTER_SANITIZE_STRING) : NULL;
	$post_imdb_imdburlpopups = isset($_POST['imdb_imdburlpopups']) ? filter_var($_POST['imdb_imdburlpopups'], FILTER_SANITIZE_STRING) : NULL;

			if (
				(isset($post_imdb_imdburlstringtaxo)) &&
	(str_replace('/','',$post_imdb_imdburlstringtaxo) == str_replace('/','',$post_imdb_imdburlpopups) ) || isset($this->imdb_admin_values['imdburlpopups']) && (str_replace('/','',$post_imdb_imdburlstringtaxo) == str_replace('/','',$this->imdb_admin_values['imdburlpopups']) )
										||
				(isset($post_imdb_imdburlpopups)) &&
	(str_replace('/','',$post_imdb_imdburlpopups) == str_replace('/','',$post_imdb_imdburlstringtaxo) ) || isset($this->imdb_admin_values['imdburlstringtaxo']) && (str_replace('/','',$post_imdb_imdburlpopups) == str_replace('/','',$this->imdb_admin_values['imdburlstringtaxo']) )
			) {

				echo $this->utilsClass->lumiere_notice(3, esc_html__( 'Wrong values. You can not select the same URL string for taxonomy pages and popups.', 'lumiere-movies') );
				echo $this->utilsClass->lumiere_notice(1, '<a href="'.wp_get_referer() .'">'. esc_html__( 'Go back', 'lumiere-movies') .'</a>');
				exit();
			}

			foreach ($_POST as $key=>$postvalue) {
				$key_sanitized = sanitize_key($key);
				$keynoimdb = str_replace ( "imdb_", "", $key_sanitized);
				if (isset($_POST["$key_sanitized"])) {
					$this->imdb_admin_values["$keynoimdb"] = sanitize_text_field( $_POST["$key_sanitized"] );
				}
			}

			// update options
			update_option($this->configClass->imdbAdminOptionsName, $this->imdb_admin_values);

			// flush rewrite rules for matches the new $this->imdb_admin_values['imdbplugindirectory'] path
			add_action('admin_init', function (){ flush_rewrite_rules(); }, 0);

			// display message on top
			echo $this->utilsClass->lumiere_notice(1, '<strong>'. esc_html__( 'Options saved.', 'lumiere-movies') .'</strong>');

			// Display a refresh link otherwise refreshed data is not seen
			if (!headers_sent()) {
				/* 2021 07 06 Shouldn't do anything here, to be removed
				// header("Refresh: 0;url=".$_SERVER[ "REQUEST_URI"]."&reset=true", false);
				wp_safe_redirect( wp_get_referer() );
				exit();*/
			} else {
				echo $this->utilsClass->lumiere_notice(1, '<a href="'.wp_get_referer() .'">'. esc_html__( 'Go back', 'lumiere-movies') .'</a>');
				exit();
			}


		} elseif ( (isset($_POST['reset_imdbSettings'])) && check_admin_referer('options_general_check', 'options_general_check') ){ //---------------------reset options selected

			delete_option($this->configClass->imdbAdminOptionsName);

			// display message on top
			echo $this->utilsClass->lumiere_notice(1, '<strong>'. esc_html__( 'Options reset.', 'lumiere-movies') .'</strong>');

			// Display a refresh link otherwise refreshed data is not seen
			if (!headers_sent()) {
				/* 2021 07 06 Shouldn't do anything here, to be removed
				// header("Refresh: 0;url=".$_SERVER[ "REQUEST_URI"]."&reset=true", false);
				wp_safe_redirect( wp_get_referer() );
				exit(); */
			} else {
				echo $this->utilsClass->lumiere_notice(1, '<a href="'.wp_get_referer() .'">'. esc_html__( 'Go back', 'lumiere-movies') .'</a>');
				exit();
			}

		}
	}

	/*
	 *  Display submenu
	 *
	 */
	private function lumiere_general_display_submenu() { ?>

<div id="tabswrap">
	<div class="imdblt_double_container lumiere_padding_five">
		<div class="lumiere_flex_auto lumiere_align_center"><img src="<?php echo esc_url( $this->configClass->lumiere_pics_dir . 'menu/admin-general-path.png'); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e( "Paths & Layout", 'lumiere-movies');?>" href="<?php echo esc_url(admin_url() . "admin.php?page=lumiere_options&generaloption=base" ); ?>"><?php esc_html_e( 'Layout', 'lumiere-movies'); ?></a></div>

		<div class="lumiere_flex_auto lumiere_align_center">&nbsp;&nbsp;<img src="<?php echo esc_url( $this->configClass->lumiere_pics_dir . 'menu/admin-general-advanced.png'); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e( "Advanced", 'lumiere-movies');?>" href="<?php echo esc_url (admin_url() . "admin.php?page=lumiere_options&generaloption=advanced" ); ?>"><?php esc_html_e( "Advanced", 'lumiere-movies'); ?></a></div>
	</div>
</div>


<div id="poststuff" class="metabox-holder">


<?php	}


	/*
	 *  Display the body
	 *
	 */
	private function lumiere_general_display_body() { 

	echo '<form method="post" id="imdbconfig_save" name="imdbconfig_save" action="' . $_SERVER[ "REQUEST_URI" ] . '">';

	if ( ( (isset($_GET['generaloption'])) && ($_GET['generaloption'] == "base") ) || (!isset($_GET['generaloption'] )) ) { 	////////// Paths & Layout section  ?>

		<div class="intro_cache"><?php esc_html_e( "The following options usually do not need further action. Nevertheless, Lumière! can be widely customized to match your needs.", 'lumiere-movies'); ?></div>


		<div class="inside imblt_border_shadow">
			<h3 class="hndle" id="layout" name="layout"><?php esc_html_e( 'Layout', 'lumiere-movies'); ?></h3>
		</div>

		<div class="inside">
			<div class="inside imblt_border_shadow">

			<?php //------------------------------------------------------------------ =[Popup]=- ?>

				<div id="popup" class="titresection">
					<img src="<?php echo esc_url( $this->configClass->lumiere_pics_dir . 'admin-title-popup.png'); ?>" width="80" height="80" align="absmiddle" />&nbsp;&nbsp;&nbsp;
					<?php esc_html_e( 'Popup', 'lumiere-movies'); ?>
				</div>

				<div class="lumiere_flex_container">
					<div class="lumiere_flex_auto imdblt_padding_five">

						<label for="imdb_popupLarg"><?php esc_html_e( 'Width', 'lumiere-movies'); ?></label><br /><br />
						<input type="text" name="imdb_popupLarg" size="5" value="<?php esc_html_e( apply_filters('format_to_edit',$this->imdb_admin_values['popupLarg']), 'lumiere-movies') ?>" >

						<div class="explain"> <?php esc_html_e( 'Popup width, in pixels', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?>"540"</div>

					</div>
					<div class="lumiere_flex_auto imdblt_padding_five">

						<label for="imdb_popupLong"><?php esc_html_e( 'Height', 'lumiere-movies'); ?></label><br /><br />
						<input type="text" name="imdb_popupLong" size="5" value="<?php esc_html_e( apply_filters('format_to_edit',$this->imdb_admin_values['popupLong']), 'lumiere-movies') ?>" >

						<div class="explain"> <?php esc_html_e( 'Popup height, in pixels', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?>"350"</div>

					</div>

					<div class="lumiere_flex_auto imdblt_padding_five">

						<label for="imdb_imdbpopuptheme"><?php esc_html_e( 'Theme color', 'lumiere-movies'); ?></label><br /><br />

						<select name="imdb_imdbpopuptheme">
							<option <?php if( ($this->imdb_admin_values['imdbpopuptheme'] == "white") || (empty($this->imdb_admin_values['imdbpopuptheme'])) ) echo 'selected="selected"'; ?>value="white"><?php esc_html_e( 'white (default)', 'lumiere-movies'); ?></option>
							<option <?php if($this->imdb_admin_values['imdbpopuptheme'] == "black") echo 'selected="selected"'; ?>value="black"><?php esc_html_e( 'black', 'lumiere-movies'); ?></option>
							<option <?php if($this->imdb_admin_values['imdbpopuptheme'] == "lightgrey") echo 'selected="selected"'; ?>value="lightgrey"><?php esc_html_e( 'lightgrey', 'lumiere-movies'); ?></option>

						</select>

						<div class="explain"> <?php esc_html_e( 'Popup color theme', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?>"white"</div>

					</div>

					<div class="lumiere_flex_auto imdblt_padding_five">&nbsp;<?php
					
					// If the folder "highslide" exists
					if(is_dir( $this->configClass->lumiere_js_path . 'highslide')) {
						esc_html_e( 'Display highslide popup', 'lumiere-movies');
						echo '
						<input type="hidden" id="imdb_imdbpopup_highslide_no" name="imdb_imdbpopup_highslide" value="0" />
						<input type="checkbox" id="imdb_imdbpopup_highslide_yes" name="imdb_imdbpopup_highslide" value="1" ';
	;
						if ($this->imdb_admin_values['imdbpopup_highslide'] == 1) { echo 'checked="checked"'; }
						echo '/>';

						echo '<div class="explain">' . esc_html__( 'Highslide popup is a more stylished popup, and allows to open movie details directly in the webpage instead of opening a new window.', 'lumiere-movies'). '<br />'. esc_html__( 'Default:','lumiere-movies') . esc_html__( 'Yes', 'lumiere-movies') .'</div>';

					// No "highslide" folder is found
					} else {
						// Say so!
						echo $this->utilsClass->lumiere_notice(4, '<span class="imdblt_red_bold">'.esc_html__('Warning! No Highslide folder was found.', 'lumiere-movies') .'</span>');
						echo "<br />";

						// Automatic download deactivated as per Wordpress's plugin staff request
						// echo "<a href='". esc_url( $this->imdb_admin_values['imdbplugindirectory'] . "inc/highslide_download.php?highslide=yes") . "' title='".esc_html__('Click here to install Highslide', 'lumiere-movies') ."'><img src='".esc_url($this->imdb_admin_values['imdbplugindirectory'] . "pics/admin-general-install-highslide.png")."' align='absmiddle' />&nbsp;&nbsp;".esc_html__('Install automatically Highslide', 'lumiere-movies') .'</a><br /><br />';

						// Add a link to highslide website
						echo '<a href="http://highslide.com/" title="' . esc_html__('Click here to visit Highslide website', 'lumiere-movies') .'"><img src="'.esc_url( $this->configClass->lumiere_pics_dir . 'menu/admin-general-install-highslide.png') . '" align="absmiddle" />&nbsp;&nbsp;'.esc_html__('Get Highslide JS library', 'lumiere-movies') . '</a><br /><br />';
					}

		?>
					</div>

				</div>


			<?php //------------------------------------------------------------------ =[Theme taxo/inside post/widget]=- ?>

				<div id="plainpages" class="titresection">
					<img src="<?php echo esc_url( $this->configClass->lumiere_pics_dir . 'admin-title-taxonomy.png'); ?>" width="80" height="80" align="absmiddle" />&nbsp;&nbsp;&nbsp;
					<?php esc_html_e( 'Plain pages', 'lumiere-movies'); ?>
				</div>


				<div class="lumiere_flex_container_content_third lumiere_padding_five">

					<label for="imdb_imdbintotheposttheme"><?php esc_html_e( 'Theme color', 'lumiere-movies'); ?></label><br /><br />

					<select name="imdb_imdbintotheposttheme">
						<option value="grey"<?php if( ($this->imdb_admin_values['imdbintotheposttheme'] == "grey") || (empty($this->imdb_admin_values['imdbintotheposttheme'])) ) echo ' selected="selected"'; ?>><?php esc_html_e( 'grey (default)', 'lumiere-movies'); ?></option>
						<option value="white"<?php if($this->imdb_admin_values['imdbintotheposttheme'] == "white") echo ' selected="selected"'; ?>><?php esc_html_e( 'white', 'lumiere-movies'); ?></option>
						<option value="black"<?php if($this->imdb_admin_values['imdbintotheposttheme'] == "black") echo ' selected="selected"'; ?>><?php esc_html_e( 'black', 'lumiere-movies'); ?></option>
					</select>

					<div class="explain"> <?php esc_html_e( 'Inside the post/widget/taxonomy color theme', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?>"grey"</div>

				</div>


			<?php //------------------------------------------------------------------ =[Cover picture]=- ?>

			<div id="coverpicture" class="titresection">
				<img src="<?php echo esc_url( $this->configClass->lumiere_pics_dir . 'cover.jpg'); ?>" height="80" align="absmiddle" />&nbsp;&nbsp;&nbsp;
				<?php esc_html_e( 'Cover picture', 'lumiere-movies'); ?>
			</div>

			<div class="lumiere_flex_container">
				<div class="lumiere_flex_container_content_third imdblt_padding_five">

					<label for="imdb_imdbcoversize"><?php esc_html_e( 'Display only thumbnail', 'lumiere-movies'); ?>&nbsp;

					<input type="hidden" id="imdb_imdbcoversize_no" name="imdb_imdbcoversize" value="0" data-checkbox_activate="imdb_imdbcoversizewidth_id" />

					<input type="checkbox" id="imdb_imdbcoversize_yes" name="imdb_imdbcoversize" value="1" data-checkbox_activate="imdb_imdbcoversizewidth_id" <?php if ($this->imdb_admin_values['imdbcoversize'] == "1") { echo 'checked="checked"'; }?> />

					<div class="explain"><?php esc_html_e( 'Whether to display a thumbnail or a large image cover for movies inside a post or a widget. Untick the box to open a new option and chose the cover picture width.', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

				</div>
				<div class="lumiere_flex_container_content_third imdblt_padding_five" id="imdb_imdbcoversizewidth_id">

					<label for="imdb_imdbcoversizewidth"><?php esc_html_e( 'Size', 'lumiere-movies'); ?></label><br /><br />

					<input type="text" name="imdb_imdbcoversizewidth" id="imdb_imdbcoversizewidth" size="5" value="<?php esc_html_e( apply_filters('format_to_edit',$this->imdb_admin_values['imdbcoversizewidth']), 'lumiere-movies'); ?>" />

					<div class="explain"><?php esc_html_e( 'Size of the imdb cover picture. The value will correspond to the width in pixels. Delete any value to get maximum width.', 'lumiere-movies'); ?> <br /><?php esc_html_e( 'Default:','lumiere-movies');?> "100"</div>

				</div>
			</div>

		</div>

		<br />
		<br />


	<?php	}
 				//////////////// Advanced section  
		if ( (isset($_GET['generaloption'])) && ($_GET['generaloption'] == "advanced") ) {?>

		<div class="intro_cache"><?php esc_html_e( "The options hereafter can break a lot of things. Edit them only if you know what you are doing.", 'lumiere-movies'); ?></div>

		<div class="inside">

			<?php //------------------------------------------------------------------ =[Search]=- ?>

		<div class="imblt_border_shadow">
			<h3 class="hndle" id="searchpart" name="searchpart"><?php esc_html_e( 'Search', 'lumiere-movies'); ?></h3>
		</div>

		<div class="inside imblt_border_shadow">

			<div class="lumiere_display_flex lumiere_flex_make_responsive">

				<div class="lumiere_flex_auto imdblt_padding_five">

					<label for="imdb_imdblanguage"><?php esc_html_e( 'Search language', 'lumiere-movies'); ?></label><br /><br />
					<select name="imdb_imdblanguage">
						<option <?php if( ($this->imdb_admin_values['imdblanguage'] == "en") || (empty($this->imdb_admin_values['imdblanguage'])) ) echo 'selected="selected"'; ?> value="en"><?php esc_html_e( 'English', 'lumiere-movies'); ?></option>
						<option <?php if($this->imdb_admin_values['imdblanguage'] == "fr,en") echo 'selected="selected"'; ?>value="fr,en"><?php esc_html_e( 'French', 'lumiere-movies'); ?></option>
						<option <?php if($this->imdb_admin_values['imdblanguage'] == "de,en") echo 'selected="selected"'; ?>value="de,en"><?php esc_html_e( 'German', 'lumiere-movies'); ?></option>
						<option <?php if($this->imdb_admin_values['imdblanguage'] == "es,en") echo 'selected="selected"'; ?>value="es,en"><?php esc_html_e( 'Spanish', 'lumiere-movies'); ?></option>
					</select>

					<div class="explain"><?php esc_html_e( 'Language used for the movie search. Very usefull for a non-English blog using Lumière! as a widget.', 'lumiere-movies'); ?>
						<br /><br />
						<?php esc_html_e( 'Default:','lumiere-movies'); ?> "English"
					</div>
				</div>

				<div class="lumiere_flex_auto imdblt_padding_five">

					<label for="imdb_imdbseriemovies"><?php esc_html_e( 'Search categories', 'lumiere-movies'); ?></label><br /><br />
					<select name="imdb_imdbseriemovies">
						<option <?php if( ($this->imdb_admin_values['imdbseriemovies'] == "movies+series") || (empty($this->imdb_admin_values['imdbSerieMovies'])) ) echo 'selected="selected"'; ?> value="movies+series"><?php esc_html_e( 'Movies and series', 'lumiere-movies'); ?></option>
						<option <?php if($this->imdb_admin_values['imdbseriemovies'] == "movies") echo 'selected="selected"'; ?>value="movies"><?php esc_html_e( 'Movies only', 'lumiere-movies'); ?></option>
						<option <?php if($this->imdb_admin_values['imdbseriemovies'] == "series") echo 'selected="selected"'; ?>value="series"><?php esc_html_e( 'Series only', 'lumiere-movies'); ?></option>
						<option <?php if($this->imdb_admin_values['imdbseriemovies'] == "videogames") echo 'selected="selected"'; ?>value="videogames"><?php esc_html_e( 'Video games only', 'lumiere-movies'); ?></option>
					</select>

					<div class="explain"><?php esc_html_e( 'What type to use for the search, such as movies, series (for TV Shows), and videogames.', 'lumiere-movies'); ?>
						<br /><br />
						<?php esc_html_e( 'Default:','lumiere-movies'); ?> "Movies and series"
					</div>
				</div>

				<div class="lumiere_flex_auto imdblt_padding_five">

					<label for="imdb_imdbmaxresults"><?php esc_html_e( 'Limit number of results', 'lumiere-movies'); ?></label>
					<br />
					<br />

					<input type="text" name="imdb_imdbmaxresults" id="imdb_imdbmaxresults" size="5" value="<?php esc_html_e( apply_filters('format_to_edit',$this->imdb_admin_values['imdbmaxresults']), 'lumiere-movies') ?>" />

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


		<div class="inside imblt_border_shadow">
			<h3 class="hndle" id="miscpart" name="miscpart"><?php esc_html_e( 'Misc', 'lumiere-movies'); ?></h3>
		</div>

		<div class="inside imblt_border_shadow">

			<div class="lumiere_flex_container">

				<div id="imdbwordpress_bigmenu" class="lumiere_flex_auto lumiere_padding_five">

					<?php esc_html_e( 'Left menu for Lumière options', 'lumiere-movies'); ?>&nbsp;

					<input type="hidden" id="imdb_imdbwordpress_bigmenu_no" name="imdb_imdbwordpress_bigmenu" value="0" <?php if ($this->imdb_admin_values['imdbwordpress_bigmenu'] == "1") { echo 'checked="checked"'; }?> />

					<input type="checkbox" id="imdb_imdbwordpress_bigmenu_yes" name="imdb_imdbwordpress_bigmenu" value="1" <?php if ($this->imdb_admin_values['imdbwordpress_bigmenu'] == "1") { echo 'checked="checked"'; }?> />

					<div class="explain"><?php esc_html_e( "If enabled, Lumiere options are displayed in a dedicated menu on the left panel instead of being displayed in the settings menu.", 'lumiere-movies'); ?> <br /><br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

				</div>
				<div id="imdbwordpress_tooladminmenu" class="lumiere_flex_auto lumiere_padding_five">

					<?php esc_html_e( 'Top menu for Lumière options', 'lumiere-movies'); ?>&nbsp;

					<input type="hidden" id="imdb_imdbwordpress_tooladminmenu_no" name="imdb_imdbwordpress_tooladminmenu" value="0" <?php if ($this->imdb_admin_values['imdbwordpress_bigmenu'] == "1") { echo 'checked="checked"'; }?> />

					<input type="checkbox" id="imdb_imdbwordpress_tooladminmenu_yes" name="imdb_imdbwordpress_tooladminmenu" value="1" <?php if ($this->imdb_admin_values['imdbwordpress_tooladminmenu'] == "1") { echo 'checked="checked"'; }?> />

					<div class="explain"><?php esc_html_e( "If activated, Lumière options are displayed in a  top menu. Not recommended f you have many plugins already occupying that area.", 'lumiere-movies'); ?> <br /><br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'Yes', 'lumiere-movies'); ?></div>

				</div>

				<div id="imdbtaxonomy" class="lumiere_flex_auto lumiere_padding_five">

					<?php esc_html_e( 'Use taxonomy', 'lumiere-movies'); ?>&nbsp;

					<input type="hidden" id="imdb_imdbtaxonomy_no" name="imdb_imdbtaxonomy" value="0" />

					<input type="checkbox" id="imdb_imdbtaxonomy_yes" name="imdb_imdbtaxonomy" value="1" <?php if ($this->imdb_admin_values['imdbtaxonomy'] == "1") { echo 'checked="checked"'; }?> />

					<div class="explain"><?php esc_html_e( 'This will add taxonomy terms found for the movie when display a page with a widget or a into a post. Taxonomy allows to group posts by a series of chosen terms, as explained in', 'lumiere-movies') ?> <a href="http://codex.wordpress.org/WordPress_Taxonomy">taxonomy</a>. <?php esc_html_e( 'Taxonomy terms are uninstalled when removing the plugin if you selected not to keep the settings upon uninstall.', 'lumiere-movies'); ?> <br /><br /><?php esc_html_e( 'Default:','lumiere-movies'); ?> <?php esc_html_e( 'Yes', 'lumiere-movies'); ?> <?php esc_html_e( '(Activated for "genre" taxonomy only)', 'lumiere-movies'); ?></div>

				</div>

				<div id="imdblinkingkill" class="lumiere_flex_auto lumiere_padding_five">

					<?php esc_html_e( 'Remove all links?', 'lumiere-movies'); ?>&nbsp;

					<input type="hidden" id="imdb_imdblinkingkill_no" name="imdb_imdblinkingkill" value="0" />

					<input type="checkbox" id="imdb_imdblinkingkill_yes" name="imdb_imdblinkingkill" value="1" <?php if ($this->imdb_admin_values['imdblinkingkill'] == "1") { echo 'checked="checked"'; }?> />

					<div class="explain"><?php esc_html_e( "Remove all links (popup and external ones) which are automatically added. Usefull for users who are not interested in popup function. Please note that it will remove every single HTML link as well, such as the the links to the official IMDb website.", 'lumiere-movies'); ?><br /><br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

				</div>

				<div id="imdbautopostwidget" class="lumiere_flex_auto lumiere_padding_five">

					<?php esc_html_e( 'Auto widget?', 'lumiere-movies'); ?>&nbsp;

					<input type="hidden" id="imdb_imdbautopostwidget_no" name="imdb_imdbautopostwidget" value="0" />

					<input type="checkbox" id="imdb_imdbautopostwidget_yes" name="imdb_imdbautopostwidget" value="1" <?php if ($this->imdb_admin_values['imdbautopostwidget'] == "1") { echo 'checked="checked"'; }?> />

					<div class="explain"><?php esc_html_e( "Add automatically a widget according to your post title. If regular widgets have been added to post too, the auto widget will be displayed before them. Usefull if blog a lot about movies; if a query does not bring any result with the post title, nothing is displayed.", 'lumiere-movies'); ?><br /><br /><?php esc_html_e( 'Default:','lumiere-movies');?><?php esc_html_e( 'No', 'lumiere-movies'); ?></div>

				</div>

				<div id="imdbkeepsettings" class="lumiere_flex_auto lumiere_padding_five">

					<?php esc_html_e( 'Keep settings upon deactivation', 'lumiere-movies'); ?>&nbsp;

					<input type="hidden" id="imdb_imdbkeepsettings_no" name="imdb_imdbkeepsettings" value="0" />

					<input type="checkbox" id="imdb_imdbkeepsettings_yes" name="imdb_imdbkeepsettings" value="1" <?php if ($this->imdb_admin_values['imdbkeepsettings'] == "1") { echo 'checked="checked"'; }?> />

					<div class="explain"><?php esc_html_e( "Whether to keep or delete Lumière! settings upon plugin deactivation. Prevent from deleting the taxonomy terms and the cache too.", 'lumiere-movies'); ?><br /><br /><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'Yes', 'lumiere-movies'); ?></div>

				</div>

				<div id="imdbdebug" class="lumiere_flex_auto lumiere_padding_five">

					<?php esc_html_e( 'Debug Lumière!', 'lumiere-movies'); ?>&nbsp;

					<input type="hidden" id="imdb_imdbdebug_no" class="activatehidesectionRemove" name="imdb_imdbdebug" value="0" <?php if ($this->imdb_admin_values['imdbdebug'] == 0) { echo 'checked="checked"'; } ?>  />

					<input type="checkbox" id="imdb_imdbdebug_yes" class="activatehidesectionAdd" name="imdb_imdbdebug" value="1" <?php if ($this->imdb_admin_values['imdbdebug'] == "1") { echo 'checked="checked"'; }?> />

					<label for="imdb_imdbdebug"></label>
					<br />
					<br />
					<div class="explain"><?php esc_html_e( 'Use integrated debugging functions.','lumiere-movies');?></div>
				</div>

				<div class="lumiere_flex_auto lumiere_padding_five hidesectionOfCheckbox">


					<?php esc_html_e( '[Extra debugging options]', 'lumiere-movies'); ?><br /><br />

					<div class="lumiere_padding_top_bottom_ten">
						<?php esc_html_e( 'Debug verbosity', 'lumiere-movies'); ?>&nbsp;

						<select name="imdb_imdbdebuglevel">
							<option <?php if( ($this->imdb_admin_values['imdbdebuglevel'] == "DEBUG") || (empty($this->imdb_admin_values['imdbdebuglevel'])) ) echo 'selected="selected"'; ?> value="DEBUG">Debug</option>
							<option <?php if($this->imdb_admin_values['imdbdebuglevel'] == "INFO") echo 'selected="selected"'; ?>value="INFO">Info</option>
							<option <?php if($this->imdb_admin_values['imdbdebuglevel'] == "NOTICE") echo 'selected="selected"'; ?>value="NOTICE">Notice</option>
							<option <?php if($this->imdb_admin_values['imdbdebuglevel'] == "WARNING") echo 'selected="selected"'; ?>value="WARNING">Warning</option>
							<option <?php if($this->imdb_admin_values['imdbdebuglevel'] == "ERROR") echo 'selected="selected"'; ?>value="ERROR">Error</option>
							<option <?php if($this->imdb_admin_values['imdbdebuglevel'] == "CRITICAL") echo 'selected="selected"'; ?>value="CRITICAL">Critical</option>
							<option <?php if($this->imdb_admin_values['imdbdebuglevel'] == "ALERT") echo 'selected="selected"'; ?>value="ALERT">Alert</option>
							<option <?php if($this->imdb_admin_values['imdbdebuglevel'] == "EMERGENCY") echo 'selected="selected"'; ?>value="EMERGENCY">Emergency</option>
						</select>
						<div class="explain"><?php esc_html_e( 'From lowest to highest verbosity level.','lumiere-movies');?></div>
					</div>

					<div class="lumiere_padding_top_bottom_ten">
						<?php esc_html_e( 'Display debug on screen', 'lumiere-movies'); ?>&nbsp;

						<input type="hidden" id="imdb_imdbdebugscreen_no" name="imdb_imdbdebugscreen" value="0" />

						<input type="checkbox" id="imdb_imdbdebugscreen_yes" name="imdb_imdbdebugscreen" value="1" <?php if ($this->imdb_admin_values['imdbdebugscreen'] == "1") { echo 'checked="checked"'; }?> />

						<div class="explain"><?php esc_html_e( 'Show the debug log on screen (for administrators only).','lumiere-movies');?></div>
					</div>

					<div class="lumiere_padding_top_bottom_ten">
						<?php esc_html_e( 'Save logs', 'lumiere-movies'); ?>&nbsp;

						<input type="hidden" id="imdb_imdbdebuglog_no" data-checkbox_activate="imdb_imdbdebuglogpath_id" name="imdb_imdbdebuglog" value="0" />

						<input type="checkbox" id="imdb_imdbdebuglog_yes" data-checkbox_activate="imdb_imdbdebuglogpath_id" name="imdb_imdbdebuglog" value="1" <?php if ($this->imdb_admin_values['imdbdebuglog'] == "1") { echo 'checked="checked"'; }?> />

						<div id="imdb_imdbdebuglogpath_id" class="lumiere_padding_top_bottom_ten" >
							<label for="imdb_imdbdebuglogpath"><?php esc_html_e( 'Path', 'lumiere-movies'); ?></label>
							<input class="lumiere_border_width_medium imdb_imdbdebuglogpath" type="text" name="imdb_imdbdebuglogpath" value="<?php echo $this->imdb_admin_values['imdbdebuglogpath']; ?>" >

							<div class="explain"><?php esc_html_e( 'Default:','lumiere-movies');?> <?php esc_html_e( 'WordPress default debug log', 'lumiere-movies'); ?></div>
							</div>
						</div>
					</div>
			</div>

		</div>

		<br />
		<br />

		<div class="imblt_border_shadow">
			<h3 class="hndle" id="directories" name="directories"><?php esc_html_e( 'Paths: url & folders', 'lumiere-movies'); ?></h3>
		</div>

		<div class="inside imblt_border_shadow">
			<div class="lumiere_intro_options"><?php esc_html_e('Edit the following values with caution. It can have unattended effects on your WordPress installation.', 'lumiere-movies'); ?></div>
			<br />
			<br />

		<div>
			<?php //------------------------------------------------------------------=[ URL Popups ]=---- ?>
			<div class="lumiere_flex_container">
				<div class="lumiere_flex_container_content_twenty">
					<label for="imdb_imdburlpopups"><?php esc_html_e( 'URL for the popups', 'lumiere-movies'); ?></label>
				</div>
				<div class="lumiere_flex_container_content_eighty">
					<div class="lumiere_align_items_center">
						<?php echo $this->imdb_admin_values['blog_adress']; ?>
						<input type="text" class="lumiere_border_width_medium" name="imdb_imdburlpopups" value="<?php esc_html_e( apply_filters('format_to_edit',$this->imdb_admin_values['imdburlpopups']), 'lumiere-movies') ?>">
					</div>
					<div class="explain"><?php esc_html_e( 'The URL that will be displayed for the movies\' and people\'s popups.', 'lumiere-movies'); ?>
					<br />
					<?php esc_html_e( 'Default:','lumiere-movies');?> "<?php echo "/imdblt/"; ?>"
					<br />
					<br />
					<?php esc_html_e( 'Example: the full URL utilized for the movies\' popups will be:', 'lumiere-movies'); ?>
					<br />
					<?php echo $this->imdb_admin_values['blog_adress'] . $this->imdb_admin_values['imdburlpopups'] . 'film' ; ?>
					<br />
					<br />
					<?php esc_html_e( 'Example: the full URL utilized for the people\'s popup will be:', 'lumiere-movies'); ?>
					<br />
					<?php echo $this->imdb_admin_values['blog_adress'] . $this->imdb_admin_values['imdburlpopups'] . 'person' ; ?>
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
						<?php echo $this->imdb_admin_values['blog_adress']; ?>/
						<input type="text" class="lumiere_border_width_medium" name="imdb_imdburlstringtaxo" value="<?php esc_html_e( apply_filters('format_to_edit',$this->imdb_admin_values['imdburlstringtaxo']), 'lumiere-movies') ?>">
					</div>
					<div class="explain"><?php esc_html_e( 'The URL that will be displayed for the taxonomy\'s pages. Warning! It cannot be identical to the URL of popups above.', 'lumiere-movies'); ?>
					<br />
					<?php esc_html_e( 'Default:','lumiere-movies');?> "<?php echo "imdblt_"; ?>"
					<br />
					<br />
					<?php esc_html_e( 'The full URL utilized for the director taxonomy page will be:', 'lumiere-movies'); ?>
					<br />
					<?php echo $this->imdb_admin_values['blog_adress'] . '/' . $this->imdb_admin_values['imdburlstringtaxo'] . 'director' ; ?>
					</div>
				</div>
			</div>

		</div>
	</div>

	<?php	} // end of advanced section ?>


		<?php //------------------------------------------------------------------ =[Submit selection]=- ?>
		<div class="submit submit-imdb lumiere_sticky_boxshadow lumiere_align_center">
			<?php wp_nonce_field('options_general_check', 'options_general_check');   ?>
			<input type="submit" id="reset_imdbSettings" class="button-primary" name="reset_imdbSettings" value="<?php esc_html_e( 'Reset settings', 'lumiere-movies') ?>" />&nbsp;&nbsp;
			<input type="submit"  id="update_imdbSettings" class="button-primary" name="update_imdbSettings" value="<?php esc_html_e( 'Update settings', 'lumiere-movies') ?>" />
		</div>
		<br />
	</form>
	</div>
<?php	}

}
?>