<?php
// Missing wordpress constants in phpstan
// define('TEMPLATEPATH', get_stylesheet_directory_uri() ); // removed on 26.5.23, now it is defined
define('WP_CONTENT_DIR', ABSPATH . 'wp-content/' );
define( 'WPINC', 'asdfadsf');
