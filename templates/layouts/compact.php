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
		<div class="drc-aww-product-image">
			<a href="<?php echo esc_url( $product->get_permalink() ); ?>">
				<?php echo $product->get_image( 'thumbnail' ); ?>
			</a>
		</div>

		<div class="drc-aww-compact-info">
			<h4 class="drc-aww-product-title">
				<a href="<?php echo esc_url( $product->get_permalink() ); ?>">
					<?php echo esc_html( $product->get_name() ); ?>
				</a>
			</h4>

			<div class="drc-aww-product-price">
				<?php echo $product->get_price_html(); ?>
			</div>
		</div>
	</div>
</div>
