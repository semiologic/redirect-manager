<?php


/**
 * redirect_manager_admin class
 *
 * @package Redirect Manager
 **/

class redirect_manager_admin {
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
	 * Constructor.
	 *
	 *
	 */

	public function __construct() {
		$this->plugin_url    = plugins_url( '/', __FILE__ );
		$this->plugin_path   = plugin_dir_path( __FILE__ );

		$this->init();
    } #redirect_manager_admin

	/**
	 * init()
	 *
	 * @return void
	 **/

	function init() {
		// more stuff: register actions and filters
		if ( is_admin() ) {
	        add_action('save_post', array($this, 'save_entry'));
        }
	}

    /**
	 * edit_entry()
	 *
	 * @param object $post
	 * @return void
	 **/
	
	static function edit_entry($post) {
		if ( $post->ID > 0 )
			$value = get_post_meta($post->ID, '_redirect_url', true);
		else
			$value = '';
		
		echo '<p>'
			. '<input type="text" name="redirect_manager" size="58" tabindex="5" class="code widefat"'
				. ' value="' . esc_attr($value) . '"'
				. ' />'
			. '</p>';
		
		echo '<p>'
			. __('Enter the url to which visitors of this entry should automatically be redirected.', 'redirect-manager')
			. '</p>';
	} # edit_entry()
	
	
	/**
	 * save_entry()
	 *
	 * @param int $post_id
	 * @return void
	 **/
	
	function save_entry($post_id) {
		if ( wp_is_post_revision($post_id) || !current_user_can('edit_post', $post_id) )
			return;

		if ( !isset($_POST['redirect_manager'] ))
			return;

		$value = $_POST['redirect_manager'];
		$value = stripslashes($value);
		$value = esc_url_raw($value);
		$value = addslashes($value);

		if ( $value ) {
			update_post_meta($post_id, '_redirect_url', $value);
		} else {
			delete_post_meta($post_id, '_redirect_url');
		}

	} # save_entry()
} # redirect_manager_admin

$redirect_manager_admin = redirect_manager_admin::get_instance();
