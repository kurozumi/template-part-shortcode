<?php
/**
 * Plugin Name:     Template Part Shortcode
 * Description:     Enables template shortcode in TinyMCE, the visual editor in WordPress.
 * Author:          akira kurozumi
 * Author URI:      https://a-zumi.net
 * Text Domain:     template-part-shortcode
 * Domain Path:     /languages
 * Version:         0.1.1
 *
 * @package         Template_Part_Shortcode
 */

add_action(
	'plugins_loaded',
	function () {
		load_plugin_textdomain( 'template-part-shortcode', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
);

require_once __DIR__ . '/includes/class-templatepartshortcodetinymce.php';

/**
 * Initializes the TinyMCE integration.
 */
function template_part_shortcode_init_tinymce() {
	new TemplatePartShortcodeTinyMCE();
}

add_action( 'init', 'template_part_shortcode_init_tinymce' );

/**
 * Returns the template files in the theme's template-parts/shortcode directory.
 *
 * @return string[] Template file paths keyed by template name.
 */
function template_part_shortcode_get_template_files() {
	$files = array();
	$paths = glob( get_template_directory() . '/template-parts/shortcode/*.php' );

	if ( false === $paths ) {
		return $files;
	}

	foreach ( $paths as $file ) {
		$files[ pathinfo( $file, PATHINFO_FILENAME ) ] = $file;
	}

	return $files;
}

/**
 * Renders the [tps name="..."] shortcode.
 *
 * @param array|string $atts Shortcode attributes.
 *
 * @return string Rendered template.
 */
function template_part_shortcode_render( $atts ) {
	$atts = shortcode_atts(
		array(
			'name' => '',
		),
		$atts
	);

	// Only allow templates that exist in template-parts/shortcode to prevent path traversal.
	$files = template_part_shortcode_get_template_files();
	if ( '' === $atts['name'] || ! isset( $files[ $atts['name'] ] ) ) {
		return '';
	}

	ob_start();
	get_template_part( 'template-parts/shortcode/' . $atts['name'] );

	return ob_get_clean();
}

/**
 * Registers the [tps] shortcode for every request, including front-end visitors.
 */
function template_part_shortcode_register_shortcode() {
	add_shortcode( 'tps', 'template_part_shortcode_render' );
}

add_action( 'init', 'template_part_shortcode_register_shortcode' );
