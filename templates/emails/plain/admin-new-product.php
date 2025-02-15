<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<?php printf( __( 'The product %s was %s', 'wpmpw' ), $product_name, $action ); ?>

<?php _e( 'View product:', 'wpmpw' ); ?> <?php echo $edit_link; ?>
<?php _e( 'Product author:', 'wpmpw' ); ?> <?php echo $author_link; ?>
