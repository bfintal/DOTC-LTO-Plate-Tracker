<?php
	
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
					$('body').trigger('lto_search_form_receive', { 'data': data } );
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
		$('html').on('lto_search_form_receive', 'body', function( data ) {
			$('.lto_search_form_results').hide().html( data.data ).fadeIn();
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
	// $categoryIDs = explode( ',', $_POST['category_ids'] );
//
// 	foreach ( $categoryIDs as $position => $categoryID ) {
// 		$wpdb->query( $wpdb->prepare( "UPDATE dotc_project_categories SET position = %d WHERE id = %d;", $position, $categoryID ) );
// 	}

	echo "Works";
	wp_die();
}
	
?>