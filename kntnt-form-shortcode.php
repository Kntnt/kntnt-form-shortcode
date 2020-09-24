<?php

/**
 * Plugin main file.
 *
 * @wordpress-plugin
 * Plugin Name:       Kntnt Form Shortcode
 * Plugin URI:        https://www.kntnt.com/
 * GitHub Plugin URI: https://github.com/Kntnt/kntnt-form-shortcode
 * Description:       Provides shortcodes to build a form (i.e. a shortcode-based form builder) that can be used to build simple forms whose data is either sent with POST to a provided URL or provided through the action hook `kntnt-form-shortcode-submit`.
 * Version:           1.0.0
 * Author:            Thomas Barregren
 * Author URI:        https://www.kntnt.com/
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 */


namespace Kntnt\Form_Shortcode;

require_once 'classes/Plugin.php';

defined( 'WPINC' ) && new Plugin;
