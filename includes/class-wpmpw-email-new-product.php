<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( class_exists( 'WC_Email' ) ) {
class WC_Email_New_Product extends WC_Email {

    public function __construct() {
        $this->id          = 'wc_new_product_email';
        $this->title       = __( 'New Product Notification', 'wpmpw' );
        $this->description = __( 'Sent to the admin when creating or editing a product', 'wpmpw' );

        $this->template_html  = 'emails/admin-new-product.php';
        $this->template_plain = 'emails/plain/admin-new-product.php';

        $this->recipient = get_option( 'admin_email' );

        parent::__construct();

        add_action( 'wpmpw_product_updated', array( $this, 'trigger' ), 10, 2 );
    }

    public function trigger( $product_id, $is_new ) {
        if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
            return;
        }

        $product = wc_get_product( $product_id );

        if ( ! $product ) {
            return;
        }

        $this->object = $product;

        $this->placeholders = array(
            '{product_name}' => $product->get_name(),
            '{edit_link}'    => admin_url( 'post.php?post=' . $product_id . '&action=edit' ),
            '{author_link}'  => admin_url( 'user-edit.php?user_id=' . $product->get_author_id() ),
            '{action}'       => $is_new ? __( 'created', 'wpmpw' ) : __( 'updated', 'wpmpw' ),
        );

        $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
    }

    public function get_subject() {
        return sprintf( __( 'Product %s was %s', 'wpmpw' ), '{product_name}', '{action}' );
    }

    public function get_content_html() {
        return wc_get_template_html( 
            $this->template_html, 
            array(
                'email_heading' => $this->get_subject(),
                'product_name'  => $this->placeholders['{product_name}'],
                'edit_link'     => $this->placeholders['{edit_link}'],
                'author_link'   => $this->placeholders['{author_link}'],
                'action'        => $this->placeholders['{action}'],
                'email'         => $this,
            ),
            '',
            plugin_dir_path( __FILE__ ) . 'templates/'
        );
    }

    public function get_content_plain() {
        return wc_get_template_html( 
            $this->template_plain, 
            array(
                'email_heading' => $this->get_subject(),
                'product_name'  => $this->placeholders['{product_name}'],
                'edit_link'     => $this->placeholders['{edit_link}'],
                'author_link'   => $this->placeholders['{author_link}'],
                'action'        => $this->placeholders['{action}'],
                'email'         => $this,
            ),
            '',
            plugin_dir_path( __FILE__ ) . 'templates/'
        );
    }
}
}

