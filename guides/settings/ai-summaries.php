<?php
/**
 * Site Guide: AI Summaries settings.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'updated'         => '2026-11-23',
	'title'           => __( 'AI Summaries', 'ltt-dive-in' ),
	'group'           => 'settings',
	'icon'            => 'superhero-alt',
	'menu'            => __( 'Settings → AI Summaries', 'ltt-dive-in' ),
	'screen'          => array(
		'parent'     => 'options-general.php',
		'slug'       => 'ltt-dive-in-ai-summaries-settings',
		'capability' => 'manage_options',
	),
	'short'           => __( 'Connects the post editor to Google Gemini to draft article summaries.', 'ltt-dive-in' ),
	'summary'         => __( 'Blog posts can show a short summary near the top that visitors can open. Editors can ask Google Gemini to draft that summary from the post text, right in the post editor. This screen holds the Gemini key and the model used. Nothing is ever written or published automatically: the editor reviews the draft and saves the post.', 'ltt-dive-in' ),
	'shows'           => array(
		__( 'The <strong>Generate summary</strong> button in the <strong>AI Summary</strong> box of the post editor.', 'ltt-dive-in' ),
		__( 'Visitors only see summaries that an editor saved. They never talk to Gemini.', 'ltt-dive-in' ),
	),
	'fields'          => array(
		array(
			'label'       => __( 'Connection status', 'ltt-dive-in' ),
			'description' => __( 'Not something you fill in. It tells you whether a key is saved and whether the last request to Gemini worked.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Gemini API key', 'ltt-dive-in' ),
			'description' => __( 'The key from Google AI Studio. Once saved it is hidden. To change it, paste a new key and save.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Remove saved key', 'ltt-dive-in' ),
			'description' => __( 'Turn it on and save to delete the key and turn off drafting. Summaries that are already saved on posts stay visible.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Gemini model', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'Which Gemini model writes the drafts. Leave it as <code>gemini-flash-latest</code>, which always points to the current fast model. If that model is busy, a lighter one is tried once.', 'ltt-dive-in' ),
		),
	),
	'status'          => array(
		array(
			'label' => __( 'Gemini key', 'ltt-dive-in' ),
			'check' => static function () {
				return ltt_dive_in_settings_guide_key_check(
					function_exists( 'ltt_dive_in_get_gemini_api_key' ) ? ltt_dive_in_get_gemini_api_key() : '',
					function_exists( 'ltt_dive_in_gemini_api_key_is_constant' ) && ltt_dive_in_gemini_api_key_is_constant(),
					__( 'Editors cannot generate summaries. Saved summaries still show.', 'ltt-dive-in' )
				);
			},
		),
		array(
			'label' => __( 'Last request to Gemini', 'ltt-dive-in' ),
			'check' => static function () {
				return ltt_dive_in_settings_guide_last_request_check( 'ltt_dive_in_gemini_last_status' );
			},
		),
	),
	'tips'            => array(
		__( 'Always read the draft before saving. AI can get facts wrong, like dates, prices, or names.', 'ltt-dive-in' ),
		__( 'If the post changes after the summary was generated, the editor shows a warning so you can update the summary.', 'ltt-dive-in' ),
		__( 'The post text is sent to Google to write the draft. Do not use it on posts with private information.', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(
		__( 'The status says the request failed.', 'ltt-dive-in' ) => __( 'The key may be wrong or expired, or Google may be busy. Try again in a few minutes. If it keeps failing, create a new key in Google AI Studio.', 'ltt-dive-in' ),
	),
);
