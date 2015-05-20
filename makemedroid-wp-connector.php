<?php

/**
 * Plugin Name: Make me Droid Mobile App Connector
 * Plugin URI: http://www.makemedroid.com
 * Description: Connect your WordPress blog or site to your Android or IPhone mobile application made on Make me Droid. You can visualize information about your app, send automatic push messages to the app when you write new articles on your blog, and more.
 * Version: 1.10
 * Author: Make me Droid (contact@makemedroid.com)
 * Author URI:  http://www.makemedroid.com
 * Text Domain: makemedroid-wp-connector
 * License: GPL2 license
 */

/*
 * Note to developers: feel free to edit and improve this plugin the way you want, as long as this can help you make your Make me Droid application better!
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
// Get plugin's locale
add_filter('plugin_locale', 'mmd_wp_get_locale');

// Catch the right URLs and redirect to our JSON content
add_filter('rewrite_rules_array', 'mmd_wp_create_rewrite_rules');
add_filter('query_vars', 'mmd_wp_add_query_vars');
add_action('template_redirect', 'mmd_wp_template_redirect_intercept') ;
// Add content to footer
add_action('wp_footer', 'mmd_wp_footer');

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
	load_plugin_textdomain(MMD_WP_SLUG, false, basename(dirname( __FILE__ )) . '/'.MMD_WP_LANG_PATH);
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

/*
 * Tells which locale this plugin wants to use to wordpress.
 * We use user or default locale if a translation is available, otherwise we fallback to english translations.
 * Locale format: "en_US"
 */
function mmd_wp_get_locale($locale)
{
	$expectedLocale = null;
	
	//$locale = "da_DK"; // FOR DEBUG PURPOSE ONLY - TO FORCE LOCALE TO SHOW
	
	if (function_exists('wp_get_current_user'))
        $expectedLocale = get_user_meta(get_current_user_id(), 'user_lang', 'true');
	
	// No user locale: use blog's global locale
	if ($expectedLocale == null)
		$expectedLocale = $locale;
		
	// Check if a translation file is available for the required locale. If so, we use it. If not, we fallback to en_US.
	$moFilePath = dirname( __FILE__ ) . '/'.MMD_WP_LANG_PATH.'/'.MMD_WP_SLUG.'-'.$expectedLocale.'.mo';
	if (file_exists($moFilePath))
		return $expectedLocale;
	else
		return "en_US";
}

function mmd_wp_footer() {
    echo '<div style="text-align:right;font-size:0.7em;padding:5px;background:black;color:white;">'._tran('footer_mmd_credit_mobile_app_sponsored').'</div>';
}

/*
 * Try by all possible ways to access the GET URL parameters. Depending on servers and WP configurations, this behaviours changes
 * and we cannot fully rely on reading $_GET or using query_vars.
 */
function mmd_wp_extract_GET()
{
	global $_GET, $_SERVER, $_REQUEST;

	if (isset($_REQUEST) && !empty($_REQUEST))
	{
		$args = wp_parse_args($_REQUEST);
		if (!empty($args))
			return $args;
	}

	if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != "")
	{
		$args = wp_parse_args($_SERVER['QUERY_STRING']);
		if (!empty($args))
			return $args;
	}

	if (isset($_SERVER['REQUEST_URI']))
	{
		$url = parse_url($_SERVER['REQUEST_URI']);
		if (!isset($url["query"]))
		{
			// If server URI does not contain the server name (always the case?) we happen the server name,
			// as some PHP versions of configuration of parse_url() seem not to work with only the /....
			// So we add the server name
			if (isset($_SERVER['SERVER_NAME']))
			{
				$url = parse_url($_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']);
			}
		}

		if (isset($url["query"]))
		{
			$args = wp_parse_args($url["query"]);
			if (!empty($args))
				return $args;
		}
	}

	return $_GET;
}

?>
