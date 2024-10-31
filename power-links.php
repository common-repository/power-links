<?php
/*
Plugin Name: Power Links
Plugin URI: http://xmlswf.com/power-links-plugin-for-wordpress/
Description: Manages your WordPress site internal liks effectively.
Version: 2.3
Author: "xml/swf"
Author URI: http://xmlxwf.com
Stable tag: 2.3

*/

//error_reporting(E_ALL);
//ini_set('display_errors', 'On');


// core initiation
if( !class_Exists('vooMainStart') ){
	class vooMainStart{
		var $locale;
		function __construct( $locale, $includes, $path ){
			$this->locale = $locale;
			
			// include files
			foreach( $includes as $single_path ){
				include( $path.$single_path );				
			}
			// calling localization
			add_action('plugins_loaded', array( $this, 'myplugin_init' ) );
		}
		function myplugin_init() {
		 $plugin_dir = basename(dirname(__FILE__));
		 load_plugin_textdomain( $this->locale , false, $plugin_dir );
		}
	}
	
	
}


// initiate main class
new vooMainStart('wkr', array(
	'modules/scripts.php',
	'modules/cpt.php',
	'modules/hooks.php',
	'modules/settings.php',
	'modules/meta_box.php',
), dirname(__FILE__).'/' );

 
 
?>