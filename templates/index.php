<?php
/**
 * Template for the hovercard.
 *
 * @package   o2-hovercards
 * @subpackage \templates\index
 *
 * @since 1.0.0
 */
?>
<script type="text/html" id="tmpl-o2-hovercard">
	<div class="o2hc-container">
		<# if ( data.loader ) { #>
			<img src="{{data.loader}}" class="o2hc-loader">

		<# } else if ( data.error ) { #>
			<p class="error">{{data.errorText}}</p>

		<# } else { #>
			<div class="o2hc-description" open="true">
				<div class="o2hc-title">{{data.title}}</div>
				<div class="o2hc-subtitle">
					<a href="{{{data.url}}}">{{data.subtitle}}</a>
				</div>

				<# if ( data.description ) { #>
					<div class="description">{{{data.description}}}</div>
				<# } #>
			</div>
		<# } #>
	</div>
</script>
