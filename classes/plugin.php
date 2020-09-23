<?php


namespace Kntnt\Form_Shortcode;

class Plugin {

  static private $ns;

  static private $plugin_dir;

  static private $instances;

  public function __construct() {

    // This plugin's machine name a.k.a. slug.
    self::$ns = strtr( strtolower( __NAMESPACE__ ), '_\\', '--' );

    // Path to this plugin's directory relative file system root.
    self::$plugin_dir = strtr( dirname( __DIR__ ), '\\', '/' );

    // Setup localization.
    add_action( 'plugins_loaded', function () {
      load_plugin_textdomain( self::$ns, false, self::$ns . '/languages' );
    } );

    $this->run();

  }
  
  public static final function ns() {
    return self::$ns;
  }

  public static final function instance( $class_name, $create_always = false, $create_if_not_existing = true ) {
    if ( $create_always || $create_if_not_existing && ! isset( self::$instances[$class_name] ) ) {

        $file_name = strtr( strtolower( $class_name ), '_', '-' );
        $class_name = __NAMESPACE__ . '\\' . $class_name;
        require_once self::$plugin_dir . "/classes/$file_name.php";
        return self::$instances[$class_name] = new $class_name();
    }
    else {
      if ( isset( self::$instances[$class_name] ) ) {
        return self::$instances[$class_name];
      }
      else {
        throw new \LogicException( "No instance with name '$class_name'." );
      }
    }
  }

  public static function peel_off( $key, &$array ) {
    if( array_key_exists( $key, $array ) ) {
      $val = $array[$key];
      unset($array[$key]);
    }
    else {
      $val = null;
    }
    return $val;
  }

  public static function esc_attrs( $vals ) {
    foreach($vals as &$val) {
      $val = esc_attr( trim( $val ) );
    }
    return $vals;
  }

  public static function attributes( $atts ) {
    foreach( $atts as $att => &$val ) {
      if ( ! empty ( $val ) ) {
        $val = "{$att}=\"{$val}\"";
      }      
    }
    return join( ' ', array_filter( $atts ) );
  }

  // A more forgiving version of WP's shortcode_atts().
  public static function shortcode_atts( $pairs, $atts, $shortcode = '' ) {

    $atts = (array) $atts;
    $out = [];
    $pos = 0;
    while( $name = key($pairs) ) {
      $default = array_shift( $pairs );
      if ( array_key_exists($name, $atts ) ) {
        $out[$name] = $atts[$name];
      }
      elseif ( array_key_exists( $pos, $atts ) ) {
        $out[$name] = $atts[$pos];
        ++$pos;
      }
      else {
        $out[$name] = $default;
      }
    }

    if ( $shortcode ) {
      $out = apply_filters( "shortcode_atts_{$shortcode}", $out, $pairs, $atts, $shortcode );
    }
    
    return $out;

  }

  public function run() {
    self::instance( 'Form_Shortcode' )->run();
  }

}
