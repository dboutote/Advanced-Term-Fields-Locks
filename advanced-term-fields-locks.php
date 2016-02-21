<?php
/**
 * Advanced Term Fields: Locks
 *
 * @package Adv_Term_Fields_Locks
 *
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GPL-2.0+
 * @version     0.1.0
 *
 * Plugin Name: Advanced Term Fields: Locks
 * Plugin URI:  http://darrinb.com/advanced-term-fields-locks
 * Description: Prevent term editing for unauthorized users.
 * Version:     0.1.0
 * Author:      Darrin Boutote
 * Author URI:  http://darrinb.com
 * Text Domain: atf-locks
 * Domain Path: /lang
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */


// No direct access
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


/**
 * @internal Nobody should be able to overrule the real version number
 * as this can cause serious issues, so no if ( ! defined() )
 *
 * @since 0.1.0
 */
define( 'ATF_LOCKS_VERSION', '0.1.0' );


if ( ! defined( 'ATF_LOCKS_FILE' ) ) {
	define( 'ATF_LOCKS_FILE', __FILE__ );
}


/**
 * Load Utilities
 *
 * @since 0.1.0
 */
include dirname( __FILE__ ) . '/inc/functions.php';


/**
 * Checks compatibility
 *
 * @since 0.1.0
 */
add_action( 'plugins_loaded', '_atf_locks_compatibility_check', 99 );


/**
 * Instantiates the main Advanced Term Fields: Locks class
 *
 * @since 0.1.0
 */
function _atf_locks_init() {

	if ( ! _atf_locks_compatibility_check() ){ return; }

	include dirname( __FILE__ ) . '/inc/class-adv-term-fields-locks.php';

	$Adv_Term_Fields_Locks = new Adv_Term_Fields_Locks( __FILE__ );
	$Adv_Term_Fields_Locks->init();

}
add_action( 'init', '_atf_locks_init', 99 );


/**
 * Run actions on plugin upgrade
 *
 * @since 0.1.0
 */
add_action( "atf__term_lock_version_upgraded", '_atf_locks_version_upgraded_notice', 10, 5 );