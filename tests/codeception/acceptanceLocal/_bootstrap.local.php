<?php

/**
 * This bootstrap is loaded for LOCAL config only (loaded in suite.yml)
 * Constants can be accessed directly in code
 * If the same constant is defined in both local and remote bootstrap, it will discriminate against the suite run
 * DO NOT PUT ANY CREDENTIAL HERE! Uploaded to git
 */
 
define( 'DEVELOPMENT_ENVIR', 'local' );

// WP Post including 1/ an IMDb movie link into the post 2/ A widget IMDb 3/ Inside the post movie
// An example is available in tests/IMPORT_WP_POST_MAIN.txt

define( 'ADMIN_POST_ID_TESTS', '/wp-admin/post.php?post=4740&action=edit' ); // Different in remote
