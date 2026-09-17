<?php
/** Activities block renderer. @package LTT_Dive_In */
if ( ! defined( 'ABSPATH' ) || ! function_exists( 'get_field' ) ) { return; }
$is_preview = ! empty( $is_preview );
$data = isset( $block['data'] ) && is_array( $block['data'] ) ? $block['data'] : array();
$get = static function ( $name ) use ( $data ) { $value = get_field( $name ); return ( false === $value || null === $value ) && isset( $data[ $name ] ) ? $data[ $name ] : $value; };
$items = $get( 'ltt_dive_in_activities_items' );
$title = $get( 'ltt_dive_in_activities_heading' );
$copy = $get( 'ltt_dive_in_activities_copy' );
$tag = $get( 'ltt_dive_in_activities_tag' );
$primary = $get( 'ltt_dive_in_activities_primary_link' );
$secondary = $get( 'ltt_dive_in_activities_secondary_link' );
$tiles = array();
foreach ( is_array( $items ) ? $items : array() as $item ) { $tile = ltt_dive_in_get_activity_tile_from_post( $item ); if ( $tile ) { $tiles[] = $tile; } }
if ( ! is_string( $title ) || ! trim( $title ) || ! is_string( $copy ) || ! trim( $copy ) || 4 !== count( $tiles ) ) { if ( ! empty( $is_preview ) ) { echo '<p>' . esc_html__( 'Add a heading, introduction, and exactly four published pages or posts with featured images.', 'ltt-dive-in' ) . '</p>'; } return; }
$id = ! empty( $block['anchor'] ) ? sanitize_html_class( $block['anchor'] ) : 'activities-' . sanitize_html_class( $block['id'] );
$links = array_filter( array( $primary, $secondary ), static function ( $link ) { return is_array( $link ) && ! empty( $link['title'] ) && ! empty( $link['url'] ); } );
?>
<section id="<?php echo esc_attr( $id ); ?>" class="home-activities<?php echo ! empty( $block['align'] ) && 'full' === $block['align'] ? ' alignfull' : ''; ?><?php echo $is_preview ? ' home-activities--preview' : ''; ?>" aria-labelledby="<?php echo esc_attr( $id . '-title' ); ?>">
	<div class="home-activities__container"><header class="home-activities__header"><div class="home-activities__intro"><?php if ( $tag ) : ?><p class="home-activities__tag"><?php echo esc_html( $tag ); ?></p><?php endif; ?><h2 id="<?php echo esc_attr( $id . '-title' ); ?>" class="home-activities__title"><?php echo esc_html( $title ); ?></h2><p class="home-activities__copy"><?php echo esc_html( $copy ); ?></p></div><?php if ( $links ) : ?><div class="home-activities__actions"><?php foreach ( $links as $index => $link ) : ?><a class="ltt-button ltt-button--<?php echo esc_attr( 0 === $index ? 'primary-outline' : 'secondary-outline' ); ?>" href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $link['title'] ); ?></a><?php endforeach; ?></div><?php endif; ?></header></div>
	<div class="home-activities__grid"><?php foreach ( $tiles as $tile ) : ?><article class="home-activities__card"><div class="home-activities__media" aria-hidden="true"><?php echo wp_get_attachment_image( $tile['image_id'], 'large', false, array( 'alt' => '', 'loading' => 'lazy' ) ); ?></div><div class="home-activities__card-content"><h3 class="home-activities__card-title"><?php echo esc_html( $tile['title'] ); ?></h3><?php if ( $tile['description'] ) : ?><p class="home-activities__card-copy"><?php echo esc_html( $tile['description'] ); ?></p><?php endif; ?><a class="home-activities__card-link ltt-button ltt-button--glass" href="<?php echo esc_url( $tile['url'] ); ?>"><?php echo esc_html( $tile['cta_label'] ); ?></a></div></article><?php endforeach; ?></div>
</section>
