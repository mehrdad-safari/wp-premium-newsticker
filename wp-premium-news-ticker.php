<?php
/*
 * Plugin Name: WP-Premium news-ticker with type effect
 * Version: 1.0
 * Plugin URI: http://www.wp-premium.com/
 * Description: A simple news ticker plugin with Type effect
 * Author: Mehrdad Safari
 * Author URI: http://www.wp-premium.com/
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: wp-premium-news-ticker
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Mehrdad Safari
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-wp-premium-news-ticker.php' );
require_once( 'includes/class-wp-premium-news-ticker-settings.php' );

// Load plugin libraries
require_once( 'includes/lib/class-wp-premium-news-ticker-admin-api.php' );
require_once( 'includes/lib/class-wp-premium-news-ticker-post-type.php' );
require_once( 'includes/lib/class-wp-premium-news-ticker-taxonomy.php' );

/**
 * Returns the main instance of WP_Premium_News_Ticker to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object WP_Premium_News_Ticker
 */
function WP_Premium_News_Ticker () {
	$instance = WP_Premium_News_Ticker::instance( __FILE__, '1.0.0' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = WP_Premium_News_Ticker_Settings::instance( $instance );
	}

	return $instance;
}

WP_Premium_News_Ticker();