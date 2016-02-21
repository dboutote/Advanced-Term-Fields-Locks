<?php
/**
 * Add form view
 *
 * Displays the form field for adding terms.
 *
 * @package Advanced_Term_Fields
 * @subpackage Adv_Term_Fields_Locks\Views
 *
 * @since 0.1.0
 */

$lock = $this->create_term_lock();
?>

<ul>
	<li>
		<label>
			<input type="checkbox" name="<?php echo esc_attr( $this->meta_key ); ?>" id="<?php echo esc_attr( $this->meta_slug ); ?>-edit" value="<?php echo esc_attr( $lock ); ?>" />
			<span class="term-lock-edit"><?php esc_html_e( 'Edit Lock', 'atf-locks' ); ?></span>
		</label>
	</li>
</ul>