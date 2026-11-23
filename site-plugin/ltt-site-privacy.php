<?php
/**
 * Plugin Name: LTT Site Privacy
 * Description: Keeps WordPress user names and login slugs out of public pages, link previews, feeds, and the REST API.
 * Version: 1.0.0
 * Author: Lake Tahoe Travel
 * License: GPL-2.0-or-later
 *
 * Install as a must-use plugin: copy this file to wp-content/mu-plugins/.
 *
 * @package LTT_Site_Privacy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Remove the author from oEmbed responses.
 *
 * Link previews in Discord, Slack, and similar apps read this data, so the
 * card shows only the site name and the page title.
 *
 * @param array $data oEmbed response data.
 * @return array
 */
function ltt_site_privacy_oembed_data( $data ) {
	unset( $data['author_name'], $data['author_url'] );

	return $data;
}
add_filter( 'oembed_response_data', 'ltt_site_privacy_oembed_data', 99 );

/**
 * Limit the REST API users endpoints to people who edit content.
 *
 * The block editor still lists authors for logged-in editors; anonymous
 * visitors can no longer list user names and login slugs.
 *
 * @param mixed           $result  Response to replace the requested version with.
 * @param WP_REST_Server  $server  Server instance.
 * @param WP_REST_Request $request Request used to generate the response.
 * @return mixed
 */
function ltt_site_privacy_rest_users( $result, $server, $request ) {
	$route = untrailingslashit( (string) $request->get_route() );

	if ( ! empty( $result ) || 0 !== strpos( $route, '/wp/v2/users' ) || current_user_can( 'edit_posts' ) ) {
		return $result;
	}

	return new WP_Error(
		'rest_user_cannot_view',
		__( 'Sorry, you are not allowed to list users.', 'ltt-site-privacy' ),
		array( 'status' => rest_authorization_required_code() )
	);
}
// Late priority: other plugins hook this filter and may return null, which
// would discard an earlier error.
add_filter( 'rest_pre_dispatch', 'ltt_site_privacy_rest_users', 999, 3 );

/**
 * Send author archives and ?author= requests to the home page.
 *
 * Runs before WordPress's canonical redirect, which would otherwise reveal the
 * login slug in the redirect address.
 */
function ltt_site_privacy_author_archives() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only redirect.
	if ( is_admin() || ( ! is_author() && ! isset( $_GET['author'] ) ) ) {
		return;
	}

	wp_safe_redirect( home_url( '/' ), 301 );
	exit;
}
add_action( 'template_redirect', 'ltt_site_privacy_author_archives', 1 );

/**
 * Point author links at the home page so no template can link to an archive.
 *
 * @return string
 */
function ltt_site_privacy_author_link() {
	return home_url( '/' );
}
add_filter( 'author_link', 'ltt_site_privacy_author_link', 99 );

/**
 * Show the site name instead of the author in feeds.
 *
 * @param string|null $author Author display name.
 * @return string|null
 */
function ltt_site_privacy_feed_author( $author ) {
	return is_feed() ? get_bloginfo( 'name' ) : $author;
}
add_filter( 'the_author', 'ltt_site_privacy_feed_author', 99 );

/**
 * Keep users out of the WordPress XML sitemap.
 *
 * @param WP_Sitemaps_Provider $provider Sitemap provider.
 * @param string               $name     Provider name.
 * @return WP_Sitemaps_Provider|false
 */
function ltt_site_privacy_sitemap_users( $provider, $name ) {
	return 'users' === $name ? false : $provider;
}
add_filter( 'wp_sitemaps_add_provider', 'ltt_site_privacy_sitemap_users', 10, 2 );
