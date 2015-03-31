<?php
/*
Plugin Name: Ajax Comment Deletion
Description: Adds a link to posts so comments can be deleted by an admin without a page refresh
Version: 1.0
Author: Daniel Martinez
*/
 
add_action( 'template_redirect', 'dm_acd_addjs_ifcomments' );
function dm_acd_addjs_ifcomments() {
	if( is_single() && current_user_can( 'moderate_comments' ) ){
		global $post;
		if( $post->comment_count ) {
			$path = plugin_dir_url( __FILE__ );

			wp_enqueue_script( 'dm_acd', $path.'js/script.js',array('jquery'));
			$protocol = isset( $_SERVER["HTTPS"]) ? 'https://' : 'http://';
			$params = array(
				'ajaxurl' => admin_url( 'admin-ajax.php', $protocol ) 
			);
			wp_localize_script( 'dm_acd', 'dm_acd', $params ); 
		}
	}
}

add_filter( 'comment_text', 'dm_acd_add_link' ); 
function dm_acd_add_link( $text ) {
	global $comment;
	$comment_id = $comment->comment_ID;
	$link = admin_url( 'comment.php?action=trash&c='.$comment_id ); 
	$link = wp_nonce_url( $link, 'dm_acd-delete-'.$comment_id ); 
	$link = "<a href='$link' class='dm_acd_link'>delete comment</a>";
	return $text."<p>[admin: $link]</p>"; 
}
add_action( 'wp_ajax_dm_acd_ajax_delete', 'dm_acd_ajax_delete' ); 
function dm_acd_ajax_delete() {
	$cid = absint( $_POST['cid'] );
	
	$response = new WP_Ajax_Response;
	
	if(
		current_user_can( 'moderate_comments' ) &&
		check_ajax_referer( 'dm_acd-delete-'.$cid, 'nonce', false ) && 
		wp_delete_comment( $cid )
	){
		$response->add( array(
			'data' => 'success', 
			'supplemental' => array(
				'cid' => $cid,
				'message' => 'this comment has been deleted' 
			),
		) ); 
	} else {
		$response->add( array( 
			'data' => 'error',
			'supplemental' => array(
				'cid' => $cid,
				'message' => 'an error occurred'
			), 
		) );
	}
	$response->send();
	exit(); 
}