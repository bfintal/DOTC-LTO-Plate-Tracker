<?php
	
add_action( 'wp_footer', 'lto_include_templates' );
function lto_include_templates() {
	include LTO_PATH . 'template-result-no.php';
	include LTO_PATH . 'template-result-ok.php';
}

add_action( 'wp_enqueue_scripts', 'lt_include_templating' );
function lt_include_templating() {
	wp_enqueue_script( 'wp-util' );
}
	
add_shortcode( 'lto_search_form', 'lto_search_form' );
function lto_search_form( $atts, $content = '' ) {
	$atts = shortcode_atts( array(
		'value' => '',
		'placeholder' => '',
		'label' => '',
		'search_label' => 'Search',
	), $atts );
	
	ob_start();
	?>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('body').on('click', '.lto_search_form button', function() {
			$.post( 
				'<?php echo esc_url_raw( admin_url( 'admin-ajax.php' ) ) ?>',
				{
					'action': 'plate_search',
					'nonce': '<?php echo esc_attr( wp_create_nonce( 'plate_search' ) ) ?>',
					'search': $(this).parent().find('input[type="text"]').val()
				},
				function( data ) {
					data = JSON.parse( data );
					$('body').trigger('lto_search_form_receive', [{ 'data': data }] );
				}
			);
		});
	});
	</script>
	<div class="lto_search_form">
		<label for="lto_search_form_field">
			<?php echo esc_html( $atts['label'] ) ?>
			<input type="text" id="lto_search_form_field" name="lto_search_form_field" placeholder="<?php echo esc_attr( $atts['placeholder'] ) ?>" value="<?php echo esc_attr( $atts['value'] ) ?>">
		</label>
		<button><?php echo esc_attr( $atts['search_label'] ) ?></button>
	</div>
	<?php
	return ob_get_clean();
}
	
add_shortcode( 'lto_search_form_results', 'lto_search_form_results' );
function lto_search_form_results( $atts, $content = '' ) {
	$atts = shortcode_atts( array(
	), $atts );
	
	ob_start();
	?>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('html').on('lto_search_form_receive', 'body', function( e, result ) {
			
			var template = 'result-ok';
			if ( result.data === false ) {
				template = 'result-no';
			}
			
			$('.lto_search_form_results').hide().html(
				wp.template( template )( result.data )
			).fadeIn();
		});
	});
	</script>
	<div class="lto_search_form_results"></div>
	<?php
	return ob_get_clean();
}



add_action( "wp_ajax_plate_search", "wp_ajax_plate_search" );
add_action( "wp_ajax_nopriv_plate_search", "wp_ajax_plate_search" );
function wp_ajax_plate_search() {
	if ( ! wp_verify_nonce( $_REQUEST['nonce'], "plate_search" ) ) {
		wp_die();
	}

	if ( empty( $_POST['search'] ) ) {
		wp_die();
	}

	global $wpdb;
	$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "dotc_lto_pt_vehicles WHERE engine_number = %s OR conduction_sticker = %s", $_POST['search'], $_POST['search'] ) );
	
	if ( ! empty( $row ) ) {
		echo json_encode( array(
			'date' => date( 'F d Y', strtotime( $row->received_date ) ),
			'unit' => $row->unit,
		) );
	} else {
		echo json_encode( false );
	}

	wp_die();
}
	
?>