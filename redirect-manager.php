<?php
/*
Plugin Name: Redirect Manager
Plugin URI: http://www.semiologic.com/software/redirect-manager/
Description: Lets you manage redirects on your site without messing around with .htaccess files.
Version: 1.2
Author: Denis de Bernardy & Mike Koepke
Author URI: http://www.getsemiologic.com
Text Domain: redirect-manager
Domain Path: /lang
*/

/*
Terms of use
------------

This software is copyright Mesoconcepts (http://www.mesoconcepts.com), and is distributed under the terms of the Mesoconcepts license. In a nutshell, you may freely use it for any purpose, but may not redistribute it without written permission.

http://www.mesoconcepts.com/license/
**/


load_plugin_textdomain('redirect-manager', false, dirname(plugin_basename(__FILE__)) . '/lang');


/**
 * redirect_manager
 *
 * @package Redirect Manager
 **/

class redirect_manager {
    /**
     * redirect_manager()
     */
    function redirect_manager() {
        add_action('admin_menu', array($this, 'meta_boxes'));
        add_action('template_redirect', array($this, 'redirect'), -1000000);
    } #redirect_manager()

    /**
	 * meta_boxes()
	 *
	 * @return void
	 **/
	
	function meta_boxes() {
		if ( current_user_can('edit_posts') )
			add_meta_box('redirect_manager', __('Redirect', 'redirect-manager'), array('redirect_manager_admin', 'edit_entry'), 'post');
		
		if ( current_user_can('edit_pages') )
			add_meta_box('redirect_manager', __('Redirect', 'redirect-manager'), array('redirect_manager_admin', 'edit_entry'), 'page');
	} # meta_boxes()
	
	
	/**
	 * redirect()
	 *
	 * @return void
	 **/
	
	function redirect() {
		if ( is_feed() || !is_singular() )
		 	return;
		
		global $wp_query;
		$post_id = $wp_query->get_queried_object_id();
		
		if ( $location = get_post_meta($post_id, '_redirect_url', true) ) {
			if ( !current_user_can('edit_post', $post_id) ) {
				wp_redirect($location, 301);
				die;
			} else {
				add_filter('the_content', array($this, 'display'));
			}
		}
	} # redirect()
	
	
	/**
	 * display()
	 *
	 * @param string $content the entry's content
	 * @return string $content
	 **/
	
	function display($content) {
		if ( is_feed() || !is_singular() )
			return $content;
		
		global $wp_the_query;
		$post_id = $wp_the_query->get_queried_object_id();
		$location = get_post_meta($post_id, '_redirect_url', true);
		
		if ( $location ) {
			$location = esc_url($location);
			$caption = __('This page is redirecting visitors to <a href="%1$s">%2$s</a>. You\'re not being redirected because you can edit this entry.', 'redirect-manager');
			$content = '<p>' . sprintf($caption, $location, $location) . '</p>';
		}
		
		return $content;
	} # display()
} # redirect_manager


function redirect_manager_admin() {
	include_once dirname(__FILE__) . '/redirect-manager-admin.php';
}

foreach ( array('page-new.php', 'page.php', 'post-new.php', 'post.php') as $hook )
	add_action("load-$hook", 'redirect_manager_admin');

$redirect_manager = new redirect_manager();

?>