<?php
/**
 * Compact layout template
 */

defined( 'ABSPATH' ) || exit;

$product   = $args['product'] ?? null;
$settings  = $args['settings'] ?? [];

if ( ! $product ) {
	return;
}
?>

<div class="drc-aww-product drc-aww-layout-compact">
	<div class="drc-aww-compact-inner">
		<?php if ( ! empty( $settings['show_image'] ) ) : ?>
			<div class="drc-aww-product-image">
				<a href="<?php echo esc_url( $product->get_permalink() ); ?>">
					<?php echo $product->get_image( 'thumbnail' ); ?>
				</a>
			</div>
		<?php endif; ?>

		<div class="drc-aww-compact-info">
			<?php if ( ! empty( $settings['show_title'] ) ) : ?>
				<h4 class="drc-aww-product-title">
					<a href="<?php echo esc_url( $product->get_permalink() ); ?>">
						<?php echo esc_html( $product->get_name() ); ?>
					</a>
				</h4>
			<?php endif; ?>

			<?php if ( ! empty( $settings['show_price'] ) ) : ?>
				<div class="drc-aww-product-price">
					<?php echo $product->get_price_html(); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>