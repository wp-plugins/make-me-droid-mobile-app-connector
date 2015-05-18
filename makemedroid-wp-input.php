<?php

include_once "makemedroid-wp-config.php";
include_once "makemedroid-wp-net-utils.php";

/*
 * Receives a "post comment" form from the MMD app.
 */
function mmd_wp_input_handle_post_comment()
{
	//header('Content-Type: application/json; charset=utf-8');
	
	$get = mmd_wp_extract_GET();
	
	$getVar = array_map('stripslashes_deep', $get);	// Make sure to get unescaped $get content (especially the JSON content).
		
	if (!isset($getVar) || !isset($getVar["form"]))
		die("Missing form");
	
	$formData = json_decode($getVar["form"], true);
	if ($formData == null)
		die("Invalid form structure");
		
	if (!isset($formData["postid"]))
		die("Missing post ID");
		
	if (!isset($formData["name"]))
		die("Missing poster's name");
		
	if (!isset($formData["email"]))
		die("Missing poster's email");
		
	if (!isset($formData["comment"]))
		die("Missing comment");

	$postid = $formData["postid"];
	$name = $formData["name"];
	$email = $formData["email"];
	$comment = $formData["comment"];
	
	// Try to retrieve the post from its ID
	$post = get_post($postid);
	if ($post == null)
		die("This post doesn't exist");
		
	// TODO: Make sure comments are enabled in WP config.
	
	global $current_user;
	$current_user = wp_get_current_user();
		
	// Insert comment for real
	$data = array(
		'comment_post_ID' => $post->ID,
		'comment_author' => $name,
		'comment_author_email' => $email,
		'comment_author_url' => '',
		'comment_content' => $comment,
		'comment_type' => '',
		'comment_parent' => 0,
		'user_id' => $current_user->ID,
		'comment_author_IP' => getReadableUserIp(),
		'comment_agent' => getUserAgent(),
		'comment_date' => current_time('mysql'),
		'comment_approved' => 1,
	);
	
	wp_insert_comment($data);	// This function always returns "success" (an ID)
	
	die("success");
}

?>
