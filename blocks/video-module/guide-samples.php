<?php
/**
 * Block Guide samples for Video Module.
 *
 * Videos come from each site's own sources, so every layout reuses a Video
 * Module already published on this site with that layout.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$samples = array();

foreach ( array( 'editorial', 'full_bleed', 'full_bleed_copy', 'carousel', 'episodic' ) as $variant ) {
	$samples[ $variant ] = array( '__existing' => array( 'ltt_dive_in_video_module_variant' => $variant ) );
}

return $samples;
