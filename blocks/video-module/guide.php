<?php
/**
 * Block Guide content for Video Module.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'summary'         => __( 'Puts video on the page. Each video shows a still image (the poster) with a play button, and only loads when someone presses play, so the page stays fast. Videos can come from Cloudflare Stream, YouTube, Vimeo, or an MP4 in the Media Library. There are five layouts, from a single video to a series with episodes.', 'ltt-dive-in' ),
	'where'           => __( 'Pages and blog posts.', 'ltt-dive-in' ),
	'steps'           => array(
		__( 'Click the + button, open <strong>LTT Blocks</strong>, and add <strong>Video Module</strong>.', 'ltt-dive-in' ),
		__( 'Choose a <strong>Variant</strong>.', 'ltt-dive-in' ),
		__( 'Add your videos under <strong>Videos</strong>. For each one pick the source, paste the ID or link, and choose a poster image.', 'ltt-dive-in' ),
		__( 'If the preview does not change after you switch layouts, save the page or click outside the block and back.', 'ltt-dive-in' ),
	),
	'fields_heading'  => __( 'Fields for each video', 'ltt-dive-in' ),
	'fields'          => array(
		array(
			'label'       => __( 'Variant', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'The layout. See the layouts below for how many videos each one needs.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Video source', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'Cloudflare Stream, YouTube or Vimeo, or Self-hosted MP4.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Cloudflare Stream video ID', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'For Cloudflare: the video ID, a long string of letters and numbers you copy from the video in the Cloudflare Stream dashboard.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'YouTube or Vimeo URL', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'For YouTube or Vimeo: the normal link to the video, copied from the address bar or the Share button.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'MP4 file', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'For self-hosted: an MP4 from the Media Library. Keep it short and compressed. Large files load slowly and fill up the server.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Poster image', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'The still image shown before the video plays. Pick a frame that shows what the video is about.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Tag', 'ltt-dive-in' ),
			'description' => __( 'A small label. In the Episodic layout, the tags become filter buttons. Up to 40 characters.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Duration', 'ltt-dive-in' ),
			'description' => __( 'How long the video is, with the unit, like "3 min" or "1:45 min".', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Title', 'ltt-dive-in' ),
			'description' => __( 'The video title. Required in every layout except Full Bleed. Up to 90 characters.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Body copy', 'ltt-dive-in' ),
			'description' => __( 'A short description. Not shown in Full Bleed. Up to 600 characters.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Call to action', 'ltt-dive-in' ),
			'description' => __( 'An optional button that goes with the video.', 'ltt-dive-in' ),
		),
	),
	'variants'        => array(
		'editorial'       => array(
			'name'        => __( '1x1 Editorial', 'ltt-dive-in' ),
			'description' => __( 'One video next to its title, text, and button.', 'ltt-dive-in' ),
			'notes'       => array( __( 'Exactly 1 video, with a title.', 'ltt-dive-in' ) ),
		),
		'full_bleed'      => array(
			'name'        => __( 'Full Bleed', 'ltt-dive-in' ),
			'description' => __( 'One video across the full width of the screen, with no text around it.', 'ltt-dive-in' ),
			'notes'       => array( __( 'Exactly 1 video. The title is optional, but adding one helps screen reader users know what they are about to play.', 'ltt-dive-in' ) ),
		),
		'full_bleed_copy' => array(
			'name'        => __( 'Full Bleed w/copy', 'ltt-dive-in' ),
			'description' => __( 'One full-width video with its title, text, and button.', 'ltt-dive-in' ),
			'notes'       => array( __( 'Exactly 1 video, with a title.', 'ltt-dive-in' ) ),
		),
		'carousel'        => array(
			'name'        => __( 'Video Carousel', 'ltt-dive-in' ),
			'description' => __( 'A row of videos that visitors slide through.', 'ltt-dive-in' ),
			'notes'       => array( __( '2 to 8 videos, each with a title.', 'ltt-dive-in' ) ),
		),
		'episodic'        => array(
			'name'        => __( 'Episodic', 'ltt-dive-in' ),
			'description' => __( 'A video series. The first video is featured at the top, and the rest appear as episodes underneath. Clicking an episode opens it in a video window. If the videos have tags, visitors can filter the episodes by tag.', 'ltt-dive-in' ),
			'best_for'    => __( 'A series, like a set of trail guides or interviews.', 'ltt-dive-in' ),
			'fields'      => array(
				array(
					'label'       => __( 'Section tag', 'ltt-dive-in' ),
					'description' => __( 'A small label above the series heading. Up to 40 characters.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Section heading', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'The name of the series. Up to 90 characters.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Section introduction', 'ltt-dive-in' ),
					'description' => __( 'A short paragraph about the series. Up to 300 characters.', 'ltt-dive-in' ),
				),
			),
			'notes'       => array(
				__( '2 to 12 videos, each with a title. The first one in the list is the featured video.', 'ltt-dive-in' ),
				__( 'Use the same few tags across episodes, like "Summer" and "Winter", so the filter buttons are useful.', 'ltt-dive-in' ),
			),
		),
	),
	'tips'            => array(
		__( 'Videos never play on their own, and nothing loads from YouTube or Vimeo until the visitor presses play.', 'ltt-dive-in' ),
		__( 'Videos with people talking need captions. Add them in Cloudflare, YouTube, or Vimeo before you publish.', 'ltt-dive-in' ),
		__( 'Cloudflare Stream is the best choice for longer or high quality videos. Use a self-hosted MP4 only for short clips.', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(
		__( 'WordPress says "This variant requires exactly one video."', 'ltt-dive-in' ) => __( 'Editorial, Full Bleed, and Full Bleed w/copy show a single video. Remove the extra rows or switch to Carousel or Episodic.', 'ltt-dive-in' ),
		__( 'WordPress says "Every video in this variant needs a title."', 'ltt-dive-in' ) => __( 'Fill in the Title of each video.', 'ltt-dive-in' ),
		__( 'The editor says "Add the required videos and complete each source and poster image."', 'ltt-dive-in' ) => __( 'A video is missing its ID, link, or file, or its poster image. Check each row.', 'ltt-dive-in' ),
	),
);
