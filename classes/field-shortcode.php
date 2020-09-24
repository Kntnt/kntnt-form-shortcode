<?php


namespace Kntnt\Form_Shortcode;


class Field_Shortcode {

    private $type_count = [];

    private $form_id;

    // Map of allowed attributes. The first key is an attribute. If the value of
    // the attribute isn't an array, it an be used with all elements with the
    // provided default value. If the value of the attribute is an array, the
    // keys of that array correspond to input types for which the attribute is
    // allowed, and the values of that array is corresponding default values.
    private $defaults = [

        // Keep type, id, class and style at top and in that order to make it
        // possible for users to use positional arguments instead of named
        // arguments for them.
        'type' => null,
        'id' => null,
        'class' => null,
        'style' => null,

        'checked' => [ 'radio' => false, 'checkbox' => false ],
        'cols' => [ 'textarea' => 20 ],
        'description' => null,
        'description-class' => null,
        'description-style' => null,
        'disabled' => null,
        'label' => null,
        'label-class' => null,
        'label-style' => null,
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
        'wrapper-class' => null,
        'wrapper-style' => null,

    ];

    // Array of supported fields and their templates.
    // Following placeholders in the templates will be replaced:
    //   - {form-id}    => the name space of this plugin
    //   - {id}         => the fields id attribute
    //   - {value}      => the value/content of the field
    //   - {attributes} => all attributes except id and value
    private $templates = [
        'html' => "<div id=\"{id}\" {attributes}>\n{value}\n</div>",
        'textarea' => "<textarea id=\"{id}\" name=\"{form-id}[{id}]\" {attributes}>\n{value}\n</textarea>",
        'text' => '<input type="text" id="{id}" name="{form-id}[{id}]" value="{value}" {attributes}>',
        'email' => '<input type="email" id="{id}" name="{form-id}[{id}]" value="{value}" {attributes}>',
        'url' => '<input type="url" id="{id}" name="{form-id}[{id}]" value="{value}" {attributes}>',
        'tel' => '<input type="tel" id="{id}" name="{form-id}[{id}]" value="{value}" {attributes}>',
        'hidden' => '<input type="hidden" id="{id}" name="{form-id}[{id}]" value="{value}" {attributes}>',
        'submit' => '<input type="submit" id="{id}" name="{form-id}[{id}]" value="{value}" {attributes}>',
        'success' => "<div id=\"{id}\" {attributes}>\n{value}\n</div>",
        'failure' => "<div id=\"{id}\" {attributes}>\n{value}\n</div>",
    ];

    // Template for generating a label.
    // Following placeholders in the template will be replaced:
    //   - {form-id}    => the id attribute of the form
    //   - {id}         => the id attribute of the associated field
    //   - {attributes} => attributes of the label if provided
    //   - {value}      => the content that replaces … in <label>…</label>
    private $label_template = '<label for="{id}" id={id}-label {attributes}>{value}</label>';

    // Template for generating a description.
    // Following placeholders in the template will be replaced:
    //   - {form-id}    => the id attribute of the form
    //   - {id}         => the id attribute of the associated field
    //   - {attributes} => attributes of the description if provided
    //   - {value}      => the content that replaces … in <div>…</div>
    private $description_template = '<div id={id}-description {attributes}>{value}</div>';

    // Template for generating the field wrapper.
    // Following placeholders in the template will be replaced:
    //   - {form-id}    => the id attribute of the form
    //   - {id}         => the id attribute of the enclosed field
    //   - {attributes} => attributes of the wrapper if provided
    //   - {label}      => <label>…</label> with a label if provided
    //   - {field}      => the field itself
    //   - {decription} => <div>…</div> with a description if provided
    private $wrapper_template = "<div id=\"{id}-wrapper\" {attributes}>\n{label}\n{field}\n{description}\n</div>\n";

    public function set_form_id( $form_id ) {
        $this->form_id = $form_id;
    }

    public function run() {
        add_shortcode( 'field', [ $this, 'shortcode' ] );
    }

    public function halt() {
        remove_shortcode( 'field' );
    }

    public function shortcode( $atts, $content = '' ) {

        // Plugin::shortcode_atts(), called below, will translate positional
        // attributes to named attributes and fill in default attributes for
        // missing ones. But we need the attribute `type` before that…
        if ( isset( $atts['type'] ) ) {
            $type = $atts['type'];
        }
        else if ( ! isset( $atts['type'] ) && isset( $atts[0] ) ) {
            $type = $atts[0];
        }

        // Do nothing if type isn't provided or not supported.
        if ( empty( $type ) || ! isset( $this->templates[ $type ] ) ) {
            return $content;
        }

        // Allow developers to modify the default attributes.
        $defaults = apply_filters( 'kntnt-form-shortcode-field-defaults', $this->defaults( $type ), $type );

        // Remove unsupported attributes and add default values for missing ones.
        $atts = Plugin::shortcode_atts( $defaults, $atts );

        // Add `id` if missing.
        if ( empty( $atts['id'] ) ) {
            if ( empty( $this->type_count[ $type ] ) ) {
                $this->type_count[ $type ] = 0;
            }
            $atts['id'] = $type . '-' . ++ $this->type_count[ $type ];
        }

        // `$post_success === true` iff the current page is shown after a
        // successful post of this form. `$post_success === true` iff the
        // current page is shown after a failed post of this form.
        $post_success = Plugin::instance( 'Post_Handler' )->is_success( $this->form_id );

        // Return nothing if this is a success message field and this pages is
        // not viewed due to the form has been posted or if there are errors
        // in the posted data.
        if ( 'success' == $type && ( true !== $post_success ) ) {
            return '';
        }

        // Return nothing if this is a failure message field and this pages is
        // not viewed due to the form has been posted or if there are no errors
        // in the posted data.
        if ( 'failure' == $type && ( false !== $post_success ) ) {
            return '';
        }

        // Prepare attributes for the wrapper.
        $wrapper_atts = [
            'id' => $atts['id'],
            'type' => $type,
            'class' => Plugin::peel_off( 'wrapper-class', $atts ),
            'style' => Plugin::peel_off( 'wrapper-style', $atts ),
            'label' => $this->label( $atts ),
            'description' => $this->description( $atts ),
        ];

        // Execute any shortcodes in the enclosed content.
        $content = do_shortcode( $content );

        // Create a HTML form field.
        $content = $this->field( $atts, $content );
        $content = $this->wrapper( $wrapper_atts, $content );

        return $content;

    }

    // Returns an array of attributes and their default values for
    // a field of the provided type.
    private function defaults( $type ) {
        $defaults = [];
        foreach ( $this->defaults as $att => $val ) {
            if ( is_array( $val ) ) {
                if ( array_key_exists( $type, $val ) ) {
                    $defaults[ $att ] = $val[ $type ];
                }
            }
            else {
                $defaults[ $att ] = $val;
            }
        }

        return $defaults;
    }

    private function label( &$atts ) {

        $content = Plugin::peel_off( 'label', $atts );

        // Allow developers to modify the the label content.
        $content = apply_filters( 'kntnt-form-shortcode-label-content', $content, $atts['type'], $atts['id'] );

        if ( $content ) {

            // Get label attributes and remove them from the array `$atts`.
            $html_atts = [
                'class' => Plugin::peel_off( 'label-class', $atts ),
                'style' => Plugin::peel_off( 'label-style', $atts ),
            ];

            // Allow developers to modify the label attributes.
            $html_atts = apply_filters( 'kntnt-form-shortcode-label-attributes', $html_atts, $atts['type'], $atts['id'] );

            // Allow developers to modify the label template.
            $template = apply_filters( 'kntnt-form-shortcode-label-template', $this->label_template, $atts['type'], $atts['id'] );

            // Replace placeholders in the template with actual values.
            $content = strtr( $template, [
                '{form-id}' => $this->form_id,
                '{id}' => $atts['id'],
                '{attributes}' => Plugin::attributes( $html_atts ),
                '{value}' => $content,
            ] );

        }

        return $content;

    }

    private function description( &$atts ) {

        // Get the description content and remove it from the array `$atts`.
        $content = Plugin::peel_off( 'description', $atts );

        // Allow developers to modify the the description content.
        $content = apply_filters( 'kntnt-form-shortcode-description-content', $content, $atts['type'], $atts['id'] );

        if ( $content ) {

            // Get description attributes and remove them from the array `$atts`.
            $html_atts = [
                'class' => Plugin::peel_off( 'description-class', $atts ),
                'style' => Plugin::peel_off( 'description-style', $atts ),
            ];

            // Allow developers to modify the description attributes.
            $html_atts = apply_filters( 'kntnt-form-shortcode-description-attributes', $html_atts, $atts['type'], $atts['id'] );

            // Allow developers to modify the description template.
            $template = apply_filters( 'kntnt-form-shortcode-description-template', $this->description_template, $atts['type'], $atts['id'] );

            // Replace placeholders in the template with actual values.
            $content = strtr( $template, [
                '{form-id}' => $this->form_id,
                '{id}' => $atts['id'],
                '{attributes}' => Plugin::attributes( $html_atts ),
                '{value}' => $content,
            ] );

        }

        return $content;

    }

    // Generate HTML for a field.
    private function field( $atts, $content ) {

        // Get type and id and remove them from the array `$atts`.
        $type = Plugin::peel_off( 'type', $atts );
        $id = Plugin::peel_off( 'id', $atts );

        // Allow developers to modify the field template.
        $template = apply_filters( 'kntnt-form-shortcode-field-template', $this->templates[ $type ], $type, $id );

        // Replace placeholders in the field template with actual values.
        $content = strtr( $template, [
            '{form-id}' => $this->form_id,
            '{id}' => $id,
            '{attributes}' => Plugin::attributes( $atts ),
            '{value}' => $content,
        ] );

        return $content;

    }

    // Generate HTML for a wrapper.
    private function wrapper( $atts, $content ) {

        // Get the ready-made label and description and remove them from the array `$atts`.
        $label = Plugin::peel_off( 'label', $atts );
        $description = Plugin::peel_off( 'description', $atts );

        // Get type and id and remove them from the array `$atts`.
        $type = Plugin::peel_off( 'type', $atts );
        $id = Plugin::peel_off( 'id', $atts );

        // Allow developers to modify the wrapper template.
        $template = apply_filters( 'kntnt-form-shortcode-wrapper-template', $this->wrapper_template, $type, $id );

        // Replace placeholders in the wrapper template with actual values.
        $content = strtr( $template, [
            '{form-id}' => $this->form_id,
            '{id}' => $id,
            '{attributes}' => Plugin::attributes( $atts ),
            '{label}' => $label,
            '{field}' => $content,
            '{description}' => $description,
        ] );

        return $content;

    }

}
