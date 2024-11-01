<?php
/**
 * Plugin Name: WP order-by
 * Plugin URI: https://wordpress.org/plugins/wp-order-by/
 * Description: A wordpress post, post types and pages ordering plugin
 * Version: 1.4.2
 * Author: Uri Weil
 * Text Domain: wp-order-by
 * Domain Path: /lang/
 * License: GPL2
 * Text Domain: wp-order-by
 */
/*  Copyright  2015  by WEIL URI  (email : weiluri@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/ 

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

register_activation_hook(__FILE__,'wp_order_by_install'); 
function wp_order_by_install() {
}

register_deactivation_hook( __FILE__, 'wp_order_by_remove' );
function wp_order_by_remove() {
}

//clean plugin from db on uninstall
register_uninstall_hook( __FILE__, 'wp_order_by_uninstall' );
function wp_order_by_uninstall() {

	if ( ! current_user_can( 'activate_plugins' ) )
        return;
	global $wpdb, $table_prefix;
	$q = 'delete from '.$table_prefix.'options where option_name like "wpob-%"';
	$wpdb->query( $q );		
}

define( 'WPOB__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPOB__PLUGIN_URL', plugin_dir_url( __FILE__ ) );


require_once( WPOB__PLUGIN_DIR . 'admin-options.php' );