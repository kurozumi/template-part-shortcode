<?php
/**
 * Class Test_Shortcode
 *
 * @package Template_Part_Shortcode
 */

/**
 * Tests for the [tps] shortcode.
 */
class Test_Shortcode extends WP_UnitTestCase {

	/**
	 * Theme directory used by the tests.
	 *
	 * @var string
	 */
	private $theme_dir;

	/**
	 * Points the active theme at the test fixture theme.
	 */
	public function set_up() {
		parent::set_up();

		$this->theme_dir = __DIR__ . '/fixtures/theme';
		add_filter( 'template_directory', array( $this, 'get_theme_dir' ) );
		add_filter( 'stylesheet_directory', array( $this, 'get_theme_dir' ) );
		$this->reset_theme_paths();
	}

	/**
	 * Restores the original theme directory.
	 */
	public function tear_down() {
		remove_filter( 'template_directory', array( $this, 'get_theme_dir' ) );
		remove_filter( 'stylesheet_directory', array( $this, 'get_theme_dir' ) );
		$this->reset_theme_paths();

		parent::tear_down();
	}

	/**
	 * Returns the fixture theme directory.
	 *
	 * @return string
	 */
	public function get_theme_dir() {
		return $this->theme_dir;
	}

	/**
	 * Clears the theme paths that locate_template() caches in WordPress 6.4+.
	 */
	private function reset_theme_paths() {
		global $wp_stylesheet_path, $wp_template_path;

		$wp_stylesheet_path = null;
		$wp_template_path   = null;
	}

	/**
	 * The shortcode is registered for visitors who are not logged in.
	 */
	public function test_shortcode_is_registered_for_visitors() {
		wp_set_current_user( 0 );

		$this->assertTrue( shortcode_exists( 'tps' ) );
	}

	/**
	 * The shortcode renders the template from template-parts/shortcode.
	 */
	public function test_renders_template() {
		$this->assertSame( '<p>sample template</p>', do_shortcode( '[tps name="sample"]' ) );
	}

	/**
	 * Names that are not templates in template-parts/shortcode render nothing.
	 *
	 * @dataProvider data_invalid_names
	 *
	 * @param string $name Template name.
	 */
	public function test_invalid_name_renders_nothing( $name ) {
		$this->assertSame( '', do_shortcode( '[tps name="' . $name . '"]' ) );
	}

	/**
	 * Invalid template names.
	 *
	 * @return array
	 */
	public function data_invalid_names() {
		return array(
			'empty'            => array( '' ),
			'missing template' => array( 'missing' ),
			'path traversal'   => array( '../shortcode/sample' ),
			'parent directory' => array( '../../template-parts/shortcode/sample' ),
		);
	}

	/**
	 * The editor menu lists templates with their Template Label.
	 */
	public function test_editor_menu_lists_templates() {
		$tinymce    = new TemplatePartShortcodeTinyMCE();
		$shortcodes = $tinymce->get_shortcodes();

		$this->assertSame( 'Sample Template', $shortcodes['sample']['label'] );
		$this->assertSame( '[tps name="sample"]', $shortcodes['sample']['template'] );
	}
}
