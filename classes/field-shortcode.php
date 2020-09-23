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
    'wrapper-class' => null,
    'wrapper-style' => null,
  ];

  // Array of supported fields and their templates.
  // Following placeholders in the templates will be replaced:
  //   - {ns}         => the name space of this plugin
  //   - {id}         => the fields id attribute
  //   - {value}      => the value/content of the field
  //   - {attributes} => all attributes except id and value
  private static $templates = [
    'html' => "<div id=\"{id}\" {attributes}>\n{value}\n</div>",
    'textarea' => "<textarea id=\"{id}\" name=\"{ns}[{id}]\" {attributes}>\n{value}\n</textarea>",
    'text' => '<input type="text" id="{id}" name="{ns}[{id}]" value="{value}" {attributes}>',
    'email' => '<input type="email" id="{id}" name="{ns}[{id}]" value="{value}" {attributes}>',
    'url' => '<input type="url" id="{id}" name="{ns}[{id}]" value="{value}" {attributes}>',
    'tel' => '<input type="tel" id="{id}" name="{ns}[{id}]" value="{value}" {attributes}>',
    'hidden' => '<input type="hidden" id="{id}" name="{ns}[{id}]" value="{value}" {attributes}>',
    'submit' => '<input type="submit" id="{id}" value="{value}" {attributes}>',
  ];

  // Template for generating a label.
  // Following placeholders in the template will be replaced:
  //   - {ns}         => the name space of this plugin
  //   - {id}         => the id attribute of the associated field
  //   - {attributes} => attribues of the label if provided
  //   - {value}      => the content that replaces … in <label>…</label>
  private static $label_template = '<label for="{id}" id={id}-label {attributes}>{value}</label>';

  // Template for generating a description.
  // Following placeholders in the template will be replaced:
  //   - {ns}         => the name space of this plugin
  //   - {id}         => the id attribute of the associated field
  //   - {attributes} => attribues of the description if provided
  //   - {value}      => the content that replaces … in <div>…</div>
  private static $description_template = '<div id={id}-description {attributes}>{value}</div>';

  // Template for generating the field wrapper.
  // Following placeholders in the template will be replaced:
  //   - {ns}         => the name space of this plugin
  //   - {id}         => the id attribute of the enclosed field
  //   - {attributes} => attribues of the wrapper if provided
  //   - {label}      => <label>…</label> with a label if provided
  //   - {field}      => the field itself
  //   - {decription} => <div>…</div> with a description if provided
  private static $wrapper_template = "<div id=\"{id}-wrapper\" {attributes}>\n{label}\n{field}\n{description}\n</div>\n";

  public function run() {
    add_shortcode( 'field', [ $this, 'shortcode' ] );
  }

  public function halt() {
    remove_shortcode( 'field' );
  }

  public function shortcode( $atts, $content ) {

    // Do nothing if type isn't provided or not supported.
    if ( empty( $atts['type'] ) || ! isset( self::$templates[$atts['type']] ) ) {
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

    // Prepare attributes for the wrapper.
    $wrapper_atts = [
      'id' => $atts['id'],
      'type' => $atts['type'],
      'class' => Plugin::peel_off( 'wrapper-class', $atts ),
      'style' => Plugin::peel_off( 'wrapper-style', $atts ),
      'label' => self::label( $atts ),
      'description' => self::description( $atts ),
    ];

    // Execute any shortcodes in the enclosed content.
    $content = do_shortcode( $content );

    // Create a HTML form field.
    $content =  self::field( $atts, $content );
    $content =  self::wrapper( $wrapper_atts, $content );

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

  private static function label( &$atts ) {

    // Get the label content and remove it from the array `$atts`.
    $content = Plugin::peel_off( 'label', $atts );

    // Allow developers to modify the the label content.
    $content = apply_filters( 'kntnt-form-shortcode-label-content', $content, $atts['type'], $atts['id'] );

    if ( $content ) {

      // Get label attributes and remove them from the array `$atts`.
      $html_atts = [
        'class' => Plugin::peel_off( 'label_class', $atts ),
        'style' => Plugin::peel_off( 'label_style', $atts ),
      ];

      // Allow developers to modify the label attributes.
      $html_atts = apply_filters( 'kntnt-form-shortcode-label-attributes', $html_atts, $atts['type'], $atts['id'] );

      // Allow developers to modify the label template.
      $template = apply_filters( 'kntnt-form-shortcode-label-template', self::$label_template, $atts['type'], $atts['id'] );

      // Replace placeholders in the template with actual values.
      $content = strtr( $template, [
        '{ns}' => Plugin::ns(),
        '{id}' => $atts['id'],
        '{attributes}' => Plugin::attributes( $html_atts ),
        '{value}' => $content,
      ] );

    }

    return $content;

  }

  private static function description( &$atts ) {

    // Get the description content and remove it from the array `$atts`.
    $content = Plugin::peel_off( 'description', $atts );

    // Allow developers to modify the the description content.
    $content = apply_filters( 'kntnt-form-shortcode-description-content', $content, $atts['type'], $atts['id'] );

    if ( $content ) {

      // Get description attributes and remove them from the array `$atts`.
      $html_atts = [
        'class' => Plugin::peel_off( 'label_class', $atts ),
        'style' => Plugin::peel_off( 'label_style', $atts ),
      ];

      // Allow developers to modify the description attributes.
      $html_atts = apply_filters( 'kntnt-form-shortcode-description-attributes', $html_atts, $atts['type'], $atts['id'] );

      // Allow developers to modify the description template.
      $template = apply_filters( 'kntnt-form-shortcode-description-template', self::$description_template, $atts['type'], $atts['id'] );

      // Replace placeholders in the template with actual values.
      $content = strtr( $template, [
        '{ns}' => Plugin::ns(),
        '{id}' => $atts['id'],
        '{attributes}' => Plugin::attributes( $html_atts ),
        '{value}' => $content,
      ] );

    }

    return $content;

  }

  // Generate HTML for a field.
  private static function field( $atts, $content ) {

    // Get type and id and remove them from the array `$atts`.
    $type = Plugin::peel_off( 'type', $atts );
    $id = Plugin::peel_off( 'id', $atts );

    // Allow developers to modify the field template.
    $template = apply_filters( 'kntnt-form-shortcode-field-template', self::$templates[$type], $type, $id );

    // Replace placeholders in the field template with actual values.
    $content = strtr( $template, [
      '{ns}' => Plugin::ns(),
      '{id}' => $id,
      '{attributes}' => Plugin::attributes( $atts ),
      '{value}' => $content,
    ] );

    return $content;

  }

  // Generate HTML for a wrapper.
  private static function wrapper( $atts, $content ) {

    // Get the ready-made label and description and remove them from the array `$atts`.
    $label = Plugin::peel_off( 'label', $atts );
    $description = Plugin::peel_off( 'description', $atts );

    // Get type and id and remove them from the array `$atts`.
    $type = Plugin::peel_off( 'type', $atts );
    $id = Plugin::peel_off( 'id', $atts );

    // Allow developers to modify the wrapper template.
    $template = apply_filters( 'kntnt-form-shortcode-wrapper-template', self::$wrapper_template, $type, $id );

    // Replace placeholders in the wrapper template with actual values.
    $content = strtr( $template, [
      '{ns}' => Plugin::ns(),
      '{id}' => $id,
      '{attributes}' => Plugin::attributes( $atts ),
      '{label}' => $label,
      '{field}' => $content,
      '{description}' => $description,
    ] );

    return $content;

  }

}
