<?php


namespace Kntnt\Form_Shortcode;

class Form_Shortcode {

  // Array of supported attributes and heirdefault values.
  private static $defaults = [
    'id' => null,
    'class' => null,
    'style' => null,
    'no_br' => true,
    'post' => null,
    'success' => null,
  ];

  // Template for generating the form elemet.
  // Following placeholders in the template will be replaced:
  //   - {attributes} => the attributes of the form
  //   - {content}    => the content of the form
  private static $template = "<form method=\"post\" {attributes}>{content}</form>";

  public function run() {

    add_filter( 'no_texturize_shortcodes', function ( $shortcodes ) {
      $shortcodes[] = 'form';
      return $shortcodes;
    } );
 
    add_shortcode( 'form', [ $this, 'shortcode' ] );

  }

  public function shortcode( $atts, $content ) {

    // Remove unsupported attributes and add defalt values for missing ones.
    $atts = Plugin::shortcode_atts( self::$defaults, $atts );

    // Fetch post, show and no_br remove them from the list of attributes.
    $post = Plugin::peel_off( 'post', $atts );
    $show = Plugin::peel_off( 'show', $atts );
    $no_br = Plugin::peel_off( 'no_br', $atts );

    // Remaning attributes will be used as HTML attributes. Remove those with
    // no value, and trim and escape the other.
    $atts = array_filter( $atts, function( $att ) { return null !== $att; } );
    $atts = Plugin::esc_attrs( $atts );

    // Execute field shortcodes in the enloced content.
    Plugin::instance( 'Field_Shortcode' )->run(); // Add field shortcode
    $content = do_shortcode( $content ); // Process shortcodes in content.
    Plugin::instance( 'Field_Shortcode' )->halt(); // Remove field shortcode

    // Create a HTML form.
    $content = self::form( $atts, $content );

    // Typically, a user will put field shortcodes on separate lines.
    // Wordpress' infamous wpautop() will interpret each line as empty, thus
    // adding <br /> into HTML. The effect is a rudimentary styling of the
    // form, which is a good thing for uses who don't can/want to add their
    // own CSS. But it is unnecessary and usually undesirable when one is
    // styling the form. Hence the option to remove them with the shortcode
    // attribute `no_br`.
    if ( $no_br ) {
      $content = strtr( $content, [ '<br />' => '' ] );
    }

    return $content;

  }

  // Generate the HTML.
  private static function form( $atts, $content ) {
    $out = '';
    if ( $content ) {
      $content = "\n" . wp_nonce_field( Plugin::ns(), '_wpnonce', true, false ) . $content;
      $out = strtr( self::$template, [
        '{attributes}' => Plugin::attributes( $atts ),
        '{content}' => $content,
      ] );
    }
    return $out;
  }

}
