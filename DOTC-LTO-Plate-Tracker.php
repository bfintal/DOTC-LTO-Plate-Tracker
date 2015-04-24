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
defined( 'LTO_VERSION' ) or define( 'LTO_VERSION', '1.0' );

// Increment this when the database structure changes
defined( 'DB_VERSION' ) or define( 'DB_VERSION', '1.0' );

// Load required files
require_once( 'titan-framework-checker.php' );
require_once( 'titan-options.php' );


/**
 * Creates the table wp_dotc_lto_pt_vehicles
 */
register_activation_hook( __FILE__ , 'dotc_lto_pt_create_vehicle_table' );
function dotc_lto_pt_create_vehicle_table() {

	// Only do this in the admin
	if ( ! is_admin() ) {
		return;
	}

	// Check if table exists
	if ( get_option( 'dotc_lto_pt_vehicle_table_created' ) === DB_VERSION ) {
		return;
	}

	// Run code that creates table
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE " . $wpdb->prefix . "dotc_lto_pt_vehicles (
		  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		  received_date datetime NOT NULL,
		  engine_number varchar(50) NOT NULL,
		  conduction_sticker varchar(50) NOT NULL,
		  unit varchar(10) NOT NULL,
		  PRIMARY KEY  (id),
		  UNIQUE KEY engine_number (engine_number),
  		  UNIQUE KEY conduction_sticker (conduction_sticker)
		) $charset_collate;";
		
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	// Remember that we already created the table
	update_option( 'dotc_lto_pt_vehicle_table_created', DB_VERSION );
}