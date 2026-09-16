<?php
/**
 * Reusable enhanced select control.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$id                 = isset( $args['id'] ) ? sanitize_html_class( $args['id'] ) : wp_unique_id( 'ltt-select-' );
$label              = isset( $args['label'] ) && is_string( $args['label'] ) ? $args['label'] : __( 'Choose an option', 'ltt-dive-in' );
$options            = isset( $args['options'] ) && is_array( $args['options'] ) ? $args['options'] : array();
$selected_value     = isset( $args['selected_value'] ) && is_scalar( $args['selected_value'] ) ? (string) $args['selected_value'] : '';
$surface            = isset( $args['surface'] ) && 'dark' === $args['surface'] ? 'dark' : 'light';
$class_name         = isset( $args['class_name'] ) ? sanitize_html_class( $args['class_name'] ) : '';
$native_attributes  = isset( $args['native_attributes'] ) && is_array( $args['native_attributes'] ) ? $args['native_attributes'] : array();
$aria_controls      = isset( $args['aria_controls'] ) ? sanitize_html_class( $args['aria_controls'] ) : '';
$label_id           = $id . '-label';
$current_id         = $id . '-current';
$menu_id            = $id . '-options';
$icon_file          = 'dark' === $surface ? 'assets/images/icons/select-toggle-dark.svg' : 'assets/images/icons/select-toggle.svg';

if ( ! $options ) {
	return;
}
?>
<label id="<?php echo esc_attr( $label_id ); ?>" class="screen-reader-text" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
<div class="ltt-select ltt-select--<?php echo esc_attr( $surface ); ?><?php echo $class_name ? ' ' . esc_attr( $class_name ) : ''; ?>" x-data="lttSelect" x-on:click.outside="close" x-on:keydown.escape.window="close">
	<select x-ref="nativeSelect" id="<?php echo esc_attr( $id ); ?>" class="ltt-select__native"<?php echo $aria_controls ? ' aria-controls="' . esc_attr( $aria_controls ) . '"' : ''; ?><?php foreach ( $native_attributes as $attribute => $value ) : ?><?php if ( preg_match( '/^(?:aria|data)-[a-z0-9-]+$/', (string) $attribute ) ) : ?><?php echo true === $value ? ' ' . esc_attr( $attribute ) : ' ' . esc_attr( $attribute ) . '="' . esc_attr( (string) $value ) . '"'; ?><?php endif; ?><?php endforeach; ?>>
		<?php foreach ( $options as $option ) : ?>
			<?php $option_value = isset( $option['value'] ) && is_scalar( $option['value'] ) ? (string) $option['value'] : ''; ?>
			<?php $option_label = isset( $option['label'] ) && is_string( $option['label'] ) ? $option['label'] : ''; ?>
			<?php if ( ! $option_label ) { continue; } ?>
			<option value="<?php echo esc_attr( $option_value ); ?>"<?php selected( $selected_value, $option_value ); ?>><?php echo esc_html( $option_label ); ?></option>
		<?php endforeach; ?>
	</select>
	<span class="ltt-select__native-icon" aria-hidden="true"><img src="<?php echo esc_url( get_theme_file_uri( $icon_file ) ); ?>" alt="" /></span>
	<button x-ref="trigger" x-cloak class="ltt-select__trigger" type="button" x-bind:aria-expanded="open" aria-controls="<?php echo esc_attr( $menu_id ); ?>" aria-labelledby="<?php echo esc_attr( $label_id ); ?> <?php echo esc_attr( $current_id ); ?>" x-on:click="toggle" x-on:keydown.escape.prevent="close">
		<span id="<?php echo esc_attr( $current_id ); ?>" x-text="selectedLabel"></span>
		<span class="ltt-select__trigger-icon" aria-hidden="true"><img src="<?php echo esc_url( get_theme_file_uri( $icon_file ) ); ?>" alt="" /></span>
	</button>
	<div x-ref="optionsMenu" x-cloak x-show="open" x-transition.opacity.duration.200ms id="<?php echo esc_attr( $menu_id ); ?>" class="ltt-select__menu" role="group" aria-labelledby="<?php echo esc_attr( $label_id ); ?>">
		<?php foreach ( $options as $option ) : ?>
			<?php $option_value = isset( $option['value'] ) && is_scalar( $option['value'] ) ? (string) $option['value'] : ''; ?>
			<?php $option_label = isset( $option['label'] ) && is_string( $option['label'] ) ? $option['label'] : ''; ?>
			<?php if ( ! $option_label ) { continue; } ?>
			<button class="ltt-select__option" type="button" data-ltt-select-value="<?php echo esc_attr( $option_value ); ?>" x-bind:aria-pressed="isSelected($el.dataset.lttSelectValue)" x-on:click="choose"><?php echo esc_html( $option_label ); ?></button>
		<?php endforeach; ?>
	</div>
</div>
