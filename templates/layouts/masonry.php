<?php
/**
 * Masonry layout template
 */

defined( 'ABSPATH' ) || exit;

$product   = $args['product'] ?? null;
$settings  = $args['settings'] ?? [];

if ( ! $product ) {
	return;
}
?>

<div class="drc-aww-product drc-aww-layout-masonry">
	<?php if ( ! empty( $settings['show_image'] ) ) : ?>
		<div class="drc-aww-product-image">
			<a href="<?php echo esc_url( $product->get_permalink() ); ?>">
				<?php echo $product->get_image( 'woocommerce_thumbnail' ); ?>
			</a>
			<?php if ( $product->is_on_sale() ) : ?>
				<span class="drc-aww-sale-badge"><?php esc_html_e( 'Sale', 'drc-advanced-woo-widgets' ); ?></span>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="drc-aww-masonry-content">
		<?php if ( ! empty( $settings['show_title'] ) ) : ?>
			<h3 class="drc-aww-product-title">
				<a href="<?php echo esc_url( $product->get_permalink() ); ?>">
					<?php echo esc_html( $product->get_name() ); ?>
				</a>
			</h3>
		<?php endif; ?>

		<div class="drc-aww-product-excerpt">
			<?php echo wp_kses_post( wp_trim_words( $product->get_short_description(), 15 ) ); ?>
		</div>

		<?php if ( ! empty( $settings['show_price'] ) ) : ?>
			<div class="drc-aww-product-price">
				<?php echo $product->get_price_html(); ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $settings['show_rating'] ) ) : ?>
			<div class="drc-aww-product-rating">
				<?php woocommerce_template_loop_rating(); ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $settings['show_add_to_cart'] ) ) : ?>
			<div class="drc-aww-product-add-to-cart">
				<?php woocommerce_template_loop_add_to_cart(); ?>
			</div>
		<?php endif; ?>
	</div>
</div>