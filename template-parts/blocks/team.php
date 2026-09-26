<?php
/**
 * Meet the Team block renderer.
 *
 * @package LTT_Dive_In
 *
 * @var array $block      Block settings and attributes.
 * @var bool  $is_preview Whether the block is being rendered in the editor.
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'get_field' ) ) {
	return;
}

$is_preview      = ! empty( $is_preview );
$tag             = get_field( 'ltt_dive_in_team_tag' );
$heading         = get_field( 'ltt_dive_in_team_heading' );
$intro           = get_field( 'ltt_dive_in_team_intro' );
$contact_heading = get_field( 'ltt_dive_in_team_contact_heading' );
$contact_copy    = get_field( 'ltt_dive_in_team_contact_copy' );
$contact_link    = get_field( 'ltt_dive_in_team_contact_link' );
$members         = ltt_dive_in_prepare_team_members( get_field( 'ltt_dive_in_team_members' ) );
$heading         = is_string( $heading ) ? trim( $heading ) : '';

if ( ! $heading || ! $members ) {
	if ( $is_preview ) {
		echo '<p>' . esc_html__( 'Add a heading and at least one team member with a name and photo.', 'ltt-dive-in' ) . '</p>';
	}

	return;
}

$tag             = is_string( $tag ) ? trim( $tag ) : '';
$intro           = is_string( $intro ) ? trim( $intro ) : '';
$contact_heading = is_string( $contact_heading ) ? trim( $contact_heading ) : '';
$contact_copy    = is_string( $contact_copy ) ? trim( $contact_copy ) : '';
$has_link        = is_array( $contact_link ) && ! empty( $contact_link['title'] ) && ! empty( $contact_link['url'] );
$opens_new_tab   = $has_link && ! empty( $contact_link['target'] ) && '_blank' === $contact_link['target'];
$has_contact     = $contact_heading || $contact_copy || $has_link;
$id              = ! empty( $block['anchor'] ) ? sanitize_html_class( $block['anchor'] ) : 'team-' . sanitize_html_class( $block['id'] );
$classes         = array( 'meet-the-team' );

if ( ! empty( $block['align'] ) && 'full' === $block['align'] ) {
	$classes[] = 'alignfull';
}
?>
<section id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" aria-labelledby="<?php echo esc_attr( $id . '-title' ); ?>">
	<div class="meet-the-team__container">
		<header class="meet-the-team__header">
			<div class="meet-the-team__intro">
				<?php if ( $tag ) : ?>
					<p class="meet-the-team__tag"><?php echo esc_html( $tag ); ?></p>
				<?php endif; ?>
				<h2 id="<?php echo esc_attr( $id . '-title' ); ?>" class="meet-the-team__title"><?php echo esc_html( $heading ); ?></h2>
				<?php if ( $intro ) : ?>
					<p class="meet-the-team__copy"><?php echo esc_html( $intro ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( $has_contact ) : ?>
				<div class="meet-the-team__contact">
					<?php if ( $contact_heading || $contact_copy ) : ?>
						<div class="meet-the-team__contact-text">
							<?php if ( $contact_heading ) : ?>
								<p class="meet-the-team__contact-title"><?php echo esc_html( $contact_heading ); ?></p>
							<?php endif; ?>
							<?php if ( $contact_copy ) : ?>
								<p class="meet-the-team__contact-copy"><?php echo esc_html( $contact_copy ); ?></p>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					<?php if ( $has_link ) : ?>
						<a class="meet-the-team__contact-link ltt-button ltt-button--primary-outline" href="<?php echo esc_url( $contact_link['url'] ); ?>"<?php echo $opens_new_tab ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html( $contact_link['title'] ); ?></a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</header>

		<ul class="meet-the-team__grid" role="list">
			<?php foreach ( $members as $member ) : ?>
				<li class="meet-the-team__card">
					<div class="meet-the-team__media">
						<?php echo wp_get_attachment_image( $member['image_id'], 'large', false, array( 'alt' => '', 'loading' => 'lazy', 'sizes' => '(max-width: 767.98px) 100vw, 400px' ) ); ?>
					</div>
					<div class="meet-the-team__card-content">
						<div class="meet-the-team__card-heading">
							<h3 class="meet-the-team__name"><?php echo esc_html( $member['name'] ); ?></h3>
							<?php if ( $member['role'] ) : ?>
								<p class="meet-the-team__role"><?php echo esc_html( $member['role'] ); ?></p>
							<?php endif; ?>
						</div>
						<?php if ( $member['bio'] ) : ?>
							<p class="meet-the-team__bio"><?php echo esc_html( $member['bio'] ); ?></p>
						<?php endif; ?>
					</div>
					<?php if ( $member['socials'] ) : ?>
						<ul class="meet-the-team__socials" role="list">
							<?php foreach ( $member['socials'] as $social ) : ?>
								<li>
									<a class="meet-the-team__social-link" href="<?php echo esc_url( $social['url'] ); ?>" target="_blank" rel="noopener noreferrer">
										<span class="meet-the-team__social-icon meet-the-team__social-icon--<?php echo esc_attr( $social['network'] ); ?>" aria-hidden="true"></span>
										<span class="screen-reader-text">
											<?php
											/* translators: 1: team member name, 2: social network name. */
											echo esc_html( sprintf( __( '%1$s on %2$s (opens in a new tab)', 'ltt-dive-in' ), $member['name'], $social['label'] ) );
											?>
										</span>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
