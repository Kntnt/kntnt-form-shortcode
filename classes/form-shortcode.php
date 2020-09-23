<?php


namespace Kntnt\Form_Shortcode;

class Form_Shortcode {

  private static $defaults = [
    'id' => null,
    'class' => null,
    'style' => null,
    'no_br' => true,
  ];

  private static $template = "<form method=\"post\" {attributes}>{content}</form>";
        
  public function run() {

    add_filter( 'no_texturize_shortcodes', function ( $shortcodes ) {
      $shortcodes[] = 'form';
      return $shortcodes;
    } );
 
    add_shortcode( 'form', [ $this, 'shortcode' ] );

  }

  public function shortcode( $atts, $content ) {

    $atts = Plugin::shortcode_atts( self::$defaults, $atts );
    $atts = array_filter( $atts, function( $att ) { return null !== $att; } );
    $atts = Plugin::esc_attrs( $atts );

    $no_br = Plugin::peel_off( 'no_br', $atts );

    
    Plugin::instance( 'Field_Shortcode' )->run();
    $content = do_shortcode( $content );
    Plugin::instance( 'Field_Shortcode' )->halt();

    $content = self::form( $atts, $content );

    if ( $no_br ) {
      $content = strtr( $content, [ '<br />' => '' ] );
    }

    return $content;

  }

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
