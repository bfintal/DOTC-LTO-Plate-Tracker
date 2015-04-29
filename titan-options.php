<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


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
			
			$csvRow = array_map( 'trim', $csvRow );
			
			// Don't save if we don't have any data
			if ( count( $csvRow ) == 0 ) {
				continue;
			}

			// Stop if first row isn't even alphanumeric, indicative it's not a date.
			if ( ! preg_match( '/^[0-9\/]+$/', $csvRow[0] ) ) {
			    continue;
			}					

			// We have a different timestamp format in the CSV, convert it to MYSQL format
			$regDate = DateTime::createFromFormat( 'm/d/Y', $csvRow[0] );
			$regDated = $regDate->format( 'Y-m-d' );		

			// If all the results are empty, don't save the entry
			if ( empty( $csvRow[0] ) && empty( $csvRow[1] ) && empty( $csvRow[2] ) ) {
				continue;
			}
						
			// If engine number exists, don't save the entry
			$sql = $wpdb->prepare( "SELECT count(*) FROM " . $wpdb->prefix . "dotc_lto_pt_vehicles WHERE engine_number = %s", trim( $csvRow[1] ) );
			$numberOfMatches = $wpdb->get_var( $sql );
			if ( $numberOfMatches != '0' ) {
				continue;
			}

			// Save our data
			$sql = $wpdb->prepare( "INSERT IGNORE INTO " . $wpdb->prefix . "dotc_lto_pt_vehicles (received_date, engine_number, unit) values ( %s, %s, %s )",
				$regDated,
				trim( $csvRow[1] ),				
				trim( $csvRow[2] )
			);
			
			//echo $sql;
			//die();
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
        <p>Vehicle information have been successfully imported.</p>
    </div>
    <?php
}
add_action( 'admin_notices', 'dotc_lto_pt_import_notice' );