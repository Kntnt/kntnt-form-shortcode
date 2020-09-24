<?php


namespace Kntnt\Form_Shortcode;


class Plugin {

    static private $ns;

    static private $plugin_dir;

    static private $instances = [];

    public function __construct() {

        // This plugin's machine name a.k.a. slug.
        self::$ns = strtr( strtolower( __NAMESPACE__ ), '_\\', '--' );

        // Path to this plugin's directory relative file system root.
        self::$plugin_dir = strtr( dirname( __DIR__ ), '\\', '/' );

        // Set things in motion.
        $this->run();

    }

    // Name space of the plugin.
    public static final function ns() {
        return self::$ns;
    }

    // Returns the first created instance of the class with the provided name.
    // If no such instance exists and `$create_if_not_existing` is true, or if
    // `$create_always` is true, a new instance is created.
    public static final function instance( $class_name, $create_always = false, $create_if_not_existing = true ) {
        if ( $create_always || $create_if_not_existing && ! isset( self::$instances[ $class_name ] ) ) {
            $file_name = strtr( strtolower( $class_name ), '_', '-' );
            require_once self::$plugin_dir . "/classes/$file_name.php";
            $class = __NAMESPACE__ . '\\' . $class_name;
            $instance = new $class;
            if ( ! isset( self::$instances[ $class_name ] ) ) {
                self::$instances[ $class_name ] = $instance;
            }
            return $instance;
        }
        else {
            if ( isset( self::$instances[ $class_name ] ) ) {
                return self::$instances[ $class_name ];
            }
            else {
                throw new \LogicException( "No instance with name '$class_name'." );
            }
        }
    }

    // Removes the element with the provided key and returns it value or null
    // if it didn't exist.
    public static function peel_off( $key, &$array ) {
        if ( array_key_exists( $key, $array ) ) {
            $val = $array[ $key ];
            unset( $array[ $key ] );
        }
        else {
            $val = null;
        }

        return $val;
    }

    // Returns a string of space separated key/value-pairs from the provided
    // array. The key/value pair has the form `key="value"`. Only key/value-
    // pairs with a non-empty value after removing leading and trailing spaces
    // will be present. The value will be HTML encoded – any of the characters
    // `<`, `>`, `&`, `”` and `‘` is replaced with corresponding  HTML entity.
    public static function attributes( $attributes ) {
        $a = [];
        foreach ( $attributes as $key => $value ) {
            if ( is_string( $value ) ) {
                $value = trim( $value );
                if ( '' != $value ) {
                    $value = esc_attr( $value );
                    $a[] = "$key=\"$value\"";
                }
            }
        }
        return join( ' ', $a );
    }

    // A more forgiving version of WordPress' shortcode_atts().
    public static function shortcode_atts( $pairs, $atts, $shortcode = '' ) {

        $atts = (array) $atts;
        $out = [];
        $pos = 0;
        while ( $name = key( $pairs ) ) {
            $default = array_shift( $pairs );
            if ( array_key_exists( $name, $atts ) ) {
                $out[ $name ] = $atts[ $name ];
            }
            else if ( array_key_exists( $pos, $atts ) ) {
                $out[ $name ] = $atts[ $pos ];
                ++ $pos;
            }
            else {
                $out[ $name ] = $default;
            }
        }

        if ( $shortcode ) {
            $out = apply_filters( "shortcode_atts_{$shortcode}", $out, $pairs, $atts, $shortcode );
        }

        return $out;

    }

    // The main function of this plugin.
    public function run() {
        add_action( 'plugins_loaded', [ self::instance( 'Form_Shortcode' ), 'run' ] );
        add_action( 'template_redirect', [ self::instance( 'Post_Handler' ), 'run' ] );
    }

}
