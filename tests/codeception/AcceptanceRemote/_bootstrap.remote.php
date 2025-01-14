<?php

/**
 * This bootstrap is loaded for REMOTE config only (loaded in suite.yml)
 * Constants can be accessed directly in code
 * If the same constant is defined in both local and remote bootstrap, it will discriminate against the suite run
 * DO NOT PUT ANY CREDENTIAL HERE! Uploaded to git
 */

define( 'DEVELOPMENT_ENVIR', 'remote' );

// WP Post including 1/ an IMDb movie link into the post 2/ A widget IMDb 3/ Inside the post movie
	
define( 'ADMIN_POST_ID_TESTS', '/wp-admin/post.php?post=4715&action=edit' ); // Different in local

// For auto title widget post

define( 'ADMIN_POST_AUTOTITLEWIDGET_ID', '/wp-admin/post.php?post=4745&action=edit' ); // Different in remote

// For ban bots

define( 'BAN_BOTS_MSG', 'Requête de recherche invalide.' ); // Different in remote, which is in French, and local, which is in English.
