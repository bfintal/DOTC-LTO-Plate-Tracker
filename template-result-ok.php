<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<script type="text/html" id="tmpl-result-ok">
	
	<div class="result-ok">
		<div class="date">Registration filing received on {{ data.date }}</div>
		<# if ( typeof( data.unit ) !== "undefined" && data.unit !== "" ) { #>
			<div class="unit">Vehicle: {{ data.unit }}</div>
		<# } #>
		<div class="disclaimer">* This specifies the date the filing was received by LTO, but this doesn't indicate whether the filing was returned (e.g. due to incomplete requirements). This also does not indicate whether or not the processing of the registration has finished, only when LTO has first received the filing.</div>
	</div>

</script>