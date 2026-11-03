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

add_action( 'plugins_loaded', function(){
	load_plugin_textdomain( 'template-part-shortcode', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

});

class TemplatePartShortcodeTinyMCE {
	public function __construct() {
		defined( 'TEMPLATE_PART_SHORTCODE_TINYMCE_URI' ) or define( 'TEMPLATE_PART_SHORTCODE_TINYMCE_URI', plugin_dir_url( __FILE__ ) . 'tinymce/' );

		if ( current_user_can( 'publish_posts' ) && get_user_option( 'rich_editing' ) == 'true' ) {
			add_filter( 'mce_buttons', array( $this, 'add_button' ) );
			add_filter( 'mce_external_plugins', array( $this, 'add_plugin' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'localize' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'localize' ) );
			add_action( 'print_media_templates', array( $this, 'print_media_templates' ) );
		}
	}

	/**
	 * Add button to WP Editor.
	 *
	 * @param array $mce_buttons
	 *
	 * @return mixed
	 */
	public function add_button( $mce_buttons ) {
		array_push( $mce_buttons, 'separator', 'btnTemplatePartShortcodeMenu' );

		return $mce_buttons;
	}

	/**
	 * Add plugin JS file to list of external plugins.
	 *
	 * @param array $mce_external_plugins
	 *
	 * @return mixed
	 */
	public function add_plugin( $mce_external_plugins ) {
		$mce_external_plugins['btnTemplatePartShortcodeMenu'] = TEMPLATE_PART_SHORTCODE_TINYMCE_URI . 'plugin.js';

		return $mce_external_plugins;
	}

	/**
	 * Pass strings to JS to set the labels of the WP Editor shortcode button and menu.
	 */
	public function localize() {
		wp_localize_script( 'editor', 'templatePartShortcodeEditor', [
			'nonce'      => wp_create_nonce( 'template-part-shortcode-editor-nonce' ),
			'shortcodes' => $this->get_shortcodes(),
			'editor'     => [
				'menuTooltip' => __( 'Templatecodes', 'template-part-shortcode' ),
				'menuName'    => __( 'Templatecodes', 'template-part-shortcode' ),
			]
		] );
	}

	/**
	 * Get list of Themify shortcodes and their config
	 */
	public function get_shortcodes() {
		$shortcodes = apply_filters( 'template_part_shortcodes', include( dirname( __FILE__ ) . '/tinymce/shortcodes.php' ) );

		/* sort list of shortcodes by their priority key */
		uasort( $shortcodes, array( $this, 'sort_by_priority_key' ) );

		/* sort the fields array in each shortcode */
		foreach ( $shortcodes as $key => $def ) {
			if ( isset( $shortcodes[ $key ]['fields'] ) && ! empty( $shortcodes[ $key ]['fields'] ) ) {
				usort( $shortcodes[ $key ]['fields'], array( $this, 'sort_by_priority_key' ) );
			}
		}

		return $shortcodes;
	}

	/**
	 * Print template files that will generate the shortcode code
	 */
	public function print_media_templates( $shortcodes = null ) {
		if ( $shortcodes == null ) {
			$shortcodes = $this->get_shortcodes();
		}
		foreach ( $shortcodes as $key => $shortcode ) {
			if ( isset( $shortcode['menu'] ) ) {
				$this->print_media_templates( $shortcode['menu'] );
			} else {
				echo '<script type="text/html" id="tmpl-template-part-shortcode-' . $key . '">';
				if ( isset( $shortcode['template'] ) ) {
					echo $shortcode['template'];
				} else {
					// generate the shortcode template based on parameters
					echo '[' . $key;
					if ( isset( $shortcode['fields'] ) ) {
						foreach ( $shortcode['fields'] as $field ) {
							if ( isset( $field['ignore'] ) ) {
								continue;
							} else {
								echo '<# if ( data.' . $field['name'] . ' ) { #> ' . $field['name'] . '="{{data.' . $field['name'] . '}}"<# } #>';
							}
						}
					}
					echo ']';
					if ( isset( $shortcode['closing_tag'] ) && $shortcode['closing_tag'] == true ) {
						echo isset( $shortcode['wrap_with'] ) ? $shortcode['wrap_with'] : '{{{data.selectedContent}}}';
						echo '[/' . $key . ']';
					}
				}
				echo '</script>';
			}
		}
	}

	/**
	 * Callback for usort and uasort, sorts the array by the 'priority' key
	 * Default priority for all keys is 10
	 *
	 * @return int
	 */
	public function sort_by_priority_key( $item1, $item2 ) {
		$i1p = isset( $item1['priority'] ) ? $item1['priority'] : 10;
		$i2p = isset( $item2['priority'] ) ? $item2['priority'] : 10;
		if ( $i1p == $i2p ) {
			return 0;
		}

		return $i1p < $i2p ? - 1 : 1;
	}
}


function template_part_shortcode_init_tinymce() {
	new TemplatePartShortcodeTinyMCE();
}

add_action( 'init', 'template_part_shortcode_init_tinymce' );
