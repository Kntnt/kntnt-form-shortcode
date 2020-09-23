<?php


namespace Kntnt\Form_Shortcode;

class Post_Handler {

  public function run() {

    // Is this page shown because of POST by a form built with this plugin?
    if ( isset( $_POST[Plugin::ns()] ) ) {

      // Verify the nonce.
      if ( ! isset( $_POST['_wpnonce'] ) ||  ! wp_verify_nonce( $_POST['_wpnonce'], Plugin::ns() ) ) {
          wp_die( '' , __( 'Something went wrong.' ), 403 );
      }

      // Hansle the post data.
      $this->handle( $_POST[Plugin::ns()] );

    }

  }

  public function handle( $id ) {

    // Fetch the posted data. The need for stripslashes() despite that Magic
    // Quotes were deprecated already in PHP 5.4 is due to WordPress backward
    // compatibility. WordPress roll their won version of "magic quotes"
    // because too much core and plugin code have come to rely on the quotes
    // being there. Jeez…
    $form_data = stripslashes_deep( $_POST[$id] );

    // Extract information on what to show.
    $show = Plugin::peel_off( 'show', $form_data);

    // Let developers do something clever with the form data. :-)
    do_action( 'kntnt-form-shortcode-post', $form_data, $id );

    // Let developers decide if this was a success or not and what to show.
    $status = apply_filters( 'kntnt-form-shortcode-post-data-status', [ 'success' => true, 'show' => $show ], $form_data, $id );

    // If `show` begins with http:// or https:// the user is redirected with
    // `Location`-header set to the content in `show`. Otherwise the content
    // of `show` is showed on either as a success or error message dependeing
    // on whether $success is true or false. If `shown` is empty, no message
    // is shown.
    if ( preg_match( '`^https?://`', $status['show'] ) ) {
      wp_redirect( $status['show'] );
      exit;
    }
    elseif ( ! empty( $status['show'] ) ) {
      $this->show_message( $status['show'], $status['success'] );
    }

  }

  private function show_message( $message, $success_message ) {
    echo "<script> alert( '$message' ); </script>"; // TODO: Replace this hack with text in the rendered form.
  }

}
