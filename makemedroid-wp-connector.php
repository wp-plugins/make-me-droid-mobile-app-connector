<?php

/**
 * Plugin Name: Make me Droid Mobile App Connector
 * Plugin URI: http://www.makemedroid.com
 * Description: Connect your WordPress blog or site to your Android or IPhone mobile application made on Make me Droid. You can visualize information about your app, send automatic push messages to the app when you write new articles on your blog, and more.
 * Version: Version 1.1
 * Author: Make me Droid (contact@makemedroid.com)
 * Author URI:  http://www.makemedroid.com
 * Text Domain: makemedroid-wp-connector
 * License: GPL2 license
 */

/*
 * Developers note: feel free to edit and improve this plugin the way you want, as long as this can help you make your Make me Droid application better!
 *
 * You can also send us your changes, bug reports and improvements proposal to our forum at http://www.makemedroid.com/forum/ or by email at contact@makemedroid.com. We will
 * integrate your changes in future plugin releases if it is interesting to do so.
 */

include_once "makemedroid-wp-config.php";
include_once "makemedroid-wp-admin.php";
include_once "makemedroid-wp-output.php";
include_once "makemedroid-wp-input.php";
 
// ACTIONS INTEGRATION
register_activation_hook(__FILE__, 'mmd_wp_plugin_activated');
add_action('init', 'mmd_wp_init' );
// Plugins loaded
add_action('plugins_loaded', 'mmd_wp_plugins_loaded');

// Catch the right URLs and redirect to our JSON content
add_filter('rewrite_rules_array', 'mmd_wp_create_rewrite_rules');
add_filter('query_vars', 'mmd_wp_add_query_vars');
add_action('template_redirect', 'mmd_wp_template_redirect_intercept') ;

/*
 * Called when wordpress is initialized
 */
function mmd_wp_init()
{
	// Flush our URL rewriting rules to .htaccess, if necessary.
	flush_rewrite_rules();
}

/*
 * Called when plugins are loaded.
 */
function mmd_wp_plugins_loaded()
{
	// Load internationalization files.
	load_plugin_textdomain(MMD_WP_SLUG, false, basename(dirname( __FILE__ )) . '/languages' );
}

/*
 * Catch our special JSON URLs and convert them into a index.php parameters list
 */
function mmd_wp_create_rewrite_rules($rules)
{
	global $wp_rewrite;
	
	$newRule = array(MMD_WP_SHORT_PATH.'/(.+)' => 'index.php?'.MMD_WP_PLUGIN_KEY.'='.$wp_rewrite->preg_index(1));
	$newRules = $newRule + $rules;
	
	return $newRules;
}

function  mmd_wp_add_query_vars($qvars)
{
	$qvars[] = MMD_WP_PLUGIN_KEY;
	return $qvars;
}

/*
 * A rewrited-URL was catched through our "plugin" redirection. Now we must return the appropriate content.
 */
function mmd_wp_template_redirect_intercept()
{
	global $wp_query;
	
	if ($wp_query->get(MMD_WP_PLUGIN_KEY))
	{
		$key = $wp_query->get(MMD_WP_PLUGIN_KEY);
		
		if ($key == MMD_WP_CONNECT_KEY)
		{
			// Internal call to let MMD talk with this wordpress plugin and share some information.
			mmd_wp_output_connect();
		}
		else if ($key == MMD_WP_GETARTICLES_KEY)
		{
			mmd_wp_output_articles();
		}
		else if ($key == MMD_WP_GETCATEGORIES_KEY)
		{
			mmd_wp_output_categories();
		}
		else if ($key == MMD_WP_GETCOMMENTS_KEY)
		{
			mmd_wp_output_comments();
		}
		else if ($key == MMD_WP_GETATTACHMENTS_KEY)
		{
			mmd_wp_output_attachments();
		}
		else if ($key == MMD_WP_POSTCOMMENT_KEY)
		{
			mmd_wp_input_handle_post_comment();
		}
		
		exit;
	}
}
	
/*
 * Called when our plugin is activated. So we need to check current blog status and generate a few things to get ready
 */
function mmd_wp_plugin_activated()
{
	// Nothing yet.
}

?>
