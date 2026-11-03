<?php
/**
 * TinyMCE integration for template shortcodes.
 *
 * @package Template_Part_Shortcode
 */

/**
 * Adds the template shortcode menu to TinyMCE.
 */
class TemplatePartShortcodeTinyMCE {
	/**
	 * Registers the editor hooks for users who can use the visual editor.
	 */
	public function __construct() {
		if ( ! defined( 'TEMPLATE_PART_SHORTCODE_TINYMCE_URI' ) ) {
			define( 'TEMPLATE_PART_SHORTCODE_TINYMCE_URI', plugin_dir_url( __DIR__ ) . 'tinymce/' );
		}

		if ( current_user_can( 'publish_posts' ) && 'true' === get_user_option( 'rich_editing' ) ) {
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
	 * @param array $mce_buttons TinyMCE buttons.
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
	 * @param array $mce_external_plugins TinyMCE external plugins.
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
		wp_localize_script(
			'editor',
			'templatePartShortcodeEditor',
			array(
				'nonce'      => wp_create_nonce( 'template-part-shortcode-editor-nonce' ),
				'shortcodes' => $this->get_shortcodes(),
				'editor'     => array(
					'menuTooltip' => __( 'Templatecodes', 'template-part-shortcode' ),
					'menuName'    => __( 'Templatecodes', 'template-part-shortcode' ),
				),
			)
		);
	}

	/**
	 * Get list of Themify shortcodes and their config
	 */
	public function get_shortcodes() {
		$shortcodes = apply_filters( 'template_part_shortcodes', include dirname( __DIR__ ) . '/tinymce/shortcodes.php' );

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
	 *
	 * @param array|null $shortcodes Shortcodes to print. Defaults to all shortcodes.
	 */
	public function print_media_templates( $shortcodes = null ) {
		if ( empty( $shortcodes ) ) {
			$shortcodes = $this->get_shortcodes();
		}
		foreach ( $shortcodes as $key => $shortcode ) {
			if ( isset( $shortcode['menu'] ) ) {
				$this->print_media_templates( $shortcode['menu'] );
			} else {
				// The contents are wp.template() markup, so they must be output unescaped.
				// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<script type="text/html" id="tmpl-template-part-shortcode-' . esc_attr( $key ) . '">';
				if ( isset( $shortcode['template'] ) ) {
					echo $shortcode['template'];
				} else {
					// Generate the shortcode template based on parameters.
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
					if ( ! empty( $shortcode['closing_tag'] ) ) {
						echo isset( $shortcode['wrap_with'] ) ? $shortcode['wrap_with'] : '{{{data.selectedContent}}}';
						echo '[/' . $key . ']';
					}
				}
				echo '</script>';
				// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}
	}

	/**
	 * Callback for usort and uasort, sorts the array by the 'priority' key
	 * Default priority for all keys is 10
	 *
	 * @param array $item1 First item to compare.
	 * @param array $item2 Second item to compare.
	 *
	 * @return int
	 */
	public function sort_by_priority_key( $item1, $item2 ) {
		$i1p = isset( $item1['priority'] ) ? $item1['priority'] : 10;
		$i2p = isset( $item2['priority'] ) ? $item2['priority'] : 10;
		if ( $i1p == $i2p ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual -- priority may be int or numeric string.
			return 0;
		}

		return $i1p < $i2p ? - 1 : 1;
	}
}
