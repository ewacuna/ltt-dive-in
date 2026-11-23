<?php
/**
 * Site Guide: AI Summary box.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'updated'         => '2026-11-23',
	'title'           => __( 'AI Summary', 'ltt-dive-in' ),
	'group'           => 'posts',
	'order'           => 10,
	'icon'            => 'superhero-alt',
	'where'           => __( 'Post editor, "AI Summary" box below the content', 'ltt-dive-in' ),
	'applies'         => __( 'Blog posts.', 'ltt-dive-in' ),
	'short'           => __( 'A short summary at the top of a post that visitors can open.', 'ltt-dive-in' ),
	'summary'         => __( 'A short summary of the article, shown near the top of the post behind a "Show Summary" button. You can write it yourself or let Google Gemini draft it from the post text and then edit it.', 'ltt-dive-in' ),
	'steps'           => array(
		__( 'Finish writing the post first, so the summary matches the final text.', 'ltt-dive-in' ),
		__( 'Click <strong>Generate summary</strong>. After a few seconds a draft appears in the Summary field.', 'ltt-dive-in' ),
		__( 'Read it carefully and fix anything that is not right.', 'ltt-dive-in' ),
		__( 'Update the post. The summary is only saved and shown when you save the post.', 'ltt-dive-in' ),
	),
	'fields'          => array(
		array(
			'label'       => __( 'Summary', 'ltt-dive-in' ),
			'description' => __( 'The summary text, up to 650 characters. You can type it by hand instead of generating it. Leave it empty to hide the summary on this post.', 'ltt-dive-in' ),
		),
	),
	'tips'            => array(
		__( 'If you change the post after generating the summary, the box warns you so you can update the summary too.', 'ltt-dive-in' ),
		__( 'If you edited the summary by hand, the box asks before replacing it with a new draft.', 'ltt-dive-in' ),
		__( 'The Generate summary button only works when the Gemini key is set in Settings → AI Summaries.', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(),
);
