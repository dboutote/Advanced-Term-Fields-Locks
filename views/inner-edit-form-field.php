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
$lock = $this->create_term_lock();
?>

<ul>
	<li>
		<label>
			<input type="checkbox" name="<?php echo esc_attr( $this->meta_key ); ?>" id="<?php echo esc_attr( $this->meta_slug ); ?>-edit" value="<?php echo esc_attr( $lock ); ?>" <?php checked( !empty($meta_value) ); ?>/>
			<span class="term-lock-edit"><?php esc_html_e( 'Edit Lock', 'atf-locks' ); ?></span>
		</label>
	</li>
</ul>