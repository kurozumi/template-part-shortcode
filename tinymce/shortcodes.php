<?php
/**
 * Builds the list of template shortcodes from the active theme.
 *
 * This file is included from TemplatePartShortcodeTinyMCE::get_shortcodes(),
 * so the variables below are local to that method, not globals.
 *
 * @package Template_Part_Shortcode
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$shortcodes = array();

foreach ( glob( get_template_directory() . '/template-parts/shortcode/*.php' ) as $file ) {
	$headers                 = get_file_data( $file, array( 'Label' => 'Template Label' ) );
	$filename                = pathinfo( $file, \PATHINFO_FILENAME );
	$shortcodes[ $filename ] = array(
		'label'    => $headers['Label'],
		'fields'   => array(),
		'template' => '[tps name="' . $filename . '"]',
	);
}

if ( $shortcodes ) {
	add_shortcode(
		'tps',
		function ( $atts ) {
			$atts = shortcode_atts(
				array(
					'name' => '',
				),
				$atts
			);
			if ( ! empty( $atts['name'] ) ) {
				ob_start();
				get_template_part( 'template-parts/shortcode/' . $atts['name'] );
				$template = ob_get_contents();
				ob_end_clean();

				return $template;
			}
		}
	);
}

return $shortcodes;
