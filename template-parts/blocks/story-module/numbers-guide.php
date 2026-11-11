<?php
/**
 * Story Module — Numbers Guide.
 *
 * @package LTT_Dive_In
 *
 * @var array $args Arguments from the Story Module renderer; `fields` holds the `numbers_guide` field group.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_template_part( 'template-parts/blocks/story-module/guide', null, array_merge( $args, array( 'list' => 'numbers' ) ) );
