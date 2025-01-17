<?php

/**
 * This bootstrap is loaded for LOCAL config only (loaded in suite.yml)
 * Constants can be accessed directly in code
 * If the same constant is defined in both local and remote bootstrap, it will discriminate against the suite run
 * DO NOT PUT ANY CREDENTIAL HERE! Uploaded to git
 */
 
define( 'DEVELOPMENT_ENVIR', 'local' );

// WP Post including 1/ an IMDb movie link into the post 2/ A widget IMDb 3/ Inside the post movie

define( 'ADMIN_POST_ID_TESTS', '/wp-admin/post.php?post=4740&action=edit' ); // Different in remote

// For auto title widget post

define( 'ADMIN_POST_AUTOTITLEWIDGET_ID', '/wp-admin/post.php?post=4767&action=edit' ); // Different in remote

// For ban bots and nonce

define( 'BAN_BOTS_MSG', 'Prevented a bad request.' ); // Always English.
define( 'BAN_NONCE_MSG', 'Invalid or missing nonce.' ); // English, usually found.
define( 'BAN_NONCE_MSG_FR', 'Nonce invalide ou manquant.' ); // French, because sometimes it switch to.

