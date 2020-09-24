<?php


namespace Kntnt\Form_Shortcode;

class Post_Handler {

    private $form_id = null;

    private $success = null;

    public function run() {

        // Is this page shown because of POST by a form built with this plugin?
        if ( isset( $_POST[ Plugin::ns() ] ) ) {

            // Verify the nonce.
            if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], Plugin::ns() ) ) {
                wp_die( '', __( 'Something went wrong.' ), 403 );
            }

            // Get the form id.
            $this->form_id = $_POST[ Plugin::ns() ];

            // Handle the post data.
            $this->handle();

        }

    }

    public function handle() {

        // Fetch the posted data. The need for stripslashes() despite that
        // Magic Quotes were deprecated already in PHP 5.4 is due to WordPress
        // backward compatibility. WordPress roll their won version of "magic
        // quotes" because "too much core and plugin code have come to rely on
        // the quotes being there". Jeez…
        $form_data = stripslashes_deep( $_POST[ $this->form_id ] );

        // Extract information whether or not to redirect.
        $redirect = Plugin::peel_off( 'redirect', $form_data );

        // Let developers modify the form data.
        $form_data = apply_filters( 'kntnt-form-shortcode-post-data', $form_data, $this->form_id );

        // Let developers decide if this was a success or not and what to show.
        $this->success = apply_filters( 'kntnt-form-shortcode-post-status', true, $form_data, $this->form_id );

        // Success?
        if ( $this->success ) {

            // Let developers do something clever with the form data. :-)
            do_action( 'kntnt-form-shortcode-post', $form_data, $this->form_id, $this->success );

            // If `redirect` is not empty, user is redirected with
            // `Location`-header set to the content in `redirect`.
            if ( $redirect ) {
                wp_redirect( $redirect );
                exit;
            }

        }

    }

    public function is_success( $form_id ) {
        return is_null( $this->form_id ) || $form_id != $this->form_id ? null : $this->success;
    }

}
