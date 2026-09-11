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
	<meta
		name="theme-color"
		content="<?php echo esc_attr( is_front_page() ? '#02162b' : '#073959' ); ?>"
		data-menu-theme-color="#073959"
	>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'ltt-dive-in' ); ?></a>

<div id="page" class="site">
	<?php
	$header_assets_uri = LTT_DIVE_IN_URI . '/assets/images/header';
	$weather           = ltt_dive_in_get_header_weather();
	$weather_link      = ltt_dive_in_get_header_option( 'ltt_dive_in_header_weather_link', array() );
	$header_classes    = is_front_page() ? 'site-header site-header--overlay' : 'site-header site-header--solid';
	?>
	<header
		id="masthead"
		class="<?php echo esc_attr( $header_classes ); ?>"
		x-data="{ searchHovered: false, searchFocused: false, searchValue: '' }"
	>
		<div class="site-header__inner">
			<div class="site-header__branding">
				<?php if ( has_custom_logo() ) : ?>
					<?php echo get_custom_logo(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core-generated custom logo markup. ?>
				<?php else : ?>
					<a class="site-header__logo-link" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
						<img class="site-header__logo" src="<?php echo esc_url( $header_assets_uri . '/main-logo.svg' ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="153" height="56">
					</a>
				<?php endif; ?>
			</div>

			<div
				id="header-navigation"
				class="site-header__navigation"
			>
				<form
					role="search"
					method="get"
					class="site-header__search ltt-search-bar ltt-search-bar--on-image"
					action="<?php echo esc_url( home_url( '/' ) ); ?>"
					x-bind:class="{ 'is-active': searchHovered || searchFocused || searchValue.length > 0 }"
					@mouseenter="searchHovered = true"
					@mouseleave="searchHovered = false"
					@focusin="searchFocused = true"
					@focusout="searchFocused = false"
				>
					<label for="header-search-field" class="screen-reader-text"><?php esc_html_e( 'Search the site', 'ltt-dive-in' ); ?></label>
					<input
						id="header-search-field"
						class="ltt-search-bar__input"
						type="search"
						name="s"
						value="<?php echo esc_attr( get_search_query() ); ?>"
						placeholder="<?php echo esc_attr_x( 'Search', 'header search placeholder', 'ltt-dive-in' ); ?>"
						data-default-placeholder="<?php echo esc_attr_x( 'Search', 'header search placeholder', 'ltt-dive-in' ); ?>"
						data-expanded-placeholder="<?php echo esc_attr_x( 'Type your search…', 'expanded header search placeholder', 'ltt-dive-in' ); ?>"
						x-model="searchValue"
						x-init="searchValue = $el.value"
						x-bind:placeholder="searchHovered || searchFocused ? $el.dataset.expandedPlaceholder : $el.dataset.defaultPlaceholder"
					>
					<button class="ltt-search-bar__submit" type="submit">
						<span class="screen-reader-text"><?php esc_html_e( 'Submit search', 'ltt-dive-in' ); ?></span>
						<img class="site-header__search-icon site-header__search-icon--default" src="<?php echo esc_url( $header_assets_uri . '/search-icon.svg' ); ?>" alt="" width="22" height="17">
						<img class="site-header__search-icon site-header__search-icon--active" src="<?php echo esc_url( $header_assets_uri . '/search-icon-active.svg' ); ?>" alt="" width="22" height="17">
					</button>
				</form>

				<nav id="site-navigation" class="main-navigation" aria-label="<?php esc_attr_e( 'Primary menu', 'ltt-dive-in' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'primary',
							'menu_id'        => 'primary-menu',
							'menu_class'     => 'main-navigation__menu',
							'container'      => false,
							'fallback_cb'    => false,
							'depth'          => 3,
							'walker'         => new LTT_Dive_In_Navigation_Walker(),
						)
					);
					?>
				</nav>

				<?php if ( has_nav_menu( 'header_utility' ) ) : ?>
					<nav class="utility-navigation" aria-label="<?php esc_attr_e( 'Top header menu', 'ltt-dive-in' ); ?>">
						<?php
						wp_nav_menu(
							array(
								'theme_location' => 'header_utility',
								'menu_id'        => 'header-utility-menu',
								'menu_class'     => 'utility-navigation__menu',
								'container'      => false,
								'fallback_cb'    => false,
								'depth'          => 1,
							)
						);
						?>
					</nav>
				<?php endif; ?>
			</div>

			<?php if ( $weather['label'] ) : ?>
				<div class="site-header__weather">
					<?php if ( is_array( $weather_link ) && ! empty( $weather_link['url'] ) ) : ?>
						<a href="<?php echo esc_url( $weather_link['url'] ); ?>"<?php echo ! empty( $weather_link['target'] ) ? ' target="' . esc_attr( $weather_link['target'] ) . '" rel="noopener noreferrer"' : ''; ?>>
					<?php else : ?>
						<span>
					<?php endif; ?>
						<?php if ( $weather['icon_url'] ) : ?>
							<img class="site-header__weather-icon" src="<?php echo esc_url( $weather['icon_url'] ); ?>" alt="" width="28" height="28">
						<?php endif; ?>
						<span class="site-header__weather-label" aria-hidden="true"><?php echo esc_html( $weather['label'] ); ?></span>
						<span class="screen-reader-text"><?php echo esc_html( $weather['accessible_label'] ); ?></span>
					<?php if ( is_array( $weather_link ) && ! empty( $weather_link['url'] ) ) : ?>
						</a>
					<?php else : ?>
						</span>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<button
				class="menu-toggle"
				type="button"
				data-menu-button
				aria-controls="header-navigation"
				aria-expanded="false"
			>
				<span
					class="screen-reader-text"
					data-menu-label
					data-open-label="<?php esc_attr_e( 'Open menu', 'ltt-dive-in' ); ?>"
					data-close-label="<?php esc_attr_e( 'Close menu', 'ltt-dive-in' ); ?>"
				><?php esc_html_e( 'Open menu', 'ltt-dive-in' ); ?></span>
				<img class="menu-toggle__icon menu-toggle__icon--open" src="<?php echo esc_url( $header_assets_uri . '/mobile-menu-icon.svg' ); ?>" alt="" width="25" height="22">
				<img class="menu-toggle__icon menu-toggle__icon--close" src="<?php echo esc_url( $header_assets_uri . '/close-icon.svg' ); ?>" alt="" width="25" height="25">
			</button>
		</div>
	</header>
