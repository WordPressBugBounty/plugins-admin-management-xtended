<?php
/*
Plugin Name: Admin Management Xtended
Version: 2.5.0
Plugin URI: https://www.schloebe.de/wordpress/admin-management-xtended-plugin/
Description: <strong>WordPress 4.3+ only.</strong> Extends admin functionalities by introducing: toggling post/page visibility inline, changing page order with drag'n'drop, inline category management, inline tag management, changing publication date inline, changing post slug inline, toggling comment status open/closed, hide draft posts, change media order, change media description inline, toggling link visibility, changing link categories
Author: Oliver Schl&ouml;be
Author URI: https://www.schloebe.de/
Text Domain: admin-management-xtended
Domain Path: /languages


Copyright 2008-2025 Oliver Schlöbe (email : scripts@schloebe.de)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * The main plugin file
 *
 * @package WordPress_Plugins
 * @subpackage AdminManagementXtended
 */


/**
 * Define the plugin version
 */
define("AME_VERSION", "2.5.0");

/**
 * Define the global var AMEISWP43, returning bool if WP 4.3 or higher is running
 */
define('AMEISWP43', version_compare($GLOBALS['wp_version'], '4.2.999', '>'));

if( !function_exists( 'is_plugin_active' ) ) {
	require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
}

/**
 * Define the global var ISINSTBTM, returning bool
 * if the 'Better Tags Manager' plugin is installed
 */
define('ISINSTBTM', is_plugin_active('better-tags-manager/better-tags-manager.php') );

/**
 * Define the plugin path slug
 */
define("AME_PLUGINPATH", "/" . plugin_basename( basename( dirname(__FILE__) ) ) . "/");

/**
 * Define the plugin full url
 */
define("AME_PLUGINFULLURL", trailingslashit(plugins_url( '', __FILE__ )) );

/**
 * Define the plugin full directory
 */
define("AME_PLUGINFULLDIR", WP_PLUGIN_DIR . AME_PLUGINPATH );

/**
 * Define the plugin image set
 */
define("AME_IMGSET", get_option("ame_imgset") . "/" );


/**
* The AdminManagementXtended class
*
* @package WordPress_Plugins
* @subpackage AdminManagementXtended
* @since 1.4.0
* @author scripts@schloebe.de
*/
class AdminManagementXtended {
	private $textdomain_loaded = false;

	/**
 	* The AdminManagementXtended class constructor
 	* initializing required stuff for the plugin
 	*
	* PHP 5 Constructor
 	*
 	* @since 2.3.9
 	* @author scripts@schloebe.de
 	*/
	function __construct() {
		if( ISINSTBTM ) {
			add_action('admin_notices', array(&$this, 'wpBTMIncompCheck'));
		}

		if ( !AMEISWP43 ) {
			add_action('admin_notices', array(&$this, 'wpVersionFailed'));
			return;
		}

		add_action('plugins_loaded', array(&$this, 'ame_load_textdomain'));

		/**
 		* This file holds all of the general information and functions
 		*/
		require_once(AME_PLUGINFULLDIR . 'general-functions.php');

		/**
 		* This file holds all of the post functions
 		*/
		require_once(AME_PLUGINFULLDIR . 'post-functions.php');

		/**
 		* This file holds all of the page functions
 		*/
		require_once(AME_PLUGINFULLDIR . 'page-functions.php');

		/**
 		* This file holds all of the media functions
 		*/
		require_once(AME_PLUGINFULLDIR . 'media-functions.php');

		/**
 		* This file holds all of the link functions
 		*/
		require_once(AME_PLUGINFULLDIR . 'link-functions.php');

		if( !get_option("ame_show_orderoptions") ) {
			add_option("ame_show_orderoptions", "1");
		}
		if( !get_option("ame_toggle_showinvisposts") ) {
			add_option("ame_toggle_showinvisposts", "1");
		}
		if( !get_option("ame_version") ) {
			add_option("ame_version", AME_VERSION);
		}
		if( !get_option("ame_imgset") ) {
			add_option("ame_imgset", 'set1');
		}
		if( get_option("ame_version") != AME_VERSION ) {
			update_option("ame_version", AME_VERSION);
		}
	}



	/**
 	* The AdminManagementXtended class constructor
 	* initializing required stuff for the plugin
 	*
	* PHP 4 Compatible Constructor
 	*
 	* @since 2.3.9
 	* @author scripts@schloebe.de
 	*/
	function AdminManagementXtended() {
		$this->__construct();
	}


	/**
	 * Fires several actions, depending on type
	 *
	 * @since 1.8.6
	 * @author scripts@schloebe.de
	 */
	static function fireActions( $type, $postid, $post ) {
		switch( $type ) {
			case "post":
				do_action('edit_post', $postid, $post);
				do_action('wp_insert_post', $postid, $post);
				return $post;
		}
	}

	/**
 	* Initialize and load the plugin textdomain
 	*
 	* @since 1.8.5
 	* @author scripts@schloebe.de
 	*/
	function ame_load_textdomain() {
		if($this->textdomain_loaded) return;
		load_plugin_textdomain('admin-management-xtended', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
		$this->textdomain_loaded = true;
	}

	/**
 	* Checks for the version of WordPress,
 	* and adds a message to inform the user
 	* if WP version is >= 4.3 which isnt supported
 	*
 	* @since 2.4.0
 	* @author scripts@schloebe.de
 	*/
	function wpVersionFailed() {
		echo "<div id='amewpversionfailedmessage' class='error fade'><p>" . sprintf(__("<strong>Admin Management Xtended</strong> 2.4.0 and above require at least WordPress 4.3! If you're still using a WP version prior to 4.3, please <a href='%s'>use Admin Management Xtended version 2.3.9.4</a>! Consider updating to the latest WP version for your own safety!", 'admin-management-xtended'), 'https://downloads.wordpress.org/plugin/admin-management-xtended.zip') . "</p></div>";
	}

	/**
 	* Checks for the existance of 'Better Tags Manager' plugin,
 	* which is known to cause problems with this plugin
 	* and adds a message to inform the user
 	*
 	* @since 1.4.0
 	* @author scripts@schloebe.de
 	*/
	function wpBTMIncompCheck() {
		echo "<div id='wpbtmincompmessage' class='error fade'><p>" . __("You seem using the <em>Better Tags Manager</em> plugin, which collides with the <em>Admin Management Xtended</em> plugin since both extend the tags column. Please deactivate one of both to make this message disappear.", 'admin-management-xtended') . "</p><p align='right' style='font-weight:200;'><small><em>" . __('(This message was created by Admin Management Xtended plugin)', 'admin-management-xtended') . "</em></small></p></div>";
	}

}

if ( class_exists('AdminManagementXtended') && is_admin() ) {
	$adminmanagementxtended = new AdminManagementXtended();
}
