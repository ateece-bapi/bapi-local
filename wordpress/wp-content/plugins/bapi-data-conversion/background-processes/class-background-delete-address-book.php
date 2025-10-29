<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( !class_exists( 'WP_Async_Request', false ) ) {
    include_once plugin_dir_path( WC_PLUGIN_FILE ) . 'includes/libraries/wp-async-request.php';
}

if ( !class_exists( 'WP_Background_Process', false ) ) {
    include_once plugin_dir_path( WC_PLUGIN_FILE ) . 'includes/libraries/wp-background-process.php';
}

class BAPI_Background_Delete_Address_Book extends WP_Background_Process {

    protected $action = 'bapi_delete_address_book';

    protected function task( $item ) {
        $user        = $item['user'];
        $logger      = wc_get_logger();

        if (  $user ) {
            if ( have_rows( 'address_book', 'user_' . $user->ID ) ) {

                while ( have_rows( 'address_book', 'user_' . $user->ID ) ) {
                    the_row();
                    delete_row( 'address_book', get_row_index(), 'user_' . $user->ID );

                }
            }

            $logger->info( sprintf( 'Address Book deleted for User ID %s', $user->ID ), array( 'source' => $this->action ) );
        }

        return false;
    }

    protected function complete() {

        parent::complete();

        WC_Admin_Notices::add_custom_notice(
            $this->action . '_complete',
            sprintf(
                __( 'The process that deletes all address book information has finished. <a href="%1$s">Click here to view the import log.</a>', 'woocommerce' ),
                admin_url( 'admin.php?page=wc-status&tab=logs&source=' . $this->action )

            )
        );
    }
}
