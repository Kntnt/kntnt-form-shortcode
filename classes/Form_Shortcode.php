<?php


namespace Kntnt\Form_Shortcode;

class Form_Shortcode {

    private $form_count = 0;

    // Array of supported attributes and their default values.
    // Keep them in the same order to make it possible for users to use
    // positional arguments instead of named arguments for them.
    private $defaults = [
        'id' => null,
        'class' => null,
        'style' => null,
        'success' => null,
        'failure' => null,
        'action' => null,
        'keep-br' => false,
    ];

    // Template for generating the form element.
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

    public function shortcode( $atts, $content = '' ) {

        // Allow developers to modify the default attributes.
        $defaults = apply_filters( 'kntnt-form-shortcode-form-defaults', $this->defaults );

        // Remove unsupported attributes and add default values for missing ones.
        $atts = Plugin::shortcode_atts( $defaults, $atts );

        // Add `id` if missing.
        if ( empty( $atts['id'] ) ) {
            $atts['id'] = Plugin::ns() . '-' . ++ $this->form_count;
        }

        // Allow developers to modify the attributes.
        $atts = apply_filters( 'kntnt-form-shortcode-attributes', $atts, $atts['id'] );

        // Fetch and remove success/failure redirect URL from the list
        // of attributes.
        $success = Plugin::peel_off( 'success', $atts );
        $failure = Plugin::peel_off( 'failure', $atts );

        // Allow developers to modify the content before shortcodes are
        // processed.
        $content = apply_filters( 'kntnt-form-shortcode-content-before', $content, $atts['id'] );

        // Process the content.
        $content = $this->do_shortcode( $content, $atts );

        // Allow developers to modify the content after shortcodes were
        // processed.
        $content = apply_filters( 'kntnt-form-shortcode-content-after', $content, $atts['id'] );

        // Add an identifier that the Post_Handler can look for.
        $content .= strtr( '<input type="hidden" name="{ns}" value="{id}">', [ '{id}' => $atts['id'], '{ns}' => Plugin::ns() ] );

        // If no action attribute is provided, that is a URL to where this form
        // should be posted, this plugin will take care of it.
        if ( ! isset( $atts['action'] ) ) {

            // For security, we add hidden fields to be verified to prevent
            // various attacks including CSRF.
            $content .= wp_nonce_field( Plugin::ns(), '_wpnonce', true, false );

            // If a success redirect URL is provided, add it to a hidden field
            // to be used after form processing for redirection on success.
            // If not provided, current page is shown again, including any
            // provided success message field.
            if ( isset( $success ) ) {
                $content .= strtr( '<input type="hidden" name="{id}[success]" value="{success}">', [ '{success}' => esc_attr( $success ), '{id}' => $atts['id'] ] );
            }

            // If a failure redirect URL is provided, add it to a hidden field
            // to be used after form processing for redirection on failure.
            // If not provided, current page is shown again, including any
            // provided failure message field.
            if ( isset( $failure ) ) {
                $content .= strtr( '<input type="hidden" name="{id}[failure]" value="{failure}">', [ '{failure}' => esc_attr( $failure ), '{id}' => $atts['id'] ] );
            }

        }

        // Create a HTML form.
        $content = $this->form( $atts, $content );

        return $content;

    }

    private function do_shortcode( $content, &$atts ) {

        // Fetch and remove keep-br from the list of attributes.
        $keep_br = Plugin::peel_off( 'keep-br', $atts );

        // Allow the field shortcode in the enclosed content, and process it.
        $fs = Plugin::instance( 'Field_Shortcode' );
        $fs->set_form_id( $atts['id'] );
        $fs->run();
        $content = do_shortcode( $content );
        $fs->halt();

        // The dreadful wpautop() messes with shortcodes in a crazy way that
        // can only be cured with draconian measures. Let's delete all <br>:s!
        // Users that don't know how to style can put each field between
        // <p>…</p> to get a nice looking form. But for wpautop-lovers there is
        // the possibility to add `keep-br="1"` in the shortcode.
        if ( ! $keep_br ) {
            $content = strtr( $content, [ '<br />' => '', '<br>' => '' ] );
        }

        return $content;

    }

    private function form( $atts, $content ) {

        if ( $content ) {

            // Allow developers to modify the form attributes.
            $atts = apply_filters( 'kntnt-form-shortcode-form-attributes', $atts, $atts['id'] );

            // Allow developers to modify the form template.
            $form_template = apply_filters( 'kntnt-form-shortcode-form-template', $this->form_template, $atts['id'] );

            // Replace placeholders in the template with actual values.
            $content = strtr( $form_template, [
                '{id}' => $atts['id'],
                '{attributes}' => Plugin::attributes( $atts ),
                '{content}' => $content,
            ] );

            do_action( 'kntnt-form-shortcode-form', $atts['id'], $atts, $content );

        }

        return $content;

    }

}
