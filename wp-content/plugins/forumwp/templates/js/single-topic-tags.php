<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<script type="text/html" id="tmpl-fmwp-topic-tags-line">
	<# if ( data.length ) { #>
		<?php _e( 'Tags:' ); ?>&nbsp;
		<# _.each( data, function( tag, key, list ) { #>
			<span class="fmwp-topic-tags-list">
				<a href="{{{tag.permalink}}}">{{{tag.name}}}</a><# if ( ( key + 1 ) < data.length ) { #>,&nbsp;<# } #>
			</span>
		<# }); #>
	<# } #>
</script>