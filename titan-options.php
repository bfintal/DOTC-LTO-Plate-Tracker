<?php
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
	if ( get_option( 'dotc_lto_pt_vehicle_table_created' ) !== false ) {
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
		  KEY location (engine_number),
  		  KEY location (conduction_sticker)
		) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	// Remember that we already created the table
	update_option( 'dotc_lto_pt_vehicle_table_created', true );
}


/**
 * Add the upload results data in the admin menu
 */
add_action('admin_menu', 'register_my_custom_submenu_page');
function register_my_custom_submenu_page() {
   add_menu_page( 'Upload Vehicle Data', 'Upload Vehicle Data', 'manage_options', 'upload-vehicle-data', 'upload_vehicle_data', 'dashicons-upload' );
}


/**
 * If the upload form was imported, save the data in our dotc_lto_pt_vehicles database
 */
add_action('admin_init', 'dotc_lto_pt_save_upload_data' );
function dotc_lto_pt_save_upload_data() {

	// Only save data when we are in our own settings page
	if ( empty( $_GET['page'] ) ) {
		return;
	}
	if ( $_GET['page'] != 'upload-vehicle-data' ) {
		return;
	}

	// Save data if import results button was clicked
	if ( ! empty( $_POST ) ) {

		global $wpdb;

		$csvRows = str_getcsv( $_POST['csv'], "\n" );

		foreach ( $csvRows as $key => $value ) {

			$csvRow = str_getcsv( $value );

			// Don't save if we don't have any data
			if ( count( $csvRow[0] ) == 0 ) {
				continue;
			}

			// Don't save column headers
			if ( ! is_numeric( trim( $csvRow[0] ) ) ) {
				continue;
			}

			// We have a different timestamp format in the CSV, convert it to MYSQL format
			$openDate = DateTime::createFromFormat( 'n/j/Y', $csvRow[0] );
			$openDate = $openDate->format( 'Y-m-d H:i:s' );	
			
			// If all the results are empty, don't save the entry
			if ( empty( $csvRow[0] ) && empty( $csvRow[1] ) && empty( $csvRow[2] ) && empty( $csvRow[3] ) ) {
				continue;
			}

			// If engine number exists, don't save the entry
			$sql = $wpdb->prepare( "SELECT count(*) FROM " . $wpdb->prefix . "dotc_lto_pt_vehicles WHERE engine_number = %s", trim( $csvRow[2] ) );
			$numberOfMatches = $wpdb->get_var( $sql );
			if ( $numberOfMatches != '0' ) {
				continue;
			}

			// If conduction sticker exists, don't save the entry
			$sql = $wpdb->prepare( "SELECT count(*) FROM " . $wpdb->prefix . "dotc_lto_pt_vehicles WHERE conduction_sticker = %s", trim( $csvRow[1] ) );
			$numberOfMatches = $wpdb->get_var( $sql );
			if ( $numberOfMatches != '0' ) {
				continue;
			}

			// Save our data
			$sql = $wpdb->prepare( "INSERT IGNORE INTO " . $wpdb->prefix . "dotc_lto_pt_vehicles (date_registered, conduction_sticker, engine_number, open_datetime) values ( %d, %s, %s, %s )",
				trim( $csvRow[0] ),
				trim( $csvRow[1] ),				
				trim( $csvRow[2] ),				
				trim( $csvRow[3] )
			);
			$wpdb->query( $sql );
		}

		// Done saving, redirect to prevent page refreshes
		wp_redirect( admin_url( 'admin.php?page=upload-vehicle-data&message=done' ) );
		die();
	}
}


/**
 * Display our import form
 */
function upload_vehicle_data() {
	global $titan;
	$titan = TitanFramework::getInstance( 'dotc_lto_pt-web' );
	?>
	<h2>Upload Vehicle Data</h2>
	<form method='POST' action='admin.php?page=upload-vehicle-data'>
		<p>
			<label for='csv'>Paste your CSV vehicle data here, you can export from excel then copy & paste the exported data.
				<br>
				<textarea id='csv' name='csv' style='width: 80%; height: 400px'></textarea>
			</label>
			<br>
			<input type='submit' value='Import Vehicle Data' class='button button-primary'/>
		</p>
	</form>
	<?php
}


/**
 * Adds the import complete notice
 */
function dotc_lto_pt_import_notice() {

	if ( empty( $_GET['page'] ) ) {
		return;
	}
	if ( $_GET['page'] != 'upload-vehicle-data' ) {
		return;
	}
	if ( empty( $_GET['message'] ) ) {
		return;
	}
	if ( $_GET['message'] != 'done' ) {
		return;
	}

    ?>
    <div class="updated">
        <p>Draw results have been imported!</p>
    </div>
    <?php
}
add_action( 'admin_notices', 'dotc_lto_pt_import_notice' );