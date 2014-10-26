<?php
/*
Plugin Name: Redirect Manager
Plugin URI: http://www.semiologic.com/software/redirect-manager/
Description: Lets you manage redirects on your site without messing around with .htaccess files.
Version: 1.5
Author: Denis de Bernardy & Mike Koepke
Author URI: http://www.semiologic.com
Text Domain: redirect-manager
Domain Path: /lang
License: Dual licensed under the MIT and GPLv2 licenses
*/

/*
Terms of use
------------

This software is copyright Denis de Bernardy & Mike Koepke, and is distributed under the terms of the MIT and GPLv2 licenses.

**/



/**
 * redirect_manager
 *
 * @package Redirect Manager
 **/

class redirect_manager {
	/**
	 * Plugin instance.
	 *
	 * @see get_instance()
	 * @type object
	 */
	protected static $instance = NULL;

	/**
	 * URL to this plugin's directory.
	 *
	 * @type string
	 */
	public $plugin_url = '';

	/**
	 * Path to this plugin's directory.
	 *
	 * @type string
	 */
	public $plugin_path = '';

	/**
	 * Access this plugin’s working instance
	 *
	 * @wp-hook plugins_loaded
	 * @return  object of this class
	 */
	public static function get_instance()
	{
		NULL === self::$instance and self::$instance = new self;

		return self::$instance;
	}

	/**
	 * Loads translation file.
	 *
	 * Accessible to other classes to load different language files (admin and
	 * front-end for example).
	 *
	 * @wp-hook init
	 * @param   string $domain
	 * @return  void
	 */
	public function load_language( $domain )
	{
		load_plugin_textdomain(
			$domain,
			FALSE,
			dirname(plugin_basename(__FILE__)) . '/lang'
		);
	}

	/**
	 * Constructor.
	 *
	 *
	 */

    public function __construct() {
	    $this->plugin_url    = plugins_url( '/', __FILE__ );
        $this->plugin_path   = plugin_dir_path( __FILE__ );
        $this->load_language( 'redirect-manager' );

	    add_action( 'plugins_loaded', array ( $this, 'init' ) );
    } #redirect_manager()

	/**
	 * init()
	 *
	 * @return void
	 **/

	function init() {
		// more stuff: register actions and filters
		if ( !is_admin() ) {
            add_action('template_redirect', array($this, 'redirect'), -1000000);
        }
        else {
            add_action('admin_menu', array($this, 'meta_boxes'));

	        foreach ( array('page-new.php', 'page.php', 'post-new.php', 'post.php') as $hook )
	        	add_action("load-$hook", array($this, 'redirect_manager_admin' ));
        }
	}

	/**
	* meta_boxes()
	*
	* @return void
	**/
	function redirect_manager_admin() {
 	    include_once $this->plugin_path . '/redirect-manager-admin.php';
    }

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
				if ( $this->wp_redirect($location, 301) )
					exit();
			} else {
				add_filter('the_content', array($this, 'display'));
			}
		}
	} # redirect()

	/**
	 * Redirects to another page.
	 *
	 * @param string $location The path to redirect to.
	 * @param int $status Status code to use.
	 * @return bool False if $location is not provided, true otherwise.
	 */
	function wp_redirect($location, $status = 302) {
		global $is_IIS;

		$location = apply_filters( 'wp_redirect', $location, $status );

		$status = apply_filters( 'wp_redirect_status', $status, $location );

		if ( ! $location )
			return false;

		$location = wp_sanitize_redirect($location);

		if ( !$is_IIS && php_sapi_name() != 'cgi-fcgi' )
			status_header($status); // This causes problems on IIS and some FastCGI setups

		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Location: $location", true, $status);

		return true;
	}

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

$redirect_manager = redirect_manager::get_instance();