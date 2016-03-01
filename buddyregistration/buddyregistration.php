<?php

/**
 * @package Buddy Registration widget
 */
/*
Plugin Name: Buddy Registration widget
Plugin URI: http://clariontechnologies.co.in
Description: Buddy Registration
Version: 1.0.0
Author: Clarion, pawaryogesh1989
Author URI: http://clariontechnologies.co.in
License: GPLv2 or later
Text Domain: Buddy Registration widget
*/

//Plugin Constant
defined('ABSPATH') or die('Restricted direct access!');
define('AUTH_PLUGINS_PATH', plugins_url());
$plugin = plugin_basename(__FILE__);
define('BUDDY_FILE_DIRECTORY', __DIR__);

//Main Plugin files
if (!class_exists('Buddy_Registration')) {
    require('classes/class.buddy.registration.php');
}

new Buddy_Registration();
Buddy_Profile::get_instance();

// register widget
add_action('widgets_init', create_function('', 'return register_widget("Buddy_Registration");'));
?>