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
 #  Function : Help to configure IMDbLT		                            #
 #									              #
 #############################################################################

// constants
global $imdb_admin_values;
$readmefile = $imdb_admin_values['imdbplugindirectory'] . "README.txt";
$changelogfile = $imdb_admin_values['imdbplugindirectory']."CHANGELOG.txt";

$allowed_html_for_esc_html_functions = [
	'i',
	'strong',
];


// Boxes
add_meta_box('imdbLT_help_plb', esc_html__( 'Popup link builder', 'imdb'), 'imdbLT_help_plb_function', 'imdblt_help', 'left', 'core');
add_meta_box('imdblt_help_itp', esc_html__( 'Inside the post', 'imdb'), 'imdblt_help_itp_function', 'imdblt_help', 'right', 'core');
add_meta_box('imdblt_help_w', esc_html__( 'Widget', 'imdb'), 'imdblt_help_w_function', 'imdblt_help', 'left', 'core');
add_meta_box('imdblt_help_adminmenubig', esc_html__( 'Big admin menu', 'imdb'), 'imdblt_help_adminbigmenu_function', 'imdblt_help', 'right', 'core');
add_meta_box('imdblt_help_addsearchform', esc_html__( 'Add a search form', 'imdb'), 'imdblt_help_addsearchform_function', 'imdblt_help', 'left', 'core');
add_meta_box('imdblt_help_keepcss', esc_html__( 'Keep css through update', 'imdb'), 'imdblt_help_keepcss_function', 'imdblt_help', 'right', 'core');
/* --- moviepilot is not used anymore 
add_meta_box('imdblt_help_getridofimdb', esc_html__( 'Use a website other than IMDb', 'imdb'), 'imdblt_help_getridofimdb_function', 'imdblt_help', 'left', 'core');
*/
add_meta_box('imdblt_help_usetaxonomy', esc_html__( 'Taxonomy with Wordpress', 'imdb'), 'imdblt_help_usetaxonomy_function', 'imdblt_help', 'left', 'core');
add_meta_box('imdblt_help_autowidget_function', esc_html__( 'Widget auto according post\'s title', 'imdb'), 'imdblt_help_autowidget_function', 'imdblt_help', 'right', 'core');
?>

<div id="tabswrap">
	<ul id="tabs">
		<li><img src="<?php echo esc_url( $imdb_admin_values[imdbplugindirectory] ."pics/admin-help-howto.png"); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e( "How to use IMDb link transformer", 'imdb');?>" href="<?php echo esc_url( admin_url() . "admin.php?page=imdblt_options&subsection=help&helpsub=howto"); ?>"><?php esc_html_e( 'How to', 'imdb'); ?></a></li>

		<li>&nbsp;&nbsp;<img src="<?php echo esc_url( $imdb_admin_values[imdbplugindirectory] . "pics/admin-help-faq.png"); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e( "Frequently asked questions", 'imdb');?>" href="<?php echo esc_url( admin_url() . "admin.php?page=imdblt_options&subsection=help&helpsub=faqs"); ?>"><?php esc_html_e( 'FAQs', 'imdb'); ?></a></li>

		<li>&nbsp;&nbsp;<img src="<?php echo esc_url( $imdb_admin_values[imdbplugindirectory] . "pics/admin-help-changelog.png"); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e( "What's new", 'imdb');?>" href="<?php echo esc_url( admin_url() . "admin.php?page=imdblt_options&subsection=help&helpsub=changelog"); ?>"><?php esc_html_e( 'Changelog', 'imdb'); ?></a></li>

		<li>&nbsp;&nbsp;<img src="<?php echo esc_url( $imdb_admin_values[imdbplugindirectory] . "pics/admin-help-support.png"); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e( "To get support and to support what you get", 'imdb');?>" href="<?php echo esc_url( admin_url() . "admin.php?page=imdblt_options&subsection=help&helpsub=support"); ?>"><?php esc_html_e( 'Support, donate & credits', 'imdb'); ?></a></li>
	</ul>
</div>

<div id="poststuff" class="metabox-holder">

<?php
if ($_GET['helpsub'] == "faqs")  { 	// Readme section ?>

	<div class="postbox-container">
		<div id="left-sortables" class="meta-box-sortables">
			<div id="imdbLT_help_plb" class="postbox">
				<div class="handlediv" title="Click to toggle"><br></div>
				<h3 class="hndle"><?php esc_html_e("Frequently asked questions", "imdb"); ?></h3>
				<div class="inside">
					<div class="helpdiv">
					<?php 
					$patterntitle = '/== Frequently Asked Questions ==(.*?)== Support ==/ms';
					$faqfile = file_get_contents($readmefile);
					preg_match($patterntitle, $faqfile, $faqsection);
					$faqsection = $faqsection[1];
					$faqsectionarray = preg_split ('/=(.*?)=/', $faqsection, -1, PREG_SPLIT_DELIM_CAPTURE);

					// replace links from (specially formated for wordpress website) readme with casual html
					$patternlink = '~(\\[{1}(.*?)\\]\()(https://)(([[:punct:]]|[[:alnum:]])*)( \"{1}(.*?)\"\))~';
					$faqsectionarray = preg_replace($patternlink,"<a href=\"\${3}\${4}\" title=\"\${7}\">\${2}</a>",$faqsectionarray);
					$faqsectionarray = preg_replace("~\*\*(.*?)\*\*~","<i>\${1}</i>",$faqsectionarray);
				
					$i=0;
					echo "<br />\n<ol>\n";
						foreach ($faqsectionarray as $texte) {
							if ($i > "0") {
								if ($i%2 == 1) { // uneven number -> title
								// display text formatted
								echo "\t\t\t\t\t\t<li><strong>".$texte."</strong></li>\n";
								} else { // even number -> text
								// display text formatted
								echo "\t\t\t\t\t\t<div class='imdblt_padding_twenty'>".nl2br(str_replace("\n\n", "\n", $texte))."\t\t\t\t\t\t</div>\n";
								}
							}
						$i++;
						}
					echo "\t\t\t\t\t</ol>\n"; ?>
					</div>
				</div>
			</div>
		</div>
	</div>

<?php
	} elseif ($_GET['helpsub'] == "changelog")  { 	// Changlog section ?>


	<div class="postbox-container">
		<div id="left-sortables" class="meta-box-sortables">
			<div id="imdbLT_help_plb" class="postbox">
				<div class="handlediv" title="Click to toggle"><br></div>
				<h3 class="hndle"><?php esc_html_e("Changelog", "imdb"); ?></h3>
				<div class="inside">
					<div class="helpdiv">
					<?php 
					$changlelogfile = file($changelogfile, FILE_BINARY);
					// replace **...** by strong and i
					$changlelogfile = preg_replace("~(\*\s\[)(.*?)(\])~","<strong><i>\${2}</i></strong>",$changlelogfile);
					// replace links from (specially formated for wordpress website) readme with casual html
					$patternlink = '~(\\[{1}(.*?)\\]\()(http://)(([[:punct:]]|[[:alnum:]])*)( \"{1}(.*?)\"\))~';

					$changlelogfile = preg_replace($patternlink,"<a href=\"\${3}\${4}\" title=\"\${7}\">\${2}</a>",$changlelogfile);
					$i=0;
					echo "<ul>";					
					foreach ($changlelogfile as $texte) {
						if  ($i > "1") {

							// display text formatted
							echo "\t\t\t\t\t\t<li>".str_replace("\n", "", nl2br($texte))."</li>\n";
						}
					$i++;
					}
					echo "\t\t\t\t\t</ul>\n";
					?>
					</div>
				</div>
			</div>
		</div>
	</div>


<?php
	} elseif ($_GET['helpsub'] == "support")  { 	// Support section
?>
	<div align="center"><?php wp_kses( _e( 'Two ways to match <strong>IMDb link transformer</strong> and <strong>support</strong>', 'imdb'), $allowed_html_for_esc_html_functions); ?>:</div>
	<br />
	<br />	
	
	<div class="postbox-container" class="imdblt_float_right">
		<div id="right-sortables" class="meta-box-sortables">
			<div id="imdbLT_support" class="postbox">
				<div class="handlediv" title="Click to toggle"><br></div>
				<h3 class="hndle"><?php esc_html_e( 'Be supported!', 'imdb'); ?></h3>
				<div class="inside">
					<div class="helpdiv">
						<?php esc_html_e( 'You will never believe there is so many ways to be supported. Yes, you can:', 'imdb'); ?><br />
			<strong>1</strong>. <?php esc_html_e( 'visit ', 'imdb'); ?> <a href="<?php echo IMDBHOMEPAGE ?>">IMDb link transformer home website</a> <?php esc_html_e( 'to ask for support. ', 'imdb'); ?><br />
			<strong>2</strong>. <?php esc_html_e( 'check ', 'imdb'); ?> <a href="?page=imdblt_options&subsection=help&helpsub=faqs"><?php esc_html_e( 'FAQs ', 'imdb'); ?></a>.<br />
			<strong>3</strong>. <?php esc_html_e( 'check ', 'imdb'); ?> <a href="?page=imdblt_options&subsection=help&helpsub=howto"><?php esc_html_e( 'how to', 'imdb'); ?></a>.<br />
					</div>
				</div>		
			</div>
		</div>
	</div>


	<div class="postbox-container" class="imdblt_float_left">
		<div id="left-sortables" class="meta-box-sortables">
			<div id="imdbLT_support" class="postbox">
				<div class="handlediv" title="Click to toggle"><br></div>
				<h3 class="hndle"><?php esc_html_e( 'Support me!', 'imdb'); ?></h3>
				<div class="inside">
					<div class="helpdiv">
						<?php esc_html_e( 'You will never believe there is so many ways to thank me. Yes, you can:', 'imdb'); ?><br />
		<strong>1</strong>. <?php esc_html_e( 'vote for IMDb link transformer on ', 'imdb'); ?> <a href="https://wordpress.org/extend/plugins/imdb-link-transformer/">Wordpress' website</a>.<br />
		<strong>2</strong>. <?php esc_html_e( 'vote for IMDb link transformer on its own', 'imdb'); ?> <a href="<?php echo IMDBHOMEPAGE ?>">website</a>.<br />
		<strong>3</strong>. <?php esc_html_e( "send as many bugfixes and propositions as you can on IMDb link transformer website.", 'imdb'); ?><br />
		<strong>4</strong>. <?php esc_html_e( 'pay whatever you want on paypal', 'imdb'); ?> <a href="https://www.paypal.me/jcvignoli"><img src="<?php echo esc_url( $imdb_admin_values[imdbplugindirectory] . "pics/paypal-donate.png"); ?>" width="60px" class="imdblt_align_bottom" valign="bottom"/><?php esc_html_e( 'website', 'imdb'); ?></a>.<br />
		<strong>5</strong>. <?php esc_html_e( "translate the plugin into your own language.", 'imdb'); ?><br />
		<strong>6</strong>. <?php esc_html_e( "help me to improve the plugin's design. I'm not gifted at all.", 'imdb'); ?><br />
		<strong>7</strong>. <?php esc_html_e( "place a trackback, talk about this tool, and so on.", 'imdb'); ?><br />
					</div>
				</div>		
			</div>
		</div>
	</div>

	
	<div class="postbox-container" class="imdblt_float_left">
		<div id="left-sortables" class="meta-box-sortables">
			<div id="imdbLT_support" class="postbox">
				<div class="handlediv" title="Click to toggle"><br></div>
				<h3 class="hndle"><span><?php esc_html_e( 'Credits:', 'imdb'); ?></span></h3>
				<div class="inside">
					<div class="helpdiv">
						<?php esc_html_e( 'Special thanks to:', 'imdb'); ?><br /><br />
						<li><a href="https://github.com/tboothman/imdbphp">tboothman</a>, imdbphp project classes (core)</li>
						<li>Murillo Ferrari, Brazilian translation</li>
						<li>Andr&eacute;s Cabrera, Spanish translation</li>
						<li>Peter, Bulgarian translation</li>
						<li>Web Geek Sciense (<a href="https://webhostinggeeks.com/">Web Hosting Geeks</a>), Romanian translation</li>
						<li><a href="http://highslide.com">Highslide JS</a>, smart & pretty js libraries collection -> BTW, discovered thanks to <a href="https://www.fots.nl/index.php/imdb-tag/">Hans Sleurink</a>, an inspiring plugin maker</li>
						<li>Several icons made by <a href="https://p.yusukekamiyamane.com/">Yusuke Kamiyamane</a></li>
					</div>
				</div>		
			</div>
		</div>
	</div>


<?php } else { 				// How to display ?>

	<div class="intro_cache"><?php esc_html_e( "IMDb link transformer evolves a lot. As a result of this, the plugin include many options, many ways to achieve bloggers wishes. Since then, IMDb link transformer main functions are explained hereafter, aiming to make the experience better. As you may discover, there are three main ways to display data from movies and there is many options. This is an attempt to document it, but please check also FAQs.", 'imdb'); ?>
	</div>

	<div class="imdblt_double_container">
		<div class="postbox-container imdblt_double_container_content">
			<?php do_meta_boxes('imdblt_help', 'left', null); ?>
		</div>
		<div class="postbox-container imdblt_double_container_content">
			<?php do_meta_boxes('imdblt_help', 'right', null); ?>
		</div>	
	</div>	
<?php
	wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); 
	wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); 

	} // end test on helpsub ?>

</div>
<br clear="all">

<?php

//-------------functions boxes (layout above)

#######################
## imdbLT_help_plb_function
######################

function imdbLT_help_plb_function () { 
	global $imdb_admin_values; ?>

	<div class="helpdiv">
		<h4><?php esc_html_e( 'Why a popup window?', 'imdb'); ?></h4>
		<a href="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] . ".wordpress-org/screenshot-1.jpg"); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'imdb'); ?>"><img align="right" width="55%" src="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] . ".wordpress-org/screenshot-1.jpg"); ?>" alt="screenshot Link creator" /></a>
		<?php esc_html_e( "The first way to use Imdb link transformer is the first historically available. The idea behind was to find a solution for posts quoting many movies; blogger and reader would appreciate to have director's name, to know the alternative title's name, etc. And these data should not waste space in post itself; thus the popup idea.", 'imdb'); ?>
	</div>

	<div class="helpdiv">
		<?php esc_html_e( "Popup, as an extra window opened on click, will permit to get director's and movie's data; it is fully browsable and when browsing a movie, one can also look for 'AKAs' (movies which have a name similar).", 'imdb'); ?>
	</div>

	<div class="helpdiv">
		<?php esc_html_e( 'It is quite intuitive to understand how popup can help; when writing about many movies, if one would like to speak about every director or actor he mentions in his post, it would take ages and will not have the power of the linkage (once seen the director, looking for his filmography).', 'imdb'); ?>
	</div>

	<div class="helpdiv">
		<h4><?php esc_html_e( 'How to get a popup window', 'imdb'); ?></h4>
		<?php esc_html_e( "To create a link to a popup window, you only need to put the <b>movie's title</b> (it doesn't work with any other data) inside dedicated tags. Either you use the HTML style to write posts, and a new button could help you to achieve that:", 'imdb'); ?><br />
		<div align="center"><a href="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] . ".wordpress-org/screenshot-7.jpg"); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'imdb'); ?>"><img width="90%" src="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] . ".wordpress-org/screenshot-7.jpg"); ?>" alt="screenshot Link creator button added for bloggers who prefer HTML writing way" /></a></div>
	</div>

	<div class="helpdiv">
		<?php esc_html_e( 'Or you use the Visual style to write posts, and a new button could help you to achieve that :', 'imdb'); ?>
		<div align="center"><a href="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] . ".wordpress-org/screenshot-6.jpg"); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'imdb'); ?>"><img width="90%" src="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] . ".wordpress-org/screenshot-6.jpg"); ?>" alt="screenshot Link creator New button added for bloggers who prefer Visual writing way" /></a></div>
	</div>

	<div class="helpdiv">
		<?php esc_html_e( "Whatever the tool you choose, when you will save your post you will get a nice link added to your movie.", 'imdb'); ?>
	</div>

<?php } // end function imdbLT_help_plb


#######################
## imdblt_help_w_function
######################

function imdblt_help_w_function () { 
	global $imdb_admin_values; ?>

	<div class="helpdiv">
		<h4><?php esc_html_e( "Why to use widget?", 'imdb'); ?></h4>
		<a href="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] . ".wordpress-org/screenshot-3.jpg"); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'imdb'); ?>"><img align="right" width="50%" src="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] . ".wordpress-org/screenshot-3.jpg"); ?>" alt="<?php esc_html_e( 'key and value for custom fields', 'imdb'); ?>" /></a>

		<?php esc_html_e( 'Widgets are widely used in wordpress. They allow to quickly select what information to display in an area, usually on sidebar. The main advantage is that blogger can easily select what information to display - or not, in what order.', 'imdb'); ?>
	</div>
	
	<div class="helpdiv">
		<h4><?php esc_html_e( "How to use widget", 'imdb'); ?></h4>
		<?php wp_kses( _e( "<strong>Firstly</strong>, go to <a href='widgets.php'>widget</a> administration (<i>appearance</i> tab), drag <i>imdb widget</i> (from <i>inactive widgets</i>) to a sidebar, and modify the box's title (in case you don't want to have the box named <i>IMDb data</i>).", 'imdb'), $allowed_html_for_esc_html_functions); ?>
	</div>
	
	<div class="helpdiv">
		<?php wp_kses( _e( "<strong>Secondly</strong>, open an old post (or write a new one) and add the key <i>imdb-movie-widget</i> to the <i>custom fields</i> of your message <strong>and</strong> the name of the movie you want to display to <i>value</i> (first case from the picture). IMDb link transformer will automatically display in the widget the movie selected.", 'imdb'), $allowed_html_for_esc_html_functions); ?>
	</div>
	
	<div class="helpdiv">
		<a href="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] . ".wordpress-org/screenshot-5.jpg"); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'imdb'); ?>"><img align="left" width="50%" src="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] . ".wordpress-org/screenshot-5.jpg"); ?>" alt="<?php esc_html_e( 'IMDb link transformer widget', 'imdb'); ?>" /></a>
		<?php wp_kses( _e( "<strong>Another possibility:</strong> add to your post the key <i>imdb-movie-widget-bymid</i> into the <i>custom fields</i> from your message <strong>and</strong> the IMDb ID for the movie you want to be displayed on your sidebar to <i>value</i> (second case from the picture). Instead of looking for a name, IMDb link transformer would directly display the movie you want to display. Very useful when your movie's name does not work as it should (if there are many movies with the same name, the wrong movie is displayed, etc).", 'imdb'),  $allowed_html_for_esc_html_functions); ?>
	</div>
	
	<div class="helpdiv">
		<?php wp_kses( _e( "To get the movie's IMDb id, search for a title on <a href='https://www.imdb.com' >Internet movie database</a> website, look at the adress bar for a 'ttXXXXX' section, keep only the numerical part (XXXXX) and add result to the <i>value</i> custom field. However, in this specific case, do not mix with an <i>imdb-movie-widget</i> key. Only the first one will be displayed.", 'imdb'), $allowed_html_for_esc_html_functions); ?>
	</div>

<?php } // end function imdblt_help_w_function


#######################
## imdblt_help_itp_function
######################

function imdblt_help_itp_function () { 
	global $imdb_admin_values; ?>

	<div class="helpdiv">
		<h4><?php esc_html_e( "What's the point of displaying movie's data inside my post?", 'imdb'); ?></h4>
		<a href="<?php echo esc_url ($imdb_admin_values['imdbplugindirectory'] . ".wordpress-org/screenshot-2.jpg"); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'imdb'); ?>"><img align="right" width="50%" src="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] . ".wordpress-org/screenshot-2.jpg"); ?>" alt="<?php esc_html_e( 'IMDb link transformer widget', 'imdb'); ?>" /></a>
		<?php esc_html_e( "Having movie's data inside the post is quite useful; it could ingeniously decorate your message, display crucial informations (director, actors) and in the same time add links to a popup which would include these informations (but with more details). At the begining, this function could only be achieved with a special plugin. Since release 1.1.14.3, IMDb link transformer does not need any third party plugin anymore to display movie's data inside you message. ", 'imdb'); ?>
	</div>

	<div class="helpdiv">
		<?php esc_html_e( "It means that you only need to put the movie's name into brackets, to get movie's data inside your post.", 'imdb'); ?>
	</div>

	<div class="helpdiv">
		<h4><?php esc_html_e( 'How to display data inside my post', 'imdb'); ?></h4>
		<div align="center">**[IMDBLT]**</div>
		<div><?php esc_html_e( "You only need to put the movie's name into brackets, to get movie's data inside your post. Data can be inserted exactly where you want, there is no limitation. Just be sure to put the movie's name inside [imdblt][/imdblt], like this:", 'imdb'); ?></div>
		<div align="center"><a href="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] . ".wordpress-org/screenshot-8.jpg"); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'imdb'); ?>"><img width="90%" src="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] . ".wordpress-org/screenshot-8.jpg"); ?>" alt="<?php esc_html_e( 'IMDb link transformer Inside a post', 'imdb'); ?>" /></a></div>
		<div><?php esc_html_e( "And you are done.", 'imdb'); ?></div>
		<div align="center">**[IMDBLTID]**</div>
		<div><?php esc_html_e( "You additionnaly can get a movie from its imdb id, using its imdb movie's id instead of its name. When writing your post, put the movie's imdb id inside tags [imdbltid][/imdbltid] (which gives ie [imdbltid]0137523[/imdbltid], for Fight Club movie). You can get movie's imdb id from imdb website, search for you movie, and check your browser's adress bar. The number after 'tt' part is the movie's id.", 'imdb'); ?></div>
	</div>
	

	<div class="helpdiv">
		<h4><?php esc_html_e( 'How to display data inside my post - tricky way', 'imdb'); ?></h4>
		<div><?php esc_html_e( "It could happen you don't want to use the previous solution to display movie's data. For exemple, if you wish to use IMDbLT outside a post (in a personalized page), it won't work. In this case, and in this case only, you should (may be) to download Exec-PHP plugin (<a href='http://wordpress.org/extend/plugins/exec-php'>http://wordpress.org/extend/plugins/exec-php</a>), depending of your page.", 'imdb'); ?></div><br />
		<div><?php esc_html_e( "The function to be called is <strong>imdb_call_external ()</strong>. It has two parameters, and both are mandatory. The first is the movie's name, and the second take always 'external'. For exemple, one'd like to display 'The Descent' should call the function like this:", 'imdb'); ?></div><br />
		<blockquote align="center" class="imdblt_padding_left">$imdblt = new imdblt;<br />$imdblt->imdb_external_call('Descent', 'external')</blockquote>
		<div><?php esc_html_e( "The function can also be called using still 'external as second parameter, but the first will be blank and a new third parameter will take its IMDb movie's ID. For exemple, one'd like to display 'The Descent' should call the function this manner:", 'imdb'); ?></div><br />
		<blockquote align="center" class="imdblt_padding_left">$imdblt = new imdblt;<br />$imdblt->imdb_external_call('', 'external', '0435625')</blockquote>
	</div>

	<div class="helpdiv">
		<h4><?php esc_html_e( "I like to display movie details, but I want to get rid of links which open a popup", 'imdb'); ?></h4>
		<?php esc_html_e( "It could happen you do not want popups at all. Since by default IMDb link transformer add links whenever relevant to movie's details displayed inside posts, one may find this useless. To get rid of them, look for 'Widget/Inside post Options / Misc / Remove popup links?' and switch the option to 'yes'. No links are created anymore, for both widget and inside a post.", 'imdb'); ?>
	</div>
<?php } // end function imdblt_help_w_function


#######################
## imdblt_help_adminbigmenu_function
######################

function imdblt_help_adminbigmenu_function () { 
	global $imdb_admin_values; ?>

	<div class="helpdiv">
		<h4><?php esc_html_e( "IMDb link transformer is a mess! I'm lost with so many options.", 'imdb'); ?></h4>
	</div>
	<div class="helpdiv">
		<a href="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] . "pics/imdblt_menubig.jpg"); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'imdb'); ?>"><img align="right" width="10%" src="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] . "pics/imdblt_menubig.jpg"); ?>" alt="imdblt big menu" /></a>
		<?php esc_html_e( "Wordpress admin area offers more and more options to user. Starting with 2.7 release, it also offers the possibility to add new admin menu for plugins. IMDb link transformer makes the most of this possibility, and can add its own admin menu. It can be added - or removed. Depending of how many plugin's admin menus you already have, you would prefer to keep access to IMDb link transformer settings only through usual 'settings' tab; it's up to you to choose to either get a complet and bigger IMDb link transformer menu - or not.", 'imdb'); ?>
	</div>
	
	<div class="helpdiv">
		<?php esc_html_e( "In the case you want to, go to 'General options / Advanced / Display plugin in big admin menu' and turn to 'yes' the option. You will get a brand new menu, as shown on left.", 'imdb'); ?>
	</div>
	
	<div class="helpdiv">
		<a href="<?php echo $imdb_admin_values['imdbplugindirectory']; ?>pics/imdblt_menubig_ozh.jpg" title="<?php esc_html_e( 'click to get a larger picture', 'imdb'); ?>"><img align="left" width="20%" src="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] . "pics/imdblt_menubig_ozh.jpg"); ?>" alt="imdblt big menu with Admin Drop Down Menu plugin" /></a>
		<?php esc_html_e( "One can go still further; if installing Ozh Admin Drop Down Menu (https://planetozh.com/blog/my-projects/wordpress-admin-menu-drop-down-css/), a plugin to switch classical vertical admin menu to horizontal and which gives the admin area more of a 'desktop application' feel. IMDb link transformer is ready to use improvements from Ozh's plugin, adding icons (when user select IMDb's plugin from the settings menu). Also, if you activate both Ozh's plugin and IMDb link transformer's big admin menu, you will get a nice and complete horizontal menu. Very useful.", 'imdb'); ?>
	</div>
<?php } // end function imdblt_help_adminbigmenu_function



#######################
## imdblt_help_addsearchform_function
######################

function imdblt_help_addsearchform_function () { 
	global $imdb_admin_values; ?>

	<div class="helpdiv">
		<h4><?php esc_html_e( "How to add a search function for movies in general, and not only for selected movies?", 'imdb'); ?></h4>

		<?php esc_html_e( "It is indeed doable. Plugin is versatile enough to handle this function. By adding a form, you can add a search field which will allow users to search for every movie on your blog. Here is the code:", 'imdb'); ?>
	</div>

	<div class="helpdiv">
		<blockquote class="imdblt_align_left">
							&lt;form action="" method="post" id="searchmovie" onSubmit="&lt;?php echo "window.open('".$imdb_admin_values[imdbplugindirectory]."inc/popup.php?film="."' + document.getElementById('moviesearched').value"; ?&gt, 'popup', 'resizable=yes, toolbar=no, scrollbars=yes, location=no, width=&lt;?php echo $imdb_admin_values[popupLarg]; ?&gt, height=&lt;?php echo $imdb_admin_values[popupLong]; ?>, top=5, left=5')"&gt<br />
								&lt;div&gt Search a movie: &lt;/div&gt<br />
								&lt;input type="text" id="moviesearched" name="moviesearched" &gt<br />
								&lt;input type="submit" value="Go"&gt<br />
							&lt;/form&gt<br />
		</blockquote>
	</div>
	
	<div class="helpdiv">
		<?php esc_html_e( "It perfectly fits in your sidebar, for exemple.", 'imdb'); ?>
	</div>




<?php } // end function imdblt_help_addsearchform_function


#######################
## imdblt_help_keepcss_function
######################

function imdblt_help_keepcss_function () { 
	global $imdb_admin_values; ?>

	<div class="helpdiv">
		<h4><?php esc_html_e( "How to keep my own css through updated process?", 'imdb'); ?></h4>

		<?php esc_html_e( "Fed up of losing your carefully handmade visual settings at every IMDbLT update? There is a solution.", 'imdb'); ?>
	</div>
	<div class="helpdiv">
		<?php esc_html_e( "Every modification you can make is done in css/imdb.css file. Instead of using this file, put an imdb.css file in you template root folder; this css file will taken instead of plugin's css file. Whenever you update, your template's one will stay untouched!", 'imdb'); ?>
	</div>
	<div class="helpdiv">
		<?php esc_html_e( "However, be careful with this manner, since css/imdb.css file can be updated from version to version, and you'd not be aware of changes made...", 'imdb'); ?>
	</div>


<?php } // end function imdblt_help_keepcss_function


#######################
## imdblt_help_getridofimdb_function
######################
/* Pilot is not used anymore
function imdblt_help_getridofimdb_function () { 
	global $imdb_admin_values; ?>

	<div class="helpdiv">
		<h4><?php esc_html_e( "How to switch to another website other than IMDb?", 'imdb'); ?></h4>

		<?php esc_html_e( "Starting with IMDbLT 1.6 release, you have the opportunity to use other website source than IMDb.", 'imdb'); ?>
	</div>
	<div class="helpdiv">
		<?php esc_html_e( "Since IMDb website's licence of use is quite protective, you may choose to use Moviepilot website instead (could have more choices in far future). To completely comply with legal obligations, follow these steps:", 'imdb'); ?>
	</div>
	<div class="helpdiv" class="imdblt_align_center">
		<a href="<?php echo $imdb_admin_values['imdbplugindirectory']; ?>pics/getRidofIMDb.jpg" title="<?php esc_html_e( 'click to get a larger picture', 'imdb'); ?>"><img align="center" width="90%" src="<?php echo $imdb_admin_values['imdbplugindirectory']; ?>pics/getRidofIMDb.jpg" alt="imdblt get rid of IMDb option" /></a>
	</div>
	<div class="helpdiv">
		1/ <?php esc_html_e( "You need an API key from moviepilot.com. Send an email to api@moviepilot.de asking for an API key, and copy-paste the code you have been given in 'General options -> Advanced -> Search, moviepilot part-> API Pilot key' field.", 'imdb'); ?> 
	<?php esc_html_e( "For a preformatted email", 'imdb'); ?> <a href="mailto:api@moviepilot.de?subject=API%20key%20for%20moviepilot%20wordpress%20plugin&body=Hi,%20Would%20you%20kindly%20send%20me%20an%20API%20key%20to%20use%20movie%20pilot%20wordpress%20plugin%3F%20Many%20thanks%20and%20kind%20regards."><?php esc_html_e( "click here", 'imdb'); ?></a>.<br /><br />
		2/ <?php esc_html_e( "Switch 'Get rid of IMDb' option to 'on'", 'imdb'); ?><br /><br />
		3/ <?php esc_html_e( "You may now select both your local language and the accuracy level to comply with (IMDb behaviour).", 'imdb'); ?><br /><br />
	</div>


<?php } // end function imdblt_help_getridofimdb_function
*/



#######################
## imdblt_help_usetaxonomy_function
######################

function imdblt_help_usetaxonomy_function () { 
	global $imdb_admin_values; ?>

	<div class="helpdiv">
		<h4><?php esc_html_e( "What is Wordpress' taxonomy?", 'imdb'); ?></h4>

		<?php esc_html_e( "Starting with Wordpress 2.3, wordpress users can make use taxonomy (https://codex.wordpress.org/WordPress_Taxonomy).", 'imdb'); ?>
		<?php esc_html_e( "Taxonomy is a feature adding a supplementary categorisation beside Categories and Tags; in other words, it is like having species (a block named 'genre') and subspecies (few words describing the genre, like 'Adventure', 'Terror'). It is not fundamentaly different from Categories and Tags, but it is much more appropriate for IMDb Link Transformer needs.", 'imdb'); ?>
	</div>
	<div class="helpdiv">
		<h4><?php esc_html_e( "How to use taxonomy Wordpress capability?", 'imdb'); ?></h4>

		<?php esc_html_e( "To activate the automatically generated taxonomy, turn 'General options -> Advanced ->  'Use automatical genre taxonomy?' to 'yes'. Note that since taxonomy is related to movie details, the movie detail you want to be used as taxonomy has to be also activated from 'Widget/Inside post Options -> What to display'. Eventually, it will permit to select in 'Widget/Inside post Options -> What to taxonomize' menu if you want to taxonomize Genres, but also actors, colors, composers, directors, etc.", 'imdb'); ?><br />
		<div align="center">
			<a href="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] . "pics/taxonomy_details.png"); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'imdb'); ?>"><img align="center" width="60%" src="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] . "pics/taxonomy_details.png"); ?>" alt="taxonomy details" /></a>
		</div>
	</div>
	<div class="helpdiv">
		<?php esc_html_e( "After that, pay a visit to your post/page; first refresh will not immediately display the movie detail (it will only generate them). Though, on a second refresh, you will see both movie details and (if you wordpress theme is configured to show it) taxonomy in your post.", 'imdb'); ?>
	</div>
	
	<div class="helpdiv">
		<h4><?php esc_html_e( "New option(s) in 'Posts' menu", 'imdb'); ?></h4>

		<?php esc_html_e( "Once you have activated all taxonomy options and have triggered its wordpress function (by visiting your post and refreshing the page), wordpress administration posts menu will include new option(s). Depending on movie details previously selected, you will find an option to manage the words automatically generated by the taxonomy function. For instance to select 'genre':", 'imdb'); ?><br />
		<div align="center">
		<a href="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] . "pics/taxonomy_newmenu.png"); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'imdb'); ?>"><img align="center" src="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] . "pics/taxonomy_newmenu.png"); ?>" alt="taxonomy new options" /></a>
		</div>
	</div>
	
	<div class="helpdiv">
		<h4><?php esc_html_e( "Limits", 'imdb'); ?></h4>
		<?php esc_html_e( "There is a strong limitation: since eache page/post can contain only a unique taxonomy sequence, only the first movie (first 'inside the post', first 'widget') will use the taxonomy feature. If you have two movie's widgets, only the first widget will display the links to genre's taxonomy. If you have both a movie's widget and an inside the post data, the movie's widget will prevail.", 'imdb'); ?>
	</div>
	<div class="helpdiv">
		<?php esc_html_e( "Also, select cautiously options you want to be displayed as taxonomies: it could happen it creates a conflict with other functions, especially if you display many movies in same post. When selecting one of the taxonomy options from 'What to taxonomize' section, it will supersede any other function or link created; for instance, you won't have anymore access to popup links for directors, if directors taxonomy is chosen. Taxonomy will always prevail.", 'imdb'); ?>
	</div>
		
	<div class="helpdiv">
		<h4><?php esc_html_e( "Advanced: Customizing your theme according taxonomy", 'imdb'); ?></h4>
		
		<?php esc_html_e( "To fully enjoy this feature, copy the file in IMDb link transformer folder 'theme/taxonomy-genre.php' to your 'theme' folder. Customize this page according to your tastes and theme design. The design is the same as (ie) the tag.php file.", 'imdb'); ?><br />
		<div align="center">
			<a href="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] . "pics/taxonomy.png"); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'imdb'); ?>"><img align="center" width="80%" src="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] . "pics/taxonomy.png"); ?>" alt="taxonomy result" /></a>
		</div>
	</div>	
<?php } // end function imdblt_help_usetaxonomy_function


#######################
## imdblt_help_keepcss_function
######################

function imdblt_help_autowidget_function () { 
	global $imdb_admin_values; ?>

	<div class="helpdiv">
		<h4><?php esc_html_e( "How to get the movie retrieved automatically according to the post's title?", 'imdb'); ?></h4>

		<?php esc_html_e( "You have hundreds of posts, carrefully named as they could be found on IMDb, and you don't want to change hundreds of posts. There is a straightforward solution.", 'imdb'); ?>
	</div>
	<div class="helpdiv">
		<?php esc_html_e( "Activate Widget option in IMDbLT (it is activated by default), add the IMDbLT Widget to your sidebar, and go to 'Widget/Inside post Options' menu, select 'misc' from the new menu, and select « yes » from « Auto widget? » option at end.", 'imdb'); ?>

		<div align="center">
			<a href="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] . "pics/auto-widget.png"); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'imdb'); ?>"><img align="center" width="80%" src="<?php echo esc_url( $imdb_admin_values['imdbplugindirectory'] . "pics/auto-widget.png"); ?>" alt="auto widget option" /></a>
		</div>
	</div>

	<div class="helpdiv">
		<?php esc_html_e( "Next time you will look at your post, you will find the widget according to your post’s title.", 'imdb'); ?>
	</div>
<?php 
} // end function imdblt_help_keepcss_function
?>

