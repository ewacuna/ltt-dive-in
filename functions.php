<?php
/**
 * LTT Dive In theme bootstrap.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LTT_DIVE_IN_VERSION', '1.0.0' );
define( 'LTT_DIVE_IN_DIR', get_template_directory() );
define( 'LTT_DIVE_IN_URI', get_template_directory_uri() );

require_once LTT_DIVE_IN_DIR . '/inc/setup.php';
require_once LTT_DIVE_IN_DIR . '/inc/enqueue.php';
require_once LTT_DIVE_IN_DIR . '/inc/template-tags.php';
require_once LTT_DIVE_IN_DIR . '/inc/template-functions.php';
require_once LTT_DIVE_IN_DIR . '/inc/customizer.php';
require_once LTT_DIVE_IN_DIR . '/inc/footer-settings.php';
