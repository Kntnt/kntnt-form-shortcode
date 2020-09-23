<?php


namespace Kntnt\Form_Shortcode;

class Field_Shortcode {

  private static $type_count = [];

  private static $defaults = [
    'checked' => [ 'radio' => false, 'checkbox' => false ],
    'class' => null,
    'cols' => [ 'textarea' => 20 ],
    'disabled' => null,
    'id' => null,
    'max' => [ 'date' => null, 'month' => null, 'week' => null, 'time' => null, 'datetime-local' => null, 'number' => null, 'range' => null ],
    'maxlength' => [ 'password' => null, 'search' => null, 'tel' => null, 'text' => null, 'url' => null ],
    'min' => [ 'date' => null, 'month' => null, 'week' => null, 'time' => null, 'datetime-local' => null, 'number' => null, 'range' => null ],
    'minlength' => [ 'password' => null, 'search' => null, 'tel' => null, 'text' => null, 'url' => null ],
    'pattern' => [
      'email' => '^[a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$',
      'password' => null,
      'search' => null,
      'tel' => '^(\+|00)((?:9[679]|8[035789]|6[789]|5[90]|42|3[578]|2[1-689])|9[0-58]|8[1246]|6[0-6]|5[1-8]|4[013-9]|3[0-469]|2[70]|7|1)(?:\W*\d){0,13}\d$',
      'text' => null,
      'url' => '^https?:\/\/(?:[^ .:/#]+:[^ .:/#]+@)?(?:[^ .:/#]+\.)+[^ .:/#]+($|[:/#].*)$',
    ],
    'placeholder' => [ 'email' => null, 'password' => null, 'search' => null, 'tel' => null, 'text' => null, 'url' => null ],
    'readonly' => null,
    'required' => null,
    'rows' => [ 'textarea' => 5 ],
    'size' => [ 'email' => null, 'password' => null, 'tel' => null, 'text' => null ],
    'spellcheck' => [ 'textarea' => null ],
    'step' => [ 'date' => null, 'month' => null, 'week' => null, 'time' => null, 'datetime-local' => null, 'number' => null, 'range' => null ],
    'style' => null,
    'type' => null,
    // 'value' => null,
  ];

  private static $templates = [
    'textarea' => '<textarea id="{id}" name="{id}" {attributes}>{content}</textarea>',
    'text' => '<input type="text" id="{id}" name="{id}" value="{content}" {attributes}>',
    'email' => '<input type="email" id="{id}" name="{id}" value="{content}" {attributes}>',
    'url' => '<input type="url" id="{id}" name="{id}" value="{content}" {attributes}>',
    'tel' => '<input type="tel" id="{id}" name="{id}" value="{content}" {attributes}>',
    'hidden' => '<input type="hidden" id="{id}" name="{id}" value="{content}" {attributes}>',
    'html' => '<div id="{id}" {attributes}>{content}</div>',
    'submit' => '<input type="submit" value="{content}" {attributes}>',
  ];

  public function run() {
    add_shortcode( 'field', [ $this, 'shortcode' ] );
  }

  public function halt() {
    remove_shortcode( 'field' );
  }

  public function shortcode( $atts, $content ) {

    if ( ! empty( $atts['type'] ) ) {

      $atts = Plugin::shortcode_atts( self::defaults( $atts['type'] ), $atts );
      $atts = array_filter( $atts, function( $att ) { return null !== $att; } );
      $atts = Plugin::esc_attrs( $atts );

      if ( empty( $atts['id'] ) ) {
        if ( empty( self::$type_count[$atts['type']] ) ) {
          self::$type_count[$atts['type']] = 0;
        }
        $atts['id'] = $atts['type'] . '-' . ++self::$type_count[$atts['type']];
      }

      $content = do_shortcode( $content );
      $content =  self::field( $atts, $content );

    }    

    return $content;

  }

  private static function defaults( $type ) {
    $defaults = [];
    foreach ( self::$defaults as $att => $val ) {
      if ( is_array( $val ) ) {
        if ( array_key_exists( $type, $val ) ) {
          $defaults[$att] = $val[$type];          
        }
      }
      else {
        $defaults[$att] = $val;
      }
    }
    return $defaults;
  }

  private static function field( $atts, $content ) {
    $out = '';
    $type = Plugin::peel_off( 'type', $atts );
    if ( isset( self::$templates[$type] ) ) {
      $out = strtr( self::$templates[$type], [
        '{id}' => Plugin::peel_off( 'id', $atts ),
        '{attributes}' => Plugin::attributes( $atts ),
        '{content}' => $content,
      ] );
    }
    return $out;
  }

}
