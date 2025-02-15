<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<p><?php printf( __( 'Product <strong>%s</strong> was %s.', 'wpmpw' ), esc_html( $product_name ), esc_html( $action ) ); ?></p>
<p><?php _e( 'View product:', 'wpmpw' ); ?> <a href="<?php echo esc_url( $edit_link ); ?>"><?php _e( 'Edit', 'wpmpw' ); ?></a></p>
<p><?php _e( 'Product author:', 'wpmpw' ); ?> <a href="<?php echo esc_url( $author_link ); ?>"><?php _e( 'View profile', 'wpmpw' ); ?></a></p>
