<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPMPW_My_Products {
    public static function render() {
        if ( ! is_user_logged_in() ) {
            echo '<p>You must be logged in to view your products</p>';
            return;
        }

        $user_id = get_current_user_id();
        $paged   = max( 1, get_query_var('paged', 1) );
        $per_page = 5;

        $args = array(
            'post_type'      => 'product',
            'post_status'    => array('publish', 'pending', 'draft'),
            'posts_per_page' => $per_page,
            'paged'          => $paged,
            'author'         => $user_id,
        );

        $query = new WP_Query( $args );

        echo '<h2>My products</h2>';

        if ( $query->have_posts() ) {
            echo '<table border="1" cellspacing="0" cellpadding="5">
                    <tr>
                        <th>Product name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>';
            
            while ( $query->have_posts() ) {
                $query->the_post();
                $product_id = get_the_ID();
                $price = get_post_meta( $product_id, '_price', true );
                $stock = get_post_meta( $product_id, '_stock', true );
                $status = get_post_status( $product_id );

                echo "<tr>
                        <td><a href='" . get_permalink( $product_id ) . "'>" . get_the_title() . "</a></td>
                        <td>" . esc_html( $stock ) . "</td>
                        <td>" . esc_html( $price ) . " $</td>
                        <td>" . esc_html( ucfirst( $status ) ) . "</td>
                        <td>
                            <a href='" . get_edit_post_link( $product_id ) . "'>Edit</a> |
                            <form method='post' style='display:inline;'>
                                <input type='hidden' name='product_id' value='" . esc_attr( $product_id ) . "'>
                                <input type='hidden' name='wpmpw_delete_product_nonce' value='" . wp_create_nonce('wpmpw_delete_product_action') . "'>
                                <input type='submit' name='delete_product' value='Remove' onclick='return confirm(\"Are you sure?\");'>
                            </form>
                        </td>
                    </tr>";
            }
            echo '</table>';

            echo paginate_links( array(
                'total'   => $query->max_num_pages,
                'current' => $paged,
                'format'  => '?paged=%#%',
            ) );

            wp_reset_postdata();
        } else {
            echo '<p>You don`t have any products yet</p>';
        }
    }
}
