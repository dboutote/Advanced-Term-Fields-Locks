<?php

/**
 * Adv_Term_Fields_Locks Class
 *
 * Adds locks for taxonomy terms.
 *
 * @package Advanced_Term_Fields
 * @subpackage Adv_Term_Fields_Locks
 *
 * @since 0.1.0
 *
 */

// No direct access
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


/**
 * Adds locks for taxonomy terms
 *
 * @version 1.0.0
 *
 * @since 0.1.0
 *
 */
class Adv_Term_Fields_Locks extends Advanced_Term_Fields
{

	/**
	 * Version number
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	protected $version = ATF_LOCKS_VERSION;


	/**
	 * Metadata database key
	 *
	 * For storing/retrieving the meta value.
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	public $meta_key = '_term_lock';


	/**
	 * Singular slug for meta key
	 *
	 * Used for:
	 * - localizing js files
	 * - form field views
	 *
	 * @see Adv_Term_Fields_Locks::enqueue_admin_scripts()
	 * @see Adv_Term_Fields_Locks\Views\(add|edit|qedit).php
	 *
	 * @since 0.1.2
	 *
	 * @var string
	 */
	public $meta_slug = 'term-lock';


	/**
	 * Unique singular descriptor for meta type
	 *
	 * (e.g.) "icon", "color", "thumbnail", "image", "lock".
	 *
	 * Used in localizing js files.
	 *
	 * @see Adv_Term_Fields_Locks::enqueue_admin_scripts()
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	public $data_type = 'lock';


	#public $show_custom_column = false;


	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * @since 0.1.0
	 *
	 * @param string $file Full file path to calling plugin file
	 */
	public function __construct( $file = '' )
	{
		parent::__construct( $file );
	}


	/**
	 * Loads the class
	 *
	 * @uses Advanced_Term_Fields::show_custom_column()
	 * @uses Advanced_Term_Fields::show_custom_fields()
	 * @uses Advanced_Term_Fields::register_meta()
	 * @uses Advanced_Term_Fields::process_term_meta()
	 * @uses Advanced_Term_Fields::filter_terms_query()
	 * @uses Advanced_Term_Fields::$allowed_taxonomies
	 * @uses Adv_Term_Fields_Locks::load_admin_functions()
	 * @uses Adv_Term_Fields_Locks::show_inner_fields()
	 *
	 * @access public
	 *
	 * @since 0.1.0
	 */
	public function init()
	{
		$this->register_meta();
		$this->load_admin_functions();
		$this->show_custom_column( $this->allowed_taxonomies );
		$this->show_custom_fields( $this->allowed_taxonomies );
		$this->process_term_meta();
		$this->filter_terms_query();
		$this->show_inner_fields();

#add_filter( 'map_meta_cap' , array($this, 'map_meta_cap'), 999, 4 );
add_action ( 'pre_delete_term', array($this, 'maybe_prevent_delete'), 99, 2 );

		$this->remove_bulk_actions( $this->allowed_taxonomies );
		$this->filter_term_name();
		$this->filter_table_row_actions( $this->allowed_taxonomies );
	}


	/**
	 * Loads various admin functions
	 *
	 * - Checks for version update.
	 * - Loads js/css scripts
	 *
	 * @uses Advanced_Term_Fields::load_admin_functions()
	 *
	 * @access public
	 *
	 * @since 0.1.2
	 *
	 * @return void
	 */
	public function load_admin_functions()
	{
		parent::load_admin_functions();
		add_action( 'admin_init', array( $this, 'check_for_update' ) );
	}


	/**
	 * Loads upgrade check
	 *
	 * Checks if declared plugin version  matches the version stored in the database.
	 *
	 * @uses Adv_Term_Fields_Locks::$version
	 * @uses Adv_Term_Fields_Locks::$db_version_key
	 * @uses WordPress get_option()
	 * @uses Adv_Term_Fields_Locks::upgrade_version()
	 *
	 *
	 * @access public
	 *
	 * @since 0.1.2
	 *
	 * @return void
	 */
	public function check_for_update()
	{
		$db_version_key = $this->db_version_key;
		$db_version = get_option( $db_version_key );
		$plugin_version = $this->version;

		do_action( "atf_pre_{$this->meta_key}_upgrade_check", $db_version_key, $db_version );

		if( ! $db_version || version_compare( $db_version, $plugin_version, '<' ) ) {
			$this->upgrade_version( $db_version_key, $plugin_version, $db_version, $this->meta_key );
		}
	}


	/**
	 * Upgrades database record of plugin version
	 *
	 * @uses WordPress update_option()
	 *
	 * @since 0.1.2
	 *
	 * @param string $db_version_key The database key for the plugin version.
	 * @param string $plugin_version The most recent plugin version.
	 * @param string $db_version     The plugin version stored in the database pre upgrade.
	 * @param string $meta_key       The meta field key.
	 *
	 * @return bool $updated True if version has changed, false if not or if update failed.
	 */
	public function upgrade_version( $db_version_key, $plugin_version, $db_version = 0, $meta_key = '' )
	{
		do_action( "atf_pre_{$meta_key}_version_upgrade", $plugin_version, $db_version, $db_version_key );

		$updated = update_option( $db_version_key, $plugin_version );

		do_action( "atf_{$meta_key}_version_upgraded", $updated, $db_version_key, $plugin_version, $db_version, $meta_key );

		return $updated;
	}


	/**
	 * Sets labels for form fields
	 *
	 * @access public
	 *
	 * @since 0.1.0
	 */
	public function set_labels()
	{
		$this->labels = array(
			'singular'	  => esc_html__( 'Lock',  'atf-locks' ),
			'plural'	  => esc_html__( 'Locks', 'atf-locks' ),
			'description' => esc_html__( 'Enable locking of this term.', 'atf-locks' )
		);
	}


	/**
	 * Loads js admin scripts
	 *
	 * Note: Only loads on edit-tags.php
	 *
	 * @uses Advanced_Term_Fields::$custom_column_name
	 * @uses Advanced_Term_Fields::$meta_key
	 * @uses Advanced_Term_Fields::$data_type
	 *
	 * @access public
	 *
	 * @since 0.1.0
	 *
	 * @param string $hook The slug of the currently loaded page.
	 *
	 * @return void
	 */
	public function enqueue_admin_scripts( $hook )
	{

		wp_enqueue_script( 'atf-locks', $this->url . 'js/admin.js', array( 'jquery'), '', true );

		wp_localize_script( 'atf-locks', 'l10n_ATF_Locks', array(
			'custom_column_name' => esc_html__( $this->custom_column_name ),
			'meta_key'	         => esc_html__( $this->meta_key ),
			'meta_slug'	         => esc_html__( $this->meta_slug ),
			'data_type'	         => esc_html__( $this->data_type ),
		) );
	}


	/**
	  * Prints out CSS styles in admin head
	 *
	 * Note: Only loads on edit-tags.php
	 *
	 * @access public
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function admin_head_styles()
	{
		ob_start();
		include dirname( $this->file ) . "/css/admin.css";
		$css = ob_get_contents();
		ob_end_clean();

		echo $css;
	}


	/**
	/**
	 * Displays inner form field on Add Term form
	 *
	 * @see Advanced_Term_Fields::show_custom_fields()
	 * @see Advanced_Term_Fields::add_form_field()
	 *
	 * @uses Advanced_Term_Fields::$file To include view.
	 * @uses Advanced_Term_Fields::$meta_key To populate field attributes.
	 * @uses Advanced_Term_Fields::$meta_slug To populate CSS IDs, classes.
	 *
	 * @access public
	 *
	 * @since 0.1.0
	 *
	 * @param string $taxonomy Current taxonomy slug.
	 *
	 * @return void
	 */
	public function show_inner_field_add( $taxonomy = '' )
	{
		ob_start();
		include dirname( $this->file ) . '/views/inner-add-form-field.php';
		$field = ob_get_contents();
		ob_end_clean();

		echo $field;
	}


	/**
	 * Displays inner form field on Edit Term form
	 *
	 * @see Advanced_Term_Fields::show_custom_fields()
	 * @see Advanced_Term_Fields::edit_form_field()
	 *
	 * @uses Advanced_Term_Fields::$file To include view.
	 * @uses Advanced_Term_Fields::$meta_key To populate field attributes.
	 * @uses Advanced_Term_Fields::get_meta() To retrieve meta value.
	 * @uses Advanced_Term_Fields::$meta_slug To populate CSS IDs, classes.
	 *
	 * @access public
	 *
	 * @since 0.1.0
	 *
	 * @param object $term Term object.
	 * @param string $taxonomy Current taxonomy slug.
	 *
	 * @return void
	 */
	public function show_inner_field_edit( $term = false, $taxonomy = '' )
	{
		ob_start();
		include dirname( $this->file ) . '/views/inner-edit-form-field.php';
		$field = ob_get_contents();
		ob_end_clean();

		echo $field;
	}


	/**
	 * Displays inner form field on Quick Edit Term form
	 *
	 * @see Advanced_Term_Fields::show_custom_fields()
	 * @see Advanced_Term_Fields::quick_edit_form_field()
	 *
	 * @uses Advanced_Term_Fields::$file To include view.
	 * @uses Advanced_Term_Fields::$meta_key To populate field attributes.
	 * @uses Advanced_Term_Fields::$meta_slug To populate CSS IDs, classes.
	 *
	 * @access public
	 *
	 * @since 0.1.0
	 *
	 * @param string $column_name Name of the column to edit.
	 * @param string $screen	  The screen name.
	 * @param string $taxonomy	  Current taxonomy slug.
	 *
	 * @return void
	 */
	public function show_inner_field_qedit( $column_name = '' , $screen = '' , $taxonomy = '' )
	{
		ob_start();
		include dirname( $this->file ) . '/views/inner-quick-form-field.php';
		$field = ob_get_contents();
		ob_end_clean();

		echo $field;
	}


/**
 * Creates the value of the term lock
 *
 * @since 0.1.0
 *
 * @return mixed $lock False if no user ID is detected, string hash if user ID & current time if
 *                      successful.
 */
public function create_term_lock()
{
	$lock = false;

	if ( 0 == ( $user_id = get_current_user_id() ) ) {
		return $lock;
	}

	$now = time();
	$lock = "$now:$user_id";

	$lock = apply_filters( "atf_term_lock", $lock );

	return $lock;
}

/**
 * Checks to see if the term is locked for editing
 *
 * @since 0.1.0
 *
 * @param int $term_id ID of the term to check.
 *
 * @return mixed False: not locked or locked by current user.  Int: user ID of user with lock.
 */
public function check_term_lock( $term_id )
{
	if ( !$term = get_term( $term_id ) ) {
		return false;
	}

	if ( !$lock = get_term_meta( $term_id, $this->meta_key, true ) ) {
		return false;
	}

	$lock = explode( ':', $lock );
	$time = $lock[0];

	$user_id = isset( $lock[1] ) ? $lock[1] : 0;

	return $user_id;
}


public function map_meta_cap( $caps, $cap, $user_id, $args )
{
	// Get the current taxonomy name
	$tax_slug = $this->get_current_taxonomy();

	if( '' === $tax_slug ){
		return $caps;
	}
wp_die(__METHOD__);
	$tax = get_taxonomy( $tax_slug );
	#_debug( $tax->cap->delete_terms );

	switch ( $cap ) {
		#case $tax->cap->edit_terms :
			#$caps[] = array( 'do_not_allow' );
			#break;
		case $tax->cap->delete_terms :
			$caps[] = array( 'do_not_allow' );
			break;
		#case $tax->cap->manage_terms :
			#$caps[] = array( 'do_not_allow' );
			#break;
	}

	#_debug($args);

	#wp_die(__METHOD__);

	return $caps;
}


// Get the current taxonomy name
public function get_current_taxonomy()
{
	global $taxnow;

	$current_taxonomy = $taxnow;

	if( '' === $current_taxonomy ) {
		$current_taxonomy = ( ! empty( $_POST['taxonomy'] ) )? $_POST['taxonomy'] : '';
	}

	if( '' === $current_taxonomy && function_exists( 'get_current_screen' ) ) {
		$current_taxonomy = get_current_screen()->taxonomy;
	}

	if( is_null( $current_taxonomy ) ) {
		$current_taxonomy = '';
	}

	return $current_taxonomy;
}




public function prevent_delete_msg( $term_id, $taxonomy )
{
	return sprintf( '<h1>%s</h1><p>%s</p>',
		__( 'Locked Term', 'atf-locks' ),
		__( 'One or more of the selected terms are locked.  You are not allowed to delete.', 'atf-locks' )
	);
}





	/**
	 * Prevents deletion of term
	 *
	 * Applies filter "atf_delete_term_lock_cap" to allow other plugins to filter the capability.
	 *
	 * Checks happen in this order:
	 * - no lock: allow delete
	 * - no current user: prevent delete
	 * - current user matches lock author: allow delete
	 * - current user can manage others term locks: allow delete
	 * - current user does not match lock author: prevent delete
	 *
	 * @uses Adv_Term_Fields_Locks::prevent_delete_msg()
	 *
	 * @access public
	 *
	 * @since 0.1.0
	 *
	 * @param int    $term_id     Term ID.
	 * @param string $taxonomy Taxonomy Name.
	 *
	 * @return mixed int    $term_id If current user can delete the term or term is not locked.
	 *               object WP_Error If current user can't delete term or user is not detected.
	 *               die()  On AJAX calls: If current user can't delete term or user is not detected.
	 */
	public function maybe_prevent_delete( $term_id, $taxonomy )
	{
		// if no lock, return term ID
		if ( ! $lock = get_term_meta( $term_id, $this->meta_key, true ) ) {
			return $term_id;
		}

		$lock = explode( ':', $lock );
		$time = $lock[0];
		$lock_user_id = isset( $lock[1] ) ? $lock[1] : 0;

		// If we can't detect the current user, prevent deletion
		if ( 0 == ( $curr_user_id = get_current_user_id() ) ) :
			if ( defined('DOING_AJAX') && DOING_AJAX ) {
				wp_die(-1);
			} else {
				wp_die( $this->prevent_delete_msg(), 403 );
			}
		endif;

		// if the current user matches the term lock author, allow deletion
		if ( (int) $curr_user_id === (int) $lock_user_id ) {
			return $term_id;
		}

		// If the current user can manage others' term locks
		$delete_term_lock_cap = apply_filters("atf_delete_term_lock_cap", "manage_others_term_locks");
		if ( current_user_can( $delete_term_lock_cap ) ) {
			return $term_id;
		};

		// If the current user doesn't match the user ID from the lock, prevent deletion.
		if ( (int) $curr_user_id !== (int) $lock_user_id ) :
			if ( defined('DOING_AJAX') && DOING_AJAX ) {
				wp_die(-1);
			} else {
				wp_die( $this->prevent_delete_msg(), 403 );
			}
		endif;

		return $term_id;
	}


	/**
	 * Filters term name
	 *
	 * @see Adv_Term_Fields_Locks::term_name()
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function filter_term_name()
	{
		add_filter( 'term_name', array( $this, 'maybe_filter_term_name' ), 99, 2 );
	}


/**
* Filters term name
*
* Appends a dashicon lock icon to the end of locked term names in Terms List Table.
*
* @since 0.1.0
*
* @param string $name The term name, padded if not top-level.
* @param object $term Term object.
*
* @todo: FIX THIS
*
* @return string $name The filtered term name.
*/
public function maybe_filter_term_name( $name = '', $term = null )
{
$lock = get_term_meta( $term->term_id, $this->meta_key, true );

if ( $lock ) {
$name = '</a><span class="dashicons dashicons-lock"></span> <span class="row-title">' . $name . '</span><a href="">';
#$name .= ' <span class="dashicons dashicons-lock"></span>';
}

return $name;
}


	/**
	 * Filters table row actions
	 *
	 * @see Adv_Term_Fields_Locks::maybe_filter_row_actions()
	 *
	 * @since 0.1.0
	 *
	 * @return array $allowed_taxonomies The allowed taxonomies.
	 */
	public function filter_table_row_actions( $allowed_taxonomies = array() )
	{
		if ( ! empty( $allowed_taxonomies ) ) :
			foreach ( $allowed_taxonomies as $tax_name ) {
				add_filter( "{$tax_name}_row_actions", array( $this, 'maybe_filter_row_actions' ), 10, 2 );
			}
		endif;

		return $allowed_taxonomies;
	}


	/**
	 * Filters table row actions
	 *
	 * Removes edit|quick edit|delete links from table row actions if current user can't manage
	 * locked terms.
	 *
	 * Checks happen in this order:
	 * - no lock: allow actions
	 * - no current user: filter actions
	 * - current user matches lock author: allow actions
	 * - current user can manage others term locks: allow actions
	 * - current user does not match lock author: filter actions
	 *
	 * @access public
	 *
	 * @since 0.1.0
	 *
	 * @param array  $actions An array of action links to be displayed. Default actions:
	 *                        array( 'edit' => 'Edit', 'inline hide-if-no-js' => 'Quick Edit',
	 *                               'delete' => 'Delete', 'view' => 'View' )
	 * @param object $term    Term object.
	 */
	public function maybe_filter_row_actions( $actions, $term )
	{
		// If no lock, return $actions
		if ( ! $lock = get_term_meta( $term->term_id, $this->meta_key, true ) ) {
			return $actions;
		}

		$allowed_actions = apply_filters('atf_allowed_row_actions', array('view') );
		$filtered_actions = array_intersect_key( $actions, array_flip($allowed_actions));

		$lock = explode( ':', $lock );
		$time = $lock[0];
		$lock_user_id = isset( $lock[1] ) ? $lock[1] : 0;

		// If we can't detect the current user, filter actions.
		if ( 0 == ( $curr_user_id = get_current_user_id() ) ) :
			return $filtered_actions;
		endif;

		// If the current user matches the term lock author, allow actions.
		if ( (int) $curr_user_id === (int) $lock_user_id ) {
			return $actions;
		}

		// If the current user can manage others' term locks
		$manage_term_lock_cap = apply_filters("atf_manage_term_lock_cap", "manage_others_term_locks");
		if ( current_user_can( $manage_term_lock_cap ) ) {
			return $actions;
		};

		// If the current user doesn't match the term lock author, filter actions.
		if ( (int) $curr_user_id !== (int) $lock_user_id ) {
			return $filtered_actions;
		}

		return $filtered_actions;
	}


public function remove_bulk_actions( $allowed_taxonomies )
{
	if ( ! empty( $allowed_taxonomies ) ) :
		foreach ( $allowed_taxonomies as $tax_name ) {
			add_filter( "bulk_actions-edit-{$tax_name}", array( $this, 'maybe_filter_bulk_actions' ) );
			add_filter( "manage_edit-{$tax_name}_columns", array( $this, 'remove_cb_col' ) );
			add_filter( "manage_{$tax_name}_custom_column", array( $this, 'redo_name_column' ), 10, 3 );
			add_filter( 'list_table_primary_column', array( $this, 'get_primary_col'), 10, 2);
		}
	endif;

	return $allowed_taxonomies;
}

public function get_primary_col( $default, $screen_id )
{
	$default = 'atf-name';

	return $default;

_debug( $default );
_debug( $screen_id );
}


public function maybe_filter_bulk_actions( $actions )
{
	unset( $actions['delete'] );
	return $actions;
}

public function remove_cb_col( $columns )
{
	$new = array();
	
	unset( $columns['name'] );
	unset( $columns['cb'] );
	
	foreach( $columns as $key => $title ){
		if( 'description' === $key ) {
			$new['atf-name'] = 'Name';
		}
		$new[$key] = $title;
	}

	return $new;
}

public function redo_name_column( $empty = '', $column_name = '', $term_id = 0 )
{
	if ( empty( $_REQUEST['taxonomy'] ) || ( 'atf-name' !== $column_name ) || ! empty( $empty ) ) {
		return;
	}

	echo $term_id;

}

}