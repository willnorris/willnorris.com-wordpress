<?php
/*
Plugin Name: Manual Control for Jetpack
Description: Prevents the Jetpack plugin from auto-activating its new features.
Version: 0.1
Author: Mark Jaquith
Author URI: http://coveredwebservices.com/
*/

class CWS_Manual_Control_for_Jetpack_Plugin {
	static $instance;
	static $jetpack;
	const MESSAGE = 'stop_auto_activating';

	function __construct() {
		self::$instance = $this;
		add_action( 'init', array( $this, 'init' ), 11 );
	}

	function init() {
		if ( class_exists( 'Jetpack' ) ) {
			if ( method_exists( 'Jetpack', 'init' ) )
				self::$jetpack = Jetpack::init();
			add_action( 'load-toplevel_page_jetpack', array( $this, 'before_load' ), 9 );
		}
	}

	function before_load() {
		if ( class_exists( 'Jetpack' ) ) {
			Jetpack::state( 'error', self::MESSAGE );
			add_action( 'jetpack_notices', array( $this, 'kill_notice' ), 0 );
		}
	}

	function kill_notice() {
		if ( Jetpack::state( 'error' ) == self::MESSAGE ) {
			self::$jetpack->error = false;
		}
	}

}

new CWS_Manual_Control_for_Jetpack_Plugin;
