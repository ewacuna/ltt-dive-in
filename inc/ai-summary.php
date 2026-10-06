<?php
/**
 * AI summaries for blog posts.
 *
 * Editors generate a summary from the post editor; the server sends the post
 * text to the Gemini API with the key saved in Settings → AI Summaries and
 * returns a draft that is only stored when the editor updates the post. The
 * front end reads the saved fields and never calls Gemini.
 *
 * A generation record keeps hashes of the post text and of the generated
 * summary, so the editor can warn when the post changed after generation and
 * confirm before replacing manual edits. Nothing is regenerated automatically.
 *
 * @link https://ai.google.dev/api/generate-content
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post meta that stores the latest saved generation record.
 */
const LTT_DIVE_IN_AI_SUMMARY_RECORD_META = '_ltt_dive_in_ai_summary_generation';

/**
 * Return the Gemini API key.
 *
 * A `LTT_DIVE_IN_GEMINI_API_KEY` constant in wp-config.php takes precedence
 * over the key saved in Settings → AI Summaries.
 *
 * @return string
 */
function ltt_dive_in_get_gemini_api_key() {
	if ( defined( 'LTT_DIVE_IN_GEMINI_API_KEY' ) && is_string( LTT_DIVE_IN_GEMINI_API_KEY ) ) {
		$key = LTT_DIVE_IN_GEMINI_API_KEY;
	} else {
		$key = get_option( 'options_ltt_dive_in_gemini_api_key', '' );
	}

	/**
	 * Filter the Gemini API key used for server-side requests.
	 *
	 * @param string $key Gemini API key.
	 */
	$key = apply_filters( 'ltt_dive_in_gemini_api_key', is_string( $key ) ? trim( $key ) : '' );

	return is_string( $key ) ? trim( $key ) : '';
}

/**
 * Whether the Gemini API key is supplied by wp-config.php.
 *
 * @return bool
 */
function ltt_dive_in_gemini_api_key_is_constant() {
	return defined( 'LTT_DIVE_IN_GEMINI_API_KEY' ) && is_string( LTT_DIVE_IN_GEMINI_API_KEY ) && '' !== trim( LTT_DIVE_IN_GEMINI_API_KEY );
}

/**
 * Return the Gemini model used for summaries.
 *
 * @return string
 */
function ltt_dive_in_get_gemini_model() {
	$model = get_option( 'options_ltt_dive_in_gemini_model', '' );
	$model = is_string( $model ) && preg_match( '/^[a-z0-9][a-z0-9.\-]*$/', $model ) ? $model : 'gemini-flash-latest';

	/**
	 * Filter the Gemini model used for summaries.
	 *
	 * @param string $model Gemini model code.
	 */
	return (string) apply_filters( 'ltt_dive_in_gemini_model', $model );
}

/**
 * Return the model used when the primary model is overloaded.
 *
 * @return string
 */
function ltt_dive_in_get_gemini_fallback_model() {
	/**
	 * Filter the Gemini model used when the primary model is overloaded.
	 *
	 * @param string $model Gemini model code.
	 */
	return (string) apply_filters( 'ltt_dive_in_gemini_fallback_model', 'gemini-flash-lite-latest' );
}

/**
 * Record the outcome of the latest Gemini request for the settings screen.
 *
 * @param string $status `ok` or an error code.
 * @return void
 */
function ltt_dive_in_record_gemini_status( $status ) {
	update_option(
		'ltt_dive_in_gemini_last_status',
		array(
			'status' => sanitize_key( $status ),
			'time'   => time(),
		),
		false
	);
}

/**
 * Reduce post content or summary text to comparable plain text.
 *
 * Markup, block delimiters, shortcodes, entities, and whitespace differences
 * are ignored so formatting-only edits do not mark a summary as outdated.
 *
 * @param string $text Raw text or post content.
 * @return string
 */
function ltt_dive_in_ai_summary_plain_text( $text ) {
	$text = wp_strip_all_tags( strip_shortcodes( (string) $text ) );
	$text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$text = preg_replace( '/\s+/u', ' ', $text );

	return trim( (string) $text );
}

/**
 * Hash post content for change detection.
 *
 * @param string $content Post content.
 * @return string
 */
function ltt_dive_in_ai_summary_content_hash( $content ) {
	return hash( 'sha256', ltt_dive_in_ai_summary_plain_text( $content ) );
}

/**
 * Hash a summary for manual-edit detection.
 *
 * @param string $summary Summary text.
 * @return string
 */
function ltt_dive_in_ai_summary_output_hash( $summary ) {
	return hash( 'sha256', ltt_dive_in_ai_summary_plain_text( $summary ) );
}

/**
 * Describe the summary state shown in the editor.
 *
 * @param int    $post_id Post ID.
 * @param string $content Current post content.
 * @param string $summary Current summary field value.
 * @return array{has_summary:bool, has_record:bool, stale:bool, edited:bool, generated_at:string}
 */
function ltt_dive_in_get_ai_summary_state( $post_id, $content, $summary ) {
	$record      = get_post_meta( $post_id, LTT_DIVE_IN_AI_SUMMARY_RECORD_META, true );
	$has_record  = is_array( $record ) && ! empty( $record['source_hash'] ) && ! empty( $record['output_hash'] );
	$has_summary = '' !== ltt_dive_in_ai_summary_plain_text( $summary );

	return array(
		'has_summary'  => $has_summary,
		'has_record'   => $has_record,
		'stale'        => $has_record && $has_summary && ! hash_equals( (string) $record['source_hash'], ltt_dive_in_ai_summary_content_hash( $content ) ),
		// A summary without a record was written by hand; treat it as edited.
		'edited'       => $has_summary && ( ! $has_record || ! hash_equals( (string) $record['output_hash'], ltt_dive_in_ai_summary_output_hash( $summary ) ) ),
		'generated_at' => $has_record && ! empty( $record['time'] ) ? wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), (int) $record['time'] ) : '',
	);
}

/**
 * Sign a generation record so it can travel through the editor form.
 *
 * @param array $record Generation record.
 * @return string
 */
function ltt_dive_in_sign_ai_summary_record( $record ) {
	$data = base64_encode( (string) wp_json_encode( $record ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Transport encoding, not obfuscation.

	return $data . '.' . hash_hmac( 'sha256', $data, wp_salt( 'auth' ) );
}

/**
 * Verify a signed generation record.
 *
 * @param string $token   Signed record.
 * @param int    $post_id Post the record must belong to.
 * @return array|null
 */
function ltt_dive_in_verify_ai_summary_record( $token, $post_id ) {
	$parts = explode( '.', (string) $token );

	if ( 2 !== count( $parts ) || ! hash_equals( hash_hmac( 'sha256', $parts[0], wp_salt( 'auth' ) ), $parts[1] ) ) {
		return null;
	}

	$record = json_decode( (string) base64_decode( $parts[0], true ), true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- Transport encoding, not obfuscation.

	if ( ! is_array( $record ) || (int) ( $record['post_id'] ?? 0 ) !== (int) $post_id ) {
		return null;
	}

	return array(
		'source_hash' => sanitize_text_field( (string) ( $record['source_hash'] ?? '' ) ),
		'output_hash' => sanitize_text_field( (string) ( $record['output_hash'] ?? '' ) ),
		'model'       => sanitize_text_field( (string) ( $record['model'] ?? '' ) ),
		'time'        => absint( $record['time'] ?? 0 ),
	);
}

/**
 * Ask Gemini for a summary of a post.
 *
 * @param string $title   Post title.
 * @param string $content Post content.
 * @return array{model:string, summary:string}|WP_Error
 */
function ltt_dive_in_request_gemini_summary( $title, $content ) {
	$api_key = ltt_dive_in_get_gemini_api_key();
	$text    = ltt_dive_in_ai_summary_plain_text( $content );

	if ( '' === $api_key ) {
		return new WP_Error( 'ltt_dive_in_gemini_missing_api_key', __( 'Add a Gemini API key in Settings → AI Summaries first.', 'ltt-dive-in' ) );
	}

	if ( str_word_count( $text ) < 80 ) {
		return new WP_Error( 'ltt_dive_in_ai_summary_too_short', __( 'Write at least 80 words in the post before generating a summary.', 'ltt-dive-in' ) );
	}

	$instructions = 'You write the summary readers can expand at the top of an article on Lake Tahoe Travel, a destination-marketing website. '
		. 'Use only facts stated in the article; never add details, prices, dates, or claims it does not contain. '
		. 'Write one paragraph of 60 to 90 words in clear, friendly American English in the third person, without markdown, lists, emojis, or hype. '
		. 'Cover the main takeaways a reader needs, not a restatement of the title.';
	$body         = array(
		'systemInstruction' => array( 'parts' => array( array( 'text' => $instructions ) ) ),
		'contents'          => array(
			array(
				'role'  => 'user',
				'parts' => array( array( 'text' => 'Title: ' . ltt_dive_in_ai_summary_plain_text( $title ) . "\n\nArticle:\n" . mb_substr( $text, 0, 60000 ) ) ),
			),
		),
		'generationConfig'  => array(
			'temperature'      => 0.3,
			'responseMimeType' => 'application/json',
			'responseSchema'   => array(
				'type'       => 'OBJECT',
				'properties' => array(
					'summary' => array( 'type' => 'STRING' ),
				),
				'required'   => array( 'summary' ),
			),
		),
	);
	$models       = array_unique( array( ltt_dive_in_get_gemini_model(), ltt_dive_in_get_gemini_fallback_model() ) );

	// Overloaded models answer 500/503; retry once with the fallback model.
	foreach ( $models as $model ) {
		$response = wp_remote_post(
			'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode( $model ) . ':generateContent',
			array(
				'timeout' => 45,
				'headers' => array(
					'Content-Type'   => 'application/json',
					'x-goog-api-key' => $api_key,
				),
				'body'    => wp_json_encode( $body ),
			)
		);

		if ( is_wp_error( $response ) || ! in_array( (int) wp_remote_retrieve_response_code( $response ), array( 500, 503 ), true ) ) {
			break;
		}
	}

	if ( is_wp_error( $response ) ) {
		ltt_dive_in_record_gemini_status( 'request_failed' );

		return new WP_Error( 'ltt_dive_in_gemini_request_failed', __( 'Gemini could not be reached. Try again in a moment.', 'ltt-dive-in' ) );
	}

	$code = (int) wp_remote_retrieve_response_code( $response );
	$data = json_decode( (string) wp_remote_retrieve_body( $response ), true );

	if ( 200 !== $code ) {
		$reasons = wp_list_pluck( (array) ( $data['error']['details'] ?? array() ), 'reason' );

		if ( in_array( $code, array( 401, 403 ), true ) || in_array( 'API_KEY_INVALID', $reasons, true ) ) {
			ltt_dive_in_record_gemini_status( 'unauthorized' );

			return new WP_Error( 'ltt_dive_in_gemini_unauthorized', __( 'Gemini rejected the API key. Check it in Settings → AI Summaries.', 'ltt-dive-in' ) );
		}

		if ( 429 === $code ) {
			ltt_dive_in_record_gemini_status( 'rate_limited' );

			return new WP_Error( 'ltt_dive_in_gemini_rate_limited', __( 'The Gemini usage limit was reached. Try again later.', 'ltt-dive-in' ) );
		}

		if ( in_array( $code, array( 500, 503 ), true ) ) {
			ltt_dive_in_record_gemini_status( 'unavailable' );

			return new WP_Error( 'ltt_dive_in_gemini_unavailable', __( 'Gemini is busy right now. Try again in a few minutes.', 'ltt-dive-in' ) );
		}

		ltt_dive_in_record_gemini_status( 404 === $code ? 'model_not_found' : 'http_' . $code );

		return new WP_Error( 'ltt_dive_in_gemini_http_error', __( 'Gemini could not generate a summary right now. Try again in a moment.', 'ltt-dive-in' ) );
	}

	$output = '';

	foreach ( (array) ( $data['candidates'][0]['content']['parts'] ?? array() ) as $part ) {
		$output .= is_array( $part ) && isset( $part['text'] ) && empty( $part['thought'] ) ? (string) $part['text'] : '';
	}

	$result  = json_decode( $output, true );
	$summary = is_array( $result ) ? ltt_dive_in_ai_summary_plain_text( $result['summary'] ?? '' ) : '';

	if ( '' === $summary ) {
		ltt_dive_in_record_gemini_status( 'invalid_response' );

		return new WP_Error( 'ltt_dive_in_gemini_invalid_response', __( 'Gemini returned a response that could not be used. Try again.', 'ltt-dive-in' ) );
	}

	ltt_dive_in_record_gemini_status( 'ok' );

	return array(
		'model'   => $model,
		'summary' => $summary,
	);
}

/**
 * Validate an editor AJAX request and return the post it targets.
 *
 * @return WP_Post
 */
function ltt_dive_in_ai_summary_ajax_post() {
	check_ajax_referer( 'ltt_dive_in_ai_summary', 'nonce' );

	$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	$post    = $post_id ? get_post( $post_id ) : null;

	if ( ! $post || 'post' !== $post->post_type || ! current_user_can( 'edit_post', $post_id ) ) {
		wp_send_json_error( array( 'message' => __( 'You cannot edit this post.', 'ltt-dive-in' ) ), 403 );
	}

	return $post;
}

/**
 * Read the unsaved editor values posted by the AJAX request.
 *
 * @return array{content:string, summary:string}
 */
function ltt_dive_in_ai_summary_ajax_values() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- Verified in ltt_dive_in_ai_summary_ajax_post().
	return array(
		'content' => isset( $_POST['content'] ) ? (string) wp_unslash( $_POST['content'] ) : '', // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Reduced to plain text before use.
		'summary' => isset( $_POST['summary'] ) ? sanitize_textarea_field( wp_unslash( $_POST['summary'] ) ) : '',
	);
	// phpcs:enable
}

/**
 * Report whether the current summary is outdated or manually edited.
 */
function ltt_dive_in_ajax_ai_summary_status() {
	$post   = ltt_dive_in_ai_summary_ajax_post();
	$values = ltt_dive_in_ai_summary_ajax_values();

	wp_send_json_success( ltt_dive_in_get_ai_summary_state( $post->ID, $values['content'], $values['summary'] ) );
}
add_action( 'wp_ajax_ltt_dive_in_ai_summary_status', 'ltt_dive_in_ajax_ai_summary_status' );

/**
 * Generate a summary draft from the current editor content.
 */
function ltt_dive_in_ajax_ai_summary_generate() {
	$post   = ltt_dive_in_ai_summary_ajax_post();
	$values = ltt_dive_in_ai_summary_ajax_values();
	$lock   = 'ltt_dive_in_ai_summary_lock_' . $post->ID;
	$title  = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : $post->post_title; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified in ltt_dive_in_ai_summary_ajax_post().

	if ( get_transient( $lock ) ) {
		wp_send_json_error( array( 'message' => __( 'A summary is already being generated for this post.', 'ltt-dive-in' ) ), 429 );
	}

	set_transient( $lock, 1, MINUTE_IN_SECONDS );
	$result = ltt_dive_in_request_gemini_summary( $title, $values['content'] );
	delete_transient( $lock );

	if ( is_wp_error( $result ) ) {
		wp_send_json_error( array( 'message' => $result->get_error_message() ), 502 );
	}

	$record = array(
		'post_id'     => $post->ID,
		'source_hash' => ltt_dive_in_ai_summary_content_hash( $values['content'] ),
		'output_hash' => ltt_dive_in_ai_summary_output_hash( $result['summary'] ),
		'model'       => $result['model'],
		'time'        => time(),
	);

	wp_send_json_success(
		array(
			'summary' => $result['summary'],
			'token'   => ltt_dive_in_sign_ai_summary_record( $record ),
		)
	);
}
add_action( 'wp_ajax_ltt_dive_in_ai_summary_generate', 'ltt_dive_in_ajax_ai_summary_generate' );

/**
 * Store the generation record when the editor saves a generated summary.
 *
 * The record arrives as a signed hidden input in the ACF meta box, so a draft
 * that is generated but never saved leaves the previous record untouched.
 *
 * @param int|string $post_id ACF post ID.
 * @return void
 */
function ltt_dive_in_save_ai_summary_record( $post_id ) {
	if ( ! is_numeric( $post_id ) || 'post' !== get_post_type( (int) $post_id ) || wp_is_post_revision( (int) $post_id ) || ! current_user_can( 'edit_post', (int) $post_id ) ) {
		return;
	}

	$post_id = (int) $post_id;

	if ( '' === ltt_dive_in_ai_summary_plain_text( (string) get_post_meta( $post_id, 'ltt_dive_in_ai_summary', true ) ) ) {
		delete_post_meta( $post_id, LTT_DIVE_IN_AI_SUMMARY_RECORD_META );

		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- ACF verifies its form nonce before acf/save_post.
	$token  = isset( $_POST['ltt_dive_in_ai_summary_token'] ) ? sanitize_text_field( wp_unslash( $_POST['ltt_dive_in_ai_summary_token'] ) ) : '';
	$record = '' !== $token ? ltt_dive_in_verify_ai_summary_record( $token, $post_id ) : null;

	if ( $record ) {
		update_post_meta( $post_id, LTT_DIVE_IN_AI_SUMMARY_RECORD_META, $record );
	}
}
add_action( 'acf/save_post', 'ltt_dive_in_save_ai_summary_record', 20 );

/**
 * Load the summary controls in the post editor.
 */
function ltt_dive_in_enqueue_ai_summary_editor() {
	$screen = get_current_screen();
	$post   = get_post();

	if ( ! function_exists( 'acf_add_local_field_group' ) || ! $screen || 'post' !== $screen->post_type || ! $post ) {
		return;
	}

	$script_path = LTT_DIVE_IN_DIR . '/assets/js/admin/ai-summary.js';
	$summary     = (string) get_post_meta( $post->ID, 'ltt_dive_in_ai_summary', true );

	wp_enqueue_script( 'ltt-dive-in-ai-summary', LTT_DIVE_IN_URI . '/assets/js/admin/ai-summary.js', array( 'wp-data', 'wp-dom-ready' ), file_exists( $script_path ) ? (string) filemtime( $script_path ) : LTT_DIVE_IN_VERSION, true );
	wp_localize_script(
		'ltt-dive-in-ai-summary',
		'ltt_dive_in_ai_summary',
		array(
			'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
			'nonce'       => wp_create_nonce( 'ltt_dive_in_ai_summary' ),
			'postId'      => $post->ID,
			'hasKey'      => '' !== ltt_dive_in_get_gemini_api_key(),
			'settingsUrl' => current_user_can( 'manage_options' ) ? admin_url( 'options-general.php?page=ltt-dive-in-ai-summaries-settings' ) : '',
			'state'       => ltt_dive_in_get_ai_summary_state( $post->ID, $post->post_content, $summary ),
			'i18n'        => array(
				'generate'      => __( 'Generate summary', 'ltt-dive-in' ),
				'regenerate'    => __( 'Regenerate summary', 'ltt-dive-in' ),
				'generating'    => __( 'Generating summary…', 'ltt-dive-in' ),
				'generated'     => __( 'A new summary was added below. Review it, then update the post to save it.', 'ltt-dive-in' ),
				/* translators: %s: date and time. */
				'generatedAt'   => __( 'Generated with AI on %s.', 'ltt-dive-in' ),
				'stale'         => __( 'The post text changed after this summary was generated. Review the summary or regenerate it.', 'ltt-dive-in' ),
				'edited'        => __( 'The summary includes manual edits.', 'ltt-dive-in' ),
				'confirm'       => __( 'Replace the current summary, including manual edits? Nothing is saved until you update the post.', 'ltt-dive-in' ),
				'noKey'         => __( 'Add a Gemini API key in Settings → AI Summaries to generate summaries.', 'ltt-dive-in' ),
				'openSettings'  => __( 'Open AI Summaries settings', 'ltt-dive-in' ),
				'requestFailed' => __( 'The summary could not be generated. Try again in a moment.', 'ltt-dive-in' ),
			),
		)
	);
}
add_action( 'enqueue_block_editor_assets', 'ltt_dive_in_enqueue_ai_summary_editor' );

/**
 * Return the saved summary for display.
 *
 * @param int|null $post_id Post ID. Defaults to the current post.
 * @return string Empty when there is no summary to show.
 */
function ltt_dive_in_get_ai_summary( $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();

	if ( ! $post_id || post_password_required( $post_id ) ) {
		return '';
	}

	return trim( (string) get_post_meta( $post_id, 'ltt_dive_in_ai_summary', true ) );
}
