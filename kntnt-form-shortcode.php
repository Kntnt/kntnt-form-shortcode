<?php

/**
 * Plugin main file.
 *
 * @wordpress-plugin
 * Plugin Name:       Kntnt Form Shortcode
 * Plugin URI:        https://www.kntnt.com/
 * GitHub Plugin URI: https://github.com/Kntnt/kntnt-form-shortcode
 * Description:       Provides a shortcode-based form builder (i.e. shortcodes to build a form) that can be used to build simple forms whose data is either sent with POST to a provided URL or provided through the action hook `kntnt-form-shortcode-submit`.
 * Version:           1.2.6
 * Author:            Thomas Barregren
 * Author URI:        https://www.kntnt.com/
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 */


namespace Kntnt\Form_Shortcode;

require 'autoload.php';

defined( 'WPINC' ) && new Plugin;