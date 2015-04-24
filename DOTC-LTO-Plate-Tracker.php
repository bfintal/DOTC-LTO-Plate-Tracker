<?php
/**
* Plugin Name: DOTC-LTO Plate Tracker
* Plugin URI: https://www.dotc.gov.ph
* Description: Provides means of checking legitimacy of a given vehicle information, such as plate number.
* Version: 1.0
* Author: Gambit Technologies, Inc.
* Author URI: http://gambit.ph
* License: Proprietary
* Text Domain: dotc-lto-pt
* Domain Path: /languages
*/

require_once( 'shortcodes.php' );
	
// Shortcode for checker form
function checker_form() {
	echo "I CHECK YOUR PLATES!";
}

// Shortcode for checker output?
function checker_output() {
	echo "";
}
