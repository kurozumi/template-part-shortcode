<?php
/**
 * Plugin Name:     Template Part Shortcode
 * Description:     Enables template shortcode in TinyMCE, the visual editor in WordPress.
 * Author:          akira kurozumi
 * Author URI:      https://a-zumi.net
 * Text Domain:     template-part-shortcode
 * Domain Path:     /languages
 * Version:         0.1.0
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
