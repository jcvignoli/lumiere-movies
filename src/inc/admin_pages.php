<?php
 #############################################################################
 # Lumière! Movies WordPress Plugin                                          #
 # written by Lost Highway                                                   #
 # https://www.jcvignoli.com/blog                                            #
 # ------------------------------------------------------------------------- #
 # This program is free software; you can redistribute and/or modify it      #
 # under the terms of the GNU General Public License (see LICENSE)           #
 # ------------------------------------------------------------------------- #
 #       			                                                	#
 #  Function : Print the admin pages options   				     	#
 #       	  			                                    	#
 #											#
 #############################################################################

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die('You can not call directly this page');
}

//Prints out the admin page
function lumiere_admin_pages() {

	if (class_exists("\Lumiere\Settings")) {
		$config = new \Lumiere\Settings();
		$imdb_admin_values = $config->get_imdb_admin_option();
		$imdb_widget_values = $config->get_imdb_widget_option();
		$imdb_cache_values = $config->get_imdb_cache_option();

		// Start utils and logger class if debug is selected
		if ( (isset($config->imdb_admin_values['imdbdebug'])) && ($config->imdb_admin_values['imdbdebug'] == 1) ){

			// Start the class Utils to activate debug
			$utils = new \Lumiere\Utils();

			// Start the logger
			$config->lumiere_start_logger('adminLumiere');

			// Store the class so we can use it later for imdbphp class call
			$logger = $config->loggerclass;

		} 

	}

	//----------------------------------------------------------display the admin settings options ?>

<div class=wrap>

	<h2 class="imdblt_padding_bottom_right_fifteen"><img src="<?php echo esc_url ( $imdb_admin_values['imdbplugindirectory'] . "pics/lumiere-ico80x80.png"); ?>" width="80" height="80" align="absmiddle" />&nbsp;&nbsp;<i>Lumière!</i>&nbsp;<?php esc_html_e( 'admin options', 'lumiere-movies'); ?></h2>

	<div class="subpage">
		<div align="left" class="imdblt_double_container">

			<div class="imdblt_padding_five imdblt_flex_auto">
				<img src="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] . "pics/admin-general.png"); ?>" align="absmiddle" width="16px" />&nbsp;
				<a title="<?php esc_html_e( 'General Options', 'lumiere-movies'); ?>" href="<?php echo esc_url( admin_url() . "admin.php?page=imdblt_options"); ?>"> <?php esc_html_e( 'General Options', 'lumiere-movies'); ?></a>
			</div>

			<?php 	### Data subpage is relative to what is activated ?>

			<div class="imdblt_padding_five imdblt_flex_auto">
				<img src="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] . "pics/admin-widget-inside.png"); ?>" align="absmiddle" width="16px" />&nbsp;


				<a title="<?php esc_html_e( 'Data Management', 'lumiere-movies'); ?>" href="<?php echo esc_url ( admin_url() . "admin.php?page=imdblt_options&subsection=dataoption"); ?>"><?php esc_html_e( 'Data Management', 'lumiere-movies'); ?></a>

<?php			if ( ! is_active_widget( '', '', 'lumiere-movies-widget') ) { ?>

				- <em><font size=-2><a href="<?php echo esc_url( admin_url() . 'widgets.php'); ?>"><?php esc_html_e( 'Widget unactivated', 'lumiere-movies'); ?></a></font></em>

<?php 			} 
			if ( ($imdb_admin_values['imdbdirectsearch'] == "0")  || (!isset($imdb_admin_values['imdbdirectsearch'])) ) { ?>

				- <em><font size=-2><a href="<?php echo esc_url( admin_url() . "admin.php?page=imdblt_options&generaloption=advanced#imdb_imdbdirectsearch_yes"); ?>"><?php esc_html_e( 'Direct search', 'lumiere-movies'); ?></a> <?php esc_html_e( 'unactivated', 'lumiere-movies'); ?></font></em> 

<?php			} 
			if( ($imdb_admin_values['imdbtaxonomy'] == "0")  || (empty($imdb_admin_values['imdbtaxonomy'])) ) { ?>

				- <em><font size=-2><a href="<?php echo esc_url( admin_url() . "admin.php?page=imdblt_options&generaloption=advanced#imdb_imdbtaxonomy_yes"); ?>"><?php esc_html_e( 'Auto taxonomy', 'lumiere-movies'); ?></a> <?php esc_html_e( 'unactivated', 'lumiere-movies'); ?></font></em>

<?php 			} ?>

			</div>

			<div class="imdblt_padding_five imdblt_flex_auto">			
				<img src="<?php echo esc_url ( $imdb_admin_values['imdbplugindirectory'] . "pics/admin-cache.png"); ?>" align="absmiddle" width="16px" />&nbsp;
				<a title="<?php esc_html_e( 'Cache management', 'lumiere-movies'); ?>" href="<?php echo admin_url(); ?>admin.php?page=imdblt_options&subsection=cache"><?php esc_html_e( 'Cache management', 'lumiere-movies'); ?></a>
			</div>

			<div align="right" class="imdblt_padding_five imdblt_flex_auto" >
				<img src="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] . "pics/admin-help.png"); ?>" align="absmiddle" width="16px" />&nbsp;
				<a title="<?php esc_html_e( 'How to use Lumière!, check FAQs & changelog', 'lumiere-movies');?>" href="<?php echo esc_url( admin_url() . "admin.php?page=imdblt_options&subsection=help"); ?>">
					<i>Lumière!</i> <?php esc_html_e( 'help', 'lumiere-movies'); ?>
				</a>
			</div>
		</div>
	</div>

<?php ### select the sub-page


	if (!isset($_GET['subsection'])) {

		require_once ( plugin_dir_path( __DIR__ ). 'inc/options-general.php'  );

	}

	if ( (isset($_GET['subsection'])) && ($_GET['subsection'] == "dataoption") ) {

		require_once ( plugin_dir_path( __DIR__ ) . 'inc/options-data.php' ); 

	} elseif ( (isset($_GET['subsection'])) && ($_GET['subsection'] == "cache") ) {

		require_once ( plugin_dir_path( __DIR__ ). 'inc/options-cache.php' );

	} elseif ( (isset($_GET['subsection'])) && ($_GET['subsection'] == "help") ) {

		require_once ( plugin_dir_path( __DIR__ ) . 'inc/help.php' );

	}
	// end subselection 

	echo $utils->lumiere_admin_signature(); 

?></div><!-- .wrap -->

<?php
} //End function printAdminPage()

?>
