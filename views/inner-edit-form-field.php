<?php
/**
 * Edit form view
 *
 * Displays the form field for editing terms.
 *
 * @package Advanced_Term_Fields
 * @subpackage Adv_Term_Fields_Locks\Views
 *
 * @since 0.1.0
 */

$meta_value = $this->get_meta( $term->term_id );
$icon = sprintf(
	'<i data-icon="%1$s" class="term-icon dashicons %1$s"></i>',
	esc_attr( $meta_value )
);
?>
[edit form field]