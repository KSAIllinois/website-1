<?php

// Note that 'jetpack-layout-grid' gets replaced with 'jetpack-layout-grid' when bundling
add_action( 'init', function() {
	register_block_type( 'jetpack/layout-grid', [
		'editor_script' => 'jetpack-layout-grid',
		'style' => 'jetpack-layout-grid',
		'editor_style' => 'jetpack-layout-grid-editor',
	] );

	register_block_type( 'jetpack/layout-grid-column', [
		'editor_script' => 'jetpack-layout-grid',
		'style' => 'jetpack-layout-grid',
		'editor_style' => 'jetpack-layout-grid-editor',
	] );

	wp_set_script_translations( 'jetpack-layout-grid', 'layout-grid' );
} );
