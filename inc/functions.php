<?php

/**
 * Checks compatibility
 *
 * @uses _atf_locks_plugin_deactivate()
 * @uses _atf_locks_plugin_compatibility_notice()
 *
 * @since 0.1.0
 *
 * @return void
 */
function _atf_locks_compatibility_check() {

	if ( ! class_exists( 'Advanced_Term_Fields' ) ) {
		add_action( 'admin_init', '_atf_locks_plugin_deactivate' );
		add_action( 'admin_notices', '_atf_locks_plugin_compatibility_notice' );
		return false;
	};

	return true;
}


/**
 * Deactivates plugin
 *
 * @since 0.1.0
 *
 * @return void
 */
function _atf_locks_plugin_deactivate() {
	deactivate_plugins( plugin_basename( ATF_LOCKS_FILE ) );
}


/**
 * Displays deactivation notice
 *
 * @since 0.1.0
 *
 * @return void
 */
function _atf_locks_plugin_compatibility_notice() {

	echo '<div class="error"><p>'
		. sprintf(
			__( '%1$s requires the %2$s plugin to function correctly. Unable to activate at this time.', 'atf-locks' ),
			'<strong>' . esc_html( 'Advanced Term Fields: Locks' ) . '</strong>',
			'<strong>' . esc_html( 'Advanced Term Fields' ) . '</strong>'
			)
		. '</p></div>';

	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}

}


/**
 * Displays upgrade notice
 *
 * @since 0.1.0
 *
 * @param bool   $updated        True|False flag for option being updated.
 * @param string $db_version_key The database key for the plugin version.
 * @param string $plugin_version The most recent plugin version.
 * @param string $db_version     The plugin version stored in the database pre upgrade.
 * @param string $meta_key       The meta field key.
 *
 * @return void
 */
function _atf_locks_version_upgraded_notice( $updated, $db_version_key, $plugin_version, $db_version, $meta_key ){
	if ( $updated ) {

		$_msg = sprintf(
			'<div class="updated notice is-dismissible"><p><b>%1$s</b> has been upgraded to version <b>%2$s</b></p></div>',
			__( 'Advanced Term Fields: Locks', 'atf-locks' ),
			$plugin_version
		);

		add_action( 'admin_notices', function() use ( $_msg ) {
			echo $_msg;
		} );

	}
}

/**
 * Sets the capability for deleting terms with term locks
 *
 * @since 0.1.0
 *
 * @param string $cap The capability being filtered.  Default is "manage_others_term_locks".
 *
 * @return string $cap The filtered capability.
 */
function _atf_manage_term_lock_cap( $cap ){
	if( is_super_admin() ) {
		return 'manage_categories';
	}
	return $cap;
}
add_filter( "atf_locks_term_delete_cap",  '_atf_manage_term_lock_cap' );
add_filter( "atf_locks_term_manage_cap",  '_atf_manage_term_lock_cap' );
add_filter( "atf_locks_term_update_cap",  '_atf_manage_term_lock_cap' );