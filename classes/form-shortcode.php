<?php


namespace Kntnt\Form_Shortcode;

class Form_Shortcode {

  private $form_count = 0;

  // Array of supported attributes and heirdefault values.
  private $defaults = [
    'id' => null,
    'class' => null,
    'style' => null,
    'no-br' => false,
    'action' => null,
    'show' => null,
  ];

  // Template for generating the form elemet.
  // Following placeholders in the template will be replaced:
  //   - {id}         => the id attribute of the form
  //   - {attributes} => the attributes of the form
  //   - {content}    => the content of the form
  private $form_template = '<form id="{id}" name="{id}" {attributes} method="post">{content}</form>';

  public function run() {

    add_filter( 'no_texturize_shortcodes', function ( $shortcodes ) {
      $shortcodes[] = 'form';
      return $shortcodes;
    } );
 
    add_shortcode( 'form', [ $this, 'shortcode' ] );

  }

  public function shortcode( $atts, $content ) {

    // Allow developers to modify the default attributes.
    $defaults = apply_filters( 'kntnt-form-shortcode-form-defaults', $this->defaults );

    // Remove unsupported attributes and add defalt values for missing ones.
    $atts = Plugin::shortcode_atts( $defaults, $atts );

    // Add `id` if missing. 
    if ( empty( $atts['id'] ) ) {
      $atts['id'] = Plugin::ns() . '-' . ++$this->form_count;
    }

    // Fetch action, show and no-br remove them from the list of attributes.
    $show = Plugin::peel_off( 'show', $atts );
    $no_br = Plugin::peel_off( 'no-br', $atts );

    // Execute field shortcodes in the enloced content.
    $fs = Plugin::instance( 'Field_Shortcode' );
    $fs->set_form_id( $atts['id'] );
    $fs->run(); // Add field shortcode
    $content = do_shortcode( $content ); // Process shortcodes in content.
    $fs->halt(); // Remove field shortcode

    // Typically, a user will put field shortcodes on separate lines.
    // Wordpress' infamous wpautop() will interpret each line as empty, thus
    // adding <br /> into HTML. The effect is a rudimentary styling of the
    // form, which is not necessary a bad thing; uses who don't can/want to
    // add their own CSS. But it is unnecessary and usually undesirable when
    // one is styling the form. Hence the option to remove them with
    // the shortcode attribute `no-br`.
    if ( $no_br ) {
      $content = strtr( $content, [ '<br />' => '' ] );
    }

    // Create a HTML form.
    $content = $this->form( $atts, $content );

    return $content;

  }

  private function form( $atts, $content ) {

    if ( $content ) {

      // Get label attributes and remove them from the array `$atts`.
      $html_atts = [
        'action' => Plugin::peel_off( 'action', $atts ),
        'class' => Plugin::peel_off( 'class', $atts ),
        'style' => Plugin::peel_off( 'style', $atts ),
      ];

      // Allow developers to modify the label attributes.
      $html_atts = apply_filters( 'kntnt-form-shortcode-form-attributes', $html_atts, $atts['id'] );

      // Allow developers to modify the label template.
      $form_template = apply_filters( 'kntnt-form-shortcode-form-template', $this->form_template, $atts['id'] );

      // Replace placeholders in the template with actual values.
      $content = strtr( $form_template, [
        '{id}' => $atts['id'],
        '{attributes}' => Plugin::attributes( $html_atts ),
        '{content}' => $content,
      ] );

    }

    return $content;

  }

}
