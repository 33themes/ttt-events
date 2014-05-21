<?php
/*
Plugin Name: TTT Events
Plugin URI: http://www.33themes.com
Description: Simple and quickly events manager
Version: 0.1
Author: 33 Themes GmbH
Author URI: http://www.33themes.com
*/





define('TTTINC_EVENTS', dirname(__FILE__) );
define('TTTVERSION_EVENTS', 0.1 );


function ttt_autoload_events( $class ) {
	if ( 0 !== strpos( $class, 'TTTEvents_' ) )
		return;
	
	$file = TTTINC_EVENTS . '/class/' . $class . '.php';
	if (is_file($file))
		require_once $file;
		return true;
	
	throw new Exception("Unable to load $class at ".$file);
}

if ( function_exists( 'spl_autoload_register' ) ) {
	spl_autoload_register( 'ttt_autoload_events' );
} else {
	require_once TTTINC_EVENTS . '/class/TTTEvents_Common.php';
}

function tttevents_init () {
	$s = load_plugin_textdomain( 'tttevents', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	if ( !is_admin() ) {
		global $TTTEvents_Front;
		$TTTEvents_Front = new TTTEvents_Front();
		$TTTEvents_Front->init();
	}
	else {
		$TTTEvents_Admin = new TTTEvents_Admin();
		$TTTEvents_Admin->init();
	}

}

add_action('init', 'tttevents_init');

register_deactivation_hook( __FILE__ ,'tttevents_uninstall' );
register_uninstall_hook( __FILE__ , 'tttevents_uninstall' );

function tttevents_uninstall() {
	require_once TTTINC_EVENTS . '/class/TTTEvents_Common.php';
	require_once TTTINC_EVENTS . '/class/TTTEvents_Admin.php';

	$TTTEvents_Admin = new TTTEvents_Admin();
	$TTTEvents_Admin->uninstall();
}


