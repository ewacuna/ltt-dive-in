<?php
/**
 * Current Conditions template helpers.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether a page uses the Current Conditions template.
 *
 * @param int $post_id Page ID. Defaults to the current queried page.
 * @return bool
 */
function ltt_dive_in_is_current_conditions_page( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_queried_object_id();

	return $post_id && 'templates/current-conditions.php' === get_page_template_slug( $post_id );
}

/**
 * Resolve the page that owns the shared Current Conditions hero.
 *
 * @param int $post_id Page ID. Defaults to the current queried page.
 * @return int Parent page ID for a tab, otherwise the page itself.
 */
function ltt_dive_in_get_current_conditions_hub_id( $post_id = 0 ) {
	$post_id   = $post_id ? absint( $post_id ) : get_queried_object_id();
	$parent_id = (int) wp_get_post_parent_id( $post_id );

	return $parent_id && ltt_dive_in_is_current_conditions_page( $parent_id ) ? $parent_id : $post_id;
}

/**
 * Whether a page is the Current Conditions parent that owns the shared hero.
 *
 * @param int $post_id Page ID.
 * @return bool
 */
function ltt_dive_in_is_current_conditions_hub( $post_id ) {
	$post_id = absint( $post_id );

	return ltt_dive_in_is_current_conditions_page( $post_id ) && ltt_dive_in_get_current_conditions_hub_id( $post_id ) === $post_id;
}

/**
 * Hide the Featured image panel on the Current Conditions parent page.
 */
function ltt_dive_in_enqueue_current_conditions_editor_assets() {
	if ( ! ltt_dive_in_is_current_conditions_hub( get_the_ID() ) ) {
		return;
	}

	$script_path = LTT_DIVE_IN_DIR . '/assets/js/admin/current-conditions-editor.js';

	wp_enqueue_script(
		'ltt-dive-in-current-conditions-editor',
		LTT_DIVE_IN_URI . '/assets/js/admin/current-conditions-editor.js',
		array( 'wp-data', 'wp-editor', 'wp-edit-post' ),
		file_exists( $script_path ) ? (string) filemtime( $script_path ) : LTT_DIVE_IN_VERSION,
		true
	);
}
add_action( 'enqueue_block_editor_assets', 'ltt_dive_in_enqueue_current_conditions_editor_assets' );

/**
 * Hide hero design fields that the compact Current Conditions hero does not use.
 *
 * @param array|false $field ACF field prepared for rendering.
 * @return array|false
 */
function ltt_dive_in_hide_current_conditions_hero_fields( $field ) {
	return ltt_dive_in_is_current_conditions_page( get_the_ID() ) ? false : $field;
}
foreach ( array(
	'field_ltt_dive_in_page_header_hero_variant',
	'field_ltt_dive_in_meetings_hero_title',
	'field_ltt_dive_in_meetings_hero_text',
	'field_ltt_dive_in_meetings_hero_icon',
) as $ltt_dive_in_hidden_field_key ) {
	add_filter( 'acf/prepare_field/key=' . $ltt_dive_in_hidden_field_key, 'ltt_dive_in_hide_current_conditions_hero_fields' );
}
unset( $ltt_dive_in_hidden_field_key );

/**
 * Register the live-module fields for Current Conditions tabs.
 */
function ltt_dive_in_register_current_conditions_tab_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$weather_only = array(
		array(
			array(
				'field'    => 'field_ltt_dive_in_conditions_tab_module',
				'operator' => '==',
				'value'    => 'weather',
			),
		),
	);

	$roads_only = array(
		array(
			array(
				'field'    => 'field_ltt_dive_in_conditions_tab_module',
				'operator' => '==',
				'value'    => 'roads',
			),
		),
	);

	$cams_only = array(
		array(
			array(
				'field'    => 'field_ltt_dive_in_conditions_tab_module',
				'operator' => '==',
				'value'    => 'cams',
			),
		),
	);

	acf_add_local_field_group(
		array(
			'key'          => 'group_ltt_dive_in_current_conditions_tab',
			'title'        => __( 'LTT Dive In — Current Conditions Tab', 'ltt-dive-in' ),
			'description'  => __( 'The featured image is the background behind the tab intro and its live module.', 'ltt-dive-in' ),
			'fields'       => array(
				array(
					'key'           => 'field_ltt_dive_in_conditions_tab_module',
					'label'         => __( 'Live module', 'ltt-dive-in' ),
					'name'          => 'ltt_dive_in_conditions_tab_module',
					'type'          => 'select',
					'instructions'  => __( 'Shown below the tab intro.', 'ltt-dive-in' ),
					'choices'       => array(
						''        => __( 'None', 'ltt-dive-in' ),
						'weather' => __( 'Current weather', 'ltt-dive-in' ),
						'roads'   => __( 'Road conditions', 'ltt-dive-in' ),
						'cams'    => __( 'Live cameras', 'ltt-dive-in' ),
					),
					'default_value' => '',
					'return_format' => 'value',
				),
				array(
					'key'               => 'field_ltt_dive_in_weather_locations',
					'label'             => __( 'Weather locations', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_weather_locations',
					'type'              => 'repeater',
					'instructions'      => __( 'Each location becomes a city tile; selecting a tile shows its weather in the main panel. The first location is shown when the page loads. Drag rows to reorder.', 'ltt-dive-in' ),
					'required'          => 1,
					'min'               => 1,
					'max'               => 12,
					'layout'            => 'block',
					'button_label'      => __( 'Add location', 'ltt-dive-in' ),
					'collapsed'         => 'field_ltt_dive_in_weather_location_row_name',
					'conditional_logic' => $weather_only,
					'sub_fields'        => array(
						array(
							'key'       => 'field_ltt_dive_in_weather_location_row_name',
							'label'     => __( 'Name', 'ltt-dive-in' ),
							'name'      => 'name',
							'type'      => 'text',
							'required'  => 1,
							'maxlength' => 40,
							'wrapper'   => array( 'width' => '40' ),
						),
						array(
							'key'          => 'field_ltt_dive_in_weather_location_latitude',
							'label'        => __( 'Latitude', 'ltt-dive-in' ),
							'name'         => 'latitude',
							'type'         => 'number',
							'instructions' => __( 'Decimal degrees, for example 39.1677.', 'ltt-dive-in' ),
							'required'     => 1,
							'min'          => -90,
							'max'          => 90,
							'step'         => 'any',
							'wrapper'      => array( 'width' => '20' ),
						),
						array(
							'key'          => 'field_ltt_dive_in_weather_location_longitude',
							'label'        => __( 'Longitude', 'ltt-dive-in' ),
							'name'         => 'longitude',
							'type'         => 'number',
							'instructions' => __( 'Decimal degrees, for example -120.1452.', 'ltt-dive-in' ),
							'required'     => 1,
							'min'          => -180,
							'max'          => 180,
							'step'         => 'any',
							'wrapper'      => array( 'width' => '20' ),
						),
						array(
							'key'          => 'field_ltt_dive_in_weather_location_elevation',
							'label'        => __( 'Elevation (ft)', 'ltt-dive-in' ),
							'name'         => 'elevation',
							'type'         => 'number',
							'instructions' => __( 'Optional. Hidden when empty.', 'ltt-dive-in' ),
							'min'          => -300,
							'max'          => 15000,
							'step'         => 1,
							'wrapper'      => array( 'width' => '20' ),
						),
					),
				),
				array(
					'key'               => 'field_ltt_dive_in_roads_area_center_latitude',
					'label'             => __( 'Area center latitude', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_roads_area_center_latitude',
					'type'              => 'number',
					'instructions'      => __( 'Road reports, closures, and cameras are shown only within the radius below. Decimal degrees; the default is the middle of Lake Tahoe.', 'ltt-dive-in' ),
					'default_value'     => LTT_DIVE_IN_ROADS_DEFAULT_LATITUDE,
					'min'               => -90,
					'max'               => 90,
					'step'              => 'any',
					'wrapper'           => array( 'width' => '33' ),
					'conditional_logic' => $roads_only,
				),
				array(
					'key'               => 'field_ltt_dive_in_roads_area_center_longitude',
					'label'             => __( 'Area center longitude', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_roads_area_center_longitude',
					'type'              => 'number',
					'instructions'      => __( 'Decimal degrees, for example -120.0324.', 'ltt-dive-in' ),
					'default_value'     => LTT_DIVE_IN_ROADS_DEFAULT_LONGITUDE,
					'min'               => -180,
					'max'               => 180,
					'step'              => 'any',
					'wrapper'           => array( 'width' => '33' ),
					'conditional_logic' => $roads_only,
				),
				array(
					'key'               => 'field_ltt_dive_in_roads_area_radius',
					'label'             => __( 'Area radius (miles)', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_roads_area_radius',
					'type'              => 'number',
					'instructions'      => __( 'How far from the center a report may be. Larger values include more of each highway.', 'ltt-dive-in' ),
					'default_value'     => LTT_DIVE_IN_ROADS_DEFAULT_RADIUS,
					'min'               => 5,
					'max'               => 150,
					'step'              => 1,
					'wrapper'           => array( 'width' => '33' ),
					'conditional_logic' => $roads_only,
				),
				array(
					'key'               => 'field_ltt_dive_in_roads_highways',
					'label'             => __( 'Highways', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_roads_highways',
					'type'              => 'repeater',
					'instructions'      => __( 'Each highway becomes a card and an option in “Filter Highway”, in this order. Only reports inside the area above are listed.', 'ltt-dive-in' ),
					'required'          => 1,
					'min'               => 1,
					'max'               => 12,
					'layout'            => 'block',
					'button_label'      => __( 'Add highway', 'ltt-dive-in' ),
					'collapsed'         => 'field_ltt_dive_in_roads_highway_name',
					'conditional_logic' => $roads_only,
					'sub_fields'        => array(
						array(
							'key'           => 'field_ltt_dive_in_roads_highway_shield',
							'label'         => __( 'Route type', 'ltt-dive-in' ),
							'name'          => 'shield',
							'type'          => 'select',
							'instructions'  => __( 'Sets the route sign and the reports matched to the card.', 'ltt-dive-in' ),
							'required'      => 1,
							'choices'       => array(
								'interstate' => __( 'Interstate (I-)', 'ltt-dive-in' ),
								'us'         => __( 'U.S. Route (US-)', 'ltt-dive-in' ),
								'state'      => __( 'State Route (SR-)', 'ltt-dive-in' ),
							),
							'default_value' => 'state',
							'return_format' => 'value',
							'wrapper'       => array( 'width' => '25' ),
						),
						array(
							'key'      => 'field_ltt_dive_in_roads_highway_number',
							'label'    => __( 'Route number', 'ltt-dive-in' ),
							'name'     => 'number',
							'type'     => 'number',
							'required' => 1,
							'min'      => 1,
							'max'      => 999,
							'step'     => 1,
							'wrapper'  => array( 'width' => '15' ),
						),
						array(
							'key'          => 'field_ltt_dive_in_roads_highway_name',
							'label'        => __( 'Name', 'ltt-dive-in' ),
							'name'         => 'name',
							'type'         => 'text',
							'instructions' => __( 'For example, “Interstate 80” or “Highway 50”.', 'ltt-dive-in' ),
							'required'     => 1,
							'maxlength'    => 40,
							'wrapper'      => array( 'width' => '30' ),
						),
						array(
							'key'          => 'field_ltt_dive_in_roads_highway_subtitle',
							'label'        => __( 'Subtitle', 'ltt-dive-in' ),
							'name'         => 'subtitle',
							'type'         => 'text',
							'instructions' => __( 'Optional. For example, “Sacramento to Truckee / Donner”.', 'ltt-dive-in' ),
							'maxlength'    => 60,
							'wrapper'      => array( 'width' => '30' ),
						),
						array(
							'key'           => 'field_ltt_dive_in_roads_highway_states',
							'label'         => __( 'Show under', 'ltt-dive-in' ),
							'name'          => 'states',
							'type'          => 'checkbox',
							'instructions'  => __( 'Choose both for highways that cross the state line, such as US-50.', 'ltt-dive-in' ),
							'required'      => 1,
							'choices'       => array(
								'california' => __( 'California', 'ltt-dive-in' ),
								'nevada'     => __( 'Nevada', 'ltt-dive-in' ),
							),
							'default_value' => array( 'california' ),
							'layout'        => 'horizontal',
							'return_format' => 'value',
							'wrapper'       => array( 'width' => '30' ),
						),
						array(
							'key'           => 'field_ltt_dive_in_roads_highway_sign',
							'label'         => __( 'Route sign', 'ltt-dive-in' ),
							'name'          => 'sign',
							'type'          => 'image',
							'instructions'  => __( 'Optional. The route sign from the Media Library: an SVG (sanitized by Safe SVG), or a transparent PNG or WebP about 124 px wide. Leave empty to show a generic sign for the route type and number. The sign is decorative, so its alt text is not read on this page.', 'ltt-dive-in' ),
							'return_format' => 'id',
							'preview_size'  => 'thumbnail',
							'library'       => 'all',
							'mime_types'    => 'png,webp,svg',
							'wrapper'       => array( 'width' => '35' ),
						),
						array(
							'key'               => 'field_ltt_dive_in_roads_highway_sign_nevada',
							'label'             => __( 'Nevada route sign', 'ltt-dive-in' ),
							'name'              => 'sign_nevada',
							'type'              => 'image',
							'instructions'      => __( 'Optional. Shown under Nevada when the state uses a different sign, such as SR-28. Leave empty to use the route sign above.', 'ltt-dive-in' ),
							'return_format'     => 'id',
							'preview_size'      => 'thumbnail',
							'library'           => 'all',
							'mime_types'        => 'png,webp,svg',
							'wrapper'           => array( 'width' => '35' ),
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'field_ltt_dive_in_roads_highway_states',
										'operator' => '==',
										'value'    => 'nevada',
									),
								),
							),
						),
						array(
							'key'           => 'field_ltt_dive_in_roads_highway_camera_mode',
							'label'         => __( 'Cameras', 'ltt-dive-in' ),
							'name'          => 'camera_mode',
							'type'          => 'button_group',
							'instructions'  => __( 'Automatic shows the nearest camera on this highway beside each report.', 'ltt-dive-in' ),
							'choices'       => array(
								'auto'   => __( 'Automatic', 'ltt-dive-in' ),
								'manual' => __( 'Choose', 'ltt-dive-in' ),
								'none'   => __( 'None', 'ltt-dive-in' ),
							),
							'default_value' => 'auto',
							'return_format' => 'value',
							'wrapper'       => array( 'width' => '30' ),
						),
						array(
							'key'               => 'field_ltt_dive_in_roads_highway_cameras',
							'label'             => __( 'Chosen cameras', 'ltt-dive-in' ),
							'name'              => 'cameras',
							'type'              => 'select',
							'instructions'      => __( 'Cameras inside the area, from the latest provider data. The nearest chosen camera is shown beside each report.', 'ltt-dive-in' ),
							'choices'           => array(),
							'multiple'          => 1,
							'ui'                => 1,
							'allow_null'        => 1,
							'return_format'     => 'value',
							'wrapper'           => array( 'width' => '40' ),
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'field_ltt_dive_in_roads_highway_camera_mode',
										'operator' => '==',
										'value'    => 'manual',
									),
								),
							),
						),
					),
				),
				array(
					'key'               => 'field_ltt_dive_in_live_cams',
					'label'             => __( 'Live cameras', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_live_cams',
					'type'              => 'repeater',
					'instructions'      => __( 'Up to four cameras. The first sits on the page background; the others get their own band.', 'ltt-dive-in' ),
					'required'          => 1,
					'min'               => 1,
					'max'               => 4,
					'layout'            => 'block',
					'button_label'      => __( 'Add camera', 'ltt-dive-in' ),
					'collapsed'         => 'field_ltt_dive_in_live_cam_heading',
					'conditional_logic' => $cams_only,
					'sub_fields'        => array(
						array(
							'key'          => 'field_ltt_dive_in_live_cam_heading',
							'label'        => __( 'Heading', 'ltt-dive-in' ),
							'name'         => 'heading',
							'type'         => 'text',
							'instructions' => __( 'For example, “Lake Cam”.', 'ltt-dive-in' ),
							'required'     => 1,
							'maxlength'    => 40,
							'wrapper'      => array( 'width' => '40' ),
						),
						array(
							'key'          => 'field_ltt_dive_in_live_cam_text',
							'label'        => __( 'Description', 'ltt-dive-in' ),
							'name'         => 'text',
							'type'         => 'textarea',
							'instructions' => __( 'Optional. One or two sentences.', 'ltt-dive-in' ),
							'maxlength'    => 220,
							'rows'         => 2,
							'new_lines'    => '',
							'wrapper'      => array( 'width' => '60' ),
						),
						array(
							'key'           => 'field_ltt_dive_in_live_cam_link',
							'label'         => __( 'Live feed link', 'ltt-dive-in' ),
							'name'          => 'link',
							'type'          => 'link',
							'instructions'  => __( 'Opens when the image is selected. The link text is read only by screen readers, for example “Watch the Lake Cam live”.', 'ltt-dive-in' ),
							'required'      => 1,
							'return_format' => 'array',
							'wrapper'       => array( 'width' => '50' ),
						),
						array(
							'key'               => 'field_ltt_dive_in_live_cam_image_url',
							'label'             => __( 'Latest image URL', 'ltt-dive-in' ),
							'name'              => 'image_url',
							'type'              => 'url',
							'instructions'      => __( 'Optional. The provider’s authorized link to the camera’s newest image. While it works, it replaces the camera image.', 'ltt-dive-in' ),
							'placeholder'       => 'https://',
							'wrapper'           => array( 'width' => '50' ),
						),
						array(
							'key'           => 'field_ltt_dive_in_live_cam_image',
							'label'         => __( 'Camera image', 'ltt-dive-in' ),
							'name'          => 'image',
							'type'          => 'image',
							'instructions'  => __( 'Landscape, at least 1220px wide. Describe the view in its alt text.', 'ltt-dive-in' ),
							'required'      => 1,
							'return_format' => 'id',
							'preview_size'  => 'medium',
							'library'       => 'all',
							'min_width'     => 600,
							'mime_types'    => 'jpg, jpeg, png, webp',
							'wrapper'       => array( 'width' => '50' ),
						),
						array(
							'key'           => 'field_ltt_dive_in_live_cam_background',
							'label'         => __( 'Band background', 'ltt-dive-in' ),
							'name'          => 'background',
							'type'          => 'image',
							'instructions'  => __( 'Optional. Not used by the first camera. Without one, the band is navy.', 'ltt-dive-in' ),
							'return_format' => 'id',
							'preview_size'  => 'medium',
							'library'       => 'all',
							'mime_types'    => 'jpg, jpeg, png, webp',
							'wrapper'       => array( 'width' => '50' ),
						),
					),
				),
				array(
					'key'               => 'field_ltt_dive_in_ski_cams_heading',
					'label'             => __( 'Ski cams heading', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_ski_cams_heading',
					'type'              => 'text',
					'instructions'      => __( 'Optional section below the cameras that links to resort ski cams on other sites. Leave the heading empty to hide it.', 'ltt-dive-in' ),
					'default_value'     => 'Find a Ski Cam',
					'maxlength'         => 60,
					'wrapper'           => array( 'width' => '50' ),
					'conditional_logic' => $cams_only,
				),
				array(
					'key'               => 'field_ltt_dive_in_ski_cams_eyebrow',
					'label'             => __( 'Ski cams label', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_ski_cams_eyebrow',
					'type'              => 'text',
					'instructions'      => __( 'Optional. Short tag above the heading.', 'ltt-dive-in' ),
					'default_value'     => 'Live feed',
					'maxlength'         => 30,
					'wrapper'           => array( 'width' => '50' ),
					'conditional_logic' => $cams_only,
				),
				array(
					'key'               => 'field_ltt_dive_in_ski_cams_intro',
					'label'             => __( 'Ski cams introduction', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_ski_cams_intro',
					'type'              => 'textarea',
					'instructions'      => __( 'Optional. One short paragraph.', 'ltt-dive-in' ),
					'maxlength'         => 400,
					'rows'              => 3,
					'new_lines'         => '',
					'conditional_logic' => $cams_only,
				),
				array(
					'key'               => 'field_ltt_dive_in_ski_cams_featured',
					'label'             => __( 'Featured ski cam', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_ski_cams_featured',
					'type'              => 'group',
					'instructions'      => __( 'Optional. Shown above the resort list. It needs a name, an image, and a live feed URL.', 'ltt-dive-in' ),
					'layout'            => 'block',
					'conditional_logic' => $cams_only,
					'sub_fields'        => array(
						array(
							'key'       => 'field_ltt_dive_in_ski_cams_featured_heading',
							'label'     => __( 'Name', 'ltt-dive-in' ),
							'name'      => 'heading',
							'type'      => 'text',
							'maxlength' => 60,
							'wrapper'   => array( 'width' => '50' ),
						),
						array(
							'key'         => 'field_ltt_dive_in_ski_cams_featured_url',
							'label'       => __( 'Live feed URL', 'ltt-dive-in' ),
							'name'        => 'url',
							'type'        => 'url',
							'placeholder' => 'https://',
							'wrapper'     => array( 'width' => '50' ),
						),
						array(
							'key'          => 'field_ltt_dive_in_ski_cams_featured_text',
							'label'        => __( 'Description', 'ltt-dive-in' ),
							'name'         => 'text',
							'type'         => 'textarea',
							'instructions' => __( 'Optional. Leave a blank line between paragraphs.', 'ltt-dive-in' ),
							'maxlength'    => 300,
							'rows'         => 3,
							'new_lines'    => '',
						),
						array(
							'key'           => 'field_ltt_dive_in_ski_cams_featured_image',
							'label'         => __( 'Image', 'ltt-dive-in' ),
							'name'          => 'image',
							'type'          => 'image',
							'instructions'  => __( 'Landscape, at least 656px wide. Describe the view in its alt text.', 'ltt-dive-in' ),
							'return_format' => 'id',
							'preview_size'  => 'medium',
							'library'       => 'all',
							'min_width'     => 656,
							'mime_types'    => 'jpg, jpeg, png, webp',
							'wrapper'       => array( 'width' => '50' ),
						),
						array(
							'key'           => 'field_ltt_dive_in_ski_cams_featured_label',
							'label'         => __( 'Button text', 'ltt-dive-in' ),
							'name'          => 'label',
							'type'          => 'text',
							'default_value' => 'View Live Feed',
							'maxlength'     => 30,
							'wrapper'       => array( 'width' => '50' ),
						),
					),
				),
				array(
					'key'               => 'field_ltt_dive_in_ski_cams_resorts',
					'label'             => __( 'Ski resorts', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_ski_cams_resorts',
					'type'              => 'repeater',
					'instructions'      => __( 'Each resort becomes a filter; the first one is selected when the page loads. Every camera link opens in a new tab.', 'ltt-dive-in' ),
					'min'               => 0,
					'max'               => 8,
					'layout'            => 'block',
					'button_label'      => __( 'Add resort', 'ltt-dive-in' ),
					'collapsed'         => 'field_ltt_dive_in_ski_cams_resort_name',
					'conditional_logic' => $cams_only,
					'sub_fields'        => array(
						array(
							'key'       => 'field_ltt_dive_in_ski_cams_resort_name',
							'label'     => __( 'Resort', 'ltt-dive-in' ),
							'name'      => 'name',
							'type'      => 'text',
							'required'  => 1,
							'maxlength' => 30,
						),
						array(
							'key'          => 'field_ltt_dive_in_ski_cams_resort_cams',
							'label'        => __( 'Cameras', 'ltt-dive-in' ),
							'name'         => 'cams',
							'type'         => 'repeater',
							'required'     => 1,
							'min'          => 1,
							'max'          => 8,
							'layout'       => 'table',
							'button_label' => __( 'Add camera', 'ltt-dive-in' ),
							'sub_fields'   => array(
								array(
									'key'          => 'field_ltt_dive_in_ski_cams_cam_heading',
									'label'        => __( 'Name', 'ltt-dive-in' ),
									'name'         => 'heading',
									'type'         => 'text',
									'instructions' => __( 'For example, “North View”.', 'ltt-dive-in' ),
									'required'     => 1,
									'maxlength'    => 60,
									'wrapper'      => array( 'width' => '25' ),
								),
								array(
									'key'       => 'field_ltt_dive_in_ski_cams_cam_text',
									'label'     => __( 'Description', 'ltt-dive-in' ),
									'name'      => 'text',
									'type'      => 'textarea',
									'maxlength' => 160,
									'rows'      => 2,
									'new_lines' => '',
									'wrapper'   => array( 'width' => '45' ),
								),
								array(
									'key'         => 'field_ltt_dive_in_ski_cams_cam_url',
									'label'       => __( 'Live feed URL', 'ltt-dive-in' ),
									'name'        => 'url',
									'type'        => 'url',
									'required'    => 1,
									'placeholder' => 'https://',
									'wrapper'     => array( 'width' => '30' ),
								),
							),
						),
					),
				),
				array(
					'key'          => 'field_ltt_dive_in_conditions_links',
					'label'        => __( 'Related conditions', 'ltt-dive-in' ),
					'name'         => 'ltt_dive_in_conditions_links',
					'type'         => 'repeater',
					'instructions' => __( 'Optional. Up to three prompts shown on a white band below the live module, each pointing to another conditions page. Leave empty to hide the band.', 'ltt-dive-in' ),
					'min'          => 0,
					'max'          => 3,
					'layout'       => 'block',
					'button_label' => __( 'Add prompt', 'ltt-dive-in' ),
					'collapsed'    => 'field_ltt_dive_in_conditions_link_title',
					'sub_fields'   => array(
						array(
							'key'          => 'field_ltt_dive_in_conditions_link_title',
							'label'        => __( 'Heading', 'ltt-dive-in' ),
							'name'         => 'title',
							'type'         => 'text',
							'instructions' => __( 'For example, “Need traffic conditions?”', 'ltt-dive-in' ),
							'required'     => 1,
							'maxlength'    => 60,
							'wrapper'      => array( 'width' => '50' ),
						),
						array(
							'key'          => 'field_ltt_dive_in_conditions_link_text',
							'label'        => __( 'Text', 'ltt-dive-in' ),
							'name'         => 'text',
							'type'         => 'textarea',
							'instructions' => __( 'Optional. One short sentence.', 'ltt-dive-in' ),
							'maxlength'    => 120,
							'rows'         => 2,
							'new_lines'    => '',
							'wrapper'      => array( 'width' => '50' ),
						),
						array(
							'key'           => 'field_ltt_dive_in_conditions_link_cta',
							'label'         => __( 'Button', 'ltt-dive-in' ),
							'name'          => 'cta',
							'type'          => 'link',
							'instructions'  => __( 'Choose the destination page and enter a descriptive button label, such as “Check Conditions” or “Find a Cam”.', 'ltt-dive-in' ),
							'required'      => 1,
							'return_format' => 'array',
						),
					),
				),
			),
			'location'     => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'templates/current-conditions.php',
					),
					array(
						'param'    => 'page_type',
						'operator' => '==',
						'value'    => 'child',
					),
				),
			),
			'position'     => 'normal',
			'style'        => 'default',
			'active'       => true,
			'show_in_rest' => 1,
		)
	);
}
add_action( 'acf/init', 'ltt_dive_in_register_current_conditions_tab_fields' );

/**
 * Resolve the live module and its settings for a Current Conditions tab.
 *
 * @param int $post_id Tab page ID.
 * @return array Module settings: `module` is `weather`, `roads`, `cams`, or empty when nothing should render.
 *               Weather adds locations, active, and view; roads adds the values from ltt_dive_in_get_road_conditions_module();
 *               cams adds the valid cameras from ltt_dive_in_get_live_cams().
 */
function ltt_dive_in_get_current_conditions_tab_module( $post_id ) {
	$module = function_exists( 'get_field' ) ? get_field( 'ltt_dive_in_conditions_tab_module', $post_id ) : '';

	if ( 'roads' === $module ) {
		return ltt_dive_in_get_road_conditions_module( $post_id );
	}

	if ( 'cams' === $module ) {
		$cams = ltt_dive_in_get_live_cams( $post_id );

		return $cams ? array(
			'module' => 'cams',
			'cams'   => $cams,
		) : array( 'module' => '' );
	}

	if ( 'weather' !== $module ) {
		return array( 'module' => '' );
	}

	$locations = ltt_dive_in_get_weather_locations( $post_id );

	if ( ! $locations ) {
		return array( 'module' => '' );
	}

	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only display choices, validated against allowed values.
	$requested = isset( $_GET['location'] ) ? sanitize_title( wp_unslash( $_GET['location'] ) ) : '';
	$view      = isset( $_GET['weather_view'] ) ? sanitize_key( wp_unslash( $_GET['weather_view'] ) ) : '';
	// phpcs:enable WordPress.Security.NonceVerification.Recommended

	return array(
		'module'    => 'weather',
		'locations' => $locations,
		'active'    => isset( $locations[ $requested ] ) ? $requested : array_key_first( $locations ),
		'view'      => in_array( $view, array( 'forecast', 'history' ), true ) ? $view : 'current',
	);
}

/**
 * Get a weather tab's valid locations, keyed by slug, in editor order.
 *
 * @param int $post_id Tab page ID.
 * @return array<string, array{slug: string, name: string, latitude: float, longitude: float, elevation: int|null}>
 */
function ltt_dive_in_get_weather_locations( $post_id ) {
	if ( ! function_exists( 'get_field' ) ) {
		return array();
	}

	$rows      = get_field( 'ltt_dive_in_weather_locations', $post_id );
	$locations = array();

	foreach ( is_array( $rows ) ? array_slice( $rows, 0, 12 ) : array() as $row ) {
		$name = isset( $row['name'] ) && is_string( $row['name'] ) ? trim( $row['name'] ) : '';
		$slug = sanitize_title( $name );

		if (
			'' === $slug || isset( $locations[ $slug ] ) ||
			! isset( $row['latitude'], $row['longitude'] ) || ! is_numeric( $row['latitude'] ) || ! is_numeric( $row['longitude'] ) ||
			abs( (float) $row['latitude'] ) > 90 || abs( (float) $row['longitude'] ) > 180
		) {
			continue;
		}

		$locations[ $slug ] = array(
			'slug'      => $slug,
			'name'      => $name,
			'latitude'  => (float) $row['latitude'],
			'longitude' => (float) $row['longitude'],
			'elevation' => isset( $row['elevation'] ) && is_numeric( $row['elevation'] ) ? (int) $row['elevation'] : null,
		);
	}

	return $locations;
}

/**
 * Get a live-cameras tab's valid cameras, in editor order.
 *
 * A camera needs a heading, an image attachment, and a link with both a label
 * and a valid URL; incomplete rows are skipped.
 *
 * @param int $post_id Tab page ID.
 * @return array<int, array{heading: string, text: string, image: int, latest: array|null, background: int, label: string, url: string, new_tab: bool}>
 *         `latest` is the current downloaded copy of the camera's latest-image URL, when there is one.
 */
function ltt_dive_in_get_live_cams( $post_id ) {
	if ( ! function_exists( 'get_field' ) ) {
		return array();
	}

	$rows = get_field( 'ltt_dive_in_live_cams', $post_id );
	$cams = array();

	foreach ( is_array( $rows ) ? array_slice( $rows, 0, 4 ) : array() as $row ) {
		$heading = isset( $row['heading'] ) && is_string( $row['heading'] ) ? trim( $row['heading'] ) : '';
		$image   = isset( $row['image'] ) ? absint( $row['image'] ) : 0;
		$link    = isset( $row['link'] ) && is_array( $row['link'] ) ? $row['link'] : array();
		$label   = isset( $link['title'] ) && is_string( $link['title'] ) ? trim( $link['title'] ) : '';
		$url     = isset( $link['url'] ) && is_string( $link['url'] ) ? esc_url_raw( $link['url'] ) : '';

		if ( '' === $heading || ! $image || ! wp_attachment_is_image( $image ) || '' === $label || '' === $url ) {
			continue;
		}

		$background = isset( $row['background'] ) ? absint( $row['background'] ) : 0;
		$image_url  = function_exists( 'ltt_dive_in_sanitize_live_cam_image_url' ) ? ltt_dive_in_sanitize_live_cam_image_url( $row['image_url'] ?? '' ) : '';

		$cams[] = array(
			'heading'    => $heading,
			'text'       => isset( $row['text'] ) && is_string( $row['text'] ) ? trim( $row['text'] ) : '',
			'image'      => $image,
			'latest'     => $image_url ? ltt_dive_in_get_live_cam_image( $image_url ) : null,
			'background' => $background && wp_attachment_is_image( $background ) ? $background : 0,
			'label'      => $label,
			'url'        => $url,
			'new_tab'    => '_blank' === ( $link['target'] ?? '' ),
		);
	}

	return $cams;
}

/**
 * Get a live-cameras tab's ski cams section.
 *
 * @param int $post_id Tab page ID.
 * @return array Empty when the section should not render.
 */
function ltt_dive_in_get_ski_cams( $post_id ) {
	if ( ! function_exists( 'get_field' ) ) {
		return array();
	}

	$string = static function ( $value ) {
		return is_string( $value ) ? trim( $value ) : '';
	};
	$url    = static function ( $value ) {
		$value = is_string( $value ) ? esc_url_raw( trim( $value ), array( 'http', 'https' ) ) : '';

		return $value && wp_http_validate_url( $value ) ? $value : '';
	};

	$heading = $string( get_field( 'ltt_dive_in_ski_cams_heading', $post_id ) );

	if ( '' === $heading ) {
		return array();
	}

	$featured = get_field( 'ltt_dive_in_ski_cams_featured', $post_id );
	$featured = is_array( $featured ) ? $featured : array();
	$featured = array(
		'heading' => $string( $featured['heading'] ?? '' ),
		'text'    => $string( $featured['text'] ?? '' ),
		'image'   => absint( $featured['image'] ?? 0 ),
		'url'     => $url( $featured['url'] ?? '' ),
		'label'   => $string( $featured['label'] ?? '' ),
	);

	if ( '' === $featured['heading'] || '' === $featured['url'] || ! $featured['image'] || ! wp_attachment_is_image( $featured['image'] ) ) {
		$featured = null;
	} elseif ( '' === $featured['label'] ) {
		$featured['label'] = __( 'View Live Feed', 'ltt-dive-in' );
	}

	$rows    = get_field( 'ltt_dive_in_ski_cams_resorts', $post_id );
	$resorts = array();

	foreach ( is_array( $rows ) ? array_slice( $rows, 0, 8 ) : array() as $row ) {
		$name = $string( $row['name'] ?? '' );
		$slug = sanitize_title( $name );
		$cams = array();

		if ( '' === $slug || isset( $resorts[ $slug ] ) ) {
			continue;
		}

		foreach ( isset( $row['cams'] ) && is_array( $row['cams'] ) ? array_slice( $row['cams'], 0, 8 ) : array() as $cam ) {
			$cam_heading = $string( $cam['heading'] ?? '' );
			$cam_url     = $url( $cam['url'] ?? '' );

			if ( '' !== $cam_heading && '' !== $cam_url ) {
				$cams[] = array(
					'heading' => $cam_heading,
					'text'    => $string( $cam['text'] ?? '' ),
					'url'     => $cam_url,
				);
			}
		}

		if ( $cams ) {
			$resorts[ $slug ] = array(
				'slug' => $slug,
				'name' => $name,
				'cams' => $cams,
			);
		}
	}

	if ( ! $featured && ! $resorts ) {
		return array();
	}

	return array(
		'eyebrow'  => $string( get_field( 'ltt_dive_in_ski_cams_eyebrow', $post_id ) ),
		'heading'  => $heading,
		'intro'    => $string( get_field( 'ltt_dive_in_ski_cams_intro', $post_id ) ),
		'featured' => $featured,
		'resorts'  => $resorts,
	);
}

/**
 * Get a tab's valid Related conditions prompts.
 *
 * A prompt needs a heading and a button with both a label and a valid URL.
 *
 * @param int $post_id Tab page ID.
 * @return array<int, array{title: string, text: string, label: string, url: string, new_tab: bool}>
 */
function ltt_dive_in_get_current_conditions_links( $post_id ) {
	if ( ! function_exists( 'get_field' ) ) {
		return array();
	}

	$rows  = get_field( 'ltt_dive_in_conditions_links', $post_id );
	$links = array();

	foreach ( is_array( $rows ) ? array_slice( $rows, 0, 3 ) : array() as $row ) {
		$title = isset( $row['title'] ) && is_string( $row['title'] ) ? trim( $row['title'] ) : '';
		$cta   = isset( $row['cta'] ) && is_array( $row['cta'] ) ? $row['cta'] : array();
		$label = isset( $cta['title'] ) && is_string( $cta['title'] ) ? trim( $cta['title'] ) : '';
		$url   = isset( $cta['url'] ) && is_string( $cta['url'] ) ? esc_url_raw( $cta['url'] ) : '';

		if ( '' === $title || '' === $label || '' === $url ) {
			continue;
		}

		$links[] = array(
			'title'   => $title,
			'text'    => isset( $row['text'] ) && is_string( $row['text'] ) ? trim( $row['text'] ) : '',
			'label'   => $label,
			'url'     => $url,
			'new_tab' => '_blank' === ( $cta['target'] ?? '' ),
		);
	}

	return $links;
}

/**
 * Get the published Current Conditions page that shows the weather module.
 *
 * @return int Page ID, or 0 when no weather tab is set up.
 */
function ltt_dive_in_get_weather_tab_page_id() {
	$page_id = get_transient( 'ltt_dive_in_weather_tab_page_id' );

	if ( false === $page_id ) {
		$pages = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'orderby'        => array(
					'menu_order' => 'ASC',
					'ID'         => 'ASC',
				),
				'no_found_rows'  => true,
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Cached in a transient that is cleared whenever a page is saved.
				'meta_query'     => array(
					array(
						'key'   => '_wp_page_template',
						'value' => 'templates/current-conditions.php',
					),
					array(
						'key'   => 'ltt_dive_in_conditions_tab_module',
						'value' => 'weather',
					),
				),
			)
		);
		$page_id = $pages ? (int) $pages[0] : 0;

		set_transient( 'ltt_dive_in_weather_tab_page_id', $page_id, DAY_IN_SECONDS );
	}

	return (int) $page_id;
}

/**
 * Get the default (first) location of the published Current Conditions weather tab.
 *
 * The header weather indicator uses it so both show the same report.
 *
 * @return array Location from ltt_dive_in_get_weather_locations(), or empty when no weather tab is set up.
 */
function ltt_dive_in_get_default_weather_location() {
	static $location = null;

	if ( null === $location ) {
		$page_id   = ltt_dive_in_get_weather_tab_page_id();
		$locations = $page_id ? ltt_dive_in_get_weather_locations( $page_id ) : array();
		$location  = $locations ? reset( $locations ) : array();
	}

	return $location;
}

/**
 * Forget the cached weather tab whenever a page changes.
 */
function ltt_dive_in_clear_weather_tab_page_cache() {
	delete_transient( 'ltt_dive_in_weather_tab_page_id' );
}
add_action( 'save_post_page', 'ltt_dive_in_clear_weather_tab_page_cache' );
add_action( 'deleted_post', 'ltt_dive_in_clear_weather_tab_page_cache' );

/**
 * Build the compact hero arguments from the parent page's title and excerpt.
 *
 * @return array
 */
function ltt_dive_in_get_current_conditions_hero_args() {
	$hub_id = ltt_dive_in_get_current_conditions_hub_id();

	return array(
		'title'         => get_the_title( $hub_id ),
		'heading_level' => get_queried_object_id() === $hub_id ? 'h1' : 'p',
		'text'          => has_excerpt( $hub_id ) ? get_the_excerpt( $hub_id ) : '',
	);
}
