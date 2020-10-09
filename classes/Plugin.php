<?php


namespace Kntnt\Form_Shortcode;


class Plugin extends Abstract_Plugin {

    use Options;
    use Shortcodes;
    use Logger;

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
    // `<`, `>`, `&`, `”` and `‘` is replaced with corresponding HTML entity.
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

    public function classes_to_load() {
        return [
            'public' => [
                'plugins_loaded' => [
                    'Form_Shortcode',
                ],
                'template_redirect' => [
                    'Post_Handler',
                ],
            ],
        ];
    }

}
