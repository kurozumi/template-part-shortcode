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

foreach ( template_part_shortcode_get_template_files() as $filename => $file ) {
	$headers                 = get_file_data( $file, array( 'Label' => 'Template Label' ) );
	$shortcodes[ $filename ] = array(
		'label'    => $headers['Label'],
		'fields'   => array(),
		'template' => '[tps name="' . $filename . '"]',
	);
}

return $shortcodes;
