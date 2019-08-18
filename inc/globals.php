<?php
/**
 * Globals.
 *
 * @package   o2-hovercards
 * @subpackage \inc\globals
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Register plugin globals.
 *
 * @since 1.0.0
 */
function o2_hovercards_register_globals() {
	$o2hc = o2_hovercards();

	// Plugin version.
	$o2hc->version = '1.0.0-beta';

	// Paths and Urls.
	$o2hc->dir      = plugin_dir_path( dirname( __FILE__ ) );
	$o2hc->inc_path = plugin_dir_path( __FILE__ );
	$o2hc->js_url   = plugins_url( 'assets/js/', dirname( __FILE__ ) );
	$o2hc->css_url  = plugins_url( 'assets/css/', dirname( __FILE__ ) );
	$o2hc->tpl_dir  = trailingslashit( plugin_dir_path( dirname( __FILE__ ) ) ) . 'templates';

	// Services globals.
	$o2hc->regex    = array();
	$o2hc->services = array();
	$o2hc->tickets  = array();
	$o2hc->urls     = array();
}

/**
 * Set services globals.
 *
 * @since 1.0.0
 */
function o2_hovercards_set_services_globals() {
	$o2hc = o2_hovercards();

	$o2hc->regex    = apply_filters( 'o2_hovercards_regex_keys', $o2hc->regex );
	$o2hc->services = apply_filters( 'o2_hovercards_regex_services', $o2hc->services );
	$o2hc->tickets  = apply_filters( 'o2_hovercards_regex_tickets', $o2hc->tickets );
	$o2hc->urls     = apply_filters( 'o2_hovercards_regex_urls', $o2hc->urls );
}
