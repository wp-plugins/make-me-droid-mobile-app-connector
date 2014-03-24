<?php

define('MMD_WP_TITLE', 'Make me Droid');
define('MMD_WP_SLUG', 'makemedroid-wp-connector');
define('MMD_WP_PLUGIN_KEY', 'MMDConnector');
define('MMD_WP_SHORT_PATH', 'mmd-connect');
define('WP_ROOT_PATH', dirname( __FILE__ )."/../../..");
define('MMD_WP_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('MMD_WP_LANG_PATH', 'languages');

define('MMD_WP_GETARTICLES_KEY', 'getarticles');
define('MMD_WP_GETCATEGORIES_KEY', 'getcategories');
define('MMD_WP_GETCOMMENTS_KEY', 'getcomments');
define('MMD_WP_GETATTACHMENTS_KEY', 'getattachments');
define('MMD_WP_CONNECT_KEY', 'connect');
define('MMD_WP_POSTCOMMENT_KEY', 'postcomment');

// COMPUTED
define('ARTICLES_URL', site_url()."/".MMD_WP_SHORT_PATH."/".MMD_WP_GETARTICLES_KEY."/");
define('CATEGORIES_URL', site_url()."/".MMD_WP_SHORT_PATH."/".MMD_WP_GETCATEGORIES_KEY."/");
define('COMMENTS_URL', site_url()."/".MMD_WP_SHORT_PATH."/".MMD_WP_GETCOMMENTS_KEY."/");
define('ATTACHMENTS_URL', site_url()."/".MMD_WP_SHORT_PATH."/".MMD_WP_GETATTACHMENTS_KEY."/");
define('POST_COMMENT_URL', site_url()."/".MMD_WP_SHORT_PATH."/".MMD_WP_POSTCOMMENT_KEY."/");

?>
