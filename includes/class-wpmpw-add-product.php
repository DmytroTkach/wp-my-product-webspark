<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

class WPMPW_Add_Product {
    public static function render() {
        if ( ! is_user_logged_in() ) {
            echo '<p>You must be authorized to add products.</p>';
            return;
        }
        ?>
        <h2>Add product</h2>
        <form id="wpmpw-add-product-form" method="post" enctype="multipart/form-data">
            <p>
                <label for="product_title">Product name</label>
                <input type="text" name="product_title" required>
            </p>
            <p>
                <label for="product_price">Price</label>
                <input type="number" name="product_price" step="0.01" required>
            </p>
            <p>
                <label for="product_stock">Quantity</label>
                <input type="number" name="product_stock" required>
            </p>
            <p>
                <label for="product_description">Description</label>
                <?php wp_editor( '', 'product_description' ); ?>
            </p>
            <p>
                <label for="product_image">Image</label>
                <input type="hidden" name="product_image" id="product_image">
                <button type="button" id="upload_image_button" class="button">Select image</button>
                <div id="image_preview"></div>
            </p>
            <p>
            <?php wp_nonce_field( 'wpmpw_add_product_action', 'wpmpw_add_product_nonce' ); ?>
                <input type="submit" name="submit_product" value="Save product">
            </p>
        </form>
        <script>
        jQuery(document).ready(function($) {
            var mediaUploader;
            $('#upload_image_button').click(function(e) {
                e.preventDefault();
                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }
                mediaUploader = wp.media({
                    title: 'Select an image',
                    button: {
                        text: 'Select'
                    },
                    multiple: false
                });
                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('#product_image').val(attachment.id);
                    $('#image_preview').html('<img src="' + attachment.url + '" width="150">');
                });
                mediaUploader.open();
            });
        });
        </script>
        <?php
    }

    public static function handle_form_submission() {
        if ( isset( $_POST['submit_product'] ) && is_user_logged_in() ) {

            if ( ! isset( $_POST['wpmpw_add_product_nonce'] ) || 
                 ! wp_verify_nonce( $_POST['wpmpw_add_product_nonce'], 'wpmpw_add_product_action' ) ) {
                wp_die( 'Invalid request' );
            }
    
            $title       = sanitize_text_field( $_POST['product_title'] );
            $price       = floatval( $_POST['product_price'] );
            $stock       = intval( $_POST['product_stock'] );
            $description = wp_kses_post( $_POST['product_description'] );
            $image_id    = intval( $_POST['product_image'] );
            $user_id     = get_current_user_id();
    
            $product_data = array(
                'post_title'   => $title,
                'post_content' => $description,
                'post_status'  => 'pending',
                'post_type'    => 'product',
                'post_author'  => $user_id
            );
    
            $post_id = wp_insert_post( $product_data );
    
            if ( $post_id ) {
                update_post_meta( $post_id, '_price', $price );
                update_post_meta( $post_id, '_stock', $stock );
                if ( $image_id ) {
                    set_post_thumbnail( $post_id, $image_id );
                }
                wp_set_object_terms( $post_id, 'simple', 'product_type' );
    
                wp_safe_redirect( add_query_arg( 'product_added', '1', get_permalink() ) );
                exit;
            }
        }
    }
    
}

function wpmpw_notify_admin_on_product_update( $product_id, $is_new = false ) {
    do_action( 'wpmpw_product_updated', $product_id, $is_new );
}

add_action( 'init', array( 'WPMPW_Add_Product', 'handle_form_submission' ) );
?>
