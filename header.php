<?php
/**
 * Site header.
 *
 * @package LTT_Dive_In
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'ltt-dive-in' ); ?></a>

<div id="page" class="site">
	<header id="masthead" class="site-header">
		<div class="container site-header__inner">
			<div class="site-branding">
				<?php ltt_dive_in_branding(); ?>
				<?php if ( get_bloginfo( 'description' ) ) : ?>
					<p class="site-description"><?php bloginfo( 'description' ); ?></p>
				<?php endif; ?>
			</div>

			<button class="menu-toggle" type="button" aria-controls="primary-menu" aria-expanded="false">
				<span class="menu-toggle__label"><?php esc_html_e( 'Menu', 'ltt-dive-in' ); ?></span>
			</button>

			<nav id="site-navigation" class="main-navigation" aria-label="<?php esc_attr_e( 'Primary menu', 'ltt-dive-in' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'menu_id'        => 'primary-menu',
						'container'      => false,
						'fallback_cb'    => false,
					)
				);
				?>
			</nav>
		</div>
	</header>
