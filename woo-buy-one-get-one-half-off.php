<?php

/**
 * @link              https://github.com/Gigfiliate/woo-buy-one-get-one-half-off
 * @since             0.0.1
 * @package           WOOBOGOHO
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce Buy One Get One Half OFF
 * Plugin URI:        https://github.com/Gigfiliate/woo-buy-one-get-one-half-off
 * Description:       WooCommerce Buy One Get One Half OFF
 * Version:           0.0.1
 * Author:            Estrada Enterprises
 * Author URI:        https://estradaenterprises.biz/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woobogoho
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
  die;
}

define( 'GIGFILIATE_WP_VERSION', '0.0.1' );

/**
 * WooCommerce Cart Calculate Fees 
 */
add_action('woocommerce_cart_calculate_fees', function ($wc_cart)
{
  if (is_admin() && !defined('DOING_AJAX')) return;
  // TODO: Create admin settings instead of using Advanced Custom Fields Pro
  $buy_one_get_one_fifty_percent_off = get_field('buy_one_get_one_fifty_percent_off', 'option');
  if (!$buy_one_get_one_fifty_percent_off['enable']) {
    return;
  }
  $discount = 0;
  $items_prices = [];
  $qty_notice = 0; 
  $product_names = [];

  // Set HERE your targeted variable product ID
  $targeted_product_ids = $buy_one_get_one_fifty_percent_off['discounted_products'];

  foreach ($wc_cart->get_cart() as $key => $cart_item) {
    if (in_array($cart_item['product_id'], $targeted_product_ids)) {
      $qty = intval($cart_item['quantity']);
      $qty_notice += intval($cart_item['quantity']);
      for ($i = 0; $i < $qty; $i++)
        $items_prices[] = floatval($cart_item['line_subtotal'] / $cart_item['quantity']);
        $name  = (string) $cart_item['data']->get_name();
        $product_names[] = $name;
    }
  }
  $count_items_prices = count($items_prices);
  //to get the discount of lowest price sorting in descending order 
  rsort($items_prices);
  if ($count_items_prices > 1){
    foreach ($items_prices as $key => $price) {
      if ($key % 2 == 1) {
        $discount -= number_format($price / 2, 2);
      }
    }
  }
  if ($discount != 0) {
    $wc_cart->add_fee('Buy one get one 50% off', $discount, true); //EDITED
    wc_clear_notices();
    if (!is_checkout()) {
      wc_add_notice(__("Hurrah!! You got 50% off discount on the 2nd item"), 'notice');
    }
  } elseif ($qty_notice == 1) {
    wc_clear_notices();
    if (!is_checkout()) {
      $product_names = array_unique($product_names);
      wc_add_notice(sprintf(
        __("Add one more to get 50%% off on the 2nd item for %s"),
        '"<strong>' . implode(', ', $product_names) . '</strong>"'
      ), 'notice');
    }
  }
}, 10, 1);