<?php
/**
 * Hooks.
 *
 * @package   o2-hovercards
 * @subpackage \inc\hooks
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Globals and text domain.
add_action( 'o2_loaded', 'o2_hovercards_register_globals',      10 );
add_action( 'o2_loaded', 'o2_hovercards_load_textdomain',       11 );
add_action( 'init',      'o2_hovercards_set_services_globals', 100 );

// Assets.
add_action( 'init',               'o2_hovercards_register_assets', 11 );
add_action( 'wp_enqueue_scripts', 'o2_hovercards_enqueue_assets',  11 );

// Request an hovercard.
add_action( 'o2_read_api_hovercards', 'o2_hovercards_get_hovercard' );

// Hovercardify contents.
add_filter( 'the_content',   'o2_hovercards_markup_links', -10, 1 );
add_filter( 'comment_text',  'o2_hovercards_markup_links', -10, 1 );
add_filter( 'o2_found_tags', 'o2_hovercards_found_tags',    10, 1 );

// Print the hovercards template.
add_action( 'o2_templates', 'o2_hovercards_template' );
