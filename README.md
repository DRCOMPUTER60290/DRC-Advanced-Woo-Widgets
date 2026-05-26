# DRC Advanced Woo Widgets

Advanced WooCommerce product widgets for Elementor with Magical Shop Builder compatibility.

## Features

### 6 Advanced Widgets
- **Best Selling Products** - Display top selling products by various time periods
- **Deal of the Week** - Highlight discounted products with minimum discount threshold
- **Trending Products** - Show trending products based on recent sales velocity
- **Popular Products** - Display products by composite score (sales + views + ratings)
- **Recently Sold Products** - Show products sold in the last X hours
- **Flash Sale Products** - Countdown timer for time-limited sale products

### Layouts
- Grid (responsive columns)
- List (full-width with excerpt)
- Carousel (SwiperJS-powered)
- Compact (thumbnail + minimal info)
- Masonry (Pinterest-style)

### Filters
- Time period (today, this week, this month, last 7 days, last 30 days, custom)
- Category / Tags
- Featured products only
- On sale products only
- Stock status

### Sort Options
- Sales, Views, Average Rating, Review Count, Date, Random, Custom Score

### Performance
- Custom `wp_drc_product_stats` table
- Automated cron recalculation
- Transient/object cache support
- Optimized SQL queries (no `SUM(meta_value)` loops)

### Other
- AJAX pagination, load more, dynamic filters
- WP-CLI commands (`wp drc-aww recalculate`)
- Admin dashboard with stats overview
- Fully internationalized

## Requirements
- WordPress 6.0+
- PHP 7.4+
- WooCommerce 7.0+
- Elementor 3.0+

## Installation
1. Upload the `drc-advanced-woo-widgets` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu
3. Widgets appear in Elementor under "DRC Woo Widgets"

## Template Overrides
Copy templates from `/templates/layouts/` to your theme's `/drc-aww/layouts/` directory.

## WP-CLI
```
wp drc-aww recalculate    # Recalculate product stats
wp drc-aww clear-cache    # Clear all caches
wp drc-aww top-products   # Show top products
```

## Hooks
- `drc_aww_calculate_stats` - Hourly cron hook for stat recalculation
- `woocommerce_order_status_completed` - Auto-records sale events

## License
GPL-2.0+