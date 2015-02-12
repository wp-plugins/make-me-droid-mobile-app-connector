<?php

include_once "makemedroid-wp-config.php";
include_once "makemedroid-wp-api.php";

// Admin panel is initialized
add_action('admin_init', 'mmd_wp_admin_init');
// Builds the admin menu to access this plugin configuration
add_action('admin_menu', 'mmd_wp_insert_admin_menu');
// Used to load plugin's CSS and Javascript files
add_action('admin_enqueue_scripts', 'mmd_wp_load_js_and_css');
// Called when a article is published
add_action('publish_post', 'mmd_wp_post_published');

function mmd_wp_admin_init()
{
	// We use app_XX, which is always 0 for now, but in the future that will let us attach multiple apps to use the same blog (for instance, a free and pro versions of the same app will
	// need the same blog plugin).
	add_option("app_0_api_key", "", "", "yes");
	add_option("app_0_app_key", "", "", "yes");
}

function mmd_wp_insert_admin_menu()
{
	add_submenu_page('options-general.php', MMD_WP_TITLE, MMD_WP_TITLE, 'manage_options', MMD_WP_SLUG, 'mmd_wp_build_admin_options_menu');
}

/*
 * Builds the Make me Droid admin settings page, showing user documentation, plugin status information, JSON URLs available for use, and more.
 */
function mmd_wp_build_admin_options_menu()
{
?>
<div id="mmd-connector">
	<h2><?php echo _tran('admin_title'); ?></h2>
	
<?php

	// .htaccess must be writable
	if (!wp_is_writable(WP_ROOT_PATH."/.htaccess"))
	{
		echo '<div id="message" class="mmd-wp-error"><b>Error:</b> .htaccess must be writable. Please check your server configuration.</div>';
	}
	
	// Permalinks must be enabled, to make sure URL rewriting works.
	global $wp_rewrite;  
	if (!is_array($wp_rewrite->rules))
	{
		echo '<div id="message" class="mmd-wp-error"><b>Error:</b> You have to enable permalinks for this plugin to work.</div>';
	}
	
	$currentUser = wp_get_current_user();
	$currentUserName = $currentUser->user_nicename;
	$currentUserEmail = $currentUser->user_email;
	
	// We make it more straight forward for users to register on Make me Droid, by pre-filling our registration form using some information we can find on the blog.
	$newAccountURL = "http://www.makemedroid.com/?registrationName=".urlencode($currentUserName)."&registrationEmail=".urlencode($currentUserEmail)."&registrationSource=wpplugin";
	
?>
	<p>
		<?php printf(_tran('admin_intro'), $newAccountURL); ?>
	</p>
	
	<a href="<?php echo $newAccountURL; ?>" target="_blank"><img src="<?php echo MMD_WP_PLUGIN_URL."img/MMDLogo.png"; ?>" width="150"/></a>
	
	<h3><?php echo _tran('admin_feature_list_title'); ?></h3>
	<p>
		<ul>
			<li><?php echo _tran('admin_feature_1'); ?></li>
			<li><?php echo _tran('admin_feature_2'); ?></li>
			<li><?php echo _tran('admin_feature_3'); ?></li>
			<li><?php echo _tran('admin_feature_4'); ?></li>
			<li><?php echo _tran('admin_feature_5'); ?></li>
		</ul>
		<br/>
		<b><?php echo _tran('admin_feature_list_note'); ?>
		<br/><br/>
	</p>

	<h3><?php echo _tran('admin_howto_get_app_title'); ?></h3>
	<ul>
		<li><?php printf(_tran('admin_howto_get_app_item_1'), $newAccountURL); ?></li>
		<li><?php echo _tran('admin_howto_get_app_item_2'); ?></li>
		<li><?php printf(_tran('admin_howto_get_app_item_3'), "http://www.makemedroid.com/".getLanguageSlugForMakeMeDroidURL()."/guides/firstapptutorial/"); ?></li>
		<li><?php echo _tran('admin_howto_get_app_item_4'); ?></li>
		<li><?php printf(_tran('admin_howto_get_app_item_5'), "http://www.makemedroid.com/".getLanguageSlugForMakeMeDroidURL()."/guides/testing/", "http://www.makemedroid.com/".getLanguageSlugForMakeMeDroidURL()."/guides/publishing/"); ?></li>
	</ul>
	
	<h3><?php echo _tran('admin_connect_title'); ?></h3>
	<ul>
		<li>
			<b><u><?php echo _tran('admin_connect_basic'); ?></u></b><br/>
			<ul>
				<li><?php echo _tran('admin_connect_item_1'); ?></li>
				<li><?php echo _tran('admin_connect_item_2'); ?></li>
				<li><?php echo _tran('admin_connect_item_3'); ?></li>
			</ul>
		</li>
		<li>
			<b><u><?php echo _tran('admin_connect_advanced'); ?></u></b><br/>
			<ul>
				<li><?php echo _tran('admin_connect_item_4'); ?></li>
				<li><?php printf(_tran('admin_connect_item_5'), "http://www.makemedroid.com/".getLanguageSlugForMakeMeDroidURL()."/guides/jsondatasources/"); ?></li>
				<li><?php echo _tran('admin_connect_item_6'); ?></li>
			</ul>
		</li>
	</ul>
	
	<table class="mmd-wp-padded_td_table">
		<tr>
			<td><img src="<?php echo MMD_WP_PLUGIN_URL."img/mobile-screen-1.jpg"; ?>"/></td>
			<td><img src="<?php echo MMD_WP_PLUGIN_URL."img/mobile-screen-2.jpg"; ?>"/></td>
			<td><img src="<?php echo MMD_WP_PLUGIN_URL."img/mobile-screen-3.jpg"; ?>"/></td>
		</tr>
	</table>
				
	
	<h3><?php echo _tran('admin_about_title'); ?></h3>
	<p>
	<?php
	
		$appFound = false;
		$apiKey = get_option("app_0_api_key");
		$appKey = get_option("app_0_app_key");
		
		// Ask Make me Droid more information about this application, if we have the API access to do so.
		if ($apiKey != false && $appKey != false)
		{
			$data = array(
				"app"=> $appKey,
				"key"=> $apiKey
			);
			
			$result = mmd_wp_call_mmd_api("getappinfo", $data, get_option("app_0_api_url"));
			if ($result != null && $result["result"] == "success")
			{
				$appFound = true;
				
				echo "<div id='message' class='updated'>"._tran('admin_abount_contacted')."</div>";
				
				echo "<table class='mmd-wp-padded_td_table'>";
				echo "<tr><td><b>"._tran('admin_account')."</b>:</td><td>".$result["account"]["name"]." (".$result["account"]["login"].")</td></tr>";
				echo "<tr><td><b>"._tran('admin_app_name')."</b>:</td><td>".$result["appname"]."</td></tr>";
				echo "<tr><td><b>"._tran('admin_api_key')."</b>:</td><td>".$apiKey."</td></tr>";
				echo "<tr><td><b>"._tran('admin_app_key')."</b>:</td><td>".$appKey."</td></tr>";
				echo "<tr><td><b>"._tran('admin_package')."</b>:</td><td>".$result["packagename"]."</td></tr>";
				
				$data = array(
					"app"=> $appKey,
					"key"=> $apiKey
				);
				
				$result = mmd_wp_call_mmd_api("getpushlisteners", $data, get_option("app_0_api_url"));
				if ($result != null && $result["result"] == "success")
				{
					echo "<tr><td><b>"._tran('admin_listening_devices')."</b>:</td><td>".$result["pushlisteners"]." <img src='".MMD_WP_PLUGIN_URL."/img/phone16.png' width='16' height='16'/></td></tr>";
				}
				
				echo "</table>";
			}
		}
		
		if (!$appFound)
		{
			echo "<div id='message' class='error'>"._tran('admin_account_no_contact')."</div>";
		}
	?>
	</p>
	
	<h3><?php echo _tran('admin_help_title'); ?></h3>
	<p>
		<?php echo _tran('admin_help_forum'); ?>
	</p>
	
	<h3><?php echo _tran('admin_url_title'); ?></h3>
	
	<p><?php echo _tran('admin_url_intro'); ?></p>
 
	<p>
		<div class="mmd-wp-json-url-title"><?php echo _tran('admin_url_1'); ?></div>
		<div class="mmd-wp-json-url"><?php echo ARTICLES_URL; ?></div>
		<?php echo "<a href='".ARTICLES_URL."' target='_blank'>"; ?><?php echo _tran('admin_view_it'); ?></a>
		<div class="mmd-wp-json-url-params">
			<b><?php echo _tran('admin_url_opt_field'); ?>:</b> "catid": <?php echo _tran('admin_url_1_catid_info'); ?>
		</div>
	</p>
	
	<p>
		<div class="mmd-wp-json-url-title"><?php echo _tran('admin_url_2'); ?></div>
		<div class="mmd-wp-json-url"><?php echo CATEGORIES_URL; ?></div>
		<?php echo "<a href='".CATEGORIES_URL."' target='_blank'>"; ?><?php echo _tran('admin_view_it'); ?></a>
	</p>
	
	<p>
		<div class="mmd-wp-json-url-title"><?php echo _tran('admin_url_3'); ?></div>
		<div class="mmd-wp-json-url"><?php echo COMMENTS_URL; ?></div>
		<?php echo "<a href='".COMMENTS_URL."' target='_blank'>"; ?><?php echo _tran('admin_view_it'); ?></a>
		<div class="mmd-wp-json-url-params">
			<b><?php echo _tran('admin_url_opt_field'); ?>:</b> "postid": <?php echo _tran('admin_url_3_postid_info'); ?>
		</div>
	</p>
	
	<p>
		<div class="mmd-wp-json-url-title"><?php echo _tran('admin_url_4'); ?></div>
		<div class="mmd-wp-json-url"><?php echo ATTACHMENTS_URL; ?></div>
		<?php echo "<a href='".ATTACHMENTS_URL."' target='_blank'>"; ?><?php echo _tran('admin_view_it'); ?></a>
		<div class="mmd-wp-json-url-params">
			<b><?php echo _tran('admin_url_req_field'); ?>:</b> "postid": <?php echo _tran('admin_url_4_postid_info'); ?>
		</div>
	</p>
	
	<p>
		<div class="mmd-wp-json-url-title"><?php echo _tran('admin_url_5'); ?></div>
		<div class="mmd-wp-json-url-text">
			<?php echo _tran('admin_url_5_info'); ?>
		</div>
		<div class="mmd-wp-json-url"><?php echo POST_COMMENT_URL; ?></div>
		<div class="mmd-wp-json-url-params">
			<b><?php echo _tran('admin_url_req_field'); ?>:</b> "postid": <?php echo _tran('admin_url_5_postid_info'); ?><br/>
			<b><?php echo _tran('admin_url_req_field'); ?>:</b> "name": <?php echo _tran('admin_url_5_name_info'); ?><br/>
			<b><?php echo _tran('admin_url_req_field'); ?>:</b> "email": <?php echo _tran('admin_url_5_email_info'); ?><br/>
			<b><?php echo _tran('admin_url_req_field'); ?>:</b> "comment": <?php echo _tran('admin_url_5_comment_info'); ?>
		</div>
	</p>

	<br/>
	<a href="<?php echo $newAccountURL; ?>" target="_blank"><img src="<?php echo MMD_WP_PLUGIN_URL."img/MMDLogo.png"; ?>" width="150"/></a>
</div>
<?php

}

function mmd_wp_load_js_and_css()
{
	wp_register_style( 'makemedroid-wp-connector', MMD_WP_PLUGIN_URL . 'css/makemedroid-wp-connector.css');
	wp_enqueue_style( 'makemedroid-wp-connector');
}

function mmd_wp_post_published($post_id)
{
	// An article just got published - Make sure it's published for the first time (or at least this is a real "publish" action).
	if( ($_POST['post_status'] == 'publish') && ($_POST['original_post_status'] != 'publish'))
	{
		$post = get_post($post_id);
		if ($post != null)
		{
			$title = $post->post_title;
			
			$apiKey = get_option("app_0_api_key");
			$appKey = get_option("app_0_app_key");
		
			if ($apiKey != false && $appKey != false)
			{
				// Send a PUSH notification to the app to tell users that a new article is available.
				// We use the Make me Droid API to access this function.
				$data = array(
					"app"=> $appKey,
					"key"=> $apiKey,
					"p_article_title"=> $title
				);
				
				$result = mmd_wp_call_mmd_api("sendnewarticle", $data, get_option("app_0_api_url"));
				if ($result != null && $result["result"]=="success")
					error_log("Sent Push notification");
				else
					error_log("Failed to send push notification");
			}
		}
	}
}

?>
