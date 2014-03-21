<?php

include_once "makemedroid-wp-config.php";
include_once "makemedroid-wp-string-utils.php";
include_once "makemedroid-wp-image-utils.php";
include_once "makemedroid-wp-api.php";

/*
 * Additional URL parameters:
 * - catid: to list only posts that belong to that category.
 */
function mmd_wp_output_articles()
{
	mmd_wp_output_write_json_header();
	
	// TODO: extract and handle $_GET s=, si=,cnt=, etc
		
	$output = array();
	mmd_wp_output_append_site_info($output);
	
	$args = array(
		'posts_per_page'   => 10,
		'offset'           => 0,
		'orderby'          => 'post_date',
		'order'            => 'DESC',
		'post_status'      => 'publish',
		'suppress_filters' => true);
	
	// Optional category
	if (isset($_GET["catid"]))
	{
		$args["category"] = $_GET["catid"];
	}
	
	$posts = get_posts($args);
	$output["posts"] = array();
	foreach ($posts as $post)
	{
		$postEntry = array();
		$postEntry["id"] = $post->ID;
		$postEntry["title"] = $post->post_title;
		$postEntry["textcontent"] = html_entity_decode(strip_tags($post->post_content), ENT_QUOTES, "UTF-8");	// TODO: also remove [caption]...[/caption] and other stuff like that.
		$postEntry["url"] = get_permalink($post->ID);
		$postEntry["creationdate"] = $post->post_date;
		$postEntry["creationdategmt"] = $post->post_date_gmt;
		
		// Retrieve attachments contained by this post
		mmd_wp_output_append_attachments($post->post_content, $postEntry);
		
		// Retrieve CATEGORIES contained by this post
		$postEntry["categories"] = array();
		$postCategories = wp_get_post_categories($post->ID);	
		foreach($postCategories as $c){
			$category = get_category($c);
			
			$postCat = array();
			$postCat["id"] = $category->cat_ID;
			$postCat["name"] = $category->name;
			$postCat["slug"] = $category->slug;
			
			$postEntry["categories"][] = $postCat;
		}
		
		// Retrieve COMMENTS contained by this post
		$commentsArgs = array(
			'status' => 'approve',
			'post_id' => $post->ID
		);
		
		$postEntry["comments"] = array();
		$comments = get_comments($commentsArgs);
		mmd_wp_output_append_comment($postEntry["comments"], $comments);
		
		$output["posts"][] = $postEntry;
	}
	
	echo pretty_json(json_encode($output));
}

function mmd_wp_output_categories()
{
	mmd_wp_output_write_json_header();
	
	// TODO: extract and handle $_GET s=, si=,cnt=, etc
	
	$output = array();
	mmd_wp_output_append_site_info($output);

	$args = array();
	
	$categories = get_categories($args);
	foreach ($categories as $category)
	{
		$catEntry = array();
		$catEntry["id"] = $category->cat_ID;
		$catEntry["name"] = $category->name;
		$catEntry["slug"] = $category->slug;
		
		$output["categories"][] = $catEntry;
	}
	
	echo pretty_json(json_encode($output));
}

/*
 * Mandatory URL parameters:
 * - the post ID.
 */
function mmd_wp_output_attachments()
{
	mmd_wp_output_write_json_header();
	
	// TODO: extract and handle $_GET s=, si=,cnt=, etc
	
	$output = array();
	mmd_wp_output_append_site_info($output);
	
	// Mandatory post ID
	if (isset($_GET["postid"]))
	{
		$post = get_post($_GET["postid"]);
		if ($post != null)
		{
			$output["postid"] = $post->ID;
			// Retrieve attachments contained by this post
			mmd_wp_output_append_attachments($post->post_content, $output);
		}
	}
	
	echo pretty_json(json_encode($output));
}

/*
 * Additional URL parameters:
 * - postid: to list only comments that belong to that post.
 */
function mmd_wp_output_comments()
{
	mmd_wp_output_write_json_header();
	
	// TODO: extract and handle $_GET s=, si=,cnt=, etc
		
	$output = array();
	mmd_wp_output_append_site_info($output);
	
	// Retrieve COMMENTS for the whole blog, or for a specific post
	$commentsArgs = array(
		'status' => 'approve'
	);
	
	// Optional post
	if (isset($_GET["postid"]))
	{
		$commentsArgs["post_id"] = $_GET["postid"];
	}
	
	$output["comments"] = array();
	$comments = get_comments($commentsArgs);
	mmd_wp_output_append_comment($output["comments"], $comments);
	
	echo pretty_json(json_encode($output));
}

// Write the common header for all JSON outputs.
function mmd_wp_output_write_json_header()
{
	header('Content-Type: application/json; charset=utf-8');
}

// Appends site information to an on going JSON array output.
function mmd_wp_output_append_site_info(&$output)
{
	$output["site"] = array();
	$output["site"]["title"] = get_bloginfo('name');
	$output["site"]["description"] = get_bloginfo('description');
	$output["site"]["url"] = get_bloginfo('url');
}

/*
 * Appends attachments to an on going JSON array output.
 * Attachments are extracted from a post html content, by parsing some HTML tags.
 * We can't rely on Wordpress post attachments, as they represent the attachments associated with a post by the time they were first added, but NOT the attachments
 * that really are in the post.
 */
function mmd_wp_output_append_attachments($postcontent, &$output)
{
	// TODO: video + sound attachments
	
	// Retrieve IMAGES contained by this post
	$output["images"] = array();
	// We have to manually extract images really USED in the post (not the attached image, which can have been removed, or inserted in another post first and used here, etc: that's a mess).
	preg_match_all("/<img[^']*?src=\"([^']*?)\"[^']*?>/", $postcontent, $matches, PREG_PATTERN_ORDER);
	foreach($matches[1] as $match)
	{
		$imageURL = $match;
		$attachmentID = pn_get_attachment_id_from_url($imageURL);
		
		if ($attachmentID !== false)
		{
			$attachmentImageData = wp_get_attachment_image_src($attachmentID, thumbnail);
			$image["thumbnail"] = array();
			$image["thumbnail"]["url"] = $attachmentImageData[0];
			$image["thumbnail"]["width"] = $attachmentImageData[1];
			$image["thumbnail"]["height"] = $attachmentImageData[1];
			
			$attachmentImageData = wp_get_attachment_image_src($attachmentID, large);
			$image["large"] = array();
			$image["large"]["url"] = $attachmentImageData[0];
			$image["large"]["width"] = $attachmentImageData[1];
			$image["large"]["height"] = $attachmentImageData[1];
		}
		else
		{
			$image["thumbnail"] = array();
			$image["thumbnail"]["url"] = $imageURL;
			
			$image["large"] = array();
			$image["large"]["url"] = $imageURL;
		}
		
		$output["images"][] = $image;
	}
}

// Appends comments to an on going JSON array output
function  mmd_wp_output_append_comment(&$output, $comments)
{
	foreach ($comments as $comment)
	{
		$commentEntry = array();
		
		$commentEntry["id"] = $comment->comment_ID;
		$commentEntry["author"] = array();
		$commentEntry["author"]["name"] = $comment->comment_author;
		$commentEntry["author"]["email"] = $comment->comment_author_email;
		$commentEntry["author"]["url"] = $comment->comment_author_url;
		$commentEntry["textcontent"] = html_entity_decode(strip_tags($comment->comment_content), ENT_QUOTES, "UTF-8");
		
		$output[] = $commentEntry;
	}
}

/*
 * Called by Make me Droid website to check if the Make me Droid Wordpress plugin is active and available at this address.
 * As we are called here, this means the plugin is active, so we return some information about this plugin, that Make me Droid will keep in mind for later use.
 */
function mmd_wp_output_connect()
{
	header('Content-Type: application/json; charset=utf-8');
	header('Access-Control-Allow-Origin: http://www.makemedroid.com'); // Whitelist for cross-domain call.
		
	// API KEY CONFIRMATION
	// We normally get the MMD app API key, appkey and account in $_GET.
	// Use this information to call the MMD API and make sure these information are valid. If so,
	// we can save this in the plugin database for later use.
	$account = $_GET["account"];
	$appkey = $_GET["appkey"];
	$apikey = $_GET["apikey"];
	$apiurl = $_GET["apiurl"];
	
	$data = array(
		"app"=> $appkey,
		"key"=> $apikey,
		"account"=> $account
	);
	
	$result = mmd_wp_call_mmd_api("validatekeys", $data, $apiurl);
	if ($result == null || $result["result"] != "success" || $result["validation"] != "valid")
	{
		$result = array();
		$result["status"] = "accessforbidden";
	}
	else
	{
		// All right! We got the confirmation that these keys are valid, so we can store them to be reused later on.
		update_option("app_0_account", $account);
		update_option("app_0_api_key", $apikey);
		update_option("app_0_app_key", $appkey);
		update_option("app_0_api_url", $apiurl);
		
		// Now return some information for MMD to know more about the plugin configuration.
		$result = array();
		$result["status"] = "ready";
		$result["pluginurl"] = site_url();
		$result["entryPoints"] = array();
		$result["entryPoints"]["getArticles"] = ARTICLES_URL;
		$result["entryPoints"]["postComment"] = POST_COMMENT_URL;
	}

	echo $_GET['callback']."(".json_encode($result).");";
}

?>
