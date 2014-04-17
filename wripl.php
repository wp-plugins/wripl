<?php
/*
  Plugin Name: Wripl
  Description: Plugin to bring wripl's easy recommendations to Wordpress.
  Version: 1.6.4
  Author: Wripl
  Author URI: http://wripl.com
 */
require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

new WriplWordpress_Plugin(__FILE__);