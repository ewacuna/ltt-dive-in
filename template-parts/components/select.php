<?php
/**
 * Reusable enhanced select control.
 *
 * Set multiple and selected_values to opt into the shared checkbox menu.
 * The name argument stays unsuffixed; multiple mode adds [] for form submission.
 * Multiple options may include count (int|null) and count_label (accessible text).
 * Set reset_label to name the placeholder differently in the menu, where it clears the choice.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$id                 = isset( $args['id'] ) ? sanitize_html_class( $args['id'] ) : wp_unique_id( 'ltt-select-' );
$label              = isset( $args['label'] ) && is_string( $args['label'] ) ? $args['label'] : __( 'Choose an option', 'ltt-dive-in' );
$options            = isset( $args['options'] ) && is_array( $args['options'] ) ? $args['options'] : array();
$placeholder        = isset( $args['placeholder'] ) && is_string( $args['placeholder'] ) ? $args['placeholder'] : '';
$reset_label        = isset( $args['reset_label'] ) && is_string( $args['reset_label'] ) ? $args['reset_label'] : '';
$selected_value     = isset( $args['selected_value'] ) && is_scalar( $args['selected_value'] ) ? (string) $args['selected_value'] : '';
$multiple           = ! empty( $args['multiple'] );
$selected_values    = array_map( 'strval', array_filter( (array) ( $args['selected_values'] ?? array() ), 'is_scalar' ) );
$surface            = isset( $args['surface'] ) && 'dark' === $args['surface'] ? 'dark' : 'light';
$class_name         = isset( $args['class_name'] ) ? sanitize_html_class( $args['class_name'] ) : '';
$native_attributes  = isset( $args['native_attributes'] ) && is_array( $args['native_attributes'] ) ? $args['native_attributes'] : array();
$aria_controls      = isset( $args['aria_controls'] ) ? sanitize_html_class( $args['aria_controls'] ) : '';
$name               = isset( $args['name'] ) && is_string( $args['name'] ) ? sanitize_key( $args['name'] ) : '';
$label_id           = $id . '-label';
$current_id         = $id . '-current';
$menu_id            = $id . '-options';
$icon_file          = 'dark' === $surface ? 'assets/images/icons/select-toggle-dark.svg' : 'assets/images/icons/select-toggle.svg';
$leading_icon       = isset( $args['icon'] ) && is_string( $args['icon'] ) ? $args['icon'] : '';

if ( ! $options ) {
	return;
}

// The placeholder resets the filter, so the menu lists it only while another value is selected.
if ( $placeholder ) {
	array_unshift(
		$options,
		array(
			'value'       => '',
			'label'       => $placeholder,
			'placeholder' => true,
		)
	);
}
?>
<label id="<?php echo esc_attr( $label_id ); ?>" class="screen-reader-text" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
<div class="ltt-select ltt-select--<?php echo esc_attr( $surface ); ?><?php echo $leading_icon ? ' ltt-select--has-icon' : ''; ?><?php echo $class_name ? ' ' . esc_attr( $class_name ) : ''; ?>" <?php if ( $multiple ) : ?>data-ltt-select-multiple data-select-id="<?php echo esc_attr( $id ); ?>" data-select-count="<?php esc_attr_e( '%d Selected', 'ltt-dive-in' ); ?>"<?php else : ?>x-data="lttSelect" x-on:click.outside="close" x-on:keydown.escape.window="close"<?php endif; ?>>
	<?php if ( $leading_icon ) : ?>
		<span class="ltt-select__icon" aria-hidden="true"><img src="<?php echo esc_url( get_theme_file_uri( $leading_icon ) ); ?>" alt="" /></span>
	<?php endif; ?>
	<select x-ref="nativeSelect" id="<?php echo esc_attr( $id ); ?>" class="ltt-select__native"<?php echo $multiple ? ' multiple="multiple"' : ''; ?><?php echo $name ? ' name="' . esc_attr( $name . ( $multiple ? '[]' : '' ) ) . '"' : ''; ?><?php echo $aria_controls ? ' aria-controls="' . esc_attr( $aria_controls ) . '"' : ''; ?><?php foreach ( $native_attributes as $attribute => $value ) : ?><?php if ( preg_match( '/^(?:aria|data)-[a-z0-9-]+$/', (string) $attribute ) ) : ?><?php echo true === $value ? ' ' . esc_attr( $attribute ) : ' ' . esc_attr( $attribute ) . '="' . esc_attr( (string) $value ) . '"'; ?><?php endif; ?><?php endforeach; ?>>
		<?php foreach ( $options as $option ) : ?>
			<?php $option_value = isset( $option['value'] ) && is_scalar( $option['value'] ) ? (string) $option['value'] : ''; ?>
			<?php $option_label = isset( $option['label'] ) && is_string( $option['label'] ) ? $option['label'] : ''; ?>
			<?php if ( ! $option_label ) { continue; } ?>
			<?php
			$option_count       = $multiple && isset( $option['count'] ) && is_int( $option['count'] ) && 0 <= $option['count'] ? $option['count'] : null;
			$option_count_text  = null !== $option_count ? number_format_i18n( $option_count ) : '';
			$option_count_label = isset( $option['count_label'] ) && is_string( $option['count_label'] ) ? $option['count_label'] : $option_count_text;
			?>
			<option value="<?php echo esc_attr( $option_value ); ?>"<?php selected( $multiple ? in_array( $option_value, $selected_values, true ) : $selected_value === $option_value ); ?><?php if ( null !== $option_count ) : ?> data-ltt-select-option-label="<?php echo esc_attr( $option_label ); ?>" data-ltt-select-option-count="<?php echo esc_attr( $option_count_text ); ?>" data-ltt-select-option-count-label="<?php echo esc_attr( $option_count_label ); ?>"<?php endif; ?>><?php echo esc_html( $option_label . ( null !== $option_count ? ' (' . $option_count_text . ')' : '' ) ); ?></option>
		<?php endforeach; ?>
	</select>
	<span class="ltt-select__native-icon" aria-hidden="true"><img src="<?php echo esc_url( get_theme_file_uri( $icon_file ) ); ?>" alt="" /></span>
	<?php if ( ! $multiple ) : ?>
	<button x-ref="trigger" x-cloak class="ltt-select__trigger" type="button" x-bind:aria-expanded="open" aria-controls="<?php echo esc_attr( $menu_id ); ?>" aria-labelledby="<?php echo esc_attr( $label_id ); ?> <?php echo esc_attr( $current_id ); ?>" x-on:click="toggle" x-on:keydown.escape.prevent="close">
		<span id="<?php echo esc_attr( $current_id ); ?>" x-text="selectedLabel"></span>
		<span class="ltt-select__trigger-icon" aria-hidden="true"><img src="<?php echo esc_url( get_theme_file_uri( $icon_file ) ); ?>" alt="" /></span>
	</button>
	<div x-ref="optionsMenu" x-cloak x-show="open" x-transition.opacity.duration.200ms id="<?php echo esc_attr( $menu_id ); ?>" class="ltt-select__menu" role="group" aria-labelledby="<?php echo esc_attr( $label_id ); ?>">
		<?php foreach ( $options as $option ) : ?>
			<?php $option_value = isset( $option['value'] ) && is_scalar( $option['value'] ) ? (string) $option['value'] : ''; ?>
			<?php $option_label = isset( $option['label'] ) && is_string( $option['label'] ) ? $option['label'] : ''; ?>
			<?php if ( ! $option_label ) { continue; } ?>
			<?php $option_label = ! empty( $option['placeholder'] ) && $reset_label ? $reset_label : $option_label; ?>
			<button class="ltt-select__option" type="button" data-ltt-select-value="<?php echo esc_attr( $option_value ); ?>" x-bind:aria-pressed="isSelected($el.dataset.lttSelectValue)" x-on:click="choose"<?php echo ! empty( $option['placeholder'] ) ? ' x-show="! isSelected($el.dataset.lttSelectValue)"' : ''; ?>><?php echo esc_html( $option_label ); ?></button>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>
</div>
