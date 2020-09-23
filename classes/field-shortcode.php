<?php


namespace Kntnt\Form_Shortcode;

class Field_Shortcode {

  private static $type_count = [];

  // Map of allowed attributes. The first key is an attribute. If the value of
  // the attribute isn't an array, it an be used with all elemets with the
  // provided default value. If the value of the attribute is an array, the
  // keys of that array correspond to input types for which the attribute is
  // allowed, and the values of that array is corresponding default values.
  private static $defaults = [
    'checked' => [ 'radio' => false, 'checkbox' => false ],
    'class' => null,
    'cols' => [ 'textarea' => 20 ],
    'description' => null,
    'disabled' => null,
    'id' => null,
    'label' => null,
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
  ];

  // Array of supported fields and their templates.
  // Following placeholders in the templates will be replaced:
  //   - {id}         => the fields id attribute
  //   - {value}      => the value/content of the field
  //   - {attributes} => all attributes except id and value
  private static $templates = [
    'textarea' => '<textarea id="{id}" name="{id}" {attributes}>{value}</textarea>',
    'text' => '<input type="text" id="{id}" name="{id}" value="{value}" {attributes}>',
    'email' => '<input type="email" id="{id}" name="{id}" value="{value}" {attributes}>',
    'url' => '<input type="url" id="{id}" name="{id}" value="{value}" {attributes}>',
    'tel' => '<input type="tel" id="{id}" name="{id}" value="{value}" {attributes}>',
    'hidden' => '<input type="hidden" id="{id}" name="{id}" value="{value}" {attributes}>',
    'html' => '<div id="{id}" {attributes}>{value}</div>',
    'submit' => '<input type="submit" value="{value}" {attributes}>',
  ];

  public function run() {
    add_shortcode( 'field', [ $this, 'shortcode' ] );
  }

  public function halt() {
    remove_shortcode( 'field' );
  }

  public function shortcode( $atts, $content ) {

    // Leave shortcode as it is if its type is not provided.
    if ( empty( $atts['type'] ) ) {
      return $content;
    }

    // Add `id` if missing. 
    if ( empty( $atts['id'] ) ) {
      if ( empty( self::$type_count[$atts['type']] ) ) {
        self::$type_count[$atts['type']] = 0;
      }
      $atts['id'] = $atts['type'] . '-' . ++self::$type_count[$atts['type']];
    }

    // Remove unsupported attributes and add defalt values for missing ones.
    $atts = Plugin::shortcode_atts( self::defaults( $atts['type'] ), $atts );

    // Fetch label and description and remove them from the list of attributes.
    $label = Plugin::peel_off( 'label', $atts );
    $description = Plugin::peel_off( 'description', $atts );

    // Remaning attributes will be used as HTML attributes. Remove those with
    // no value, and trim and escape the other.
    $atts = array_filter( $atts, function( $att ) { return null !== $att; } );
    $atts = Plugin::esc_attrs( $atts );

    // Execute any shortcodes in the enclosed content.
    $content = do_shortcode( $content );

    // Create a HTML from field.
    $content =  self::field( $atts, $content, $label, $description );

    return $content;

  }

  // Returns an array of attributes and their default values for
  // a field of the provied type.
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

  // Returns a HTML form field.
  private static function field( $atts, $content ) {
    $out = '';
    $type = Plugin::peel_off( 'type', $atts );
    if ( isset( self::$templates[$type] ) ) {
      $out = strtr( self::$templates[$type], [
        '{id}' => Plugin::peel_off( 'id', $atts ),
        '{attributes}' => Plugin::attributes( $atts ),
        '{value}' => $content,
      ] );
    }
    return $out;
  }

}
